

![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Chat Websocket API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **Create Apartment**
we use the following URL to access reset password endpoints
```http
  https://plateform.omarty.net/omartyapis/Create/
```

### **Request Header**
Each Request to the API should include the following parameters in the header of the request.

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Accept-Encoding` | `gzip, deflate, br` | **Required**. Accepted encoding types |
| `Content-Type` | `application/x-www-form-urlencoded` | **Required**. Content type|
| `Authorization` | `Bearer` | **Required**. Bearer Token|

------------------------------
### **Requests & Responses**

#### **1- Create Apartment**
to Create record for this unit in Data Base.

Request should include the header parameters
```http
  https://plateform.omarty.net/omartyapis/Create/
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `api` | `String` | **Required**. End point name|
| `floorNum` | `Number` | **Required**. New Apartment floor number|
| `aptNum` | `Number` | **Required**. Number of the new unit in the block|
| `blockId` | `Number` | **Optional**. User Block ID|
| `apartmentId` | `Number` | **Optional**. User Apartment ID|
| `longitude` | `Number` | **Optional**. Device Longitude|
| `latitude` | `Number` | **Optional**. Device Latitude|

#### `api`

- End point that will trigger creating unit is `CreateApartment`.

#### `floorNum`

- New unit floor number.

#### `aptNum`

- number of the new unit in the block.

#### `blockId`

- User's block ID which will be added to.

#### `apartmentId`

- User's apartment ID.

#### `longitude`

- Device longitude that will be stored in logs table.

#### `latitude`
- Device latitude that will be stored in logs table.

#### Example 1

```javascript
{
	"api": "CreateApartment",
	"floorNum": 1,
	"aptNum" : 2,
	"blockId": 4,
	"apartmentId": 1,
	"longitude": 121.12221,
	"latitude" : 20.233,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": "Apartment registered."
}
```

#### ERROR Response
The Response is JSON object containing array of objects named `status` and `message` the "message" array shows the body of the response and status shows response status.

##### Case 1 : User Does not belong to this block or he is not the manager.
```javascript
{
    "status": 200,
    "message": "User does not have permissions in this block."
}
```
##### Case 2 : apartment number already has its resident.
```javascript
{
    "status": 200,
    "message": "Apartment already registered."
}
```

## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)

