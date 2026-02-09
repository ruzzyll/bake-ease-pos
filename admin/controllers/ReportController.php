<?php
class ReportController {
    private $db;

    public function __construct($db) { $this->db = $db; }

    // Get Total Sales Amount
    public function getTotalSales() {
        $query = "SELECT SUM(total_amount) as total FROM orders";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    // Get Count of Low Stock Items (less than 5)
    public function getLowStockCount() {
        $query = "SELECT COUNT(*) as low_count FROM product_sizes WHERE stock < 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['low_count'] ?? 0;
    }

    // Get Recent Transactions
    public function getRecentTransactions($limit = 5) {
        $query = "SELECT * FROM orders ORDER BY order_date DESC LIMIT ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$limit]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}