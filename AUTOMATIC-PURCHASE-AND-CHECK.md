# FastCheckerGH — Automatic Purchase + Results Flow

The Automatic Results Checker now owns the purchase step.

Flow:

1. Candidate selects BECE, WASSCE School, or WASSCE Private.
2. Candidate enters exam year, index number, optional date of birth, Mobile Money number, and email.
3. FastCheckerGH creates a one-checker order using the active BECE Checker or WASSCE Checker product price from the database.
4. FastCheckerGH sends the candidate to Paystack.
5. `payment-success.php` verifies the transaction through the existing Paystack verification/fulfilment flow.
6. The existing voucher assignment logic assigns one checker automatically.
7. The result request is linked to the assigned voucher.
8. FastCheckerGH attempts the configured result provider.
9. If `RESULT_PROVIDER=official_portal` (the default), the candidate is sent to the official WAEC eResults portal. FastCheckerGH does not scrape or invent an undocumented API.
10. When an authorized WAEC API contract/endpoint is available, configure `RESULT_PROVIDER=waec_api` and complete `includes/results-provider.php` with the provider's documented endpoint and response mapping. No unofficial endpoint is guessed.

The official WAEC eResults service currently accepts index number, examination year, serial number and PIN. https://eresults.waecgh.org/
