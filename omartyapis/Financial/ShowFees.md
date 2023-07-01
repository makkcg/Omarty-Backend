![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Financial API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **show Fees (عرض المستحقات)**
we use the following URL to Show My Fee
```http
  https://plateform.omarty.net/omartyapis/Financial/
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

#### **1- Show Fees**
to Show Fees for this Block Or user's units in Data Base.

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
| `repeatStatus` | `Number` | **Optional**. Serach Key by repeatetion of the fee|
| `expanseId` | `Number` | **Optional**. Search key by Expense type|
| `page` | `Number` | **Required**. Page has 10 Records|
| `startDate` | `Number` | **Optional**. Search key by the date to start searching from|
| `endDate` | `Number` | **Required**. Search key by the date to End searching At|
| `flagAptFees` | `Number` | **Required**. Flag to tell to show Fees of Unit that its id is givin in this key|
| `vendorId` | `Number` | **Optional**. Search key by VendorID|


#### `api`

- End point that will trigger Showing Fees is `showFees`.

#### `blockId`

- Block ID in the DB.

#### `apartmentId`

- User Apartment ID in data base.

#### `repeatStatus`

- Search key by repetetion type.

#### `expanseId`

- Search key by Expense type.

#### `page`

- each page has 10 records.

#### `startDate`

- Search key to specify date to start search with.

#### `endDate`

- Search key to specify date to End search At.

#### `flagAptFees`

- show Apartment Fees if it is set > 0.

#### `vendorId`

- Search key by vendor id.


#### Example 1

```javascript
{
	"api": "showFees",
	"blockId": 1,
	"apartmentId" : 1,
	"repeatStatus": ,
	"expanseId" : ,
	"page": 1,
	"startDate": ,
	"endDate" : ,
	"flagAptFees": 1,
	"vendorId" : ,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status the totalFeeAmount key is the amount of the whole fees set for this unit or block.
```javascript
{
    "status": 200,
    "data": [
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
            "cashierID": {
                "CashierAptNumber": "1",
                "CashierAptName": "A1",
                "CashierAptFloorNumber": "1",
                "CashierName": "Muhammad Waheed",
                "CashierPhoneNum": "01144338618"
            },
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
            "flagLastPage": 1
        },
        {
            "totalFeeAmount": 150
        }
    ]
}
```

#### Example 2

```javascript
{
	"api": "showFees",
	"blockId": 1,
	"apartmentId" : 1,
	"repeatStatus": ,
	"expanseId" : ,
	"page": 1,
	"startDate": ,
	"endDate" : ,
	"longitude" : 121.12221,
	"latitude": 20.233,
	"flagAptFees": ,
	"vendorId" : ,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": [
        {
            "id": "2",
            "feeStatment": "Test Fee 2 By Waheed",
            "amount": "100",
            "paiedAmount": "0",
            "paymentRemaining": "0",
            "paymentMethod": null,
            "dueDate": null,
            "paymentDate": null,
            "repeatStatusID": null,
            "expenseName": "اشتراكات",
            "cashierID": {
                "CashierAptNumber": "1",
                "CashierAptName": "A1",
                "CashierAptFloorNumber": "1",
                "CashierName": "Muhammad Waheed",
                "CashierPhoneNum": "01144338618"
            },
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
            "flagLastPage": 1
        },
        {
            "totalFeeAmount": 100
        }
    ]
}
```

#### ERROR Response
The Response is JSON object containing array of objects named `status` and `message` the "message" array shows the body of the response and status shows response status.

##### Case 1 : apartmentId Key is empty and not sending apartment id.
```javascript
{
    "status": 200,
    "message": "Please Enter Apartment ID."
}
```

##### Case 2 : blockId Key is empty and not sending block id.
```javascript
{
    "status": 200,
    "message": "Please Enter Block ID."
}
```

##### Case 3 : Send in api key any other value than showFees.
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
