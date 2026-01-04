<?php
session_start();
require_once '../config/db.php';
$page_title = 'Sales History';
include '../includes/header.php';

// Get tenant ID
$stmt = $pdo->prepare("SELECT id FROM tenants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$tenant_id = $stmt->fetchColumn();

// Fetch transactions
$stmt = $pdo->prepare("
    SELECT t.*, u.full_name as operator_name 
    FROM transactions t 
    JOIN users u ON t.operator_id = u.id 
    WHERE t.tenant_id = ? 
    ORDER BY t.created_at DESC
");
$stmt->execute([$tenant_id]);
$transactions = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Transaction History</h5>
            <div class="input-group input-group-sm w-auto">
                <span class="input-group-text bg-white border-end-0"><i class="bi bi-calendar3"></i></span>
                <input type="text" class="form-control border-start-0" id="histSearch" placeholder="Filter history...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="histTable">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Trx ID</th>
                            <th>Date & Time</th>
                            <th>Cashier</th>
                            <th>Items</th>
                            <th>Total Amount</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $t): 
                            // Get item count
                            $count_stmt = $pdo->prepare("SELECT SUM(quantity) FROM transaction_items WHERE transaction_id = ?");
                            $count_stmt->execute([$t['id']]);
                            $item_count = $count_stmt->fetchColumn();
                        ?>
                        <tr>
                            <td class="ps-4 fw-bold text-primary">#<?php echo str_pad($t['id'], 6, '0', STR_PAD_LEFT); ?></td>
                            <td><?php echo date('M d, Y h:i A', strtotime($t['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($t['operator_name']); ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo $item_count; ?> items</span></td>
                            <td class="fw-bold">₱<?php echo number_format($t['total_amount'], 2); ?></td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-outline-primary btn-sm rounded-pill px-3" onclick="showReceipt(<?php echo $t['id']; ?>)">
                                    <i class="bi bi-receipt me-1"></i> Receipt
                                </button>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($transactions)): ?>
                            <tr>
                                <td colspan="6" class="py-5 text-center text-muted small">No transactions found.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
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
function showReceipt(transactionId) {
    $.get('../api/get_receipt.php?id=' + transactionId, function(html) {
        $('#receiptContent').html(html);
        const modal = new bootstrap.Modal(document.getElementById('receiptModal'));
        modal.show();
    });
}

$(document).ready(function() {
    $("#histSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#histTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
