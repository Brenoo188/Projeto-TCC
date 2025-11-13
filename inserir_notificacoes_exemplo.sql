-- INSERIR NOTIFICAÇÕES DE EXEMPLO PARA TESTAR O SISTEMA
-- Execute este script após criar o banco para adicionar notificações de teste

USE bd_tcc;

-- Inserir algumas notificações de exemplo para o administrador (id = 1)
INSERT IGNORE INTO notificacoes (id_usuario_destino, titulo, mensagem, tipo, tabela_referencia, id_referencia) VALUES
(1, 'Bem-vindo ao Sistema!', 'Seja bem-vindo ao Sistema Acadêmico. Esta é uma notificação de boas-vindas.', 'success', 'sistema', NULL),
(1, 'Novo Professor Cadastrado', 'Professor Carlos Oliveira foi cadastrado no sistema.', 'info', 'cadastros_int', 4),
(1, 'Aula Cadastrada', 'Nova aula de Mecatrônica Industrial foi agendada para próxima semana.', 'info', 'eventos_calendario', 1),
(1, 'Atualização do Sistema', 'O sistema foi atualizado para a versão 1.0.0 com novas funcionalidades.', 'warning', 'sistema', NULL),
(1, 'Lembrete de Segurança', 'Por favor, atualize sua senha para manter a segurança da conta.', 'danger', 'sistema', NULL);

-- Inserir notificações para os professores (ids 2, 3, 4)
INSERT IGNORE INTO notificacoes (id_usuario_destino, titulo, mensagem, tipo, tabela_referencia, id_referencia) VALUES
(2, 'Matéria Atribuída', 'Você foi atribuído à matéria Mecatrônica Industrial.', 'success', 'materias', 1),
(2, 'Próxima Aula', 'Você tem uma aula agendada amanhã às 8h.', 'info', 'eventos_calendario', 1),
(3, 'Matéria Atribuída', 'Você foi atribuído à matéria Automação Industrial.', 'success', 'materias', 2),
(3, 'Reunião de Professores', 'Reunião agendada para sexta-feira às 14h.', 'warning', 'eventos_calendario', 2),
(4, 'Matéria Atribuída', 'Você foi atribuído à matéria Eletrotécnica.', 'success', 'materias', 3),
(4, 'Novo Aluno', 'Novo aluno adicionado à sua turma.', 'info', 'cadastros_int', 5);

-- Inserir alguns logs de atividades de exemplo
INSERT IGNORE INTO logs_atividades (id_usuario, acao, descricao, tabela_afetada, id_registro_afetado) VALUES
(1, 'INSERT', 'Administrador criou usuário Professor Carlos Oliveira', 'usuarios', 4),
(1, 'UPDATE', 'Administrador atualizou configurações do sistema', 'configuracoes_sistema', 1),
(2, 'INSERT', 'Professor João Silva criou nova aula', 'eventos_calendario', 1),
(3, 'UPDATE', 'Professor Maria Santos atualizou matéria', 'materias', 2),
(1, 'LOGIN', 'Administrador acessou o sistema', 'usuarios', 1);

COMMIT;