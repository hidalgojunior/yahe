<?php 
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../config/database.php';

// Busca dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$usuario = $stmt->fetch();

// Busca últimos pedidos
$stmt = $pdo->prepare("
    SELECT p.*, pg.status as pagamento_status 
    FROM pedidos p 
    LEFT JOIN pagamentos pg ON pg.pedido_id = p.id 
    WHERE p.cliente_email = :email 
    ORDER BY p.created_at DESC 
    LIMIT 5
");
$stmt->execute(['email' => $usuario['email']]);
$ultimos_pedidos = $stmt->fetchAll();

// Busca endereços
$stmt = $pdo->prepare("SELECT * FROM enderecos WHERE usuario_id = :usuario_id");
$stmt->execute(['usuario_id' => $_SESSION['user_id']]);
$enderecos = $stmt->fetchAll();
?>

<div class="ui container">
    <div class="ui grid stackable">
        <!-- Menu lateral -->
        <div class="four wide column">
            <div class="ui vertical fluid menu">
                <div class="item">
                    <div class="header">Minha Conta</div>
                    <div class="menu">
                        <a class="active item" href="/yahe/minha-conta">
                            <i class="home icon"></i> Início
                        </a>
                        <a class="item" href="/yahe/minha-conta/pedidos">
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
                <i class="home icon"></i>
                <div class="content">
                    Bem-vindo(a), <?php echo htmlspecialchars($usuario['nome']); ?>!
                    <div class="sub header">Gerencie seus pedidos e informações</div>
                </div>
            </h2>
            
            <div class="ui divider"></div>
            
            <!-- Últimos Pedidos -->
            <h3 class="ui header">Últimos Pedidos</h3>
            <?php if (empty($ultimos_pedidos)): ?>
                <div class="ui message">
                    <p>Você ainda não realizou nenhum pedido.</p>
                </div>
            <?php else: ?>
                <table class="ui celled table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($ultimos_pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo $pedido['order_id']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($pedido['created_at'])); ?></td>
                            <td>
                                <div class="ui label <?php 
                                    echo match($pedido['status']) {
                                        'pendente' => 'yellow',
                                        'pago' => 'green',
                                        'cancelado' => 'red',
                                        default => 'grey'
                                    };
                                ?>">
                                    <?php echo ucfirst($pedido['status']); ?>
                                </div>
                            </td>
                            <td>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                            <td>
                                <a href="/yahe/minha-conta/pedido.php?id=<?php echo $pedido['order_id']; ?>" 
                                   class="ui tiny button">
                                    Detalhes
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <a href="/yahe/minha-conta/pedidos" class="ui right floated button">
                    Ver todos os pedidos
                </a>
            <?php endif; ?>
            
            <div class="ui divider"></div>
            
            <!-- Endereços -->
            <h3 class="ui header">Meus Endereços</h3>
            <?php if (empty($enderecos)): ?>
                <div class="ui message">
                    <p>Você ainda não cadastrou nenhum endereço.</p>
                </div>
            <?php else: ?>
                <div class="ui two cards">
                    <?php foreach ($enderecos as $endereco): ?>
                    <div class="card">
                        <div class="content">
                            <?php if ($endereco['principal']): ?>
                            <div class="ui right corner label">
                                <i class="star icon"></i>
                            </div>
                            <?php endif; ?>
                            
                            <div class="header">
                                <?php echo $endereco['rua']; ?>, <?php echo $endereco['numero']; ?>
                            </div>
                            <div class="meta">
                                <?php echo $endereco['bairro']; ?>
                            </div>
                            <div class="description">
                                <?php echo $endereco['cidade']; ?> - <?php echo $endereco['estado']; ?><br>
                                CEP: <?php echo $endereco['cep']; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                
                <a href="/yahe/minha-conta/enderecos" class="ui right floated button">
                    Gerenciar endereços
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?> 