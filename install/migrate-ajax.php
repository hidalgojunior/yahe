<?php
header('Content-Type: application/json');

require_once __DIR__ . '/../config/database.php';

try {
    // Busca próxima migração não executada
    $migrations = glob(__DIR__ . '/migrations/*.sql');
    sort($migrations);
    
    $executed = $pdo->query("SELECT migration FROM migrations")->fetchAll(PDO::FETCH_COLUMN);
    
    foreach ($migrations as $file) {
        $filename = basename($file);
        
        if (!in_array($filename, $executed)) {
            // Executa a migração
            $sql = file_get_contents($file);
            $commands = array_filter(
                array_map('trim', explode(';', $sql)),
                function($cmd) { return !empty($cmd); }
            );
            
            $pdo->beginTransaction();
            
            try {
                foreach ($commands as $command) {
                    $pdo->exec($command);
                }
                
                $stmt = $pdo->prepare("INSERT INTO migrations (migration) VALUES (:migration)");
                $stmt->execute(['migration' => $filename]);
                
                $pdo->commit();
                
                echo json_encode([
                    'success' => true,
                    'message' => "Migração {$filename} executada com sucesso."
                ]);
                exit;
                
            } catch (Exception $e) {
                $pdo->rollBack();
                throw $e;
            }
        }
    }
    
    echo json_encode([
        'success' => true,
        'message' => 'Todas as migrações foram executadas.'
    ]);
    
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Erro: ' . $e->getMessage()
    ]);
} 