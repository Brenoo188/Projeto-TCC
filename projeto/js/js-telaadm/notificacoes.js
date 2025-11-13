// Script para gerenciar notificações em tempo real
document.addEventListener('DOMContentLoaded', function() {

    // Atualizar contador de notificações não lidas
    function atualizarContadorNotificacoes() {
        fetch('../api/contar-notificacoes-nao-lidas.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const badge = document.querySelector('.notificacao-badge');
                    if (badge) {
                        if (data.total > 0) {
                            badge.textContent = data.total;
                            badge.style.display = 'inline-block';
                        } else {
                            badge.style.display = 'none';
                        }
                    }
                }
            })
            .catch(error => console.error('Erro ao atualizar contador:', error));
    }

    // Verificar notificações a cada 30 segundos
    setInterval(atualizarContadorNotificacoes, 30000);

    // Animação para novas notificações
    function animarNovaNotificacao() {
        const naoLidas = document.querySelectorAll('.notificacao-nao-lida');
        naoLidas.forEach((notificacao, index) => {
            setTimeout(() => {
                notificacao.style.animation = 'slideInLeft 0.3s ease';
            }, index * 100);
        });
    }

    // Chamar animação quando a página carregar
    animarNovaNotificacao();

    // Confirmação para excluir notificação
    document.querySelectorAll('.btn-outline-danger').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (this.title === 'Excluir') {
                if (!confirm('Tem certeza que deseja excluir esta notificação?')) {
                    e.preventDefault();
                }
            }
        });
    });

    // Auto-refresh a cada 2 minutos se houver notificações não lidas
    const temNaoLidas = document.querySelector('.notificacao-nao-lida');
    if (temNaoLidas) {
        setTimeout(() => {
            location.reload();
        }, 120000); // 2 minutos
    }
});

// Função para mostrar notificação toast (se necessário no futuro)
function mostrarToast(mensagem, tipo = 'info') {
    const toastContainer = document.getElementById('toastContainer');
    if (!toastContainer) {
        const container = document.createElement('div');
        container.id = 'toastContainer';
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        document.body.appendChild(container);
    }

    const toast = document.createElement('div');
    toast.className = `toast align-items-center text-white bg-${tipo === 'erro' ? 'danger' : tipo}`;
    toast.setAttribute('role', 'alert');
    toast.innerHTML = `
        <div class="d-flex">
            <div class="toast-body">
                ${mensagem}
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    `;

    document.getElementById('toastContainer').appendChild(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();

    // Remover o toast após ser ocultado
    toast.addEventListener('hidden.bs.toast', () => {
        toast.remove();
    });
}