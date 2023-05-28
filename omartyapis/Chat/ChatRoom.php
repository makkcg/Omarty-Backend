<?php
    // $msg = $_GET["msg"];
    // $User = $_GET["user"];
    // // $Send = $_GET["send"];

?>
<html>
    <body>
        <script>
            var conn = new WebSocket('wss://kcgwebservices.net:8088');
            conn.onopen = function(e) {
                console.log("Connection established!");
            };
            
            conn.onmessage = function(e) {
                console.log(e.data);
                // conn.send('Hello World!');
                
            };    
            
        </script>
    </body>
</html>

 