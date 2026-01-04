<?php
session_start();
require_once '../config/db.php';
$page_title = 'Financial Reports';
include '../includes/header.php';

// Filter logic
$range = $_GET['range'] ?? 'daily';
$date_filter = "";

switch ($range) {
    case 'weekly':
        $date_filter = "AND tr.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
        break;
    case 'monthly':
        $date_filter = "AND tr.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
        break;
    case 'yearly':
        $date_filter = "AND tr.created_at >= DATE_SUB(NOW(), INTERVAL 1 YEAR)";
        break;
    default:
        $date_filter = "AND DATE(tr.created_at) = CURDATE()";
        break;
}

// Fetch sales data for chart
$chart_query = "SELECT DATE(tr.created_at) as sale_date, SUM(tr.total_amount) as daily_total 
                FROM transactions tr 
                WHERE 1=1 $date_filter 
                GROUP BY DATE(tr.created_at) 
                ORDER BY sale_date ASC";
$chart_stmt = $pdo->query($chart_query);
$chart_data = $chart_stmt->fetchAll();

// Fetch tenant sales performance
$tenant_query = "SELECT t.business_name, SUM(tr.total_amount) as total_sales 
                 FROM tenants t 
                 JOIN transactions tr ON t.id = tr.tenant_id 
                 WHERE 1=1 $date_filter 
                 GROUP BY t.id 
                 ORDER BY total_sales DESC";
$tenant_stmt = $pdo->query($tenant_query);
$tenant_performance = $tenant_stmt->fetchAll();

// Total Stats
$total_sales = array_sum(array_column($tenant_performance, 'total_sales'));
$total_transactions = $pdo->query("SELECT COUNT(*) FROM transactions tr WHERE 1=1 $date_filter")->fetchColumn();
?>

<div class="container-fluid">
    <!-- Header/Filters -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-0">Financial Analytics</h4>
            <p class="text-muted small mb-0">Visualizing business performance across all stalls.</p>
        </div>
        <div class="btn-group shadow-sm">
            <a href="?range=daily" class="btn <?php echo $range == 'daily' ? 'btn-primary' : 'btn-white'; ?> px-4 fw-bold small border">Daily</a>
            <a href="?range=weekly" class="btn <?php echo $range == 'weekly' ? 'btn-primary' : 'btn-white'; ?> px-4 fw-bold small border">Weekly</a>
            <a href="?range=monthly" class="btn <?php echo $range == 'monthly' ? 'btn-primary' : 'btn-white'; ?> px-4 fw-bold small border">Monthly</a>
            <a href="?range=yearly" class="btn <?php echo $range == 'yearly' ? 'btn-primary' : 'btn-white'; ?> px-4 fw-bold small border">Yearly</a>
        </div>
    </div>

    <!-- Quick Summary Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100 bg-primary text-white">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-white-50 small text-uppercase fw-bold">Total Revenue (<?php echo ucfirst($range); ?>)</h6>
                        <h2 class="display-5 fw-bold mb-0">₱<?php echo number_format($total_sales, 2); ?></h2>
                    </div>
                    <i class="bi bi-wallet2 fs-1 opacity-25"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm p-4 h-100">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="text-muted small text-uppercase fw-bold">Total Transactions</h6>
                        <h2 class="display-5 fw-bold mb-0"><?php echo $total_transactions; ?></h2>
                    </div>
                    <i class="bi bi-receipt-cutoff fs-1 text-primary opacity-25"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <!-- Sales Trend Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Revenue Trend</h6>
                </div>
                <div class="card-body">
                    <canvas id="salesChart" height="300"></canvas>
                </div>
            </div>
        </div>

        <!-- Tenant Performance -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="mb-0 fw-bold">Tenant Contribution</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush">
                        <?php foreach ($tenant_performance as $ten): ?>
                            <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                                <div>
                                    <div class="fw-bold small"><?php echo htmlspecialchars($ten['business_name']); ?></div>
                                    <div class="progress mt-1" style="height: 4px; width: 100px;">
                                        <div class="progress-bar" style="width: <?php echo ($total_sales > 0 ? ($ten['total_sales'] / $total_sales) * 100 : 0); ?>%"></div>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <div class="fw-bold text-primary small">₱<?php echo number_format($ten['total_sales'], 2); ?></div>
                                    <div class="x-small text-muted"><?php echo number_format($total_sales > 0 ? ($ten['total_sales'] / $total_sales) * 100 : 0, 1); ?>%</div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        <?php if (empty($tenant_performance)): ?>
                            <div class="p-5 text-center text-muted small">No data for this period.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('salesChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?php echo json_encode(array_column($chart_data, 'sale_date')); ?>,
        datasets: [{
            label: 'Daily Revenue',
            data: <?php echo json_encode(array_column($chart_data, 'daily_total')); ?>,
            borderColor: '#4f46e5',
            backgroundColor: 'rgba(79, 70, 229, 0.1)',
            fill: true,
            tension: 0.4,
            borderWidth: 3,
            pointBackgroundColor: '#4f46e5',
            pointBorderColor: '#fff',
            pointHoverRadius: 6
        }]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { display: false }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { borderDash: [5, 5] },
                ticks: {
                    callback: function(value) { return '₱' + value.toLocaleString(); }
                }
            },
            x: {
                grid: { display: false }
            }
        }
    }
});
</script>

<style>
.btn-white { background: white; color: #64748b; }
.btn-white:hover { background: #f8fafc; color: var(--primary); }
</style>

<?php include '../includes/footer.php'; ?>
