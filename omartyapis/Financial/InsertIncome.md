![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Financial API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **Insert Income (اضافة ايراد)**
we use the following URL to Insert Income to units and Blocks. And the user who uses it is only Block Manager.
```http
  https://plateform.omarty.net/omartyapis/Financial/
```

### **Request Header**
Each Request to the API should include the following parameters in the header of the request.

| Parameter | Type     | Description                |
| :-------- | :------- | :------------------------- |
| `Accept-Encoding` | `gzip, deflate, br` | **Required**. Accepted encoding types |
| `Content-Type` | `multipart/form-data; boundary=<calculated when request is sent>` | **Required**. Content type|
| `Authorization` | `Bearer` | **Required**. Bearer Token|

------------------------------
### **Requests & Responses**

#### **1- Insert Income**
to Create Income records for this Block and its units in Data Base.

Request should include the header parameters
```http
  https://plateform.omarty.net/omartyapis/Financial/
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `api` | `String` | **Required**. End point name|
| `blockId` | `Number` | **Required**. Block ID in DB|
| `apartmentId` | `Number` | **Required**. Admin's Unit ID in DB|
| `amount` | `Number` | **Required**. Income Amount|
| `attach` | `File` | **Optional**. Income Attachment|
| `incomeStatment` | `String` | **Required**. Income Statment is the explaination of why does this Income exist|
| `flagApartmentIncome` | `Number` | **Required**. Flag to tell that Income is for Unit in the block, AND its value is the target unit to get that Income|
| `longitude` | `String` | **Required**. Longitude of user device ID for collecting all data of user when he/she performs this action.|
| `latitude` | `String` | **Required**. Latitude of user device ID for collecting all data of user when he/she performs this action.|


#### `api`

- End point that will trigger Income Insertion is `insertIncome`.

#### `blockId`

- Block ID in the DB.

#### `apartmentId`

- Admin's Apartment ID in data base.

#### `amount`

- money amount of this Income.

#### `incomeStatment`

- Income Statment which explains why is this Income in database.

#### `flagApartmentIncome`

- Flag that this Income is for a Unit in Block and set the flag its value must be target unit ID that got the Income.

#### `longitude`

- Longitude of user device ID for collecting all data of user when he/she performs this action.

  #### `Latitude`

  - Latitude of user device ID for collecting all data of user when he/she performs this action.


#### Example 1

```javascript
{
	"api": "insertIncome",
	"blockId": 1,
	"apartmentId" : 1,
	"amount": 120,
	"attach": ,
	"feeStatment" : "Explain this Income",
	"flagApartmentFee" : 1,
  	"longitude" : ,
	"latitude" : ,

}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": "Fee Inserted on Unit 1 with Amount of 120"
}
```

#### Example 2

```javascript
{
	"api": "insertFees",
	"blockId": 1,
	"apartmentId" : 1,
	"amount": 120,
	"dueDate" : ,
	"repeatId": ,
	"expenseId": 3,
	"feeStatment" : "Explain this Fee",
	"startDate" : ,
	"endDate": 30/6/2030,
	"flagBlockFee": 1,
	"flagApartmentFee" : ,
  "vendorId" : 1,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": "Fee Inserted on Block 1 with Amount of 120"
}
```

#### Example 3

```javascript
{
	"api": "insertFees",
	"blockId": 1,
	"apartmentId" : 1,
	"amount": 120,
	"dueDate" : ,
	"repeatId": ,
	"expenseId": 3,
	"feeStatment" : "Explain this Fee",
	"startDate" : ,
	"endDate": 30/6/2030,
	"flagBlockFee": ,
	"flagApartmentFee" : ,
  	"vendorId" : 1,
}
```

#### ERROR Response
The Response is JSON object containing array of objects named `status` and `message` the "message" array shows the body of the response and status shows response status.

##### Case 1 : Leaving Flags of Units and blocks empty.
```javascript
{
	"status": 200,
    	"message": "Please enter Block's ID OR Apartment's ID in thier keys."
}
```

##### Case 2 : Not providing fee amount.
```javascript
{
    "status": 200,
    "message": "Please enter fee amount."
}
```

##### Case 3 : Not providing api key.
```javascript
{
    "status": 404,
    "message": "Method Financial::() does not exist"
}
```

## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)

