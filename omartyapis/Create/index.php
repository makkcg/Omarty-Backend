<?php



include("../vendor/autoload.php");

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

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
        
        $create = new Create;
        $Fun = new Functions;
    
        
        try
        {
            $method = new reflectionMethod($create, $api);

            method_exists($create, $api);
            $method->invoke($create);
            // $Fun->throwError(100, "API $api doesn't exist.");
        }
        catch(Exception $e)
        {
            $Fun->throwError(404, $e->getMessage());
        }
    }
    try
    {
        runApi("Create", $api);
    }
    catch(Exeption $e)
    {
        $Fun->throwError(404, $e->getMessage());
    }
    
    // ===================================================================================


?>