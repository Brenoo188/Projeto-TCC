<?php
session_start();
include_once('../conexao.php');

// Se o formulário não foi enviado, volta
if (!isset($_POST['submit'])) {
    header('Location: index.php?erro=campos_vazios');
    exit();
}

$email = $_POST['email_confirm'] ?? '';
$senha = $_POST['senha_confirm'] ?? '';

if ($email === '' || $senha === '') {
    header('Location: index.php?erro=campos_vazios');
    exit();
}

// Consulta segura pelo email
$stmt = $conexao->prepare(
    "SELECT id, nome_user, email_user, senha_user, tipo_usuario FROM usuarios WHERE email_user = ? LIMIT 1"
);
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if (!$result || $result->num_rows !== 1) {
    header('Location: index.php?erro=usuario_nao_encontrado');
    exit();
}

$usuario = $result->fetch_assoc();

// Senha atual salva em texto puro no DB — comparar diretamente
if ($senha !== $usuario['senha_user']) {
    header('Location: index.php?erro=senha_incorreta');
    exit();
}

// Salva na sessão e redireciona conforme tipo
$_SESSION['id'] = $usuario['id'];
$_SESSION['nome_user'] = $usuario['nome_user'];
$_SESSION['email_user'] = $usuario['email_user'];
$_SESSION['tipo_usuario'] = $usuario['tipo_usuario'];

$tipo = $usuario['tipo_usuario'];
if ($tipo === 'Administrador') {
    header('Location: ../tela-adm/home.php');
} elseif ($tipo === 'Professor') {
    header('Location: ../tela-prof/home.php');
} else {
    header('Location: index.php?erro=tipo_invalido');
}
exit();
?>
