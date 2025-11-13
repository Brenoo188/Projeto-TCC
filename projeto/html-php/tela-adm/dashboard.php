<?php
session_start();
include_once('../conexao.php');
include_once('../funcoes_logs.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

if ($_SESSION['tipo_usuario'] !== 'Administrador') {
    header('Location: ../parte-inicial/index.php?erro=acesso_negado');
    exit();
}

// Obter estatísticas do sistema
function obterEstatisticas($conexao) {
    $estatisticas = [];

    // Total de usuários
    $result = $conexao->query("SELECT COUNT(*) as total FROM usuarios");
    $estatisticas['total_usuarios'] = $result->fetch_assoc()['total'];

    // Usuários por tipo
    $result = $conexao->query("SELECT tipo_usuario, COUNT(*) as total FROM usuarios GROUP BY tipo_usuario");
    while ($row = $result->fetch_assoc()) {
        $estatisticas['usuarios_por_tipo'][$row['tipo_usuario']] = $row['total'];
    }

    // Total de matérias
    $result = $conexao->query("SELECT COUNT(*) as total FROM materias");
    $estatisticas['total_materias'] = $result->fetch_assoc()['total'];

    // Eventos este mês
    $stmt = $conexao->prepare("
        SELECT COUNT(*) as total FROM eventos_calendario
        WHERE MONTH(data_inicio) = MONTH(CURRENT_DATE())
        AND YEAR(data_inicio) = YEAR(CURRENT_DATE())
    ");
    $stmt->execute();
    $estatisticas['eventos_este_mes'] = $stmt->get_result()->fetch_assoc()['total'];

    // Logs hoje
    $stmt = $conexao->prepare("
        SELECT COUNT(*) as total FROM logs_atividades
        WHERE DATE(data_hora) = CURDATE()
    ");
    $stmt->execute();
    $estatisticas['logs_hoje'] = $stmt->get_result()->fetch_assoc()['total'];

    // Notificações não lidas
    $stmt = $conexao->prepare("
        SELECT COUNT(*) as total FROM notificacoes
        WHERE lida = 0
    ");
    $stmt->execute();
    $estatisticas['notificacoes_nao_lidas'] = $stmt->get_result()->fetch_assoc()['total'];

    // Últimos usuários cadastrados
    $result = $conexao->query("
        SELECT nome_user, tipo_usuario, data_criacao FROM usuarios
        ORDER BY id DESC LIMIT 5
    ");
    $estatisticas['ultimos_usuarios'] = [];
    while ($row = $result->fetch_assoc()) {
        $estatisticas['ultimos_usuarios'][] = $row;
    }

    // Atividades recentes
    $result = $conexao->query("
        SELECT la.*, u.nome_user FROM logs_atividades la
        JOIN usuarios u ON la.id_usuario = u.id
        ORDER BY la.data_hora DESC LIMIT 10
    ");
    $estatisticas['atividades_recentes'] = [];
    while ($row = $result->fetch_assoc()) {
        $estatisticas['atividades_recentes'][] = $row;
    }

    return $estatisticas;
}

$estatisticas = obterEstatisticas($conexao);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Administrativo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/dashboard.css">
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
                <a href="calendario-eventos.php">
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

  
            <li class="menu-botoes">
                <a href="configuracao.php">
                    <span class="icon"><i class="bi bi-gear"></i></span>
                    <span class="txt-link">Configuração</span>
                </a>
            </li>

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
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="conteudo-dashboard">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">
                        <i class="bi bi-speedometer2"></i> Dashboard Administrativo
                    </h1>

                    <!-- Cards de Estatísticas -->
                    <div class="row mb-4">
                        <div class="col-md-3 mb-3">
                            <div class="card estatistica-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-1"><?php echo $estatisticas['total_usuarios']; ?></h3>
                                            <p class="text-muted mb-0">Total de Usuários</p>
                                        </div>
                                        <div class="estatistica-icon usuario">
                                            <i class="bi bi-people"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card estatistica-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-1"><?php echo $estatisticas['total_materias']; ?></h3>
                                            <p class="text-muted mb-0">Matérias</p>
                                        </div>
                                        <div class="estatistica-icon materia">
                                            <i class="bi bi-book"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card estatistica-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-1"><?php echo $estatisticas['eventos_este_mes']; ?></h3>
                                            <p class="text-muted mb-0">Eventos este mês</p>
                                        </div>
                                        <div class="estatistica-icon evento">
                                            <i class="bi bi-calendar-event"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-3 mb-3">
                            <div class="card estatistica-card">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <h3 class="mb-1"><?php echo $estatisticas['logs_hoje']; ?></h3>
                                            <p class="text-muted mb-0">Atividades hoje</p>
                                        </div>
                                        <div class="estatistica-icon atividade">
                                            <i class="bi bi-activity"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Gráfico de Usuários por Tipo -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-pie-chart"></i> Usuários por Tipo</h5>
                                </div>
                                <div class="card-body">
                                    <canvas id="graficoUsuariosTipo" width="400" height="300"></canvas>
                                </div>
                            </div>
                        </div>

                        <!-- Atividades Recentes -->
                        <div class="col-md-6 mb-4">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-clock-history"></i> Atividades Recentes</h5>
                                </div>
                                <div class="card-body">
                                    <div class="atividade-lista">
                                        <?php if (empty($estatisticas['atividades_recentes'])): ?>
                                            <p class="text-muted">Nenhuma atividade recente.</p>
                                        <?php else: ?>
                                            <?php foreach ($estatisticas['atividades_recentes'] as $atividade): ?>
                                            <div class="atividade-item">
                                                <div class="d-flex justify-content-between align-items-start">
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($atividade['nome_user']); ?></strong>
                                                        <span class="text-muted">- <?php echo htmlspecialchars($atividade['acao']); ?></span>
                                                        <?php if ($atividade['descricao']): ?>
                                                            <br><small class="text-muted"><?php echo htmlspecialchars($atividade['descricao']); ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                    <small class="text-muted">
                                                        <?php echo date('H:i', strtotime($atividade['data_hora'])); ?>
                                                    </small>
                                                </div>
                                            </div>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Últimos Usuários Cadastrados -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0"><i class="bi bi-person-plus"></i> Últimos Usuários Cadastrados</h5>
                                </div>
                                <div class="card-body">
                                    <?php if (empty($estatisticas['ultimos_usuarios'])): ?>
                                        <p class="text-muted">Nenhum usuário cadastrado ainda.</p>
                                    <?php else: ?>
                                        <div class="table-responsive">
                                            <table class="table table-hover">
                                                <thead>
                                                    <tr>
                                                        <th>Nome</th>
                                                        <th>Tipo</th>
                                                        <th>Data de Cadastro</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php foreach ($estatisticas['ultimos_usuarios'] as $usuario): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($usuario['nome_user']); ?></td>
                                                        <td>
                                                            <span class="badge badge-<?php echo $usuario['tipo_usuario'] === 'Administrador' ? 'danger' : 'primary'; ?>">
                                                                <?php echo htmlspecialchars($usuario['tipo_usuario']); ?>
                                                            </span>
                                                        </td>
                                                        <td><?php echo date('d/m/Y H:i', strtotime($usuario['data_criacao'])); ?></td>
                                                    </tr>
                                                    <?php endforeach; ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script src="../../Javascript/js-telaadm/menu.js"></script>
    <script>
        // Dados do PHP para JavaScript
        const estatisticas = <?php echo json_encode($estatisticas); ?>;

        // Gráfico de Usuários por Tipo
        const ctxUsuariosTipo = document.getElementById('graficoUsuariosTipo').getContext('2d');
        new Chart(ctxUsuariosTipo, {
            type: 'doughnut',
            data: {
                labels: ['Administradores', 'Professores'],
                datasets: [{
                    data: [
                        estatisticas.usuarios_por_tipo['Administrador'] || 0,
                        estatisticas.usuarios_por_tipo['Professor'] || 0
                    ],
                    backgroundColor: [
                        '#dc3545',
                        '#007bff'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((context.parsed / total) * 100).toFixed(1);
                                return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                            }
                        }
                    }
                }
            }
        });

        // Auto-refresh a cada 30 segundos
        setTimeout(() => {
            location.reload();
        }, 30000);
    </script>
</body>
</html>