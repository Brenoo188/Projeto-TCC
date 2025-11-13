<?php
// Função para registrar logs de atividades
function registrarLog($conexao, $id_usuario, $acao, $descricao = null) {
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $user_agent = $_SERVER['HTTP_USER_AGENT'] ?? 'unknown';

    $stmt = $conexao->prepare("
        INSERT INTO logs_atividades (id_usuario, acao, descricao, ip_address, user_agent)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issss", $id_usuario, $acao, $descricao, $ip_address, $user_agent);
    $stmt->execute();
}

// Função para obter logs recentes de um usuário
function obterLogsUsuario($conexao, $id_usuario, $limite = 10) {
    $stmt = $conexao->prepare("
        SELECT acao, descricao, data_hora, ip_address
        FROM logs_atividades
        WHERE id_usuario = ?
        ORDER BY data_hora DESC
        LIMIT ?
    ");
    $stmt->bind_param("ii", $id_usuario, $limite);
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    return $logs;
}

// Função para obter todos os logs (para administradores)
function obterTodosLogs($conexao, $limite = 50) {
    $stmt = $conexao->prepare("
        SELECT la.*, u.nome_user, u.email_user
        FROM logs_atividades la
        JOIN usuarios u ON la.id_usuario = u.id
        ORDER BY la.data_hora DESC
        LIMIT ?
    ");
    $stmt->bind_param("i", $limite);
    $stmt->execute();
    $result = $stmt->get_result();

    $logs = [];
    while ($row = $result->fetch_assoc()) {
        $logs[] = $row;
    }
    return $logs;
}

// Função para registrar login
function registrarLogin($conexao, $id_usuario) {
    registrarLog($conexao, $id_usuario, 'login', 'Usuário fez login no sistema');
}

// Função para registrar logout
function registrarLogout($conexao, $id_usuario) {
    registrarLog($conexao, $id_usuario, 'logout', 'Usuário fez logout do sistema');
}

// Função para registrar cadastro
function registrarCadastro($conexao, $id_usuario, $tipo_usuario) {
    registrarLog($conexao, $id_usuario, 'cadastro', "Novo usuário cadastrado como $tipo_usuario");
}

// Função para registrar alteração de senha
function registrarAlteracaoSenha($conexao, $id_usuario) {
    registrarLog($conexao, $id_usuario, 'alteracao_senha', 'Usuário alterou sua senha');
}

// Função para registrar recuperação de senha
function registrarRecuperacaoSenha($conexao, $id_usuario) {
    registrarLog($conexao, $id_usuario, 'recuperacao_senha', 'Usuário solicitou recuperação de senha');
}

// Função para registrar acesso a página restrita
function registrarAcessoPagina($conexao, $id_usuario, $pagina) {
    registrarLog($conexao, $id_usuario, 'acesso_pagina', "Acessou a página: $pagina");
}

// Função para registrar criação de evento
function registrarCriacaoEvento($conexao, $id_usuario, $titulo_evento) {
    registrarLog($conexao, $id_usuario, 'criacao_evento', "Criou evento: $titulo_evento");
}

// Função para registrar alteração de perfil
function registrarAlteracaoPerfil($conexao, $id_usuario) {
    registrarLog($conexao, $id_usuario, 'alteracao_perfil', 'Usuário alterou dados do perfil');
}
?>