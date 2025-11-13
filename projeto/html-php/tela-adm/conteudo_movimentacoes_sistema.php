<?php
$movimentacoes = obterMovimentacoesSistema($conexao, $limite, $tipo_filtro, $tabela_filtro);

// Estatísticas das movimentações
$estatisticas_mov = [
    'total' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes")->fetch_assoc()['total'],
    'hoje' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE DATE(criada_em) = CURDATE()")->fetch_assoc()['total'],
    'nao_lidas' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE lida = 0")->fetch_assoc()['total'],
    'aulas' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE tabela_referencia = 'aulas'")->fetch_assoc()['total'],
    'materias' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE tabela_referencia = 'materias'")->fetch_assoc()['total'],
    'usuarios' => $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE tabela_referencia = 'cadastros_int'")->fetch_assoc()['total']
];
?>

<!-- Estatísticas das Movimentações -->
<div class="row mb-4">
    <div class="col-md-2 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas_mov['total']; ?></div>
            <div class="text-muted">Total</div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas_mov['hoje']; ?></div>
            <div class="text-muted">Hoje</div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas_mov['nao_lidas']; ?></div>
            <div class="text-muted">Não Lidas</div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas_mov['aulas']; ?></div>
            <div class="text-muted">Aulas</div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas_mov['materias']; ?></div>
            <div class="text-muted">Matérias</div>
        </div>
    </div>
    <div class="col-md-2 mb-3">
        <div class="estatistica-card">
            <div class="numero"><?php echo $estatisticas_mov['usuarios']; ?></div>
            <div class="text-muted">Usuários</div>
        </div>
    </div>
</div>

<!-- Filtros Avançados -->
<div class="filtro-section">
    <div class="row">
        <div class="col-md-3">
            <label class="form-label text-white">Filtrar por Tipo:</label>
            <select class="form-select filtro-select" name="tipo_filtro">
                <option value="todos" <?php echo $tipo_filtro === 'todos' ? 'selected' : ''; ?>>Todos os Tipos</option>
                <option value="info" <?php echo $tipo_filtro === 'info' ? 'selected' : ''; ?>>ℹ️ Informações</option>
                <option value="success" <?php echo $tipo_filtro === 'success' ? 'selected' : ''; ?>>✅ Sucesso</option>
                <option value="warning" <?php echo $tipo_filtro === 'warning' ? 'selected' : ''; ?>>⚠️ Avisos</option>
                <option value="danger" <?php echo $tipo_filtro === 'danger' ? 'selected' : ''; ?>>❌ Erros</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label text-white">Filtrar por Módulo:</label>
            <select class="form-select filtro-select" name="tabela_filtro">
                <option value="todas" <?php echo $tabela_filtro === 'todas' ? 'selected' : ''; ?>>Todos os Módulos</option>
                <option value="aulas" <?php echo $tabela_filtro === 'aulas' ? 'selected' : ''; ?>>📚 Aulas</option>
                <option value="materias" <?php echo $tabela_filtro === 'materias' ? 'selected' : ''; ?>>📖 Matérias</option>
                <option value="professores" <?php echo $tabela_filtro === 'professores' ? 'selected' : ''; ?>>👨‍🏫 Professores</option>
                <option value="cadastros_int" <?php echo $tabela_filtro === 'cadastros_int' ? 'selected' : ''; ?>>👥 Cadastros</option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label text-white">Resultados:</label>
            <select class="form-select filtro-select" name="limite">
                <option value="50" <?php echo $limite == 50 ? 'selected' : ''; ?>>50</option>
                <option value="100" <?php echo $limite == 100 ? 'selected' : ''; ?>>100</option>
                <option value="200" <?php echo $limite == 200 ? 'selected' : ''; ?>>200</option>
                <option value="500" <?php echo $limite == 500 ? 'selected' : ''; ?>>500</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button class="btn btn-light w-100" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Atualizar
            </button>
        </div>
    </div>
</div>

