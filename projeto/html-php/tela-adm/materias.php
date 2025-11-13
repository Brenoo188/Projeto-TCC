<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || $_SESSION['tipo_usuario'] !== 'Administrador') {
    header('Location: ../parte-inicial/index.php?erro=acesso_negado');
    exit();
}

if (!$conexao || $conexao->connect_error) {
    die("<div class='alert alert-danger text-center'><h3>Erro de conexão com o banco!</h3></div>");
}

// DESATIVA WARNINGS DE CHAVE INEXISTENTE
error_reporting(E_ALL ^ E_WARNING);

// FUNÇÃO SEGURA
function safeQuery($conexao, $sql, $types = "", $params = []) {
    $stmt = $conexao->prepare($sql);
    if (!$stmt) return false;
    if (!empty($types) && !empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
    return $result;
}

// MENSAGENS
$mensagem = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $acao = $_POST['acao'];

    if ($acao === 'cadastrar') {
        $nome = trim($_POST['nome_materia'] ?? '');
        $descricao = trim($_POST['descricao_materia'] ?? '');
        $carga = (int)($_POST['carga_horaria'] ?? 0);
        $periodo = $_POST['periodo'] ?? '';

        if (empty($nome) || $carga <= 0) {
            $mensagem = ['tipo' => 'danger', 'texto' => 'Preencha todos os campos obrigatórios!'];
        } else {
            $check = safeQuery($conexao, "SELECT id FROM materias WHERE nome_materia = ?", "s", [$nome]);
            if ($check && $check->num_rows > 0) {
                $mensagem = ['tipo' => 'danger', 'texto' => 'Matéria já existe!'];
            } else {
                // Usa a coluna correta: categoria
                $sql = "INSERT INTO materias (nome_materia, descricao_materia, carga_horaria, categoria) VALUES (?, ?, ?, ?)";
                $result = safeQuery($conexao, $sql, "ssis", [$nome, $descricao, $carga, $periodo]);
                $mensagem = $result ? ['tipo' => 'success', 'texto' => 'Matéria cadastrada com sucesso!'] : ['tipo' => 'danger', 'texto' => 'Erro ao cadastrar!'];
            }
        }
    }

    if ($acao === 'excluir') {
        $id = (int)($_POST['id_materia'] ?? 0);
        if ($id > 0) {
            safeQuery($conexao, "DELETE FROM usuario_materia WHERE id_materia = ?", "i", [$id]);
            $result = safeQuery($conexao, "DELETE FROM materias WHERE id = ?", "i", [$id]);
            $mensagem = $result ? ['tipo' => 'success', 'texto' => 'Matéria excluída com sucesso!'] : ['tipo' => 'danger', 'texto' => 'Erro ao excluir!'];
        }
    }

    header("Location: materias.php");
    exit();
}

