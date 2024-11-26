<?php 
require_once 'includes/header.php';

$slug = isset($_GET['slug']) ? filter_var($_GET['slug'], FILTER_SANITIZE_STRING) : null;

if (!$slug) {
    header('Location: /produtos');
    exit;
}

// Busca produto
$stmt = $pdo->prepare("
    SELECT p.*, c.nome as categoria_nome 
    FROM produtos p 
    JOIN categorias c ON c.id = p.categoria_id 
    WHERE p.slug = ? AND p.ativo = 1
");
$stmt->execute([$slug]);
$produto = $stmt->fetch();

if (!$produto) {
    header('Location: /produtos');
    exit;
}

// Busca imagens do produto
$stmt = $pdo->prepare("
    SELECT * FROM produtos_imagens 
    WHERE produto_id = ? 
    ORDER BY ordem
");
$stmt->execute([$produto['id']]);
$imagens = $stmt->fetchAll();

// Busca opções do produto
$stmt = $pdo->prepare("
    SELECT * FROM produtos_opcoes 
    WHERE produto_id = ? 
    ORDER BY id
");
$stmt->execute([$produto['id']]);
$opcoes = $stmt->fetchAll();
?>

<div class="ui grid">
    <!-- Imagens -->
    <div class="six wide column">
        <div class="ui fluid image">
            <img src="<?php echo $produto['imagem'] ?: '/assets/images/produto-sem-foto.jpg'; ?>" id="main-image">
        </div>
        
        <?php if ($imagens): ?>
            <div class="ui tiny images" style="margin-top: 1em;">
                <img class="ui image" src="<?php echo $produto['imagem']; ?>" onclick="changeImage(this.src)">
                <?php foreach ($imagens as $img): ?>
                    <img class="ui image" src="<?php echo $img['imagem']; ?>" onclick="changeImage(this.src)">
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
    
    <!-- Informações -->
    <div class="ten wide column">
        <h1 class="ui header">
            <?php echo $produto['nome']; ?>
            <div class="sub header"><?php echo $produto['categoria_nome']; ?></div>
        </h1>
        
        <div class="ui divider"></div>
        
        <div class="ui huge header">
            R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?>
        </div>
        
        <p><?php echo nl2br($produto['descricao']); ?></p>
        
        <form class="ui form" method="POST" action="/carrinho/adicionar">
            <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
            
            <?php foreach ($opcoes as $opcao): ?>
                <div class="field">
                    <label><?php echo $opcao['nome']; ?></label>
                    
                    <?php if ($opcao['tipo'] == 'texto'): ?>
                        <input type="text" name="opcoes[<?php echo $opcao['id']; ?>]" 
                               <?php echo $opcao['obrigatorio'] ? 'required' : ''; ?>>
                               
                    <?php elseif ($opcao['tipo'] == 'select'): ?>
                        <select class="ui dropdown" name="opcoes[<?php echo $opcao['id']; ?>]"
                                <?php echo $opcao['obrigatorio'] ? 'required' : ''; ?>>
                            <option value="">Selecione...</option>
                            <?php foreach (json_decode($opcao['opcoes']) as $opt): ?>
                                <option value="<?php echo $opt; ?>"><?php echo $opt; ?></option>
                            <?php endforeach; ?>
                        </select>
                        
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            
            <div class="field">
                <label>Quantidade</label>
                <input type="number" name="quantidade" value="1" min="1" required>
            </div>
            
            <button class="ui primary button" type="submit">
                <i class="cart plus icon"></i>
                Adicionar ao Carrinho
            </button>
        </form>
    </div>
</div>

<script>
function changeImage(src) {
    document.getElementById('main-image').src = src;
}

$(document).ready(function() {
    $('.ui.dropdown').dropdown();
});
</script>

<?php require_once 'includes/footer.php'; ?> 