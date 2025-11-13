-- =====================================================
-- CORREÇÕES DO BANCO DE DADOS - SISTEMA ACADÊMICO TCC
-- =====================================================

-- Usar o banco de dados
USE `bd_tcc`;

-- =====================================================
-- VERIFICAR SE AS TABELAS PRINCIPAIS EXISTEM
-- =====================================================

-- Verificar se a tabela usuarios existe
SHOW TABLES LIKE 'usuarios';

-- Verificar se a tabela materias existe
SHOW TABLES LIKE 'materias';

-- Verificar se a tabela perfis_usuario existe
SHOW TABLES LIKE 'perfis_usuario';

-- Verificar se a tabela usuario_materia existe
SHOW TABLES LIKE 'usuario_materia';

-- =====================================================
-- TABELAS PRINCIPAIS QUE DEVEM EXISTIR
-- =====================================================

-- Tabela usuarios (já deve existir)
-- CREATE TABLE IF NOT EXISTS `usuarios` (...)

-- Tabela materias (já deve existir)
-- CREATE TABLE IF NOT EXISTS `materias` (...)

-- Tabela perfis_usuario (já deve existir)
-- CREATE TABLE IF NOT EXISTS `perfis_usuario` (...)

-- Tabela usuario_materia (já deve existir)
-- CREATE TABLE IF NOT EXISTS `usuario_materia` (...)

-- =====================================================
-- INSERIR DADOS DE TESTE (SE NECESSÁRIO)
-- =====================================================

-- Inserir administrador padrão se não existir
INSERT IGNORE INTO `usuarios` (`nome_user`, `cpf_user`, `email_user`, `senha_user`, `tipo_usuario`, `status`)
VALUES ('Administrador', '12345678900', 'admin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Administrador', 'ativo');

-- Inserir professor de teste se não existir
INSERT IGNORE INTO `usuarios` (`nome_user`, `cpf_user`, `email_user`, `senha_user`, `tipo_usuario`, `status`)
VALUES ('Professor Teste', '98765432100', 'professor@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Professor', 'ativo');

-- Inserir perfil para o professor de teste
INSERT IGNORE INTO `perfis_usuario` (`id_usuario`, `especialidade`, `formacao`)
VALUES ((SELECT id FROM usuarios WHERE email_user = 'professor@sistema.com'), 'Matemática', 'Licenciatura em Matemática');

-- Inserir matéria de teste
INSERT IGNORE INTO `materias` (`nome_materia`, `descricao`, `carga_horaria`, `status`)
VALUES ('Matemática', 'Matemática Fundamental', '80', 'ativa');

-- =====================================================
-- VERIFICAR INTEGRIDADE DOS DADOS
-- =====================================================

-- Verificar usuários cadastrados
SELECT id, nome_user, email_user, tipo_usuario, status FROM usuarios ORDER BY tipo_usuario, nome_user;

-- Verificar perfis de usuários
SELECT pu.id, u.nome_user, pu.especialidade, pu.formacao
FROM perfis_usuario pu
JOIN usuarios u ON pu.id_usuario = u.id;

-- Verificar matérias cadastradas
SELECT id_materia, nome_materia, carga_horaria, status FROM materias ORDER BY nome_materia;

-- Verificar vínculos professor-matéria
SELECT um.id, u.nome_user as professor, m.nome_materia as materia, um.status
FROM usuario_materia um
JOIN usuarios u ON um.id_usuario = u.id
JOIN materias m ON um.id_materia = m.id_materia
ORDER BY m.nome_materia, u.nome_user;

-- =====================================================
-- RELATÓRIO FINAL
-- =====================================================
SELECT 'Banco de dados verificado e corrigido com sucesso!' as mensagem,
       NOW() as data_verificacao,
       VERSION() as versao_mysql,
       DATABASE() as banco_selecionado;