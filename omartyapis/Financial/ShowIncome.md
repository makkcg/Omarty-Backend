![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Financial API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **show Income (عرض الايرادات)**
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
to Show Income for this Block Or user's units from Data Base.

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
| `flagAptIncome` | `Number` | **Required**. Flag to tell to show Fees of Unit that its id is givin in this key|



#### `api`

- End point that will trigger Showing Income is `ShowIncome`.

#### `blockId`

- Block ID in the DB.

#### `apartmentId`

- User Apartment ID in data base / if User is not block manager the ID given here will be searched for its Income records.

#### `page`

- each page has 10 records.

#### `startDate`

- Search key to specify date to start search with.

#### `endDate`

- Search key to specify date to End search At.

#### `flagAptIncome`

- If User is Block Manager show Apartment Income if it is set > 0 other wise will show blocks Income.



#### Example 1

```javascript
{
	"api": "ShowIncome",
	"blockId": 1,
	"apartmentId" : 1,
	"page": 1,
	"startDate": ,
	"endDate" : ,
	"flagAptFees": 1,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status the totalIncomeAmount key is the amount of the whole Income set for this unit or block.
```javascript
{
    "status": 200,
    "data": {
        "1": {
            "id": "3",
            "amount": "200",
            "incomeStatment": null,
            "attachment": null,
            "residentID": "1",
            "residentName": "Muhammad Waheed",
            "blockID": "1",
            "blockNumber": "1",
            "blockName": "عمارة وحيد1",
            "apartmentID": "1",
            "apartmentNumber": "1",
            "apartmentName": "A1",
            "apartmentFloorNumber": "1",
            "date": "Test Income Waheed",
            "createdAt": "2023-06-21"
        },
        "2": {
            "id": "4",
            "amount": "200",
            "incomeStatment": null,
            "attachment": null,
            "residentID": "1",
            "residentName": "Muhammad Waheed",
            "blockID": "1",
            "blockNumber": "1",
            "blockName": "عمارة وحيد1",
            "apartmentID": "1",
            "apartmentNumber": "1",
            "apartmentName": "A1",
            "apartmentFloorNumber": "1",
            "date": "Test Income Waheed",
            "createdAt": "2023-06-24 04:15:25pm"
        },
        "totalIncomeAmount": 400
    }
}
```

#### Example 2

```javascript
{
	"api": "ShowIncome",
	"blockId": 1,
	"apartmentId" : 1,
	"page": 1,
	"startDate": ,
	"endDate" : ,
	"flagAptFees": ,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": {
        "payments": {
            "1": {
                "id": "2",
                "amount": "5",
                "incomeStatment": "Waheed Fee test",
                "billImage": "",
                "attachment": "https://plateform.omarty.net/omartyapis/Images/PaymentImages/MTY0N2M3YTc5YTUwZjcwLjk2MzE4OTA0.png",
                "confirm": "1",
                "expenseName": "اشتراكات",
                "residentID": "1",
                "residentName": "Muhammad Waheed",
                "blockID": "1",
                "blockNumber": "1",
                "blockName": "عمارة وحيد1",
                "vendorName": null,
                "vendorImage": "https://plateform.omarty.net/Images/VendorImages/Default.jpg",
                "vendorPhoneNumber": null,
                "vendorEmail": null,
                "createdAt": "2023-06-04 14:50:17"
            }
          },
        "incomes": {
            "31": {
                "id": "1",
                "amount": "200",
                "incomeStatment": "Test Income Waheed",
                "attachment": null,
                "residentID": "1",
                "residentName": "Muhammad Waheed",
                "blockID": "1",
                "blockNumber": "1",
                "blockName": "عمارة وحيد1",
                "date": "2023-06-21",
                "createdAt": "2023-06-21 11:04:42"
            }
          },
        "totalIncomeAmount": 205
    }
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

##### Case 3 : Send in api key any other value than ShowIncome.
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

