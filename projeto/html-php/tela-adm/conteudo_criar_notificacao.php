<!-- Formulário para Criar Notificação -->
<div class="card">
    <div class="card-header bg-success text-white">
        <h5 class="mb-0">
            <i class="bi bi-plus-circle"></i> Criar Nova Notificação
        </h5>
    </div>
    <div class="card-body">
        <form method="POST" action="notificacoes.php?acao=criar">
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="id_usuario_destino" class="form-label">Destinatário:</label>
                        <select class="form-select" id="id_usuario_destino" name="id_usuario_destino" required>
                            <option value="">Selecione um usuário...</option>
                            <?php
                            $tipo_atual = '';
                            while ($usuario = $usuarios->fetch_assoc()):
                                if ($tipo_atual !== $usuario['tipo_usuario']):
                                    if ($tipo_atual !== '') echo '</optgroup>';
                                    echo '<optgroup label="' . htmlspecialchars($usuario['tipo_usuario']) . 's">';
                                    $tipo_atual = $usuario['tipo_usuario'];
                                endif;
                            ?>
                                <option value="<?php echo $usuario['id']; ?>">
                                    <?php echo htmlspecialchars($usuario['nome_user']); ?>
                                </option>
                            <?php
                            endwhile;
                            if ($tipo_atual !== '') echo '</optgroup>';
                            ?>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo da Notificação:</label>
                        <select class="form-select" id="tipo" name="tipo" required>
                            <option value="info">ℹ️ Informação</option>
                            <option value="success">✅ Sucesso</option>
                            <option value="warning">⚠️ Aviso</option>
                            <option value="danger">❌ Erro/Importante</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título:</label>
                        <input type="text" class="form-control" id="titulo" name="titulo"
                               placeholder="Digite o título da notificação..." required>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="mb-3">
                        <label for="mensagem" class="form-label">Mensagem:</label>
                        <textarea class="form-control" id="mensagem" name="mensagem" rows="4"
                                  placeholder="Digite a mensagem detalhada..." required></textarea>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="bi bi-send"></i> Enviar Notificação
                    </button>
                    <button type="reset" class="btn btn-secondary ms-2">
                        <i class="bi bi-x-circle"></i> Limpar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Notificações Rápidas Predefinidas -->
<div class="card mt-4">
    <div class="card-header">
        <h6 class="mb-0">
            <i class="bi bi-lightning"></i> Notificações Rápidas
        </h6>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4 mb-3">
                <button class="btn btn-outline-info w-100" onclick="preencherNotificacao('info', 'Atualização do Sistema', 'O sistema será atualizado nas próximas horas. Podem ocorrer instabilidades temporárias.')">
                    <i class="bi bi-info-circle"></i> Manutenção
                </button>
            </div>
            <div class="col-md-4 mb-3">
                <button class="btn btn-outline-success w-100" onclick="preencherNotificacao('success', 'Bem-vindo!', 'Seja bem-vindo ao Sistema Acadêmico. Explore todas as funcionalidades disponíveis.')">
                    <i class="bi bi-check-circle"></i> Boas-vindas
                </button>
            </div>
            <div class="col-md-4 mb-3">
                <button class="btn btn-outline-warning w-100" onclick="preencherNotificacao('warning', 'Lembrete', 'Não se esqueça de atualizar suas informações de perfil.')">
                    <i class="bi bi-exclamation-triangle"></i> Lembrete
                </button>
            </div>
            <div class="col-md-4 mb-3">
                <button class="btn btn-outline-danger w-100" onclick="preencherNotificacao('danger', 'Ação Necessária', 'Sua atenção é necessária. Verifique suas configurações imediatamente.')">
                    <i class="bi bi-x-circle"></i> Urgente
                </button>
            </div>
            <div class="col-md-4 mb-3">
                <button class="btn btn-outline-primary w-100" onclick="preencherNotificacao('info', 'Novo Recurso', 'Uma nova funcionalidade foi adicionada ao sistema. Confira!')">
                    <i class="bi bi-star"></i> Novo Recurso
                </button>
            </div>
            <div class="col-md-4 mb-3">
                <button class="btn btn-outline-secondary w-100" onclick="preencherNotificacao('info', 'Avisos Gerais', 'Comunicados importantes para todos os usuários do sistema.')">
                    <i class="bi bi-megaphone"></i> Comunicado
                </button>
            </div>
        </div>
    </div>
</div>

<script>
function preencherNotificacao(tipo, titulo, mensagem) {
    document.getElementById('tipo').value = tipo;
    document.getElementById('titulo').value = titulo;
    document.getElementById('mensagem').value = mensagem;

    // Rola para o formulário
    document.querySelector('.card').scrollIntoView({ behavior: 'smooth' });
}
</script>