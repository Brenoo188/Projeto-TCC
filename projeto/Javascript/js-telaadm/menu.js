// Menu interativo
document.addEventListener('DOMContentLoaded', function() {
    const btnExpandir = document.getElementById('btn-expan');
    const menuLateral = document.querySelector('.menu-lateral');

    if (btnExpandir && menuLateral) {
        btnExpandir.addEventListener('click', function() {
            menuLateral.classList.toggle('expandir');
        });
    }

    // Menu lateral hover
    const menuBotoes = document.querySelectorAll('.menu-botoes');
    menuBotoes.forEach(botao => {
        botao.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgb(0, 92, 169)';
            this.style.transition = 'background-color 0.3s ease';
            this.querySelector('.txt-link').style.color = '#fff';
            this.querySelector('.icon').style.color = '#fff';
        });

        botao.addEventListener('mouseleave', function() {
            if (!this.classList.contains('ativo')) {
                this.style.backgroundColor = '';
                this.querySelector('.txt-link').style.color = '';
                this.querySelector('.icon').style.color = '';
            }
        });

        botao.addEventListener('click', function() {
            menuBotoes.forEach(b => b.classList.remove('ativo'));
            this.classList.add('ativo');
        });
    });

    // Efeito de hover no botão expandir
    if (btnExpandir) {
        btnExpandir.addEventListener('mouseenter', function() {
            this.style.backgroundColor = 'rgb(0, 92, 169)';
            this.style.transition = 'background-color 0.3s ease';
            this.querySelector('i').style.color = '#fff';
        });

        btnExpandir.addEventListener('mouseleave', function() {
            this.style.backgroundColor = '';
            this.querySelector('i').style.color = '';
        });
    }
});

// Animações de transição suaves
const style = document.createElement('style');
style.textContent = `
    .menu-lateral {
        transition: all 0.3s ease;
    }

    .menu-lateral.expandir {
        width: 250px !important;
    }

    .menu-lateral.expandir .txt-link {
        display: inline !important;
        opacity: 1 !important;
        transition: opacity 0.3s ease;
    }

    .menu-botoes {
        transition: background-color 0.3s ease;
    }

    .menu-botoes:hover {
        background-color: rgb(0, 92, 169) !important;
    }

    .menu-botoes:hover .txt-link,
    .menu-botoes:hover .icon {
        color: #fff !important;
    }

    .menu-botoes.ativo {
        background-color: rgb(0, 92, 169) !important;
    }

    .menu-botoes.ativo .txt-link,
    .menu-botoes.ativo .icon {
        color: #fff !important;
    }
`;
document.head.appendChild(style);