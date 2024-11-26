<?php
require_once '../includes/auth.php';
require_once '../config/database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

try {
    $endereco_id = $_POST['endereco_id'] ?? 0;
    
    // Verifica se o endereço pertence ao usuário
    $stmt = $pdo->prepare("SELECT id FROM enderecos WHERE id = :id AND usuario_id = :usuario_id");
    $stmt->execute([
        'id' => $endereco_id,
        'usuario_id' => $_SESSION['user_id']
    ]);
    
    if ($stmt->fetch()) {
        // Remove principal de todos os endereços do usuário
        $stmt = $pdo->prepare("UPDATE enderecos SET principal = 0 WHERE usuario_id = :usuario_id");
        $stmt->execute(['usuario_id' => $_SESSION['user_id']]);
        
        // Define o novo endereço principal
        $stmt = $pdo->prepare("UPDATE enderecos SET principal = 1 WHERE id = :id");
        $stmt->execute(['id' => $endereco_id]);
        
        echo json_encode(['success' => true]);
    } else {
        throw new Exception('Endereço não encontrado');
    }
    
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} 