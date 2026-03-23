<?php
/**
 * admin/includes/sidebar.php
 * Final Polished Sidebar for Bake Ease Admin
 */
?>
<div class="sidebar">
    <div class="brand-section">
        <a href="index.php?page=dashboard" class="brand-name">Bake Ease 🧁</a>
    </div>
    
    <ul class="nav flex-column mt-2">
        <li class="nav-item">
            <a class="nav-link <?= $page == 'dashboard' ? 'active' : '' ?>" href="index.php?page=dashboard">
                <span class="nav-icon">🏠</span> Dashboard
            </a>
        </li>
    </ul>

    <div class="nav-section-title">Sales & Orders</div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link" href="../index.php">
                <span class="nav-icon">🛒</span> New Order (POS)
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $page == 'order_list' ? 'active' : '' ?>" href="index.php?page=order_list">
                <span class="nav-icon">📋</span> Order List
            </a>
        </li>
    </ul>

    <div class="nav-section-title">Inventory Management</div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= ($page == 'inventory' || $page == 'edit_product') ? 'active' : '' ?>" href="index.php?page=inventory">
                <span class="nav-icon">🎂</span> Products
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $page == 'sizes' ? 'active' : '' ?>" href="index.php?page=sizes">
                <span class="nav-icon">📏</span> Sizes
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $page == 'add_ons' ? 'active' : '' ?>" href="index.php?page=add_ons">
                <span class="nav-icon">🍓</span> Add-ons
            </a>
        </li>
    </ul>

    <div class="nav-section-title">People & Logistics</div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= $page == 'customers' ? 'active' : '' ?>" href="index.php?page=customers">
                <span class="nav-icon">👥</span> Customers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link <?= $page == 'deliveries' ? 'active' : '' ?>" href="index.php?page=deliveries">
                <span class="nav-icon">🚚</span> Deliveries
            </a>
        </li>
    </ul>

    <div class="nav-section-title">Analytics</div>
    <ul class="nav flex-column">
        <li class="nav-item">
            <a class="nav-link <?= $page == 'reports' ? 'active' : '' ?>" href="index.php?page=reports">
                <span class="nav-icon">📈</span> Reports
            </a>
        </li>
    </ul>
</div>

<style>
/* SIDEBAR POLISHED STYLES 
   Matching the Dark Navy / Orange theme
*/
.sidebar { 
    min-height: 100vh; 
    background: #1e1e2d; /* Dark Navy */
    color: #a2a3b7; 
    width: 255px; 
    position: fixed; 
    top: 0;
    left: 0;
    z-index: 1000;
    box-shadow: 4px 0 10px rgba(0,0,0,0.1);
}

.brand-section { 
    padding: 25px; 
    display: flex; 
    align-items: center;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}

.brand-name { 
    color: #ff7a00; /* Signature Orange */
    font-weight: 800; 
    font-size: 1.5rem; 
    text-decoration: none; 
    letter-spacing: -0.5px;
}

.brand-name:hover {
    color: #ff9d47;
}

.nav-section-title { 
    padding: 20px 25px 8px; 
    font-size: 0.68rem; 
    text-transform: uppercase; 
    font-weight: 700; 
    color: #4c4e6f; 
    letter-spacing: 1.2px;
}

.sidebar .nav-link {
    color: #a2a3b7;
    padding: 12px 25px;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    transition: all 0.2s ease;
    text-decoration: none;
    border-left: 4px solid transparent;
}

.sidebar .nav-link:hover { 
    color: #ffffff; 
    background: rgba(255,255,255,0.04); 
}

.sidebar .nav-link.active {
    background: #2b2b40;
    color: #ff7a00; 
    font-weight: 600;
    border-left-color: #ff7a00;
}

.nav-icon {
    margin-right: 12px;
    font-size: 1.1rem;
    display: inline-block;
    width: 20px;
    text-align: center;
}

/* Ensure emojis don't get washed out by link colors */
.nav-link.active .nav-icon {
    filter: drop-shadow(0 0 2px rgba(255,122,0,0.3));
}
</style>