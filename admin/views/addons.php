<?php
require_once 'controllers/AttributeController.php';
$attr = new AttributeController($db);

// 1. Handle Delete Logic
if (isset($_GET['delete_addon'])) {
    $attr->deleteAddon($_GET['delete_addon']);
    echo "<script>window.location.href='index.php?page=addons';</script>";
}

// 2. Handle Save Logic
if (isset($_POST['save_addon'])) {
    $name = $_POST['addon_name'];
    $price = $_POST['price'];
    $attr->addAddon($name, $price);
    echo "<script>window.location.href='index.php?page=addons';</script>";
}

$addons = $attr->getAddons();
?>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Manage Add-ons 🍓</h5>
        <button class="btn btn-warning text-white fw-bold px-4" 
                style="background: var(--primary-orange); border:none;" 
                data-bs-toggle="modal" 
                data-bs-target="#addAddonModal">
            + Add Add-on
        </button>
    </div>
    <div class="p-3">
        <table class="table table-hover align-middle">
            <thead class="bg-light">
                <tr>
                    <th>Add-on Name</th>
                    <th>Price</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($addons)): ?>
                    <tr><td colspan="3" class="text-center text-muted">No add-ons found.</td></tr>
                <?php else: foreach($addons as $a): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($a['addon_name']) ?></td>
                    <td>₱<?= number_format($a['price'], 2) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light border me-1">✏️</button>
                        <a href="index.php?page=addons&delete_addon=<?= $a['addon_id'] ?>" 
                           class="btn btn-sm btn-light border text-danger" 
                           onclick="return confirm('Remove this add-on?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addAddonModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 15px;">
            <form action="index.php?page=addons" method="POST">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Add New Add-on</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">ADD-ON NAME</label>
                        <input type="text" name="addon_name" class="form-control" placeholder="e.g. Extra Sprinkles" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">PRICE (₱)</label>
                        <input type="number" step="0.01" name="price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_addon" class="btn btn-warning text-white fw-bold px-4" style="background: var(--primary-orange);">Save Add-on</button>
                </div>
            </form>
        </div>
    </div>
</div>