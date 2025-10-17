<?php
// Inicia a sessão global para salvar dados do usuário após login
session_start();

// Conecta ao banco de dados
$conexao = mysqli_connect('localhost', 'root', '', 'bd_TCC');
// Verifica se a conexão foi realizada com sucesso
if (!$conexao) {
    // Redireciona para a página de login com mensagem de erro
    header("Location: login.html?erro=Erro ao conectar ao banco");
    exit();
}

// Verifica se o formulário está sendo enviado via método POST
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verifica se os campos de email e senha estão preenchidos
    if (empty($email) || empty($password)) {
        
        header("Location: login.html?erro=Preencha todos os campos");
        exit();
    }

    // Busca o usuário no banco, pelo email
    $query = "SELECT id_usuario, email, senha, tipo_usuario FROM Usuario WHERE email = '$email'";
    $resultado = mysqli_query($conexao, $query);
    $usuario = mysqli_fetch_assoc($resultado);

    // Verifica se o usuário existe e se a senha digitada está correta
    if ($usuario && password_verify($password, $usuario['senha'])) {
        // Salva os dados do usuário na sessão
        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

       
        header("Location: ../assets/int_site/home.php");
        exit();
    } else {

        header("Location: login.html?erro=E-mail ou senha incorretos");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
  
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
 
    <link rel="stylesheet" href="../../css/css-inicial/estilolog.css">
    <title>login</title>
</head>

<body>
    <div class="container-login">
        <div class="ld-esq">

            <h1 style="color: rgb(255, 255, 255);">Bem-vindo de volta!</h1>
            <p style="color: white; margin-left: 50px;">Por favor, faça login para continuar.</p>
        </div>
        <div class="ld-dir">
            <div class="caixa_int">

                <form method="post" action="">
                    <div class="mb-3" style="width: 350px; margin: 10px;">
                        <label for="emailLO" class="form-label"><strong>E-mail</strong></label>

                        <input type="email" class="form-control" id="emailLO" name="email" placeholder="Insira seu email" required>
                    </div>
                    <div class="mb-3" style="width: 350px; margin: 10px;">
                        <label for="senhaLO" class="form-label"><strong>Senha</strong></label>

                        <input type="password" class="form-control" id="senhaLO" name="password" placeholder="Digite sua senha" required>
                    </div>
                    <br>

                    <button type="submit" class="btn btn-outline-dark" style="width: 350px; margin: 10px;">Entrar</button>
                    <br><br><br>

                    <div class="pergunta-conta">
                        Não tem uma conta?
                        <a href="../parte-inicial/cadastro.php">criar conta</a>
                    </div>
            </div>
            </form>
        </div>
    </div>
</body>

</html>