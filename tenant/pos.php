<?php
session_start();
require_once '../config/db.php';
$page_title = 'POS Module';
include '../includes/header.php';

// Get tenant ID
$stmt = $pdo->prepare("SELECT id FROM tenants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$tenant_id = $stmt->fetchColumn();
?>

<div class="container-fluid">
    <div class="row g-4">
        <!-- Products Grid -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-bottom-0">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="mb-0 fw-bold">Select Items</h5>
                        <div class="input-group input-group-sm w-auto">
                            <span class="input-group-text bg-light border-end-0"><i class="bi bi-search"></i></span>
                            <input type="text" class="form-control border-start-0" id="itemSearch" placeholder="Search items...">
                        </div>
                    </div>
                    
                    <ul class="nav nav-pills gap-2 mb-0 overflow-auto flex-nowrap pb-2" id="categoryTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active px-4 py-2 small fw-bold" data-category="all">All Items</button>
                        </li>
                        <?php
                        $cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                        while ($cat = $cats->fetch()):
                        ?>
                            <li class="nav-item">
                                <button class="nav-link px-4 py-2 small fw-bold text-nowrap" data-category="<?php echo $cat['id']; ?>">
                                    <?php echo htmlspecialchars($cat['name']); ?>
                                </button>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                </div>
                <div class="card-body bg-light-subtle">
                    <div class="pos-grid" id="productGrid">
                        <?php
                        $stmt = $pdo->prepare("SELECT i.*, c.name as category_name, i.category_id FROM inventory i LEFT JOIN categories c ON i.category_id = c.id WHERE i.tenant_id = ? AND i.stock_quantity > 0");
                        $stmt->execute([$tenant_id]);
                        while ($item = $stmt->fetch()):
                        ?>
                            <div class="pos-item product-card" 
                                 data-id="<?php echo $item['id']; ?>" 
                                 data-name="<?php echo htmlspecialchars($item['name']); ?>" 
                                 data-price="<?php echo $item['price']; ?>"
                                 data-cat-id="<?php echo $item['category_id'] ?? 'misc'; ?>"
                                 onclick="addToCart(this)">
                                <div class="card h-100 border-0 shadow-sm hover-grow transition-all">
                                    <div class="card-body text-center p-3">
                                        <div class="avatar-md bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center fw-bold mx-auto mb-3 fs-4">
                                            <?php echo substr($item['name'], 0, 1); ?>
                                        </div>
                                        <h6 class="fw-bold mb-1 text-truncate"><?php echo htmlspecialchars($item['name']); ?></h6>
                                        <div class="d-flex flex-column align-items-center mt-2">
                                            <span class="text-primary fw-bold h5 mb-0">₱<?php echo number_format($item['price'], 2); ?></span>
                                            <span class="x-small text-muted">Stock: <?php echo $item['stock_quantity']; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                        <?php if ($stmt->rowCount() == 0): ?>
                            <div class="col-12 py-5 text-center text-muted">
                                <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                                <p>No items in inventory. <a href="inventory.php">Add items now.</a></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Checkout Column -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100 d-flex flex-column">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="bi bi-cart3 me-2 text-primary"></i> Current Order</h5>
                </div>
                <div class="card-body flex-grow-1 p-0 overflow-auto" style="max-height: 400px;">
                    <table class="table table-hover align-middle mb-0" id="cartTable">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-3 py-2 small fw-bold">Item</th>
                                <th class="py-2 small fw-bold text-center">Qty</th>
                                <th class="py-2 small fw-bold text-end pe-3">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Cart items will be injected here -->
                        </tbody>
                    </table>
                </div>
                <div class="card-footer bg-white border-top-0 p-4">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-medium" id="cartSubtotal">₱0.00</span>
                    </div>
                    <div class="d-flex justify-content-between mb-4 mt-2">
                        <h4 class="fw-bold mb-0">Total</h4>
                        <h4 class="fw-bold mb-0 text-primary" id="cartTotal">₱0.00</h4>
                    </div>
                    <button class="btn btn-primary w-100 py-3 fw-bold shadow-sm" id="processCheckout" disabled onclick="checkout()">
                        CONFIRM TRANSACTION
                    </button>
                    <button class="btn btn-outline-secondary btn-sm w-100 mt-2 border-0" onclick="clearCart()">
                        Clear All
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for Receipt -->
<div class="modal fade" id="receiptModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-sm">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-body p-4" id="receiptContent">
                <!-- Receipt content injected by AJAX -->
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary w-100" onclick="window.print()">Print Receipt</button>
            </div>
        </div>
    </div>
</div>

<script>
let cart = [];

function addToCart(element) {
    const id = $(element).data('id');
    const name = $(element).data('name');
    const price = parseFloat($(element).data('price'));

    const existingItem = cart.find(item => item.id === id);
    if (existingItem) {
        existingItem.quantity += 1;
    } else {
        cart.push({ id, name, price, quantity: 1 });
    }
    renderCart();
}

function updateQuantity(id, change) {
    const item = cart.find(item => item.id === id);
    if (item) {
        item.quantity += change;
        if (item.quantity <= 0) {
            cart = cart.filter(i => i.id !== id);
        }
    }
    renderCart();
}

function renderCart() {
    const tbody = $('#cartTable tbody');
    tbody.empty();
    let total = 0;

    cart.forEach(item => {
        const subtotal = item.price * item.quantity;
        total += subtotal;
        tbody.append(`
            <tr>
                <td class="ps-3 small fw-medium">${item.name}</td>
                <td class="text-center">
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <button class="btn btn-link p-0 text-muted" onclick="updateQuantity(${item.id}, -1)"><i class="bi bi-dash-circle"></i></button>
                        <span class="small fw-bold">${item.quantity}</span>
                        <button class="btn btn-link p-0 text-muted" onclick="updateQuantity(${item.id}, 1)"><i class="bi bi-plus-circle"></i></button>
                    </div>
                </td>
                <td class="text-end pe-3 small fw-bold">₱${subtotal.toLocaleString('en-US', {minimumFractionDigits: 2})}</td>
            </tr>
        `);
    });

    if (cart.length === 0) {
        tbody.append('<tr><td colspan="3" class="text-center py-4 text-muted small">Cart is empty</td></tr>');
        $('#processCheckout').prop('disabled', true);
    } else {
        $('#processCheckout').prop('disabled', false);
    }

    $('#cartSubtotal, #cartTotal').text('₱' + total.toLocaleString('en-US', {minimumFractionDigits: 2}));
}

function clearCart() {
    cart = [];
    renderCart();
}

function checkout() {
    if (cart.length === 0) return;

    const btn = $('#processCheckout');
    btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');

    $.ajax({
        url: '../api/process_transaction.php',
        method: 'POST',
        data: { cart: JSON.stringify(cart) },
        success: function(response) {
            const res = JSON.parse(response);
            if (res.status === 'success') {
                showReceipt(res.transaction_id);
                clearCart();
                // Reload inventory stocks visual
                setTimeout(() => location.reload(), 2000); 
            } else {
                alert('Error: ' + res.message);
            }
        },
        error: function() {
            alert('Server error occurred.');
        },
        complete: function() {
            btn.prop('disabled', false).html('CONFIRM TRANSACTION');
        }
    });
}

function showReceipt(transactionId) {
    $.get('../api/get_receipt.php?id=' + transactionId, function(html) {
        $('#receiptContent').html(html);
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    });
}

// Search filter
$("#itemSearch").on("keyup", function() {
    var value = $(this).val().toLowerCase();
    $("#productGrid .product-card").filter(function() {
        $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
    });
});

// Category filtering
$("#categoryTabs .nav-link").on("click", function() {
    $("#categoryTabs .nav-link").removeClass("active");
    $(this).addClass("active");
    
    const catId = $(this).data("category");
    if (catId === "all") {
        $("#productGrid .product-card").show();
    } else {
        $("#productGrid .product-card").hide();
        $(`#productGrid .product-card[data-cat-id="${catId}"]`).show();
    }
});
</script>

<style>
@media print {
    body * { visibility: hidden; }
    #receiptModal, #receiptModal * { visibility: visible; }
    #receiptModal { position: absolute; left: 0; top: 0; width: 100%; }
    .btn, .modal-footer { display: none !important; }
}
.transition-all { transition: all 0.3s ease; }
.hover-grow:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.08) !important;
}
.avatar-md {
    width: 60px;
    height: 60px;
}
.pos-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
    gap: 1.25rem;
}
.nav-pills .nav-link.active {
    background-color: var(--primary);
    box-shadow: 0 4px 10px rgba(79, 70, 229, 0.2);
}
.nav-pills .nav-link {
    background: white;
    color: #64748b;
    border: 1px solid #e2e8f0;
}
#categoryTabs::-webkit-scrollbar {
    display: none;
}
</style>

<?php include '../includes/footer.php'; ?>
