# FastCheckerGH — Phase 9 Production Security & Deployment

This phase hardens the existing FastCheckerGH build without changing the customer-facing purchase/result logic.

## Production environment

Set:

```env
APP_ENV=production
APP_DEBUG=0
FASTCHECKERGH_ENV=production
RETRIEVE_DEV_OTP=0
FCGH_ALLOW_DIAGNOSTICS=0
FCGH_ALLOW_SETUP=0
```

Keep secrets only in server environment variables or the protected `.env` file. Do not commit `.env` to source control.

## Paystack webhook

Configure the Paystack dashboard webhook URL to:

```text
https://YOUR-DOMAIN/api/paystack-webhook.php
```

The endpoint validates the `x-paystack-signature` HMAC-SHA512 signature before processing events.

## Deployment checklist

1. Enable HTTPS.
2. Use a real database user (not MySQL `root`).
3. Set strong `DB_PASSWORD` and `DB_*` environment values.
4. Set `APP_ENV=production` and `APP_DEBUG=0`.
5. Set `RETRIEVE_DEV_OTP=0`.
6. Set `FCGH_ALLOW_DIAGNOSTICS=0`.
7. Set `FCGH_ALLOW_SETUP=0` after the first administrator is created.
8. Configure Paystack live credentials only after test-mode end-to-end checks succeed.
9. Configure Brevo sender/domain and messaging credentials.
10. Obtain authorized WAEC RVS/API credentials before enabling automatic result retrieval.
11. Configure automated database backups.
12. Confirm `.env`, `.sql`, `.md`, `.log`, `.bak`, and backup files are not downloadable over HTTP.
13. Run a mobile and desktop smoke test for homepage, BECE, WASSCE, checkout, retrieve, My Checkers, receipts, and admin login.
14. Set the Paystack webhook URL and confirm webhook deliveries in the Paystack dashboard.
15. Rotate any credential that was ever exposed during development.

## Important compatibility note

The Paystack Settings page now updates only Paystack-related keys in `.env`; it no longer overwrites the Brevo, WAEC, database, and other configuration entries.
