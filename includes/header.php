<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Dashboard'; ?> - Alfresco</title>
    <link rel="stylesheet" href="../assets/css/style.css?v=<?php echo time(); ?>">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Mobile Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom d-lg-none sticky-top">
        <div class="container-fluid">
            <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas">
                <span class="navbar-toggler-icon"></span>
            </button>
            <a class="navbar-brand fw-bold text-primary ms-2" href="#">Alfresco</a>
            <div class="d-flex align-items-center gap-2">
                <!-- Notifications -->
                <div class="dropdown">
                    <button class="btn btn-link link-dark text-decoration-none shadow-none position-relative" type="button" data-bs-toggle="dropdown" id="notifBtn">
                        <i class="bi bi-bell fs-4"></i>
                        <span id="notifBadge" class="position-absolute top-25 start-75 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.5rem; padding: 0.35em 0.5em;">0</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-lg border-0 mt-3 p-0 overflow-hidden" style="width: 320px;" id="notifList">
                        <li class="px-3 py-2 bg-light border-bottom">
                            <h6 class="mb-0 fw-bold small">Alerts & Notifications</h6>
                        </li>
                        <div id="notifContent" style="max-height: 400px; overflow-y: auto;">
                            <!-- Notifications injected here -->
                            <li class="p-4 text-center text-muted small">No new notifications</li>
                        </div>
                    </ul>
                </div>

                <div class="dropdown">
                    <button class="btn btn-link link-dark dropdown-toggle text-decoration-none shadow-none" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-person-circle fs-4"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2">
                        <li class="px-3 py-2 border-bottom">
                            <p class="small mb-0 fw-bold"><?php echo $_SESSION['full_name']; ?></p>
                            <p class="x-small text-muted mb-0 text-capitalize"><?php echo $_SESSION['role']; ?></p>
                        </li>
                        <li><a class="dropdown-item text-danger small pt-2" href="../auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="dashboard-layout">
        <!-- Desktop Sidebar -->
        <aside class="sidebar d-none d-lg-flex border-end">
            <div class="sidebar-header mb-4 d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="h4 fw-bold text-primary mb-0">Alfresco</h2>
                    <p class="x-small text-muted mb-0">Rental Management</p>
                </div>
                <!-- Desktop Notifications -->
                <div class="dropdown">
                    <button class="btn btn-link link-dark p-0 text-decoration-none shadow-none position-relative" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-bell-fill text-muted"></i>
                        <span id="notifBadgeDesktop" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none" style="font-size: 0.5rem; padding: 0.35em 0.5em;">0</span>
                    </button>
                    <ul class="dropdown-menu shadow-lg border-0 mt-3 p-0" style="width: 320px;">
                        <li class="px-3 py-2 bg-light border-bottom">
                            <h6 class="mb-0 fw-bold small">Alerts & Notifications</h6>
                        </li>
                        <div id="notifContentDesktop" style="max-height: 400px; overflow-y: auto;">
                            <!-- Same content as mobile -->
                        </div>
                    </ul>
                </div>
            </div>
            <nav class="nav flex-column flex-grow-1">
                <?php $role = $_SESSION['role']; ?>
                <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                <?php if ($role == 'admin'): ?>
                    <a href="tenants.php" class="nav-link"><i class="bi bi-people-fill"></i> Manage Tenants</a>
                    <a href="rent.php" class="nav-link"><i class="bi bi-wallet2"></i> Manage Rent</a>
                    <a href="categories.php" class="nav-link"><i class="bi bi-tags-fill"></i> Categories</a>
                    <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart-fill"></i> Reports</a>
                <?php else: ?>
                    <a href="pos.php" class="nav-link"><i class="bi bi-cart-fill"></i> POS Module</a>
                    <a href="inventory.php" class="nav-link"><i class="bi bi-box-seam-fill"></i> Inventory</a>
                    <a href="history.php" class="nav-link"><i class="bi bi-clock-history"></i> Sales History</a>
                <?php endif; ?>
            </nav>
            <div class="sidebar-footer mt-auto pt-3 border-top">
                <div class="d-flex align-items-center mb-3">
                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold me-2">
                        <?php echo substr($_SESSION['full_name'], 0, 1); ?>
                    </div>
                    <div class="overflow-hidden">
                        <p class="small mb-0 fw-bold text-truncate"><?php echo $_SESSION['full_name']; ?></p>
                        <p class="x-small text-muted mb-0 text-capitalize text-truncate"><?php echo $_SESSION['role']; ?></p>
                    </div>
                </div>
                <a href="../auth/logout.php" class="btn btn-outline-danger btn-sm w-100 py-2"><i class="bi bi-box-arrow-right me-1"></i> Logout</a>
            </div>
        </aside>

        <!-- Mobile Offcanvas Sidebar -->
        <div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarOffcanvas" style="width: 280px;">
            <div class="offcanvas-header border-bottom">
                <h5 class="offcanvas-title fw-bold text-primary">Alfresco</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column">
                <nav class="nav flex-column flex-grow-1">
                    <a href="dashboard.php" class="nav-link"><i class="bi bi-grid-1x2-fill"></i> Dashboard</a>
                    <?php if ($role == 'admin'): ?>
                        <a href="tenants.php" class="nav-link"><i class="bi bi-people-fill"></i> Manage Tenants</a>
                        <a href="rent.php" class="nav-link"><i class="bi bi-wallet2"></i> Manage Rent</a>
                        <a href="categories.php" class="nav-link"><i class="bi bi-tags-fill"></i> Categories</a>
                        <a href="reports.php" class="nav-link"><i class="bi bi-bar-chart-fill"></i> Reports</a>
                    <?php else: ?>
                        <a href="pos.php" class="nav-link"><i class="bi bi-cart-fill"></i> POS Module</a>
                        <a href="inventory.php" class="nav-link"><i class="bi bi-box-seam-fill"></i> Inventory</a>
                        <a href="history.php" class="nav-link"><i class="bi bi-clock-history"></i> Sales History</a>
                    <?php endif; ?>
                </nav>
            </div>
        </div>

        <main class="main-content">
