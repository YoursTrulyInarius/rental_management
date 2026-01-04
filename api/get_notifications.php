<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$notifications = [];

try {
    if ($role == 'tenant') {
        // Get tenant ID
        $stmt = $pdo->prepare("SELECT id FROM tenants WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $tenant_id = $stmt->fetchColumn();

        if ($tenant_id) {
            // 1. Check Low Stock (< 10 units)
            $st = $pdo->prepare("SELECT name, stock_quantity FROM inventory WHERE tenant_id = ? AND stock_quantity < 10");
            $st->execute([$tenant_id]);
            while ($item = $st->fetch()) {
                $notifications[] = [
                    'type' => 'warning',
                    'icon' => 'bi-exclamation-triangle',
                    'title' => 'Low Stock Alert',
                    'message' => "{$item['name']} is running low ({$item['stock_quantity']} left)."
                ];
            }

            // 2. Check Pending Rent
            $st = $pdo->prepare("SELECT month_year FROM rent_payments WHERE tenant_id = ? AND status = 'pending'");
            $st->execute([$tenant_id]);
            while ($rent = $st->fetch()) {
                $notifications[] = [
                    'type' => 'danger',
                    'icon' => 'bi-wallet2',
                    'title' => 'Rent Due',
                    'message' => "Your rent for {$rent['month_year']} is still pending."
                ];
            }
        }
    } else if ($role == 'admin') {
        // Admin: Total Pending Rents
        $pending = $pdo->query("SELECT t.business_name, r.month_year FROM rent_payments r JOIN tenants t ON r.tenant_id = t.id WHERE r.status = 'pending'")->fetchAll();
        if (count($pending) > 0) {
            $notifications[] = [
                'type' => 'info',
                'icon' => 'bi-info-circle',
                'title' => 'Collection Reminder',
                'message' => "There are " . count($pending) . " pending rent collections."
            ];
        }
    }

    echo json_encode(['status' => 'success', 'data' => $notifications]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
?>
