<?php
session_start();
require_once '../config/db.php';

if (!isset($_GET['id'])) exit;

$transaction_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT t.*, b.business_name, b.stall_number, u.full_name as operator_name 
    FROM transactions t 
    JOIN tenants b ON t.tenant_id = b.id 
    JOIN users u ON t.operator_id = u.id 
    WHERE t.id = ?
");
$stmt->execute([$transaction_id]);
$trx = $stmt->fetch();

if (!$trx) exit;

$stmt = $pdo->prepare("
    SELECT ti.*, i.name 
    FROM transaction_items ti 
    JOIN inventory i ON ti.item_id = i.id 
    WHERE ti.transaction_id = ?
");
$stmt->execute([$transaction_id]);
$items = $stmt->fetchAll();
?>

<div class="text-center mb-4">
    <h5 class="fw-bold mb-0"><?php echo htmlspecialchars($trx['business_name']); ?></h5>
    <p class="small text-muted mb-0">Stall <?php echo htmlspecialchars($trx['stall_number']); ?></p>
    <p class="small text-muted">A&J Building, Pagadian City</p>
</div>

<div class="border-top border-bottom py-3 mb-3">
    <div class="d-flex justify-content-between small mb-1">
        <span>Receipt #</span>
        <span class="fw-bold"><?php echo str_pad($trx['id'], 6, '0', STR_PAD_LEFT); ?></span>
    </div>
    <div class="d-flex justify-content-between small mb-1">
        <span>Date:</span>
        <span><?php echo date('M d, Y h:i A', strtotime($trx['created_at'])); ?></span>
    </div>
    <div class="d-flex justify-content-between small">
        <span>Cashier:</span>
        <span><?php echo htmlspecialchars($trx['operator_name']); ?></span>
    </div>
</div>

<div class="mb-4">
    <?php foreach ($items as $item): ?>
    <div class="d-flex justify-content-between small mb-1">
        <span><?php echo $item['quantity']; ?>x <?php echo htmlspecialchars($item['name']); ?></span>
        <span>₱<?php echo number_format($item['price_at_sale'] * $item['quantity'], 2); ?></span>
    </div>
    <?php endforeach; ?>
</div>

<div class="border-top pt-3">
    <div class="d-flex justify-content-between fw-bold h5">
        <span>TOTAL</span>
        <span class="text-primary">₱<?php echo number_format($trx['total_amount'], 2); ?></span>
    </div>
</div>

<div class="text-center mt-5">
    <p class="small text-muted mb-0">Thank you for your purchase!</p>
    <p class="x-small text-muted">Alfresco System</p>
</div>

<style>
.x-small { font-size: 0.7rem; }
</style>
