<?php
session_start();
require_once '../config/db.php';
$page_title = 'Manage Inventory';
include '../includes/header.php';

// Get tenant ID
$stmt = $pdo->prepare("SELECT id FROM tenants WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$tenant_id = $stmt->fetchColumn();

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_item'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $category_id = $_POST['category_id'];

    $stmt = $pdo->prepare("INSERT INTO inventory (tenant_id, name, price, stock_quantity, category_id) VALUES (?, ?, ?, ?, ?)");
    if ($stmt->execute([$tenant_id, $name, $price, $stock, $category_id])) {
        $message = "Item added successfully!";
    }
}
?>

<div class="container-fluid">
    <div class="row g-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Add New Item</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success py-2 small">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Item Name</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="row mb-3">
                            <div class="col">
                                <label class="form-label small fw-semibold">Price (₱)</label>
                                <input type="number" step="0.01" name="price" class="form-control" required>
                            </div>
                            <div class="col">
                                <label class="form-label small fw-semibold">Stock</label>
                                <input type="number" name="stock" class="form-control" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Category</label>
                            <select name="category_id" class="form-select" required>
                                <option value="">Select Category</option>
                                <?php
                                $cats = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                                while ($cat = $cats->fetch()):
                                ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo htmlspecialchars($cat['name']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <button type="submit" name="add_item" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Add to Inventory
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm overflow-hidden">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold">Inventory List</h5>
                    <input type="text" class="form-control form-control-sm w-auto" id="invSearch" placeholder="Search items...">
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="invTable">
                            <thead class="bg-light">
                                <tr>
                                    <th class="ps-4">Item Name</th>
                                    <th>Category</th>
                                    <th>Price</th>
                                    <th>Stock</th>
                                    <th class="pe-4 text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $pdo->prepare("SELECT i.*, c.name as category_name FROM inventory i LEFT JOIN categories c ON i.category_id = c.id WHERE i.tenant_id = ?");
                                $stmt->execute([$tenant_id]);
                                while ($row = $stmt->fetch()):
                                ?>
                                <tr>
                                    <td class="ps-4 fw-medium"><?php echo htmlspecialchars($row['name']); ?></td>
                                    <td><span class="badge bg-light text-dark border"><?php echo htmlspecialchars($row['category_name'] ?? 'Uncategorized'); ?></span></td>
                                    <td>₱<?php echo number_format($row['price'], 2); ?></td>
                                    <td>
                                        <span class="fw-bold <?php echo $row['stock_quantity'] < 10 ? 'text-danger' : 'text-success'; ?>">
                                            <?php echo $row['stock_quantity']; ?>
                                        </span>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <button class="btn btn-outline-secondary btn-sm border-0"><i class="bi bi-pencil"></i></button>
                                        <button class="btn btn-outline-danger btn-sm border-0"><i class="bi bi-trash"></i></button>
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

<script>
$(document).ready(function() {
    $("#invSearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#invTable tbody tr").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
