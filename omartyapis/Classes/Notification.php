<?php


class Notification
{
    private $RootUrl = "https://plateform.omarty.net/";
    public function FCMNotification($notifBody)
    {
            $url = "https://fcm.googleapis.com/fcm/send";

            // API key.
            $apiKey = "AAAAMl-gbTg:APA91bGT9gTFLE1lTLMDn5W_4NbRWA8QHQ_S3z9yz6E7krXpaoHt6EOLWVFz6FQe_jP6qlhDhy95HxUFOLRErlyBpho2d3YbMavN9pLkSnca93VYuxgU8h0cJEmQDfQrI5rFJHHeMdXR";

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

            // print_r($result);
            return $result;

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
                        $MeetingImageUrl = $this->RootUrl . "omartyapis/Images/meetingImages/$MeetData[2]";
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
                        $EventImageUrl = $this->RootUrl . "omartyapis/Images/eventImages/$EventData[2]";
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
                            $NewsImageUrl = $this->RootUrl . "omartyapis/Images/newsImages/$NewsData[2]";
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
                            $OfferImageUrl = $this->RootUrl . "omartyapis/Images/AdsAndOffers/$OfferData[3]";
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
                            $ChatImageUrl = $this->RootUrl . "omartyapis/Images/ChatImages/$ChatData[1]";
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

    public function MeetNoti(array $UserID, $MeetingID)
    {
        include("../Config.php");
        $count = count($UserID) - 1;
        for($i = 0; $i <= $count; $i++)
        {
            // Check for multible Registration tokens.
            $sqlCheckGoogleToken = $conn->query("SELECT GoogleToken FROM Resident_Devices_Tokens WHERE ResidentID = '$UserID[$i]'");
            while($GoogleToken = $sqlCheckGoogleToken->fetch_row())
            {
                // Check Allowed Notification for this Apartment.
                $sqlCheckAllowedNotif = $conn->query("SELECT HideMeeting FROM NotifSettings WHERE UserID = '$UserID[$i]'");
                if($sqlCheckAllowedNotif->num_rows > 0)
                {
                         $Allowed = $sqlCheckAllowedNotif->fetch_row();
                        if($Allowed[0] == "0")
                        {
                            // Get Data From Record $TypeRecordID in Meeting table.
                            $sqlGetData = $conn->query("SELECT Tittle, Body, Attachment, MeetingLocation, Approval, NumOfAttendees, Date, UserID FROM Meeting WHERE ID = '$MeetingID'");
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
                                    $MeetingImageUrl = $this->RootUrl . "omartyapis/Images/meetingImages/$MeetData[2]";
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
                                        // "to" => "/topics/my-topic",
                                        // To set Of Users.
                                        // "registration_ids" => "Array Of registration_ids OR Token Json"
                                        "registration_ids" => $GoogleToken
                                    ];
                                    
                                    $result = $this->FCMNotification($notifBody);
                                    return $result;
                                    // echo "Notification OK";
                                }
                            }
                            elseif($sqlGetData->num_rows > 0)
                            {
                                $this->throwError(200, "Couldn't get Meeting Notification");
                            }
                        }
                    }
            }
        }
    }

    public function EventNoti(array $UserID, $EventID)
    {
        $count = count($UserID);
        for($i = 0; $i <= $count; $i++)
        {
            // Check for multible Registration tokens.
            $sqlCheckGoogleToken = $conn->query("SELECT GoogleToken FROM Resident_Devices_Tokens WHERE ResidentID = '$UserID[$i]'");
            while($GoogleToken = $sqlCheckGoogleToken->fetch_row())
            {
                // Check Allowed Notification for this Apartment.
                $sqlCheckAllowedNotif = $conn->query("SELECT HideEvent FROM NotifSettings WHERE UserID = '$UserID[$i]'");
                if($sqlCheckAllowedNotif->num_rows > 0)
                {
                     $Allowed = $sqlCheckAllowedNotif->fetch_row();
                    if($Allowed[0] == "0" && $Type = "Event")
                    {
                        // Get Data From Record $TypeRecordID
                        $sqlGetData = $conn->query("SELECT Tittle, Body, Image, EventLocation, NumOfAttendees, Date, UserID FROM Event WHERE ID = '$EventID'");
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
                                $EventImageUrl = $this->RootUrl . "omartyapis/Images/eventImages/$EventData[2]";
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
                                    // "registration_ids" => $GoogleToken
                                ];
                                    
                                $result = $this->FCMNotification($notifBody);
                                print_r($result);
                                return $result;
                            }
                        }
                        elseif($sqlGetData->num_rows > 0)
                        {
                            $this->throwError(200, "Couldn't get Event Notification");
                        }
                    }
                }
            }
        }
    }
    
    public function NewsNoti($UserID, $NewsID)
    {
        $count = count($UserID);
        for($i = 0; $i <= $count; $i++)
        {
            // Check for multible Registration tokens.
            $sqlCheckGoogleToken = $conn->query("SELECT GoogleToken FROM Resident_Devices_Tokens WHERE ResidentID = '$UserID[$i]'");
            while($GoogleToken = $sqlCheckGoogleToken->fetch_row())
            {
                // Check Allowed Notification for this Apartment.
                $sqlCheckAllowedNotif = $conn->query("SELECT HideNews FROM NotifSettings WHERE UserID = '$UserID'");
                if($sqlCheckAllowedNotif->num_rows > 0)
                {
                    $Allowed = $sqlCheckAllowedNotif->fetch_row();
                     if($Allowed[0] == "0")
                        {
                            // Get Data From Record $TypeRecordID
                            $sqlGetData = $conn->query("SELECT Tittle, LetterOfNews, Image, Date, ResidentID FROM News WHERE ID = '$NewsID'");
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
                                    $NewsImageUrl = $this->RootUrl . "omartyapis/Images/newsImages/$NewsData[2]";
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
                                        // "to" => "/topics/my-topic"
                                        // To set Of Users.
                                        "registration_ids" => $GoogleToken
                                    ];
                                    
                                    $this->FCMNotification($notifBody);
                                }
                            }
                            elseif($sqlGetData->num_rows > 0)
                            {
                                $this->throwError(200, "Couldn't get News Notification");
                            }
                        }
                }
            }
        }
    }
    
    public function OfferNoti($UserID, $TypeRecordID, $registration_ids = [])
    {
        $count = count($UserID);
        for($i = 1; $i <= $count; $i++)
        {
            // Check for multible Registration tokens.
            $sqlCheckGoogleToken = $conn->query("SELECT GoogleToken FROM Resident_Devices_Tokens WHERE ResidentID = '$UserID[$i]'");
            while($GoogleToken = $sqlCheckGoogleToken->fetch_row())
            {
                // Check Allowed Notification for this Apartment.
                $sqlCheckAllowedNotif = $conn->query("SELECT HideNews FROM NotifSettings WHERE UserID = '$UserID' AND ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                if($sqlCheckAllowedNotif->num_rows > 0)
                {
                    $Allowed = $sqlCheckAllowedNotif->fetch_row();
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
                                $OfferImageUrl = $this->RootUrl . "omartyapis/Images/AdsAndOffers/$OfferData[3]";
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
                                    // "to" => "/topics/my-topic",
                                    // To set Of Users.
                                    "registration_ids" => $GoogleToken   // "Array Of registration_ids OR Token Json"
                                ];
                                    
                                $this->FCMNotification($notifBody);
                                print_r($notifBody);
                            }
                        }
                        elseif($sqlGetData->num_rows > 0)
                        {
                            $this->throwError(200, "Couldn't get Offers Notification");
                        }
                    }
                }
            }
        }
    }
    
    // public function OfferNoti($UserID, $TypeRecordID, $registration_ids = [])
    // {
    //     // Check Allowed Notification for this Apartment.
    //     $sqlCheckAllowedNotif = $conn->query("SELECT HideNews FROM NotifSettings WHERE UserID = '$UserID' AND ApartmentID = '$APTID' AND BlockID = '$BLKID'");
    //     if($sqlCheckAllowedNotif->num_rows > 0)
    //     {
    //         $Allowed = $sqlCheckAllowedNotif->fetch_row();
    //         if($Allowed[7] == "0" && $Type = "Financial")
    //             {

    //                 // Get Data From Record $TypeRecordID
    //                 $sqlGetData = $conn->query("SELECT Amount, DueDate, BlockID, ApartmentID, FeeStatment FROM Fee WHERE ID = '$TypeRecordID'");
    //                 if($sqlGetData->num_rows > 0)
    //                 {
    //                     $FeeData = $sqlGetData->fetch_row();
    //                     if($FeeData[4] == '1')
    //                     {
    //                         // Continue to send notification.
    //                         // Send Financial Notification
    //                         $MsgBody = [
    //                             "feeAmount" => $FeeData[0],
    //                             "feeDueDate" => $FeeData[1],
    //                             "feeOnBlockData" => $FeeData[2],
    //                             "feeOnApartmentData" => $FeeData[3],
    //                             "feeStatment" => $FeeData[4]
    //                         ];
                        
    //                         $NotifData = 
    //                         [
    //                             "title" => "New Assigned Fee",
    //                             "body" => $MsgBody,
    //                             // "image" => $FeeImageUrl,
    //                             // "click_action" => "activities.notifhandler"
    //                         ];
                        
    //                         $dataPayload = 
    //                         [
    //                             "to" => "User",
    //                             "title" => "New Assigned Fee",
    //                             "message" => $MsgBody,
    //                             // "Other" => "Other Data"
    //                         ];
                        
    //                         $notifBody = 
    //                         [
    //                             "notification" => $NotifData,
    //                             // Optional
    //                             "data" => $dataPayload,
    //                             // Optional
    //                             "time_to_live" => 3600 * 24 * 7, /* Top Is 4 Weeks */
    //                             // To specific User.
    //                             // "to" => "Token OR Reg_id"
    //                             // To All Users.
    //                             "to" => "/topics/my-topic"
    //                             // To set Of Users.
    //                             // "registration_ids" => "Array Of registration_ids OR Token Json"
    //                         ];
                            
    //                         $this->FCMNotification($notifBody);
    //                     }
    //             }
    //             // Get Notification Data ( Title / Message / Date ).
    //             // Send Notification To User When Apartment is Opened.
    //         }
    //     }
    // }
}
    
?>
