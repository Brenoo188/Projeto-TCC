<?php

$servidor = "localhost";
$usuario  = "root";
$senha    = "";
$banco    = "bd_tcc";

// Conexão com o banco
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);

// Verificar conexão
if (!$conexao) {
    die("ERRO DE CONEXÃO: " . mysqli_connect_error());
}

// Configurar charset para evitar problemas com caracteres especiais
mysqli_set_charset($conexao, "utf8mb4");

// Definir timezone para datas
date_default_timezone_set('America/Sao_Paulo');
