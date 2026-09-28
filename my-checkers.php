<?php

require_once "includes/db.php";
require_once "includes/phone.php";


/*
|--------------------------------------------------------------------------
| START SESSION
|--------------------------------------------------------------------------
*/

session_start();


/*
|--------------------------------------------------------------------------
| CHECK RETRIEVAL AUTHENTICATION
|--------------------------------------------------------------------------
*/

if (
    empty($_SESSION["checker_retrieval_verified"]) ||
    $_SESSION["checker_retrieval_verified"] !== true
) {

    header("Location: retrieve.php");

    exit;
}

// Retrieval sessions expire after 20 minutes.
if (
    empty($_SESSION["checker_retrieval_expires"]) ||
    time() > (int)$_SESSION["checker_retrieval_expires"]
) {

    unset(
        $_SESSION["checker_retrieval_verified"],
        $_SESSION["checker_retrieval_user_id"],
        $_SESSION["checker_retrieval_phone"],
        $_SESSION["checker_retrieval_time"],
        $_SESSION["checker_retrieval_expires"]
    );

    header("Location: retrieve.php?expired=1");

    exit;
}


/*
|--------------------------------------------------------------------------
| GET VERIFIED USER ID
|--------------------------------------------------------------------------
*/

$userId = isset(
    $_SESSION["checker_retrieval_user_id"]
)
    ? (int) $_SESSION["checker_retrieval_user_id"]
    : 0;


