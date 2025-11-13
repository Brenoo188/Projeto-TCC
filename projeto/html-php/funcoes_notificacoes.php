<?php
// Função para criar notificação
function criarNotificacao($conexao, $id_usuario_destino, $titulo, $mensagem, $tipo = 'info', $id_usuario_remetente = null) {
    $stmt = $conexao->prepare("
        INSERT INTO notificacoes (id_usuario_destino, id_usuario_remetente, titulo, mensagem, tipo)
        VALUES (?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("iisss", $id_usuario_destino, $id_usuario_remetente, $titulo, $mensagem, $tipo);
    return $stmt->execute();
}

// Função para obter notificações não lidas de um usuário
function obterNotificacoesNaoLidas($conexao, $id_usuario, $limite = 10) {
    $stmt = $conexao->prepare("
        SELECT n.*,
               CASE
                   WHEN n.tabela_referencia = 'aulas' THEN 'calendario.php'
                   WHEN n.tabela_referencia = 'materias' THEN 'materias.php'
                   WHEN n.tabela_referencia = 'professores' THEN 'professores.php'
                   WHEN n.tabela_referencia = 'cadastros_int' THEN 'cadastros_int.php'
                   WHEN n.tabela_referencia = 'eventos_calendario' THEN 'calendario.php'
                   ELSE '#'
               END as link_acao
        FROM notificacoes n
        WHERE n.id_usuario_destino = ? AND n.lida = 0
        ORDER BY n.criada_em DESC
        LIMIT ?
    ");
    $stmt->bind_param("ii", $id_usuario, $limite);
    $stmt->execute();
    return $stmt->get_result();
}

// Função para obter todas as notificações de um usuário
function obterTodasNotificacoes($conexao, $id_usuario, $limite = 50, $tipo_filtro = 'todos', $periodo = 'todos') {
    $sql = "
        SELECT n.*,
               CASE
                   WHEN n.tabela_referencia = 'aulas' THEN 'calendario.php'
                   WHEN n.tabela_referencia = 'materias' THEN 'materias.php'
                   WHEN n.tabela_referencia = 'professores' THEN 'professores.php'
                   WHEN n.tabela_referencia = 'cadastros_int' THEN 'cadastros_int.php'
                   WHEN n.tabela_referencia = 'eventos_calendario' THEN 'calendario.php'
                   ELSE '#'
               END as link_acao
        FROM notificacoes n
        WHERE n.id_usuario_destino = ?
    ";

    $params = [$id_usuario];
    $types = "i";

    // Filtro por tipo
    if ($tipo_filtro !== 'todos') {
        $sql .= " AND n.tipo = ?";
        $params[] = $tipo_filtro;
        $types .= "s";
    }

    // Filtro por período
    if ($periodo !== 'todos') {
        switch ($periodo) {
            case 'hoje':
                $sql .= " AND DATE(n.criada_em) = CURDATE()";
                break;
            case 'semana':
                $sql .= " AND n.criada_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case 'mes':
                $sql .= " AND n.criada_em >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
        }
    }

    $sql .= " ORDER BY n.criada_em DESC LIMIT ?";
    $params[] = $limite;
    $types .= "i";

    $stmt = $conexao->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}

// Função para marcar notificação como lida
function marcarNotificacaoComoLida($conexao, $id_notificacao, $id_usuario) {
    $stmt = $conexao->prepare("
        UPDATE notificacoes
        SET lida = 1, data_leitura = NOW()
        WHERE id = ? AND id_usuario_destino = ?
    ");
    $stmt->bind_param("ii", $id_notificacao, $id_usuario);
    return $stmt->execute();
}

// Função para marcar todas as notificações como lidas
function marcarTodasComoLidas($conexao, $id_usuario) {
    $stmt = $conexao->prepare("
        UPDATE notificacoes
        SET lida = 1, data_leitura = NOW()
        WHERE id_usuario_destino = ? AND lida = 0
    ");
    $stmt->bind_param("i", $id_usuario);
    return $stmt->execute();
}

// Função para contar notificações não lidas
function contarNotificacoesNaoLidas($conexao, $id_usuario) {
    $stmt = $conexao->prepare("
        SELECT COUNT(*) as total
        FROM notificacoes
        WHERE id_usuario_destino = ? AND lida = 0
    ");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    return $row['total'];
}

// Função para excluir notificação
function excluirNotificacao($conexao, $id_notificacao, $id_usuario) {
    $stmt = $conexao->prepare("
        DELETE FROM notificacoes
        WHERE id = ? AND id_usuario_destino = ?
    ");
    $stmt->bind_param("ii", $id_notificacao, $id_usuario);
    return $stmt->execute();
}

// Funções de notificação específicas
function notificarNovoUsuario($conexao, $id_usuario_destino, $nome_usuario) {
    criarNotificacao(
        $conexao,
        $id_usuario_destino,
        'Novo Usuário Cadastrado',
        "O usuário $nome_usuario foi cadastrado no sistema.",
        'info'
    );
}

function notificarAlteracaoSenha($conexao, $id_usuario) {
    criarNotificacao(
        $conexao,
        $id_usuario,
        'Senha Alterada',
        'Sua senha foi alterada com sucesso.',
        'sucesso'
    );
}

function notificarNovoEvento($conexao, $id_usuario_destino, $titulo_evento, $id_usuario_criador) {
    criarNotificacao(
        $conexao,
        $id_usuario_destino,
        'Novo Evento',
        "Um novo evento foi criado: $titulo_evento",
        'info',
        $id_usuario_criador
    );
}

function notificarLembreteEvento($conexao, $id_usuario_destino, $titulo_evento, $data_evento) {
    criarNotificacao(
        $conexao,
        $id_usuario_destino,
        'Lembrete de Evento',
        "O evento '$titulo_evento' acontecerá em breve: $data_evento",
        'aviso'
    );
}

function notificarAcessoIncomum($conexao, $id_usuario, $ip_address) {
    criarNotificacao(
        $conexao,
        $id_usuario,
        'Acesso Incomum Detectado',
        "Detectamos um acesso de um novo endereço IP: $ip_address",
        'erro'
    );
}

function notificarBackupRealizado($conexao, $id_usuario_destino) {
    criarNotificacao(
        $conexao,
        $id_usuario_destino,
        'Backup Realizado',
        'O backup do banco de dados foi realizado com sucesso.',
        'sucesso'
    );
}
?>