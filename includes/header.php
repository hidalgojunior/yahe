<?php
session_start();
require_once __DIR__ . '/../config/database.php';

// Define a URL base
define('BASE_URL', '/yahe');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja de Personalizados</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>/assets/css/style.css">
</head>
<body>

<div class="ui fixed menu">
    <div class="ui container">
        <a href="<?php echo BASE_URL; ?>/" class="header item">
            <img src="<?php echo BASE_URL; ?>/assets/images/logo.png" class="logo">
            Loja de Personalizados
        </a>
        
        <a href="<?php echo BASE_URL; ?>/produtos" class="item">Produtos</a>
        
        <div class="ui dropdown item">
            Categorias <i class="dropdown icon"></i>
            <div class="menu">
                <?php
                $stmt = $pdo->query("SELECT nome, slug FROM categorias ORDER BY nome");
                while ($categoria = $stmt->fetch()) {
                    echo "<a class='item' href='" . BASE_URL . "/categoria/{$categoria['slug']}'>{$categoria['nome']}</a>";
                }
                ?>
            </div>
        </div>
        
        <div class="right menu">
            <a href="<?php echo BASE_URL; ?>/carrinho" class="item">
                <i class="shopping cart icon"></i>
                Carrinho
                <?php if (!empty($_SESSION['carrinho'])): ?>
                    <div class="ui red circular label"><?php echo count($_SESSION['carrinho']); ?></div>
                <?php endif; ?>
            </a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="ui dropdown item">
                    <i class="user icon"></i>
                    <?php echo $_SESSION['user_nome']; ?>
                    <i class="dropdown icon"></i>
                    <div class="menu">
                        <a class="item" href="<?php echo BASE_URL; ?>/minha-conta">Minha Conta</a>
                        <a class="item" href="<?php echo BASE_URL; ?>/minha-conta/pedidos">Meus Pedidos</a>
                        <a class="item" href="<?php echo BASE_URL; ?>/logout">Sair</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="<?php echo BASE_URL; ?>/login" class="item">
                    <i class="user icon"></i>
                    Entrar
                </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="ui main container" style="margin-top: 6em;">
</body>
</html> 