<?php
// FastCheckerGH homepage
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FastCheckerGH - BECE & WASSCE Results Checker</title>
    <meta name="description" content="Buy BECE and WASSCE results checker vouchers, check results and retrieve previous checkers with FastCheckerGH.">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/home.css">
</head>
<body class="home-page">

<header class="home-header">
    <div class="home-header-inner">
        <a href="index.php" class="home-logo" aria-label="FastCheckerGH home">
            <span class="logo-fast">Fast</span><span class="logo-checker">Checker</span><span class="logo-gh">GH</span><span class="logo-dot">.com</span>
        </a>
        <nav class="home-nav" aria-label="Main navigation">
            <a href="index.php">Home</a>
            <a href="bece.php">BECE</a>
            <a href="wassce.php">WASSCE</a>
            <a href="retrieve.php">Retrieve Checker</a>
        </nav>
        <a class="home-contact" href="tel:+233000000000">Contact</a><button class="menu-toggle" id="menu-toggle" type="button" aria-label="Open menu">☰</button>
    </div>
</header>

<main>
    <section class="home-hero">
        <div class="hero-pattern"></div>
        <div class="hero-content">
            <span class="hero-kicker">FAST • SECURE • CONVENIENT</span>
            <h1>BECE &amp; WASSCE<br><span>Results Checker</span></h1>
            <p>Buy your checker, check your results or retrieve a previous checker — all in one place.</p>
            <div class="hero-actions">
                <button class="primary-btn" type="button" data-open-modal="buy-modal">Buy Results Checker</button>
                <button class="secondary-btn" type="button" data-open-modal="check-modal">Check Results</button>
            </div>
        </div>
    </section>

    <section class="services-section">
        <div class="section-heading">
            <span>HOW IT WORKS</span>
            <h2>Choose what you want to do</h2>
            <p>Everything you need to manage your BECE and WASSCE results.</p>
        </div>

        <div class="service-grid">
            <article class="service-card">
                <div class="service-card-image"><img src="assets/logo_waec.jpg" alt="WAEC results checker"></div>
                <div class="service-card-body">
                    <span class="card-number">01</span>
                    <h3>Buy BECE/WASSCE Results Checker</h3>
                    <p>Pay securely with Mobile Money and receive your checker through the available delivery options.</p>
                    <button class="card-btn" type="button" data-open-modal="buy-modal">CLICK HERE TO BUY <span>→</span></button>
                </div>
            </article>

            <article class="service-card">
                <div class="service-card-image"><img src="assets/searcher.jpg" alt="Automatic results checker"></div>
                <div class="service-card-body">
                    <span class="card-number">02</span>
                    <h3>Automatic Results Checker</h3>
                    <p>Select your result type, provide your details and continue through the FastCheckerGH process.</p>
                    <button class="card-btn" type="button" data-open-modal="check-modal">CLICK HERE TO CHECK <span>→</span></button>
                </div>
            </article>

            <article class="service-card">
                <div class="service-card-image"><img src="assets/retrieve.jpg" alt="Retrieve old checkers"></div>
                <div class="service-card-body">
                    <span class="card-number">03</span>
                    <h3>Retrieve Old Checkers</h3>
                    <p>Recover checkers you have already purchased using the Mobile Money number used for payment.</p>
                    <a class="card-btn" href="retrieve.php">CLICK HERE TO RETRIEVE <span>→</span></a>
                </div>
            </article>
        </div>
    </section>

    <section class="trust-section">
        <div class="trust-item"><strong>Secure Payments</strong><span>Mobile Money &amp; Paystack</span></div>
        <div class="trust-item"><strong>Fast Delivery</strong><span>Digital checker delivery</span></div>
        <div class="trust-item"><strong>Easy Retrieval</strong><span>Recover previous purchases</span></div>
    </section>

    <section class="official-section">
        <div>
            <span class="section-label">OFFICIAL LINKS</span>
            <h2>Need to check directly with WAEC?</h2>
            <p>You can also access the official WAEC result portals.</p>
        </div>
        <div class="official-links">
            <a href="https://eresults.waecgh.org/" target="_blank" rel="noopener noreferrer">BECE Results Website ↗</a>
            <a href="https://ghana.waecdirect.org/" target="_blank" rel="noopener noreferrer">WASSCE Results Website ↗</a>
        </div>
    </section>
</main>

<?php include __DIR__ . '/includes/footer.php'; ?>

<!-- BUY MODAL -->
<div class="modal" id="buy-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="buy-modal-title">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-card">
        <button class="modal-close" type="button" data-close-modal aria-label="Close">×</button>
        <div class="modal-icon">₵</div>
        <h2 id="buy-modal-title">Buy Results Checker</h2>
        <p class="modal-intro">Choose the checker you want to purchase.</p>

        <label for="checker-type">Select Checker Type <span>*</span></label>
        <select id="checker-type">
            <option value="">Select checker type</option>
            <option value="bece.php">BECE (School &amp; Private) Results</option>
            <option value="wassce.php">WASSCE (School &amp; Private) Results</option>
        </select>

        <div class="modal-quantity">
            <label for="checker-quantity">Quantity <span>*</span></label>
            <div class="quantity-box">
                <button type="button" data-quantity="minus">−</button>
                <input id="checker-quantity" type="number" min="1" max="200" value="1" readonly>
                <button type="button" data-quantity="plus">+</button>
            </div>
        </div>
        <p class="modal-note">You will continue to the secure purchase page after selection.</p>
        <button class="modal-submit" type="button" id="continue-buy">CONTINUE TO BUY</button>
    </div>
</div>

<!-- CHECK MODAL -->
<div class="modal" id="check-modal" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="check-modal-title">
    <div class="modal-backdrop" data-close-modal></div>
    <div class="modal-card">
        <button class="modal-close" type="button" data-close-modal aria-label="Close">×</button>
        <div class="modal-icon search-icon">⌕</div>
        <h2 id="check-modal-title">Automatic Results Checker</h2>
        <p class="modal-intro">Which type of result do you want to check?</p>

        <label for="result-type-home">Select Result Type <span>*</span></label>
        <select id="result-type-home">
            <option value="">Select result type</option>
            <option value="wassce-school">WASSCE School Result</option>
            <option value="wassce-private">WASSCE Private (Nov-Dec) Result</option>
            <option value="bece">BECE Result</option>
            <option value="shs-placement">SHS Placement</option>
        </select>

        <p class="modal-note">Continue to enter your index number and examination details.</p>
        <button class="modal-submit" type="button" id="continue-check">CONTINUE TO CHECK</button>
    </div>
</div>

<script src="js/home.js"></script>
</body>
</html>
