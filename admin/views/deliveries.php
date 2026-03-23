<?php
/**
 * admin/views/deliveries.php
 */
try {
    // We join with customers to get names and check for VIP status (LTV)
    $query = "SELECT o.*, c.full_name, c.contact_number, 
              (SELECT SUM(total_amount) FROM orders WHERE customer_id = o.customer_id) as customer_ltv
              FROM orders o 
              JOIN customers c ON o.customer_id = c.customer_id
              WHERE o.order_method = 'Delivery' AND o.order_status != 'Cancelled'
              ORDER BY o.schedule_date ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $deliveries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $deliveries = [];
}
?>

<div class="row mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3" style="border-radius: 15px; background: linear-gradient(45deg, #1e1e2d, #2b2b40); color: white;">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-white bg-opacity-10 p-3 me-3">
                    <i class="fas fa-truck fa-lg text-warning"></i>
                </div>
                <div>
                    <small class="text-uppercase opacity-75 d-block" style="font-size: 0.7rem; letter-spacing: 1px;">Pending Deliveries</small>
                    <h3 class="fw-bold mb-0"><?= count($deliveries) ?></h3>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm" style="border-radius: 15px; overflow: hidden;">
    <div class="card-header bg-white py-3 border-0">
        <h6 class="m-0 font-weight-bold text-dark"><i class="fas fa-route me-2 text-primary"></i>Delivery Dispatch Board</h6>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-light">
                <tr class="small text-uppercase text-muted">
                    <th class="ps-4 border-0">Order & Time</th>
                    <th class="border-0">Recipient</th>
                    <th class="border-0">Delivery Address</th>
                    <th class="border-0 text-center">Status</th>
                    <th class="border-0 text-end pe-4">Navigation</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($deliveries)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5">
                            <img src="https://cdn-icons-png.flaticon.com/512/4076/4076549.png" width="80" class="opacity-25 mb-3"><br>
                            <span class="text-muted small italic">No delivery orders found.</span>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach($deliveries as $d): ?>
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold text-dark">#<?= $d['order_id'] ?></div>
                            <div class="text-primary small fw-bold">
                                <i class="far fa-clock me-1"></i><?= date('h:i A', strtotime($d['schedule_date'])) ?>
                            </div>
                        </td>
                        <td>
                            <div class="d-flex align-items-center">
                                <span class="fw-bold text-dark me-2"><?= htmlspecialchars($d['full_name']) ?></span>
                                <?php if($d['customer_ltv'] >= 5000): ?>
                                    <span class="badge bg-warning text-dark" style="font-size: 0.6rem;">VIP</span>
                                <?php endif; ?>
                            </div>
                            <div class="small text-muted"><?= htmlspecialchars($d['contact_number']) ?></div>
                        </td>
                        <td style="max-width: 250px;">
                            <div class="small text-dark lh-sm">
                                <i class="fas fa-map-marker-alt text-danger me-1"></i>
                                <?= htmlspecialchars($d['address_details']) ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php 
                                $status = $d['order_status'];
                                $badgeColor = ($status == 'Preparing') ? 'warning' : 'info';
                                if($status == 'Completed') $badgeColor = 'success';
                            ?>
                            <span class="badge bg-<?= $badgeColor ?> bg-opacity-10 text-<?= $badgeColor ?> rounded-pill px-3">
                                <?= $status ?>
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <a href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($d['address_details']) ?>" 
                               target="_blank" class="btn btn-sm btn-dark rounded-pill px-3 shadow-sm">
                                <i class="fas fa-directions me-1"></i> Map
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>