<?php
session_start();
require_once '../config/db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 'tenant') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $cart = json_decode($_POST['cart'], true);
    
    if (empty($cart)) {
        echo json_encode(['status' => 'error', 'message' => 'Cart is empty']);
        exit;
    }

    try {
        $pdo->beginTransaction();

        // Get tenant ID
        $stmt = $pdo->prepare("SELECT id FROM tenants WHERE user_id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $tenant_id = $stmt->fetchColumn();

        // Calculate total
        $total_amount = 0;
        foreach ($cart as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        // Insert transaction
        $stmt = $pdo->prepare("INSERT INTO transactions (tenant_id, total_amount, operator_id) VALUES (?, ?, ?)");
        $stmt->execute([$tenant_id, $total_amount, $_SESSION['user_id']]);
        $transaction_id = $pdo->lastInsertId();

        // Insert items and update stock
        foreach ($cart as $item) {
            // Check stock first
            $st = $pdo->prepare("SELECT stock_quantity FROM inventory WHERE id = ? FOR UPDATE");
            $st->execute([$item['id']]);
            $current_stock = $st->fetchColumn();

            if ($current_stock < $item['quantity']) {
                throw new Exception("Insufficient stock for item: " . $item['name']);
            }

            // Insert item
            $st = $pdo->prepare("INSERT INTO transaction_items (transaction_id, item_id, quantity, price_at_sale) VALUES (?, ?, ?, ?)");
            $st->execute([$transaction_id, $item['id'], $item['quantity'], $item['price']]);

            // Update stock
            $st = $pdo->prepare("UPDATE inventory SET stock_quantity = stock_quantity - ? WHERE id = ?");
            $st->execute([$item['quantity'], $item['id']]);
        }

        $pdo->commit();
        echo json_encode(['status' => 'success', 'transaction_id' => $transaction_id]);

    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    }
}
?>
