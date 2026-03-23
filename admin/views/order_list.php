<?php
/**
 * admin/views/order_list.php
 */
try {
    // SQL fetches orders, amounts, and joins items/sizes into a readable list
    $query = "SELECT o.*, 
              GROUP_CONCAT(CONCAT(oi.product_name, ' (', oi.size_name, ')') SEPARATOR '<br>') as item_details
              FROM orders o 
              LEFT JOIN order_items oi ON o.order_id = oi.order_id
              GROUP BY o.order_id
              ORDER BY o.order_id DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $orderCount = count($orders);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $orders = []; $orderCount = 0;
}
?>

<div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-utensils me-2 text-primary"></i>Live Kitchen Orders</h6>
        <span class="badge bg-info bg-opacity-10 text-info px-3 rounded-pill">Total: <?= $orderCount ?></span>
    </div>
    
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead style="background-color: #f8f9fc;">
                <tr>
                    <th class="ps-4 border-0 text-muted small text-uppercase">ID</th>
                    <th class="border-0 text-muted small text-uppercase">Items & Size</th>
                    <th class="border-0 text-muted small text-uppercase text-center">Fulfillment</th>
                    <th class="border-0 text-muted small text-uppercase">Schedule</th>
                    <th class="border-0 text-center text-muted small text-uppercase">Amount</th>
                    <th class="border-0 text-center text-muted small text-uppercase">Status</th>
                    <th class="pe-4 border-0 text-end text-muted small text-uppercase">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $o): ?>
                <tr>
                    <td class="ps-4 fw-bold">#<?= $o['order_id'] ?></td>
                    <td>
                        <div class="fw-bold text-dark" style="line-height: 1.2;">
                            <?= $o['item_details'] ?: htmlspecialchars($o['product_name']) ?>
                        </div>
                        <small class="text-muted mt-1 d-block">Ref: <?= htmlspecialchars($o['type_name'] ?? 'General') ?></small>
                    </td>
                    <td class="text-center">
                        <?php if($o['order_method'] == 'Delivery'): ?>
                            <span class="badge bg-warning bg-opacity-10 text-warning px-3 rounded-pill">
                                <i class="fas fa-truck me-1"></i> Delivery
                            </span>
                        <?php else: ?>
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 rounded-pill">
                                <i class="fas fa-store me-1"></i> Pickup
                            </span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="fw-bold text-dark" style="font-size: 0.9rem;">
                            <?= date('M d, Y', strtotime($o['schedule_date'])) ?>
                        </div>
                        <div class="text-muted small"><?= date('h:i A', strtotime($o['schedule_date'])) ?></div>
                    </td>
                    <td class="text-center">
                        <span class="fw-bold text-dark">₱<?= number_format($o['total_amount'], 2) ?></span>
                    </td>
                    <td class="text-center">
                        <?php 
                            $status = $o['order_status'];
                            $badgeClass = ($status == 'Completed') ? 'bg-success' : (($status == 'Cancelled') ? 'bg-danger' : 'bg-info');
                        ?>
                        <span class="badge rounded-pill <?= $badgeClass ?> bg-opacity-10 text-<?= str_replace('bg-', '', $badgeClass) ?> px-3">
                            <?= $status ?>
                        </span>
                    </td>
                    <td class="pe-4 text-end">
                        <button type="button" class="btn btn-sm btn-outline-secondary border-0 rounded-circle shadow-sm" 
                                onclick="viewOrderDetails(<?= $o['order_id'] ?>)">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="orderModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content" style="border-radius: 20px; border: none;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Order <span id="modalOrderId" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="orderModalBody" class="modal-body p-4"></div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-dark rounded-pill px-4" onclick="window.print()">
                    <i class="fas fa-print me-2"></i>Print Receipt
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetails(orderId) {
    const modal = new bootstrap.Modal(document.getElementById('orderModal'));
    document.getElementById('modalOrderId').innerText = '#' + orderId;
    document.getElementById('orderModalBody').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    // Ensure the path correctly points to your AJAX handler
    fetch('views/get_order_details.php?id=' + orderId)
        .then(res => res.text())
        .then(html => { document.getElementById('orderModalBody').innerHTML = html; });
}

function completeOrder(orderId) {
    if(confirm('Is this order finished and ready for the customer?')) {
        window.location.href = 'update_order_status.php?id=' + orderId + '&status=Completed';
    }
}
</script>