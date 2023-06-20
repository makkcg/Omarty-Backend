<?php
// session_start();
include("../vendor/autoload.php");

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header("content-type: Application/json");

class Show extends Functions
{
    private $RootUrl = "https://plateform.omarty.net/";
    public function __construct()
    {
        include("../Config.php");
        $this->conn = $conn;
    }

    function show_BlocksIDs_And_ApartmentsIDs_For_Resident()
    {
        // // include("../Config.php");

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
            // $token = $_SESSION["Token"];
            // $secret = $this->Signature;
            $secret = "secret123";
            $decode = JWT::decode($token, new KEY($secret, 'HS256'));
            // $this->returnResponse(200, $decode);
        }
        catch(Exception $e)
        {
            $this->throwError(401, $e->getMessage());
        }

        if( empty($decode->id)  )
        {
            $this->throwError(403,"User not found.") ;
        }
        elseif( !empty($decode->id) )
        {
            // ResidentID Assignment.
            // $RESID = $sqlGetResID->fetch_row();
            // Get Block ID and apartment id
            
            $sqlGetBlockID = $this->conn->query("SELECT ApartmentID, BlockID, StatusID, RoleID  FROM RES_APART_BLOCK_ROLE WHERE ResidentID='$decode->id' LIMIT $Start, $Limit");
            $sqlGetBlockID2 = $this->conn->query("SELECT ApartmentID, BlockID, StatusID, RoleID  FROM RES_APART_BLOCK_ROLE WHERE ResidentID='$decode->id'");
            $RowsNum = $sqlGetBlockID2->num_rows;
            $LastPage = ceil($RowsNum / $Limit);
            if($sqlGetBlockID->num_rows <= 0)
            {
                $this->returnResponse(200, []);
                exit;
            }
            elseif($sqlGetBlockID->num_rows > 0)
            {
                $count = 1;
                while($BLKAPT = $sqlGetBlockID->fetch_row())
                {
                    // Get Last page flag.
                    if(($Limit + $Start) >= $RowsNum)
                    {
                        $FLP = 1;
                    }
                    elseif(($Limit + $Start) < $RowsNum)
                    {
                        $FLP = 0;
                    }
                    $sqlGetStatus = $this->conn->query("SELECT Name From Status WHERE ID = $BLKAPT[2]");
                    try
                    {
                        $Status = $sqlGetStatus->fetch_row();
                    }
                    catch(Exception $e)
                    {
                        $this->throwError(304, $e->getMessage()); exit;
                    }
                    $sqlGetRoles = $this->conn->query("SELECT RoleName From Role WHERE ID = $BLKAPT[3]");
                    try
                    {
                        $Roles = $sqlGetRoles->fetch_row();
                    }
                    catch(Exception $e)
                    {
                        $this->throwError(304, $e->getMessage()); exit;
                    }
                    try
                    {
                        // Get Apartment Number And Floor Number.
                        $sqlGetFloorNum = $this->conn->query("SELECT FloorNum, ApartmentNumber, ApartmentName FROM Apartment WHERE ID = '$BLKAPT[0]'");
                        $FloorNum = $sqlGetFloorNum->fetch_row();
                        // Get Block Number
                        $sqlGetBLKNum = $this->conn->query("SELECT * FROM Block WHERE ID = '$BLKAPT[1]'");
                        $BLKNum = $sqlGetBLKNum->fetch_row();
                        if(!empty($BLKNum[4]))
                        {
                            $ImageUrl = $this->RootUrl . "omartyapis/Images/BlockImages/$BLKNum[4]";    
                        }
                        if(empty($BLKNum[4]))
                        {
                            $ImageUrl = $this->RootUrl . "omartyapis/Images/BlockImages/Default.jpg";
                        }
                        // Get Location Data.
                        $sqlGetCountry = $this->conn->query("SELECT name FROM Country WHERE ID = '$BLKNum[10]'");
                        $Country = $sqlGetCountry->fetch_row();
                        $sqlGetGov = $this->conn->query("SELECT GovName FROM Governate WHERE ID = '$BLKNum[11]'");
                        $Gov = $sqlGetGov->fetch_row();
                        $sqlGetCity = $this->conn->query("SELECT Name FROM City WHERE ID = '$BLKNum[12]'");
                        $City = $sqlGetCity->fetch_row();
                        $sqlGetRegion = $this->conn->query("SELECT RegionName FROM Region WHERE ID = '$BLKNum[13]'");
                        $Region = $sqlGetRegion->fetch_row();
                        $sqlGetComp = $this->conn->query("SELECT CompundName FROM Compound WHERE ID = '$BLKNum[14]'");
                        $Compound = $sqlGetComp->fetch_row();
                        $sqlGetStreet = $this->conn->query("SELECT StreetName FROM Street WHERE ID = '$BLKNum[15]'");
                        $Street = $sqlGetStreet->fetch_row();
                        
                    }
                    catch(Exception $e)
                    {
                        $this->throwError(304, $e->getMessage());
                    }

                    $Data["record$count"] = [
                            "id"=> "$count",
                            "apartmentId" => "$BLKAPT[0]",
                            "blockId" => "$BLKAPT[1]",
                            "apartmentNumber" => "$FloorNum[1]",
                            "apartmentName" => "$FloorNum[2]",
                            "floorNumber" => "$FloorNum[0]",
                            "image" => $ImageUrl,
                            "blockNumber" =>"$BLKNum[1]",
                            "blockName" =>"$BLKNum[20]",
                            "role" => "$Roles[0]",
                            "status" => "$Status[0]",
                            "countryName" => "$Country[0]",
                            "governateName" => "$Gov[0]",
                            "cityName" => "$City[0]",
                            "regionName" => "$Region[0]",
                            "compoundName" => "$Compound[0]",
                            "streetName" => "$Street[0]",
                            "flagLastPage" => $FLP
                        ];
                    
                    $count++;
                }
                $this->returnResponse(200, array_values($Data));
                exit;

            }
        }
    }

    function showNews() // OK Final (Return User Data (Name, Apartment, floor number)).
    {
        // // include("../Config.php");
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
                // $this->returnResponse(200, $decode);
            }
            catch(Exception $e)
            {
                $this->throwError(401, $e->getMessage());
            }
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $UserID = $decode->id;
           
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existense.
                    $sqlCheckBlock = $this->conn->query("SELECT ID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check Resident Relation to Block.
                        $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status.
                            $sqlBlkStatus = $this->conn->query("SELECT StatusID FROM Block WHERE ID = '$BLKID'");
                            if($sqlBlkStatus->num_rows > 0)
                            {
                                $blockStatus = $sqlBlkStatus->fetch_row();
                                if($blockStatus[0] == "2")
                                {
                                    // Check apartment Existence.
                                    $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = $APTID AND BlockID = '$BLKID'");
                                    
                                    if($sqlCheckAPT->num_rows <= 0)
                                    {
                                        $this->throwError(200, "apartment not found in block");
                                    }
                                    elseif($sqlCheckAPT->num_rows > 0)
                                    {
                                        // Check User Relation to the Apartment.
                                        $AptData = $sqlCheckAPT->fetch_row();
                                        if($AptData[2] == $UserID)
                                        {
                                            // Check Apartment Status.
                                            if($AptData[1] == '2')
                                            {
                                                // Select all news with the same block id.
                                                $sqlGetNews = $this->conn->query("SELECT LetterOfNews, ResidentID, Date, Image, ApartmentID, Tittle, CreatedAt, ID FROM News WHERE BlockID = '$BLKID' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                                $sqlGetNews2 = $this->conn->query("SELECT LetterOfNews, ResidentID, Date, Image, ApartmentID, Tittle, CreatedAt, ID FROM News WHERE BlockID = '$BLKID' ORDER BY CreatedAt DESC");
                                                $RowsNum = $sqlGetNews2->num_rows;
                                                // if($sqlGetNews->num_rows <= 0)
                                                // {
                                                //     $this->throwError(200, []);
                                                // }
                                                
                                                    $count = 1;
                                                    while($NewsData = $sqlGetNews->fetch_row())
                                                    {
                                                        // Get Last page flag.
                                                        if(($Limit + $Start) >= $RowsNum)
                                                        {
                                                            $FLP = 1;
                                                        }
                                                        elseif(($Limit + $Start) < $RowsNum)
                                                        {
                                                            $FLP = 0;
                                                        }
                                                        
                                                        // Get Resident Name
                                                        $sqlGetResName = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = '$NewsData[1]'");
                                                        if($sqlGetResName->num_rows > 0)
                                                        {
                                                            $ResData = $sqlGetResName->fetch_row();
                                                            $ResName = $ResData[0];
                                                            if(!empty($ResData[1]))
                                                            {
                                                                $ResImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$ResData[1]";
                                                            }
                                                            elseif(empty($ResData[1]))
                                                            {
                                                                $ResImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                            }
                                                        }
                                                        elseif($sqlGetResName->num_rows <= 0)
                                                        {
                                                            $ResName = $NewsData[1];
                                                        }
                                                        // Get Res Floor number.
                                                        $sqlGetAptFloor = $this->conn->query("SELECT FloorNum, ApartmentNumber, ApartmentName FROM Apartment WHERE ID = '$NewsData[4]'");
                                                        if($sqlGetAptFloor->num_rows > 0)
                                                        {
                                                            $ResAPTFloorData = $sqlGetAptFloor->fetch_row();
                                                            $ResAPTFloor = $ResAPTFloorData[0];
                                                            $ResAPTnum = $ResAPTFloorData[1];
                                                            $ResAPTName = $ResAPTFloorData[2];
                                                        }
                                                        elseif($sqlGetResName->num_rows <= 0)
                                                        {
                                                            $ResAPTFloor = $NewsData[4];
                                                            $ResAPTnum = $NewsData[4];
                                                            $ResAPTName = $NewsData[4];
                                                        }
                                                        // Get News Image
                                                        
                                                        if(!empty($NewsData[3]))
                                                        {
                                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/newsImages/$NewsData[3]";
                                                        }
                                                        elseif(empty($NewsData[3]))
                                                        {
                                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/newsImages/Default.jpg";
                                                        }
                                                        
                                                        
                                                        $News["Record$count"] = 
                                                        [
                                                            "id" => $NewsData[7],
                                                            "residentId" => $NewsData[1],
                                                            "residentName" => $ResName,
                                                            "residentImage" => $ResImageUrl,
                                                            "apartmentNumber" =>$ResAPTnum,
                                                            "apartmentName" =>$ResAPTName,
                                                            "apartmentFloorNumber" => $ResAPTFloor,
                                                            "newsTittle" => $NewsData[5],
                                                            "letterOfNews" => $NewsData[0],
                                                            "image" => $ImageUrl,
                                                            "date" => $NewsData[6],
                                                            "flagLastPage" => $FLP
                                                        ];
                                                        $count++;
                                                    }
                                                    if($sqlGetNews->num_rows <= 0)
                                                    {
                                                        $News = [];
                                                    }
                                                    $this->returnResponse(200, array_values($News));
                                                   
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
                                                $this->throwError(401, "Apartment status not acceptable.");
                                            }
                                        }
                                        else
                                        {
                                            $this->throwError(401, "User does not relate to this apartment.");
                                        }
                                    }
                                }
                                elseif($blockStatus[0] == "1")
                                {
                                    $this->throwError(200, "Block status is Binding.");
                                }
                                elseif($blockStatus[0] == "3")
                                {
                                    $this->throwError(200, "Block is Banned.");
                                }
                                else
                                {
                                    $this->throwError(401, "Block status not acceptable.");
                                }
                            }
                            else
                            {
                                $this->throwError(401, "Block status is not acceptble.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "User does not relate to this block.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Block Not Found.");
                    }
                }
    }

    function showMeetings() // OK Final (Next / Previous)
    {
        // // include("../Config.php");
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
            
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $Next = $_POST["upComming"];
            $Previous = $_POST["previous"];
            $MeetingID = $_POST["meetingId"];
            $UserID = $decode->id;
            if(empty($BLKID))
            {
                $this->throwError(200, "Block Not found.");
            }
            // Check Block existence.
            $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
            if($sqlCheckBlock->num_rows > 0)
            {
                // Check Resident Relation to Block.
                $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                if($sqlCheckResBlkRel->num_rows > 0)
                {
                    // Check Block Status.
                    $blockData = $sqlCheckBlock->fetch_row();
                    if($blockData[1] == '2')
                    {
                            // Check apartment Existence.
                            // $sqlCheckAPT = $this->conn->query("SELECT ID FROM Apartment WHERE ID = '$APTID'");
                            $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                            
                            if($sqlCheckAPT->num_rows <= 0)
                            {
                                $this->throwError(200, "apartment not found in block");
                            }
                            elseif($sqlCheckAPT->num_rows > 0)
                            {
                                // Check Resident Relation to this apartment.
                                $AptData = $sqlCheckAPT->fetch_row();
                                if($AptData[2] == $UserID)
                                {
                                    // Check Apartment Status.
                                    if($AptData[1] == '2')
                                    {
                                        $CurrentDay = date("Y-m-d h:i:sa");
                                        if($Next == '1')
                                        {
                                            // Select all upComming Meetings with the same block id and they are all approved by block manager.
                                            $sqlGetMeeting = $this->conn->query("SELECT * FROM Meeting WHERE BlockID = '$BLKID' AND Approval = 1 AND Date > '$CurrentDay' ORDER BY CreatedAt DESC LIMIT $Start, $Limit"); 
                                            $sqlGetMeeting2 = $this->conn->query("SELECT * FROM Meeting WHERE BlockID = '$BLKID' AND Approval = 1 AND Date > '$CurrentDay' ORDER BY CreatedAt DESC"); 
                                            $RowsNum = $sqlGetMeeting2->num_rows;
                                            // if($sqlGetMeeting->num_rows <= 0)
                                            // {
                                            //     $this->throwError(200, "Block does not have upcomming meeting");
                                            // }
                                            
                                        }
                                        if($Previous == '1')
                                        {
                                            // Select all previous Meetings with the same block id and they are all approved by block manager.
                                            $sqlGetMeeting = $this->conn->query("SELECT * FROM Meeting WHERE BlockID = '$BLKID' AND Approval = 1 AND Date < '$CurrentDay' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                            $sqlGetMeeting2 = $this->conn->query("SELECT * FROM Meeting WHERE BlockID = '$BLKID' AND Approval = 1 AND Date < '$CurrentDay' ORDER BY CreatedAt DESC");
                                            $RowsNum = $sqlGetMeeting2->num_rows;
                                            // if($sqlGetMeeting->num_rows <= 0)
                                            // {
                                            //     $this->throwError(200, "Block does not have Previous meeting");
                                            // }
                                        }
                                        if((empty($Next) && empty($Previous) && empty($MeetingID) || (!empty($Next) && (!empty($MeetingID)) && !empty($Previous))))
                                        {
                                            // Select all Meetings with the same block id and they are all approved by block manager.
                                            $sqlGetMeeting = $this->conn->query("SELECT * FROM Meeting WHERE BlockID = '$BLKID' AND Approval = 1 ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                            $sqlGetMeeting2 = $this->conn->query("SELECT * FROM Meeting WHERE BlockID = '$BLKID' AND Approval = 1 ORDER BY CreatedAt DESC");
                                            $RowsNum = $sqlGetMeeting2->num_rows;
                                            // if($sqlGetMeeting->num_rows <= 0)
                                            // {
                                            //     $this->returnResponse(200, []);
                                            // }
                                        }
                                        if(!empty($MeetingID))
                                        {
                                            // Select Meeting By ID with the same block id and they are all approved by block manager.
                                            $sqlGetMeeting = $this->conn->query("SELECT * FROM Meeting WHERE ID = '$MeetingID' LIMIT $Start, $Limit");
                                            $sqlGetMeeting2 = $this->conn->query("SELECT * FROM Meeting WHERE ID = '$MeetingID'");
                                            $RowsNum = $sqlGetMeeting2->num_rows;
                                            // if($sqlGetMeeting->num_rows <= 0)
                                            // {
                                            //     $this->throwError(200, "Block does not have Previous meeting");
                                            // }
                                        }
                                        $count = 1;
                                        while($MeetingData = $sqlGetMeeting->fetch_row())
                                        {
                                            // Get Last page flag.
                                            if(($Limit + $Start) >= $RowsNum)
                                            {
                                                $FLP = 1;
                                            }
                                            elseif(($Limit + $Start) < $RowsNum)
                                            {
                                                $FLP = 0;
                                            }
                                            $DecCount = 1;
                                            // Get Meetings Decisions.
                                            $sqlGetDec = $this->conn->query("SELECT ID, Decision, Likes, DisLikes, CreatedAt FROM Decision WHERE MeetingID = '$MeetingData[0]' ORDER BY ID DESC LIMIT $Start, $Limit");
                                            $sqlGetDec2 = $this->conn->query("SELECT ID, Decision, Likes, DisLikes, CreatedAt FROM Decision WHERE MeetingID = '$MeetingData[0]' ORDER BY ID DESC");
                                            $RowsNum3 = $sqlGetDec2->num_rows;
                                            $DecArr = [];
                                            while($Decision = $sqlGetDec->fetch_row())
                                            {
                                                // Get Last page flag.
                                                if(($Limit + $Start) >= $RowsNum3)
                                                {
                                                    $FLPD = 1;
                                                }
                                                elseif(($Limit + $Start) < $RowsNum3)
                                                {
                                                    $FLPD = 0;
                                                }
                                                
                                                // Get Vote Status.
                                                $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Decision' AND RecordID = '$Decision[0]'");
                                                if($sqlGetVoteStatus->num_rows > 0)
                                                {
                                                    $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                    $Likes = $VoteStatusArr[0];
                                                    $DisLikes = $VoteStatusArr[1];
                                                    if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                    {
                                                        $VoteStatus = TRUE;
                                                    }
                                                    elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                    {
                                                        $VoteStatus = FALSE;
                                                    }
                                                    elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                }
                                                elseif($sqlGetVoteStatus->num_rows <= 0)
                                                {
                                                    $VoteStatus = NULL;
                                                }
                                                
                                                $ComArr = [];
                                                $CommentCounter = 1;
                                                // Get Decision Comments.
                                                $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$Decision[0]' AND OriginalPostTable = 'Decision' ORDER BY ID DESC LIMIT $Start, $Limit");
                                                $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$Decision[0]' AND OriginalPostTable = 'Decision' ORDER BY ID DESC");
                                                $RowsNum2 = $sqlGetComment2->num_rows;
                                                while($Comment = $sqlGetComment->fetch_row())
                                                {
                                                    // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum2)
                                                    {
                                                        $FLPC = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum2)
                                                    {
                                                        $FLPC = 0;
                                                    }
                                                    // Get Resident Name Image 
                                                    $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                                    if($sqlGetRes->num_rows > 0)
                                                    {
                                                        $ResDT = $sqlGetRes->fetch_row();
                                                        $ResName = $ResDT[0];
                                                        if(!empty($ResDT[1]))
                                                        {
                                                            $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/$ResDT[1]";
                                                        }
                                                        elseif(empty($ResDT[1]))
                                                        {
                                                            $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    elseif($sqlGetRes->num_rows <= 0)
                                                    {
                                                        $ResName = $Comment[2];
                                                        $Resimage = "";
                                                    }
                                                    // Get Apartment Num and Apartment Floor Num.
                                                    $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName, BlockID FROM Apartment WHERE ID = '$Comment[3]'");
                                                    if($sqlGetAptData->num_rows > 0)
                                                    {
                                                        $AptDataC = $sqlGetAptData->fetch_row();
                                                        $AptNumC = $AptDataC[0];
                                                        $AptFloorNumC = $AptDataC[1];
                                                        $AptNameC = $AptDataC[2];
                                                    }
                                                    if($sqlGetAptData->num_rows <= 0)
                                                    {
                                                        $AptNumC = $Comment[3];
                                                        $AptFloorNumC = $Comment[3];
                                                        $AptNameC = $Comment[3];
                                                    }
                                                    
                                                    // Get Block Number and name.
                                                    $sqlGetBlockName = $this->conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$AptDataC[3]'");
                                                    if($sqlGetBlockName->num_rows > 0)
                                                    {
                                                        $BlkDataC = $sqlGetBlockName->fetch_row();
                                                        $BlkIdC = $BlkDataC[0];
                                                        $BlkNumC = $BlkDataC[0];
                                                        $BlkNameC = $BlkDataC[1];
                                                    }
                                                    if($sqlGetBlockName->num_rows <= 0)
                                                    {
                                                        $BlkIdC = NULL;
                                                        $BlkNumC = NULL;
                                                        $BlkNameC = NULL;
                                                    }
                                                    
                                                    // Get Vote Status.
                                                    $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                                    if($sqlGetVoteStatus->num_rows > 0)
                                                    {
                                                        $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                        $Likes = $VoteStatusArr[0];
                                                        $DisLikes = $VoteStatusArr[1];
                                                        if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                        {
                                                            $VoteStatus = TRUE;
                                                        }
                                                        elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                        {
                                                            $VoteStatus = FALSE;
                                                        }
                                                        elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                    }
                                                    elseif($sqlGetVoteStatus->num_rows <= 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                    
                                                    $ComArr[$CommentCounter] = 
                                                    [
                                                        "commentId" => $Comment[0],
                                                        "comment" => $Comment[1],
                                                        "residentId" => $Comment[2],
                                                        "residentName" => $ResName,
                                                        "residentImage" => $ResImage,
                                                        "apartmentId" => $Comment[3],
                                                        "apartmentNumber" => $AptNumC,
                                                        "apartmentName" => $AptNameC,
                                                        "apartmentFloorNumber" => $AptFloorNumC,
                                                        "blockId" => $BlkIdC,
                                                        "blockNumber" => $BlkNumC,
                                                        "blockName" => $BlkNameC,
                                                        "likes" => $Comment[4],
                                                        "disLikes" => $Comment[5],
                                                        "voteStatus" => $VoteStatus,
                                                        "createdAt" => $Comment[6],
                                                        "flagLastPage" => $FLPC
                                                    ];
                                                    $CommentCounter++;
                                                }
                                                $DecArr[$DecCount] = 
                                                [
                                                    "decisionID" => $Decision[0],
                                                    "decision" => $Decision[1],
                                                    "likes" => $Decision[2],
                                                    "disLikes" => $Decision[3],
                                                    "voteStatus" => $VoteStatus,
                                                    "createdAt" => $Decision[4],
                                                    "comments" => array_values($ComArr),
                                                    "flagLastPage" => $FLPD
                                                ];
                                                $DecCount++;
                                            }
                                            // $this->returnResponse(200, $DecArr);
                                            if(empty($MeetingData[3]))
                                            {
                                                $attachmentURL = $this->RootUrl . "omartyapis/Images/meetingImages/Default.jpg";
                                            }
                                            elseif(!empty($MeetingData[3]))
                                            {
                                                $attachmentURL = $this->RootUrl . "omartyapis/Images/meetingImages/" . $MeetingData[3];
                                            }
                                            
                                            // Get User Name.
                                            $sqlGetUserName = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $MeetingData[8]");
                                            if($sqlGetUserName->num_rows > 0)
                                            {
                                                $residentName = $sqlGetUserName->fetch_row();
                                                $RESNAME = $residentName[0];
                                                if(!empty($residentName[1]))
                                                {
                                                    $ResImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$residentName[1]";
                                                }
                                                elseif(empty($residentName[1]))
                                                {
                                                    $ResImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                }
                                            }
                                            else
                                            {
                                                $RESNAME = $MeetingData[8];
                                            }
                                            // Get block manager name.
                                            $sqlGetBLKMNGName = $this->conn->query("SELECT Name FROM Resident_User WHERE ID = $MeetingData[9]");
                                            if($sqlGetBLKMNGName->num_rows > 0)
                                            {
                                                $BLKMNGName = $sqlGetBLKMNGName->fetch_row();
                                                $BMNAME = $BLKMNGName[0];
                                            }
                                            else
                                            {
                                                $BMNAME = $MeetingData[9];
                                            }
                                            // Get Block number.
                                             $sqlGetBLKNUM = $this->conn->query("SELECT BlockNum, BlockName FROM Block WHERE ID = $MeetingData[10]");
                                            if($sqlGetBLKNUM->num_rows > 0)
                                            {
                                                $BLKNUM = $sqlGetBLKNUM->fetch_row();
                                                $BlockNum = $BLKNUM[0];
                                                $BlockName = $BLKNUM[1];
                                            }
                                            else
                                            {
                                                $BlockNum = $MeetingData[10];
                                                $BlockName = $MeetingData[10];
                                            }
                                            
                                            $AttendStatus = NULL;
                                            // Get Attend Status.
                                            $sqlGetAttendStatus = $this->conn->query("SELECT Attend, Absent FROM Attendees WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Meeting' AND RecordID = '$MeetingData[0]'");
                                            if($sqlGetAttendStatus->num_rows > 0)
                                            {
                                                $AttendStatusArr = $sqlGetAttendStatus->fetch_row();
                                                $Likes = $AttendStatusArr[0];
                                                $DisLikes = $AttendStatusArr[1];
                                                if($AttendStatusArr[0] > 0 && $AttendStatusArr[1] <= 0)
                                                {
                                                    $AttendStatus = TRUE;
                                                }
                                                elseif($AttendStatusArr[1] >= 0 && $AttendStatusArr[0] <= 0)
                                                {
                                                    $AttendStatus = FALSE;
                                                }
                                                elseif($AttendStatusArr[0] == 0 && $AttendStatusArr[1] == 0)
                                                {
                                                    $AttendStatus = NULL;
                                                }
                                            }
                                            
                                            $Meeting["Record$count"] = 
                                            [
                                                "id" => $MeetingData[0],
                                                "tittle" => $MeetingData[1],
                                                "body" => $MeetingData[2],
                                                "attachmentUrl" => $attachmentURL,
                                                "meetingDate" => $MeetingData[4],
                                                "location" => $MeetingData[5],
                                                "numOfAttendees" => $MeetingData[7],
                                                "attendStatus" => $AttendStatus,
                                                // "approval" => $MeetingData[6],
                                                "residentName" => $RESNAME,
                                                "residentImage" => $ResImageUrl,
                                                "blockManagerName" => $BMNAME,
                                                "blockNumber" => $BlockNum,
                                                "blockName" => $BlockName,
                                                "date" => $MeetingData[11],
                                                "decision" => array_values($DecArr),
                                                "flagLastPage" => $FLP
                                            ];
                                            $count++;
                                        }
                                        
                                        if($sqlGetMeeting->num_rows <= 0)
                                        {
                                            $Meeting = [];
                                        }
                                        $this->returnResponse(200, array_values($Meeting));
                                    }
                                    elseif($AptData[1] == '1')
                                    {
                                        $this->throwError(401, "Apartment Status is still Binding.");
                                    }
                                    elseif($AptData[1] == '3')
                                    {
                                        $this->throwError(401, "Apartment is Banned.");
                                    }
                                    else
                                    {
                                        $this->throwError(401, "Apartment status not acceptable.");
                                    }
                                }
                                else
                                {
                                    $this->throwError(401, "User does not relate to this apartment.");
    
                                }
                            }
                    }
                    elseif($blockData[1] == '1')
                    {
                        $this->throwError(200, "Block status is still Binding.");
                    }
                    elseif($blockData[1] == '3')
                    {
                        $this->throwError(200, "Block is Banned.");
                    }
                    else
                    {
                        $this->throwError(401, "Block status not acceptable.");
                    }
                }
                else
                {
                    $this->throwError(406, "User doesn't relate to this block.");
                }
            }
            else
            {
                $this->throwError(200, "Block Not found.");
            }
    }

    function showFavourite() // for Block Manager
    {
        // // include("../Config.php");
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
            
            // Check Block Existence.
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
            $UserID = $decode->id;
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];

            // Get Block ID and Apartment ID
            // $BLKID = $decode->apartmentsAndBlocks->record1->block;
            // $APTID = $decode->apartmentsAndBlocks->record1->apartment;

            // Get category id 
            $categoryID = $_POST["categoryId"];

            if(empty($BLKID))
            {
                $this->throwError(200, "Please Enter Block ID.");
            }
            elseif(!empty($BLKID))
            {
                // Check Block Existence.
                $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
                if($sqlCheckBlock->num_rows > 0)
                {
                    // Check Block Status 
                    $blockData = $sqlCheckBlock->fetch_row();
                    if($blockData[1] == "2")
                    {   
                        // The response Array.
                        $Favourite = [];
                        // Check apartment Existence and user status.
                        $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
                        
                        if($sqlCheckAPT->num_rows <= 0)
                        {
                            $this->throwError(200, "apartment not found in block");
                        }
                        elseif($sqlCheckAPT->num_rows > 0)
                        {
                            // Check Apartment Status 
                            $aptData = $sqlCheckAPT->fetch_row();
                            if($aptData[1] == '2')
                            {
                                if(empty($categoryID))
                                {
                                    // Select all Favourites with the same ResidentID.
                                    $sqlGetFavourite = $this->conn->query("SELECT * FROM Favourite WHERE ResidentID = '$UserID' LIMIT $Start, $Limit");
                                    $sqlGetFavourite2 = $this->conn->query("SELECT * FROM Favourite WHERE ResidentID = '$UserID'");
                                    $RowsNum = $sqlGetFavourite2->num_rows;
                                }
                                elseif(!empty($categoryID))
                                {
                                    // Select all Favourites with the same ResidentID.
                                    $sqlGetFavourite = $this->conn->query("SELECT * FROM Favourite WHERE ResidentID = '$UserID' AND CategoryID = '$categoryID' LIMIT $Start, $Limit");
                                    $sqlGetFavourite2 = $this->conn->query("SELECT * FROM Favourite WHERE ResidentID = '$UserID' AND CategoryID = '$categoryID'");
                                    $RowsNum = $sqlGetFavourite2->num_rows;
                                    // if($sqlGetFavourite->num_rows <= 0)
                                    // {
                                    //     $this->returnResponse(200, []);
                                    // }
                                    if($sqlGetFavourite->num_rows > 0)
                                    {
                                        $count = 1;
                                        
                                        while($FavouriteData = $sqlGetFavourite->fetch_row())
                                        {
                                            // Get Last page flag.
                                            if(($Limit + $Start) >= $RowsNum)
                                            {
                                                $FLP = 1;
                                            }
                                            elseif(($Limit + $Start) < $RowsNum)
                                            {
                                                $FLP = 0;
                                            }
                                            // Get Category Name.
                                            $sqlGetCategoryName = $this->conn->query("SELECT Name_ar FROM ServiceCategory WHERE ID = '$categoryID'");
                                            if($sqlGetCategoryName->num_rows > 0)
                                            {
                                                $CatName = $sqlGetCategoryName->fetch_row();
                                            }
                                            elseif($sqlGetCategoryName->num_rows < 0)
                                            {
                                                $CatName[0] = $categoryID;
                                            }
                                            // Get Service Name.
                                            $sqlGetServName = $this->conn->query("SELECT Name, PhoneNumI, PhoneNumII, PhoneNumIII, PhoneNumIV, Image FROM Service WHERE ID = '$FavouriteData[6]'");
                                            if($this->conn->error)
                                            {
                                                echo $this->conn->error;
                                            }
                                            $Service = [];
                                            if($sqlGetServName->num_rows > 0)
                                            {
                                                $ServData = $sqlGetServName->fetch_row();
                                                $ServName = $ServData[0];
                                                $PhoneNums = ["PhoneNum1" => $ServData[1]];
                                                if(!empty($ServData[2]))
                                                {
                                                    $PhoneNums += ["PhoneNum2" => $ServData[2]];
                                                }
                                                if(!empty($ServData[3]))
                                                {
                                                    $PhoneNums += ["PhoneNum3" => $ServData[3]];
                                                }
                                                if(!empty($ServData[4]))
                                                {
                                                    $PhoneNums += ["PhoneNum4" => $ServData[4]];
                                                }
                                                if(!empty($ServData[5]))
                                                {
                                                    $ImageUrl = $this->RootUrl . "omartyapis/Images/serviceImages/$ServData[5]";
                                                }
                                                elseif(empty($ServData[5]))
                                                {
                                                    $ImageUrl = $this->RootUrl . "omartyapis/Images/serviceImages/Default.jpg";
                                                }
                                                $Service = 
                                                [
                                                    "name" =>$ServName,
                                                    "phoneNums" => $PhoneNums,
                                                    "image" => $ImageUrl
                                                ];
                                            }
                                            // elseif($sqlGetServName->num_rows <= 0)
                                            // {
                                            //     $ServName = $FavouriteData[6];
                                            //     $PhoneNums = [];
                                            //     $ImageUrl = "";
                                            // }
                                            
                                            $Favourite["Record$count"] = 
                                            [
                                                "id" => $FavouriteData[0],
                                                "name" => $FavouriteData[1],
                                                "residentID" => $FavouriteData[2],
                                                "categoryID" => $FavouriteData[5],
                                                "categoryName" => $CatName[0],
                                                "serviceID" => $FavouriteData[6],
                                                "serviceData" => $Service,
                                                "createdAt" => $FavouriteData[8],
                                                "flagLastPage" => $FLP
                                            ];
                                            $count++;
                                        }
                                        
                                    }
                                }
                                // $this->returnResponse(200, array_values($Favourite));
                                 $count = 1;
                                 $Favourite = [];
                                    while($FavouriteData = $sqlGetFavourite->fetch_row())
                                    {
                                        // Get Last page flag.
                                        if(($Limit + $Start) >= $RowsNum)
                                        {
                                            $FLP = 1;
                                        }
                                        elseif(($Limit + $Start) < $RowsNum)
                                        {
                                            $FLP = 0;
                                        }
                                        
                                        // Get Category Name.
                                        $sqlGetCategoryName = $this->conn->query("SELECT Name_ar FROM ServiceCategory WHERE ID = '$FavouriteData[5]'");
                                        if($sqlGetCategoryName->num_rows > 0)
                                        {
                                            $CatName = $sqlGetCategoryName->fetch_row();
                                        }
                                        elseif($sqlGetCategoryName->num_rows < 0)
                                        {
                                            $CatName[0] = $categoryID;
                                        }
                                            
                                        // Fetched Fav is Service.
                                        if($FavouriteData[7] === NULL)
                                        {
                                            // ============================================================================================================================================
                                            // Get Service Data.
                                            $sqlGetSerData = $this->conn->query("SELECT * FROM Service WHERE ID = $FavouriteData[6]");
                                            if($sqlGetSerData->num_rows > 0)
                                            {
                                                $ServiceData = $sqlGetSerData->fetch_row();
                                                // Check Comments
                                                $ComArr = [];
                                                $CommentCounter = 1;
                                                // Get Service Comments.
                                                $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$ServiceData[0]' AND OriginalPostTable = 'Service'");
                                                $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$ServiceData[0]' AND OriginalPostTable = 'Service'");
                                                $RowsNum2 = $sqlGetComment2->num_rows;
                                                while($Comment = $sqlGetComment->fetch_row())
                                                {
                                                    // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum2)
                                                    {
                                                        $FLPC = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum2)
                                                    {
                                                        $FLPC = 0;
                                                    }
                                                    // Get Resident Name Image 
                                                    $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                                    if($sqlGetRes->num_rows > 0)
                                                    {
                                                        $ResDT = $sqlGetRes->fetch_row();
                                                        $ResName = $ResDT[0];
                                                        if(!empty($ResDT[1]))
                                                        {
                                                            $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/$ResDT[1]";
                                                        }
                                                        elseif(empty($ResDT[1]))
                                                        {
                                                            $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    elseif($sqlGetRes->num_rows <= 0)
                                                    {
                                                        $ResName = $Comment[2];
                                                        $Resimage = "";
                                                    }
                                                    // Get Apartment Num and Apartment Floor Num.
                                                    $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName, BlockID FROM Apartment WHERE ID = '$Comment[3]'");
                                                    if($sqlGetAptData->num_rows > 0)
                                                    {
                                                        $AptDataC = $sqlGetAptData->fetch_row();
                                                        $AptNumC = $AptDataC[0];
                                                        $AptFloorNumC = $AptDataC[1];
                                                        $AptNameC = $AptDataC[2];
                                                    }
                                                    if($sqlGetAptData->num_rows <= 0)
                                                    {
                                                        $AptNumC = $Comment[3];
                                                        $AptFloorNumC = $Comment[3];
                                                        $AptNameC = $Comment[3];
                                                    }
                                                    
                                                    // Get Block Number and name.
                                                    $sqlGetBlockName = $this->conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$AptDataC[3]'");
                                                    if($sqlGetBlockName->num_rows > 0)
                                                    {
                                                        $BlkDataC = $sqlGetBlockName->fetch_row();
                                                        $BlkIdC = $BlkDataC[0];
                                                        $BlkNumC = $BlkDataC[0];
                                                        $BlkNameC = $BlkDataC[1];
                                                    }
                                                    if($sqlGetBlockName->num_rows <= 0)
                                                    {
                                                        $BlkIdC = NULL;
                                                        $BlkNumC = NULL;
                                                        $BlkNameC = NULL;
                                                    }
                                                    
                                                    // Get Vote Status.
                                                    $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                                    if($sqlGetVoteStatus->num_rows > 0)
                                                    {
                                                        $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                        $Likes = $VoteStatusArr[0];
                                                        $DisLikes = $VoteStatusArr[1];
                                                        if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                        {
                                                            $VoteStatus = TRUE;
                                                        }
                                                        elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                        {
                                                            $VoteStatus = FALSE;
                                                        }
                                                        elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                    }
                                                    elseif($sqlGetVoteStatus->num_rows <= 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                    
                                                    $ComArr[$CommentCounter] = 
                                                    [
                                                        "commentId" => $Comment[0],
                                                        "comment" => $Comment[1],
                                                        "residentId" => $Comment[2],
                                                        "residentName" => $ResName,
                                                        "residentImage" => $ResImage,
                                                        "apartmentId" => $Comment[3],
                                                        "apartmentNumber" => $AptNumC,
                                                        "apartmentName" => $AptNameC,
                                                        "apartmentFloorNumber" => $AptFloorNumC,
                                                        "blockId" => $BlkIdC,
                                                        "blockNumber" => $BlkNumC,
                                                        "blockName" => $BlkNameC,
                                                        "likes" => $Comment[4],
                                                        "disLikes" => $Comment[5],
                                                        "voteStatus" => $VoteStatus,
                                                        "flagLastPage" => $FLPC
                                                    ];
                                                    $CommentCounter++;
                                                }
                                                //   ==============================================================================================
                                                if(empty($ServiceData[7]))
                                                {
                                                    $attachmentURL = $this->RootUrl . "omartyapis/Images/serviceImages/Default.jpg";
                                                }
                                                elseif(!empty($ServiceData[7]))
                                                {
                                                    $attachmentURL = $this->RootUrl . "omartyapis/Images/serviceImages/" . $ServiceData[7];
                                                }
                                                        
                                                // Get User Name.
                                                $sqlGetUserName = $this->conn->query("SELECT Name FROM Resident_User WHERE ID = '$ServiceData[11]'");
                                                if($sqlGetUserName->num_rows > 0)
                                                {
                                                    $residentName = $sqlGetUserName->fetch_row();
                                                    $RESNAME = $residentName[0];
                                                }
                                                else
                                                {
                                                    // if Coudln't get resident Username get his ID.
                                                    $RESNAME = $ServiceData[11]; // Standing here.
                                                }
                        
                                                // Get Service Phone Numbers in $PhoneNums array.
                                                $PhoneNums = ["phoneNum1" => $ServiceData[3]];
                                                if(!empty($ServiceData[4]))
                                                {
                                                    $PhoneNums += ["phoneNum2" => $ServiceData[4]];
                                                    // $this->returnResponse(200, $PhoneNums);
                                                }
                                                if(!empty($ServiceData[5]))
                                                {
                                                    $PhoneNums += ["phoneNum3" => $ServiceData[5]];
                                                    // $this->returnResponse(200, $PhoneNums);
                                                }
                                                if(!empty($ServiceData[6]))
                                                {
                                                    $PhoneNums += ["phoneNum4" => $ServiceData[6]];
                                                    // $this->returnResponse(200, $PhoneNums);
                                                }
                                                
                                                // Get Block number.
                                                $sqlGetBLKNUM = $this->conn->query("SELECT BlockNum FROM Block WHERE ID = '$ServiceData[13]'");
                                                if($sqlGetBLKNUM->num_rows > 0)
                                                {
                                                    $BLKNUM = $sqlGetBLKNUM->fetch_row();
                                                    $BlockNum = $BLKNUM[0];
                                                }
                                                else
                                                {
                                                    $BlockNum = $ServiceData[13];
                                                }
                                                
                                                // Get Category Name
                                                $sqlGetSerCat = $this->conn->query("SELECT Name_ar FROM ServiceCategory WHERE ID = '$ServiceData[10]'");
                                                if($sqlGetSerCat->num_rows > 0)
                                                {
                                                    $CatName = $sqlGetSerCat->fetch_row();
                                                }
                                                elseif($sqlGetSerCat->num_rows <= 0)
                                                {
                                                    $CatName[0] = $ServiceData[10];
                                                }
                                                
                                                // Get Country
                                                $sqlGetCountry = $this->conn->query("SELECT name From Country Where ID = '$ServiceData[16]'");
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
                                                $sqlGetGov = $this->conn->query("SELECT GOVName From Governate Where ID = '$ServiceData[17]'");
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
                                                $sqlGetCity = $this->conn->query("SELECT Name From City Where ID = '$ServiceData[18]'");
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
                                                $sqlGetRegion = $this->conn->query("SELECT RegionName From Region Where ID = '$ServiceData[19]'");
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
                                                $sqlGetCompound = $this->conn->query("SELECT CompundName From Compound Where ID = '$ServiceData[20]'");
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
                                                $sqlGetStreet = $this->conn->query("SELECT StreetName From Street Where ID = '$ServiceData[21]'");
                                                if($sqlGetStreet->num_rows > 0)
                                                {
                                                    $StreetNameArr = $sqlGetStreet->fetch_row();
                                                    $StreetName = $StreetNameArr[0];
                                                    
                                                }
                                                elseif($sqlGetStreet->num_rows <= 0)
                                                {
                                                    $StreetName = $ServiceData[21];
                                                }
                                                // ============================================================================================================================================
                                                // Get Service Data
                                                $Favourite["Record$count"] = 
                                                [
                                                    "id" => $ServiceData[0],
                                                    "name" => $ServiceData[1],
                                                    // "isFav" => $Favourite,
                                                    "description" => $ServiceData[2],
                                                    "phoneNums" => $PhoneNums,
                                                    "image" => $attachmentURL,
                                                    "rate" => $ServiceData[8],
                                                    "categoryID" => $ServiceData[10],
                                                    "categoryName" => $CatName[0],
                                                    "Comments" => array_values($ComArr),
                                                    "latitude" => $ServiceData[14],
                                                    "longitude" => $ServiceData[15],      
                                                    "countryName" => $CountryName,
                                                    "governateName" => $GovName,
                                                    "cityName" => $CityName,
                                                    "regionName" => $RegionName,
                                                    "compoundName" => $CompName,
                                                    "streetName" => $StreetName,
                                                    "flagLastPage" => $FLP
                                                ];    
                                            }
                                            
                                        }
                                        // Fetched Fav is Neighbour.
                                        elseif($FavouriteData[7] > 0)
                                        {
                                            // Get Neighbour Data
                                            // Get User Name, Image and Phone number.
                                            $sqlGetPN = $this->conn->query("SELECT Name, Image, PhoneNum, Hide FROM Resident_User WHERE ID = '$FavouriteData[7]'");
                                            if($sqlGetPN->num_rows > 0)
                                            {
                                                $residentPN = $sqlGetPN->fetch_row();
                                                if($residentPN[3] == "2" || $residentPN[3] == "3")
                                                {
                                                    $RESpn = "Hidden";
                                                }
                                                else
                                                {
                                                    $RESpn = $residentPN[2];
                                                }
                                                
                                                // Check For Secondary phone Numbers
                                                $sqlCheckPN = $this->conn->query("SELECT PhoneNum, ID FROM PhoneNums WHERE UserID = '$FavouriteData[7]' AND Hide = 0");
                                                $SecondaryPNs = [];
                                                if($sqlCheckPN->num_rows > 0)
                                                {
                                                    $count = 1;
                                                    while($PNData = $sqlCheckPN->fetch_row())
                                                    {
                                                        $SecondaryPNs[$count] = ["id" => $PNData[1], "phoneNumber" => $PNData[0]];
                                                        $count++;
                                                    }
                                                    
                                                }
                                                
                                                $RESname = $residentPN[0];
                                                // get image.
                                                if(!empty($residentPN[1]))
                                                {
                                                    $ResidentImage = $this->RootUrl . "omartyapis/Images/profilePictures/$residentPN[1]";
                                                }
                                                elseif(empty($residentPN[1]))
                                                {
                                                    $ResidentImage = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                }
                                            }
                                            else
                                            {
                                                $RESpn = $FavouriteData[7];
                                            }
                                            // Get Neighbour ApartmentID.
                                            $sqlGetNeighAptId = $this->conn->query("SELECT ApartmentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$FavouriteData[7]' AND BlockID = '$BLKID'");
                                            if($sqlGetNeighAptId->num_rows > 0)
                                            {
                                                $NeighAptId = $sqlGetNeighAptId->fetch_row();
                                                // Get Apatment Number and Floor number..
                                                $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = $NeighAptId[0]");
                                                if($sqlGetAptNum->num_rows > 0)
                                                {
                                                    $AptNum = $sqlGetAptNum->fetch_row();
                                                    $APTNUM = $AptNum[0];
                                                    $APTFLRNUM = $AptNum[1];
                                                    $APTNAME = $AptNum[2];
                                                }
                                                else
                                                {
                                                    $APTNUM = $NeighAptId[0];
                                                    $APTFLRNUM = $NeighAptId[0];
                                                    $APTNAME = $NeighAptId[0];
                                                }
                                            }
                                            
                                            $Favourite["Record$count"] = 
                                            [
                                                "id" => $FavouriteData[7],
                                                "residentName" => $RESname,
                                                "residentImage" => $ResidentImage,
                                                "apartmentNumber" => $APTNUM,
                                                "apartmentName" => $APTNAME,
                                                "apartmentFloorNumber" => $APTFLRNUM,
                                                "phoneNumber" => $RESpn,
                                                "secondaryPhoneNumbers" => array_values($SecondaryPNs),
                                                "categoryName" => "جيراني",
                                                "flagLastPage" => $FLP,
                                            ];
                                        }
                                        $count++;
                                    }
                                    if($Favourite == NULL)
                                    {
                                        $this->returnResponse(200, []);
                                    }
                                    else
                                    {
                                        $this->returnResponse(200, array_values($Favourite));
                                    }
                                
                                // ==================================================================================================================================
                            }
                            elseif($aptData[1] == '1')
                            {
                                $this->throwError(200, "Apartment status is Binding.");
                            }
                            elseif($aptData[1] == '3')
                            {
                                $this->throwError(200, "Apartment is  Banned.");
                            }
                            else
                            {
                                $this->throwError(200, "Apartment status is not acceptable.");
                            }
                        }
                    }
                    elseif($blockData[1] == "1")
                    {
                        $this->throwError(200, "Block status is Binding.");
                    }
                    elseif($blockData[1] == "3")
                    {
                        $this->throwError(200, "Block is Banned.");
                    }
                    else
                    {
                        $this->throwError(200, "Block status is not acceptable.");
                    }
                }
                elseif($sqlCheckBlock->num_rows <= 0)
                {
                    $this->throwError(200, "Block Not Found.");
                }
            }
            
    }

    function showEvents() // OK Final (Mine / Next / Previous)
    {
        date_default_timezone_set('Africa/Cairo');
        // // include("../Config.php");
        
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
        
            // Check Block Existence.
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
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $Mine = $_POST["myEvents"];
            $Next = $_POST["upComming"];
            $Previous = $_POST["previous"];
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
                                            if($Mine == '1')
                                            {
                                                // Select all Events with the same block id and belongs to the user.
                                                $sqlGetEvent = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' AND UserID = '$UserID' AND ApartmentID = '$APTID' ORDER BY ID DESC LIMIT $Start, $Limit");
                                                $sqlGetEvent2 = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' AND UserID = '$UserID' AND ApartmentID = '$APTID' ORDER BY ID DESC");
                                                $RowsNum = $sqlGetEvent2->num_rows;
                                                if($sqlGetEvent->num_rows <= 0)
                                                {
                                                    $this->returnResponse(200, []);
                                                }
                                            }
                                            if($Next == '1')
                                            {
                                                // Select all up comming Events with the same block id.
                                                $sqlGetEvent = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' AND Date > '$date' ORDER BY ID DESC LIMIT $Start, $Limit");
                                                $sqlGetEvent2 = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' AND Date > '$date' ORDER BY ID DESC");
                                                $RowsNum = $sqlGetEvent2->num_rows;
                                                if($sqlGetEvent->num_rows <= 0)
                                                {
                                                    $this->returnResponse(200, []);
                                                    exit;
                                                }
                                            }
                                            if($Previous == '1')
                                            {
                                                // Select all previous Events with the same block id.
                                                $sqlGetEvent = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' AND Date < '$date' ORDER BY ID DESC LIMIT $Start, $Limit");
                                                $sqlGetEvent2 = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' AND Date < '$date' ORDER BY ID DESC");
                                                $RowsNum = $sqlGetEvent2->num_rows;
                                                if($sqlGetEvent->num_rows <= 0)
                                                {
                                                    $this->returnResponse(200, []);
                                                    exit;
                                                }
                                            }
                                            if((!empty($Mine) && !empty($Next) && !empty($Previous)) || (empty($Mine) && empty($Next) && empty($Previous)))
                                            {
                                                $sqlGetEvent = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' ORDER BY ID DESC LIMIT $Start, $Limit");
                                                $sqlGetEvent2 = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' ORDER BY ID DESC");
                                                $RowsNum = $sqlGetEvent2->num_rows;
                                                if($sqlGetEvent->num_rows <= 0)
                                                {
                                                    $this->returnResponse(200, []);
                                                    exit;
                                                }
                                            }
                                            
                                                $count = 1;
                                                while($EventData = $sqlGetEvent->fetch_row())
                                                {
                                                    // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum)
                                                    {
                                                        $FLP = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum)
                                                    {
                                                        $FLP = 0;
                                                    }
                                                    if(empty($EventData[3]))
                                                    {
                                                        $attachmentURL = $this->RootUrl . "omartyapis/Images/eventImages/Default.jpg";
                                                    }
                                                    elseif(!empty($EventData[3]))
                                                    {
                                                        $attachmentURL = $this->RootUrl . "omartyapis/Images/eventImages/" . $EventData[3];
                                                    }
                                                
                                                     // Get User Name.
                                                    $sqlGetUserName = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $EventData[8]");
                                                    if($sqlGetUserName->num_rows > 0)
                                                    {
                                                        $residentData = $sqlGetUserName->fetch_row();
                                                        $RESNAME = $residentData[0];
                                                        if(!empty($residentData[1]))
                                                        {
                                                            $ResImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$residentData[1]";
                                                        }
                                                        elseif(empty($residentData[1]))
                                                        {
                                                            $ResImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $RESNAME = $EventData[8];
                                                    }
                                                    // Get block manager name.
                                                    $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = $EventData[9]");
                                                    if($sqlGetAptNum->num_rows > 0)
                                                    {
                                                        $AptNum = $sqlGetAptNum->fetch_row();
                                                        $APTNUM = $AptNum[0];
                                                        $FloorNum = $AptNum[1];
                                                        $APTNAME = $AptNum[2];
                                                    }
                                                    else
                                                    {
                                                        $APTNUM = $EventData[9];
                                                        $FloorNum = NULL;
                                                        $APTNAME = $EventData[9];
                                                    }
                                                    // Get Block number.
                                                     $sqlGetBLKNUM = $this->conn->query("SELECT BlockNum, BlockName FROM Block WHERE ID = $EventData[10]");
                                                    if($sqlGetBLKNUM->num_rows > 0)
                                                    {
                                                        $BLKNUM = $sqlGetBLKNUM->fetch_row();
                                                        $BlockNum = $BLKNUM[0];
                                                        $BlockName = $BLKNUM[1];
                                                    }
                                                    else
                                                    {
                                                        $BlockNum = $EventData[10];
                                                        $BlockName = $EventData[10];
                                                    }
                                                    
                                                    $AttendStatus = NULL;
                                                    // Get Attend Status.
                                                    $sqlGetAttendStatus = $this->conn->query("SELECT Attend, Absent FROM Attendees WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Event' AND RecordID = '$EventData[0]'");
                                                    if($sqlGetAttendStatus->num_rows > 0)
                                                    {
                                                        $AttendStatusArr = $sqlGetAttendStatus->fetch_row();
                                                        $Likes = $AttendStatusArr[0];
                                                        $DisLikes = $AttendStatusArr[1];
                                                        if($AttendStatusArr[0] > 0 && $AttendStatusArr[1] <= 0)
                                                        {
                                                            $AttendStatus = TRUE;
                                                        }
                                                        elseif($AttendStatusArr[1] >= 0 && $AttendStatusArr[0] <= 0)
                                                        {
                                                            $AttendStatus = FALSE;
                                                        }
                                                        elseif($AttendStatusArr[0] == 0 && $AttendStatusArr[1] == 0)
                                                        {
                                                            $AttendStatus = NULL;
                                                        }
                                                    }
                                                    
                                                    $Event["Record$count"] = 
                                                    [
                                                        "eventId" => $EventData[0],
                                                        "tittle" => $EventData[1],
                                                        "body" => $EventData[2],
                                                        "image" => $attachmentURL,
                                                        "date" => $EventData[5],
                                                        "eventLocation" => $EventData[6],
                                                        "numOfAttendees" => $EventData[7],
                                                        "attendStatus" => $AttendStatus,
                                                        "residentName" => $RESNAME,
                                                        "residentImage" => $ResImageUrl,
                                                        "apartmentNumber" => $APTNUM,
                                                        "apartmentName" => $APTNAME,
                                                        "floorNumber" => $FloorNum,
                                                        "blockNumber" => $BlockNum,
                                                        "blockName" => $BlockName,
                                                        "flagLastPage" => $FLP
                                                    ];
                                                    $count++;
                                                }
                                                // echo "<img src = '../ProfilePicturesTW9oYW1lZDYzZGQ2NGM4MDg3ZTIyLjE4NjYyNTA4.png' alt = 'image'/>";
                                                $this->returnResponse(200, array_values($Event));
                                            
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

    function showOffersAndADS() // OK
    {
        // // include("../Config.php");
       
            // Check Block Existence.
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
            $UserID = $decode->id;
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];

                // // Get Block ID and Apartment ID
                // $BLKID = $decode->apartmentsAndBlocks->record1->block;
                // $APTID = $decode->apartmentsAndBlocks->record1->apartment;

                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter BlockID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlCeckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCeckBlock->num_rows > 0)
                    {
                        $BlockStat = $sqlCeckBlock->fetch_row();
                        // Check Block Status
                        if($BlockStat[1] == '2')
                        {
                            // Check apartment Existence.
                            $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
                            if($sqlCheckAPT->num_rows <= 0)
                            {
                                $this->throwError(200, "apartment not found in block");
                            }
                            elseif($sqlCheckAPT->num_rows > 0)
                            {
                                $AptStat = $sqlCheckAPT->fetch_row();
                                // Check Apartment Status.
                                if($AptStat[1] == '2')
                                {
                                    // Select all Offers and ADs
                                    $sqlGetOffers = $this->conn->query("SELECT * FROM AdsAndOffers");
                                    if($sqlGetOffers->num_rows <= 0)
                                    {
                                        $this->returnResponse(200, []);
                                    }
                                    else
                                    {
                                        $count = 1;
                                        while($OfferData = $sqlGetOffers->fetch_row())
                                        {
                                            if(empty($OfferData[4]))
                                            {
                                                $attachmentURL = $this->RootUrl . "omartyapis/Images/AdsAndOffers/Default.png";
                                            }
                                            elseif(!empty($OfferData[4]))
                                            {
                                                $attachmentURL = $this->RootUrl . "omartyapis/Images/AdsAndOffers/" . $OfferData[4];
                                            }
                                            
                                            $Offer["Record$count"] = 
                                            [
                                                "tittle" => $OfferData[1],
                                                "body" => $OfferData[2],
                                                "owner" => $OfferData[3],
                                                "imageUrl" => $attachmentURL,
                                                "startDate" => $OfferData[5],
                                                "endDate" => $OfferData[6],
                                                "userID" => $OfferData[7],
                                                "createdAt" => $OfferData[8],
                                                
                                            ];
                                            $count++;
                                        }
                                        $this->returnResponse(200, array_values($Offer));
                                    }
                                }
                                elseif($AptStat[1] == '1')
                                {
                                    $this->throwError(200, "Apartment status is Binding.");
                                }
                                elseif($AptStat[1] == '3')
                                {
                                    $this->throwError(200, "Apartment is Banned.");
                                }
                                else
                                {
                                    $this->throwError(200, "Apartment status is not acceptable.");
                                }
                                
                            }
                        }
                        elseif($BlockStat[1] == '1')
                        {
                            $this->throwError(200, "Block status is Binding.");
                        }
                        elseif($BlockStat[1] == '3')
                        {
                            $this->throwError(200, "Block is Banned.");
                        }
                        else
                        {
                            $this->throwError(200, "Block status is not acceptable.");
                        }
                        
                    }
                    elseif($sqlCeckBlock->num_rows <= 0)
                    {
                        $this>throwError(200, "Block Not Found.");
                    }
                    
                }
    }

    function showBlock() // Check Search.
    {
        // // include("../Config.php");
        $BLKID = $_POST["blockId"];
        // $APTID = $_POST["apartmentId"];
        // $Search = $_POST["search"];
        $BMPN = $_POST["phoneNum"];
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
        
        try
        {
            // $Signature = new ReflectionClass("Login");

            $token = $this->getBearerToken();
            $secret = "secret123";
            // $Segnature = new ReflectionProperty("Login", "Signature");
            $decode = JWT::decode($token, new KEY($secret, 'HS256'));
        }
        catch(Exception $e)
        {
            $this->throwError(401, $e->getMessage());
        }

        if(empty($decode->id))
        {
            $this->throwError(403, "User not found. please enter your registered phone number");
        }
        elseif(!empty($decode->id))
        {
            // ========================================================================================================
            
            // ========================================================================================================
            
            if(!empty($BLKID))
            {
                $sqlblkData = $this->conn->query("SELECT * FROM Block WHERE ID = '$BLKID'");


                if($sqlblkData->num_rows > 0)
                {
                    $count = 1;
                    $arr = [];
                    while($BD = $sqlblkData->fetch_row())
                    {
                        // Get Image.
                        if(!empty($BD[4]))
                        {
                            $ImageUrl = $this->RootUrl . "omartyapis/Images/BlockImages/$BD[4]";    
                        }
                        if(empty($BD[4]))
                        {
                            $ImageUrl = $this->RootUrl . "omartyapis/Images/BlockImages/Default.jpg";
                        }
                        // Get Country Name.
                        $sqlGetCountryName = $this->conn->query("SELECT name FROM Country WHERE ID = '$BD[10]'");
                        if($sqlGetCountryName->num_rows > 0)
                        {
                            $CountryName = $sqlGetCountryName->fetch_Row();
                        }
                        elseif($sqlGetCountryName->num_rows <= 0)
                        {
                            $CountryName[0] = $BD[10];
                        }
                        // Get Governate Name.
                        $sqlGetGovName = $this->conn->query("SELECT GovName FROM Governate WHERE ID = '$BD[11]'");
                        if($sqlGetGovName->num_rows > 0)
                        {
                            $GovName = $sqlGetGovName->fetch_Row();
                        }
                        elseif($sqlGetGovName->num_rows <= 0)
                        {
                            $GovName[0] = $BD[11];
                        }
                        // Get City Name.
                        $sqlGetCityName = $this->conn->query("SELECT Name FROM City WHERE ID = '$BD[12]'");
                        if($sqlGetCityName->num_rows > 0)
                        {
                            $CityName = $sqlGetCityName->fetch_Row();
                        }
                        elseif($sqlGetCityName->num_rows <= 0)
                        {
                            $CityName[0] = $BD[12];
                        }
                        // Get Region Name.
                        $sqlGetRegionName = $this->conn->query("SELECT RegionName FROM Region WHERE ID = '$BD[13]'");
                        if($sqlGetRegionName->num_rows > 0)
                        {
                            $RegionName = $sqlGetRegionName->fetch_Row();
                        }
                        elseif($sqlGetRegionName->num_rows <= 0)
                        {
                            $RegionName[0] = $BD[13];
                        }
                        // Get Compound Name.
                        $sqlGetCompoundName = $this->conn->query("SELECT CompundName FROM Compound WHERE ID = '$BD[14]'");
                        if($sqlGetCompoundName->num_rows > 0)
                        {
                            $CompoundName = $sqlGetCompoundName->fetch_Row();
                        }
                        elseif($sqlGetCompoundName->num_rows <= 0)
                        {
                            $CompoundName[0] = $BD[14];
                        }
                        // Get Street Name.
                        $sqlGetStreetName = $this->conn->query("SELECT StreetName FROM Street WHERE ID = '$BD[15]'");
                        if($sqlGetStreetName->num_rows > 0)
                        {
                            $StreetName = $sqlGetStreetName->fetch_Row();
                        }
                        elseif($sqlGetStreetName->num_rows <= 0)
                        {
                            $StreetName[0] = $BD[15];
                        }
                        $arr[$count] = 
                        [
                            'id' => $BD[0],
                            'blockNumber' => $BD[1],
                            'blockName' => $BD[20],
                            'numberofapartments'=> $BD[2],
                            'numberOfFloors' => $BD[3],
                            "image" => $ImageUrl,
                            'password'=>$BD[5],
                            "balance" => $BD[6],
                            "fees" => $BD[7],
                            "longitude" => $BD[8],
                            "latitude" => $BD[9],
                            "countryId" => $BD[10],
                            "countryName" => $CountryName[0],
                            "governateId" => $BD[11],
                            "governateName" => $GovName[0],
                            "cityId" => $BD[12],
                            "cityName" => $CityName[0],
                            "regionId" => $BD[13],
                            "regionName" => $RegionName[0],
                            "compoundId" => $BD[14],
                            "compoundName" => $CompoundName[0],
                            "streetId" => $BD[15],
                            "streetName" => $StreetName[0],
                        ];
                        $count++;
                    }
                    
                    $this->returnResponse(200, array_values($arr));
                }

                else
                {
                    $this->returnResponse(200, []);
                }
            }
        // ===============================================================================================================
            elseif(empty($BLKID))
            {
                    if(!empty($BMPN))
                    {
                            // Get Blocks By Block Name.  Standing Here.
                            $sqlblkId = $this->conn->query("SELECT ID FROM Block WHERE BlockNum LIKE '%$BMPN%' LIMIT $Start, $Limit");
                            $sqlblkId2 = $this->conn->query("SELECT ID FROM Block WHERE BlockNum LIKE '%$BMPN%'");
                            if($sqlblkId->num_rows > 0)
                            {
                                $RowsNum = $sqlblkId2->num_rows;    
                            }
                            else
                            {
                                
                                // Get any resident in block ID By his Phone Number.
                                $sqlResID = $this->conn->query("SELECT ID FROM Resident_User WHERE PhoneNum LIKE '%$BMPN%'");
                                if($sqlResID->num_rows > 0)
                                {
                                    $BmId = $sqlResID->fetch_row();
                                    // Get Blocks with User ID 
                                    $sqlblkId = $this->conn->query("SELECT BlockID FROM RES_APART_BLOCK_ROLE WHERE ResidentID LIKE '%$BmId[0]%' LIMIT $Start, $Limit");
                                    $sqlblkId2 = $this->conn->query("SELECT BlockID FROM RES_APART_BLOCK_ROLE WHERE ResidentID LIKE '%$BmId[0]%'");
                                    $RowsNum = $sqlblkId2->num_rows;
                                }
                                    
                                // $BMPN = ucfirst($BMPN);
                                // Get Blocks By Country Name.
                                $sqlSearchByCountryName = $this->conn->query("SELECT ID FROM Country WHERE name LIKE '%%$BMPN%%'");
                                if($sqlSearchByCountryName->num_rows > 0)
                                {
                                        $CountryIDSearch =$sqlSearchByCountryName->fetch_row();
                                        // Get Blocks Where CountryID = @CountryIDSearch.
                                        // User Does not have Country data in table Resident_User.
                                        $sqlblkId = $this->conn->query("SELECT ID FROM Block WHERE CountryID = '$CountryIDSearch[0]' LIMIT $Start, $Limit");
                                        $sqlblkId2 = $this->conn->query("SELECT ID FROM Block WHERE CountryID = '$CountryIDSearch[0]'");
                                        $RowsNum = $sqlblkId2->num_rows;
                                    
                                }
                                
                                // Get Blocks By Governate Name.
                                $sqlSearchByGovName = $this->conn->query("SELECT ID FROM Governate WHERE GOVName LIKE '%$BMPN%'");
                                if($sqlSearchByGovName->num_rows > 0)
                                {
                                    $GovIDSearch =$sqlSearchByGovName->fetch_row();
                                    
                                    // Get Blocks Where CountryID = @CountryIDSearch.
                                    // User Does not have Country data in table Resident_User.
                                    $sqlblkId = $this->conn->query("SELECT ID FROM Block WHERE GovernateID = '$GovIDSearch[0]' LIMIT $Start, $Limit");
                                    $sqlblkId2 = $this->conn->query("SELECT ID FROM Block WHERE GovernateID = '$GovIDSearch[0]'");
                                    $RowsNum = $sqlblkId2->num_rows;
                                }
                                
                                // Get Blocks By City Name.
                                $sqlSearchByCityName = $this->conn->query("SELECT ID FROM City WHERE Name LIKE '%$BMPN%'");
                                if($sqlSearchByCityName->num_rows > 0)
                                {
                                    $CityIDSearch =$sqlSearchByCityName->fetch_row();
                                    $sqlblkId = $this->conn->query("SELECT ID FROM Block WHERE CityID = '$CityIDSearch[0]' LIMIT $Start, $Limit");
                                    $sqlblkId2 = $this->conn->query("SELECT ID FROM Block WHERE CityID = '$CityIDSearch[0]'");
                                    $RowsNum = $sqlblkId2->num_rows;
                                }
                                
                                // Get Blocks By Region Name.
                                $sqlSearchByRegionName = $this->conn->query("SELECT ID FROM Region WHERE RegionName LIKE '%$BMPN%'");
                                if($sqlSearchByRegionName->num_rows > 0)
                                {
                                    $RegionIDSearch =$sqlSearchByRegionName->fetch_row();
                                    $sqlblkId = $this->conn->query("SELECT ID FROM Block WHERE RegionID = '$RegionIDSearch[0]' LIMIT $Start, $Limit");
                                    $sqlblkId2 = $this->conn->query("SELECT ID FROM Block WHERE RegionID = '$RegionIDSearch[0]'");
                                    $RowsNum = $sqlblkId2->num_rows;
                                }
                                
                                // Get Blocks By Compound Name.
                                $sqlSearchByCompName = $this->conn->query("SELECT ID FROM Compound WHERE CompundName LIKE '%$BMPN%'");
                                if($sqlSearchByCompName->num_rows > 0)
                                {
                                    $CompIDSearch =$sqlSearchByCompName->fetch_row();
                                    $sqlblkId = $this->conn->query("SELECT ID FROM Block WHERE CompoundID = '$CompIDSearch[0]' LIMIT $Start, $Limit");
                                    $sqlblkId2 = $this->conn->query("SELECT ID FROM Block WHERE CompoundID = '$CompIDSearch[0]'");
                                    $RowsNum = $sqlblkId2->num_rows;
                                }
                                
                                // Get Blocks By Street Name.
                                $sqlSearchByStreetName = $this->conn->query("SELECT ID FROM Street WHERE StreetName LIKE '%$BMPN%'");
                                if($sqlSearchByStreetName->num_rows > 0)
                                {
                                    $StreetIDSearch =$sqlSearchByStreetName->fetch_row();
                                    $sqlblkId = $this->conn->query("SELECT ID FROM Block WHERE StreetID = '$StreetIDSearch[0]' LIMIT $Start, $Limit");
                                    $sqlblkId2 = $this->conn->query("SELECT ID FROM Block WHERE StreetID = '$StreetIDSearch[0]'");
                                    $RowsNum = $sqlblkId2->num_rows;
                                }     
                            }
                               
                                if($sqlblkId->num_rows > 0)
                                {
                                    $arr = [];
                                    $count = 1;
                                    while($BlkIds = $sqlblkId->fetch_row())
                                    {
                                        // Get Last page flag.
                                        if(($Limit + $Start) >= $RowsNum)
                                        {
                                            $FLP = 1;
                                        }
                                        elseif(($Limit + $Start) < $RowsNum)
                                        {
                                            $FLP = 0;
                                        }
                                        $sqlblkData = $this->conn->query("SELECT * FROM Block WHERE ID = '$BlkIds[0]'");
                                       
                                        while($BD = $sqlblkData->fetch_row())
                                        {
                                            // Get Image.
                                            if(!empty($BD[4]))
                                            {
                                                $ImageUrl = $this->RootUrl . "omartyapis/Images/BlockImages/$BD[4]";    
                                            }
                                            if(empty($BD[4]))
                                            {
                                                $ImageUrl = $this->RootUrl . "omartyapis/Images/BlockImages/Default.jpg";
                                            }
                                            // Get Country Name.
                                            $sqlGetCountryName = $this->conn->query("SELECT name FROM Country WHERE ID = '$BD[10]'");
                                            if($sqlGetCountryName->num_rows > 0)
                                            {
                                                $CountryName = $sqlGetCountryName->fetch_Row();
                                            }
                                            elseif($sqlGetCountryName->num_rows <= 0)
                                            {
                                                $CountryName[0] = $BD[10];
                                            }
                                            // Get Governate Name.
                                            $sqlGetGovName = $this->conn->query("SELECT GovName FROM Governate WHERE ID = '$BD[11]'");
                                            if($sqlGetGovName->num_rows > 0)
                                            {
                                                $GovName = $sqlGetGovName->fetch_Row();
                                            }
                                            elseif($sqlGetGovName->num_rows <= 0)
                                            {
                                                $GovName[0] = $BD[11];
                                            }
                                            // Get City Name.
                                            $sqlGetCityName = $this->conn->query("SELECT Name FROM City WHERE ID = '$BD[12]'");
                                            if($sqlGetCityName->num_rows > 0)
                                            {
                                                $CityName = $sqlGetCityName->fetch_Row();
                                            }
                                            elseif($sqlGetCityName->num_rows <= 0)
                                            {
                                                $CityName[0] = $BD[12];
                                            }
                                            // Get Region Name.
                                            $sqlGetRegionName = $this->conn->query("SELECT RegionName FROM Region WHERE ID = '$BD[13]'");
                                            if($sqlGetRegionName->num_rows > 0)
                                            {
                                                $RegionName = $sqlGetRegionName->fetch_Row();
                                            }
                                            elseif($sqlGetRegionName->num_rows <= 0)
                                            {
                                                $RegionName[0] = $BD[13];
                                            }
                                            // Get Compound Name.
                                            $sqlGetCompoundName = $this->conn->query("SELECT CompundName FROM Compound WHERE ID = '$BD[14]'");
                                            if($sqlGetCompoundName->num_rows > 0)
                                            {
                                                $CompoundName = $sqlGetCompoundName->fetch_Row();
                                            }
                                            elseif($sqlGetCompoundName->num_rows <= 0)
                                            {
                                                $CompoundName[0] = $BD[14];
                                            }
                                            // Get Street Name.
                                            $sqlGetStreetName = $this->conn->query("SELECT StreetName FROM Street WHERE ID = '$BD[15]'");
                                            if($sqlGetStreetName->num_rows > 0)
                                            {
                                                $StreetName = $sqlGetStreetName->fetch_Row();
                                            }
                                            elseif($sqlGetStreetName->num_rows <= 0)
                                            {
                                                $StreetName[0] = $BD[15];
                                            }
                                            $arr[$count] = 
                                            [
                                                'id' => $BD[0],
                                                'blockNumber' => $BD[1],
                                                'blockName' => $BD[20],
                                                'numberofapartments'=> $BD[2],
                                                'numberOfFloors' => $BD[3],
                                                "image" => $ImageUrl,
                                                'password'=>$BD[5],
                                                "balance" => $BD[6],
                                                "fees" => $BD[7],
                                                "longitude" => $BD[8],
                                                "latitude" => $BD[9],
                                                "countryId" => $BD[10],
                                                "countryName" => $CountryName[0],
                                                "governateId" => $BD[11],
                                                "governateName" => $GovName[0],
                                                "cityId" => $BD[12],
                                                "cityName" => $CityName[0],
                                                "regionId" => $BD[13],
                                                "regionName" => $RegionName[0],
                                                "compoundId" => $BD[14],
                                                "compoundName" => $CompoundName[0],
                                                "streetId" => $BD[15],
                                                "streetName" => $StreetName[0],
                                                "flagLastPage" => $FLP
                                            ];
                                            $count++;
                                        }
                                        
                                     
                                    }
                                       $this->returnResponse(200, array_values($arr));
                                }
                                else
                                {
                                    $this->returnResponse(200, []);
                                }
                        
                    }
                    
                    if(empty($BMPN))
                    {
                        // Get Block Manager ID By his Phone Number.
                        $sqlResID = $this->conn->query("SELECT ID FROM Resident_User WHERE PhoneNum LIKE '%$BMPN%'");
                        
                        if($sqlResID->num_rows > 0)
                        {
                            $BmId = $sqlResID->fetch_row();
                            // Get All Blocks.
                            $sqlblkId = $this->conn->query("SELECT ID FROM Block LIMIT $Start, $Limit");
                            $sqlblkId2 = $this->conn->query("SELECT ID FROM Block");
                            $RowsNum = $sqlblkId2->num_rows;
                            if($sqlblkId->num_rows > 0)
                            {
                                $arr = [];
                                $count = 1;
                                while($BlkIds = $sqlblkId->fetch_row())
                                {
                                    // Get Last page flag.
                                    if(($Limit + $Start) >= $RowsNum)
                                    {
                                        $FLP = 1;
                                    }
                                    elseif(($Limit + $Start) < $RowsNum)
                                    {
                                        $FLP = 0;
                                    }
                                    $sqlblkData = $this->conn->query("SELECT * FROM Block WHERE ID = '$BlkIds[0]'");
                                   
                                    while($BD = $sqlblkData->fetch_row())
                                    {
                                        // Get Image.
                                        if(!empty($BD[4]))
                                        {
                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/BlockImages/$BD[4]";    
                                        }
                                        if(empty($BD[4]))
                                        {
                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/BlockImages/Default.jpg";
                                        }
                                        // Get Country Name.
                                        $sqlGetCountryName = $this->conn->query("SELECT name FROM Country WHERE ID = '$BD[10]'");
                                        if($sqlGetCountryName->num_rows > 0)
                                        {
                                            $CountryName = $sqlGetCountryName->fetch_Row();
                                        }
                                        elseif($sqlGetCountryName->num_rows <= 0)
                                        {
                                            $CountryName[0] = $BD[10];
                                        }
                                        // Get Governate Name.
                                        $sqlGetGovName = $this->conn->query("SELECT GovName FROM Governate WHERE ID = '$BD[11]'");
                                        if($sqlGetGovName->num_rows > 0)
                                        {
                                            $GovName = $sqlGetGovName->fetch_Row();
                                        }
                                        elseif($sqlGetGovName->num_rows <= 0)
                                        {
                                            $GovName[0] = $BD[11];
                                        }
                                        // Get City Name.
                                        $sqlGetCityName = $this->conn->query("SELECT Name FROM City WHERE ID = '$BD[12]'");
                                        if($sqlGetCityName->num_rows > 0)
                                        {
                                            $CityName = $sqlGetCityName->fetch_Row();
                                        }
                                        elseif($sqlGetCityName->num_rows <= 0)
                                        {
                                            $CityName[0] = $BD[12];
                                        }
                                        // Get Region Name.
                                        $sqlGetRegionName = $this->conn->query("SELECT RegionName FROM Region WHERE ID = '$BD[13]'");
                                        if($sqlGetRegionName->num_rows > 0)
                                        {
                                            $RegionName = $sqlGetRegionName->fetch_Row();
                                        }
                                        elseif($sqlGetRegionName->num_rows <= 0)
                                        {
                                            $RegionName[0] = $BD[13];
                                        }
                                        // Get Compound Name.
                                        $sqlGetCompoundName = $this->conn->query("SELECT CompundName FROM Compound WHERE ID = '$BD[14]'");
                                        if($sqlGetCompoundName->num_rows > 0)
                                        {
                                            $CompoundName = $sqlGetCompoundName->fetch_Row();
                                        }
                                        elseif($sqlGetCompoundName->num_rows <= 0)
                                        {
                                            $CompoundName[0] = $BD[14];
                                        }
                                        // Get Street Name.
                                        $sqlGetStreetName = $this->conn->query("SELECT StreetName FROM Street WHERE ID = '$BD[15]'");
                                        if($sqlGetStreetName->num_rows > 0)
                                        {
                                            $StreetName = $sqlGetStreetName->fetch_Row();
                                        }
                                        elseif($sqlGetStreetName->num_rows <= 0)
                                        {
                                            $StreetName[0] = $BD[15];
                                        }
                                        $arr[$count] = 
                                        [
                                            'id' => $BD[0],
                                            'blockNumber' => $BD[1],
                                            'blockName' => $BD[20],
                                            'numberofapartments'=> $BD[2],
                                            'numberOfFloors' => $BD[3],
                                            "image" => $BD[4],
                                            'password'=>$BD[5],
                                            "balance" => $BD[6],
                                            "fees" => $BD[7],
                                            "longitude" => $BD[8],
                                            "latitude" => $BD[9],
                                            "countryId" => $BD[10],
                                            "countryName" => $CountryName[0],
                                            "governateId" => $BD[11],
                                            "governateName" => $GovName[0],
                                            "cityId" => $BD[12],
                                            "cityName" => $CityName[0],
                                            "regionId" => $BD[13],
                                            "regionName" => $RegionName[0],
                                            "compoundId" => $BD[14],
                                            "compoundName" => $CompoundName[0],
                                            "streetId" => $BD[15],
                                            "streetName" => $StreetName[0],
                                            "flagLastPage" => $FLP
                                        ];
                                        $count++;
                                    }
                                    
                                 
                                }
                                   $this->returnResponse(200, array_values($arr));
                            }
                            else
                            {
                                $this->returnResponse(200, []);
                            }
        
                        }
                        else
                        {
                            $this->returnResponse(200, []);
                        }
                        
                    }
            }
        // ===============================================================================================================

        }  
    }

    function showApartment()
    {
        // // include("../Config.php");
        
        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new KEY($secret, 'HS256'));
        }
        catch(Exception $e)
        {
            $this->throwError(401, $e->getMessage());
            exit;
        }
        
        // Get User ID.
        $UserID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        
        // Check Block Existence.
        $sqlCheckBlock = $this->conn->query("SELECT BlockID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
        if($sqlCheckBlock->num_rows > 0)
        {
            
            if(empty($UserID))
            {
                // If User Was not found.
                $this->throwError(403, "User not found. please enter your registered phone number");
                exit;
            }
            elseif(!empty($UserID))
            {
                // if user were found.

                // get Apartment Data from RES_APART_BLOCK_ROLE.

                if(empty($APTID))
                {
                    // if apartment was not found.
                    $this->throwError(200, "Please Enter Apartment ID");
                    exit;
                }
                elseif(!empty($APTID))
                {
                    $sqlGetapt = $this->conn->query("SELECT StatusID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                    // Check Apartment Status
                    if($sqlGetapt->num_rows > 0)
                    {
                        $AptStatus = $sqlGetapt->fetch_row();
                        if($AptStatus[0] == '2')
                        {
                            // get apartment data.
                            $APTCount = 0;
                            $APTBan = 0;
                            $arr = [];
                            $count = 1;
                            $sqlGetaptDataI = $this->conn->query("SELECT ApartmentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$UserID'");
                            while($APTStatus = $sqlGetaptDataI->fetch_row())
                            {
                                $Status = $APTStatus[1];
                                // Check Watchman existence.
                                $sqlGetWatchMan = $this->conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = 7");
                                if($sqlGetWatchMan->num_rows > 0)
                                {
                                    // Get Watchman Data.
                                    $WtchManID = $sqlGetWatchMan->fetch_row();
                                    $sqlGetWMData = $this->conn->query("SELECT Name, PhoneNum FROM Resident_User WHERE ID = '$WtchManID[0]'");
                                    if($sqlGetWMData->num_rows > 0)
                                    {
                                        $WtchManData = $sqlGetWMData->fetch_row();
                                        $WMName = $WtchManData[0];
                                        $WMPN = $WtchManData[1];
                                    }
                                }
                                elseif($sqlGetWatchMan->num_rows <= 0)
                                {
                                    $WMName = "";
                                    $WMPN = "";
                                }
                                // Check BlockManager Data.
                                $sqlGetBM = $this->conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = 1");
                                if($sqlGetBM->num_rows > 0)
                                {
                                    // Get BlockManager Data.
                                    $BMID = $sqlGetBM->fetch_row();
                                    $sqlGetWMData = $this->conn->query("SELECT Name, PhoneNum FROM Resident_User WHERE ID = '$BMID[0]'");
                                    if($sqlGetWMData->num_rows > 0)
                                    {
                                        $BMData = $sqlGetWMData->fetch_row();
                                        $BMName = $BMData[0];
                                        $BMPN = $BMData[1];
                                    }
                                }
                                elseif($sqlGetBM->num_rows <= 0)
                                {
                                    $BMName = "";
                                    $BMPN = "";
                                }
                                // ===============================================================================================
                                // Block Payment Methods
                                // Get Payment Methods from table BlockPaymentMethod.
                                // @PM => PaymentMethod
                                $PMArr = [];
                                $sqlGetBLKPM = $this->conn->query("SELECT * FROM BlockPaymentMethod WHERE BlockID = '$BLKID'");
                                if($sqlGetBLKPM->num_rows > 0)
                                {
                                    $PM = $sqlGetBLKPM->fetch_row();
                                    $BLKPayMethods = explode (",", $PM[3]);
                                    foreach($BLKPayMethods as $BPM)
                                    {
                                        $PMArr += [$PM[2] => $BPM];
                                    }
                                }
                                // ===============================================================================================
                                if($Status == 2)
                                {
                                    $sqlAPTData = $this->conn->query("SELECT * FROM Apartment WHERE ID = '$APTID'");
                                    $AptData = $sqlAPTData->fetch_row();
                                        $arr[$count] = [
                                            'id' => $AptData[0],
                                            'floorNumber'=> $AptData[1],
                                            "apartmentNumber" => $AptData[2],
                                            "apartmentName" => $AptData[10],
                                            "balance" => $AptData[3],
                                            "fees" => $AptData[4],
                                            "blockId" => $AptData[5],
                                            "watchmanName" => $WMName,
                                            "watchmanPhoneNumber" => $WMPN,
                                            "blockManagerName" => $BMName,
                                            "blockManagerPhoneNumber" => $BMPN,
                                            "blockPaymentMethods" => $PMArr
                                        ];
                                }
                                elseif($Status == 1)
                                {
                                    $APTCount++;
                                }
                                elseif($Status == 3)
                                {
                                    $APTBan++;
                                }
                                if($APTCount > 0)
                                {
                                    $arr +=["binding" => "You have $APTCount apartment Status is Binding"];
                                }
                                if($APTBan > 0)
                                {
                                    $arr +=["banned" => "You have $APTBan apartment Status is Banned by Omarty Super Admin"];
                                }
                            }
                            $this->returnResponse(200, array_values($arr));
                            exit;
                        }
                        elseif($AptStatus[0] == '1')
                        {
                            $this->throwError(200, "Apartment Status is binding.");
                        }
                        elseif($AptStatus[0] == '3')
                        {
                            $this->throwError(200, "Apartment is Banned.");
                        }
                        else
                        {
                            $this->throwError(200, "Apartment status is not acceptable.");
                        }
                    }
                    else
                    {
                        $this->returnResponse(200, []);
                    }

                }
            }
        }
        else
        {
            $this->throwError(200, "This resident doesn't have any apartments, please contact your block manager.");
        }
    }

    function showResident() // OK Final (Show all personal Data and all apartments and blocks and his relation and status to it)
    {
        // include("../Config.php");
        $token = $this->getBearerToken();
        try
        {
            $secret = "secret123";
            $decode = JWT::decode($token, new KEY($secret, 'HS256'));
        }
        catch(Exception $e)
        {
            $this->throwError(401, $e->getMessage());
        }

        if(empty($decode->id))
        {
            $this->throwError(403, "User not found. please enter your registered phone number");
        }
        elseif(!empty($decode->id))
        {
            $UserID = $decode->id;
            // $BLKID = $decode->apartmentsAndBlocks->record1->block;
            $sqlGetAll = $this->conn->query("SELECT ID, Name, UserName, Email, PhoneNum, Image, MartialStatus FROM Resident_User WHERE ID = $UserID");
            $Col = $sqlGetAll->fetch_row();

            // Get User Apartments and Blocks and Roles.
            $sqlGetRES = $this->conn->query("SELECT * FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$UserID'");
            $REL = [];
            $count = 1;
            // Resident Apartments, Blocks, and their status and his role in them.
            while($RESDATA = $sqlGetRES->fetch_row())
            {
                try
                {
                    // Get Apartment Status.
                    $sqlGetUserStatus = $this->conn->query("SELECT Name FROM Status Where ID = '$RESDATA[5]'");
                    $UserStatus = $sqlGetUserStatus->fetch_row();
                }
                catch(Exception $e)
                {
                    $this->throwError(200, $e->getMessage());
                }
    
                try
                {
                    // Get Resident Role.
                    $sqlGetRoleName = $this->conn->query("SELECT RoleName FROM Role WHERE ID = '$RESDATA[4]'");
                    $RoleName = $sqlGetRoleName->fetch_row();
                }
                catch(Exception $e)
                {
                    $this->throwError(200, $e->getMessage());
                }
                
                // Get ApartmentNumber, Name And floor number, .
                $sqlGetFloorNum = $this->conn->query("SELECT FloorNum, ApartmentNumber, ApartmentName FROM Apartment WHERE ID = '$RESDATA[2]'");
                if($sqlGetFloorNum->num_rows > 0)
                {
                    $FloorNum = $sqlGetFloorNum->fetch_row();
                    $FloorNumVar = $FloorNum[0];
                    $apartmentNum = $FloorNum[1];
                    $apartmentName = $FloorNum[2];
                }
                else
                {
                    $FloorNumVar = NULL;
                    $apartmentNum = NULL;
                    $apartmentName = NULL;
                }
                
                $REL["record$count"] = 
                [
                    "apartment" => "$RESDATA[2]",
                    "apartmentNumber" => "$apartmentNum",
                    "apartmentName" => "$apartmentName",
                    "floorNumber" => "$FloorNumVar",
                    "block" => "$RESDATA[3]",
                    "status" => "$UserStatus[0]",
                    "role" => "$RoleName[0]"
                ];
                $count++;
            }
                // Get Profile Picture Url
                if(!empty($Col[5]))
                {
                    $ImgaeUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$Col[5]";
                }
                if(empty($Col[5]))
                {
                    $ImgaeUrl = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                }
                // Check If Email is not hidden.
                $sqlCheckEmailHidden = $this->conn->query("SELECT ID FROM Resident_User WHERE ID = '$UserID' AND (Hide = 1 OR Hide = 3)");
                if($sqlCheckEmailHidden->num_rows > 0)
                {
                    $Email = "Hidden";
                }
                elseif($sqlCheckEmailHidden->num_rows <= 0)
                {
                    $Email = $Col[3];
                }
                // Check If PhoneNum is not hidden.
                $sqlCheckPNHidden = $this->conn->query("SELECT ID FROM Resident_User WHERE ID = '$UserID' AND (Hide = 2 OR Hide = 3)");
                if($sqlCheckPNHidden->num_rows > 0)
                {
                    $PN = "Hidden";
                }
                elseif($sqlCheckPNHidden->num_rows <= 0)
                {
                    $PN = $Col[4];
                }
                // Get Non hidden Contacts.
                    // Get PhoneNums.
                    $sqlGetPN = $this->conn->query("SELECT PhoneNum, ID FROM PhoneNums WHERE UserID = '$UserID' AND Hide = 0");
                    $SecondaryPNs = [];
                    $count = 1;
                    while($SecondPNs = $sqlGetPN->fetch_row())
                    {
                        $SecondaryPNs[$count] = ["id" => $SecondPNs[1], "phoneNumber" => $SecondPNs[0]];
                        $count++;
                    }
                    // Get Emails.
                    $sqlGetEmail = $this->conn->query("SELECT Email, ID FROM Emails WHERE UserID = '$UserID' AND Hide = 0");
                    $SecondaryEmails = [];
                    $count = 1;
                    while($Secondemails = $sqlGetEmail->fetch_row())
                    {
                        $SecondaryEmails[$count] = ["id" => $Secondemails[1], "email" => $Secondemails[0]];
                        $count++;
                    }
                    
            $arr = [
                'id' => $Col[0],
                'name'=> $Col[1],
                "userName" => $Col[2],
                "email" => $Email,
                "secondaryEmails" => array_values($SecondaryEmails),
                "phoneNumber" => $PN,
                "secondaryPhoneNumbers" => array_values($SecondaryPNs),
                "image" => $ImgaeUrl,
                "martialStatus" => $Col[6],
                "residentApartments" => array_values($REL),
            ];
            $this->returnResponse(200, $arr);
        }
    }
    
    function showMyProfile() // OK Final (Show all personal Data and all apartments and blocks and his relation and status to it)
    {
        // include("../Config.php");
        $token = $this->getBearerToken();
        try
        {
            $secret = "secret123";
            $decode = JWT::decode($token, new KEY($secret, 'HS256'));
        }
        catch(Exception $e)
        {
            $this->throwError(401, $e->getMessage());
        }

        if(empty($decode->id))
        {
            $this->throwError(403, "User not found. please enter your registered phone number");
        }
        elseif(!empty($decode->id))
        {
            $UserID = $decode->id;
            // $BLKID = $decode->apartmentsAndBlocks->record1->block;
            $sqlGetAll = $this->conn->query("SELECT ID, Name, UserName, Email, PhoneNum, Image, MartialStatus FROM Resident_User WHERE ID = $UserID");
            $Col = $sqlGetAll->fetch_row();

            // Get User Apartments and Blocks and Roles.
            $sqlGetRES = $this->conn->query("SELECT * FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$UserID'");
            $REL = [];
            $count = 1;
            // Resident Apartments, Blocks, and their status and his role in them.
            while($RESDATA = $sqlGetRES->fetch_row())
            {
                try
                {
                    // Get Apartment Status.
                    $sqlGetUserStatus = $this->conn->query("SELECT Name FROM Status Where ID = '$RESDATA[5]'");
                    $UserStatus = $sqlGetUserStatus->fetch_row();
                }
                catch(Exception $e)
                {
                    $this->throwError(200, $e->getMessage());
                }
    
                try
                {
                    // Get Resident Role.
                    $sqlGetRoleName = $this->conn->query("SELECT RoleName FROM Role WHERE ID = '$RESDATA[4]'");
                    $RoleName = $sqlGetRoleName->fetch_row();
                }
                catch(Exception $e)
                {
                    $this->throwError(200, $e->getMessage());
                }
                
                try
                {
                    // Get Apartment floor number.
                    $sqlGetFloorNum = $this->conn->query("SELECT FloorNum, ApartmentNumber, ApartmentName FROM Apartment WHERE ID = '$RESDATA[2]'");
                    $FloorNum = $sqlGetFloorNum->fetch_row();
                }
                catch(Exception $e)
                {
                    $this->throwError(200, $e->getMessage());
                }
                
                $REL["record$count"] = ["apartment" => "$RESDATA[2] ", "ApartmentNumber" => "$FloorNum[1]", "apartmentName" => "$FloorNum[2]", "floorNumber" => "$FloorNum[0]", "block" => "$RESDATA[3]", "status" => "$UserStatus[0]", "role" => "$RoleName[0]" ];
                $count++;
            }
                // Get Profile Picture Url
                if(!empty($Col[5]))
                {
                    $ImgaeUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$Col[5]";
                }
                if(empty($Col[5]))
                {
                    $ImgaeUrl = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                }
                // Get Non hidden Contacts.
                    // Get PhoneNums.
                    $sqlGetPN = $this->conn->query("SELECT PhoneNum, ID FROM PhoneNums WHERE UserID = '$UserID'");
                    $SecondaryPNs = [];
                    $count = 1;
                    while($SecondPNs = $sqlGetPN->fetch_row())
                    {
                        $SecondaryPNs[$count] = ["id" => $SecondPNs[1], "phoneNumber" => $SecondPNs[0]];
                        $count++;
                    }
                    // Get Emails.
                    $sqlGetEmail = $this->conn->query("SELECT Email, ID FROM Emails WHERE UserID = '$UserID'");
                    $SecondaryEmails = [];
                    $count = 1;
                    while($Secondemails = $sqlGetEmail->fetch_row())
                    {
                        $SecondaryEmails[$count] = ["id" => $Secondemails[1], "email" => $Secondemails[0]];
                        $count++;
                    }
                    
            $arr = [
                'id' => $Col[0],
                'name'=> $Col[1],
                "userName" => $Col[2],
                "email" => $Col[3],
                "secondaryEmails" => array_values($SecondaryEmails),
                "phoneNumber" => $Col[4],
                "secondaryPhoneNumbers" => array_values($SecondaryPNs),
                "image" => $ImgaeUrl,
                "martialStatus" => $Col[6],
                "residentApartments" => array_values($REL),
            ];
            $this->returnResponse(200, $arr);
        }
    }

    function showService() // OK Final
    {
        // include("../Config.php");
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
            
        // Get Block ID and Apartment ID
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $catID = $_POST["categoryId"];
        $CompoundID = $_POST["compoundId"];
        $RegionID = $_POST["regionId"];
        $CityID = $_POST["cityId"];
        $UserID = $decode->id;
        if(empty($BLKID))
        {
            $this->throwError(200, "Block Not found.");
        }
        // Check Block existence.
        $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID, CityID, RegionID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows > 0)
        {
            // Check Resident Relation to Block.
            $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                $blockData = $sqlCheckBlock->fetch_row();
                // Check Block Status.
                if($blockData[1] == '2')
                {
                    // Check apartment Existence.
                    $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                    if($sqlCheckAPT->num_rows <= 0)
                    {
                        $this->throwError(200, "apartment not found in block");
                    }
                    elseif($sqlCheckAPT->num_rows > 0)
                    {
                        $AptData = $sqlCheckAPT->fetch_row();
                        // Check Resident relation to the apartment
                        if($AptData[2] == $UserID)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == '2')
                            {
                                /**
                                * 
                                * What to search by??
                                * Region?
                                * City?
                                * 
                                * Already Made it With Region.
                                * 
                                */
                                // Check Apartment Status.
            
                                // Select all Services with the same city id if no category were entered.
                                if(empty($catID))
                                {
                                    $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' LIMIT $Start, $Limit");
                                    $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]'");
                                    $RowsNum = $sqlGetService2->num_rows;
                                    if(empty($RegionID) && empty($CompoundID))
                                    {
                                        $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' LIMIT $Start, $Limit");
                                        $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]'");
                                        $RowsNum = $sqlGetService2->num_rows;
                                        // if($sqlGetService->num_rows <= 0)
                                        // {
                                        //     $this->throwError(200, "There are no services with these credintials.");
                                        // }
                                    }
                                    if(!empty($RegionID) && !empty($CompoundID))
                                    {
                                        $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$RegionID' AND CompoundID = '$CompoundID' LIMIT $Start, $Limit");
                                        $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$RegionID' AND CompoundID = '$CompoundID'");
                                        $RowsNum = $sqlGetService2->num_rows;
                                        // if($sqlGetService->num_rows <= 0)
                                        // {
                                        //     $this->throwError(200, "There are no services with these credintials. Compound, Region");
                                        // }
                                    }
                                    if(empty($RegionID) && !empty($CompoundID))
                                    {
                                        $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' AND CompoundID = '$CompoundID' LIMIT $Start, $Limit");
                                        $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' AND CompoundID = '$CompoundID'");
                                        $RowsNum = $sqlGetService2->num_rows;
                                        // if($sqlGetService->num_rows <= 0)
                                        // {
                                        //     $this->throwError(200, "There are no services with these credintials. Only Compound");
                                        // }
                                    }
                                    if(!empty($RegionID) && empty($CompoundID))
                                    {
                                        $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$RegionID' LIMIT $Start, $Limit");
                                        $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$RegionID'");
                                        $RowsNum = $sqlGetService2->num_rows;
                                        // if($sqlGetService->num_rows > 0)
                                        // {
                                        //     $this->throwError(200, "There are no services with these credintials. Only region");
                                        // }
                                    }
                                    // if($sqlGetService->num_rows <=0)
                                    // {
                                        //     $this->throwError(200, "Sorry found no Services with these credintials.");
                                        //     exit;
                                        // }
                                }
                                // ========================================================================================================
                                // Select all Services with the same city id with the specified category ID.
                                elseif(!empty($catID))
                                {
                                    $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' AND CategoryID = '$catID' LIMIT $Start, $Limit");
                                    $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' AND CategoryID = '$catID'");
                                    $RowsNum = $sqlGetService2->num_rows;
                                    
                                    if(empty($RegionID) && empty($CompoundID))
                                    {
                                        $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' AND CategoryID = '$catID' LIMIT $Start, $Limit");
                                        $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' AND CategoryID = '$catID'");
                                        $RowsNum = $sqlGetService2->num_rows;
                                        // if($sqlGetService->num_rows <= 0)
                                        // {
                                        //     $this->throwError(200, "There are no services with these credintials.");
                                        // }
                                    }
                                    if(!empty($RegionID) && !empty($CompoundID))
                                    {
                                        $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$RegionID' AND CompoundID = '$CompoundID' AND CategoryID = '$catID' LIMIT $Start, $Limit");
                                        $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$RegionID' AND CompoundID = '$CompoundID' AND CategoryID = '$catID'");
                                        $RowsNum = $sqlGetService2->num_rows;
                                        // if($sqlGetService->num_rows <= 0)
                                        // {
                                        //     $this->throwError(200, "There are no services with these credintials. Compound, Region");
                                        // }
                                    }
                                    if(empty($RegionID) && !empty($CompoundID))
                                    {
                                        $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' AND CompoundID = '$CompoundID' AND CategoryID = '$catID' LIMIT $Start, $Limit");
                                        $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' AND CompoundID = '$CompoundID' AND CategoryID = '$catID'");
                                        $RowsNum = $sqlGetService2->num_rows;
                                        // if($sqlGetService->num_rows <= 0)
                                        // {
                                        //     $this->throwError(200, "There are no services with these credintials. Only Compound");
                                        // }
                                    }
                                    if(!empty($RegionID) && empty($CompoundID))
                                    {
                                        $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$RegionID' AND CategoryID = '$catID' LIMIT $Start, $Limit");
                                        $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$RegionID' AND CategoryID = '$catID'");
                                        $RowsNum = $sqlGetService2->num_rows;
                                        // if($sqlGetService->num_rows <= 0)
                                        // {
                                        //     $this->throwError(200, "There are no services with these credintials. Only region");
                                        // }
                                    }
    
                                }
                                /*
                                if($sqlGetService->num_rows <= 0)
                                {
                                    if(empty($catID))
                                    {
                                        $this->returnResponse(200, "City doesn't have Services added yet.");
                                    }
                                    // Check Category existence.
                                    elseif(!empty($catID))
                                    {
                                        // Get Service Category Name.
                                        $sqlCheckCat = $this->conn->query("SELECT Name FROM ServiceCategory WHERE ID = '$catID'");
                                        if($sqlCheckCat->num_rows > 0)
                                        {
                                            $catName = $sqlCheckCat->fetch_row();
                                            $this->returnResponse(200, "City doesn't have Services Of type $catName[0] yet.");
                                        }
                                        elseif($sqlCheckCat->num_rows <= 0)
                                        {
                                            $this->returnResponse(200, "Category ID does not exist.");
                                        }
                                    }
                                        
                                }
                                */
                                
                                    $count = 1;
                                    while($ServiceData = $sqlGetService->fetch_row())
                                    {
                                        // Get Last page flag.
                                        if(($Limit + $Start) >= $RowsNum)
                                        {
                                            $FLP = 1;
                                        }
                                        elseif(($Limit + $Start) < $RowsNum)
                                        {
                                            $FLP = 0;
                                        }
                                        
                                        // Check Comments
                                        $ComArr = [];
                                        $CommentCounter = 1;
                                        // Get Service Comments.
                                        $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$ServiceData[0]' AND OriginalPostTable = 'Service'");
                                        $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$ServiceData[0]' AND OriginalPostTable = 'Service'");
                                        $RowsNum2 = $sqlGetComment2->num_rows;
                                        while($Comment = $sqlGetComment->fetch_row())
                                        {
                                            // Get Last page flag.
                                            if(($Limit + $Start) >= $RowsNum2)
                                            {
                                                $FLPC = 1;
                                            }
                                            elseif(($Limit + $Start) < $RowsNum2)
                                            {
                                                $FLPC = 0;
                                            }
                                            // Get Resident Name Image 
                                            $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                            if($sqlGetRes->num_rows > 0)
                                            {
                                                $ResDT = $sqlGetRes->fetch_row();
                                                $ResName = $ResDT[0];
                                                if(!empty($ResDT[1]))
                                                {
                                                    $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/$ResDT[1]";
                                                }
                                                elseif(empty($ResDT[1]))
                                                {
                                                    $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                }
                                            }
                                            elseif($sqlGetRes->num_rows <= 0)
                                            {
                                                $ResName = $Comment[2];
                                                $Resimage = "";
                                            }
                                            // Get Apartment Num and Apartment Floor Num.
                                            $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName, BlockID FROM Apartment WHERE ID = '$Comment[3]'");
                                            if($sqlGetAptData->num_rows > 0)
                                            {
                                                $AptDataC = $sqlGetAptData->fetch_row();
                                                $AptNumC = $AptDataC[0];
                                                $AptFloorNumC = $AptDataC[1];
                                                $AptNameC = $AptDataC[2];
                                            }
                                            if($sqlGetAptData->num_rows <= 0)
                                            {
                                                $AptNumC = $Comment[3];
                                                $AptFloorNumC = $Comment[3];
                                                $AptNameC = $Comment[3];
                                            }
                                            
                                            // Get Block Number and name.
                                            $sqlGetBlockName = $this->conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$AptDataC[3]'");
                                            if($sqlGetBlockName->num_rows > 0)
                                            {
                                                $BlkDataC = $sqlGetBlockName->fetch_row();
                                                $BlkIdC = $BlkDataC[0];
                                                $BlkNumC = $BlkDataC[0];
                                                $BlkNameC = $BlkDataC[1];
                                            }
                                            if($sqlGetBlockName->num_rows <= 0)
                                            {
                                                $BlkIdC = NULL;
                                                $BlkNumC = NULL;
                                                $BlkNameC = NULL;
                                            }
                                            
                                            // Get Vote Status.
                                            $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                            if($sqlGetVoteStatus->num_rows > 0)
                                            {
                                                $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                $Likes = $VoteStatusArr[0];
                                                $DisLikes = $VoteStatusArr[1];
                                                if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                {
                                                    $VoteStatus = TRUE;
                                                }
                                                elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                {
                                                    $VoteStatus = FALSE;
                                                }
                                                elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                {
                                                    $VoteStatus = NULL;
                                                }
                                            }
                                            elseif($sqlGetVoteStatus->num_rows <= 0)
                                            {
                                                $VoteStatus = NULL;
                                            }
                                            
                                            $ComArr[$CommentCounter] = 
                                            [
                                                "commentId" => $Comment[0],
                                                "comment" => $Comment[1],
                                                "residentId" => $Comment[2],
                                                "residentName" => $ResName,
                                                "residentImage" => $ResImage,
                                                "apartmentId" => $Comment[3],
                                                "apartmentNumber" => $AptNumC,
                                                "apartmentName" => $AptNameC,
                                                "apartmentFloorNumber" => $AptFloorNumC,
                                                "blockId" => $BlkIdC,
                                                "blockNumber" => $BlkNumC,
                                                "BlockName" => $BlkNameC,
                                                "likes" => $Comment[4],
                                                "disLikes" => $Comment[5],
                                                "voteStatus" => $VoteStatus,
                                                "flagLastPage" => $FLPC
                                            ];
                                            $CommentCounter++;
                                        }
                                    //   ==============================================================================================
                                        if(empty($ServiceData[7]))
                                        {
                                            $attachmentURL = $this->RootUrl . "omartyapis/Images/serviceImages/Default.jpg";
                                        }
                                        elseif(!empty($ServiceData[7]))
                                        {
                                            $attachmentURL = $this->RootUrl . "omartyapis/Images/serviceImages/" . $ServiceData[7];
                                        }
                                                
                                        // Get User Name.
                                        $sqlGetUserName = $this->conn->query("SELECT UserName FROM Resident_User WHERE ID = '$ServiceData[11]'");
                                        if($sqlGetUserName->num_rows > 0)
                                        {
                                            $residentName = $sqlGetUserName->fetch_row();
                                            $RESNAME = $residentName[0];
                                        }
                                        else
                                        {
                                            // if Coudln't get resident Username get his ID.
                                            $RESNAME = $ServiceData[11]; // Standing here.
                                        }
                
                                        // Get Service Phone Numbers in $PhoneNums array.
                                        $PhoneNums = ["phoneNum1" => $ServiceData[3]];
                                        if(!empty($ServiceData[4]))
                                        {
                                            $PhoneNums += ["phoneNum2" => $ServiceData[4]];
                                            // $this->returnResponse(200, $PhoneNums);
                                        }
                                        if(!empty($ServiceData[5]))
                                        {
                                            $PhoneNums += ["phoneNum3" => $ServiceData[5]];
                                            // $this->returnResponse(200, $PhoneNums);
                                        }
                                        if(!empty($ServiceData[6]))
                                        {
                                            $PhoneNums += ["phoneNum4" => $ServiceData[6]];
                                            // $this->returnResponse(200, $PhoneNums);
                                        }
                                        
                                        // Get Block number.
                                        $sqlGetBLKNUM = $this->conn->query("SELECT BlockNum FROM Block WHERE ID = '$ServiceData[13]'");
                                        if($sqlGetBLKNUM->num_rows > 0)
                                        {
                                            $BLKNUM = $sqlGetBLKNUM->fetch_row();
                                            $BlockNum = $BLKNUM[0];
                                        }
                                        else
                                        {
                                            $BlockNum = $ServiceData[13];
                                        }
                                        // Get CategoryID.
                                        $sqlGetCatId = $this->conn->query("SELECT CategoryID FROM Service WHERE ID = $ServiceData[0]");
                                        if($sqlGetCatId->num_rows > 0)
                                        {
                                           $CatID = $sqlGetCatId->fetch_row();
                                        }
                                        else
                                        {
                                            $CatID[0] = 1;
                                        }
                                        //  Recheck Fetching services favourite or not.
                                        $sqlCheckFav = $this->conn->query("SELECT * FROM Favourite WHERE ResidentID = '$UserID' AND ServiceID = '$ServiceData[0]' AND CategoryID = '$CatID[0]'");
                                        // Check if favourite or not
                                        if($sqlCheckFav->num_rows > 0)
                                        {
                                            $Favourite = true;
                                        }
                                        elseif($sqlCheckFav->num_rows <= 0)
                                        {
                                            $Favourite = false;
                                        }
                                        // ==========================================================================================================================================
                                        // Get Category Name
                                        $sqlGetSerCat = $this->conn->query("SELECT Name_ar FROM ServiceCategory WHERE ID = '$ServiceData[10]'");
                                        if($sqlGetSerCat->num_rows > 0)
                                        {
                                            $CatName = $sqlGetSerCat->fetch_row();
                                        }
                                        elseif($sqlGetSerCat->num_rows <= 0)
                                        {
                                            $CatName[0] = $ServiceData[10];
                                        }
                                        
                                        // Get Country
                                        $sqlGetCountry = $this->conn->query("SELECT name From Country Where ID = '$ServiceData[16]'");
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
                                        $sqlGetGov = $this->conn->query("SELECT GOVName From Governate Where ID = '$ServiceData[17]'");
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
                                        $sqlGetCity = $this->conn->query("SELECT Name From City Where ID = '$ServiceData[18]'");
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
                                        $sqlGetRegion = $this->conn->query("SELECT RegionName From Region Where ID = '$ServiceData[19]'");
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
                                        $sqlGetCompound = $this->conn->query("SELECT CompundName From Compound Where ID = '$ServiceData[20]'");
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
                                        $sqlGetStreet = $this->conn->query("SELECT StreetName From Street Where ID = '$ServiceData[21]'");
                                        if($sqlGetStreet->num_rows > 0)
                                        {
                                            $StreetNameArr = $sqlGetStreet->fetch_row();
                                            $StreetName = $StreetNameArr[0];
                                        }
                                        elseif($sqlGetStreet->num_rows <= 0)
                                        {
                                            $StreetName = $ServiceData[21];
                                        }
                                        $Service["Record$count"] = 
                                        [
                                            "id" => $ServiceData[0],
                                            "name" => $ServiceData[1],
                                            "isFav" => $Favourite,
                                            "description" => $ServiceData[2],
                                            "phoneNums" => $PhoneNums,
                                            "image" => $attachmentURL,
                                            "rate" => $ServiceData[8],
                                            "categoryID" => $ServiceData[10],
                                            "categoryName" => $CatName[0],
                                            "Comments" => array_values($ComArr),
                                            "latitude" => $ServiceData[14],
                                            "longitude" => $ServiceData[15],      
                                            "countryName" => $CountryName,
                                            "governateName" => $GovName,
                                            "cityName" => $CityName,
                                            "regionName" => $RegionName,
                                            "compoundName" => $CompName,
                                            "streetName" => $StreetName,
                                            "flagLastPage" => $FLP
                                        ];
                                        $count++;
                                    }
                                    if($sqlGetService->num_rows <= 0)
                                    {
                                        $Service = [];
                                    }
                                    $this->returnResponse(200, array_values($Service));
                                
                            }
                            elseif($AptData[1] == "1")
                            {
                                $this->throwError(200, "Apartment status is still binding.");
                            }
                            elseif($AptData[1] == "3")
                            {
                                $this->throwError(200, "Apartment is Banned.");
                            }
                            else
                            {
                                $this->throwError(200, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "Resident doesn't have any relation to this apartment.");
                        }
                    }
                }
                elseif($blockData[1] == '1')
                {
                    $this->throwError(200, "Block status is still binding.");
                }
                elseif($blockData[1] == '3')
                {
                    $this->throwError(200, "Block is Banned.");
                }
                else
                {
                    $this->throwError(200, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "User does not relate to this block.");
            }
        }
        else
        {
            $this->throwError(200, "Block Not found.");
        }
    }

    function showNeighbours() // OK Final
    {
        // include("../Config.php");
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
        
            // Check Block Existence.
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
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $date = date("Y:m:d");
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
                                            // Select all Neighbours with the same block id.
                                            $sqlGetNeighbour = $this->conn->query("SELECT ResidentID, ApartmentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND StatusID = 2 AND ResidentID != '$UserID' LIMIT $Start, $Limit"); 
                                            $sqlGetNeighbour2 = $this->conn->query("SELECT ResidentID, ApartmentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND StatusID = 2 AND ResidentID != '$UserID'"); 
                                            $RowsNum = $sqlGetNeighbour2->num_rows;
                                            // if($this->conn->error) {echo $this->conn->error . "1";}
                                            // if($sqlGetNeighbour->num_rows <= 0)
                                            // {
                                            //     $this->returnResponse(200, "You don't have Neighbours Yet.");
                                            // }
                                                $count = 1;
                                                while($ResIds = $sqlGetNeighbour->fetch_row())
                                                {
                                                     // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum)
                                                    {
                                                        $FLP = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum)
                                                    {
                                                        $FLP = 0;
                                                    }
                                                    
                                                    // Get User Phone number.
                                                    $sqlGetPN = $this->conn->query("SELECT ID, Name, PhoneNum, Image, Hide FROM Resident_User WHERE ID = $ResIds[0]");
                                                    if($this->conn->error)
                                                    {
                                                        echo $this->conn->error;
                                                        exit;
                                                    }
                                                    // if($this->conn->error) {echo $this->conn->error . "2";}
                                                    if($sqlGetPN->num_rows > 0)
                                                    {
                                                        $residentPN = $sqlGetPN->fetch_row();
                                                        if($residentPN[4] == '2' || $residentPN[4] == '3')
                                                        {
                                                            $RESpn = "Hidden";
                                                        }
                                                        else
                                                        {
                                                            $RESpn = $residentPN[2];
                                                        }
                                                        
                                                        // Check For Secondary phone Numbers
                                                        $sqlCheckPN = $this->conn->query("SELECT PhoneNum, ID FROM PhoneNums WHERE UserID = '$residentPN[0]' AND Hide = 0");
                                                        $SecondaryPNs = [];
                                                        if($sqlCheckPN->num_rows > 0)
                                                        {
                                                            $count = 1;
                                                            while($PNData = $sqlCheckPN->fetch_row())
                                                            {
                                                                $SecondaryPNs[$count] = ["id" => $PNData[1], "phoneNumber" => $PNData[0]];
                                                                $count++;
                                                            }
                                                            
                                                        }
                                                        
                                                        $RESname = $residentPN[1];
                                                        // get image.
                                                        if(!empty($residentPN[3]))
                                                        {
                                                            $ResidentImage = $this->RootUrl . "omartyapis/Images/profilePictures/$residentPN[3]";
                                                        }
                                                        elseif(empty($residentPN[3]))
                                                        {
                                                            $ResidentImage = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $RESpn = $ResIds[0];
                                                    }
                                                    // Get Apatment Number and Floor number..
                                                    $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = '$ResIds[1]'");
                                                    if($sqlGetAptNum->num_rows > 0)
                                                    {
                                                        $AptNum = $sqlGetAptNum->fetch_row();
                                                        $APTNUM = $AptNum[0];
                                                        $APTFLRNUM = $AptNum[1];
                                                        $APTNAME = $AptNum[2];
                                                    }
                                                    else
                                                    {
                                                        $APTNUM = $ResIds[1];
                                                        $APTFLRNUM = $ResIds[1];
                                                        $APTNAME = $ResIds[1];
                                                    }
                                                    // Check Favourite.
                                                    $sqlCheckFavourite = $this->conn->query("SELECT ID FROM Favourite WHERE NeighbourID = $ResIds[0] AND CategoryID = 1");
                                                    if($sqlCheckFavourite->num_rows > 0)
                                                    {
                                                        $isFav = true;
                                                    }
                                                    elseif($sqlCheckFavourite->num_rows <= 0)
                                                    {
                                                        $isFav = false;
                                                    }
                                                    
                                                    $Arr["Record$count"] = 
                                                    [
                                                        "id" => $ResIds[0],
                                                        "residentName" => $RESname,
                                                        "residentImage" => $ResidentImage,
                                                        "apartmentNumber" => $APTNUM,
                                                        "apartmentName" => $APTNAME,
                                                        "apartmentFloorNumber" => $APTFLRNUM,
                                                        "phoneNumber" => $RESpn,
                                                        "secondaryPhoneNumbers" => array_values($SecondaryPNs),
                                                        "isFav" => $isFav,
                                                        "flagLastPage" => $FLP,
                                                    ];
                                                    $count++;
                                                }
                                                if($sqlGetNeighbour->num_rows <= 0)
                                                {
                                                    $Arr = [];
                                                }
                                                $this->returnResponse(200, array_values($Arr));
                                            
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

    function showComplaints() // OK Final (Mine)
    {
        // include("../Config.php");
       $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
        
            // Check Block Existence.
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
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $Mine = $_POST["mine"];
            $date = date("Y:m:d");
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
                                            if($Mine == '1')
                                            {
                                                $sqlGetCmp = $this->conn->query("SELECT * FROM Complaint WHERE BlockID = '$BLKID' AND ResidentID = '$UserID' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                                $sqlGetCmp2 = $this->conn->query("SELECT * FROM Complaint WHERE BlockID = '$BLKID' AND ResidentID = '$UserID' ORDER BY CreatedAt DESC");
                                                $RowsNum = $sqlGetCmp2->num_rows;
                                            }
                                            else
                                            {
                                                // Select all Complaints with the same block id.
                                                $sqlGetCmp = $this->conn->query("SELECT * FROM Complaint WHERE BlockID = '$BLKID' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                                $sqlGetCmp2 = $this->conn->query("SELECT * FROM Complaint WHERE BlockID = '$BLKID' ORDER BY CreatedAt DESC");
                                                $RowsNum = $sqlGetCmp2->num_rows;
                                            }
                                            // if($sqlGetCmp->num_rows <= 0)
                                            // {
                                            //     $this->returnResponse(200, "Block doesn't have Complaints.");
                                            // }
                                                $count = 1;
                                                while($CmpData = $sqlGetCmp->fetch_row())
                                                {
                                                     // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum)
                                                    {
                                                        $FLP = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum)
                                                    {
                                                        $FLP = 0;
                                                    }
                                                    
                                                    // Check Comments
                                                    $ComArr = [];
                                                    $CommentCounter = 1;
                                                    // Get Service Comments.
                                                    $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$CmpData[0]' AND OriginalPostTable = 'Complaint' LIMIT $Start, $Limit");
                                                    $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$CmpData[0]' AND OriginalPostTable = 'Complaint'");
                                                    if($this->conn->error)
                                                    {
                                                        echo $this->conn->error;
                                                        exit;
                                                    }
                                                    $RowsNum2 = $sqlGetComment2->num_rows;
                                                    while($Comment = $sqlGetComment->fetch_row())
                                                    {
                                                        // Get Last page flag.
                                                        if(($Limit + $Start) >= $RowsNum2)
                                                        {
                                                            $FLPC = 1;
                                                        }
                                                        elseif(($Limit + $Start) < $RowsNum2)
                                                        {
                                                            $FLPC = 0;
                                                        }
                                                        
                                                        // Get Resident Name Image 
                                                        $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                                        if($sqlGetRes->num_rows > 0)
                                                        {
                                                            $ResDT = $sqlGetRes->fetch_row();
                                                            $ResName = $ResDT[0];
                                                            if(!empty($ResDT[1]))
                                                            {
                                                                $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/$ResDT[1]";
                                                            }
                                                            elseif(empty($ResDT[1]))
                                                            {
                                                                $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                            }
                                                        }
                                                        elseif($sqlGetRes->num_rows <= 0)
                                                        {
                                                            $ResName = $Comment[2];
                                                            $Resimage = "";
                                                        }
                                                        // Get Apartment Num and Apartment Floor Num.
                                                        $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName, BlockID FROM Apartment WHERE ID = '$Comment[3]'");
                                                        if($sqlGetAptData->num_rows > 0)
                                                        {
                                                            $AptDataC = $sqlGetAptData->fetch_row();
                                                            $AptNumC = $AptDataC[0];
                                                            $AptFloorNumC = $AptDataC[1];
                                                            $AptNameC = $AptDataC[2];
                                                        }
                                                        if($sqlGetAptData->num_rows <= 0)
                                                        {
                                                            $AptNumC = $Comment[3];
                                                            $AptFloorNumC = $Comment[3];
                                                            $AptNameC = $Comment[3];
                                                        }
                                                        
                                                        // Get Block Number and name.
                                                        $sqlGetBlockName = $this->conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$AptDataC[3]'");
                                                        if($sqlGetBlockName->num_rows > 0)
                                                        {
                                                            $BlkDataC = $sqlGetBlockName->fetch_row();
                                                            $BlkIdC = $BlkDataC[0];
                                                            $BlkNumC = $BlkDataC[0];
                                                            $BlkNameC = $BlkDataC[1];
                                                        }
                                                        if($sqlGetBlockName->num_rows <= 0)
                                                        {
                                                            $BlkIdC = NULL;
                                                            $BlkNumC = NULL;
                                                            $BlkNameC = NULL;
                                                        }
                                                        
                                                        // Get Vote Status.
                                                        $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                                        if($sqlGetVoteStatus->num_rows > 0)
                                                        {
                                                            $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                            $Likes = $VoteStatusArr[0];
                                                            $DisLikes = $VoteStatusArr[1];
                                                            if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                            {
                                                                $VoteStatus = TRUE;
                                                            }
                                                            elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                            {
                                                                $VoteStatus = FALSE;
                                                            }
                                                            elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                            {
                                                                $VoteStatus = NULL;
                                                            }
                                                        }
                                                        elseif($sqlGetVoteStatus->num_rows <= 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                        
                                                        $ComArr[$CommentCounter] = 
                                                        [
                                                            "commentId" => $Comment[0],
                                                            "comment" => $Comment[1],
                                                            "residentId" => $Comment[2],
                                                            "residentName" => $ResName,
                                                            "residentImage" => $ResImage,
                                                            "apartmentId" => $Comment[3],
                                                            "apartmentNumber" => $AptNumC,
                                                            "apartmentName" => $AptNameC,
                                                            "apartmentFloorNumber" => $AptFloorNumC,
                                                            "blockId" => $BlkIdC,
                                                            "blockNumber" => $BlkNumC,
                                                            "blockName" => $BlkNameC,
                                                            "likes" => $Comment[4],
                                                            "disLikes" => $Comment[5],
                                                            "createdAt" => $Comment[6],
                                                            "flagLastPage" => $FLPC
                                                        ];
                                                        $CommentCounter++;
                                                    }
                                                    
                                                    // Get Letter Of Complaint.
                                                    $cmpId = $CmpData[0];
                                                    $cmp = $CmpData[2];
                                                    $likes = $CmpData[3];
                                                    $disLikes = $CmpData[4];
                                                    $CreatedAt = $CmpData[9];
                                                    // Get User Phone number and name.
                                                    $sqlGetPN = $this->conn->query("SELECT ID, Name, PhoneNum, Image, Hide FROM Resident_User WHERE ID = $CmpData[5]");
                                                    if($sqlGetPN->num_rows > 0)
                                                    {
                                                        $residentPN = $sqlGetPN->fetch_row();
                                                        if($residentPN[4] == '2' || $residentPN[4] == '3')
                                                        {
                                                            $RESpn = "Hidden";    
                                                        }
                                                        else
                                                        {
                                                            $RESpn = $residentPN[2];
                                                        }
                                                        
                                                        // Check For Secondary phone Numbers
                                                        $sqlCheckPN = $this->conn->query("SELECT PhoneNum, ID FROM PhoneNums WHERE UserID = '$residentPN[0]' AND Hide = 0");
                                                        $SecondaryPNs = [];
                                                        if($sqlCheckPN->num_rows > 0)
                                                        {
                                                            $count = 1;
                                                            while($PNData = $sqlCheckPN->fetch_row())
                                                            {
                                                                $SecondaryPNs[$count] = ["id" => $PNData[1], "phoneNumber" => $PNData[0]];
                                                                $count++;
                                                            }
                                                            
                                                        }
                                                        
                                                        $RESname = $residentPN[1];
                                                        if(!empty($residentPN[3]))
                                                        {
                                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$residentPN[3]";
                                                        }
                                                        elseif(empty($residentPN[3]))
                                                        {
                                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $RESpn = $CmpData[0];
                                                    }
                                                    // Get Apatment Number and Floor number..
                                                    $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum FROM Apartment WHERE ID = $CmpData[7]");
                                                    if($sqlGetAptNum->num_rows > 0)
                                                    {
                                                        $AptNum = $sqlGetAptNum->fetch_row();
                                                        $APTNUM = $AptNum[0];
                                                        $APTFLRNUM = $AptNum[1];
                                                    }
                                                    else
                                                    {
                                                        $APTNUM = $CmpData[2];
                                                        $APTFLRNUM = $CmpData[2];
                                                    }
                                                    
                                                    // Get Vote Status.
                                                    $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Complaint' AND RecordID = '$CmpData[0]'");
                                                    if($sqlGetVoteStatus->num_rows > 0)
                                                    {
                                                        $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                        $Likes = $VoteStatusArr[0];
                                                        $DisLikes = $VoteStatusArr[1];
                                                        if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                        {
                                                            $VoteStatus = TRUE;
                                                        }
                                                        elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                        {
                                                            $VoteStatus = FALSE;
                                                        }
                                                        elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                    }
                                                    elseif($sqlGetVoteStatus->num_rows <= 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                    
                                                    $Arr["Record$count"] = 
                                                    [
                                                        "id" => $cmpId,
                                                        "residentName" => $RESname,
                                                        "apartmentNumber" => $APTNUM,
                                                        "apartmentFloorNumber" => $APTFLRNUM,
                                                        "phoneNumber" => $RESpn,
                                                        "secondaryPhoneNumbers" => array_values($SecondaryPNs),
                                                        "residentImage" => $ImageUrl,
                                                        "complaint" => $cmp,
                                                        "likes" => $likes,
                                                        "disLikes" => $disLikes,
                                                        "voteStatus" => $VoteStatus,
                                                        "createdAt" => $CreatedAt,
                                                        "comments" => array_values($ComArr),
                                                        "flagLastPage" => $FLP
                                                    ];
                                                    $count++;
                                                }
                                                if($sqlGetCmp->num_rows <= 0)
                                                {
                                                    $Arr = [];
                                                }
                                                $this->returnResponse(200, array_values($Arr));
                                            
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

    function showSuggestion() // OK Final (Mine)
    {
        // include("../Config.php");
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
          
            // Check Block Existence.
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
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $Mine = $_POST["mine"];
            $date = date("Y:m:d");
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
                                            if($Mine == '1')
                                            {
                                                $sqlGetSGT = $this->conn->query("SELECT * FROM Suggestion WHERE BlockID = '$BLKID' AND ResidentID = '$UserID' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                                $sqlGetSGT2 = $this->conn->query("SELECT * FROM Suggestion WHERE BlockID = '$BLKID' AND ResidentID = '$UserID' ORDER BY CreatedAt DESC");
                                                $RowsNum = $sqlGetSGT2->num_rows;
                                            }
                                            else
                                            {
                                                // Select all Suggestion with the same block id.
                                                $sqlGetSGT = $this->conn->query("SELECT * FROM Suggestion WHERE BlockID = '$BLKID' LIMIT $Start, $Limit");
                                                $sqlGetSGT2 = $this->conn->query("SELECT * FROM Suggestion WHERE BlockID = '$BLKID'");
                                                $RowsNum = $sqlGetSGT2->num_rows;
                                            }
                                            // if($sqlGetSGT->num_rows <= 0)
                                            // {
                                            //     $this->returnResponse(200, "Block doesn't have Suggestions.");
                                            // }
                                                $count = 1;
                                                while($SgtData = $sqlGetSGT->fetch_row())
                                                {
                                                    // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum)
                                                    {
                                                        $FLP = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum)
                                                    {
                                                        $FLP = 0;
                                                    }
                                                    
                                                    // Check Comments
                                                    $ComArr = [];
                                                    $CommentCounter = 1;
                                                    // Get Service Comments.
                                                    $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$SgtData[0]' AND OriginalPostTable = 'Suggestion' LIMIT $Start, $Limit");
                                                    $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$SgtData[0]' AND OriginalPostTable = 'Suggestion'");
                                                    $RowsNum2 = $sqlGetComment2->num_rows;
                                                    while($Comment = $sqlGetComment->fetch_row())
                                                    {
                                                        // Get Last page flag.
                                                        if(($Limit + $Start) >= $RowsNum2)
                                                        {
                                                            $FLPC = 1;
                                                        }
                                                        elseif(($Limit + $Start) < $RowsNum2)
                                                        {
                                                            $FLPC = 0;
                                                        }
                                                        // Get Resident Name Image 
                                                        $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                                        if($sqlGetRes->num_rows > 0)
                                                        {
                                                            $ResDT = $sqlGetRes->fetch_row();
                                                            $ResName = $ResDT[0];
                                                            if(!empty($ResDT[1]))
                                                            {
                                                                $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/$ResDT[1]";
                                                            }
                                                            elseif(empty($ResDT[1]))
                                                            {
                                                                $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                            }
                                                        }
                                                        elseif($sqlGetRes->num_rows <= 0)
                                                        {
                                                            $ResName = $Comment[2];
                                                            $Resimage = "";
                                                        }
                                                        // Get Apartment Num and Apartment Floor Num.
                                                        $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName, BlockID FROM Apartment WHERE ID = '$Comment[3]'");
                                                        if($sqlGetAptData->num_rows > 0)
                                                        {
                                                            $AptDataC = $sqlGetAptData->fetch_row();
                                                            $AptNumC = $AptDataC[0];
                                                            $AptFloorNumC = $AptDataC[1];
                                                            $AptNameC = $AptDataC[2];
                                                        }
                                                        elseif($sqlGetAptData->num_rows <= 0)
                                                        {
                                                            $AptNumC = $Comment[3];
                                                            $AptFloorNumC = $Comment[3];
                                                            $AptNameC = $Comment[3];
                                                        }
                                                        
                                                        // Get Block Number and name.
                                                        $sqlGetBlockName = $this->conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$AptDataC[3]'");
                                                        if($sqlGetBlockName->num_rows > 0)
                                                        {
                                                            $BlkDataC = $sqlGetBlockName->fetch_row();
                                                            $BlkIdC = $BlkDataC[0];
                                                            $BlkNumC = $BlkDataC[0];
                                                            $BlkNameC = $BlkDataC[1];
                                                        }
                                                        if($sqlGetBlockName->num_rows <= 0)
                                                        {
                                                            $BlkIdC = NULL;
                                                            $BlkNumC = NULL;
                                                            $BlkNameC = NULL;
                                                        }
                                                        
                                                        // Get Vote Status.
                                                        $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                                        if($sqlGetVoteStatus->num_rows > 0)
                                                        {
                                                            $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                            $Likes = $VoteStatusArr[0];
                                                            $DisLikes = $VoteStatusArr[1];
                                                            if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                            {
                                                                $VoteStatus = TRUE;
                                                            }
                                                            elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                            {
                                                                $VoteStatus = FALSE;
                                                            }
                                                            elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                            {
                                                                $VoteStatus = NULL;
                                                            }
                                                        }
                                                        elseif($sqlGetVoteStatus->num_rows <= 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                        
                                                        $ComArr[$CommentCounter] = 
                                                        [
                                                            "commentId" => $Comment[0],
                                                            "comment" => $Comment[1],
                                                            "residentId" => $Comment[2],
                                                            "residentName" => $ResName,
                                                            "residentImage" => $ResImage,
                                                            "apartmentId" => $Comment[3],
                                                            "apartmentNumber" => $AptNumC,
                                                            "apartmentName" => $AptNameC,
                                                            "apartmentFloorNumber" => $AptFloorNumC,
                                                            "blockId" => $BlkIdC,
                                                            "blockNumber" => $BlkNumC,
                                                            "blockName" => $BlkNameC,
                                                            "likes" => $Comment[4],
                                                            "disLikes" => $Comment[5],
                                                            "voteStatus" => $VoteStatus,
                                                            "createdAt" => $Comment[6],
                                                            "flagLastPage" => $FLPC
                                                        ];
                                                        $CommentCounter++;
                                                    }
                                                    
                                                    // Get Letter Of Suggestion.
                                                    $sgtId = $SgtData[0];
                                                    $sgt = $SgtData[1];
                                                    $likes = $SgtData[2];
                                                    $disLikes = $SgtData[3];
                                                    $CreatedAt = $SgtData[8];
                                                    // Get User Phone number and Image.
                                                    $sqlGetPN = $this->conn->query("SELECT ID, PhoneNum, Image, Hide, Name FROM Resident_User WHERE ID = $SgtData[4]");
                                                    $RESpn = NULL;
                                                    $RESname = NULL;
                                                    if($sqlGetPN->num_rows > 0)
                                                    {
                                                        $residentPN = $sqlGetPN->fetch_row();
                                                        $RESname = $residentPN[4];
                                                        if($residentPN[3] == '2' || $residentPN[3] == '3')
                                                        {
                                                            $RESpn = "Hidden";    
                                                        }
                                                        else
                                                        {
                                                            $RESpn = $residentPN[1];
                                                        }
                                                        
                                                        
                                                        // Check For Secondary phone Numbers
                                                        $sqlCheckPN = $this->conn->query("SELECT PhoneNum, ID FROM PhoneNums WHERE UserID = '$residentPN[0]' AND Hide = 0");
                                                        $SecondaryPNs = [];
                                                        if($sqlCheckPN->num_rows > 0)
                                                        {
                                                            $count = 1;
                                                            while($PNData = $sqlCheckPN->fetch_row())
                                                            {
                                                                $SecondaryPNs[$count] = ["id" => $PNData[1], "phoneNumber" => $PNData[0]];
                                                                $count++;
                                                            }
                                                            
                                                        }
                                                        
                                                        if(!empty($residentPN[2]))
                                                        {
                                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$residentPN[2]";
                                                        }
                                                        elseif(empty($residentPN[2]))
                                                        {
                                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $RESpn = $SgtData[4];
                                                        $RESname = $SgtData[4];
                                                    }
                                                    // Get Apatment Number and Floor number..
                                                    $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum FROM Apartment WHERE ID = $SgtData[6]");
                                                    if($sqlGetAptNum->num_rows > 0)
                                                    {
                                                        $AptNum = $sqlGetAptNum->fetch_row();
                                                        $APTNUM = $AptNum[0];
                                                        $APTFLRNUM = $AptNum[1];
                                                    }
                                                    else
                                                    {
                                                        $APTNUM = $SgtData[6];
                                                        $APTFLRNUM = $SgtData[6];
                                                    }
                                                    
                                                    // Get Vote Status.
                                                    $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Suggestion' AND RecordID = '$SgtData[0]'");
                                                    if($sqlGetVoteStatus->num_rows > 0)
                                                    {
                                                        $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                        $Likes = $VoteStatusArr[0];
                                                        $DisLikes = $VoteStatusArr[1];
                                                        if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                        {
                                                            $VoteStatus = TRUE;
                                                        }
                                                        elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                        {
                                                            $VoteStatus = FALSE;
                                                        }
                                                        elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                    }
                                                    elseif($sqlGetVoteStatus->num_rows <= 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                    
                                                    $Arr["Record$count"] = 
                                                    [
                                                        "id" => $sgtId,
                                                        "residentName" => $RESname,
                                                        "apartmentNumber" => $APTNUM,
                                                        "apartmentFloorNumber" => $APTFLRNUM,
                                                        "phoneNumber" => $RESpn,
                                                        "secondaryPhoneNumber"=> array_values($SecondaryPNs),
                                                        "residentImage" => $ImageUrl,
                                                        "Suggestion" => $sgt,
                                                        "likes" => $likes,
                                                        "disLikes" => $disLikes,
                                                        "voteStatus" => $VoteStatus,
                                                        "createdAt" => $CreatedAt,
                                                        "comments" => array_values($ComArr),
                                                        "flagLastPage" => $FLP
                                                    ];
                                                    $count++;
                                                }
                                                if($sqlGetSGT->num_rows <= 0)
                                                {
                                                    $Arr = [];
                                                }
                                                $this->returnResponse(200, array_values($Arr));
                                            
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

    function ChatShowMessage() //OK Final
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
        
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        // $Longitude = $_POST["longitude"];
        // $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        $CurrentDate = date("Y-m-d H-i-s");
        $Date = date("Y-m-d h-i-sa");
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) *$Limit;

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
                                // if User didn't enter image or file continue and upload file value = NULL.
                                if(!empty($image)) { $newImage = $image["newName"]; }
                                else { $newImage = NULL; }
                                // Get Mesage Then post it in block's chat group.
                                $sqlShowMessage = $this->conn->query("SELECT * FROM Message WHERE BlockID = '$BLKID' LIMIT $Start, $Limit"); //  AND ApartmentID = '$APTID'  ORDER BY CreatedAt DESC
                                $sqlShowMessage2 = $this->conn->query("SELECT * FROM Message WHERE BlockID = '$BLKID'");
                                if($sqlShowMessage2->num_rows > 0)
                                {
                                    $RowsNum = $sqlShowMessage2->num_rows;
                                    $NumOfPages = ceil($RowsNum / 10);
                                    $count = 0;
                                    while($Chat = $sqlShowMessage->fetch_row())
                                    {
                                        // Get Image URL
                                        if(!empty($Chat[2]))
                                        {
                                            $AttachUrl = $this->RootUrl . "omartyapis/Images/ChatImages/$Chat[2]" ;
                                        }
                                        elseif(empty($Chat[2]))
                                        {
                                            $AttachUrl = "";
                                        }
                                        // Get Sender Name.
                                        $sqlGetResName = $this->conn->query("SELECT Name FROM Resident_User WHERE ID = '$Chat[3]'");
                                        if($sqlGetResName->num_rows > 0)
                                        {
                                            $ResName = $sqlGetResName->fetch_row();
                                        }
                                        elseif($sqlGetResName->num_rows <= 0)
                                        {
                                            $ResName[0] = $Chat[3];
                                        }
                                        // Get Block Number.
                                        $sqlGetBLKData = $this->conn->query("SELECT BlockNum, RegionID, BlockName FROM Block WHERE ID = $Chat[5]");
                                        if($sqlGetBLKData->num_rows > 0)
                                        {
                                            $BlockNum = $sqlGetBLKData->fetch_row();
                                            if($BlockNum[1] > 1)
                                            {
                                                // Get Region Name.
                                                $sqlGetRegionName = $this->conn->query("SELECT RegionName FROM Region WHERE ID = '$BlockNum[1]'");
                                                if($sqlGetRegionName->num_rows > 0)
                                                {
                                                    $RegName = $sqlGetRegionName->fetch_row();
                                                }
                                                elseif($sqlGetRegionName->num_rows <= 0)
                                                {
                                                    $RegName[0] = NULL;
                                                }
                                            }
                                        }
                                        elseif($sqlGetBLKData->num_rows <= 0)
                                        {
                                            $BlockNum[0] = $Chat[5];
                                            $BlockNum[2] = $Chat[5];
                                        }
                                        // Get Apartment Number and floor Number.
                                        $sqlGetAPTData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = '$Chat[4]'");
                                        if($sqlGetAPTData->num_rows > 0)
                                        {
                                            $APTData = $sqlGetAPTData->fetch_row();
                                            $AptNum = $APTData[0];
                                            $AptFloorNum = $APTData[1];
                                            $AptName = $APTData[2];
                                        }
                                        elseif($sqlGetAPTData->num_rows <= 0)
                                        {
                                            $AptNum = $Chat[4];
                                            $AptFloorNum = $Chat[4];
                                            $AptName = $Chat[4];
                                        }
                                        // Get Last page flag.
                                        if(($Limit + $Start) >= $RowsNum)
                                        {
                                            $FLP = 1;
                                        }
                                        elseif(($Limit + $Start) < $RowsNum)
                                        {
                                            $FLP = 0;
                                        }
                                        $Arr[$count] = 
                                        [
                                            "id" => $Chat[0],
                                            "message" => $Chat[1],
                                            "attach" => $AttachUrl,
                                            "senderId" => $Chat[3],
                                            "senderName" => $ResName[0],
                                            "blockId" => $Chat[5],
                                            "blockNumber" => $BlockNum[0],
                                            "blockName" => $BlockNum[1],
                                            "regionName" => $RegName[0],
                                            "apartmentId" => $Chat[4],
                                            "apartmentNumber" => $AptNum,
                                            "apartmentName" => $AptName,
                                            "apartmentFloorNumber" => $AptFloorNum,
                                            "createdAt" => $Chat[6],
                                            "flagLastPage" => $FLP
                                        ];
                                        $count++;
                                    }
                                    if($Arr == NULL)
                                    {
                                        $LastPage = ceil($RowsNum / $Limit);
                                        
                                        $this->returnResponse(200, "Last page is $LastPage");
                                    }
                                    else
                                    {
                                        $this->returnResponse(200, array_values($Arr));
                                    }
                                    // Insert Message Data into database (MessageContent / Image OR File / SenderID / Receiver / BlockID / ApartmentID / DateTime).
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

    function ShowManagementAndPaymentInfo()
    {
        // include("../Config.php");
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
          
            // Check Block Existence.
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
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $date = date("Y:m:d");
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
                                            
                                                $count = 1;
                                                while($SgtData = $sqlGetSGT->fetch_row())
                                                {
                                                    // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum)
                                                    {
                                                        $FLP = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum)
                                                    {
                                                        $FLP = 0;
                                                    }
                                                    
                                                    // Check Comments
                                                    $ComArr = [];
                                                    $CommentCounter = 1;
                                                    // Get Service Comments.
                                                    $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$SgtData[0]' AND OriginalPostTable = 'Suggestion' LIMIT $Start, $Limit");
                                                    $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$SgtData[0]' AND OriginalPostTable = 'Suggestion'");
                                                    $RowsNum2 = $sqlGetComment2->num_rows;
                                                    while($Comment = $sqlGetComment->fetch_row())
                                                    {
                                                        // Get Last page flag.
                                                        if(($Limit + $Start) >= $RowsNum2)
                                                        {
                                                            $FLPC = 1;
                                                        }
                                                        elseif(($Limit + $Start) < $RowsNum2)
                                                        {
                                                            $FLPC = 0;
                                                        }
                                                        // Get Resident Name Image 
                                                        $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                                        if($sqlGetRes->num_rows > 0)
                                                        {
                                                            $ResDT = $sqlGetRes->fetch_row();
                                                            $ResName = $ResDT[0];
                                                            if(!empty($ResDT[1]))
                                                            {
                                                                $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/$ResDT[1]";
                                                            }
                                                            elseif(empty($ResDT[1]))
                                                            {
                                                                $ResImage = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                            }
                                                        }
                                                        elseif($sqlGetRes->num_rows <= 0)
                                                        {
                                                            $ResName = $Comment[2];
                                                            $Resimage = "";
                                                        }
                                                        // Get Apartment Num and Apartment Floor Num.
                                                        $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = '$Comment[3]'");
                                                        if($sqlGetAptData->num_rows > 0)
                                                        {
                                                            $AptDataC = $sqlGetAptData->fetch_row();
                                                            $AptNumC = $AptDataC[0];
                                                            $AptFloorNumC = $AptDataC[1];
                                                            $AptNameC = $AptDataC[2];
                                                        }
                                                        elseif($sqlGetAptData->num_rows <= 0)
                                                        {
                                                            $AptNumC = $Comment[3];
                                                            $AptFloorNumC = $Comment[3];
                                                            $AptNameC = $Comment[3];
                                                        }
                                                        
                                                        // Get Vote Status.
                                                        $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                                        if($sqlGetVoteStatus->num_rows > 0)
                                                        {
                                                            $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                            $Likes = $VoteStatusArr[0];
                                                            $DisLikes = $VoteStatusArr[1];
                                                            if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                            {
                                                                $VoteStatus = TRUE;
                                                            }
                                                            elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                            {
                                                                $VoteStatus = FALSE;
                                                            }
                                                            elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                            {
                                                                $VoteStatus = NULL;
                                                            }
                                                        }
                                                        elseif($sqlGetVoteStatus->num_rows <= 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                        
                                                        $ComArr[$CommentCounter] = 
                                                        [
                                                            "commentId" => $Comment[0],
                                                            "comment" => $Comment[1],
                                                            "residentId" => $Comment[2],
                                                            "residentName" => $ResName,
                                                            "residentImage" => $ResImage,
                                                            "apartmentId" => $Comment[3],
                                                            "apartmentNumber" => $AptNumC,
                                                            "apartmentName" => $AptNameC,
                                                            "apartmentFloorNumber" => $AptFloorNumC,
                                                            "likes" => $Comment[4],
                                                            "disLikes" => $Comment[5],
                                                            "voteStatus" => $VoteStatus,
                                                            "createdAt" => $Comment[6],
                                                            "flagLastPage" => $FLPC
                                                        ];
                                                        $CommentCounter++;
                                                    }
                                                    
                                                    // Get Letter Of Suggestion.
                                                    $sgtId = $SgtData[0];
                                                    $sgt = $SgtData[1];
                                                    $likes = $SgtData[2];
                                                    $disLikes = $SgtData[3];
                                                    $CreatedAt = $SgtData[8];
                                                    // Get User Phone number, name and Image.
                                                    $sqlGetPN = $this->conn->query("SELECT Name, PhoneNum, Image FROM Resident_User WHERE ID = $SgtData[4]");
                                                    if($sqlGetPN->num_rows > 0)
                                                    {
                                                        $residentPN = $sqlGetPN->fetch_row();
                                                        $RESpn = $residentPN[1];
                                                        $RESname = $residentPN[0];
                                                        if(!empty($residentPN[2]))
                                                        {
                                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/$residentPN[2]";
                                                        }
                                                        elseif(empty($residentPN[2]))
                                                        {
                                                            $ImageUrl = $this->RootUrl . "omartyapis/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $RESpn = $SgtData[4];
                                                        $RESname = $SgtData[4];
                                                    }
                                                    // Get Apatment Number and Floor number..
                                                    $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = $SgtData[6]");
                                                    if($sqlGetAptNum->num_rows > 0)
                                                    {
                                                        $AptNum = $sqlGetAptNum->fetch_row();
                                                        $APTNUM = $AptNum[0];
                                                        $APTFLRNUM = $AptNum[1];
                                                        $APTNAME = $AptNum[2];
                                                    }
                                                    else
                                                    {
                                                        $APTNUM = $SgtData[6];
                                                        $APTFLRNUM = $SgtData[6];
                                                        $APTNAME = $SgtData[6];
                                                    }
                                                    
                                                    // Get Vote Status.
                                                    $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Suggestion' AND RecordID = '$SgtData[0]'");
                                                    if($sqlGetVoteStatus->num_rows > 0)
                                                    {
                                                        $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                        $Likes = $VoteStatusArr[0];
                                                        $DisLikes = $VoteStatusArr[1];
                                                        if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                        {
                                                            $VoteStatus = TRUE;
                                                        }
                                                        elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                        {
                                                            $VoteStatus = FALSE;
                                                        }
                                                        elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                    }
                                                    elseif($sqlGetVoteStatus->num_rows <= 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                    
                                                    $Arr["Record$count"] = 
                                                    [
                                                        "id" => $sgtId,
                                                        "residentName" => $RESname,
                                                        "apartmentNumber" => $APTNUM,
                                                        "apartmentName" => $APTNAME,
                                                        "apartmentFloorNumber" => $APTFLRNUM,
                                                        "phoneNumber" => $RESpn,
                                                        "residentImage" => $ImageUrl,
                                                        "Suggestion" => $sgt,
                                                        "likes" => $likes,
                                                        "disLikes" => $disLikes,
                                                        "voteStatus" => $VoteStatus,
                                                        "createdAt" => $CreatedAt,
                                                        "comments" => array_values($ComArr),
                                                        "flagLastPage" => $FLP
                                                    ];
                                                    $count++;
                                                }
                                                if($sqlGetSGT->num_rows <= 0)
                                                {
                                                    $Arr = [];
                                                }
                                                $this->returnResponse(200, array_values($Arr));
                                            
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

    function HomePage()
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
            // $StartDate = $_POST["StartDate"];
            // $Next = $_POST["upComming"];
            // $Previous = $_POST["previous"];
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
                                            // Get Meetings.
                                            $Meetings = $this->GetMeetings($BLKID, $APTID);
                                            // Get Events.
                                            $Events = $this->GetEvents($BLKID, $APTID);
                                            // Get News.
                                            $News = $this->GetNews($BLKID, $APTID);
                                            // Get Offers
                                            $Offers = $this->GetOffers($BLKID, $APTID);
                                            // Get Services.
                                            $Services = $this->GetServices($BLKID, $APTID);
                                            // Get Favourite.
                                            $Favourites = $this->GetFavourites($BLKID, $APTID);
                                            // Get Header.
                                            $Header = $this->GetHeader($BLKID, $APTID);
                                            
                                            $HomePage = 
                                            [
                                                "events" => $Events,
                                                "meetings" => $Meetings,
                                                "news" => $News,
                                                "offers" => $Offers,
                                                "services" => $Services,
                                                "favourites" => $Favourites,
                                                "header" => $Header,
                                            ];
                                            $this->returnResponse(200, $HomePage);
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

    private function GetEvents($BLKID, $APTID) // OK Final (Mine / Next / Previous)
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
        
        // $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
        
            // Check Block Existence.
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
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
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
                                                $sqlGetEvent = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' ORDER BY ID DESC LIMIT $Start, $Limit");
                                                $sqlGetEvent2 = $this->conn->query("SELECT * FROM Event WHERE BlockID = '$BLKID' ORDER BY ID DESC");
                                                $RowsNum = $sqlGetEvent2->num_rows;
                                                if($sqlGetEvent->num_rows <= 0)
                                                {
                                                    $this->returnResponse(200, []);
                                                    exit;
                                                }

                                                $count = 1;
                                                while($EventData = $sqlGetEvent->fetch_row())
                                                {
                                                    // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum)
                                                    {
                                                        $FLP = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum)
                                                    {
                                                        $FLP = 0;
                                                    }
                                                    if(empty($EventData[3]))
                                                    {
                                                        $attachmentURL = "https://kcgserver.com/omarty/Images/eventImages/Default.jpg";
                                                    }
                                                    elseif(!empty($EventData[3]))
                                                    {
                                                        $attachmentURL = "https://kcgserver.com/omarty/Images/eventImages/" . $EventData[3];
                                                    }
                                                
                                                     // Get User Name.
                                                    $sqlGetUserName = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $EventData[8]");
                                                    if($sqlGetUserName->num_rows > 0)
                                                    {
                                                        $residentData = $sqlGetUserName->fetch_row();
                                                        $RESNAME = $residentData[0];
                                                        if(!empty($residentData[1]))
                                                        {
                                                            $ResImageUrl = "https://kcgserver.com/omarty/Images/profilePictures/$residentData[1]";
                                                        }
                                                        elseif(empty($residentData[1]))
                                                        {
                                                            $ResImageUrl = "https://kcgserver.com/omarty/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    else
                                                    {
                                                        $RESNAME = $EventData[8];
                                                    }
                                                    // Get block manager name.
                                                    $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = $EventData[9]");
                                                    if($sqlGetAptNum->num_rows > 0)
                                                    {
                                                        $AptNum = $sqlGetAptNum->fetch_row();
                                                        $APTNUM = $AptNum[0];
                                                        $FloorNum = $AptNum[1];
                                                        $APTNAME = $AptNum[2];
                                                    }
                                                    else
                                                    {
                                                        $APTNUM = $EventData[9];
                                                        $FloorNum = NULL;
                                                        $APTNAME = $EventData[9];
                                                    }
                                                    // Get Block number.
                                                     $sqlGetBLKNUM = $this->conn->query("SELECT BlockNum, BlockName FROM Block WHERE ID = $EventData[10]");
                                                    if($sqlGetBLKNUM->num_rows > 0)
                                                    {
                                                        $BLKNUM = $sqlGetBLKNUM->fetch_row();
                                                        $BlockNum = $BLKNUM[0];
                                                        $BlockName = $BLKNUM[1];
                                                    }
                                                    else
                                                    {
                                                        $BlockNum = $EventData[10];
                                                        $BlockName = $EventData[10];
                                                    }
                                                    
                                                    $AttendStatus = NULL;
                                                    // Get Attend Status.
                                                    $sqlGetAttendStatus = $this->conn->query("SELECT Attend, Absent FROM Attendees WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Event' AND RecordID = '$EventData[0]'");
                                                    if($sqlGetAttendStatus->num_rows > 0)
                                                    {
                                                        $AttendStatusArr = $sqlGetAttendStatus->fetch_row();
                                                        $Likes = $AttendStatusArr[0];
                                                        $DisLikes = $AttendStatusArr[1];
                                                        if($AttendStatusArr[0] > 0 && $AttendStatusArr[1] <= 0)
                                                        {
                                                            $AttendStatus = TRUE;
                                                        }
                                                        elseif($AttendStatusArr[1] >= 0 && $AttendStatusArr[0] <= 0)
                                                        {
                                                            $AttendStatus = FALSE;
                                                        }
                                                        elseif($AttendStatusArr[0] == 0 && $AttendStatusArr[1] == 0)
                                                        {
                                                            $AttendStatus = NULL;
                                                        }
                                                    }
                                                    
                                                    $Event["Record$count"] = 
                                                    [
                                                        "eventId" => $EventData[0],
                                                        "tittle" => $EventData[1],
                                                        "body" => $EventData[2],
                                                        "image" => $attachmentURL,
                                                        "date" => $EventData[5],
                                                        "eventLocation" => $EventData[6],
                                                        "numOfAttendees" => $EventData[7],
                                                        "attendStatus" => $AttendStatus,
                                                        "residentName" => $RESNAME,
                                                        "residentImage" => $ResImageUrl,
                                                        "apartmentNumber" => $APTNUM,
                                                        "apartmentName" => $APTNAME,
                                                        "floorNumber" => $FloorNum,
                                                        "blockNumber" => $BlockNum,
                                                        "blockName" => $BlockName,
                                                        "flagLastPage" => $FLP
                                                    ];
                                                    $count++;
                                                }
                                                return array_values($Event);
                                            
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
    private function GetMeetings($BLKID, $APTID) // OK Final (Next / Previous)
    {
        // include("../Config.php");
        // $Page = $_POST["page"];
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
            
            // Get Block ID and Apartment ID
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $UserID = $decode->id;
            if(empty($BLKID))
            {
                $this->throwError(200, "Block Not found.");
            }
            // Check Block existence.
            $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
            if($sqlCheckBlock->num_rows > 0)
            {
                // Check Resident Relation to Block.
                $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                if($sqlCheckResBlkRel->num_rows > 0)
                {
                    // Check Block Status.
                    $blockData = $sqlCheckBlock->fetch_row();
                    if($blockData[1] == '2')
                    {
                            // Check apartment Existence.
                            // $sqlCheckAPT = $this->conn->query("SELECT ID FROM Apartment WHERE ID = '$APTID'");
                            $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                            
                            if($sqlCheckAPT->num_rows <= 0)
                            {
                                $this->throwError(200, "apartment not found in block");
                            }
                            elseif($sqlCheckAPT->num_rows > 0)
                            {
                                // Check Resident Relation to this apartment.
                                $AptData = $sqlCheckAPT->fetch_row();
                                if($AptData[2] == $UserID)
                                {
                                    // Check Apartment Status.
                                    if($AptData[1] == '2')
                                    {
                                        $CurrentDay = date("Y-m-d h:i:sa");

                                            // Select all Meetings with the same block id and they are all approved by block manager.
                                            $sqlGetMeeting = $this->conn->query("SELECT * FROM Meeting WHERE BlockID = '$BLKID' AND Approval = 1 ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                            $sqlGetMeeting2 = $this->conn->query("SELECT * FROM Meeting WHERE BlockID = '$BLKID' AND Approval = 1 ORDER BY CreatedAt DESC");
                                            $RowsNum = $sqlGetMeeting2->num_rows;
                                        
                                        $count = 1;
                                        while($MeetingData = $sqlGetMeeting->fetch_row())
                                        {
                                            // Get Last page flag.
                                            if(($Limit + $Start) >= $RowsNum)
                                            {
                                                $FLP = 1;
                                            }
                                            elseif(($Limit + $Start) < $RowsNum)
                                            {
                                                $FLP = 0;
                                            }
                                            $DecCount = 1;
                                            // Get Meetings Decisions.
                                            $sqlGetDec = $this->conn->query("SELECT ID, Decision, Likes, DisLikes, CreatedAt FROM Decision WHERE MeetingID = '$MeetingData[0]' ORDER BY ID DESC LIMIT $Start, $Limit");
                                            $sqlGetDec2 = $this->conn->query("SELECT ID, Decision, Likes, DisLikes, CreatedAt FROM Decision WHERE MeetingID = '$MeetingData[0]' ORDER BY ID DESC");
                                            $RowsNum3 = $sqlGetDec2->num_rows;
                                            $DecArr = [];
                                            while($Decision = $sqlGetDec->fetch_row())
                                            {
                                                // Get Last page flag.
                                                if(($Limit + $Start) >= $RowsNum3)
                                                {
                                                    $FLPD = 1;
                                                }
                                                elseif(($Limit + $Start) < $RowsNum3)
                                                {
                                                    $FLPD = 0;
                                                }
                                                
                                                // Get Vote Status.
                                                $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Decision' AND RecordID = '$Decision[0]'");
                                                if($sqlGetVoteStatus->num_rows > 0)
                                                {
                                                    $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                    $Likes = $VoteStatusArr[0];
                                                    $DisLikes = $VoteStatusArr[1];
                                                    if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                    {
                                                        $VoteStatus = TRUE;
                                                    }
                                                    elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                    {
                                                        $VoteStatus = FALSE;
                                                    }
                                                    elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                }
                                                elseif($sqlGetVoteStatus->num_rows <= 0)
                                                {
                                                    $VoteStatus = NULL;
                                                }
                                                
                                                $ComArr = [];
                                                $CommentCounter = 1;
                                                // Get Decision Comments.
                                                $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$Decision[0]' AND OriginalPostTable = 'Decision' ORDER BY ID DESC LIMIT $Start, $Limit");
                                                $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$Decision[0]' AND OriginalPostTable = 'Decision' ORDER BY ID DESC");
                                                $RowsNum2 = $sqlGetComment2->num_rows;
                                                while($Comment = $sqlGetComment->fetch_row())
                                                {
                                                    // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum2)
                                                    {
                                                        $FLPC = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum2)
                                                    {
                                                        $FLPC = 0;
                                                    }
                                                    // Get Resident Name Image 
                                                    $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                                    if($sqlGetRes->num_rows > 0)
                                                    {
                                                        $ResDT = $sqlGetRes->fetch_row();
                                                        $ResName = $ResDT[0];
                                                        if(!empty($ResDT[1]))
                                                        {
                                                            $ResImage = "https://kcgserver.com/omarty/Images/profilePictures/$ResDT[1]";
                                                        }
                                                        elseif(empty($ResDT[1]))
                                                        {
                                                            $ResImage = "https://kcgserver.com/omarty/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    elseif($sqlGetRes->num_rows <= 0)
                                                    {
                                                        $ResName = $Comment[2];
                                                        $Resimage = "";
                                                    }
                                                    // Get Apartment Num and Apartment Floor Num.
                                                    $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName, BlockID FROM Apartment WHERE ID = '$Comment[3]'");
                                                    if($sqlGetAptData->num_rows > 0)
                                                    {
                                                        $AptDataC = $sqlGetAptData->fetch_row();
                                                        $AptNumC = $AptDataC[0];
                                                        $AptFloorNumC = $AptDataC[1];
                                                        $AptNameC = $AptDataC[2];
                                                    }
                                                    if($sqlGetAptData->num_rows <= 0)
                                                    {
                                                        $AptNumC = $Comment[3];
                                                        $AptFloorNumC = $Comment[3];
                                                        $AptNameC = $Comment[3];
                                                    }
                                                    
                                                    // Get Block Number and name.
                                                    $sqlGetBlockName = $this->conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$AptDataC[3]'");
                                                    if($sqlGetBlockName->num_rows > 0)
                                                    {
                                                        $BlkDataC = $sqlGetBlockName->fetch_row();
                                                        $BlkIdC = $BlkDataC[0];
                                                        $BlkNumC = $BlkDataC[0];
                                                        $BlkNameC = $BlkDataC[1];
                                                    }
                                                    if($sqlGetBlockName->num_rows <= 0)
                                                    {
                                                        $BlkIdC = NULL;
                                                        $BlkNumC = NULL;
                                                        $BlkNameC = NULL;
                                                    }
                                                    
                                                    // Get Vote Status.
                                                    $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                                    if($sqlGetVoteStatus->num_rows > 0)
                                                    {
                                                        $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                        $Likes = $VoteStatusArr[0];
                                                        $DisLikes = $VoteStatusArr[1];
                                                        if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                        {
                                                            $VoteStatus = TRUE;
                                                        }
                                                        elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                        {
                                                            $VoteStatus = FALSE;
                                                        }
                                                        elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                    }
                                                    elseif($sqlGetVoteStatus->num_rows <= 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                    
                                                    $ComArr[$CommentCounter] = 
                                                    [
                                                        "commentId" => $Comment[0],
                                                        "comment" => $Comment[1],
                                                        "residentId" => $Comment[2],
                                                        "residentName" => $ResName,
                                                        "residentImage" => $ResImage,
                                                        "apartmentId" => $Comment[3],
                                                        "apartmentNumber" => $AptNumC,
                                                        "apartmentName" => $AptNameC,
                                                        "apartmentFloorNumber" => $AptFloorNumC,
                                                        "blockId" =>$BlkIdC,
                                                        "blockNumber" => $BlkNumC,
                                                        "blockName" => $BlkNameC,
                                                        "likes" => $Comment[4],
                                                        "disLikes" => $Comment[5],
                                                        "voteStatus" => $VoteStatus,
                                                        "createdAt" => $Comment[6],
                                                        "flagLastPage" => $FLPC
                                                    ];
                                                    $CommentCounter++;
                                                }
                                                $DecArr[$DecCount] = 
                                                [
                                                    "decisionID" => $Decision[0],
                                                    "decision" => $Decision[1],
                                                    "likes" => $Decision[2],
                                                    "disLikes" => $Decision[3],
                                                    "voteStatus" => $VoteStatus,
                                                    "createdAt" => $Decision[4],
                                                    "comments" => array_values($ComArr),
                                                    "flagLastPage" => $FLPD
                                                ];
                                                $DecCount++;
                                            }
                                            // $this->returnResponse(200, $DecArr);
                                            if(empty($MeetingData[3]))
                                            {
                                                $attachmentURL = "https://kcgserver.com/omarty/Images/meetingImages/Default.jpg";
                                            }
                                            elseif(!empty($MeetingData[3]))
                                            {
                                                $attachmentURL = "https://kcgserver.com/omarty/Images/meetingImages/" . $MeetingData[3];
                                            }
                                            
                                            // Get User Name.
                                            $sqlGetUserName = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $MeetingData[8]");
                                            if($sqlGetUserName->num_rows > 0)
                                            {
                                                $residentName = $sqlGetUserName->fetch_row();
                                                $RESNAME = $residentName[0];
                                                if(!empty($residentName[1]))
                                                {
                                                    $ResImageUrl = "https://kcgserver.com/omarty/Images/profilePictures/$residentName[1]";
                                                }
                                                elseif(empty($residentName[1]))
                                                {
                                                    $ResImageUrl = "https://kcgserver.com/omarty/Images/profilePictures/DefaultMale.png";
                                                }
                                            }
                                            else
                                            {
                                                $RESNAME = $MeetingData[8];
                                            }
                                            // Get block manager name.
                                            $sqlGetBLKMNGName = $this->conn->query("SELECT Name FROM Resident_User WHERE ID = $MeetingData[9]");
                                            if($sqlGetBLKMNGName->num_rows > 0)
                                            {
                                                $BLKMNGName = $sqlGetBLKMNGName->fetch_row();
                                                $BMNAME = $BLKMNGName[0];
                                            }
                                            else
                                            {
                                                $BMNAME = $MeetingData[9];
                                            }
                                            // Get Block number.
                                             $sqlGetBLKNUM = $this->conn->query("SELECT BlockNum, BlockName FROM Block WHERE ID = $MeetingData[10]");
                                            if($sqlGetBLKNUM->num_rows > 0)
                                            {
                                                $BLKNUM = $sqlGetBLKNUM->fetch_row();
                                                $BlockNum = $BLKNUM[0];
                                                $BlockName = $BLKNUM[1];
                                            }
                                            else
                                            {
                                                $BlockNum = $MeetingData[10];
                                                $BlockName = $MeetingData[10];
                                            }
                                            
                                            // Get Attend Status.
                                            $AttendStatus = NULL;
                                            $sqlGetAttendStatus = $this->conn->query("SELECT Attend, Absent FROM Attendees WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Meeting' AND RecordID = '$MeetingData[0]'");
                                            if($sqlGetAttendStatus->num_rows > 0)
                                            {
                                                $AttendStatusArr = $sqlGetAttendStatus->fetch_row();
                                                $Likes = $AttendStatusArr[0];
                                                $DisLikes = $AttendStatusArr[1];
                                                if($AttendStatusArr[0] > 0 && $AttendStatusArr[1] <= 0)
                                                {
                                                    $AttendStatus = TRUE;
                                                }
                                                elseif($AttendStatusArr[1] >= 0 && $AttendStatusArr[0] <= 0)
                                                {
                                                    $AttendStatus = FALSE;
                                                }
                                                elseif($AttendStatusArr[0] == 0 && $AttendStatusArr[1] == 0)
                                                {
                                                    $AttendStatus = NULL;
                                                }
                                            }
                                            
                                            $Meeting["Record$count"] = 
                                            [
                                                "id" => $MeetingData[0],
                                                "tittle" => $MeetingData[1],
                                                "body" => $MeetingData[2],
                                                "attachmentUrl" => $attachmentURL,
                                                "meetingDate" => $MeetingData[4],
                                                "location" => $MeetingData[5],
                                                "numOfAttendees" => $MeetingData[7],
                                                "attendStatus" => $AttendStatus,
                                                // "approval" => $MeetingData[6],
                                                "residentName" => $RESNAME,
                                                "residentImage" => $ResImageUrl,
                                                "blockManagerName" => $BMNAME,
                                                "blockNumber" => $BlockNum,
                                                "blockName" => $BlockName,
                                                "date" => $MeetingData[11],
                                                "decision" => array_values($DecArr),
                                                "flagLastPage" => $FLP
                                            ];
                                            $count++;
                                        }
                                        
                                        if($sqlGetMeeting->num_rows <= 0)
                                        {
                                            $Meeting = [];
                                        }
                                        return array_values($Meeting);
                                    }
                                    elseif($AptData[1] == '1')
                                    {
                                        $this->throwError(401, "Apartment Status is still Binding.");
                                    }
                                    elseif($AptData[1] == '3')
                                    {
                                        $this->throwError(401, "Apartment is Banned.");
                                    }
                                    else
                                    {
                                        $this->throwError(401, "Apartment status not acceptable.");
                                    }
                                }
                                else
                                {
                                    $this->throwError(401, "User does not relate to this apartment.");
    
                                }
                            }
                    }
                    elseif($blockData[1] == '1')
                    {
                        $this->throwError(200, "Block status is still Binding.");
                    }
                    elseif($blockData[1] == '3')
                    {
                        $this->throwError(200, "Block is Banned.");
                    }
                    else
                    {
                        $this->throwError(401, "Block status not acceptable.");
                    }
                }
                else
                {
                    $this->throwError(406, "User doesn't relate to this block.");
                }
            }
            else
            {
                $this->throwError(200, "Block Not found.");
            }
    }
    private function GetNews($BLKID, $APTID) // OK Final (Return User Data (Name, Apartment, floor number)).
    {
        // include("../Config.php");
        // $Page = $_POST["page"];
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
                // $this->returnResponse(200, $decode);
            }
            catch(Exception $e)
            {
                $this->throwError(401, $e->getMessage());
            }
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];
            $UserID = $decode->id;
           
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existense.
                    $sqlCheckBlock = $this->conn->query("SELECT ID FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check Resident Relation to Block.
                        $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status.
                            $sqlBlkStatus = $this->conn->query("SELECT StatusID FROM Block WHERE ID = '$BLKID'");
                            if($sqlBlkStatus->num_rows > 0)
                            {
                                $blockStatus = $sqlBlkStatus->fetch_row();
                                if($blockStatus[0] == "2")
                                {
                                    // Check apartment Existence.
                                    $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = $APTID AND BlockID = '$BLKID'");
                                    
                                    if($sqlCheckAPT->num_rows <= 0)
                                    {
                                        $this->throwError(200, "apartment not found in block");
                                    }
                                    elseif($sqlCheckAPT->num_rows > 0)
                                    {
                                        // Check User Relation to the Apartment.
                                        $AptData = $sqlCheckAPT->fetch_row();
                                        if($AptData[2] == $UserID)
                                        {
                                            // Check Apartment Status.
                                            if($AptData[1] == '2')
                                            {
                                                // Select all news with the same block id.
                                                $sqlGetNews = $this->conn->query("SELECT LetterOfNews, ResidentID, Date, Image, ApartmentID, Tittle, CreatedAt, ID FROM News WHERE BlockID = '$BLKID' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                                $sqlGetNews2 = $this->conn->query("SELECT LetterOfNews, ResidentID, Date, Image, ApartmentID, Tittle, CreatedAt, ID FROM News WHERE BlockID = '$BLKID' ORDER BY CreatedAt DESC");
                                                $RowsNum = $sqlGetNews2->num_rows;
                                                // if($sqlGetNews->num_rows <= 0)
                                                // {
                                                //     $this->throwError(200, []);
                                                // }
                                                
                                                    $count = 1;
                                                    while($NewsData = $sqlGetNews->fetch_row())
                                                    {
                                                        // Get Last page flag.
                                                        if(($Limit + $Start) >= $RowsNum)
                                                        {
                                                            $FLP = 1;
                                                        }
                                                        elseif(($Limit + $Start) < $RowsNum)
                                                        {
                                                            $FLP = 0;
                                                        }
                                                        
                                                        // Get Resident Name
                                                        $sqlGetResName = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = '$NewsData[1]'");
                                                        if($sqlGetResName->num_rows > 0)
                                                        {
                                                            $ResData = $sqlGetResName->fetch_row();
                                                            $ResName = $ResData[0];
                                                            if(!empty($ResData[1]))
                                                            {
                                                                $ResImageUrl = "https://kcgserver.com/omarty/Images/profilePictures/$ResData[1]";
                                                            }
                                                            elseif(empty($ResData[1]))
                                                            {
                                                                $ResImageUrl = "https://kcgserver.com/omarty/Images/profilePictures/DefaultMale.png";
                                                            }
                                                        }
                                                        elseif($sqlGetResName->num_rows <= 0)
                                                        {
                                                            $ResName = $NewsData[1];
                                                        }
                                                        // Get Res Floor number.
                                                        $sqlGetAptFloor = $this->conn->query("SELECT FloorNum, ApartmentNumber, ApartmentName FROM Apartment WHERE ID = '$NewsData[4]'");
                                                        if($sqlGetAptFloor->num_rows > 0)
                                                        {
                                                            $ResAPTFloorData = $sqlGetAptFloor->fetch_row();
                                                            $ResAPTFloor = $ResAPTFloorData[0];
                                                            $ResAPTnum = $ResAPTFloorData[1];
                                                            $ResAPTname = $ResAPTFloorData[2];
                                                        }
                                                        elseif($sqlGetResName->num_rows <= 0)
                                                        {
                                                            $ResAPTFloor = $NewsData[4];
                                                            $ResAPTnum = $NewsData[4];
                                                            $ResAPTname = $NewsData[4];
                                                        }
                                                        // Get News Image
                                                        
                                                        if(!empty($NewsData[3]))
                                                        {
                                                            $ImageUrl = "https://kcgserver.com/omarty/Images/newsImages/$NewsData[3]";
                                                        }
                                                        elseif(empty($NewsData[3]))
                                                        {
                                                            $ImageUrl = "https://kcgserver.com/omarty/Images/newsImages/Default.jpg";
                                                        }
                                                        
                                                        
                                                        $News["Record$count"] = 
                                                        [
                                                            "id" => $NewsData[7],
                                                            "residentId" => $NewsData[1],
                                                            "residentName" => $ResName,
                                                            "residentImage" => $ResImageUrl,
                                                            "apartmentNumber" =>$ResAPTnum,
                                                            "apartmentName" =>$ResAPTname,
                                                            "apartmentFloorNumber" => $ResAPTFloor,
                                                            "newsTittle" => $NewsData[5],
                                                            "letterOfNews" => $NewsData[0],
                                                            "image" => $ImageUrl,
                                                            "date" => $NewsData[6],
                                                            "flagLastPage" => $FLP
                                                        ];
                                                        $count++;
                                                    }
                                                    if($sqlGetNews->num_rows <= 0)
                                                    {
                                                        $News = [];
                                                    }
                                                    return array_values($News);
                                                   
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
                                                $this->throwError(401, "Apartment status not acceptable.");
                                            }
                                        }
                                        else
                                        {
                                            $this->throwError(401, "User does not relate to this apartment.");
                                        }
                                    }
                                }
                                elseif($blockStatus[0] == "1")
                                {
                                    $this->throwError(200, "Block status is Binding.");
                                }
                                elseif($blockStatus[0] == "3")
                                {
                                    $this->throwError(200, "Block is Banned.");
                                }
                                else
                                {
                                    $this->throwError(401, "Block status not acceptable.");
                                }
                            }
                            else
                            {
                                $this->throwError(401, "Block status is not acceptble.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "User does not relate to this block.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Block Not Found.");
                    }
                }
    }
    private function GetOffers($BLKID, $APTID)
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
        
        // Setting Paging.
        // $Page = $_POST["page"];
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
            $date = date("Y-m-d H:i:s");
            $UserID = $decode->id;
            
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter Block ID.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlCheckBlock = $this->conn->query("SELECT * FROM Block WHERE ID = '$BLKID'");
                    if($sqlCheckBlock->num_rows > 0)
                    {
                        // Check User in block.
                        $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
                        if($sqlCheckResBlkRel->num_rows > 0)
                        {
                            // Get Block Data.
                            $blockData = $sqlCheckBlock->fetch_row();
                            // Check Block Status
                            if($blockData[16] == "2")
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
                                            // Get All Offers.
                                            $sqlGetOffers = $this->conn->query("SELECT * FROM AdsAndOffers WHERE StatusID = 2 AND StartDate < '$date' AND EndDate > '$date' ORDER BY ID DESC");
                                            $Arr = [];
                                            $count = 0;
                                            
                                            while($rows = $sqlGetOffers->fetch_row())
                                            {
                                                if(!empty($rows[4]))
                                                {
                                                    $ImgUrl = "https://kcgserver.com/omarty/Images/AdsAndOffers/$rows[4]";
                                                }
                                                else
                                                {
                                                    $ImgUrl = "https://kcgserver.com/omarty/Images/AdsAndOffers/Default.png";
                                                }
                                                // if All Locations apply ( Country - Governate - City - Region - Compound).
                                                if($rows[11] == $blockData[10] && $rows[12] == $blockData[11] && $rows[13] == $blockData[12] && $rows[14] == $blockData[13] && $rows[15] == $blockData[14])
                                                {
                                                    $Arr[$count] = 
                                                    [
                                                        "id" => $rows[0],
                                                        "tittle" => $rows[1],
                                                        "body" => $rows[2],
                                                        "owner" => $rows[3],
                                                        "image" => $ImgUrl,
                                                    ];    
                                                }
                                                // if Only Compound doesn't comply
                                                if($rows[11] == $blockData[10] && $rows[12] == $blockData[11] && $rows[13] == $blockData[12] && $rows[14] == $blockData[13] && $rows[15] == NULL)
                                                {
                                                    
                                                    $Arr[$count] = 
                                                    [
                                                        "id" => $rows[0],
                                                        "tittle" => $rows[1],
                                                        "body" => $rows[2],
                                                        "owner" => $rows[3],
                                                        "image" => $ImgUrl,
                                                    ];    
                                                }
                                                // if Only Region And Compound doesn't comply
                                                if($rows[11] == $blockData[10] && $rows[12] == $blockData[11] && $rows[13] == $blockData[12] && $rows[14] == NULL && $rows[15] == NULL)
                                                {
                                                    $Arr[$count] = 
                                                    [
                                                        "id" => $rows[0],
                                                        "tittle" => $rows[1],
                                                        "body" => $rows[2],
                                                        "owner" => $rows[3],
                                                        "image" => $ImgUrl,
                                                    ];    
                                                }
                                                // // if Only City And Region And Compound doesn't comply
                                                if($rows[11] == $blockData[10] && $rows[12] == $blockData[11] && $rows[13] == NULL && $rows[14] == NULL && $rows[15] == NULL)
                                                {
                                                    $Arr[$count] = 
                                                    [
                                                        "id" => $rows[0],
                                                        "tittle" => $rows[1],
                                                        "body" => $rows[2],
                                                        "owner" => $rows[3],
                                                        "image" => $ImgUrl,
                                                    ];    
                                                }
                                                // // if Only Gov And City And Region And Compound doesn't comply
                                                if(($rows[11] == $blockData[10]) && ($rows[12] == NULL) && ($rows[13] == NULL) && ($rows[14] == NULL) && ($rows[15] == NULL))
                                                {
                                                    $Arr[$count] = 
                                                    [
                                                        "id" => $rows[0],
                                                        "tittle" => $rows[1],
                                                        "body" => $rows[2],
                                                        "owner" => $rows[3],
                                                        "image" => $ImgUrl,
                                                    ];    
                                                }
                                                $count++;
                                            }
                                            return array_values($Arr);
                                        }
                                        elseif($AptData[16] == '1')
                                        {
                                            $this->throwError(200, "Apartment status is still binding.");
                                        }
                                        elseif($AptData[16] == '3')
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
    private function GetServices($BLKID, $APTID) // OK Final
    {
        // include("../Config.php");
    //   $Page = $_POST["page"];
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
            
        // Get Block ID and Apartment ID
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $UserID = $decode->id;
        if(empty($BLKID))
        {
            $this->throwError(200, "Block Not found.");
        }
        // Check Block existence.
        $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID, CityID, RegionID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows > 0)
        {
            // Check Resident Relation to Block.
            $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                $blockData = $sqlCheckBlock->fetch_row();
                // Check Block Status.
                if($blockData[1] == '2')
                {
                    // Check apartment Existence.
                    $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                    if($sqlCheckAPT->num_rows <= 0)
                    {
                        $this->throwError(200, "apartment not found in block");
                    }
                    elseif($sqlCheckAPT->num_rows > 0)
                    {
                        $AptData = $sqlCheckAPT->fetch_row();
                        // Check Resident relation to the apartment
                        if($AptData[2] == $UserID)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == '2')
                            {
            
                                // Select all Services with the same Region id.
                                
                                    $sqlGetService = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]' LIMIT $Start, $Limit");
                                    $sqlGetService2 = $this->conn->query("SELECT * FROM Service WHERE RegionID = '$blockData[3]'");
                                    $RowsNum = $sqlGetService2->num_rows;
                                
                                // ========================================================================================================
                                
                                    $count = 1;
                                    while($ServiceData = $sqlGetService->fetch_row())
                                    {
                                        // Get Last page flag.
                                        if(($Limit + $Start) >= $RowsNum)
                                        {
                                            $FLP = 1;
                                        }
                                        elseif(($Limit + $Start) < $RowsNum)
                                        {
                                            $FLP = 0;
                                        }
                                        
                                        // Check Comments
                                        $ComArr = [];
                                        $CommentCounter = 1;
                                        // Get Service Comments.
                                        $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$ServiceData[0]' AND OriginalPostTable = 'Service'");
                                        $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$ServiceData[0]' AND OriginalPostTable = 'Service'");
                                        $RowsNum2 = $sqlGetComment2->num_rows;
                                        while($Comment = $sqlGetComment->fetch_row())
                                        {
                                            // Get Last page flag.
                                            if(($Limit + $Start) >= $RowsNum2)
                                            {
                                                $FLPC = 1;
                                            }
                                            elseif(($Limit + $Start) < $RowsNum2)
                                            {
                                                $FLPC = 0;
                                            }
                                            // Get Resident Name Image 
                                            $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                            if($sqlGetRes->num_rows > 0)
                                            {
                                                $ResDT = $sqlGetRes->fetch_row();
                                                $ResName = $ResDT[0];
                                                if(!empty($ResDT[1]))
                                                {
                                                    $ResImage = "https://kcgserver.com/omarty/Images/profilePictures/$ResDT[1]";
                                                }
                                                elseif(empty($ResDT[1]))
                                                {
                                                    $ResImage = "https://kcgserver.com/omarty/Images/profilePictures/DefaultMale.png";
                                                }
                                            }
                                            elseif($sqlGetRes->num_rows <= 0)
                                            {
                                                $ResName = $Comment[2];
                                                $Resimage = "";
                                            }
                                            // Get Apartment Num and Apartment Floor Num.
                                            $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = '$Comment[3]'");
                                            if($sqlGetAptData->num_rows > 0)
                                            {
                                                $AptDataC = $sqlGetAptData->fetch_row();
                                                $AptNumC = $AptDataC[0];
                                                $AptFloorNumC = $AptDataC[1];
                                                $AptNameC = $AptDataC[2];
                                            }
                                            if($sqlGetAptData->num_rows <= 0)
                                            {
                                                $AptNumC = $Comment[3];
                                                $AptFloorNumC = $Comment[3];
                                                $AptNameC = $Comment[3];
                                            }
                                            
                                            // Get Vote Status.
                                            $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                            if($sqlGetVoteStatus->num_rows > 0)
                                            {
                                                $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                $Likes = $VoteStatusArr[0];
                                                $DisLikes = $VoteStatusArr[1];
                                                if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                {
                                                    $VoteStatus = TRUE;
                                                }
                                                elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                {
                                                    $VoteStatus = FALSE;
                                                }
                                                elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                {
                                                    $VoteStatus = NULL;
                                                }
                                            }
                                            elseif($sqlGetVoteStatus->num_rows <= 0)
                                            {
                                                $VoteStatus = NULL;
                                            }
                                            
                                            $ComArr[$CommentCounter] = 
                                            [
                                                "commentId" => $Comment[0],
                                                "comment" => $Comment[1],
                                                "residentId" => $Comment[2],
                                                "residentName" => $ResName,
                                                "residentImage" => $ResImage,
                                                "apartmentId" => $Comment[3],
                                                "apartmentNumber" => $AptNumC,
                                                "apartmentName" => $AptNameC,
                                                "apartmentFloorNumber" => $AptFloorNumC,
                                                "likes" => $Comment[4],
                                                "disLikes" => $Comment[5],
                                                "voteStatus" => $VoteStatus,
                                                "flagLastPage" => $FLPC
                                            ];
                                            $CommentCounter++;
                                        }
                                    //   ==============================================================================================
                                        if(empty($ServiceData[7]))
                                        {
                                            $attachmentURL = "https://kcgserver.com/omarty/Images/serviceImages/Default.jpg";
                                        }
                                        elseif(!empty($ServiceData[7]))
                                        {
                                            $attachmentURL = "https://kcgserver.com/omarty/Images/serviceImages/" . $ServiceData[7];
                                        }
                                                
                                        // Get User Name.
                                        $sqlGetUserName = $this->conn->query("SELECT UserName FROM Resident_User WHERE ID = '$ServiceData[11]'");
                                        if($sqlGetUserName->num_rows > 0)
                                        {
                                            $residentName = $sqlGetUserName->fetch_row();
                                            $RESNAME = $residentName[0];
                                        }
                                        else
                                        {
                                            // if Coudln't get resident Username get his ID.
                                            $RESNAME = $ServiceData[11]; // Standing here.
                                        }
                
                                        // Get Service Phone Numbers in $PhoneNums array.
                                        $PhoneNums = ["phoneNum1" => $ServiceData[3]];
                                        if(!empty($ServiceData[4]))
                                        {
                                            $PhoneNums += ["phoneNum2" => $ServiceData[4]];
                                            // $this->returnResponse(200, $PhoneNums);
                                        }
                                        if(!empty($ServiceData[5]))
                                        {
                                            $PhoneNums += ["phoneNum3" => $ServiceData[5]];
                                            // $this->returnResponse(200, $PhoneNums);
                                        }
                                        if(!empty($ServiceData[6]))
                                        {
                                            $PhoneNums += ["phoneNum4" => $ServiceData[6]];
                                            // $this->returnResponse(200, $PhoneNums);
                                        }
                                        
                                        // Get Block number.
                                        $sqlGetBLKNUM = $this->conn->query("SELECT BlockNum FROM Block WHERE ID = $ServiceData[13]");
                                        if($sqlGetBLKNUM->num_rows > 0)
                                        {
                                            $BLKNUM = $sqlGetBLKNUM->fetch_row();
                                            $BlockNum = $BLKNUM[0];
                                        }
                                        else
                                        {
                                            $BlockNum = $ServiceData[13];
                                        }
                                        // Get CategoryID.
                                        $sqlGetCatId = $this->conn->query("SELECT CategoryID FROM Service WHERE ID = $ServiceData[0]");
                                        if($sqlGetCatId->num_rows > 0)
                                        {
                                           $CatID = $sqlGetCatId->fetch_row();
                                        }
                                        else
                                        {
                                            $CatID[0] = 1;
                                        }
                                        //  Recheck Fetching services favourite or not.
                                        $sqlCheckFav = $this->conn->query("SELECT * FROM Favourite WHERE ResidentID = '$UserID' AND ServiceID = '$ServiceData[0]' AND CategoryID = '$CatID[0]'");
                                        // Check if favourite or not
                                        if($sqlCheckFav->num_rows > 0)
                                        {
                                            $Favourite = true;
                                        }
                                        elseif($sqlCheckFav->num_rows <= 0)
                                        {
                                            $Favourite = false;
                                        }
                                        // ==========================================================================================================================================
                                        // Get Category Name
                                        $sqlGetSerCat = $this->conn->query("SELECT Name FROM ServiceCategory WHERE ID = $ServiceData[10]");
                                        if($sqlGetSerCat->num_rows > 0)
                                        {
                                            $CatName = $sqlGetSerCat->fetch_row();
                                        }
                                        elseif($sqlGetSerCat->num_rows <= 0)
                                        {
                                            $CatName[0] = $ServiceData[10];
                                        }
                                        
                                        // Get Country
                                        $sqlGetCountry = $this->conn->query("SELECT name From Country Where ID = $ServiceData[16]");
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
                                        $sqlGetGov = $this->conn->query("SELECT GOVName From Governate Where ID = $ServiceData[17]");
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
                                        $sqlGetCity = $this->conn->query("SELECT Name From City Where ID = $ServiceData[18]");
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
                                        $sqlGetRegion = $this->conn->query("SELECT RegionName From Region Where ID = $ServiceData[19]");
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
                                        $sqlGetCompound = $this->conn->query("SELECT CompundName From Compound Where ID = $ServiceData[20]");
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
                                        $sqlGetStreet = $this->conn->query("SELECT StreetName From Street Where ID = $ServiceData[21]");
                                        if($sqlGetStreet->num_rows > 0)
                                        {
                                            $StreetNameArr = $sqlGetStreet->fetch_row();
                                            $StreetName = $StreetNameArr[0];
                                        }
                                        elseif($sqlGetStreet->num_rows <= 0)
                                        {
                                            $StreetName = $ServiceData[21];
                                        }
                                        $Service["Record$count"] = 
                                        [
                                            "id" => $ServiceData[0],
                                            "name" => $ServiceData[1],
                                            "isFav" => $Favourite,
                                            "description" => $ServiceData[2],
                                            "phoneNums" => $PhoneNums,
                                            "image" => $attachmentURL,
                                            "rate" => $ServiceData[8],
                                            "categoryID" => $ServiceData[10],
                                            "categoryName" => $CatName[0],
                                            "Comments" => array_values($ComArr),
                                            "latitude" => $ServiceData[14],
                                            "longitude" => $ServiceData[15],      
                                            "countryName" => $CountryName,
                                            "governateName" => $GovName,
                                            "cityName" => $CityName,
                                            "regionName" => $RegionName,
                                            "compoundName" => $CompName,
                                            "streetName" => $StreetName,
                                            "flagLastPage" => $FLP
                                        ];
                                        $count++;
                                    }
                                    if($sqlGetService->num_rows <= 0)
                                    {
                                        $Service = [];
                                    }
                                    return array_values($Service);
                                
                            }
                            elseif($AptData[1] == "1")
                            {
                                $this->throwError(200, "Apartment status is still binding.");
                            }
                            elseif($AptData[1] == "3")
                            {
                                $this->throwError(200, "Apartment is Banned.");
                            }
                            else
                            {
                                $this->throwError(200, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(401, "Resident doesn't have any relation to this apartment.");
                        }
                    }
                }
                elseif($blockData[1] == '1')
                {
                    $this->throwError(200, "Block status is still binding.");
                }
                elseif($blockData[1] == '3')
                {
                    $this->throwError(200, "Block is Banned.");
                }
                else
                {
                    $this->throwError(200, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "User does not relate to this block.");
            }
        }
        else
        {
            $this->throwError(200, "Block Not found.");
        }
    }
    private function GetFavourites($BLKID, $APTID) // for Block Manager
    {
        // include("../Config.php");
        // $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 100;
        $Start = ($Page - 1) * $Limit;
            
            // Check Block Existence.
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
            $UserID = $decode->id;
            $BLKID = $_POST["blockId"];
            $APTID = $_POST["apartmentId"];

            if(empty($BLKID))
            {
                $this->throwError(200, "Please Enter Block ID.");
            }
            elseif(!empty($BLKID))
            {
                // Check Block Existence.
                $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
                if($sqlCheckBlock->num_rows > 0)
                {
                    // Check Block Status 
                    $blockData = $sqlCheckBlock->fetch_row();
                    if($blockData[1] == "2")
                    {   
                        // Check apartment Existence and user status.
                        $sqlCheckAPT = $this->conn->query("SELECT ApartmentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID'");
                        
                        if($sqlCheckAPT->num_rows <= 0)
                        {
                            $this->throwError(200, "apartment not found in block");
                        }
                        elseif($sqlCheckAPT->num_rows > 0)
                        {
                            // Check Apartment Status 
                            $aptData = $sqlCheckAPT->fetch_row();
                            if($aptData[1] == '2')
                            {
                                if(empty($categoryID))
                                {
                                    // Select all Favourites with the same ResidentID.
                                    $sqlGetFavourite = $this->conn->query("SELECT * FROM Favourite WHERE ResidentID = '$UserID' LIMIT $Start, $Limit");
                                    $sqlGetFavourite2 = $this->conn->query("SELECT * FROM Favourite WHERE ResidentID = '$UserID'");
                                    $RowsNum = $sqlGetFavourite2->num_rows;
                                }
                                
                                 $count = 1;
                                 $Favourite = [];
                                    while($FavouriteData = $sqlGetFavourite->fetch_row())
                                    {
                                        // Get Last page flag.
                                        if(($Limit + $Start) >= $RowsNum)
                                        {
                                            $FLP = 1;
                                        }
                                        elseif(($Limit + $Start) < $RowsNum)
                                        {
                                            $FLP = 0;
                                        }
                                        
                                        // Get Category Name.
                                        $sqlGetCategoryName = $this->conn->query("SELECT Name_ar FROM ServiceCategory WHERE ID = '$FavouriteData[5]'");
                                        if($sqlGetCategoryName->num_rows > 0)
                                        {
                                            $CatName = $sqlGetCategoryName->fetch_row();
                                        }
                                        elseif($sqlGetCategoryName->num_rows < 0)
                                        {
                                            $CatName[0] = $categoryID;
                                        }
                                            
                                        // Fetched Fav is Service.
                                        if(empty($FavouriteData[7]))
                                        {
                                            // ============================================================================================================================================
                                            // Get Service Data.
                                            $sqlGetSerData = $this->conn->query("SELECT * FROM Service WHERE ID = $FavouriteData[6]");
                                            if($sqlGetSerData->num_rows > 0)
                                            {
                                                $ServiceData = $sqlGetSerData->fetch_row();
                                                // Check Comments
                                                $ComArr = [];
                                                $CommentCounter = 1;
                                                // Get Service Comments.
                                                $sqlGetComment = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$ServiceData[0]' AND OriginalPostTable = 'Service'");
                                                $sqlGetComment2 = $this->conn->query("SELECT ID, CommentText, ResidentID, ApartmentID, Likes, DisLikes, CreatedAt FROM Comment WHERE OriginalPostID = '$ServiceData[0]' AND OriginalPostTable = 'Service'");
                                                $RowsNum2 = $sqlGetComment2->num_rows;
                                                while($Comment = $sqlGetComment->fetch_row())
                                                {
                                                    // Get Last page flag.
                                                    if(($Limit + $Start) >= $RowsNum2)
                                                    {
                                                        $FLPC = 1;
                                                    }
                                                    elseif(($Limit + $Start) < $RowsNum2)
                                                    {
                                                        $FLPC = 0;
                                                    }
                                                    // Get Resident Name Image 
                                                    $sqlGetRes = $this->conn->query("SELECT Name, Image FROM Resident_User WHERE ID = $Comment[2]");
                                                    if($sqlGetRes->num_rows > 0)
                                                    {
                                                        $ResDT = $sqlGetRes->fetch_row();
                                                        $ResName = $ResDT[0];
                                                        if(!empty($ResDT[1]))
                                                        {
                                                            $ResImage = "https://kcgserver.com/omarty/Images/profilePictures/$ResDT[1]";
                                                        }
                                                        elseif(empty($ResDT[1]))
                                                        {
                                                            $ResImage = "https://kcgserver.com/omarty/Images/profilePictures/DefaultMale.png";
                                                        }
                                                    }
                                                    elseif($sqlGetRes->num_rows <= 0)
                                                    {
                                                        $ResName = $Comment[2];
                                                        $Resimage = "";
                                                    }
                                                    // Get Apartment Num and Apartment Floor Num.
                                                    $sqlGetAptData = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName, BlockID FROM Apartment WHERE ID = '$Comment[3]'");
                                                    if($sqlGetAptData->num_rows > 0)
                                                    {
                                                        $AptDataC = $sqlGetAptData->fetch_row();
                                                        $AptNumC = $AptDataC[0];
                                                        $AptFloorNumC = $AptDataC[1];
                                                        $AptNameC = $AptDataC[2];
                                                    }
                                                    if($sqlGetAptData->num_rows <= 0)
                                                    {
                                                        $AptNumC = $Comment[3];
                                                        $AptFloorNumC = $Comment[3];
                                                        $AptNameC = $Comment[3];
                                                    }
                                                    
                                                    // Get Block Number and name.
                                                    $sqlGetBlockName = $this->conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$AptDataC[3]'");
                                                    if($sqlGetBlockName->num_rows > 0)
                                                    {
                                                        $BlkDataC = $sqlGetBlockName->fetch_row();
                                                        $BlkIdC = $BlkDataC[0];
                                                        $BlkNumC = $BlkDataC[0];
                                                        $BlkNameC = $BlkDataC[1];
                                                    }
                                                    if($sqlGetBlockName->num_rows <= 0)
                                                    {
                                                        $BlkIdC = NULL;
                                                        $BlkNumC = NULL;
                                                        $BlkNameC = NULL;
                                                    }
                                                    
                                                    // Get Vote Status.
                                                    $sqlGetVoteStatus = $this->conn->query("SELECT UpLike, DownDisLike FROM Likes WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID' AND TableName = 'Comment' AND RecordID = '$Comment[0]'");
                                                    if($sqlGetVoteStatus->num_rows > 0)
                                                    {
                                                        $VoteStatusArr = $sqlGetVoteStatus->fetch_row();
                                                        $Likes = $VoteStatusArr[0];
                                                        $DisLikes = $VoteStatusArr[1];
                                                        if($VoteStatusArr[0] > 0 && $VoteStatusArr[1] <= 0)
                                                        {
                                                            $VoteStatus = TRUE;
                                                        }
                                                        elseif($VoteStatusArr[1] > 0 && $VoteStatusArr[0] <= 0)
                                                        {
                                                            $VoteStatus = FALSE;
                                                        }
                                                        elseif($VoteStatusArr[0] == 0 && $VoteStatusArr[1] == 0)
                                                        {
                                                            $VoteStatus = NULL;
                                                        }
                                                    }
                                                    elseif($sqlGetVoteStatus->num_rows <= 0)
                                                    {
                                                        $VoteStatus = NULL;
                                                    }
                                                    
                                                    $ComArr[$CommentCounter] = 
                                                    [
                                                        "commentId" => $Comment[0],
                                                        "comment" => $Comment[1],
                                                        "residentId" => $Comment[2],
                                                        "residentName" => $ResName,
                                                        "residentImage" => $ResImage,
                                                        "apartmentId" => $Comment[3],
                                                        "apartmentNumber" => $AptNumC,
                                                        "apartmentName" => $AptNameC,
                                                        "apartmentFloorNumber" => $AptFloorNumC,
                                                        "blockId" => $BlkIdC,
                                                        "blockNumber" => $BlkNumC,
                                                        "blockName" => $BlkNameC,
                                                        "likes" => $Comment[4],
                                                        "disLikes" => $Comment[5],
                                                        "voteStatus" => $VoteStatus,
                                                        "flagLastPage" => $FLPC
                                                    ];
                                                    $CommentCounter++;
                                                }
                                                //   ==============================================================================================
                                                if(empty($ServiceData[7]))
                                                {
                                                    $attachmentURL = "https://kcgserver.com/omarty/Images/serviceImages/Default.jpg";
                                                }
                                                elseif(!empty($ServiceData[7]))
                                                {
                                                    $attachmentURL = "https://kcgserver.com/omarty/Images/serviceImages/" . $ServiceData[7];
                                                }
                                                        
                                                // Get User Name.
                                                $sqlGetUserName = $this->conn->query("SELECT Name FROM Resident_User WHERE ID = '$ServiceData[11]'");
                                                if($sqlGetUserName->num_rows > 0)
                                                {
                                                    $residentName = $sqlGetUserName->fetch_row();
                                                    $RESNAME = $residentName[0];
                                                }
                                                else
                                                {
                                                    // if Coudln't get resident Username get his ID.
                                                    $RESNAME = $ServiceData[11]; // Standing here.
                                                }
                        
                                                // Get Service Phone Numbers in $PhoneNums array.
                                                $PhoneNums = ["phoneNum1" => $ServiceData[3]];
                                                if(!empty($ServiceData[4]))
                                                {
                                                    $PhoneNums += ["phoneNum2" => $ServiceData[4]];
                                                    // $this->returnResponse(200, $PhoneNums);
                                                }
                                                if(!empty($ServiceData[5]))
                                                {
                                                    $PhoneNums += ["phoneNum3" => $ServiceData[5]];
                                                    // $this->returnResponse(200, $PhoneNums);
                                                }
                                                if(!empty($ServiceData[6]))
                                                {
                                                    $PhoneNums += ["phoneNum4" => $ServiceData[6]];
                                                    // $this->returnResponse(200, $PhoneNums);
                                                }
                                                
                                                // Get Block number.
                                                $sqlGetBLKNUM = $this->conn->query("SELECT BlockNum FROM Block WHERE ID = '$ServiceData[13]'");
                                                if($sqlGetBLKNUM->num_rows > 0)
                                                {
                                                    $BLKNUM = $sqlGetBLKNUM->fetch_row();
                                                    $BlockNum = $BLKNUM[0];
                                                }
                                                else
                                                {
                                                    $BlockNum = $ServiceData[13];
                                                }
                                                
                                                // Get Category Name
                                                $sqlGetSerCat = $this->conn->query("SELECT Name_ar FROM ServiceCategory WHERE ID = '$ServiceData[10]'");
                                                if($sqlGetSerCat->num_rows > 0)
                                                {
                                                    $CatName = $sqlGetSerCat->fetch_row();
                                                }
                                                elseif($sqlGetSerCat->num_rows <= 0)
                                                {
                                                    $CatName[0] = $ServiceData[10];
                                                }
                                                
                                                // Get Country
                                                $sqlGetCountry = $this->conn->query("SELECT name From Country Where ID = '$ServiceData[16]'");
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
                                                $sqlGetGov = $this->conn->query("SELECT GOVName From Governate Where ID = '$ServiceData[17]'");
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
                                                $sqlGetCity = $this->conn->query("SELECT Name From City Where ID = '$ServiceData[18]'");
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
                                                $sqlGetRegion = $this->conn->query("SELECT RegionName From Region Where ID = '$ServiceData[19]'");
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
                                                $sqlGetCompound = $this->conn->query("SELECT CompundName From Compound Where ID = '$ServiceData[20]'");
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
                                                $sqlGetStreet = $this->conn->query("SELECT StreetName From Street Where ID = '$ServiceData[21]'");
                                                if($sqlGetStreet->num_rows > 0)
                                                {
                                                    $StreetNameArr = $sqlGetStreet->fetch_row();
                                                    $StreetName = $StreetNameArr[0];
                                                }
                                                elseif($sqlGetStreet->num_rows <= 0)
                                                {
                                                    $StreetName = $ServiceData[21];
                                                }
                                                // ============================================================================================================================================
                                                // Get Service Data
                                                $Favourite["Record$count"] = 
                                                [
                                                    "id" => $ServiceData[0],
                                                    "name" => $ServiceData[1],
                                                    // "isFav" => $Favourite,
                                                    "description" => $ServiceData[2],
                                                    "phoneNums" => $PhoneNums,
                                                    "image" => $attachmentURL,
                                                    "rate" => $ServiceData[8],
                                                    "categoryID" => $ServiceData[10],
                                                    "categoryName" => $CatName[0],
                                                    "Comments" => array_values($ComArr),
                                                    "latitude" => $ServiceData[14],
                                                    "longitude" => $ServiceData[15],      
                                                    "countryName" => $CountryName,
                                                    "governateName" => $GovName,
                                                    "cityName" => $CityName,
                                                    "regionName" => $RegionName,
                                                    "compoundName" => $CompName,
                                                    "streetName" => $StreetName,
                                                    "flagLastPage" => $FLP
                                                ];    
                                            }
                                            
                                        }
                                        // Fetched Fav is Neighbour.
                                        elseif(!empty($FavouriteData[7]))
                                        {
                                            // Get Neighbour Data
                                            // Get User Phone number.
                                            $sqlGetPN = $this->conn->query("SELECT Name, PhoneNum, Image FROM Resident_User WHERE ID = $FavouriteData[7]");
                                            if($sqlGetPN->num_rows > 0)
                                            {
                                                $residentPN = $sqlGetPN->fetch_row();
                                                $RESpn = $residentPN[1];
                                                $RESname = $residentPN[0];
                                                // get image.
                                                if(!empty($residentPN[2]))
                                                {
                                                    $ResidentImage = "https://kcgserver.com/omarty/Images/profilePictures/$residentPN[2]";
                                                }
                                                elseif(empty($residentPN[2]))
                                                {
                                                    $ResidentImage = "https://kcgserver.com/omarty/Images/profilePictures/DefaultMale.png";
                                                }
                                            }
                                            else
                                            {
                                                $RESpn = $FavouriteData[7];
                                            }
                                            // Get Neighbour ApartmentID.
                                            $sqlGetNeighAptId = $this->conn->query("SELECT ApartmentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$FavouriteData[7]' AND BlockID = '$BLKID'");
                                            if($this->conn->error)
                                            {
                                                echo $this->conn->error;
                                            }
                                            if($sqlGetNeighAptId->num_rows > 0)
                                            {
                                                $NeighAptId = $sqlGetNeighAptId->fetch_row();
                                                // Get Apatment Number and Floor number..
                                                $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = $NeighAptId[0]");
                                                if($sqlGetAptNum->num_rows > 0)
                                                {
                                                    $AptNum = $sqlGetAptNum->fetch_row();
                                                    $APTNUM = $AptNum[0];
                                                    $APTFLRNUM = $AptNum[1];
                                                    $APTNAME = $AptNum[2];
                                                }
                                                else
                                                {
                                                    $APTNUM = $NeighAptId[0];
                                                    $APTFLRNUM = $NeighAptId[0];
                                                    $APTNAME = $NeighAptId[0];
                                                }
                                            }
                                            
                                            $Favourite["Record$count"] = 
                                            [
                                                "id" => $FavouriteData[7],
                                                "residentName" => $RESname,
                                                "residentImage" => $ResidentImage,
                                                "apartmentNumber" => $APTNUM,
                                                "apartmentName" => $APTNAME,
                                                "apartmentFloorNumber" => $APTFLRNUM,
                                                "phoneNumber" => $RESpn,
                                                "categoryName" => "جيراني",
                                                "flagLastPage" => $FLP,
                                            ];
                                        }
                                        $count++;
                                    }
                                    // if($Favourite == NULL)
                                    // {
                                    //     $this->returnResponse(200, []);
                                    // }
                                    // else
                                    // {
                                        return array_values($Favourite);
                                    // }
                                
                                // ==================================================================================================================================
                            }
                            elseif($aptData[1] == '1')
                            {
                                $this->throwError(200, "Apartment status is Binding.");
                            }
                            elseif($aptData[1] == '3')
                            {
                                $this->throwError(200, "Apartment is  Banned.");
                            }
                            else
                            {
                                $this->throwError(200, "Apartment status is not acceptable.");
                            }
                        }
                    }
                    elseif($blockData[1] == "1")
                    {
                        $this->throwError(200, "Block status is Binding.");
                    }
                    elseif($blockData[1] == "3")
                    {
                        $this->throwError(200, "Block is Banned.");
                    }
                    else
                    {
                        $this->throwError(200, "Block status is not acceptable.");
                    }
                }
                elseif($sqlCheckBlock->num_rows <= 0)
                {
                    $this->throwError(200, "Block Not Found.");
                }
            }
            
    }
    private function GetHeader($BLKID, $APTID)
    {
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
            exit;
        }
        
        // Get User ID.
        $UserID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        
        // Check Block Existence.
        $sqlCheckBlock = $this->conn->query("SELECT BlockID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$UserID'");
        if($sqlCheckBlock->num_rows > 0)
        {
            
            if(empty($UserID))
            {
                // If User Was not found.
                $this->throwError(403, "User not found. please enter your registered phone number");
                exit;
            }
            elseif(!empty($UserID))
            {
                // get Apartment Data from RES_APART_BLOCK_ROLE.
                if(empty($APTID))
                {
                    // if apartment was not found.
                    $this->throwError(200, "Please Enter Apartment ID");
                    exit;
                }
                elseif(!empty($APTID))
                {
                    $sqlGetapt = $this->conn->query("SELECT StatusID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                    // Check Apartment Status
                    if($sqlGetapt->num_rows > 0)
                    {
                        $AptStatus = $sqlGetapt->fetch_row();
                        if($AptStatus[0] == '2')
                        {
                            // get apartment data.
                            $APTCount = 0;
                            $APTBan = 0;
                            $arr = [];
                            $count = 1;
                            $sqlGetaptDataI = $this->conn->query("SELECT ApartmentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$UserID'");
                            while($APTStatus = $sqlGetaptDataI->fetch_row())
                            {
                                $Status = $APTStatus[1];
                                // Check Watchman existence.
                                $sqlGetWatchMan = $this->conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = 7");
                                if($sqlGetWatchMan->num_rows > 0)
                                {
                                    // Get Watchman Data.
                                    $WtchManID = $sqlGetWatchMan->fetch_row();
                                    $sqlGetWMData = $this->conn->query("SELECT Name, PhoneNum FROM Resident_User WHERE ID = '$WtchManID[0]'");
                                    if($sqlGetWMData->num_rows > 0)
                                    {
                                        $WtchManData = $sqlGetWMData->fetch_row();
                                        $WMName = $WtchManData[0];
                                        $WMPN = $WtchManData[1];
                                    }
                                }
                                elseif($sqlGetWatchMan->num_rows <= 0)
                                {
                                    $WMName = "";
                                    $WMPN = "";
                                }
                                // Check BlockManager Data.
                                $sqlGetBM = $this->conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = 1");
                                if($sqlGetBM->num_rows > 0)
                                {
                                    // Get BlockManager Data.
                                    $BMID = $sqlGetBM->fetch_row();
                                    $sqlGetWMData = $this->conn->query("SELECT Name, PhoneNum FROM Resident_User WHERE ID = '$BMID[0]'");
                                    if($sqlGetWMData->num_rows > 0)
                                    {
                                        $BMData = $sqlGetWMData->fetch_row();
                                        $BMName = $BMData[0];
                                        $BMPN = $BMData[1];
                                    }
                                }
                                elseif($sqlGetBM->num_rows <= 0)
                                {
                                    $BMName = "";
                                    $BMPN = "";
                                }
                                // ===============================================================================================
                                // Block Payment Methods
                                // Get Payment Methods from table BlockPaymentMethod.
                                // @PM => PaymentMethod
                                $PMArr = [];
                                $sqlGetBLKPM = $this->conn->query("SELECT * FROM BlockPaymentMethod WHERE BlockID = '$BLKID'");
                                if($sqlGetBLKPM->num_rows > 0)
                                {
                                    $count = 0;
                                    while($PM = $sqlGetBLKPM->fetch_row())
                                    {
                                        $PMArr[$count] = [
                                            "name" => $PM[2],
                                            "method" => $PM[3],
                                            ];
                                            $count++;
                                    }
                                }
                                // ===============================================================================================
                                if($Status == 2)
                                {
                                    $sqlAPTData = $this->conn->query("SELECT * FROM Apartment WHERE ID = '$APTID'");
                                    $AptData = $sqlAPTData->fetch_row();
                                        $arr[$count] = [
                                            'id' => $AptData[0],
                                            "watchmanName" => $WMName,
                                            "watchmanPhoneNumber" => $WMPN,
                                            "blockManagerName" => $BMName,
                                            "blockManagerPhoneNumber" => $BMPN,
                                            "blockPaymentMethods" => $PMArr
                                        ];
                                }
                                elseif($Status == 1)
                                {
                                    $APTCount++;
                                }
                                elseif($Status == 3)
                                {
                                    $APTBan++;
                                }
                                if($APTCount > 0)
                                {
                                    $arr +=["binding" => "You have $APTCount apartment Status is Binding"];
                                }
                                if($APTBan > 0)
                                {
                                    $arr +=["banned" => "You have $APTBan apartment Status is Banned by Omarty Super Admin"];
                                }
                            }
                            return array_values($arr);
                            exit;
                        }
                        elseif($AptStatus[0] == '1')
                        {
                            $this->throwError(200, "Apartment Status is binding.");
                        }
                        elseif($AptStatus[0] == '3')
                        {
                            $this->throwError(200, "Apartment is Banned.");
                        }
                        else
                        {
                            $this->throwError(200, "Apartment status is not acceptable.");
                        }
                    }
                    else
                    {
                        $this->returnResponse(200, []);
                    }

                }
            }
        }
        else
        {
            $this->throwError(200, "This resident doesn't have any apartments, please contact your block manager.");
        }
    }

    private function Function_Template()
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
        
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
            $StartDate = $_POST["StartDate"];
            $Next = $_POST["upComming"];
            $Previous = $_POST["previous"];
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
    
    public function GetHeaderA()
    {
        echo $_SERVER["HTTP_AUTHORIZATION"];
    }
    
}
?>
