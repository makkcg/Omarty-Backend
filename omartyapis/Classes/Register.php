<?php

    header('Content-Type: text/html; charset=utf-8');
    header("Access-Control-Allow-Origin: *");
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    header("content-type: Aplication/json");
   
    // assigning users data into variable after filtering and hashing password.

class Register extends Functions
{
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
                            $sqlInsert = $conn->query("INSERT INTO Resident_User (Name, UserName, Email, Password, PhoneNum, StatusID, CreatedAt) VALUES ('$name', '$name', '$email', '$password', '$pnum', 2, '$CurrentDate');");
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
                                if($conn->error)
                                {
                                    echo $conn->error;
                                }
                                    $this->returnResponse(200, "A new record added");
                                    exit;
                                
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
