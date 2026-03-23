<?php
require_once '../core/config/Database.php';
session_start();

if (isset($_GET['id']) && isset($_GET['status'])) {
    $database = new Database();
    $db = $database->getConnection();
    
    $id = $_GET['id'];
    $status = $_GET['status'];

    $query = "UPDATE orders SET order_status = ? WHERE order_id = ?";
    $stmt = $db->prepare($query);
    
    if ($stmt->execute([$status, $id])) {
        header("Location: index.php?page=order_list&msg=updated");
    } else {
        header("Location: index.php?page=order_list&msg=error");
    }
}