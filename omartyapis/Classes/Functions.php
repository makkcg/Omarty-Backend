<?php

class Functions
{
        protected $request;
        protected $serviceName;
        protected $param;
        protected $Key;
        protected $Signature = "secret123";
    
        protected function SetSignature($sig)
        {
            $Signature = $sig;
            $this->Signature = $Signature;
            return $Signature;
            
        }
        public function validateRequest($request)
        {
            // For Raw data input.
            /* 
                if($_SERVER["CONTENT_TYPE"] !== "application/json")
                {
                    $this->throwError(101, "Content Type Not Valid");
                }
                elseif(($_SERVER["CONTENT_TYPE"] == "application/json"))
                {
                    $data = json_decode($request, true);
                    // print_r($data);
                }
            */
            if(!isset($_POST["SN"]) || $_POST["SN"] == "")
            {
                $this->throwError(404 ,"API name Required");
            }
            else
            {
                $this->serviceName = $_POST["SN"];
            }
        }

        public function throwError($status, $message)
        {
            http_response_code($status);
            $err = ["status"=>$status, "message"=>$message];
            echo json_encode($err);
            exit;
        }

        public function returnResponse($status, $message)
        {
            http_response_code($status);
            $res = ['status'=>$status, 'data'=>$message];
            print_r(json_encode($res));
        }

        public function getAuthHeader()
        {
            $headers = null;
            if(isset($_SERVER["Authorization"]))
            {
                $headers = trim($_SERVER["Autorization"]);
            }
            elseif(isset($_SERVER["HTTP_AUTHORIZATION"]))
            {
                $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
            }
            elseif(function_exists('apache_request_headers'))
            {
                $requestHeaders = apache_request_headers();
    
                $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
                if(isset($requestHeaders["Authorization"]))
                {
                    $headers = trim($requestHeaders["Authorization"]);
                }
            }
            return $headers;
        }
        
        public function getBearerToken()
        {
            $headers = $this->getAuthHeader();
            if(!empty($headers))
            {
                if(preg_match('/Bearer\s(\S+)/', $headers, $matchs))
                {
                    return $matchs[1];
                }
            }
            else
            {
                $this->throwError(403,["Error" => ["name" => "Access token not found"]]);
            }
        }
    
        public function getRandomKey($n)
        {
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $this->Key = '';
    
            for ($i = 0; $i < $n; $i++) 
            {
                $index = rand(0, strlen($characters) - 1);
                $this->Key .= $characters[$index];
            }
    
            return $this->Key;
        }
        
        // pass $Attach as the key which will be passed by FrontEnd.
        public function uploadFile($name, $Attach, array $extensions)
        {
            // if($_FILES[$Attach]["error"] === 4)
            $file = $_FILES[$Attach];
            $fileName = $_FILES[$Attach]["name"];
            $fileError = $_FILES[$Attach]["error"];
            $fileSize = $_FILES[$Attach]["size"];
            $fileTempName = $_FILES[$Attach]["tmp_name"];
            // Get file extension (type).
            $fileExt = explode('.', $fileName);
            $fileExtension = strtolower(end($fileExt));
            // Check Extension is allowed or not.
            if($_FILES[$Attach]["error"] == 4)
            {
                return false;
            }
            elseif($fileError == 0)
            {
                if(!in_array($fileExtension, $extensions))
                {
                    $this->throwError(406, "File extension not OK.");              
                }
                else
                {
                    // $this->returnResponse(200, "OK File Extension is  ". $fileExtension);
                   
                        // echo "Inside Error";
                        if($fileSize < 1024000)
                        {
                            // Ok
                            $NewName = base64_encode(uniqid($name, true)).".". $fileExtension;
                            $file += ["newName"=> $NewName];
                            // array_push($file, ["newName"=> $NewName]);
                            // $location = $path.$NewName;
                            // Don't move file to continue in the original function
                            // move_uploaded_file($fileTempName, $location);
                            $actualFileName = substr($file["name"], strpos($file["name"], '"') + 1);
                            return $file;
                        }
                        else
                        {
                            // file too big
                            $this->throwError(406, "File size is too big, You can upload files not larger than 1 MB.");
                        }
                    // If Ok move file to path.
                }
            }
            else
            {
                $this->throwError(204, "some error occured during uploading file, Please try again.");
            }
            // print_r($fileExtension);
        }
        
