<?php
require_once 'controllers/AttributeController.php';
$attr = new AttributeController($db);

// Handle Delete Logic if a delete ID is passed
if (isset($_GET['delete_size'])) {
    $attr->deleteSize($_GET['delete_size']);
    echo "<script>window.location.href='index.php?page=sizes';</script>";
}

$sizes = $attr->getSizes();
?>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Manage Sizes 📏</h5>
        <button class="btn btn-warning text-white fw-bold px-4" 
                style="background: var(--primary-orange); border:none;" 
                data-bs-toggle="modal" 
                data-bs-target="#addSizeModal">
            + Add Size
        </button>
    </div>
    <div class="p-3">
        <table class="table table-hover align-middle">
            <thead class="bg-light">
                <tr>
                    <th>Size Name</th>
                    <th>Extra Price</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sizes)): ?>
                    <tr><td colspan="3" class="text-center text-muted">No sizes found.</td></tr>
                <?php else: foreach($sizes as $s): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($s['size_name']) ?></td>
                    <td>₱<?= number_format($s['extra_price'], 2) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light border me-1">✏️</button>
                        <a href="index.php?page=sizes&delete_size=<?= $s['size_id'] ?>" 
                           class="btn btn-sm btn-light border text-danger" 
                           onclick="return confirm('Delete this size?')">🗑️</a>
                    </td>
                </tr>
                <?php endforeach; endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="modal fade" id="addSizeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 15px;">
            <form action="index.php?page=sizes" method="POST">
                <div class="modal-header border-0 p-4 pb-0">
                    <h5 class="modal-title fw-bold">Add New Size</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">SIZE NAME (E.G., 8-INCH)</label>
                        <input type="text" name="size_name" class="form-control" placeholder="Enter size..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">EXTRA PRICE (₱)</label>
                        <input type="number" step="0.01" name="extra_price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_size" class="btn btn-warning text-white fw-bold px-4" style="background: var(--primary-orange);">Save Size</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
// Logic to handle the Form Submission
if (isset($_POST['save_size'])) {
    $name = $_POST['size_name'];
    $price = $_POST['extra_price'];
    $attr->addSize($name, $price);
    echo "<script>window.location.href='index.php?page=sizes';</script>";
}
?>