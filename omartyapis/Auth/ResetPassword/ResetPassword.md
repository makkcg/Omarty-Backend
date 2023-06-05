


![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Chat Websocket API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **Reset Password**
we use the following URL to access reset password endpoints
```http
  https://plateform.omarty.net/omartyapis/Auth/ResetPassword/
```

### **Request Header**
Each Request to the API should include the following parameters in the header of the request.

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Accept-Encoding` | `gzip, deflate, br` | **Required**. Accepted encoding types |
| `Content-Type` | `application/x-www-form-urlencoded` | **Required**. Content type|
------------------------------
### **Requests & Responses**

#### **1- Send email to get OTP**
To get OTP code to change password.

Request should include the header parameters
```http
  https://plateform.omarty.net/omartyapis/Auth/ResetPassword/
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `api` | `String` | **Required**. End point name|
| `email` | `String` | **Required**. User email to receive OTP code on|

#### `api`

- End point that will trigger sending the OTP is `sendmailOTP`.

#### `email`

- User email to receive OTP code on.
