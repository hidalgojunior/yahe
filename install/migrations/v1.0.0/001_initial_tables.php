<?php
class Migration_001_initial_tables {
    private $conn;
    
    public function __construct($conn) {
        $this->conn = $conn;
    }
    
    public function up() {
        // Tabela de Usuários
        $this->conn->exec("CREATE TABLE IF NOT EXISTS usuarios (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nome VARCHAR(100) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            senha VARCHAR(255) NOT NULL,
            tipo ENUM('admin', 'cliente') DEFAULT 'cliente',
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_email (email)
        )");

        // Tabela de Produtos
        $this->conn->exec("CREATE TABLE IF NOT EXISTS produtos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            nome VARCHAR(100) NOT NULL,
            descricao TEXT,
            preco DECIMAL(10,2) NOT NULL,
            imagem VARCHAR(255),
            personalizavel BOOLEAN DEFAULT TRUE,
            ativo BOOLEAN DEFAULT TRUE,
            data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_ativo (ativo),
            INDEX idx_personalizavel (personalizavel)
        )");

        // Tabela de Pedidos
        $this->conn->exec("CREATE TABLE IF NOT EXISTS pedidos (
            id INT PRIMARY KEY AUTO_INCREMENT,
            usuario_id INT,
            status ENUM('pendente', 'aprovado', 'em_producao', 'enviado', 'entregue') DEFAULT 'pendente',
            valor_total DECIMAL(10,2) NOT NULL,
            data_pedido TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
            INDEX idx_status (status),
            INDEX idx_usuario (usuario_id)
        )");
    }
    
    public function down() {
        $this->conn->exec("DROP TABLE IF EXISTS pedidos");
        $this->conn->exec("DROP TABLE IF EXISTS produtos");
        $this->conn->exec("DROP TABLE IF EXISTS usuarios");
    }
} 