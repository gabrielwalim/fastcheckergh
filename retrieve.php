<?php

require_once "includes/db.php";

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
        Retrieve Old Checkers - FastCheckerGH
    </title>

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


<main class="retrieve-page">

    <div class="retrieve-container">

        <div class="retrieve-header">

            <h1>
                Retrieve Old Checkers
            </h1>

            <p>
                Recover your previously purchased
                result checker vouchers securely.
            </p>

        </div>


        <div class="retrieve-card">

            <?php if (isset($_GET['expired'])): ?>
                <div class="retrieve-message error">Your retrieval session expired. Please verify your phone number again.</div>
            <?php endif; ?>

            <div class="retrieve-icon">
                🔐
            </div>


            <h2>
                Verify Your Mobile Number
            </h2>


            <p class="retrieve-description">

                Enter the Mobile Money number you used
                when purchasing your checker.

                We will send you a verification code.

            </p>


            <form
                id="retrieve-phone-form"
            >

                <div class="form-group">

                    <label for="retrieve-phone">
                        Mobile Money Number
                    </label>

                    <input
                        type="tel"
                        id="retrieve-phone"
                        name="phone"
                        placeholder="+233XXXXXXXXX"
                        autocomplete="tel"
                        required
                    >

                </div>


                <button
                    type="submit"
                    id="send-otp-button"
                    class="retrieve-button"
                >
                    Send OTP
                </button>

            </form>


            <div
                id="otp-section"
                class="otp-section"
                style="display: none;"
            >

                <div class="otp-divider"></div>


                <h3>
                    Enter Verification Code
                </h3>


                <p>
                    Enter the 6-digit OTP sent
                    to your phone.
                </p>


                <form
                    id="verify-otp-form"
                >

                    <div class="form-group">

                        <label for="otp">
                            Verification Code
                        </label>

                        <input
                            type="text"
                            id="otp"
                            name="otp"
                            maxlength="6"
                            inputmode="numeric"
                            placeholder="123456"
                            autocomplete="one-time-code"
                            required
                        >

                    </div>


                    <button
                        type="submit"
                        id="verify-otp-button"
                        class="retrieve-button"
                    >
                        Verify OTP
                    </button>

                </form>

            </div>


            <div
                id="retrieve-message"
                class="retrieve-message"
            ></div>


        </div>

    </div>

</main>


<?php include __DIR__ . '/includes/footer.php'; ?>


<script>

/*
|--------------------------------------------------------------------------
| GET FORM ELEMENTS
|--------------------------------------------------------------------------
*/

const phoneForm =
    document.getElementById(
        "retrieve-phone-form"
    );

const verifyForm =
    document.getElementById(
        "verify-otp-form"
    );

const otpSection =
    document.getElementById(
        "otp-section"
    );

const messageBox =
    document.getElementById(
        "retrieve-message"
    );

const sendOtpButton =
    document.getElementById(
        "send-otp-button"
    );

const verifyOtpButton =
    document.getElementById(
        "verify-otp-button"
    );


/*
|--------------------------------------------------------------------------
| SHOW MESSAGE
|--------------------------------------------------------------------------
*/

function showMessage(
    message,
    type
) {

    messageBox.textContent =
        message;

    messageBox.className =
        "retrieve-message " +
        type;

}


/*
|--------------------------------------------------------------------------
| SEND OTP
|--------------------------------------------------------------------------
*/

phoneForm.addEventListener(
    "submit",
    async function(event) {

        event.preventDefault();


        const phone =
            document
                .getElementById(
                    "retrieve-phone"
                )
                .value
                .trim();


        if (phone === "") {

            showMessage(
                "Please enter your Mobile Money number.",
                "error"
            );

            return;

        }


        sendOtpButton.disabled =
            true;

        sendOtpButton.textContent =
            "Sending OTP...";


        try {

            const response =
                await fetch(
                    "api/send-retrieve-otp.php",
                    {
                        method: "POST",

                        headers: {
                            "Content-Type":
                                "application/json"
                        },

                        body:
                            JSON.stringify({
                                phone: phone
                            })
                    }
                );


            const rawResponse = await response.text();
            let result;

            try {
                result = JSON.parse(rawResponse);
            } catch (parseError) {
                console.error("Non-JSON OTP API response:", rawResponse);
                throw new Error(
                    "The server returned an invalid response. Please check the PHP error log."
                );
            }

            console.log(
                "OTP API response:",
                result
            );


            if (
                !response.ok ||
                !result.success
            ) {

                throw new Error(
                    result.message ||
                    "Unable to send OTP."
                );

            }


            /*
            |--------------------------------------------------------------------------
            | DEVELOPMENT OTP
            |--------------------------------------------------------------------------
            |
            | This is temporary.
            | It allows us to test the system before
            | connecting a real SMS provider.
            |
            */

            if (
                result.development_otp
            ) {

                showMessage(
                    "Development OTP: " +
                    result.development_otp,
                    "success"
                );

                console.log(
                    "DEVELOPMENT OTP:",
                    result.development_otp
                );

            } else {

                showMessage(
                    result.message ||
                    "OTP sent successfully.",
                    "success"
                );

            }


            otpSection.style.display =
                "block";


            sendOtpButton.textContent =
                "OTP Sent";


        } catch (error) {

            console.error(
                "OTP error:",
                error
            );


            showMessage(
                error.message,
                "error"
            );


            sendOtpButton.disabled =
                false;

            sendOtpButton.textContent =
                "Send OTP";

        }

    }
);


/*
|--------------------------------------------------------------------------
| VERIFY OTP
|--------------------------------------------------------------------------
*/

verifyForm.addEventListener(
    "submit",
    async function(event) {

        event.preventDefault();


        const phone =
            document
                .getElementById(
                    "retrieve-phone"
                )
                .value
                .trim();


        const otp =
            document
                .getElementById(
                    "otp"
                )
                .value
                .trim();


        if (phone === "") {

            showMessage(
                "Please enter your phone number.",
                "error"
            );

            return;

        }


        if (
            otp === "" ||
            !/^[0-9]{6}$/.test(otp)
        ) {

            showMessage(
                "Please enter the 6-digit OTP.",
                "error"
            );

            return;

        }


        verifyOtpButton.disabled =
            true;

        verifyOtpButton.textContent =
            "Verifying...";


        try {

            const response =
                await fetch(
                    "api/verify-retrieve-otp.php",
                    {
                        method: "POST",

                        headers: {
                            "Content-Type":
                                "application/json"
                        },

                        body:
                            JSON.stringify({
                                phone: phone,
                                otp: otp
                            })
                    }
                );


            const rawResponse = await response.text();
            let result;

            try {
                result = JSON.parse(rawResponse);
            } catch (parseError) {
                console.error("Non-JSON OTP verification response:", rawResponse);
                throw new Error(
                    "The server returned an invalid response. Please check the PHP error log."
                );
            }

            console.log(
                "OTP verification response:",
                result
            );


            if (
                !response.ok ||
                !result.success
            ) {

                throw new Error(
                    result.message ||
                    "OTP verification failed."
                );

            }


            showMessage(
                "OTP verified successfully. Loading your checkers...",
                "success"
            );


            /*
            |--------------------------------------------------------------------------
            | REDIRECT
            |--------------------------------------------------------------------------
            */

            window.location.href =
                "my-checkers.php";

        } catch (error) {

            console.error(
                "Verification error:",
                error
            );


            showMessage(
                error.message,
                "error"
            );


            verifyOtpButton.disabled =
                false;

            verifyOtpButton.textContent =
                "Verify OTP";

        }

    }
);

</script>


</body>

</html>