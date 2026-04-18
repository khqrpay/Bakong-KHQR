<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/logger.php';

$gateway_url = env('KHQR_GATEWAY_URL');
$profile_id = env('KHQR_PROFILE_ID');
$secret_key = env('KHQR_SECRET_KEY');

$transaction_id = "ORD_" . time();
$amount = 0.01;
$success_url = env('APP_BASE_URL') . "/callback.php?transaction_id=" . $transaction_id;
$remark = "Purchase for Order " . $transaction_id;

// LOG: Page loaded
write_log('payment', 'INFO', 'Checkout page loaded', [
    'transaction_id' => $transaction_id,
    'amount' => $amount,
    'success_url' => $success_url,
    'remark' => $remark,
]);

// 3. SECURITY (SHA1 HASHING)
// Formula: sha1(secret + id + amt + url + remark)
$raw_string = $secret_key . $transaction_id . $amount . $success_url . $remark;
$hash = sha1($raw_string);

// LOG: Hash generated
write_log('payment', 'INFO', 'Payment hash generated', [
    'transaction_id' => $transaction_id,
    'hash' => $hash,
]);

// 4. BUILD REDIRECT URL
$params = [
    "transaction_id" => $transaction_id,
    "amount" => $amount,
    "success_url" => $success_url,
    "remark" => $remark,
    "hash" => $hash
];

$final_url = $gateway_url . "/" . $profile_id . "?" . http_build_query($params);

// LOG: Redirect URL built
write_log('payment', 'INFO', 'Payment redirect URL built', [
    'transaction_id' => $transaction_id,
    'redirect_url' => $final_url,
]);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - KHQRPay</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-md w-full max-w-md text-center">
        <h2 class="text-2xl font-bold mb-4">Confirm Payment</h2>
        <p class="text-gray-600 mb-6">You are about to pay <strong>$
                <?php echo number_format($amount, 2); ?>
            </strong></p>

        <a href="<?php echo $final_url; ?>"
            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-6 rounded-lg block transition">
            Pay with KHQR / Bank App
        </a>

        <p class="mt-4 text-xs text-gray-400">Securely processed by KHQRPay</p>
    </div>
</body>

</html>