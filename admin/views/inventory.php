<?php
/**
 * admin/views/inventory.php
 * Fixed: Quick Action buttons restored.
 */
require_once 'controllers/ProductController.php';
$productCtrl = new ProductController($db);

// Handle Stock Updates
if (isset($_POST['confirm_update_stock'])) {
    $p_name = $_POST['p_name'] ?? '';
    $s_name = $_POST['s_name'] ?? '';
    $qty = intval($_POST['qty'] ?? 0);
    if ($qty > 0 && $productCtrl->updateStock($p_name, $s_name, $qty)) {
        echo "<script>window.location.href='index.php?page=inventory&success=1';</script>";
        exit();
    }
}
$products = $productCtrl->getAllProducts();
?>

<style>
    .food-img-preview { object-fit: cover; border-radius: 12px; border: 2px solid #fff; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
    .btn-orange { background: #ff7a00 !important; color: white !important; font-weight: bold; border: none; border-radius: 10px; }
    .size-box { background: #ffffff; border: 1px solid #eee; padding: 6px 12px; border-radius: 12px; margin-bottom: 6px; font-size: 0.8rem; display: flex; justify-content: space-between; align-items: center; }
    .badge-qty { background: #e3f2fd; color: #0d47a1; padding: 2px 10px; border-radius: 8px; font-weight: 800; }
    .btn-action-sm { padding: 8px 12px; border-radius: 10px; font-size: 0.85rem; font-weight: 600; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; }
    .btn-edit-outline { border: 1px solid #ff7a00; color: #ff7a00; background: transparent; }
    .btn-edit-outline:hover { background: #ff7a00; color: white; }
    .btn-archive-outline { border: 1px solid #6c757d; color: #6c757d; background: transparent; }
    .btn-archive-outline:hover { background: #6c757d; color: white; }
</style>

<div class="container-fluid p-4">
    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0">Inventory Management 🍰</h4>
            <a href="index.php?page=add_product" class="btn btn-orange px-4">+ New Product</a>
        </div>
        
        <div class="table-responsive px-4 pb-4">
            <table class="table align-middle">
                <thead>
                    <tr class="text-muted small text-uppercase">
                        <th>Product</th>
                        <th>Best Before</th>
                        <th style="width: 25%;">Stock Status</th>
                        <th class="text-center">Quick Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($products as $p): 
                        $pid = $p['product_id'];
                        $p_name = $p['product_name'];
                        $stockDetails = $productCtrl->getProductStock($p_name);
                    ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center">
                                <img src="../assets/uploads/<?= htmlspecialchars($p['image']) ?>" width="50" height="50" class="food-img-preview me-3">
                                <strong><?= htmlspecialchars($p_name) ?></strong>
                            </div>
                        </td>
                        <td><span class="text-muted small">📅 <?= !empty($p['best_before']) ? date('M d, Y', strtotime($p['best_before'])) : 'Not Set' ?></span></td>
                        <td>
                            <?php foreach($stockDetails as $st): ?>
                                <div class="size-box">
                                    <span><?= htmlspecialchars($st['size_name']) ?></span>
                                    <span class="badge-qty"><?= $st['stock'] ?></span>
                                </div>
                            <?php endforeach; ?>
                        </td>
                        <td class="text-center">
                            <div class="dropdown mb-2">
                                <button class="btn btn-sm w-100 py-2 dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" style="background:#198754; color:white; border-radius:10px;">Add Stock</button>
                                <ul class="dropdown-menu">
                                    <?php foreach($stockDetails as $st): ?>
                                        <li><button type="button" class="dropdown-item" onclick="openStockModal('<?= base64_encode($p_name) ?>','<?= base64_encode($st['size_name']) ?>')">+ <?= $st['size_name'] ?></button></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="index.php?page=edit_product&id=<?= $pid ?>" class="btn-action-sm btn-edit-outline flex-grow-1"><i class="fas fa-edit me-1"></i> Edit</a>
                                <button onclick="confirmArchive(<?= $pid ?>)" class="btn-action-sm btn-archive-outline"><i class="fas fa-archive"></i></button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>