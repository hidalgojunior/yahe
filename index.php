<?php
require_once 'includes/header.php';

// Busca produtos em destaque
$stmt = $pdo->query("
    SELECT p.*, c.nome as categoria_nome 
    FROM produtos p 
    JOIN categorias c ON c.id = p.categoria_id 
    WHERE p.destaque = 1 AND p.ativo = 1 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$destaques = $stmt->fetchAll();

// Busca produtos mais recentes
$stmt = $pdo->query("
    SELECT p.*, c.nome as categoria_nome 
    FROM produtos p 
    JOIN categorias c ON c.id = p.categoria_id 
    WHERE p.ativo = 1 
    ORDER BY p.created_at DESC 
    LIMIT 8
");
$recentes = $stmt->fetchAll();
?>

<!-- Banner Principal -->
<div class="ui fluid image">
    <img src="/assets/images/banner.jpg" alt="Banner">
</div>

<!-- Produtos em Destaque -->
<h2 class="ui header">Produtos em Destaque</h2>
<div class="ui four cards">
    <?php foreach ($destaques as $produto): ?>
        <div class="card">
            <div class="image">
                <img src="<?php echo $produto['imagem'] ?: '/assets/images/produto-sem-foto.jpg'; ?>">
            </div>
            <div class="content">
                <div class="header"><?php echo $produto['nome']; ?></div>
                <div class="meta">
                    <span class="category"><?php echo $produto['categoria_nome']; ?></span>
                </div>
                <div class="description">
                    R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                </div>
            </div>
            <a class="ui bottom attached button" href="/produto/<?php echo $produto['slug']; ?>">
                <i class="eye icon"></i>
                Ver Produto
            </a>
        </div>
    <?php endforeach; ?>
</div>

<!-- Produtos Recentes -->
<h2 class="ui header">Produtos Recentes</h2>
<div class="ui four cards">
    <?php foreach ($recentes as $produto): ?>
        <div class="card">
            <div class="image">
                <img src="<?php echo $produto['imagem'] ?: '/assets/images/produto-sem-foto.jpg'; ?>">
            </div>
            <div class="content">
                <div class="header"><?php echo $produto['nome']; ?></div>
                <div class="meta">
                    <span class="category"><?php echo $produto['categoria_nome']; ?></span>
                </div>
                <div class="description">
                    R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
                </div>
            </div>
            <a class="ui bottom attached button" href="/produto/<?php echo $produto['slug']; ?>">
                <i class="eye icon"></i>
                Ver Produto
            </a>
        </div>
    <?php endforeach; ?>
</div>

<?php require_once 'includes/footer.php'; ?> 