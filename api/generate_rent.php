<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $month_year = $_POST['month_year']; // Format YYYY-MM from html input month
    
    // Convert to MM-YYYY for DB consistency (based on schema comment)
    $formatted_month = date('m-Y', strtotime($month_year));

    try {
        $pdo->beginTransaction();

        $tenants = $pdo->query("SELECT id, rent_amount FROM tenants WHERE status = 'active'")->fetchAll();
        $count = 0;

        foreach ($tenants as $t) {
            // Check if already exists
            $check = $pdo->prepare("SELECT id FROM rent_payments WHERE tenant_id = ? AND month_year = ?");
            $check->execute([$t['id'], $formatted_month]);
            
            if (!$check->fetch()) {
                $stmt = $pdo->prepare("INSERT INTO rent_payments (tenant_id, amount, month_year, status) VALUES (?, ?, ?, 'pending')");
                $stmt->execute([$t['id'], $t['rent_amount'], $formatted_month]);
                $count++;
            }
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'message' => "Successfully generated dues for $count tenants."]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
