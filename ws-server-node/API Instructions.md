https://readme.so/editor


![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Chat Websocket API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference

This API Connects to a Web Socket Server at the follwing URL :

```http
  ws://ws.omarty.net:PortNumber
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
| `message`      | `string` | **Required**. can be Empty string "" |

#### targetUserID

- If set to 0 ; it will send the chat history of all the users in the BlockID.
- If set to value > 0 , it should be the target user ID , to retrieve the chat between UserID and targetUserID 



#### Example 1
Request chat history of BlockID = 2 , from the user UserID = 1 and all the other BlockID = 2 users 

```javascript
{
  "BlockID": 2,
  "UserID": 1,
  "UserFName": "",
  "message": "",
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
  "message": "",
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


## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)


