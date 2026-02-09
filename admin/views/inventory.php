<?php
require_once 'controllers/ProductController.php';
$productCtrl = new ProductController($db);

// Handle Stock Update Request
if (isset($_POST['update_stock_btn'])) {
    $productCtrl->updateStock($_POST['p_id'], $_POST['s_id'], $_POST['qty']);
    echo "<script>window.location.href='index.php?page=inventory';</script>";
}

$products = $productCtrl->getAllProducts();
?>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Inventory Management 🥖</h5>
        <button class="btn btn-warning text-white fw-bold px-4" style="background: var(--primary-orange); border:none;">+ New Product</button>
    </div>
    <div class="p-3">
        <table class="table table-hover align-middle">
            <thead class="bg-light">
                <tr>
                    <th>Product</th>
                    <th>Price</th>
                    <th>Current Stock</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($products as $p): 
                    $stockDetails = $productCtrl->getProductStock($p['product_id']);
                ?>
                <tr>
                    <td>
                        <div class="d-flex align-items-center">
                            <img src="../assets/uploads/<?= $p['image'] ?>" width="45" height="45" class="rounded me-3" onerror="this.src='../assets/uploads/placeholder.jpg'">
                            <div>
                                <div class="fw-bold"><?= $p['product_name'] ?></div>
                                <small class="text-muted"><?= $p['category_name'] ?></small>
                            </div>
                        </div>
                    </td>
                    <td>₱<?= number_format($p['price'], 2) ?></td>
                    <td>
                        <?php foreach($stockDetails as $st): ?>
                            <span class="badge border text-dark fw-normal">
                                <?= $st['size_name'] ?>: <b class="<?= $st['stock'] < 5 ? 'text-danger' : 'text-success' ?>"><?= $st['stock'] ?></b>
                            </span>
                        <?php endforeach; ?>
                    </td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-outline-success me-1" data-bs-toggle="modal" data-bs-target="#stockModal<?= $p['product_id'] ?>">➕ Stock</button>
                        <button class="btn btn-sm btn-light border">✏️</button>
                    </td>
                </tr>

                <div class="modal fade" id="stockModal<?= $p['product_id'] ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow" style="border-radius: 15px;">
                            <form method="POST">
                                <input type="hidden" name="p_id" value="<?= $p['product_id'] ?>">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h5 class="fw-bold">Update Stock: <?= $p['product_name'] ?></h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <label class="small fw-bold text-muted">SELECT SIZE</label>
                                    <select name="s_id" class="form-select mb-3" required>
                                        <?php foreach($stockDetails as $st): ?>
                                            <option value="<?= $st['size_id'] ?>"><?= $st['size_name'] ?> (Current: <?= $st['stock'] ?>)</option>
                                        <?php endforeach; ?>
                                    </select>
                                    <label class="small fw-bold text-muted">ADD QUANTITY</label>
                                    <input type="number" name="qty" class="form-control" placeholder="Amount to add..." required min="1">
                                </div>
                                <div class="modal-footer border-0 p-4 pt-0">
                                    <button type="submit" name="update_stock_btn" class="btn btn-warning text-white fw-bold w-100" style="background: var(--primary-orange);">Save Inventory</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>