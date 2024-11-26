<?php
require_once __DIR__ . '/../../vendor/autoload.php';

class Pagamento {
    private $db;
    private $api_key;
    private $api_url;
    
    public function __construct($db) {
        $this->db = $db;
        $this->api_key = '$aact_MzkwODA2MWY2OGM3MWRlMDU2NWM3MzJlNzZmNGZhZGY6OjA1NjQwYmIwLTZhODUtNGVlNy1hNGFlLTgxMTdkYmU2ZjI3ODo6JGFhY2hfN2RmNmVmMjMtNzk5MS00YTc5LTgzMjctOGIwOTE5MGE5MmY0'; // Chave de API do Asaas
        $this->api_url = 'https://sandbox.asaas.com/api/v3'; // Usar https://api.asaas.com/v3 para produção
    }
    
    private function request($endpoint, $method = 'GET', $data = null) {
        $curl = curl_init();
        
        $options = [
            CURLOPT_URL => $this->api_url . $endpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'access_token: ' . $this->api_key
            ]
        ];
        
        if ($data && in_array($method, ['POST', 'PUT'])) {
            $options[CURLOPT_POSTFIELDS] = json_encode($data);
        }
        
        curl_setopt_array($curl, $options);
        
        $response = curl_exec($curl);
        $err = curl_error($curl);
        
        curl_close($curl);
        
        if ($err) {
            throw new Exception('Erro na requisição: ' . $err);
        }
        
