<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;

use SplObjectStorage;

header("content-type: Application/json");

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $allowedUsers;
    protected $allowedGroups;
    protected $UserID;

    public function __construct() {
        
        $this->clients = new \SplObjectStorage;
        echo "Server Started.";

    }
/*
    public function onOpen(ConnectionInterface $connection) {
        include("./Config.php");    

         // Get the user ID and group from the WebSocket connection's query string
        //  $userId = $conn->httpRequest->getUri()->getQueryParameter('userId');
        //  $userId2 = $conn->httpRequest->getUri();         
        // echo $userId;
        //  $group = $conn->httpRequest->getUri()->getQueryParameter('group');
        // =========================================================================
        // $queryString = $conn->httpRequest->getUri()->getQuery();
        // echo $queryString;
        // echo "OK";
        // $queryParams = array();
        // parse_str($queryString, $queryParams);
        // $userId = isset($queryParams['userId']) ? $queryParams['userId'] : null;

        // // =========================================================================
        //  // If the user is not allowed to access the chat, close the WebSocket connection
        //  if (!in_array($userId, $this->allowedUsers)) {
        //      $conn->close();
        //      return;
        //  }
        // Get the query string from the WebSocket connection
        $queryString = $connection->httpRequest->getUri()->getQuery();
        // echo $queryString . "\n";
        // Parse the query string to an array of parameters
        // $queryParams = array();
        parse_str($queryString, $queryParams);
        // var_dump($queryParams);
        // Get the user ID from the query parameters
        $userId = isset($queryParams['userId']) ? $queryParams['userId'] : null;
        $BlkId = isset($queryParams['blockId']) ? $queryParams['blockId'] : null;
        // If the user is not allowed to access the chat, close the WebSocket connection
            // Check BlockID in DB.
            $SqlCheckBlk = $conn->query("SELECT ID FROM Block WHERE ID = '$BlkId'");
            if($SqlCheckBlk->num_rows > 0)
            {
                // Check User in Block in DB.
                $sqlCheckUserInBlk = $conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BlkId' AND ResidentID = '$userId'");
                // if User in Block => then Open Connection.
                if($sqlCheckUserInBlk->num_rows > 0)
                {
                    // Store the new connection to send messages to later
                    $this->clients->attach($connection);
                            
                    echo "New connection! ({$connection->resourceId})\n";
                    echo "OK";
                }
                else
                {
                    echo "OUT";
                }
                // if User is not in Block => then Don't Open Connection.
            }
            
        // if (!in_array($userId, $this->allowedUsers)) {
        //     $conn->close();
        //     return;
        // }

        // Store the new connection to send messages to later
        // $this->clients->attach($conn);
        
        // echo "New connection! ({$conn->resourceId})\n";
            
    }
*/

    public function onOpen(ConnectionInterface $connection) {
        include("./Config.php");
        $queryString = $connection->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        $userId = isset($queryParams['userId']) ? $queryParams['userId'] : null;
        $blockId = isset($queryParams['blockId']) ? $queryParams['blockId'] : null;
        $this->UserID = $userId;
        // If the block group doesn't exist yet, create it
        if (!isset($this->groups[$blockId])) {
            $this->groups[$blockId] = new \SplObjectStorage;
        }

        // Check if the user is allowed in the block group
        $sqlCheckUserInBlk = $conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$blockId' AND ResidentID = '$userId'");
        if ($sqlCheckUserInBlk->num_rows > 0) {
            // Add the connection to the block group
            $this->groups[$blockId]->attach($connection);

            echo "New connection! ({$connection->resourceId})\n";
            echo "User $userId joined block $blockId\n";
            $this->getMessages($blockId);
        } else {
            // User is not allowed in the block group, close the connection
            $connection->close();
            echo "User $userId is not allowed in block $blockId\n";
        }
    }

    // send Message only as text.
    public function onMessage(ConnectionInterface $from, $msg) {
        // Include Config.php file.
        include("./Config.php");
        date_default_timezone_set('Africa/Cairo');
        $Date = date("Y-m-d H:i:s");

        // Get the block ID of the sender's connection
        $blockId = $this->getBlockId($from);
        
        // Get Number of residents in Block.
        $sqlGetBlockRes = $conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$blockId'");

        $numRecv = $sqlGetBlockRes->num_rows;
        echo sprintf('Connection %d sending message "%s" to %d other connection%s' . "\n"
            , $from->resourceId, $msg, $numRecv, $numRecv == 1 ? '' : 's');

        // Broadcast the message to all connections in the sender's block group
        foreach ($this->groups[$blockId] as $client) {
            if ($from !== $client) {
                // Get User Apartment.
                $sqlGetResApt = $conn->query("SELECT ApartmentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$this->UserID' AND BlockID = '$blockId'");
                if($sqlGetResApt->num_rows > 0)
                {
                    $AptID = $sqlGetResApt->fetch_row();
                }
                // Save Message Data to DB.
                $sqlSaveMsg = $conn->query("INSERT INTO Message (Message, SenderID , BlockID , ApartmentID, CreatedAt) VALUES ('$msg', '$this->UserID', '$blockId', '$AptID[0]', '$Date')");
                $client->send($msg);
            }
        }
    }

