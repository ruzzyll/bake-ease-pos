<?php
// 1. Database Connection
$host = "localhost"; $db_name = "bake-ease-pos"; $user = "root"; $pass = "";
try {
    $db = new PDO("mysql:host=$host;dbname=$db_name", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) { die("Connection Error: " . $e->getMessage()); }

// 2. Fetch Data
$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$addons = $db->query("SELECT * FROM addons")->fetchAll(PDO::FETCH_ASSOC);

// 3. FIXED SQL: Joining by product_name to avoid "Column not found" error
$query = "SELECT p.*, 
          GROUP_CONCAT(CONCAT(s.size_name, ':', s.extra_price, ':', ps.stock) SEPARATOR '|') as size_data
          FROM products p
          LEFT JOIN product_sizes ps ON p.product_name = ps.product_name
          LEFT JOIN sizes s ON s.size_name = ps.size_name
          GROUP BY p.product_id";
$products = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bake Ease POS</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        :root { --primary: #ff7a00; --soft-bg: #f8f9fb; }
        body { background: var(--soft-bg); font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        
        /* Layout */
        .main-content { padding: 20px; margin-right: 420px; }
        .cart-tray { width: 400px; background: #fff; height: 100vh; position: fixed; right: 0; top: 0; padding: 20px; border-left: 1px solid #eee; display: flex; flex-direction: column; z-index: 1000; }

        /* Tabs */
        .category-tabs { display: flex; gap: 10px; overflow-x: auto; padding-bottom: 15px; scrollbar-width: none; }
        .cat-btn { border: 1px solid #ddd; background: #fff; padding: 8px 22px; border-radius: 50px; white-space: nowrap; font-weight: 600; transition: 0.3s; }
        .cat-btn.active { background: var(--primary); color: #fff; border-color: var(--primary); }

        /* Product Cards */
        .food-card { border-radius: 18px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; height: 100%; transition: transform 0.2s; }
        .food-card:hover { transform: translateY(-5px); }
        .img-box { height: 160px; background: #ececec; position: relative; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        
        .status-badge { position: absolute; top: 12px; right: 12px; font-size: 0.7rem; padding: 4px 10px; border-radius: 8px; font-weight: bold; }

        /* Selectors */
        .size-btn { border: 1px solid #ddd; background: #fff; font-size: 0.75rem; padding: 6px 12px; border-radius: 8px; margin: 2px; cursor: pointer; }
        .size-btn.active { background: #333; color: #fff; border-color: #333; }
        
        /* Tray Qty Control */
        .qty-ctrl { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        .qty-btn { width: 28px; height: 28px; border: 1px solid #ddd; background: #fff; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; font-weight: bold; }
    </style>
</head>
<body>

<main class="main-content">
    <h2 class="fw-bold mb-4">Bake Ease POS 🧁</h2>

    <div class="category-tabs">
        <button class="cat-btn active" onclick="filterMenu('all', this)">All</button>
        <?php foreach($categories as $c): ?>
            <button class="cat-btn" onclick="filterMenu(<?= $c['category_id'] ?>, this)"><?= htmlspecialchars($c['category_name']) ?></button>
        <?php endforeach; ?>
    </div>

    <div class="row g-4">
        <?php foreach($products as $p): 
            $bbDate = date('M d, Y', strtotime("+" . ($p['shelf_life_days'] ?? 3) . " days")); 
            $sizes = !empty($p['size_data']) ? explode('|', $p['size_data']) : [];
            $totalStock = 0;
            foreach($sizes as $sz) { $totalStock += (int)(explode(':', $sz)[2] ?? 0); }
        ?>
        <div class="col-md-6 col-lg-4 product-item" data-category="<?= $p['category_id'] ?>">
            <div class="card food-card">
                <div class="img-box">
                    <span class="status-badge <?= $totalStock > 0 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                        <?= $totalStock > 0 ? 'Available' : 'Out of Stock' ?>
                    </span>
                    <img src="assets/uploads/<?= htmlspecialchars($p['image']) ?>" onerror="this.src='assets/uploads/placeholder.jpg'">
                </div>
                <div class="card-body d-flex flex-column">
                    <h6 class="fw-bold mb-1"><?= htmlspecialchars($p['product_name']) ?></h6>
                    <p class="text-muted small mb-1" style="font-size: 0.75rem; line-height: 1.2; height: 35px; overflow: hidden;"><?= htmlspecialchars($p['description']) ?></p>
                    <p class="text-danger fw-bold small mb-3">Best Before: <?= $bbDate ?></p>

                    <div class="mb-3 size-group" data-pid="<?= $p['product_id'] ?>">
                        <?php foreach($sizes as $idx => $sz): 
                            $d = explode(':', $sz); if(empty($d[0])) continue; ?>
                            <button class="size-btn <?= $idx==0?'active':'' ?>" 
                                    onclick="setProductSize(this, <?= $d[1] ?>, '<?= $d[0] ?>', <?= $p['product_id'] ?>)">
                                <?= $d[0] ?>
                            </button>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-warning h5 mb-0">₱<?= number_format($p['price'], 2) ?></span>
                        <button class="btn btn-dark btn-sm rounded-pill px-4" 
                                onclick="addToTray(<?= $p['product_id'] ?>, '<?= addslashes($p['product_name']) ?>', <?= $p['price'] ?>)"
                                <?= $totalStock <= 0 ? 'disabled' : '' ?>>+ Add</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<aside class="cart-tray">
    <h4 class="fw-bold mb-4">My Tray</h4>
    <div id="tray-list" class="flex-grow-1 overflow-auto"></div>

    <div class="border-top pt-3">
        <label class="small fw-bold mb-1">Add-ons</label>
        <select id="addon-picker" class="form-select mb-3" onchange="applyAddon()">
            <option value="">Select Add-on...</option>
            <?php foreach($addons as $a): ?>
                <option value="<?= $a['price'] ?>" data-name="<?= $a['addon_name'] ?>"><?= $a['addon_name'] ?> (+₱<?= $a['price'] ?>)</option>
            <?php endforeach; ?>
        </select>

        <div class="d-flex justify-content-between h5 fw-bold">
            <span>Total</span>
            <span id="tray-total" style="color:var(--primary)">₱0.00</span>
        </div>
        <button class="btn btn-warning w-100 py-3 mt-2 fw-bold text-white" style="background:var(--primary); border:none;" onclick="processCheckout()">CHECKOUT</button>
    </div>
</aside>

<script>
let tray = []; let selectedIdx = null; let selections = {};

function setProductSize(btn, extra, name, pid) {
    btn.parentElement.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    selections[pid] = { extra: parseFloat(extra), name: name };
}

function addToTray(pid, name, base) {
    if(!selections[pid]) {
        let active = document.querySelector(`.size-group[data-pid="${pid}"] .active`);
        if(active) active.click();
    }
    let s = selections[pid] || { extra: 0, name: 'Standard' };
    tray.push({ pid, name, unitPrice: parseFloat(base) + s.extra, size: s.name, qty: 1, addons: [] });
    selectedIdx = tray.length - 1;
    renderTray();
}

function updateQty(idx, mod) {
    tray[idx].qty += mod;
    if(tray[idx].qty < 1) tray.splice(idx, 1);
    renderTray();
}

function applyAddon() {
    let p = document.getElementById('addon-picker');
    if (selectedIdx === null || !p.value) return;
    tray[selectedIdx].addons.push({ name: p.options[p.selectedIndex].dataset.name, price: parseFloat(p.value) });
    p.selectedIndex = 0;
    renderTray();
}

function renderTray() {
    let list = document.getElementById('tray-list'); list.innerHTML = '';
    let total = 0;
    tray.forEach((item, i) => {
        let sub = (item.unitPrice * item.qty);
        item.addons.forEach(a => sub += (a.price * item.qty));
        total += sub;
        list.innerHTML += `
            <div class="p-3 mb-2 rounded border ${selectedIdx===i?'border-warning bg-light shadow-sm':'bg-white'}" onclick="selectedIdx=${i};renderTray()">
                <div class="d-flex justify-content-between"><b>${item.name}</b> <span>₱${sub.toFixed(2)}</span></div>
                <small class="text-secondary d-block">Size: ${item.size}</small>
                ${item.addons.map(a => `<small class="text-muted d-block">+ ${a.name}</small>`).join('')}
                <div class="qty-ctrl" onclick="event.stopPropagation()">
                    <div class="qty-btn" onclick="updateQty(${i}, -1)">-</div>
                    <span class="fw-bold">${item.qty}</span>
                    <div class="qty-btn" onclick="updateQty(${i}, 1)">+</div>
                </div>
            </div>`;
    });
    document.getElementById('tray-total').innerText = '₱' + total.toFixed(2);
}

function filterMenu(id, btn) {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.querySelectorAll('.product-item').forEach(i => i.style.display = (id==='all' || i.dataset.category == id) ? 'block' : 'none');
}

async function processCheckout() {
    if(tray.length === 0) return alert("Tray is empty!");
    const response = await fetch('checkout.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ total: document.getElementById('tray-total').innerText.replace('₱',''), items: tray })
    });
    const res = await response.json();
    if(res.success) { alert("Success! Order #"+res.order_id); tray=[]; renderTray(); location.reload(); }
}
</script>
</body>
</html>