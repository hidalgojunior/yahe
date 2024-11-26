INSERT INTO usuarios (
    nome,
    email,
    senha,
    cpf,
    telefone
) VALUES (
    'Administrador',
    'admin@admin.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- senha: password
    '000.000.000-00',
    '(00) 00000-0000'
);

-- Inserir algumas categorias iniciais
INSERT INTO categorias (nome, slug) VALUES 
('Camisetas', 'camisetas'),
('Canecas', 'canecas'),
('Adesivos', 'adesivos'),
('Quadros', 'quadros'); 