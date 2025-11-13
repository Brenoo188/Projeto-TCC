<?php
session_start();

if (!isset($_SESSION['id']) || !isset($_SESSION['tipo_usuario'])) {
    header('Location: ../parte-inicial/index.php?erro=nao_logado');
    exit();
}

if ($_SESSION['tipo_usuario'] !== 'Administrador') {
    header('Location: ../parte-inicial/index.php?erro=acesso_negado');
    exit();
}
?>


<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="../../css/css_telaadm/menu-padrao.css">
    <link rel="stylesheet" href="../../css/css_telaadm/home.css">
</head>

<body>

    <nav class="menu-lateral">

        <div class="btn-expandir" id="btn-expan" style="padding-right: 0px;">

            <i class="bi bi-list"></i>

        </div>

        <ul>

            <li class="menu-botoes ativo">
                <a href="home.php">
                    <span class="icon"><i class="bi bi-house"></i></span>
                    <span class="txt-link">Home</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="calendario.php">
                    <span class="icon"><i class="bi bi-calendar"></i></span>
                    <span class="txt-link">Calendário</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="cadastros_int.php">
                    <span class="icon"><i class="bi bi-people"></i></span>
                    <span class="txt-link">Cadastros</span>
                </a>
            </li>

            <?php if ($_SESSION['tipo_usuario'] === 'Administrador'): ?>
            <li class="menu-botoes">
                <a href="professores.php">
                    <span class="icon"><i class="bi bi-person-workspace"></i></span>
                    <span class="txt-link">Professores</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="materias.php">
                    <span class="icon"><i class="bi bi-book"></i></span>
                    <span class="txt-link">Matérias</span>
                </a>
            </li>
            <?php endif; ?>

            <li class="menu-botoes">
                <a href="notificacoes.php">
                    <span class="icon"><i class="bi bi-bell"></i></span>
                    <span class="txt-link">Notificações</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="conta.php">
                    <span class="icon"><i class="bi bi-person-circle"></i></span>
                    <span class="txt-link">Conta</span>
                </a>
            </li>

            <li class="menu-botoes">
                <a href="configuracao.php">
                    <span class="icon"><i class="bi bi-gear"></i></span>
                    <span class="txt-link">Configuração</span>
                </a>
            </li>

        </ul>

    </nav>

    <main class="conteudo">

        
        <div class="card" style="width: 100%; margin-top: 20px;">
            <div class="card-header">
                <h5 class="mb-0"><i class="bi bi-images"></i> Sistema Acadêmico</h5>
            </div>
            <div class="card-body p-0">
                <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel" style="border-radius: 0 0 15px 15px; overflow: hidden;">
                    <div class="carousel-inner">
                        <div class="carousel-item active">
                            <img src="../../img/10.png" class="d-block w-100" alt="Calendário 1" style="height: 400px; object-fit: cover;">
                        </div>
                        <div class="carousel-item">
                            <img src="../../img/9.png" class="d-block w-100" alt="Calendário 2" style="height: 400px; object-fit: cover;">
                        </div>
                        <div class="carousel-item">
                            <img src="../../img/8.png" class="d-block w-100" alt="Calendário 3" style="height: 400px; object-fit: cover;">
                        </div>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Próximo</span>
                    </button>
                </div>
            </div>
        </div>

    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../../Javascript/js-telaadm/menu.js"></script>
    <script src="../../Javascript/js-telaadm/home.js"></script>

</body>

</html>
