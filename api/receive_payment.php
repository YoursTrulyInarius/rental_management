<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tenant_id = $_POST['tenant_id'];
    $month_year = $_POST['month_year'];

    try {
        $stmt = $pdo->prepare("UPDATE rent_payments SET status = 'paid', payment_date = CURRENT_TIMESTAMP WHERE tenant_id = ? AND month_year = ?");
        $stmt->execute([$tenant_id, $month_year]);

        echo json_encode(['status' => 'success', 'message' => 'Payment received successfully!']);
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
