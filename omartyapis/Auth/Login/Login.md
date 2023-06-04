

![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Chat Websocket API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference

This API Connects to a Web Socket Server at the follwing URL :

```http
  GET https://plateform.omarty.net/omartyapis/Auth/Login/
```
### **Request Header**
Each Request to the API should include the following parameters in the header of the request.

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Accept-Encoding` | `gzip, deflate, br` | **Required**. Accepted encoding types |
| `Content-Type` | `application/x-www-form-urlencoded` | **Required**. Content type|
------------------------------
### **Requests & Responses**

#### **1- Login to system**
To Login into Omarty system.

Request should include the header parameters

```http
  https://plateform.omarty.net/omartyapis/Auth/Login/
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `email` | `string` | **Required**. User email Or phone number user has registered|
| `password` | `string` | **Required**. User's account password|
| `googleToken` | `string` | **Required**. User's Google Token that is used for notification system|
| `os` | `string` | **Optional**. Device OS either Android or IOS|
| `latitude` | `string` | **Optional**. Device latitude|
| `longitude` | `string` | **Optional**. Device longitude|
| `deviceId` | `string` | **Required**. Device ID|

#### `email`

- If user entered his email ; will search for this email in users DB and authenticate him through his email and password since it is a unique value for each user.
- If user entered his phone number , will search for this phone number in users DB and authenticate him through his phone number and password since it is a unique value for each user.

#### `googleToken`

- If user wants his notifications to work properly google token is used to send the notification.

#### `longitude & latitude`

- They are used to to specify user location to set it in the logs table.

#### `deviceId`

- Its used to check if user is using multiple devices to send notifications to all devices.

#### Example 1
Login using email "mohamedwaheed73780@gmail.com" or phone number "01014584099" in the email parameter would result the same output.

```javascript
{
	"email": "mohamedwaheed73780@gmail.com",
	"password": "mohamedwaheed"
	"googleToken": 2
	"os": "android",
	"latitude": "29.9453866",
	"longitude": "31.2900529",
	"deviceId": "123234132",
}
```

#### Response
The Response is JSON object containing array of objects named "status" and "data" the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": {
        "Data": {
            "iat": 1685877089,
            "id": "2",
            "userName": "Muhammad Waheed",
            "email": "mohamedwaheed73780@gmail.com",
            "phoneNumber": "01014584099",
            "residentImage": "https://plateform.omarty.net/omartyapis/Images/profilePictures/"
        },
        "Token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpYXQiOjE2ODU4NzcwODksImlkIjoiMiIsInVzZXJOYW1lIjoiTXVoYW1tYWQgV2FoZWVkIiwiZW1haWwiOiJtb2hhbWVkd2FoZWVkNzM3ODBAZ21haWwuY29tIiwicGhvbmVOdW1iZXIiOiIwMTAxNDU4NDA5OSIsInJlc2lkZW50SW1hZ2UiOiJodHRwczovL3BsYXRlZm9ybS5vbWFydHkubmV0L29tYXJ0eWFwaXMvSW1hZ2VzL3Byb2ZpbGVQaWN0dXJlcy8ifQ.zwH6Xsh4ojFQ0JYSCYecH61ibYMr3eGBB6hCgNYvvvg"
    }
}
```


#### **2- login with social media account**
Used to log in with google emails without entering password

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

## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)

