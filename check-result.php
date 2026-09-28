<?php
session_start();
require_once __DIR__ . '/includes/automatic-results-schema.php';

$requestId = (int)($_SESSION['fc_result_request_id'] ?? 0);
if ($requestId <= 0) {
    header('Location: automatic-results.php');
    exit;
}

$st = $pdo->prepare("SELECT id, result_type, exam_year, index_number, date_of_birth, status FROM result_requests WHERE id = ? LIMIT 1");
$st->execute([$requestId]);
$request = $st->fetch();
if (!$request) {
    header('Location: automatic-results.php');
    exit;
}

$st = $pdo->prepare("SELECT name FROM result_types WHERE code = ? LIMIT 1");
$st->execute([$request['result_type']]);
$resultType = $st->fetchColumn() ?: $request['result_type'];
?>
<!doctype html>
<html lang="en">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Verify Checker - FastCheckerGH</title><link rel="stylesheet" href="css/style.css"></head>
<body>
<header class="site-header"><div class="container header-container"><a href="index.php" class="logo"><span style="color:#171923">Fast</span><span style="color:#ff6500">Checker</span><span style="color:#171923">GH</span><small style="font-size:12px;color:#626575;margin-left:2px">.com</small></a><nav class="nav-links"><a href="index.php">Home</a><a href="bece.php">BECE</a><a href="wassce.php">WASSCE</a><a href="automatic-results.php">Check Results</a><a href="retrieve.php">Retrieve Checker</a></nav><a class="header-contact" href="index.php#contact">Contact</a><button class="menu-toggle" id="menu-toggle" type="button">☰</button></div></header>
<main class="results-page"><div class="results-container">
<div class="results-header"><span class="section-label">SECURE VERIFICATION</span><h1>Verify Your Checker</h1><p><?php echo htmlspecialchars($resultType, ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($request['exam_year'], ENT_QUOTES, 'UTF-8'); ?> · <?php echo htmlspecialchars($request['index_number'], ENT_QUOTES, 'UTF-8'); ?></p></div>
<div class="result-form-card">
<form method="post" action="verify-checker.php">
<div class="form-group"><label for="serial-number">Checker Serial Number</label><input id="serial-number" name="serial_number" maxlength="191" required placeholder="Enter checker serial number"></div>
<div class="form-group"><label for="pin">Checker PIN</label><input id="pin" name="pin" maxlength="191" required placeholder="Enter checker PIN"></div>
<button class="results-button" type="submit">Verify Checker & Continue</button>
</form>
<p class="secure-note">Use a checker you purchased through FastCheckerGH or an assigned checker card.</p>
<a class="back-link" href="result-form.php?type=<?php echo urlencode($request['result_type']); ?>">← Edit Result Details</a>
</div></div></main>
<?php include __DIR__ . '/includes/footer.php'; ?><script src="js/app.js"></script>
</body></html>
