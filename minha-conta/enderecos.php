<?php 
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../config/database.php';

// Busca endereços do usuário
$stmt = $pdo->prepare("SELECT * FROM enderecos WHERE usuario_id = :usuario_id ORDER BY principal DESC");
$stmt->execute(['usuario_id' => $_SESSION['user_id']]);
$enderecos = $stmt->fetchAll();

// Processa formulário de novo endereço
$message = null;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $principal = isset($_POST['principal']) ? 1 : 0;
        
        // Se marcou como principal, desmarca os outros
        if ($principal) {
            $stmt = $pdo->prepare("UPDATE enderecos SET principal = 0 WHERE usuario_id = :usuario_id");
            $stmt->execute(['usuario_id' => $_SESSION['user_id']]);
        }
        
        $stmt = $pdo->prepare("
            INSERT INTO enderecos (
                usuario_id, cep, rua, numero, complemento, 
                bairro, cidade, estado, principal
            ) VALUES (
                :usuario_id, :cep, :rua, :numero, :complemento,
                :bairro, :cidade, :estado, :principal
            )
        ");
        
        $stmt->execute([
            'usuario_id' => $_SESSION['user_id'],
            'cep' => $_POST['cep'],
            'rua' => $_POST['rua'],
            'numero' => $_POST['numero'],
            'complemento' => $_POST['complemento'],
            'bairro' => $_POST['bairro'],
            'cidade' => $_POST['cidade'],
            'estado' => $_POST['estado'],
            'principal' => $principal
        ]);
        
        header('Location: /yahe/minha-conta/enderecos');
        exit;
        
    } catch (Exception $e) {
        $message = [
            'type' => 'error',
            'text' => 'Erro ao salvar endereço'
        ];
    }
}
?>

<div class="ui container">
    <div class="ui grid stackable">
        <!-- Menu lateral -->
        <div class="four wide column">
            <div class="ui vertical fluid menu">
                <div class="item">
                    <div class="header">Minha Conta</div>
                    <div class="menu">
                        <a class="item" href="/yahe/minha-conta">
                            <i class="home icon"></i> Início
                        </a>
                        <a class="item" href="/yahe/minha-conta/pedidos">
                            <i class="shopping bag icon"></i> Meus Pedidos
                        </a>
                        <a class="active item" href="/yahe/minha-conta/enderecos">
                            <i class="map marker alternate icon"></i> Endereços
                        </a>
                        <a class="item" href="/yahe/minha-conta/dados">
                            <i class="user icon"></i> Meus Dados
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Conteúdo principal -->
        <div class="twelve wide column">
            <h2 class="ui header">
                <i class="map marker alternate icon"></i>
                <div class="content">
                    Meus Endereços
                    <div class="sub header">Gerencie seus endereços de entrega</div>
                </div>
            </h2>
            
            <?php if ($message): ?>
            <div class="ui <?php echo $message['type']; ?> message">
                <p><?php echo $message['text']; ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Lista de Endereços -->
            <?php if (!empty($enderecos)): ?>
            <div class="ui three stackable cards">
                <?php foreach ($enderecos as $endereco): ?>
                <div class="card">
                    <div class="content">
                        <?php if ($endereco['principal']): ?>
                        <div class="ui right corner label" data-tooltip="Endereço Principal">
                            <i class="star icon"></i>
                        </div>
                        <?php endif; ?>
                        
                        <div class="header">
                            <?php echo $endereco['rua']; ?>, <?php echo $endereco['numero']; ?>
                        </div>
                        <div class="meta">
                            <?php echo $endereco['bairro']; ?>
                        </div>
                        <div class="description">
                            <?php if ($endereco['complemento']): ?>
                                <?php echo $endereco['complemento']; ?><br>
                            <?php endif; ?>
                            <?php echo $endereco['cidade']; ?> - <?php echo $endereco['estado']; ?><br>
                            CEP: <?php echo $endereco['cep']; ?>
                        </div>
                    </div>
                    <div class="extra content">
                        <div class="ui two buttons">
                            <button class="ui basic green button" 
                                    onclick="definirPrincipal(<?php echo $endereco['id']; ?>)"
                                    <?php echo $endereco['principal'] ? 'disabled' : ''; ?>>
                                Principal
                            </button>
                            <button class="ui basic red button"
                                    onclick="excluirEndereco(<?php echo $endereco['id']; ?>)">
                                Excluir
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
            
            <!-- Formulário de Novo Endereço -->
            <div class="ui segment">
                <h3 class="ui header">Adicionar Novo Endereço</h3>
                <form class="ui form" method="POST">
                    <div class="fields">
                        <div class="four wide field">
                            <label>CEP</label>
                            <input type="text" name="cep" required maxlength="9" 
                                   onkeyup="mascara('#####-###', this, event)">
                        </div>
                        <div class="eight wide field">
                            <label>Rua</label>
                            <input type="text" name="rua" required>
                        </div>
                        <div class="four wide field">
                            <label>Número</label>
                            <input type="text" name="numero" required>
                        </div>
                    </div>
                    
                    <div class="fields">
                        <div class="eight wide field">
                            <label>Complemento</label>
                            <input type="text" name="complemento">
                        </div>
                        <div class="eight wide field">
                            <label>Bairro</label>
                            <input type="text" name="bairro" required>
                        </div>
                    </div>
                    
                    <div class="fields">
                        <div class="ten wide field">
                            <label>Cidade</label>
                            <input type="text" name="cidade" required>
                        </div>
                        <div class="six wide field">
                            <label>Estado</label>
                            <select class="ui dropdown" name="estado" required>
                                <option value="">Selecione</option>
                                <option value="AC">Acre</option>
                                <option value="AL">Alagoas</option>
                                <!-- ... outros estados ... -->
                                <option value="SP">São Paulo</option>
                                <option value="TO">Tocantins</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="principal">
                            <label>Definir como endereço principal</label>
                        </div>
                    </div>
                    
                    <button class="ui primary button" type="submit">Adicionar Endereço</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
