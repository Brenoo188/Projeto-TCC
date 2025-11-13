<?php
session_start();
include_once('../conexao.php');
include_once('../funcoes_logs.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

if ($_SESSION['tipo_usuario'] !== 'Administrador' && $_SESSION['tipo_usuario'] !== 'Professor') {
    header('Location: ../parte-inicial/index.php?erro=acesso_negado');
    exit();
}

$id_usuario = $_SESSION['id'];
$mensagem = '';
$tipo_mensagem = '';

// Processar upload de foto
if (isset($_POST['upload_foto'])) {
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $arquivo = $_FILES['foto_perfil'];
        $nome_arquivo = $arquivo['name'];
        $tipo_arquivo = $arquivo['type'];
        $tamanho_arquivo = $arquivo['size'];
        $nome_temporario = $arquivo['tmp_name'];

        // Validar tipo de arquivo
        $tipos_permitidos = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($tipo_arquivo, $tipos_permitidos)) {
            $mensagem = 'Apenas arquivos JPG, PNG ou GIF são permitidos.';
            $tipo_mensagem = 'danger';
        } elseif ($tamanho_arquivo > 5 * 1024 * 1024) { // 5MB
            $mensagem = 'O arquivo não pode ser maior que 5MB.';
            $tipo_mensagem = 'danger';
        } else {
            // Criar diretório se não existir
            $diretorio_upload = '../../uploads/perfis/';
            if (!file_exists($diretorio_upload)) {
                mkdir($diretorio_upload, 0777, true);
            }

            // Gerar nome único para o arquivo
            $extensao = pathinfo($nome_arquivo, PATHINFO_EXTENSION);
            $novo_nome = 'perfil_' . $id_usuario . '_' . time() . '.' . $extensao;
            $caminho_completo = $diretorio_upload . $novo_nome;

            if (move_uploaded_file($nome_temporario, $caminho_completo)) {
                // Salvar no banco de dados
                $stmt = $conexao->prepare("
                    INSERT INTO perfis_usuario (id_usuario, foto_perfil)
                    VALUES (?, ?)
                    ON DUPLICATE KEY UPDATE foto_perfil = VALUES(foto_perfil)
                ");
                $stmt->bind_param("is", $id_usuario, $novo_nome);

                if ($stmt->execute()) {
                    $mensagem = 'Foto de perfil atualizada com sucesso!';
                    $tipo_mensagem = 'success';
                    registrarAlteracaoPerfil($conexao, $id_usuario);
                } else {
                    $mensagem = 'Erro ao salvar foto no banco de dados.';
                    $tipo_mensagem = 'danger';
                }
            } else {
                $mensagem = 'Erro ao fazer upload do arquivo.';
                $tipo_mensagem = 'danger';
            }
        }
    }
}

