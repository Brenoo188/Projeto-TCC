<?php
session_start();
include_once('../conexao.php');
include_once('../funcoes_notificacoes.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

// Apenas administradores podem ver todas as movimentações
$ver_todas_movimentacoes = ($_SESSION['tipo_usuario'] === 'Administrador');

$id_usuario = $_SESSION['id'];
$mensagem = '';
$tipo_mensagem = '';

// Processar ações
if (isset($_GET['acao'])) {
    switch ($_GET['acao']) {
        case 'marcar_lida':
            if (isset($_GET['id'])) {
                $id_notificacao = $_GET['id'];
                if (marcarNotificacaoComoLida($conexao, $id_notificacao, $id_usuario)) {
                    $mensagem = 'Notificação marcada como lida.';
                    $tipo_mensagem = 'success';
                }
            }
            break;

        case 'marcar_todas_lidas':
            if (marcarTodasComoLidas($conexao, $id_usuario)) {
                $mensagem = 'Todas as notificações foram marcadas como lidas.';
                $tipo_mensagem = 'success';
            }
            break;

        case 'excluir':
            if (isset($_GET['id'])) {
                $id_notificacao = $_GET['id'];
                if (excluirNotificacao($conexao, $id_notificacao, $id_usuario)) {
                    $mensagem = 'Notificação excluída com sucesso.';
                    $tipo_mensagem = 'success';
                }
            }
            break;

        case 'criar':
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $id_destino = $_POST['id_usuario_destino'] ?? 0;
                $titulo = $_POST['titulo'] ?? '';
                $mensagem_texto = $_POST['mensagem'] ?? '';
                $tipo = $_POST['tipo'] ?? 'info';

                if (criarNotificacao($conexao, $id_destino, $titulo, $mensagem_texto, $tipo)) {
                    $mensagem = 'Notificação enviada com sucesso!';
                    $tipo_mensagem = 'success';
                } else {
                    $mensagem = 'Erro ao enviar notificação.';
                    $tipo_mensagem = 'danger';
                }
            }
            break;
    }
}

// Parâmetros de filtro
$limite = $_GET['limite'] ?? 50;
$tipo_filtro = $_GET['tipo_filtro'] ?? 'todos';
$periodo_filtro = $_GET['periodo_filtro'] ?? 'todos';
$tabela_filtro = $_GET['tabela_filtro'] ?? 'todas';

// Obter dados
if ($ver_todas_movimentacoes && isset($_GET['movimentacoes'])) {
    // Administrador vendo todas as movimentações do sistema
    $movimentacoes = obterMovimentacoesSistema($conexao, $limite, $tipo_filtro, $tabela_filtro);
    $titulo_pagina = "Todas as Movimentações do Sistema";
} else {
    // Notificações do usuário
    $notificacoes_nao_lidas = obterNotificacoesNaoLidas($conexao, $id_usuario, 10);
    $todas_notificacoes = obterTodasNotificacoes($conexao, $id_usuario, $limite, $tipo_filtro, $periodo_filtro);
    $total_nao_lidas = contarNotificacoesNaoLidas($conexao, $id_usuario);
    $titulo_pagina = "Minhas Notificações";
}

// Obter usuários para envio de notificações
$usuarios = $conexao->query("SELECT id, nome_user, tipo_usuario FROM usuarios ORDER BY nome_user");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - Sistema Acadêmico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/menu-padrao.css">
    <style>
        .notificacao-card {
            border-left: 4px solid #dee2e6;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }

        .notificacao-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .notificacao-nao-lida {
            border-left-color: #007bff;
            background-color: #f8f9ff;
        }

        .notificacao-info { border-left-color: #17a2b8; }
        .notificacao-success { border-left-color: #28a745; }
        .notificacao-warning { border-left-color: #ffc107; }
        .notificacao-danger { border-left-color: #dc3545; }

        .badge-tipo {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .filtro-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
        }

        .movimentacao-item {
            border-radius: 10px;
            margin-bottom: 10px;
            padding: 15px;
            background: white;
            border-left: 4px solid #667eea;
            transition: all 0.3s ease;
        }

        .movimentacao-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .estatistica-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .estatistica-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .estatistica-card .numero {
            font-size: 2rem;
            font-weight: bold;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
    </style>
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
                    <span class="txt-link">Home</span>
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

   
            <li class="menu-botoes ativo">
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

            <li class="menu-botoes">
                <a href="configuracao.php">
                    <span class="icon"><i class="bi bi-gear"></i></span>
                    <span class="txt-link">Configuração</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="conteudo">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">
                        <i class="bi bi-bell"></i> <?php echo $titulo_pagina; ?>
                    </h1>

                    <!-- Mensagens -->
                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                            <?php echo $mensagem; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if ($ver_todas_movimentacoes): ?>
                        <!-- Abas para Administradores -->
                        <ul class="nav nav-tabs mb-4" id="abasNotificacoes" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="minhas-tab" data-bs-toggle="tab" data-bs-target="#minhas" type="button" role="tab">
                                    <i class="bi bi-person"></i> Minhas Notificações
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="movimentacoes-tab" data-bs-toggle="tab" data-bs-target="#movimentacoes" type="button" role="tab">
                                    <i class="bi bi-activity"></i> Todas as Movimentações
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="criar-tab" data-bs-toggle="tab" data-bs-target="#criar" type="button" role="tab">
                                    <i class="bi bi-plus-circle"></i> Criar Notificação
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="abasNotificacoesContent">
                            <!-- Aba Minhas Notificações -->
                            <div class="tab-pane fade show active" id="minhas" role="tabpanel">
                                <?php include('conteudo_notificacoes_usuario.php'); ?>
                            </div>

                            <!-- Aba Todas as Movimentações -->
                            <div class="tab-pane fade" id="movimentacoes" role="tabpanel">
                                <?php include('conteudo_movimentacoes_sistema.php'); ?>
                            </div>

                            <!-- Aba Criar Notificação -->
                            <div class="tab-pane fade" id="criar" role="tabpanel">
                                <?php include('conteudo_criar_notificacao.php'); ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- View para usuários não administradores -->
                        <?php include('conteudo_notificacoes_usuario.php'); ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Javascript/js-telaadm/menu.js"></script>
    <script>
        // Auto-refresh das notificações a cada 30 segundos
        setInterval(function() {
            location.reload();
        }, 30000);

        // Marcar notificação como lida ao clicar
        document.querySelectorAll('.notificacao-card').forEach(function(card) {
            card.addEventListener('click', function() {
                const idNotificacao = this.dataset.id;
                const lida = this.dataset.lida;

                if (lida === '0') {
                    window.location.href = 'notificacoes.php?acao=marcar_lida&id=' + idNotificacao;
                }
            });
        });

        // Filtros
        document.querySelectorAll('.filtro-select').forEach(function(select) {
            select.addEventListener('change', function() {
                const params = new URLSearchParams(window.location.search);
                params.set(this.name, this.value);
                window.location.href = 'notificacoes.php?' + params.toString();
            });
        });
    </script>
</body>
</html>