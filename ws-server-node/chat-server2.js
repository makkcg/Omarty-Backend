const WebSocket = require('ws');

const server = new WebSocket.Server({ port: 3003 });

server.on('connection', (client) => {
  console.log('A new client has connected');

  client.on('message', (message) => {
    const senderClientId = getClientId(client);
    console.log(`Received message from client ${senderClientId}:`, message);

    server.clients.forEach((connectedClient) => {
      if (connectedClient.readyState === WebSocket.OPEN) {
        connectedClient.send(`Client ${senderClientId}: ${message}`);
      }
    });
  });

  client.on('close', () => {
    console.log('A client has disconnected');
  });
});

console.log('WebSocket server started on port 3005');

function getClientId(client) {
  // Generate a unique client ID based on the client's WebSocket object
  return Math.random().toString(36).substr(2, 9);
}