        return json_decode($response, true);
    }
    
    private function criarOuAtualizarCliente($pedido) {
        try {
            // Busca cliente por CPF
            $cpf = preg_replace('/\D/', '', $pedido['cliente_cpf']);
            $clientes = $this->request("/customers?cpfCnpj={$cpf}");
            
            if (!empty($clientes['data'])) {
                $cliente = $clientes['data'][0];
                
                // Atualiza cliente
                $this->request("/customers/{$cliente['id']}", 'PUT', [
                    'name' => $pedido['cliente_nome'],
                    'email' => $pedido['cliente_email'],
                    'phone' => $pedido['cliente_telefone'],
                    'mobilePhone' => $pedido['cliente_telefone'],
                    'cpfCnpj' => $cpf,
                    'postalCode' => preg_replace('/\D/', '', $pedido['endereco_cep']),
                    'address' => $pedido['endereco_rua'],
                    'addressNumber' => $pedido['endereco_numero'],
                    'complement' => $pedido['endereco_complemento'],
                    'province' => $pedido['endereco_bairro'],
                    'city' => $pedido['endereco_cidade'],
                    'state' => $pedido['endereco_estado']
                ]);
                
                return $cliente['id'];
            }
            
            // Cria novo cliente
            $cliente = $this->request('/customers', 'POST', [
                'name' => $pedido['cliente_nome'],
                'email' => $pedido['cliente_email'],
                'phone' => $pedido['cliente_telefone'],
                'mobilePhone' => $pedido['cliente_telefone'],
                'cpfCnpj' => $cpf,
                'postalCode' => preg_replace('/\D/', '', $pedido['endereco_cep']),
                'address' => $pedido['endereco_rua'],
                'addressNumber' => $pedido['endereco_numero'],
                'complement' => $pedido['endereco_complemento'],
                'province' => $pedido['endereco_bairro'],
                'city' => $pedido['endereco_cidade'],
                'state' => $pedido['endereco_estado']
            ]);
            
            return $cliente['id'];
            
        } catch (Exception $e) {
            throw new Exception('Erro ao processar cliente: ' . $e->getMessage());
        }
    }
    
    public function gerarPix($pedido) {
        try {
            $customer_id = $this->criarOuAtualizarCliente($pedido);
            
            $payment = $this->request('/payments', 'POST', [
                'customer' => $customer_id,
                'billingType' => 'PIX',
                'value' => $pedido['valor_total'],
                'dueDate' => date('Y-m-d', strtotime('+1 day')),
                'description' => "Pedido #{$pedido['order_id']}",
                'externalReference' => $pedido['order_id']
            ]);
            
            if ($payment['id']) {
                return [
                    'status' => $payment['status'],
                    'qr_code' => $payment['pix']['qrCode'],
                    'qr_code_text' => $payment['pix']['payload'],
                    'codigo_transacao' => $payment['id']
                ];
            }
            
            throw new Exception('Erro ao gerar PIX');
            
        } catch (Exception $e) {
            throw new Exception('Erro ao processar pagamento: ' . $e->getMessage());
        }
    }
    
    public function processarCartao($pedido, $card_data) {
        try {
            $customer_id = $this->criarOuAtualizarCliente($pedido);
            
            // Tokeniza o cartão
            $card_token = $this->request('/creditCard/tokenize', 'POST', [
                'customer' => $customer_id,
                'creditCard' => [
                    'holderName' => $card_data['holder_name'],
                    'number' => $card_data['number'],
                    'expiryMonth' => $card_data['expiry_month'],
                    'expiryYear' => $card_data['expiry_year'],
                    'ccv' => $card_data['cvv']
                ]
            ]);
            
            // Cria o pagamento
            $payment = $this->request('/payments', 'POST', [
                'customer' => $customer_id,
                'billingType' => 'CREDIT_CARD',
                'value' => $pedido['valor_total'],
                'dueDate' => date('Y-m-d'),
                'description' => "Pedido #{$pedido['order_id']}",
                'externalReference' => $pedido['order_id'],
                'creditCard' => [
                    'holderName' => $card_data['holder_name'],
                    'creditCardToken' => $card_token['creditCardToken'],
                    'creditCardHolderInfo' => [
                        'name' => $pedido['cliente_nome'],
                        'email' => $pedido['cliente_email'],
                        'cpfCnpj' => preg_replace('/\D/', '', $pedido['cliente_cpf']),
                        'postalCode' => preg_replace('/\D/', '', $pedido['endereco_cep']),
                        'addressNumber' => $pedido['endereco_numero'],
                        'phone' => $pedido['cliente_telefone']
                    ]
                ],
                'installmentCount' => $card_data['parcelas']
            ]);
            
            if ($payment['id']) {
                return [
                    'status' => $payment['status'],
                    'codigo_transacao' => $payment['id']
                ];
            }
            
            throw new Exception('Erro ao processar cartão');
            
        } catch (Exception $e) {
            throw new Exception('Erro ao processar pagamento: ' . $e->getMessage());
        }
    }
    
    public function processarWebhook($data) {
        try {
            $payment = $this->request('/payments/' . $data['payment']['id']);
            
            // Busca o pedido pelo código de transação
            $sql = "SELECT p.order_id 
                    FROM pedidos p 
                    JOIN pagamentos pg ON pg.pedido_id = p.id 
                    WHERE pg.codigo_transacao = :codigo";
            
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['codigo' => $payment['id']]);
            $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($pedido) {
                $pedidoObj = new Pedido($this->db);
                
                // Mapeia status do Asaas para status interno
                $status_map = [
                    'CONFIRMED' => 'approved',
                    'RECEIVED' => 'approved',
                    'PENDING' => 'pending',
                    'FAILED' => 'failed',
                    'CANCELLED' => 'cancelled'
                ];
                
                // Atualiza status do pagamento
                $pedidoObj->atualizarStatusPagamento($pedido['order_id'], $status_map[$payment['status']]);
                
                // Se aprovado, atualiza status do pedido
                if (in_array($payment['status'], ['CONFIRMED', 'RECEIVED'])) {
                    $pedidoObj->atualizarStatus($pedido['order_id'], 'pago');
                }
                
                return true;
            }
            
            return false;
            
        } catch (Exception $e) {
            error_log('Erro ao processar webhook: ' . $e->getMessage());
            return false;
        }
    }
} 