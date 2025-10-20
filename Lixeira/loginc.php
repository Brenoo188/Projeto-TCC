<!--logica login-->

<?php

session_start();

//print_r($_REQUEST);
if(isset($_POST['submit']) && !empty($_POST['email_confirm']) && !empty($_POST['senha_confirm'])) 
{
    include_once('../conexao.php');
    $email = $_POST['email_confirm'];
    $senha = $_POST['senha_confirm'];

    //Verificar se o email e senha existem no banco de dados

   // print_r("Email: " . $email);
   // print_r('<br>');
   // print_r("Senha: " . $senha);

   $sql = "SELECT * FROM usuarios WHERE email_user = '$email' AND senha_user = '$senha'";
   $result = $conexao->query($sql);

    //verificar se encontrou algum registro

    //   print_r($SQL);
    //   print_r($result);


    //Informar se o usuário foi encontrado ou não

    if(mysqli_num_rows($result) < 1)
    {
        unset($_SESSION['email_confirm']);
        unset($_SESSION['senha_confirm']);
        header('Location: index.php');
        }
    else {
        $_SESSION['email_confirm'] = $email;
        $_SESSION['senha_confirm'] = $senha;
        header('Location: ../tela-adm/home.php');
        }


}
else 
{
    header('Location: index.php');
}
?>

<!--logica login-->