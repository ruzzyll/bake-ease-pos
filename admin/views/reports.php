<?php
require_once 'controllers/ReportController.php'; 
$reportCtrl = new ReportController($db);

// Handle Date Filtering (Default to current month)
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date'] ?? date('Y-m-d');

// --- DATA FETCHING ---
$orderList      = $reportCtrl->getFilteredOrderList($startDate, $endDate);
$categoryData   = $reportCtrl->getSalesByCategoryFiltered($startDate, $endDate); 
$topSellers     = $reportCtrl->getTopProductsFiltered($startDate, $endDate, 5); 
$hourlyData     = $reportCtrl->getOrdersByHour($startDate, $endDate);
$fulfillment    = $reportCtrl->getFulfillmentDistribution($startDate, $endDate);

// ANALYTICS DATA
$fastMoving     = $reportCtrl->getFastMovingProducts($startDate, $endDate);
$slowMoving     = $reportCtrl->getSlowMovingProducts($startDate, $endDate);
$sizePerf       = $reportCtrl->getSizePerformance($startDate, $endDate);
$addonPerf      = $reportCtrl->getAddonPerformance($startDate, $endDate);
$customerTrends = $reportCtrl->getCustomerTypeOverTime($startDate, $endDate);
$geoAnalysis    = $reportCtrl->getBarangayCustomerAnalysis(); 

// Helpers
if (!function_exists('formatCurrency')) {
    function formatCurrency($num) { return '₱' . number_format($num, 2); }
}
?>

<style>
    /* UI Enhancement Styles */
    .bg-soft-primary { background-color: rgba(13, 110, 253, 0.1); }
    .bg-soft-success { background-color: rgba(25, 135, 84, 0.1); }
    .bg-soft-danger { background-color: rgba(220, 53, 69, 0.1); }
    
    .icon-shape { 
        width: 35px; height: 35px; 
        display: flex; align-items: center; justify-content: center; 
        font-size: 0.9rem;
    }
    
    /* Scrollable Table Containers */
    .table-scroll-container {
        max-height: 400px; 
        overflow-y: auto;
        border: 1px solid #d8e2eb;
        border-radius: 4px;
        background-color: #fff;
    }

    /* Sticky Headers with visibility fix */
    .table-scroll-container thead th {
        position: sticky;
        top: 0;
        background-color: #212529 !important; /* Force Dark Header */
        color: #ffffff !important;           /* Force White Text */
        z-index: 20;
        box-shadow: inset 0 -1px 0 #dee2e6;
        border: none;
    }

    /* Force Body Text to Dark to prevent inheritance issues */
    .table-scroll-container tbody td {
        color: #212529 !important;
        vertical-align: middle;
    }

    .scrollable-list {
        max-height: 320px;
        overflow-y: auto;
    }

    @media print {
        .nav-pills, .btn-primary, .card-header button, form, .input-group { display: none !important; }
        .col-md-3.border-end { display: none !important; }
        .col-md-9 { width: 100% !important; }
        .table-scroll-container { max-height: none !important; overflow: visible !important; }
    }
</style>

