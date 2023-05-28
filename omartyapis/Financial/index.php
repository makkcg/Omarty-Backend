<?php



include("../vendor/autoload.php");

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

    header("content-type: Aplication/json");
    
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
        
        $financial = new Financial;
        $Fun = new Functions;
    
        
        try
        {
            $method = new reflectionMethod($financial, $api);

            method_exists($financial, $api);
            $method->invoke($financial);
            // $Fun->throwError(100, "API $api doesn't exist.");
        }
        catch(Exception $e)
        {
            $Fun->throwError(404, $e->getMessage());
        }
    }
    try
    {
        runApi("Financial", $api);
    }
    catch(Exeption $e)
    {
        $Fun->throwError(404, $e->getMessage());
    }
    
    

?>