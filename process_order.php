<?php
header('Content-Type: application/json');

// Database Configuration
$host = "127.0.0.1"; $port = "3307"; $db_name = "bake-ease-pos"; $user = "root"; $pass = "";

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$db_name", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $data = json_decode(file_get_contents('php://input'), true);
    if (!$data || empty($data['items'])) throw new Exception("No order data received.");

    $db->beginTransaction();

    // 1. Handle Customer
    $stmt = $db->prepare("SELECT customer_id FROM customers WHERE full_name = ? AND contact_number = ? LIMIT 1");
    $stmt->execute([$data['name'], $data['phone']]);
    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($existing) {
        $customer_id = $existing['customer_id'];
        $db->prepare("UPDATE customers SET total_orders = total_orders + 1 WHERE customer_id = ?")->execute([$customer_id]);
    } else {
        $stmt = $db->prepare("INSERT INTO customers (full_name, contact_number, type_name, total_orders, created_at) VALUES (?, ?, ?, 1, NOW())");
        $stmt->execute([$data['name'], $data['phone'], $data['type']]);
        $customer_id = $db->lastInsertId();
    }

    // 2. Insert Main Order Header
    $location_display = ($data['method'] === 'Pickup') ? 'Store Pickup' : $data['address'];
    $firstItem = $data['items'][0];
    
    // Defaulting status to 'Preparing'
    $stmt = $db->prepare("INSERT INTO orders (
        customer_id, type_name, reason_name, location_name, address_details, 
        product_name, size_name, category, order_method, total_amount, 
        schedule_date, order_status, created_at, order_date
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Preparing', NOW(), NOW())");

    $stmt->execute([
        $customer_id, 
        $data['type'], 
        $data['reason'], 
        $location_display, 
        $location_display, 
        $firstItem['name'], 
        $firstItem['size'], 
        ($firstItem['category'] ?? 'General'), 
        $data['method'], 
        $data['total'], 
        $data['schedule']
    ]);
    $order_id = $db->lastInsertId();

    // 3. Process Items & Deduct Inventory
    $itemStmt = $db->prepare("INSERT INTO order_items (order_id, product_name, size_name, quantity, price_at_purchase, special_instructions) VALUES (?, ?, ?, ?, ?, ?)");
    
    /**
     * UPDATED DEDUCTION QUERY
     * Uses LIKE CONCAT(?, '%') so that a size of "6" matches "6\" Round (Small)"
     */
    $stockUpdate = $db->prepare("UPDATE product_sizes 
                                 SET stock = stock - ? 
                                 WHERE LOWER(TRIM(product_name)) = LOWER(TRIM(?)) 
                                 AND size_name LIKE CONCAT(?, '%')");

    foreach ($data['items'] as $item) {
        // A. Insert into order_items
        $itemStmt->execute([
            $order_id, 
            $item['name'], 
            $item['size'], 
            $item['qty'], 
            $item['unitPrice'],
            $item['note'] ?? ''
        ]);

        // B. Update stock using partial matching for size
        $stockUpdate->execute([
            $item['qty'],
            $item['name'],
            $item['size']
        ]);
        
        // Match check
        if ($stockUpdate->rowCount() === 0) {
            throw new Exception("Inventory Match Failed: Could not find Product '" . $item['name'] . "' with a size starting with '" . $item['size'] . "'.");
        }

        // C. Safety Check (using same LIKE logic)
        $check = $db->prepare("SELECT stock FROM product_sizes WHERE LOWER(TRIM(product_name)) = LOWER(TRIM(?)) AND size_name LIKE CONCAT(?, '%')");
        $check->execute([$item['name'], $item['size']]);
        $currentStock = $check->fetchColumn();

        if ($currentStock < 0) {
            throw new Exception("Insufficient stock: " . $item['name'] . " (" . $item['size'] . ")");
        }
    }

    $db->commit();
    echo json_encode(['success' => true, 'order_id' => $order_id]);

} catch (Exception $e) {
    if (isset($db) && $db->inTransaction()) $db->rollBack();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>