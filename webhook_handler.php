<?php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/logger.php';

$secret_key = env('KHQR_SECRET_KEY');

write_log('webhook', 'INFO', 'Webhook endpoint accessed', [
    'method'       => $_SERVER['REQUEST_METHOD'],
    'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'N/A',
]);

$json_payload = file_get_contents("php://input");
$data = json_decode($json_payload, true);

write_log('webhook', 'INFO', 'Raw payload received', [
    'raw_payload'   => $json_payload,
    'parsed_data'   => $data,
    'payload_length' => strlen($json_payload),
]);

if (!$data) {
    write_log('webhook', 'ERROR', 'No data received or invalid JSON', [
        'raw_payload' => $json_payload,
    ]);
    http_response_code(400);
    exit("No data received");
}

$expected_hash = hash(
    "sha256",
    $secret_key .
    $data['req_time'] .
    $data['transaction_id'] .
    $data['amount'] .
    $data['status']
);

write_log('webhook', 'INFO', 'Hash verification attempt', [
    'transaction_id' => $data['transaction_id'],
    'amount'         => $data['amount'],
    'status'         => $data['status'],
    'received_hash'  => $data['hash'] ?? 'MISSING',
    'expected_hash'  => $expected_hash,
]);

if (!hash_equals($expected_hash, $data['hash'])) {
    write_log('webhook', 'ERROR', 'Signature mismatch — REJECTED', [
        'transaction_id' => $data['transaction_id'],
        'received_hash'  => $data['hash'],
        'expected_hash'  => $expected_hash,
    ]);
    http_response_code(403);
    exit("Signature mismatch");
}

write_log('webhook', 'SUCCESS', 'Hash verified successfully', [
    'transaction_id' => $data['transaction_id'],
]);

if ($data['status'] === 'SUCCESS') {
    $order_id = $data['transaction_id'];
    $amount = $data['amount'];

    write_log('webhook', 'SUCCESS', 'Payment SUCCESS — processing order', [
        'order_id' => $order_id,
        'amount'   => $amount,
    ]);

} else {
    write_log('webhook', 'WARNING', 'Webhook received with non-SUCCESS status', [
        'transaction_id' => $data['transaction_id'],
        'status'         => $data['status'],
        'amount'         => $data['amount'],
    ]);
}

write_log('webhook', 'INFO', 'Responding 200 OK to gateway', [
    'transaction_id' => $data['transaction_id'],
]);

http_response_code(200);
echo json_encode(["status" => "received"]);