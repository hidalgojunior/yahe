<?php
class Database {
    private $conn = null;
    
    public function __construct() {
        try {
            $db_config = [
                'host' => 'localhost',
                'dbname' => 'loja_personalizados',
                'username' => 'root',
                'password' => ''  // Ajuste a senha se necessário
            ];
            
            // Primeiro conecta sem o banco de dados
            $this->conn = new PDO(
                "mysql:host={$db_config['host']}",
                $db_config['username'],
                $db_config['password'],
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                ]
            );
            
            // Cria o banco se não existir
            $this->conn->exec("CREATE DATABASE IF NOT EXISTS `{$db_config['dbname']}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            
            // Conecta ao banco criado
            $this->conn->exec("USE `{$db_config['dbname']}`");
            
            // Configura charset
            $this->conn->exec("SET NAMES utf8mb4");
            
        } catch (PDOException $e) {
            throw new Exception("Erro ao conectar ao banco de dados: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->conn;
    }
} 