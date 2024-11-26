<?php 
require_once '../includes/header.php';

// TODO: Implementar paginação e filtros
$produtos = [
    [
        'id' => 1,
        'nome' => 'Camiseta Personalizada',
        'descricao' => 'Camiseta 100% algodão para personalização',
        'preco' => 49.90,
        'imagem' => 'camiseta.jpg',
        'categoria' => 'Camisetas'
    ],
    [
        'id' => 2,
        'nome' => 'Caneca Mágica',
        'descricao' => 'Caneca que revela a imagem com líquido quente',
        'preco' => 39.90,
        'imagem' => 'caneca.jpg',
        'categoria' => 'Canecas'
    ],
    // Mais produtos serão carregados do banco posteriormente
];
?>

<div class="ui container">
    <!-- Breadcrumb -->
    <div class="ui breadcrumb">
        <a href="/" class="section">Início</a>
        <i class="right angle icon divider"></i>
        <div class="active section">Produtos</div>
    </div>

    <div class="ui grid">
        <!-- Filtros -->
        <div class="four wide column">
            <div class="ui vertical menu">
                <div class="item">
                    <div class="header">Categorias</div>
                    <div class="menu">
                        <a class="item">Camisetas</a>
                        <a class="item">Canecas</a>
                        <a class="item">Quadros</a>
                        <a class="item">Acessórios</a>
                    </div>
                </div>
                <div class="item">
                    <div class="header">Preço</div>
                    <div class="ui form">
                        <div class="field">
                            <div class="ui range slider" id="priceRange"></div>
                            <div class="ui input">
                                <input type="text" id="priceLabel" readonly>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="item">
                    <div class="header">Ordenar por</div>
                    <select class="ui dropdown">
                        <option value="">Selecione</option>
                        <option value="preco_asc">Menor Preço</option>
                        <option value="preco_desc">Maior Preço</option>
                        <option value="nome_asc">A-Z</option>
                        <option value="nome_desc">Z-A</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Lista de Produtos -->
        <div class="twelve wide column">
            <h1 class="ui header">Nossos Produtos</h1>
            
            <!-- Barra de Busca -->
            <div class="ui fluid action input">
                <input type="text" placeholder="Buscar produtos...">
                <button class="ui button">Buscar</button>
            </div>

            <div class="ui divider"></div>

            <!-- Grid de Produtos -->
            <div class="ui four cards">
                <?php foreach ($produtos as $produto): ?>
                <div class="card">
                    <div class="image">
                        <img src="/yahe/assets/images/products/<?php echo $produto['imagem']; ?>">
                    </div>
                    <div class="content">
                        <div class="header"><?php echo $produto['nome']; ?></div>
                        <div class="meta">
                            <span class="category"><?php echo $produto['categoria']; ?></span>
                        </div>
                        <div class="description">
                            <?php echo $produto['descricao']; ?>
                        </div>
                    </div>
                    <div class="extra content">
                        <span class="right floated">
                            R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                        </span>
                    </div>
                    <div class="ui two bottom attached buttons">
                        <a href="/yahe/produtos/personalizar.php?id=<?php echo $produto['id']; ?>" class="ui primary button">
                            Personalizar
                        </a>
                        <div class="ui button" onclick="addToCart(<?php echo $produto['id']; ?>)">
                            <i class="cart icon"></i>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Paginação -->
            <div class="ui center aligned container" style="margin-top: 2em;">
                <div class="ui pagination menu">
                    <a class="active item">1</a>
                    <a class="item">2</a>
                    <a class="item">3</a>
                    <div class="disabled item">...</div>
                    <a class="item">10</a>
                </div>
            </div>
        </div>
    </div>
</div> 