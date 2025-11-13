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

$termo_busca = $_GET['busca'] ?? '';
$pagina = $_GET['pagina'] ?? 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

$usuarios = [];
$total_resultados = 0;

if ($termo_busca) {
    // Buscar usuários com o termo
    $termo = "%" . $termo_busca . "%";
    $stmt = $conexao->prepare("
        SELECT id, nome_user, email_user, cpf_user, telefone_user, tipo_usuario
        FROM usuarios
        WHERE nome_user LIKE ? OR email_user LIKE ? OR cpf_user LIKE ?
        ORDER BY nome_user
        LIMIT ? OFFSET ?
    ");
    $stmt->bind_param("sssii", $termo, $termo, $termo, $limite, $offset);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $usuarios[] = $row;
    }

    // Contar total de resultados
    $stmt = $conexao->prepare("
        SELECT COUNT(*) as total FROM usuarios
        WHERE nome_user LIKE ? OR email_user LIKE ? OR cpf_user LIKE ?
    ");
    $stmt->bind_param("sss", $termo, $termo, $termo);
    $stmt->execute();
    $total_resultados = $stmt->get_result()->fetch_assoc()['total'];

    // Registrar busca no log
    registrarLog($conexao, $_SESSION['id'], 'busca_usuario', "Buscou por: '$termo_busca'");
}

$total_paginas = ceil($total_resultados / $limite);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Usuários</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/buscar.css">
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
    <main class="conteudo-buscar">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">
                        <i class="bi bi-search"></i> Buscar Usuários
                    </h1>

                    <!-- Formulário de Busca -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <form method="GET" action="buscar-usuarios.php">
                                <div class="row align-items-end">
                                    <div class="col-md-8">
                                        <label for="busca" class="form-label">Buscar por nome, e-mail ou CPF</label>
                                        <div class="input-group">
                                            <span class="input-group-text">
                                                <i class="bi bi-search"></i>
                                            </span>
                                            <input type="text" class="form-control" id="busca" name="busca"
                                                   value="<?php echo htmlspecialchars($termo_busca); ?>"
                                                   placeholder="Digite o termo de busca..."
                                                   autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="bi bi-search"></i> Buscar
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    <?php if ($termo_busca): ?>
                    <!-- Resultados da Busca -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                Resultados da busca: "<?php echo htmlspecialchars($termo_busca); ?>"
                                <span class="badge bg-secondary ms-2"><?php echo $total_resultados; ?> encontrado(s)</span>
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php if (empty($usuarios)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-search" style="font-size: 3rem; color: #6c757d;"></i>
                                <h6 class="mt-3 text-muted">Nenhum usuário encontrado</h6>
                                <p class="text-muted">Tente usar outros termos para a busca.</p>
                            </div>
                            <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
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
                                        <?php foreach ($usuarios as $usuario): ?>
                                        <tr>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="usuario-avatar me-3">
                                                        <i class="bi bi-person-circle"></i>
                                                    </div>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($usuario['nome_user']); ?></strong>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($usuario['email_user']); ?></td>
                                            <td><?php echo formatarCPF($usuario['cpf_user']); ?></td>
                                            <td><?php echo htmlspecialchars($usuario['telefone_user'] ?? '-'); ?></td>
                                            <td>
                                                <span class="badge <?php echo $usuario['tipo_usuario'] === 'Administrador' ? 'bg-danger' : 'bg-primary'; ?>">
                                                    <?php echo htmlspecialchars($usuario['tipo_usuario']); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <div class="btn-group btn-group-sm" role="group">
                                                    <button type="button" class="btn btn-outline-primary"
                                                            onclick="verDetalhes(<?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-eye"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-warning"
                                                            onclick="editarUsuario(<?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-pencil"></i>
                                                    </button>
                                                    <button type="button" class="btn btn-outline-danger"
                                                            onclick="excluirUsuario(<?php echo $usuario['id']; ?>)">
                                                        <i class="bi bi-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Paginação -->
                            <?php if ($total_paginas > 1): ?>
                            <nav aria-label="Paginação de resultados">
                                <ul class="pagination justify-content-center">
                                    <?php if ($pagina > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?busca=<?php echo urlencode($termo_busca); ?>&pagina=<?php echo $pagina - 1; ?>">
                                            <i class="bi bi-chevron-left"></i> Anterior
                                        </a>
                                    </li>
                                    <?php endif; ?>

                                    <?php for ($i = max(1, $pagina - 2); $i <= min($total_paginas, $pagina + 2); $i++): ?>
                                    <li class="page-item <?php echo $i == $pagina ? 'active' : ''; ?>">
                                        <a class="page-link" href="?busca=<?php echo urlencode($termo_busca); ?>&pagina=<?php echo $i; ?>">
                                            <?php echo $i; ?>
                                        </a>
                                    </li>
                                    <?php endfor; ?>

                                    <?php if ($pagina < $total_paginas): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="?busca=<?php echo urlencode($termo_busca); ?>&pagina=<?php echo $pagina + 1; ?>">
                                            Próxima <i class="bi bi-chevron-right"></i>
                                        </a>
                                    </li>
                                    <?php endif; ?>
                                </ul>
                            </nav>
                            <?php endif; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </main>

    <!-- Modal de Detalhes -->
    <div class="modal fade" id="modalDetalhes" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">
                        <i class="bi bi-person-info"></i> Detalhes do Usuário
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="detalhesUsuario">
                    <!-- Será preenchido via JavaScript -->
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
        // Função para formatar CPF
        function formatarCPF(cpf) {
            if (!cpf) return '-';
            return cpf.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
        }

        // Função para ver detalhes do usuário
        function verDetalhes(idUsuario) {
            // Simulação - em produção faria uma requisição AJAX
            const usuarios = <?php echo json_encode($usuarios); ?>;
            const usuario = usuarios.find(u => u.id === idUsuario);

            if (usuario) {
                const detalhesHtml = `
                    <div class="usuario-detalhes">
                        <div class="text-center mb-3">
                            <div class="usuario-avatar-lg">
                                <i class="bi bi-person-circle"></i>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nome:</strong><br>
                                ${usuario.nome_user}
                            </div>
                            <div class="col-md-6">
                                <strong>E-mail:</strong><br>
                                ${usuario.email_user}
                            </div>
                        </div>

                        <hr>

                        <div class="row">
                            <div class="col-md-6">
                                <strong>CPF:</strong><br>
                                ${formatarCPF(usuario.cpf_user)}
                            </div>
                            <div class="col-md-6">
                                <strong>Telefone:</strong><br>
                                ${usuario.telefone_user || 'Não informado'}
                            </div>
                        </div>

                        <hr>

                        <div class="text-center">
                            <strong>Tipo de Usuário:</strong><br>
                            <span class="badge ${usuario.tipo_usuario === 'Administrador' ? 'bg-danger' : 'bg-primary'}">
                                ${usuario.tipo_usuario}
                            </span>
                        </div>
                    </div>
                `;

                document.getElementById('detalhesUsuario').innerHTML = detalhesHtml;
                const modal = new bootstrap.Modal(document.getElementById('modalDetalhes'));
                modal.show();
            }
        }

        // Função para editar usuário (placeholder)
        function editarUsuario(idUsuario) {
            alert('Funcionalidade de edição em desenvolvimento. ID: ' + idUsuario);
        }

        // Função para excluir usuário (placeholder)
        function excluirUsuario(idUsuario) {
            if (confirm('Tem certeza que deseja excluir este usuário? Esta ação não pode ser desfeita.')) {
                alert('Funcionalidade de exclusão em desenvolvimento. ID: ' + idUsuario);
            }
        }

        // Auto-complete da busca (simulação)
        const buscaInput = document.getElementById('busca');
        if (buscaInput) {
            let timeout;
            buscaInput.addEventListener('input', function() {
                clearTimeout(timeout);
                const termo = this.value.trim();

                if (termo.length >= 3) {
                    timeout = setTimeout(() => {
                        // Aqui poderia implementar busca via AJAX
                        console.log('Buscando por:', termo);
                    }, 300);
                }
            });
        }

        // Foco automático no campo de busca
        if (buscaInput && !buscaInput.value) {
            buscaInput.focus();
        }
    </script>

    <?php
    // Função auxiliar para formatar CPF
    function formatarCPF($cpf) {
        if (!$cpf) return '-';
        return preg_replace('/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $cpf);
    }
    ?>
</body>
</html>