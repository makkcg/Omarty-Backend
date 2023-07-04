![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Financial API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **Apartment Accounting (كشف حساب الشقة مدين)**
we use the following URL to Show My Unit Accounting
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

#### **1- Apartment Accounting**
to Show Apartment Due Acounting (عرض كشف حساب الشقة مدين).

Request should include the header parameters
```http
  https://plateform.omarty.net/omartyapis/Financial/
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `api` | `String` | **Required**. End point name|
| `blockId` | `Number` | **Required**. User Block ID in DB|
| `apartmentId` | `Number` | **Required**. User Unit ID in DB|
| `page` | `Number` | **Required**. Page has 10 Records|
| `startDate` | `Number` | **Optional**. Search key by the date to start searching from|
| `endDate` | `Number` | **Required**. Search key by the date to End searching At|

#### `api`

- End point that will trigger Showing Apartment Accounting is `apartmentDueAccounting`.

#### `blockId`

- Block ID in the DB.

#### `apartmentId`

- User Apartment ID in data base.

#### `page`

- each page has 10 records.

#### `startDate`

- Search key to specify date to start search with, if Start date and End date are left empty the default retrieved values will be of the current month.

#### `endDate`

- Search key to specify date to End search At, if Start date and End date are left empty the default retrieved values will be of the current month .


#### Example 1

```javascript
{
	"api": "apartmentDueAccounting",
	"blockId": 1,
	"apartmentId" : 1,
	"page": 1,
	"startDate": ,
	"endDate" : ,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status the totalFeeAmount key is the amount of the whole fees set for this unit or block.
```javascript
{
    "status": 200,
    "data": [
        "feeData": [
		{
                "id": "1",
                "feeStatment": "Waheed Fee test",
                "amount": "150",
                "paiedAmount": "150",
                "paymentRemaining": "0",
                "paymentMethod": null,
                "dueDate": "2023-06-07 04:17:17",
                "paymentDate": null,
                "repeatStatusID": null,
                "expenseName": "اشتراكات",
                "cashierID": "1",
                "blockID": "1",
                "blockNumber": "1",
                "blockName": "عمارة وحيد1",
                "apartmentID": "1",
                "apartmentNumber": "1",
                "apartmentName": "A1",
                "apartmentFloorNumber": "1",
                "date": "2023-06-04 04:17:17",
                "createdAt": "2023-06-04 14:39:20",
                "createdBy": null,
                "flagLastPage": 0
            }
	],
	"paymentData": [
		{
                "id": "2",
                "paymentMethod": "Cash",
                "originalFeeAmount": "150",
                "amount": "150",
                "remainingAmount": "0",
                "partial": "1",
                "feeID": "1",
                "feeStatment": "Waheed Fee test",
                "billImage": "",
                "attachment": "https://plateform.omarty.net/omartyapis/Images/PaymentImages/MTY0N2M3YTc5YTUwZjcwLjk2MzE4OTA0.png",
                "confirm": "1",
                "expenseName": "اشتراكات",
                "residentID": "1",
                "residentName": "Muhammad Waheed",
                "blockID": "1",
                "blockNumber": "1",
                "blockName": "عمارة وحيد1",
                "apartmentID": "1",
                "apartmentNumber": "1",
                "apartmentName": "A1",
                "apartmentFloorNumber": "1",
                "paymentdate": "2023-06-04 14:50:17",
                "flagLastPage": 0
            }
	],
	"incomeData": [
		{
                "id": "3",
                "amount": "200",
                "incomeStatment": "Test Income Waheed",
                "attachment": "https://plateform.omarty.net/omartyapis/Images/PaymentImages/MTY0ODRiNGMyMGFiYzMwLjAwOTQyNDU3.png",
                "residentID": "1",
                "residentName": "Muhammad Waheed",
                "blockID": "1",
                "blockNumber": "1",
                "blockName": "عمارة وحيد1",
                "apartmentID": "1",
                "apartmentNumber": "1",
                "apartmentName": "A1",
                "apartmentFloorNumber": "1",
                "date": "2023-06-21",
                "createdAt": "2023-06-21 11:13:32"
            }
	],
	"balance": 0,
        "previousAccount": 0,
        "IncomeGrossAmount": 200
    ]
}
```

#### ERROR Response
The Response is JSON object containing array of objects named `status` and `message` the "message" array shows the body of the response and status shows response status.

##### Case 1 : apartmentId Key is empty and not sending apartment id.
```javascript
{
    "status": 200,
    "message": "Apartment Not Found."
}
```

##### Case 2 : blockId Key is empty and not sending block id.
```javascript
{
    "status": 200,
    "message": "Block Not Found."
}
```

##### Case 3 : Send in api key any other value than apartmentDueAccounting.
```javascript
{
    "status": 404,
    "message": "Method Financial::another value() does not exist"
}
```

## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)

