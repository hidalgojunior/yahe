<?php
define('LATEST_VERSION', '1.0.0');

class DatabaseVersion {
    private $conn;
    private $migrations_path;
    
    public function __construct($conn) {
        $this->conn = $conn;
        $this->migrations_path = __DIR__ . '/migrations';
        $this->createVersionTable();
    }
    
    private function createVersionTable() {
        $sql = "CREATE TABLE IF NOT EXISTS db_version (
            version VARCHAR(10) NOT NULL
        )";
        $this->conn->exec($sql);
        
        // Insere versão inicial se tabela estiver vazia
        $stmt = $this->conn->query("SELECT COUNT(*) FROM db_version");
        if ($stmt->fetchColumn() == 0) {
            $this->conn->exec("INSERT INTO db_version (version) VALUES ('0.0.0')");
        }
    }
    
    public function getCurrentVersion() {
        $stmt = $this->conn->query("SELECT version FROM db_version LIMIT 1");
        return $stmt->fetchColumn();
    }
    
    public function updateToVersion($version) {
        // Lista arquivos de migração
        $migrations = glob($this->migrations_path . '/*.sql');
        sort($migrations);
        
        foreach ($migrations as $file) {
            // Executa a migração
            try {
                $sql = file_get_contents($file);
                
                // Divide o SQL em comandos separados por ;
                $commands = array_filter(
                    array_map('trim', explode(';', $sql)),
                    function($cmd) { return !empty($cmd); }
                );
                
                // Executa cada comando separadamente
                foreach ($commands as $command) {
                    if (!empty($command)) {
                        $this->conn->exec($command);
                    }
                }
                
                // Registra a migração
                $stmt = $this->conn->prepare("INSERT INTO db_version (version) VALUES (?)");
                $stmt->execute([$version]);
                
            } catch (Exception $e) {
                throw new Exception("Erro na migração {$file}: " . $e->getMessage());
            }
        }
    }
} 