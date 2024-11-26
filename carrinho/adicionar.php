<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'] ?? null;
    $quantidade = $_POST['quantidade'] ?? 1;
    $tamanho = $_POST['tamanho'] ?? null;
    $cor = $_POST['cor'] ?? null;
    
    // TODO: Buscar informações do produto do banco de dados
    $produto = [
        'id' => $product_id,
        'nome' => 'Produto Exemplo',
        'preco' => 49.90,
        'imagem' => 'produto.jpg'
    ];
    
    if (!isset($_SESSION['carrinho'])) {
        $_SESSION['carrinho'] = [];
    }
    
    $item = [
        'id' => $produto['id'],
        'nome' => $produto['nome'],
        'preco' => $produto['preco'],
        'quantidade' => max(1, min(10, intval($quantidade))),
        'imagem' => $produto['imagem'],
        'tamanho' => $tamanho,
        'cor' => $cor
    ];
    
    // Verifica se o produto já existe no carrinho
    $found = false;
    foreach ($_SESSION['carrinho'] as &$cartItem) {
        if ($cartItem['id'] == $product_id && 
            $cartItem['tamanho'] == $tamanho && 
            $cartItem['cor'] == $cor) {
            $cartItem['quantidade'] += $item['quantidade'];
            $cartItem['quantidade'] = min(10, $cartItem['quantidade']);
            $found = true;
            break;
        }
    }
    
    if (!$found) {
        $_SESSION['carrinho'][] = $item;
    }
    
    header('Content-Type: application/json');
    echo json_encode(['success' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['success' => false]); 