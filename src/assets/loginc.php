<?php
// Iniciar a sessão (variavel global sessão)
//$_SESSION: Salva o ID, e-mail e tipo de usuário para uso em outras páginas
session_start();

// Conectar ao banco de dados
$conexao = mysqli_connect('localhost', 'root', '', 'bd_TCC');

if (!$conexao) {
    header("Location: login.html?erro=Erro ao conectar ao banco");
    exit();
}

// Verificar se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    // Verificar se os campos estão preenchidos
    if (empty($email) || empty($password)) {
        header("Location: login.html?erro=Preencha todos os campos");
        exit();
    }

    // Buscar o usuário no banco
    $query = "SELECT id_usuario, email, senha, tipo_usuario FROM Usuario WHERE email = '$email'";
    $resultado = mysqli_query($conexao, $query);
    $usuario = mysqli_fetch_assoc($resultado);

    // Verificar se o usuário existe e a senha está correta
    if ($usuario && password_verify($password, $usuario['senha'])) {
        // Salvar dados na sessão
        $_SESSION['user_id'] = $usuario['id_usuario'];
        $_SESSION['email'] = $usuario['email'];
        $_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];
        header("Location: ../assets/int_site/home.php");
        exit();
    } else {
        header("Location: login.html?erro=E-mail ou senha incorretos");
        exit();
    }
} else {
    header("Location: login.html?erro=Acesso inválido");
    exit();
}
?>