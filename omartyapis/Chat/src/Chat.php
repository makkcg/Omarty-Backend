<?php
namespace MyApp;
use Ratchet\MessageComponentInterface;
use Ratchet\ConnectionInterface;
use SplObjectStorage;

class Chat implements MessageComponentInterface {
    protected $clients;
    protected $allowedUsers;
 
    public function __construct() {
        $this->clients = new \SplObjectStorage;
        $this->allowedUsers = array(1, 2, 3); // Example: user IDs allowed to access chat
        echo "Server Started";
    }
 
    public function onOpen(ConnectionInterface $conn) {
        // Store the new connection to send messages to later
        $this->clients->attach($conn);
    }

    public function onMessage(ConnectionInterface $from, $msg) {
        $numRecv = count($this->clients) - 1;
        $messageData = json_decode($msg);
        $userId = $messageData->userId;
        $message = $messageData->message;
        
        // Check if the user is allowed to access the chat
        if (in_array($userId, $this->allowedUsers)) {
            // Send the message to all clients except the sender
            foreach ($this->clients as $client) {
                if ($from !== $client) {
                    $client->send($message);
                }
            }
        }
    }
 
    public function onClose(ConnectionInterface $conn) {
        // Remove the connection when it's closed
        $this->clients->detach($conn);
    }
 
    public function onError(ConnectionInterface $conn, \Exception $e) {
        $conn->close();
    }
}