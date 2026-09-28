<?php
session_start();
require_once __DIR__ . '/includes/automatic-results-schema.php';
require_once __DIR__ . '/includes/results-provider.php';

$requestId = (int)($_SESSION['fc_result_request_id'] ?? 0);
if ($requestId <= 0 || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: automatic-results.php');
    exit;
}

$serial = trim((string)($_POST['serial_number'] ?? ''));
$pin = trim((string)($_POST['pin'] ?? ''));

$st = $pdo->prepare("SELECT id, result_type, exam_year, index_number, date_of_birth, status FROM result_requests WHERE id = ? LIMIT 1");
$st->execute([$requestId]);
$request = $st->fetch();
if (!$request) { header('Location: automatic-results.php'); exit; }

$errors = [];
if ($serial === '' || $pin === '') $errors[] = 'Both serial number and PIN are required.';

$voucher = null;
if (!$errors) {
    $st = $pdo->prepare("SELECT v.id, v.serial_number, v.pin, v.product_id, v.order_id, v.status, p.name AS product_name, o.status AS order_status FROM vouchers v INNER JOIN products p ON p.id = v.product_id LEFT JOIN orders o ON o.id = v.order_id WHERE v.serial_number = ? AND v.pin = ? LIMIT 1");
    $st->execute([$serial, $pin]);
    $voucher = $st->fetch();
    if (!$voucher) {
        $errors[] = 'The checker serial number or PIN could not be verified.';
    } else {
        $name = strtolower((string)$voucher['product_name']);
        $type = (string)$request['result_type'];
        $matches = ($type === 'bece' && strpos($name, 'bece') !== false) || ($type !== 'bece' && strpos($name, 'wassce') !== false);
        if (!$matches) $errors[] = 'This checker card does not match the selected examination type.';
        if (($voucher['status'] ?? '') !== 'assigned' || !in_array(($voucher['order_status'] ?? ''), ['paid','completed'], true)) $errors[] = 'This checker is not attached to a completed FastCheckerGH purchase.';
    }
}

if ($errors) {
    http_response_code(422);
    $msg = htmlspecialchars(implode(' ', $errors), ENT_QUOTES, 'UTF-8');
    echo '<!doctype html><html><head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Checker Verification</title><link rel="stylesheet" href="css/style.css"></head><body><main class="results-page"><div class="results-container"><div class="results-header"><h1>Checker verification failed</h1><p>' . $msg . '</p></div><a class="results-button" href="check-result.php" style="display:block;text-align:center">Try Again</a></div></main></body></html>';
    exit;
}

$providerResult = null;
$providerMode = fc_result_provider_mode();
if ($providerMode === 'waec_api') {
    $providerResult = fc_submit_to_waec_api([
        'request_id' => $requestId,
        'exam_type' => $request['result_type'],
        'exam_year' => $request['exam_year'],
        'index_number' => $request['index_number'],
        'date_of_birth' => $request['date_of_birth'],
        'serial_number' => $voucher['serial_number'],
        'pin' => $voucher['pin'],
    ]);
}

if ($providerMode === 'waec_api' && is_array($providerResult) && !empty($providerResult['success'])) {
    $st = $pdo->prepare("UPDATE result_requests SET status='completed', provider='waec_api', voucher_id=?, provider_reference=?, result_payload=?, error_message=NULL, processed_at=NOW() WHERE id=?");
    $st->execute([$voucher['id'], $providerResult['reference'] ?? null, isset($providerResult['data']) ? json_encode($providerResult['data']) : null, $requestId]);
} else {
    $message = $providerMode === 'waec_api' && is_array($providerResult) ? ($providerResult['message'] ?? 'WAEC API not available.') : 'FastCheckerGH is using the official WAEC eResults portal.';
    $st = $pdo->prepare("UPDATE result_requests SET status='provider_unavailable', provider='official_portal', voucher_id=?, error_message=?, processed_at=NOW() WHERE id=?");
    $st->execute([$voucher['id'], $message, $requestId]);
}

$_SESSION['fc_verified_voucher_id'] = (int)$voucher['id'];
$_SESSION['fc_verified_voucher_serial'] = $voucher['serial_number'];
$_SESSION['fc_verified_voucher_pin'] = $voucher['pin'];
$_SESSION['fc_result_provider_mode'] = $providerMode;

$officialUrl = fc_official_results_url();
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Continue to Results - FastCheckerGH</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<header class="site-header"><div class="container header-container"><a href="index.php" class="logo"><span style="color:#171923">Fast</span><span style="color:#ff6500">Checker</span><span style="color:#171923">GH</span><small style="font-size:12px;color:#626575;margin-left:2px">.com</small></a><nav class="nav-links"><a href="index.php">Home</a><a href="bece.php">BECE</a><a href="wassce.php">WASSCE</a><a href="automatic-results.php">Check Results</a><a href="retrieve.php">Retrieve Checker</a></nav><a class="header-contact" href="index.php#contact">Contact</a><button class="menu-toggle" id="menu-toggle" type="button">☰</button></div></header>
<main class="results-page"><div class="results-container">
<div class="results-header"><span class="section-label">CHECKER VERIFIED</span><h1>Ready to Check Your Result</h1><p>Your FastCheckerGH checker has been verified.</p></div>
<div class="result-form-card">
<div class="checker-main"><div class="checker-row"><span class="checker-label">Examination</span><strong><?php echo htmlspecialchars($request['result_type'], ENT_QUOTES, 'UTF-8'); ?></strong></div><div class="checker-row"><span class="checker-label">Year</span><strong><?php echo htmlspecialchars($request['exam_year'], ENT_QUOTES, 'UTF-8'); ?></strong></div><div class="checker-row"><span class="checker-label">Index Number</span><strong><?php echo htmlspecialchars($request['index_number'], ENT_QUOTES, 'UTF-8'); ?></strong></div><div class="checker-row"><span class="checker-label">Serial Number</span><strong><?php echo htmlspecialchars($voucher['serial_number'], ENT_QUOTES, 'UTF-8'); ?></strong></div></div>
<div class="status-card" style="margin-top:18px"><strong>Official WAEC portal</strong><p style="margin-top:6px">The public WAEC eResults service currently requires the candidate details plus the checker serial number and PIN. FastCheckerGH does not invent or scrape an unofficial results API.</p></div>
<a class="results-button" href="<?php echo htmlspecialchars($officialUrl, ENT_QUOTES, 'UTF-8'); ?>" target="_blank" rel="noopener noreferrer" style="display:block;text-align:center;margin-top:18px">Open Official WAEC Results Portal</a>
<a class="back-link" href="my-checkers.php">View My Checkers</a>
</div></div></main>
<?php include __DIR__ . '/includes/footer.php'; ?><script src="js/app.js"></script>
</body></html>
