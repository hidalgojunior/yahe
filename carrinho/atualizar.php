<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantity = $_POST['quantity'] ?? 0;
    $absolute = $_POST['absolute'] ?? false;

    if ($product_id && isset($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as &$item) {
            if ($item['id'] == $product_id) {
                if ($absolute) {
                    $item['quantidade'] = max(1, min(10, intval($quantity)));
                } else {
                    $item['quantidade'] = max(1, min(10, $item['quantidade'] + intval($quantity)));
                }
                break;
            }
        }
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false]); 