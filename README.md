# Omarty-Backend
Omarty project by Diginovia , Source Code for backend and APIs

**Server URL**

https://plateform.omarty.net/

**Postman**

https://omarty.postman.co/workspace/

**Commands Websocket**

Run in terminal background : 

screen -S websocket-server 

php -q public_html/omartyapis/bin/chat-server.php

then Crt+A and d

***Usefull ssh commands**

check port
sudo lsof -i :3000 

terminate process
sudo kill <PID> 

  ***steps creating websocket on centos :***

  

  To enable and run a WebSocket server on your VPS with CentOS 7.9.2009 and cPanel & WHM, follow these step-by-step instructions:

1. Connect to your VPS via SSH:
   ```
   ssh username@your_server_ip
   ```

   Replace `username` with your SSH username and `your_server_ip` with the IP address of your VPS.

2. Install Node.js on your server. Run the following commands to add the Node.js repository and install Node.js:
   ```
   curl -sL https://rpm.nodesource.com/setup_14.x | sudo bash -
   sudo yum install -y nodejs
   ```

   This will install Node.js version 14.x.

3. Verify the installation of Node.js and npm (Node Package Manager) by running the following commands:
   ```
   node -v
   npm -v
   ```

   You should see the versions of Node.js and npm printed on the console.

4. Create a new directory for your WebSocket server files. For example, you can create a directory named `websocket`:
   ```
   mkdir websocket
   ```

5. Change to the newly created directory:
   ```
   cd websocket
   ```

6. Create a new file for your WebSocket server code. You can use any text editor to create the file. For example, using the `vi` editor:
   ```
   vi chat-server.js
   ```

7. Inside the `chat-server.js` file, write your WebSocket server code. Here's a simple example using the `ws` library:
   ```javascript
   enter your nodejs websocket server code here
   ```

   This code sets up a WebSocket server on port 3000 and echoes back any received message to the connected clients.

8. Save the file and exit the text editor.

 befor stpe 9 you need to create the package.json
 
 user the command
  ```
 npm init -y
 ```
 
 then open the file package.json and add the description and repository of your source code
  ```
 "description": "A WebSocket server for real-time chat application.",
 
 "repository": {
  "type": "git",
  "url": "https://github.com/your-username/your-repo.git"
},
  ```
 
9. Install the `ws` library using npm to add WebSocket support to your server:
   ```
   npm install ws
   ```

10. Start your WebSocket server by running the following command:
    ```
    node chat-server.js
    ```

    You should see the message "WebSocket server listening on port 3000" indicating that your server is running.

11. To keep the WebSocket server running even after you close the SSH session, you can use tools like `nohup` or `pm2`. Here's an example of using `pm2` to manage your Node.js process:
    ```
    sudo npm install -g pm2
    pm2 start chat-server.js
    ```

    The `pm2` tool will ensure that your WebSocket server continues running in the background.

12. Your WebSocket server is now enabled and running on your VPS. You can use the WebSocket server address (e.g., `ws://your_server_ip:3000`) to connect to it from client applications.

Please note that the above instructions assume a basic setup and may require modifications depending on your specific server configuration and requirements. Make sure to adjust the code and configuration according to your needs.

If you encounter any issues or have further questions, feel free to ask.

