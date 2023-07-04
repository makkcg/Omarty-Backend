![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Financial API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **Block Accounting (كشف حساب العمارة)**
we use the following URL to Show My Block Accounting
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

#### **1- Block Accounting**
to Show Block Due Acounting (عرض كشف حساب العمارة مدين).

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

- End point that will trigger Showing Block Accounting is `blockDueAccounting`.

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
	"api": "blockDueAccounting",
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
                "id": "2",
                "feeStatment": "Test Fee 2 By Waheed",
                "amount": "100",
                "paiedAmount": "5",
                "paymentRemaining": "95",
                "reciepts": [],
                "paymentMethod": null,
                "dueDate": null,
                "paymentDate": null,
                "repeatStatusID": null,
                "expenseName": "اشتراكات",
                "cashierID": "1",
                "blockID": "1",
                "blockNumber": "1",
                "blockName": "عمارة وحيد1",
                "vendorName": null,
                "vendorImage": "https://plateform.omarty.net/Images/VendorImages/Default.jpg",
                "vendorPhoneNumber": null,
                "vendorEmail": null,
                "date": "2023-06-10 08:10:19 BM",
                "createdAt": "2023-06-10 20:10:19",
                "createdBy": "1",
                "flagLastPage": 0
            }
	],
	"paymentData": [
		{
                "id": "11",
                "paymentMethod": "Cash",
                "originalFeeAmount": "100",
                "amount": "5",
                "remainingAmount": "95",
                "partial": "1",
                "feeID": "2",
                "feeStatment": "Test Fee 2 By Waheed",
                "billImage": "https://plateform.omarty.net/omartyapis/Images/BillImages/B1A1I7.pdf",
                "attachment": "https://plateform.omarty.net/omartyapis/Images/PaymentImages/MTY0ODRhZjQ4ZDRmMTA4LjUxOTI4ODk1.png",
                "confirm": "1",
                "expenseName": "اشتراكات",
                "residentID": "1",
                "residentName": "Muhammad Waheed",
                "blockID": "1",
                "blockNumber": "1",
                "blockName": "عمارة وحيد1",
                "vendorName": "شركة الكهرباء",
                "vendorImage": "https://plateform.omarty.net/Images/VendorImages/Default.jpg",
                "vendorPhoneNumber": "012341234",
                "vendorEmail": "Vendor@Vendor.com",
                "paymentdate": "2023-06-10 20:13:44",
                "flagLastPage": 0
            }
	],
	"incomeData": [
		{
                "id": "1",
                "amount": "200",
                "incomeStatment": "Test Income Waheed",
                "attachment": null,
                "residentID": "1",
                "residentName": "Muhammad Waheed",
                "blockID": "1",
                "blockNumber": "1",
                "blockName": "عمارة وحيد1",
                "vendorName": null,
                "vendorImage": "https://plateform.omarty.net/Images/VendorImages/Default.jpg",
                "vendorPhoneNumber": null,
                "vendorEmail": null,
                "flagLastPage": 1,
                "date": "2023-06-21",
                "createdAt": "2023-06-21 11:04:42"
            }
	],
	"balance": 95,
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

##### Case 3 : Send in api key any other value than blockDueAccounting.
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

