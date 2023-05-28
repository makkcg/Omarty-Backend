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



function runApi($className, $api)
{

    $show = new Show;
    $Fun = new Functions;

    $method = new reflectionMethod($show, $api);

    if(!method_exists($show, $api))
    {
        $Fun->throwError(404, "API $api doesn't exist.");
        exit;
    }
    else
    {
        $method->invoke($show);
    }
}

    $api = $_POST["api"];

    runApi("Show", $api);

?>