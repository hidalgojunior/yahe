<?php
session_start();
require_once '../config/database.php';
require_once '../includes/classes/Pedido.php';
require_once '../includes/classes/Pagamento.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('HTTP/1.1 400 Bad Request');
    exit;
}

// Verifica se há itens no carrinho
if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    echo json_encode(['success' => false, 'message' => 'Carrinho vazio']);
    exit;
}

try {
    // Recebe os dados do formulário
    $dados = [
        'order_id' => uniqid('ORDER'),
        'nome' => $_POST['nome'] ?? '',
        'cpf' => $_POST['cpf'] ?? '',
        'email' => $_POST['email'] ?? '',
        'telefone' => $_POST['telefone'] ?? '',
        'cep' => $_POST['cep'] ?? '',
        'endereco' => $_POST['endereco'] ?? '',
        'numero' => $_POST['numero'] ?? '',
        'complemento' => $_POST['complemento'] ?? '',
        'bairro' => $_POST['bairro'] ?? '',
        'cidade' => $_POST['cidade'] ?? '',
        'estado' => $_POST['estado'] ?? '',
        'payment' => $_POST['payment'] ?? '',
    ];

    // Validação básica
    $campos_obrigatorios = ['nome', 'cpf', 'email', 'telefone', 'cep', 'endereco', 'numero', 'bairro', 'cidade', 'estado', 'payment'];
    foreach ($campos_obrigatorios as $campo) {
        if (empty($dados[$campo])) {
            throw new Exception('Preencha todos os campos obrigatórios');
        }
    }

    // Calcula o total
    $total = 0;
    foreach ($_SESSION['carrinho'] as $item) {
        $total += $item['preco'] * $item['quantidade'];
    }
    $dados['total'] = $total;

    // Instancia as classes
    $pedido = new Pedido($pdo);
    $pagamento = new Pagamento($pdo);

    // Processa o pagamento
    if ($dados['payment'] === 'pix') {
        // Cria o pedido primeiro
        $pedido_id = $pedido->criar($dados, $_SESSION['carrinho'], [
            'type' => 'pix',
            'status' => 'pending'
        ]);
        
        // Busca o pedido completo
        $pedido_dados = $pedido->buscarPorOrderId($dados['order_id']);
        
        // Gera o PIX
        $payment_info = $pagamento->gerarPix($pedido_dados);
        
    } else {
        // Processa pagamento com cartão
        $card_data = [
            'token' => $_POST['card_token'],
            'parcelas' => $_POST['installments']
        ];
        
        // Cria o pedido primeiro
        $pedido_id = $pedido->criar($dados, $_SESSION['carrinho'], [
            'type' => 'card',
            'status' => 'processing'
        ]);
        
        // Busca o pedido completo
        $pedido_dados = $pedido->buscarPorOrderId($dados['order_id']);
        
        // Processa o cartão
        $payment_info = $pagamento->processarCartao($pedido_dados, $card_data);
    }

    // Atualiza o pagamento com as informações do gateway
    $pedido->atualizarStatusPagamento($dados['order_id'], $payment_info['status']);

    // Limpa o carrinho
    unset($_SESSION['carrinho']);

    // Retorna sucesso
    echo json_encode([
        'success' => true,
        'order_id' => $dados['order_id']
    ]);
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 