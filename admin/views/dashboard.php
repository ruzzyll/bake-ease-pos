<?php
/**
 * admin/views/dashboard.php - MINIMALIST SNAPSHOT
 */

// Fetch Today's KPIs
$today_sales = $reportCtrl->getTodaySales() ?? 0;
$orders_today_count = $reportCtrl->getTodayOrderCount() ?? 0;
$items_sold_today = method_exists($reportCtrl, 'getTodayItemsSold') ? $reportCtrl->getTodayItemsSold() : 0;

// Fetch Trend Indicator (% change vs yesterday)
$growth = $reportCtrl->getDoDGrowth();
$growth_class = ($growth >= 0) ? 'text-success' : 'text-danger';
$growth_icon = ($growth >= 0) ? 'fa-caret-up' : 'fa-caret-down';

// Fetch Recent Activity
$recent_orders = $reportCtrl->getRecentTransactions(5); 
?>

<div class="container-fluid p-4">
    <div class="mb-3">
        <a href="index.php?page=dashboard" class="text-decoration-none text-muted small fw-bold">
            <i class="fas fa-arrow-left me-1"></i> Dashboard
        </a>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold text-dark mb-0">Bake Ease Dashboard 🧁</h3>
            <p class="text-muted small mb-0">Daily Performance Snapshot</p>
        </div>
        <div class="text-end">
             <div class="badge bg-white text-dark shadow-sm border py-2 px-3 rounded-pill mb-2">
                <i class="far fa-calendar-alt me-2 text-primary"></i><?= date('M d, Y') ?>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 5px solid #ff7a00;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Sales Today</small>
                            <h2 class="fw-bold mb-0 mt-1">₱<?= number_format($today_sales, 2) ?></h2>
                            <small class="<?= $growth_class ?> fw-bold">
                                <i class="fas <?= $growth_icon ?> me-1"></i><?= number_format(abs($growth), 1) ?>% vs yesterday
                            </small>
                        </div>
                        <div class="p-3 rounded-circle" style="background: rgba(255, 122, 0, 0.1);">
                            <i class="fas fa-coins text-warning fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 5px solid #3498db;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Total Orders Today</small>
                            <h2 class="fw-bold mb-0 mt-1"><?= $orders_today_count ?></h2>
                            <small class="text-muted">Completed Transactions</small>
                        </div>
                        <div class="p-3 rounded-circle" style="background: rgba(52, 152, 219, 0.1);">
                            <i class="fas fa-shopping-basket text-primary fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 15px; border-left: 5px solid #2ecc71;">
                <div class="card-body p-4">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <small class="text-muted text-uppercase fw-bold" style="font-size: 0.7rem;">Items Sold Today</small>
                            <h2 class="fw-bold mb-0 mt-1"><?= $items_sold_today ?></h2>
                            <small class="text-muted">Total Quantity</small>
                        </div>
                        <div class="p-3 rounded-circle" style="background: rgba(46, 204, 113, 0.1);">
                            <i class="fas fa-box-open text-success fa-lg"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex flex-wrap gap-2 justify-content-center bg-white p-3 shadow-sm" style="border-radius: 15px;">
                <a href="../index.php" class="btn btn-primary px-4 rounded-pill">
                    <i class="fas fa-cart-plus me-2"></i>Add New Order
                </a>
                
                <a href="index.php?page=order_list" class="btn btn-outline-dark px-4 rounded-pill">
                    <i class="fas fa-list me-2"></i>Go to Orders
                </a>
                
                <a href="index.php?page=inventory" class="btn btn-outline-dark px-4 rounded-pill">
                    <i class="fas fa-warehouse me-2"></i>Go to Products
                </a>
                
                <a href="index.php?page=reports" class="btn btn-outline-primary px-4 rounded-pill">
                    <i class="fas fa-chart-line me-2"></i>Go to Reports
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm" style="border-radius: 15px;">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold mb-0">Recent Activity</h6>
                    <span class="badge bg-light text-muted fw-normal">Latest 5 Transactions</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.85rem;">
                            <thead class="bg-light text-muted">
                                <tr>
                                    <th class="ps-4 border-0">Order ID</th>
                                    <th class="border-0">Method</th>
                                    <th class="border-0">Status</th>
                                    <th class="pe-4 text-end border-0">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(empty($recent_orders)): ?>
                                    <tr><td colspan="4" class="text-center py-4">No activity yet for today.</td></tr>
                                <?php else: ?>
                                    <?php foreach($recent_orders as $ro): ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">#ORD-<?= $ro['order_id'] ?></td>
                                        <td>
                                            <i class="fas <?= ($ro['order_method'] ?? '') == 'Delivery' ? 'fa-motorcycle' : 'fa-walking' ?> me-2 text-muted"></i>
                                            <?= htmlspecialchars($ro['order_method'] ?? 'N/A') ?>
                                        </td>
                                        <td>
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 border border-success border-opacity-25">
                                                <?= $ro['order_status'] ?>
                                            </span>
                                        </td>
                                        <td class="pe-4 text-end fw-bold">₱<?= number_format($ro['total_amount'], 2) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>