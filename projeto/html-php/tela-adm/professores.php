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
                cadastrarProfessor($conexao);
                break;
            case 'editar':
                editarProfessor($conexao);
                break;
            case 'excluir':
                excluirProfessor($conexao);
                break;
        }
    }
}

function cadastrarProfessor($conexao) {
    $nome = $_POST['nome_professor'] ?? '';
    $email = $_POST['email_professor'] ?? '';
    $cpf = $_POST['cpf_professor'] ?? '';
    $telefone = $_POST['telefone_professor'] ?? '';
    $especialidade = $_POST['especialidade'] ?? '';
    $senha = 'temp123'; // Senha temporária

    // Validações básicas
    if (empty($nome) || empty($email) || empty($cpf)) {
        header('Location: professores.php?erro=campos_obrigatorios');
        exit();
    }

    // Verificar se CPF já existe
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE cpf_user = ? AND tipo_usuario = 'Professor'");
    $stmt->bind_param("s", $cpf);
    $stmt->execute();
    if ($stmt->get_result()->num_rows > 0) {
        header('Location: professores.php?erro=cpf_existe');
        exit();
    }

    // Inserir professor
    $senha_hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $conexao->prepare("
        INSERT INTO usuarios (nome_user, email_user, cpf_user, telefone_user, senha_user, tipo_usuario, data_criacao)
        VALUES (?, ?, ?, ?, ?, 'Professor', NOW())
    ");
    $stmt->bind_param("sssss", $nome, $email, $cpf, $telefone, $senha_hash);

    if ($stmt->execute()) {
        $id_professor = $stmt->insert_id;

        // Inserir especialidade se fornecida
        if (!empty($especialidade)) {
            $stmt = $conexao->prepare("INSERT INTO perfis_usuario (id_usuario, especialidade) VALUES (?, ?)");
            $stmt->bind_param("is", $id_professor, $especialidade);
            $stmt->execute();
        }

        registrarLog($conexao, $_SESSION['id'], 'cadastro_professor', "Cadastrou professor: $nome");
        header('Location: professores.php?sucesso=cadastro');
        exit();
    } else {
        header('Location: professores.php?erro=erro_cadastro');
        exit();
    }
}

function editarProfessor($conexao) {
    $id = $_POST['id_professor'] ?? 0;
    $nome = $_POST['nome_professor'] ?? '';
    $email = $_POST['email_professor'] ?? '';
    $telefone = $_POST['telefone_professor'] ?? '';
    $especialidade = $_POST['especialidade'] ?? '';

    if (empty($id) || empty($nome) || empty($email)) {
        header('Location: professores.php?erro=campos_obrigatorios');
        exit();
    }

    // Atualizar dados básicos
    $stmt = $conexao->prepare("
        UPDATE usuarios SET nome_user = ?, email_user = ?, telefone_user = ?
        WHERE id = ? AND tipo_usuario = 'Professor'
    ");
    $stmt->bind_param("sssi", $nome, $email, $telefone, $id);

    if ($stmt->execute()) {
        // Atualizar especialidade
        if (!empty($especialidade)) {
            // Verificar se já existe especialidade
            $stmt = $conexao->prepare("SELECT id FROM perfis_usuario WHERE id_usuario = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();

            if ($stmt->get_result()->num_rows > 0) {
                $stmt = $conexao->prepare("UPDATE perfis_usuario SET especialidade = ? WHERE id_usuario = ?");
                $stmt->bind_param("si", $especialidade, $id);
            } else {
                $stmt = $conexao->prepare("INSERT INTO perfis_usuario (id_usuario, especialidade) VALUES (?, ?)");
                $stmt->bind_param("is", $id, $especialidade);
            }
            $stmt->execute();
        }

        registrarLog($conexao, $_SESSION['id'], 'editou_professor', "Editou dados do professor ID: $id");
        header('Location: professores.php?sucesso=edicao');
        exit();
    } else {
        header('Location: professores.php?erro=erro_edicao');
        exit();
    }
}

function excluirProfessor($conexao) {
    $id = $_POST['id_professor'] ?? 0;

    if (empty($id)) {
        header('Location: professores.php?erro=id_invalido');
        exit();
    }

    // Verificar se professor existe
    $stmt = $conexao->prepare("SELECT nome_user FROM usuarios WHERE id = ? AND tipo_usuario = 'Professor'");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        header('Location: professores.php?erro=professor_nao_encontrado');
        exit();
    }

    $professor = $result->fetch_assoc();
    $nome_professor = $professor['nome_user'];

    // Excluir especialidades
    $stmt = $conexao->prepare("DELETE FROM perfis_usuario WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Excluir relações com matérias
    $stmt = $conexao->prepare("DELETE FROM usuario_materia WHERE id_usuario = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    // Excluir professor
    $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id = ? AND tipo_usuario = 'Professor'");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        registrarLog($conexao, $_SESSION['id'], 'excluiu_professor', "Excluiu professor: $nome_professor");
        header('Location: professores.php?sucesso=exclusao');
        exit();
    } else {
        header('Location: professores.php?erro=erro_exclusao');
        exit();
    }
}

// Obter professores
$professores = [];
$result = $conexao->query("
    SELECT u.id, u.nome_user, u.email_user, u.cpf_user, u.telefone_user, u.data_criacao,
           pu.especialidade
    FROM usuarios u
    LEFT JOIN perfis_usuario pu ON u.id = pu.id_usuario
    WHERE u.tipo_usuario = 'Professor'
    ORDER BY u.nome_user
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
    <title>Professores - Sistema Acadêmico</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
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

            <li class="menu-botoes ativo" style="background-color: rgb(0, 92, 169);">
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
                        <i class="bi bi-person-workspace"></i> Gerenciamento de Professores
                    </h1>

                    <!-- Mensagens de feedback -->
                    <?php if (isset($_GET['sucesso'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="bi bi-check-circle"></i>
                        <?php
                        switch ($_GET['sucesso']) {
                            case 'cadastro': echo 'Professor cadastrado com sucesso!'; break;
                            case 'edicao': echo 'Professor atualizado com sucesso!'; break;
                            case 'exclusao': echo 'Professor excluído com sucesso!'; break;
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
                            case 'cpf_existe': echo 'Já existe um professor com este CPF!'; break;
                            case 'erro_cadastro': echo 'Erro ao cadastrar professor!'; break;
                            case 'erro_edicao': echo 'Erro ao atualizar professor!'; break;
                            case 'erro_exclusao': echo 'Erro ao excluir professor!'; break;
                            case 'professor_nao_encontrado': echo 'Professor não encontrado!'; break;
                            case 'id_invalido': echo 'ID inválido!'; break;
                        }
                        ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <!-- Formulário de Cadastro -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person-plus"></i> Cadastrar Novo Professor
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="professores.php">
                                <input type="hidden" name="acao" value="cadastrar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nome_professor" class="form-label">Nome Completo *</label>
                                            <input type="text" class="form-control" id="nome_professor" name="nome_professor" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_professor" class="form-label">E-mail *</label>
                                            <input type="email" class="form-control" id="email_professor" name="email_professor" required>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="cpf_professor" class="form-label">CPF *</label>
                                            <input type="text" class="form-control" id="cpf_professor" name="cpf_professor"
                                                   maxlength="14" placeholder="000.000.000-00" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="telefone_professor" class="form-label">Telefone</label>
                                            <input type="tel" class="form-control" id="telefone_professor" name="telefone_professor"
                                                   maxlength="15" placeholder="(00) 00000-0000">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="especialidade" class="form-label">Especialidade</label>
                                            <input type="text" class="form-control" id="especialidade" name="especialidade"
                                                   placeholder="Ex: Matemática, Português, etc.">
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Cadastrar Professor
                                </button>
                                <button type="reset" class="btn btn-secondary">
                                    <i class="bi bi-arrow-clockwise"></i> Limpar
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Professores -->
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="mb-0">
                                <i class="bi bi-people"></i> Professores Cadastrados
                                <span class="badge bg-primary ms-2"><?php echo count($professores); ?></span>
                            </h5>
                            <div class="d-flex gap-2">
                                <button class="btn btn-sm btn-outline-success" onclick="exportarProfessores()">
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
                                            <th>E-mail</th>
                                            <th>CPF</th>
                                            <th>Telefone</th>
                                            <th>Especialidade</th>
                                            <th>Cadastro</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($professores)): ?>
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">Nenhum professor cadastrado.</td>
                                        </tr>
                                        <?php else: ?>
                                            <?php foreach ($professores as $professor): ?>
                                            <tr>
                                                <td><strong><?php echo htmlspecialchars($professor['nome_user']); ?></strong></td>
                                                <td><?php echo htmlspecialchars($professor['email_user']); ?></td>
                                                <td><?php echo formatarCPF($professor['cpf_user']); ?></td>
                                                <td><?php echo htmlspecialchars($professor['telefone_user'] ?? '-'); ?></td>
                                                <td><?php echo htmlspecialchars($professor['especialidade'] ?? 'Não definida'); ?></td>
                                                <td><?php echo date('d/m/Y', strtotime($professor['data_criacao'])); ?></td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-warning" onclick="editarProfessor(<?php echo $professor['id']; ?>)">
                                                            <i class="bi bi-pencil"></i>
                                                        </button>
                                                        <button class="btn btn-outline-info" onclick="verProfessor(<?php echo $professor['id']; ?>)">
                                                            <i class="bi bi-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-danger" onclick="excluirProfessor(<?php echo $professor['id']; ?>, '<?php echo htmlspecialchars($professor['nome_user']); ?>')">
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

    <!-- Modal de Edição -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-pencil"></i> Editar Professor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" action="professores.php" id="formEditar">
                        <input type="hidden" name="acao" value="editar">
                        <input type="hidden" name="id_professor" id="edit_id">
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome Completo *</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome_professor" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">E-mail *</label>
                            <input type="email" class="form-control" id="edit_email" name="email_professor" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_telefone" class="form-label">Telefone</label>
                            <input type="tel" class="form-control" id="edit_telefone" name="telefone_professor" maxlength="15">
                        </div>
                        <div class="mb-3">
                            <label for="edit_especialidade" class="form-label">Especialidade</label>
                            <input type="text" class="form-control" id="edit_especialidade" name="especialidade">
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
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person"></i> Detalhes do Professor
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalhesProfessor">
                    <!-- Preenchido via JavaScript -->
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
        // Máscaras
        document.getElementById('cpf_professor').addEventListener('input', function() {
            let cpf = this.value.replace(/\D/g, '');
            if (cpf.length >= 3) {
                cpf = cpf.substring(0, 3) + '.' + cpf.substring(3);
            }
            if (cpf.length >= 7) {
                cpf = cpf.substring(0, 7) + '.' + cpf.substring(7);
            }
            if (cpf.length >= 11) {
                cpf = cpf.substring(0, 11) + '-' + cpf.substring(11);
            }
            this.value = cpf.substring(0, 14);
        });

        document.getElementById('telefone_professor').addEventListener('input', function() {
            let telefone = this.value.replace(/\D/g, '');
            if (telefone.length >= 2) {
                telefone = '(' + telefone.substring(0, 2) + ') ' + telefone.substring(2);
            }
            if (telefone.length >= 10) {
                telefone = telefone.substring(0, 10) + '-' + telefone.substring(10);
            }
            this.value = telefone.substring(0, 15);
        });

        document.getElementById('edit_telefone').addEventListener('input', function() {
            let telefone = this.value.replace(/\D/g, '');
            if (telefone.length >= 2) {
                telefone = '(' + telefone.substring(0, 2) + ') ' + telefone.substring(2);
            }
            if (telefone.length >= 10) {
                telefone = telefone.substring(0, 10) + '-' + telefone.substring(10);
            }
            this.value = telefone.substring(0, 15);
        });

        // Dados dos professores para edição
        const professores = <?php echo json_encode($professores); ?>;

        function editarProfessor(id) {
            const professor = professores.find(p => p.id === id);
            if (professor) {
                document.getElementById('edit_id').value = professor.id;
                document.getElementById('edit_nome').value = professor.nome_user;
                document.getElementById('edit_email').value = professor.email_user;
                document.getElementById('edit_telefone').value = professor.telefone_user || '';
                document.getElementById('edit_especialidade').value = professor.especialidade || '';

                const modal = new bootstrap.Modal(document.getElementById('modalEditar'));
                modal.show();
            }
        }

        function verProfessor(id) {
            const professor = professores.find(p => p.id === id);
            if (professor) {
                const detalhesHtml = `
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <strong>Nome:</strong><br>
                                ${professor.nome_user}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>E-mail:</strong><br>
                                ${professor.email_user}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>CPF:</strong><br>
                                ${formatarCPF(professor.cpf_user)}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Telefone:</strong><br>
                                ${professor.telefone_user || 'Não informado'}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <strong>Especialidade:</strong><br>
                                ${professor.especialidade || 'Não definida'}
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-12">
                            <div class="mb-3">
                                <strong>Data de Cadastro:</strong><br>
                                ${new Date(professor.data_criacao).toLocaleString('pt-BR')}
                            </div>
                        </div>
                    </div>
                `;

                document.getElementById('detalhesProfessor').innerHTML = detalhesHtml;
                const modal = new bootstrap.Modal(document.getElementById('modalVisualizar'));
                modal.show();
            }
        }

        function excluirProfessor(id, nome) {
            if (confirm(`Tem certeza que deseja excluir o professor "${nome}"? Esta ação não pode ser desfeita.`)) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = 'professores.php';

                const acaoInput = document.createElement('input');
                acaoInput.type = 'hidden';
                acaoInput.name = 'acao';
                acaoInput.value = 'excluir';

                const idInput = document.createElement('input');
                idInput.type = 'hidden';
                idInput.name = 'id_professor';
                idInput.value = id;

                form.appendChild(acaoInput);
                form.appendChild(idInput);
                document.body.appendChild(form);
                form.submit();
            }
        }

        function formatarCPF(cpf) {
            if (!cpf) return '';
            return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }

        function exportarProfessores() {
            window.location.href = 'exportar_professores.php';
        }
    </script>

    <?php
    function formatarCPF($cpf) {
        if (!$cpf) return '';
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
    ?>
</body>
</html>