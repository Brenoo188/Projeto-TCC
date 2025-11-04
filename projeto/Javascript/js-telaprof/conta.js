// --- Lógica para o Estado Ativo do Menu ---
var menuItem = document.querySelectorAll('.menu-botoes');

function ativarLink() {
    // Remove a classe 'ativo' de todos os itens
    menuItem.forEach((item) => item.classList.remove('ativo'));

    // Adiciona a classe 'ativo' apenas no item clicado
    this.classList.add('ativo');
}

menuItem.forEach((item) => item.addEventListener('click', ativarLink));

// --- Lógica para Expandir o Menu ---
var btnExpandir = document.querySelector('#btn-expan');
var menuLateral = document.querySelector('nav.menu-lateral');

btnExpandir.addEventListener('click', function() {
    // Alterna a classe 'expandir_btn' para abrir/fechar o menu
    menuLateral.classList.toggle('expandir_btn');
});

// --- Lógica para as Abas (Dados pessoais, Documentos, etc) ---
var botoesAbas = document.querySelectorAll('.abas button');
var conteudosAbas = document.querySelectorAll('.conteudo-tab');

// Adiciona evento de clique em cada botão
botoesAbas.forEach((botao) => {
    botao.addEventListener('click', function() {
        // Remove o estado ativo de todos os botões e abas
        botoesAbas.forEach((b) => b.classList.remove('ativo'));
        conteudosAbas.forEach((aba) => aba.classList.remove('ativo'));

        // Adiciona a classe 'ativo' no botão clicado
        this.classList.add('ativo');

        // Pega o ID da aba correspondente ao botão
        var tabId = this.getAttribute('data-tab');
        var abaSelecionada = document.getElementById(tabId);

        // Exibe apenas a aba correspondente
        if (abaSelecionada) {
            abaSelecionada.classList.add('ativo');
        }
    });
});

// --- Aba padrão ao carregar a página ---
if (botoesAbas.length > 0) {
    botoesAbas[0].classList.add('ativo');
    conteudosAbas[0].classList.add('ativo');
}

// --- Lógica do botão "Editar" ---
const btnEditar = document.getElementById('btnEditar');
let modoEdicao = false;

if (btnEditar) {
    btnEditar.addEventListener('click', () => {
        const elementos = document.querySelectorAll('main.conteudo p, main.conteudo h1, main.conteudo h2');

        if (!modoEdicao) {
            // Ativa o modo de edição
            elementos.forEach(el => {
                // Verifica se o texto contém "ID" e pula (não edita o ID do professor)
                if (el.innerText.toLowerCase().includes('id')) return;

                const input = document.createElement('input');
                input.type = 'text';
                input.value = el.innerText;
                input.classList.add('campo-edicao');
                el.replaceWith(input);
            });

            btnEditar.innerHTML = '<i class="bi bi-save"></i> Salvar';
            modoEdicao = true;
        } else {
            // Salva as edições
            const inputs = document.querySelectorAll('.campo-edicao');
            inputs.forEach(el => {
                const p = document.createElement('p');
                p.innerText = el.value;
                el.replaceWith(p);
            });

            btnEditar.innerHTML = '<i class="bi bi-pencil-square"></i> Editar';
            modoEdicao = false;
        }
    });
}
 