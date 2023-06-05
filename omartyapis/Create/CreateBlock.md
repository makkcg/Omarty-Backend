

![Logo](https://omarty.net/wp-content/uploads/2023/03/cropped-omarty_logo_80h.png)


# Omarty Chat Websocket API Documentation

Omarty is an application for Buildings commuinities, it includes a chat module for chating between building users




## API Reference
### **Create Block**
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

#### **1- Create Block**
to Create record for this Block and its units in Data Base.

Request should include the header parameters
```http
  https://plateform.omarty.net/omartyapis/Create/
```
##### **Request Parameters**

| Parameter | Type     | Description                       |
| :-------- | :------- | :-------------------------------- |
| `api` | `String` | **Required**. End point name|
| `blockNum` | `String` | **Required**. Block number in the street|
| `numOfApartments` | `Number` | **Required**. Number of units in the block|
| `image` | `File` | **Optional**. Image Of the block|
| `balance` | `Number` | **Optional**. Balance of block of Fees and Incomes|
| `fees` | `Number` | **Optional**. Block fees|
| `password` | `String` | **Optional**. Used For nothing|
| `longitude` | `Number` | **Optional**. Device Longitude|
| `latitude` | `Number` | **Optional**. Device Latitude|
| `numOfFloors` | `Number` | **Required**. Number Of floors in the block|
| `apartmentNum` | `Number` | **Required**. Number Of block manager unit in the block|
| `apartmentFloorNum` | `Number` | **Required**. Number Of block manager floor in the block|
| `countryID` | `Number` | **Required**. Country ID|
| `governateID` | `Number` | **Required**. Provence ID|
| `cityID` | `Number` | **Required**. City ID|
| `regionID` | `Number` | **Required**. Region ID|
| `compoundID` | `Number` | **Optional**. Compound ID|
| `streetID` | `Number` | **Required**. Street ID|
| `governateName` | `String` | **Optional**. Entering a new government name|
| `cityName` | `String` | **Optional**. Entering a new city name|
| `regionName` | `String` | **Optional**. Entering a new region name|
| `compoundName` | `String` | **Optional**. Entering a new compound name|
| `streetName` | `String` | **Optional**. Entering a new street name|
| `blockName` | `String` | **Optional**. Entering Block name|
| `apartmentName` | `String` | **Optional**. Entering Unit name|


#### `api`

- End point that will trigger creating Block is `createBlock`.

#### `blockNum`

- Block number in the street.

#### `numOfApartments`

- Number of units in the block to create their records in data base.

#### `image`

- Block Image.

#### `balance`

- Block balance to set it to its financial account.

#### `fees`

- Block fees to set it to its financial account.

#### `password`

- Block Password that is used for nothing even it has been not used in the app interface.

#### `longitude`

- Block Longitude to specify its location for offers and services recommendations and offers .

#### `latitude`

- Block latitude to to specify its location for offers and services recommendations and offers.

#### `countryID`

- Country ID which will be in drop down list.

#### `governateID`

- Government ID which will be in drop down list..

#### `cityID`

- City ID which will be in drop down list.

#### `regionID`

- Region ID which will be in drop down list.
#### `compoundID`

- Compound ID which will be in drop down list.

#### `streetID`

- Street ID which will be in drop down list.

#### `numOfFloors`

- Number of floors in the block

#### `apartmentNum`

- Number of block manager unit.

#### `apartmentFloorNum`

- Number of block manager floor.

#### `governateName`

- government name if the wanted one was not found will be entered in Data Base as data collection.

#### `cityName`

- City name if the wanted one was not found will be entered in Data Base as data collection.

#### `regionName`

- Region name if the wanted one was not found will be entered in Data Base as data collection.

#### `compoundName`

- Compound name if the wanted one was not found will be entered in Data Base as data collection.

#### `streetName`

- Street name if the wanted one was not found will be entered in Data Base as data collection.

#### `blockName`

- Block name.

#### `apartmentName`

- Unit Name EX. (A1 / Z3 / 101 / 103).

#### Example 1

```javascript
{
	"api": "createBlock",
	"blockNum": "BlockNameTest",
	"numOfApartments" : 20,
	"image": "",
	"balance": 0,
	"fees" : 2000,
	"password": "",
	"longitude": 121.12221,
	"latitude" : 20.233,
	"countryID": 67,
	"governateID": 1,
	"cityID" : 2,
	"regionID" : 8,
	"compoundID": ,
	"streetID": 8,
	"numOfFloors" : 5,
	"apartmentNum": 1,
	"apartmentFloorNum": 1,
	"governateName" : ,
	"cityName": ,
	"regionName": ,
	"compoundName" : ,
	"streetName": ,
	"blockName": "برج الصفا",
	"apartmentName" : "A1",
}
```

#### Response
The Response is JSON object containing array of objects named `status` and `data` the data array shows the body of the response and status shows response status.
```javascript
{
    "status": 200,
    "data": "Block Registered"
}
```

#### ERROR Response
The Response is JSON object containing array of objects named `status` and `message` the "message" array shows the body of the response and status shows response status.

##### Case 1 : same Block number in the same street.
```javascript
{
    "status": 200,
    "message": "Block already registered."
}
```

## Authors

This Code, Trademark, and Application is Copywrite protected by law to [Diginovia](https://diginovia.com/)
- Mohammed Khalifa [@makkcg](https://github.com/makkcg)

## Links

- [Postman](https://omarty.postman.co/workspace/Omarty-Workspace-VPS~7efc4af7-9f9e-48ce-a5b5-d127cfd455b1/overview)

