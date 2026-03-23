<?php
/**
 * views/sizes.php
 * Logic and UI for Size Management
 */

// Note: $db is inherited from index.php
require_once 'controllers/AttributeController.php';
$attr = new AttributeController($db);

// --- FORM HANDLING LOGIC ---

// 1. Handle ADD New Size
if (isset($_POST['save_size'])) {
    $name = $_POST['size_name'];
    $price = $_POST['extra_price'];
    $category = $_POST['category'];
    $attr->addSize($name, $price, $category);
    echo "<script>window.location.href='index.php?page=sizes';</script>";
}

// 2. Handle UPDATE Existing Size
if (isset($_POST['update_size'])) {
    $id = $_POST['size_id'];
    $name = $_POST['size_name'];
    $price = $_POST['extra_price'];
    $category = $_POST['category'];
    $attr->updateSize($id, $name, $price, $category);
    echo "<script>window.location.href='index.php?page=sizes';</script>";
}

// 3. Handle DELETE Logic
if (isset($_GET['delete_size'])) {
    $attr->deleteSize($_GET['delete_size']);
    echo "<script>window.location.href='index.php?page=sizes';</script>";
}

// Fetch all sizes for display
$sizes = $attr->getSizes();
?>

<div class="card border-0 shadow-sm" style="border-radius: 15px;">
    <div class="card-header bg-white p-4 border-0 d-flex justify-content-between align-items-center">
        <h5 class="fw-bold mb-0">Manage Sizes 📏</h5>
        <button class="btn btn-warning text-white fw-bold px-4" 
                style="background: #ff7a00; border:none;" 
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
                    <th>Category</th>
                    <th>Extra Price</th>
                    <th class="text-center">Action</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sizes)): ?>
                    <tr><td colspan="4" class="text-center text-muted p-5">No sizes found in the database.</td></tr>
                <?php else: foreach($sizes as $s): ?>
                <tr>
                    <td class="fw-bold"><?= htmlspecialchars($s['size_name']) ?></td>
                    <td>
                        <span class="badge bg-light text-dark border fw-normal">
                            <?= htmlspecialchars($s['category']) ?>
                        </span>
                    </td>
                    <td>₱<?= number_format($s['extra_price'], 2) ?></td>
                    <td class="text-center">
                        <button class="btn btn-sm btn-light border me-1" 
                                data-bs-toggle="modal" 
                                data-bs-target="#editModal<?= $s['size_id'] ?>">✏️</button>
                        
                        <a href="index.php?page=sizes&delete_size=<?= $s['size_id'] ?>" 
                           class="btn btn-sm btn-light border text-danger" 
                           onclick="return confirm('Are you sure you want to delete this size?')">🗑️</a>
                    </td>
                </tr>

                <div class="modal fade" id="editModal<?= $s['size_id'] ?>" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow" style="border-radius: 15px;">
                            <form method="POST">
                                <input type="hidden" name="size_id" value="<?= $s['size_id'] ?>">
                                <div class="modal-header border-0 p-4 pb-0">
                                    <h5 class="modal-title fw-bold">Edit Size</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-4">
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">SIZE NAME</label>
                                        <input type="text" name="size_name" class="form-control" value="<?= htmlspecialchars($s['size_name']) ?>" required>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">CATEGORY</label>
                                        <select name="category" class="form-select" required>
                                            <option value="Cake" <?= $s['category'] == 'Cake' ? 'selected' : '' ?>>Cake</option>
                                            <option value="Individual" <?= $s['category'] == 'Individual' ? 'selected' : '' ?>>Individual</option>
                                            <option value="Bilao" <?= $s['category'] == 'Bilao' ? 'selected' : '' ?>>Bilao</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label text-muted small fw-bold">EXTRA PRICE (₱)</label>
                                        <input type="number" step="0.01" name="extra_price" class="form-control" value="<?= $s['extra_price'] ?>" required>
                                    </div>
                                </div>
                                <div class="modal-footer border-0 p-4 pt-0">
                                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                                    <button type="submit" name="update_size" class="btn btn-warning text-white fw-bold px-4" style="background: #ff7a00;">Update Size</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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
                        <label class="form-label text-muted small fw-bold">SIZE NAME</label>
                        <input type="text" name="size_name" class="form-control" placeholder="e.g. 8-inch Round" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">CATEGORY</label>
                        <select name="category" class="form-select" required>
                            <option value="Cake">Cake</option>
                            <option value="Individual">Individual</option>
                            <option value="Bilao">Bilao</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">EXTRA PRICE (₱)</label>
                        <input type="number" step="0.01" name="extra_price" class="form-control" placeholder="0.00" required>
                    </div>
                </div>
                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" name="save_size" class="btn btn-warning text-white fw-bold px-4" style="background: #ff7a00;">Save Size</button>
                </div>
            </form>
        </div>
    </div>
</div>