        // Pass $Attach as File. (This Function is the one Used to Upload Files in Omarty).
        public function uploadFile2($name, array $Attach, array $extensions)
        {
            // if($_FILES[$Attach]["error"] === 4)
            // $file = $_FILES[$Attach];
            $fileName = $Attach["name"];
            $fileError = $Attach["error"];
            $fileSize = $Attach["size"];
            $fileTempName = $Attach["tmp_name"];
            // Get file extension (type).
            $fileExt = explode('.', $fileName);
            $fileExtension = strtolower(end($fileExt));
            // Check Extension is allowed or not.
            if($Attach["error"] == 4)
            {
                return false;
            }
            elseif($fileError == 0)
            {
                if(!in_array($fileExtension, $extensions))
                {
                    $this->throwError(406, "File extension not OK.");              
                }
                else
                {
                    // $this->returnResponse(200, "OK File Extension is  ". $fileExtension);
                   
                        // echo "Inside Error";
                        if($fileSize < 1024000)
                        {
                            // Ok
                            $NewName = base64_encode(uniqid($name, true)).".". $fileExtension;
                            $Attach += ["newName"=> $NewName];
                            // array_push($file, ["newName"=> $NewName]);
                            // $location = $path.$NewName;
                            // Don't move file to continue in the original function
                            // move_uploaded_file($fileTempName, $location);
                            $actualFileName = substr($Attach["name"], strpos($Attach["name"], '"') + 1);
                            return $Attach;
                        }
                        else
                        {
                            // file too big
                            $this->throwError(406, "File size is too big, You can upload files not larger than 1 MB.");
                        }
                    // If Ok move file to path.
                }
            }
            else
            {
                $this->throwError(204, "some error occured during uploading file, Please try again.");
            }
            // print_r($fileExtension);
        }
        
