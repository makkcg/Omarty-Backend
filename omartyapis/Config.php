<?php


    // $Server = "localhost";
    // $Username = "kcgwebse_omartydbusr";
    // $Password = "2IyNI*+f&N_Qb%wF";
    // $DBName = "kcgwebse_omartyDB";

    $Server = "localhost";
    $Username = "cpomartyadmi_omartydbus";
    $Password = "kzEiLr7U.[cX1x6!";
    $DBName = "cpomartyadmi_omarydb";


    $conn = new mysqli($Server, $Username, $Password, $DBName);

     if($conn->error)
    {
        die("there is some thing wrong  " . $conn->errno . " the error is " . $conn->error);
    }
    

?>