<?php
class DatabaseVersion {
    private $conn;
    private $version_table = 'db_version';
    
    public function __construct($conn) {
        if (!$conn) {
            throw new Exception("Conexão com o banco de dados não estabelecida");
        }
        $this->conn = $conn;
        $this->createVersionTable();
    }

    private function createVersionTable() {
        try {
            // Verifica se a tabela já existe
            $stmt = $this->conn->query("SHOW TABLES LIKE '{$this->version_table}'");
            if ($stmt->rowCount() == 0) {
                $sql = "CREATE TABLE IF NOT EXISTS {$this->version_table} (
                    id INT PRIMARY KEY AUTO_INCREMENT,
                    version VARCHAR(10) NOT NULL,
                    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                )";
                
                $this->conn->exec($sql);
                
                // Insere a versão inicial
                $sql = "INSERT INTO {$this->version_table} (version) VALUES ('0.0.0')";
                $this->conn->exec($sql);
            }
        } catch (PDOException $e) {
            throw new Exception("Erro ao criar tabela de versão: " . $e->getMessage());
        }
    }

    public function getCurrentVersion() {
        try {
            $sql = "SELECT version FROM {$this->version_table} ORDER BY id DESC LIMIT 1";
            $stmt = $this->conn->query($sql);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ? $result['version'] : '0.0.0';
        } catch (PDOException $e) {
            return '0.0.0';
        }
    }

    public function setVersion($version) {
        $sql = "INSERT INTO {$this->version_table} (version) VALUES (:version)";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['version' => $version]);
    }
} 