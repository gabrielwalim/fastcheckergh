# FastCheckerGH — Production Deployment Checklist

## 1. Server
- PHP 8.1+ with PDO MySQL and cURL enabled.
- MySQL 8.x or compatible MariaDB.
- HTTPS certificate installed.
- Apache/Nginx configured to serve the project directory.

## 2. Environment
Copy `.env.production.example` to `.env` on the server and set real values.
Do not upload a local `.env` containing secrets.

Required minimums:
- `APP_ENV=production`
- `APP_DEBUG=0`
- `FASTCHECKERGH_ENV=production`
- `FCGH_ALLOW_DIAGNOSTICS=0`
- `FCGH_ALLOW_SETUP=0`
- `RETRIEVE_DEV_OTP=0`
- `PAYSTACK_SECRET_KEY=<server secret>`
- `PAYSTACK_API_URL=https://api.paystack.co`
- `FASTCHECKERGH_SITE_URL=https://your-domain.example`

For messaging, configure Brevo values only after the account/senders are verified.
For automatic WAEC processing, configure only the authorized WAEC RVS/API credentials issued to your institution.

## 3. Database
Import the existing FastCheckerGH database to production.
Verify that `admin_users`, `orders`, `payments`, `products`, `vouchers`, `result_types`, `result_requests`, and `notification_logs` exist.

## 4. Paystack
Set the Paystack webhook in the dashboard to:
`https://your-domain.example/api/paystack-webhook.php`

Use a live secret only after a successful test-mode end-to-end test.

## 5. Security
- Keep `.env` outside public access or blocked by server configuration.
- Keep diagnostics disabled.
- Remove temporary development files after deployment.
- Use HTTPS only.
- Take a database backup before enabling live payments.

## 6. End-to-end smoke test
1. Home page loads.
2. BECE checkout opens.
3. WASSCE checkout opens.
4. Paystack test/live flow verifies correctly.
5. Voucher is assigned once.
6. Retrieve Old Checkers finds the paid checker.
7. Receipt PDF downloads.
8. Admin login works.
9. Admin order/voucher/notification pages load.
10. Automatic Results order can be created and paid.
11. If WAEC API is not authorized/configured, the system reports that state without exposing credentials or stack traces.
12. Production messaging is tested separately for Email, SMS, and WhatsApp.

## 7. Rollback
Keep the previous deployment package and a database backup before each production release.
