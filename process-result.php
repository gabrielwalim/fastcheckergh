<?php

// Connect to database
require_once "includes/db.php";


// Make sure the form was submitted using POST
if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: automatic-results.php");
    exit;

}


// Get submitted values
$typeCode = $_POST["type"] ?? "";
$indexNumber = trim($_POST["index_number"] ?? "");
$year = $_POST["year"] ?? "";


// Check that all required information was provided
if (
    empty($typeCode) ||
    empty($indexNumber) ||
    empty($year)
) {

    die("Please complete all required fields.");

}


// Find the selected examination type
$stmt = $pdo->prepare("
    SELECT id, name, code
    FROM result_types
    WHERE code = ?
    AND status = 'active'
    LIMIT 1
");

$stmt->execute([$typeCode]);

$resultType = $stmt->fetch(PDO::FETCH_ASSOC);


// Make sure the examination type exists
if (!$resultType) {

    die("Invalid examination type.");

}


// Create the display name
if ($resultType["code"] === "bece") {

    $displayName = "🎓 BECE";

} elseif ($resultType["code"] === "wassce-school") {

    $displayName = "🏫 WASSCE School";

} elseif ($resultType["code"] === "wassce-private") {

    $displayName = "📚 WASSCE Private";

} else {

    $displayName = htmlspecialchars($resultType["name"]);

}


// Protect values before displaying them
$safeIndexNumber = htmlspecialchars($indexNumber);
$safeYear = htmlspecialchars($year);

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Confirm Result Details - FastCheckerGH</title>

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>
<header class="site-header">
  <div class="container header-container">
    <a href="index.php" class="logo"><span style="color:#171923">Fast</span><span style="color:#ff6500">Checker</span><span style="color:#171923">GH</span><small style="font-size:12px;color:#626575;margin-left:2px">.com</small></a>
    <nav class="nav-links" aria-label="Main navigation">
      <a href="index.php">Home</a>
      <a href="bece.php">BECE</a>
      <a href="wassce.php">WASSCE</a>
      <a href="automatic-results.php">Check Results</a>
      <a href="retrieve.php">Retrieve Checker</a>
    </nav>
    <a class="header-contact" href="index.php#contact">Contact</a>
    <button class="menu-toggle" id="menu-toggle" type="button" aria-label="Open menu">☰</button>
  </div>
</header>


<main class="results-page">

    <div class="results-container">

        <div class="results-header">

            <h1>
                Confirm Your Details
            </h1>

            <p>
                Please check your information before continuing.
            </p>

        </div>


        <div class="form-group">

            <label>
                Examination Type
            </label>

            <input
                type="text"
                value="<?php echo $displayName; ?>"
                readonly
            >

        </div>


        <div class="form-group">

            <label>
                Index Number
            </label>

            <input
                type="text"
                value="<?php echo $safeIndexNumber; ?>"
                readonly
            >

        </div>


        <div class="form-group">

            <label>
                Examination Year
            </label>

            <input
                type="text"
                value="<?php echo $safeYear; ?>"
                readonly
            >

        </div>


        <form
            action="automatic-results.php"
            method="GET"
        >

            <button
                type="submit"
                class="results-button"
            >
                Go Back
            </button>

        </form>


        <p style="text-align: center; margin-top: 20px;">
            Details received successfully.
        </p>

    </div>

</main>


<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="js/app.js"></script>
</body>

</html>