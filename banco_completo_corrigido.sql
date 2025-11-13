-- BANCO DE DADOS COMPLETO CORRIGIDO PARA O SISTEMA ACADÊMICO TCC
-- Execute este script no MySQL/phpMyAdmin para criar/corrigir o banco

-- DROP DATABASE IF EXISTS bd_tcc;
CREATE DATABASE IF NOT EXISTS bd_tcc CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE bd_tcc;

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_user VARCHAR(100) NOT NULL,
    email_user VARCHAR(100) NOT NULL UNIQUE,
    cpf_user VARCHAR(20) NOT NULL UNIQUE,
    telefone_user VARCHAR(20),
    senha_user VARCHAR(255) NOT NULL,
    tipo_usuario ENUM('Administrador', 'Professor') NOT NULL DEFAULT 'Professor',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    status ENUM('ativo', 'inativo') DEFAULT 'ativo'
);

-- Tabela de matérias/disciplinas
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome_materia VARCHAR(255) NOT NULL,
    descricao_materia TEXT,
    codigo_materia VARCHAR(50) UNIQUE,
    carga_horaria INT DEFAULT 60,
    tipo_materia ENUM('teórica', 'prática', 'teórico-prática') DEFAULT 'teórico-prática',
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    criada_por INT,
    FOREIGN KEY (criada_por) REFERENCES usuarios(id)
);

-- Tabela de relação entre professores e matérias
CREATE TABLE IF NOT EXISTS usuario_materia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    id_materia INT NOT NULL,
    data_atribuicao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_usuario_materia (id_usuario, id_materia),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (id_materia) REFERENCES materias(id) ON DELETE CASCADE
);

-- Tabela de eventos do calendário (incluindo aulas como eventos)
CREATE TABLE IF NOT EXISTS eventos_calendario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_criador INT NOT NULL,
    id_materia INT NULL,
    titulo VARCHAR(255) NOT NULL,
    descricao TEXT,
    tipo_evento ENUM('aula', 'prova', 'reunião', 'evento', 'outro') DEFAULT 'aula',
    data_inicio DATE NOT NULL,
    hora_inicio TIME NOT NULL,
    data_fim DATE NOT NULL,
    hora_fim TIME NOT NULL,
    local_evento VARCHAR(255),
    status ENUM('planejado', 'realizado', 'cancelado', 'adiado') DEFAULT 'planejado',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario_criador) REFERENCES usuarios(id),
    FOREIGN KEY (id_materia) REFERENCES materias(id)
);

-- Tabela de notificações do sistema
CREATE TABLE IF NOT EXISTS notificacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario_destino INT NOT NULL,
    titulo VARCHAR(255) NOT NULL,
    mensagem TEXT NOT NULL,
    tipo ENUM('info', 'success', 'warning', 'danger') DEFAULT 'info',
    lida BOOLEAN DEFAULT FALSE,
    lida_em TIMESTAMP NULL,
    id_referencia INT NULL,
    tabela_referencia VARCHAR(100) NULL,
    criada_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario_destino) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de logs de atividades do sistema
CREATE TABLE IF NOT EXISTS logs_atividades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT NOT NULL,
    acao VARCHAR(100) NOT NULL,
    descricao TEXT,
    tabela_afetada VARCHAR(100),
    id_registro_afetado INT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    data_hora TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

