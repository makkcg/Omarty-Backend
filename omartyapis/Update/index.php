<?php

include("../vendor/autoload.php");

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    
    header("content-type: Application/json");

    spl_autoload_register(function($className){
        $path = "../Classes/" . $className. '.php';
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
        
        $update = new Update;
        $Fun = new Functions;
    
        
        try
        {
            $method = new reflectionMethod($update, $api);

            method_exists($update, $api);
            $method->invoke($update);
        }
        catch(Exception $e)
        {
            $Fun->throwError(404, $e->getMessage());
        }
    }
    try
    {
        runApi("Update", $api);
    }
    catch(Exeption $e)
    {
        $Fun->throwError(404, $e->getMessage());
    }
    
    

?>