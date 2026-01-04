<?php
session_start();
require_once '../config/db.php';
$page_title = 'Rent Management';
include '../includes/header.php';

// Fetch all tenants with their latest rent status
$stmt = $pdo->query("
    SELECT t.*, 
    (SELECT status FROM rent_payments WHERE tenant_id = t.id ORDER BY month_year DESC LIMIT 1) as last_payment_status,
    (SELECT month_year FROM rent_payments WHERE tenant_id = t.id ORDER BY month_year DESC LIMIT 1) as last_month
    FROM tenants t
");
$tenants = $stmt->fetchAll();
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
        <div>
            <h4 class="fw-bold mb-0">Rent Tracking</h4>
            <p class="text-muted small mb-0">Monitor and record monthly stall rentals.</p>
        </div>
        <button class="btn btn-primary shadow-sm fw-bold px-4 w-100 w-md-auto" data-bs-toggle="modal" data-bs-target="#generateRentModal">
            <i class="bi bi-calendar-plus me-2"></i> Generate Monthly Dues
        </button>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Business Name</th>
                            <th>Stall #</th>
                            <th>Monthly Rent</th>
                            <th>Latest Month</th>
                            <th>Status</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tenants as $t): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold"><?php echo htmlspecialchars($t['business_name']); ?></div>
                                <div class="x-small text-muted">ID: T-<?php echo str_pad($t['id'], 3, '0', STR_PAD_LEFT); ?></div>
                            </td>
                            <td><span class="badge bg-secondary-subtle text-secondary border"><?php echo htmlspecialchars($t['stall_number']); ?></span></td>
                            <td class="fw-bold text-primary">₱<?php echo number_format($t['rent_amount'], 2); ?></td>
                            <td><?php echo $t['last_month'] ?? '<span class="text-muted">No records</span>'; ?></td>
                            <td>
                                <?php if ($t['last_payment_status'] == 'paid'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success px-3">Paid</span>
                                <?php elseif ($t['last_payment_status'] == 'pending'): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning px-3">Pending</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border px-3">Not Set</span>
                                <?php endif; ?>
                            </td>
                            <td class="pe-4 text-end">
                                <button class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="viewHistory(<?php echo $t['id']; ?>)">
                                    <i class="bi bi-clock-history me-1"></i> <span class="d-none d-md-inline">History</span>
                                </button>
                                <?php if ($t['last_payment_status'] == 'pending'): ?>
                                    <button class="btn btn-sm btn-success rounded-pill px-3 ms-2" onclick="receivePayment(<?php echo $t['id']; ?>, '<?php echo $t['last_month']; ?>')">
                                        <i class="bi bi-check2-circle me-1"></i> <span class="d-none d-md-inline">Received</span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Generate Dues -->
<div class="modal fade" id="generateRentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Generate Monthly Dues</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <form id="generateRentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Select Month & Year</label>
                        <input type="month" name="month_year" class="form-control" required value="<?php echo date('Y-m'); ?>">
                        <small class="text-muted">This will create "Pending" rent records for all active tenants.</small>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light fw-bold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 fw-bold">Generate Now</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Payment History -->
<div class="modal fade" id="historyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header">
                <h5 class="modal-title fw-bold">Payment History</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0" id="historyContent">
                <!-- Loaded via AJAX -->
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('#generateRentForm').on('submit', function(e) {
        e.preventDefault();
        const btn = $(this).find('button[type="submit"]');
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Processing...');

        $.ajax({
            url: '../api/generate_rent.php',
            method: 'POST',
            data: $(this).serialize(),
            success: function(response) {
                const res = JSON.parse(response);
                alert(res.message);
                if (res.status === 'success') location.reload();
            },
            error: function() { alert('Server error'); },
            complete: function() { btn.prop('disabled', false).text('Generate Now'); }
        });
    });
});

function receivePayment(tenantId, monthYear) {
    if (confirm('Verify receipt of payment for ' + monthYear + '?')) {
        $.post('../api/receive_payment.php', { tenant_id: tenantId, month_year: monthYear }, function(response) {
            const res = JSON.parse(response);
            if (res.status === 'success') location.reload();
            else alert(res.message);
        });
    }
}

function viewHistory(tenantId) {
    const modal = new bootstrap.Modal(document.getElementById('historyModal'));
    $('#historyContent').html('<div class="p-5 text-center"><div class="spinner-border text-primary"></div></div>');
    modal.show();
    
    $.get('../api/get_rent_history.php?tenant_id=' + tenantId, function(html) {
        $('#historyContent').html(html);
    });
}
</script>

<?php include '../includes/footer.php'; ?>