<div class="container-fluid mt-4 mb-5">
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3 align-items-center">
                <input type="hidden" name="page" value="reports">
                <div class="col-md-auto"><h5 class="mb-0 fw-bold">Analytics Period:</h5></div>
                <div class="col-md-3"><input type="date" name="start_date" class="form-control" value="<?= $startDate ?>"></div>
                <div class="col-md-3"><input type="date" name="end_date" class="form-control" value="<?= $endDate ?>"></div>
                <div class="col-md-2"><button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-2"></i>Apply Filter</button></div>
            </form>
        </div>
    </div>

    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card bg-primary text-white border-0 shadow-sm p-3 h-100">
                <small class="opacity-75 fw-bold">PERIOD REVENUE</small>
                <h3 class="mb-0"><?= formatCurrency(array_sum(array_column($categoryData, 'revenue'))) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-dark text-white border-0 shadow-sm p-3 h-100">
                <small class="opacity-75 fw-bold">TOTAL ORDERS</small>
                <h3 class="mb-0"><?= count($orderList) ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-info text-white border-0 shadow-sm p-3 h-100">
                <small class="opacity-75 fw-bold">TOP ADD-ON</small>
                <h3 class="mb-0"><?= !empty($addonPerf) ? array_key_first($addonPerf) : 'None' ?></h3>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card bg-success text-white border-0 shadow-sm p-3 h-100">
                <small class="opacity-75 fw-bold">AVG. ORDER VALUE</small>
                <h3 class="mb-0"><?= count($orderList) > 0 ? formatCurrency(array_sum(array_column($categoryData, 'revenue')) / count($orderList)) : '₱0.00' ?></h3>
            </div>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-dark">Business Intelligence Dashboard 📊</h5>
            <button onclick="window.print()" class="btn btn-sm btn-outline-secondary"><i class="fas fa-print me-1"></i>Export PDF</button>
        </div>
        
        <div class="card-body p-0">
            <div class="row g-0">
                <div class="col-md-3 border-end bg-light">
                    <div class="nav flex-column nav-pills p-3">
                        <button class="nav-link active mb-2 text-start" data-bs-toggle="pill" data-bs-target="#tab-sales">Sales Performance</button>
                        <button class="nav-link mb-2 text-start" data-bs-toggle="pill" data-bs-target="#tab-behavior">Customer Behavior</button>
                        <button class="nav-link mb-2 text-start" data-bs-toggle="pill" data-bs-target="#tab-geo">Geography (CDO)</button>
                        <button class="nav-link mb-2 text-start" data-bs-toggle="pill" data-bs-target="#tab-orders">Detailed Order List</button>
                    </div>
                </div>

                <div class="col-md-9 p-4">
                    <div class="tab-content">
                        
                        <div class="tab-pane fade show active" id="tab-sales">
                            <div class="row mb-4">
                                <div class="col-md-6 border-end">
                                    <h6 class="fw-bold text-muted mb-3"><i class="fas fa-chart-pie me-2"></i>Revenue by Category</h6>
                                    <canvas id="catChart"></canvas>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-muted mb-3"><i class="fas fa-plus-circle me-2"></i>Add-ons Usage</h6>
                                    <div class="table-responsive" style="max-height: 250px;">
                                        <table class="table table-sm table-hover align-middle">
                                            <thead class="bg-light sticky-top">
                                                <tr class="small text-uppercase text-muted"><th>Add-on</th><th class="text-end">Orders</th></tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach($addonPerf as $name => $count): ?>
                                                <tr>
                                                    <td><span class="text-dark fw-medium small"><?= $name ?></span></td>
                                                    <td class="text-end"><span class="badge bg-soft-primary text-primary"><?= $count ?>x</span></td>
                                                </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                            <hr class="my-4">
                            <div class="row mt-4">
                                <div class="col-md-4">
                                    <h6 class="fw-bold mb-3 text-success"><i class="fas fa-bolt me-1"></i> Fast Moving</h6>
                                    <div class="list-group list-group-flush border rounded shadow-sm">
                                        <?php foreach($fastMoving as $p): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                                            <span class="text-truncate fw-medium" style="max-width: 140px;"><?= $p['product_name'] ?></span>
                                            <span class="badge bg-success rounded-pill px-2"><?= $p['volume'] ?> sold</span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="fw-bold mb-3 text-danger"><i class="fas fa-arrow-down me-1"></i> Slow Moving</h6>
                                    <div class="list-group list-group-flush border rounded shadow-sm">
                                        <?php foreach($slowMoving as $p): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 small text-muted">
                                            <span class="text-truncate" style="max-width: 140px;"><?= $p['product_name'] ?></span>
                                            <span class="badge bg-light text-dark border rounded-pill"><?= $p['volume'] ?> sold</span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <h6 class="fw-bold mb-3 text-primary"><i class="fas fa-ruler-combined me-1"></i> Size Perf.</h6>
                                    <div class="list-group list-group-flush border rounded shadow-sm scrollable-list">
                                        <?php foreach($sizePerf as $s): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center py-2 small">
                                            <span class="fw-bold text-secondary"><?= $s['size_name'] ?></span>
                                            <span class="text-dark fw-bold"><?= formatCurrency($s['revenue']) ?></span>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-behavior">
                            <h6 class="fw-bold mb-4">Peak Ordering Hours (Revenue vs Count)</h6>
                            <canvas id="hourChart" height="100"></canvas>
                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">Customer Type Trends (Daily)</h6>
                            <canvas id="typeTrendChart" height="100"></canvas>
                        </div>

                        <div class="tab-pane fade" id="tab-geo">
                            <h6 class="fw-bold mb-3"><i class="fas fa-map-marker-alt me-2"></i>Customer Distribution by Barangay</h6>
                            <div class="mb-4" style="height: 350px;">
                                <canvas id="geoStackedChart"></canvas>
                            </div>
                            <hr class="my-4">
                            <h6 class="fw-bold mb-3">Detailed Barangay Analysis</h6>
                            <div class="table-scroll-container">
                                <table class="table table-hover table-sm small mb-0">
                                    <thead>
                                        <tr>
                                            <th>Barangay</th>
                                            <th>Customer Type</th>
                                            <th class="text-center">Orders</th>
                                            <th class="text-end">Revenue</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($geoAnalysis as $g): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($g['barangay']) ?></td>
                                            <td><span class="badge bg-soft-primary text-primary"><?= $g['type_name'] ?></span></td>
                                            <td class="text-center"><?= $g['count'] ?></td>
                                            <td class="fw-bold text-success text-end"><?= formatCurrency($g['revenue']) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="tab-orders">
                            <div class="row mb-4 align-items-center bg-light rounded p-3 mx-0 border">
                                <div class="col-md-4 border-end">
                                    <h6 class="fw-bold mb-2 text-center text-muted small text-uppercase">Fulfillment Distribution</h6>
                                    <div style="height: 160px;"><canvas id="fulfillmentChart"></canvas></div>
                                </div>
                                <div class="col-md-8 ps-md-4 text-center">
                                    <div class="row">
                                        <?php 
                                            $deliveryCount = count(array_filter($orderList, fn($o) => $o['fulfillment_type'] == 'Delivery'));
                                            $pickupCount   = count(array_filter($orderList, fn($o) => $o['fulfillment_type'] == 'Pickup'));
                                            $totalOrders   = count($orderList);
                                        ?>
                                        <div class="col-6">
                                            <h2 class="fw-bold text-warning mb-0"><?= $deliveryCount ?></h2>
                                            <p class="text-muted small mb-0">DELIVERIES</p>
                                            <span class="badge bg-soft-danger text-danger mt-1"><?= $totalOrders > 0 ? round(($deliveryCount/$totalOrders)*100) : 0 ?>%</span>
                                        </div>
                                        <div class="col-6">
                                            <h2 class="fw-bold text-primary mb-0"><?= $pickupCount ?></h2>
                                            <p class="text-muted small mb-0">PICKUPS</p>
                                            <span class="badge bg-soft-primary text-primary mt-1"><?= $totalOrders > 0 ? round(($pickupCount/$totalOrders)*100) : 0 ?>%</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between mb-3 align-items-center">
                                <h6 class="fw-bold mb-0">Filtered Transactions</h6>
                                <div class="input-group input-group-sm w-25">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" id="orderSearch" class="form-control" placeholder="Search customer or product...">
                                </div>
                            </div>

                            <div class="table-scroll-container">
                                <table class="table table-hover table-sm small mb-0" id="ordersTable">
                                    <thead>
                                        <tr>
                                            <th>ID</th><th>Customer</th><th>Product</th><th>Method</th><th>Type</th><th>Total</th><th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($orderList as $ol): ?>
                                        <tr>
                                            <td>#<?= $ol['order_id'] ?></td>
                                            <td><?= $ol['full_name'] ?? 'Walk-in' ?></td>
                                            <td><?= $ol['product_name'] ?></td>
                                            <td><?= $ol['order_method'] ?></td>
                                            <td><span class="badge <?= $ol['fulfillment_type'] == 'Delivery' ? 'bg-warning text-dark' : 'bg-primary' ?>"><?= $ol['fulfillment_type'] ?></span></td>
                                            <td class="fw-bold"><?= formatCurrency($ol['total_amount']) ?></td>
                                            <td class="text-muted"><?= date('M d, H:i', strtotime($ol['order_date'])) ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    // 1. REVENUE BY CATEGORY
    new Chart(document.getElementById('catChart'), {
        type: 'doughnut',
        data: {
            labels: <?= json_encode(array_column($categoryData, 'category')) ?>,
            datasets: [{
                data: <?= json_encode(array_column($categoryData, 'revenue')) ?>,
                backgroundColor: ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc']
            }]
        },
        options: { cutout: '70%', plugins: { legend: { position: 'bottom' } } }
    });

    // 2. PEAK HOURS
    const hourlyRaw = <?= json_encode($hourlyData) ?>;
    const hourValues = new Array(24).fill(0);
    hourlyRaw.forEach(item => { hourValues[item.hr] = item.order_count; });

    new Chart(document.getElementById('hourChart'), {
        type: 'line',
        data: {
            labels: Array.from({length: 24}, (_, i) => i + ":00"),
            datasets: [{
                label: 'Order Count',
                data: hourValues,
                borderColor: '#4e73df',
                backgroundColor: 'rgba(78, 115, 223, 0.1)',
                fill: true,
                tension: 0.4
            }]
        },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // 3. CUSTOMER TYPE TRENDS
    const trendData = <?= json_encode($customerTrends) ?>;
    const dates = [...new Set(trendData.map(t => t.date))];
    const types = [...new Set(trendData.map(t => t.type_name))];
    const colors = ['#4e73df', '#1cc88a', '#f6c23e', '#e74a3b', '#36b9cc', '#858796'];

    const trendDatasets = types.map((type, index) => ({
        label: type,
        data: dates.map(date => {
            const found = trendData.find(t => t.date === date && t.type_name === type);
            return found ? found.count : 0;
        }),
        backgroundColor: colors[index % colors.length],
        borderRadius: 4
    }));

    new Chart(document.getElementById('typeTrendChart'), {
        type: 'bar',
        data: { labels: dates, datasets: trendDatasets },
        options: { responsive: true, scales: { y: { beginAtZero: true } } }
    });

    // 4. BARANGAY STACKED BAR CHART
    const geoRaw = <?= json_encode($geoAnalysis) ?>;
    const uniqueBarangays = [...new Set(geoRaw.map(g => g.barangay))];
    const uniqueTypes = [...new Set(geoRaw.map(g => g.type_name))];
    const geoColors = { 'Walk-in': '#f6c23e', 'Regular': '#1cc88a', 'Bulk Buyer': '#4e73df', 'Pre-order': '#36b9cc', 'Event': '#e74a3b' };

    const geoDatasets = uniqueTypes.map(type => ({
        label: type,
        data: uniqueBarangays.map(brgy => {
            const entry = geoRaw.find(g => g.barangay === brgy && g.type_name === type);
            return entry ? parseInt(entry.count) : 0;
        }),
        backgroundColor: geoColors[type] || '#858796',
        borderRadius: 4
    }));

    new Chart(document.getElementById('geoStackedChart'), {
        type: 'bar',
        data: { labels: uniqueBarangays, datasets: geoDatasets },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            scales: { x: { stacked: true }, y: { stacked: true } }
        }
    });

    // 5. FULFILLMENT DONUT
    new Chart(document.getElementById('fulfillmentChart'), {
        type: 'doughnut',
        data: {
            labels: ['Delivery', 'Pickup'],
            datasets: [{
                data: [<?= $deliveryCount ?>, <?= $pickupCount ?>],
                backgroundColor: ['#f6c23e', '#4e73df'],
                borderWidth: 0
            }]
        },
        options: {
            maintainAspectRatio: false,
            cutout: '75%',
            plugins: { legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 10 } } } }
        }
    });

    // SEARCH LOGIC
    document.getElementById('orderSearch').addEventListener('keyup', function() {
        let value = this.value.toLowerCase();
        let rows = document.querySelectorAll('#ordersTable tbody tr');
        rows.forEach(row => {
            row.style.display = (row.innerText.toLowerCase().indexOf(value) > -1) ? '' : 'none';
        });
    });
});
</script>