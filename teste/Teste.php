<?php

 //Comentário
 //Comandos de saída de teste
 echo "Olá, mundo!";echo '<br>';
 echo("Boa tarde");echo '<br>';
 echo '<br>';
 echo '<br>';
 print "Olá, mundo!";echo '<br>';
 print("Olá mundo!");echo '<br>';

 //Variaveis

 $nome = "Breno";
 $curso = "Desenvolvimento de sistemas";
 $idade  = "17";
 $altura = "1,65";
 $peso = "57,5";
 $imc;
 $doador_de_orgaos = true;
 
//Mostra infos sobre var

 var_dump($nome);
 var_dump($curso);
 var_dump($idade);

//imprimir na tela mensagem junto com as variaveis

 echo"<h1> Eu sou o $nome e estou cursando $curso. </h1>";
 echo"<h1> Tenho $idade anos e $altura de atura. </h1>";
 echo"<h1> Atualmente estou pesando $peso Kilos. </h1>";

 //Criar Constante

 define("TAXA", 0.5);
 define("JUROS", 0.01);

 echo"Taxa " . TAXA . "% </br>";
 echo"Juros " . JUROS . "% </br>";

 ?>