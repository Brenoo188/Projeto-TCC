<?php

$servidor = "localhost";    
$usuario  = "root";         
$senha    = "";              
$banco    = "bd_TCC";        

// Conexão com o banco
$conexao = mysqli_connect($servidor, $usuario, $senha, $banco);


if (!$conexao) {
    die("Erro na conexão com o banco de dados: " . mysqli_connect_error());
}


 echo "Conexão realizada com sucesso!";
?>
