<?php
/**
 * views/edit_product.php
 * Handles product detail updates and image replacement
 */

require_once 'controllers/ProductController.php';
$productCtrl = new ProductController($db);

$id = $_GET['id'] ?? null;
$p = $productCtrl->getProductById($id);

if (!$p) {
    echo "<div class='alert alert-danger'>Product not found.</div>";
    return;
}

// Fetch categories for the dropdown
$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

// Handle the Update Form Submission
if (isset($_POST['update_product'])) {
    // Note: $_FILES['image'] is passed to the controller
    if ($productCtrl->updateProduct($_POST, $_FILES['image'])) {
        echo "<script>alert('Update successful!'); window.location.href='index.php?page=inventory';</script>";
        exit();
    }
}
?>

<div class="container-fluid p-4">
    <div class="d-flex align-items-center mb-4">
        <a href="index.php?page=inventory" class="btn btn-light border-0 shadow-sm me-3" style="border-radius: 10px;">
            <i class="fas fa-arrow-left"></i>
        </a>
        <h4 class="fw-bold mb-0">Edit Product: <?= htmlspecialchars($p['product_name']) ?></h4>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 20px;">
        <form method="POST" enctype="multipart/form-data" class="p-4">
            <input type="hidden" name="product_id" value="<?= $p['product_id'] ?>">
            <input type="hidden" name="existing_image" value="<?= $p['image'] ?>">
            
            <div class="row">
                <div class="col-md-4 text-center border-end">
                    <label class="form-label fw-bold d-block mb-3">Product Photo</label>
                    <div class="mb-3 position-relative d-inline-block">
                        <img id="imgPreview" src="../assets/uploads/<?= htmlspecialchars($p['image']) ?>" 
                             onerror="this.src='../assets/uploads/placeholder.jpg'"
                             class="shadow-sm"
                             style="width: 200px; height: 200px; object-fit: cover; border-radius: 20px; border: 4px solid #f8f9fa;">
                    </div>
                    <div class="px-3">
                        <input type="file" name="image" id="fileInput" class="form-control form-control-sm" accept="image/*">
                        <small class="text-muted d-block mt-2">Recommended: Square image (1:1), Max 2MB</small>
                    </div>
                </div>

                <div class="col-md-8 px-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Product Name</label>
                            <input type="text" name="product_name" class="form-control shadow-sm" value="<?= htmlspecialchars($p['product_name']) ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Category</label>
                            <select name="category_id" class="form-select shadow-sm">
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?= $cat['category_id'] ?>" <?= $p['category_id'] == $cat['category_id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($cat['category_name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Description</label>
                            <textarea name="description" class="form-control shadow-sm" rows="3"><?= htmlspecialchars($p['description']) ?></textarea>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Base Price (₱)</label>
                            <input type="number" step="0.01" name="price" class="form-control shadow-sm" value="<?= $p['price'] ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Shelf Life (Days)</label>
                            <input type="number" name="shelf_life_days" class="form-control shadow-sm" value="<?= $p['shelf_life_days'] ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label small fw-bold text-muted text-uppercase">Best Before</label>
                            <input type="date" name="best_before" class="form-control shadow-sm" value="<?= $p['best_before'] ?>">
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <button type="submit" name="update_product" class="btn px-5 py-2 fw-bold" style="background: #ff7a00; color: white; border-radius: 12px;">
                            Save Changes
                        </button>
                        <a href="index.php?page=inventory" class="btn btn-light px-4 py-2 ms-2" style="border-radius: 12px;">Cancel</a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
    // Live Image Preview Logic
    document.getElementById('fileInput').onchange = evt => {
        const [file] = document.getElementById('fileInput').files
        if (file) {
            document.getElementById('imgPreview').src = URL.createObjectURL(file)
        }
    }
</script>