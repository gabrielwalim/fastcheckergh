<?php
require_once __DIR__ . '/includes/automatic-results-schema.php';

$stmt = $pdo->query("SELECT id, name, code, description FROM result_types WHERE status = 'active' ORDER BY id ASC");
$resultTypes = $stmt->fetchAll();
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Automatic Results Checker - FastCheckerGH</title>
<link rel="stylesheet" href="css/style.css">
</head>
<body>
<header class="site-header"><div class="container header-container">
<a href="index.php" class="logo"><span style="color:#171923">Fast</span><span style="color:#ff6500">Checker</span><span style="color:#171923">GH</span><small style="font-size:12px;color:#626575;margin-left:2px">.com</small></a>
<nav class="nav-links" aria-label="Main navigation"><a href="index.php">Home</a><a href="bece.php">BECE</a><a href="wassce.php">WASSCE</a><a href="automatic-results.php">Check Results</a><a href="retrieve.php">Retrieve Checker</a></nav>
<a class="header-contact" href="index.php#contact">Contact</a><button class="menu-toggle" id="menu-toggle" type="button" aria-label="Open menu">☰</button>
</div></header>
<main class="results-page"><div class="results-container">
<div class="results-header"><span class="section-label">RESULTS</span><h1>Automatic Results Checker</h1><p>Select your examination type and enter your candidate details to continue.</p></div>
<div class="result-selection-card">
<form method="get" action="result-form.php">
<label for="result-type">Select Result Type</label>
<select id="result-type" name="type" required>
<option value="">Select an examination type</option>
<?php foreach ($resultTypes as $type): ?>
<option value="<?php echo htmlspecialchars($type['code'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars($type['name'], ENT_QUOTES, 'UTF-8'); ?></option>
<?php endforeach; ?>
</select>
<button class="results-button" type="submit">Continue</button>
</form>
<div class="secure-note" style="margin-top:18px">FastCheckerGH verifies your checker card, then connects you to the official WAEC results service. Live automatic retrieval requires an authorized WAEC API integration.</div>
</div>
</div></main>
<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="js/app.js"></script>
</body></html>
