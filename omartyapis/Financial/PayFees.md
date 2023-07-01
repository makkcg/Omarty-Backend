![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Financial API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **Pay Fees  تسجيل المدفوعات المستحقة)**
we use the following URL to Pay Fees on Units.
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

#### **1- Pay Fees**
to Create Payment records for this Block and its units in Data Base.

Request should include the header parameters
```http
  https://plateform.omarty.net/omartyapis/Financial/
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `api` | `String` | **Required**. End point name|
| `blockId` | `Number` | **Required**. Block ID in DB|
| `apartmentId` | `Number` | **Required**. Unit ID in DB|
| `longitude` | `Number` | **Optional**. Device Longitude|
| `latitude` | `Number` | **Optional**. Device Latitude|
| `feeId` | `Number` | **Required**. FeeID in DB|
| `partialAmount` | `Number` | **Optional**. Money amount that user has paied if left empty the amount is all the remaining of this fee|
| `paymentMethod` | `Number` | **Required**. Payment Method ID in DB|
| `attach` | `File` | **Optional**. Attach of Image or PDF|
| `aptPay` | `Number` | **Required**. Flag to tell that payment is for Unit in Block|
| `blkPay` | `Number` | **Required**. Flag to tell that payment is for Block|
| `vendorId` | `Number` | **Optional**. Vendor Id that is transfering the money to. And its required if blkPay key is set|


#### `api`

- End point that will trigger Paying Fees is `payFees`.

#### `blockId`

- Block ID in the DB.

#### `apartmentId`

- User apartment ID in data base.

#### `longitude`

- Device Longitude to specify its location.

#### `latitude`

- Device latitude to to specify its location.

#### `feeId`

- Fee ID that is being paied from DB.

#### `partialAmount`

- the money amount that user wants to pay of this fee, if he wants to pay its whole amount he just leve it empty or type the amount by himself.

#### `paymentMethod`

- Payment method ID in DB.

#### `attach`

- Attachment for proof if there is.

#### `aptPay`

- Flag that this payment is for an apartment and set the flag its value must be > 0 if .

#### `blkPay`

- Flag that this payment is for a Block and set the flag its value must be > 0 if .

#### `vendorId`

- Vendor ID in DB, it needs to be set only if blkPay flag is set to tell where the amount of money is going to.


#### Example 1

```javascript
{
	"api": "payFees",
	"blockId": 1,
	"apartmentId" : 1,
	"longitude": 121.12221,
	"latitude" : 20.233,
	"feeId": 2,
	"partialAmount": 5,
	"paymentMethod" : 1,
	"attach" : ,
	"aptPay": 1,
	"blkPay": ,
	"vendorId" : ,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": "Payment Inserted."
}
```

#### Example 2

```javascript
{
	"api": "createBlock",
	"blockId": 1,
	"apartmentId" : 1,
	"longitude": 121.12221,
	"latitude" : 20.233,
	"feeId": ,
	"partialAmount": 5,
	"paymentMethod" : 1,
	"attach" : ,
	"aptPay": ,
	"blkPay": 1,
	"vendorId" : ,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": "Payment Inserted."
}
```

#### Example 3

```javascript
{
	"api": "createBlock",
	"blockId": 1,
	"apartmentId" : 1,
	"longitude": 121.12221,
	"latitude" : 20.233,
	"feeId": 3,
	"partialAmount": 5,
	"paymentMethod" : 1,
	"attach" : ,
	"aptPay": ,
	"blkPay": 1,
	"vendorId" : 1,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": "Payment Inserted."
}
```

#### ERROR Response
The Response is JSON object containing array of objects named `status` and `message` the "message" array shows the body of the response and status shows response status.

##### Case 1 : Payment is already paied and user is paying the same fee again.
```javascript
{
    "status": 200,
    "message": "This amount 6 + What was paied before is greater than original fee amount."
}
```

## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)
