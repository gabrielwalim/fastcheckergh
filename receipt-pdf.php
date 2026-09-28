<?php
/**
 * FastCheckerGH - downloadable payment receipt PDF.
 * Only completed/successful orders with assigned vouchers can be downloaded.
 */

require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/receipt-pdf.php';

$reference = trim((string)($_GET['reference'] ?? ''));
if ($reference === '' || strlen($reference) > 120) {
    http_response_code(400);
    exit('Invalid receipt reference.');
}

try {
    $stmt = $pdo->prepare("
        SELECT
            p.payment_reference,
            p.amount,
            p.status AS payment_status,
            o.id AS order_id,
            o.status AS order_status,
            o.created_at AS purchase_date,
            u.phone AS customer_phone,
            prod.name AS product_name,
            oi.quantity,
            CASE
                WHEN LOWER(prod.name) LIKE '%wassce%' THEN 'https://ghana.waecdirect.org/'
                ELSE 'https://eresults.waecgh.org/'
            END AS official_url
        FROM payments p
        INNER JOIN orders o ON o.id = p.order_id
        INNER JOIN users u ON u.id = o.user_id
        INNER JOIN order_items oi ON oi.order_id = o.id
        INNER JOIN products prod ON prod.id = oi.product_id
        WHERE p.payment_reference = ?
          AND p.status = 'successful'
          AND o.status IN ('paid', 'completed')
        ORDER BY oi.id ASC
        LIMIT 1
    ");
    $stmt->execute([$reference]);
    $order = $stmt->fetch();

    if (!$order) {
        http_response_code(404);
        exit('Receipt not available for this payment.');
    }

    $stmt = $pdo->prepare("
        SELECT id, serial_number, pin
        FROM vouchers
        WHERE order_id = ?
          AND status = 'assigned'
        ORDER BY id ASC
    ");
    $stmt->execute([(int)$order['order_id']]);
    $vouchers = $stmt->fetchAll();

    if (!$vouchers) {
        http_response_code(409);
        exit('Receipt is not available until the checker voucher has been assigned.');
    }

    $pdf = (new FastCheckerGHPdfReceipt())->build([
        'reference' => $order['payment_reference'],
        'order_id' => $order['order_id'],
        'purchase_date' => date('d M Y, H:i', strtotime((string)$order['purchase_date'])),
        'customer_phone' => $order['customer_phone'],
        'product_name' => $order['product_name'],
        'quantity' => $order['quantity'],
        'amount' => $order['amount'],
        'official_url' => $order['official_url'],
        'vouchers' => $vouchers,
    ]);

    $filename = 'FastCheckerGH-Receipt-' . preg_replace('/[^A-Za-z0-9_-]/', '', $reference) . '.pdf';
    header('Content-Type: application/pdf');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    header('Content-Length: ' . strlen($pdf));
    header('Cache-Control: private, no-store, max-age=0');
    echo $pdf;
} catch (Throwable $e) {
    error_log('FastCheckerGH receipt PDF error: ' . $e->getMessage());
    http_response_code(500);
    exit('Unable to generate the receipt right now.');
}
