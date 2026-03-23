<?php
require_once 'controllers/AttributeController.php';
$attr = new AttributeController($db);

// --- 1. HANDLE FORM LOGIC ---

// Handle ADD
if (isset($_POST['save_addon'])) {
    $name = $_POST['addon_name'];
    $price = $_POST['price'];
    $cat_id = 1; // Default category ID if you haven't built category management yet
    if ($attr->addAddon($name, $price, $cat_id)) {
        echo "<script>window.location.href='index.php?page=addons';</script>";
    }
}

// Handle UPDATE
if (isset($_POST['update_addon'])) {
    $id = $_POST['addon_id'];
    $name = $_POST['addon_name'];
    $price = $_POST['price'];
    $cat_id = 1;
    if ($attr->updateAddon($id, $name, $price, $cat_id)) {
        echo "<script>window.location.href='index.php?page=addons';</script>";
    }
}

// Handle DELETE
if (isset($_GET['delete_addon'])) {
    if ($attr->deleteAddon($_GET['delete_addon'])) {
        echo "<script>window.location.href='index.php?page=addons';</script>";
    }
}

$addons = $attr->getAddons();
?>

<style>
    .btn-orange { background: #ff7a00; color: white; border: none; font-weight: bold; border-radius: 8px; }
    .btn-orange:hover { background: #e66e00; color: white; }
    .action-btn { background: none; border: none; font-size: 1.1rem; padding: 5px 10px; cursor: pointer; }
</style>

<div class="container-fluid p-3">
    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
            <h4 class="fw-bold mb-0">Add-on List ✨</h4>
            <button class="btn btn-orange px-4 py-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#addAddonModal">
                + New Add-on
            </button>
        </div>
        
        <div class="table-responsive px-4 pb-4">
            <table class="table table-hover align-middle">
                <thead class="bg-light text-muted small uppercase">
                    <tr>
                        <th style="width: 60%;">Add-on Name</th>
                        <th>Price</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($addons)): ?>
                        <?php foreach($addons as $a): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark"><?= htmlspecialchars($a['addon_name']) ?></div>
                                <small class="text-muted">ID: #<?= $a['addon_id'] ?></small>
                            </td>
                            <td class="text-primary fw-bold">
                                ₱<?= number_format($a['price'], 2) ?>
                            </td>
                            <td class="text-center">
                                <button class="action-btn" data-bs-toggle="modal" data-bs-target="#editAddon<?= $a['addon_id'] ?>">📝</button>
                                
                                <a href="index.php?page=addons&delete_addon=<?= $a['addon_id'] ?>" 
                                   class="action-btn" 
                                   onclick="return confirm('Remove this add-on?')">🗑️</a>
                            </td>
                        </tr>

                        <div class="modal fade" id="editAddon<?= $a['addon_id'] ?>" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow" style="border-radius: 15px;">
                                    <form method="POST">
                                        <input type="hidden" name="addon_id" value="<?= $a['addon_id'] ?>">
                                        <div class="modal-header border-0 p-4 pb-0">
                                            <h5 class="fw-bold">Edit Add-on</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">ADD-ON NAME</label>
                                                <input type="text" name="addon_name" class="form-control" value="<?= htmlspecialchars($a['addon_name']) ?>" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label small fw-bold">PRICE (₱)</label>
                                                <input type="number" step="0.01" name="price" class="form-control" value="<?= $a['price'] ?>" required>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 p-4 pt-0">
                                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" name="update_addon" class="btn btn-orange">Update Changes</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center py-5 text-muted">No add-ons found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="modal fade" id="addAddonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 15px;">
            <form method="POST">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="fw-bold">New Add-on</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">ADD-ON NAME</label>
                        <input type="text" name="addon_name" class="form-control" placeholder="e.g. Birthday Candle" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">PRICE (₱)</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_addon" class="btn btn-orange">Save Add-on</button>
                </div>
            </form>
        </div>
    </div>
</div>