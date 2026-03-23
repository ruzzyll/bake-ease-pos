<?php
/**
 * admin/views/get_customer_history.php
 */
$host = "127.0.0.1"; $port = "3307"; $db_name = "bake-ease-pos"; $user = "root"; $pass = "";
$db = new PDO("mysql:host=$host;port=$port;dbname=$db_name", $user, $pass);

$custId = $_GET['customer_id'] ?? 0;

$query = "SELECT o.order_date, oi.product_name, oi.size_name, oi.quantity, oi.special_instructions
          FROM orders o
          JOIN order_items oi ON o.order_id = oi.order_id
          WHERE o.customer_id = ?
          ORDER BY o.order_date DESC";
$stmt = $db->prepare($query);
$stmt->execute([$custId]);
$history = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$history): ?>
    <div class="text-center py-4 text-muted small italic">No purchase history found for this customer.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
            <thead class="small text-muted border-bottom">
                <tr>
                    <th>Date</th>
                    <th>Product & Size</th>
                    <th class="text-center">Qty</th>
                    <th>Special Requests</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($history as $h): ?>
                <tr>
                    <td class="small"><?= date('M d, Y', strtotime($h['order_date'])) ?></td>
                    <td>
                        <div class="fw-bold"><?= htmlspecialchars($h['product_name']) ?></div>
                        <small class="text-muted"><?= htmlspecialchars($h['size_name']) ?></small>
                    </td>
                    <td class="text-center">x<?= $h['quantity'] ?></td>
                    <td class="small italic text-primary">
                        <?= htmlspecialchars($h['special_instructions'] ?: 'None') ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>