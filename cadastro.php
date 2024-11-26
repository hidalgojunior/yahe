<?php 
require_once 'includes/header.php';

// Redireciona se já estiver logado
if (isset($_SESSION['user_id'])) {
    header('Location: /yahe/minha-conta');
    exit;
}

// Processa o cadastro
$error = null;
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $email = $_POST['email'] ?? '';
    $senha = $_POST['senha'] ?? '';
    $confirma_senha = $_POST['confirma_senha'] ?? '';
    
    try {
        require_once 'config/database.php';
        
        // Validações
        if (strlen($senha) < 6) {
            throw new Exception('A senha deve ter no mínimo 6 caracteres');
        }
        
        if ($senha !== $confirma_senha) {
            throw new Exception('As senhas não conferem');
        }
        
        // Verifica se e-mail já existe
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = :email");
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            throw new Exception('Este e-mail já está cadastrado');
        }
        
        // Insere o usuário
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (nome, email, senha, created_at) 
            VALUES (:nome, :email, :senha, NOW())
        ");
        
        $stmt->execute([
            'nome' => $nome,
            'email' => $email,
            'senha' => password_hash($senha, PASSWORD_DEFAULT)
        ]);
        
        $success = true;
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<div class="ui container">
    <div class="ui grid centered">
        <div class="eight wide column">
            <h2 class="ui header center aligned">Criar Conta</h2>
            
            <?php if ($error): ?>
            <div class="ui negative message">
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="ui success message">
                <div class="header">Cadastro realizado com sucesso!</div>
                <p>Você já pode fazer <a href="/yahe/login.php">login</a> com seu e-mail e senha.</p>
            </div>
            <?php else: ?>
            
            <form class="ui form" method="POST">
                <div class="field">
                    <label>Nome completo</label>
                    <input type="text" name="nome" required>
                </div>
                
                <div class="field">
                    <label>E-mail</label>
                    <input type="email" name="email" required>
                </div>
                
                <div class="field">
                    <label>Senha</label>
                    <input type="password" name="senha" required 
                           pattern=".{6,}" title="Mínimo de 6 caracteres">
                </div>
                
                <div class="field">
                    <label>Confirmar senha</label>
                    <input type="password" name="confirma_senha" required>
                </div>
                
                <button class="ui fluid primary button" type="submit">Criar conta</button>
                
                <div class="ui divider"></div>
                
                <div class="ui center aligned basic segment">
                    Já tem uma conta? 
                    <a href="/yahe/login.php">Fazer login</a>
                </div>
            </form>
            
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?> 