<?php
session_start();
require_once __DIR__ . '/includes/automatic-results-schema.php';
require_once __DIR__ . '/includes/phone.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: automatic-results.php');
    exit;
}

$csrf = (string)($_POST['csrf'] ?? '');
if (empty($_SESSION['fc_result_csrf']) || !hash_equals($_SESSION['fc_result_csrf'], $csrf)) {
    http_response_code(419);
    exit('Invalid security token. Please go back and try again.');
}

$resultType = trim((string)($_POST['result_type'] ?? ''));
$examYear = trim((string)($_POST['exam_year'] ?? ''));
$indexNumber = trim((string)($_POST['index_number'] ?? ''));
$dateOfBirth = trim((string)($_POST['date_of_birth'] ?? ''));
$phoneInput = trim((string)($_POST['phone'] ?? ''));
$email = trim((string)($_POST['email'] ?? ''));

$stmt = $pdo->prepare("SELECT id, name, code FROM result_types WHERE code = ? AND status = 'active' LIMIT 1");
$stmt->execute([$resultType]);
$exam = $stmt->fetch();

$errors = [];
$currentYear = (int)date('Y');
$phone = fc_canonical_phone($phoneInput);
if (!$exam) $errors[] = 'Invalid examination type.';
if (!preg_match('/^\d{4}$/', $examYear) || (int)$examYear < 2000 || (int)$examYear > $currentYear) $errors[] = 'Enter a valid examination year.';
if ($indexNumber === '' || !preg_match('/^[A-Za-z0-9\-\/ ]{3,80}$/', $indexNumber)) $errors[] = 'Enter a valid candidate/index number.';
if ($phone === '') $errors[] = 'Enter a valid Ghana mobile number.';
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email address.';

$dob = null;
if ($dateOfBirth !== '') {
    $d = DateTime::createFromFormat('Y-m-d', $dateOfBirth);
    if (!$d || $d->format('Y-m-d') !== $dateOfBirth) $errors[] = 'Enter a valid date of birth.';
    else $dob = $dateOfBirth;
}

if ($errors) {
    http_response_code(422);
    $msg = htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Result Request Error</title><link rel="stylesheet" href="css/style.css"></head><body><main class="results-page"><div class="results-container"><div class="results-header"><h1>Check the details</h1><p>' . $msg . '</p></div><a class="results-button" href="result-form.php?type=' . urlencode($resultType) . '" style="display:block;text-align:center">Go Back</a></div></main></body></html>';
    exit;
}

$productName = ($resultType === 'bece') ? 'BECE Checker' : 'WASSCE Checker';
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE name = ? AND status = 'active' LIMIT 1");
$stmt->execute([$productName]);
$product = $stmt->fetch();
if (!$product) {
    http_response_code(503);
    exit('The required checker product is currently unavailable.');
}

// Do not send a customer to Paystack when there is no checker in stock.
$stockStmt = $pdo->prepare("SELECT COUNT(*) FROM vouchers WHERE product_id = ? AND status = 'available' AND order_id IS NULL");
$stockStmt->execute([(int)$product['id']]);
$availableStock = (int)$stockStmt->fetchColumn();
if ($availableStock < 1) {
    http_response_code(409);
    echo '<!doctype html><html lang="en"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Checker Temporarily Unavailable</title><link rel="stylesheet" href="css/style.css"></head><body><main class="results-page"><div class="results-container"><div class="results-header"><span class="section-label">CHECKER INVENTORY</span><h1>Checker Temporarily Unavailable</h1><p>We do not currently have an available ' . htmlspecialchars($productName, ENT_QUOTES, 'UTF-8') . '. Please try again later.</p></div><a class="results-button" href="automatic-results.php" style="display:block;text-align:center">Back to Automatic Results</a></div></main></body></html>';
    exit;
}

try {
    $pdo->beginTransaction();

    $stmt = $pdo->prepare("SELECT id FROM users WHERE phone = ? LIMIT 1");
    $stmt->execute([$phone]);
    $user = $stmt->fetch();

    if ($user) {
        $userId = (int)$user['id'];
        $stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
        $stmt->execute([$email, $userId]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (phone, email) VALUES (?, ?)");
        $stmt->execute([$phone, $email]);
        $userId = (int)$pdo->lastInsertId();
    }

    $unitPrice = (float)$product['price'];
    $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, status) VALUES (?, ?, 'pending')");
    $stmt->execute([$userId, $unitPrice]);
    $orderId = (int)$pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, unit_price, subtotal) VALUES (?, ?, 1, ?, ?)");
    $stmt->execute([$orderId, (int)$product['id'], $unitPrice, $unitPrice]);

    if (fc_column_exists($pdo, 'result_requests', 'result_type_id')) {
        // Some earlier FastCheckerGH databases created a foreign key from
        // result_requests.result_type_id to result_types.id. Use the real
        // result-type ID so MySQL's foreign-key constraint is satisfied.
        $stmt = $pdo->prepare("INSERT INTO result_requests (user_id, order_id, result_type_id, result_type, exam_year, index_number, date_of_birth, status, provider) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', 'automatic_purchase')");
        $stmt->execute([$userId, $orderId, (int)$exam['id'], $resultType, $examYear, $indexNumber, $dob]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO result_requests (user_id, order_id, result_type, exam_year, index_number, date_of_birth, status, provider) VALUES (?, ?, ?, ?, ?, ?, 'pending', 'automatic_purchase')");
        $stmt->execute([$userId, $orderId, $resultType, $examYear, $indexNumber, $dob]);
    }
    $requestId = (int)$pdo->lastInsertId();

    $pdo->commit();

    $_SESSION['fc_automatic_results_request_id'] = $requestId;
    $_SESSION['fc_automatic_results_order_id'] = $orderId;
    $_SESSION['fc_automatic_results_flow'] = true;
    $_SESSION['fc_result_request_id'] = $requestId;

    header('Location: automatic-payment.php');
    exit;
} catch (Throwable $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('FastCheckerGH automatic result order error: ' . $e->getMessage());
    http_response_code(500);
    $host = strtolower((string)($_SERVER['HTTP_HOST'] ?? ''));
    $env = strtolower((string)(getenv('FASTCHECKERGH_ENV') ?: ''));
    $isLocal = ($env === 'local' || $env === 'development' || strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false);
    if ($isLocal) {
        echo '<h2>Automatic result order error</h2><pre style="white-space:pre-wrap">' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</pre>';
    } else {
        echo 'We could not create the automatic result order. Please try again.';
    }
    exit;
}
