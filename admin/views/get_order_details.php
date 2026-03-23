<?php
// admin/views/get_order_details.php
$host = "127.0.0.1"; $port = "3307"; $db_name = "bake-ease-pos"; $user = "root"; $pass = "";
$db = new PDO("mysql:host=$host;port=$port;dbname=$db_name", $user, $pass);

$id = $_GET['id'] ?? 0;
$stmt = $db->prepare("SELECT o.*, c.full_name, c.contact_number FROM orders o JOIN customers c ON o.customer_id = c.customer_id WHERE o.order_id = ?");
$stmt->execute([$id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$items = $db->prepare("SELECT * FROM order_items WHERE order_id = ?");
$items->execute([$id]);
$items = $items->fetchAll(PDO::FETCH_ASSOC);

if (!$order) exit("Order not found.");
?>

<div id="printableReceipt" style="font-family: 'Courier New', Courier, monospace;">
    <div class="text-center mb-3">
        <h4 class="fw-bold mb-0">BAKE-EASE BAKERY</h4>
        <div class="border-top border-bottom py-1 my-2" style="border-style: dashed !important;">
            ORDER #<?= str_pad($order['order_id'], 5, '0', STR_PAD_LEFT) ?>
        </div>
    </div>

    <div class="row small mb-3">
        <div class="col-6">
            <strong>CUSTOMER:</strong><br>
            <?= strtoupper(htmlspecialchars($order['full_name'])) ?><br>
            <?= htmlspecialchars($order['contact_number']) ?>
        </div>
        <div class="col-6 text-end">
            <strong>METHOD:</strong><br>
            <span class="badge bg-dark text-white rounded-0 px-2"><?= strtoupper($order['order_method']) ?></span><br>
            <?= date('M d, Y h:i A', strtotime($order['schedule_date'])) ?>
        </div>
    </div>

    <div class="p-2 bg-light mb-3 small border-start border-4 border-dark">
        <strong>INSTRUCTIONS / ADDRESS:</strong><br>
        <?= nl2br(htmlspecialchars($order['address_details'])) ?>
    </div>

    <table class="table table-sm table-borderless small mb-0">
        <thead class="border-bottom border-dark">
            <tr>
                <th>ITEM</th>
                <th class="text-center">QTY</th>
                <th class="text-end">PRICE</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach($items as $item): ?>
            <tr>
                <td class="py-2">
                    <strong><?= strtoupper($item['product_name']) ?></strong><br>
                    <small class="text-muted">[<?= $item['size_name'] ?>]</small>
                    <?php if(!empty($item['special_instructions'])): ?>
                        <div class="bg-warning bg-opacity-10 p-1 mt-1">* <?= htmlspecialchars($item['special_instructions']) ?></div>
                    <?php endif; ?>
                </td>
                <td class="text-center py-2">x<?= $item['quantity'] ?></td>
                <td class="text-end py-2">₱<?= number_format($item['price_at_purchase'] * $item['quantity'], 2) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="border-top border-dark border-2 mt-2 pt-2 d-flex justify-content-between h5 fw-bold">
        <span>TOTAL:</span>
        <span>₱<?= number_format($order['total_amount'], 2) ?></span>
    </div>
</div>

<?php if($order['order_status'] === 'Preparing'): ?>
<div class="mt-4 p-3 bg-success bg-opacity-10 border border-success border-opacity-25 rounded-3 d-print-none text-center">
    <button class="btn btn-success fw-bold px-5 rounded-pill shadow-sm py-2" 
            onclick="completeOrder(<?= $order['order_id'] ?>)">
        <i class="fas fa-check-circle me-2"></i>Mark as Completed
    </button>
</div>
<?php endif; ?>

<style>
@media print {
    #printableReceipt { width: 80mm; margin: 0 auto; }
    .modal-header, .modal-footer, .btn-close, .d-print-none { display: none !important; }
}
</style>