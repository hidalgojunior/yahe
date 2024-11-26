<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YAHE - Produtos Personalizados</title>
    
    <!-- Semantic UI -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>
    <!-- Menu Principal -->
    <div class="ui large top fixed menu">
        <div class="ui container">
            <a href="/" class="header item">
                <?php if (file_exists($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logo.png')): ?>
                    <img class="logo" src="/assets/images/logo.png" alt="YAHE">
                <?php else: ?>
                    <div class="ui header">
                        <i class="shopping bag icon"></i>
                        YAHE
                    </div>
                <?php endif; ?>
            </a>
            
            <a href="/produtos" class="item">Produtos</a>
            <a href="/personalizar" class="item">Personalizar</a>
            <a href="/sobre" class="item">Sobre</a>
            
            <div class="right menu">
                <div class="item">
                    <div class="ui icon input">
                        <input type="text" placeholder="Buscar...">
                        <i class="search link icon"></i>
                    </div>
                </div>
                
                <a href="/carrinho" class="item">
                    <i class="shopping cart icon"></i>
                    Carrinho
                    <div class="ui circular teal label">0</div>
                </a>
                
                <div class="ui dropdown item">
                    <i class="user icon"></i>
                    Minha Conta
                    <i class="dropdown icon"></i>
                    <div class="menu">
                        <a class="item" href="/login">Login</a>
                        <a class="item" href="/registro">Registrar</a>
                        <div class="divider"></div>
                        <a class="item" href="/meus-pedidos">Meus Pedidos</a>
                        <a class="item" href="/perfil">Perfil</a>
                        <a class="item" href="/sair">Sair</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Conteúdo Principal -->
    <div class="ui main container">
</body>
</html> 