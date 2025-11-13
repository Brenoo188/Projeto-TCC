<?php
// FUNÇÕES PARA SISTEMA DE NOTIFICAÇÕES COMPLETO

// Criar notificação automática para qualquer movimentação
function criarNotificacao($conexao, $id_usuario_destino, $titulo, $mensagem, $tipo = 'info', $id_referencia = null, $tabela_referencia = null) {
    $stmt = $conexao->prepare("INSERT INTO notificacoes (id_usuario_destino, titulo, mensagem, tipo, id_referencia, tabela_referencia, criada_em) VALUES (?, ?, ?, ?, ?, ?, NOW())");
    $stmt->bind_param("issssi", $id_usuario_destino, $titulo, $mensagem, $tipo, $id_referencia, $tabela_referencia);
    return $stmt->execute();
}

// Criar notificação para múltiplos usuários
function criarNotificacaoParaGrupo($conexao, $array_usuarios, $titulo, $mensagem, $tipo = 'info', $id_referencia = null, $tabela_referencia = null) {
    foreach ($array_usuarios as $id_usuario) {
        criarNotificacao($conexao, $id_usuario, $titulo, $mensagem, $tipo, $id_referencia, $tabela_referencia);
    }
}

// Notificar todos os administradores
function notificarAdministradores($conexao, $titulo, $mensagem, $tipo = 'info', $id_referencia = null, $tabela_referencia = null) {
    $admins = $conexao->query("SELECT id FROM usuarios WHERE tipo_usuario = 'Administrador'");
    while ($admin = $admins->fetch_assoc()) {
        criarNotificacao($conexao, $admin['id'], $titulo, $mensagem, $tipo, $id_referencia, $tabela_referencia);
    }
}

// Notificar todos os professores
function notificarProfessores($conexao, $titulo, $mensagem, $tipo = 'info', $id_referencia = null, $tabela_referencia = null) {
    $professores = $conexao->query("SELECT id FROM usuarios WHERE tipo_usuario = 'Professor'");
    while ($prof = $professores->fetch_assoc()) {
        criarNotificacao($conexao, $prof['id'], $titulo, $mensagem, $tipo, $id_referencia, $tabela_referencia);
    }
}

// Obter notificações não lidas
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

