![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Financial API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **showPayments  (عرض المدفوعات)**
we use the following URL to show Payments.
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

#### **1- Show Payments**
to Show payment records for this Block and its units in Data Base.

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
| `page` | `Number` | **Required**. page has 10 records|
| `startDate` | `Date` | **Optional**. Search key by date to start searching from a certain date|
| `endDate` | `Number` | **Optional**. Search key by date to End searching at a certain date|
| `expenseId` | `Number` | **Optional**. Search key by Expense type|
| `vendorId` | `Number` | **Optional**. Vendor Id that is transfering the mony to|
| `flagAptPayments` | `Number` | **Optional**. Unit Id that user wants to show its payments|



#### `api`

- End point that will trigger creating Block is `showPayments`.

#### `blockId`

- Block ID in the DB.

#### `apartmentId`

- Apartment ID in data base if this key is empty then Block's payment records will be showen.

#### `page`

- each page has 10 records.

#### `startDate`

- Search key to set date to start search with.

#### `endDate`

- Search key to set date to End search At.

#### `expenseId`

- Search Key by expense type.

#### `vendorId`

- Search key by Vendor ID.


#### Example 1

```javascript
{
	"api": "showPayments",
	"blockId": 1,
	"apartmentId" : 1,
	"page": 1,
	"startDate" : ,
	"endDate": ,
	"expenseId": ,
	"vendorId": ,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": {
        "1": {
            "id": "2",
            "paymentMethod": "Cash",
            "originalFeeAmount": "150",
            "amount": "5",
            "remainingAmount": "100",
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
        },
      "totalPaiedAmount": 50
    }
}
      
      ```

#### Example 2

```javascript
{
	"api": "showPayments",
	"blockId": 1,
	"apartmentId" : ,
	"page": 1,
	"startDate" : ,
	"endDate": ,
	"expenseId": ,
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": {
        "1": {
            "id": "59",
            "paymentMethod": null,
            "originalFeeAmount": "6",
            "amount": "6",
            "remainingAmount": "0",
            "partial": "0",
            "feeID": "27",
            "feeStatment": "دفع مصاريف في الحال",
            "billImage": "",
            "attachment": "",
            "confirm": "1",
            "expenseName": "دفع وقتي",
            "residentID": "1",
            "residentName": "Muhammad Waheed",
            "blockID": "1",
            "blockNumber": "1",
            "blockName": "عمارة وحيد1",
            "vendorName": "شركة الكهرباء",
            "vendorImage": "https://plateform.omarty.net/Images/VendorImages/Default.jpg",
            "vendorPhoneNumber": "012341234",
            "vendorEmail": "Vendor@Vendor.com",
            "paymentdate": "2023-06-20 20:48:27",
            "flagLastPage": 0
        },
         "totalPaiedAmount": 5
    }
}
```

#### ERROR Response
The Response is JSON object containing array of objects named `status` and `message` the "message" array shows the body of the response and status shows response status.


## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)
