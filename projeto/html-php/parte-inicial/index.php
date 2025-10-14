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
<script></script>

    <div class="container-login">
        <div class="ld-esq">
            <a href="index.html"><button class="btn-voltar"></button></a>

            <h1 style="color: rgb(255, 255, 255);">Bem-vindo de volta!</h1>
            <p style="color: white; margin-left: 50px;">Por favor, faça login para continuar.</p>

        </div>
        <div class="ld-dir">
            <div class="caixa_int">
            <form method="post" action="login.html">
                <div class="mb-3" style="width: 350px; margin: 10px ;">
                    <label for="emailLO" class="form-label"><strong>E-mail</strong></label>
                    <input type="mail" class="form-control" id="emailLO" placeholder="Insira seu email">
                </div>
                <div class="mb-3" style="width: 350px; margin: 10px ;">
                    <label for="SenhaLO" class="form-label"><strong>Senha</strong></label>
                    <input type="password" class="form-control" id="senhaLO" placeholder="Digite sua senha">
                </div>
                <br>
                <button type="button" class="btn btn-outline-dark" style="width: 350px; margin: 10px ;"
                    onclick="window.location.href='../assets/int_site/home.html'">Entrar</button>

                <br>
                <br>
                <br>

                <div class="pergunta-conta">
                    Não tem uma conta?
                    <a href="../tela-adm/home.html">criar conta</a>
                </div>
                </div>
            </form>
        </div>
    </div>
</body>

</html>
