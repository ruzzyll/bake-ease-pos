<?php
// Database Connection
$host = "127.0.0.1"; $port = "3307"; $db_name = "bake-ease-pos"; $user = "root"; $pass = "";
try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$db_name", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(Exception $e) { die("DB Error: " . $e->getMessage()); }

// 1. Fetch Categories and Addons
$categories = $db->query("SELECT * FROM categories")->fetchAll(PDO::FETCH_ASSOC);
$addons = $db->query("SELECT * FROM addons")->fetchAll(PDO::FETCH_ASSOC);

// 2. Fetch Products with stock data AND category names
$query = "SELECT p.*, c.category_name,
          GROUP_CONCAT(CONCAT(s.size_name, ':', s.extra_price, ':', ps.stock) SEPARATOR '|') as size_data
          FROM products p
          LEFT JOIN categories c ON p.category_id = c.category_id
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root { --primary: #ff7a00; --soft-bg: #f8f9fb; }
        body { background: var(--soft-bg); font-family: 'Segoe UI', sans-serif; }
        .main-content { padding: 20px; margin-right: 420px; }
        .cart-tray { width: 400px; background: #fff; height: 100vh; position: fixed; right: 0; top: 0; padding: 20px; border-left: 1px solid #eee; display: flex; flex-direction: column; }
        
        /* Search & Category Styling */
        .search-wrapper { position: relative; width: 300px; }
        .search-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #aaa; }
        .search-input { width: 100%; border-radius: 12px; border: 1px solid #ddd; padding: 10px 15px 10px 40px; transition: 0.3s; }
        .search-input:focus { border-color: var(--primary); outline: none; box-shadow: 0 0 0 3px rgba(255,122,0,0.1); }
        .cat-btn { border: none; background: #fff; padding: 10px 20px; border-radius: 12px; font-weight: 600; color: #666; transition: 0.2s; box-shadow: 0 2px 5px rgba(0,0,0,0.03); white-space: nowrap; }
        .cat-btn.active { background: var(--primary); color: #fff; }

        /* Card Styling */
        .food-card { border-radius: 18px; border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); overflow: hidden; height: 100%; transition: 0.3s; }
        .img-box { height: 160px; background: #ececec; position: relative; }
        .img-box img { width: 100%; height: 100%; object-fit: cover; }
        .status-badge { position: absolute; top: 12px; right: 12px; font-size: 0.7rem; padding: 4px 10px; border-radius: 8px; font-weight: bold; }
        .size-btn { border: 1px solid #ddd; background: #fff; font-size: 0.75rem; padding: 6px 12px; border-radius: 8px; margin: 2px; cursor: pointer; }
        .size-btn.active { background: #333 !important; color: #fff !important; border-color: #333 !important; }
        
        /* Tray Item Styling */
        .qty-ctrl { display: flex; align-items: center; gap: 10px; margin-top: 10px; }
        .qty-btn { width: 28px; height: 28px; border: 1px solid #ddd; background: #fff; border-radius: 6px; display: flex; align-items: center; justify-content: center; cursor: pointer; }
        .tray-item { cursor: pointer; border: 1px solid #eee; transition: 0.2s; border-radius: 12px; position: relative; }
        .tray-item.selected { border: 2px solid var(--primary) !important; background: #fff9f4; }
        
        /* NEW: Note and Empty State Styles */
        .clear-all-link { font-size: 0.75rem; color: #dc3545; text-decoration: none; font-weight: bold; cursor: pointer; }
        .empty-state { text-align: center; color: #ccc; padding: 50px 20px; }
        .item-note { font-size: 0.75rem; background: #f0f0f0; padding: 4px 8px; border-radius: 6px; margin-top: 5px; color: #555; font-style: italic; display: block; }
        .add-note-btn { font-size: 0.7rem; color: var(--primary); text-decoration: none; font-weight: bold; margin-top: 5px; display: inline-block; }
    </style>
</head>
<body>

<main class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold m-0">Bake Ease POS 🧁</h2>
        <div class="search-wrapper">
            <i class="fas fa-search"></i>
            <input type="text" id="posSearch" class="search-input" placeholder="Search product name..." onkeyup="applyFilters()">
        </div>
    </div>

    <div class="d-flex gap-2 mb-4 overflow-auto pb-2" id="category-nav">
        <button class="cat-btn active" onclick="setCategory('All', this)">All Items</button>
        <?php foreach($categories as $cat): ?>
            <button class="cat-btn" onclick="setCategory('<?= htmlspecialchars($cat['category_name']) ?>', this)">
                <?= htmlspecialchars($cat['category_name']) ?>
            </button>
        <?php endforeach; ?>
    </div>

    <div class="row g-4" id="product-grid">
        <?php foreach($products as $p): 
            $sizes = !empty($p['size_data']) ? explode('|', $p['size_data']) : [];
            $totalStock = 0;
            foreach($sizes as $sz) { $d = explode(':', $sz); $totalStock += (int)($d[2] ?? 0); }
        ?>
        <div class="col-md-6 col-lg-4 product-item" 
             data-category="<?= htmlspecialchars($p['category_name']) ?>"
             data-name="<?= strtolower(htmlspecialchars($p['product_name'])) ?>">
            <div class="card food-card">
                <div class="img-box">
                    <span class="status-badge <?= $totalStock > 0 ? 'bg-success text-white' : 'bg-danger text-white' ?>">
                        <?= $totalStock > 0 ? 'Available' : 'Out of Stock' ?>
                    </span>
                    <img src="assets/uploads/<?= htmlspecialchars($p['image']) ?>" onerror="this.src='assets/uploads/placeholder.jpg'">
                </div>
                <div class="card-body d-flex flex-column">
                    <div class="mb-1">
                        <small class="text-muted text-uppercase fw-bold" style="font-size: 0.65rem;"><?= htmlspecialchars($p['category_name']) ?></small>
                        <h6 class="fw-bold mb-2"><?= htmlspecialchars($p['product_name']) ?></h6>
                    </div>
                    
                    <div class="mb-3 size-group" id="size-group-<?= $p['product_id'] ?>">
                        <?php 
                        $firstAvailableSet = false;
                        foreach($sizes as $sz): 
                            $d = explode(':', $sz); if(empty($d[0])) continue; 
                            $sStock = (int)$d[2];
                            $isActive = (!$firstAvailableSet && $sStock > 0);
                            if($isActive) $firstAvailableSet = true;
                        ?>
                            <button class="size-btn <?= $isActive ? 'active' : '' ?>" 
                                    data-extra="<?= $d[1] ?>" 
                                    data-name="<?= $d[0] ?>" 
                                    data-stock="<?= $sStock ?>"
                                    onclick="selectSize(this)"
                                    <?= $sStock <= 0 ? 'disabled' : '' ?>><?= $d[0] ?></button>
                        <?php endforeach; ?>
                    </div>

                    <div class="mt-auto d-flex justify-content-between align-items-center">
                        <span class="fw-bold text-dark h5 mb-0">₱<?= number_format($p['price'], 2) ?></span>
                        <button class="btn btn-warning btn-sm rounded-pill px-4 fw-bold" 
                                onclick="addToTray(<?= $p['product_id'] ?>, '<?= addslashes($p['product_name']) ?>', <?= $p['price'] ?>, '<?= addslashes($p['category_name']) ?>')" 
                                <?= $totalStock <= 0 ? 'disabled' : '' ?>>+ Add</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</main>

<aside class="cart-tray shadow">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold m-0">My Tray</h4>
        <span class="clear-all-link" onclick="clearTray()">CLEAR ALL</span>
    </div>

    <div id="tray-list" class="flex-grow-1 overflow-auto"></div>

    <div class="border-top pt-3">
        <select id="addon-picker" class="form-select mb-3" onchange="applyAddon()">
            <option value="">Add optional extra...</option>
            <?php foreach($addons as $a): ?>
                <option value="<?= $a['price'] ?>" data-name="<?= $a['addon_name'] ?>"><?= $a['addon_name'] ?> (+₱<?= $a['price'] ?>)</option>
            <?php endforeach; ?>
        </select>
        <div class="d-flex justify-content-between h5 fw-bold mb-3">
            <span>Total</span><span id="tray-total" style="color:var(--primary)">₱0.00</span>
        </div>
        <button class="btn btn-dark w-100 py-3 fw-bold" onclick="processCheckout()">PROCEED TO CHECKOUT</button>
    </div>
</aside>

<script>
let tray = []; 
let selectedIdx = null;
let activeCategory = 'All';

// NEW: Clear Tray
function clearTray() {
    if (tray.length > 0 && confirm("Are you sure you want to clear the entire tray?")) {
        tray = [];
        selectedIdx = null;
        renderTray();
    }
}

// NEW: Edit Note
function editNote(idx) {
    let currentNote = tray[idx].note || "";
    let newNote = prompt("Add special instructions (e.g., 'Less sugar', 'Happy Birthday name'):", currentNote);
    if (newNote !== null) {
        tray[idx].note = newNote;
        renderTray();
    }
}

function applyFilters() {
    const searchInput = document.getElementById('posSearch').value.toLowerCase();
    const items = document.querySelectorAll('.product-item');
    items.forEach(item => {
        const matchesCategory = (activeCategory === 'All' || item.getAttribute('data-category') === activeCategory);
        const matchesSearch = item.getAttribute('data-name').includes(searchInput);
        item.style.display = (matchesCategory && matchesSearch) ? 'block' : 'none';
    });
}

function setCategory(category, btn) {
    document.querySelectorAll('.cat-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    activeCategory = category;
    applyFilters();
}

function selectSize(btn) {
    btn.parentElement.querySelectorAll('.size-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

function addToTray(pid, name, basePrice, categoryName) {
    const sizeGroup = document.getElementById(`size-group-${pid}`);
    const activeSizeBtn = sizeGroup.querySelector('.size-btn.active');
    if (!activeSizeBtn) { alert("Select a size!"); return; }

    const sizeName = activeSizeBtn.getAttribute('data-name');
    const extraPrice = parseFloat(activeSizeBtn.getAttribute('data-extra'));
    const maxStock = parseInt(activeSizeBtn.getAttribute('data-stock'));

    let countInTray = tray.filter(t => t.pid === pid && t.size === sizeName).reduce((a, t) => a + t.qty, 0);
    if(countInTray + 1 > maxStock) { alert("Stock limit reached!"); return; }

    tray.push({
        pid: pid,
        name: name,
        size: sizeName,
        category: categoryName,
        unitPrice: parseFloat(basePrice) + extraPrice,
        qty: 1,
        addons: [],
        max: maxStock,
        note: "" // Initialize note
    });

    selectedIdx = tray.length - 1;
    renderTray();
}

function renderTray() {
    const list = document.getElementById('tray-list');
    list.innerHTML = '';
    let grandTotal = 0;

    // NEW: Empty State UI
    if (tray.length === 0) {
        list.innerHTML = `
            <div class="empty-state">
                <i class="fas fa-shopping-basket fa-3x mb-3"></i>
                <p>Your tray is empty</p>
            </div>`;
        document.getElementById('tray-total').innerText = '₱0.00';
        return;
    }

    tray.forEach((item, i) => {
        let itemTotal = item.unitPrice * item.qty;
        item.addons.forEach(a => itemTotal += (a.price * item.qty));
        grandTotal += itemTotal;

        list.innerHTML += `
            <div class="tray-item p-3 mb-2 bg-white ${selectedIdx === i ? 'selected' : ''}" 
                 onclick="selectedIdx=${i}; renderTray()">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <b class="d-block">${item.name}</b>
                        <small class="text-primary">${item.size} | ${item.category}</small>
                        
                        ${item.note ? `<span class="item-note"><i class="fas fa-pen me-1"></i> ${item.note}</span>` : ''}
                        
                        <a href="javascript:void(0)" class="add-note-btn" onclick="event.stopPropagation(); editNote(${i})">
                            ${item.note ? 'Edit Note' : '+ Add Note'}
                        </a>
                    </div>
                    <span class="fw-bold">₱${itemTotal.toFixed(2)}</span>
                </div>
                ${item.addons.map(a => `<div class="small text-muted">+ ${a.name}</div>`).join('')}
                <div class="qty-ctrl" onclick="event.stopPropagation()">
                    <div class="qty-btn" onclick="updateQty(${i}, -1)">-</div>
                    <span class="fw-bold px-2">${item.qty}</span>
                    <div class="qty-btn" onclick="updateQty(${i}, 1)">+</div>
                </div>
            </div>`;
    });
    document.getElementById('tray-total').innerText = '₱' + grandTotal.toLocaleString(undefined, {minimumFractionDigits: 2});
}

function updateQty(idx, mod) {
    let item = tray[idx];
    if(mod > 0 && item.qty + 1 > item.max) return alert("Out of stock!");
    item.qty += mod;
    if(item.qty < 1) { tray.splice(idx, 1); selectedIdx = null; }
    renderTray();
}

function applyAddon() {
    let picker = document.getElementById('addon-picker');
    if (selectedIdx === null || !picker.value) return;
    tray[selectedIdx].addons.push({
        name: picker.options[picker.selectedIndex].dataset.name,
        price: parseFloat(picker.value)
    });
    picker.selectedIndex = 0;
    renderTray();
}

function processCheckout() {
    if(tray.length === 0) return alert("Tray is empty!");
    localStorage.setItem('current_tray', JSON.stringify(tray));
    window.location.href = 'checkout.php';
}
</script>
</body>
</html>