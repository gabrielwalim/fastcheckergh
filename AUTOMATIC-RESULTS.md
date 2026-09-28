# FastCheckerGH — Automatic Results Checker

## Current implementation
The customer flow is now:

1. Select BECE / WASSCE School / WASSCE Private.
2. Enter examination year, index number, and optional date of birth.
3. FastCheckerGH creates a result request.
4. Customer enters the serial number and PIN of a checker assigned to a completed FastCheckerGH purchase.
5. The checker is verified against the local voucher inventory and matched to the selected examination type.
6. The customer is sent to the official WAEC eResults portal.

## Why the final results are not scraped automatically
The public WAEC Ghana eResults page currently requires candidate details plus the checker serial number and PIN. FastCheckerGH therefore does not pretend that a private/unofficial results API exists.

For true server-to-server automatic retrieval, configure an **authorized WAEC API integration** using the exact endpoint and response contract supplied by WAEC. The code includes a provider adapter placeholder in `includes/results-provider.php` for that purpose.

## Local testing
No external API credentials are required to test the complete FastCheckerGH flow up to the official WAEC handoff.

## Environment variables for authorized API integration
```text
RESULT_PROVIDER=official_portal
WAEC_API_URL=
WAEC_API_TOKEN=
```
Set `RESULT_PROVIDER=waec_api` only after you have an authorized WAEC API contract and credentials.