if ($userId <= 0) {

    session_unset();

    session_destroy();

    header("Location: retrieve.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| GET USER
|--------------------------------------------------------------------------
*/

$stmt = $pdo->prepare("
    SELECT
        id,
        phone
    FROM users
    WHERE id = ?
    LIMIT 1
");

$stmt->execute([
    $userId
]);

$user = $stmt->fetch();


if (!$user) {

    session_unset();

    session_destroy();

    header("Location: retrieve.php");

    exit;
}


/*
|--------------------------------------------------------------------------
| GET PAID/COMPLETED ORDERS
|--------------------------------------------------------------------------
|
| Some older purchases may belong to a duplicate users row created with a
| different Ghana phone format (055..., 233..., +233...). The retrieval OTP
| is verified against the same mobile number, so include every users row that
| matches that verified number as well as the selected user id.
|
*/
$verifiedPhone = (string) ($_SESSION["checker_retrieval_phone"] ?? $user["phone"]);
$phoneVariants = fc_phone_variants($verifiedPhone);
$digitVariants = [];
foreach ($phoneVariants as $variant) {
    $digits = preg_replace('/\D+/', '', $variant);
    if ($digits !== '') {
        $digitVariants[] = $digits;
    }
}
$digitVariants = array_values(array_unique($digitVariants));

$phoneConditions = [];
$phoneParams = [];
foreach ($digitVariants as $digits) {
    $phoneConditions[] = "REPLACE(REPLACE(REPLACE(REPLACE(u.phone,'+',''),' ',''),'-',''),'(', '') = ?";
    $phoneParams[] = $digits;
}
$last9 = substr(preg_replace('/\D+/', '', fc_canonical_phone($verifiedPhone)), -9);
if ($last9 !== '') {
    $phoneConditions[] = "RIGHT(REPLACE(REPLACE(REPLACE(REPLACE(u.phone,'+',''),' ',''),'-',''),'(', ''), 9) = ?";
    $phoneParams[] = $last9;
}

$userMatchSql = "(u.id = ?";
$userMatchParams = [$userId];
if ($phoneConditions) {
    $userMatchSql .= " OR " . implode(' OR ', $phoneConditions);
    $userMatchParams = array_merge($userMatchParams, $phoneParams);
}
$userMatchSql .= ")";

$stmt = $pdo->prepare("
    SELECT
        o.id AS order_id,
        o.total_amount,
        o.status AS order_status,
        o.created_at,

        oi.product_id,
        oi.quantity,
        oi.unit_price,
        oi.subtotal,

        p.name AS product_name,

        v.id AS voucher_id,
        v.serial_number,
        v.pin,
        v.status AS voucher_status

    FROM orders o

    INNER JOIN users u
        ON u.id = o.user_id

    INNER JOIN order_items oi
        ON oi.order_id = o.id

    INNER JOIN products p
        ON p.id = oi.product_id

    LEFT JOIN vouchers v
        ON v.order_id = o.id
        AND v.product_id = oi.product_id
        AND v.status = 'assigned'

    WHERE " . $userMatchSql . "
    AND o.status IN ('paid', 'completed')

    ORDER BY o.created_at DESC, v.id ASC
");

$stmt->execute($userMatchParams);
$rows = $stmt->fetchAll();

/*
|--------------------------------------------------------------------------
| GROUP ORDERS
|--------------------------------------------------------------------------
*/

$orders = [];


foreach ($rows as $row) {

    $orderId =
        (int) $row["order_id"];


    if (!isset($orders[$orderId])) {

        $orders[$orderId] = [

            "order_id" =>
                $orderId,

            "product_name" =>
                $row["product_name"],

            "quantity" =>
                (int) $row["quantity"],

            "total_amount" =>
                $row["total_amount"],

            "status" =>
                $row["order_status"],

            "created_at" =>
                $row["created_at"],

            "vouchers" =>
                []

        ];

    }


    /*
    | Only add an actual voucher.
    */

    if (
        !empty($row["voucher_id"])
    ) {

        $orders[$orderId]["vouchers"][] = [

            "serial_number" =>
                $row["serial_number"],

            "pin" =>
                $row["pin"]

        ];

    }

}


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
        My Checkers - FastCheckerGH
    </title>

    <style>

        * {
            box-sizing: border-box;
        }


        body {

            margin: 0;

            font-family:
                Arial,
                Helvetica,
                sans-serif;

            background: #f7f7f7;

            color: #222;

        }


        .header {

            background: #ffffff;

            border-bottom:
                1px solid #eeeeee;

            padding:
                20px;

            text-align: center;

        }


        .logo {

            font-size: 25px;

            font-weight: 800;

        }


        .fast {

            color: #111111;

        }


        .checker {

            color: #f57c00;

        }


        .gh {

            color: #111111;

        }


        .container {

            width: 92%;

            max-width: 900px;

            margin:
                0 auto;

            padding:
                45px 0;

        }


        .page-title {

            text-align: center;

            margin-bottom:
                35px;

        }


        .page-title h1 {

            margin:
                0 0 8px;

            font-size: 30px;

        }


        .page-title p {

            margin: 0;

            color: #777;

        }


        .account-box {

            background:
                #ffffff;

            border:
                1px solid #eeeeee;

            padding:
                20px;

            margin-bottom:
                25px;

            text-align: center;

        }


        .account-box strong {

            color:
                #f57c00;

        }


        .order {

            background:
                #ffffff;

            border:
                1px solid #e5e5e5;

            margin-bottom:
                25px;

            padding:
                25px;

        }


        .order-header {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                20px;

            border-bottom:
                1px solid #eeeeee;

            padding-bottom:
                15px;

            margin-bottom:
                20px;

        }


        .order-header h2 {

            margin: 0;

            font-size: 20px;

        }


        .order-number {

            color:
                #777;

            font-size:
                13px;

            margin-top:
                5px;

        }


        .status {

            color:
                #16803a;

            font-size:
                13px;

            font-weight:
                700;

        }


        .voucher {

            border:
                1px solid #eeeeee;

            background:
                #fafafa;

            padding:
                18px;

            margin-top:
                12px;

        }


        .voucher-row {

            display:
                flex;

            justify-content:
                space-between;

            gap:
                20px;

            padding:
                8px 0;

        }


        .voucher-label {

            color:
                #777;

            font-size:
                14px;

        }


        .voucher-value {

            font-weight:
                700;

            word-break:
                break-all;

        }


        .empty {

            background:
                #ffffff;

            border:
                1px solid #eeeeee;

            padding:
                40px 20px;

            text-align:
                center;

        }


        .empty h2 {

            margin-top: 0;

        }


        .back-link {

            display:
                inline-block;

            margin-top:
                25px;

            color:
                #f57c00;

            font-weight:
                700;

        }
@media (max-width: 600px) {

            .order-header {

                flex-direction:
                    column;

            }


            .voucher-row {

                flex-direction:
                    column;

                gap:
                    3px;

            }


            .page-title h1 {

                font-size:
                    25px;

            }

        }

    </style>

<link rel="stylesheet" href="css/style.css">
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



<main class="container">


    <section class="page-title">

        <h1>
            My Checkers
        </h1>

        <p>
            Your previously purchased
            result checker vouchers.
        </p>

        <p style="margin-top:10px">
            <a href="api/retrieval-logout.php" class="back-link">Sign out of retrieval</a>
        </p>

    </section>



    <section class="account-box">

        Verified Mobile Money Number:

        <strong>
            <?php
            echo htmlspecialchars(
                $user["phone"]
            );
            ?>
        </strong>

    </section>



    <?php if (empty($orders)): ?>


        <section class="empty">

            <h2>
                No Previous Checkers
            </h2>

            <p>
                We could not find any paid
                checker vouchers for this account.
            </p>

            <a
                href="index.php"
                class="back-link"
            >
                ← Back to FastCheckerGH
            </a>

        </section>


    <?php else: ?>


        <?php foreach ($orders as $order): ?>

            <section class="order">


                <div class="order-header">

                    <div>

                        <h2>

                            <?php
                            echo htmlspecialchars(
                                $order["product_name"]
                            );
                            ?>

                        </h2>

                        <div class="order-number">

                            Order #

                            <?php
                            echo $order["order_id"];
                            ?>

                            <br>

                            Purchased:

                            <?php
                            echo htmlspecialchars(
                                $order["created_at"]
                            );
                            ?>

                        </div>

                    </div>


                    <div class="status">

                        <?php
                        echo strtoupper(
                            htmlspecialchars(
                                $order["status"]
                            )
                        );
                        ?>

                    </div>

                </div>



                <?php if (
                    empty($order["vouchers"])
                ): ?>

                    <p>
                        Your payment was completed,
                        but the voucher has not yet
                        been assigned.
                    </p>

                <?php else: ?>


                    <?php
                    $voucherNumber = 1;
                    ?>


                    <?php foreach (
                        $order["vouchers"]
                        as $voucher
                    ): ?>


                        <div class="voucher">

                            <div class="voucher-row">

                                <span class="voucher-label">
                                    Checker #
                                    <?php
                                    echo $voucherNumber;
                                    ?>
                                </span>

                            </div>


                            <div class="voucher-row">

                                <span class="voucher-label">
                                    Serial Number
                                </span>

                                <span class="voucher-value">

                                    <?php
                                    echo htmlspecialchars(
                                        $voucher[
                                            "serial_number"
                                        ]
                                    );
                                    ?>

                                </span>

                            </div>


                            <div class="voucher-row">

                                <span class="voucher-label">
                                    PIN
                                </span>

                                <span class="voucher-value">

                                    <?php
                                    echo htmlspecialchars(
                                        $voucher["pin"]
                                    );
                                    ?>

                                </span>

                            </div>

                        </div>


                        <?php
                        $voucherNumber++;
                        ?>


                    <?php endforeach; ?>


                <?php endif; ?>


            </section>

        <?php endforeach; ?>


    <?php endif; ?>


</main>



<?php include __DIR__ . '/includes/footer.php'; ?>
<script src="js/app.js"></script>
</body>

</html>