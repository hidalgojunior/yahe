    </div> <!-- Fechamento do container principal -->
    
    <!-- Footer -->
    <div class="ui inverted vertical footer segment">
        <div class="ui container">
            <div class="ui stackable inverted divided equal height stackable grid">
                <div class="three wide column">
                    <h4 class="ui inverted header">Sobre</h4>
                    <div class="ui inverted link list">
                        <a href="/sobre" class="item">Quem Somos</a>
                        <a href="/contato" class="item">Contato</a>
                        <a href="/termos" class="item">Termos de Uso</a>
                        <a href="/privacidade" class="item">Privacidade</a>
                    </div>
                </div>
                
                <div class="three wide column">
                    <h4 class="ui inverted header">Serviços</h4>
                    <div class="ui inverted link list">
                        <a href="/produtos" class="item">Produtos</a>
                        <a href="/personalizar" class="item">Personalização</a>
                        <a href="/faq" class="item">FAQ</a>
                        <a href="/suporte" class="item">Suporte</a>
                    </div>
                </div>
                
                <div class="seven wide column">
                    <h4 class="ui inverted header">YAHE - Produtos Personalizados</h4>
                    <p>Transforme suas ideias em produtos únicos e especiais.</p>
                    <div class="ui inverted horizontal link list">
                        <a href="#" class="item"><i class="facebook f icon"></i></a>
                        <a href="#" class="item"><i class="instagram icon"></i></a>
                        <a href="#" class="item"><i class="twitter icon"></i></a>
                        <a href="#" class="item"><i class="whatsapp icon"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    <script src="/assets/js/main.js"></script>
    
    <script>
        $(document).ready(function() {
            // Inicializa os dropdowns
            $('.ui.dropdown').dropdown();
            
            // Ajusta o padding do conteúdo principal para compensar o menu fixo
            $('.main.container').css('padding-top', '7em');
        });
    </script>
</body>
</html> 