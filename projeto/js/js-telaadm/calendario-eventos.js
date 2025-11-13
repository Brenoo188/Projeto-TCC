// Calendário Interativo de Eventos
document.addEventListener('DOMContentLoaded', function() {

    let calendario;
    let eventoAtual = null;

    // Inicializar FullCalendar
    const calendarioEl = document.getElementById('calendario');
    if (calendarioEl) {
        calendario = new FullCalendar.Calendar(calendarioEl, {
            initialView: 'dayGridMonth',
            locale: 'pt-br',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay,listWeek'
            },
            events: eventos,
            editable: true,
            selectable: true,
            selectMirror: true,
            dayMaxEvents: true,
            weekends: true,
            eventClick: function(info) {
                mostrarDetalhesEvento(info.event);
            },
            select: function(info) {
                // Preencher formulário com datas selecionadas
                document.getElementById('data_inicio').value = formatarData(info.start);
                document.getElementById('hora_inicio').value = formatarHora(info.start);

                if (info.end) {
                    document.getElementById('data_fim').value = formatarData(info.end);
                    document.getElementById('hora_fim').value = formatarHora(info.end);
                }

                // Abrir modal de novo evento
                const modal = new bootstrap.Modal(document.getElementById('modalEvento'));
                modal.show();
            },
            eventDrop: function(info) {
                if (confirm('Tem certeza que deseja mover este evento?')) {
                    atualizarDataEvento(info.event);
                } else {
                    info.revert();
                }
            },
            eventResize: function(info) {
                if (confirm('Tem certeza que deseja alterar a duração deste evento?')) {
                    atualizarDataEvento(info.event);
                } else {
                    info.revert();
                }
            }
        });

        calendario.render();
    }

    // Mostrar detalhes do evento
    function mostrarDetalhesEvento(event) {
        eventoAtual = event;
        const dadosEvento = event.extendedProps;

        const detalhesHtml = `
            <div class="evento-detalhes">
                <h6 class="text-primary">${event.title}</h6>

                <div class="mb-2">
                    <strong>Tipo:</strong>
                    <span class="badge tipo-${dadosEvento.type}">${traduzirTipoEvento(dadosEvento.type)}</span>
                </div>

                <div class="mb-2">
                    <strong>Início:</strong> ${formatarDataHora(event.start)}
                </div>

                ${event.end ? `
                <div class="mb-2">
                    <strong>Fim:</strong> ${formatarDataHora(event.end)}
                </div>
                ` : ''}

                ${dadosEvento.location ? `
                <div class="mb-2">
                    <strong>Local:</strong> ${dadosEvento.location}
                </div>
                ` : ''}

                <div class="mb-2">
                    <strong>Criado por:</strong> ${dadosEvento.creator}
                </div>

                ${dadosEvento.description ? `
                <div class="mb-2">
                    <strong>Descrição:</strong><br>
                    <p class="mb-0">${dadosEvento.description}</p>
                </div>
                ` : ''}

                <div class="mb-2">
                    <strong>Cor:</strong>
                    <span class="badge" style="background-color: ${dadosEvento.color}; color: white;">
                        ${dadosEvento.color}
                    </span>
                </div>
            </div>
        `;

        document.getElementById('detalhesEvento').innerHTML = detalhesHtml;

        // Mostrar/esconder botões de edição/exclusão
        const btnEditar = document.getElementById('btnEditar');
        const btnExcluir = document.getElementById('btnExcluir');

        if (dadosEvento.canEdit) {
            btnEditar.style.display = 'inline-block';
            btnExcluir.style.display = 'inline-block';
        } else {
            btnEditar.style.display = 'none';
            btnExcluir.style.display = 'none';
        }

        const modal = new bootstrap.Modal(document.getElementById('modalVisualizar'));
        modal.show();
    }

    // Editar evento
    document.getElementById('btnEditar')?.addEventListener('click', function() {
        if (!eventoAtual) return;

        const dadosEvento = eventoAtual.extendedProps;

        // Preencher formulário
        document.getElementById('modalTitulo').textContent = 'Editar Evento';
        document.getElementById('eventoAcao').value = 'editar_evento';
        document.getElementById('eventoId').value = eventoAtual.id;
        document.getElementById('titulo').value = eventoAtual.title;
        document.getElementById('tipo_evento').value = dadosEvento.type;
        document.getElementById('data_inicio').value = formatarData(eventoAtual.start);
        document.getElementById('hora_inicio').value = formatarHora(eventoAtual.start);

        if (eventoAtual.end) {
            document.getElementById('data_fim').value = formatarData(eventoAtual.end);
            document.getElementById('hora_fim').value = formatarHora(eventoAtual.end);
        }

        document.getElementById('local').value = dadosEvento.location || '';
        document.getElementById('cor').value = dadosEvento.color || '#007bff';
        document.getElementById('descricao').value = dadosEvento.description || '';

        // Fechar modal de visualização e abrir de edição
        bootstrap.Modal.getInstance(document.getElementById('modalVisualizar')).hide();

        setTimeout(() => {
            const modal = new bootstrap.Modal(document.getElementById('modalEvento'));
            modal.show();
        }, 500);
    });

    // Excluir evento
    document.getElementById('btnExcluir')?.addEventListener('click', function() {
        if (!eventoAtual) return;

        if (confirm('Tem certeza que deseja excluir este evento? Esta ação não pode ser desfeita.')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="acao" value="excluir_evento">
                <input type="hidden" name="id_evento" value="${eventoAtual.id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    });

    // Resetar modal quando fechar
    document.getElementById('modalEvento').addEventListener('hidden.bs.modal', function() {
        document.getElementById('formEvento').reset();
        document.getElementById('modalTitulo').textContent = 'Novo Evento';
        document.getElementById('eventoAcao').value = 'criar_evento';
        document.getElementById('eventoId').value = '';
    });

    // Atualizar data do evento (drag and drop ou resize)
    function atualizarDataEvento(event) {
        // Esta função implementaria a atualização via AJAX
        // Por enquanto, apenas mostra um alerta
        console.log('Evento movido/redimensionado:', event.title, event.start);
    }

    // Carregar próximos eventos
    carregarProximosEventos();

    // Carregar estatísticas
    carregarEstatisticas();

    // Funções auxiliares
    function formatarData(data) {
        const d = new Date(data);
        return d.toISOString().split('T')[0];
    }

    function formatarHora(data) {
        const d = new Date(data);
        return d.toTimeString().slice(0, 5);
    }

    function formatarDataHora(data) {
        const d = new Date(data);
        return d.toLocaleString('pt-BR', {
            day: '2-digit',
            month: '2-digit',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    function traduzirTipoEvento(tipo) {
        const tipos = {
            'prova': 'Prova',
            'trabalho': 'Trabalho',
            'reuniao': 'Reunião',
            'aula': 'Aula',
            'outro': 'Outro'
        };
        return tipos[tipo] || tipo;
    }

    function carregarProximosEventos() {
        const container = document.getElementById('proximos-eventos');
        if (!container) return;

        const agora = new Date();
        const proximosEventos = eventos
            .filter(e => new Date(e.start) > agora)
            .sort((a, b) => new Date(a.start) - new Date(b.start))
            .slice(0, 5);

        if (proximosEventos.length === 0) {
            container.innerHTML = '<p class="text-muted">Nenhum evento próximo.</p>';
            return;
        }

        let html = '<div class="list-group list-group-flush">';
        proximosEventos.forEach(evento => {
            const dataInicio = new Date(evento.start);
            html += `
                <div class="list-group-item">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="mb-1">${evento.title}</h6>
                            <small class="text-muted">
                                <i class="bi bi-calendar"></i> ${formatarDataHora(dataInicio)}
                            </small>
                            ${evento.location ? `<br><small><i class="bi bi-geo-alt"></i> ${evento.location}</small>` : ''}
                        </div>
                        <span class="badge" style="background-color: ${evento.color}; color: white;">
                            ${traduzirTipoEvento(evento.type)}
                        </span>
                    </div>
                </div>
            `;
        });
        html += '</div>';

        container.innerHTML = html;
    }

    function carregarEstatisticas() {
        const container = document.getElementById('estatisticas-eventos');
        if (!container) return;

        const agora = new Date();
        const esteMes = new Date(agora.getFullYear(), agora.getMonth(), 1);
        const proximoMes = new Date(agora.getFullYear(), agora.getMonth() + 1, 1);

        const eventosEsteMes = eventos.filter(e => {
            const dataEvento = new Date(e.start);
            return dataEvento >= esteMes && dataEvento < proximoMes;
        });

        const tiposEventos = {};
        eventosEsteMes.forEach(evento => {
            tiposEventos[evento.type] = (tiposEventos[evento.type] || 0) + 1;
        });

        let html = `
            <div class="row text-center">
                <div class="col-6">
                    <h4 class="text-primary">${eventosEsteMes.length}</h4>
                    <small class="text-muted">Eventos este mês</small>
                </div>
                <div class="col-6">
                    <h4 class="text-success">${eventos.length}</h4>
                    <small class="text-muted">Total de eventos</small>
                </div>
            </div>
            <hr>
            <h6 class="mb-3">Eventos por tipo</h6>
        `;

        for (const [tipo, quantidade] of Object.entries(tiposEventos)) {
            html += `
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>${traduzirTipoEvento(tipo)}</span>
                    <span class="badge bg-secondary">${quantidade}</span>
                </div>
            `;
        }

        if (Object.keys(tiposEventos).length === 0) {
            html += '<p class="text-muted">Nenhum evento este mês.</p>';
        }

        container.innerHTML = html;
    }

    // Validação de formulário
    document.getElementById('formEvento')?.addEventListener('submit', function(e) {
        const dataInicio = document.getElementById('data_inicio').value;
        const dataFim = document.getElementById('data_fim').value;
        const horaInicio = document.getElementById('hora_inicio').value;
        const horaFim = document.getElementById('hora_fim').value;

        if (dataFim && dataInicio > dataFim) {
            e.preventDefault();
            alert('A data de fim não pode ser anterior à data de início.');
            return;
        }

        if (dataFim === dataInicio && horaFim && horaInicio > horaFim) {
            e.preventDefault();
            alert('A hora de fim não pode ser anterior à hora de início.');
            return;
        }
    });

    // Auto-atualizar lista de próximos eventos a cada 5 minutos
    setInterval(carregarProximosEventos, 300000);
});