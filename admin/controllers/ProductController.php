<?php
// admin/controllers/ProductController.php

class ProductController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // Fetch all products with their categories
    public function getAllProducts() {
        $query = "SELECT p.*, c.category_name FROM products p 
                  LEFT JOIN categories c ON p.category_id = c.category_id 
                  ORDER BY p.product_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Fetch stock details for a specific product
    

    // Update Stock (Add or Subtract)
    public function updateStock($product_id, $size_id, $quantity) {
    // Corrected column names to id_product and id_size
    $query = "UPDATE product_sizes SET stock = stock + :qty 
              WHERE id_product = :p_id AND id_size = :s_id";
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':qty', $quantity);
    $stmt->bindParam(':p_id', $product_id);
    $stmt->bindParam(':s_id', $size_id);
    return $stmt->execute();
}
    // admin/controllers/ProductController.php

// admin/controllers/ProductController.php

public function getProductStock($product_id) {
    // We will use the most likely standard names. 
    // If 'product_id' failed and 'id_product' failed, 
    // check if your SQL used 'prod_id' or 'item_id'.
    
    $query = "SELECT ps.*, s.size_name 
              FROM product_sizes ps 
              JOIN sizes s ON ps.size_id = s.size_id 
              WHERE ps.product_id = :id"; // Changing back to standard
              
    $stmt = $this->db->prepare($query);
    $stmt->bindParam(':id', $product_id, PDO::PARAM_INT);
    
    try {
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // This will print the error but also the table structure to help us!
        die("Database Error: " . $e->getMessage() . " <br> Please check column names in 'product_sizes' table.");
    }
}
    // Fetch only items where stock is below 5
    public function getLowStockReport() {
    $query = "SELECT p.product_name, s.size_name, ps.stock 
              FROM product_sizes ps
              JOIN products p ON ps.id_product = p.product_id
              JOIN sizes s ON ps.id_size = s.size_id
              WHERE ps.stock < 5
              ORDER BY ps.stock ASC";
    $stmt = $this->db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
}