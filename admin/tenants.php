<?php
session_start();
require_once '../config/db.php';
$page_title = 'Manage Tenants';
include '../includes/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_tenant'])) {
    $business_name = $_POST['business_name'];
    $stall_number = $_POST['stall_number'];
    $rent_amount = $_POST['rent_amount'];
    $username = $_POST['username'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $full_name = $_POST['full_name'];

    try {
        $pdo->beginTransaction();
        
        // Create user account
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, full_name) VALUES (?, ?, 'tenant', ?)");
        $stmt->execute([$username, $password, $full_name]);
        $user_id = $pdo->lastInsertId();

        // Create tenant record
        $stmt = $pdo->prepare("INSERT INTO tenants (user_id, business_name, stall_number, rent_amount) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $business_name, $stall_number, $rent_amount]);

        $pdo->commit();
        $message = "Tenant added successfully!";
    } catch (Exception $e) {
        $pdo->rollBack();
        $message = "Error: " . $e->getMessage();
    }
}
?>

<div class="container-fluid">
    <div class="row g-3 g-lg-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Add New Tenant</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert <?php echo strpos($message, 'Error') === false ? 'alert-success' : 'alert-danger'; ?> py-2 small">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" id="addTenantForm">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Business Name</label>
                            <input type="text" name="business_name" class="form-control" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label small fw-semibold">Stall #</label>
                                <input type="text" name="stall_number" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="form-label small fw-semibold">Rent (₱)</label>
                                <input type="number" step="0.01" name="rent_amount" class="form-control" required>
                            </div>
                        </div>
                        <hr class="my-4">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Operator Name</label>
                            <input type="text" name="full_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Username</label>
                            <input type="text" name="username" class="form-control" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Password</label>
                            <input type="password" name="password" class="form-control" required>
                        </div>
                        <button type="submit" name="add_tenant" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-person-plus me-1"></i> Create Tenant
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <h5 class="mb-0 fw-bold">Current Tenants</h5>
                    <div class="input-group input-group-sm w-100 w-sm-auto">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" id="tenantSearch" placeholder="Search...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="tenantTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Business</th>
                                    <th>Stall</th>
                                    <th>Operator</th>
                                    <th>Monthly Rent</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->query("SELECT t.*, u.full_name FROM tenants t JOIN users u ON t.user_id = u.id");
                                while ($row = $stmt->fetch()):
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="fw-bold text-primary"><?php echo htmlspecialchars($row['business_name']); ?></div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['stall_number']); ?></span></td>
                                    <td><?php echo htmlspecialchars($row['full_name']); ?></td>
                                    <td>₱<?php echo number_format($row['rent_amount'], 2); ?></td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-outline-danger btn-sm border-0" onclick="deleteTenant(<?php echo $row['id']; ?>, '<?php echo addslashes($row['business_name']); ?>')">
                                            <i class="bi bi-trash-fill"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php if ($stmt->rowCount() == 0): ?>
                                    <tr>
                                        <td colspan="5" class="py-5 text-center text-muted">No tenants registered yet.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#tenantSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#tenantTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

function deleteTenant(id, name) {
    if (confirm("Are you sure you want to delete '" + name + "'? This will also remove all their sales, inventory, and rent records.")) {
        $.post('../api/delete_tenant.php', { id: id }, function(response) {
            const res = JSON.parse(response);
            if (res.status === 'success') location.reload();
            else alert(res.message);
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