<script>
// Notification system
function loadNotifications() {
    $.get('../api/get_notifications.php', function(response) {
        try {
            const res = JSON.parse(response);
            if (res.status === 'success') {
                const list = $('#notifContent');
                const badge = $('#notifBadge');
                const count = res.data.length;

                if (count > 0) {
                    badge.removeClass('d-none').text(count);
                    $('#notifBadgeDesktop').removeClass('d-none').text(count);
                    let html = '';
                    res.data.forEach(n => {
                        html += `
                            <li class="p-3 border-bottom hover-bg-light transition-all">
                                <div class="d-flex gap-3">
                                    <div class="avatar-sm bg-${n.type}-subtle text-${n.type} rounded-circle">
                                        <i class="bi ${n.icon}-fill"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <h6 class="mb-0 small fw-bold">${n.title}</h6>
                                        <p class="mb-0 x-small text-muted mt-1">${n.message}</p>
                                    </div>
                                </div>
                            </li>
                        `;
                    });
                    list.html(html);
                    $('#notifContentDesktop').html(html);
                } else {
                    badge.addClass('d-none');
                    $('#notifBadgeDesktop').addClass('d-none');
                    const emptyHtml = '<li class="p-4 text-center text-muted small">No new notifications</li>';
                    list.html(emptyHtml);
                    $('#notifContentDesktop').html(emptyHtml);
                }
            }
        } catch(e) {}
    });
}

document.addEventListener('DOMContentLoaded', function() {
    loadNotifications();
    setInterval(loadNotifications, 30000); // Refresh every 30s

    const currentPath = window.location.pathname;
    document.querySelectorAll('.nav-link').forEach(link => {
        if (currentPath.includes(link.getAttribute('href'))) {
            link.classList.add('active');
        }
    });
});
</script>
