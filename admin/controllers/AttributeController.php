<?php
// admin/controllers/AttributeController.php

class AttributeController {
    private $db;

    /**
     * Constructor to initialize database connection
     */
    public function __construct($db) {
        $this->db = $db;
    }

    // ==========================================
    // SIZE MANAGEMENT METHODS
    // ==========================================

    /**
     * Fetch all available product sizes
     */
    public function getSizes() {
        $query = "SELECT * FROM sizes ORDER BY size_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a new size to the database
     */
    public function addSize($name, $price) {
        $query = "INSERT INTO sizes (size_name, extra_price) VALUES (:name, :price)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        return $stmt->execute();
    }

    /**
     * Remove a size by ID
     */
    public function deleteSize($id) {
        $query = "DELETE FROM sizes WHERE size_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }

    // ==========================================
    // ADD-ON MANAGEMENT METHODS
    // ==========================================

    /**
     * Fetch all available product add-ons
     */
    public function getAddons() {
        $query = "SELECT * FROM addons ORDER BY addon_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Add a new add-on (e.g., extra sprinkles, greeting card)
     */
    public function addAddon($name, $price) {
        $query = "INSERT INTO addons (addon_name, price) VALUES (:name, :price)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        return $stmt->execute();
    }

    /**
     * Remove an add-on by ID
     */
    public function deleteAddon($id) {
        $query = "DELETE FROM addons WHERE addon_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}