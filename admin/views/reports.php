<div class="container mt-4">
    <div class="card shadow-sm border-0" style="border-radius: 15px;">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Daily Sales Report</h5>
        </div>
        <div class="card-body">
            <div class="row mb-4">
                <div class="col-md-4">
                    <div class="p-3 bg-light rounded-3">
                        <small class="text-muted d-block">Total Revenue</small>
                        <h3 class="fw-bold text-warning mb-0">₱<?= number_format($totalRevenue, 2) ?></h3>
                    </div>
                </div>
            </div>

            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>Order ID</th>
                        <th>Time</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($sales)): ?>
                        <tr><td colspan="4" class="text-center">No sales found for today.</td></tr>
                    <?php else: foreach($sales as $row): ?>
                        <tr>
                            <td>#<?= $row['order_id'] ?></td>
                            <td><?= date('h:i A', strtotime($row['order_date'])) ?></td>
                            <td class="fw-bold">₱<?= number_format($row['total_amount'], 2) ?></td>
                            <td><button class="btn btn-sm btn-outline-dark">View Details</button></td>
                        </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>