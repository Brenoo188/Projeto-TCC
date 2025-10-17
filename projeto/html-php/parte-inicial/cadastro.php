<?php
if (isset($_POST['submit'])) {
    
    $nome = $_POST['nome_user'];
    $cpf = $_POST['cpf_user'];
    $email = $_POST['email_user'];
    $telefone = $_POST['telefone_user'];
    $senha = $_POST['senha_user'];
    $confirma_senha = $_POST['Csenha_user'];
    $tipo_usuario = $_POST['tipo_usuario'];


    $campos_vazios = false;
    //echo $nome; echo $cpf; echo $email; echo $telefone; echo $senha; echo $confirma_senha; echo $tipo_usuario; 

if (empty(trim($nome)) || empty(trim($cpf)) || empty(trim($email)) || empty(trim($telefone)) || empty(trim($senha)) || empty(trim($confirma_senha)) || empty(trim($tipo_usuario))) {
    echo "<script>alert('Por favor, preencha todos os campos!');</script>";
} elseif ($senha !== $confirma_senha) {
    echo "<script>alert('As senhas não conferem!');</script>";
} else {
   
}

        
        $senha_hash = password_hash($senha, PASSWORD_DEFAULT);

      
        include_once('../conexao.php');

        
        $sql = "INSERT INTO Usuario (nome, cpf, email, telefone, senha, tipo_usuario) 
        VALUES (?, ?, ?, ?, ?, ?)";
        // Prepara a instrução SQL para evitar SQL Injection
        $stmt = mysqli_prepare($conexao, $sql);

        $stmt = mysqli_prepare($conexao, $sql);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "ssssss", $nome, $cpf, $email, $telefone, $senha_hash, $tipo_usuario);

         if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Cadastro realizado com sucesso!'); window.location.href='index.php';</script>";
        } else {
        echo "<script>alert('Erro ao cadastrar: " . mysqli_stmt_error($stmt) . "');</script>";
        }

            mysqli_stmt_close($stmt);
        } else {
         echo "<script>alert('Erro na preparação da query: " . mysqli_error($conexao) . "');</script>";
}


    }

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastro</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="../../css/css-inicial/estilocadas.css">
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

            <form method="post" action="">
                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="nome_user" class="form-label"><strong>Nome</strong></label>
                    <input type="text" class="form-control" id="nome_user" name="nome_user" 
                           placeholder="Insira seu nome" value="<?php echo isset($_POST['nome_user']) ? $_POST['nome_user'] : ''; ?>">
                </div>

                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="cpf_user" class="form-label"><strong>CPF</strong></label>
                    <input type="text" class="form-control" id="cpf_user" name="cpf_user" 
                           placeholder="Insira seu CPF" maxlength="11" pattern="\d{11}" 
                           title="Digite exatamente 11 números" value="<?php echo isset($_POST['cpf_user']) ? $_POST['cpf_user'] : ''; ?>">
                </div>

                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="email_user" class="form-label"><strong>E-mail</strong></label>
                    <input type="email" class="form-control" id="email_user" name="email_user" 
                           placeholder="Insira seu E-mail" value="<?php echo isset($_POST['email_user']) ? $_POST['email_user'] : ''; ?>">
                </div>

                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="telefone_user" class="form-label"><strong>Telefone</strong></label>
                    <input type="text" class="form-control" id="telefone_user" name="telefone_user" 
                           placeholder="Insira seu Telefone" value="<?php echo isset($_POST['telefone_user']) ? $_POST['telefone_user'] : ''; ?>">
                </div>

                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="senha_user" class="form-label"><strong>Senha</strong></label>
                    <input type="password" class="form-control" id="senha_user" name="senha_user" 
                           placeholder="Insira sua senha">
                </div>

                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="Csenha_user" class="form-label"><strong>Confirme sua Senha</strong></label>
                    <input type="password" class="form-control" id="Csenha_user" name="Csenha_user" 
                           placeholder="Confirme sua senha">
                </div>

                <h3 style="text-align: center;"><strong>Dado Profissional</strong></h3><br>

                <select class="form-select" name="tipo_usuario" aria-label="Tipo de usuário" 
                        style="width: 350px; margin: 10px;">
                    <option value="">Selecione</option>
                    <option value="Professor" <?php echo (isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] == 'Professor') ? 'selected' : ''; ?>>Professor</option>
                    <option value="Administrador" <?php echo (isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                </select>

                <br>
                <button type="submit" name="submit" class="btn btn-outline-dark" style="width: 350px; margin: 10px;">Enviar</button>

                <div class="pergunta-conta">
                    Já tem uma conta? <a href="index.php" style="margin: 10px;">Fazer login</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>