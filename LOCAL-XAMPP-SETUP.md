# FastCheckerGH — Local XAMPP Setup

This package is configured for local development/testing on Windows XAMPP. It is **not** a hosting/deployment package.

## 1. Project location

Extract the project to:

`C:\xampp\htdocs\fastcheckergh`

Start **Apache** and **MySQL** from XAMPP.

## 2. Database

Keep using your existing `fastcheckergh` MySQL database. If you are setting up a fresh database, use the SQL files in `database/` and `admin/schema.sql` as required by the existing project.

The local defaults are:

- Host: `localhost`
- Database: `fastcheckergh`
- User: `root`
- Password: blank

## 3. Paystack

You do not need to edit PHP files. After logging into the admin area, open:

`http://localhost/fastcheckergh/admin/paystack-settings.php`

Enter your **Paystack Secret Key** (for example `sk_test_...`) and keep the site URL as:

`http://localhost/fastcheckergh`

The settings page writes the key into the local `.env` file without replacing unrelated settings.

## 4. Admin

If the admin tables do not exist, open:

`http://localhost/fastcheckergh/admin/setup.php`

Create the first administrator.

## 5. Local testing tools

These are intentionally included while you are working locally:

- `admin/paystack-test.php`
- `admin/delivery-test.php`
- `admin/retrieve-check.php`
- `admin/retrieve-setup.php`

Do not expose these diagnostic tools on a public website later.

## 6. Retrieve Old Checkers

Local development OTP mode is enabled by default. This lets you test retrieval before you configure real SMS delivery.

## 7. Brevo

Email, SMS and WhatsApp remain optional until you configure Brevo. The system will show a clear provider-configuration error rather than breaking Paystack/voucher functions.

## 8. Automatic WAEC results

The automatic-results code is ready for an authorized WAEC RVS/API integration, but real result retrieval requires credentials/endpoint information supplied by WAEC.
