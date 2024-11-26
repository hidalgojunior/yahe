<?php 
require_once '../includes/header.php';

// Inicializa a sessão se ainda não estiver iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Inicializa o carrinho se não existir
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Recupera os itens do carrinho
$itens = $_SESSION['carrinho'];
$total = 0;
?>

<div class="ui container">
    <div class="ui breadcrumb">
        <a href="/yahe/" class="section">Início</a>
        <i class="right angle icon divider"></i>
        <div class="active section">Carrinho</div>
    </div>

    <h1 class="ui header">Carrinho de Compras</h1>

    <?php if (empty($itens)): ?>
    <div class="ui placeholder segment">
        <div class="ui icon header">
            <i class="shopping cart icon"></i>
            Seu carrinho está vazio
        </div>
        <a href="/yahe/produtos" class="ui primary button">
            Continuar Comprando
        </a>
    </div>
    <?php else: ?>
    <div class="ui stackable grid">
        <!-- Lista de Produtos -->
        <div class="eleven wide column">
            <div class="ui items">
                <?php foreach ($itens as $item): 
                    $subtotal = $item['preco'] * $item['quantidade'];
                    $total += $subtotal;
                ?>
                <div class="item">
                    <div class="image">
                        <?php if (isset($item['personalizado']) && $item['personalizado']): ?>
                            <img src="<?php echo $item['preview']; ?>" alt="<?php echo $item['nome']; ?>">
                        <?php else: ?>
                            <img src="/yahe/assets/images/products/<?php echo $item['imagem']; ?>" alt="<?php echo $item['nome']; ?>">
                        <?php endif; ?>
                    </div>
                    <div class="content">
                        <div class="header"><?php echo $item['nome']; ?></div>
                        <div class="meta">
                            <span>R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></span>
                        </div>
                        <div class="description">
                            <?php if (isset($item['cor'])): ?>
                                <p>Cor: <?php echo $item['cor']; ?></p>
                            <?php endif; ?>
                            <?php if (isset($item['tamanho'])): ?>
                                <p>Tamanho: <?php echo $item['tamanho']; ?></p>
                            <?php endif; ?>
                            <?php if (isset($item['personalizado']) && $item['personalizado']): ?>
                                <p><i class="paint brush icon"></i> Produto Personalizado</p>
                            <?php endif; ?>
                        </div>
                        <div class="extra">
                            <div class="ui right floated">
                                <button class="ui icon button" onclick="removeFromCart(<?php echo $item['id']; ?>)">
                                    <i class="trash icon"></i>
                                </button>
                            </div>
                            <div class="ui action input">
                                <button class="ui icon button" onclick="updateQuantity(<?php echo $item['id']; ?>, -1)">
                                    <i class="minus icon"></i>
                                </button>
                                <input type="number" value="<?php echo $item['quantidade']; ?>" 
                                       min="1" max="10" 
                                       onchange="updateQuantity(<?php echo $item['id']; ?>, this.value, true)">
                                <button class="ui icon button" onclick="updateQuantity(<?php echo $item['id']; ?>, 1)">
                                    <i class="plus icon"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Resumo do Pedido -->
        <div class="five wide column">
            <div class="ui segment">
                <h3 class="ui header">Resumo do Pedido</h3>
                <div class="ui divider"></div>
                <div class="ui list">
                    <div class="item">
                        <div class="content">
                            <div class="header">Subtotal</div>
                            <div class="description">R$ <?php echo number_format($total, 2, ',', '.'); ?></div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="content">
                            <div class="header">Frete</div>
                            <div class="description">Calculado no checkout</div>
                        </div>
                    </div>
                </div>
                <div class="ui divider"></div>
                <div class="ui list">
                    <div class="item">
                        <div class="content">
                            <div class="header">Total</div>
                            <div class="description">
                                <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="ui divider"></div>
                <a href="/yahe/checkout" class="ui fluid primary button">Finalizar Compra</a>
                <a href="/yahe/produtos" class="ui fluid basic button" style="margin-top: 1em;">
                    Continuar Comprando
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
function updateQuantity(productId, delta, absolute = false) {
    $.ajax({
        url: '/yahe/carrinho/atualizar.php',
        method: 'POST',
        data: {
            product_id: productId,
            quantity: delta,
            absolute: absolute
        },
        success: function(response) {
            location.reload();
        },
        error: function() {
            alert('Erro ao atualizar quantidade');
        }
    });
}

function removeFromCart(productId) {
    if (confirm('Tem certeza que deseja remover este item?')) {
        $.ajax({
            url: '/yahe/carrinho/remover.php',
            method: 'POST',
            data: {
                product_id: productId
            },
            success: function(response) {
                location.reload();
            },
            error: function() {
                alert('Erro ao remover item');
            }
        });
    }
}
</script>

<?php require_once '../includes/footer.php'; ?> 