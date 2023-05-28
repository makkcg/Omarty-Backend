<?php
    // $msg = $_GET["msg"];
    // $User = $_GET["user"];
    // // $Send = $_GET["send"];

?>
<html>
    <body>
        <script>
            const webSocket = new WebSocket('wss://50.87.253.107:3000?userId=1&blockId=1');

            webSocket.addEventListener('open', function(event) {
            console.log('WebSocket connection established.');
            });

            webSocket.addEventListener('message', function(event) {
            console.log('Received message: ' + event.data);
            });

            webSocket.addEventListener('close', function(event) {
            console.log('WebSocket connection closed.');
            });

            webSocket.addEventListener('error', function(event) {
            console.error('WebSocket error occurred: ' + event.error);
            });
            // conn.onopen = function(e) {
            //     console.log("Connection established!");
            // };

            // conn.onmessage = function(e) {
            //     console.log(e.data);
            //     // conn.send('Hello World!');

            // };    

        </script>
    </body>
</html>

// conn.send(<?php $msg ?>);