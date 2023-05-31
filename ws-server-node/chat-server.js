const WebSocket = require('ws');
const mysql = require('mysql');

// Create a MySQL connection pool
const pool = mysql.createPool({
  host: 'localhost',
  user: 'cpomartyadmi_omartydbus',
  password: 'kzEiLr7U.[cX1x6!',
  database: 'cpomartyadmi_omarydb',
  timezone: 'Africa/Cairo',
});

// WebSocket server
const wss = new WebSocket.Server({ port: 3008 });

// Start the WebSocket server
wss.on('listening', () => {
  console.log('WebSocket server is running on port 3008');
});

wss.on('error', (error) => {
	sendErrorMessage(ws, 'Error has occured in the Websocket server', error);
  //console.error('WebSocket server error:', error);
  updateUserStatus(ws,ws.UserID, 0);
});

wss.on('close', () => {
   // Handle WebSocket connection close
	updateUserStatus(ws,ws.UserID, 0);
});

wss.on('connection', (ws, req) => {
  // Handle new WebSocket connection
	
	


  // Check if the required headers exist and have the correct type
  if (
    typeof req.headers.userid !== 'string' ||
    typeof req.headers.blockid !== 'string'
  ) {
    sendErrorMessage(ws, 'Invalid headers, userid and blockid',req.headers);
    ws.close();
    return;
  }
  
  // Get UserID and BlockID from the request headers, add them the the connection client parameters
  const { userid, blockid } = req.headers;

  ws.UserID = Number(userid);
  ws.BlockID = Number(blockid);
  // add newly connected client UserID to the array 
  const connectedUserIDs = Array.from(wss.clients).map(client => client.UserID);
  const connectedUsersNumber= connectedUserIDs.length;
  
  //send message to the connected client with array of all connected usersIDs
  sendErrorMessage(ws, 'New Client Connected added to Array', connectedUserIDs);
  
  //ws.send(`New user connection ${ws.UserID} -- blockid ${JSON.stringify(connectedUserIDs)}`);
  //ws.send(JSON.stringify(connectedUserIDs));
  
  //update user status to be online
	updateUserStatus(ws,ws.UserID , 1);
  ws.on('message', (message) => {
	  updateUserStatus(ws,ws.UserID, 1);
    // Handle incoming WebSocket messages
    let data;
    try {
      data = JSON.parse(message);
    } catch (error) {
      sendErrorMessage(ws, 'Invalid Request Parameters', data);
      return;
    }

    const { BlockID, UserID, UserFName, message: msg, requesttype, targetUserID } = data;

    // Check if all required parameters are present and have the correct types
    if (
      typeof BlockID !== 'number' ||
      typeof UserID !== 'number' ||
      typeof UserFName !== 'string' ||
      typeof msg !== 'string' ||
      typeof requesttype !== 'string' ||
      typeof targetUserID !== 'number'
    ) {
      sendErrorMessage(ws, 'Invalid parameters', data);
      return;
    }
	/// switch between functions based on the incoming request type
	
    switch (requesttype) {
      case 'send':
        processAndSendMessage(ws.BlockID, ws.UserID, UserFName, msg, targetUserID, ws);
        break;
      case 'loadprev':
        loadPreviousMessages(UserID,BlockID, targetUserID, ws, Number(msg));
        break;
      case 'getuserstatus':
        getUserStatus(BlockID, ws);
        break;
      default:
        sendErrorMessage(ws, 'The Request Type is Not correct or not exist', data);
    }
  });

  ws.on('close', () => {
    // Handle WebSocket connection close
	
    // User status changes to offline
    updateUserStatus(ws,ws.UserID, 0);
	
  });

  ws.on('error', (error) => {
    // Handle WebSocket connection error
    sendErrorMessage(ws, 'WebSocket error occurred', error);
	updateUserStatus(ws,ws.UserID, 0);
  });
});

function processAndSendMessage(BlockID, UserID, UserFName, msg, targetUserID, ws) {
  const TimeStamp = new Date().toISOString().slice(0, 19).replace('T', ' ');

  // Check if UserID and BlockID exist in the usersblocks table
  pool.query(
    'SELECT * FROM Chat WHERE UserIDs = ? AND BlockID = ?',
    [UserID, BlockID],
    (error, results) => {
      if (error) {
        sendErrorMessage(ws, 'Database error', error);
        return;
      }

      if (results.length === 0) {
        // If UserID and BlockID don't exist, insert them
        pool.query(
          'INSERT INTO Chat (UserIDs, BlockID, UserFName) VALUES (?, ?, ?)',
          [UserID, BlockID, UserFName],
          (error) => {
            if (error) {
              sendErrorMessage(ws, 'Database error', error);
              return;
            }
            //saveAndSendMessage(BlockID, UserID, UserFName, msg, TimeStamp, targetUserID, ws);
          }
        );
		saveAndSendMessage(BlockID, UserID, UserFName, msg, TimeStamp, targetUserID, ws);
      } else {
        saveAndSendMessage(BlockID, UserID, UserFName, msg, TimeStamp, targetUserID, ws);
      }
    }
  );
}

