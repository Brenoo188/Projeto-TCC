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

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['acao'])) {
        switch ($_POST['acao']) {
            case 'cadastrar':
                cadastrarMateria($conexao);
                break;
            case 'editar':
                editarMateria($conexao);
                break;
            case 'excluir':
                excluirMateria($conexao);
                break;
            case 'vincular_professor':
                vincularProfessor($conexao);
                break;
            case 'desvincular_professor':
                desvincularProfessor($conexao);
                break;
        }
    }
}

function cadastrarMateria($conexao) {
    $nome = $_POST['nome_materia'] ?? '';
    $descricao = $_POST['descricao_materia'] ?? '';
    $carga_horaria = $_POST['carga_horaria'] ?? 0;
    $periodo = $_POST['periodo'] ?? '';

    // Validações básicas
    if (empty($nome) || empty($carga_horaria)) {
        header('Location: materias.php?erro=campos_obrigatorios');
        exit();
    }

    // Verificar se matéria já existe
    $stmt = $conexao->prepare("SELECT id FROM materias WHERE nome = ?");
    $stmt->bind_param("s", $nome);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header('Location: materias.php?erro=materia_existe');
        exit();
    }

    // Inserir matéria
    $stmt = $conexao->prepare("
        INSERT INTO materias (nome_materia, descricao, carga_horaria, categoria, data_criacao)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->bind_param("ssis", $nome, $descricao, $carga_horaria, $periodo);

    if ($stmt->execute()) {
        registrarLog($conexao, $_SESSION['id'], 'cadastro_materia', "Cadastrou matéria: $nome");
        header('Location: materias.php?sucesso=cadastro');
        exit();
    } else {
        header('Location: materias.php?erro=erro_cadastro');
        exit();
    }
}

