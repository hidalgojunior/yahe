<?php 
require_once '../includes/header.php';

// TODO: Buscar produto do banco de dados
$produto_id = $_GET['id'] ?? null;
$produto = [
    'id' => 1,
    'nome' => 'Camiseta Personalizada',
    'imagem' => 'camiseta.jpg',
    'preco' => 49.90,
    'areas_personalizacao' => [
        'frente' => [
            'width' => 300,
            'height' => 400,
            'x' => 100,
            'y' => 50
        ],
        'costas' => [
            'width' => 300,
            'height' => 400,
            'x' => 100,
            'y' => 50
        ]
    ]
];
?>

<div class="ui container">
    <!-- Breadcrumb -->
    <div class="ui breadcrumb">
        <a href="/yahe/" class="section">Início</a>
        <i class="right angle icon divider"></i>
        <a href="/yahe/produtos" class="section">Produtos</a>
        <i class="right angle icon divider"></i>
        <a href="/yahe/produtos/produto.php?id=<?php echo $produto['id']; ?>" class="section"><?php echo $produto['nome']; ?></a>
        <i class="right angle icon divider"></i>
        <div class="active section">Personalizar</div>
    </div>

    <div class="ui grid">
        <!-- Área de Visualização -->
        <div class="ten wide column">
            <div class="ui segment">
                <div class="ui two item menu">
                    <a class="item active" data-tab="frente">Frente</a>
                    <a class="item" data-tab="costas">Costas</a>
                </div>
                <div class="ui tab active" data-tab="frente">
                    <div id="canvas-container-frente" class="canvas-container">
                        <canvas id="canvas-frente"></canvas>
                    </div>
                </div>
                <div class="ui tab" data-tab="costas">
                    <div id="canvas-container-costas" class="canvas-container">
                        <canvas id="canvas-costas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Painel de Ferramentas -->
        <div class="six wide column">
            <div class="ui segments">
                <!-- Informações do Produto -->
                <div class="ui segment">
                    <h3 class="ui header">
                        <?php echo $produto['nome']; ?>
                        <div class="sub header">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></div>
                    </h3>
                </div>

                <!-- Ferramentas de Personalização -->
                <div class="ui segment">
                    <div class="ui form">
                        <!-- Adicionar Texto -->
                        <div class="field">
                            <label>Adicionar Texto</label>
                            <div class="ui action input">
                                <input type="text" id="texto" placeholder="Digite seu texto...">
                                <button class="ui button" onclick="addText()">Adicionar</button>
                            </div>
                        </div>

                        <!-- Upload de Imagem -->
                        <div class="field">
                            <label>Adicionar Imagem</label>
                            <input type="file" id="imageUpload" accept="image/*" style="display: none;">
                            <div class="ui primary button" onclick="$('#imageUpload').click()">
                                <i class="upload icon"></i> Upload de Imagem
                            </div>
                        </div>

                        <!-- Opções de Texto -->
                        <div id="text-options" style="display: none;">
                            <h4 class="ui header">Opções de Texto</h4>
                            <div class="field">
                                <label>Fonte</label>
                                <select class="ui dropdown" id="fontFamily">
                                    <option value="Arial">Arial</option>
                                    <option value="Times New Roman">Times New Roman</option>
                                    <option value="Courier New">Courier New</option>
                                </select>
                            </div>
                            <div class="field">
                                <label>Tamanho</label>
                                <input type="range" id="fontSize" min="12" max="72" value="24">
                            </div>
                            <div class="field">
                                <label>Cor</label>
                                <input type="color" id="textColor">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Ações -->
                <div class="ui segment">
                    <button class="ui red button" onclick="removeSelected()">
                        <i class="trash icon"></i> Remover Selecionado
                    </button>
                    <button class="ui button" onclick="clearCanvas()">
                        <i class="eraser icon"></i> Limpar Tudo
                    </button>
                </div>

                <!-- Finalizar -->
                <div class="ui secondary segment">
                    <button class="ui primary fluid button" onclick="saveDesign()">
                        <i class="cart icon"></i> Adicionar ao Carrinho
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Preview -->
<div class="ui modal">
    <i class="close icon"></i>
    <div class="header">
        Confirmar Design
    </div>
    <div class="content">
        <div class="ui two column grid">
            <div class="column">
                <h4>Frente</h4>
                <img id="preview-frente" class="ui fluid image">
            </div>
            <div class="column">
                <h4>Costas</h4>
                <img id="preview-costas" class="ui fluid image">
            </div>
        </div>
    </div>
    <div class="actions">
        <div class="ui cancel button">Voltar</div>
        <div class="ui positive button" onclick="addToCart()">Confirmar</div>
    </div>
