<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) exit;

$tenant_id = $_GET['tenant_id'];

$stmt = $pdo->prepare("SELECT * FROM rent_payments WHERE tenant_id = ? ORDER BY id DESC");
$stmt->execute([$tenant_id]);
$payments = $stmt->fetchAll();
?>

<div class="list-group list-group-flush">
    <?php foreach ($payments as $p): ?>
        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
            <div>
                <div class="fw-bold"><?php echo $p['month_year']; ?></div>
                <div class="x-small text-muted">Amount: ₱<?php echo number_format($p['amount'], 2); ?></div>
                <?php if ($p['status'] == 'paid'): ?>
                    <div class="x-small text-success">Paid on: <?php echo date('M d, Y', strtotime($p['payment_date'])); ?></div>
                <?php endif; ?>
            </div>
            <span class="badge rounded-pill <?php echo $p['status'] == 'paid' ? 'bg-success' : 'bg-warning text-dark'; ?> px-3 small">
                <?php echo ucfirst($p['status']); ?>
            </span>
        </div>
    <?php endforeach; ?>
    <?php if (empty($payments)): ?>
        <div class="p-5 text-center text-muted small">No payment records found.</div>
    <?php endif; ?>
</div>
