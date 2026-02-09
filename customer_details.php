<?php
require_once 'core/init.php'; 
$subtotal = isset($_GET['subtotal']) ? $_GET['subtotal'] : 0;

// Dynamic fetch (Assuming you have these tables)
$types = $db->query("SELECT * FROM customer_types")->fetchAll();
$locations = $db->query("SELECT * FROM locations")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Customer Details | Bake Ease</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <style>
        :root { --primary-orange: #ff7a00; --bg-soft: #f8f9fb; }
        body { background: var(--bg-soft); font-family: 'Montserrat', sans-serif; padding: 50px; }
        .form-card { background: white; border-radius: 35px; padding: 40px; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.03); }
        .form-control, .form-select { border-radius: 15px; padding: 12px; border: 1px solid #eee; }
        .btn-complete { background: var(--primary-orange); color: white; border: none; border-radius: 15px; padding: 15px; font-weight: bold; width: 100%; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row g-4">
            <div class="col-md-7">
                <div class="form-card">
                    <h3 class="fw-bold mb-4">Customer Details</h3>
                    <form action="core/save_final_order.php" method="POST">
                        <div class="mb-3">
                            <label class="fw-bold">Customer Type</label>
                            <select id="c_type" name="customer_type_id" class="form-select" onchange="toggleWalkIn()" required>
                                <?php foreach($types as $t): ?>
                                    <option value="<?= $t['customer_type_id'] ?>"><?= $t['type_name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div id="standard_fields">
                            <div class="mb-3">
                                <label class="fw-bold">Name</label>
                                <input type="text" id="c_name" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Contact Number</label>
                                <input type="text" id="c_contact" name="contact_number" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="fw-bold">Location</label>
                                <select id="c_location" name="location_id" class="form-select" onchange="calcDelivery()">
                                    <option value="" data-fee="0">Select Location</option>
                                    <?php foreach($locations as $l): ?>
                                        <option value="<?= $l['location_id'] ?>" data-fee="<?= $l['delivery_fee'] ?>"><?= $l['location_name'] ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <button class="btn-complete mt-3">Finalize Order</button>
                    </form>
                </div>
            </div>

            <div class="col-md-5">
                <div class="form-card">
                    <h4 class="fw-bold mb-4">Summary</h4>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Items Subtotal</span>
                        <span>₱<?= number_format($subtotal, 2) ?></span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Delivery Fee</span>
                        <span id="fee_text">₱0.00</span>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between fw-bold fs-4">
                        <span>Total</span>
                        <span id="grand_total" style="color:var(--primary-orange)">₱<?= number_format($subtotal, 2) ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const baseSubtotal = <?= $subtotal ?>;

        function toggleWalkIn() {
            const type = document.getElementById('c_type').value;
            const fields = document.getElementById('standard_fields');
            const name = document.getElementById('c_name');
            
            if(type == "1") { // Walk-in
                fields.style.opacity = "0.5";
                name.value = "Walk-in Customer";
                name.readOnly = true;
                document.getElementById('c_location').value = "";
                calcDelivery();
            } else {
                fields.style.opacity = "1";
                name.value = "";
                name.readOnly = false;
            }
        }

        function calcDelivery() {
            const loc = document.getElementById('c_location');
            const fee = parseFloat(loc.options[loc.selectedIndex].getAttribute('data-fee')) || 0;
            document.getElementById('fee_text').innerText = "₱" + fee.toFixed(2);
            document.getElementById('grand_total').innerText = "₱" + (baseSubtotal + fee).toFixed(2);
        }
    </script>
</body>
</html>