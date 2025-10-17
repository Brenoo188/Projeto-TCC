<?php


if (isset($_POST['submit'])) {
    // Coleta os dados do formulário através do atributo 'name'
    $nome = $_POST['nome_usuario'];
    $cpf = $_POST['cpf_usuario'];
    $email = $_POST['email_usuario'];
    $telefone = $_POST['telefone_usuario'];
    $senha = $_POST['senha_usuario'];
    $confirma_senha = $_POST['Csenha_usuario'];
    $tipo_usuario = $_POST['tipo_usuario']; 

    // Validação básica de campos obrigatórios
    if (empty($nome) || empty($cpf) || empty($email) || empty($telefone) || empty($senha) || empty($confirma_senha)) {
        echo "<script>alert('Por favor, preencha todos os campos!');</script>";
    } elseif ($senha !== $confirma_senha) {
        echo "<script>alert('As senhas não conferem!');</script>";
    } else {
 

    
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

        
        include_once('../assets/bd/conexao.php');

      
        $sql = "INSERT INTO Usuario (nome, cpf, email, telefone, senha, tipo_usuario) VALUES ('$nome', '$cpf', '$email', '$telefone', '$senha_hash', '$tipo_usuario')";
        $result = mysqli_query($conexao, $sql);

        if($result){
            echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='index.php';</script>";
        } else {
            echo "<script>alert('Erro ao cadastrar!');</script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/css-inicial/estilocadas.css">
    <title>Cadastro</title>
</head>
<body>
    <div class="container-login">
        <div class="ld-esq">
            <h1 style="color: rgb(255, 255, 255);">Bem-vindo!!</h1>
            <p style="color: white; margin-left: 50px;">Por favor, cadastra-se para continuar</p>
        </div>
        <div class="ld-dir">
            <div class="caixa_int">
                <h3 style="text-align: center;"><strong>Dados Pessoais</strong></h3><br>
                <!-- Formulário de cadastro -->
                <form method="post" action="">
                    <div class="mb-3" style="width: 350px; margin: 10px ;">
                        <label for="nome_user" class="form-label"><strong>Nome</strong></label>
                        <input type="text" class="form-control" id="nome_user" name="nome_user" placeholder="Insira seu nome">
                    </div>
                    <div class="mb-3" style="width: 350px; margin: 10px ;">
                        <label for="cpf_user" class="form-label"><strong>CPF</strong></label>
                        <input type="text" class="form-control" id="cpf_user" name="cpf_user" placeholder="Insira seu CPF">   
                    </div>
                    <div class="mb-3" style="width: 350px; margin: 10px ;">
                        <label for="email_user" class="form-label"><strong>E-mail</strong></label>
                        <input type="email" class="form-control" id="email_user" name="email_user" placeholder="Insira seu E-mail">
                    </div>
                    <div class="mb-3" style="width: 350px; margin: 10px ;">
                        <label for="telefone_user" class="form-label"><strong>Telefone</strong></label>
                        <input type="text" class="form-control" id="telefone_user" name="telefone_user" placeholder="Insira seu Telefone">
                    </div>
                    <div class="mb-3" style="width: 350px; margin: 10px ;">
                        <label for="senha_user" class="form-label"><strong>Senha</strong></label>
                        <input type="password" class="form-control" id="senha_user" name="senha_user" placeholder="Insira sua senha">
                    </div>
                    <div class="mb-3" style="width: 350px; margin: 10px ;">
                        <label for="Csenha_user" class="form-label"><strong>Confirme sua Senha</strong></label>
                        <input type="password" class="form-control" id="Csenha_user" name="Csenha_user" placeholder="Confirme sua senha">
                    </div>
                    <br>
                    <h3 style="text-align: center;"><strong>Dado Profissional</strong></h3><br>
                    <select class="form-select" name="tipo_usuario" aria-label="Default select example">
                        <option selected value="">Selecione</option>
                        <option value="Professor">Professor</option>
                        <option value="Administrador">Administrador</option>
                    </select>
                    <br>
                    <button type="submit" name="submit" class="btn btn-outline-dark" style="width: 350px; margin: 10px;">Enviar</button>
                    <br><br><br>
                    <div class="pergunta-conta">
                        Já tem uma conta? <a href="index.php" style="margin: 10px;">Fazer login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>