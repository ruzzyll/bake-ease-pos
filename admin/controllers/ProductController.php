<?php
// admin/controllers/ProductController.php

class ProductController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Fetch all active products for the Inventory table
     */
    public function getAllProducts() {
        $query = "SELECT p.*, c.category_name 
                  FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  WHERE p.status = 1
                  ORDER BY p.product_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetch a single product by ID
     */
    public function getProductById($id) {
        $query = "SELECT * FROM products WHERE product_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * ADD: Save a new product AND automatically create a default size entry
     */
    public function addProduct($data, $file) {
        $imageName = $this->handleImageUpload($file);
        
        $query = "INSERT INTO products (category_id, product_name, description, price, image, shelf_life_days, best_before, status) 
                  VALUES (:cat_id, :name, :desc, :price, :img, :shelf, :expiry, 1)";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cat_id', $data['category_id']);
        $stmt->bindParam(':name', $data['product_name']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':img', $imageName);
        $stmt->bindParam(':shelf', $data['shelf_life_days']);
        $stmt->bindParam(':expiry', $data['best_before']);
        
        if ($stmt->execute()) {
            // FIX: Auto-link to product_sizes table to prevent "Link Broken" error
            $sizeQuery = "INSERT INTO product_sizes (product_name, size_name, stock) VALUES (:name, 'Regular', 0)";
            $sizeStmt = $this->db->prepare($sizeQuery);
            $sizeStmt->bindParam(':name', $data['product_name']);
            return $sizeStmt->execute();
        }
        return false;
    }

    /**
     * UPDATE: Update product details including photo management
     */
    public function updateProduct($data, $file) {
        // If a new file is chosen, upload it. Otherwise, use the existing image name.
        $imageName = (!empty($file['name'])) ? $this->handleImageUpload($file) : $data['existing_image'];

        $query = "UPDATE products SET 
                  category_id = :cat_id,
                  product_name = :name, 
                  price = :price, 
                  description = :desc, 
                  image = :img,
                  shelf_life_days = :shelf,
                  best_before = :expiry 
                  WHERE product_id = :id";
        
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':cat_id', $data['category_id']);
        $stmt->bindParam(':name', $data['product_name']);
        $stmt->bindParam(':price', $data['price']);
        $stmt->bindParam(':desc', $data['description']);
        $stmt->bindParam(':img', $imageName);
        $stmt->bindParam(':shelf', $data['shelf_life_days']);
        $stmt->bindParam(':expiry', $data['best_before']);
        $stmt->bindParam(':id', $data['product_id']);
        
        return $stmt->execute();
    }

    /**
     * ARCHIVE: Soft Delete (Sets status to 0)
     */
    public function archiveProduct($id) {
        $query = "UPDATE products SET status = 0 WHERE product_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    /**
     * Fetch stock using product_name from the product_sizes table
     */
    public function getProductStock($product_name) {
        $query = "SELECT * FROM product_sizes WHERE product_name = :name";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $product_name);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Quick Update: Add quantity to existing stock
     */
    public function updateStock($product_name, $size_name, $quantity) {
        $query = "UPDATE product_sizes SET stock = stock + :qty 
                  WHERE product_name = :p_name AND size_name = :s_name";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':qty', $quantity);
        $stmt->bindParam(':p_name', $product_name);
        $stmt->bindParam(':s_name', $size_name);
        return $stmt->execute();
    }

    /**
     * Private helper to manage image movement
     */
    private function handleImageUpload($file) {
        if (empty($file['name'])) return 'placeholder.jpg';
        
        $targetDir = "../assets/uploads/";
        if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);
        
        // Use a unique name to prevent overwriting
        $fileName = time() . "_" . basename($file["name"]);
        $targetFilePath = $targetDir . $fileName;
        
        if (move_uploaded_file($file["tmp_name"], $targetFilePath)) {
            return $fileName;
        }
        return 'placeholder.jpg';
    }

    public function getLowStockReport() {
        $query = "SELECT product_name, size_name, stock FROM product_sizes WHERE stock < 5 ORDER BY stock ASC";
        return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
    }
}