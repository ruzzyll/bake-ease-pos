<?php
// admin/controllers/AttributeController.php

class AttributeController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ==========================================
    // SIZE MANAGEMENT METHODS
    // ==========================================

    /**
     * Fetch all sizes ordered by category and name
     */
    public function getSizes() {
        $query = "SELECT * FROM sizes ORDER BY category ASC, size_name ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ADD: New size attribute
     */
    public function addSize($name, $price, $category) {
        $query = "INSERT INTO sizes (size_name, extra_price, category) VALUES (:name, :price, :category)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category', $category);
        return $stmt->execute();
    }

    /**
     * UPDATE: Existing size attribute
     */
    public function updateSize($id, $name, $price, $category) {
        $query = "UPDATE sizes SET size_name = :name, extra_price = :price, category = :category WHERE size_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':category', $category);
        return $stmt->execute();
    }

    /**
     * DELETE: Remove size attribute
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
     * Fetch all add-ons
     */
    public function getAddons() {
        $query = "SELECT * FROM addons ORDER BY addon_id DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * ADD: New Add-on (Toppings, Candles, etc.)
     */
    public function addAddon($name, $price, $cat_id) {
        $query = "INSERT INTO addons (addon_name, price, addon_category_id) VALUES (:name, :price, :cat_id)";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':cat_id', $cat_id);
        return $stmt->execute();
    }

    /**
     * UPDATE: Existing Add-on
     */
    public function updateAddon($id, $name, $price, $cat_id) {
        $query = "UPDATE addons SET addon_name = :name, price = :price, addon_category_id = :cat_id WHERE addon_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':price', $price);
        $stmt->bindParam(':cat_id', $cat_id);
        return $stmt->execute();
    }

    /**
     * DELETE: Remove Add-on
     */
    public function deleteAddon($id) {
        $query = "DELETE FROM addons WHERE addon_id = :id";
        $stmt = $this->db->prepare($query);
        $stmt->bindParam(':id', $id);
        return $stmt->execute();
    }
}