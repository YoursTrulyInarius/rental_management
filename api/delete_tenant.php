<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id'];

    try {
        $pdo->beginTransaction();

        // Get user_id first to delete from users table too
        $stmt = $pdo->prepare("SELECT user_id FROM tenants WHERE id = ?");
        $stmt->execute([$id]);
        $user_id = $stmt->fetchColumn();

        if ($user_id) {
            // Delete tenant (cascades or manual)
            // Manual delete to be safe
            $pdo->prepare("DELETE FROM transactions WHERE tenant_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM inventory WHERE tenant_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM rent_payments WHERE tenant_id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM tenants WHERE id = ?")->execute([$id]);
            $pdo->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => 'Tenant and associated data deleted successfully.']);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
