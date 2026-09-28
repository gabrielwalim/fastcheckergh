# FastCheckerGH Phase 8 setup

The SMS, WhatsApp, and Email delivery code is already integrated. The only remaining step is to enter your provider credentials in `.env`.

## Brevo

Set:

- `BREVO_API_KEY` = your Brevo API key
- `BREVO_EMAIL_SENDER` = a sender address verified in Brevo
- `BREVO_SMS_SENDER` = your approved SMS sender name
- `BREVO_WHATSAPP_SENDER` = your WhatsApp sender number configured in Brevo
- `BREVO_WHATSAPP_TEMPLATE_ID` = an approved WhatsApp template ID
- `BREVO_EMAIL_SANDBOX=0` when you are ready for real email delivery

Do not put `Bearer` or `Authorization:` in the API key value.

## Test

Log into the admin dashboard and open:

`admin/delivery-test.php`

Test Email, SMS, and WhatsApp independently.

## Important

The code cannot send real messages until the Brevo account, senders, and WhatsApp template are configured. No real credentials are included in this package.

Automatic WAEC result retrieval also remains dependent on authorized WAEC RVS/API credentials.
