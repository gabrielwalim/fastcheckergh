<?php

require_once "includes/db.php";

// Get the WASSCE product from the database
$stmt = $pdo->prepare("
    SELECT id, name, description, price
    FROM products
    WHERE name = 'WASSCE Checker'
    AND status = 'active'
    LIMIT 1
");

$stmt->execute();

$product = $stmt->fetch();

// Stop if the product does not exist
if (!$product) {
    die("WASSCE Checker product is currently unavailable.");
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
        <?php echo htmlspecialchars($product['name']); ?> - FastCheckerGH
    </title>

    <meta
        name="description"
        content="Purchase your WASSCE result checker securely through FastCheckerGH."
    >

    <link
        rel="stylesheet"
        href="css/style.css"
    >

</head>


<body>


<!-- ==========================================
     HEADER
========================================== -->

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



<!-- ==========================================
     PAGE HEADER
========================================== -->

<section class="page-header">

    <div class="container">

        <h1>
            Buy WASSCE Checker
        </h1>

    
    </div>

</section>



<!-- ==========================================
     PRODUCT SECTION
========================================== -->

<main class="product-section">

    <div class="container product-layout">


    

        <!-- ==========================================
             PURCHASE CARD
        ========================================== -->

        <div class="purchase-card">


            <div class="purchase-card-header">

                <h3>
                    <?php echo htmlspecialchars($product['name']); ?>
                </h3>

                <p>
                    Select your quantity
                </p>

            </div>



            <!-- PRICE -->

            <div class="price">

                <strong>
                    GH₵<?php echo number_format($product['price'], 2); ?>
                </strong>

                <small>
                    / checker
                </small>

            </div>



            <!-- ==========================================
                 PRODUCT DATA FOR JAVASCRIPT
            ========================================== -->

            <span
                id="product-price"
                data-price="<?php echo htmlspecialchars($product['price']); ?>"
                hidden
            ></span>


            <input
                type="hidden"
                id="product-id"
                value="<?php echo (int)$product['id']; ?>"
            >



            <!-- ==========================================
                 QUANTITY
            ========================================== -->

            <div class="form-group">

                <label for="quantity">
                    Quantity
                </label>


                <div class="quantity-control">

                    <button
                        type="button"
                        id="decrease"
                        aria-label="Decrease quantity"
                    >
                        −
                    </button>


                    <input
                        type="number"
                        id="quantity"
                        value="1"
                        min="1"
                        max="10"
                        readonly
                    >


                    <button
                        type="button"
                        id="increase"
                        aria-label="Increase quantity"
                    >
                        +
                    </button>

                </div>

            </div>



            <!-- ==========================================
                 TOTAL
            ========================================== -->

            <div class="total-row">

                <span>
                    Total
                </span>


                <strong>

                    GH₵

                    <span id="total-price">
                        <?php echo number_format($product['price'], 2); ?>
                    </span>

                </strong>

            </div>



            <!-- ==========================================
                 CUSTOMER PHONE
            ========================================== -->

            <div class="form-group">

                <label for="phone">
                    Mobile Money Number
                </label>


                <input
                    type="tel"
                    id="phone"
                    placeholder="024 XXX XXXX"
                    autocomplete="tel"
                >

            </div>



            <!-- ==========================================
                 EMAIL
            ========================================== -->

            <div class="form-group">

                <label for="email">
                    Email Address
                </label>


                <input
                    type="email"
                    id="email"
                    placeholder="you@example.com"
                    autocomplete="email"
                >

            </div>



            <!-- ==========================================
                 CHECKOUT
            ========================================== -->

            <button
                type="button"
                class="checkout-button"
                id="checkout-button"
            >
                Proceed to Checkout
            </button>


            <p class="secure-note">
                🔒 Your information is securely processed.
            </p>

        </div>

    </div>

</main>



<!-- ==========================================
     CONTACT
========================================== -->

<section
    class="contact"
    id="contact"
>

    <div class="container">

        <h2>
            Need Help?
        </h2>

        <p>
            Contact our support team if you need assistance.
        </p>


        <a
            href="#"
            class="btn btn-primary"
        >
            Contact Support
        </a>

    </div>

</section>



<!-- ==========================================
     FOOTER
========================================== -->

<?php include __DIR__ . '/includes/footer.php'; ?>



<!-- ==========================================
     JAVASCRIPT
========================================== -->

<script src="js/app.js"></script>

<script src="js/wassce.js"></script>


</body>

</html>