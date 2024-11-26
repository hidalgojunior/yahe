<?php
require_once 'includes/header.php';

// Verifica se acabou de ser instalado
if (isset($_GET['installed']) && $_GET['installed'] === 'true') {
    echo '<div class="ui success message">
        <i class="close icon"></i>
        <div class="header">
            Instalação Concluída com Sucesso!
        </div>
        <p>Bem-vindo ao YAHE - Sistema de Produtos Personalizados.</p>
    </div>';
}
?>

<!-- Conteúdo da página -->
<div class="ui basic segment">
    <h1 class="ui header">Bem-vindo à YAHE</h1>
    <p>Seu destino para produtos personalizados únicos.</p>
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