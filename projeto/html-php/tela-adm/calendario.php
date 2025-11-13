<?php
session_start();
include_once('../conexao.php');
// include_once('../funcoes_notificacoes.php'); // Temporariamente desabilitado para corrigir erro

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

if ($_SESSION['tipo_usuario'] !== 'Administrador' && $_SESSION['tipo_usuario'] !== 'Professor') {
    header('Location: ../parte-inicial/index.php?erro=acesso_negado');
    exit();
}

// Processar ações do formulário
$mensagem = '';
$tipo_mensagem = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'cadastrar_aula':
                cadastrarAulaCalendario($conexao);
                break;
            case 'cadastrar_evento':
                cadastrarEvento($conexao);
                break;
            // case 'editar_aula':
            //     editarAulaCalendario($conexao);
            //     break;
            // case 'excluir_aula':
            //     excluirAulaCalendario($conexao);
            //     break;
        }
    }
}

function cadastrarAulaCalendario($conexao) {
    $id_materia = $_POST['id_materia'] ?? 0;
    $id_professor = $_POST['id_professor'] ?? $_SESSION['id'];
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $data_aula = $_POST['data_aula'] ?? '';
    $hora_inicio = $_POST['hora_inicio'] ?? '';
    $hora_fim = $_POST['hora_fim'] ?? '';
    $sala = $_POST['sala'] ?? '';
    $tipo_aula = $_POST['tipo_aula'] ?? 'teorica';
    $link_aula = $_POST['link_aula'] ?? '';
    $observacoes = $_POST['observacoes'] ?? '';

    // Inserir aula
    $stmt = $conexao->prepare("INSERT INTO aulas (id_materia, id_professor, titulo, descricao, data_aula, hora_inicio, hora_fim, sala, tipo_aula, link_aula, observacoes, status, criada_em, criada_por) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'planejada', NOW(), ?)");
    $stmt->bind_param("iisssssssssi", $id_materia, $id_professor, $titulo, $descricao, $data_aula, $hora_inicio, $hora_fim, $sala, $tipo_aula, $link_aula, $observacoes, $_SESSION['id']);

    if ($stmt->execute()) {
        // $id_aula = $conexao->insert_id;

        // Criar notificação
        // notificarNovaAula($conexao, $id_aula, $id_professor, $titulo);

        $_SESSION['mensagem'] = "Aula cadastrada com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "Erro ao cadastrar aula.";
        $_SESSION['tipo_mensagem'] = "danger";
    }

    header("Location: calendario.php");
    exit();
}

