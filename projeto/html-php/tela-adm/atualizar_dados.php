<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_SESSION['id'];
    $nome = $_POST['nome_user'] ?? '';
    $email = $_POST['email_user'] ?? '';
    $telefone = $_POST['telefone_user'] ?? '';

    // Verificar se o email já existe para outro usuário
    $stmt = $conexao->prepare("SELECT id FROM usuarios WHERE email_user = ? AND id != ?");
    $stmt->bind_param("si", $email, $id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['mensagem'] = "Este e-mail já está cadastrado para outro usuário!";
        $_SESSION['tipo_mensagem'] = "error";
    } else {
        // Atualizar dados
        $stmt = $conexao->prepare("UPDATE usuarios SET nome_user=?, email_user=?, telefone_user=? WHERE id=?");
        $stmt->bind_param("sssi", $nome, $email, $telefone, $id);

        if ($stmt->execute()) {
            $_SESSION['mensagem'] = "Dados atualizados com sucesso!";
            $_SESSION['tipo_mensagem'] = "success";

            // Atualizar nome na sessão
            $_SESSION['nome_user'] = $nome;
        } else {
            $_SESSION['mensagem'] = "Erro ao atualizar dados!";
            $_SESSION['tipo_mensagem'] = "error";
        }
    }
}

header("Location: conta.php");
exit();
?>