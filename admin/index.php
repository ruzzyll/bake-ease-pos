<?php
// admin/index.php

// 1. DATABASE: Go up one level to root, then into core/config
require_once '../core/config/Database.php'; 

// 2. CONTROLLER: It is in the SAME folder as index.php (inside admin/controllers)
require_once 'controllers/ReportController.php';

$database = new Database();
$db = $database->getConnection();

$report = new ReportController($db);

// Simple Routing
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bake Ease Admin</title>
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <style>
        :root { --primary-orange: #ff7a00; --sidebar-bg: #1e1e2d; }
        body { background-color: #f8f9fb; font-family: 'Segoe UI', sans-serif; }
        
        /* Sidebar Styling */
        .sidebar { width: 260px; height: 100vh; background: var(--sidebar-bg); position: fixed; color: #fff; }
        .sidebar .nav-link { color: #a2a3b7; padding: 15px 25px; transition: 0.3s; border-radius: 0; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background: rgba(255,122,0, 0.1); border-left: 4px solid var(--primary-orange); }
        .logo-area { padding: 30px 25px; font-size: 1.5rem; font-weight: bold; color: var(--primary-orange); }
        
        /* Main Content */
        .main-wrapper { margin-left: 260px; padding: 20px; }
        .top-nav { background: #fff; padding: 15px 30px; margin-bottom: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo-area">Bake Ease 🧁</div>
    <nav class="nav flex-column mt-2">
        <a class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">🏠 Dashboard</a>
        
        <div class="px-4 mt-3 mb-1 small text-uppercase text-muted" style="font-size: 0.65rem;">Sales & Orders</div>
        <a class="nav-link" href="../index.php">🛒 New Order (POS)</a>
        <a class="nav-link <?= $page == 'orders' ? 'active' : '' ?>" href="index.php?page=orders">📋 Order List</a>
        
        <div class="px-4 mt-3 mb-1 small text-uppercase text-muted" style="font-size: 0.65rem;">Inventory Management</div>
        <a class="nav-link <?= $page == 'inventory' ? 'active' : '' ?>" href="index.php?page=inventory">🎂 Products</a>
        <a class="nav-link <?= $page == 'sizes' ? 'active' : '' ?>" href="index.php?page=sizes">📏 Sizes</a>
        <a class="nav-link <?= $page == 'addons' ? 'active' : '' ?>" href="index.php?page=addons">🍓 Add-ons</a>
        
        <div class="px-4 mt-3 mb-1 small text-uppercase text-muted" style="font-size: 0.65rem;">People & Logistics</div>
        <a class="nav-link <?= $page == 'customers' ? 'active' : '' ?>" href="index.php?page=customers">👥 Customers</a>
        <a class="nav-link <?= $page == 'deliveries' ? 'active' : '' ?>" href="index.php?page=deliveries">🚚 Deliveries</a>
        
        <div class="px-4 mt-3 mb-1 small text-uppercase text-muted" style="font-size: 0.65rem;">Analytics</div>
        <a class="nav-link <?= $page == 'reports' ? 'active' : '' ?>" href="index.php?page=reports">📊 Reports</a>
    </nav>
</div>s
</div>

<div class="main-wrapper">
    <div class="top-nav d-flex justify-content-between align-items-center rounded">
        <h5 class="mb-0 fw-bold"><?= ucfirst($page) ?></h5>
        <div class="user-profile">Admin User 👤</div>
    </div>

    <?php 
// DYNAMICALLY LOAD THE PAGE
switch($page) {
    case 'inventory':
        include 'views/inventory.php';
        break;
        
    case 'orders':
        // You'll need an OrderController later to fetch $all_orders
        include 'views/orders.php';
        break;

    case 'sizes':
        include 'views/sizes.php';
        break;

    case 'addons':
        include 'views/addons.php';
        break;

    case 'customers':
        include 'views/customers.php';
        break;

    case 'deliveries':
        include 'views/deliveries.php';
        break;

    case 'reports':
        $today = date('Y-m-d');
        $sales = $report->getDailySales($today);
        $totalRevenue = $report->getTotalRevenue($today);
        include 'views/reports.php';
        break;

    case 'dashboard':
    default:
        $total_sales = $report->getTotalSales();
        $low_stock_count = $report->getLowStockCount();
        $recent_transactions = $report->getRecentTransactions();
        include 'views/dashboard.php';
        break;
}
?>
</div>

</body>
</html>