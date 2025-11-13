<?php
// Estatísticas das notificações
$estatisticas = [
    'nao_lidas' => contarNotificacoesNaoLidas($conexao, $id_usuario),
    'totais' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE id_usuario_destino = $id_usuario")->fetch_assoc()['total'],
    'hoje' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE id_usuario_destino = $id_usuario AND DATE(criada_em) = CURDATE()")->fetch_assoc()['total'],
    'semana' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE id_usuario_destino = $id_usuario AND criada_em >= DATE_SUB(NOW(), INTERVAL 7 DAY)")->fetch_assoc()['total']
];

$notificacoes_nao_lidas = obterNotificacoesNaoLidas($conexao, $id_usuario, 10);
$todas_notificacoes = obterTodasNotificacoes($conexao, $id_usuario, $limite, $tipo_filtro, $periodo_filtro);
?>

<!-- Estatísticas -->
<div class="row mb-4">
    <div class="col-md-3 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas['nao_lidas']; ?></div>
            <div class="text-muted">Não Lidas</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas['hoje']; ?></div>
            <div class="text-muted">Hoje</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas['semana']; ?></div>
            <div class="text-muted">Esta Semana</div>
        </div>
    </div>
    <div class="col-md-3 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas['totais']; ?></div>
            <div class="text-muted">Total</div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="filtro-section">
    <div class="row">
        <div class="col-md-4">
            <label class="form-label">Filtrar por Tipo:</label>
            <select class="form-select filtro-select" name="tipo_filtro">
                <option value="todos" <?php echo $tipo_filtro === 'todos' ? 'selected' : ''; ?>>Todos os Tipos</option>
                <option value="info" <?php echo $tipo_filtro === 'info' ? 'selected' : ''; ?>>ℹ️ Informações</option>
                <option value="success" <?php echo $tipo_filtro === 'success' ? 'selected' : ''; ?>>✅ Sucesso</option>
                <option value="warning" <?php echo $tipo_filtro === 'warning' ? 'selected' : ''; ?>>⚠️ Avisos</option>
                <option value="danger" <?php echo $tipo_filtro === 'danger' ? 'selected' : ''; ?>>❌ Erros</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Filtrar por Período:</label>
            <select class="form-select filtro-select" name="periodo_filtro">
                <option value="todos" <?php echo $periodo_filtro === 'todos' ? 'selected' : ''; ?>>Todo o Período</option>
                <option value="hoje" <?php echo $periodo_filtro === 'hoje' ? 'selected' : ''; ?>>📅 Hoje</option>
                <option value="semana" <?php echo $periodo_filtro === 'semana' ? 'selected' : ''; ?>>📆 Últimos 7 dias</option>
                <option value="mes" <?php echo $periodo_filtro === 'mes' ? 'selected' : ''; ?>>🗓️ Últimos 30 dias</option>
            </select>
        </div>
        <div class="col-md-4">
            <label class="form-label">Resultados por página:</label>
            <select class="form-select filtro-select" name="limite">
                <option value="20" <?php echo $limite == 20 ? 'selected' : ''; ?>>20</option>
                <option value="50" <?php echo $limite == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $limite == 100 ? 'selected' : ''; ?>>100</option>
            </select>
        </div>
    </div>

    <?php if ($estatisticas['nao_lidas'] > 0): ?>
    <div class="row mt-3">
        <div class="col-12">
            <a href="notificacoes.php?acao=marcar_todas_lidas" class="btn btn-light btn-sm">
                <i class="bi bi-check-all"></i> Marcar Todas como Lidas
            </a>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- Notificações Não Lidas -->
