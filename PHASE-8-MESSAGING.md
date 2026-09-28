# FastCheckerGH Phase 8 — SMS / WhatsApp / Email Delivery

## What is included

- Brevo transactional email delivery for completed automatic results.
- Brevo transactional SMS delivery for completed automatic results.
- Brevo transactional WhatsApp delivery with either an approved template or, when explicitly enabled, text mode.
- Notification logging in `notification_logs`.
- Duplicate-send protection per order/channel for completed result notifications.
- Admin Delivery Test page at `admin/delivery-test.php`.
- Local email sandbox support with `BREVO_EMAIL_SANDBOX=1`.
- Automatic notification-log table creation for older databases.

## Current provider endpoints

- Email: `POST https://api.brevo.com/v3/smtp/email`
- SMS: `POST https://api.brevo.com/v3/transactionalSMS/send`
- WhatsApp: `POST https://api.brevo.com/v3/whatsapp/sendMessage`

See Brevo documentation for current account setup, sender verification, SMS sender rules, and WhatsApp/Meta activation.

## Configuration

Copy `.env.example` to `.env` and fill in the Brevo settings. Never commit `.env` or real API keys.

For WhatsApp production use, create and approve a utility template in Brevo/Meta, then set `BREVO_WHATSAPP_TEMPLATE_ID`. The result message sends template parameters in this order:

1. Candidate name
2. Exam type
3. Exam year
4. Secure result URL

For local email integration tests, set `BREVO_EMAIL_SANDBOX=1`. This validates the Brevo email request without delivering it.

## Automatic result flow

When `api/process-automatic-result.php` completes a result request, it calls `fc_deliver_result()` and attempts email, SMS, and WhatsApp independently. A failed channel does not make the result itself fail. Each attempt is recorded in `notification_logs`.

## Important

Actual SMS and WhatsApp delivery requires an active Brevo account and the required service/sender configuration. Automatic WAEC result retrieval still requires authorized WAEC RVS/API credentials.
