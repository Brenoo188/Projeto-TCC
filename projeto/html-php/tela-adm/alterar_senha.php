<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_SESSION['id'];
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';

    // Verificar se a senha atual está correta
    $stmt = $conexao->prepare("SELECT senha_user FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();

    if (password_verify($senha_atual, $usuario['senha_user'])) {
        // Verificar se as novas senhas coincidem
        if ($nova_senha === $confirmar_senha) {
            if (strlen($nova_senha) >= 6) {
                $nova_senha_hash = password_hash($nova_senha, PASSWORD_DEFAULT);

                $stmt = $conexao->prepare("UPDATE usuarios SET senha_user=? WHERE id=?");
                $stmt->bind_param("si", $nova_senha_hash, $id);

                if ($stmt->execute()) {
                    $_SESSION['mensagem'] = "Senha alterada com sucesso!";
                    $_SESSION['tipo_mensagem'] = "success";
                } else {
                    $_SESSION['mensagem'] = "Erro ao alterar senha!";
                    $_SESSION['tipo_mensagem'] = "error";
                }
            } else {
                $_SESSION['mensagem'] = "A nova senha deve ter pelo menos 6 caracteres!";
                $_SESSION['tipo_mensagem'] = "error";
            }
        } else {
            $_SESSION['mensagem'] = "As senhas não conferem!";
            $_SESSION['tipo_mensagem'] = "error";
        }
    } else {
        $_SESSION['mensagem'] = "Senha atual incorreta!";
        $_SESSION['tipo_mensagem'] = "error";
    }
}

header("Location: conta.php");
exit();
?>