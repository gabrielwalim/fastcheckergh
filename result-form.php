<?php
session_start();
require_once __DIR__ . '/includes/automatic-results-schema.php';

$typeCode = trim((string)($_GET['type'] ?? ''));
if ($typeCode === '') {
    header('Location: automatic-results.php');
    exit;
}

$stmt = $pdo->prepare("SELECT id, name, code, description FROM result_types WHERE code = ? AND status = 'active' LIMIT 1");
$stmt->execute([$typeCode]);
$resultType = $stmt->fetch();
if (!$resultType) {
    header('Location: automatic-results.php');
    exit;
}

$productName = ($typeCode === 'bece') ? 'BECE Checker' : 'WASSCE Checker';
$stmt = $pdo->prepare("SELECT id, name, price FROM products WHERE name = ? AND status = 'active' LIMIT 1");
$stmt->execute([$productName]);
$product = $stmt->fetch();

if (!$product) {
    http_response_code(503);
    exit('The required checker product is currently unavailable. Please try again later.');
}

if (empty($_SESSION['fc_result_csrf'])) {
    $_SESSION['fc_result_csrf'] = bin2hex(random_bytes(32));
}
$csrf = $_SESSION['fc_result_csrf'];
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title><?php echo htmlspecialchars($resultType['name'], ENT_QUOTES, 'UTF-8'); ?> Automatic Checker - FastCheckerGH</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header"><div class="container header-container">
<a href="index.php" class="logo"><span style="color:#171923">Fast</span><span style="color:#ff6500">Checker</span><span style="color:#171923">GH</span><small style="font-size:12px;color:#626575;margin-left:2px">.com</small></a>
<nav class="nav-links"><a href="index.php">Home</a><a href="bece.php">BECE</a><a href="wassce.php">WASSCE</a><a href="automatic-results.php">Check Results</a><a href="retrieve.php">Retrieve Checker</a></nav>
<a class="header-contact" href="index.php#contact">Contact</a><button class="menu-toggle" id="menu-toggle" type="button">☰</button>
</div></header>
<main class="results-page"><div class="results-container">
<div class="results-header"><span class="section-label">AUTOMATIC CHECK</span><h1><?php echo htmlspecialchars($resultType['name'], ENT_QUOTES, 'UTF-8'); ?> Results</h1><p>Enter the candidate's details. FastCheckerGH will purchase one checker for this request after payment and assign it automatically.</p></div>
<div class="result-form-card">
<div class="status-card" style="margin-bottom:20px"><strong>Checker price: GH₵<?php echo number_format((float)$product['price'], 2); ?></strong><p style="margin-top:6px">One checker is included. After successful Paystack payment, the checker is assigned automatically to this result request.</p></div>
<form method="post" action="submit-result-request.php" autocomplete="off">
<input type="hidden" name="csrf" value="<?php echo htmlspecialchars($csrf, ENT_QUOTES, 'UTF-8'); ?>">
<input type="hidden" name="result_type" value="<?php echo htmlspecialchars($resultType['code'], ENT_QUOTES, 'UTF-8'); ?>">
<div class="form-group"><label>Examination Type</label><input type="text" value="<?php echo htmlspecialchars($resultType['name'], ENT_QUOTES, 'UTF-8'); ?>" readonly></div>
<div class="form-group"><label for="exam-year">Examination Year</label><input type="number" id="exam-year" name="exam_year" min="2000" max="<?php echo (int)date('Y'); ?>" placeholder="2026" required></div>
<div class="form-group"><label for="candidate-number">Candidate / Index Number</label><input type="text" id="candidate-number" name="index_number" maxlength="80" placeholder="Enter candidate/index number" required></div>
<div class="form-group"><label for="date-of-birth">Date of Birth <span style="font-weight:400;color:#888">(where applicable)</span></label><input type="date" id="date-of-birth" name="date_of_birth"></div>
<div class="form-group"><label for="phone">Mobile Money Number</label><input type="tel" id="phone" name="phone" placeholder="024 XXX XXXX" autocomplete="tel" required></div>
<div class="form-group"><label for="email">Email Address</label><input type="email" id="email" name="email" placeholder="you@example.com" autocomplete="email" required></div>
<button type="submit" class="results-button">Continue to Secure Payment</button>
</form>
<a href="automatic-results.php" class="back-link">← Change Result Type</a>
</div></div></main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="js/app.js"></script>
</body></html>
