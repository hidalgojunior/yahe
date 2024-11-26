<?php 
require_once '../includes/header.php';
require_once '../config/database.php';
require_once '../includes/classes/Pedido.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$order_id = $_GET['order'] ?? null;

if (!$order_id) {
    header('Location: /yahe/');
    exit;
}

// Instancia a classe Pedido
$pedido = new Pedido($pdo);

// Busca o pedido no banco
$dados_pedido = $pedido->buscarPorOrderId($order_id);

if (!$dados_pedido) {
    header('Location: /yahe/');
    exit;
}

// Usa os dados da sessão apenas para complementar informações não persistidas
$pedido_sessao = $_SESSION['ultimo_pedido'] ?? null;
?>

<div class="ui container">
    <div class="ui center aligned segment">
        <i class="huge green check circle icon"></i>
        <h1 class="ui header">
            Pedido Realizado com Sucesso!
            <div class="sub header">Pedido #<?php echo $dados_pedido['order_id']; ?></div>
        </h1>

        <?php if ($dados_pedido['forma_pagamento'] === 'pix'): ?>
        <div class="ui segment">
            <h3>Pagamento via PIX</h3>
            <p>Escaneie o QR Code abaixo para realizar o pagamento:</p>
            <div class="ui image">
                <!-- TODO: Implementar geração do QR Code -->
                <img src="<?php echo $dados_pedido['qr_code']; ?>" alt="QR Code PIX">
            </div>
            <div class="ui message">
                <p>Após o pagamento, seu pedido será processado automaticamente.</p>
            </div>
        </div>
        <?php else: ?>
        <div class="ui message">
            <p>Seu pagamento está sendo processado. Em breve você receberá um e-mail com a confirmação.</p>
        </div>
        <?php endif; ?>

        <div class="ui segment">
            <h3>Detalhes do Pedido</h3>
            <div class="ui divided items">
                <?php foreach ($dados_pedido['itens'] as $item): ?>
                <div class="item">
                    <div class="ui tiny image">
                        <img src="/yahe/assets/images/products/<?php echo $item['imagem']; ?>">
                    </div>
                    <div class="content">
                        <div class="header"><?php echo $item['nome']; ?></div>
                        <div class="meta">
                            <span>Quantidade: <?php echo $item['quantidade']; ?></span>
                        </div>
                        <div class="description">
                            <p>R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="ui divider"></div>

            <div class="ui grid">
                <div class="eight wide column">
                    <h4>Endereço de Entrega</h4>
                    <p>
                        <?php echo $dados_pedido['endereco']; ?>, 
                        <?php echo $dados_pedido['numero']; ?>
                        <?php if (!empty($dados_pedido['complemento'])): ?>
                            - <?php echo $dados_pedido['complemento']; ?>
                        <?php endif; ?>
                        <br>
                        <?php echo $dados_pedido['bairro']; ?><br>
                        <?php echo $dados_pedido['cidade']; ?> - <?php echo $dados_pedido['estado']; ?><br>
                        CEP: <?php echo $dados_pedido['cep']; ?>
                    </p>
                </div>
                <div class="eight wide column">
                    <h4>Total do Pedido</h4>
                    <p class="ui large text">
                        R$ <?php echo number_format($dados_pedido['total'], 2, ',', '.'); ?>
                    </p>
                </div>
            </div>
        </div>

        <div class="ui buttons">
            <a href="/yahe/" class="ui button">Voltar para a Loja</a>
            <a href="/yahe/minha-conta/pedidos" class="ui primary button">Meus Pedidos</a>
        </div>
    </div>
</div>

<?php 
// Limpa a sessão do último pedido após exibir
unset($_SESSION['ultimo_pedido']);
require_once '../includes/footer.php'; 
?> 