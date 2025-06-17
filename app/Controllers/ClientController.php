<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;
use App\Models\PaymentModel;
use App\Models\LogHistoryTransaksiModel;
use App\Models\ProductModel;
use CodeIgniter\API\ResponseTrait;
use Config\Services; // Untuk mengambil data user dari filter JWT

class ClientController extends BaseController
{
    use ResponseTrait;
    protected $transactionModel;
    protected $transactionDetailModel;
    protected $paymentModel;
    protected $logHistoryModel;
    protected $productModel;
    protected $currentUser;

    public function __construct()
    {
        $this->transactionModel         = new TransactionModel();
        $this->transactionDetailModel   = new TransactionDetailModel();
        $this->paymentModel             = new PaymentModel();
        $this->logHistoryModel          = new LogHistoryTransaksiModel();
        $this->productModel             = new ProductModel();
        helper(['response', 'text']); // 'text' untuk random_string
        
        // Mengambil user yang sudah diautentikasi oleh JWTAuthFilter
        // Pastikan $request->user diset di filter atau Services::injectMock('user', $user);
        $this->currentUser = service('request')->user ?? Services::getSharedInstance('user');
    }

    public function profile()
    {
        // $this->currentUser sudah berisi data user yang login
        if (!$this->currentUser) {
            return api_error('User not authenticated.', 401); // Seharusnya sudah ditangani filter
        }
        
        return api_response($this->currentUser, 'Profile fetched successfully');
    }

