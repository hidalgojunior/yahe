<?php 
require_once '../includes/header.php';

// TODO: Buscar produto do banco de dados
$produto = [
    'id' => 1,
    'nome' => 'Camiseta Personalizada',
    'descricao' => 'Camiseta 100% algodão para personalização',
    'descricao_longa' => 'Camiseta de alta qualidade, 100% algodão, ideal para personalização. 
                         Disponível em várias cores e tamanhos. A impressão é feita com tecnologia 
                         de última geração, garantindo durabilidade e qualidade.',
    'preco' => 49.90,
    'imagens' => [
        'camiseta.jpg',
        'camiseta-2.jpg',
        'camiseta-3.jpg'
    ],
    'categoria' => 'Camisetas',
    'cores' => ['Branco', 'Preto', 'Azul', 'Vermelho'],
    'tamanhos' => ['P', 'M', 'G', 'GG'],
    'especificacoes' => [
        'Material' => '100% Algodão',
        'Gramatura' => '160g/m²',
        'Gola' => 'Careca',
        'Manga' => 'Curta'
    ]
];
?>

<div class="ui container">
    <!-- Breadcrumb -->
    <div class="ui breadcrumb">
        <a href="/yahe/" class="section">Início</a>
        <i class="right angle icon divider"></i>
        <a href="/yahe/produtos" class="section">Produtos</a>
        <i class="right angle icon divider"></i>
        <div class="active section"><?php echo $produto['nome']; ?></div>
    </div>

    <div class="ui grid">
        <!-- Galeria de Imagens -->
        <div class="six wide column">
            <div class="ui fluid card">
                <div class="image" id="mainImage">
                    <img src="/yahe/assets/images/products/<?php echo $produto['imagens'][0]; ?>">
                </div>
                <div class="extra content">
                    <div class="ui tiny images">
                        <?php foreach($produto['imagens'] as $index => $imagem): ?>
                        <img src="/yahe/assets/images/products/<?php echo $imagem; ?>" 
                             onclick="changeMainImage(this.src)"
                             class="ui image <?php echo $index === 0 ? 'active' : ''; ?>">
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Informações do Produto -->
        <div class="ten wide column">
            <h1 class="ui header"><?php echo $produto['nome']; ?></h1>
            <div class="ui divider"></div>

            <!-- Preço -->
            <div class="ui huge header">
                R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                <div class="sub header">
                    em até 12x de R$ <?php echo number_format($produto['preco']/12, 2, ',', '.'); ?>
                </div>
            </div>

            <!-- Formulário de Compra -->
            <form class="ui form">
                <!-- Cor -->
                <div class="field">
                    <label>Cor</label>
                    <select class="ui dropdown" required>
                        <option value="">Selecione a cor</option>
                        <?php foreach($produto['cores'] as $cor): ?>
                        <option value="<?php echo $cor; ?>"><?php echo $cor; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tamanho -->
                <div class="field">
                    <label>Tamanho</label>
                    <div class="ui buttons">
                        <?php foreach($produto['tamanhos'] as $tamanho): ?>
                        <div class="ui button size-button" data-value="<?php echo $tamanho; ?>">
                            <?php echo $tamanho; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="tamanho" id="tamanho" required>
                </div>

                <!-- Quantidade -->
                <div class="field">
                    <label>Quantidade</label>
                    <div class="ui action input">
                        <button class="ui icon button" onclick="updateQuantity(-1)">
                            <i class="minus icon"></i>
                        </button>
                        <input type="number" value="1" min="1" max="10" id="quantidade">
                        <button class="ui icon button" onclick="updateQuantity(1)">
                            <i class="plus icon"></i>
                        </button>
                    </div>
                </div>

                <!-- Botões de Ação -->
                <div class="ui buttons fluid">
                    <button class="ui primary button" type="button" onclick="addToCart()">
                        <i class="cart icon"></i> Adicionar ao Carrinho
                    </button>
                    <div class="or" data-text="ou"></div>
                    <button class="ui positive button" type="button" onclick="personalize()">
                        <i class="paint brush icon"></i> Personalizar Agora
                    </button>
                </div>
            </form>

            <!-- Descrição e Especificações -->
            <div class="ui top attached tabular menu">
                <a class="active item" data-tab="descricao">Descrição</a>
                <a class="item" data-tab="especificacoes">Especificações</a>
            </div>
            <div class="ui bottom attached active tab segment" data-tab="descricao">
                <p><?php echo $produto['descricao_longa']; ?></p>
            </div>
            <div class="ui bottom attached tab segment" data-tab="especificacoes">
                <table class="ui celled table">
                    <tbody>
                        <?php foreach($produto['especificacoes'] as $nome => $valor): ?>
                        <tr>
                            <td class="collapsing"><strong><?php echo $nome; ?></strong></td>
                            <td><?php echo $valor; ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializa tabs
    $('.menu .item').tab();
    
    // Inicializa dropdown
    $('.ui.dropdown').dropdown();
    
    // Seleciona tamanho
    $('.size-button').click(function() {
        $('.size-button').removeClass('primary');
        $(this).addClass('primary');
        $('#tamanho').val($(this).data('value'));
    });
});

function changeMainImage(src) {
    $('#mainImage img').attr('src', src);
    $('.ui.tiny.images img').removeClass('active');
    $(`img[src="${src}"]`).addClass('active');
}

function updateQuantity(delta) {
    let input = $('#quantidade');
    let value = parseInt(input.val()) + delta;
    if (value >= 1 && value <= 10) {
        input.val(value);
    }
    return false;
}

function personalize() {
    window.location.href = '/yahe/produtos/personalizar.php?id=<?php echo $produto['id']; ?>';
}

function addToCart() {
    let tamanho = $('#tamanho').val();
    let cor = $('.ui.dropdown').dropdown('get value');
    let quantidade = $('#quantidade').val();
    
    if (!tamanho) {
        $('.ui.error.message').remove();
        $('.ui.form').before('<div class="ui error message"><div class="header">Selecione um tamanho</div></div>');
        return;
    }
    
    $.ajax({
        url: '/yahe/carrinho/adicionar.php',
        method: 'POST',
        data: {
            product_id: <?php echo $produto['id']; ?>,
            quantidade: quantidade,
            tamanho: tamanho,
            cor: cor
        },
        success: function(response) {
            window.location.href = '/yahe/carrinho';
        },
        error: function() {
            alert('Erro ao adicionar ao carrinho');
        }
    });
}
</script>

<?php require_once '../includes/footer.php'; ?> 