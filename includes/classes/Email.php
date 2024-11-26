<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/../../vendor/autoload.php';

class Email {
    private $mailer;
    
    public function __construct() {
        $this->mailer = new PHPMailer(true);
        
        // Configurações do servidor de e-mail
        $this->mailer->isSMTP();
        $this->mailer->Host = 'smtp.gmail.com'; // Altere conforme seu servidor
        $this->mailer->SMTPAuth = true;
        $this->mailer->Username = 'seu-email@gmail.com';
        $this->mailer->Password = 'sua-senha-app';
        $this->mailer->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $this->mailer->Port = 587;
        $this->mailer->CharSet = 'UTF-8';
        
        // Configurações do remetente
        $this->mailer->setFrom('noreply@yahe.com.br', 'YAHE Personalizados');
    }
    
    public function enviarConfirmacaoPedido($pedido) {
        try {
            $this->mailer->addAddress($pedido['cliente_email'], $pedido['cliente_nome']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Pedido #{$pedido['order_id']} - Confirmação";
            
            // Gera o HTML do e-mail
            $html = $this->gerarHTMLPedido($pedido);
            
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $html));
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $e->getMessage());
            return false;
        }
    }
    
    public function enviarAtualizacaoStatus($pedido) {
        try {
            $this->mailer->addAddress($pedido['cliente_email'], $pedido['cliente_nome']);
            $this->mailer->isHTML(true);
            $this->mailer->Subject = "Pedido #{$pedido['order_id']} - Atualização de Status";
            
            $status_texto = [
                'pendente' => 'Aguardando pagamento',
                'pago' => 'Pagamento confirmado',
                'preparando' => 'Em preparação',
                'enviado' => 'Enviado',
                'entregue' => 'Entregue',
                'cancelado' => 'Cancelado'
            ];
            
            $html = "
            <h2>Atualização do seu Pedido</h2>
            <p>Olá {$pedido['cliente_nome']},</p>
            <p>O status do seu pedido foi atualizado para: <strong>{$status_texto[$pedido['status']]}</strong></p>
            <p>Número do pedido: #{$pedido['order_id']}</p>
            ";
            
            if ($pedido['status'] === 'enviado') {
                $html .= "<p>Código de rastreamento: {$pedido['codigo_rastreio']}</p>";
            }
            
            $this->mailer->Body = $html;
            $this->mailer->AltBody = strip_tags(str_replace('<br>', "\n", $html));
            
            return $this->mailer->send();
            
        } catch (Exception $e) {
            error_log('Erro ao enviar e-mail: ' . $e->getMessage());
            return false;
        }
    }
    
    private function gerarHTMLPedido($pedido) {
        $html = "
        <h2>Confirmação de Pedido</h2>
        <p>Olá {$pedido['cliente_nome']},</p>
        <p>Recebemos seu pedido com sucesso!</p>
        
        <h3>Detalhes do Pedido #{$pedido['order_id']}</h3>
        <table style='width: 100%; border-collapse: collapse;'>
            <tr>
                <th style='text-align: left; padding: 8px; border-bottom: 1px solid #ddd;'>Produto</th>
                <th style='text-align: right; padding: 8px; border-bottom: 1px solid #ddd;'>Quantidade</th>
                <th style='text-align: right; padding: 8px; border-bottom: 1px solid #ddd;'>Valor</th>
            </tr>";
        
        foreach ($pedido['itens'] as $item) {
            $valor = number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.');
            $html .= "
            <tr>
                <td style='padding: 8px; border-bottom: 1px solid #ddd;'>{$item['nome']}</td>
                <td style='text-align: right; padding: 8px; border-bottom: 1px solid #ddd;'>{$item['quantidade']}</td>
                <td style='text-align: right; padding: 8px; border-bottom: 1px solid #ddd;'>R$ {$valor}</td>
            </tr>";
        }
        
        $total = number_format($pedido['valor_total'], 2, ',', '.');
        $html .= "
            <tr>
                <td colspan='2' style='text-align: right; padding: 8px;'><strong>Total:</strong></td>
                <td style='text-align: right; padding: 8px;'><strong>R$ {$total}</strong></td>
            </tr>
        </table>
        
        <h3>Endereço de Entrega</h3>
        <p>
            {$pedido['endereco_rua']}, {$pedido['endereco_numero']}<br>
            {$pedido['endereco_complemento']}<br>
            {$pedido['endereco_bairro']}<br>
            {$pedido['endereco_cidade']} - {$pedido['endereco_estado']}<br>
            CEP: {$pedido['endereco_cep']}
        </p>
        
        <h3>Forma de Pagamento</h3>
        <p>" . ($pedido['forma_pagamento'] === 'pix' ? 'PIX' : 'Cartão de Crédito') . "</p>";
        
        if ($pedido['forma_pagamento'] === 'pix' && !empty($pedido['qr_code_pix'])) {
            $html .= "
            <p>Escaneie o QR Code abaixo para realizar o pagamento:</p>
            <img src='{$pedido['qr_code_pix']}' alt='QR Code PIX' style='max-width: 200px;'>";
        }
        
        $html .= "
        <p>
            <small>
                Em caso de dúvidas, entre em contato conosco respondendo este e-mail<br>
                ou através do nosso WhatsApp: (00) 00000-0000
            </small>
        </p>";
        
        return $html;
    }
} 