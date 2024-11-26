<?php
function showDatabaseConfigForm($error = null) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Configuração YAHE - Banco de Dados</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
        <style>
            .container { padding-top: 50px; }
            .error.message { margin-bottom: 20px; }
        </style>
    </head>
    <body>
        <div class="ui container">
            <h1 class="ui header">
                <i class="database icon"></i>
                <div class="content">
                    Configuração do Banco de Dados
                    <div class="sub header">Configure a conexão com o banco de dados MySQL</div>
                </div>
            </h1>

            <?php if ($error): ?>
            <div class="ui error message">
                <div class="header">Erro de Configuração</div>
                <p><?php echo $error; ?></p>
            </div>
            <?php endif; ?>

            <div class="ui segment">
                <form class="ui form" method="POST" action="?step=2">
                    <div class="field">
                        <label>Host</label>
                        <input type="text" name="db_host" value="localhost" required>
                    </div>
                    
                    <div class="field">
                        <label>Nome do Banco</label>
                        <input type="text" name="db_name" value="loja_personalizada" required>
                    </div>
                    
                    <div class="field">
                        <label>Usuário</label>
                        <input type="text" name="db_user" value="root" required>
                    </div>
                    
                    <div class="field">
                        <label>Senha</label>
                        <input type="password" name="db_pass">
                    </div>
                    
                    <button class="ui primary button" type="submit">Próximo Passo</button>
                </form>
            </div>
        </div>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
    </body>
    </html>
    <?php
}

function processDatabaseConfig() {
    $config = [
        'host' => $_POST['db_host'],
        'name' => $_POST['db_name'],
        'user' => $_POST['db_user'],
        'pass' => $_POST['db_pass']
    ];
    
    try {
        $conn = new PDO(
            "mysql:host={$config['host']}", 
            $config['user'], 
            $config['pass']
        );
        
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        // Criar banco se não existir
        $conn->exec("CREATE DATABASE IF NOT EXISTS {$config['name']} CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        
        // Salvar configurações
        $configContent = "<?php\nclass Database {\n";
        $configContent .= "    private \$host = \"{$config['host']}\";\n";
        $configContent .= "    private \$db_name = \"{$config['name']}\";\n";
        $configContent .= "    private \$username = \"{$config['user']}\";\n";
        $configContent .= "    private \$password = \"{$config['pass']}\";\n";
        $configContent .= file_get_contents("../config/database.template.php");
        
        file_put_contents("../config/database.php", $configContent);
        
        $_SESSION['db_configured'] = true;
        header("Location: ?step=2");
        exit;
        
    } catch (PDOException $e) {
        showDatabaseConfigForm($e->getMessage());
        exit;
    }
}

function showInstallationOptions() {
    if (!isset($_SESSION['db_configured'])) {
        header("Location: ?step=1");
        exit;
    }
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Instalação YAHE - Opções</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
        <style>
            .container { padding-top: 50px; }
        </style>
    </head>
    <body>
        <div class="ui container">
            <h1 class="ui header">
                <i class="cogs icon"></i>
                <div class="content">
                    Opções de Instalação
                    <div class="sub header">Escolha as opções de instalação do sistema</div>
                </div>
            </h1>

            <div class="ui segment">
                <form class="ui form" method="POST" action="?step=3">
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="install_demo_data" checked>
                            <label>Instalar dados de demonstração</label>
                        </div>
                    </div>
                    
                    <div class="field">
                        <div class="ui checkbox">
                            <input type="checkbox" name="create_admin" checked>
                            <label>Criar usuário administrador</label>
                        </div>
                    </div>
                    
                    <div class="admin-fields" style="margin-left: 20px;">
                        <div class="field">
                            <label>Email do Administrador</label>
                            <input type="email" name="admin_email" value="admin@yahe.com">
                        </div>
                        
                        <div class="field">
                            <label>Senha do Administrador</label>
                            <input type="password" name="admin_password">
                        </div>
                    </div>
                    
                    <button class="ui primary button" type="submit">Iniciar Instalação</button>
                </form>
            </div>
        </div>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
        <script>
            $('.ui.checkbox').checkbox();
            $('input[name="create_admin"]').change(function() {
                $('.admin-fields').toggle(this.checked);
            });
        </script>
    </body>
    </html>
    <?php
}

function showInstallationForm($currentVersion, $error = null) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Instalação YAHE</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    </head>
    <body>
        <div class="ui container" style="padding-top: 50px;">
            <h1 class="ui header">Instalação do Sistema YAHE</h1>
            
            <div class="ui segment">
                <h3>Versão Atual: <?php echo $currentVersion; ?></h3>
                
                <form class="ui form" method="POST">
                    <div class="required field">
                        <label>Selecione a versão para instalar:</label>
                        <select name="target_version" class="ui dropdown" required>
                            <option value="">Selecione uma versão</option>
                            <option value="1.0.0">Versão 1.0.0 - Instalação Inicial</option>
                            <option value="1.0.1">Versão 1.0.1 - Módulo de Personalização</option>
                            <option value="1.0.2">Versão 1.0.2 - Módulo de Pedidos</option>
                        </select>
                    </div>
                    
                    <button class="ui primary button" type="submit">Instalar/Atualizar</button>
                </form>
            </div>
        </div>
        
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.js"></script>
        <script>
            $('.ui.dropdown').dropdown();
            
            // Validação do formulário
            $('.ui.form').form({
                fields: {
                    target_version: 'empty'
                }
            });
        </script>
    </body>
    </html>
    <?php
}

function performInstallation($conn, $versionControl) {
    if (!isset($_POST['target_version']) || empty($_POST['target_version'])) {
        showInstallationForm($versionControl->getCurrentVersion(), "Selecione uma versão para instalar");
        return;
    }

    $targetVersion = $_POST['target_version'];
    $currentVersion = $versionControl->getCurrentVersion();
    
    // Validação da versão
    if (version_compare($targetVersion, $currentVersion, '<=')) {
        showInstallationForm(
            $currentVersion, 
            "A versão selecionada ($targetVersion) é igual ou anterior à versão atual ($currentVersion)"
        );
        return;
    }

    try {
        $conn->beginTransaction();
        
        // Executa as migrações
        $migrations = getMigrationFiles($targetVersion);
        
        if (empty($migrations)) {
            throw new Exception("Nenhum arquivo de migração encontrado para a versão " . $targetVersion);
        }

        foreach ($migrations as $migration) {
            require_once $migration;
            $className = "Migration_" . str_replace('.', '_', basename($migration, '.php'));
            $migrationInstance = new $className($conn);
            $migrationInstance->up();
        }
        
        // Atualiza a versão no banco
        $versionControl->setVersion($targetVersion);
        
        // Instala dados de demonstração se solicitado
        if (isset($_POST['install_demo_data']) && $_POST['install_demo_data'] == 'on') {
            installDemoData($conn);
        }
        
        // Cria usuário admin se solicitado
        if (isset($_POST['create_admin']) && $_POST['create_admin'] == 'on') {
            createAdminUser($conn);
        }
        
        $conn->commit();
        
        showSuccess("Instalação da versão $targetVersion concluída com sucesso!");
        
    } catch (Exception $e) {
        $conn->rollBack();
        showError("Erro durante a instalação: " . $e->getMessage());
    }
}

function getMigrationFiles($targetVersion) {
    $files = [];
    $migrationPath = __DIR__ . "/migrations/v{$targetVersion}/";
    
    if (is_dir($migrationPath)) {
        $files = glob($migrationPath . "*.php");
        sort($files);
    }
    
    return $files;
}
