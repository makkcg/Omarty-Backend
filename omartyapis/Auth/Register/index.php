<?php

    include("../../Config.php");
    include("../../Classes/Functions.php");
    include("../../Classes/Register.php");
    
    $Auth = new Register;

    $Auth->register();

?>