// Processar atualização do perfil
if (isset($_POST['atualizar_perfil'])) {
    $biografia = $_POST['biografia'] ?? '';
    $especialidade = $_POST['especialidade'] ?? '';
    $formacao = $_POST['formacao'] ?? '';

    $stmt = $conexao->prepare("
        INSERT INTO perfis_usuario (id_usuario, biografia, especialidade, formacao)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE
        biografia = VALUES(biografia),
        especialidade = VALUES(especialidade),
        formacao = VALUES(formacao)
    ");
    $stmt->bind_param("issss", $id_usuario, $biografia, $especialidade, $formacao);

    if ($stmt->execute()) {
        $mensagem = 'Perfil atualizado com sucesso!';
        $tipo_mensagem = 'success';
        registrarAlteracaoPerfil($conexao, $id_usuario);
    } else {
        $mensagem = 'Erro ao atualizar perfil.';
        $tipo_mensagem = 'danger';
    }
}

// Obter dados do perfil
$stmt = $conexao->prepare("
    SELECT u.nome_user, u.email_user, u.telefone_user, u.tipo_usuario,
           p.foto_perfil, p.biografia, p.especialidade, p.formacao
    FROM usuarios u
    LEFT JOIN perfis_usuario p ON u.id = p.id_usuario
    WHERE u.id = ?
");
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$result = $stmt->get_result();
$perfil = $result->fetch_assoc();

// Determinar qual interface usar baseado no tipo de usuário
$caminho_base = $_SESSION['tipo_usuario'] === 'Administrador' ? '../tela-adm' : '../tela-prof';
$arquivo_css = $_SESSION['tipo_usuario'] === 'Administrador' ? '../../css/css_telaadm' : '../../css/css-prof';
$arquivo_js = $_SESSION['tipo_usuario'] === 'Administrador' ? '../../Javascript/js-telaadm' : '../../Javascript/js-telaprof';
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil Completo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="<?php echo $arquivo_css; ?>/perfil.css">
</head>
<body>

    <!-- Menu Lateral -->
    <nav class="menu-lateral">
        <div class="btn-expandir" id="btn-expan" style="padding-right: 0px;">
            <i class="bi bi-list"></i>
        </div>

        <ul>
            <li class="menu-botoes">
                <a href="<?php echo $caminho_base; ?>/home.php">
                    <span class="icon"><i class="bi bi-house"></i></span>
                    <span class="txt-link">home</span>
                </a>
            </li>

            <?php if ($_SESSION['tipo_usuario'] === 'Administrador'): ?>
            <li class="menu-botoes">
                <a href="<?php echo $caminho_base; ?>/calendario.php">
                    <span class="icon"><i class="bi bi-calendar"></i></span>
                    <span class="txt-link">Calendário</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="<?php echo $caminho_base; ?>/cadastros_int.php">
                    <span class="icon"><i class="bi bi-people"></i></span>
                    <span class="txt-link">Cadastros</span>
                </a>
            </li>

      
            <li class="menu-botoes">
                <a href="<?php echo $caminho_base; ?>/configuracao.php">
                    <span class="icon"><i class="bi bi-gear"></i></span>
                    <span class="txt-link">Configuração</span>
                </a>
            </li>
            <?php else: ?>
            <li class="menu-botoes">
                <a href="<?php echo $caminho_base; ?>/calendario.php">
                    <span class="icon"><i class="bi bi-calendar"></i></span>
                    <span class="txt-link">Calendário</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="<?php echo $caminho_base; ?>/configuracao.php">
                    <span class="icon"><i class="bi bi-gear"></i></span>
                    <span class="txt-link">Configuração</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="menu-botoes">
                <a href="<?php echo $caminho_base; ?>/conta.php" style="background-color: rgb(0, 92, 169)">
                    <span class="icon"><i class="bi bi-person-circle"></i></span>
                    <span class="txt-link">Conta</span>
                </a>
            </li>
        </ul>
    </nav>

    <!-- Conteúdo Principal -->
    <main class="conteudo-perfil">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <h1 class="mb-4">Meu Perfil</h1>

                    <?php if ($mensagem): ?>
                    <div class="alert alert-<?php echo $tipo_mensagem; ?> alert-dismissible fade show" role="alert">
                        <?php echo $mensagem; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Foto de Perfil -->
                        <div class="col-md-4">
                            <div class="card perfil-foto">
                                <div class="card-body text-center">
                                    <?php if ($perfil['foto_perfil']): ?>
                                        <img src="../../uploads/perfis/<?php echo htmlspecialchars($perfil['foto_perfil']); ?>"
                                             class="rounded-circle foto-perfil-img" alt="Foto de perfil">
                                    <?php else: ?>
                                        <div class="foto-placeholder rounded-circle">
                                            <i class="bi bi-person-circle"></i>
                                        </div>
                                    <?php endif; ?>

                                    <h5 class="mt-3"><?php echo htmlspecialchars($perfil['nome_user']); ?></h5>
                                    <p class="text-muted"><?php echo htmlspecialchars($perfil['tipo_usuario']); ?></p>

                                    <form method="post" enctype="multipart/form-data" class="mt-3">
                                        <div class="mb-3">
                                            <label for="foto_perfil" class="form-label">Alterar Foto</label>
                                            <input type="file" class="form-control" id="foto_perfil" name="foto_perfil"
                                                   accept="image/*">
                                            <small class="text-muted">JPG, PNG ou GIF - Máx. 5MB</small>
                                        </div>
                                        <button type="submit" name="upload_foto" class="btn btn-primary btn-sm">
                                            <i class="bi bi-upload"></i> Upload
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Informações do Perfil -->
                        <div class="col-md-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="mb-0">Informações do Perfil</h5>
                                </div>
                                <div class="card-body">
                                    <form method="post">
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="nome" class="form-label">Nome</label>
                                                <input type="text" class="form-control" id="nome"
                                                       value="<?php echo htmlspecialchars($perfil['nome_user']); ?>" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="email" class="form-label">E-mail</label>
                                                <input type="email" class="form-control" id="email"
                                                       value="<?php echo htmlspecialchars($perfil['email_user']); ?>" readonly>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="telefone" class="form-label">Telefone</label>
                                                <input type="text" class="form-control" id="telefone"
                                                       value="<?php echo htmlspecialchars($perfil['telefone_user'] ?? ''); ?>" readonly>
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="tipo_usuario" class="form-label">Tipo de Usuário</label>
                                                <input type="text" class="form-control" id="tipo_usuario"
                                                       value="<?php echo htmlspecialchars($perfil['tipo_usuario']); ?>" readonly>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="mb-3">
                                            <label for="biografia" class="form-label">Biografia</label>
                                            <textarea class="form-control" id="biografia" name="biografia"
                                                      rows="3"><?php echo htmlspecialchars($perfil['biografia'] ?? ''); ?></textarea>
                                        </div>

                                        <?php if ($perfil['tipo_usuario'] === 'Professor'): ?>
                                        <div class="row">
                                            <div class="col-md-6 mb-3">
                                                <label for="especialidade" class="form-label">Especialidade</label>
                                                <input type="text" class="form-control" id="especialidade" name="especialidade"
                                                       value="<?php echo htmlspecialchars($perfil['especialidade'] ?? ''); ?>"
                                                       placeholder="Ex: Matemática, Física">
                                            </div>
                                            <div class="col-md-6 mb-3">
                                                <label for="formacao" class="form-label">Formação</label>
                                                <input type="text" class="form-control" id="formacao" name="formacao"
                                                       value="<?php echo htmlspecialchars($perfil['formacao'] ?? ''); ?>"
                                                       placeholder="Ex: Graduação em...">
                                            </div>
                                        </div>
                                        <?php endif; ?>

                                        <button type="submit" name="atualizar_perfil" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Atualizar Perfil
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo $arquivo_js; ?>/menu.js"></script>
</body>
</html>