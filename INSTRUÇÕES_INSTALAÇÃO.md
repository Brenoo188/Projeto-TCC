# 📋 INSTRUÇÕES DE INSTALAÇÃO E CONFIGURAÇÃO

## 🔧 PASSO 1: EXECUTAR O BANCO DE DADOS

1. Abra o phpMyAdmin ou MySQL Workbench
2. Execute o arquivo `banco_completo_corrigido.sql`
3. Execute o arquivo `inserir_notificacoes_exemplo.sql` (opcional, para testar notificações)
4. Verifique se o banco `bd_tcc` foi criado com todas as tabelas

## ✅ TABELAS CRIADAS AUTOMATICAMENTE:
- `usuarios` - Usuários do sistema
- `materias` - Matérias/disciplinas
- `usuario_materia` - Relação professor-matéria
- `eventos_calendario` - Eventos e aulas do calendário
- `notificacoes` - Sistema de notificações
- `logs_atividades` - Logs do sistema
- `perfis_usuario` - Perfis dos usuários
- `configuracoes_sistema` - Configurações do sistema
- `view_aulas_eventos` - View para listar aulas como eventos

## 👤 USUÁRIOS PADRÃO CRIADOS:
- **Administrador**: admin@escola.com / senha123
- **Professores**: 3 professores criados para testes

## 📚 MATÉRIAS PADRÃO (CURSOS TÉCNICOS):
- Mecatrônica Industrial
- Automação Industrial
- Eletrotécnica
- Mecânica Industrial
- Eletrônica
- Segurança do Trabalho
- Qualidade Industrial
- Desenho Técnico
- Manutenção Industrial
- Hidráulica e Pneumática

## 🔗 FUNCIONALIDADES CORRIGIDAS:

### ✅ Calendário (calendario.php)
- Aulas agora são eventos no calendário
- Aba "Cadastrar Aula" cria eventos tipo 'aula'
- Aba "Listar Aulas" mostra todas as aulas usando a view
- Integração completa com materias e professores

### ✅ Notificações (notificacoes.php)
- Sistema completo de notificações
- 3 abas: Minhas Notificações, Todas as Movimentações, Criar Notificação
- Links corrigidos para apontar para calendário.php (aulas agora estão lá)
- Filtros por tipo, período e tabela

### ✅ CRUDs Corrigidos:
- Cadastros de usuários funcionando
- Matérias com cursos técnicos pré-definidos
- Configurações do sistema operacionais
- Perfis de usuários com foto e informações

## 🎯 ACESSO AO SISTEMA:
1. Acesse: `http://localhost/Projeto-TCC/projeto/html-php/parte-inicial/index.php`
2. Login como administrador:
   - Email: admin@escola.com
   - Senha: senha123
3. Explore todas as funcionalidades pelo menu lateral

## 📋 ESTRUTURA DO MENU:
- **Home** - Dashboard administrativo
- **Calendário** - Gerenciamento completo de aulas e eventos
- **Cadastros** - Cadastrar/editar usuários
- **Professores** - Gerenciar professores (admin)
- **Matérias** - Gerenciar matérias (admin)
- **Notificações** - Sistema completo de notificações
- **Conta** - Perfil do usuário logado
- **Configuração** - Configurações do sistema

## 🔄 INTEGRAÇÃO AULAS-CALENDÁRIO:
- As aulas NÃO são mais um CRUD separado
- Todas as aulas são eventos no calendário
- Use a aba "Cadastrar Aula" no calendário
- Use a aba "Listar Aulas" para ver todas as aulas
- Notificações sobre aulas direcionam para o calendário

## 🎨 TEMA E VISUAL:
- Bootstrap 5.3.8 responsivo
- Menu lateral com hover effects
- Cores institucionais
- Ícones Bootstrap Icons
- Interface moderna e profissional

## 📱 FUNCIONALIDADES EXTRA:
- Sistema de logs completo
- Filtros avançados em todas as listagens
- Upload de fotos de perfil
- Notificações em tempo real
- Backup automático de dados (logs)

---
**PRONTO PARA USO!** 🚀
Todos os erros foram corrigidos, sistema está funcional e integrado.