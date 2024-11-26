<?php
function showDatabaseConfigForm() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Configuração do Banco - YAHE</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    </head>
    <body>
        <div class="ui container" style="padding-top: 50px;">
            <h2 class="ui header">Configuração do Banco de Dados</h2>
            <form class="ui form" method="POST" action="?step=2">
                <div class="field">
                    <label>Host</label>
                    <input type="text" name="host" value="localhost" required>
                </div>
                <div class="field">
                    <label>Nome do Banco</label>
                    <input type="text" name="dbname" value="loja_personalizados" required>
                </div>
                <div class="field">
                    <label>Usuário</label>
                    <input type="text" name="username" value="root" required>
                </div>
                <div class="field">
                    <label>Senha</label>
                    <input type="password" name="password">
                </div>
                <button class="ui primary button" type="submit">Próximo</button>
            </form>
        </div>
    </body>
    </html>
    <?php
}

function processDatabaseConfig() {
    $host = $_POST['host'];
    $dbname = $_POST['dbname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $pdo = new PDO(
            "mysql:host=$host;dbname=$dbname",
            $username,
            $password,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        // Salva configuração
        $config = "<?php
try {
    \$db_config = [
        'host' => '$host',
        'dbname' => '$dbname',
        'username' => '$username',
        'password' => '$password'
    ];
    
    \$pdo = new PDO(
        \"mysql:host={\$db_config['host']};dbname={\$db_config['dbname']};charset=utf8mb4\",
        \$db_config['username'],
        \$db_config['password'],
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => \"SET NAMES utf8mb4\"
        ]
    );
} catch (PDOException \$e) {
    die(\"Erro de conexão: \" . \$e->getMessage());
}";
        
        file_put_contents('../config/database.php', $config);
        
    } catch (PDOException $e) {
        showError("Erro ao conectar ao banco de dados: " . $e->getMessage());
    }
}

function showInstallationOptions() {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Instalação - YAHE</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    </head>
    <body>
        <div class="ui container" style="padding-top: 50px;">
            <h2 class="ui header">Instalação do Sistema</h2>
            <div class="ui segment">
                <a href="?step=3" class="ui primary button">Iniciar Instalação</a>
            </div>
        </div>
    </body>
    </html>
    <?php
}

function showInstallationForm($currentVersion) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Instalação - YAHE</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    </head>
    <body>
        <div class="ui container" style="padding-top: 50px;">
            <h2 class="ui header">Instalação do Sistema</h2>
            <form class="ui form" method="POST">
                <input type="hidden" name="target_version" value="<?php echo LATEST_VERSION; ?>">
                <div class="field">
                    <label>Versão Atual: <?php echo $currentVersion; ?></label>
                </div>
                <button class="ui primary button" type="submit">Instalar/Atualizar</button>
            </form>
        </div>
    </body>
    </html>
    <?php
}

function performInstallation($conn, $versionControl) {
    try {
        $versionControl->updateToVersion($_POST['target_version']);
        showSuccess("Sistema instalado/atualizado com sucesso!");
    } catch (Exception $e) {
        showError($e->getMessage());
    }
}
