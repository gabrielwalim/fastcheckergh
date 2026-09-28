# FastCheckerGH — Retrieve Old Checkers

The retrieve flow is now:

1. Customer enters the phone number used to purchase a checker.
2. FastCheckerGH normalizes common Ghana phone formats.
3. A 6-digit OTP is generated and stored only as a password hash.
4. In local development, `RETRIEVE_DEV_OTP=1` returns the OTP on-screen for testing.
5. In production, set `FASTCHECKERGH_ENV=production` and `RETRIEVE_DEV_OTP=0`.
6. Configure `BREVO_API_KEY` and `BREVO_SMS_SENDER` to send the OTP through Brevo transactional SMS.
7. After OTP verification the customer is redirected to `my-checkers.php`.
8. Only paid/completed orders belonging to the verified customer are displayed.
9. The retrieval session expires after 20 minutes and can be ended with “Sign out of retrieval”.

## Local XAMPP test

Use a phone number that already has a paid/completed order and assigned voucher in the `fastcheckergh` database.

Open:

`http://localhost/fastcheckergh/retrieve.php`

Enter the same phone number used during purchase, click **Send OTP**, and use the displayed development OTP.

## Production SMS

Brevo transactional SMS uses:

`https://api.brevo.com/v3/transactionalSMS/send`

Set:

`FASTCHECKERGH_ENV=production`
`RETRIEVE_DEV_OTP=0`
`BREVO_API_KEY=...`
`BREVO_SMS_SENDER=FastCheckerGH`

Do not expose the API key to browser JavaScript.