function cadastrarEvento($conexao) {
    $titulo = $_POST['titulo'] ?? '';
    $descricao = $_POST['descricao'] ?? '';
    $data_inicio = $_POST['data_inicio'] ?? '';
    $data_fim = $_POST['data_fim'] ?? '';
    $tipo_evento = $_POST['tipo_evento'] ?? 'geral';

    $stmt = $conexao->prepare("INSERT INTO eventos_calendario (id_usuario_criador, titulo, descricao, data_inicio, data_fim, tipo_evento) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $_SESSION['id'], $titulo, $descricao, $data_inicio, $data_fim, $tipo_evento);

    if ($stmt->execute()) {
        $_SESSION['mensagem'] = "Evento cadastrado com sucesso!";
        $_SESSION['tipo_mensagem'] = "success";
    } else {
        $_SESSION['mensagem'] = "Erro ao cadastrar evento.";
        $_SESSION['tipo_mensagem'] = "danger";
    }

    header("Location: calendario.php");
    exit();
}

// Obter dados para selects
$materias = $conexao->query("SELECT * FROM materias ORDER BY nome_materia");

if ($_SESSION['tipo_usuario'] === 'Administrador') {
    $professores = $conexao->query("SELECT id, nome_user FROM usuarios WHERE tipo_usuario = 'Professor' ORDER BY nome_user");
} else {
    $stmt = $conexao->prepare("SELECT id, nome_user FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $_SESSION['id']);
    $stmt->execute();
    $professores = $stmt->get_result();
}

// Obter eventos e aulas do mês atual
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$ano = isset($_GET['ano']) ? (int)$_GET['ano'] : date('Y');

// Aulas - Usando view_aulas_eventos para compatibilidade
if ($_SESSION['tipo_usuario'] === 'Administrador') {
    $aulas_query = $conexao->query("SELECT * FROM view_aulas_eventos WHERE MONTH(data_aula) = $mes AND YEAR(data_aula) = $ano ORDER BY data_aula, hora_inicio");
} else {
    $aulas_query = $conexao->prepare("SELECT * FROM view_aulas_eventos WHERE MONTH(data_aula) = ? AND YEAR(data_aula) = ? AND nome_professor = ?");
    $aulas_query->bind_param("iis", $mes, $ano, $_SESSION['nome_user']);
    $aulas_query->execute();
    $aulas_query = $aulas_query->get_result();
}
$aulas = $aulas_query;

// Eventos do calendário
if ($_SESSION['tipo_usuario'] === 'Administrador') {
    $eventos_query = $conexao->prepare("
        SELECT e.*, u.nome_user as criador
        FROM eventos_calendario e
        LEFT JOIN usuarios u ON e.id_usuario_criador = u.id
        WHERE MONTH(e.data_inicio) = ? AND YEAR(e.data_inicio) = ?
        ORDER BY e.data_inicio
    ");
    $eventos_query->bind_param("ii", $mes, $ano);
} else {
    $eventos_query = $conexao->prepare("
        SELECT e.* FROM eventos_calendario e
        WHERE MONTH(e.data_inicio) = ? AND YEAR(e.data_inicio) = ? AND e.id_usuario_criador = ?
        ORDER BY e.data_inicio
    ");
    $eventos_query->bind_param("iii", $mes, $ano, $_SESSION['id']);
}
$eventos_query->execute();
$eventos = $eventos_query->get_result();

// Mensagens da sessão
if (isset($_SESSION['mensagem'])) {
    $mensagem = $_SESSION['mensagem'];
    $tipo_mensagem = $_SESSION['tipo_mensagem'];
    unset($_SESSION['mensagem']);
    unset($_SESSION['tipo_mensagem']);
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendário Acadêmico - Sistema de Gestão</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/menu-padrao.css">
    <style>
        .calendar-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .calendar-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .calendar-grid {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 1px;
            background: #dee2e6;
            border: 1px solid #dee2e6;
        }

        .calendar-day-header {
            background: #f8f9fa;
            padding: 10px;
            text-align: center;
            font-weight: 600;
            color: #495057;
        }

        .calendar-day {
            background: white;
            min-height: 100px;
            padding: 8px;
            position: relative;
        }

        .calendar-day-number {
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
        }

        .calendar-day.other-month .calendar-day-number {
            color: #adb5bd;
        }

        .calendar-day.today {
            background: #e3f2fd;
        }

        .calendar-day.today .calendar-day-number {
            color: #1976d2;
            font-weight: 700;
        }

        .event-item {
            font-size: 0.75rem;
            padding: 2px 5px;
            margin: 2px 0;
            border-radius: 4px;
            cursor: pointer;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .event-aula {
            background: #d4edda;
            color: #155724;
            border-left: 3px solid #28a745;
        }

        .event-prova {
            background: #f8d7da;
            color: #721c24;
            border-left: 3px solid #dc3545;
        }

        .event-evento {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 3px solid #17a2b8;
        }

        .aula-card {
            border-left: 4px solid #28a745;
            transition: all 0.3s ease;
        }

        .aula-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }

        .status-badge {
            font-size: 0.75rem;
            padding: 4px 8px;
            border-radius: 12px;
        }

        .form-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 25px;
        }

        .form-section .form-control,
        .form-section .form-select {
            background: rgba(255,255,255,0.9);
            border: none;
        }

        .form-section .form-label {
            font-weight: 600;
            margin-bottom: 8px;
        }

        .nav-tabs .nav-link.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-color: #667eea;
            color: white;
        }

        .tab-content {
            background: white;
            border-radius: 0 15px 15px 15px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
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

            <li class="menu-botoes ativo">
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
                        <i class="bi bi-calendar"></i> Calendário Acadêmico
                    </h1>

                    <!-- Mensagens -->
                    <?php if ($mensagem): ?>
                        <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                            <?php echo $mensagem; ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Abas -->
                    <ul class="nav nav-tabs mb-4" id="abasCalendario" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="visualizar-tab" data-bs-toggle="tab" data-bs-target="#visualizar" type="button" role="tab">
                                <i class="bi bi-calendar-month"></i> Visualizar Calendário
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cadastrar-aula-tab" data-bs-toggle="tab" data-bs-target="#cadastrar-aula" type="button" role="tab">
                                <i class="bi bi-plus-circle"></i> Cadastrar Aula
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cadastrar-evento-tab" data-bs-toggle="tab" data-bs-target="#cadastrar-evento" type="button" role="tab">
                                <i class="bi bi-calendar-plus"></i> Cadastrar Evento
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="listar-tab" data-bs-toggle="tab" data-bs-target="#listar" type="button" role="tab">
                                <i class="bi bi-list-ul"></i> Listar Aulas
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="abasCalendarioContent">
                        <!-- Aba Visualizar Calendário -->
                        <div class="tab-pane fade show active" id="visualizar" role="tabpanel">
                            <div class="calendar-container">
                                <div class="calendar-header">
                                    <div class="calendar-nav">
                                        <a href="?mes=<?php echo $mes == 1 ? 12 : $mes - 1; ?>&ano=<?php echo $mes == 1 ? $ano - 1 : $ano; ?>" class="btn btn-light">
                                            <i class="bi bi-chevron-left"></i>
                                        </a>
                                        <h3 class="mb-0">
                                            <?php
                                            $nomes_meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
                                            echo $nomes_meses[$mes] . ' ' . $ano;
                                            ?>
                                        </h3>
                                        <a href="?mes=<?php echo $mes == 12 ? 1 : $mes + 1; ?>&ano=<?php echo $mes == 12 ? $ano + 1 : $ano; ?>" class="btn btn-light">
                                            <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </div>
                                </div>

                                <div class="calendar-grid">
                                    <!-- Dias da semana -->
                                    <div class="calendar-day-header">Dom</div>
                                    <div class="calendar-day-header">Seg</div>
                                    <div class="calendar-day-header">Ter</div>
                                    <div class="calendar-day-header">Qua</div>
                                    <div class="calendar-day-header">Qui</div>
                                    <div class="calendar-day-header">Sex</div>
                                    <div class="calendar-day-header">Sáb</div>

                                    <!-- Dias do mês -->
                                    <?php
                                    $primeiro_dia = mktime(0, 0, 0, $mes, 1, $ano);
                                    $dia_semana = date('w', $primeiro_dia);
                                    $dias_no_mes = date('t', $primeiro_dia);
                                    $hoje = date('j');
                                    $mes_atual = date('n');
                                    $ano_atual = date('Y');

                                    // Dias vazios antes do primeiro dia do mês
                                    for ($i = 0; $i < $dia_semana; $i++) {
                                        $dia_vazio = date('t', mktime(0, 0, 0, $mes - 1, 1, $ano)) - $dia_semana + $i + 1;
                                        echo '<div class="calendar-day other-month">';
                                        echo '<div class="calendar-day-number">' . $dia_vazio . '</div>';
                                        echo '</div>';
                                    }

                                    // Dias do mês
                                    for ($dia = 1; $dia <= $dias_no_mes; $dia++) {
                                        $eh_hoje = ($dia == $hoje && $mes == $mes_atual && $ano == $ano_atual);
                                        echo '<div class="calendar-day' . ($eh_hoje ? ' today' : '') . '">';

                                        echo '<div class="calendar-day-number">' . $dia . '</div>';

                                        // Eventos do dia
                                        $data_atual = sprintf('%04d-%02d-%02d', $ano, $mes, $dia);

                                        // Aulas do dia - Integradas aos eventos do calendário
                                        // As aulas agora são exibidas como eventos normais no calendário

                                        // Eventos do calendário do dia
                                        $eventos_dia = [];
                                        $eventos->data_seek(0);
                                        while ($evento = $eventos->fetch_assoc()) {
                                            if (date('Y-m-d', strtotime($evento['data_inicio'])) == $data_atual) {
                                                $eventos_dia[] = $evento;
                                            }
                                        }

                                        foreach ($eventos_dia as $evento) {
                                            echo '<div class="event-item event-evento" title="' . htmlspecialchars($evento['titulo']) . '">';
                                            echo '<i class="bi bi-calendar-event"></i> ' . substr(htmlspecialchars($evento['titulo']), 0, 12) . '...';
                                            echo '</div>';
                                        }

                                        echo '</div>';
                                    }

                                    // Dias vazios depois do último dia do mês
                                    $dias_exibidos = $dia_semana + $dias_no_mes;
                                    $prox_mes_dias = ($dias_exibidos % 7 == 0) ? 0 : 7 - ($dias_exibidos % 7);
                                    for ($i = 1; $i <= $prox_mes_dias; $i++) {
                                        echo '<div class="calendar-day other-month">';
                                        echo '<div class="calendar-day-number">' . $i . '</div>';
                                        echo '</div>';
                                    }
                                    ?>
                                </div>
                            </div>
                        </div>

                        <!-- Aba Cadastrar Aula -->
                        <div class="tab-pane fade" id="cadastrar-aula" role="tabpanel">
                            <div class="form-section">
                                <h4 class="mb-4">
                                    <i class="bi bi-plus-circle"></i> Cadastrar Nova Aula
                                </h4>
                                <form method="POST" action="calendario.php">
                                    <input type="hidden" name="acao" value="cadastrar_aula">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="id_materia" class="form-label">Matéria:</label>
                                                <select class="form-select" id="id_materia" name="id_materia" required>
                                                    <option value="">Selecione a matéria...</option>
                                                    <?php while ($materia = $materias->fetch_assoc()): ?>
                                                    <option value="<?php echo $materia['id']; ?>">
                                                        <?php echo htmlspecialchars($materia['nome_materia']); ?>
                                                    </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <?php if ($_SESSION['tipo_usuario'] === 'Administrador'): ?>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="id_professor" class="form-label">Professor:</label>
                                                <select class="form-select" id="id_professor" name="id_professor" required>
                                                    <option value="">Selecione o professor...</option>
                                                    <?php while ($professor = $professores->fetch_assoc()): ?>
                                                    <option value="<?php echo $professor['id']; ?>">
                                                        <?php echo htmlspecialchars($professor['nome_user']); ?>
                                                    </option>
                                                    <?php endwhile; ?>
                                                </select>
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="titulo" class="form-label">Título da Aula:</label>
                                                <input type="text" class="form-control" id="titulo" name="titulo"
                                                       placeholder="Ex: Introdução à Mecatrônica" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="sala" class="form-label">Sala:</label>
                                                <input type="text" class="form-control" id="sala" name="sala"
                                                       placeholder="Ex: Lab 101, Sala 05">
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="data_aula" class="form-label">Data da Aula:</label>
                                                <input type="date" class="form-control" id="data_aula" name="data_aula" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="hora_inicio" class="form-label">Hora de Início:</label>
                                                <input type="time" class="form-control" id="hora_inicio" name="hora_inicio" required>
                                            </div>
                                        </div>

                                        <div class="col-md-4">
                                            <div class="mb-3">
                                                <label for="hora_fim" class="form-label">Hora de Término:</label>
                                                <input type="time" class="form-control" id="hora_fim" name="hora_fim" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tipo_aula" class="form-label">Tipo de Aula:</label>
                                                <select class="form-select" id="tipo_aula" name="tipo_aula">
                                                    <option value="teorica">Teórica</option>
                                                    <option value="pratica">Prática</option>
                                                    <option value="online">Online</option>
                                                    <option value="prova">Avaliação</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="link_aula" class="form-label">Link da Aula (se online):</label>
                                                <input type="url" class="form-control" id="link_aula" name="link_aula"
                                                       placeholder="https://meet.google.com/...">
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="descricao" class="form-label">Descrição:</label>
                                                <textarea class="form-control" id="descricao" name="descricao" rows="3"
                                                          placeholder="Descreva o conteúdo da aula..."></textarea>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="observacoes" class="form-label">Observações:</label>
                                                <textarea class="form-control" id="observacoes" name="observacoes" rows="2"
                                                          placeholder="Observações adicionais sobre a aula..."></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-light">
                                        <i class="bi bi-save"></i> Cadastrar Aula
                                    </button>
                                    <button type="reset" class="btn btn-outline-light ms-2">
                                        <i class="bi bi-x-circle"></i> Limpar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Aba Cadastrar Evento -->
                        <div class="tab-pane fade" id="cadastrar-evento" role="tabpanel">
                            <div class="form-section">
                                <h4 class="mb-4">
                                    <i class="bi bi-calendar-plus"></i> Cadastrar Novo Evento
                                </h4>
                                <form method="POST" action="calendario.php">
                                    <input type="hidden" name="acao" value="cadastrar_evento">

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="titulo_evento" class="form-label">Título do Evento:</label>
                                                <input type="text" class="form-control" id="titulo_evento" name="titulo"
                                                       placeholder="Ex: Semana de Provas" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="tipo_evento" class="form-label">Tipo do Evento:</label>
                                                <select class="form-select" id="tipo_evento" name="tipo_evento">
                                                    <option value="geral">Geral</option>
                                                    <option value="prova">Prova</option>
                                                    <option value="feriado">Feriado</option>
                                                    <option value="reuniao">Reunião</option>
                                                    <option value="evento">Evento Especial</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="data_inicio" class="form-label">Data de Início:</label>
                                                <input type="datetime-local" class="form-control" id="data_inicio" name="data_inicio" required>
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label for="data_fim" class="form-label">Data de Término:</label>
                                                <input type="datetime-local" class="form-control" id="data_fim" name="data_fim" required>
                                            </div>
                                        </div>

                                        <div class="col-12">
                                            <div class="mb-3">
                                                <label for="descricao_evento" class="form-label">Descrição:</label>
                                                <textarea class="form-control" id="descricao_evento" name="descricao" rows="4"
                                                          placeholder="Descreva o evento em detalhes..." required></textarea>
                                            </div>
                                        </div>
                                    </div>

                                    <button type="submit" class="btn btn-light">
                                        <i class="bi bi-save"></i> Cadastrar Evento
                                    </button>
                                    <button type="reset" class="btn btn-outline-light ms-2">
                                        <i class="bi bi-x-circle"></i> Limpar
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Aba Listar Aulas -->
                        <div class="tab-pane fade" id="listar" role="tabpanel">
                            <h4 class="mb-4">
                                <i class="bi bi-list-ul"></i> Aulas Cadastradas
                            </h4>

                            <?php if ($aulas && $aulas->num_rows > 0): ?>
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Data</th>
                                                <th>Horário</th>
                                                <th>Título</th>
                                                <th>Matéria</th>
                                                <th>Professor</th>
                                                <th>Sala</th>
                                                <th>Tipo</th>
                                                <th>Status</th>
                                                <th>Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $aulas->data_seek(0);
                                            while ($aula = $aulas->fetch_assoc()):
                                            ?>
                                            <tr>
                                                <td><?php echo date('d/m/Y', strtotime($aula['data_aula'])); ?></td>
                                                <td><?php echo $aula['hora_inicio'] . ' - ' . $aula['hora_fim']; ?></td>
                                                <td><?php echo htmlspecialchars($aula['titulo']); ?></td>
                                                <td><?php echo htmlspecialchars($aula['nome_materia'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($aula['nome_professor']); ?></td>
                                                <td><?php echo htmlspecialchars($aula['sala'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="badge bg-info">
                                                        <?php
                                                        $tipos = [
                                                            'teorica' => 'Teórica',
                                                            'pratica' => 'Prática',
                                                            'online' => 'Online',
                                                            'prova' => 'Prova'
                                                        ];
                                                        echo $tipos[$aula['tipo_aula']] ?? $aula['tipo_aula'];
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge status-badge
                                                        <?php
                                                        $status_classes = [
                                                            'planejada' => 'bg-primary',
                                                            'realizada' => 'bg-success',
                                                            'cancelada' => 'bg-danger',
                                                            'adiada' => 'bg-warning'
                                                        ];
                                                        echo $status_classes[$aula['status']] ?? 'bg-secondary';
                                                        ?>
                                                    ">
                                                        <?php echo ucfirst($aula['status']); ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary" title="Editar">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" title="Marcar como Realizada">
                                                            <i class="bi bi-check-circle"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" title="Cancelar">
                                                            <i class="bi bi-x-circle"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: #dee2e6;"></i>
                                    <h5 class="mt-3 text-muted">Nenhuma aula encontrada</h5>
                                    <p class="text-muted">Não há aulas cadastradas para este período.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Javascript/js-telaadm/menu.js"></script>
    <script>
        // Auto-refresh do calendário a cada 5 minutos
        setInterval(function() {
            location.reload();
        }, 300000);

        // Validação de datas e horários
        document.getElementById('data_aula')?.addEventListener('change', function() {
            const dataSelecionada = new Date(this.value);
            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);

            if (dataSelecionada < hoje) {
                this.setCustomValidity('A data da aula não pode ser anterior a hoje.');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validação de horários
        document.getElementById('hora_fim')?.addEventListener('change', function() {
            const horaInicio = document.getElementById('hora_inicio').value;
            const horaFim = this.value;

            if (horaInicio && horaFim && horaFim <= horaInicio) {
                this.setCustomValidity('A hora de término deve ser posterior à hora de início.');
            } else {
                this.setCustomValidity('');
            }
        });

        // Validação de datas do evento
        document.getElementById('data_fim')?.addEventListener('change', function() {
            const dataInicio = document.getElementById('data_inicio').value;
            const dataFim = this.value;

            if (dataInicio && dataFim && dataFim <= dataInicio) {
                this.setCustomValidity('A data de término deve ser posterior à data de início.');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>
</html>