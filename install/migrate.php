<?php
define('ROOT_PATH', dirname(__DIR__));
require_once ROOT_PATH . '/config/database.php';

try {
    // Cria tabela de controle de migrações se não existir
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS migrations (
            id INT AUTO_INCREMENT PRIMARY KEY,
            migration VARCHAR(255) NOT NULL,
            executed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");
    
    // Lista todos os arquivos de migração
    $migrations = glob(__DIR__ . '/migrations/*.sql');
    sort($migrations); // Garante ordem alfabética
    
    // Busca migrações já executadas
    $executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
    
    // Verifica se existem migrações
    if (empty($migrations)) {
        echo "Nenhum arquivo de migração encontrado em " . __DIR__ . "/migrations/\n";
        exit;
    }
    
    foreach ($migrations as $file) {
        $filename = basename($file);
        
        // Pula se já foi executada
        if (in_array($filename, $executed)) {
            echo "Migração {$filename} já executada.\n";
            continue;
        }
        
        echo "Executando migração {$filename}...\n";
        
        // Lê e executa o SQL
        $sql = file_get_contents($file);
        if ($sql === false) {
            throw new Exception("Não foi possível ler o arquivo {$filename}");
        }
        
        // Divide em comandos separados por ;
        $commands = array_filter(
            array_map('trim', explode(';', $sql)),
            function($cmd) { return !empty($cmd); }
        );
        
        // Inicia transação
        $pdo->beginTransaction();
        
        try {
            foreach ($commands as $command) {
                $pdo->exec($command);
            }
            
            // Registra migração
            $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
            $stmt->execute(['migration' => $filename]);
            
            $pdo->commit();
            echo "Migração {$filename} executada com sucesso.\n";
            
        } catch (Exception $e) {
            $pdo->rollBack();
            throw new Exception("Erro na migração {$filename}: " . $e->getMessage());
        }
    }
    
    echo "\nTodas as migrações foram executadas com sucesso!\n";
    
} catch (Exception $e) {
    echo "Erro: " . $e->getMessage() . "\n";
    exit(1);
} 