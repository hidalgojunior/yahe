<?php
session_start();

// Verifica se já está instalado
if (file_exists(__DIR__ . '/../config/installed.php')) {
    die('O sistema já está instalado. Por segurança, remova a pasta "install".');
}

$step = $_GET['step'] ?? 'requirements';
$error = null;
$success = null;

// Verifica requisitos do sistema
function checkRequirements() {
    $requirements = [
        'PHP Version >= 8.0' => version_compare(PHP_VERSION, '8.0.0', '>='),
        'PDO Extension' => extension_loaded('pdo'),
        'PDO MySQL Extension' => extension_loaded('pdo_mysql'),
        'Fileinfo Extension' => extension_loaded('fileinfo'),
        'GD Extension' => extension_loaded('gd'),
        'Config Directory Writable' => is_writable(__DIR__ . '/../config'),
        'Assets/uploads Directory Writable' => is_writable(__DIR__ . '/../assets/uploads')
    ];
    
    return $requirements;
}

// Testa conexão com banco
function testConnection($host, $dbname, $username, $password) {
    try {
        $pdo = new PDO(
            "mysql:host={$host};dbname={$dbname}",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return true;
    } catch (PDOException $e) {
        return false;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Instalação - YAHE</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
</head>
<body>

<div class="ui container" style="padding-top: 50px;">
    <div class="ui grid centered">
        <div class="twelve wide column">
            <div class="ui segment">
                <h2 class="ui header center aligned">
                    <img src="../assets/images/logo.png" class="ui image">
                    Instalação do Sistema
                </h2>
                
                <!-- Progress -->
                <div class="ui four steps tiny">
                    <div class="<?php echo $step === 'requirements' ? 'active' : ''; ?> step">
                        <i class="server icon"></i>
                        <div class="content">
                            <div class="title">Requisitos</div>
                        </div>
                    </div>
                    
                    <div class="<?php echo $step === 'database' ? 'active' : ''; ?> step">
                        <i class="database icon"></i>
                        <div class="content">
                            <div class="title">Banco de Dados</div>
                        </div>
                    </div>
                    
                    <div class="<?php echo $step === 'migrate' ? 'active' : ''; ?> step">
                        <i class="cogs icon"></i>
                        <div class="content">
                            <div class="title">Migrações</div>
                        </div>
                    </div>
                    
                    <div class="<?php echo $step === 'finish' ? 'active' : ''; ?> step">
                        <i class="check icon"></i>
                        <div class="content">
                            <div class="title">Finalizar</div>
                        </div>
                    </div>
                </div>
                
                <?php if ($error): ?>
                <div class="ui negative message">
                    <p><?php echo $error; ?></p>
                </div>
                <?php endif; ?>
                
                <?php if ($success): ?>
                <div class="ui success message">
                    <p><?php echo $success; ?></p>
                </div>
                <?php endif; ?>
                
                <!-- Conteúdo -->
                <?php 
                switch ($step):
                    case 'requirements':
                        $requirements = checkRequirements();
                        $canProceed = !in_array(false, $requirements);
                ?>
                    <h3 class="ui header">Verificação de Requisitos</h3>
                    <table class="ui celled table">
                        <thead>
                            <tr>
                                <th>Requisito</th>
                                <th width="100">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($requirements as $requirement => $satisfied): ?>
                            <tr>
                                <td><?php echo $requirement; ?></td>
                                <td>
                                    <?php if ($satisfied): ?>
                                        <i class="green check icon"></i> OK
                                    <?php else: ?>
                                        <i class="red times icon"></i> Erro
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if ($canProceed): ?>
                        <a href="?step=database" class="ui right floated primary button">Próximo</a>
                    <?php else: ?>
                        <div class="ui negative message">
                            <p>Corrija os requisitos não atendidos antes de continuar.</p>
                        </div>
                    <?php endif; ?>
                    
                <?php 
                    break;
                    case 'database':
                        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                            $host = $_POST['host'];
                            $dbname = $_POST['dbname'];
                            $username = $_POST['username'];
                            $password = $_POST['password'];
                            
                            if (testConnection($host, $dbname, $username, $password)) {
                                // Salva configurações
                                $config = "<?php
return [
    'host' => '{$host}',
    'dbname' => '{$dbname}',
    'username' => '{$username}',
    'password' => '{$password}'
];";
                                file_put_contents(__DIR__ . '/../config/database.php', $config);
                                
                                header('Location: ?step=migrate');
                                exit;
                            } else {
                                $error = 'Não foi possível conectar ao banco de dados. Verifique as credenciais.';
                            }
                        }
                ?>
                    <h3 class="ui header">Configuração do Banco de Dados</h3>
                    <form class="ui form" method="POST">
                        <div class="field">
                            <label>Host</label>
                            <input type="text" name="host" value="localhost" required>
                        </div>
                        
                        <div class="field">
                            <label>Nome do Banco</label>
                            <input type="text" name="dbname" value="yahe" required>
                        </div>
                        
                        <div class="field">
                            <label>Usuário</label>
                            <input type="text" name="username" value="root" required>
                        </div>
                        
                        <div class="field">
                            <label>Senha</label>
                            <input type="password" name="password">
                        </div>
                        
                        <button type="submit" class="ui right floated primary button">Próximo</button>
                        <a href="?step=requirements" class="ui right floated button">Voltar</a>
                    </form>
                    
                <?php 
                    break;
                    case 'migrate':
                        require_once __DIR__ . '/migrate.php';
                ?>
                    <div class="ui segment">
                        <div class="ui active indicating progress" id="migration-progress">
                            <div class="bar">
                                <div class="progress"></div>
                            </div>
                            <div class="label">Executando migrações...</div>
                        </div>
                        
                        <div class="ui relaxed list" id="migration-log"></div>
                    </div>
                    
                    <a href="?step=finish" class="ui right floated primary button" id="next-button" style="display: none;">
                        Próximo
                    </a>
                    
                    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                    <script>
                    $(document).ready(function() {
                        let totalMigrations = <?php echo count($migrations); ?>;
                        let completed = 0;
                        
                        function updateProgress(percent) {
                            $('#migration-progress').progress({
                                percent: percent
                            });
                        }
                        
                        function addLog(message, success = true) {
                            $('#migration-log').prepend(`
                                <div class="item">
                                    <i class="icon ${success ? 'green check' : 'red times'}"></i>
                                    <div class="content">
                                        <div class="description">${message}</div>
                                    </div>
                                </div>
                            `);
                        }
                        
                        function runMigration() {
                            $.post('migrate-ajax.php', function(response) {
                                if (response.success) {
                                    completed++;
                                    let percent = Math.round((completed / totalMigrations) * 100);
                                    updateProgress(percent);
                                    addLog(response.message);
                                    
                                    if (completed < totalMigrations) {
                                        runMigration();
                                    } else {
                                        $('#migration-progress .label').text('Migrações concluídas!');
                                        $('#next-button').show();
                                    }
                                } else {
                                    addLog(response.message, false);
                                }
                            });
                        }
                        
                        runMigration();
                    });
                    </script>
                    
                <?php 
                    break;
                    case 'finish':
                        // Marca como instalado
                        file_put_contents(__DIR__ . '/../config/installed.php', '<?php return true;');
                ?>
                    <div class="ui success message">
                        <div class="header">Instalação Concluída!</div>
                        <p>O sistema foi instalado com sucesso. Por segurança, remova a pasta "install".</p>
                    </div>
                    
                    <a href="../" class="ui right floated primary button">Ir para o Site</a>
                    
                <?php 
                    break;
                endswitch; 
                ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
</body>
</html> 