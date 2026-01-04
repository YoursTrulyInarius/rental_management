<?php
session_start();
require_once '../config/db.php';
$page_title = 'Admin Dashboard';
include '../includes/header.php';

// Fetch quick stats
$tenant_count = $pdo->query("SELECT COUNT(*) FROM tenants")->fetchColumn();
$total_sales = $pdo->query("SELECT SUM(total_amount) FROM transactions")->fetchColumn() ?? 0;
$pending_rent = $pdo->query("SELECT COUNT(*) FROM rent_payments WHERE status = 'pending'")->fetchColumn();
?>

<!-- Quick Stats Navigation -->
<div class="container-fluid">
    <div class="row g-3 g-md-4 mb-4">
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-sm h-100 stats-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold">Total Tenants</h6>
                            <h2 class="fw-bold mb-0 mt-2"><?php echo $tenant_count; ?></h2>
                        </div>
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold">
                            <i class="bi bi-people-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 stats-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold">Overall Sales</h6>
                            <h2 class="fw-bold mb-0 mt-2 text-success">₱<?php echo number_format($total_sales, 0); ?></h2>
                        </div>
                        <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center fw-bold">
                            <i class="bi bi-currency-dollar fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <div class="card border-0 shadow-sm h-100 stats-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div>
                            <h6 class="text-muted small text-uppercase fw-bold">Pending Rent</h6>
                            <h2 class="fw-bold mb-0 mt-2 text-warning"><?php echo $pending_rent; ?></h2>
                        </div>
                        <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center fw-bold">
                            <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-sm-6 col-lg-3">
            <a href="reports.php" class="card border-0 shadow-sm h-100 bg-primary text-white text-decoration-none hover-grow transition-all">
                <div class="card-body d-flex flex-column justify-content-center align-items-center text-center">
                    <i class="bi bi-bar-chart-fill fs-2 mb-2"></i>
                    <h6 class="mb-0 fw-bold">View Analytics</h6>
                    <small class="opacity-75">Daily/Monthly Reports</small>
                </div>
            </a>
        </div>
    </div>

    <div class="row g-3 g-md-4">
        <!-- Weekly Trend -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">7-Day Revenue Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="dashboardSalesChart" height="220"></canvas>
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3 border-0">
                    <h6 class="mb-0 fw-bold">Recent Tenant Activity</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-3 border-0">Business</th>
                                    <th class="border-0">Stall</th>
                                    <th class="pe-3 border-0">Last Sale</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT t.*, (SELECT MAX(created_at) FROM transactions WHERE tenant_id = t.id) as last_sale FROM tenants t LIMIT 6");
                                while ($row = $stmt->fetch()):
                                ?>
                                <tr>
                                    <td class="ps-3 py-3 fw-bold"><?php echo htmlspecialchars($row['business_name']); ?></td>
                                    <td><span class="x-small fw-bold opacity-75"><?php echo htmlspecialchars($row['stall_number']); ?></span></td>
                                    <td class="pe-3 text-muted">
                                        <?php echo $row['last_sale'] ? date('h:i A', strtotime($row['last_sale'])) : '<span class="x-small">Inactive</span>'; ?>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('dashboardSalesChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php 
            $labels = [];
            for($i=6; $i>=0; $i--) $labels[] = date('D', strtotime("-$i days"));
            echo json_encode($labels);
        ?>,
        datasets: [{
            label: 'Sales',
            data: <?php 
                $data = [];
                for($i=6; $i>=0; $i--) {
                    $d = date('Y-m-d', strtotime("-$i days"));
                    $val = $pdo->query("SELECT SUM(total_amount) FROM transactions WHERE DATE(created_at) = '$d'")->fetchColumn() ?? 0;
                    $data[] = $val;
                }
                echo json_encode($data);
            ?>,
            backgroundColor: '#4f46e5',
            borderRadius: 6
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            y: { beginAtZero: true, grid: { display: false }, ticks: { display: false } },
            x: { grid: { display: false } }
        }
    }
});
</script>

<?php include '../includes/footer.php'; ?>
