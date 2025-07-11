<?php
namespace App\Services;

use Midtrans\Config;
use Midtrans\Snap;
use App\Models\PaymentGatewayModel;

class PaymentService
{
    const PROVIDER_MIDTRANS = 'midtrans';
    const PROVIDER_XENDIT = 'xendit';
    const PROVIDER_HITPAY = 'hitpay';

    protected $configCache = [];

    public function __construct()
    {
        // Kosong, config diambil dinamis dari DB
    }

    /**
     * Ambil config gateway dari DB
     */
    protected function getConfig($provider, $mode = 'production')
    {
        $key = $provider.'_'.$mode;
        if (!isset($this->configCache[$key])) {
            $model = new PaymentGatewayModel();
            $row = $model->where('provider', $provider)->where('mode', $mode)->where('is_active', 1)->first();
            $this->configCache[$key] = $row ?: null;
        }
        return $this->configCache[$key];
    }

    /**
     * Entry point utama payment
     * $provider: 'midtrans', 'xendit', 'hitpay' (default)
     * $mode: 'production'|'sandbox' (opsional, default null = production)
     */
    public function createPayment($provider, $orderId, $amount, $customer, $mode = null)
    {
        $mode = $mode ?: 'production';
        switch ($provider) {
            case self::PROVIDER_MIDTRANS:
                return $this->payWithMidtrans($orderId, $amount, $customer, $mode);
            case self::PROVIDER_XENDIT:
                return $this->payWithXendit($orderId, $amount, $customer, $mode);
            case self::PROVIDER_HITPAY:
            default:
                return $this->payWithHitpay($orderId, $amount, $customer, $mode);
        }
    }

    // MIDTRANS
    public function payWithMidtrans($orderId, $amount, $customer, $mode = 'production')
    {
        $config = $this->getConfig(self::PROVIDER_MIDTRANS, $mode);
        if (!$config) return ['error'=>'Config Midtrans tidak ditemukan'];
        Config::$serverKey = $config['api_key'];
        Config::$isProduction = $mode === 'production';
        Config::$isSanitized = true;
        Config::$is3ds = true;
        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => $customer,
        ];
        $snapToken = Snap::getSnapToken($params);
        return [
            'provider' => self::PROVIDER_MIDTRANS,
            'snap_token' => $snapToken,
            'client_key' => $config['client_key'] ?? null,
            'is_production' => $mode === 'production'
        ];
    }

    // XENDIT
    public function payWithXendit($orderId, $amount, $customer, $mode = 'production')
    {
        $config = $this->getConfig(self::PROVIDER_XENDIT, $mode);
        if (!$config) return ['error'=>'Config Xendit tidak ditemukan'];
        $apiKey = $config['api_key'];
        $url = $config['api_url'] ?: 'https://api.xendit.co/v2/invoices';
        $data = [
            'external_id' => $orderId,
            'amount' => $amount,
            'payer_email' => $customer['email'] ?? '',
        ];
        $headers = [
            'Authorization: Basic ' . base64_encode($apiKey . ':')
        ];
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        return [
            'provider' => self::PROVIDER_XENDIT,
            'invoice_url' => $result['invoice_url'] ?? null,
            'raw_response' => $result,
            'is_production' => $mode === 'production'
        ];
    }

    // HITPAY
    public function payWithHitpay($orderId, $amount, $customer, $mode = 'production')
    {
        $config = $this->getConfig(self::PROVIDER_HITPAY, $mode);
        if (!$config) return ['error'=>'Config HitPay tidak ditemukan'];
        $apiKey = $config['api_key'];
        $apiUrl = $config['api_url'] ?: ($mode === 'production'
            ? 'https://api.hitpayapp.com/v1/payment-requests'
            : 'https://api.sandbox.hitpayapp.com/v1/payment-requests');
        $data = [
            'reference_number' => $orderId,
            'amount' => $amount,
            'currency' => 'IDR',
            'email' => $customer['email'] ?? '',
            'name' => $customer['first_name'] ?? '',
            'phone' => $customer['phone'] ?? '',
        ];
        $headers = [
            'X-BUSINESS-API-KEY: ' . $apiKey,
            'Content-Type: application/json'
        ];
        $ch = curl_init($apiUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($response, true);
        return [
            'provider' => self::PROVIDER_HITPAY,
            'payment_url' => $result['url'] ?? $result['payment_url'] ?? null,
            'raw_response' => $result,
            'is_production' => $mode === 'production'
        ];
    }
}
