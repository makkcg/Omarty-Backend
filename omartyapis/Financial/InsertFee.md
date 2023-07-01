![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Financial API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **Insert Fee (اضافة مستحقات)**
we use the following URL to Isert Fees to units. And the user who uses it is only Block Manager.
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

#### **1- Insert Fees**
to Create Fee records for this Block and its units in Data Base.

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
| `amount` | `Number` | **Required**. Fee Amount|
| `attach` | `File` | **Optional**. Fee Attachment|
| `dueDate` | `Date` | **Optional**. the last day to pay the fee|
| `repeatId` | `Number` | **Optional**. RepeatID in DB|
| `expenseId` | `Number` | **Required**. ExpenseID in DB which is the type of expense|
| `feeStatment` | `String` | **Required**. Fee Statment is the explaination of why does this fee exist|
| `startDate` | `Date` | **Optional**. if RepeatId is set then the start date shows the day of this fee is set , if its empty then its default value is the current time|
| `endDate` | `Number` | **Optional**. if RepeatId is set then the End date shows the day of this fee is to end.|
| `flagBlockFee` | `Number` | **Required**. Flag to tell that Fee is for Block|
| `flagApartmentFee` | `Number` | **Required**. Flag to tell that Fee is for Unit in the block, AND its value is the target unit to get the fee|
| `vendorId` | `Number` | **Required**. Vendor Id that is transfering the money to and its required only if inserting fees to block.|
| `longitude` | `String` | **Required**. Longitude of user device ID for collecting all data of user when he/she performs this action.|
| `latitude` | `String` | **Required**. Latitude of user device ID for collecting all data of user when he/she performs this action.|


#### `api`

- End point that will trigger Fee Insertion is `insertFees`.

#### `blockId`

- Block ID in the DB.

#### `apartmentId`

- Admin's Apartment ID in data base.

#### `amount`

- money amount of this fee.

#### `dueDate`

- The last date to pay this fee.

#### `repeatId`

- the Repeating sequence (annualy (ID : 4)/ Monthly (ID : 5)/ Weekly (ID : 6)/ Daily (ID : 7) ).

#### `expenseId`

- it is the expense type.

#### `feeStatment`

- Fee Statment which explains why is this fee in database.

#### `startDate`

- Flag of the start date of repeating this fee, if its empty then its default value is the current time.

#### `endDate`

- Flag of the end date of repeating this fee.

#### `flagBlockFee`

- Flag that this payment is for a Block and set the flag its value must be > 0 if Fee is Set For Block and it will directly reflect on the block Admin is using now.

#### `flagApartmentFee`

- Flag that this payment is for a Unit in Block and set the flag its value must be target unit ID that got the fee.


#### `vendorId`

- Vendor ID in DB, it needs to be set only if blkPay flag is set to tell where the amount of money is going to.


#### Example 1

```javascript
{
	"api": "insertFees",
	"blockId": 1,
	"apartmentId" : 1,
	"amount": 120,
	"dueDate" : ,
	"repeatId": 1,
	"expenseId": 2,
	"feeStatment" : "Explain this Fee",
	"startDate" : ,
	"endDate": 30/6/2030,
	"flagBlockFee": ,
	"flagApartmentFee" : 1,
  	"vendorId" : ,
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
```javascript
	{
    	"status": 200,
    	"message": "Please enter Block's ID OR Apartment's ID in thier keys."
	}
```
## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)
