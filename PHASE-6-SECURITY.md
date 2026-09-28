# FastCheckerGH — Production Security Hardening

This release adds the first production-security layer without changing the payment/voucher business logic.

## Included

- Secure session cookie settings (`HttpOnly`, `SameSite=Lax`, HTTPS-aware `Secure`).
- Session strict mode and regeneration after successful admin login.
- Common security response headers.
- Production error-display suppression through `APP_ENV=production`.
- Admin login rate limiting (5 failed attempts in a 15-minute window per username/IP pair).
- CSRF enforcement on the Paystack settings, Paystack connection test, checker upload, admin setup, and existing admin login flows.
- Admin setup is locked once an administrator exists.
- Apache protection for `.env`, SQL, markdown, log and backup files.
- Security database table added to `admin/schema.sql`.

## Production environment

Set at minimum:

```text
APP_ENV=production
APP_DEBUG=0
```

Also keep Paystack/Brevo/WAEC secrets in server environment variables or the protected `.env` file.

## Before public deployment

Enable HTTPS, verify backups, rotate any credentials that may have been exposed during development, and remove any temporary diagnostic pages such as `admin/retrieve-check.php`.
