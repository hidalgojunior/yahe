<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;

    if ($product_id && isset($_SESSION['carrinho'])) {
        foreach ($_SESSION['carrinho'] as $key => $item) {
            if ($item['id'] == $product_id) {
                unset($_SESSION['carrinho'][$key]);
                break;
            }
        }
        $_SESSION['carrinho'] = array_values($_SESSION['carrinho']); // Reindexar array
    }

    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false]); 