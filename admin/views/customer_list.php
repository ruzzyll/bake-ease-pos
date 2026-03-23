<?php
/**
 * admin/views/customer_list.php
 */
try {
    $query = "SELECT c.*, 
              COUNT(o.order_id) as total_orders,
              SUM(o.total_amount) as total_spent,
              MAX(o.order_date) as last_order
              FROM customers c 
              LEFT JOIN orders o ON c.customer_id = o.customer_id 
              GROUP BY c.customer_id 
              ORDER BY total_spent DESC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $customers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
}
?>

<div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-0">
        <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-users me-2 text-primary"></i>Customer Directory</h6>
        <div class="small text-muted">VIP Threshold: <span class="badge bg-warning text-dark">₱5,000.00+</span></div>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light text-muted small text-uppercase">
                <tr>
                    <th class="ps-4 border-0">Customer Profile</th>
                    <th class="border-0">Contact Details</th>
                    <th class="border-0 text-center">Total Orders</th>
                    <th class="border-0 text-center">Lifetime Value</th>
                    <th class="pe-4 border-0 text-end">History</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($customers as $c): ?>
                <tr>
                    <td class="ps-4">
                        <div class="d-flex align-items-center">
                            <div class="fw-bold text-dark me-2"><?= htmlspecialchars($c['full_name']) ?></div>
                            <?php if($c['total_spent'] >= 5000): ?>
                                <span class="badge bg-warning text-dark shadow-sm" style="font-size: 0.65rem;">
                                    <i class="fas fa-crown me-1"></i>VIP
                                </span>
                            <?php endif; ?>
                        </div>
                        <small class="text-muted">ID: #<?= str_pad($c['customer_id'], 4, '0', STR_PAD_LEFT) ?></small>
                    </td>
                    <td>
                        <div class="small"><i class="fas fa-phone fa-xs me-2 text-muted"></i><?= htmlspecialchars($c['contact_number']) ?></div>
                        <div class="small text-muted"><i class="fas fa-envelope fa-xs me-2"></i><?= htmlspecialchars($c['email'] ?? 'N/A') ?></div>
                    </td>
                    <td class="text-center">
                        <span class="badge rounded-pill bg-primary bg-opacity-10 text-primary px-3">
                            <?= $c['total_orders'] ?>
                        </span>
                    </td>
                    <td class="text-center fw-bold <?= ($c['total_spent'] >= 5000) ? 'text-warning' : 'text-success' ?>">
                        ₱<?= number_format($c['total_spent'] ?? 0, 2) ?>
                    </td>
                    <td class="pe-4 text-end">
                        <button class="btn btn-sm btn-outline-info border-0 rounded-circle shadow-sm" 
                                onclick="loadHistory(<?= $c['customer_id'] ?>, '<?= addslashes($c['full_name']) ?>')"
                                title="View Purchase History">
                            <i class="fas fa-shopping-bag"></i>
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="historyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4 pb-0">
                <h5 class="modal-title fw-bold">Purchase History: <span id="historyCustomerName" class="text-primary"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="historyContent"></div>
        </div>
    </div>
</div>

<script>
function loadHistory(custId, name) {
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    document.getElementById('historyCustomerName').innerText = name;
    document.getElementById('historyContent').innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary"></div></div>';
    modal.show();

    fetch('views/get_customer_history.php?customer_id=' + custId)
        .then(res => res.text())
        .then(html => { document.getElementById('historyContent').innerHTML = html; });
}
</script>