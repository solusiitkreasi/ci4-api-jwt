<?php
namespace App\Services;

class MailService
{
    /**
     * Mengirim email menggunakan helper send_email bawaan project
     *
     * @param string $to
     * @param string $subject
     * @param string $message (HTML)
     * @return bool
     */
    public function send($to, $subject, $message)
    {
        return send_email($to, $subject, $message);
    }

    /**
     * Kirim email reset password menggunakan template view
     *
     * @param string $to
     * @param string $token
     * @return bool
     */
    public function sendResetPassword($to, $token)
    {
        $subject = 'Reset Password Akun Anda';
        $resetLink = base_url('reset_password?token=' . $token);
        $message = 'Klik tombol di bawah untuk mereset password Anda.';
        $action_button = '<a href="' . $resetLink . '" class="button">Reset Password</a>';
        $body = view('emails/reset_password', [
            'subject' => $subject,
            'message' => $message,
            'action_button' => $action_button
        ]);
        return $this->send($to, $subject, $body);
    }

    /**
     * Kirim email aktivasi akun
     *
     * @param string $to
     * @param string $token
     * @return bool
     */
    public function sendActivation($to, $token)
    {
        $subject = 'Aktivasi Akun Anda';
        $activationLink = base_url('activate?token=' . $token);
        $message = 'Klik tombol di bawah untuk aktivasi akun Anda.';
        $action_button = '<a href="' . $activationLink . '" class="button">Aktivasi Akun</a>';
        $body = view('emails/activation', [
            'subject' => $subject,
            'message' => $message,
            'action_button' => $action_button
        ]);
        return $this->send($to, $subject, $body);
    }
}
