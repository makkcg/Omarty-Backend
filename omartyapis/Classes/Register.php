<?php

    header('Content-Type: text/html; charset=utf-8');
    header("Access-Control-Allow-Origin: *");
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header("content-type: Aplication/json");
   
    
    include("../../vendor/autoload.php");

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    
    // assigning users data into variable after filtering and hashing password.

class Register extends Functions
{
    private $RootUrl = "https://plateform.omarty.net/";
    
    public function register()
    {
        include("../../Config.php");
        date_default_timezone_set('Africa/Cairo');

        $pnum = filter_var($_POST["pnum"], FILTER_SANITIZE_NUMBER_INT);
        $Email = $_POST["email"];
        $email = strtolower($Email);
        $name = $_POST["name"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $password = password_hash($_POST["password"], PASSWORD_BCRYPT);
        $confirmpassword = $_POST["confirmPassword"];
        
        // if user left any cradential empty echo message.
        if( empty($pnum) || empty($email) || empty($password) || empty($confirmpassword) )
        {
            $this->throwError(400, "Please enter your data (pnum / email / password / confirmPassword).");
        }
        // if user entered his whole data.
        elseif( !empty($pnum) || !empty($email) || !empty($password) || !empty($confirmpassword) )
        {
            if( !password_verify($confirmpassword, $password) )
            {   
                $this->throwError(401, "password dosn't match confirm password.");
            }
            elseif(password_verify($confirmpassword, $password))
            {
                
                $sqlSelectE= $conn->query("SELECT Email FROM Resident_User where Email = '$email'");
                $sqlSelectP= $conn->query("SELECT Email FROM Resident_User where PhoneNum = '$pnum'");

                // if user entered a new data and non repeated email.
                if(filter_var($email, FILTER_VALIDATE_EMAIL) === false)
                {
                    $this->throwError(403, "$email is not a valid email address");
                    exit;
                }
                elseif(!filter_var($email, FILTER_VALIDATE_EMAIL) === false)
                {
                    
                    if($sqlSelectE->num_rows > 0)
                    {
                        $this->throwError(403, "email already exist.");
                        exit;
                    }

                    // strval($pnum);
                    // if(strlen($pnum) <= 10)
                    // {
                    //     $this->throwError(403, "$pnum is not a valid Phone Number");
                    //     exit;
                    // }
                    // elseif(strlen($pnum) > 11)
                    // {
                    //     $this->throwError(403, "$pnum is not a valid Phone Number");
                    //     exit;
                    // }
                    elseif(strlen($pnum) > 10)
                    {
                        if($sqlSelectP->num_rows > 0)
                        {
                            $this->throwError(403, "Phone number already exist.");
                            exit;
                        }
                        else
                        {
                            $date = date("Y-m-d h:i:sa");
                            $CurrentDate = date("Y-m-d H:i:s");
                            $sqlInsert = $conn->query("INSERT INTO Resident_User (Name, UserName, Email, Password, PhoneNum, Image, StatusID, CreatedAt) VALUES ('$name', '$name', '$email', '$password', '$pnum', 'DefaultMale.png', 2, '$CurrentDate');");
                            // if the DB insertion executed correct.
                            
                            if($sqlInsert === true)
                            {
                                // Enter data to logs table.
                                $RegID = $conn->query("SELECT ID FROM Resident_User ORDER BY ID DESC LIMIT 1");
                                $newId = $RegID->fetch_row();
                                $userId = $newId[0];
                                    
                                    $Action = "New User Registration";
                                    $sqlLog = $conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                    VALUES ('$userId', NULL, NULL, 1, '$Action', '$userId', 'Resident_User', '$Longitude', '$Latitude', '$date', '$CurrentDate')");
                                                                    
                                $sqlInsertNotifSetting = $conn->query("INSERT INTO NotifSettings (UserID, HideMeeting, HideEvent, HideNews, HideOffers, HideChat, HideFinancial, CreatedAt) VALUES ('$userId', 0, 0, 0, 0, 0, 0,'$CurrentDate')");
                                
                                
                                // Get User Data to return it to front end.    
                                 $sqlGetUser = $conn->query("SELECT * FROM Resident_User WHERE ID = '$userId'");
                                 if($conn->error)
                                 {
                                     echo $conn->error;
                                 }
                                if($sqlGetUser->num_rows > 0)
                                {
                                    
                                    $Col = $sqlGetUser->fetch_row();
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
                                    
                                    $secret = "secret123";
                                    $signature = $this->Signature;
                                    // Signature stored in Functions Class protected Signature = "secret123"
                                    $signature = $this->SetSignature("secret1234");
                                    // $sqlSaveNewKey = $conn->query("UPDATE Resident_User SET JWTKEY = '$secret' WHERE ID='$Col[0]'");
                                    $jwt = JWT::encode($payload, $secret, 'HS256');
                                    $decode = JWT::decode($jwt, new Key($secret, 'HS256'));
                                    
                                    $this->returnResponse(200, ["Data" => $decode, "Token" => $jwt]);
                                    exit;
                                }
                                
                            }
                            
                            else
                            {
                            // if the DB insertion executed wrong.
                                $this->throwError(205, "Database Error ". $conn->error);
                            }    
                        }
                    }
                }
            } 
        }
    }
}
?>
