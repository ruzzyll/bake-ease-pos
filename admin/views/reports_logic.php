<?php
/**
 * admin/views/reports_logic.php
 */

// 1. Calculate Growth
function calculateGrowth($current, $previous) {
    if ($previous <= 0) return $current > 0 ? 100 : 0;
    $growth = (($current - $previous) / $previous) * 100;
    return round($growth, 1);
}

// 2. Format Currency
function formatCurrency($amount) {
    return '₱' . number_format($amount, 2);
}

// 3. Determine Business Health Status
function getBusinessHealth($revenue) {
    if ($revenue >= 10000) return ['status' => 'Excellent', 'class' => 'success', 'icon' => 'fa-rocket'];
    if ($revenue >= 5000)  return ['status' => 'Good', 'class' => 'primary', 'icon' => 'fa-check-circle'];
    if ($revenue > 0)      return ['status' => 'Steady', 'class' => 'info', 'icon' => 'fa-minus-circle'];
    return ['status' => 'No Sales', 'class' => 'secondary', 'icon' => 'fa-bed']; // Fixed icon name
}

// 4. Get the most popular payment method
function getPaymentMethodStats($db) {
    try {
        // We ensure the column name in the result matches what the view expects
        $sql = "SELECT payment_method, COUNT(*) as usage_count 
                FROM orders 
                WHERE order_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
                GROUP BY payment_method 
                ORDER BY usage_count DESC LIMIT 1";
        
        $stmt = $db->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $row ?: ['payment_method' => 'None', 'usage_count' => 0];
    } catch (PDOException $e) {
        // If 'payment_method' doesn't exist, this prevents a crash
        return ['payment_method' => 'Column Error', 'usage_count' => 0];
    }
}

$months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
$currentMonth = $months[date('n') - 1];