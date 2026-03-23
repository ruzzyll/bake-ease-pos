<?php
require_once 'controllers/ProductController.php';
$productCtrl = new ProductController($db);

// Fetch categories for the dropdown
$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['save_product'])) {
    if ($productCtrl->addProduct($_POST, $_FILES['image'])) {
        echo "<script>alert('Product added successfully!'); window.location.href='index.php?page=inventory';</script>";
    }
}
?>

<div class="container py-4">
    <div class="card border-0 shadow-sm" style="border-radius: 15px;">
        <div class="card-header bg-white p-4 border-0">
            <h4 class="fw-bold mb-0">Add New Product 🆕</h4>
        </div>
        <form method="POST" enctype="multipart/form-data" class="card-body p-4">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small">PRODUCT NAME</label>
                    <input type="text" name="product_name" class="form-control" placeholder="e.g., Chocolate Cake" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold small">CATEGORY</label>
                    <select name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?= $cat['category_id'] ?>"><?= $cat['category_name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-12 mb-3">
                    <label class="form-label fw-bold small">DESCRIPTION</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Describe the product ingredients or features..."></textarea>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold small">PRICE (₱)</label>
                    <input type="number" step="0.01" name="price" class="form-control" required>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold small">SHELF LIFE (DAYS)</label>
                    <input type="number" name="shelf_life_days" class="form-control">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label fw-bold small">BEST BEFORE (OPTIONAL)</label>
                    <input type="date" name="best_before" class="form-control">
                </div>
                <div class="col-md-12 mb-4">
                    <label class="form-label fw-bold small">PRODUCT IMAGE</label>
                    <input type="file" name="image" class="form-control" accept="image/*" required>
                </div>
            </div>
            <div class="d-flex justify-content-end">
                <a href="index.php?page=inventory" class="btn btn-light me-2 fw-bold">Cancel</a>
                <button type="submit" name="save_product" class="btn btn-warning text-white fw-bold px-5" style="background:#ff7a00;">Save Product</button>
            </div>
        </form>
    </div>
</div>