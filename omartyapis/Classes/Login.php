<?php

    // session_start();
   
        include("../../vendor/autoload.php");

        // Include Database file.
        include("../../Config.php");
            header("content-type: Aplication/json");

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
 
class Login extends Functions
{
    private $RootUrl = "https://plateform.omarty.net/";
    // protected $Signature;
        // Assign the data that user have entered.

     // What happends if user didn't enter any data and clicked submit.
    
    public function login()
    {
        include("../../Config.php");
        date_default_timezone_set('Africa/Cairo');

        $Email= filter_var($_POST["email"], FILTER_SANITIZE_EMAIL);
        $email = strtolower($Email);
        $password = $_POST["password"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $GoogleToken = $_POST["googleToken"];
        $OS = $_POST["os"];
        $DeviceID = $_POST["deviceId"];
        if(empty($GoogleToken))
        {
            $GoogleToken = 0;
        }
        if(empty($OS))
        {
            $OS = 0;
        }
        if (empty($email) || empty($password))
        {
            $this->throwError(406, "Please enter Data (email / password)");
            exit;
        }
        // What happends if user didn't enter his email.
        elseif(!empty($email) && !empty($password))
        {
            // Make queries to get All data and send them to front end and to validate user info to our DataBase data if email was entered.
            $sql1 = $conn->query("SELECT * FROM Resident_User WHERE Email = '$email'");
            // Make queries to get All data and send them to front end and to validate user info to our DataBase data if phone number was entered.
            $sql2 = $conn->query("SELECT * FROM Resident_User WHERE PhoneNum = '$email'");

            //  Save data retreived from the query in @Col variable
            // $Col = $sql1->fetch_row();
            // $PN = $sql2->fetch_row();
            // // check Whether the user entered email or phone number and save the data of either option into @Col variable
            // if( $Col[5] > 0 )  { $Col = $Col; }
            // else            { $Col = $PN; }

                //  Save data retreived from the query in @Col variable
                $Col = $sql1->fetch_row();
                $PN = $sql2->fetch_row();
                // check Whether the user entered email or phone number and save the data of either option into @Col variable
                if( !empty($Col[5]))  { $Col = $Col; }
                elseif(empty($Col[5]))   { $Col = $PN; }

            // What happends if user entered any data.
            if($sql1->num_rows > 0 || $sql2->num_rows > 0)
            {
                if($Col[8] == '1')
                {
                    $this->throwError(403, "This email Status is Binding, if you have any complain please contact US");
                    exit;
                }
                if($Col[8] == '3')
                {
                    $this->throwError(403, "This email is Banned by Omarty Super Admin, if you have any complain please contact US");
                    exit;
                }
                elseif($Col[8] == '2')
                {
                    // What happends if user didn't enter the correct password.
                    if( ( $email == $Col[3] || $email == $Col[5] ) && !( password_verify($password, $Col[4]) ))
                    {
                        $this->throwError(401, "Wrong Password");
                        exit;
                    }
                    // What happends if user did enter the correct email and password.
                    elseif ( ( $email == $Col[3] || $email == $Col[5] ) && ( password_verify($password, $Col[4]) ) )
                    {
                        // // Get ALL User Data Blocks, Apartments, Roles, Status.
                        // $sqlGetBlkAPTIDs = $conn->query("SELECT BlockID, ApartmentID, StatusID, RoleID from RES_APART_BLOCK_ROLE WHERE ResidentID = $Col[0]");
                        // $count = 1;
                        // $Data = [];

                        // while($BLKAPT = $sqlGetBlkAPTIDs->fetch_row())
                        // {
                        //     // Get Status Name.
                        //     $sqlGetStatus = $conn->query("SELECT Name FROM Status WHERE ID = $BLKAPT[2]");
                        //     $status = $sqlGetStatus->fetch_row();
                        //     // Get Role Name.
                        //     $sqlGetRole = $conn->query("SELECT RoleName FROM Role WHERE ID = $BLKAPT[3]");
                        //     $role = $sqlGetRole->fetch_row();
                        //     // assign data into array.
                            
                        //         $Data["record$count"] = ["apartment" => "$BLKAPT[1]", "block" => "$BLKAPT[0]", "status" => "$status[0]", "role" => "$role[0]" ];
                           
                        //     $count++;

                        // }
                        $ResImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$Col[6]";

                        $payload = [
                            'iat' => time(),
                            // 'exp' => time() + (15*60*4),
                            'id' => $Col[0],
                            'userName' => $Col[2],
                            'email' => $Col[3],
                            'phoneNumber' => $Col[5],
                            'residentImage' => $ResImageUrl,
                            // "apartmentsAndBlocks" => $Data,
                        ];
                        // $this->Signature = $this->getRandomKey(32);

                        $secret = "secret123";
                        $signature = $this->Signature;
                        // Signature stored in Functions Class protected Signature = "secret123"
                        $signature = $this->SetSignature("secret1234");
                        // $sqlSaveNewKey = $conn->query("UPDATE Resident_User SET JWTKEY = '$secret' WHERE ID='$Col[0]'");
                        $jwt = JWT::encode($payload, $secret, 'HS256');
                        $decode = JWT::decode($jwt, new Key($secret, 'HS256'));
                          
                        $_SESSION["Token"] = $jwt;
                        $userId = $Col[0];
                        // Get User Apartment and block
                        $sqlGetBLKAPT = $conn->query("SELECT ApartmentID, BlockID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = $userId");
                    
                        // Data For Log Record    
                        
                        $Action = "User Login";
                        $date = date("Y-m-d h:i:sa");
                        $CurrentDate = date("Y-m-d H:i:s");
                        if($sqlGetBLKAPT->num_rows <= 0)
                        {
                            $apartment = NULL;
                            $block = NULL;
                            $sqlLog = $conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                        VALUES ('$userId', NULL, NULL, 5, '$Action', '$userId', 'Resident_User', '$Longitude', '$Latitude', '$date', '$CurrentDate')");
                            
                            // ============================================================= Saving Google Token ============================================================= 
                            // Check if user have the same google token.
                            $sqlCheckGoogleToken = $conn->query("SELECT ID, GoogleToken FROM Resident_Devices_Tokens WHERE DeviceID = '$DeviceID' AND ResidentID = '$userId'");
                            $count = 0;
                            if($sqlCheckGoogleToken->num_rows > 0)
                            {
                                $count = 0;
                                while($StoredGoogleToken = $sqlCheckGoogleToken->fetch_row())
                                {
                                    if($StoredGoogleToken[1] !== $GoogleToken)
                                    {
                                        // insert Google Registration token
                                        $sqlInsertFcmToken = $conn->query("Update Resident_Devices_Tokens SET GoogleToken = '$GoogleToken', MobileOS = '$OS', UpdatedAt = '$CurrentDate' WHERE ResidentID = '$userId' AND DeviceID = '$DeviceID'");
                                    }
                                    elseif($StoredGoogleToken[1] == $GoogleToken)
                                    {
                                        continue;
                                    }
                                    $count++;
                                }
                            }
                            
                            // Check if user logged in from other devices than the current one.
                            $sqlCheckUser = $conn->query("SELECT NumOfDevices FROM Resident_Devices_Tokens WHERE ResidentID = '$userId'");
                            
                            if($sqlCheckGoogleToken->num_rows <= 0)
                            {
                                if($sqlCheckUser->num_rows > 0)
                                {
                                    $NumOfDevices = $sqlCheckUser->fetch_row();
                                    $NewNumOfDevices = $NumOfDevices[0] + 1;
                                    $sqlInsertFcmToken = $conn->query("INSERT INTO Resident_Devices_Tokens (ResidentID, BlockID, GoogleToken, DeviceID, NumOfDevices, MobileOS, CreatedAt) VALUES ('$userId', NULL, '$GoogleToken', '$DeviceID', '$NewNumOfDevices', '$OS', '$CurrentDate')");
                                    $sqlUpdateNumOfDevices = $conn->query("UPDATE Resident_Devices_Tokens Set NumOfDevices = '$NewNumOfDevices' WHERE ResidentID = '$userId'");
                                    echo "First IF";
                                }
                                else
                                {
                                    // insert New Google Registration token
                                    $sqlInsertFcmToken = $conn->query("INSERT INTO Resident_Devices_Tokens (ResidentID, BlockID, GoogleToken, DeviceID, NumOfDevices, MobileOS, CreatedAt) VALUES ('$userId', NULL, '$GoogleToken', '$DeviceID', 1, '$OS', '$CurrentDate')");
                                }
                                
                            }
    
                            // Insert record in Notification Settings table NotifSettings.
                            $sqlCheckRecord = $conn->query("SELECT ID FROM NotifSettings WHERE UserID = $userId");
                            if($sqlCheckRecord->num_rows <= 0)
                            {
                                //Insert New record if user doesn't have records in this table.
                                $InsertNewRecord = $conn->query("INSERT INTO NotifSettings (UserID) VALUES ('$userId')");
                            }
                            // ============================================================= Saving Google Token ============================================================= 
                        }
                        elseif($sqlGetBLKAPT->num_rows > 0)
                        {
                            $BLKAPT = $sqlGetBLKAPT->fetch_row();
                            $apartment = $BLKAPT[0];
                            $block = $BLKAPT[1];
                        
                            $sqlLog = $conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                        VALUES ('$userId', '$apartment', '$block', 5, '$Action', '$userId', 'Resident_User', '$Longitude', '$Latitude', '$date', '$CurrentDate')");
                            
                            // ============================================================= Saving Google Token ============================================================= 
                        // Check if user have the same google token.
                        $sqlCheckGoogleToken = $conn->query("SELECT ID, GoogleToken FROM Resident_Devices_Tokens WHERE DeviceID = '$DeviceID' AND ResidentID = '$userId'");
                        $count = 0;
                        if($sqlCheckGoogleToken->num_rows > 0)
                        {
                            $count = 0;
                            while($StoredGoogleToken = $sqlCheckGoogleToken->fetch_row())
                            {
                                if($StoredGoogleToken[1] !== $GoogleToken)
                                {
                                    // insert Google Registration token
                                    $sqlInsertFcmToken = $conn->query("Update Resident_Devices_Tokens SET GoogleToken = '$GoogleToken', MobileOS = '$OS', UpdatedAt = '$CurrentDate' WHERE ResidentID = '$userId' and BlockID = '$block' AND DeviceID = '$DeviceID'");
                                }
                                elseif($StoredGoogleToken[1] == $GoogleToken)
                                {
                                    continue;
                                }
                                $count++;
                            }
                        }
                        
                        // Check if user logged in from other devices than the current one.
                        $sqlCheckUser = $conn->query("SELECT NumOfDevices FROM Resident_Devices_Tokens WHERE ResidentID = '$userId'");
                        
                        if($sqlCheckGoogleToken->num_rows <= 0)
                        {
                            if($sqlCheckUser->num_rows > 0)
                            {
                                $NumOfDevices = $sqlCheckUser->fetch_row();
                                $NewNumOfDevices = $NumOfDevices[0] + 1;
                                $sqlInsertFcmToken = $conn->query("INSERT INTO Resident_Devices_Tokens (ResidentID, BlockID, GoogleToken, DeviceID, NumOfDevices, MobileOS, CreatedAt) VALUES ('$userId', '$block', '$GoogleToken', '$DeviceID', '$NewNumOfDevices', '$OS', '$CurrentDate')");
                                $sqlUpdateNumOfDevices = $conn->query("UPDATE Resident_Devices_Tokens Set NumOfDevices = '$NewNumOfDevices' WHERE ResidentID = '$userId'");
                                echo "First IF";
                            }
                            else
                            {
                                // insert New Google Registration token
                                $sqlInsertFcmToken = $conn->query("INSERT INTO Resident_Devices_Tokens (ResidentID, BlockID, GoogleToken, DeviceID, NumOfDevices, MobileOS, CreatedAt) VALUES ('$userId', '$block', '$GoogleToken', '$DeviceID', 1, '$OS', '$CurrentDate')");    
                            }
                            
                        }

                        // Insert record in Notification Settings table NotifSettings.
                        $sqlCheckRecord = $conn->query("SELECT ID FROM NotifSettings WHERE UserID = $userId");
                        if($sqlCheckRecord->num_rows <= 0)
                        {
                            //Insert New record if user doesn't have records in this table.
                            $InsertNewRecord = $conn->query("INSERT INTO NotifSettings (UserID) VALUES ('$userId')");
                        }
                        // ============================================================= Saving Google Token ============================================================= 
                        }
                        $this->returnResponse(200, ["Data" => $decode, "Token" => $jwt]);
                        exit;
                    }
                }
                
            }// What happends if user did enter email but it's wrong or not in the data base which means user doesn't have account.
            else
            {
                $this->throwError(406, "Email not found");
                exit;
            }
        }
    }
    
    public function SMLogin()
    {
        include("./Config.php");

        $Google_OAuth_Client_ID_Web = "";
        $Google_OAuth_Client_Secret_Web = "";
        
        $client = new Google_Client([
            "client_id" => $Google_OAuth_Client_ID_Web
        ]);
        
        $id_token = $_POST["id_token"];
        
        $payload = $client->verifyIdToken($id_token);
        
        if($payload && $payload["aud"] == $Google_OAuth_Client_ID_Web)
        {
            $user_google_id = $payload["sub"];
            $myGoogleId = "fjkf,mbvm,cvxdjhk";
            $username = $payload["name"];
            $FName = ["given_name"];
        
            $password = password_hash( $myGoogleId, PASSWORD_BCRYPT);
            // $_SESSION["user"] = $user_google_id;
            // insert User to Resident User if email does not exist.
            $sqlCheckEmail = $conn->query("SELECT * FROM Resident_User WHERE Email = '$email'");
            if($sqlCheckEmail->num_rows > 0)
            {
                $Col = $sqlCheckEmail->fetch_row();
                // Check Status.
                if($Col[8] == '2')
                {
                    // Check Password. <><><> if User Used Google signin he will sign in <><><> if user didn't Use Google signin he will not sign in
                    if(password_verify($user_google_id, $Col[4]))
                    {
                        $payloadJWT = [
                            'iat' => time(),
                            'exp' => time() + (15*60),
                            'id' => $Col[0],
                            'name' => $Col[1],
                            'userName' => $Col[2],
                            'email' => $Col[3],
                            'phoneNumber' => $Col[6],
                        ];
                        $secret = "Waheed123";
                        $JWT = JWT::encode($payloadJWT, $secret, 'HS256');
                        print_r($payloadJWT);
                    }    
                }
                elseif($Col[8] == '1')
                {
                    $this->throwError(200, "This acount status is still Pinding.");
                }
                elseif($Col[8] == '3')
                {
                    $this->throwError(200, "This acount is Banned.");
                }
            }
            else
            {
                // Insert New Record With Google Data.
                $sqlInsertNewGoogleEmail = $conn->query("INSERT INTO Resident_User (FName, LName, UserName, Email, Image, Password) VALUES ('FName', 'LName', '$username', '$email', '$picture', '$password')");
                // Get User Stored Data.
                // Get Last inserted id.
                $UserStoredId = $conn->query("SELECT ID FROM Resident_User ORDER BY ID DESC LIMIT 1");
                $ResId = $UserStoredId->fetch_row();
                
                $sqlGetResData = $conn->query("SELECT * FROM Resident_User WHERE ID = '$ResId[0]'");
                if($sqlGetResData->num_rows > 0)
                {
                    $Col = $sqlGetResData->fetch_row();
                    $payloadJWT = [
                        'iat' => time(),
                        'exp' => time() + (15*60),
                        'id' => $Col[0],
                        'name' => $Col[1],
                        'userName' => $Col[2],
                        'email' => $Col[3],
                        'phoneNumber' => $Col[6],
                    ];
                    $secret = "Waheed123";
                    $JWT = JWT::encode($payloadJWT, $secret, 'HS256');
                    print_r($payloadJWT);
                }
                
            }
        
            echo "Login OK" . $user_google_id . " , " . $name . " , " . $email . " , " . $picture . " , ";
            print_r($payload);
        }
        else
        {
            echo "Login Failed";
        }
    }
}


?>