<?php if ($notificacoes_nao_lidas->num_rows > 0): ?>
<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">
            <i class="bi bi-envelope"></i> Notificações Não Lidas (<?php echo $notificacoes_nao_lidas->num_rows; ?>)
        </h5>
    </div>
    <div class="card-body p-0">
        <?php while ($notificacao = $notificacoes_nao_lidas->fetch_assoc()): ?>
        <div class="notificacao-card notificacao-nao-lida p-3"
             data-id="<?php echo $notificacao['id']; ?>"
             data-lida="<?php echo $notificacao['lida']; ?>"
             style="cursor: pointer;">
            <div class="row">
                <div class="col-md-1 text-center">
                    <div class="mt-2">
                        <?php
                        $icones = [
                            'info' => 'bi-info-circle text-info',
                            'success' => 'bi-check-circle text-success',
                            'warning' => 'bi-exclamation-triangle text-warning',
                            'danger' => 'bi-x-circle text-danger'
                        ];
                        $icone = $icones[$notificacao['tipo']] ?? 'bi-info-circle text-info';
                        ?>
                        <i class="bi <?php echo $icone; ?>" style="font-size: 1.5rem;"></i>
                    </div>
                </div>
                <div class="col-md-8">
                    <h6 class="mb-1"><?php echo htmlspecialchars($notificacao['titulo']); ?></h6>
                    <p class="mb-1 text-muted"><?php echo htmlspecialchars($notificacao['mensagem']); ?></p>
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notificacao['criada_em'])); ?>
                        <?php if ($notificacao['link_acao'] && $notificacao['link_acao'] !== '#'): ?>
                        | <a href="<?php echo $notificacao['link_acao']; ?>" class="text-decoration-none">
                            <i class="bi bi-arrow-right-circle"></i> Ver Detalhes
                        </a>
                        <?php endif; ?>
                    </small>
                </div>
                <div class="col-md-3 text-end">
                    <span class="badge bg-<?php echo $notificacao['tipo']; ?> badge-tipo">
                        <?php echo ucfirst($notificacao['tipo']); ?>
                    </span>
                    <div class="mt-2">
                        <a href="notificacoes.php?acao=marcar_lida&id=<?php echo $notificacao['id']; ?>"
                           class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-check"></i> Marcar Lida
                        </a>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
</div>
<?php endif; ?>

<!-- Todas as Notificações -->
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history"></i> Todas as Notificações
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if ($todas_notificacoes->num_rows > 0): ?>
            <?php while ($notificacao = $todas_notificacoes->fetch_assoc()): ?>
            <div class="notificacao-card notificacao-<?php echo $notificacao['tipo']; ?> p-3 <?php echo $notificacao['lida'] ? '' : 'notificacao-nao-lida'; ?>"
                 data-id="<?php echo $notificacao['id']; ?>"
                 data-lida="<?php echo $notificacao['lida']; ?>"
                 style="cursor: pointer;">
                <div class="row">
                    <div class="col-md-1 text-center">
                        <div class="mt-2">
                            <?php
                            $icone = $icones[$notificacao['tipo']] ?? 'bi-info-circle text-info';
                            ?>
                            <i class="bi <?php echo $icone; ?>" style="font-size: 1.5rem;"></i>
                        </div>
                    </div>
                    <div class="col-md-8">
                        <h6 class="mb-1">
                            <?php echo htmlspecialchars($notificacao['titulo']); ?>
                            <?php if ($notificacao['lida']): ?>
                            <span class="badge bg-secondary badge-tipo">Lida</span>
                            <?php endif; ?>
                        </h6>
                        <p class="mb-1 text-muted"><?php echo htmlspecialchars($notificacao['mensagem']); ?></p>
                        <small class="text-muted">
                            <i class="bi bi-clock"></i> <?php echo date('d/m/Y H:i', strtotime($notificacao['criada_em'])); ?>
                            <?php if ($notificacao['lida']): ?>
                            | <i class="bi bi-check-circle"></i> Lida em <?php echo date('d/m/Y H:i', strtotime($notificacao['lida_em'])); ?>
                            <?php endif; ?>
                            <?php if ($notificacao['link_acao'] && $notificacao['link_acao'] !== '#'): ?>
                            | <a href="<?php echo $notificacao['link_acao']; ?>" class="text-decoration-none">
                                <i class="bi bi-arrow-right-circle"></i> Ver Detalhes
                            </a>
                            <?php endif; ?>
                        </small>
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="badge bg-<?php echo $notificacao['tipo']; ?> badge-tipo">
                            <?php echo ucfirst($notificacao['tipo']); ?>
                        </span>
                        <div class="mt-2">
                            <?php if (!$notificacao['lida']): ?>
                            <a href="notificacoes.php?acao=marcar_lida&id=<?php echo $notificacao['id']; ?>"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-check"></i>
                            </a>
                            <?php endif; ?>
                            <a href="notificacoes.php?acao=excluir&id=<?php echo $notificacao['id']; ?>"
                               class="btn btn-sm btn-outline-danger"
                               onclick="return confirm('Tem certeza que deseja excluir esta notificação?')">
                                <i class="bi bi-trash"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-bell-slash" style="font-size: 3rem; color: #dee2e6;"></i>
                <h5 class="mt-3 text-muted">Nenhuma notificação encontrada</h5>
                <p class="text-muted">Você não possui notificações no momento.</p>
            </div>
        <?php endif; ?>
    </div>
</div>