<?php 
require_once '../includes/header.php';

// Verifica se há itens no carrinho
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['carrinho']) || empty($_SESSION['carrinho'])) {
    header('Location: /yahe/carrinho');
    exit;
}

$total = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $total += $item['preco'] * $item['quantidade'];
}
?>

<div class="ui container">
    <div class="ui breadcrumb">
        <a href="/yahe/" class="section">Início</a>
        <i class="right angle icon divider"></i>
        <a href="/yahe/carrinho" class="section">Carrinho</a>
        <i class="right angle icon divider"></i>
        <div class="active section">Checkout</div>
    </div>

    <h1 class="ui header">Finalizar Compra</h1>

    <div class="ui stackable grid">
        <!-- Formulário de Checkout -->
        <div class="ten wide column">
            <form class="ui form" id="checkoutForm">
                <!-- Dados Pessoais -->
                <h4 class="ui dividing header">Dados Pessoais</h4>
                <div class="two fields">
                    <div class="field">
                        <label>Nome</label>
                        <input type="text" name="nome" placeholder="Nome completo" required>
                    </div>
                    <div class="field">
                        <label>CPF</label>
                        <input type="text" name="cpf" placeholder="000.000.000-00" required>
                    </div>
                </div>
                <div class="two fields">
                    <div class="field">
                        <label>Email</label>
                        <input type="email" name="email" placeholder="email@exemplo.com" required>
                    </div>
                    <div class="field">
                        <label>Telefone</label>
                        <input type="tel" name="telefone" placeholder="(00) 00000-0000" required>
                    </div>
                </div>

                <!-- Endereço de Entrega -->
                <h4 class="ui dividing header">Endereço de Entrega</h4>
                <div class="field">
                    <label>CEP</label>
                    <div class="ui action input">
                        <input type="text" name="cep" placeholder="00000-000" required>
                        <button class="ui button" type="button" onclick="buscarCEP()">Buscar</button>
                    </div>
                </div>
                <div class="field">
                    <label>Endereço</label>
                    <input type="text" name="endereco" placeholder="Rua, Avenida, etc" required>
                </div>
                <div class="three fields">
                    <div class="field">
                        <label>Número</label>
                        <input type="text" name="numero" placeholder="Nº" required>
                    </div>
                    <div class="field">
                        <label>Complemento</label>
                        <input type="text" name="complemento" placeholder="Apto, Sala, etc">
                    </div>
                    <div class="field">
                        <label>Bairro</label>
                        <input type="text" name="bairro" required>
                    </div>
                </div>
                <div class="two fields">
                    <div class="field">
                        <label>Cidade</label>
                        <input type="text" name="cidade" required>
                    </div>
                    <div class="field">
                        <label>Estado</label>
                        <select class="ui dropdown" name="estado" required>
                            <option value="">Selecione</option>
                            <option value="AC">Acre</option>
                            <option value="AL">Alagoas</option>
                            <!-- Adicionar todos os estados -->
                        </select>
                    </div>
                </div>

                <!-- Forma de Pagamento -->
                <h4 class="ui dividing header">Forma de Pagamento</h4>
                <div class="field">
                    <div class="ui radio checkbox">
                        <input type="radio" name="payment" value="pix" checked>
                        <label>PIX</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui radio checkbox">
                        <input type="radio" name="payment" value="card">
                        <label>Cartão de Crédito</label>
                    </div>
                </div>

                <!-- Campos do Cartão (inicialmente ocultos) -->
                <div id="cardFields" style="display: none;">
                    <div class="field">
                        <label>Número do Cartão</label>
                        <input type="text" name="card_number" placeholder="0000 0000 0000 0000">
                    </div>
                    <div class="three fields">
                        <div class="field">
                            <label>Validade</label>
                            <input type="text" name="card_expiry" placeholder="MM/AA">
                        </div>
                        <div class="field">
                            <label>CVV</label>
                            <input type="text" name="card_cvv" placeholder="000">
                        </div>
                        <div class="field">
                            <label>Parcelas</label>
                            <select class="ui dropdown" name="installments">
                                <option value="1">1x de R$ <?php echo number_format($total, 2, ',', '.'); ?></option>
                                <option value="2">2x de R$ <?php echo number_format($total/2, 2, ',', '.'); ?></option>
                                <option value="3">3x de R$ <?php echo number_format($total/3, 2, ',', '.'); ?></option>
                                <!-- Adicionar mais parcelas -->
                            </select>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- Resumo do Pedido -->
        <div class="six wide column">
            <div class="ui segment">
                <h3 class="ui header">Resumo do Pedido</h3>
                <div class="ui divided items">
                    <?php foreach ($_SESSION['carrinho'] as $item): ?>
                    <div class="item">
                        <div class="ui tiny image">
                            <img src="/yahe/assets/images/products/<?php echo $item['imagem']; ?>">
                        </div>
                        <div class="content">
                            <div class="header"><?php echo $item['nome']; ?></div>
                            <div class="meta">
                                <span>Qtd: <?php echo $item['quantidade']; ?></span>
                            </div>
                            <div class="description">
                                R$ <?php echo number_format($item['preco'] * $item['quantidade'], 2, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
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
                            <div class="description" id="shipping-cost">Calcular</div>
                        </div>
                    </div>
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
                <button class="ui fluid primary button" onclick="finalizarCompra()">
                    Finalizar Compra
                </button>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
    $('.ui.radio.checkbox').checkbox();
    
    // Toggle campos do cartão
    $('input[name="payment"]').change(function() {
        if ($(this).val() === 'card') {
            $('#cardFields').show();
        } else {
            $('#cardFields').hide();
        }
    });
});

function buscarCEP() {
    let cep = $('input[name="cep"]').val().replace(/\D/g, '');
    
    if (cep.length !== 8) {
        alert('CEP inválido');
        return;
    }
    
    $.get(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
        if (!data.erro) {
            $('input[name="endereco"]').val(data.logradouro);
            $('input[name="bairro"]').val(data.bairro);
            $('input[name="cidade"]').val(data.localidade);
            $('select[name="estado"]').dropdown('set selected', data.uf);
        }
    });
}

function finalizarCompra() {
    if (!$('#checkoutForm')[0].checkValidity()) {
        alert('Por favor, preencha todos os campos obrigatórios');
        return;
    }
    
    let formData = new FormData($('#checkoutForm')[0]);
    
    $.ajax({
        url: '/yahe/checkout/finalizar.php',
        method: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                window.location.href = '/yahe/checkout/confirmacao.php?order=' + response.order_id;
            } else {
                alert(response.message || 'Erro ao processar pedido');
            }
        },
        error: function() {
            alert('Erro ao processar pedido');
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?> 