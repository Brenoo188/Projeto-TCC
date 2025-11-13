<?php
session_start();
include_once('../conexao.php');

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

// Processar ações
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao'])) {
    $id_usuario = $_SESSION['id'];

    switch ($_POST['acao']) {
        case 'cadastrar':
            $titulo = $_POST['titulo'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $data_inicio = $_POST['data_inicio'] ?? '';
            $data_fim = $_POST['data_fim'] ?? '';
            $tipo_evento = $_POST['tipo_evento'] ?? 'outro';
            $cor = $_POST['cor'] ?? '#007bff';
            $local = $_POST['local'] ?? '';

            if ($titulo && $data_inicio) {
                $stmt = $conexao->prepare("
                    INSERT INTO eventos_calendario
                    (id_usuario_criador, titulo, descricao, data_inicio, data_fim, tipo_evento, cor, local)
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $stmt->bind_param("isssssss", $id_usuario, $titulo, $descricao, $data_inicio, $data_fim, $tipo_evento, $cor, $local);

                if ($stmt->execute()) {
                    $_SESSION['mensagem'] = "Evento criado com sucesso!";
                    $_SESSION['tipo_mensagem'] = "success";
                } else {
                    $_SESSION['mensagem'] = "Erro ao criar evento!";
                    $_SESSION['tipo_mensagem'] = "error";
                }
            }
            header("Location: calendario.php");
            exit();
            break;

        case 'editar':
            $id = $_POST['id'] ?? 0;
            $titulo = $_POST['titulo'] ?? '';
            $descricao = $_POST['descricao'] ?? '';
            $data_inicio = $_POST['data_inicio'] ?? '';
            $data_fim = $_POST['data_fim'] ?? '';
            $tipo_evento = $_POST['tipo_evento'] ?? 'outro';
            $cor = $_POST['cor'] ?? '#007bff';
            $local = $_POST['local'] ?? '';

            if ($id && $titulo && $data_inicio) {
                $stmt = $conexao->prepare("
                    UPDATE eventos_calendario
                    SET titulo=?, descricao=?, data_inicio=?, data_fim=?, tipo_evento=?, cor=?, local=?
                    WHERE id=? AND id_usuario_criador=?
                ");
                $stmt->bind_param("ssssssssi", $titulo, $descricao, $data_inicio, $data_fim, $tipo_evento, $cor, $local, $id, $id_usuario);

                if ($stmt->execute()) {
                    $_SESSION['mensagem'] = "Evento atualizado com sucesso!";
                    $_SESSION['tipo_mensagem'] = "success";
                } else {
                    $_SESSION['mensagem'] = "Erro ao atualizar evento!";
                    $_SESSION['tipo_mensagem'] = "error";
                }
            }
            header("Location: calendario.php");
            exit();
            break;

        case 'excluir':
            $id = $_POST['id'] ?? 0;

            if ($id > 0) {
                // Verificar se o usuário é dono do evento ou admin
                if ($_SESSION['tipo_usuario'] === 'Administrador') {
                    $stmt = $conexao->prepare("DELETE FROM eventos_calendario WHERE id=?");
                    $stmt->bind_param("i", $id);
                } else {
                    $stmt = $conexao->prepare("DELETE FROM eventos_calendario WHERE id=? AND id_usuario_criador=?");
                    $stmt->bind_param("ii", $id, $id_usuario);
                }

                if ($stmt->execute()) {
                    $_SESSION['mensagem'] = "Evento excluído com sucesso!";
                    $_SESSION['tipo_mensagem'] = "success";
                } else {
                    $_SESSION['mensagem'] = "Erro ao excluir evento!";
                    $_SESSION['tipo_mensagem'] = "error";
                }
            }
            header("Location: calendario.php");
            exit();
            break;
    }
}

// Se for GET, redirecionar para o calendário
header("Location: calendario.php");
exit();
?>