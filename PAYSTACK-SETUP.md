# FastCheckerGH Paystack setup (XAMPP)

1. Put the project in `C:\xampp\htdocs\fastcheckergh`.
2. Start Apache and MySQL in XAMPP.
3. Log in to `/admin/login.php`.
4. Open **Paystack Settings**.
5. Paste only the Paystack Secret Key (`sk_test_...` or `sk_live_...`). Do not paste `Bearer` or `Authorization:`.
6. Keep the site URL as `http://localhost/fastcheckergh` for local testing.
7. Save settings.
8. Open **Paystack Connection** and click **Test Paystack Connection**.

The app writes the values to the root `.env` file. The root `.htaccess` denies Apache access to `.env` files.

Do not send your secret key through chat. For production, prefer server environment variables instead of a web-editable `.env` file.
