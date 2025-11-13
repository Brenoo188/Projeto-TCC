<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id'];

    // Coletar preferências do formulário
    $tema = $_POST['tema'] ?? 'claro';
    $idioma = $_POST['idioma'] ?? 'pt-br';
    $notificar_email = isset($_POST['notificar_email']) ? 1 : 0;
    $som_notificacoes = isset($_POST['som_notificacoes']) ? 1 : 0;

    // Atualizar ou inserir preferências na tabela de configurações do sistema
    $configuracoes = [
        'tema_usuario' => $tema,
        'idioma_usuario' => $idioma,
        'notificar_email' => $notificar_email,
        'som_notificacoes' => $som_notificacoes
    ];

    foreach ($configuracoes as $chave => $valor) {
        $stmt = $conexao->prepare("UPDATE configuracoes_sistema SET valor = ? WHERE chave = ?");
        $stmt->bind_param("ss", $valor, $chave);
        $stmt->execute();
    }

    $_SESSION['mensagem'] = "Preferências atualizadas com sucesso!";
    $_SESSION['tipo_mensagem'] = "success";

    // Atualizar variáveis de sessão se necessário
    $_SESSION['tema'] = $tema;
    $_SESSION['idioma'] = $idioma;
}

header("Location: configuracao.php");
exit();
?>