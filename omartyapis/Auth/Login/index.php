<?php

    include("../../Config.php");
    include("../../Classes/Functions.php");
    include("../../Classes/Login.php");

    $Auth = new Login;

    $Auth->login();

?>