<?php
    include("../vendor/autoload.php");

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;

class Update extends Functions
{
    public function __construct()
    {
        include("../Config.php");
        $this->conn = $conn;
    }
    
    public function updateUser() // OK Final
    {
        date_default_timezone_set('Africa/Cairo');

        // include("../Config.php");
        $name=$_POST["Name"];
        $UserName= $_POST["userName"];
        // $Email= filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        // $PhoneNum= $_POST["phoneNumber"];
        $MartialStatus= $_POST["martialStatus"];
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $Attach = $_FILES["image"];
        $error = [];
        $ARRR = [];
        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(401, $e->getMessage());
        }

            $userID = $decode->id;
            $extensions = ["jpg", "jpeg", "png", "pdf"];
            // Post image file.
            if(!empty($Attach))
            {
                $attachments = $this->uploadFile2($userID, $Attach, $extensions);    
            }
            
            
        
        if(empty($decode->id))
        {
            $this->throwError(403, "User not found. please enter your registered phone number");
        }

        elseif(!empty($decode->id))
        {
            //   Check Block existence
            $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
            if($this->conn->error){ echo $this->conn->error; }
            if($sqlCheckBlock->num_rows > 0)
            {
                $BlkData = $sqlCheckBlock->fetch_row();
                //Check Block Status.
                if($BlkData[1] == '2')
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $this->conn->query("SELECT ApartmentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID' AND ResidentID='$userID'");
                    if($sqlCheckApt->num_rows > 0)
                    {
                        $AptData = $sqlCheckApt->fetch_row();
                        // Check Apartment Status.
                        if($AptData[1] == '1' || $AptData[1] == '2')
                        {
                            
                            // Show UserData.
                            $sqlGetResData = $this->conn->query("SELECT Name, UserName, Email, PhoneNum, Image, MartialStatus FROM Resident_User WHERE ID = '$userID'");
                            if($sqlGetResData->num_rows > 0)
                            {
                                $ResData = $sqlGetResData->fetch_row();
                                if(!empty($ResData[4]))
                                {
                                    $ImageUrl = "https://kcgwebservices.net/omartyapis/Images/profilePictures/$ResData[4]";
                                }
                                elseif(empty($ResData[4]))
                                {
                                    $ImageUrl = "https://kcgwebservices.net/omartyapis/Images/profilePictures/DefaultMale.png";
                                }
                                // Get Non hidden Contacts.
                                // Get PhoneNums.
                                $sqlGetPN = $this->conn->query("SELECT PhoneNum FROM PhoneNums WHERE UserID = '$userID'");
                                $SecondaryPNs = [];
                                $count = 1;
                                while($SecondPNs = $sqlGetPN->fetch_row())
                                {
                                    $SecondaryPNs[$count] = ["$count" => $SecondPNs[0]];
                                    $count++;
                                }
                                // Get Emails.
                                $sqlGetEmail = $this->conn->query("SELECT Email FROM Emails WHERE UserID = '$userID'");
                                $SecondaryEmails = [];
                                $count = 1;
                                while($Secondemails = $sqlGetEmail->fetch_row())
                                {
                                    $SecondaryEmails[$count] = ["$count" => $Secondemails[0]];
                                    $count++;
                                }
                                $ResDataArr = 
                                [
                                    "name" => $ResData[0],
                                    "userName" => $ResData[1],
                                    "primaryEmail" => $ResData[2],
                                    "secondaryEmails" => array_values($SecondaryEmails),
                                    "primaryPhoneNumber" => $ResData[3],
                                    "secondaryPhoneNumbers" => array_values($SecondaryPNs),
                                    "image" => $ImageUrl,
                                    "status" => $ResData[5]
                                ];
                                $count = 0;
                            }
                            /*
                            // Check entered Email and PhoneNum are not dublicated.
                            $checkEmail = $this->conn->query("SELECT Email FROM Resident_User WHERE Email = '$Email'");
                            $checkPN = $this->conn->query("SELECT PhoneNum FROM Resident_User WHERE PhoneNum = '$PhoneNum'");
                
                            if (!filter_var($Email, FILTER_VALIDATE_EMAIL) === false) 
                            {
                                if($checkEmail->num_rows > 0)
                                {
                                    $this->throwError(304, "Email already registered.");
                                }
                            }
                            elseif(filter_var($Email, FILTER_VALIDATE_EMAIL) === false)
                            {
                                $error = ["errorEmail" => "$Email is not a valid email address"];
                                $Email = null;
                            }
                
                            strval($PhoneNum);
                            
                            if(strlen($PhoneNum) >= 11)
                            {
                                if($checkPN->num_rows > 0)
                                {
                                    $this->throwError(200, "Phone Number already registered.");
                                }   
                            }
                            elseif(strlen($PhoneNum) <= 10)
                            {
                                $error += ["errorPhoneNumber" => "$PhoneNum is not a valid Phone Number"];
                                $PhoneNum = null;
                            }
                             */  
                            if(empty($name))
                            {
                                $getName = $this->conn->query("SELECT Name FROM Resident_User WHERE ID = $decode->id");
                                $NARR = $getName->fetch_row();
                                $name = $NARR[0];
                            }
                            
                            if(empty($UserName))
                            {
                                $getUserName = $this->conn->query("SELECT UserName FROM Resident_User WHERE ID = $decode->id");
                                $UNARR = $getUserName->fetch_row();
                                $UserName = $UNARR[0];
                            }
                            if(empty($Email))
                            {
                                $getEmail = $this->conn->query("SELECT Email FROM Resident_User WHERE ID = $decode->id");
                                $EMAILARR = $getEmail->fetch_row();
                                $Email = $EMAILARR[0];
                            }
                            if(empty($PhoneNum))
                            {
                                $getPN = $this->conn->query("SELECT PhoneNum FROM Resident_User WHERE ID = $decode->id");
                                $PNARR = $getPN->fetch_row();
                                $PhoneNum = $PNARR[0];
                            }
                            if(empty($attachments))
                            {
                                $getImage = $this->conn->query("SELECT Image FROM Resident_User WHERE ID = $decode->id");
                                $IMARR = $getImage->fetch_row();
                                $attachName = $IMARR[0];
                                $imageUrl = "https://kcgwebservices.net/omartyapis/Images/profilePictures/" . $attachName;
                            }
                            if(!empty($attachments)) 
                            {
                                // Get Old Picture to delete.
                                $getImage = $this->conn->query("SELECT Image FROM Resident_User WHERE ID = $decode->id");
                                $IMARR = $getImage->fetch_row();
                                $attachName = $IMARR[0];
                                unlink("../Images/profilePictures/$attachName");
                                // ==================================================================================================
                                $location = "../Images/profilePictures/". $attachments["newName"];
                                $imageUrl = "https://kcgwebservices.net/omartyapis/Images/profilePictures/" . $attachments['newName'];
                                $attachName = $attachments["newName"];
                            }
                            if(empty($MartialStatus))
                            {
                                $getMS = $this->conn->query("SELECT MartialStatus FROM Resident_User WHERE ID = $decode->id");
                                $MSARR = $getMS->fetch_row();
                                $MartialStatus = $MSARR[0];
                            }
                
                            $date = date("Y-m-d H:i:s");
                            $sqlUpdateUser = $this->conn->query("UPDATE Resident_User SET Name='$name',UserName='$UserName',Email='$Email',PhoneNum='$PhoneNum',Image='$attachName',MartialStatus='$MartialStatus', UpdatedAt= '$date', UpdatedBy= '$decode->id' WHERE ID = '$decode->id' ");
                            if($sqlUpdateUser)
                            {
                                if(!empty($attachments)) { move_uploaded_file($attachments["tmp_name"], $location); }
                                $sqlGetUser = $this->conn->query("SELECT * From Resident_User where ID = $decode->id");
                                while($Col = $sqlGetUser->fetch_row())
                                {
                                    $sqlGetUserStatus = $this->conn->query("SELECT Name FROM Status Where ID = '$Col[8]'");
                                    $UserStatus = $sqlGetUserStatus->fetch_row();
                
                                    $arr = [
                                        'ID' => $Col[0],
                                        'name'=> $Col[1],
                                        "username" => $Col[2],
                                        "email" => $Col[3],
                                        "phoneNumber" => $Col[5],
                                        "image" => $imageUrl,
                                        "martialStatus" => $Col[7],
                                        "userStatus" => $UserStatus[0]
                                    ];
                                }
                                $arr += $error;
                
                                $userId = $decode->id;
                
                                $Action = "Updating User data";
                                $sqlLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, UpdatedAt, UpdatedBy) 
                                                                VALUES ('$userId', '$APTID', '$BLKID', 15, '$Action','$userId', 'Resident_User', '$Longitude', '$Latitude', '$date', '$date', '$userId')");
                                if($this->conn->error) { echo $this->conn->error; }
                
                                $this->returnResponse(200, $arr);
                            }
                            else
                            {
                                $this->throwError(205, $this->conn->error);
                            }
                        }
                        elseif($AptData[1] == '3')
                        {
                            $this->throwError(401, "Apartment is Banned.");
                        }
                        else
                        {
                            $this->throwError(406,"Apartment status is not acceptable.");
                        }
                    }
                    elseif($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment Not Found.");
                    }
                }
                elseif($BlkData[1] == '1')
                {
                    $this->throwError(401, "Block status is Binding.");
                }
                elseif($BlkData[1] == '3')
                {
                    $this->throwError(401, "Block is Banned.");
                }
                else
                {
                    $this->throwError(401, "Block status is not acceptable.");
                }
                
            }
            else
            {
                $this->throwError(200, "Block Not Found.");
            }
        }
    }
    
    public function updateUserPassword() // OK
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
        $password = $_POST["password"];
        $newPassword = $_POST["newPassword"];
        $confirmNewPassword = $_POST["confirmNewPassword"];
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];     
        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(401, $e->getMessage());
        }

        // Check Block Existence.
        $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows > 0)
        {
            $BlkData = $sqlCheckBlock->fetch_row();
            //Check Block Status.
            if($BlkData[1] == '2')
            {
                // Check Apartment Existence.
                $sqlCheckApt = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID' AND ResidentID='$decode->id'");
                if($sqlCheckApt->num_rows > 0)
                {
                    $AptData = $sqlCheckApt->fetch_row();
                    // Check User relation to apartment.
                    if($AptData[2] == $decode->id)
                    {
                        // Check Apartment Status.
                        if($AptData[1] == '1' || $AptData[1] == '2')
                        {
                            $getUserPassword = $this->conn->query("SELECT Password FROM Resident_User WHERE ID = $decode->id");
                            if($getUserPassword->num_rows <= 0)
                            {
                                $this->throwError(403, "AUTHENTICATION error WAHEED.");
                            }
                            elseif($getUserPassword->num_rows > 0)
                            {
                                $userPassword = $getUserPassword->fetch_row();
                                if(!password_verify($password, $userPassword[0]))
                                {
                                    $this->throwError(401, "Wrong password.");
                                }
                                elseif(password_verify($password, $userPassword[0]))
                                {
                                    if($newPassword !== $confirmNewPassword)
                                    {
                                        $this->throwError(400, "new passwords don't match");
                                    }
                                    elseif((empty($newPassword) || $newPassword == " " ) && (empty($confirmNewPassword) || $confirmNewPassword == " "))
                                    {
                                        $this->throwError(400, "Please fill in new password and confirm it.");
                    
                                    }
                                    elseif($newPassword === $confirmNewPassword)
                                    {
                                        $date = date("Y:m:d h-i-sa");
                                        $userId = $decode->id;
                                        $newPassword = password_hash($newPassword,PASSWORD_BCRYPT);
                                        $updatePassword = $this->conn->query("UPDATE Resident_User SET Password = '$newPassword', UpdatedAt='$date', UpdatedBy='$userId' WHERE ID = $decode->id");
                                        
                                        $Action = "Updating User password";
                                        $sqlLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, UpdatedAt, UpdatedBy) 
                                                                        VALUES ('$userId', '$APTID', '$BLKID', 16, '$Action', '$userId', 'Resident_User', '$Longitude', '$Latitude', '$date', '$date', '$userId')");
                                       
                                        $this->returnResponse(200, "password updated in " . $date);
                                    }
                                }
                            }
                        }
                        elseif($AptData[1] == '3')
                        {
                            $this->throwError(401, "Block is Banned.");
                        }
                        else
                        {
                            $this->throwError(406,"Block status is not acceptable.");
                        }
                    }
                    else
                    {
                        $this->throwError(406, "User does not relate to this apartment.");
                    }
                }
                elseif($sqlCheckApt->num_rows <= 0)
                {
                    $this->throwError(205, "Apartment Not Found.");
                }
            }
            elseif($BlkData[1] == '1')
            {
                $this->throwError(401, "Block status is Binding.");
            }
            elseif($BlkData[1] == '3')
            {
                $this->throwError(401, "Block is Banned.");
            }
            else
            {
                $this->throwError(401, "Block status is not acceptable.");
            }
        }
        else
        {
            $this->throwError(200, "Block Not Found.");
        }

    }
    
    public function updateBlock()
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
            try
            {
                $token = $this->getBearerToken();
                $secret = "secret123";
                $decode = JWT::decode($token, new KEY($secret, 'HS256'));
            }
            catch(Exception $e)
            {
                $this->throwError(401, $e->getMessage());
            }
            // Request Data.
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $BlockNum = $_POST["blockNum"];
            $BlockName = $_POST["blockName"];
            $NOA =$_POST["numberOfApartments"];
            $Image = $_FILES["image"];
            $Balance = $_POST["Balance"];
            $Fees = $_POST["fees"];
            $Longitude = $_POST["longitude"];
            $Latitude = $_POST["latitude"];
            $date = date("Y-m-d H:i:sa");
            $UserID = $decode->id;
            $extensions = ["jpg", "jpeg", "png", "pdf"];
            if(!empty($Image))
            {
                $attachments = $this->uploadFile2($UserID, $Image, $extensions);
            }
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlCheckBlock = $this->conn->query("SELECT StatusID, ID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check User in block.
                        $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status
                            if($blockData[0] == "2")
                            {
                                // Check apartment Existence.
                                // $sqlCheckAPT = $this->conn->query("SELECT ID FROM Apartment WHERE ID = '$APTID'");
                                $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
                                if($sqlCheckAPT->num_rows <= 0)
                                {
                                    $this->throwError(200, "apartment not found in block");
                                }
                                elseif($sqlCheckAPT->num_rows > 0)
                                {
                                    // Check Resident Relation to Apartment.
                                    $AptData = $sqlCheckAPT->fetch_row();
                                    if($AptData[2] == $UserID)
                                    {
                                        // Check Apartment Status
                                        if($AptData[1] == '2')
                                        {
                                            /*
                                             *  Write Code Here. 
                                             */
                                            if(empty($BlockNum))
                                            {
                                                $getBlockNum = $this->conn->query("SELECT BlockNum FROM Block WHERE ID = $BLKID");
                                                $BlockNumARR = $getBlockNum->fetch_row();
                                                $BlockNum = $BlockNumARR[0];
                                            }
                                            if(empty($BlockName))
                                            {
                                                $getBlockName = $this->conn->query("SELECT BlockName FROM Block WHERE ID = $BLKID");
                                                $BlockNameARR = $getBlockName->fetch_row();
                                                $BlockName = $BlockNameARR[0];
                                            }
                                            if(empty($NOA))
                                            {
                                                $getNOA = $this->conn->query("SELECT NumberOfAppartments FROM Block WHERE ID = $BLKID");
                                                $NOAARR = $getNOA->fetch_row();
                                                $NOA = $NOAARR[0];
                                            }
                                            if(empty($attachments))
                                            {
                                                $getImage = $this->conn->query("SELECT Image FROM Block WHERE ID = $BLKID");
                                                $ImageARR = $getImage->fetch_row();
                                                $ImageName = $ImageARR[0];
                                                $ImageUrl = "https://kcgwebservices.net/omartyapis/Images/BlockImages/" . $ImageARR[0];
                                            }
                                            if(!empty($attachments))
                                            {
                                                $getImage = $this->conn->query("SELECT Image FROM Block WHERE ID = $BLKID");
                                                $ImageARR = $getImage->fetch_row();
                                                unlink("../Images/BlockImages/" . $ImageARR[0]);
                                                $ImageUrl = "https://kcgwebservices.net/omartyapis/Images/BlockImages/" . $attachments['newName'];
                                                $ImageName = $attachments['newName'];
                                            }
                                            if(empty($Password))
                                            {
                                                $getPassword = $this->conn->query("SELECT Password FROM Block WHERE ID = $BLKID");
                                                $PWDARR = $getPassword->fetch_row();
                                                $Password = $PWDARR[0];
                                            }
                                            if(empty($Balance))
                                            {
                                                $getBalance = $this->conn->query("SELECT Balance FROM Block WHERE ID = $BLKID");
                                                $BALANCEARR = $getBalance->fetch_row();
                                                $Balance = $BALANCEARR[0];
                                                echo $Balance;
                                            }
                                            if(empty($Fees))
                                            {
                                                $getFees = $this->conn->query("SELECT Fees FROM Block WHERE ID = $BLKID");
                                                $FeesARR = $getFees->fetch_row();
                                                $Fees = $FeesARR[0];
                                            }
                                            if(empty($Longitude))
                                            {
                                                $getLongitude = $this->conn->query("SELECT Longitude FROM Block WHERE ID = $BLKID");
                                                $LOTARR = $getLongitude->fetch_row();
                                                $Longitude = $LOTARR[0];
                                            }
                                            if(empty($Latitude))
                                            {
                                                $getLatitude = $this->conn->query("SELECT Latitude FROM Block WHERE ID = $BLKID");
                                                $LATARR = $getLatitude->fetch_row();
                                                $Latitude = $LATARR[0];
                                            }
                                            
                                            $sqlUpdateBlock = $this->conn->query("UPDATE Block SET  BlockNum='$BlockNum',
                                                                                                    BlockName='$BlockName',
                                                                                                    NumberOfAppartments='$NOA',
                                                                                                    Image='$ImageName',
                                                                                                    Balance='$Balance',
                                                                                                    Fees='$Fees',
                                                                                                    Longitude='$Longitude',
                                                                                                    Latitude='$Latitude',
                                                                                                    UpdatedAt='$date',
                                                                                                    UpdatedBy='$UserID' WHERE ID = '$BLKID' ");
                                            if($sqlUpdateBlock)
                                            {
                                                $sqlGetBlock = $this->conn->query("SELECT * From Block where ID = $BLKID");
                                                if($Col = $sqlGetBlock->fetch_row())
                                                {
                                                    // Get Country
                                                    $sqlGetCountry = $this->conn->query("SELECT name From Country Where ID = '$Col[10]'");
                                                    if($sqlGetCountry->num_rows > 0)
                                                    {
                                                        $CountryNameArr = $sqlGetCountry->fetch_row();
                                                        $CountryName = $CountryNameArr[0];
                                                    }
                                                    elseif($sqlGetCountry->num_rows <= 0)
                                                    {
                                                        $CountryName = $ServiceData[16];
                                                    }
                                                    // Get Governate
                                                    $sqlGetGov = $this->conn->query("SELECT GOVName From Governate Where ID = '$Col[11]'");
                                                    if($sqlGetGov->num_rows > 0)
                                                    {
                                                        $GovNameArr = $sqlGetGov->fetch_row();
                                                        $GovName = $GovNameArr[0];
                                                    }
                                                    elseif($sqlGetGov->num_rows <= 0)
                                                    {
                                                        $GovName = $ServiceData[17];
                                                    }
                                                    // Get City
                                                    $sqlGetCity = $this->conn->query("SELECT Name From City Where ID = '$Col[12]'");
                                                    if($sqlGetCity->num_rows > 0)
                                                    {
                                                        $CityNameArr = $sqlGetCity->fetch_row();
                                                        $CityName = $CityNameArr[0];
                                                    }
                                                    elseif($sqlGetCity->num_rows <= 0)
                                                    {
                                                        $CityName = $ServiceData[18];
                                                    }
                                                    // Get Region
                                                    $sqlGetRegion = $this->conn->query("SELECT RegionName From Region Where ID = '$Col[13]'");
                                                    if($sqlGetRegion->num_rows > 0)
                                                    {
                                                        $RegionNameArr = $sqlGetRegion->fetch_row();
                                                        $RegionName = $RegionNameArr[0];
                                                    }
                                                    elseif($sqlGetRegion->num_rows <= 0)
                                                    {
                                                        $RegionName = $ServiceData[19];
                                                    }
                                                    // Get Compound
                                                    $sqlGetCompound = $this->conn->query("SELECT CompundName From Compound Where ID = '$Col[14]'");
                                                    if($sqlGetCompound->num_rows > 0)
                                                    {
                                                        $CompNameArr = $sqlGetCompound->fetch_row();
                                                        $CompName = $CompNameArr[0];
                                                    }
                                                    elseif($sqlGetCompound->num_rows <= 0)
                                                    {
                                                        $CompName = $ServiceData[20];
                                                    }
                                                    // Get Street
                                                    $sqlGetStreet = $this->conn->query("SELECT StreetName From Street Where ID = '$Col[15]'");
                                                    if($sqlGetStreet->num_rows > 0)
                                                    {
                                                        $StreetNameArr = $sqlGetStreet->fetch_row();
                                                        $StreetName = $StreetNameArr[0];
                                                    }
                                                    elseif($sqlGetStreet->num_rows <= 0)
                                                    {
                                                        $StreetName = $ServiceData[21];
                                                    }
                                                    
                                                    $arr = [
                                                        'ID' => $Col[0],
                                                        'blockNumber' => $Col[1],
                                                        'blockName' => $Col[20],
                                                        'numberOfApartments'=> $Col[2],
                                                        "image" => $ImageUrl,
                                                        "password" => $Col[5],
                                                        "balance" => $Col[6],
                                                        "fees" => $Col[7],
                                                        "longitude" => $Col[8],
                                                        "latitude" => $Col[9],
                                                        "countryID" => $Col[10],
                                                        "countryName" => $CountryName,
                                                        "governateID" => $Col[11],
                                                        "governateName" => $GovName,
                                                        "cityID" => $Col[12],
                                                        "cityName" => $CityName,
                                                        "regionID" => $Col[13],
                                                        "regionName" => $RegionName,
                                                        "compoundID" => $Col[14],
                                                        "compoundName" => $CompName,
                                                        "streetID" => $Col[15],
                                                        "streetName" => $StreetName,
                                                    ];
                                                    
                                                    
                                                    $Action = "Updating Block only by Block Manager";
                                                    $sqlLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, Date, UpdatedAt, UpdatedBy) VALUES ('$UserID', '$APTID', '$BLKID', 16, '$Action', '$date', '$date', '')");
                                           
                                                    $this->returnResponse(200, $arr);
                                                }
                                            }
                                        }
                                        elseif($AptData[1] == '1')
                                        {
                                            $this->throwError(200, "Apartment status is still binding.");
                                        }
                                        elseif($AptData[1] == '3')
                                        {
                                            $this->throwError(200, "Apartment is Banned.");
                                        }
                                        else
                                        {
                                            $this->throwError(200, "Apartment status is acceptable.");
                                        }
                                    }
                                    else
                                    {
                                        $this->throwError(406, "Resident does not relate to this apartment.");
                                    }
                                }
                            }
                            elseif($blockData[0] == "1")
                            {
                                $this->throwError(200, "Block status is still Binding.");
                            }
                            elseif($blockData[0] == "3")
                            {
                                $this->throwError(200, "Block is Banned.");
                            }
                            else
                            {
                                $this->throwError(401, "Block Status Not Acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "User doesn't have any relation to this block.");
                        }
                    }
                    else
                    {
                        $this->throwError(401, "Block Not Found.");
                    }
                    
                }
    }
    
    public function updateApartment()
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
            try
            {
                $token = $this->getBearerToken();
                $secret = "secret123";
                $decode = JWT::decode($token, new KEY($secret, 'HS256'));
            }
            catch(Exception $e)
            {
                $this->throwError(401, $e->getMessage());
            }
            // Request Data.
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $FloorNum =$_POST["floorNum"];
            $ApartmentNumber = $_POST["apartmentNum"];
            $ApartmentName = $_POST["apartmentName"];
            $Balance = $_POST["balance"];
            $Fees = $_POST["fees"];
            $date = date("Y-m-d H:i:sa");
            $UserID = $decode->id;
            
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlCheckBlock = $this->conn->query("SELECT StatusID, ID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check User in block.
                        $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status
                            if($blockData[0] == "2")
                            {
                                // Check apartment Existence.
                                // $sqlCheckAPT = $this->conn->query("SELECT ID FROM Apartment WHERE ID = '$APTID'");
                                $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
                                if($sqlCheckAPT->num_rows <= 0)
                                {
                                    $this->throwError(200, "apartment not found in block");
                                }
                                elseif($sqlCheckAPT->num_rows > 0)
                                {
                                    // Check Resident Relation to Apartment.
                                    $AptData = $sqlCheckAPT->fetch_row();
                                    if($AptData[2] == $UserID)
                                    {
                                        // Check Apartment Status
                                        if($AptData[1] == '2')
                                        {
                                            /*
                                             *  Write Code Here. 
                                             */
                                            if(empty($FloorNum))
                                            {
                                                $getFN = $this->conn->query("SELECT FloorNum FROM Apartment WHERE ID = $APTID");
                                                $FNARR = $getFN->fetch_row();
                                                $FloorNum = $FNARR[0];
                                            }
                                            if(empty($ApartmentNumber))
                                            {
                                                $getAPTNUM = $this->conn->query("SELECT ApartmentNumber FROM Apartment WHERE ID = $APTID");
                                                $APTNUMARR = $getAPTNUM->fetch_row();
                                                $ApartmentNumber = $APTNUMARR[0];
                                            }
                                            if(empty($ApartmentName))
                                            {
                                                $getAPTNAME = $this->conn->query("SELECT ApartmentName FROM Apartment WHERE ID = $APTID");
                                                $APTNAMEARR = $getAPTNAME->fetch_row();
                                                $ApartmentName = $APTNAMEARR[0];
                                            }
                                            if(empty($Balance))
                                            {
                                                $getBalance = $this->conn->query("SELECT balance FROM Apartment WHERE ID = $APTID");
                                                $BALANCEARR = $getBalance->fetch_row();
                                                $Balance = $BALANCEARR[0];
                                            }
                                            if(empty($Fees))
                                            {
                                                $getFees = $this->conn->query("SELECT Fees FROM Apartment WHERE ID = $APTID");
                                                $FeesARR = $getFees->fetch_row();
                                                $Fees = $FeesARR[0];
                                            }
                                            
                                            $sqlUpdateApartment = $this->conn->query("UPDATE Apartment SET FloorNum=$FloorNum,ApartmentNumber='$ApartmentNumber', ApartmentName='$ApartmentName' ,balance=$Balance,Fees=$Fees WHERE BlockID= '$BLKID'");
                                            if($sqlUpdateApartment)
                                            {
                                                $sqlGetApartment = $this->conn->query("SELECT * From Apartment where ID = $APTID");
                                                if($Col = $sqlGetApartment->fetch_row())
                                                {
                                                    $arr = [
                                                        'ID' => $Col[0],
                                                        "Floor Number" => $Col[1],
                                                        "Apartment Number" => $Col[2],
                                                        "ApartmentName" => $ApartmentName,
                                                        "Balance" => $Col[3],
                                                        "Fees" => $Col[4],
                                                    ];

                                                    $Action = "Updating Apartment Data";
                                                    $date = date("Y:m:d h-i-sa");
                                                    $sqlLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, Date, UpdatedAt) VALUES ('$UserID', '$APTID', '$BLKID', 15, '$Action', '$date', '$date')");
                                           
                                                    $this->returnResponse(200, $arr);
                                                }
                                            }
                                             
                                        }
                                        elseif($AptData[1] == '1')
                                        {
                                            $this->throwError(200, "Apartment status is still binding.");
                                        }
                                        elseif($AptData[1] == '3')
                                        {
                                            $this->throwError(200, "Apartment is Banned.");
                                        }
                                        else
                                        {
                                            $this->throwError(200, "Apartment status is acceptable.");
                                        }
                                    }
                                    else
                                    {
                                        $this->throwError(406, "Resident does not relate to this apartment.");
                                    }
                                }
                            }
                            elseif($blockData[0] == "1")
                            {
                                $this->throwError(200, "Block status is still Binding.");
                            }
                            elseif($blockData[0] == "3")
                            {
                                $this->throwError(200, "Block is Banned.");
                            }
                            else
                            {
                                $this->throwError(401, "Block Status Not Acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "User doesn't have any relation to this block.");
                        }
                    }
                    else
                    {
                        $this->throwError(401, "Block Not Found.");
                    }
                    
                }
    }

    public function updateEvent()
    {
        // include("../Config.php");
        date_default_timezone_set('Africa/Cairo');
        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(401, $e->getMessage());
        }

        
        // File Extensions.
        $extensions = ["jpg", "jpeg", "png", "pdf"];
        
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $EventID = $_POST["eventId"];
        $userID = $decode->id;
        $Attach = $_FILES["attach"];
        // $email = $decode->email;
        
        // get Event data
        $tittle = filter_var($_POST["tittle"], FILTER_SANITIZE_STRING);
        $body = filter_var($_POST["body"], FILTER_SANITIZE_STRING);
        $image = $this->uploadFile2($userID, $Attach, $extensions);
        $Location = $_POST["eventLocation"];
        $date = $_POST["date"];
        if(!empty($image)) { $location = "../Images/eventImages/". $image["newName"]; }
            
        $CurrentDate = date("Y-m-d h-i-sa");
        
        // Get saved Data from table Event From the DB.
        if(empty($Location))
        {
            $getLocation = $this->conn->query("SELECT EventLocation FROM Event WHERE ID = $EventID");
            $LOCARR = $getLocation->fetch_row();
            $Location = $LOCARR[0];
        }
        if(empty($tittle))
        {
            $getTittle = $this->conn->query("SELECT Tittle FROM Event WHERE ID = $EventID");
            $TITARR = $getTittle->fetch_row();
            $tittle = $TITARR[0];
        }
        if(empty($body))
        {
            $getBody = $this->conn->query("SELECT Body FROM Event WHERE ID = $EventID");
            $BODARR = $getBody->fetch_row();
            $body = $BODARR[0];
        }
        if(empty($date))
        {
            $getDate = $this->conn->query("SELECT Date FROM Event WHERE ID = $EventID");
            $DATARR = $getDate->fetch_row();
            $date = $DATARR[0];
        }
        if(empty($image))
        {
            $getImage = $this->conn->query("SELECT Image FROM Event WHERE ID = $EventID");
            $IMGARR = $getImage->fetch_row();
            $newImage = $IMGARR[0];
        }
        if(!empty($image))
        {
            $getImage = $this->conn->query("SELECT Image FROM Event WHERE ID = $EventID");
            $IMGARR = $getImage->fetch_row();
            $OldImage = $IMGARR[0];
            $newImage = $image["newName"];
            $imageUrl = "https://kcgwebservices.net/omartyapis/Images/eventImages/" . $image['newName'];
            unlink("../Images/eventImages/$OldImage");
        }
        // Check Block Existence.
        $sqlCheckBlock = $this->conn->query("SELECT ID,StatusID FROM Block WHERE ID='$BLKID' ");
        if($sqlCheckBlock->num_rows > 0)
        {
            // Check block Status.
             $blockData = $sqlCheckBlock->fetch_row();
            if($blockData[1] == '3')
            {
                $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                exit;
            }
            if($blockData[1] == '1')
            {
                $this->throwError(406, "Sorry block status is Binding.");
                exit;
            }
            if($blockData[1] == '2')
            {
                // Check User in block.
                $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
                if($sqlCheckResBlkRel->num_rows > 0)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows > 0)
                    {
                        // Check Resident relation to the apartment.
                        $AptData = $sqlCheckApt->fetch_row();
                        if($AptData[2] == $userID)
                        {
                            // Check apartment Status.
                            if($AptData[1] == '1')
                            {
                                $this->throwError(406, "Sorry Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == '3')
                            {
                                $this->throwError(406, "Sorry Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == '2')
                            {
                                // Check that event belongs to this apartment.
                                $sqlCheckEvent = $this->conn->query("SELECT ApartmentID FROM Event WHERE ID = '$EventID' AND BlockID = '$BLKID' AND ApartmentID = '$APTID' AND UserID = '$userID'");
                                if($sqlCheckEvent->num_rows > 0)
                                {
                                    // Get Block Manager ID and status.
                                    $sqlGetBlockManagerID = $this->conn->query("SELECT ResidentID,StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = 1");
                                    if($sqlGetBlockManagerID->num_rows > 0)
                                    {
                                        $BLKMNGData = $sqlGetBlockManagerID->fetch_row();
                                        if($BLKMNGData[1] == '1')
                                        {
                                            $this->throwError(406, "Your block manager acount status is some how Pending.");
                                            exit;
                                        }
                                        elseif($BLKMNGData[1] == '3')
                                        {
                                            $this->throwError(406, "Your block manager acount status is Banned.");
                                            exit;
                                        }
                                        elseif($BLKMNGData[1] == '2')
                                        {
                                            $sqlAddData = $this->conn->query("UPDATE Event SET 
                                                                                        Tittle = '$tittle',
                                                                                        Body = '$body',
                                                                                        Image = '$newImage',
                                                                                        Date = '$date',
                                                                                        EventLocation = '$Location',
                                                                                        UpdatedAt = '$CurrentDate',
                                                                                        UpdatedBy = '$userID'
                                                                                        WHERE ID = '$EventID';
                                                                    ");
                                            if($sqlAddData)
                                            {
                                                if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                            
                                                $Action = "Update New Event";
                                                $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, UpdatedAt) 
                                                                    VALUES ('$userID', '$APTID', '$BLKID', 15, '$Action', '$EventID', 'Event', '$Longitude', '$Latitude', '$CurrentDate', '$CurrentDate')");
                                                                    if($this->conn->error)
                                                                    {
                                                                        echo $this->conn->error;
                                                                    }
                                                
                                                // print out event data.
                                                $sqlGetEventData = $this->conn->query("SELECT ID, Tittle, Body, Image, Date, EventLocation, NumOfAttendees, CreatedAt, UpdatedAt FROM Event WHERE ID = $EventID");
                                                if($sqlGetEventData->num_rows > 0 )
                                                {
                                                    $ED = $sqlGetEventData->fetch_row();
                                                    $Arr = [
                                                        "id" => $ED[0],
                                                        "tittle" => $ED[1],
                                                        "body" => $ED[2],
                                                        "Image" => $imageUrl,
                                                        "date" => $ED[4],
                                                        "eventLocation" => $ED[5],
                                                        "numOfAttendees" => $ED[6],
                                                        "createdAt" => $ED[7],
                                                        "updatedAt" => $ED[8],
                                                        ];
                                                }
                                                $this->returnResponse(200, $Arr);
                                            }
                                            else
                                            {
                                                $this->throwError(304, "Record was not inserted, Please try again.");
                                            }
                                        }
                                        else
                                        {
                                            $this->throwError(406, "Block Manager status is un defined.");
                                        }
                                    }
                                    else
                                    {
                                        $this->returnResponse(205, "Block Manager was not found.");
                                    }
                                }
                                else
                                {
                                    $this->throwError(406, "Resident does not relate to this event.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Sorry Apartment status is ot acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(200, "Resident does not relate to this apartment.");
                        }
                    }
                    else
                    {
                        $this->throwError(406, "Apartment not found in Block.");
                    }
                }
                else
                {
                    $this->throwError(406, "Resident does not relate to this block.");
                }
            }
            else
            {
                $this->throwError(406, "Sorry block status is ot acceptable.");
            }
        }
        else
        {
            $this->throwError(406, "Block not found.");
        }
    }
    
    public function AddContact()
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
        
            try
            {
                $token = $this->getBearerToken();
                $secret = "secret123";
                $decode = JWT::decode($token, new KEY($secret, 'HS256'));
            }
            catch(Exception $e)
            {
                $this->throwError(401, $e->getMessage());
            }
            // Request Data.
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $PhoneNum = $_POST["phoneNumber"];
            $Email = $_POST["email"];
            $Longitude = $_POST["longitude"];
            $Latitude = $_POST["latitude"];
            $date = date("Y-m-d H:i:s");
            $UserID = $decode->id;
            
            if(empty($Longitude))
            {
                $Longitude = 0;
            }
            if(empty($Latitude))
            {
                $Latitude = 0;
            }
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlCheckBlock = $this->conn->query("SELECT StatusID, ID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check User in block.
                        $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status
                            if($blockData[0] == "2")
                            {
                                // Check apartment Existence.
                                // $sqlCheckAPT = $this->conn->query("SELECT ID FROM Apartment WHERE ID = '$APTID'");
                                $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
                                if($sqlCheckAPT->num_rows <= 0)
                                {
                                    $this->throwError(200, "apartment not found in block");
                                }
                                elseif($sqlCheckAPT->num_rows > 0)
                                {
                                    // Check Resident Relation to Apartment.
                                    $AptData = $sqlCheckAPT->fetch_row();
                                    if($AptData[2] == $UserID)
                                    {
                                        // Check Apartment Status
                                        if($AptData[1] == '2')
                                        {
                                            if(!empty($PhoneNum) && empty($Email))
                                            {
                                                // Check if Phone Number existes in User Records.
                                                $sqlCheckPN = $this->conn->query("SELECT PhoneNum FROM Resident_User WHERE ID = '$UserID' And PhoneNum = '$PhoneNum'");
                                                $sqlCheckPN2 = $this->conn->query("SELECT PhoneNum FROM PhoneNums WHERE UserID = '$UserID' And PhoneNum = '$PhoneNum'");
                                                $sqlCheckPN3 = $this->conn->query("SELECT PhoneNum FROM Resident_User WHERE ID <> '$UserID' And PhoneNum = '$PhoneNum'");
                                                $sqlCheckPN4 = $this->conn->query("SELECT PhoneNum FROM PhoneNums WHERE UserID <> '$UserID' And PhoneNum = '$PhoneNum'");
                                                if($sqlCheckPN->num_rows > 0 || $sqlCheckPN2->num_rows > 0)
                                                {
                                                    $Contacts = $this->ShowContacts($UserID);
                                                    $Contacts += ["error" => "Phone number already set for you"];
                                                    $this->throwError(200, $Contacts);
                                                }
                                                // Check if Phone Number existes in the whole database.
                                                elseif($sqlCheckPN3->num_rows > 0 || $sqlCheckPN4->num_rows > 0)
                                                {
                                                    $Contacts = $this->ShowContacts($UserID);
                                                    $Contacts += ["error" => "Phone number already set for another user"];
                                                    $this->throwError(200, $Contacts);
                                                }
                                                // Insert New Phone Number to PhoneNums Table.
                                                else
                                                {
                                                    $sqlInsertPN = $this->conn->query("INSERT INTO PhoneNums (UserID, PhoneNum, Hide, CreatedAt) VALUES ('$UserID', '$PhoneNum', 0, '$date')");    
                                                    $this->returnResponse(200, $this->ShowContacts($UserID));
                                                    if($sqlInsertPN == true)
                                                    {
                                                        // Insert into logs.
                                                        $RecordID = $this->conn->query("SELECT ID FROM PhoneNums WHERE PhoneNum = '$PhoneNum' AND UserID = '$UserID'");
                                                        $newId = $RecordID->fetch_row();
                                                        $Action = "Insert New Phone Number for user";
                                                        $sqlLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, UpdatedAt, UpdatedBy) 
                                                                                        VALUES ('$UserID', '$APTID', '$BLKID', 3, '$Action','$newId[0]', 'PhoneNums', '$Longitude', '$Latitude', '$date', '$date', '$UserID')");
                                        
                                                    }
                                                }
                                            }
                                            elseif(!empty($Email) && empty($PhoneNum))
                                            {
                                                // Check if Email existes in User Records.
                                                $sqlCheckEmail = $this->conn->query("SELECT Email FROM Resident_User WHERE ID = '$UserID' AND Email = '$Email'");
                                                $sqlCheckEmail2 = $this->conn->query("SELECT Email FROM Emails WHERE UserID = '$UserID' AND Email = '$Email'");
                                                $sqlCheckEmail3 = $this->conn->query("SELECT Email FROM Resident_User WHERE ID <> '$UserID' AND Email = '$Email'");
                                                $sqlCheckEmail4 = $this->conn->query("SELECT Email FROM Emails WHERE UserID <> '$UserID' AND Email = '$Email'");
                                                
                                                if($sqlCheckEmail->num_rows > 0 || $sqlCheckEmail2->num_rows > 0)
                                                {
                                                    
                                                    $Contacts = $this->ShowContacts($UserID);
                                                    $Contacts += ["error" => "Email already set for you"];
                                                    $this->throwError(200, $Contacts);
                                                }
                                                // Check if Email existes in the whole database.
                                                elseif($sqlCheckEmail3->num_rows > 0 || $sqlCheckEmail4->num_rows > 0)
                                                {
                                                    $Contacts = $this->ShowContacts($UserID);
                                                    $Contacts += ["error" => "Email already set for another user"];
                                                    $this->throwError(200, $Contacts);
                                                }
                                                // Insert New Email to Emails Table.
                                                else
                                                {
                                                    $sqlInsertEmail = $this->conn->query("INSERT INTO Emails (UserID, Email, Hide, CreatedAt) VALUES ('$UserID', '$Email', 0, '$date')");
                                                    $this->returnResponse(200, $this->ShowContacts($UserID));
                                                    if($sqlInsertEmail == true)
                                                    {
                                                        // Insert into logs.
                                                        $RecordID = $this->conn->query("SELECT ID FROM Emails WHERE Email = '$Email' AND UserID = '$UserID'");
                                                        $newId = $RecordID->fetch_row();
                                                        $Action = "Insert New Email for user";
                                                        $sqlLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, UpdatedAt, UpdatedBy) 
                                                                                        VALUES ('$UserID', '$APTID', '$BLKID', 3, '$Action','$newId[0]', 'Emails', '$Longitude', '$Latitude', '$date', '$date', '$UserID')");
                                        
                                                    }
                                                }
                                            }
                                            else
                                            {
                                                $Contacts = $this->ShowContacts($UserID);
                                                $Contacts += ["error" => "No Data Inserted."];
                                                $this->throwError(200, $Contacts);
                                            }
                                        }
                                        elseif($AptData[1] == '1')
                                        {
                                            $this->throwError(200, "Apartment status is still binding.");
                                        }
                                        elseif($AptData[1] == '3')
                                        {
                                            $this->throwError(200, "Apartment is Banned.");
                                        }
                                        else
                                        {
                                            $this->throwError(200, "Apartment status is acceptable.");
                                        }
                                    }
                                    else
                                    {
                                        $this->throwError(406, "Resident does not relate to this apartment.");
                                    }
                                }
                            }
                            elseif($blockData[0] == "1")
                            {
                                $this->throwError(200, "Block status is still Binding.");
                            }
                            elseif($blockData[0] == "3")
                            {
                                $this->throwError(200, "Block is Banned.");
                            }
                            else
                            {
                                $this->throwError(401, "Block Status Not Acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "User doesn't have any relation to this block.");
                        }
                    }
                    else
                    {
                        $this->throwError(401, "Block Not Found.");
                    }
                    
                }
    }
    
    private function ShowContacts($UserID)
    {
        // Get User Contacts.
            $sqlGetEmails = $this->conn->query("SELECT ID, Email FROM Emails WHERE UserID = '$UserID'");
            $sqlGetPhoneNums = $this->conn->query("SELECT ID, PhoneNum FROM PhoneNums WHERE UserID = '$UserID'");
            $ContactArr = [];
            if($sqlGetEmails->num_rows > 0 || $sqlGetPhoneNums->num_rows > 0)
            {
            $count1 = 1;
            // $StoredEmails = $sqlGetEmails->fetch_row();
            while($StoredEmails = $sqlGetEmails->fetch_row())
            {
                // array_push($ContactArr, $StoredEmails[0]);
                $ContactArr[$count1] = [ "id" => $StoredEmails[0], "email" => $StoredEmails[1] ];
                $count1++;
            }
            $count = 1;
            while($StoredPN = $sqlGetPhoneNums->fetch_row())
            {
                // $ContactArr += $StoredPN[$count];
                // array_push($ContactArr, $StoredPN[0]);
                $ContactArr[$count1] = ["id" => $StoredPN[0], "phoneNum" => $StoredPN[1] ];
                $count1++;
                $count++;
            }
        }
        // $this->returnResponse(200, $ContactArr);
        return $ContactArr;
    }
    
    public function HideData()
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");

            try
            {
                $token = $this->getBearerToken();
                $secret = "secret123";
                $decode = JWT::decode($token, new KEY($secret, 'HS256'));
            }
            catch(Exception $e)
            {
                $this->throwError(401, $e->getMessage());
            }
            // Request Data.
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $HideEmail = $_POST["email"];
            $HidePhoneNum = $_POST["phoneNumber"];
            
            $date = date("Y-m-d H:i:sa");
            $UserID = $decode->id;
            
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlCheckBlock = $this->conn->query("SELECT StatusID, ID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check User in block.
                        $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status
                            if($blockData[0] == "2")
                            {
                                // Check apartment Existence.
                                // $sqlCheckAPT = $this->conn->query("SELECT ID FROM Apartment WHERE ID = '$APTID'");
                                $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
                                if($sqlCheckAPT->num_rows <= 0)
                                {
                                    $this->throwError(200, "apartment not found in block");
                                }
                                elseif($sqlCheckAPT->num_rows > 0)
                                {
                                    // Check Resident Relation to Apartment.
                                    $AptData = $sqlCheckAPT->fetch_row();
                                    if($AptData[2] == $UserID)
                                    {
                                        // Check Apartment Status
                                        if($AptData[1] == '2')
                                        {
                                            // Get User ID, BlockID, ApartmentID, DataToBeHidden
                                            // Hide Primary Email.
                                            if($HideEmail == -1)
                                            {
                                                // Set Hide = 1 in Resident_User.
                                                    // Check if User is hidding his PhoneNumber Set Hide to 3.
                                                    $sqlCheckHide = $this->conn->query("SELECT ID FROM Resident_User WHERE ID = '$UserID' AND Hide = 2");
                                                    // if User Is Already Hidding his email then Show It
                                                    $sqlCheckShow = $this->conn->query("SELECT ID FROM Resident_User WHERE ID = '$UserID' AND Hide = 1");
                                                    // if User Is Already Hidding his email AND his Phone Number then Show Email and leave PhoneNumber Hidden
                                                    $sqlCheckShow2 = $this->conn->query("SELECT ID FROM Resident_User WHERE ID = '$UserID' AND Hide = 3");
                                                    // Show Email.
                                                    if($sqlCheckShow->num_rows > 0)
                                                    {
                                                        // Hide => 3
                                                        $Hide = 0;
                                                    }
                                                    // Show Email and leave phonenumber hidden.
                                                    elseif($sqlCheckShow2->num_rows > 0)
                                                    {
                                                        // Hide => 3
                                                        $Hide = 2;
                                                    }
                                                    // Hide Email.
                                                    elseif($sqlCheckHide->num_rows <= 0)
                                                    {
                                                        // Hide => 1
                                                        $Hide = 1;
                                                    }
                                                    // Hide Email with phone number
                                                    elseif($sqlCheckHide->num_rows > 0)
                                                    {
                                                        // Hide => 3
                                                        $Hide = 3;
                                                    }
                                                $sqlUpdateResEmail = $this->conn->query("UPDATE Resident_User SET Hide = '$Hide' WHERE ID = '$UserID'");
                                                if($sqlUpdateResEmail)
                                                {
                                                    $this->returnResponse(200, "Record Updated");
                                                }
                                                elseif($sqlUpdateResEmail)
                                                {
                                                    $this->throwError(200, "Record Not Updated");
                                                }    
                                            }
                                            // Hide Secondary Email.
                                            elseif($HideEmail !== -1 && !empty($HideEmail))
                                            {
                                                // Check if This ID exists in Emails table.
                                                $sqlCheckID = $this->conn->query("SELECT ID, Hide FROM Emails WHERE ID = '$HideEmail'");
                                                
                                                if($sqlCheckID->num_rows > 0)
                                                {
                                                    $EmailData = $sqlCheckID->fetch_row();
                                                    if($EmailData[1] == '0')
                                                    {
                                                        // Set Hide = 1 in Emails.
                                                        $sqlUpdateEmails = $this->conn->query("UPDATE Emails SET Hide = 1 WHERE UserID = '$UserID' AND ID = $HideEmail");
                                                        if($sqlUpdateEmails)
                                                        {
                                                            $this->returnResponse(200, "Record Updated");
                                                        }
                                                        elseif($sqlUpdateEmails)
                                                        {
                                                            $this->throwError(200, "Record Not Updated");
                                                        }
                                                    }
                                                    elseif($EmailData[1] == '1')
                                                    {
                                                        // Set Hide = 0 in Emails.
                                                        $sqlUpdateEmails = $this->conn->query("UPDATE Emails SET Hide = 0 WHERE UserID = '$UserID' AND ID = $HideEmail");
                                                        if($sqlUpdateEmails)
                                                        {
                                                            $this->returnResponse(200, "Record Updated");
                                                        }
                                                        elseif($sqlUpdateEmails)
                                                        {
                                                            $this->throwError(200, "Record Not Updated");
                                                        }
                                                    }
                                                }
                                                elseif($sqlCheckID->num_rows <= 0)
                                                {
                                                    $this->throwError(200, "Didn't find email with the given ID.");
                                                }
                                                
                                            }
                                            // ===================================================<><><><><><>=================================================== //
                                            // Hide Primary PhoneNumber.
                                            if($HidePhoneNum == -1)
                                            {
                                                // Set Hide = 1 in Resident_User.
                                                    // Check if User is hidding his PhoneNumber Set Hide to 3.
                                                    $sqlCheckHide = $this->conn->query("SELECT ID FROM Resident_User WHERE ID = '$UserID' AND Hide = 1");
                                                    // if User Is Already Hidding his PhoneNumber then Show It
                                                    $sqlCheckShow = $this->conn->query("SELECT ID FROM Resident_User WHERE ID = '$UserID' AND Hide = 2");
                                                    // if User Is Already Hidding his Phone Number AND his email then Show Phone Number and leave Email Hidden
                                                    $sqlCheckShow2 = $this->conn->query("SELECT ID FROM Resident_User WHERE ID = '$UserID' AND Hide = 3");
                                                    // Show PhoneNumber
                                                    if($sqlCheckShow->num_rows > 0)
                                                    {
                                                        // Hide => 3
                                                        $Hide = 0;
                                                    }
                                                    // Show PhoneNumber And Leave Email hidden
                                                    elseif($sqlCheckShow2->num_rows > 0)
                                                    {
                                                        // Hide => 3
                                                        $Hide = 1;
                                                    }
                                                    // Hide PhoneNumber
                                                    elseif($sqlCheckHide->num_rows <= 0)
                                                    {
                                                        // Hide => 1
                                                        $Hide = 2;
                                                    }
                                                    // Hide PhoneNumber With Email
                                                    elseif($sqlCheckHide->num_rows > 0)
                                                    {
                                                        // Hide => 3
                                                        $Hide = 3;
                                                    }
                                                $sqlUpdateResPN = $this->conn->query("UPDATE Resident_User SET Hide = '$Hide' WHERE ID = '$UserID'");
                                                if($sqlUpdateResPN)
                                                {
                                                    $this->returnResponse(200, "Record Updated");
                                                }
                                                elseif($sqlUpdateResPN)
                                                {
                                                    $this->throwError(200, "Record Not Updated");
                                                }
                                            }
                                            // Hide Secondary PhoneNumber.
                                            elseif($HidePhoneNum !== -1 && !empty($HidePhoneNum))
                                            {
                                                // Check if This ID exists in PhoneNums table.
                                                $sqlCheckID = $this->conn->query("SELECT ID, Hide FROM PhoneNums WHERE ID = '$HidePhoneNum'");
                                                if($sqlCheckID->num_rows > 0)
                                                {
                                                    $PNData = $sqlCheckID->fetch_row();
                                                    if($PNData[1] == '0')
                                                    {
                                                        // Set Hide = 1 in PhoneNums.
                                                        $sqlUpdatePN = $this->conn->query("UPDATE PhoneNums SET Hide = 1 WHERE UserID = '$UserID' AND ID = '$HidePhoneNum'");
                                                        if($sqlUpdatePN)
                                                        {
                                                            $this->returnResponse(200, "Record Updated");
                                                        }
                                                        elseif($sqlUpdatePN)
                                                        {
                                                            $this->throwError(200, "Record Not Updated");
                                                        }
                                                    }
                                                    elseif($PNData[1] == '1')
                                                    {
                                                        // Set Hide = 0 in PhoneNums.
                                                        $sqlUpdatePN = $this->conn->query("UPDATE PhoneNums SET Hide = 0 WHERE UserID = '$UserID' AND ID = '$HidePhoneNum'");
                                                        if($sqlUpdatePN)
                                                        {
                                                            $this->returnResponse(200, "Record Updated");
                                                        }
                                                        elseif($sqlUpdatePN)
                                                        {
                                                            $this->throwError(200, "Record Not Updated");
                                                        }
                                                    }
                                                }
                                                elseif($sqlCheckID->num_rows > 0)
                                                {
                                                    $this->throwError(200, "Didn't find email with the given ID.");
                                                }
                                            }
                                            // ===================================================<><><><><><>=================================================== //
                                        }
                                        elseif($AptData[1] == '1')
                                        {
                                            $this->throwError(200, "Apartment status is still binding.");
                                        }
                                        elseif($AptData[1] == '3')
                                        {
                                            $this->throwError(200, "Apartment is Banned.");
                                        }
                                        else
                                        {
                                            $this->throwError(200, "Apartment status is acceptable.");
                                        }
                                    }
                                    else
                                    {
                                        $this->throwError(406, "Resident does not relate to this apartment.");
                                    }
                                }
                            }
                            elseif($blockData[0] == "1")
                            {
                                $this->throwError(200, "Block status is still Binding.");
                            }
                            elseif($blockData[0] == "3")
                            {
                                $this->throwError(200, "Block is Banned.");
                            }
                            else
                            {
                                $this->throwError(401, "Block Status Not Acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "User doesn't have any relation to this block.");
                        }
                    }
                    else
                    {
                        $this->throwError(401, "Block Not Found.");
                    }
                    
                }
    }
    
    public function AllowNotification()
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
        
            try
            {
                $token = $this->getBearerToken();
                $secret = "secret123";
                $decode = JWT::decode($token, new KEY($secret, 'HS256'));
            }
            catch(Exception $e)
            {
                $this->throwError(401, $e->getMessage());
            }
            // Request Data.
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $HideMeeting = $_POST["meeting"];
            $HideEvent = $_POST["event"];
            $HideNews = $_POST["news"];
            $HideOffer = $_POST["offer"];
            $HideChat = $_POST["chat"];
            $HideFinancial = $_POST["financial"];
            $date = date("Y-m-d H:i:sa");
            $UserID = $decode->id;
            
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlCheckBlock = $this->conn->query("SELECT StatusID, ID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check User in block.
                        $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status
                            if($blockData[0] == "2")
                            {
                                // Check apartment Existence.
                                // $sqlCheckAPT = $this->conn->query("SELECT ID FROM Apartment WHERE ID = '$APTID'");
                                $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
                                if($sqlCheckAPT->num_rows <= 0)
                                {
                                    $this->throwError(200, "apartment not found in block");
                                }
                                elseif($sqlCheckAPT->num_rows > 0)
                                {
                                    // Check Resident Relation to Apartment.
                                    $AptData = $sqlCheckAPT->fetch_row();
                                    if($AptData[2] == $UserID)
                                    {
                                        // Check Apartment Status
                                        if($AptData[1] == '2')
                                        {
                                            // Check what types are not allowed.
                                                $sqlCheckAllow = $this->conn->query("SELECT HideMeeting, HideEvent, HideNews, HideOffers, HideChat, HideFinancial FROM NotifSettings WHERE UserID = '$UserID'");
                                                if($sqlCheckAllow->num_rows > 0)
                                                {
                                                    $Allowed = $sqlCheckAllow->fetch_row();
                                                    // ===================================================<><><><><><>=================================================== //
                                                        // Don't allow Meeting.
                                                        if($Allowed[0] == '0' && !empty($HideMeeting))
                                                        {
                                                            $sqlHideMeet = $this->conn->query("UPDATE NotifSettings SET HideMeeting = 1 WHERE UserID = '$UserID'");
                                                            // $this->returnResponse(200, "OK");
                                                        }
                                                        // Allow Meeting.
                                                        elseif($Allowed[0] == '1' && !empty($HideMeeting))
                                                        {
                                                            $sqlAllowMeet = $this->conn->query("UPDATE NotifSettings SET HideMeeting = 0 WHERE UserID = '$UserID'");
                                                        }
                                                    // ===================================================<><><><><><>=================================================== //
                                                        // Don't allow Event.
                                                        if($Allowed[1] == '0' && !empty($HideEvent))
                                                        {
                                                            $sqlHideEvent = $this->conn->query("UPDATE NotifSettings SET HideEvent = 1 WHERE UserID = '$UserID'");
                                                            // $this->returnResponse(200, "OK");
                                                        }
                                                        // Allow Event.
                                                        elseif($Allowed[1] == '1' && !empty($HideEvent))
                                                        {
                                                            $sqlAllowEvent = $this->conn->query("UPDATE NotifSettings SET HideEvent = 0 WHERE UserID = '$UserID'");
                                                        }
                                                    // ===================================================<><><><><><>=================================================== //
                                                        // Don't allow News.
                                                        if($Allowed[2] == '0' && !empty($HideNews))
                                                        {
                                                            $sqlHideNews = $this->conn->query("UPDATE NotifSettings SET HideNews = 1 WHERE UserID = '$UserID'");
                                                            // $this->returnResponse(200, "OK");
                                                        }
                                                        // Allow News.
                                                        elseif($Allowed[2] == '1' && !empty($HideNews))
                                                        {
                                                            $sqlAllowNews = $this->conn->query("UPDATE NotifSettings SET HideNews = 0 WHERE UserID = '$UserID'");
                                                        }
                                                    // ===================================================<><><><><><>=================================================== //
                                                        // Don't allow Offers.
                                                        if($Allowed[3] == '0' && !empty($HideOffer))
                                                        {
                                                            $sqlHideOffer = $this->conn->query("UPDATE NotifSettings SET HideOffers = 1 WHERE UserID = '$UserID'");
                                                            // $this->returnResponse(200, "OK");
                                                        }
                                                        // Allow Offers.
                                                        elseif($Allowed[3] == '1' && !empty($HideOffer))
                                                        {
                                                            $sqlAllowOffer = $this->conn->query("UPDATE NotifSettings SET HideOffers = 0 WHERE UserID = '$UserID'");
                                                        }
                                                    // ===================================================<><><><><><>=================================================== //
                                                        // Don't allow Chat.
                                                        if($Allowed[4] == '0' && !empty($HideChat))
                                                        {
                                                            $sqlHideChat = $this->conn->query("UPDATE NotifSettings SET HideChat = 1 WHERE UserID = '$UserID'");
                                                            // $this->returnResponse(200, "OK");
                                                        }
                                                        // Allow Chat.
                                                        elseif($Allowed[4] == '1' && !empty($HideChat))
                                                        {
                                                            $sqlAllowChat = $this->conn->query("UPDATE NotifSettings SET HideChat = 0 WHERE UserID = '$UserID'");
                                                        }
                                                    // ===================================================<><><><><><>=================================================== //
                                                        // Don't allow Financials.
                                                        if($Allowed[5] == '0' && !empty($HideFinancial))
                                                        {
                                                            $sqlHideFinan = $this->conn->query("UPDATE NotifSettings SET HideFinancial = 1 WHERE UserID = '$UserID'");
                                                            // $this->returnResponse(200, "OK");
                                                        }
                                                        // Allow Financials.
                                                        elseif($Allowed[5] == '1' && !empty($HideFinancial))
                                                        {
                                                            $sqlAllowFinan = $this->conn->query("UPDATE NotifSettings SET HideFinancial = 0 WHERE UserID = '$UserID'");
                                                        }
                                                    // ===================================================<><><><><><>=================================================== //
                                                    $SettingArr = [];
                                                    $sqlCheckNotifSett = $this->conn->query("SELECT HideMeeting, HideEvent, HideNews, HideOffers, HideChat, HideFinancial FROM NotifSettings WHERE UserID = '$UserID'");
                                                    if($sqlCheckNotifSett->num_rows > 0)
                                                    {
                                                        $UserNotifSettingArr = $sqlCheckNotifSett->fetch_row();
                                                        if($UserNotifSettingArr[0] == '1')
                                                        {   $SettingArr += ["meetings" => "Prevented"];    }
                                                        if($UserNotifSettingArr[1] == '1')
                                                        {   $SettingArr += ["event" => "Prevented"];   }
                                                        if($UserNotifSettingArr[2] == '1')
                                                        {   $SettingArr += ["news" => "Prevented"];    }
                                                        if($UserNotifSettingArr[3] == '1')
                                                        {   $SettingArr += ["offers" => "Prevented"];  }
                                                        if($UserNotifSettingArr[4] == '1')
                                                        {   $SettingArr += ["chat" => "Prevented"];    }
                                                        if($UserNotifSettingArr[5] == '1')
                                                        {   $SettingArr += ["financials" => "Prevented"];  }
                                                        if($UserNotifSettingArr[0] == '0')
                                                        {   $SettingArr += ["meetings" => "Allowed"];    }
                                                        if($UserNotifSettingArr[1] == '0')
                                                        {   $SettingArr += ["event" => "Allowed"];   }
                                                        if($UserNotifSettingArr[2] == '0')
                                                        {   $SettingArr += ["news" => "Allowed"];    }
                                                        if($UserNotifSettingArr[3] == '0')
                                                        {   $SettingArr += ["offers" => "Allowed"];  }
                                                        if($UserNotifSettingArr[4] == '0')
                                                        {   $SettingArr += ["chat" => "Allowed"];    }
                                                        if($UserNotifSettingArr[5] == '0')
                                                        {   $SettingArr += ["financials" => "Allowed"];  }
                                                    }
                                                    $this->returnResponse(200, $SettingArr);
                                                }
                                                elseif($sqlCheckAllow->num_rows <= 0)
                                                {
                                                    $this->throwError(200, "Error!!!");
                                                }
                                        }
                                        elseif($AptData[1] == '1')
                                        {
                                            $this->throwError(200, "Apartment status is still binding.");
                                        }
                                        elseif($AptData[1] == '3')
                                        {
                                            $this->throwError(200, "Apartment is Banned.");
                                        }
                                        else
                                        {
                                            $this->throwError(200, "Apartment status is acceptable.");
                                        }
                                    }
                                    else
                                    {
                                        $this->throwError(406, "Resident does not relate to this apartment.");
                                    }
                                }
                            }
                            elseif($blockData[0] == "1")
                            {
                                $this->throwError(200, "Block status is still Binding.");
                            }
                            elseif($blockData[0] == "3")
                            {
                                $this->throwError(200, "Block is Banned.");
                            }
                            else
                            {
                                $this->throwError(401, "Block Status Not Acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "User doesn't have any relation to this block.");
                        }
                    }
                    else
                    {
                        $this->throwError(401, "Block Not Found.");
                    }
                    
                }
    }
    
    public function SetNotifSett()
    {
        // include("../Config.php");
        date_default_timezone_set('Africa/Cairo');
        $date = date("Y-m-d H:i:s");
        
        $sqlGetAllUsers = $this->conn->query("SELECT ID FROM Resident_User");
        $count = 1;
        while($UserData = $sqlGetAllUsers->fetch_row())
        {
            // Check For Multible Apartments.
            $sqlCheckForApt = $this->conn->query("SELECT ApartmentID, BlockID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$UserData[0]'");
            if($sqlCheckForApt->num_rows > 0)
            {
                while($APTData = $sqlCheckForApt->fetch_row())
                {
                    echo 1;
                    $sqlInsertNotifSetting = $this->conn->query("INSERT INTO NotifSettings (UserID, ApartmentID, BlockID, CreatedAt) VALUES ('$UserData[0]', '$APTData[0]', '$APTData[1]', '$date')");
                }
            }
            elseif($sqlCheckForApt->num_rows <= 0)
            {
                echo 2;
                    $sqlInsertNotifSetting = $this->conn->query("INSERT INTO NotifSettings (UserID, CreatedAt) VALUES ('$UserData[0]', '$date')");
            }
            $count++;
        }
        echo $count;
    }
    
}

?>