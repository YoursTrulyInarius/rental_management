<?php
session_start();
require_once '../config/db.php';
$page_title = 'Manage Categories';
include '../includes/header.php';

$message = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $stmt = $pdo->prepare("INSERT INTO categories (name) VALUES (?)");
    if ($stmt->execute([$name])) {
        $message = "Category added successfully!";
    }
}
?>

<div class="container-fluid">
    <div class="row g-3 g-lg-4">
        <div class="col-md-5">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Add Category</h5>
                </div>
                <div class="card-body">
                    <?php if ($message): ?>
                        <div class="alert alert-success py-2 small">
                            <i class="bi bi-check-circle-fill me-2"></i> <?php echo $message; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST">
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Category Name</label>
                            <input type="text" name="name" class="form-control" required placeholder="e.g. Food, Fashion, Gym">
                        </div>
                        <button type="submit" name="add_category" class="btn btn-primary w-100 py-2 fw-bold">
                            <i class="bi bi-plus-lg me-1"></i> Add Category
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-7">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                    <h5 class="mb-0 fw-bold">Available Categories</h5>
                    <div class="input-group input-group-sm w-100 w-sm-auto">
                        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search"></i></span>
                        <input type="text" class="form-control border-start-0" id="categorySearch" placeholder="Filter...">
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" id="categoryList">
                        <?php
                        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name ASC");
                        while ($row = $stmt->fetch()):
                        ?>
                        <div class="list-group-item d-flex justify-content-between align-items-center py-3">
                            <span class="fw-medium"><?php echo htmlspecialchars($row['name']); ?></span>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-light text-muted fw-normal border">ID: <?php echo $row['id']; ?></span>
                                <button class="btn btn-link link-danger p-0 shadow-none" onclick="deleteCategory(<?php echo $row['id']; ?>, '<?php echo addslashes($row['name']); ?>')">
                                    <i class="bi bi-trash-fill"></i>
                                </button>
                            </div>
                        </div>
                        <?php endwhile; ?>
                        <?php if ($stmt->rowCount() == 0): ?>
                            <div class="p-5 text-center text-muted small">No categories added yet.</div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $("#categorySearch").on("keyup", function() {
        var value = $(this).val().toLowerCase();
        $("#categoryList .list-group-item").filter(function() {
            $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1)
        });
    });
});

function deleteCategory(id, name) {
    if (confirm("Delete category '" + name + "'?")) {
        $.post('../api/delete_category.php', { id: id }, function(response) {
            const res = JSON.parse(response);
            if (res.status === 'success') location.reload();
            else alert(res.message);
        });
    }
}
</script>

<?php include '../includes/footer.php'; ?>
