<div class="container-fluid p-4">
    <h3 class="fw-bold mb-4 text-dark">Bake Ease Dashboard 🧁</h3>
    
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 15px; border-left: 5px solid var(--primary-orange);">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-light-orange me-3" style="font-size: 1.5rem;">₱</div>
                    <div>
                        <p class="text-muted mb-0">Total Sales</p>
                        <h2 class="fw-bold mb-0">₱<?php echo number_format($total_sales, 2); ?></h2>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="card border-0 shadow-sm p-4" style="border-radius: 15px; border-left: 5px solid #dc3545;">
                <div class="d-flex align-items-center">
                    <div class="rounded-circle p-3 bg-light-red me-3" style="font-size: 1.5rem;">⚠️</div>
                    <div>
                        <p class="text-muted mb-0">Stock Alerts</p>
                        <h2 class="fw-bold mb-0 text-danger"><?php echo $low_stock_count; ?></h2>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mt-5" style="border-radius: 15px;">
        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0">Recent Transactions</h5>
            <button class="btn btn-sm btn-outline-warning text-dark border-secondary-subtle fw-bold">View All</button>
        </div>
        <div class="table-responsive p-3">
            <table class="table table-hover align-middle">
                <thead>
                    <tr>
                        <th class="border-0">Order ID</th>
                        <th class="border-0">Customer</th>
                        <th class="border-0">Status</th>
                        <th class="border-0">Amount</th>
                        <th class="border-0">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="fw-bold">#ORD-1024</td>
                        <td>Walk-in Customer</td>
                        <td><span class="badge rounded-pill bg-success bg-opacity-10 text-success px-3">Completed</span></td>
                        <td class="fw-bold">₱850.00</td>
                        <td>
                            <button class="btn btn-light btn-sm rounded-circle">👁️</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>