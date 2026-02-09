<?php
// checkout.php
header('Content-Type: application/json');
$host = "localhost"; $db_name = "bake-ease-pos"; $user = "root"; $pass = "";

try {
    $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $data = json_decode(file_get_contents('php://input'), true);

    if (empty($data['items'])) {
        echo json_encode(['success' => false, 'message' => 'Tray is empty']);
        exit;
    }

    $db->beginTransaction();

    // 1. Insert into orders table
    $stmt = $db->prepare("INSERT INTO orders (total_amount, order_date) VALUES (?, NOW())");
    $stmt->execute([$data['total']]);
    $orderId = $db->lastInsertId();

    // 2. Insert into order_items
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, size_name, quantity, price, best_before_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    foreach ($data['items'] as $item) {
        // Calculate best before based on current date + 3 days default
        $bbDate = date('Y-m-d', strtotime("+3 days")); 
        
        $itemStmt->execute([
            $orderId,
            $item['pid'],
            $item['name'],
            $item['sizeName'],
            $item['qty'],
            $item['unitPrice'],
            $bbDate
        ]);
        
        // 3. Update Stock in product_sizes
        $stockStmt = $db->prepare("UPDATE product_sizes SET stock = stock - ? WHERE product_name = ? AND size_name = ?");
        $stockStmt->execute([$item['qty'], $item['name'], $item['sizeName']]);
    }

    $db->commit();
    echo json_encode(['success' => true, 'order_id' => $orderId]);

} catch (Exception $e) {
    if (isset($db)) $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>