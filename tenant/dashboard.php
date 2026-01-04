<?php
session_start();
require_once '../config/db.php';
$page_title = 'Tenant Dashboard';
include '../includes/header.php';

// Get tenant details
$stmt = $pdo->prepare("SELECT * FROM tenants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$tenant = $stmt->fetch();

if (!$tenant) {
    echo "<div class='alert alert-warning'>Tenant profile not found. Please contact admin.</div>";
    include '../includes/footer.php';
    exit;
}

$tenant_id = $tenant['id'];

// Stats
$today_sales = $pdo->prepare("SELECT SUM(total_amount) FROM transactions WHERE tenant_id = ? AND DATE(created_at) = CURDATE()");
$today_sales->execute([$tenant_id]);
$today_sum = $today_sales->fetchColumn() ?? 0;

$pending_rent = $pdo->prepare("SELECT SUM(amount) FROM rent_payments WHERE tenant_id = ? AND status = 'pending'");
$pending_rent->execute([$tenant_id]);
$rent_sum = $pending_rent->fetchColumn() ?? 0;

$inventory_count = $pdo->prepare("SELECT COUNT(*) FROM inventory WHERE tenant_id = ?");
$inventory_count->execute([$tenant_id]);
$inv_count = $inventory_count->fetchColumn();
?>

<div class="container-fluid">
    <div class="row g-4 mb-4">
        <div class="col-md-8">
            <div class="card bg-primary text-white p-4 h-100 border-0 shadow-sm">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-1">Hello, <?php echo htmlspecialchars($tenant['business_name']); ?>!</h4>
                        <p class="opacity-75 mb-0">Stall: <?php echo htmlspecialchars($tenant['stall_number']); ?></p>
                    </div>
                    <i class="bi bi-building-fill display-4 opacity-25"></i>
                </div>
                <div class="mt-4">
                    <a href="pos.php" class="btn btn-light fw-bold px-4 py-2">
                        <i class="bi bi-cart-fill me-2"></i> Open POS
                    </a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm warning">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Pending Rent</h6>
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h2 class="display-6 fw-bold mb-0 text-warning">₱<?php echo number_format($rent_sum, 2); ?></h2>
                            <p class="small text-muted mt-2 mb-0">Dues for this month</p>
                        </div>
                        <i class="bi bi-calendar-check-fill fs-1 text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-4">
            <div class="card stats-card h-100 success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold">Today's Sales</h6>
                            <h2 class="display-6 fw-bold mb-0 text-success">₱<?php echo number_format($today_sum, 2); ?></h2>
                        </div>
                        <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-cash fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card stats-card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold">Inventory Items</h6>
                            <h2 class="display-6 fw-bold mb-0"><?php echo $inv_count; ?></h2>
                        </div>
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                            <i class="bi bi-box-seam-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card h-100 border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted small text-uppercase fw-bold">Quick Actions</h6>
                    <div class="list-group list-group-flush mt-2">
                        <a href="inventory.php" class="list-group-item list-group-item-action px-0 border-0 small">
                            <i class="bi bi-plus-circle me-2 text-primary"></i> Add New Product
                        </a>
                        <a href="history.php" class="list-group-item list-group-item-action px-0 border-0 small">
                            <i class="bi bi-file-earmark-text me-2 text-primary"></i> View Sales Report
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
