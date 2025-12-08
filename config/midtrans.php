<?php
// config/midtrans.php

class MidtransConfig {
    // =====================================================
    // SANDBOX CREDENTIALS - Ganti dengan credentials Anda
    // Dapatkan di: https://dashboard.sandbox.midtrans.com
    // =====================================================
    
    const IS_PRODUCTION = false;
    const MERCHANT_ID = 'G226966011'; // Ganti dengan Merchant ID Anda
    const CLIENT_KEY = 'Mid-client-s5DG2q-kK2fKcng9'; // Ganti dengan Client Key Anda
    const SERVER_KEY = 'Mid-server-PYziIHzc0NVQgt2vfbqFl64C'; // Ganti dengan Server Key Anda
    
    // Sandbox URLs
    const SANDBOX_SNAP_URL = 'https://app.sandbox.midtrans.com/snap/v1/transactions';
    const SANDBOX_API_URL = 'https://api.sandbox.midtrans.com/v2';
    const SANDBOX_SNAP_JS = 'https://app.sandbox.midtrans.com/snap/snap.js'; 
    
    // Production URLs
    const PRODUCTION_SNAP_URL = 'https://app.midtrans.com/snap/v1/transactions';
    const PRODUCTION_API_URL = 'https://api.midtrans.com/v2';
    const PRODUCTION_SNAP_JS = 'https://app.midtrans.com/snap/snap.js';
    
    /**
     * Get current environment URLs
     */
    public static function getSnapUrl() {
        return self::IS_PRODUCTION ? self::PRODUCTION_SNAP_URL : self::SANDBOX_SNAP_URL;
    }
    
    public static function getApiUrl() {
        return self::IS_PRODUCTION ? self::PRODUCTION_API_URL : self::SANDBOX_API_URL;
    }
    
    public static function getSnapJsUrl() {
        return self::IS_PRODUCTION ? self::PRODUCTION_SNAP_JS : self::SANDBOX_SNAP_JS;
    }
    
    /**
     * Create Snap Token for payment
     */
    public static function createSnapToken($params) {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => self::getSnapUrl(),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($params),
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode(self::SERVER_KEY . ':')
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 60
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            return [
                'success' => false,
                'message' => 'Curl Error: ' . $error
            ];
        }
        
        $result = json_decode($response, true);
        
        return [
            'success' => $httpCode === 201,
            'http_code' => $httpCode,
            'data' => $result
        ];
    }
    
    /**
     * Get transaction status from Midtrans
     */
    public static function getTransactionStatus($orderId) {
        $url = self::getApiUrl() . '/' . $orderId . '/status';
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Basic ' . base64_encode(self::SERVER_KEY . ':')
            ],
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 30
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return [
            'http_code' => $httpCode,
            'data' => json_decode($response, true)
        ];
    }
    
    /**
     * Verify notification signature
     */
    public static function verifySignature($orderId, $statusCode, $grossAmount, $signatureKey) {
        $mySignature = hash('sha512', $orderId . $statusCode . $grossAmount . self::SERVER_KEY);
        return hash_equals($mySignature, $signatureKey);
    }
    
    /**
     * Cancel transaction
     */
    public static function cancelTransaction($orderId) {
        $url = self::getApiUrl() . '/' . $orderId . '/cancel';
        
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'Authorization: Basic ' . base64_encode(self::SERVER_KEY . ':')
            ],
            CURLOPT_SSL_VERIFYPEER => false
        ]);
        
        $response = curl_exec($ch);
        curl_close($ch);
        
        return json_decode($response, true);
    }
    
    /**
     * Build transaction parameters
     */
    public static function buildTransactionParams($orderId, $amount, $user, $course, $itemDetails = []) {
        return [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => (int) $amount
            ],
            'customer_details' => [
                'first_name' => $user['name'],
                'email' => $user['email'],
                'phone' => $user['phone'] ?? ''
            ],
            'item_details' => $itemDetails ?: [
                [
                    'id' => 'COURSE-' . $course['id'],
                    'price' => (int) $amount,
                    'quantity' => 1,
                    'name' => substr($course['title'], 0, 50)
                ]
            ],
            'callbacks' => [
                'finish' => (isset($_SERVER['HTTPS']) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'] . '/pages/payment_success.php'
            ],
            'expiry' => [
                'unit' => 'hour',
                'duration' => 24
            ]
        ];
    }
}