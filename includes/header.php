<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YAHE - Produtos Personalizados</title>
    
    <!-- Semantic UI -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    <link rel="stylesheet" href="/yahe/assets/css/style.css">
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
</head>
<body>

<div class="ui fixed menu">
    <div class="ui container">
        <a href="/yahe/" class="header item">
            <img src="/yahe/assets/images/logo.png" alt="YAHE Logo" class="logo">
            YAHE
        </a>
        
        <a href="/yahe/produtos" class="item">Produtos</a>
        <a href="/yahe/produtos/personalizar.php" class="item">Personalizar</a>
        
        <div class="right menu">
            <?php if (isset($_SESSION['user_id'])): ?>
                <div class="ui dropdown item">
                    Minha Conta <i class="dropdown icon"></i>
                    <div class="menu">
                        <a href="/yahe/minha-conta" class="item">Perfil</a>
                        <a href="/yahe/minha-conta/pedidos" class="item">Meus Pedidos</a>
                        <a href="/yahe/minha-conta/enderecos" class="item">Endereços</a>
                        <div class="divider"></div>
                        <a href="/yahe/logout.php" class="item">Sair</a>
                    </div>
                </div>
            <?php else: ?>
                <a href="/yahe/login.php" class="item">Entrar</a>
                <a href="/yahe/cadastro.php" class="item">Cadastrar</a>
            <?php endif; ?>
            
            <a href="/yahe/carrinho" class="item">
                <i class="shopping cart icon"></i>
                Carrinho
                <?php if (!empty($_SESSION['carrinho'])): ?>
                    <div class="ui red circular label">
                        <?php echo count($_SESSION['carrinho']); ?>
                    </div>
                <?php endif; ?>
            </a>
        </div>
    </div>
</div>

<!-- Espaçamento para o menu fixo -->
<div style="margin-top: 60px;"></div>

<script>
$(document).ready(function() {
    $('.ui.dropdown').dropdown();
    
    // Destaca o item de menu atual
    $('.menu a.item').each(function() {
        if (window.location.pathname.indexOf($(this).attr('href')) !== -1) {
            $(this).addClass('active');
        }
    });
});
</script>
</body>
</html> 