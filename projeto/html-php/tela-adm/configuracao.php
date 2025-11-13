<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

// Permite acesso tanto para administradores quanto professores
if ($_SESSION['tipo_usuario'] !== 'Administrador' && $_SESSION['tipo_usuario'] !== 'Professor') {
    header('Location: ../parte-inicial/index.php?erro=acesso_negado');
    exit();
}

// Buscar configurações do sistema
$configuracoes = $conexao->query("SELECT * FROM configuracoes_sistema ORDER BY categoria");

// Processar atualização de configurações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    foreach ($_POST as $chave => $valor) {
        if (strpos($chave, 'config_') === 0) {
            $config_id = str_replace('config_', '', $chave);
            $stmt = $conexao->prepare("UPDATE configuracoes_sistema SET valor = ?, atualizado_em = NOW(), atualizado_por = ? WHERE id = ?");
            $stmt->bind_param("sii", $valor, $_SESSION['id'], $config_id);
            $stmt->execute();
        }
    }
    $_SESSION['mensagem'] = "Configurações atualizadas com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";
    header("Location: configuracao.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - Sistema Acadêmico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/menu-padrao.css">
    <link rel="stylesheet" href="../../css/css_telaadm/configuracao.css">
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

            <li class="menu-botoes">
                <a href="cadastros_int.php">
                    <span class="icon"><i class="bi bi-people"></i></span>
                    <span class="txt-link">Cadastros</span>
                </a>
            </li>

            <?php if ($_SESSION['tipo_usuario'] === 'Administrador'): ?>
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

            <li class="menu-botoes">
                <a href="conta.php">
                    <span class="icon"><i class="bi bi-person-circle"></i></span>
                    <span class="txt-link">Conta</span>
                </a>
            </li>

            <li class="menu-botoes ativo">
                <a href="configuracao.php">
                    <span class="icon"><i class="bi bi-gear"></i></span>
                    <span class="txt-link">Configuração</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="conteudo-configuracao">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">
                        <i class="bi bi-gear"></i> Configurações do Sistema
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

                    <!-- Configurações Gerais -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-gear-fill"></i> Configurações Gerais
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="configuracao.php">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="config_1" class="form-label">Nome do Sistema</label>
                                            <input type="text" class="form-control" id="config_1" name="config_1"
                                                   value="Sistema Acadêmico TCC" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="config_2" class="form-label">Versão do Sistema</label>
                                            <input type="text" class="form-control" id="config_2" name="config_2"
                                                   value="1.0.0" readonly>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="config_3" class="form-label">E-mail de Contato</label>
                                            <input type="email" class="form-control" id="config_3" name="config_3"
                                                   value="admin@sistema.com" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="config_4" class="form-label">Tempo de Sessão (minutos)</label>
                                            <input type="number" class="form-control" id="config_4" name="config_4"
                                                   value="120" min="30" max="480" required>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-save"></i> Salvar Configurações
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Preferências do Usuário -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person-gear"></i> Preferências do Usuário
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="atualizar_preferencias.php">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="tema" class="form-label">Tema da Interface</label>
                                            <select class="form-select" id="tema" name="tema">
                                                <option value="claro" selected>Claro</option>
                                                <option value="escuro">Escuro</option>
                                                <option value="automatico">Automático</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="idioma" class="form-label">Idioma</label>
                                            <select class="form-select" id="idioma" name="idioma">
                                                <option value="pt-br" selected>Português (Brasil)</option>
                                                <option value="en">Inglês</option>
                                                <option value="es">Espanhol</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="notificar_email" name="notificar_email" checked>
                                                <label class="form-check-label" for="notificar_email">
                                                    Receber notificações por e-mail
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <div class="form-check form-switch">
                                                <input class="form-check-input" type="checkbox" id="som_notificacoes" name="som_notificacoes">
                                                <label class="form-check-label" for="som_notificacoes">
                                                    Ativar sons de notificação
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-success">
                                    <i class="bi bi-save"></i> Salvar Preferências
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Informações do Sistema -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-info-circle"></i> Informações do Sistema
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Versão PHP:</strong></td>
                                            <td><?php echo PHP_VERSION; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Versão MySQL:</strong></td>
                                            <td><?php echo $conexao->server_info; ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Data/Hora Servidor:</strong></td>
                                            <td><?php echo date('d/m/Y H:i:s'); ?></td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <td><strong>Usuário Logado:</strong></td>
                                            <td><?php echo htmlspecialchars($_SESSION['nome_user']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Tipo de Acesso:</strong></td>
                                            <td><?php echo htmlspecialchars($_SESSION['tipo_usuario']); ?></td>
                                        </tr>
                                        <tr>
                                            <td><strong>Último Login:</strong></td>
                                            <td><?php echo date('d/m/Y H:i', strtotime($_SESSION['ultimo_login'] ?? 'now')); ?></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Javascript/js-telaadm/menu.js"></script>
    <script>
        // Salvar automaticamente as preferências
        document.querySelectorAll('input[type="checkbox"], select').forEach(element => {
            element.addEventListener('change', function() {
                // Implementar salvamento automático se desejar
            });
        });
    </script>
</body>
</html>