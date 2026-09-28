<?php
session_start();

/*
|--------------------------------------------------------------------------
| FASTCHECKERGH - PAYMENT SUCCESS
|--------------------------------------------------------------------------
| Displays a clean checker voucher and provides a print/save-as-PDF option.
|--------------------------------------------------------------------------
*/

require_once "includes/db.php";


/*
|--------------------------------------------------------------------------
| Get Paystack reference
|--------------------------------------------------------------------------
*/

$reference = isset($_GET["reference"])
    ? trim($_GET["reference"])
    : "";

$automaticFlow = (($_GET["flow"] ?? "") === "automatic") || !empty($_SESSION["fc_automatic_results_flow"]);
$automaticRequestId = (int)($_GET["request_id"] ?? ($_SESSION["fc_automatic_results_request_id"] ?? 0));
if ($reference === "" && !empty($_SESSION["fc_automatic_results_reference"])) {
    $reference = trim((string)$_SESSION["fc_automatic_results_reference"]);
}


/*
|--------------------------------------------------------------------------
| Default values
|--------------------------------------------------------------------------
*/

$success = false;
$needsVoucher = false;

$message = "Unable to verify payment.";

$vouchers = [];

$orderId = "";

$productName = "";

$customerPhone = "";

$purchaseDate = "";

$resultUrl = "https://eresults.waecgh.org/";


/*
|--------------------------------------------------------------------------
| Verify payment
|--------------------------------------------------------------------------
*/

if ($reference !== "") {

    $scheme = (!empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off") ? "https" : "http";
    $host = $_SERVER["HTTP_HOST"] ?? "localhost";
    $endpoint = $automaticFlow ? "/fastcheckergh/api/verify-automatic-payment.php" : "/fastcheckergh/api/verify-payment.php";
    $apiUrl = $scheme . "://" . $host . $endpoint;

    $ch = curl_init($apiUrl);

    curl_setopt_array($ch, [

        CURLOPT_RETURNTRANSFER => true,

        CURLOPT_POST => true,

        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json"
        ],

        CURLOPT_POSTFIELDS => json_encode(array_filter([
            "reference" => $reference,
            "request_id" => ($automaticFlow && $automaticRequestId > 0) ? $automaticRequestId : null
        ], static function ($value) { return $value !== null; })),

        CURLOPT_TIMEOUT => 30

    ]);


    $response = curl_exec($ch);


    if ($response !== false) {

        $result = json_decode(
            $response,
            true
        );

    } else {

        $result = null;

    }


    curl_close($ch);


    /*
    |--------------------------------------------------------------------------
    | Check verification result
    |--------------------------------------------------------------------------
    */

    if (
        is_array($result) &&
        isset($result["success"]) &&
        $result["success"] === true
    ) {

        $success = true;

        $message =
            $result["message"] ?? "Payment successful.";


        /*
        |--------------------------------------------------------------------------
        | Get vouchers
        |--------------------------------------------------------------------------
        */

        if (
            isset($result["vouchers"]) &&
            is_array($result["vouchers"])
        ) {

            $vouchers =
                $result["vouchers"];

        }

        if (isset($result["voucher"]) && is_array($result["voucher"])) {
            $vouchers = [$result["voucher"]];
        }


        /*
        |--------------------------------------------------------------------------
        | Get order ID
        |--------------------------------------------------------------------------
        */

        if (
            isset($result["order_id"])
        ) {

            $orderId =
                (int)$result["order_id"];

        }

    } else {

        if ($automaticFlow && is_array($result) && !empty($result["payment_verified"]) && !empty($result["needs_voucher"])) {
            $needsVoucher = true;
            $message = $result["message"] ?? "Payment was verified, but no checker is currently available.";
        } elseif (
            is_array($result) &&
            isset($result["message"])
        ) {

            $message =
                $result["message"];

        }

    }

}


/*
|--------------------------------------------------------------------------
| Get order/customer information from database
|--------------------------------------------------------------------------
*/

