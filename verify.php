<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/logger.php';

function verifyTransaction($transaction_id)
{
    $profile_id = env('KHQR_PROFILE_ID');
    $profile_key = env('KHQR_SECRET_KEY');
    $verify_url = "https://khqr.cc/api/$profile_id/payment-gateway/v1/payments/check-trans";

    $hash = sha1($profile_key . $transaction_id);


    write_log('verify', 'INFO', 'Transaction verification started', [
        'transaction_id' => $transaction_id,
        'verify_url'     => $verify_url,
        'hash'           => $hash,
    ]);

    $ch = curl_init($verify_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, [
        'transaction_id' => $transaction_id,
        'hash' => $hash
    ]);

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);


    if ($curl_error) {
        write_log('verify', 'ERROR', 'cURL error during verification', [
            'transaction_id' => $transaction_id,
            'curl_error'     => $curl_error,
        ]);
    } else {
        write_log('verify', 'INFO', 'Verification API response received', [
            'transaction_id' => $transaction_id,
            'http_code'      => $http_code,
            'response'       => json_decode($response, true),
        ]);
    }

    $result = json_decode($response, true);


    $status = $result['data']['status'] ?? 'unknown';
    write_log('verify', $status === 'success' ? 'SUCCESS' : 'WARNING', 'Verification result', [
        'transaction_id' => $transaction_id,
        'status'         => $status,
    ]);

    return $result;
}