function saveAndSendMessage(BlockID, UserID, UserFName, msg, TimeStamp, targetUserID, ws) {
  // Save the message to the ChatLog table
  pool.query(
    'INSERT INTO ChatLog (UserID, BlockID, ChatMessage, UserFName, TimeStamp, targetUserID) VALUES (?, ?, ?, ?, ?, ?)',
    [UserID, BlockID, msg, UserFName, TimeStamp, targetUserID],
    (error) => {
      if (error) {
        sendErrorMessage(ws, 'Database error', error);
        return;
      }

      // Prepare the response object
      const response = {
        UserID: UserID,
        BlockID: BlockID,
        UserFName: UserFName,
        ChatMessage: msg,
        TimeStamp: TimeStamp,
        errormsg: '',
        targetUserID: targetUserID,
		SenderID: UserID,
      };

      if (targetUserID === 0) {
        // Send the received ChatMessage to all users with the same BlockID except the sender
		const senderClientId=ws.UserID;
		wss.clients.forEach((connectedClient) => {
			
				//use if you want to execlude the sender user from recieving the his message
			  //if (connectedClient.readyState === WebSocket.OPEN && connectedClient.UserID !== UserID && connectedClient.BlockID === BlockID) {
				 //DONT execlude the sender
			  if (connectedClient.readyState === WebSocket.OPEN && connectedClient.BlockID === BlockID) {
				//connectedClient.send(`Client ${senderClientId}: ${msg}`);
				
				//send the message to all the online users at the same blockID 
				connectedClient.send(JSON.stringify(response)); 
				
				//sendErrorMessage(ws, 'Database error', error);
			  }
			});
      } else {
        // Send the ChatMessage to the specific targetUserID if target user is connected
        const targetUser = Array.from(wss.clients).find((connectedTClient) => {
          return connectedTClient.readyState === WebSocket.OPEN && connectedTClient.UserID === targetUserID;
        });

        if (targetUser) {
			//send the message to the target user id only 
			targetUser.send(JSON.stringify(response));
			//targetUser.send(`Client ${UserID} sent to ${targetUserID}: ${msg}`);
        }
      }
    }
  );
}

function updateUserStatus(ws, UserID, IsOnline) {
  ws.IsOnline = IsOnline;
  // Update the user status in the database
 /*  pool.query(
    'UPDATE Chat SET Status = ? WHERE UserIDs = ?',
    [IsOnline, ws.UserID],
    (ws, error) => {
      if (error) {
		  sendErrorMessage(ws, 'Database error:', error)
        //console.error('Database error:', error);
        // Handle the error, e.g., send an error message to the WebSocket client
      }
	  
    }
  ); */
  
   pool.query(
    'UPDATE Chat SET Status = ? WHERE UserIDs = ?',
    [IsOnline, ws.UserID],
    (error, results) => {
      if (error) {
        sendErrorMessage(ws, 'Database error', error);
        return;
      }

      const response = {
        UsersStatusObj: results,
      };
      //ws.send(JSON.stringify(response));
	  sendErrorMessage(ws, 'Update users status', {"userid":ws.UserID,"IsOnline":IsOnline});
    }
  );
}

function sendErrorMessage(ws, message, errorobj) {
  const response = {
    msg: message,
    Obj: errorobj,
  };
  ws.send(JSON.stringify(response));
}

function getUserStatus(BlockID, ws) {
  // Get the status of all users in the same BlockID
  pool.query(
    'SELECT * FROM Chat WHERE BlockID = ?',
    [BlockID],
    (error, results) => {
      if (error) {
        sendErrorMessage(ws, 'Database error', error);
        return;
      }

      const response = {
        UsersStatusOnline: results,
      };
      ws.send(JSON.stringify(response));
	  sendErrorMessage(ws, 'USers Status', response);
    }
  );
}

function loadPreviousMessages(UserID, BlockID, targetUserID, ws, NoOfMsgs=0) {
	//if load all messages history from the begining NoOfMsgs===0 Default
	if(NoOfMsgs===0){
		if(targetUserID===0){
		  // Load previous messages from the ChatLog table for all users in the same BlockID
		  pool.query(
			'SELECT * FROM ChatLog WHERE BlockID = ? ORDER BY ID ASC',
			[BlockID],
			(error, results) => {
			  if (error) {
				sendErrorMessage(ws, 'Database error', error);
				return;
			  }

			  const response = {
				ChatLog: results,
			  };
			  ws.send(JSON.stringify(response));
			}
		  );
	}else{
		 // Load previous messages from the ChatLog table the two users UserID and targetUserID
		pool.query(
			'SELECT * FROM ChatLog WHERE targetUserID <> 0 AND ((UserID = ? AND targetUserID = ?) OR (UserID = ? AND targetUserID = ?)) ORDER BY TimeStamp ASC;',
			[UserID, targetUserID, targetUserID, UserID ],
			(error, results) => {
			  if (error) {
				sendErrorMessage(ws, 'Database error', error);
				return;
			  }

			  const response = {
				ChatLog: results,
			  };
			  ws.send(JSON.stringify(response));
			}
		  );
	}
	}//if NoOfMsgs > 0 load only the last NoOfMsgs number of messages from history
	else
	{
		///add code later
	}
	
}
