<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

if (!in_array($_SESSION['tipo_usuario'], ['Administrador', 'Professor'])) {
    header('Location: ../parte-inicial/index.php?erro=acesso_negado');
    exit();
}

// VERIFICA CONEXÃO
if (!$conexao || $conexao->connect_error) {
    die("<div class='alert alert-danger text-center'><h3>Erro de conexão com o banco!</h3></div>");
}

// FUNÇÃO SEGURA PARA EXECUTAR QUERIES
function safeQuery($conexao, $sql, $types = "", $params = []) {
    $stmt = $conexao->prepare($sql);
    if (!$stmt) {
        error_log("Erro prepare: " . $conexao->error . " | SQL: " . $sql);
        return false;
    }
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// PROCESSAR FORMULÁRIOS
$mensagem = $tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {

    // CADASTRAR AULA (como evento tipo 'aula')
    if ($_POST['acao'] === 'cadastrar_aula') {
        $id_materia = (int)($_POST['id_materia'] ?? 0);
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $data_aula = $_POST['data_aula'] ?? '';
        $hora_inicio = $_POST['hora_inicio'] ?? '';
        $hora_fim = $_POST['hora_fim'] ?? '';
        $sala = trim($_POST['sala'] ?? '');
        $link_aula = trim($_POST['link_aula'] ?? '');
        $observacoes = trim($_POST['observacoes'] ?? '');

        // Variáveis obrigatórias por referência
        $criador = $_SESSION['id'];
        $tipo = 'aula';
        $status = 'planejado';
        $descricao_completa = $descricao;
        if ($link_aula) $descricao_completa .= "\nLink: $link_aula";
        if ($observacoes) $descricao_completa .= "\nObs: $observacoes";

        $sql = "INSERT INTO eventos_calendario 
                (id_usuario_criador, id_materia, titulo, descricao, tipo_evento, 
                 data_inicio, hora_inicio, data_fim, hora_fim, local_evento, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = $conexao->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("iisssssssss", 
                $criador, $id_materia, $titulo, $descricao_completa, $tipo,
                $data_aula, $hora_inicio, $data_aula, $hora_fim, $sala, $status
            );
            $sucesso = $stmt->execute();
            $stmt->close();

            $_SESSION['mensagem'] = $sucesso ? "Aula cadastrada com sucesso!" : "Erro ao cadastrar aula.";
            $_SESSION['tipo_mensagem'] = $sucesso ? "success" : "danger";
        } else {
            $_SESSION['mensagem'] = "Erro no banco de dados.";
            $_SESSION['tipo_mensagem'] = "danger";
        }
        header("Location: calendario.php");
        exit();
    }

    // CADASTRAR EVENTO GERAL
    if ($_POST['acao'] === 'cadastrar_evento') {
        $titulo = trim($_POST['titulo'] ?? '');
        $descricao = trim($_POST['descricao'] ?? '');
        $data_inicio = $_POST['data_inicio'] ?? '';
        $data_fim = $_POST['data_fim'] ?? '';
        $tipo_evento = $_POST['tipo_evento'] ?? 'evento';

        $criador = $_SESSION['id'];
        $sql = "INSERT INTO eventos_calendario (id_usuario_criador, titulo, descricao, tipo_evento, data_inicio, data_fim) 
                VALUES (?, ?, ?, ?, ?, ?)";
        
        $stmt = $conexao->prepare($sql);
        if ($stmt) {
            $stmt->bind_param("isssss", $criador, $titulo, $descricao, $tipo_evento, $data_inicio, $data_fim);
            $sucesso = $stmt->execute();
            $stmt->close();
            $_SESSION['mensagem'] = $sucesso ? "Evento cadastrado!" : "Erro ao cadastrar evento.";
            $_SESSION['tipo_mensagem'] = $sucesso ? "success" : "danger";
        }
        header("Location: calendario.php");
        exit();
    }
}

// DADOS PARA SELECTS
$materias = $conexao->query("SELECT id, nome_materia FROM materias ORDER BY nome_materia")->fetch_all(MYSQLI_ASSOC);

$professores = ($_SESSION['tipo_usuario'] === 'Administrador')
    ? $conexao->query("SELECT id, nome_user FROM usuarios WHERE tipo_usuario = 'Professor' ORDER BY nome_user")->fetch_all(MYSQLI_ASSOC)
    : [['id' => $_SESSION['id'], 'nome_user' => $_SESSION['nome_user'] ?? 'Você']];

// MÊS E ANO ATUAL
$mes = (int)($_GET['mes'] ?? date('n'));
$ano = (int)($_GET['ano'] ?? date('Y'));

// CARREGAR AULAS DO MÊS
$aulas_result = safeQuery($conexao, "
    SELECT e.*, m.nome_materia, u.nome_user AS professor 
    FROM eventos_calendario e 
    LEFT JOIN materias m ON e.id_materia = m.id 
    LEFT JOIN usuarios u ON e.id_usuario_criador = u.id 
    WHERE e.tipo_evento = 'aula' 
      AND MONTH(e.data_inicio) = ? AND YEAR(e.data_inicio) = ?
    ORDER BY e.data_inicio, e.hora_inicio
", "ii", [$mes, $ano]);

$aulas = $aulas_result ? $aulas_result->fetch_all(MYSQLI_ASSOC) : [];

// CARREGAR EVENTOS GERAIS
$eventos_result = safeQuery($conexao, "
    SELECT e.*, u.nome_user AS criador 
    FROM eventos_calendario e 
    LEFT JOIN usuarios u ON e.id_usuario_criador = u.id 
    WHERE e.tipo_evento != 'aula' 
      AND MONTH(e.data_inicio) = ? AND YEAR(e.data_inicio) = ?
    ORDER BY e.data_inicio
", "ii", [$mes, $ano]);

$eventos = $eventos_result ? $eventos_result->fetch_all(MYSQLI_ASSOC) : [];

// MENSAGEM
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo_mensagem = $_SESSION['tipo_mensagem'];
    unset($_SESSION['mensagem'], $_SESSION['tipo_mensagem']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário Acadêmico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/menu-padrao.css">
    <style>
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 2px; background: #dee2e6; }
        .calendar-day { background: white; min-height: 120px; padding: 8px; font-size: 0.85rem; }
        .calendar-day.today { background: #e3f2fd; border: 2px solid #1976d2; }
        .event-item { 
            padding: 4px 6px; margin: 2px 0; border-radius: 6px; 
            font-size: 0.75rem; display: block; white-space: nowrap; 
            overflow: hidden; text-overflow: ellipsis; cursor: pointer;
        }
        .event-aula { background: #d4edda; color: #155724; border-left: 4px solid #28a745; }
        .event-evento { background: #d1ecf1; color: #0c5460; border-left: 4px solid #17a2b8; }
        .event-prova { background: #f8d7da; color: #721c24; border-left: 4px solid #dc3545; }
        .form-section { background: linear-gradient(135deg, #667eea, #764ba2); color: white; border-radius: 15px; padding: 25px; }
    </style>
</head>
<body>

    <!-- MENU LATERAL -->
    <nav class="menu-lateral">
        <div class="btn-expandir" id="btn-expan"><i class="bi bi-list"></i></div>
        <ul>
            <li class="menu-botoes"><a href="home.php"><i class="bi bi-house"></i><span class="txt-link">Home</span></a></li>
            <li class="menu-botoes ativo"><a href="calendario.php"><i class="bi bi-calendar"></i><span class="txt-link">Calendário</span></a></li>
            <li class="menu-botoes"><a href="cadastros_int.php"><i class="bi bi-people"></i><span class="txt-link">Cadastros</span></a></li>
            <?php if ($_SESSION['tipo_usuario'] === 'Administrador'): ?>
            <li class="menu-botoes"><a href="professores.php"><i class="bi bi-person-workspace"></i><span class="txt-link">Professores</span></a></li>
            <li class="menu-botoes"><a href="materias.php"><i class="bi bi-book"></i><span class="txt-link">Matérias</span></a></li>
            <?php endif; ?>
            <li class="menu-botoes"><a href="notificacoes.php"><i class="bi bi-bell"></i><span class="txt-link">Notificações</span></a></li>
            <li class="menu-botoes"><a href="conta.php"><i class="bi bi-person-circle"></i><span class="txt-link">Conta</span></a></li>
            <li class="menu-botoes"><a href="configuracao.php"><i class="bi bi-gear"></i><span class="txt-link">Configuração</span></a></li>
        </ul>
    </nav>

    <main class="conteudo">
        <div class="container-fluid">
            <h1 class="mb-4"><i class="bi bi-calendar"></i> Calendário Acadêmico</h1>

            <!-- MENSAGEM -->
            <?php if ($mensagem): ?>
                <div class="alert alert-<?= $tipo_mensagem ?> alert-dismissible fade show">
                    <?= htmlspecialchars($mensagem) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- ABAS -->
            <ul class="nav nav-tabs mb-4">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#visualizar"><i class="bi bi-calendar-month"></i> Visualizar</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#aula"><i class="bi bi-plus-circle"></i> Nova Aula</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#evento"><i class="bi bi-calendar-plus"></i> Novo Evento</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#listar"><i class="bi bi-list-ul"></i> Listar Aulas</button></li>
            </ul>

            <div class="tab-content">
                <!-- VISUALIZAR -->
                <div class="tab-pane fade show active" id="visualizar">
                    <div class="card">
                        <div class="card-header text-center">
                            <div class="d-flex justify-content-between align-items-center">
                                <a href="?mes=<?= $mes == 1 ? 12 : $mes-1 ?>&ano=<?= $mes == 1 ? $ano-1 : $ano ?>" class="btn btn-outline-primary"><i class="bi bi-chevron-left"></i></a>
                                <h4 class="mb-0">
                                    <?= ['','Janeiro','Fevereiro','Março','Abril','Maio','Junho','Julho','Agosto','Setembro','Outubro','Novembro','Dezembro'][$mes] ?> <?= $ano ?>
                                </h4>
                                <a href="?mes=<?= $mes == 12 ? 1 : $mes+1 ?>&ano=<?= $mes == 12 ? $ano+1 : $ano ?>" class="btn btn-outline-primary"><i class="bi bi-chevron-right"></i></a>
                            </div>
                        </div>
                        <div class="card-body p-3">
                            <div class="calendar-grid">
                                <?php
                                $primeiro_dia = mktime(0,0,0,$mes,1,$ano);
                                $dia_semana = date('w', $primeiro_dia);
                                $total_dias = date('t', $primeiro_dia);
                                $hoje = date('Y-m-d');

                                // Cabeçalho
                                foreach (['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'] as $dia_nome) {
                                    echo "<div class='bg-light p-2 text-center fw-bold'>$dia_nome</div>";
                                }

                                // Dias vazios
                                for ($i = 0; $i < $dia_semana; $i++) {
                                    echo "<div class='calendar-day bg-secondary-subtle'></div>";
                                }

                                // Dias do mês
                                for ($dia = 1; $dia <= $total_dias; $dia++) {
                                    $data_atual = sprintf("%04d-%02d-%02d", $ano, $mes, $dia);
                                    $eh_hoje = $data_atual === $hoje;

                                    echo "<div class='calendar-day " . ($eh_hoje ? 'today' : '') . "'>";
                                    echo "<div class='fw-bold text-primary'>$dia</div>";

                                    // Eventos do dia
                                    $todos_eventos = array_merge($aulas, $eventos);
                                    foreach ($todos_eventos as $ev) {
                                        if (date('Y-m-d', strtotime($ev['data_inicio'])) === $data_atual) {
                                            $classe = $ev['tipo_evento'] === 'aula' ? 'event-aula' : 
                                                     ($ev['tipo_evento'] === 'prova' ? 'event-prova' : 'event-evento');
                                            $icone = $ev['tipo_evento'] === 'aula' ? 'book' : 'calendar-event';
                                            echo "<div class='event-item $classe' title='" . htmlspecialchars($ev['titulo']) . "'>";
                                            echo "<i class='bi bi-$icone'></i> " . htmlspecialchars(substr($ev['titulo'], 0, 18));
                                            echo "</div>";
                                        }
                                    }
                                    echo "</div>";
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- CADASTRAR AULA -->
                <div class="tab-pane fade" id="aula">
                    <div class="form-section">
                        <h3><i class="bi bi-plus-circle"></i> Cadastrar Nova Aula</h3>
                        <form method="POST">
                            <input type="hidden" name="acao" value="cadastrar_aula">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Matéria</label>
                                    <select name="id_materia" class="form-select" required>
                                        <option value="">Selecione...</option>
                                        <?php foreach ($materias as $m): ?>
                                            <option value="<?= $m['id'] ?>"><?= htmlspecialchars($m['nome_materia']) ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Título da Aula</label>
                                    <input type="text" name="titulo" class="form-control" placeholder="Ex: Introdução à Eletrônica" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Data</label>
                                    <input type="date" name="data_aula" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Hora Início</label>
                                    <input type="time" name="hora_inicio" class="form-control" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Hora Fim</label>
                                    <input type="time" name="hora_fim" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Sala</label>
                                    <input type="text" name="sala" class="form-control" placeholder="Ex: Lab 203">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Link (se online)</label>
                                    <input type="url" name="link_aula" class="form-control" placeholder="https://meet.google.com/...">
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Descrição</label>
                                    <textarea name="descricao" class="form-control" rows="3" placeholder="Conteúdo da aula..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-light btn-lg"><i class="bi bi-save"></i> Cadastrar Aula</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- CADASTRAR EVENTO -->
                <div class="tab-pane fade" id="evento">
                    <div class="form-section">
                        <h3><i class="bi bi-calendar-plus"></i> Cadastrar Evento</h3>
                        <form method="POST">
                            <input type="hidden" name="acao" value="cadastrar_evento">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <input type="text" name="titulo" class="form-control" placeholder="Título do evento" required>
                                </div>
                                <div class="col-md-6">
                                    <select name="tipo_evento" class="form-select">
                                        <option value="evento">Evento Geral</option>
                                        <option value="prova">Prova</option>
                                        <option value="feriado">Feriado</option>
                                        <option value="reuniao">Reunião</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <input type="datetime-local" name="data_inicio" class="form-control" required>
                                </div>
                                <div class="col-md-6">
                                    <input type="datetime-local" name="data_fim" class="form-control" required>
                                </div>
                                <div class="col-12">
                                    <textarea name="descricao" class="form-control" rows="4" placeholder="Descrição do evento..."></textarea>
                                </div>
                                <div class="col-12">
                                    <button type="submit" class="btn btn-light btn-lg"><i class="bi bi-save"></i> Cadastrar Evento</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- LISTAR AULAS -->
                <div class="tab-pane fade" id="listar">
                    <div class="card">
                        <div class="card-body">
                            <h4>Aulas do Mês</h4>
                            <?php if (!empty($aulas)): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Data</th>
                                                <th>Hora</th>
                                                <th>Título</th>
                                                <th>Matéria</th>
                                                <th>Professor</th>
                                                <th>Sala</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($aulas as $aula): ?>
                                            <tr>
                                                <td><?= date('d/m/Y', strtotime($aula['data_inicio'])) ?></td>
                                                <td><?= substr($aula['hora_inicio'], 0, 5) ?> - <?= substr($aula['hora_fim'], 0, 5) ?></td>
                                                <td><?= htmlspecialchars($aula['titulo']) ?></td>
                                                <td><?= htmlspecialchars($aula['nome_materia'] ?? '-') ?></td>
                                                <td><?= htmlspecialchars($aula['professor'] ?? 'Não informado') ?></td>
                                                <td><?= htmlspecialchars($aula['local_evento'] ?? '-') ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <p class="text-center text-muted">Nenhuma aula cadastrada neste mês.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menu lateral
        document.getElementById('btn-expan').addEventListener('click', () => {
            document.querySelector('.menu-lateral').classList.toggle('expandir');
        });

        // Validação de horários
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const horaInicio = this.querySelector('[name="hora_inicio"]')?.value;
                const horaFim = this.querySelector('[name="hora_fim"]')?.value;
                if (horaInicio && horaFim && horaFim <= horaInicio) {
                    e.preventDefault();
                    alert('A hora de término deve ser após a hora de início!');
                }
            });
        });
    </script>
</body>
</html>