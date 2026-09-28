# FastCheckerGH — Automatic Payment Fulfillment Fix

This version fixes the automatic-results flow after successful Paystack payment.

## What changed

- Automatic result payments now use a dedicated verification/fulfillment endpoint.
- Paystack success is recorded even when checker inventory is temporarily empty, preventing a paid customer from being asked to pay again.
- The system assigns a voucher from the exact product used by the automatic order.
- Assignment is idempotent: retrying the same payment cannot allocate a second checker.
- If no voucher is available, the request becomes `awaiting_voucher` and the customer can retry after the admin uploads stock.
- Automatic result orders are blocked before Paystack when there is no available checker in stock.
- Admin > Automatic Results Setup now shows available BECE/WASSCE checker stock.

## Testing

1. Upload at least one available BECE or WASSCE checker card under Admin > Upload Checker Cards.
2. Open Automatic Results.
3. Create a candidate request and pay through Paystack.
4. After payment, the exact matching checker product is assigned automatically.
5. The request continues to `automatic-result-after-payment.php`.

## Existing paid order recovery

If an older automatic payment already succeeded but checker assignment failed, open the payment-success URL again with the same payment reference and request ID, or use the Retry Checker Assignment button. The new endpoint will reuse the successful payment and only attempt voucher assignment.
