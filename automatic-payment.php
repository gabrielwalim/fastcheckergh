<?php
session_start();
require_once __DIR__ . '/includes/automatic-results-schema.php';
require_once __DIR__ . '/config/paystack.php';

$requestId = (int)($_SESSION['fc_automatic_results_request_id'] ?? 0);
$orderId = (int)($_SESSION['fc_automatic_results_order_id'] ?? 0);
if ($requestId <= 0 || $orderId <= 0) {
    header('Location: automatic-results.php');
    exit;
}

$stmt = $pdo->prepare("SELECT rr.id, rr.order_id, rr.result_type, rr.exam_year, rr.index_number, rr.date_of_birth, rr.status, o.total_amount, o.status AS order_status, p.name AS product_name, u.email FROM result_requests rr INNER JOIN orders o ON o.id = rr.order_id INNER JOIN order_items oi ON oi.order_id = o.id INNER JOIN products p ON p.id = oi.product_id INNER JOIN users u ON u.id = o.user_id WHERE rr.id = ? AND o.id = ? LIMIT 1");
$stmt->execute([$requestId, $orderId]);
$data = $stmt->fetch();
if (!$data) {
    header('Location: automatic-results.php');
    exit;
}

if (($data['order_status'] ?? '') !== 'pending') {
    header('Location: payment-success.php?flow=automatic&request_id=' . $requestId);
    exit;
}

if ($paystackSecretKey === '') {
    http_response_code(503);
    exit('Paystack is not configured. Please set PAYSTACK_SECRET_KEY first.');
}

$reference = 'FCGH-AUTO-' . $orderId . '-' . strtoupper(bin2hex(random_bytes(5)));
$callbackUrl = $siteUrl . '/payment-success.php?flow=automatic&request_id=' . $requestId;
$amount = (int)round(((float)$data['total_amount']) * 100);

$payload = [
    'email' => $data['email'],
    'amount' => (string)$amount,
    'currency' => $paystackCurrency,
    'reference' => $reference,
    'callback_url' => $callbackUrl,
    'channels' => ['card', 'mobile_money'],
    'metadata' => [
        'order_id' => $orderId,
        'result_request_id' => $requestId,
        'source' => 'FastCheckerGH_AutomaticResults'
    ]
];

$ch = curl_init($paystackApiUrl . '/transaction/initialize');
curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $paystackSecretKey,
        'Content-Type: application/json',
        'Accept: application/json'
    ],
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_TIMEOUT => 30
]);
$response = curl_exec($ch);
if ($response === false) {
    $error = curl_error($ch);
    curl_close($ch);
    error_log('FastCheckerGH automatic Paystack cURL error: ' . $error);
    http_response_code(502);
    exit('Unable to connect to Paystack: ' . htmlspecialchars($error, ENT_QUOTES, 'UTF-8'));
}
$httpStatus = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
$paystack = json_decode($response, true);
if ($httpStatus < 200 || $httpStatus >= 300 || !is_array($paystack) || empty($paystack['status']) || empty($paystack['data']['authorization_url'])) {
    error_log('FastCheckerGH automatic Paystack initialization error: ' . $response);
    $message = is_array($paystack) ? ($paystack['message'] ?? 'Paystack could not initialize payment.') : 'Invalid response from Paystack.';
    http_response_code(502);
    exit(htmlspecialchars($message, ENT_QUOTES, 'UTF-8'));
}

try {
    $stmt = $pdo->prepare("INSERT INTO payments (order_id, payment_reference, amount, method, status) VALUES (?, ?, ?, 'paystack', 'pending')");
    $stmt->execute([$orderId, $paystack['data']['reference'], (float)$data['total_amount']]);
} catch (Throwable $e) {
    error_log('FastCheckerGH automatic payment record error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to save the payment record. Please try again.');
}

$_SESSION['fc_automatic_results_reference'] = $paystack['data']['reference'];
header('Location: ' . $paystack['data']['authorization_url']);
exit;
