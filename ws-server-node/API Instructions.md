
![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Chat Websocket API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference

This API Connects to a Web Socket Server at the follwing URL :

```http
  GET ws://ws.omarty.net:PortNumber
```
### **Request Header**
Each Request to the API should include the following parameters in the header of the request.

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `userid` | `number` | **Required**. The User ID of the user opening chat session |
| `blockid` | `number` | **Required**. The Block ID of the user opening chat session |

------------------------------
### **Requests & Responses**

#### **1- Receive Chat History**
To request the chat history of a group chat in spceific block , or request chat between the User and another user (one to one), he request is sent as JSON, the respons is JSON

Request should include the header parameters

```http
  ws://ws.omarty.net:PortNumber
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `requesttype`      | `string` | **Required**. Set to "loadprev" |
| `UserID`      | `string` | **Required**. Id of the user |
| `BlockID`      | `string` | **Required**. Current BlockID  |
| `targetUserID`      | `string` | **Required**. for group (BlockID) chat history, or another UserID for chats between two users  |
| `UserFName`      | `string` | **Required**. can be Empty string "" |
| `message`      | `string` | **Required**. number of last messages to retrieve from history, if set to "0" retrieve all previous messages |

#### `targetUserID`

- If set to 0 ; it will send the chat history of all the users in the BlockID.
- If set to value > 0 , it should be the target user ID , to retrieve the chat between UserID and targetUserID 

#### `message`

- If set to "0" response will include all the previous history messages from the begining
- if set to any number , response will only include the x number of last messages



#### Example 1
Request chat history of BlockID = 2 , from the user UserID = 1 and all the other BlockID = 2 users 

```javascript
{
  "BlockID": 2,
  "UserID": 1,
  "UserFName": "",
  "message": "0",
  "requesttype": "loadprev",
  "targetUserID": 0
}
```

#### Example 2
Request chat history between two users UserID = 1 , and the other user targetUserID = 2 

```javascript
{
  "BlockID": 2,
  "UserID": 1,
  "UserFName": "",
  "message": "100",
  "requesttype": "loadprev",
  "targetUserID": 2
}
```

#### Response
The Response is JSON object containing array of objects named "ChatLog" orderd Assending by TimeStamp

```javascript
{
    "ChatLog": [
        {
            "ID": 84,
            "UserID": 200,
            "BlockID": 2,
            "UserFName": "yasser Khalifa",
            "ChatMessage": "yasser Khalifa Hello, everyone!",
            "TimeStamp": "2023-05-31 08:32:20",
            "targetUserID": 1
        },
        {
            "ID": 85,
            "UserID": 1,
            "BlockID": 2,
            "UserFName": "Moo Khalifa",
            "ChatMessage": "Moo Khalifa Hello, everyone!",
            "TimeStamp": "2023-05-31 08:32:30",
            "targetUserID": 200
        }
    ]
}
```


#### **2- Send Chat Message**
Used to Send chat message to a block (Group of users) , or Send chat message to specific user (one to one), he request is sent as JSON, the respons is JSON

Request should include the header parameters

```http
  ws://ws.omarty.net:PortNumber
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `requesttype`      | `string` | **Required**. Set to "send" |
| `UserID`      | `string` | **Required**. Id of the user |
| `BlockID`      | `string` | **Required**. Current BlockID  |
| `targetUserID`      | `string` | **Required**. for group (BlockID) chat, or another User ID for chats between two users  |
| `UserFName`      | `string` | **Required**. Nick name or user name that will appear in chat |
| `message`      | `string` | **Required**. the message to be sent |

#### targetUserID

- If set to 0 ; it will send the message to of all the users in the BlockID.
- If set to value > 0 , it should be the target user ID , to message from UserID to targetUserID 


#### Example 1
The Request for sending message from the user UserID = 1 to all users in BlockID = 2  

```javascript
{
  "BlockID": 2,
  "UserID": 1,
  "UserFName": "Mo Khalifa",
  "message": "Hello, My neighbours!",
  "requesttype": "send",
  "targetUserID": 0
}
```

#### Example 2
The Request for sending message from the user UserID = 1 to the other user targetUserID = 2 

```javascript
{
  "BlockID": 2,
  "UserID": 1,
  "UserFName": "Mo Khalifa",
  "message": "Hello, John How its going!",
  "requesttype": "send",
  "targetUserID": 2
}
```

#### ERROR Response
The Response is JSON object containing two praramets 
`msg` : is the error/response message from the server
`Obj` : is the error/response JSON object

```javascript
{
    "msg": "Invalid headers, userid and blockid",
    "Obj": {
        "sec-websocket-version": "13",
        "sec-websocket-key": "9MmX3TxQKboEzn013BOr5Q==",
        "connection": "Upgrade",
        "upgrade": "websocket",
        "userid": "3",
        "blockida": "1",
        "sec-websocket-extensions": "permessage-deflate; client_max_window_bits",
        "host": "ws.omarty.net:3008"
    }
}
```

```javascript
{
    "msg": "Invalid parameters",
    "Obj": {
        "BlockID": 1,
        "UserID": 3,
        "UserFName": "Omer Khalifa",
        "message": "Omer Khalifa Hello, everyone!",
        "requesttype": "send",
        "targetUserID": "s" <-----Number not string--
    }
```

#### Message sent Response
The Response is JSON object containing the sent message object as follows

```javascript
{
        UserID: UserID,
        BlockID: BlockID,
        UserFName: UserFName,
        ChatMessage: msg,
        TimeStamp: TimeStamp,
        errormsg: '',
        targetUserID: targetUserID,
		SenderID: UserID,
      }
```
#### **3- Get Users Online Status for A Block**
This end point retrieves the status of the users in Block, he request is sent as JSON, the respons is JSON

Request should include the header parameters

```http
  ws://ws.omarty.net:PortNumber
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `requesttype`      | `string` | **Required**. Set to "getuserstatus" |
| `UserID`      | `string` | **Required**. Id of the user |
| `BlockID`      | `string` | **Required**. Current BlockID  |
| `targetUserID`      | `string` | **Required**.  Set to 0 |
| `UserFName`      | `string` | **Required**.  leave empty "" |
| `message`      | `string` | **Required**. leave empty "" |

#### `message`

- If set to 0 ; will retrieve all the users' status in all blocks.
- If else ; it will user the  `BlockID` to retrive only the status of users in this block.


#### (Request) Example 

The Request to retrive users status in BlockID=1
note that the

`requesttype`= "getuserstatus"

```javascript
{
  "BlockID": 1,
  "UserID": 5,
  "UserFName": "",
  "message": "",
  "requesttype": "getuserstatus",
  "targetUserID": 0
}
```

#### Response
The Response is JSON object containing two praramets 

`msg` : is the error/response message from the server

`Obj` : is the JSON object that contains the array of objects in `UsersStatusOnline`

in each object `UserID`, `BlockID` and `Status` which indecates the online status if 0 means offline, if 1 means connected or online

#### Example response

```javascript
{
    "msg": "USers Status",
    "Obj": {
        "UsersStatusOnline": [
            {
                "ID": 28,
                "BlockID": 1,
                "UserIDs": "3",
                "UserFName": "محمد عبدالله",
                "Status": 1,
                "wsClientID": null
            },
            {
                "ID": 29,
                "BlockID": 1,
                "UserIDs": "4",
                "UserFName": "محمد عبدالله",
                "Status": 1,
                "wsClientID": null
            }
        ]
    }
}
```

## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

