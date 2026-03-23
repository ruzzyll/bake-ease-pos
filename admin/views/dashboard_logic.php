<?php
/**
 * Data Fetching for Bake Ease Dashboard
 */

// 1. Basic Stats
$today_sales = $reportCtrl->getTodaySales() ?? 0;
$orders_today_count = $reportCtrl->getTodayOrderCount() ?? 0;
$recent_orders = $reportCtrl->getRecentTransactions(5);
$avg_order_value = ($orders_today_count > 0) ? ($today_sales / $orders_today_count) : 0;

// 2. Pickup vs Delivery Split
try {
    $method_query = "SELECT order_method, COUNT(*) as count, SUM(total_amount) as total 
                     FROM orders 
                     WHERE order_status != 'Cancelled' AND DATE(order_date) = CURDATE()
                     GROUP BY order_method";
    $method_stmt = $db->prepare($method_query);
    $method_stmt->execute();
    $methods = $method_stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $method_data = ['Pickup' => ['count' => 0, 'total' => 0], 'Delivery' => ['count' => 0, 'total' => 0]];
    foreach($methods as $m) {
        if(isset($method_data[$m['order_method']])) { $method_data[$m['order_method']] = $m; }
    }
    $total_methods_count = array_sum(array_column($method_data, 'count')) ?: 1;
} catch (PDOException $e) { $method_data = []; }

// 3. Best Sellers (Based on order_items)
try {
    $best_query = "SELECT product_name, SUM(quantity) as total_qty, SUM(price_at_purchase * quantity) as revenue
                   FROM order_items
                   WHERE DATE(created_at) = CURDATE()
                   GROUP BY product_name
                   ORDER BY total_qty DESC LIMIT 5";
    $best_stmt = $db->prepare($best_query);
    $best_stmt->execute();
    $best_sellers = $best_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $best_sellers = []; }

// 4. MISSING LINK: Fulfillment Heatmap (Barangay Performance)
try {
    $geo_query = "SELECT barangay, COUNT(*) as volume, SUM(total_amount) as revenue 
                  FROM orders 
                  WHERE order_method = 'Delivery' AND barangay IS NOT NULL AND DATE(order_date) = CURDATE()
                  GROUP BY barangay ORDER BY volume DESC LIMIT 4";
    $geo_stmt = $db->prepare($geo_query);
    $geo_stmt->execute();
    $barangay_data = $geo_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $barangay_data = []; }

// 5. MISSING LINK: Purchase Intent (Occasions)
try {
    $reason_query = "SELECT reason_name, COUNT(*) as count 
                     FROM orders 
                     WHERE reason_name IS NOT NULL AND DATE(order_date) = CURDATE()
                     GROUP BY reason_name ORDER BY count DESC LIMIT 3";
    $reason_stmt = $db->prepare($reason_query);
    $reason_stmt->execute();
    $intent_data = $reason_stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $intent_data = []; }

// 6. MISSING LINK: Kitchen Status
try {
    $status_query = "SELECT order_status, COUNT(*) as count FROM orders 
                     WHERE DATE(order_date) = CURDATE() 
                     GROUP BY order_status";
    $status_stmt = $db->prepare($status_query);
    $status_stmt->execute();
    $status_counts = $status_stmt->fetchAll(PDO::FETCH_KEY_PAIR);
} catch (PDOException $e) { $status_counts = []; }
?>