// Máscara para CEP
function mascara(mask, input, e) {
    if (e.keyCode == 8) return;
    
    let value = input.value.replace(/\D/g, '');
    let padrao = mask.replace(/\D/g, '');
    let novo_valor = '';
    let pos = 0;
    
    for (let i = 0; i < mask.length; i++) {
        if (pos >= value.length) break;
        if (mask[i] === '#') {
            novo_valor += value[pos];
            pos++;
        } else {
            novo_valor += mask[i];
        }
    }
    
    input.value = novo_valor;
}

// Busca endereço pelo CEP
$('input[name="cep"]').blur(function() {
    let cep = $(this).val().replace(/\D/g, '');
    if (cep.length === 8) {
        $.get(`https://viacep.com.br/ws/${cep}/json/`, function(data) {
            if (!data.erro) {
                $('input[name="rua"]').val(data.logradouro);
                $('input[name="bairro"]').val(data.bairro);
                $('input[name="cidade"]').val(data.localidade);
                $('select[name="estado"]').val(data.uf);
            }
        });
    }
});

// Define endereço como principal
function definirPrincipal(id) {
    if (confirm('Definir este endereço como principal?')) {
        $.post('/yahe/minha-conta/definir-principal.php', {
            endereco_id: id
        }, function() {
            location.reload();
        });
    }
}

// Exclui endereço
function excluirEndereco(id) {
    if (confirm('Tem certeza que deseja excluir este endereço?')) {
        $.post('/yahe/minha-conta/excluir-endereco.php', {
            endereco_id: id
        }, function() {
            location.reload();
        });
    }
}

// Inicializa componentes Semantic UI
$('.ui.dropdown').dropdown();
$('.ui.checkbox').checkbox();
</script>

<?php require_once '../includes/footer.php'; ?> 