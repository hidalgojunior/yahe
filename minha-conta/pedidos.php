<?php 
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../config/database.php';

// Busca todos os pedidos do usuário
$stmt = $pdo->prepare("
    SELECT p.*, pg.status as pagamento_status 
    FROM pedidos p 
    LEFT JOIN pagamentos pg ON pg.pedido_id = p.id 
    WHERE p.cliente_email = :email 
    ORDER BY p.created_at DESC
");
$stmt->execute(['email' => $_SESSION['user_email']]);
$pedidos = $stmt->fetchAll();
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
                    Meus Pedidos
                    <div class="sub header">Histórico completo de pedidos</div>
                </div>
            </h2>
            
            <?php if (empty($pedidos)): ?>
                <div class="ui placeholder segment">
                    <div class="ui icon header">
                        <i class="shopping bag icon"></i>
                        Você ainda não realizou nenhum pedido
                    </div>
                    <a href="/yahe/produtos" class="ui primary button">Começar a Comprar</a>
                </div>
            <?php else: ?>
                <table class="ui celled table">
                    <thead>
                        <tr>
                            <th>Pedido</th>
                            <th>Data</th>
                            <th>Status</th>
                            <th>Pagamento</th>
                            <th>Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($pedidos as $pedido): ?>
                        <tr>
                            <td>#<?php echo $pedido['order_id']; ?></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($pedido['created_at'])); ?></td>
                            <td>
                                <div class="ui label <?php 
                                    echo match($pedido['status']) {
                                        'pendente' => 'yellow',
                                        'pago' => 'green',
                                        'preparando' => 'blue',
                                        'enviado' => 'teal',
                                        'entregue' => 'green',
                                        'cancelado' => 'red',
                                        default => 'grey'
                                    };
                                ?>">
                                    <?php echo ucfirst($pedido['status']); ?>
                                </div>
                            </td>
                            <td>
                                <div class="ui label <?php 
                                    echo match($pedido['pagamento_status']) {
                                        'pending' => 'yellow',
                                        'approved' => 'green',
                                        'failed' => 'red',
                                        'cancelled' => 'grey',
                                        default => 'grey'
                                    };
                                ?>">
                                    <?php 
                                    echo match($pedido['pagamento_status']) {
                                        'pending' => 'Pendente',
                                        'approved' => 'Aprovado',
                                        'failed' => 'Falhou',
                                        'cancelled' => 'Cancelado',
                                        default => 'Desconhecido'
                                    };
                                    ?>
                                </div>
                            </td>
                            <td>R$ <?php echo number_format($pedido['valor_total'], 2, ',', '.'); ?></td>
                            <td>
                                <a href="/yahe/minha-conta/pedido.php?id=<?php echo $pedido['order_id']; ?>" 
                                   class="ui tiny primary button">
                                    Detalhes
                                </a>
                                
                                <?php if ($pedido['status'] === 'pendente'): ?>
                                <button onclick="cancelarPedido('<?php echo $pedido['order_id']; ?>')" 
                                        class="ui tiny red button">
                                    Cancelar
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function cancelarPedido(orderId) {
    if (confirm('Tem certeza que deseja cancelar este pedido?')) {
        $.post('/yahe/minha-conta/cancelar-pedido.php', {
            order_id: orderId
        }, function(response) {
            if (response.success) {
                location.reload();
            } else {
                alert('Erro ao cancelar pedido: ' + response.message);
            }
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 