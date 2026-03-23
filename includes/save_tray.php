<?php
session_start();
$data = json_decode(file_get_contents('php://input'), true);

if (isset($data['items'])) {
    $_SESSION['customer_tray'] = $data['items'];
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false]);
}
?>