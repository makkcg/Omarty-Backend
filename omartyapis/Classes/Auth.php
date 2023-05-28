<?php


class Auth
   {
    public function login()
    {
        
    $email= filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
    $password = filter_var($_POST["password"], FILTER_SANITIZE_STRING);

        $login = new Login;
        $login->login($email, $password);
    }

    public function register()
    {
        $register = new Register;
        $register->register();
    }
   }

?>