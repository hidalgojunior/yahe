<?php
require_once 'includes/header.php';

// Verifica se acabou de ser instalado
if (isset($_GET['installed']) && $_GET['installed'] === 'true') {
    ?>
    <div class="ui success message" id="installMessage">
        <i class="close icon"></i>
        <div class="header">
            Instalação Concluída com Sucesso!
        </div>
        <p>Bem-vindo ao YAHE - Sistema de Produtos Personalizados.</p>
    </div>

    <script>
        $(document).ready(function() {
            // Fecha a mensagem quando clicar no X
            $('.message .close').on('click', function() {
                $(this).closest('.message').transition('fade');
            });

            // Oculta a mensagem automaticamente após 5 segundos
            setTimeout(function() {
                $('#installMessage').transition('fade');
            }, 5000);

            // Remove o parâmetro installed da URL
            if (window.history.replaceState) {
                window.history.replaceState({}, document.title, window.location.pathname);
            }
        });
    </script>
    <?php
}
?>

<!-- Hero Section -->
<div class="ui inverted vertical masthead center aligned segment">
    <div class="ui text container">
        <h1 class="ui inverted header">
            Produtos Únicos, Sua Personalidade
        </h1>
        <h2>Crie produtos exclusivos que combinam com você</h2>
        <div class="ui huge primary button">
            Comece a Personalizar <i class="right arrow icon"></i>
        </div>
    </div>
</div>

<!-- Categorias em Destaque -->
<div class="ui vertical stripe segment">
    <div class="ui middle aligned stackable grid container">
        <div class="row">
            <div class="sixteen wide column">
                <h2 class="ui header">Categorias em Destaque</h2>
            </div>
        </div>
        <div class="row">
            <div class="four wide column">
                <div class="ui fluid card">
                    <div class="image">
                        <img src="/assets/images/categories/camisetas.jpg">
                    </div>
                    <div class="content">
                        <a class="header">Camisetas</a>
                    </div>
                </div>
            </div>
            <div class="four wide column">
                <div class="ui fluid card">
                    <div class="image">
                        <img src="/assets/images/categories/canecas.jpg">
                    </div>
                    <div class="content">
                        <a class="header">Canecas</a>
                    </div>
                </div>
            </div>
            <div class="four wide column">
                <div class="ui fluid card">
                    <div class="image">
                        <img src="/assets/images/categories/quadros.jpg">
                    </div>
                    <div class="content">
                        <a class="header">Quadros</a>
                    </div>
                </div>
            </div>
            <div class="four wide column">
                <div class="ui fluid card">
                    <div class="image">
                        <img src="/assets/images/categories/acessorios.jpg">
                    </div>
                    <div class="content">
                        <a class="header">Acessórios</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Produtos em Destaque -->
<div class="ui vertical stripe segment">
    <div class="ui container">
        <h2 class="ui header">Produtos em Destaque</h2>
        <div class="ui four cards">
            <?php
            // TODO: Substituir por dados reais do banco
            $produtos = [
                ['nome' => 'Camiseta Personalizada', 'preco' => '49.90', 'imagem' => 'camiseta.jpg'],
                ['nome' => 'Caneca Mágica', 'preco' => '39.90', 'imagem' => 'caneca.jpg'],
                ['nome' => 'Quadro Decorativo', 'preco' => '89.90', 'imagem' => 'quadro.jpg'],
                ['nome' => 'Mouse Pad Personalizado', 'preco' => '29.90', 'imagem' => 'mousepad.jpg']
            ];
            
            foreach ($produtos as $produto): ?>
                <div class="card">
                    <div class="image">
                        <img src="/assets/images/products/<?php echo $produto['imagem']; ?>">
                    </div>
                    <div class="content">
                        <div class="header"><?php echo $produto['nome']; ?></div>
                        <div class="meta">
                            <span class="price">R$ <?php echo $produto['preco']; ?></span>
                        </div>
                    </div>
                    <div class="ui bottom attached button">
                        <i class="add icon"></i>
                        Adicionar ao Carrinho
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Como Funciona -->
<div class="ui vertical stripe quote segment">
    <div class="ui equal width stackable internally celled grid">
        <div class="center aligned row">
            <div class="column">
                <h3>"Como Funciona?"</h3>
                <div class="ui ordered steps">
                    <div class="step">
                        <i class="search icon"></i>
                        <div class="content">
                            <div class="title">Escolha</div>
                            <div class="description">Selecione o produto base</div>
                        </div>
                    </div>
                    <div class="step">
                        <i class="paint brush icon"></i>
                        <div class="content">
                            <div class="title">Personalize</div>
                            <div class="description">Adicione sua arte</div>
                        </div>
                    </div>
                    <div class="step">
                        <i class="shopping cart icon"></i>
                        <div class="content">
                            <div class="title">Compre</div>
                            <div class="description">Finalize seu pedido</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Fecha a mensagem de sucesso
$('.message .close').on('click', function() {
    $(this).closest('.message').transition('fade');
});

// Remove o parâmetro installed da URL após alguns segundos
if (window.history.replaceState) {
    setTimeout(function() {
        window.history.replaceState({}, document.title, window.location.pathname);
    }, 5000);
}
</script>

<?php
require_once 'includes/footer.php';
?> 