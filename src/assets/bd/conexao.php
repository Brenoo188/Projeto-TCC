<?php

$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "bd_cadastro";

if (mysqli_connect($servidor, $usuario, $senha, $banco) ) {
    // echo "Conexão com o banco de dados realizada com sucesso!";
} else {
    echo "Erro na conexão com o banco de dados: " . mysqli_connect_error();
}
