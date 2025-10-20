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

                <form method="POST" action="logica_login.php">

                    <div class="mb-3" style="width: 350px; margin: 10px;">

                        <label for="emailLO" class="form-label"><strong>E-mail</strong></label>
                        <input type="email" class="form-control" id="emailLO" name="email_confirm" placeholder="Insira seu email" required>

                    </div>

                    <div class="mb-3" style="width: 350px; margin: 10px;">

                        <label for="senhaLO" class="form-label"><strong>Senha</strong></label>
                        <input type="password" class="form-control" id="senhaLO" name="senha_confirm" placeholder="Digite sua senha" required>

                    </div>

                    <br>

                    <button type="submit" class="btn btn-outline-dark" name="submit" style="width: 350px; margin: 10px;">Entrar</button>

                    <br>
                    <br>
                    <br>

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