<?php
require_once '../config/database.php';
require_once '../includes/classes/Pagamento.php';
require_once '../includes/classes/Email.php';

// Recebe o JSON do webhook
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Verifica a assinatura do webhook (recomendado implementar)
// $signature = $_SERVER['HTTP_ASAAS_SIGNATURE'] ?? '';
// if (!verificarAssinatura($json, $signature)) {
//     http_response_code(401);
//     exit;
// }

if ($data && isset($data['event']) && strpos($data['event'], 'PAYMENT_') === 0) {
    $pagamento = new Pagamento($pdo);
    
    if ($pagamento->processarWebhook($data)) {
        // Envia e-mail de confirmação se o pagamento foi aprovado
        if (in_array($data['payment']['status'], ['CONFIRMED', 'RECEIVED'])) {
            $pedido = new Pedido($pdo);
            $pedido_dados = $pedido->buscarPorOrderId($order_id);
            $email = new Email();
            $email->enviarConfirmacaoPedido($pedido_dados);
        }
        
        http_response_code(200);
        echo json_encode(['status' => 'success']);
        exit;
    }
}

http_response_code(400);
echo json_encode(['status' => 'error']); 