// LISTAR MATÉRIAS COM TRATAMENTO SEGURO
$materias_result = $conexao->query("
    SELECT m.*, 
           COALESCE(categoria, '') as categoria_segura,
           COALESCE((SELECT COUNT(*) FROM usuario_materia um WHERE um.id_materia = m.id), 0) as total_professores
    FROM materias m
    ORDER BY m.nome_materia
");

$materias = [];
if ($materias_result) {
    while ($row = $materias_result->fetch_assoc()) {
        // Garante que categoria sempre exista
        $row['categoria'] = $row['categoria_segura'] ?? '';
        $materias[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Matérias</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/menu-padrao.css">
    <style>
        body { background: #f4f6f9; }
        .card { border: none; border-radius: 20px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); overflow: hidden; }
        .card-header { background: linear-gradient(135deg, #667eea, #764ba2); color: white; }
        .btn-primary { background: #667eea; border: none; }
        .badge { font-size: 0.9em; }
        .table td { vertical-align: middle; }
    </style>
</head>
<body>

    <!-- MENU LATERAL -->
    <nav class="menu-lateral">
        <div class="btn-expandir" id="btn-expan"><i class="bi bi-list"></i></div>
        <ul>
            <li class="menu-botoes"><a href="home.php"><i class="bi bi-house"></i><span class="txt-link">Home</span></a></li>
            <li class="menu-botoes"><a href="calendario.php"><i class="bi bi-calendar"></i><span class="txt-link">Calendário</span></a></li>
            <li class="menu-botoes"><a href="cadastros_int.php"><i class="bi bi-people"></i><span class="txt-link">Cadastros</span></a></li>
            <li class="menu-botoes"><a href="professores.php"><i class="bi bi-person-workspace"></i><span class="txt-link">Professores</span></a></li>
            <li class="menu-botoes ativo"><a href="materias.php"><i class="bi bi-book"></i><span class="txt-link">Matérias</span></a></li>
            <li class="menu-botoes"><a href="notificacoes.php"><i class="bi bi-bell"></i><span class="txt-link">Notificações</span></a></li>
            <li class="menu-botoes"><a href="conta.php"><i class="bi bi-person-circle"></i><span class="txt-link">Conta</span></a></li>
        </ul>
    </nav>

    <main class="conteudo">
        <div class="container-fluid py-5">
            <div class="text-center mb-5">
                <h1 class="display-5 fw-bold text-primary"><i class="bi bi-book"></i> Gerenciamento de Matérias</h1>
                <p class="text-muted">Cadastre, edite e organize todas as disciplinas do curso</p>
            </div>

            <!-- MENSAGEM -->
            <?php if ($mensagem): ?>
                <div class="alert alert-<?= $mensagem['tipo'] ?> alert-dismissible fade show rounded-4 shadow">
                    <strong><?= $mensagem['tipo'] === 'success' ? 'Sucesso!' : 'Erro!' ?></strong> <?= $mensagem['texto'] ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- CADASTRO -->
            <div class="card mb-5">
                <div class="card-header">
                    <h4 class="mb-0"><i class="bi bi-plus-circle"></i> Cadastrar Nova Matéria</h4>
                </div>
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="acao" value="cadastrar">
                        <div class="row g-4">
                            <div class="col-lg-6">
                                <label class="form-label fw-bold">Nome da Matéria *</label>
                                <input type="text" name="nome_materia" class="form-control form-control-lg" placeholder="Ex: Eletrônica Básica" required>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label fw-bold">Carga Horária *</label>
                                <input type="number" name="carga_horaria" class="form-control form-control-lg" min="10" max="200" placeholder="60" required>
                            </div>
                            <div class="col-lg-3">
                                <label class="form-label fw-bold">Período</label>
                                <select name="periodo" class="form-select form-select-lg">
                                    <option value="">Selecione...</option>
                                    <option value="manhã">Manhã</option>
                                    <option value="tarde">Tarde</option>
                                    <option value="noite">Noite</option>
                                </select>
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="form-label fw-bold">Descrição (opcional)</label>
                            <textarea name="descricao_materia" class="form-control" rows="3" placeholder="Conteúdo, objetivos, ementa..."></textarea>
                        </div>
                        <div class="mt-4 text-end">
                            <button type="submit" class="btn btn-primary btn-lg px-5">
                                <i class="bi bi-save"></i> Cadastrar Matéria
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- LISTA DE MATÉRIAS -->
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h4 class="mb-0"><i class="bi bi-list-check"></i> Matérias Cadastradas</h4>
                    <span class="badge bg-primary fs-5"><?= count($materias) ?></span>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($materias)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-inbox display-1 text-muted"></i>
                            <p class="mt-3 text-muted fs-4">Nenhuma matéria cadastrada ainda.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-primary">
                                    <tr>
                                        <th>Matéria</th>
                                        <th class="text-center">Carga</th>
                                        <th class="text-center">Período</th>
                                        <th class="text-center">Professores</th>
                                        <th class="text-center">Ações</th>
                                    </trrinos>
                                </thead>
                                <tbody>
                                    <?php foreach ($materias as $m): ?>
                                    <tr>
                                        <td>
                                            <div class="fw-bold text-primary"><?= htmlspecialchars($m['nome_materia']) ?></div>
                                            <?php if (!empty($m['descricao_materia'])): ?>
                                                <small class="text-muted d-block"><?= htmlspecialchars(substr($m['descricao_materia'], 0, 80)) ?>...</small>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-info fs-6"><?= $m['carga_horaria'] ?>h</span>
                                        </td>
                                        <td class="text-center">
                                            <?php if (!empty($m['categoria'])): ?>
                                                <span class="badge bg-secondary"><?= htmlspecialchars($m['categoria']) ?></span>
                                            <?php else: ?>
                                                <span class="text-muted">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success"><?= $m['total_professores'] ?></span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-danger" onclick="excluir(<?= $m['id'] ?>, '<?= addslashes(htmlspecialchars($m['nome_materia'])) ?>')">
                                                <i class="bi bi-trash"></i> Excluir
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Menu
        document.getElementById('btn-expan').addEventListener('click', () => {
            document.querySelector('.menu-lateral').classList.toggle('expandir');
        });

        // Excluir com confirmação
        function excluir(id, nome) {
            if (confirm(`Tem certeza que deseja EXCLUIR a matéria:\n"${nome}"?\n\nEsta ação não pode ser desfeita!`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" name="id_materia" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>