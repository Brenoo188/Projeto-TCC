<?php
if (isset($_POST['submit'])) {
    include_once('../conexao.php');

    $email = $_POST['email'];
    $token = bin2hex(random_bytes(32));
    $expiracao = date('Y-m-d H:i:s', strtotime('+1 hour'));

    // Verificar se o e-mail existe no banco de dados
    $stmt = $conexao->prepare("SELECT id, nome_user FROM usuarios WHERE email_user = ? LIMIT 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

        // Salvar token no banco (criar tabela de recuperação se não existir)
        $stmt = $conexao->prepare("
            INSERT INTO recuperacao_senha (id_usuario, token, expiracao)
            VALUES (?, ?, ?)
        ");
        $stmt->bind_param("iss", $usuario['id'], $token, $expiracao);
        $stmt->execute();

        // Enviar e-mail (simulação - em produção usar PHPMailer ou similar)
        $link_redefinicao = "http://localhost/Projeto-TCC/projeto/html-php/parte-inicial/redefinir-senha.php?token=" . $token;

        echo "<script>
            alert('Enviamos um link de redefinição para seu e-mail: " . $email . "');
            alert('Link de redefinição (em desenvolvimento): " . $link_redefinicao . "');
            window.location.href='index.php';
        </script>";
    } else {
        echo "<script>alert('E-mail não encontrado em nosso sistema.'); window.location.href='recuperar-senha.php';</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/css-inicial/estilocadas.css">
</head>
<body>

<div class="container-login">
    <div class="ld-esq">
        <h1 style="color: rgb(255, 255, 255);">Recuperar Senha</h1>
        <p style="color: white; margin-left: 50px;">Digite seu e-mail para receber as instruções</p>
    </div>

    <div class="ld-dir">
        <div class="caixa_int">
            <h3 style="text-align: center;"><strong>Recuperação de Senha</strong></h3><br>

            <form method="post" action="recuperar-senha.php">
                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="email" class="form-label"><strong>E-mail</strong></label>
                    <input type="email" class="form-control" id="email" name="email"
                           placeholder="Digite seu e-mail cadastrado" required>
                </div>

                <button type="submit" name="submit" class="btn btn-outline-dark" style="width: 350px; margin: 10px;">Enviar Link de Recuperação</button>

                <div class="pergunta-conta">
                    Lembrou sua senha? <a href="index.php" style="margin: 10px;">Fazer login</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>