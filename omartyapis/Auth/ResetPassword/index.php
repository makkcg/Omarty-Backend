<?php

    include("../../vendor/autoload.php");

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    spl_autoload_register(function($className){
        $path = "../../Classes/" . $className. '.php';
        // echo $path;
        if(file_exists($path))
        {
            require($path);
        }
        else
        {
            echo "File $path Doesn't exist";
        }
    });

    $api = $_POST["api"];

    function runApi($className, $api)
    {
        
        $Reset = new ResetPassword;
        $Fun = new Functions;
       
        try
        {
            $method = new reflectionMethod($Reset, $api);

            method_exists($Reset, $api);
            $method->invoke($Reset);
            // $Fun->throwError(100, "API $api doesn't exist.");
        }
        catch(Exception $e)
        {
            $Fun->throwError(400, $e->getMessage());
        }
    }
    try
    {
        runApi("ResetPassword", $api);
    }
    catch(Exeption $e)
    {
        $Fun->throwError(401, $e->getMessage());
    }

?>