function editarMateria($conexao) {
    $id = $_POST['id_materia'] ?? 0;
    $nome = $_POST['nome_materia'] ?? '';
    $descricao = $_POST['descricao_materia'] ?? '';
    $carga_horaria = $_POST['carga_horaria'] ?? 0;
    $periodo = $_POST['periodo'] ?? '';

    if (empty($id) || empty($nome) || empty($carga_horaria)) {
        header('Location: materias.php?erro=campos_obrigatorios');
        exit();
    }

    // Atualizar matéria
    $stmt = $conexao->prepare("
        UPDATE materias SET nome_materia = ?, descricao = ?, carga_horaria = ?, categoria = ?
        WHERE id_materia = ?
    ");
    $stmt->bind_param("ssisi", $nome, $descricao, $carga_horaria, $periodo, $id);

    if ($stmt->execute()) {
        registrarLog($conexao, $_SESSION['id'], 'editou_materia', "Editou dados da matéria ID: $id");
        header('Location: materias.php?sucesso=edicao');
        exit();
    } else {
        header('Location: materias.php?erro=erro_edicao');
        exit();
    }
}

function excluirMateria($conexao) {
    $id = $_POST['id_materia'] ?? 0;

    if (empty($id)) {
        header('Location: materias.php?erro=id_invalido');
        exit();
    }

    // Verificar se matéria existe
    $stmt = $conexao->prepare("SELECT nome_materia FROM materias WHERE id_materia = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: materias.php?erro=materia_nao_encontrada');
        exit();
    }

    $materia = $result->fetch_assoc();
    $nome_materia = $materia['nome_materia'];

    // Excluir relações com professores
    $stmt = $conexao->prepare("DELETE FROM usuario_materia WHERE id_materia = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Excluir matéria
    $stmt = $conexao->prepare("DELETE FROM materias WHERE id_materia = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        registrarLog($conexao, $_SESSION['id'], 'excluiu_materia', "Excluiu matéria: $nome_materia");
        header('Location: materias.php?sucesso=exclusao');
        exit();
    } else {
        header('Location: materias.php?erro=erro_exclusao');
        exit();
    }
}

function vincularProfessor($conexao) {
    $id_materia = $_POST['id_materia'] ?? 0;
    $id_professor = $_POST['id_professor'] ?? 0;

    if (empty($id_materia) || empty($id_professor)) {
        header('Location: materias.php?erro=dados_incompletos');
        exit();
    }

    // Verificar se já existe a relação
    $stmt = $conexao->prepare("SELECT id FROM usuario_materia WHERE id_materia = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_materia, $id_professor);
    $stmt->execute();

    if ($stmt->get_result()->num_rows === 0) {
        $stmt = $conexao->prepare("INSERT INTO usuario_materia (id_materia, id_usuario) VALUES (?, ?)");
        $stmt->bind_param("ii", $id_materia, $id_professor);
        $stmt->execute();

        registrarLog($conexao, $_SESSION['id'], 'vinculou_professor_materia', "Vinculou professor ID: $id_professor à matéria ID: $id_materia");
    }

    header('Location: materias.php?sucesso=vinculo');
    exit();
}

function desvincularProfessor($conexao) {
    $id_materia = $_POST['id_materia'] ?? 0;
    $id_professor = $_POST['id_professor'] ?? 0;

    if (empty($id_materia) || empty($id_professor)) {
        header('Location: materias.php?erro=dados_incompletos');
        exit();
    }

    $stmt = $conexao->prepare("DELETE FROM usuario_materia WHERE id_materia = ? AND id_usuario = ?");
    $stmt->bind_param("ii", $id_materia, $id_professor);
    $stmt->execute();

    registrarLog($conexao, $_SESSION['id'], 'desvinculou_professor_materia', "Desvinculou professor ID: $id_professor da matéria ID: $id_materia");

    header('Location: materias.php?sucesso=desvinculo');
    exit();
}

// Obter matérias
$materias = [];
$result = $conexao->query("
    SELECT m.*, COUNT(um.id_usuario) as total_professores
    FROM materias m
    LEFT JOIN usuario_materia um ON m.id_materia = um.id_materia
    GROUP BY m.id_materia
    ORDER BY m.nome_materia
");

while ($row = $result->fetch_assoc()) {
    $materias[] = $row;
}

// Obter matérias para combobox (para seleção em outros formulários)
$materias_combo = [];
$result_combo = $conexao->query("SELECT id_materia, nome_materia FROM materias WHERE status = 'ativa' ORDER BY nome_materia");
while ($row = $result_combo->fetch_assoc()) {
    $materias_combo[] = $row;
}

// Obter professores para vinculação
$professores = [];
$result = $conexao->query("
    SELECT id, nome_user
    FROM usuarios
    WHERE tipo_usuario = 'Professor'
    ORDER BY nome_user
");

while ($row = $result->fetch_assoc()) {
    $professores[] = $row;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matérias - Sistema Acadêmico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/menu-padrao.css">
    <link rel="stylesheet" href="../../css/css_telaadm/cadastros.css">
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

            <li class="menu-botoes">
                <a href="professores.php">
                    <span class="icon"><i class="bi bi-person-workspace"></i></span>
                    <span class="txt-link">Professores</span>
                </a>
            </li>

            <li class="menu-botoes ativo" style="background-color: rgb(0, 92, 169);">
                <a href="materias.php">
                    <span class="icon"><i class="bi bi-book"></i></span>
                    <span class="txt-link">Matérias</span>
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

            <li class="menu-botoes">
                <a href="configuracao.php">
                    <span class="icon"><i class="bi bi-gear"></i></span>
                    <span class="txt-link">Configuração</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="conteudo-cadastros">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">
                        <i class="bi bi-book"></i> Gerenciamento de Matérias
                    </h1>

                    <!-- Mensagens de feedback -->
                    <?php if (isset($_GET['sucesso'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i>
                        <?php
                        switch ($_GET['sucesso']) {
                            case 'cadastro': echo 'Matéria cadastrada com sucesso!'; break;
                            case 'edicao': echo 'Matéria atualizada com sucesso!'; break;
                            case 'exclusao': echo 'Matéria excluída com sucesso!'; break;
                            case 'vinculo': echo 'Professor vinculado com sucesso!'; break;
                            case 'desvinculo': echo 'Professor desvinculado com sucesso!'; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['erro'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i>
                        <?php
                        switch ($_GET['erro']) {
                            case 'campos_obrigatorios': echo 'Preencha todos os campos obrigatórios!'; break;
                            case 'materia_existe': echo 'Já existe uma matéria com este nome!'; break;
                            case 'erro_cadastro': echo 'Erro ao cadastrar matéria!'; break;
                            case 'erro_edicao': echo 'Erro ao atualizar matéria!'; break;
                            case 'erro_exclusao': echo 'Erro ao excluir matéria!'; break;
                            case 'materia_nao_encontrada': echo 'Matéria não encontrada!'; break;
                            case 'id_invalido': echo 'ID inválido!'; break;
                            case 'dados_incompletos': echo 'Dados incompletos para vínculo!'; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Formulário de Cadastro -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-plus-circle"></i> Cadastrar Nova Matéria
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="materias.php">
                                <input type="hidden" name="acao" value="cadastrar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nome_materia" class="form-label">Nome da Matéria *</label>
                                            <select class="form-select" id="nome_materia" name="nome_materia" required onchange="toggleOutroMateria()">
                                                <option value="">Selecione uma matéria...</option>
                                                <optgroup label="Mecatrônica">
                                                    <option value="Mecânica Técnica">Mecânica Técnica</option>
                                                    <option value="Eletrônica Básica">Eletrônica Básica</option>
                                                    <option value="Automação Industrial">Automação Industrial</option>
                                                    <option value="Controladores Lógicos">Controladores Lógicos (CLP)</option>
                                                    <option value="Hidráulica e Pneumática">Hidráulica e Pneumática</option>
                                                    <option value="Robótica Industrial">Robótica Industrial</option>
                                                    <option value="Sensores e Atuadores">Sensores e Atuadores</option>
                                                </optgroup>
                                                <optgroup label="Informática Industrial">
                                                    <option value="Programação de CLP">Programação de CLP</option>
                                                    <option value="Redes Industriais">Redes Industriais</option>
                                                    <option value="SCADA">SCADA</option>
                                                    <option value="Interface Homem-Máquina">Interface Homem-Máquina (IHM)</option>
                                                    <option value="Instrumentação">Instrumentação</option>
                                                </optgroup>
                                                <optgroup label="Eletrotécnica">
                                                    <option value="Instalações Elétricas">Instalações Elétricas</option>
                                                    <option value="Máquinas Elétricas">Máquinas Elétricas</option>
                                                    <option value="Comandos Elétricos">Comandos Elétricos</option>
                                                    <option value="Eletrônica Industrial">Eletrônica Industrial</option>
                                                    <option value="Análise de Circuitos">Análise de Circuitos</option>
                                                </optgroup>
                                                <optgroup label="Mecânica">
                                                    <option value="Desenho Técnico">Desenho Técnico</option>
                                                    <option value="CAD/CAM">CAD/CAM</option>
                                                    <option value="Usinagem">Usinagem</option>
                                                    <option value="Soldagem">Soldagem</option>
                                                    <option value="Manutenção Mecânica">Manutenção Mecânica</option>
                                                    <option value="Tecnologia dos Materiais">Tecnologia dos Materiais</option>
                                                </optgroup>
                                                <optgroup label="Automação">
                                                    <option value="Sistemas de Controle">Sistemas de Controle</option>
                                                    <option value="Processos Industriais">Processos Industriais</option>
                                                    <option value="Segurança do Trabalho">Segurança do Trabalho</option>
                                                    <option value="Qualidade Industrial">Qualidade Industrial</option>
                                                    <option value="Gestão da Produção">Gestão da Produção</option>
                                                </optgroup>
                                                <optgroup label="Bases Tecnológicas">
                                                    <option value="Matemática Aplicada">Matemática Aplicada</option>
                                                    <option value="Física Aplicada">Física Aplicada</option>
                                                    <option value="Química Aplicada">Química Aplicada</option>
                                                    <option value="Português Técnico">Português Técnico</option>
                                                    <option value="Inglês Técnico">Inglês Técnico</option>
                                                    <option value="Informática Básica">Informática Básica</option>
                                                </optgroup>
                                                <option value="outra">Outra (digite abaixo)</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="div_outra_materia" style="display: none;">
                                        <div class="mb-3">
                                            <label for="outra_materia" class="form-label">Nome da Matéria *</label>
                                            <input type="text" class="form-control" id="outra_materia" name="outra_materia" placeholder="Digite o nome da matéria">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="carga_horaria" class="form-label">Carga Horária (h) *</label>
                                            <input type="number" class="form-control" id="carga_horaria" name="carga_horaria" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label for="periodo" class="form-label">Período</label>
                                            <select class="form-select" id="periodo" name="periodo">
                                                <option value="">Selecione...</option>
                                                <option value="manha">Manhã</option>
                                                <option value="tarde">Tarde</option>
                                                <option value="noite">Noite</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12">
                                        <div class="mb-3">
                                            <label for="descricao_materia" class="form-label">Descrição</label>
                                            <textarea class="form-control" id="descricao_materia" name="descricao_materia" rows="3"
                                                      placeholder="Descrição detalhada da matéria, ementa, objetivos, etc."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-plus-circle"></i> Cadastrar Matéria
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Limpar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Matérias -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-book"></i> Matérias Cadastradas
                                <span class="badge bg-primary ms-2"><?php echo count($materias); ?></span>
                            </h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-success" onclick="exportarMaterias()">
                                    <i class="bi bi-download"></i> Exportar
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>Carga Horária</th>
                                            <th>Período</th>
                                            <th>Professores</th>
                                            <th>Cadastro</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($materias)): ?>
                                        <tr>
                                            <td colspan="6" class="text-center text-muted">Nenhuma matéria cadastrada.</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($materias as $materia): ?>
                                            <tr>
                                                <td>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($materia['nome_materia']); ?></strong>
                                                        <?php if (!empty($materia['descricao'])): ?>
                                                        <br><small class="text-muted"><?php echo substr(htmlspecialchars($materia['descricao']), 0, 50) . '...'; ?></small>
                                                        <?php endif; ?>
                                                    </div>
                                                </td>
                                                <td>
                                                    <span class="badge bg-info"><?php echo $materia['carga_horaria']; ?>h</span>
                                                </td>
                                                <td>
                                                    <?php if (!empty($materia['periodo'])): ?>
                                                    <span class="badge bg-secondary"><?php echo htmlspecialchars($materia['periodo']); ?></span>
                                                    <?php else: ?>
                                                    <span class="text-muted">-</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $materia['total_professores']; ?> professor(es)</span>
                                                </td>
                                                <td><?php echo date('d/m/Y', strtotime($materia['data_criacao'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-warning" onclick="editarMateria(<?php echo $materia['id_materia']; ?>)">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="verMateria(<?php echo $materia['id_materia']; ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success" onclick="gerenciarProfessores(<?php echo $materia['id_materia']; ?>, '<?php echo htmlspecialchars($materia['nome_materia']); ?>')">
                                                            <i class="bi bi-people"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="excluirMateria(<?php echo $materia['id_materia']; ?>, '<?php echo htmlspecialchars($materia['nome_materia']); ?>')">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            <?php endforeach; ?>
                                        <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Combobox de Matérias (para uso em outras partes do sistema) -->
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5><i class="bi bi-list-check"></i> Selecionar Matéria</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="materia_selecionada" class="form-label">Escolha uma matéria:</label>
                            <select class="form-select" id="materia_selecionada">
                                <option value="">Selecione uma matéria...</option>
                                <?php foreach ($materias_combo as $materia): ?>
                                    <option value="<?php echo $materia['id_materia']; ?>">
                                        <?php echo htmlspecialchars($materia['nome_materia']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary" onclick="verDetalhesMateria()">
                                <i class="bi bi-eye"></i> Ver Detalhes
                            </button>
                            <button class="btn btn-success" onclick="copiarCodigoMateria()">
                                <i class="bi bi-clipboard"></i> Copiar Código
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Edição -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Editar Matéria
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="materias.php" id="formEditar">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id_materia" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome da Matéria *</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome_materia" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_carga_horaria" class="form-label">Carga Horária (h) *</label>
                                    <input type="number" class="form-control" id="edit_carga_horaria" name="carga_horaria" min="1" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_periodo" class="form-label">Período</label>
                                    <select class="form-select" id="edit_periodo" name="periodo">
                                        <option value="">Selecione...</option>
                                        <option value="manha">Manhã</option>
                                        <option value="tarde">Tarde</option>
                                        <option value="noite">Noite</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="edit_descricao" class="form-label">Descrição</label>
                            <textarea class="form-control" id="edit_descricao" name="descricao_materia" rows="3"></textarea>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="formEditar" class="btn btn-primary">Salvar Alterações</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Visualização -->
    <div class="modal fade" id="modalVisualizar" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-book"></i> Detalhes da Matéria
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalhesMateria">
                    <!-- Preenchido via JavaScript -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Gerenciamento de Professores -->
    <div class="modal fade" id="modalProfessores" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-people"></i> Gerenciar Professores - <span id="nomeMateriaModal"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h6>Vincular Professor</h6>
                            <form method="POST" action="materias.php" id="formVincular">
                                <input type="hidden" name="acao" value="vincular_professor">
                                <input type="hidden" name="id_materia" id="vinculo_id_materia">
                                <div class="mb-3">
                                    <select class="form-select" name="id_professor" required>
                                        <option value="">Selecione um professor...</option>
                                        <?php foreach ($professores as $professor): ?>
                                        <option value="<?php echo $professor['id']; ?>"><?php echo htmlspecialchars($professor['nome_user']); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="bi bi-plus-circle"></i> Vincular
                                </button>
                            </form>
                        </div>
                        <div class="col-md-6">
                            <h6>Professores Vinculados</h6>
                            <div id="professoresVinculados" class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                                <!-- Preenchido via JavaScript -->
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Javascript/js-telaadm/menu.js"></script>
    <script>
        // Dados das matérias para edição
        const materias = <?php echo json_encode($materias); ?>;
        const professores = <?php echo json_encode($professores); ?>;

        // Função para mostrar/esconder campo "Outra matéria"
        function toggleOutroMateria() {
            const select = document.getElementById('nome_materia');
            const divOutra = document.getElementById('div_outra_materia');
            const inputOutra = document.getElementById('outra_materia');

            if (select.value === 'outra') {
                divOutra.style.display = 'block';
                inputOutra.required = true;
                select.required = false;
            } else {
                divOutra.style.display = 'none';
                inputOutra.required = false;
                inputOutra.value = '';
                select.required = true;
            }
        }

        // Validar formulário antes de enviar
        document.querySelector('form').addEventListener('submit', function(e) {
            const select = document.getElementById('nome_materia');
            const inputOutra = document.getElementById('outra_materia');

            if (select.value === 'outra' && !inputOutra.value.trim()) {
                e.preventDefault();
                alert('Por favor, digite o nome da matéria.');
                inputOutra.focus();
            }
        });

        function editarMateria(id) {
            const materia = materias.find(m => m.id === id);
            if (materia) {
                document.getElementById('edit_id').value = materia.id;
                document.getElementById('edit_nome').value = materia.nome;
                document.getElementById('edit_carga_horaria').value = materia.carga_horaria;
                document.getElementById('edit_periodo').value = materia.periodo || '';
                document.getElementById('edit_descricao').value = materia.descricao || '';

                const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
                modal.show();
            }
        }

        function verMateria(id) {
            const materia = materias.find(m => m.id === id);
            if (materia) {
                // Carregar professores vinculados
                fetch(`obter_professores_materia.php?id_materia=${id}`)
                    .then(response => response.json())
                    .then(data => {
                        const detalhesHtml = `
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong>Nome:</strong><br>
                                        ${materia.nome}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong>Carga Horária:</strong><br>
                                        <span class="badge bg-info">${materia.carga_horaria}h</span>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong>Período:</strong><br>
                                        ${materia.periodo || '<span class="text-muted">Não definido</span>'}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <strong>Data de Cadastro:</strong><br>
                                        ${new Date(materia.data_criacao).toLocaleString('pt-BR')}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <strong>Descrição:</strong><br>
                                        ${materia.descricao || '<span class="text-muted">Sem descrição</span>'}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-12">
                                    <div class="mb-3">
                                        <strong>Professores Vinculados:</strong><br>
                                        ${data.length > 0 ? data.map(p => `<span class="badge bg-primary me-1">${p.nome_user}</span>`).join('') : '<span class="text-muted">Nenhum professor vinculado</span>'}
                                    </div>
                                </div>
                            </div>
                        `;

                        document.getElementById('detalhesMateria').innerHTML = detalhesHtml;
                        const modal = new bootstrap.Modal(document.getElementById('modalVisualizar'));
                        modal.show();
                    });
            }
        }

        function gerenciarProfessores(id, nome) {
            document.getElementById('nomeMateriaModal').textContent = nome;
            document.getElementById('vinculo_id_materia').value = id;

            // Carregar professores vinculados
            fetch(`obter_professores_materia.php?id_materia=${id}`)
                .then(response => response.json())
                .then(data => {
                    const professoresHtml = data.length > 0
                        ? data.map(p => `
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span>${p.nome_user}</span>
                                <button class="btn btn-sm btn-outline-danger" onclick="desvincularProfessor(${id}, ${p.id})">
                                    <i class="bi bi-x"></i>
                                </button>
                            </div>
                        `).join('')
                        : '<p class="text-muted">Nenhum professor vinculado.</p>';

                    document.getElementById('professoresVinculados').innerHTML = professoresHtml;
                });

            const modal = new bootstrap.Modal(document.getElementById('modalProfessores'));
            modal.show();
        }

        function desvincularProfessor(id_materia, id_professor) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'materias.php';

            const acaoInput = document.createElement('input');
            acaoInput.type = 'hidden';
            acaoInput.name = 'acao';
            acaoInput.value = 'desvincular_professor';

            const materiaInput = document.createElement('input');
            materiaInput.type = 'hidden';
            materiaInput.name = 'id_materia';
            materiaInput.value = id_materia;

            const professorInput = document.createElement('input');
            professorInput.type = 'hidden';
            professorInput.name = 'id_professor';
            professorInput.value = id_professor;

            form.appendChild(acaoInput);
            form.appendChild(materiaInput);
            form.appendChild(professorInput);
            document.body.appendChild(form);
            form.submit();
        }

        function excluirMateria(id, nome) {
            if (confirm(`Tem certeza que deseja excluir a matéria "${nome}"? Esta ação não pode ser desfeita.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'materias.php';

                const acaoInput = document.createElement('input');
                acaoInput.type = 'hidden';
                acaoInput.name = 'acao';
                acaoInput.value = 'excluir';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_materia';
                idInput.value = id;

                form.appendChild(acaoInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function exportarMaterias() {
            window.location.href = 'exportar_materias.php';
        }

        // Funções para o combobox de matérias
        function verDetalhesMateria() {
            const select = document.getElementById('materia_selecionada');
            const idMateria = select.value;

            if (!idMateria) {
                alert('Por favor, selecione uma matéria primeiro!');
                return;
            }

            // Encontra a matéria no array de matérias
            const materia = materias.find(m => m.id_materia == idMateria);

            if (materia) {
                // Redireciona para a visualização da matéria
                verMateria(idMateria);
            }
        }

        function copiarCodigoMateria() {
            const select = document.getElementById('materia_selecionada');
            const idMateria = select.value;

            if (!idMateria) {
                alert('Por favor, selecione uma matéria primeiro!');
                return;
            }

            // Copia o ID da matéria para a área de transferência
            navigator.clipboard.writeText(idMateria).then(() => {
                // Mostra notificação de sucesso
                const btn = event.target.closest('button');
                const originalText = btn.innerHTML;
                btn.innerHTML = '<i class="bi bi-check"></i> Copiado!';
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-success');

                setTimeout(() => {
                    btn.innerHTML = originalText;
                    btn.classList.remove('btn-outline-success');
                    btn.classList.add('btn-success');
                }, 2000);
            }).catch(err => {
                console.error('Erro ao copiar: ', err);
                alert('Erro ao copiar o código da matéria');
            });
        }

        // Adiciona evento de change ao combobox para mostrar informações
        document.getElementById('materia_selecionada').addEventListener('change', function() {
            const idMateria = this.value;
            if (idMateria) {
                const materia = materias.find(m => m.id_materia == idMateria);
                if (materia) {
                    console.log('Matéria selecionada:', materia);
                    // Aqui você pode adicionar mais funcionalidades
                }
            }
        });

    </script>
</body>
</html>