        public function SendNotifi($Sender, $BLKID, $APTID, $Type, $RecordID, $Repeat = 0, $LoopAt = NULL)
        {
            include("../Config.php");
            date_default_timezone_set('Africa/Cairo');
            
            // Get Receivers.
            $sqlGetReceivers = $conn->query("SELECT ID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID', AND ApartmentID = '$APTID'");
            
            while($Receiver = $sqlGetReceivers->fetch_row())
            {
                // Insert new Record Of Notification Type(Meeting / Event / Payment / Fee / News / Offers / ...).
                $sqlInsertNotifi = $conn->query("INSERT INTO Notification (Type, RecordID, Sender, ApartmentID, BlockID, Receiver, LoopAt, Repetition, CreatedAt) 
                                                                    VALUES ('$Type', '$RecordID', '$Receiver[0]', '$APTID', '$BLKID', '$Sender', '$LoopAt', '$Repeat')");
            }
            
            // Once Added to DB SEND them to every user that should receive notification.
            $sqlGetNitifi = $conn->query("SELECT * FROM Notification WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
            while($Notifi = $sqlGetNitifi->fetchRow())
            {
                // Get Sender And / Receiver Data.
                $sqlGetSenderData = $conn->query("SELECT Name, Email, PhoneNum, Image FROM Resident_User WHERE ID = '$Sender'");
                if($sqlGetSenderData->num_rows > 0)
                {
                    $SenderData = $sqlGetSenderData->fetch_row();
                    $SenderName = $SenderData[0];
                    $SenderEmail = $SenderData[1];
                    $SenderPN = $SenderData[2];
                    $Senderimage = "https://kcgwebservices.net/omartyapis/Images/profilePictures/$SenderData[3]";
                }
                else
                {
                    $Senderimage = "https://kcgwebservices.net/omartyapis/Images/profilePictures/DefaultMale.png";
                }
                // Get Receivers Data.
                $sqlGetReceiverID = $conn->query("SELECT ID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID', AND ApartmentID = '$APTID'");
                $ReceiverDataArr = [];
                $count = 1;
                while($ReceiverID = $sqlGetReceiverID->fetch_row())
                {
                    // Get Resident Data By his ID.
                    $sqlGetReceiverData = $conn->query("SELECT Name, Email, PhoneNum, Image FROM Resident_User WHERE ID = '$ReceiverID[0]'");
                    if($sqlGetReceiverData->num_rows > 0)
                    {
                        $ReceiverData = $sqlGetReceiverData->fetch_row();
                        $ReceiverDataArr[$count] = 
                        [
                            "name" => $ReceiverData[0],
                            "email" => $ReceiverData[1],
                            "phoneNumber" => $ReceiverData[2],
                            "image" => "https://kcgwebservices.net/omartyapis/Images/profilePictures/$ReceiverData[3]"
                        ];
                    }
                }
                
                // Get Notification Record ID.
                $sqlGetNotifiID = $conn->query("SELECT ID FROM Notification ORDER BY ID LIMIT 1");
                $NotifiID = $sqlGetNotifiID->fetch_row();
                // Get Notification Original Record Data.
                if($Type == 'Meeting')
                {
                    $sqlGetNotifiOrigRecordData = $conn->query("SELECT * FROM $Type WHERE ID = '$NotifiID[0]'");
                    
                }
                
                
            }
        }
        
        public function GetNotifi($Sender, $BLKID, $APTID, $Type, $RecordID, $Repeat = 0, $LoopAt = NULL)
        {
            include("../Config.php");
            date_default_timezone_set('Africa/Cairo');
            
           
        }

        // Updating IDs Cause Conflict in foreign keys in other tables.
        public function UpdateID($StartID = 9, $Table = "Service")
        {
            include("../Config.php");
            $sqlGetTableCount = $conn->query("SELECT ID FROM $Table WHERE ID >$StartID - 1");
            $count = $startID - 1;
            while($TableCount = $sqlGetTableCount->fetch_row())
            {
                $sqlUpdateId = $conn->query("UPDATE $Table SET ID = $count WHERE ID > $count");
                $count++;
            }
            
        }
        
        public function FCMNotification($notifBody)
        {
            $url = "https://fcm.googleapis.com/fcm/send";
        
            // API key.
            $apiKey = "AAAAmXEz3_E:APA91bGBw4nb_tgZ_VyCQ8RsYOQX0L7db3Yx3RLMJHpWosoAHB5C9pQefZo7uLqvMoVTnPMAyl_QkG00xbvO3I4dl-KWWebbGNbdGIv-18qcsojEwZlSuh5TUyHxA-7BcGAgJyT6nXV0";
            
            $headers = 
            [
                "Authorization:key=" . $apiKey,
                "Content-Type:application/json"
            ];
        
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($notifBody));
        
            $result = curl_exec($ch);
        
            print_r($result);
        
            curl_close($ch);
        }
        
        public function FCM($UserID, $Type, $TypeRecordID, $registration_ids = [])
        {
            
            $Meeting = 0;
            $Event = 0;
            $News = 0;
            $Offer = 0;
            $Chat = 0;
            $Financial = 0;
            /*
             * 
                $NotifTitle = "UserName created a new $Type";
            
                $NotifBody = "$Type Title + Body"; 
             *
            */
            
            // Check Allowed Notification for this Apartment.
            $sqlCheckAllowedNotif = $conn->query("SELECT * FROM NotifSettings WHERE UserID = '$UserID' AND ApartmentID = '$APTID' AND BlockID = '$BLKID'");
            if($sqlCheckAllowedNotif->num_rows > 0)
            {
                $Allowed = $sqlCheckAllowedNotif->fetch_row();
                if($Allowed[2] == "0" && $Type = "Meeting")
                {
                    // Get Data From Record $TypeRecordID
                    $sqlGetData = $conn->query("SELECT Tittle, Body, Attachment, MeetingLocation, Approval, NumOfAttendees, Date, UserID FROM Meeting WHERE ID = '$TypeRecordID'");
                    if($sqlGetData->num_rows > 0)
                    {
                        $MeetData = $sqlGetData->fetch_row();
                        if($MeetData[4] == '1')
                        {
                            // Get User ( Name ).
                                // Get User Name.
                                $sqlGetUserName = $conn->query("SELECT Name FROM Resident_User WHERE ID = $MeetData[7]");
                                if($sqlGetUserName->num_rows > 0)
                                {
                                    $ResData = $sqlGetUserName->fetch_row();
                                    $ResName = $ResData[0];
                                }
                                elseif($sqlGetUserName->num_rows <= 0)
                                {
                                    $ResName = $MeetData[7];
                                }
                            // Continue to send notification.
                            // Send Meeting Notification
                            $MeetingImageUrl = "https://kcgwebservices.net/omartyapis/Images/meetingImages/$MeetData[2]";
                            $MsgBody = [
                                "meetingBody" => $MeetData[1],
                                "meetingImage" => $MeetingImageUrl,
                                "meetingLocation" => $MeetData[3],
                                "meetingNumOfAttendence" => $MeetData[5],
                                "meetingDate" => $MeetData[6],
                                "residentName" => $ResName, /* Creator of the meeting. */
                            ];
                        
                            $NotifData = 
                            [
                                "title" => $MeetData[0],
                                "body" => $MsgBody,
                                "image" => $MeetingImageUrl,
                                // "click_action" => "activities.notifhandler"
                            ];
                        
                            $dataPayload = 
                            [
                                "to" => "User",
                                "title" => $MeetData[0],
                                "message" => $MsgBody,
                                // "Other" => "Other Data"
                            ];
                        
                            $notifBody = 
                            [
                                "notification" => $NotifData,
                                // Optional
                                "data" => $dataPayload,
                                // Optional
                                "time_to_live" => 3600 * 24 * 7, /* Top Is 4 Weeks */
                                // To specific User.
                                // "to" => "Token OR Reg_id"
                                // To All Users.
                                "to" => "/topics/my-topic"
                                // To set Of Users.
                                // "registration_ids" => "Array Of registration_ids OR Token Json"
                                // "registration_ids" => $registration_ids
                            ];
                            
                            $this->FCMNotification($notifBody);
                        }
                    }
                    elseif($sqlGetData->num_rows > 0)
                    {
                        $this->throwError(200, "Couldn't get Meeting Notification");
                    }
                }
                if($Allowed[3] == "0" && $Type = "Event")
                {
                    // Get Data From Record $TypeRecordID
                    $sqlGetData = $conn->query("SELECT Tittle, Body, Image, EventLocation, NumOfAttendees, Date, UserID FROM Event WHERE ID = '$TypeRecordID'");
                    if($sqlGetData->num_rows > 0)
                    {
                        $EventData = $sqlGetData->fetch_row();
                        if($EventData[4] == '1')
                        {
                            // Get Resident Name.
                            $sqlGetUserName = $conn->query("SELECT Name FROM Resident_User WHERE ID = $EventData[6]");
                            if($sqlGetUserName->num_rows > 0)
                                {
                                    $ResData = $sqlGetUserName->fetch_row();
                                    $ResName = $ResData[0];
                                }
                                elseif($sqlGetUserName->num_rows <= 0)
                                {
                                    $ResName = $EventData[6];
                                }
                            // Continue to send notification.
                            // Send Event Notification
                            $EventImageUrl = "https://kcgwebservices.net/omartyapis/Images/eventImages/$EventData[2]";
                            $MsgBody = [
                                "eventBody" => $EventData[1],
                                "eventImage" => $EventImageUrl,
                                "eventLocation" => $EventData[3],
                                "eventNumOfAttendence" => $EventData[4],
                                "eventDate" => $EventData[5],
                                "residentName" => $ResName, /* Creator of event. */
                            ];
                        
                            $NotifData = 
                            [
                                "title" => $EventData[0],
                                "body" => $MsgBody,
                                "image" => $EventImageUrl,
                                // "click_action" => "activities.notifhandler"
                            ];
                        
                            $dataPayload = 
                            [
                                "to" => "User",
                                "title" => $EventData[0],
                                "message" => $MsgBody,
                                // "Other" => "Other Data"
                            ];
                        
                            $notifBody = 
                            [
                                "notification" => $NotifData,
                                // Optional
                                "data" => $dataPayload,
                                // Optional
                                "time_to_live" => 3600 * 24 * 7, /* Top Is 4 Weeks */
                                // To specific User.
                                // "to" => "Token OR Reg_id"
                                // To All Users.
                                "to" => "/topics/my-topic"
                                // To set Of Users.
                                // "registration_ids" => "Array Of registration_ids OR Token Json"
                            ];
                            
                            $this->FCMNotification($notifBody);
                        }
                    }
                    elseif($sqlGetData->num_rows > 0)
                    {
                        $this->throwError(200, "Couldn't get Event Notification");
                    }
                }
                if($Allowed[4] == "0" && $Type = "News")
                {
                    // Get Data From Record $TypeRecordID
                    $sqlGetData = $conn->query("SELECT Tittle, LetterOfNews, Image, Date, ResidentID FROM News WHERE ID = '$TypeRecordID'");
                    if($sqlGetData->num_rows > 0)
                    {
                        $NewsData = $sqlGetData->fetch_row();
                        if($NewsData[4] == '1')
                        {
                            // Get Resident Name.
                            $sqlGetUserName = $conn->query("SELECT Name FROM Resident_User WHERE ID = $NewsData[4]");
                            if($sqlGetUserName->num_rows > 0)
                                {
                                    $ResData = $sqlGetUserName->fetch_row();
                                    $ResName = $ResData[0];
                                }
                                elseif($sqlGetUserName->num_rows <= 0)
                                {
                                    $ResName = $NewsData[4];
                                }
                            // Continue to send notification.
                            // Send News Notification
                            $NewsImageUrl = "https://kcgwebservices.net/omartyapis/Images/newsImages/$NewsData[2]";
                            $MsgBody = [
                                "newsBody" => $NewsData[1],
                                "newsImage" => $NewsImageUrl,
                                "newsDate" => $NewsData[3],
                                "residentName" => $ResName, /* creator of the news. */
                            ];
                        
                            $NotifData = 
                            [
                                "title" => $NewsData[0],
                                "body" => $MsgBody,
                                "image" => $NewsImageUrl,
                                // "click_action" => "activities.notifhandler"
                            ];
                        
                            $dataPayload = 
                            [
                                "to" => "User",
                                "title" => $NewsData[0],
                                "message" => $MsgBody,
                                // "Other" => "Other Data"
                            ];
                        
                            $notifBody = 
                            [
                                "notification" => $NotifData,
                                // Optional
                                "data" => $dataPayload,
                                // Optional
                                "time_to_live" => 3600 * 24 * 7, /* Top Is 4 Weeks */
                                // To specific User.
                                // "to" => "Token OR Reg_id"
                                // To All Users.
                                "to" => "/topics/my-topic"
                                // To set Of Users.
                                // "registration_ids" => "Array Of registration_ids OR Token Json"
                            ];
                            
                            $this->FCMNotification($notifBody);
                        }
                    }
                    elseif($sqlGetData->num_rows > 0)
                    {
                        $this->throwError(200, "Couldn't get News Notification");
                    }
                }
                if($Allowed[5] == "0" && $Type = "Offer")
                {
                    // Send Offers Notification
                    // Get Data From Record $TypeRecordID
                    $sqlGetData = $conn->query("SELECT Tittle, Body, Owner, Image, StartDate, EndDate FROM AdsAndOffers WHERE ID = '$TypeRecordID'");
                    if($sqlGetData->num_rows > 0)
                    {
                        $OfferData = $sqlGetData->fetch_row();
                        if($OfferData[4] == '1')
                        {
                            // Continue to send notification.
                            // Send News Notification
                            $OfferImageUrl = "https://kcgwebservices.net/omartyapis/Images/AdsAndOffers/$OfferData[3]";
                            $MsgBody = [
                                "OfferBody" => $OfferData[1],
                                "OfferOwner" => $OfferData[2],
                                "OfferImage" => $OfferImageUrl,
                                "OfferStartDate" => $OfferData[4],
                                "OfferEndDate" => $OfferData[5]
                            ];
                        
                            $NotifData = 
                            [
                                "title" => $OfferData[0],
                                "body" => $MsgBody,
                                "image" => $OfferImageUrl,
                                // "click_action" => "activities.notifhandler"
                            ];
                        
                            $dataPayload = 
                            [
                                "to" => "User",
                                "title" => $OfferData[0],
                                "message" => $MsgBody,
                                // "Other" => "Other Data"
                            ];
                        
                            $notifBody = 
                            [
                                "notification" => $NotifData,
                                // Optional
                                "data" => $dataPayload,
                                // Optional
                                "time_to_live" => 3600 * 24 * 7, /* Top Is 4 Weeks */
                                // To specific User.
                                // "to" => "Token OR Reg_id"
                                // To All Users.
                                "to" => "/topics/my-topic"
                                // To set Of Users.
                                // "registration_ids" => "Array Of registration_ids OR Token Json"
                            ];
                            
                            $this->FCMNotification($notifBody);
                        }
                    }
                    elseif($sqlGetData->num_rows > 0)
                    {
                        $this->throwError(200, "Couldn't get Offers Notification");
                    }
                }
                if($Allowed[6] == "0" && $Type = "Chat")
                {
                    // Send Chat Notification
                    // Get Data From Record $TypeRecordID
                    $sqlGetData = $conn->query("SELECT Message, Attach, SenderID, ApartmentID, BlockID, CreatedAT, SenderID FROM Message WHERE ID = '$TypeRecordID'");
                    if($sqlGetData->num_rows > 0)
                    {
                        $ChatData = $sqlGetData->fetch_row();
                        if($ChatData[4] == '1')
                        {
                            // Get Resident Name.
                            $sqlGetUserName = $conn->query("SELECT Name FROM Resident_User WHERE ID = $ChatData[6]");
                            if($sqlGetUserName->num_rows > 0)
                                {
                                    $ResData = $sqlGetUserName->fetch_row();
                                    $ResName = $ResData[0];
                                }
                                elseif($sqlGetUserName->num_rows <= 0)
                                {
                                    $ResName = $ChatData[6];
                                }
                            // Get Apartment Name.
                            $sqlGetAptName = $conn->query("SELECT ApartmentName FROM Apartment WHERE ID = $ChatData[3]");
                            if($sqlGetAptName->num_rows > 0)
                                {
                                    $AptData = $sqlGetAptName->fetch_row();
                                    $AptName = $AptData[0];
                                }
                                elseif($sqlGetAptName->num_rows <= 0)
                                {
                                    $AptName = $ChatData[3];
                                }
                            // Get Block Name Name.
                            $sqlGetBlkName = $conn->query("SELECT BlockName FROM Block WHERE ID = $ChatData[4]");
                            if($sqlGetBlkName->num_rows > 0)
                                {
                                    $BlkData = $sqlGetBlkName->fetch_row();
                                    $BlkName = $BlkData[0];
                                }
                                elseif($sqlGetBlkName->num_rows <= 0)
                                {
                                    $BlkName = $ChatData[4];
                                }
                            // Continue to send notification.
                            // Send News Notification
                            $ChatImageUrl = "https://kcgwebservices.net/omartyapis/Images/ChatImages/$ChatData[1]";
                            $MsgBody = [
                                "chatBody" => $ChatData[0],
                                "chatSenderName" => $ResName,
                                "chatImage" => $ChatImageUrl,
                                "senderApartmentData" => $AptName,
                                "senderBlockData" => $BlkName,
                                "dateSent" => $ChatData[5],
                            ];
                        
                            $NotifData = 
                            [
                                "title" => $ChatData[0],
                                "body" => $MsgBody,
                                "image" => $ChatImageUrl,
                                // "click_action" => "activities.notifhandler"
                            ];
                        
                            $dataPayload = 
                            [
                                "to" => "User",
                                "title" => $ChatData[0],
                                "message" => $MsgBody,
                                // "Other" => "Other Data"
                            ];
                        
                            $notifBody = 
                            [
                                "notification" => $NotifData,
                                // Optional
                                "data" => $dataPayload,
                                // Optional
                                "time_to_live" => 3600 * 24 * 7, /* Top Is 4 Weeks */
                                // To specific User.
                                // "to" => "Token OR Reg_id"
                                // To All Users.
                                "to" => "/topics/my-topic"
                                // To set Of Users.
                                // "registration_ids" => "Array Of registration_ids OR Token Json"
                            ];
                            
                            $this->FCMNotification($notifBody);
                        }
                    }
                    elseif($sqlGetData->num_rows > 0)
                    {
                        $this->throwError(200, "Couldn't get Chat Notification");
                    }
                }
                if($Allowed[7] == "0" && $Type = "Financial")
                {

                    // Get Data From Record $TypeRecordID
                    $sqlGetData = $conn->query("SELECT Amount, DueDate, BlockID, ApartmentID, FeeStatment FROM Fee WHERE ID = '$TypeRecordID'");
                    if($sqlGetData->num_rows > 0)
                    {
                        $FeeData = $sqlGetData->fetch_row();
                        if($FeeData[4] == '1')
                        {
                            // Continue to send notification.
                            // Send Financial Notification
                            $MsgBody = [
                                "feeAmount" => $FeeData[0],
                                "feeDueDate" => $FeeData[1],
                                "feeOnBlockData" => $FeeData[2],
                                "feeOnApartmentData" => $FeeData[3],
                                "feeStatment" => $FeeData[4]
                            ];
                        
                            $NotifData = 
                            [
                                "title" => "New Assigned Fee",
                                "body" => $MsgBody,
                                // "image" => $FeeImageUrl,
                                // "click_action" => "activities.notifhandler"
                            ];
                        
                            $dataPayload = 
                            [
                                "to" => "User",
                                "title" => "New Assigned Fee",
                                "message" => $MsgBody,
                                // "Other" => "Other Data"
                            ];
                        
                            $notifBody = 
                            [
                                "notification" => $NotifData,
                                // Optional
                                "data" => $dataPayload,
                                // Optional
                                "time_to_live" => 3600 * 24 * 7, /* Top Is 4 Weeks */
                                // To specific User.
                                // "to" => "Token OR Reg_id"
                                // To All Users.
                                "to" => "/topics/my-topic"
                                // To set Of Users.
                                // "registration_ids" => "Array Of registration_ids OR Token Json"
                            ];
                            
                            $this->FCMNotification($notifBody);
                        }
                }
                elseif($Allowed[7] == "1" && $Type = "Financial")
                {
                    // Don't Send Financial Notification
                }
                // Get Notification Data ( Title / Message / Date ).
                // Send Notification To User When Apartment is Opened.
            }
            elseif($sqlCheckAllowedNotif->num_rows <= 0)
            {
                $this->throwError(200, "Couldn't send the notification");
            }
        }
        
}

        public function chat()
        {
        date_default_timezone_set('Africa/Cairo');
        include("../Config.php");
        
        // Setting Paging.
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
        
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
            $UserID = $decode->id;
            $Longitude = $_POST["longitude"];
            if(empty($Longitude)){ $Longitude = 0; }
            $Latitude = $_POST["latitude"];
            if(empty($Latitude)){ $Latitude = 0; }
            $date = date("Y-m-d h:i:sa");
            $CurrentDate = date("Y-m-d H:i:s");
            $msg = $_POST["message"];
            $extensions = ["jpg", "jpeg", "png", "pdf"];
            $Attach = $_POST["attach"];
            if(!empty($Attach))
            {
                $attachments = $this->uploadFile($userID, $Attach, $extensions);
            }
            $imageUrl = "https://kcgwebservices.net/omartyapis/Images/ChatImages/" . $attachments["newName"];
            
            if(!empty($attachments)) { $location = "../Images/ChatImages/". $attachments["newName"]; }
            


                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlCheckBlock = $conn->query("SELECT StatusID, ID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check User in block.
                        $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status
                            if($blockData[0] == "2")
                            {
                                // Check apartment Existence.
                                // $sqlCheckAPT = $conn->query("SELECT ID FROM Apartment WHERE ID = '$APTID'");
                                $sqlCheckAPT = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
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
                                            // Upload Message to DB.
                                                // if User didn't enter image or file continue and upload file value = NULL.
                                                if(!empty($image)) { $newImage = $image["newName"]; }
                                                else { $newImage = NULL; }
                                                // Get Mesage Then post it in block's chat group.
                                                // Insert Message Data into database (MessageContent / Image OR File / SenderID / Receiver / BlockID / ApartmentID / DateTime).
                                                $sqlInsertMessage = $conn->query("INSERT INTO Message (Message, Attach, SenderID, BlockID, ApartmentID, CreatedAt)
                                                                        VALUES ('$Message', '$newImage', '$userID', '$BLKID', '$APTID', '$CurrentDate')");
                                                if($sqlInsertMessage)
                                                {
                                                    //  Send Message Via Ratchet WebSocket.
                                                    /*  Send Message Via Ratchet WebSocket.
                                                     *  Send Message Via Ratchet WebSocket.
                                                     *  Send Message Via Ratchet WebSocket.
                                                    */
                                                    if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                                                    // Insert Logs.
                                                        // Get MessageID Where UserID
                                                        $sqlGetId = $conn->query("SELECT ID FROM Message WHERE SenderID = '$UserID' ORDER BY ID DESC LIMIT 1");
                                                        $newId = $sqlGetId->fetch_row();
                                                        $Action = "Send Message To Chat Room in BlockID : $BLKID";
                                                        $sqlInsertLog = $conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                                VALUES ('$UserID', '$APTID', '$BLKID', 23, '$Action', '$newId[0]', 'Message', '$Longitude', '$Latitude', '$date', '$CurrentDate')");
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
}
?>