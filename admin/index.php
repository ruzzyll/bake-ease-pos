<?php
session_start();

// 1. Database & Controller Setup
$dbPath = __DIR__ . '/../core/config/Database.php';
if (file_exists($dbPath)) {
    require_once $dbPath;
} else {
    die("Database Configuration Not Found.");
}

try {
    $database = new Database();
    $db = $database->getConnection();
} catch (Exception $e) {
    die("Connection Error: " . $e->getMessage());
}

// 2. Load Controllers
require_once 'controllers/AttributeController.php';
require_once 'controllers/ProductController.php';
require_once 'controllers/ReportController.php'; 

$attr = new AttributeController($db);
$productCtrl = new ProductController($db);
$reportCtrl = new ReportController($db); 

// 3. Routing Logic
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bake Ease Admin | <?= ucfirst(str_replace('_', ' ', $page)) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        body { background-color: #f4f6f9; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; overflow-x: hidden; }
        .sidebar { min-height: 100vh; background: #1e1e2d; color: #a2a3b7; width: 255px; position: fixed; left: 0; top: 0; z-index: 1000; transition: all 0.3s; }
        .brand-section { padding: 25px; border-bottom: 1px solid rgba(255,255,255,0.05); }
        .brand-name { color: #ff7a00; font-weight: 800; font-size: 1.4rem; text-decoration: none; }
        .nav-section-title { padding: 15px 25px 5px; font-size: 0.65rem; text-transform: uppercase; font-weight: 700; color: #4c4e6f; letter-spacing: 1px; }
        .sidebar .nav-link { color: #a2a3b7; padding: 12px 25px; font-size: 0.85rem; display: flex; align-items: center; transition: 0.2s; text-decoration: none; border-left: 3px solid transparent; }
        .sidebar .nav-link i { width: 20px; margin-right: 10px; }
        .sidebar .nav-link:hover { color: #ffffff; background: rgba(255,255,255,0.03); }
        .sidebar .nav-link.active { background: #2b2b40; color: #ff7a00; font-weight: 600; border-left-color: #ff7a00; }
        
        .main-content { margin-left: 255px; padding: 25px; width: calc(100% - 255px); min-height: 100vh; transition: all 0.3s; }
        .top-bar { background: white; padding: 15px 25px; border-radius: 12px; margin-bottom: 25px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
        
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f1f1; }
        ::-webkit-scrollbar-thumb { background: #ff7a00; border-radius: 10px; }
    </style>
</head>
<body>

<div class="d-flex">
    <div class="sidebar">
        <div class="brand-section">
            <a href="index.php" class="brand-name">Bake Ease 🧁</a>
        </div>
        
        <div class="nav-section-title">Analytics</div>
        <ul class="nav flex-column mt-2">
            <li class="nav-item">
                <a class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                    <i class="fas fa-home"></i> Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $page == 'reports' ? 'active' : '' ?>" href="index.php?page=reports">
                    <i class="fas fa-chart-line"></i> Reports
                </a>
            </li>
        </ul>

        <div class="nav-section-title">Sales & Orders</div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link" href="../index.php"><i class="fas fa-shopping-cart"></i> New Order (POS)</a></li>
            <li class="nav-item">
                <a class="nav-link <?= $page == 'order_list' ? 'active' : '' ?>" href="index.php?page=order_list">
                    <i class="fas fa-list-check"></i> Order List
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?= $page == 'kitchen_view' ? 'active' : '' ?>" href="index.php?page=kitchen_view">
                    <i class="fas fa-utensils"></i> Kitchen Queue
                </a>
            </li>
        </ul>

        <div class="nav-section-title">Inventory Management</div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= $page == 'inventory' ? 'active' : '' ?>" href="index.php?page=inventory"><i class="fas fa-cake-candles"></i> Products</a></li>
            <li class="nav-item"><a class="nav-link <?= $page == 'sizes' ? 'active' : '' ?>" href="index.php?page=sizes"><i class="fas fa-ruler-combined"></i> Sizes</a></li>
            <li class="nav-item"><a class="nav-link <?= $page == 'addons' ? 'active' : '' ?>" href="index.php?page=addons"><i class="fas fa-ice-cream"></i> Add-ons</a></li>
        </ul>

        <div class="nav-section-title">People & Logistics</div>
        <ul class="nav flex-column">
            <li class="nav-item"><a class="nav-link <?= $page == 'customers' ? 'active' : '' ?>" href="index.php?page=customers"><i class="fas fa-users"></i> Customers</a></li>
            <li class="nav-item"><a class="nav-link <?= $page == 'deliveries' ? 'active' : '' ?>" href="index.php?page=deliveries"><i class="fas fa-truck"></i> Deliveries</a></li>
        </ul>
    </div>

    <div class="main-content">
        <header class="top-bar">
            <h5 class="mb-0 fw-bold" style="color: #1e1e2d;">
                <?php 
                    if($page == 'addons') echo "Add-ons";
                    elseif($page == 'add_product') echo "Add New Product";
                    elseif($page == 'edit_product') echo "Edit Product Details";
                    elseif($page == 'order_list') echo "Order List";
                    elseif($page == 'kitchen_view') echo "Kitchen Queue";
                    elseif($page == 'customers') echo "Customer Directory";
                    elseif($page == 'deliveries') echo "Delivery Dispatch";
                    else echo ucfirst(str_replace('_', ' ', $page));
                ?>
            </h5>
            <div class="d-flex align-items-center">
                <span class="small fw-semibold text-muted me-3">Admin User 👤</span>
                <a href="logout.php" class="btn btn-sm btn-outline-danger border-0"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </header>

        <div class="container-fluid p-0">
            <?php 
            switch ($page) {
                case 'dashboard':
                    if (file_exists('views/dashboard.php')) include 'views/dashboard.php';
                    break;
                
                case 'inventory':
                    if (file_exists('views/inventory.php')) include 'views/inventory.php';
                    break;

                case 'order_list':
                    if (file_exists('views/order_list.php')) include 'views/order_list.php';
                    break;

                case 'kitchen_view':
                    if (file_exists('views/kitchen_view.php')) include 'views/kitchen_view.php';
                    break;

                case 'reports':
                    // These variables are now available for reports.php
                    $todaySales = $reportCtrl->getTodaySales(); 
                    if (file_exists('views/reports.php')) include 'views/reports.php';
                    break;

                case 'add_product':
                    if (file_exists('views/add_product.php')) include 'views/add_product.php';
                    break;

                case 'edit_product':
                    if (file_exists('views/edit_product.php')) include 'views/edit_product.php';
                    break;

                case 'sizes':
                    if (file_exists('views/sizes.php')) include 'views/sizes.php';
                    break;

                case 'addons':
                    if (file_exists('views/addons.php')) include 'views/addons.php';
                    break;

                case 'customers':
                    if (file_exists('views/customer_list.php')) include 'views/customer_list.php';
                    break;

                case 'deliveries':
                    if (file_exists('views/deliveries.php')) include 'views/deliveries.php';
                    break;

                default:
                    echo "<div class='alert alert-light border shadow-sm' style='border-radius:10px;'>
                            <i class='fas fa-info-circle me-2'></i> Section under development.
                          </div>";
                    break;
            }
            ?>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>