-- Tabela de perfis de usuários
CREATE TABLE IF NOT EXISTS perfis_usuario (
    id INT AUTO_INCREMENT PRIMARY KEY,
    id_usuario INT UNIQUE NOT NULL,
    foto_perfil VARCHAR(255),
    biografia TEXT,
    especialidade VARCHAR(255),
    formacao TEXT,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Tabela de configurações do sistema
CREATE TABLE IF NOT EXISTS configuracoes_sistema (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(100) NOT NULL,
    chave VARCHAR(100) NOT NULL,
    valor TEXT,
    descricao TEXT,
    tipo ENUM('texto', 'numero', 'boolean', 'select') DEFAULT 'texto',
    atualizado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    atualizado_por INT,
    UNIQUE KEY unique_categoria_chave (categoria, chave),
    FOREIGN KEY (atualizado_por) REFERENCES usuarios(id)
);

-- INSERIR ADMINISTRADOR PADRÃO (se não existir)
INSERT IGNORE INTO usuarios (id, nome_user, email_user, cpf_user, senha_user, tipo_usuario)
VALUES (1, 'Administrador', 'admin@escola.com', '00000000000', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador');

-- INSERIR MATÉRIAS PADRÃO (CURSOS TÉCNICOS)
INSERT IGNORE INTO materias (nome_materia, descricao_materia, codigo_materia, carga_horaria, tipo_materia) VALUES
('Mecatrônica Industrial', 'Estudo de sistemas mecânicos e eletrônicos integrados', 'MEC001', 120, 'teórico-prática'),
('Automação Industrial', 'Controle e automação de processos industriais', 'AUT001', 100, 'teórico-prática'),
('Eletrotécnica', 'Sistemas elétricos e instalações industriais', 'ELE001', 120, 'teórico-prática'),
('Mecânica Industrial', 'Máquinas e processos mecânicos industriais', 'MEC002', 120, 'teórico-prática'),
('Eletrônica', 'Circuitos e componentes eletrônicos', 'ELN001', 100, 'teórico-prática'),
('Segurança do Trabalho', 'Normas e procedimentos de segurança', 'SEG001', 40, 'teórica'),
('Qualidade Industrial', 'Gestão da qualidade em processos industriais', 'QUA001', 60, 'teórica'),
('Desenho Técnico', 'Representação técnica de projetos', 'DES001', 80, 'prática'),
('Manutenção Industrial', 'Manutenção de equipamentos industriais', 'MAN001', 100, 'teórico-prática'),
('Hidráulica e Pneumática', 'Sistemas hidráulicos e pneumáticos industriais', 'HID001', 80, 'teórico-prática');

-- INSERIR CONFIGURAÇÕES PADRÃO DO SISTEMA
INSERT IGNORE INTO configuracoes_sistema (categoria, chave, valor, descricao, tipo) VALUES
('sistema', 'nome_sistema', 'Sistema Acadêmico', 'Nome do sistema acadêmico', 'texto'),
('sistema', 'versao', '1.0.0', 'Versão atual do sistema', 'texto'),
('sistema', 'manutencao', '0', 'Sistema em manutenção', 'boolean'),
('notificacoes', 'tempo_expiracao', '30', 'Dias para expirar notificações antigas', 'numero'),
('notificacoes', 'email_habilitado', '0', 'Habilitar envio de notificações por email', 'boolean'),
('calendario', 'duracao_aula_padrao', '50', 'Duração padrão das aulas em minutos', 'numero'),
('calendario', 'intervalo_aulas', '10', 'Intervalo entre aulas em minutos', 'numero'),
('usuarios', 'senha_min_tamanho', '8', 'Tamanho mínimo da senha', 'numero'),
('usuarios', 'senha_expiracao', '90', 'Dias para expirar senha', 'numero'),
('usuarios', 'limite_tentativas_login', '5', 'Número máximo de tentativas de login', 'numero');

-- INSERIR ALGUNS USUÁRIOS DE EXEMPLO
INSERT IGNORE INTO usuarios (nome_user, email_user, cpf_user, senha_user, tipo_usuario) VALUES
('Professor João Silva', 'joao.silva@escola.com', '11111111111', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Professor'),
('Professor Maria Santos', 'maria.santos@escola.com', '22222222222', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Professor'),
('Professor Carlos Oliveira', 'carlos.oliveira@escola.com', '33333333333', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Professor');

-- INSERIR RELAÇÃO PROFESSOR-MATÉRIA
INSERT IGNORE INTO usuario_materia (id_usuario, id_materia)
SELECT u.id, m.id
FROM usuarios u, materias m
WHERE u.tipo_usuario = 'Professor' AND u.id IN (2,3,4)
AND m.id IN (1,2,3)
ORDER BY RAND()
LIMIT 6;

-- ÍNDICES PARA MELHORAR PERFORMANCE
CREATE INDEX idx_notificacoes_destino_lida ON notificacoes(id_usuario_destino, lida);
CREATE INDEX idx_notificacoes_criada_em ON notificacoes(criada_em);
CREATE INDEX idx_eventos_data ON eventos_calendario(data_inicio);
CREATE INDEX idx_logs_data_hora ON logs_atividades(data_hora);
CREATE INDEX idx_logs_usuario ON logs_atividades(id_usuario);

-- CRIAR VIEW PARA LISTAR AULAS COMO EVENTOS
CREATE OR REPLACE VIEW view_aulas_eventos AS
SELECT
    e.id,
    e.titulo,
    e.descricao,
    e.data_inicio AS data_aula,
    e.hora_inicio,
    e.data_fim,
    e.hora_fim,
    e.local_evento AS sala,
    m.nome_materia,
    u.nome_user AS nome_professor,
    e.tipo_evento AS tipo_aula,
    e.status,
    CASE
        WHEN e.status = 'planejado' THEN 'planejada'
        WHEN e.status = 'realizado' THEN 'realizada'
        WHEN e.status = 'cancelado' THEN 'cancelada'
        WHEN e.status = 'adiado' THEN 'adiada'
        ELSE e.status
    END AS status_aula
FROM eventos_calendario e
LEFT JOIN materias m ON e.id_materia = m.id
LEFT JOIN usuarios u ON e.id_usuario_criador = u.id
WHERE e.tipo_evento = 'aula'
ORDER BY e.data_inicio, e.hora_inicio;

COMMIT;