// send Message as text and files.
/*
    public function onMessage(ConnectionInterface $from, $msg) {
        // Include Config.php file.
        include("./Config.php");
        date_default_timezone_set('Africa/Cairo');
        $Date = date("Y-m-d H:i:s");
    
        // Get the block ID of the sender's connection
        $blockId = $this->getBlockId($from);
        
        // Decode the JSON payload
        $payload = json_decode($msg);
    
        // Extract the message and file from the payload
        $message = isset($payload->message) ? $payload->message : '';
        $fileData = isset($payload->fileData) ? $payload->fileData : null;
    
        // If a file was included in the payload, decode it from base64 and save it to disk
        if ($fileData) {
            $fileName = isset($payload->fileName) ? $payload->fileName : '';
            $fileType = isset($payload->fileType) ? $payload->fileType : '';
    
            $filePath = './uploads/' . $fileName;
            $fileContents = base64_decode($fileData);
    
            file_put_contents($filePath, $fileContents);
        }
    
        // Get Number of residents in Block.
        $sqlGetBlockRes = $conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$blockId'");
        $NumberOfResidents = $sqlGetBlockRes->num_rows;
    
        // Prepare the message to send
        $messageToSend = $this->UserID . "|" . $message . "|" . $Date;
    
        // Send the message to all connections in the block group
        foreach ($this->groups[$blockId] as $client) {
            $client->send($messageToSend);
        }
    }
    
    Client Side =>
    // Get the file input element
    var fileInput = document.getElementById('fileInput');

    // Get the selected file (if any)
    var file = fileInput.files[0];

    // Create a FileReader to read the file contents
    var reader = new FileReader();

    // When the file is loaded, encode it as base64 and send it to the server
    reader.onload = function(e) {
        var fileData = e.target.result.split(',')[1];

        // Create the message payload
        var payload = {
            message: message,
            fileData: fileData,
            fileName: file.name,
            fileType: file.type
        };

        // Send the payload to the server
        connection.send(JSON.stringify(payload));
    };

    // Read the file contents as a data URL
    reader.readAsDataURL(file);

*/
    public function onClose(ConnectionInterface $conn) {
        $blockId = $this->getBlockId($conn);

        // Remove the connection from the block group
        if (isset($this->groups[$blockId])) {
            $this->groups[$blockId]->detach($conn);
        }

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }

    private function getBlockId(ConnectionInterface $conn) {
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        return isset($queryParams['blockId']) ? $queryParams['blockId'] : null;
    }

    private function getMessages($BLKID)
    {
        include("./Config.php");
        // Get Messages of this block with limit 100 messages per page.
        // $Page = $_POST["page"];
        // if(empty($Page))
        // {
        //     $Page = 1;
        // }
        // $Limit = 100;
        // $Start = ($Page - 1) * $Limit;

        // Get Block name.
        $sqlGetBlkData = $conn->query("SELECT BlockName FROM Block WHERE ID = '$BLKID'");
        if($sqlGetBlkData->num_rows > 0)
        {
            $BlkData = $sqlGetBlkData->fetch_row();
            $BlkName = $BlkData[0];
        }
        $count = 0;
        $sqlGetMsg = $conn->query("SELECT Message, Attach, SenderID, ApartmentID, BlockID, ChatID, CreatedAt FROM Message WHERE BlockID = '$BLKID'");
        while($MsgData = $sqlGetMsg->fetch_row())
        {
            // Get Sender Name.
            $sqlGetResName = $conn->query("SELECT Name FROM Resident_User WHERE ID = '$MsgData[2]'");
            if($sqlGetResName->num_rows > 0)
            {
                $ResData = $sqlGetResName->fetch_row();
                $ResName = $ResData[0];
            }
            else
            {
                $ResName = $MsgData[2];
            }
            // Get Sender Apatment name, Floor number.
            $sqlGetAptData = $conn->query("SELECT ApartmentName, FloorNum FROM Apartment WHERE ID = '$MsgData[4]'");
            if($sqlGetAptData->num_rows > 0)
            {
                $AptData = $sqlGetAptData->fetch_row();
                $AptName = $AptData[0];
                $AptFloorNum = $AptData[1];
            }
            else
            {
                $AptName = $MsgData[4];
                $AptFloorNum = NULL;
            }
            $MsgDataArr[$count] = 
            [
                "senderName" => $ResName,
                "apartmentName" => $AptName,
                "apartmentFloorNum" => $AptFloorNum,
                "blockName" => $BlkName,
                "message" => $MsgData[0],
                "attach" => $MsgData[1],
                "createdAt" => $MsgData[6]
            ];
            $count++;
        }

        
        print_r(json_encode(array_values($MsgDataArr)));
    }
}

// $Chat = new Chat(1);
