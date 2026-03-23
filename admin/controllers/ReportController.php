<?php
class ReportController {
    private $db;

    public function __construct($db) { $this->db = $db; }

    /** 1. DASHBOARD SNAPSHOTS (Curdate Only) **/
    
    public function getTodaySales() {
        $query = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = CURDATE() AND order_status = 'Completed'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'] ?? 0;
    }

    public function getTodayOrderCount() {
        $query = "SELECT COUNT(*) as order_count FROM orders WHERE DATE(order_date) = CURDATE() AND order_status = 'Completed'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['order_count'] ?? 0;
    }

    // NEW: Fix for "Items Sold Today"
    public function getTodayItemsSold() {
        $query = "SELECT COUNT(*) as items_count FROM orders WHERE DATE(order_date) = CURDATE() AND order_status = 'Completed'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['items_count'] ?? 0;
    }

    // NEW: Fix for Trend Indicator (% growth vs yesterday)
    public function getDoDGrowth() {
        $today = $this->getTodaySales();
        $query = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = SUBDATE(CURDATE(), 1) AND order_status = 'Completed'";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        $yesterday = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;

        if ($yesterday <= 0) return ($today > 0) ? 100 : 0;
        return (($today - $yesterday) / $yesterday) * 100;
    }

    /** 2. ENHANCED SALES PERFORMANCE (Filtered) **/

    public function getTopProductsFiltered($start, $end, $limit = 5) {
        $query = "SELECT product_name, SUM(total_amount) as total_revenue, COUNT(*) as times_sold
                  FROM orders 
                  WHERE DATE(order_date) BETWEEN :start AND :end 
                  AND order_status = 'Completed'
                  GROUP BY product_name 
                  ORDER BY total_revenue DESC LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':start', $start);
        $stmt->bindValue(':end', $end);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFastMovingProducts($start, $end) {
        $query = "SELECT product_name, COUNT(*) as volume, SUM(total_amount) as revenue 
                  FROM orders WHERE DATE(order_date) BETWEEN :start AND :end 
                  AND order_status = 'Completed' GROUP BY product_name ORDER BY volume DESC LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSlowMovingProducts($start, $end) {
        $query = "SELECT product_name, COUNT(*) as volume, SUM(total_amount) as revenue 
                  FROM orders WHERE DATE(order_date) BETWEEN :start AND :end 
                  AND order_status = 'Completed' GROUP BY product_name ORDER BY volume ASC LIMIT 5";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSizePerformance($start, $end) {
        $query = "SELECT size_name, COUNT(*) as count, SUM(total_amount) as revenue 
                  FROM orders WHERE DATE(order_date) BETWEEN :start AND :end 
                  AND order_status = 'Completed' AND size_name IS NOT NULL
                  GROUP BY size_name ORDER BY revenue DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAddonPerformance($start, $end) {
        $query = "SELECT addons FROM orders WHERE DATE(order_date) BETWEEN :start AND :end 
                  AND order_status = 'Completed' AND addons IS NOT NULL AND addons != 'None' AND addons != ''";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $counts = [];
        foreach ($rows as $row) {
            $items = explode(',', $row['addons']);
            foreach ($items as $item) {
                $name = trim($item);
                if($name != "" && $name != "None") {
                    $counts[$name] = ($counts[$name] ?? 0) + 1;
                }
            }
        }
        arsort($counts);
        return array_slice($counts, 0, 10);
    }

    /** 3. CUSTOMER BEHAVIOR & TYPE ANALYSIS **/

    public function getCustomerTypeOverTime($start, $end) {
        $query = "SELECT type_name, DATE(order_date) as date, COUNT(*) as count 
                  FROM orders WHERE DATE(order_date) BETWEEN :start AND :end 
                  AND order_status = 'Completed' GROUP BY type_name, DATE(order_date)";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getOrdersByHour($start, $end) {
        $query = "SELECT HOUR(order_date) as hr, COUNT(*) as order_count, SUM(total_amount) as revenue
                  FROM orders WHERE DATE(order_date) BETWEEN :start AND :end 
                  AND order_status = 'Completed' GROUP BY hr ORDER BY hr ASC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** 4. GEOGRAPHIC ANALYSIS (CDO) **/

    public function getBarangayCustomerAnalysis() {
        $query = "SELECT barangay, type_name, COUNT(*) as count, SUM(total_amount) as revenue
                  FROM orders WHERE order_status = 'Completed' AND barangay IS NOT NULL 
                  GROUP BY barangay, type_name ORDER BY barangay ASC, count DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBarangayHeatmap() {
        $query = "SELECT barangay, SUM(total_amount) as total_revenue, COUNT(*) as order_count
                  FROM orders WHERE order_status = 'Completed' AND barangay IS NOT NULL AND barangay != ''
                  GROUP BY barangay ORDER BY total_revenue DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /** 5. DETAILED LIST & FULFILLMENT **/

    public function getFilteredOrderList($start, $end) {
        $query = "SELECT o.*, c.full_name 
                  FROM orders o 
                  LEFT JOIN customers c ON o.customer_id = c.customer_id
                  WHERE DATE(o.order_date) BETWEEN :start AND :end 
                  AND o.order_status = 'Completed' 
                  ORDER BY o.order_date DESC";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // NEW: Fix for Recent Activity Table on Dashboard
    public function getRecentTransactions($limit = 5) {
        $query = "SELECT order_id, order_method, order_status, total_amount 
                  FROM orders 
                  ORDER BY order_date DESC LIMIT :limit";
        $stmt = $this->db->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getFulfillmentDistribution($start, $end) {
        $query = "SELECT order_method, COUNT(*) as count 
                  FROM orders 
                  WHERE DATE(order_date) BETWEEN :start AND :end 
                  AND order_status = 'Completed'
                  GROUP BY order_method";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getSalesByCategoryFiltered($start, $end) {
        $query = "SELECT category, SUM(total_amount) as revenue FROM orders 
                  WHERE DATE(order_date) BETWEEN :start AND :end AND order_status = 'Completed' GROUP BY category";
        $stmt = $this->db->prepare($query);
        $stmt->execute(['start' => $start, 'end' => $end]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}