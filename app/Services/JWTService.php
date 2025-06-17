<?php

namespace App\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Config\Services;
use Exception;

class JWTService
{
    private $secretKey;
    private $expirationTime;

    public function __construct()
    {
        $this->secretKey = getenv('jwt.secretKey');
        if (empty($this->secretKey)) {
            log_message('error', 'JWT Secret Key is not set in .env file.');
            // Anda mungkin ingin throw exception di sini jika production
        }
        $this->expirationTime = getenv('jwt.expirationTime') ?: 3600; // Default 1 jam jika tidak ada
    }

    public function encode(array $payload): string
    {
        $issuedAt = time();
        // Pastikan expirationTime adalah integer
        $expiration = $issuedAt + (int)$this->expirationTime;

        $payload['iat'] = $issuedAt; // Issued at: time when the token was generated
        $payload['exp'] = $expiration; // Expire
        // $payload['nbf'] = $issuedAt; // Not before: token is valid since (opsional)

        return JWT::encode($payload, $this->secretKey, 'HS256');
    }

    public function decode(string $token)
    {
        if (empty($this->secretKey)) {
            log_message('critical', 'JWTService Error: Attempting to decode token without a secret key.');
            return null; // Atau throw exception
        }
        try {
            return JWT::decode($token, new Key($this->secretKey, 'HS256'));
        } catch (ExpiredException $e) {
            log_message('info', 'JWT Decode: Token expired - ' . $e->getMessage());
            return null; 
        } catch (SignatureInvalidException $e) {
            log_message('error', 'JWT Decode: Signature verification failed - ' . $e->getMessage());
            return null;
        } catch (BeforeValidException $e) {
            log_message('info', 'JWT Decode: Token not yet valid (nbf) - ' . $e->getMessage());
            return null;
        } catch (Exception $e) { // Catch all other Firebase\JWT\JWT exceptions
            log_message('error', 'JWT Decode: Invalid token - ' . $e->getMessage());
            return null;
        }
    }

}
