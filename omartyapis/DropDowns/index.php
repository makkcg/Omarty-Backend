<?php

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
        
        $DD = new DropDowns;
        $Fun = new Functions;
    
        
        try
        {
            $method = new reflectionMethod($DD, $api);
            method_exists($DD, $api);
            $method->invoke($DD);
        }
        catch(Exception $e)
        {
            // http_response_code(404);
            // print_r(json_encode("wAHEDDDASDA"));
            // header("Status: 404 API not found");
            $Fun->throwError(404, $e->getMessage());
        }
    }
    try
    {
        runApi("DropDowns", $api);
    }
    catch(Exeption $e)
    {
        $Fun->throwError(404, $e->getMessage());
    }
    
    

?>