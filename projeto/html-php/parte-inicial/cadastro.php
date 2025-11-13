<?php
if (isset($_POST['submit'])) {
    include_once('../conexao.php');
    include_once('../funcoes_logs.php');

    $nome_usuario = $_POST['nome_user'];
    $cpf_usuario = $_POST['cpf_user'];
    $email_usuario = $_POST['email_user']; 
    $telefone_usuario = $_POST['telefone_user'];
    $senha_usuario = $_POST['senha_user'];
    $confirmar_senha = $_POST['Csenha_user'];
    $tipo_usuario = $_POST['tipo_usuario'];

    // Validação de senha forte
    if (strlen($senha_usuario) < 8) {
        echo "<script>alert('A senha deve ter pelo menos 8 caracteres.'); window.location.href='cadastro.php';</script>";
        exit();
    }
    if (!preg_match('/[A-Z]/', $senha_usuario)) {
        echo "<script>alert('A senha deve conter pelo menos uma letra maiúscula.'); window.location.href='cadastro.php';</script>";
        exit();
    }
    if (!preg_match('/[a-z]/', $senha_usuario)) {
        echo "<script>alert('A senha deve conter pelo menos uma letra minúscula.'); window.location.href='cadastro.php';</script>";
        exit();
    }
    if (!preg_match('/\d/', $senha_usuario)) {
        echo "<script>alert('A senha deve conter pelo menos um número.'); window.location.href='cadastro.php';</script>";
        exit();
    }
    if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $senha_usuario)) {
        echo "<script>alert('A senha deve conter pelo menos um caractere especial.'); window.location.href='cadastro.php';</script>";
        exit();
    }

    // Verificar se as senhas coincidem
    if ($senha_usuario !== $confirmar_senha) {
        echo "<script>alert('As senhas não coincidem.'); window.location.href='cadastro.php';</script>";
        exit();
    }

    // Verifica se o tipo é administrador
    if ($tipo_usuario === 'Administrador') {
        $verifica = $conexao->prepare("SELECT COUNT(*) as total FROM usuarios WHERE tipo_usuario = 'Administrador'");
        $verifica->execute();
        $resultado = $verifica->get_result()->fetch_assoc();

        if ($resultado['total'] >= 1) {
            echo "<script>alert('Já existe um administrador cadastrado.'); window.location.href='cadastro.php';</script>";
            exit();
        }
    }

    // Criptografa a senha
    $senha_hash = password_hash($senha_usuario, PASSWORD_DEFAULT);

    // Inserção segura com prepared statement
    $stmt = $conexao->prepare("
        INSERT INTO usuarios (nome_user, cpf_user, email_user, telefone_user, senha_user, tipo_usuario) 
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("ssssss", $nome_usuario, $cpf_usuario, $email_usuario, $telefone_usuario, $senha_hash, $tipo_usuario);

    if ($stmt->execute()) {
        // Obter o ID do usuário recém-criado
        $id_usuario = $conexao->insert_id;

        // Registrar cadastro no log de atividades
        registrarCadastro($conexao, $id_usuario, $tipo_usuario);

        echo "<script>alert('Usuário cadastrado com sucesso!'); window.location.href='index.php';</script>";
    } else {
        echo "<script>alert('Erro ao cadastrar usuário.');</script>";
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
    <script src="../Javascript/js-parteinicial/validacao-senhas.js"></script>
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

            <form method="post" action="cadastro.php">
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
                           placeholder="Insira sua senha" required>
                    <div id="senha-feedback" class="small mt-1"></div>
                </div>

                <div class="mb-3" style="width: 350px; margin: 10px;">
                    <label for="Csenha_user" class="form-label"><strong>Confirme sua Senha</strong></label>
                    <input type="password" class="form-control" id="Csenha_user" name="Csenha_user"
                           placeholder="Confirme sua senha" required>
                    <div id="confirm-feedback" class="small mt-1"></div>
                </div>

                <h3 style="text-align: center;"><strong>Dado Profissional</strong></h3><br>

                <select class="form-select" name="tipo_usuario" aria-label="Tipo de usuário" 
                        style="width: 350px; margin: 10px;">
                    <option value="">Selecione</option>
                    <option value="Professor" <?php echo (isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] == 'Professor') ? 'selected' : ''; ?>>Professor</option>
                    <option value="Administrador" <?php echo (isset($_POST['tipo_usuario']) && $_POST['tipo_usuario'] == 'Administrador') ? 'selected' : ''; ?>>Administrador</option>
                </select>

                <br>
                <button type="submit" name="submit" class="btn btn-outline-dark" style="width: 350px; margin: 10px;"
                        onclick="return validarFormularioCadastro()">Enviar</button>

                <div class="pergunta-conta">
                    Já tem uma conta? <a href="index.php" style="margin: 10px;">Fazer login</a>
                </div>
            </form>
        </div>
    </div>
</div>

</body>
</html>