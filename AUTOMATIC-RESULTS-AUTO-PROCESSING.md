# FastCheckerGH Automatic Result Processing

This build adds the end-to-end application layer for: payment -> voucher assignment -> authorized WAEC API/RVS retrieval -> result storage -> secure result page -> email/SMS/WhatsApp delivery.

## Important authorization requirement

The WAEC Ghana Result Verification System states that developer REST API access is for authorized third-party institutions. FastCheckerGH must obtain an institutional account, fund the provider wallet as required, and generate API credentials before direct result retrieval can operate.

## Environment

Set these values in `app/.env` (or the server environment):

- `RESULT_PROVIDER=waec_api`
- `WAEC_API_URL=https://...` (the exact authorized endpoint supplied by WAEC)
- `WAEC_API_TOKEN=...`
- `WAEC_API_AUTH_HEADER=Authorization`
- `WAEC_API_AUTH_SCHEME=Bearer`
- `WAEC_API_TIMEOUT=30`
- `WAEC_API_VERIFY_SSL=1`
- `BREVO_API_KEY=...`
- `BREVO_EMAIL_SENDER=...`
- `BREVO_EMAIL_SENDER_NAME=FastCheckerGH`
- `BREVO_SMS_SENDER=FastCheckerGH`
- `BREVO_WHATSAPP_TEMPLATE_ID=...` (recommended for transactional WhatsApp)
- `BREVO_WHATSAPP_SENDER=...`

The adapter sends JSON to the configured endpoint. Because WAEC's private API contract is not public, the endpoint path and exact schema are intentionally configured rather than guessed.

## Delivery

When a result is successfully retrieved, FastCheckerGH:

1. Stores the normalized result payload and a raw provider payload.
2. Generates a 64-character random access token for the secure result view.
3. Emails the full result.
4. Sends a concise SMS with a secure result link.
5. Sends a WhatsApp notification with the result link.
6. Logs each channel separately in `notification_logs`.

A notification failure does not erase a successful result.
