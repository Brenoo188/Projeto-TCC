<?php
session_start();
include_once('../conexao.php');

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
                cadastrarUsuario($conexao);
                break;
            case 'editar':
                editarUsuario($conexao);
                break;
            case 'excluir':
                excluirUsuario($conexao);
                break;
        }
    }
}

function cadastrarUsuario($conexao) {
    $nome = $_POST['nome_user'] ?? '';
    $email = $_POST['email_user'] ?? '';
    $cpf = $_POST['cpf_user'] ?? '';
    $telefone = $_POST['telefone_user'] ?? '';
    $senha = password_hash('senha123', PASSWORD_DEFAULT); // Senha padrão
    $tipo = $_POST['tipo_usuario'] ?? 'Professor';

    if ($nome && $email && $cpf) {
        $stmt = $conexao->prepare("INSERT INTO usuarios (nome_user, email_user, cpf_user, telefone_user, senha_user, tipo_usuario) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssss", $nome, $email, $cpf, $telefone, $senha, $tipo);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Usuário cadastrado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao cadastrar usuário!";
            $_SESSION['tipo_mensagem'] = "danger";
        }
    }

    header("Location: cadastros_int.php");
    exit();
}

function editarUsuario($conexao) {
    $id = $_POST['id'] ?? 0;
    $nome = $_POST['nome_user'] ?? '';
    $email = $_POST['email_user'] ?? '';
    $cpf = $_POST['cpf_user'] ?? '';
    $telefone = $_POST['telefone_user'] ?? '';
    $tipo = $_POST['tipo_usuario'] ?? 'Professor';

    if ($id && $nome && $email && $cpf) {
        $stmt = $conexao->prepare("UPDATE usuarios SET nome_user=?, email_user=?, cpf_user=?, telefone_user=?, tipo_usuario=? WHERE id=?");
        $stmt->bind_param("sssssi", $nome, $email, $cpf, $telefone, $tipo, $id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Usuário atualizado com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar usuário!";
            $_SESSION['tipo_mensagem'] = "error";
        }
    }

    header("Location: cadastros_int.php");
    exit();
}

function excluirUsuario($conexao) {
    $id = $_POST['id'] ?? 0;

    if ($id > 1) { // Impede excluir o admin principal
        $stmt = $conexao->prepare("DELETE FROM usuarios WHERE id=?");
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Usuário excluído com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";
        } else {
            $_SESSION['mensagem'] = "Erro ao excluir usuário!";
            $_SESSION['tipo_mensagem'] = "error";
        }
    }

    header("Location: cadastros_int.php");
    exit();
}

// Buscar usuários
$usuarios = $conexao->query("SELECT * FROM usuarios ORDER BY nome_user");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro de Usuários - Sistema Acadêmico</title>
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
            <li class="menu-botoes ativo">
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
                        <i class="bi bi-person-plus"></i> Cadastro de Usuários
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

                    <!-- Formulário de Cadastro -->
                    <div class="card mb-4">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-person-fill-add"></i> Novo Usuário
                            </h5>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="cadastros_int.php">
                                <input type="hidden" name="acao" value="cadastrar">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="nome_user" class="form-label">Nome Completo *</label>
                                            <input type="text" class="form-control" id="nome_user" name="nome_user" required>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="email_user" class="form-label">E-mail *</label>
                                            <input type="email" class="form-control" id="email_user" name="email_user" required>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="cpf_user" class="form-label">CPF *</label>
                                            <input type="text" class="form-control" id="cpf_user" name="cpf_user" required>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="telefone_user" class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="telefone_user" name="telefone_user">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="mb-3">
                                            <label for="tipo_usuario" class="form-label">Tipo de Usuário *</label>
                                            <select class="form-select" id="tipo_usuario" name="tipo_usuario" required>
                                                <option value="Professor">Professor</option>
                                                <option value="Administrador">Administrador</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-person-plus"></i> Cadastrar Usuário
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Lista de Usuários -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-people"></i> Usuários Cadastrados
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Nome</th>
                                            <th>E-mail</th>
                                            <th>CPF</th>
                                            <th>Telefone</th>
                                            <th>Tipo</th>
                                            <th>Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php while ($usuario = $usuarios->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($usuario['nome_user']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['email_user']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['cpf_user']); ?></td>
                                                <td><?php echo htmlspecialchars($usuario['telefone_user'] ?? '-'); ?></td>
                                                <td>
                                                    <span class="badge bg-<?php echo $usuario['tipo_usuario'] == 'Administrador' ? 'danger' : 'primary'; ?>">
                                                        <?php echo $usuario['tipo_usuario']; ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <?php if ($usuario['id'] > 1): ?>
                                                        <button class="btn btn-sm btn-outline-danger" onclick="excluirUsuario(<?php echo $usuario['id']; ?>)">
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal Editar Usuário -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="cadastros_int.php">
                    <input type="hidden" name="acao" value="editar">
                    <input type="hidden" id="edit_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Usuário</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="edit_nome" class="form-label">Nome Completo</label>
                            <input type="text" class="form-control" id="edit_nome" name="nome_user" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_email" class="form-label">E-mail</label>
                            <input type="email" class="form-control" id="edit_email" name="email_user" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_cpf" class="form-label">CPF</label>
                            <input type="text" class="form-control" id="edit_cpf" name="cpf_user" required>
                        </div>
                        <div class="mb-3">
                            <label for="edit_telefone" class="form-label">Telefone</label>
                            <input type="text" class="form-control" id="edit_telefone" name="telefone_user">
                        </div>
                        <div class="mb-3">
                            <label for="edit_tipo" class="form-label">Tipo de Usuário</label>
                            <select class="form-select" id="edit_tipo" name="tipo_usuario" required>
                                <option value="Professor">Professor</option>
                                <option value="Administrador">Administrador</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Excluir -->
    <div class="modal fade" id="modalExcluir" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="cadastros_int.php">
                    <input type="hidden" name="acao" value="excluir">
                    <input type="hidden" id="delete_id" name="id">
                    <div class="modal-header">
                        <h5 class="modal-title">Confirmar Exclusão</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p>Tem certeza que deseja excluir este usuário?</p>
                        <p class="text-danger"><strong>Esta ação não poderá ser desfeita!</strong></p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-danger">Excluir</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Dados dos usuários para edição
        const usuarios = <?php echo json_encode($conexao->query("SELECT * FROM usuarios")->fetch_all(MYSQLI_ASSOC)); ?>;

        function editarUsuario(id) {
            const usuario = usuarios.find(u => u.id == id);
            if (usuario) {
                document.getElementById('edit_id').value = usuario.id;
                document.getElementById('edit_nome').value = usuario.nome_user;
                document.getElementById('edit_email').value = usuario.email_user;
                document.getElementById('edit_cpf').value = usuario.cpf_user;
                document.getElementById('edit_telefone').value = usuario.telefone_user || '';
                document.getElementById('edit_tipo').value = usuario.tipo_usuario;

                new bootstrap.Modal(document.getElementById('modalEditar')).show();
            }
        }

        function excluirUsuario(id) {
            document.getElementById('delete_id').value = id;
            new bootstrap.Modal(document.getElementById('modalExcluir')).show();
        }
    </script>
</body>
</html>