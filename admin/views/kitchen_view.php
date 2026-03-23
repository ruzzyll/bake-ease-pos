<?php
/**
 * admin/views/kitchen_view.php
 */

try {
    // Only fetch orders that are currently being baked/prepared
    $query = "SELECT * FROM orders WHERE order_status = 'Preparing' ORDER BY schedule_date ASC";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $kitchenOrders = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $activeCount = count($kitchenOrders);
} catch (Exception $e) {
    echo "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    $kitchenOrders = []; $activeCount = 0;
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">👨‍🍳 Kitchen Queue</h4>
        <p class="text-muted small mb-0">Live production list for the bakery</p>
    </div>
    <div class="text-end">
        <span class="badge bg-danger px-4 py-2 rounded-pill shadow-sm" style="font-size: 0.9rem;">
            <?= $activeCount ?> ACTIVE TASKS
        </span>
    </div>
</div>

<div class="row">
    <?php if ($activeCount > 0): ?>
        <?php foreach($kitchenOrders as $o): ?>
        <div class="col-md-6 col-lg-4 mb-4">
            <div class="card border-0 shadow-sm h-100" style="border-top: 5px solid #0dcaf0; border-radius: 15px;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="badge bg-light text-dark border mb-2">#<?= $o['order_id'] ?></span>
                            <h5 class="fw-bold text-dark mb-0"><?= htmlspecialchars($o['product_name'] ?? 'Custom Order') ?></h5>
                        </div>
                        <div class="text-end">
                            <i class="fas <?= ($o['order_method'] == 'Delivery') ? 'fa-truck text-warning' : 'fa-store text-info' ?> fa-lg"></i>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <div class="small text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Size & Category</div>
                        <div class="text-dark fw-semibold"><?= htmlspecialchars($o['size_name'] ?? 'Standard') ?> (<?= htmlspecialchars($o['category'] ?? 'General') ?>)</div>
                    </div>

                    <div class="p-3 bg-light rounded-3 mb-4">
                        <div class="small text-muted text-uppercase fw-bold mb-1" style="font-size: 0.7rem;">Special Notes / Reason:</div>
                        <div class="text-secondary small italic">"<?= htmlspecialchars($o['reason_name'] ?? 'No special notes') ?>"</div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <div>
                            <div class="small text-muted" style="font-size: 0.7rem;">DUE TIME</div>
                            <div class="fw-bold text-danger"><?= date('h:i A', strtotime($o['schedule_date'])) ?></div>
                        </div>
                        <div class="text-end">
                            <div class="small text-muted" style="font-size: 0.7rem;">DATE</div>
                            <div class="fw-bold"><?= date('M d', strtotime($o['schedule_date'])) ?></div>
                        </div>
                    </div>

                    <div class="d-grid">
                        <a href="update_order_status.php?id=<?= $o['order_id'] ?>&status=Completed" 
                           class="btn btn-success py-2 fw-bold shadow-sm"
                           onclick="return confirm('Is this order ready for the customer?')">
                            <i class="fas fa-check-circle me-2"></i>MARK AS FINISHED
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    <?php else: ?>
        <div class="col-12 text-center py-5">
            <div class="py-5">
                <i class="fas fa-utensils fa-4x text-muted opacity-25 mb-3"></i>
                <h4 class="text-muted">The kitchen is clear!</h4>
                <p class="text-muted">New orders will appear here automatically.</p>
            </div>
        </div>
    <?php endif; ?>
</div>