if ($success && $orderId > 0) {

    try {

        $stmt = $pdo->prepare("

            SELECT

                o.id AS order_id,

                o.created_at AS purchase_date,

                u.phone AS customer_phone,

                p.name AS product_name

            FROM orders o

            INNER JOIN users u
                ON u.id = o.user_id

            INNER JOIN order_items oi
                ON oi.order_id = o.id

            INNER JOIN products p
                ON p.id = oi.product_id

            WHERE o.id = ?

            LIMIT 1

        ");


        $stmt->execute([
            $orderId
        ]);


        $orderInfo =
            $stmt->fetch();


        if ($orderInfo) {

            $customerPhone =
                $orderInfo["customer_phone"] ?? "";

            $productName =
                $orderInfo["product_name"] ?? "";

            $purchaseDate =
                $orderInfo["purchase_date"] ?? "";

        }


    } catch (PDOException $e) {

        error_log(
            "FastCheckerGH payment-success database error: " .
            $e->getMessage()
        );

    }

}


/*
|--------------------------------------------------------------------------
| Automatic results purchase flow
|--------------------------------------------------------------------------
| After a successful Paystack payment, the normal verification endpoint
| assigns the checker. For an automatic-results request we link that
| assigned voucher to the candidate request and continue automatically.
*/

$automaticFlow = (($_GET["flow"] ?? "") === "automatic") || !empty($_SESSION["fc_automatic_results_flow"]);
$automaticRequestId = (int)($_GET["request_id"] ?? ($_SESSION["fc_automatic_results_request_id"] ?? 0));

if ($success && $automaticFlow && $automaticRequestId > 0 && $orderId > 0) {
    $_SESSION["fc_automatic_results_request_id"] = $automaticRequestId;
    $_SESSION["fc_automatic_results_order_id"] = $orderId;
    $_SESSION["fc_result_request_id"] = $automaticRequestId;
    unset($_SESSION["fc_automatic_results_flow"], $_SESSION["fc_automatic_results_reference"]);
    header("Location: automatic-result-after-payment.php?request_id=" . $automaticRequestId);
    exit;
}

/*
|--------------------------------------------------------------------------
| Determine official results website
|--------------------------------------------------------------------------
*/

$productLower =
    strtolower($productName);


if (
    strpos($productLower, "wassce") !== false
) {

    $resultUrl =
        "https://ghana.waecdirect.org/";

} else {

    $resultUrl =
        "https://eresults.waecgh.org/";

}


/*
|--------------------------------------------------------------------------
| Format product name
|--------------------------------------------------------------------------
*/

$displayProduct =
    strtoupper($productName);


if ($displayProduct === "") {

    $displayProduct =
        "RESULT CHECKER";

}


/*
|--------------------------------------------------------------------------
| Format purchase date
|--------------------------------------------------------------------------
*/

$formattedDate = "";

if ($purchaseDate !== "") {

    $timestamp =
        strtotime($purchaseDate);

    if ($timestamp !== false) {

        $formattedDate =
            date(
                "Y-m-d H:i:s",
                $timestamp
            );

    } else {

        $formattedDate =
            $purchaseDate;

    }

}


/*
|--------------------------------------------------------------------------
| HTML
|--------------------------------------------------------------------------
*/

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>
        Checker Voucher - FastCheckerGH
    </title>


    <link
        rel="stylesheet"
        href="css/style.css"
    >


    <style>

        /*
        |--------------------------------------------------------------------------
        | CHECKER RECEIPT
        |--------------------------------------------------------------------------
        */

        .checker-receipt-wrapper {

            min-height: 100vh;

            display: flex;

            align-items: center;

            justify-content: center;

            padding: 40px 20px;

            background: #f5f5f5;

        }


        .checker-receipt {

            width: 100%;

            max-width: 600px;

            background: #ffffff;

            border: 1px solid #dddddd;

            padding: 40px;

            box-sizing: border-box;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

        }


        .receipt-brand {

            text-align: center;

            margin-bottom: 25px;

        }


        .receipt-brand h1 {

            margin: 0;

            font-size: 24px;

            font-weight: 800;

            color: #111111;

        }


        .receipt-brand h1 span {

            color: #f97316;

        }


        .receipt-brand p {

            margin: 6px 0 0;

            font-size: 12px;

            color: #777777;

        }


        .checker-title {

            text-align: center;

            font-size: 20px;

            font-weight: 800;

            margin: 20px 0;

            text-transform: uppercase;

        }


        .divider {

            border-top: 1px dashed #333333;

            margin: 18px 0;

        }


        .checker-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 8px 0;

        }


        .checker-label {

            font-weight: 700;

            color: #333333;

        }


        .checker-value {

            font-weight: 700;

            text-align: right;

            word-break: break-word;

        }


        .checker-main {

            margin: 20px 0;

        }


        .checker-main-row {

            display: flex;

            justify-content: space-between;

            gap: 20px;

            padding: 12px 0;

        }


        .checker-main-label {

            font-size: 15px;

            font-weight: 700;

        }


        .checker-main-value {

            font-size: 18px;

            font-weight: 800;

            letter-spacing: 1px;

            text-align: right;

            word-break: break-all;

        }


        .official-link {

            text-align: center;

            margin: 20px 0;

        }


        .official-link p {

            margin: 5px 0;

            font-size: 13px;

        }


        .official-link a {

            color: #111111;

            font-weight: 700;

            text-decoration: none;

            word-break: break-all;

        }


        .receipt-footer {

            text-align: center;

            margin-top: 20px;

        }


        .receipt-footer p {

            margin: 5px 0;

            font-size: 12px;

            color: #555555;

        }


        .receipt-actions {

            display: flex;

            justify-content: center;

            gap: 12px;

            margin-top: 25px;

        }


        .receipt-print-button,

        .receipt-home-button {

            border: none;

            padding: 12px 20px;

            border-radius: 6px;

            font-weight: 700;

            cursor: pointer;

            text-decoration: none;

            display: inline-block;

        }


        .receipt-print-button {

            background: #111111;

            color: #ffffff;

        }


        .receipt-home-button {

            background: #f97316;

            color: #ffffff;

        }


        .receipt-download-button {

            background: #ff6500;

            color: #ffffff;

            border: none;

            padding: 12px 20px;

            border-radius: 6px;

            font-weight: 700;

            text-decoration: none;

            display: inline-block;

        }


        /*
        |--------------------------------------------------------------------------
        | ERROR
        |--------------------------------------------------------------------------
        */

        .checker-error {

            max-width: 600px;

            margin: 80px auto;

            background: #ffffff;

            padding: 40px;

            text-align: center;

            border: 1px solid #dddddd;

        }


        /*
        |--------------------------------------------------------------------------
        | PRINT VERSION
        |--------------------------------------------------------------------------
        */

        @media print {

            @page {

                size: A4;

                margin: 15mm;

            }


            html,

            body {

                margin: 0 !important;

                padding: 0 !important;

                background: #ffffff !important;

            }


            body * {

                visibility: hidden;

            }


            .checker-receipt,

            .checker-receipt * {

                visibility: visible;

            }


            .checker-receipt-wrapper {

                display: block;

                min-height: auto;

                padding: 0;

                margin: 0;

                background: #ffffff;

            }


            .checker-receipt {

                width: 100%;

                max-width: 600px;

                margin: 0 auto;

                padding: 25px;

                border: none;

                box-shadow: none;

            }


            .receipt-actions {

                display: none !important;

            }


            .no-print {

                display: none !important;

            }

        }


        /*
        |--------------------------------------------------------------------------
        | MOBILE
        |--------------------------------------------------------------------------
        */

        @media (max-width: 600px) {

            .checker-receipt-wrapper {

                padding: 15px;

            }


            .checker-receipt {

                padding: 25px 18px;

            }


            .checker-row,

            .checker-main-row {

                flex-direction: column;

                gap: 5px;

            }


            .checker-value,

            .checker-main-value {

                text-align: left;

            }


            .receipt-actions {

                flex-direction: column;

            }


            .receipt-print-button,

            .receipt-download-button,

            .receipt-home-button {

                width: 100%;

                text-align: center;

            }

        }

    </style>

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