<!-- Lista de Movimentações -->
<div class="card">
    <div class="card-header bg-dark text-white">
        <h5 class="mb-0">
            <i class="bi bi-activity"></i> Movimentações do Sistema
            <small class="text-white-50">(<?php echo $movimentacoes->num_rows; ?> registros)</small>
        </h5>
    </div>
    <div class="card-body p-0">
        <?php if ($movimentacoes->num_rows > 0): ?>
            <?php while ($mov = $movimentacoes->fetch_assoc()): ?>
            <div class="movimentacao-item">
                <div class="row align-items-center">
                    <div class="col-md-1 text-center">
                        <?php
                        $icones_modulo = [
                            'aulas' => 'bi-calendar-event text-primary',
                            'materias' => 'bi-book text-info',
                            'professores' => 'bi-person-workspace text-success',
                            'cadastros_int' => 'bi-people text-warning',
                            'sistema' => 'bi-gear text-secondary'
                        ];
                        $icone_modulo = $icones_modulo[$mov['tabela_referencia']] ?? 'bi-info-circle text-muted';
                        ?>
                        <i class="bi <?php echo $icone_modulo; ?>" style="font-size: 1.5rem;"></i>
                    </div>

                    <div class="col-md-8">
                        <div class="d-flex align-items-center mb-1">
                            <h6 class="mb-0 me-2"><?php echo htmlspecialchars($mov['titulo']); ?></h6>
                            <span class="badge bg-<?php echo $mov['tipo']; ?> badge-tipo">
                                <?php echo ucfirst($mov['tipo']); ?>
                            </span>
                            <?php if ($mov['lida'] == 0): ?>
                            <span class="badge bg-primary badge-tipo ms-1">Nova</span>
                            <?php endif; ?>
                        </div>
                        <p class="mb-1 text-muted"><?php echo htmlspecialchars($mov['mensagem']); ?></p>
                        <div class="d-flex align-items-center text-muted small">
                            <span class="me-3">
                                <i class="bi bi-person"></i>
                                Para: <strong><?php echo htmlspecialchars($mov['nome_destino'] ?? 'Sistema'); ?></strong>
                            </span>
                            <span class="me-3">
                                <i class="bi bi-clock"></i>
                                <?php echo date('d/m/Y H:i:s', strtotime($mov['criada_em'])); ?>
                            </span>
                            <?php if ($mov['nome_criador']): ?>
                            <span>
                                <i class="bi bi-person-plus"></i>
                                Por: <strong><?php echo htmlspecialchars($mov['nome_criador']); ?></strong>
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="col-md-3 text-end">
                        <div class="mb-2">
                            <?php
                            $nomes_modulos = [
                                'aulas' => 'Aulas',
                                'materias' => 'Matérias',
                                'professores' => 'Professores',
                                'cadastros_int' => 'Cadastros',
                                'sistema' => 'Sistema'
                            ];
                            $nome_modulo = $nomes_modulos[$mov['tabela_referencia']] ?? 'Outro';
                            ?>
                            <span class="badge bg-secondary">
                                <?php echo $nome_modulo; ?>
                            </span>
                        </div>
                        <?php if ($mov['link_acao'] && $mov['link_acao'] !== '#'): ?>
                        <a href="<?php echo $mov['link_acao']; ?>" class="btn btn-sm btn-outline-primary">
                            <i class="bi bi-arrow-right-circle"></i> Ver
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="bi bi-activity" style="font-size: 3rem; color: #dee2e6;"></i>
                <h5 class="mt-3 text-muted">Nenhuma movimentação encontrada</h5>
                <p class="text-muted">Não há movimentações registradas com os filtros selecionados.</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Resumo por Tipo de Movimentação -->
<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="bi bi-bar-chart"></i> Resumo por Tipo
                </h6>
            </div>
            <div class="card-body">
                <div class="row text-center">
                    <div class="col-md-3">
                        <div class="p-3 border rounded">
                            <i class="bi bi-info-circle text-info" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">
                                <?php echo $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE tipo = 'info'")->fetch_assoc()['total']; ?>
                            </h5>
                            <small class="text-muted">Informações</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">
                                <?php echo $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE tipo = 'success'")->fetch_assoc()['total']; ?>
                            </h5>
                            <small class="text-muted">Sucessos</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded">
                            <i class="bi bi-exclamation-triangle text-warning" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">
                                <?php echo $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE tipo = 'warning'")->fetch_assoc()['total']; ?>
                            </h5>
                            <small class="text-muted">Avisos</small>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="p-3 border rounded">
                            <i class="bi bi-x-circle text-danger" style="font-size: 2rem;"></i>
                            <h5 class="mt-2">
                                <?php echo $conexao->query("SELECT COUNT(*) as total FROM notificacoes WHERE tipo = 'danger'")->fetch_assoc()['total']; ?>
                            </h5>
                            <small class="text-muted">Erros</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>