    public function createTransaction()
    {
        $rules = [
            'items' => 'required|is_array',
            'items.*.product_id' => 'required|is_natural_no_zero|is_not_unique[products.id]',
            'items.*.quantity' => 'required|is_natural_no_zero',
            // Tambahkan validasi untuk payment jika ada
            'payment.method' => 'permit_empty|alpha_dash', // Contoh: 'bank_transfer', 'credit_card'
            'payment.details' => 'permit_empty|is_array'  // Data spesifik per metode bayar
        ];

        $messages = [
            'items.*.product_id' => [
                'is_not_unique' => 'One or more products are invalid or not found.'
            ]
        ];

        if (!$this->validate($rules, $messages)) {
            return api_error('Validation failed', $this->getResponsegetStatusCode(), $this->validator->getErrors());
        }

        $items = $this->request->getVar('items');
        $paymentData = $this->request->getVar('payment');
        $userId = $this->currentUser->id;

        $db = \Config\Database::connect();
        $db->transStart(); // Mulai transaksi database

        try {
            $totalAmount = 0;
            $transactionDetailsData = [];

            foreach ($items as $item) {
                $product = $this->productModel->find($item->product_id);
                if (!$product || $product['stock'] < $item->quantity) {
                    $db->transRollback();
                    return api_error("Product '{$product['name']}' is out of stock or insufficient.", 400);
                }

                $subtotal = $product['price'] * $item->quantity;
                $totalAmount += $subtotal;

                $transactionDetailsData[] = [
                    // 'transaction_id' akan diisi setelah transaksi utama dibuat
                    'product_id'            => $item->product_id,
                    'quantity'              => $item->quantity,
                    'price_per_unit'        => $product['price'], // Harga saat transaksi
                    'subtotal'              => $subtotal
                ];

                // Kurangi stok produk
                $this->productModel->update($item->product_id, ['stock' => $product['stock'] - $item->quantity]);
            }
            
            $transactionCode = 'INV/' . date('Ymd') . '/' . strtoupper(random_string('alnum', 6));

            // 1. Simpan ke tabel 'transactions'
            $transactionData = [
                'user_id' => $userId,
                'transaction_code' => $transactionCode,
                'total_amount' => $totalAmount,
                'status' => 'pending', // Status awal
            ];
            $transactionId = $this->transactionModel->insert($transactionData);

            if (!$transactionId) {
                $db->transRollback();
                return api_error('Failed to create transaction.', 500, $this->transactionModel->errors());
            }

            // 2. Simpan ke tabel 'transaction_details'
            foreach ($transactionDetailsData as &$detail) {
                $detail['transaction_id'] = $transactionId;
            }
            unset($detail); // Hapus referensi
            if (!$this->transactionDetailModel->insertBatch($transactionDetailsData)) {
                $db->transRollback();
                return api_error('Failed to save transaction details.', 500, $this->transactionDetailModel->errors());
            }

            // 3. Simpan ke tabel 'payments' (jika ada)
            if (!empty($paymentData) && !empty($paymentData->method)) {
                $paymentInput = [
                    'transaction_id'    => $transactionId,
                    'payment_method'    => $paymentData->method,
                    'amount_paid'       => $totalAmount, // Asumsi bayar lunas, bisa disesuaikan
                    'payment_status'    => 'pending', // Atau 'success' jika langsung
                    'paid_at'           => date('Y-m-d h:i:s'),
                    'payment_gateway_response' => '-'
                    // 'payment_proof_url' => $paymentData['proof_url'] ?? null,
                    // 'external_payment_id' => $paymentData['external_id'] ?? null,
                ];
                $paymentSave = $this->paymentModel->insert($paymentInput);

                if (!$paymentSave) {
                    $db->transRollback();
                    return api_error('Failed to record payment.', 500, $this->paymentModel->errors());
                }
            }

            // 4. Simpan ke tabel 'log_history_transaksi'
            $logData = [
                'transaction_id'    => $transactionId,
                'user_id'           => $userId,
                'action'            => 'Transaction Created',
                'details'           => json_encode(['items_count' => count($items), 'total' => $totalAmount])
            ];
            $this->logHistoryModel->insert($logData);

            $db->transCommit(); // Selesaikan transaksi

            // Ambil data transaksi yang baru dibuat untuk response
            $newTransaction = $this->transactionModel
                ->select('transactions.*, users.name as user_name, users.email as user_email')
                ->join('users', 'users.id = transactions.user_id')
                ->find($transactionId);
            
            // Ambil detailnya juga
            $newTransaction['details'] = $this->transactionDetailModel
                ->select('transaction_details.*, products.name as product_name')
                ->join('products', 'products.id = transaction_details.product_id')
                ->where('transaction_id', $transactionId)->findAll();

            return api_response($newTransaction, 'Transaction created successfully', 201);

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', '[ERROR] Create Transaction: ' . $e->getMessage());
            return api_error('An error occurred during transaction: ' . $e->getMessage(), 500);
        }
    }

    public function listTransactions()
    {
        $userId = $this->currentUser->id;
        $transactions = $this->transactionModel
            ->where('user_id', $userId)
            ->orderBy('transaction_date', 'DESC')
            ->findAll();
        return api_response($transactions, 'User transactions fetched successfully');
    }

    public function getTransactionDetail($transactionCode)
    {
        $userId = $this->currentUser->id;
        $transaction = $this->transactionModel
            ->select('transactions.*, users.name as user_name, users.email as user_email')
            ->join('users', 'users.id = transactions.user_id')
            ->where('transactions.user_id', $userId)
            ->where('transactions.transaction_code', $transactionCode)
            ->first();

        if (!$transaction) {
            return api_error('Transaction not found or access denied.', 404);
        }

        $transaction['details'] = $this->transactionDetailModel
            ->select('transaction_details.*, products.name as product_name')
            ->join('products', 'products.id = transaction_details.product_id')
            ->where('transaction_id', $transaction['id'])->findAll();
        
        // Jika ada payment
        $transaction['payment'] = $this->paymentModel->where('transaction_id', $transaction['id'])->first();
        
        // Jika ada log
        $transaction['logs'] = $this->logHistoryModel->where('transaction_id', $transaction['id'])->orderBy('created_at', 'ASC')->findAll();

        return api_response($transaction, 'Transaction detail fetched successfully');
    }

}