


![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Chat Websocket API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference

### **Request Header**
Each Request to the API should include the following parameters in the header of the request.

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Accept-Encoding` | `gzip, deflate, br` | **Required**. Accepted encoding types |
| `Content-Type` | `application/x-www-form-urlencoded` | **Required**. Content type|
------------------------------
### **Requests & Responses**

#### **1- Register to system**
To Register into Omarty system.

Request should include the header parameters

```http
  https://plateform.omarty.net/omartyapis/Auth/Register/
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `pnum` | `Number` | **Required**. User phone number|
| `email` | `String` | **Required**. User email|
| `password` | `string` | **Required**. User's account password|
| `confirmPassword` | `string` | **Required**. Confirmation of account password|
| `name` | `String` | **Required**. User's name|
| `longitude` | `Number` | **Optional**. Device longitude|
| `latitude` | `number` | **Optional**. Device latitude|

#### `pnum`

- User primary phone number which he can use to login.

#### `email`

- User email which he can use to login.
- 
#### `password & confirmPassword`

- Entering account's password and confirm that user got it right.

#### `name`

- User First and last Name.


#### `longitude & latitude`

- They are used to to specify user location to set it in the logs table.

#### Example 1
Register using email "test@test.com" And phone number "01011223344" in the email parameter would result the same output.

```javascript
{
	"pnum": 01011223344,
	"email": "test@test.com",
	"password": "test"
	"confirmPassword": "test"
	"name": "TestName",
	"latitude": 29.9453866,
	"longitude": 31.2900529,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": "A new record added"
}
```

#### ERROR Response
The Response is JSON object containing array of objects named `status` and `message` the "message" array shows the body of the response and status shows response status.

##### Case 1 : email already exist.
```javascript
{
    "status": 403,
    "message": "email already exist."
}
```
##### Case 2 : phone number already exist.
```javascript
{
    "status": 403,
    "message": "Phone number already exist."
}
```
##### Case 3 : passwords don't match.
```javascript
{
    "status": 401,
    "message": "password dosn't match confirm password."
}
```

## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)

