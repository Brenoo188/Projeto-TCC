// --- Lógica para o Estado Ativo do Menu ---
var menuItem = document.querySelectorAll('.menu-botoes');

function ativarLink() {
    // Remove a classe 'ativo' de todos os itens
    menuItem.forEach((item)=>
        item.classList.remove('ativo')
    )
    // Adiciona a classe 'ativo' apenas no item clicado
    this.classList.add('ativo')
}

menuItem.forEach((item)=>
    item.addEventListener('click', ativarLink)
)

// --- Lógica para Expandir o Menu ---
var btnExpandir = document.querySelector('#btn-expan');
var menuLateral = document.querySelector('nav.menu-lateral');

btnExpandir.addEventListener('click', function() {
    // CORREÇÃO: Usa a classe correta 'expandir_btn'
    menuLateral.classList.toggle('expandir_btn');
})
