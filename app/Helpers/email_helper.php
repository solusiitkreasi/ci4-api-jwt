<?php

if (!function_exists('send_email')) {
    function send_email($to, $subject, $message)
    {
        $email = \Config\Services::email();
        $email->setTo($to);
        $email->setSubject($subject);
        $email->setMessage($message);
        $email->setMailType('html');

        if (!$email->send()) {
            log_message('error', 'Email gagal dikirim ke: ' . $to . ' | ' . $email->printDebugger(['headers']));
            return false;
        }

        return true;
    }
}