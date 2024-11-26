<?php

require_once __DIR__ . '/Email.php';

class Pedido {
    private $db;
    private $email;
    
    public function __construct($db) {
        $this->db = $db;
        $this->email = new Email();
    }
    
    public function criar($dados, $itens, $pagamento) {
        try {
            $this->db->beginTransaction();
            
            // Insere o pedido
            $sql = "INSERT INTO pedidos (
                order_id, cliente_nome, cliente_cpf, cliente_email, cliente_telefone,
                endereco_cep, endereco_rua, endereco_numero, endereco_complemento,
                endereco_bairro, endereco_cidade, endereco_estado, valor_total,
                forma_pagamento, status
            ) VALUES (
                :order_id, :nome, :cpf, :email, :telefone,
                :cep, :endereco, :numero, :complemento,
                :bairro, :cidade, :estado, :total,
                :forma_pagamento, :status
            )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'order_id' => $dados['order_id'],
                'nome' => $dados['nome'],
                'cpf' => $dados['cpf'],
                'email' => $dados['email'],
                'telefone' => $dados['telefone'],
                'cep' => $dados['cep'],
                'endereco' => $dados['endereco'],
                'numero' => $dados['numero'],
                'complemento' => $dados['complemento'],
                'bairro' => $dados['bairro'],
                'cidade' => $dados['cidade'],
                'estado' => $dados['estado'],
                'total' => $dados['total'],
                'forma_pagamento' => $dados['payment'],
                'status' => 'pendente'
            ]);
            
            $pedido_id = $this->db->lastInsertId();
            
            // Insere os itens do pedido
            foreach ($itens as $item) {
                $sql = "INSERT INTO pedidos_itens (
                    pedido_id, produto_id, quantidade, preco_unitario,
                    cor, tamanho, personalizado, design_frente, design_costas
                ) VALUES (
                    :pedido_id, :produto_id, :quantidade, :preco_unitario,
                    :cor, :tamanho, :personalizado, :design_frente, :design_costas
                )";
                
                $stmt = $this->db->prepare($sql);
                $stmt->execute([
                    'pedido_id' => $pedido_id,
                    'produto_id' => $item['id'],
                    'quantidade' => $item['quantidade'],
                    'preco_unitario' => $item['preco'],
                    'cor' => $item['cor'] ?? null,
                    'tamanho' => $item['tamanho'] ?? null,
                    'personalizado' => isset($item['personalizado']) ? 1 : 0,
                    'design_frente' => $item['design_frente'] ?? null,
                    'design_costas' => $item['design_costas'] ?? null
                ]);
            }
            
            // Insere o pagamento
            $sql = "INSERT INTO pagamentos (
                pedido_id, tipo, status, valor, parcelas,
                codigo_transacao, qr_code_pix
            ) VALUES (
                :pedido_id, :tipo, :status, :valor, :parcelas,
                :codigo_transacao, :qr_code_pix
            )";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'pedido_id' => $pedido_id,
                'tipo' => $pagamento['type'],
                'status' => $pagamento['status'],
                'valor' => $dados['total'],
                'parcelas' => $pagamento['parcelas'] ?? 1,
                'codigo_transacao' => $pagamento['codigo_transacao'] ?? null,
                'qr_code_pix' => $pagamento['qr_code'] ?? null
            ]);
            
            $this->db->commit();
            
            // Envia e-mail de confirmação
            $pedido_completo = $this->buscarPorOrderId($dados['order_id']);
            $this->email->enviarConfirmacaoPedido($pedido_completo);
            
            return $pedido_id;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }
    
    public function buscarPorOrderId($order_id) {
        $sql = "SELECT p.*, pg.tipo as pagamento_tipo, pg.status as pagamento_status,
                       pg.qr_code_pix, pg.codigo_transacao
                FROM pedidos p
                LEFT JOIN pagamentos pg ON pg.pedido_id = p.id
                WHERE p.order_id = :order_id";
                
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['order_id' => $order_id]);
        
        $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($pedido) {
            // Busca os itens do pedido
            $sql = "SELECT * FROM pedidos_itens WHERE pedido_id = :pedido_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['pedido_id' => $pedido['id']]);
            $pedido['itens'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        
        return $pedido;
    }
    
    public function atualizarStatus($order_id, $status) {
        $sql = "UPDATE pedidos SET status = :status WHERE order_id = :order_id";
        $stmt = $this->db->prepare($sql);
        $result = $stmt->execute([
            'status' => $status,
            'order_id' => $order_id
        ]);
        
        if ($result) {
            // Envia e-mail de atualização
            $pedido = $this->buscarPorOrderId($order_id);
            $this->email->enviarAtualizacaoStatus($pedido);
        }
        
        return $result;
    }
} 