</div>

<!-- Fabric.js e Scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.3.1/fabric.min.js"></script>
<script>
let canvasFrente, canvasCostas;

$(document).ready(function() {
    // Inicializa tabs
    $('.menu .item').tab();
    
    // Inicializa canvas
    canvasFrente = initCanvas('canvas-frente');
    canvasCostas = initCanvas('canvas-costas');
    
    // Carrega imagem do produto
    loadProductImage(canvasFrente, 'assets/images/products/<?php echo $produto['imagem']; ?>');
    loadProductImage(canvasCostas, 'assets/images/products/<?php echo $produto['imagem']; ?>');
    
    // Event listeners
    $('#imageUpload').change(handleImageUpload);
    
    // Atualiza opções quando objeto é selecionado
    canvasFrente.on('selection:created', updateTextOptions);
    canvasFrente.on('selection:updated', updateTextOptions);
    canvasFrente.on('selection:cleared', hideTextOptions);
    
    canvasCostas.on('selection:created', updateTextOptions);
    canvasCostas.on('selection:updated', updateTextOptions);
    canvasCostas.on('selection:cleared', hideTextOptions);
});

function initCanvas(id) {
    let canvas = new fabric.Canvas(id, {
        width: 500,
        height: 600,
        backgroundColor: '#fff'
    });
    
    return canvas;
}

function loadProductImage(canvas, url) {
    fabric.Image.fromURL('/yahe/' + url, function(img) {
        img.scaleToWidth(canvas.width);
        img.set({
            selectable: false,
            evented: false
        });
        canvas.add(img);
        canvas.renderAll();
    });
}

function addText() {
    let text = $('#texto').val();
    if (!text) return;
    
    let activeCanvas = $('.tab.active').attr('data-tab') === 'frente' ? canvasFrente : canvasCostas;
    
    let fabricText = new fabric.Text(text, {
        left: 100,
        top: 100,
        fontFamily: 'Arial',
        fontSize: 24,
        fill: '#000000'
    });
    
    activeCanvas.add(fabricText);
    activeCanvas.setActiveObject(fabricText);
    activeCanvas.renderAll();
    
    $('#texto').val('');
    updateTextOptions();
}

function handleImageUpload(e) {
    let file = e.target.files[0];
    let reader = new FileReader();
    
    reader.onload = function(f) {
        let data = f.target.result;
        fabric.Image.fromURL(data, function(img) {
            img.scaleToWidth(200);
            
            let activeCanvas = $('.tab.active').attr('data-tab') === 'frente' ? canvasFrente : canvasCostas;
            activeCanvas.add(img);
            activeCanvas.renderAll();
        });
    };
    
    reader.readAsDataURL(file);
}

function updateTextOptions() {
    let activeCanvas = $('.tab.active').attr('data-tab') === 'frente' ? canvasFrente : canvasCostas;
    let activeObject = activeCanvas.getActiveObject();
    
    if (activeObject && activeObject.type === 'text') {
        $('#text-options').show();
        $('#fontFamily').val(activeObject.fontFamily);
        $('#fontSize').val(activeObject.fontSize);
        $('#textColor').val(activeObject.fill);
    } else {
        $('#text-options').hide();
    }
}

function hideTextOptions() {
    $('#text-options').hide();
}

function removeSelected() {
    let activeCanvas = $('.tab.active').attr('data-tab') === 'frente' ? canvasFrente : canvasCostas;
    let activeObject = activeCanvas.getActiveObject();
    
    if (activeObject) {
        activeCanvas.remove(activeObject);
        activeCanvas.renderAll();
    }
}

function clearCanvas() {
    let activeCanvas = $('.tab.active').attr('data-tab') === 'frente' ? canvasFrente : canvasCostas;
    activeCanvas.clear();
    loadProductImage(activeCanvas, 'assets/images/products/<?php echo $produto['imagem']; ?>');
}

function saveDesign() {
    // Gera previews
    $('#preview-frente').attr('src', canvasFrente.toDataURL());
    $('#preview-costas').attr('src', canvasCostas.toDataURL());
    
    // Mostra modal
    $('.ui.modal').modal('show');
}

function addToCart() {
    // TODO: Implementar adição ao carrinho com design personalizado
    let design = {
        frente: canvasFrente.toDataURL(),
        costas: canvasCostas.toDataURL()
    };
    
    // Simula adição ao carrinho
    alert('Produto adicionado ao carrinho!');
    window.location.href = '/yahe/carrinho';
}
</script>

<?php require_once '../includes/footer.php'; ?> 