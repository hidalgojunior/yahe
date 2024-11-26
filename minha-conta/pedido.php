<?php 
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../config/database.php';

$order_id = $_GET['id'] ?? null;

if (!$order_id) {
    header('Location: /yahe/minha-conta/pedidos');
    exit;
}

// Busca o pedido
$stmt = $pdo->prepare("
    SELECT p.*, pg.status as pagamento_status, pg.qr_code_pix
    FROM pedidos p 
    LEFT JOIN pagamentos pg ON pg.pedido_id = p.id 
    WHERE p.order_id = :order_id AND p.cliente_email = :email
");
$stmt->execute([
    'order_id' => $order_id,
    'email' => $_SESSION['user_email']
]);
$pedido = $stmt->fetch();

if (!$pedido) {
    header('Location: /yahe/minha-conta/pedidos');
    exit;
}

// Busca os itens do pedido
$stmt = $pdo->prepare("
    SELECT pi.*, pr.nome as produto_nome, pr.imagem as produto_imagem
    FROM pedidos_itens pi
    JOIN produtos pr ON pr.id = pi.produto_id
    WHERE pi.pedido_id = :pedido_id
");
$stmt->execute(['pedido_id' => $pedido['id']]);
$itens = $stmt->fetchAll();
?>

<div class="ui container">
    <div class="ui grid stackable">
        <!-- Menu lateral -->
        <div class="four wide column">
            <div class="ui vertical fluid menu">
                <div class="item">
                    <div class="header">Minha Conta</div>
                    <div class="menu">
                        <a class="item" href="/yahe/minha-conta">
                            <i class="home icon"></i> Início
                        </a>
                        <a class="active item" href="/yahe/minha-conta/pedidos">
                            <i class="shopping bag icon"></i> Meus Pedidos
                        </a>
                        <a class="item" href="/yahe/minha-conta/enderecos">
                            <i class="map marker alternate icon"></i> Endereços
                        </a>
                        <a class="item" href="/yahe/minha-conta/dados">
                            <i class="user icon"></i> Meus Dados
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Conteúdo principal -->
        <div class="twelve wide column">
            <h2 class="ui header">
                <i class="shopping bag icon"></i>
                <div class="content">
                    Pedido #<?php echo $pedido['order_id']; ?>
                    <div class="sub header">
                        Realizado em <?php echo date('d/m/Y \à\s H:i', strtotime($pedido['created_at'])); ?>
                    </div>
                </div>
            </h2>
            
            <!-- Status do Pedido -->
            <div class="ui segment">
                <h4 class="ui header">Status do Pedido</h4>
                <div class="ui steps">
                    <?php 
                    $status_ordem = ['pendente', 'pago', 'preparando', 'enviado', 'entregue'];
                    $status_atual = array_search($pedido['status'], $status_ordem);
                    
                    foreach ($status_ordem as $index => $status):
                        $class = '';
                        if ($index < $status_atual) $class = 'completed';
                        if ($index === $status_atual) $class = 'active';
                        if ($pedido['status'] === 'cancelado') $class = 'disabled';
                    ?>
                    <div class="<?php echo $class; ?> step">
                        <i class="<?php 
                            echo match($status) {
                                'pendente' => 'clock',
                                'pago' => 'check',
                                'preparando' => 'box',
                                'enviado' => 'truck',
                                'entregue' => 'home',
                                default => 'circle'
                            };
                        ?> icon"></i>
                        <div class="content">
                            <div class="title"><?php echo ucfirst($status); ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <?php if ($pedido['status'] === 'cancelado'): ?>
                <div class="ui red message">
                    <i class="close icon"></i>
                    <div class="header">Pedido Cancelado</div>
                    <p>Este pedido foi cancelado.</p>
                </div>
                <?php endif; ?>
            </div>
            
            <!-- Itens do Pedido -->
            <div class="ui segment">
                <h4 class="ui header">Itens do Pedido</h4>
                <div class="ui items">
                    <?php foreach ($itens as $item): ?>
                    <div class="item">
                        <div class="ui tiny image">
                            <img src="/yahe/assets/images/products/<?php echo $item['produto_imagem']; ?>">
                        </div>
                        <div class="content">
                            <div class="header"><?php echo $item['produto_nome']; ?></div>
                            <div class="meta">
                                <span>Quantidade: <?php echo $item['quantidade']; ?></span>
                                <?php if ($item['tamanho']): ?>
                                <span>Tamanho: <?php echo $item['tamanho']; ?></span>
                                <?php endif; ?>
                                <?php if ($item['cor']): ?>
                                <span>Cor: <?php echo $item['cor']; ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="description">
                                <p>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></p>
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
                            <?php echo $pedido['endereco_rua']; ?>, 
                            <?php echo $pedido['endereco_numero']; ?><br>
                            <?php if ($pedido['endereco_complemento']): ?>
                                <?php echo $pedido['endereco_complemento']; ?><br>
                            <?php endif; ?>
                            <?php echo $pedido['endereco_bairro']; ?><br>
                            <?php echo $pedido['endereco_cidade']; ?> - 
                            <?php echo $pedido['endereco_estado']; ?><br>
                            CEP: <?php echo $pedido['endereco_cep']; ?>
                        </p>
                    </div>
                    <div class="eight wide column">
                        <h4>Pagamento</h4>
                        <p>
                            Forma: <?php echo $pedido['forma_pagamento'] === 'pix' ? 'PIX' : 'Cartão de Crédito'; ?><br>
                            Status: <?php 
                                echo match($pedido['pagamento_status']) {
                                    'pending' => 'Pendente',
                                    'approved' => 'Aprovado',
                                    'failed' => 'Falhou',
                                    'cancelled' => 'Cancelado',
                                    default => 'Desconhecido'
                                };
                            ?>
                        </p>
                        
                        <?php if ($pedido['forma_pagamento'] === 'pix' && $pedido['pagamento_status'] === 'pending'): ?>
                        <div class="ui segment">
                            <h5>QR Code PIX</h5>
                            <img src="<?php echo $pedido['qr_code_pix']; ?>" alt="QR Code PIX" style="max-width: 200px;">
                        </div>
                        <?php endif; ?>
                        
                        <div class="ui large text">
                            Total: R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 