// Obter TODAS as notificações com sistema de filtros avançado
function obterTodasNotificacoes($conexao, $id_usuario, $limite = 50, $tipo_filtro = 'todos', $periodo = 'todos') {
    $sql = "
        SELECT n.*, u.nome as nome_criador,
               CASE
                   WHEN n.tabela_referencia = 'aulas' THEN 'calendario.php'
                   WHEN n.tabela_referencia = 'materias' THEN 'materias.php'
                   WHEN n.tabela_referencia = 'professores' THEN 'professores.php'
                   WHEN n.tabela_referencia = 'cadastros_int' THEN 'cadastros_int.php'
                   WHEN n.tabela_referencia = 'eventos_calendario' THEN 'calendario.php'
                   ELSE '#'
               END as link_acao
        FROM notificacoes n
        LEFT JOIN usuarios u ON n.id_usuario_criador = u.id
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

// Obter movimentações recentes do sistema (para admins)
function obterMovimentacoesSistema($conexao, $limite = 100, $tipo_filtro = 'todos', $tabela_filtro = 'todas') {
    $sql = "
        SELECT n.*, u_destino.nome_user as nome_destino, u_criador.nome_user as nome_criador,
               CASE
                   WHEN n.tabela_referencia = 'aulas' THEN 'calendario.php'
                   WHEN n.tabela_referencia = 'materias' THEN 'materias.php'
                   WHEN n.tabela_referencia = 'professores' THEN 'professores.php'
                   WHEN n.tabela_referencia = 'cadastros_int' THEN 'cadastros_int.php'
                   WHEN n.tabela_referencia = 'eventos_calendario' THEN 'calendario.php'
                   ELSE '#'
               END as link_acao
        FROM notificacoes n
        LEFT JOIN usuarios u_destino ON n.id_usuario_destino = u_destino.id
        LEFT JOIN usuarios u_criador ON n.id_usuario_criador = u_criador.id
        WHERE 1=1
    ";

    $params = [];
    $types = "";

    // Filtro por tipo de notificação
    if ($tipo_filtro !== 'todos') {
        $sql .= " AND n.tipo = ?";
        $params[] = $tipo_filtro;
        $types .= "s";
    }

    // Filtro por tabela de referência
    if ($tabela_filtro !== 'todas') {
        $sql .= " AND n.tabela_referencia = ?";
        $params[] = $tabela_filtro;
        $types .= "s";
    }

    $sql .= " ORDER BY n.criada_em DESC LIMIT ?";
    $params[] = $limite;
    $types .= "i";

    $stmt = $conexao->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    return $stmt->get_result();
}

// Marcar notificação como lida
function marcarNotificacaoComoLida($conexao, $id_notificacao, $id_usuario) {
    $stmt = $conexao->prepare("UPDATE notificacoes SET lida = 1, lida_em = NOW() WHERE id = ? AND id_usuario_destino = ?");
    $stmt->bind_param("ii", $id_notificacao, $id_usuario);
    return $stmt->execute();
}

// Marcar todas como lidas
function marcarTodasComoLidas($conexao, $id_usuario) {
    $stmt = $conexao->prepare("UPDATE notificacoes SET lida = 1, lida_em = NOW() WHERE id_usuario_destino = ? AND lida = 0");
    $stmt->bind_param("i", $id_usuario);
    return $stmt->execute();
}

// Excluir notificação
function excluirNotificacao($conexao, $id_notificacao, $id_usuario) {
    $stmt = $conexao->prepare("DELETE FROM notificacoes WHERE id = ? AND id_usuario_destino = ?");
    $stmt->bind_param("ii", $id_notificacao, $id_usuario);
    return $stmt->execute();
}

// Contar notificações não lidas
function contarNotificacoesNaoLidas($conexao, $id_usuario) {
    $stmt = $conexao->prepare("SELECT COUNT(*) as total FROM notificacoes WHERE id_usuario_destino = ? AND lida = 0");
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $resultado = $stmt->get_result();
    return $resultado->fetch_assoc()['total'];
}

// Funções específicas para notificações automáticas

function notificarNovaAula($conexao, $id_aula, $id_professor, $titulo_aula) {
    // Notificar o professor
    $professor = $conexao->prepare("SELECT nome FROM usuarios WHERE id = ?");
    $professor->bind_param("i", $id_professor);
    $professor->execute();
    $nome_professor = $professor->get_result()->fetch_assoc()['nome'];

    criarNotificacao(
        $conexao,
        $id_professor,
        "Nova Aula Cadastrada",
        "Uma nova aula '{$titulo_aula}' foi cadastrada para você.",
        'success',
        $id_aula,
        'aulas'
    );

    // Notificar administradores
    notificarAdministradores(
        $conexao,
        "Nova Aula Cadastrada",
        "O professor {$nome_professor} cadastrou uma nova aula: {$titulo_aula}",
        'info',
        $id_aula,
        'aulas'
    );
}

function notificarEdicaoAula($conexao, $id_aula, $id_professor, $titulo_aula) {
    notificarAdministradores(
        $conexao,
        "Aula Editada",
        "Uma aula foi editada: {$titulo_aula}",
        'warning',
        $id_aula,
        'aulas'
    );
}

function notificarCancelamentoAula($conexao, $id_aula, $titulo_aula) {
    notificarAdministradores(
        $conexao,
        "Aula Cancelada",
        "A aula '{$titulo_aula}' foi cancelada",
        'danger',
        $id_aula,
        'aulas'
    );
}

function notificarNovoUsuario($conexao, $id_usuario, $nome_usuario, $tipo_usuario) {
    notificarAdministradores(
        $conexao,
        "Novo {$tipo_usuario} Cadastrado",
        "Um novo {$tipo_usuario} foi cadastrado: {$nome_usuario}",
        'success',
        $id_usuario,
        'cadastros_int'
    );
}

function notificarNovaMateria($conexao, $id_materia, $nome_materia) {
    notificarProfessores(
        $conexao,
        "Nova Matéria Disponível",
        "Uma nova matéria foi adicionada ao sistema: {$nome_materia}",
        'info',
        $id_materia,
        'materias'
    );
}

function notificarEdicaoMateria($conexao, $id_materia, $nome_materia) {
    notificarAdministradores(
        $conexao,
        "Matéria Editada",
        "A matéria '{$nome_materia}' foi editada",
        'warning',
        $id_materia,
        'materias'
    );
}

function notificarExclusaoMateria($conexao, $nome_materia) {
    notificarAdministradores(
        $conexao,
        "Matéria Excluída",
        "A matéria '{$nome_materia}' foi excluída do sistema",
        'danger',
        null,
        'materias'
    );
}

function notificarEdicaoPerfil($conexao, $id_usuario, $novo_nome) {
    criarNotificacao(
        $conexao,
        $id_usuario,
        "Perfil Atualizado",
        "Seu perfil foi atualizado com sucesso",
        'success',
        null,
        null
    );
}

function notificarAlteracaoSenha($conexao, $id_usuario) {
    criarNotificacao(
        $conexao,
        $id_usuario,
        "Senha Alterada",
        "Sua senha foi alterada recentemente",
        'warning',
        null,
        null
    );
}
?>