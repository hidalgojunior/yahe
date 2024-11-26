<?php 
require_once '../includes/header.php';

// Paginação
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Filtros
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;
$busca = isset($_GET['busca']) ? filter_var($_GET['busca'], FILTER_SANITIZE_STRING) : null;

// Monta a query
$where = ["p.ativo = 1"];
$params = [];

if ($categoria_id) {
    $where[] = "p.categoria_id = ?";
    $params[] = $categoria_id;
}

if ($busca) {
    $where[] = "(p.nome LIKE ? OR p.descricao LIKE ?)";
    $params[] = "%$busca%";
    $params[] = "%$busca%";
}

$where = implode(" AND ", $where);

// Conta total de produtos
$stmt = $pdo->prepare("SELECT COUNT(*) FROM produtos p WHERE $where");
$stmt->execute($params);
$total = $stmt->fetchColumn();

// Busca produtos
$sql = "
    SELECT p.*, c.nome as categoria_nome 
    FROM produtos p 
    JOIN categorias c ON c.id = p.categoria_id 
    WHERE $where 
    ORDER BY p.created_at DESC 
    LIMIT $per_page OFFSET $offset
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll();

// Calcula total de páginas
$total_pages = ceil($total / $per_page);
?>

<div class="ui grid">
    <!-- Filtros -->
    <div class="four wide column">
        <div class="ui vertical menu">
            <div class="item">
                <div class="header">Categorias</div>
                <div class="menu">
                    <?php
                    $cats = $pdo->query("SELECT * FROM categorias ORDER BY nome");
                    while ($cat = $cats->fetch()) {
                        $active = $categoria_id == $cat['id'] ? 'active' : '';
                        echo "<a class='item $active' href='?categoria={$cat['id']}'>{$cat['nome']}</a>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Lista de Produtos -->
    <div class="twelve wide column">
        <!-- Busca -->
        <form class="ui form" method="GET">
            <div class="field">
                <div class="ui icon input">
                    <input type="text" name="busca" placeholder="Buscar produtos..." value="<?php echo $busca; ?>">
                    <i class="search icon"></i>
                </div>
            </div>
        </form>
        
        <!-- Grid de Produtos -->
        <div class="ui four cards">
            <?php foreach ($produtos as $produto): ?>
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
        
        <!-- Paginação -->
        <?php if ($total_pages > 1): ?>
            <div class="ui pagination menu">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a class="item" href="?page=<?php echo $i; ?>"><?php echo $i; ?></a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </div>
</div> 