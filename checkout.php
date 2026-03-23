<?php
session_start();
$host = "127.0.0.1"; $port = "3307"; $db_name = "bake-ease-pos"; $user = "root"; $pass = "";

try {
    $db = new PDO("mysql:host=$host;port=$port;dbname=$db_name", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $reasonQuery = $db->query("SELECT reason_name FROM purchase_reasons ORDER BY reason_name ASC");
    $reasons = $reasonQuery->fetchAll(PDO::FETCH_ASSOC);

    $types = ["Walk-in", "Pre-order", "Event", "Regular", "Bulk Buyer"];
} catch(PDOException $e) { 
    die("Connection failed: " . $e->getMessage()); 
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Bake Ease Checkout</title>
    <link rel="stylesheet" href="assets/css/bootstrap.min.css">
    <style>
        :root { --primary: #ff7a00; }
        body { background: #f8f9fb; font-family: 'Segoe UI', sans-serif; }
        .checkout-card { border-radius: 20px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.05); }
        .section-label { font-size: 0.75rem; text-transform: uppercase; font-weight: 800; color: #aaa; letter-spacing: 1px; margin-bottom: 15px; display: block; }
        .method-btn { border: 2px solid #eee; background: #fff; padding: 12px; border-radius: 12px; cursor: pointer; transition: 0.3s; width: 100%; font-weight: bold; }
        .method-btn.active { border-color: var(--primary); background: #fff9f4; color: var(--primary); }
        
        /* Note Styling in Summary */
        .summary-note { 
            font-size: 0.75rem; 
            color: #666; 
            background: #eee; 
            padding: 2px 8px; 
            border-radius: 4px; 
            display: inline-block; 
            margin-top: 2px;
            font-style: italic;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-11">
            <div class="card checkout-card p-4">
                <h3 class="fw-bold mb-4">Order Confirmation</h3>

                <div class="row g-4">
                    <div class="col-lg-7">
                        <span class="section-label">1. Customer Information</span>
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <label class="small fw-bold">Full Name *</label>
                                <input type="text" id="cust_name" class="form-control" placeholder="Required">
                            </div>
                            <div class="col-md-6">
                                <label class="small fw-bold">Contact Number *</label>
                                <input type="text" id="cust_phone" class="form-control" placeholder="Required">
                            </div>
                            <div class="col-md-12">
                                <label class="small fw-bold">Customer Type</label>
                                <select id="cust_type" class="form-select">
                                    <?php foreach($types as $t): ?><option value="<?= $t ?>"><?= $t ?></option><?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <span class="section-label">2. Fulfillment & Reason</span>
                        <div class="row g-3 mb-3">
                            <div class="col-6">
                                <button class="method-btn active" id="btn-pickup" onclick="setMethod('Pickup')">🛍️ Pickup</button>
                            </div>
                            <div class="col-6">
                                <button class="method-btn" id="btn-delivery" onclick="setMethod('Delivery')">🚚 Delivery</button>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="small fw-bold">Reason for Purchase *</label>
                            <select id="purchase_reason" class="form-select" onchange="updateReasonText()">
                                <option value="">-- Choose Reason --</option>
                                <?php foreach($reasons as $r): ?>
                                    <option value="<?= htmlspecialchars($r['reason_name']) ?>"><?= htmlspecialchars($r['reason_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="pickup-info" class="p-3 bg-light rounded-3 mb-3">
                            <label class="small fw-bold d-block mb-2">Pickup Date & Time</label>
                            <div class="d-flex gap-2">
                                <input type="date" id="order_date" class="form-control" value="<?= date('Y-m-d') ?>">
                                <input type="time" id="order_time" class="form-control">
                            </div>
                        </div>
                        <div id="delivery-info" class="p-3 bg-light rounded-3 mb-3 d-none">
                            <label class="small fw-bold">Delivery Address Details</label>
                            <textarea id="delivery_address" class="form-control" rows="2" placeholder="Brgy, Street, House No."></textarea>
                        </div>
                    </div>

                    <div class="col-lg-5">
                        <div class="p-4 bg-light rounded-4 border h-100">
                            <span class="section-label">Order Summary</span>
                            <div id="item-list" class="mb-4"></div>
                            
                            <div class="small text-muted mb-3 border-top pt-3">
                                <div class="d-flex justify-content-between"><span>Method:</span> <strong id="sum-method" class="text-dark">Pickup</strong></div>
                                <div class="d-flex justify-content-between"><span>Reason:</span> <strong id="sum-reason" class="text-primary">Not Selected</strong></div>
                            </div>

                            <div class="d-flex justify-content-between h4 fw-bold mb-4">
                                <span>Grand Total</span>
                                <span id="sum-total" style="color:var(--primary)">₱0.00</span>
                            </div>

                            <button class="btn btn-dark w-100 py-3 fw-bold" onclick="processOrder()">CONFIRM TRANSACTION</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let tray = JSON.parse(localStorage.getItem('current_tray')) || [];
let currentMethod = 'Pickup';

function setMethod(m) {
    currentMethod = m;
    document.getElementById('btn-pickup').classList.toggle('active', m === 'Pickup');
    document.getElementById('btn-delivery').classList.toggle('active', m === 'Delivery');
    document.getElementById('pickup-info').classList.toggle('d-none', m === 'Delivery');
    document.getElementById('delivery-info').classList.toggle('d-none', m === 'Pickup');
    document.getElementById('sum-method').innerText = m;
}

function updateReasonText() {
    const val = document.getElementById('purchase_reason').value;
    document.getElementById('sum-reason').innerText = val || 'Not Selected';
}

function renderSummary() {
    let total = 0;
    const itemList = document.getElementById('item-list');
    
    if (tray.length === 0) {
        itemList.innerHTML = '<p class="text-center text-muted">No items in tray.</p>';
        return;
    }

    itemList.innerHTML = tray.map(item => {
        let addonPrice = item.addons ? item.addons.reduce((a, b) => a + b.price, 0) : 0;
        let sub = (item.unitPrice + addonPrice) * item.qty;
        total += sub;

        return `
            <div class="mb-3">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <span class="fw-bold">${item.name} x${item.qty}</span><br>
                        <small class="text-muted">${item.size} ${item.addons.length > 0 ? '+ Addons' : ''}</small>
                        ${item.note ? `<br><span class="summary-note">Note: ${item.note}</span>` : ''}
                    </div>
                    <b class="text-dark">₱${sub.toFixed(2)}</b>
                </div>
            </div>`;
    }).join('');

    document.getElementById('sum-total').innerText = '₱' + total.toLocaleString(undefined, {minimumFractionDigits: 2});
}

async function processOrder() {
    const name = document.getElementById('cust_name').value.trim();
    const phone = document.getElementById('cust_phone').value.trim();
    const reason = document.getElementById('purchase_reason').value;
    
    if(!name || !phone) return alert("Please fill in customer details.");
    if(!reason) return alert("Please select a Reason for Purchase.");
    if(currentMethod === 'Pickup' && !document.getElementById('order_time').value) return alert("Select pickup time.");
    if(currentMethod === 'Delivery' && !document.getElementById('delivery_address').value) return alert("Enter delivery address.");

    const payload = {
        name, phone, reason,
        type: document.getElementById('cust_type').value,
        method: currentMethod,
        schedule: document.getElementById('order_date').value + ' ' + (document.getElementById('order_time').value || '00:00'),
        address: document.getElementById('delivery_address').value,
        total: document.getElementById('sum-total').innerText.replace(/[₱,]/g,''),
        items: tray // This now includes the .note for each item
    };

    try {
        const res = await fetch('process_order.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(payload)
        });

        const result = await res.json();
        if(result.success) {
            alert("Success! Order #" + result.order_id);
            localStorage.removeItem('current_tray');
            window.location.href = 'index.php';
        } else {
            alert("Error: " + result.message);
        }
    } catch (err) {
        alert("Server error. Please check connection.");
    }
}

renderSummary();
</script>
</body>
</html>