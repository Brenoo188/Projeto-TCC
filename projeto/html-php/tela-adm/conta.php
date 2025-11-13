<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

// Buscar dados do usuário logado
$stmt = $conexao->prepare("SELECT * FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Buscar dados do perfil extendido
$stmt = $conexao->prepare("SELECT * FROM perfis_usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $_SESSION['id']);
$stmt->execute();
$perfil = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Conta - Sistema Acadêmico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/menu-padrao.css">
    <link rel="stylesheet" href="../../css/css_telaadm/conta.css">
</head>

<body>
    <!-- Menu Lateral -->
    <nav class="menu-lateral">
        <div class="btn-expandir" id="btn-expan" style="padding-right: 0px;">
            <i class="bi bi-list"></i>
        </div>

        <ul>
            <li class="menu-botoes">
                <a href="home.php">
                    <span class="icon"><i class="bi bi-house"></i></span>
                    <span class="txt-link">home</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="calendario.php">
                    <span class="icon"><i class="bi bi-calendar"></i></span>
                    <span class="txt-link">Calendário</span>
                </a>
            </li>

            <?php if ($_SESSION['tipo_usuario'] === 'Administrador'): ?>
            <li class="menu-botoes">
                <a href="cadastros_int.php">
                    <span class="icon"><i class="bi bi-people"></i></span>
                    <span class="txt-link">Cadastros</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="professores.php">
                    <span class="icon"><i class="bi bi-person-workspace"></i></span>
                    <span class="txt-link">Professores</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="materias.php">
                    <span class="icon"><i class="bi bi-book"></i></span>
                    <span class="txt-link">Matérias</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="menu-botoes">
                <a href="notificacoes.php">
                    <span class="icon"><i class="bi bi-bell"></i></span>
                    <span class="txt-link">Notificações</span>
                </a>
            </li>

            <li class="menu-botoes ativo">
                <a href="conta.php">
                    <span class="icon"><i class="bi bi-person-circle"></i></span>
                    <span class="txt-link">Conta</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="configuracao.php">
                    <span class="icon"><i class="bi bi-gear"></i></span>
                    <span class="txt-link">Configuração</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="conteudo-conta">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">
                        <i class="bi bi-person-circle"></i> Minha Conta
                    </h1>

                    <!-- Mensagens -->
                    <?php if (isset($_SESSION['mensagem'])): ?>
                        <div class="alert alert-<?php echo $_SESSION['tipo_mensagem']; ?> alert-dismissible fade show" role="alert">
                            <?php
                            echo $_SESSION['mensagem'];
                            unset($_SESSION['mensagem']);
                            unset($_SESSION['tipo_mensagem']);
                            ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Informações do Usuário -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person"></i> Informações Pessoais
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="atualizar_dados.php">
                                <div class="row">
                                    <div class="col-md-3 text-center">
                                        <div class="usuario-avatar-container mb-3">
                                            <div class="usuario-avatar">
                                                <i class="bi bi-person-circle" style="font-size: 120px; color: #003366;"></i>
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm" onclick="alert('Funcionalidade em desenvolvimento')">
                                            <i class="bi bi-camera"></i> Alterar Foto
                                        </button>
                                    </div>
                                    <div class="col-md-9">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="nome_user" class="form-label">Nome Completo</label>
                                                    <input type="text" class="form-control" id="nome_user" name="nome_user"
                                                           value="<?php echo htmlspecialchars($usuario['nome_user']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="email_user" class="form-label">E-mail</label>
                                                    <input type="email" class="form-control" id="email_user" name="email_user"
                                                           value="<?php echo htmlspecialchars($usuario['email_user']); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="cpf_user" class="form-label">CPF</label>
                                                    <input type="text" class="form-control" id="cpf_user" name="cpf_user"
                                                           value="<?php echo htmlspecialchars($usuario['cpf_user']); ?>" readonly>
                                                    <small class="text-muted">CPF não pode ser alterado</small>
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="telefone_user" class="form-label">Telefone</label>
                                                    <input type="text" class="form-control" id="telefone_user" name="telefone_user"
                                                           value="<?php echo htmlspecialchars($usuario['telefone_user'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-4">
                                                <div class="mb-3">
                                                    <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
                                                    <input type="text" class="form-control" id="tipo_usuario" name="tipo_usuario"
                                                           value="<?php echo htmlspecialchars($usuario['tipo_usuario']); ?>" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="data_nascimento" class="form-label">Data de Nascimento</label>
                                                    <input type="date" class="form-control" id="data_nascimento" name="data_nascimento"
                                                           value="<?php echo htmlspecialchars($perfil['data_nascimento'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="mb-3">
                                                    <label for="endereco" class="form-label">Endereço</label>
                                                    <input type="text" class="form-control" id="endereco" name="endereco"
                                                           value="<?php echo htmlspecialchars($perfil['endereco'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary">
                                            <i class="bi bi-save"></i> Atualizar Dados
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Informações Profissionais (para professores) -->
                    <?php if ($usuario['tipo_usuario'] === 'Professor'): ?>
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-briefcase"></i> Informações Profissionais
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="atualizar_dados.php">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="especialidade" class="form-label">Especialidade</label>
                                            <input type="text" class="form-control" id="especialidade" name="especialidade"
                                                   value="<?php echo htmlspecialchars($perfil['especialidade'] ?? ''); ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="formacao" class="form-label">Formação Acadêmica</label>
                                            <input type="text" class="form-control" id="formacao" name="formacao"
                                                   value="<?php echo htmlspecialchars($perfil['formacao'] ?? ''); ?>">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="linkedin" class="form-label">LinkedIn</label>
                                            <input type="url" class="form-control" id="linkedin" name="linkedin"
                                                   value="<?php echo htmlspecialchars($perfil['linkedin'] ?? ''); ?>"
                                                   placeholder="https://linkedin.com/in/seuperfil">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="site_pessoal" class="form-label">Site Pessoal</label>
                                            <input type="url" class="form-control" id="site_pessoal" name="site_pessoal"
                                                   value="<?php echo htmlspecialchars($perfil['site_pessoal'] ?? ''); ?>"
                                                   placeholder="https://seusite.com">
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="biografia" class="form-label">Biografia</label>
                                    <textarea class="form-control" id="biografia" name="biografia" rows="4"><?php echo htmlspecialchars($perfil['biografia'] ?? ''); ?></textarea>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Atualizar Informações Profissionais
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Segurança da Conta -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-shield-lock"></i> Segurança da Conta
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="alterar_senha.php">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="senha_atual" class="form-label">Senha Atual</label>
                                            <input type="password" class="form-control" id="senha_atual" name="senha_atual" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="nova_senha" class="form-label">Nova Senha</label>
                                            <input type="password" class="form-control" id="nova_senha" name="nova_senha" minlength="6" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                                            <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" minlength="6" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="alert alert-info">
                                    <i class="bi bi-info-circle"></i>
                                    <strong>Dica:</strong> Use senhas fortes com pelo menos 6 caracteres, incluindo letras, números e símbolos.
                                </div>

                                <button type="submit" class="btn btn-warning">
                                    <i class="bi bi-key"></i> Alterar Senha
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Estatísticas da Conta -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-graph-up"></i> Estatísticas da Conta
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-primary"><?php
                                            $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM usuarios WHERE data_criacao >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
                                            $stmt->execute();
                                            echo $stmt->get_result()->fetch_assoc()['total'];
                                        ?></h4>
                                        <small>Novos usuários (30 dias)</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-success"><?php
                                            $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM materias WHERE id_usuario_criador = ?");
                                            $stmt->bind_param("i", $_SESSION['id']);
                                            $stmt->execute();
                                            echo $stmt->get_result()->fetch_assoc()['total'];
                                        ?></h4>
                                        <small>Matérias criadas</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-info"><?php
                                            $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM eventos_calendario WHERE id_usuario_criador = ?");
                                            $stmt->bind_param("i", $_SESSION['id']);
                                            $stmt->execute();
                                            echo $stmt->get_result()->fetch_assoc()['total'];
                                        ?></h4>
                                        <small>Eventos criados</small>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="text-center">
                                        <h4 class="text-warning"><?php
                                            $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE id_usuario_destino = ? AND lida = 0");
                                            $stmt->bind_param("i", $_SESSION['id']);
                                            $stmt->execute();
                                            echo $stmt->get_result()->fetch_assoc()['total'];
                                        ?></h4>
                                        <small>Notificações não lidas</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Ações da Conta -->
                    <div class="card mt-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-gear"></i> Ações da Conta
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <button class="btn btn-outline-primary w-100 mb-2" onclick="exportarDados()">
                                        <i class="bi bi-download"></i> Exportar Meus Dados
                                    </button>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-outline-info w-100 mb-2" onclick="verLogs()">
                                        <i class="bi bi-list-ul"></i> Ver Meu Histórico
                                    </button>
                                </div>
                            </div>

                            <hr>

                            <div class="row">
                                <div class="col-12">
                                    <button class="btn btn-outline-danger" onclick="confirmarLogout()">
                                        <i class="bi bi-box-arrow-right"></i> Sair da Conta
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menu interativo
        document.querySelector('.btn-expandir').addEventListener('click', function() {
            document.querySelector('.menu-lateral').classList.toggle('expandir');
        });

        // Validar senhas
        document.querySelector('form[action="alterar_senha.php"]').addEventListener('submit', function(e) {
            const novaSenha = document.getElementById('nova_senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;

            if (novaSenha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não conferem!');
            }
        });

        // Funções de ação
        function exportarDados() {
            alert('Funcionalidade de exportação em desenvolvimento.');
        }

        function verLogs() {
            window.open('logs_atividades.php', '_blank');
        }

        function confirmarLogout() {
            if (confirm('Tem certeza que deseja sair do sistema?')) {
                window.location.href = '../parte-inicial/logout.php';
            }
        }
    </script>
</body>
</html>