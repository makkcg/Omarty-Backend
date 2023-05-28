<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use MyApp\Chat;

    require dirname(__DIR__) . '/vendor/autoload.php';
//die(dirname(__DIR__) . '/vendor/autoload.php') ;
    $server = IoServer::factory(
        new HttpServer(
            new WsServer(
                new Chat()
            )
        ),
        3000
    );

    $server->run();

    // $server = IoServer::factory(
    //     new HttpServer(
    //         new WsServer(
    //             new Chat($allowedUsersGroupA)
    //         )
    //     ),
    //     8080
    // );
    
    // $server->loop->addPeriodicTimer(1, function() use ($server) {
    //     echo "Server is running on port 8080\n";
    // });
    
    // $server2 = IoServer::factory(
    //     new HttpServer(
    //         new WsServer(
    //             new Chat($allowedUsersGroupB)
    //         )
    //     ),
    //     8080
    // );
    
    // $server2->loop->addPeriodicTimer(1, function() use ($server2) {
    //     echo "Server is running on port 8080\n";
    // });
    
    // $server->run();
    // $server2->run();