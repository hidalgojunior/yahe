<?php 
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../config/database.php';

// Busca dados do usuário
$stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id");
$stmt->execute(['id' => $_SESSION['user_id']]);
$usuario = $stmt->fetch();

$message = null;

// Processa atualização de dados
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $tipo = $_POST['tipo'] ?? '';
        
        if ($tipo === 'dados') {
            // Atualiza dados pessoais
            $stmt = $pdo->prepare("
                UPDATE usuarios 
                SET nome = :nome, 
                    cpf = :cpf, 
                    telefone = :telefone 
                WHERE id = :id
            ");
            
            $stmt->execute([
                'nome' => $_POST['nome'],
                'cpf' => $_POST['cpf'],
                'telefone' => $_POST['telefone'],
                'id' => $_SESSION['user_id']
            ]);
            
            $_SESSION['user_nome'] = $_POST['nome'];
            $message = ['type' => 'success', 'text' => 'Dados atualizados com sucesso!'];
            
        } elseif ($tipo === 'senha') {
            // Verifica senha atual
            if (!password_verify($_POST['senha_atual'], $usuario['senha'])) {
                throw new Exception('Senha atual incorreta');
            }
            
            // Valida nova senha
            if (strlen($_POST['nova_senha']) < 6) {
                throw new Exception('A nova senha deve ter no mínimo 6 caracteres');
            }
            
            if ($_POST['nova_senha'] !== $_POST['confirma_senha']) {
                throw new Exception('As senhas não conferem');
            }
            
            // Atualiza senha
            $stmt = $pdo->prepare("UPDATE usuarios SET senha = :senha WHERE id = :id");
            $stmt->execute([
                'senha' => password_hash($_POST['nova_senha'], PASSWORD_DEFAULT),
                'id' => $_SESSION['user_id']
            ]);
            
            $message = ['type' => 'success', 'text' => 'Senha atualizada com sucesso!'];
        }
        
    } catch (Exception $e) {
        $message = ['type' => 'error', 'text' => $e->getMessage()];
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
                        <a class="item" href="/yahe/minha-conta/enderecos">
                            <i class="map marker alternate icon"></i> Endereços
                        </a>
                        <a class="active item" href="/yahe/minha-conta/dados">
                            <i class="user icon"></i> Meus Dados
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Conteúdo principal -->
        <div class="twelve wide column">
            <h2 class="ui header">
                <i class="user icon"></i>
                <div class="content">
                    Meus Dados
                    <div class="sub header">Atualize suas informações pessoais</div>
                </div>
            </h2>
            
            <?php if ($message): ?>
            <div class="ui <?php echo $message['type']; ?> message">
                <p><?php echo $message['text']; ?></p>
            </div>
            <?php endif; ?>
            
            <!-- Dados Pessoais -->
            <div class="ui segment">
                <h3 class="ui header">Dados Pessoais</h3>
                <form class="ui form" method="POST">
                    <input type="hidden" name="tipo" value="dados">
                    
                    <div class="field">
                        <label>Nome completo</label>
                        <input type="text" name="nome" value="<?php echo $usuario['nome']; ?>" required>
                    </div>
                    
                    <div class="field">
                        <label>E-mail</label>
                        <input type="email" value="<?php echo $usuario['email']; ?>" disabled>
                        <div class="ui tiny label">O e-mail não pode ser alterado</div>
                    </div>
                    
                    <div class="two fields">
                        <div class="field">
                            <label>CPF</label>
                            <input type="text" name="cpf" value="<?php echo $usuario['cpf']; ?>" 
                                   maxlength="14" onkeyup="mascara('###.###.###-##', this, event)">
                        </div>
                        
                        <div class="field">
                            <label>Telefone</label>
                            <input type="text" name="telefone" value="<?php echo $usuario['telefone']; ?>"
                                   maxlength="15" onkeyup="mascara('(##) #####-####', this, event)">
                        </div>
                    </div>
                    
                    <button class="ui primary button" type="submit">Atualizar Dados</button>
                </form>
            </div>
            
            <!-- Alterar Senha -->
            <div class="ui segment">
                <h3 class="ui header">Alterar Senha</h3>
                <form class="ui form" method="POST">
                    <input type="hidden" name="tipo" value="senha">
                    
                    <div class="field">
                        <label>Senha atual</label>
                        <input type="password" name="senha_atual" required>
                    </div>
                    
                    <div class="two fields">
                        <div class="field">
                            <label>Nova senha</label>
                            <input type="password" name="nova_senha" required 
                                   pattern=".{6,}" title="Mínimo de 6 caracteres">
                        </div>
                        
                        <div class="field">
                            <label>Confirmar nova senha</label>
                            <input type="password" name="confirma_senha" required>
                        </div>
                    </div>
                    
                    <button class="ui primary button" type="submit">Alterar Senha</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
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
</script>

<?php require_once '../includes/footer.php'; ?> 