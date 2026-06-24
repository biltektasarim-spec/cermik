<?php

class SmsService {
    private $api_id;
    private $api_key;
    private $sender_id;

    public function __construct($api_id = null, $api_key = null, $sender_id = 'FREEMIND') {
        $this->api_id = $api_id ?: '7073c30918869aee144ddca9';
        $this->api_key = $api_key ?: 'bb37df2be980e603326bce12';
        $this->sender_id = $sender_id;
    }

    /**
     * SMS Gönder (VatanSMS OTP API - 'yarisma' projesindeki calisan model)
     */
    public function sendSms($phone, $message) {
        if (!extension_loaded('curl')) {
            return ['status' => false, 'message' => 'Sistem Hatası: cURL eklentisi yüklü değil.'];
        }
        $phone = $this->formatPhone($phone);
        
        $postData = [
            "api_id" => $this->api_id,
            "api_key" => $this->api_key,
            "sender" => $this->sender_id,
            "message_type" => "normal",
            "message" => $message,
            "phones" => [$phone]
        ];

        try {
            $ch = curl_init("https://api.vatansms.net/api/v1/otp");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
            curl_setopt($ch, CURLOPT_TIMEOUT, 15);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            
            $response = curl_exec($ch);
            $err = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            // Log entry - api klasörü içine yazmaya çalış
            $logFile = $_SERVER['DOCUMENT_ROOT'] . '/sms_debug_v2.log';
            if (!is_writable($_SERVER['DOCUMENT_ROOT'])) {
                 $logFile = __DIR__ . '/sms_debug_v2.log'; 
            }
            $logData = "[" . date('Y-m-d H:i:s') . "] SMS Request (Action: " . ($_GET['action'] ?? 'CLI/Google') . "):\n";
            $logData .= "Phone: $phone\n";
            $logData .= "Sender: " . $this->sender_id . "\n";
            $logData .= "Message: $message\n";
            $logData .= "Data Type: " . gettype($postData['phones'][0]) . "\n";
            $logData .= "HTTP Code: $httpCode\n";
            if ($err) $logData .= "CURL Error: $err\n";
            $logData .= "Response Body: $response\n\n";
            @file_put_contents($logFile, $logData, FILE_APPEND);
            @error_log("[REHBER] SMS Raw Result: " . $response);

            if ($err) {
                return ['status' => false, 'message' => 'CURL Hatası: ' . $err];
            }

            $resData = json_decode($response, true);
            
            // VatanSMS Yanit Kontrolü
            if ($httpCode == 200 || $httpCode == 201) {
                return ['status' => true, 'message' => 'SMS Başarıyla Gönderildi', 'response' => $resData];
            } else {
                return ['status' => false, 'message' => 'API Hatası: ' . $httpCode, 'response' => $resData];
            }
        } catch (Throwable $e) {
            return ['status' => false, 'message' => 'Sistem Hatası: ' . $e->getMessage()];
        }
    }

    /**
     * Telefon No Formatla (905xxxxxxxxx)
     */
    private function formatPhone($phone) {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (strlen($phone) == 10 && $phone[0] == '5') {
            $phone = '90' . $phone;
        } elseif (strlen($phone) == 11 && $phone[0] == '0') {
            $phone = '9' . $phone;
        }
        return $phone;
    }
}
