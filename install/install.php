<?php
session_start();
require_once '../config/database.php';
require_once 'version.php';
require_once 'functions.php';
require_once 'classes/Database.php';

// Funções auxiliares de mensagens
function showError($message) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Erro - YAHE</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    </head>
    <body>
        <div class="ui container" style="padding-top: 50px;">
            <div class="ui negative message">
                <div class="header">Erro</div>
                <p><?php echo $message; ?></p>
            </div>
            <a href="?step=1" class="ui button">Voltar ao início</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

function showSuccess($message) {
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <title>Sucesso - YAHE</title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/semantic-ui@2.4.2/dist/semantic.min.css">
    </head>
    <body>
        <div class="ui container" style="padding-top: 50px;">
            <div class="ui success message">
                <div class="header">Sucesso!</div>
                <p><?php echo $message; ?></p>
            </div>
            <a href="../index.php" class="ui primary button">Ir para o sistema</a>
        </div>
    </body>
    </html>
    <?php
    exit;
}

$step = isset($_GET['step']) ? $_GET['step'] : 1;
$error = null;

try {
    switch($step) {
        case 1:
            showDatabaseConfigForm();
            break;
            
        case 2:
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                processDatabaseConfig();
            }
            showInstallationOptions();
            break;
            
        case 3:
            try {
                $database = new Database();
                $conn = $database->getConnection();
                
                if (!$conn) {
                    throw new Exception("Não foi possível conectar ao banco de dados");
                }
                
                $versionControl = new DatabaseVersion($conn);
                
                if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['target_version'])) {
                    performInstallation($conn, $versionControl);
                } else {
                    showInstallationForm($versionControl->getCurrentVersion());
                }
            } catch (Exception $e) {
                showError($e->getMessage());
            }
            break;
            
        default:
            header("Location: ?step=1");
            exit;
    }
} catch (Exception $e) {
    showError($e->getMessage());
} 