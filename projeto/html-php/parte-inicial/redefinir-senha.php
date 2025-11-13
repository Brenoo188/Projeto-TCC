<?php
if (isset($_GET['token'])) {
    include_once('../conexao.php');

    $token = $_GET['token'];
    $agora = date('Y-m-d H:i:s');

    // Verificar se o token é válido e não expirou
    $stmt = $conexao->prepare("
        SELECT rs.id_usuario, u.email_user
        FROM recuperacao_senha rs
        JOIN usuarios u ON rs.id_usuario = u.id
        WHERE rs.token = ? AND rs.expiracao > ? AND rs.utilizado = 0
        LIMIT 1
    ");
    $stmt->bind_param("ss", $token, $agora);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows !== 1) {
        echo "<script>alert('Link de redefinição inválido ou expirado.'); window.location.href='index.php';</script>";
        exit();
    }

    $recuperacao = $result->fetch_assoc();

    if (isset($_POST['submit'])) {
        $nova_senha = $_POST['nova_senha'];
        $confirmar_senha = $_POST['confirmar_senha'];

        // Validações de senha forte
        if (strlen($nova_senha) < 8) {
            echo "<script>alert('A senha deve ter pelo menos 8 caracteres.'); window.location.href='redefinir-senha.php?token=" . $token . "';</script>";
            exit();
        }
        if (!preg_match('/[A-Z]/', $nova_senha)) {
            echo "<script>alert('A senha deve conter pelo menos uma letra maiúscula.'); window.location.href='redefinir-senha.php?token=" . $token . "';</script>";
            exit();
        }
        if (!preg_match('/[a-z]/', $nova_senha)) {
            echo "<script>alert('A senha deve conter pelo menos uma letra minúscula.'); window.location.href='redefinir-senha.php?token=" . $token . "';</script>";
            exit();
        }
        if (!preg_match('/\d/', $nova_senha)) {
            echo "<script>alert('A senha deve conter pelo menos um número.'); window.location.href='redefinir-senha.php?token=" . $token . "';</script>";
            exit();
        }
        if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $nova_senha)) {
            echo "<script>alert('A senha deve conter pelo menos um caractere especial.'); window.location.href='redefinir-senha.php?token=" . $token . "';</script>";
            exit();
        }

        if ($nova_senha !== $confirmar_senha) {
            echo "<script>alert('As senhas não coincidem.'); window.location.href='redefinir-senha.php?token=" . $token . "';</script>";
            exit();
        }

        // Atualizar senha
        $senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);
        $stmt = $conexao->prepare("UPDATE usuarios SET senha_user = ? WHERE id = ?");
        $stmt->bind_param("si", $senha_hash, $recuperacao['id_usuario']);

        if ($stmt->execute()) {
            // Marcar token como utilizado
            $stmt = $conexao->prepare("UPDATE recuperacao_senha SET utilizado = 1 WHERE token = ?");
            $stmt->bind_param("s", $token);
            $stmt->execute();

            echo "<script>alert('Senha redefinida com sucesso!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Erro ao redefinir senha. Tente novamente.'); window.location.href='redefinir-senha.php?token=" . $token . "';</script>";
        }
    }
} else {
    header('Location: index.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redefinir Senha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/css-inicial/estilocadas.css">
</head>
<body>

<div class="container-login">
    <div class="ld-esq">
        <h1 style="color: rgb(255, 255, 255);">Redefinir Senha</h1>
        <p style="color: white; margin-left: 50px;">Digite sua nova senha</p>
    </div>

    <div class="ld-dir">
        <div class="caixa_int">
            <h3 style="text-align: center;"><strong>Nova Senha</strong></h3><br>

            <form method="post" action="redefinir-senha.php?token=<?php echo htmlspecialchars($_GET['token']); ?>">
                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="nova_senha" class="form-label"><strong>Nova Senha</strong></label>
                    <input type="password" class="form-control" id="nova_senha" name="nova_senha"
                           placeholder="Digite sua nova senha" required>
                    <div class="small text-muted">Mínimo 8 caracteres, incluindo maiúsculas, minúsculas, números e caracteres especiais.</div>
                </div>

                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="confirmar_senha" class="form-label"><strong>Confirmar Nova Senha</strong></label>
                    <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha"
                           placeholder="Confirme sua nova senha" required>
                </div>

                <button type="submit" name="submit" class="btn btn-outline-dark" style="width: 350px; margin: 10px;">Redefinir Senha</button>

                <div class="pergunta-conta">
                    <a href="index.php" style="margin: 10px;">Voltar para o login</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>