<?php if ($success && !empty($vouchers)): ?>


    <!-- =====================================================
         CLEAN CHECKER RECEIPT
         ===================================================== -->

    <div class="checker-receipt-wrapper">


        <div class="checker-receipt">


            <!-- BRAND -->

            <div class="receipt-brand">

                <h1>
                    Fast<span>Checker</span>GH
                </h1>

                <p>
                    Secure Online Result Checker
                </p>

            </div>


            <!-- CHECKER TITLE -->

            <div class="checker-title">

                <?php
                echo htmlspecialchars(
                    $displayProduct
                );
                ?>

            </div>


            <div class="divider"></div>


            <!-- VOUCHERS -->

            <?php foreach (
                $vouchers as $index => $voucher
            ): ?>


                <?php if ($index > 0): ?>

                    <div class="divider"></div>

                <?php endif; ?>


                <?php if (count($vouchers) > 1): ?>

                    <div class="checker-row">

                        <span class="checker-label">

                            Checker <?php echo $index + 1; ?>

                        </span>

                    </div>

                <?php endif; ?>


                <!-- SERIAL -->

                <div class="checker-main">

                    <div class="checker-main-row">

                        <span class="checker-main-label">
                            Serial:
                        </span>

                        <span class="checker-main-value">

                            <?php

                            echo htmlspecialchars(
                                $voucher["serial_number"]
                            );

                            ?>

                        </span>

                    </div>


                    <!-- PIN -->

                    <div class="checker-main-row">

                        <span class="checker-main-label">
                            PIN:
                        </span>

                        <span class="checker-main-value">

                            <?php

                            echo htmlspecialchars(
                                $voucher["pin"]
                            );

                            ?>

                        </span>

                    </div>

                </div>


            <?php endforeach; ?>


            <div class="divider"></div>


            <!-- CUSTOMER -->

            <div class="checker-row">

                <span class="checker-label">

                    Purchased by:

                </span>

                <span class="checker-value">

                    <?php

                    echo htmlspecialchars(
                        $customerPhone
                    );

                    ?>

                </span>

            </div>


            <!-- DATE -->

            <div class="checker-row">

                <span class="checker-label">

                    Date:

                </span>

                <span class="checker-value">

                    <?php

                    echo htmlspecialchars(
                        $formattedDate
                    );

                    ?>

                </span>

            </div>


            <div class="divider"></div>


            <!-- OFFICIAL WEBSITE -->

            <div class="official-link">

                <p>
                    Log on to:
                </p>

                <a
                    href="<?php echo htmlspecialchars($resultUrl); ?>"
                >

                    <?php

                    echo htmlspecialchars(
                        $resultUrl
                    );

                    ?>

                </a>

            </div>


            <div class="divider"></div>


            <!-- BUY MORE -->

            <div class="official-link">

                <p>
                    Buy More from:
                </p>

                <a href="https://fastcheckergh.com">

                    https://fastcheckergh.com

                </a>

            </div>


            <!-- FOOTER -->

            <div class="receipt-footer">

                <p>
                    Thank you for using FastCheckerGH.
                </p>

            </div>


        </div>


        <!-- ACTION BUTTONS -->

        <div class="receipt-actions no-print">

            <button
                type="button"
                class="receipt-print-button"
                onclick="window.print()"
            >

                Print Receipt

            </button>

            <a
                href="receipt-pdf.php?reference=<?php echo urlencode($reference); ?>"
                class="receipt-download-button"
                target="_blank"
                rel="noopener"
            >

                Download PDF

            </a>


            <a
                href="index.php"
                class="receipt-home-button"
            >

                Back to Home

            </a>

        </div>


    </div>


<?php else: ?>


    <!-- =====================================================
         PAYMENT ERROR
         ===================================================== -->

    <div class="checker-error">

        <h2>
            Payment Not Verified
        </h2>

        <p>

            <?php

            echo htmlspecialchars(
                $message
            );

            ?>

        </p>


        <?php if ($reference !== ""): ?>

            <p>

                Reference:

                <strong>

                    <?php

                    echo htmlspecialchars(
                        $reference
                    );

                    ?>

                </strong>

            </p>

        <?php endif; ?>


        <?php if ($needsVoucher && $automaticRequestId > 0 && $reference !== ""): ?>
            <a
                href="payment-success.php?flow=automatic&amp;request_id=<?php echo (int)$automaticRequestId; ?>&amp;reference=<?php echo urlencode($reference); ?>"
                class="receipt-home-button"
                style="display:inline-block;margin-right:8px"
            >
                Retry Checker Assignment
            </a>
        <?php endif; ?>

        <a
            href="index.php"
            class="receipt-home-button"
        >

            Return to Home

        </a>

    </div>


<?php endif; ?>


<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="js/app.js"></script>
</body>

</html>