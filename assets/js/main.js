// Funções gerais do site
const YAHE = {
    init: function() {
        this.initMenuMobile();
        this.initSearch();
        this.updateCartCount();
    },

    initMenuMobile: function() {
        $('.ui.sidebar').sidebar('attach events', '.toc.item');
    },

    initSearch: function() {
        $('.ui.search').search({
            apiSettings: {
                url: '/api/search?q={query}'
            },
            minCharacters: 3
        });
    },

    updateCartCount: function() {
        // Atualiza contador do carrinho via AJAX
        $.get('/api/cart/count', function(response) {
            $('.cart.icon .label').text(response.count);
        });
    },

    addToCart: function(productId) {
        $.post('/api/cart/add', {
            product_id: productId
        }, function(response) {
            if (response.success) {
                YAHE.updateCartCount();
                // Mostra mensagem de sucesso
                $('.ui.success.message')
                    .show()
                    .delay(3000)
                    .fadeOut();
            }
        });
    }
};

// Inicializa quando o documento estiver pronto
$(document).ready(function() {
    YAHE.init();
}); 