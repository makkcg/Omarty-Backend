<?php
    include("../vendor/autoload.php");

    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    

class Financial extends Functions
{

    //  (MONTHLY , ANNUALY , DAILY , NONE) (StartDate , EndDate)
    public function insertFeesAPT() 
    {
        include("../Config.php");
        date_default_timezone_set("Africa/Cairo");
        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(401, $e->getMessage());
        }
        // Check Role .

        $UserID = $decode->id;
        $role = $decode->apartmentsAndBlocks->record1->role;
        $BLKID = $decode->apartmentsAndBlocks->record1->block;

        if($role == 'Block Manager' || $role == 'Cashier')
        {
            $Amount = $_POST["amount"];
            $BillID = $_POST["billId"];
            $ExpenseID = $_POST["expenseID"];
            $ApartmentID = $_POST["apartmentId"];
            $Repetition = $_POST["repeat"];
            if(!empty($Repetition))
            {
                $startDate = $_POST["startDate"];
                $endDate = $_POST["endDate"];
                if(empty($startDate))
                {
                    $startDate = date("Y-m-d");
                }
                elseif(empty($endDate))
                {
                    $endDate = NULL;
                }
            }
            elseif(empty($Repetition))
            {
                $startDate = date("Y-m-d");
                $endDate = NULL;
                $Repetition = NULL;
            }
            
            $Date24 = date("Y-m-d H:i:s");
            $MT = mkTime(intval($Date24));
            $Date12 = date("Y-m-d h:i:sa");


            // Check Apartment Existence in Block
            $sqlCheckAPTinBLK = $conn->query("SELECT ID, StatusID FROM Apartment WHERE ID = '$ApartmentID' AND BlockID = '$BLKID'");
            if($sqlCheckAPTinBLK->num_rows > 0)
            {
                    $APT = $sqlCheckAPTinBLK->fetch_row();
                    if($APT[1] == 1)
                    {
                        $this->throwError(210, "Apartment status is Binding Please Activate its acount first.");
                    }
                    elseif($APT[1] == 3)
                    {
                        $this->throwError(210, "Apartment status is Banned Please Contact Omarty Super Admin.");
                    }
                    elseif($APT[1] == 2)
                    {
                        if(!empty($ExpenseID))
                        {
                            if(empty($Amount))
                            {
                                // Get Expense Price 
                                $sqlGetExpensePrice = $conn->query("SELECT Price FROM Expense WHERE ID = $ExpenseID");
                                if($sqlGetExpensePrice->num_rows > 0)
                                {
                                    $ExpensePrice = $sqlGetExpensePrice->fetch_row();
                                    if($ExpensePrice[0] !== null)
                                    {
                                        // If Expense price is set assign it to @Amount
                                        $Amount = $ExpensePrice[0];
                                        $this->returnResponse(200, "Price of expense in database is used as fee amount.");
                                    }
                                    else
                                    {
                                        // If Expense price is not set ThrowError.
                                        $this->throwError(304, "Please Enter Fee amount.");
                                        // header("Status: 304 Please Enter Fee amount.");

                                    }
                                }
                                else
                                {
                                    $this->throwError(304, "Please Enter Fee amount.");
                                }
                            }
                            elseif(!empty($Amount))
                            {
                                $Amount = $Amount;
                            }

                        }
                        elseif(empty($ExpenseID))
                        {
                            if(empty($Amount))
                            {
                                $this->throwError(304, "Please Specify Fee expense ID, or enter Amount for the fee.");
                            }
                            // 
                            elseif(!empty($Amount))
                            {
                                $ExpenseID = null;
                                $Amount = $Amount;
                            }
                        }
                        // insert new data.
                        if(!empty($Repetition))
                        {
                            $sqlInsertFee = $conn->query("INSERT INTO Fee (Amount, BillID, ExpenseID, CashierID, BlockID, ApartmentID, Date24, Date12, RepeatStatus, StartDate, EndDate) VALUES ('$Amount', '$BillID', '$ExpenseID', '$UserID', '$BLKID', '$ApartmentID', '$Date24', '$Date12', $Repetition, '$startDate', '$endDate')");
                            if($sqlInsertFee)
                            {
                                $this->returnResponse(200, "Fee inserted successfuly.");
                            }
                            else
                            {
                                $this->throwError(304, "Fee was not inserted. please try again.");
                            }
                        }
                        elseif(empty($Repetition))
                        {
                            $sqlInsertFee = $conn->query("INSERT INTO Fee (Amount, BillID, ExpenseID, CashierID, BlockID, ApartmentID, Date24, Date12, RepeatStatus, StartDate, EndDate) VALUES ('$Amount', '$BillID', $ExpenseID, '$UserID', '$BLKID', '$ApartmentID', '$Date24', '$Date12', NULL, '$startDate', NULL)");
                            if($sqlInsertFee)
                            {
                                $this->returnResponse(200, "Fee inserted successfuly.");
                            }
                            else
                            {
                                $this->throwError(304, "Fee was not inserted. please try again.");
                            }
                        }
                    }
                    else
                    {
                        $this->throwError(304, "Apartment Status Not Set, Please Set Apartment status.");
                    }
            }
            else
            {
                $this->throwError(200, "Apartment Not Found in Block.");
            }
        }
        else
        {
            $this->throwError(401, "Role Permessions don't apply");
        }

    }

    public function generateBill() // OK Final
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');

        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage() . " Token Error");
        }

        $userID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["managerApartmentId"];
        $apartmentId = $_POST["apartmentId"];
        $PaymentId = $_POST["paymentId"];
        // $FeeID To Add in bill
        // $PaymentID To Add in bill
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $CurrentTime = date("Y/m/d H:i:sa");
        $Date = date("Y/m/d h:i:sa");
        $extensions = ["jpg", "jpeg", "png", "pdf"];
        $Attach = $_POST["attach"];
        if(!empty($Attach))
        {
            $attachments = $this->uploadFile($userID, $Attach, $extensions);
        }
        
        /*Generating Bill for Block in the next line format.*/ 
        
        // echo $BillID . "  " . $BLKID . "  " . $APTID . "  " . $LastBillInBlock;
        // exit;
        
        // $BillID = "B".$BLKID."A".$APTID."I".$LastBillInBlock+1;
        
        
        // Check Block Existence.
        
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID' AND RoleID = 1");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
               
                    $BlockID = "B$BLKID";
                    // Check Manager Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID' AND RoleID = 1");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Manager Apartment Not Found.");
                    }
                    else
                    {
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check Resident relation to this apartment.
                        if($AptData[2] == $userID)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Manager Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Manager Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            {
                                // Check Resident apartment existence.
                                $sqlCheckResApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$apartmentId' AND BlockID='$BLKID'");
                                if($sqlCheckResApt->num_rows <= 0)
                                {
                                    $this->throwError(200, "Resident Apartment Not Found.");
                                }
                                else
                                {
                                    $APARTID = "A$apartmentId";
                                    // get last BillID In Block.
                                    $sqlGetLastBill = $conn->query("SELECT ID FROM BILL Where Block = '$BLKID' AND LastBillInBlock > 0");
                                    // if There are no bills for this block.
                                    if($sqlGetLastBill->num_rows <= 0)
                                    {   
                                        // Generate New Bill (B@BLKID A@APTID I1).
                                        $FirstBill = "B" . $BLKID . "A" . $apartmentId . "I1";
                                        // Save Bill With its name is its ID
                                        $attachments["newName"] = $FirstBill;
                                        $imageUrl = "https://kcgwebservices.net/omartyapis/Images/BillImages/" . $attachments["newName"];
                                        if(!empty($attachments)) { $location = "../Images/BillImages/". $attachments["newName"]; }
                                        if(!empty($attachments)) { $attachName = $attachments["newName"]; }
                                        else { $attachName = NULL; }
                                        // Generate Bill Receipt.   
                                        $this->makePdf($BLKID, $apartmentId, $userID, $PaymentId, $FirstBill);
                                        
                                        // Insert First Bill In Block And save Block ID (B@BLKID) and Apartment ID (A@APTID) And set LastBillInBlock Column to 1.
                                        $sqlInsertNewLastBill = $conn->query("INSERT INTO BILL (ID, BillImage, Date, PaymentID, Block, Apartment, LastBillInBlock, CreatedAt, CreatedBy) 
                                                                    VALUES ('$FirstBill', '$FirstBill.pdf', '$Date', '$PaymentId', '$BLKID','$apartmentId', '1', '$CurrentTime', '$userID')");
                                        if($sqlInsertNewLastBill)
                                        {
                                            // Move Image To BillImages Directory
                                            // if(!empty($attachments)) { move_uploaded_file($attachments["tmp_name"], $location); }
                                            
                                            // $this->returnResponse(200, "Generated New Bill.");
                                            return $FirstBill;
                                            
                                            $Action = "Generate Bill.";
                                            // Insert Log Generate Bill.
                                            $BillID = $conn->query("SELECT ID FROM BILL ORDER BY CreatedAt DESC LIMIT 1");
                                            $newId = $BillID->fetch_row();
                                            $sqlBillLog = $conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                    VALUES ('$userID', '$APTID', '$BLKID', 7, '$Action', '$newId[0]', 'BILL', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                        }
                                        else
                                        {
                                            // $this->throwError(200, "Bill Was not Generated Please try again.");
                                            return FALSE;
                                        }
                                    }
                                    // if There are bills for this block.
                                    elseif($sqlGetLastBill->num_rows > 0)
                                    {
                                        // Assign Last BillId in variable @LastBill.
                                        $LastBill = $sqlGetLastBill->fetch_row();
                                        // Trim the bill to get the number after character I refering to ID 
                                        $trmBillId = substr($LastBill[0], strpos($LastBill[0], "I") + 1);
                                        // increment the trimmed number by 1.
                                        $newID = intval($trmBillId) + 1;
                                        // Generate the new ID.
                                        $BillID = "B".$BLKID."A".$apartmentId."I".$newID;
                                        // Update Old lastBillInBlock to 0.
                                        
                                        $attachments["newName"] = $BillID;
                                        $imageUrl = "https://kcgwebservices.net/omartyapis/Images/BillImages/" . $attachments["newName"];
                                        if(!empty($attachments)) { $location = "../Images/BillImages/". $attachments["newName"]; }
                                        if(!empty($attachments)) { $attachName = $attachments["newName"]; }
                                        else { $attachName = NULL; }
                                        // Generate Bill Receipt.   
                                        $this->makePdf($BLKID, $apartmentId, $userID, $PaymentId, $BillID);
                                        
                                        $sqlUpdateLastBillInBlock = $conn->query("UPDATE BILL Set LastBillInBlock = 0 WHERE ID = '$LastBill[0]'");
                                        // Insert New bill With Generated ID @BillID and Set LastBillInBlock to 1.
                                        $sqlInsertNewLastBill = $conn->query("INSERT INTO BILL (ID, BillImage, Date, PaymentID, Block, Apartment, LastBillInBlock, CreatedAt, CreatedBy) 
                                                                    VALUES ('$BillID', '$BillID.pdf', '$Date', '$PaymentId', '$BLKID', '$apartmentId', '1', '$CurrentTime', '$userID')");

                                        if($conn->error)
                                        {
                                            echo $conn->error;
                                        }
                                         if($sqlInsertNewLastBill)
                                        {
                                            // Move Image To BillImages Directory
                                            // if(!empty($attachments)) { move_uploaded_file($attachments["tmp_name"], $location); }
                                            // $this->returnResponse(200, "Generated New Bill.");
                                            return $BillID;
                                            
                                            $Action = "Generate Bill.";
                                            // Insert Log Generate Bill.
                                            $BillID = $conn->query("SELECT ID FROM BILL ORDER BY CreatedAt DESC LIMIT 1");
                                            $newId = $BillID->fetch_row();
                                            $sqlBillLog = $conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                    VALUES ('$userID', '$APTID', '$BLKID', 7, '$Action', '$newId[0]', 'BILL', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                        }
                                        else
                                        {
                                            // $this->throwError(200, "Bill Was not Generated Please try again.");
                                            return FALSE;
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Manager Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Manager does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Manager does not relate to this block.");
            }
        }
        
    }

     public function showFeesAPT() // OK Final // Repeated fees, Fees of certain Expense type, All Fees
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');
        // Paging Data.
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
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage());
        }

        $userID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $Repeat = $_POST["repeatStatus"];
        $Expense = $_POST["expanseId"];
        // $apartmentId = $_POST["apartmentId"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $CurrentTime = date("Y-m-d H:i:s");
        $Date = date("Y-m-d h:i:sa");
        
        if(!empty($Repeat))
        {
            if($Repeat > 7)
            {   
                $this->throwError(200, "Repetition Start from 4 to 7, 4 as Annualy 7 as Daily.");
            }
            if($Repeat == '1')
            {
                $Repeat = 4;
            }
            if($Repeat == '2')
            {
                $Repeat = 5;
            }
            if($Repeat == '3')
            {
                $Repeat = 6;
            }
        }
        
        // Check Block Existence.
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment Not Found.");
                    }
                    elseif($sqlCheckApt->num_rows > 0)
                    {
                        // Check Block manager.
                        $sqlCheckMNG = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$userID' AND BlockID='$BLKID' AND RoleID = '1'");
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check User relation to this apartment is Resident Or manager.
                        if($AptData[2] == $userID || $sqlCheckMNG->num_rows > 0)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            {
                                /*
                                    Another Way of getting Fee.
                                    // if(!empty($Repeat))
                                    // {
                                    //     if(!empty($Expense))
                                    //     {
                                    //         // Get Fees Data from Fee Table With Specified Expense.
                                    //         $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND ExpenseID = '$Expense'");
                                    //     }
                                    //     else
                                    //     {
                                    //         // Get Fees Data from Fee Table With Specified repitetion.
                                    //         $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND RepeatStatusID = '$Repeat'");
                                    //     }
                                    // }
                                    // if(!empty($Expense))
                                    // {
                                    //     if(!empty($Repeat))
                                    //     {
                                    //         // Get Fees Data from Fee Table With Specified repitetion.
                                    //         $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND RepeatStatusID = '$Repeat'");
                                    //     }
                                    //     else
                                    //     {
                                    //         // Get Fees Data from Fee Table With Specified Expense.
                                    //         $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND ExpenseID = '$Expense'");
                                    //     }
                                    // }
                                **/
                                
                                    if(!empty($Repeat))
                                    {
                                        // Get Fees Data from Fee Table With Specified repitetion.
                                        $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND RepeatStatusID = '$Repeat' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                        $sqlGetFeeData2 = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND RepeatStatusID = '$Repeat' ORDER BY CreatedAt DESC");
                                        $RowsNum = $sqlGetFeeData2->num_rows;
                                    }
                                    if(!empty($Expense))
                                    {
                                        // Get Fees Data from Fee Table With Specified Expense.
                                        $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND ExpenseID = '$Expense' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                        $sqlGetFeeData2 = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND ExpenseID = '$Expense' ORDER BY CreatedAt DESC");
                                        $RowsNum = $sqlGetFeeData2->num_rows;
                                    }
                                    if(!empty($Expense) && !empty($Repeat))
                                    {
                                        // Get Fees Data from Fee Table With Specified Expense and Specified repitetion.
                                        $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND ExpenseID = '$Expense' AND RepeatStatusID = '$Repeat' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                        $sqlGetFeeData2 = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND ExpenseID = '$Expense' AND RepeatStatusID = '$Repeat' ORDER BY CreatedAt DESC");
                                        $RowsNum = $sqlGetFeeData2->num_rows;
                                    }
                                    if(empty($Expense) && empty($Repeat))
                                    {
                                        // Get Fees Data from Fee Table.
                                        $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                        $sqlGetFeeData2 = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' ORDER BY CreatedAt DESC");
                                        $RowsNum = $sqlGetFeeData2->num_rows;
                                    }
                                    
                                    $count = 1;
                                    $TotalFeeAmount = 0;
                                    $Data = [];
                                    if($sqlGetFeeData->num_rows > 0)
                                    {
                                        while($FeeData = $sqlGetFeeData->fetch_row())
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
                                            
                                            // Get Repetition status name from Status table
                                            $sqlGetStatus = $conn->query("SELECT Name From Status Where ID = '$FeeData[5]'");
                                            if($sqlGetStatus->num_rows > 0)
                                            {
                                                $repeat = $sqlGetStatus->fetch_row();
                                            }
                                            elseif($sqlGetStatus->num_rows <= 0)
                                            {
                                                $repeat[0] = $FeeData[5];
                                            }
                                            // Get Expense name from Status table
                                            $sqlGetExpenseName = $conn->query("SELECT Name From Expense WHERE ID = '$FeeData[7]'");
                                            if($sqlGetExpenseName->num_rows > 0)
                                            {    
                                                $expense = $sqlGetExpenseName->fetch_row();
                                            }
                                            elseif($sqlGetExpenseName->num_rows <= 0)
                                            {
                                                $expense[0] = $FeeData[7];
                                            }
                                            // Get Bill Image.
                                            $sqlGetBillImage = $conn->query("SELECT BillImage From BILL WHERE ID = '$FeeData[6]'");
                                            if($sqlGetBillImage->num_rows > 0)
                                            {    
                                                $Bill = $sqlGetBillImage->fetch_row();
                                                $BillImg = "https://kcgwebservices.net/omartyapis/Images/BillImages/$Bill[0]";
                                            }
                                            elseif($sqlGetBillImage->num_rows <= 0)
                                            {
                                                $BillImg = $FeeData[6];
                                            }
                                            // Get the remaining of Payments.
                                            $sqlGetPayRem = $conn->query("SELECT Remaining FROM Payment WHERE FeeID = '$FeeData[0]' ORDER BY ID DESC");
                                            if($conn->error)
                                            {
                                                echo $conn->error;
                                                exit;
                                            }
                                            if($sqlGetPayRem->num_rows > 0)
                                            {
                                                $paymentRemain = $sqlGetPayRem->fetch_row();
                                                $paymentRemaining = $paymentRemain[0];
                                            }
                                            // Get Payment method Name.
                                            $sqlGetPayMethod = $conn->query("SELECT Name FROM PaymentMethods WHERE ID = '$FeeData[2]'");
                                            if($sqlGetPayMethod->num_rows > 0)
                                            {
                                                $PaymentMethodName = $sqlGetPayMethod->fetch_row();
                                            }
                                            else
                                            {
                                                $PaymentMethodName[0] = $FeeData[2];
                                            }
                                            
                                            // Check If User didn't Pay whole amount of mony.
                                            $sqlCheckPayment = $conn->query("SELECT * FROM Payment WHERE ApartmentID = '$APTID' AND ResidentID = '$userID' AND FeeID = '$FeeData[0]'");
                                            if($sqlCheckPayment->num_rows > 0)
                                            {
                                                $PaiedAmount = 0;
                                                $Reciepts = [];
                                                $count = 1;
                                                while($PayData = $sqlCheckPayment->fetch_row())
                                                {
                                                    $PaiedAmount += $PayData[3];
                                                    $BillPdf = "https://kcgwebservices.net/omartyapis/Images/BillImages/$PayData[7].pdf";
                                                    $Reciepts += 
                                                    [
                                                        "bill $count" => $BillPdf
                                                    ];
                                                    $count++;
                                                }
                                            }
                                            else
                                            {
                                                $PaiedAmount = 0;
                                            }
                                            
                                            // Get User Payment Reciept.
                                            
                                            
                                            // Get Apartment Num and Apartment Floor Num.
                                            $sqlGetAptData = $conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = '$FeeData[10]'");
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
                                            $sqlGetBlockName = $conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$FeeData[9]'");
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
                                            
                                            $Data["record$count"] = [
                                                "id" =>             $FeeData[0],
                                                "feeStatment" =>    $FeeData[16],
                                                "amount" =>         $FeeData[1],
                                                "paiedAmount" =>    "$PaiedAmount",
                                                "paymentRemaining" => $paymentRemaining,
                                                "reciepts" => $Reciepts,
                                                "paymentMethod" =>  $PaymentMethodName[0],
                                                "dueDate" =>        $FeeData[3],
                                                "paymentDate" =>    $FeeData[4],
                                                "repeatStatusID" => $repeat[0],
                                                "bill" =>           $BillImg,
                                                "expenseName" =>      $expense[0],
                                                "cashierID" =>      $FeeData[8],
                                                "blockID" =>        $FeeData[9],
                                                "blockNumber" =>    $BlkNumC,
                                                "blockName" =>      $BlkNameC,
                                                "apartmentID" =>    $FeeData[10],
                                                "apartmentNumber" =>$AptNumC,
                                                "apartmentName" =>  $AptNameC,
                                                "apartmentFloorNumber" => $AptFloorNumC,
                                                "date" =>           $FeeData[11],
                                                "createdAt" =>      $FeeData[12],
                                                "createdBy" =>      $FeeData[13],
                                                "flagLastPage" =>   $FLP
                                            ];
                                            $TotalFeeAmount += $FeeData[1];
                                            $count++;
                                        }
                                        
                                        $Data += ["recordLast"=>["totalFeeAmount" => $TotalFeeAmount]];
                                        $this->returnResponse(200, array_values($Data));
                                    }
                                    else
                                    {
                                        $this->returnResponse(200, array_values($Data));
                                    }
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "User does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Resident does not relate to this block.");
            }
        }
        
    }

    public function showHomePage() // OK Final
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');

        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage());
        }

        $userID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        // $Repeat = $_POST["repeatStatus"];
        // $Expense = $_POST["expanseId"];
        // // $apartmentId = $_POST["apartmentId"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $CurrentTime = date("Y/m/d H:i:sa");
        $Date = date("Y/m/d h:i:sa");
        
        // Check Block Existence.
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment Not Found.");
                    }
                    elseif($sqlCheckApt->num_rows > 0)
                    {
                        // Check Block manager.
                        $sqlCheckMNG = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$userID' AND BlockID='$BLKID' AND RoleID = '1'");
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check User relation to this apartment is Resident Or manager.
                        if($AptData[2] == $userID || $sqlCheckMNG->num_rows > 0)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            {
                                $homePage = [];
                                
                                    // Get Block Manager Phone number AND watchMan phone Number AND block's paymentMethods.
                                        // Get Block Manager ID
                                        $sqlGetBMPN = $conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = '1'");
                                        // Get WatchMan ID
                                        $sqlGetWMPN = $conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = '7'");
                                        // Get Block Manager Name, PhoneNumber
                                        if($sqlGetBMPN->num_rows > 0)
                                        {
                                            $BMID = $sqlGetBMPN->fetch_row();
                                            $sqlGetBMData = $conn->query("SELECT Name, PhoneNum FROM Resident_User WHERE ID = '$BMID[0]'");
                                            if($sqlGetBMData->num_rows > 0)
                                            {
                                                $BMData = $sqlGetBMData->fetch_row();
                                                $homePage += ["blockManagerName" => $BMData[0], "blockManagerPhoneNumber" => $BMData[1]];
                                            }
                                            
                                        }
                                        // Get WatchMan Name, PhoneNumber
                                        if($sqlGetWMPN->num_rows > 0)
                                        {
                                            $WMID = $sqlGetWMPN->fetch_row();
                                            $sqlGetWMData = $conn->query("SELECT Name, PhoneNum FROM Resident_User WHERE ID = '$WMID[0]'");
                                            if($sqlGetWMData->num_rows > 0)
                                            {
                                                $WMData = $sqlGetWMData->fetch_row();
                                                $homePage += ["watchManName" => $WMData[0], "watchManPhoneNumber" => $WMData[1]];
                                            }
                                        }
                                        // Get Block's Payment Methods.
                                        $sqlGetPM = $conn->query("SELECT Name FROM PaymentMethods WHERE BlockID = '$BLKID'");
                                        if($sqlGetPM->num_rows > 0)
                                        {
                                            $BPM = $sqlGetPM->fetch_row();
                                        }
                                    // Get Late Payments.
                        // $sqlGetLatePay = $conn->query("SELECT Remaining FROM Paymnent WHERE BlockID = '$BLKID' AND ApartmentID = 'APTID' AND ResidentID = '$userID'");
                                    // Get Current Month Monthly fee of mentainnace.
                                    // Check If Fee Already exists
                                    $sqlCheckFee = $conn->query("SELECT * FROM Fee WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID' AND ExpenseID = 4");
                                    if($sqlCheckFee->num_rows > 0)
                                    {
                                        $feeData = $sqlCheckFee->fetch_row();
                                        // Check If User Paied the fee.
                                        $sqlCheckPay = $conn->query("SELECT * FROM Payment WHERE BlockID = '$BLKID' AND ApartmentID = '$APTID' AND ResidentID = '$userID'");
                                        
                                        while($payData = $sqlCheckPay->fetch_row())
                                        {
                                            if($payData[4] == $feeData[1])
                                            {
                                               $homePage += ["monthlyMentainance" => $payData[4]];
                                            }
                                        }
                                    }
                                    
                                    $this->returnResponse(200, $homePage);
                                    $sqlGetMonthlyFee = $conn->query("SELECT Remain");
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "User does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Resident does not relate to this block.");
            }
        }
        
    }

    public function payFeesAPT() // OK Final
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');

        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage());
        }

        $extensions = ["jpg", "jpeg", "png", "pdf"];

        $userID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $PartialAmount = $_POST["partialAmount"];
        $FeeID = $_POST["feeId"];
        $PaymentMethod = $_POST["paymentMethod"];
        $Attach = $_POST["attach"];
        if(!empty($Attach))
        {
            $attachments = $this->uploadFile($userID, $Attach, $extensions);
        }
            // File Location.
            if(!empty($attachments))
            {
                $location = "../Images/PaymentImages/". $attachments["newName"];
                $attachName = $attachments["newName"];
            }
            elseif(empty($attachments))
            {
                $attachName = NULL;
            }
            
            if(!empty($attachments)) { move_uploaded_file($attachments["tmp_name"], $location); }

        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $CurrentTime = date("Y/m/d H:i:sa");
        $Date = date("Y/m/d h:i:sa");
        
        // Check Block Existence.
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID, RoleID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment Not Found.");
                    }
                    elseif($sqlCheckApt->num_rows > 0)
                    {
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check User relation to this apartment is Resident Or manager.
                        if($AptData[2] == $userID)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            { 
                                // Get Fee Data.
                                $sqlGetFeeData = $conn->query("SELECT * FROM Fee WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' AND ID = '$FeeID'");
                                // Get Financial account data for apartment and block.
                                $sqlGetAptFin = $conn->query("SELECT * FROM FinancialAcount WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                                $sqlGetBlkFin = $conn->query("SELECT * FROM FinancialAcount WHERE ApartmentID IS NULL AND BlockID = '$BLKID'");
                                
                                if($sqlGetFeeData->num_rows > 0)
                                {
                                    // Assign fee data to @feeData
                                    $feeData = $sqlGetFeeData->fetch_row();
                                    // Assign Apartment Financial data to @AptFin
                                    $AptFin = $sqlGetAptFin->fetch_row();
                                    // Assign Block Financial data to @BlkFin
                                    $BlkFin = $sqlGetBlkFin->fetch_row();
                                    
                                    // Check If User paied before.
                                    $sqlCheckPayment = $conn->query("SELECT * FROM Payment WHERE ApartmentID = '$APTID' AND FeeID = '$feeData[0]'");
                                    if($sqlCheckPayment->num_rows > 0)
                                    {
                                        // Get Sum of All Payments amounts of this Fee.
                                        $PaymentSum = 0;
                                        /*
                                         * Get Fee Remaining by calculating the whole amount of payment and set all records with the new value by subtracting PaymentAmount from OriginalFeeAmount.
                                         */
                                        while($PaySum = $sqlCheckPayment->fetch_row())
                                        {
                                            $PaymentSum += $PaySum[3];
                                        }
                                        $PaymentRem = $feeData[1] - $PaymentSum - $PartialAmount;
                                        
                                        // Insert New Payment.
                                        // Partial Payment.
                                        if(!empty($PartialAmount))
                                        {
                                            // Insert Record to table Payment with the partial amount of mony
                                            if($AptData[3] == '1')
                                            {
                                                
                                                $sqlInsertPay = $conn->query("INSERT INTO Payment (MethodID, OriginalFeeAmount, Amount, Partial, BlockID, ApartmentID, ExpenseID, Attachment, Confirm, ResidentID, FeeID, CreatedAt, CreatedBy)
                                                                VALUES ('$PaymentMethod', $feeData[1], '$PartialAmount', 1, '$BLKID', '$APTID', '$feeData[7]', '$attachName', 1, '$userID', '$FeeID', '$CurrentTime', '$userID')");
                                                // Get PaymentID.
                                                $sqlGetPayID = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $PayIDForBill = $sqlGetPayID->fetch_row();
                                                // Generate Bill For Block Manager.
                                                $BMBill = $this->generateBillForBM($BLKID, $APTID, $Longitude, $Latitude, $PayIDForBill[0]);
                                                // Update Payment Record to set Bill ID.
                                                $sqlUpdatePay = $conn->query("UPDATE Payment SET BillID = '$BMBill' WHERE ID = '$PayIDForBill[0]'");
                                            }
                                            else
                                            {
                                                $sqlInsertPay = $conn->query("INSERT INTO Payment (MethodID, OriginalFeeAmount, Amount, Partial, BlockID, ApartmentID, ExpenseID, Attachment, ResidentID, FeeID, CreatedAt, CreatedBy)
                                                                    VALUES ('$PaymentMethod', $feeData[1], '$PartialAmount', 1, '$BLKID', '$APTID', '$feeData[7]', '$attachName', '$userID', '$FeeID', '$CurrentTime', '$userID')");
                                            }                
    
                                            if($sqlInsertPay)
                                            {
                                                // Pay Fee
                                                $Action = "Create New Payment By Resident ID: $userID, Apartment ID: $APTID, Block ID: $BLKID.";
                                                // Log insert Create new Payment.
                                                $PMTID = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $newId = $PMTID->fetch_row();
                                                 $sqlInsertFinLog = $conn->query("INSERT INTO FinancialLog (UserID, BlockID, ApartmentID, LogTypeID, PaymentID, Action, LogRecordInActualTable, LogActualTable, 
                                                                                    ApartmentTotalFee, ApartmentTotalBalance, BlockTotalFee, BlockTotalBalance, Longitude, Latitude, Date, CreatedAt)
                                                                        VALUES ('$userID', '$BLKID', '$APTID', 20, '$newId[0]', '$Action', '$newId[0]', 'Payment', '$AptFin[2]', '$AptFin[1]',
                                                                                '$BlkFin[2]', '$BlkFin[1]', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                                               
                                                if($AptData[3] == '1')
                                                {
                                                // Get Last Entered Payment.
                                                $sqlGetPayId = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $PayID = $sqlGetPayId->fetch_row();
                                                // Update fee Record if User's role is Block manager.
                                                $Remaining = $feeData[1] - $PartialAmount;
                                                // $Remaining = floatval($Remaining);
                                                $sqlUpdateFee = $conn->query("UPDATE Payment SET Remaining = '$Remaining', Confirm = '1' WHERE ID = '$PayID[0]'");
    
                                                $Action = "Update Payment By Block manager with Resident ID: $userID, Apartment ID: $APTID, Block ID: $BLKID.";
                                                $sqlInsertFinLog = $conn->query("INSERT INTO FinancialLog (UserID, BlockID, ApartmentID, LogTypeID, PaymentID, Action, LogRecordInActualTable, LogActualTable, 
                                                                                        ApartmentTotalFee, ApartmentTotalBalance, BlockTotalFee, BlockTotalBalance, Longitude, Latitude, Date, CreatedAt)
                                                                            VALUES ('$userID', '$BLKID', '$APTID', 15, '$PayID[0]', '$Action', '$newId[0]', 'Payment', '$AptFin[2]', '$AptFin[1]',
                                                                                    '$BlkFin[2]', '$BlkFin[1]', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                                }
                                                
                                                $this->returnResponse(200, "Payment Inserted.");
                                                
                                            }
                                        }
                                        // Full Payment.
                                        elseif(empty($PartialAmount))
                                        {
                                            if($AptData[3] == '1')
                                            {
                                                $sqlInsertPay = $conn->query("INSERT INTO Payment (MethodID, OriginalFeeAmount, Amount, Partial, BlockID, ApartmentID, ExpenseID, Attachment, Confirm, ResidentID, FeeID, CreatedAt, CreatedBy)
                                                                VALUES ('$PaymentMethod', $feeData[1], '$feeData[1]', 0, '$BLKID', '$APTID', '$feeData[7]', '$attachName', 1, '$userID', '$FeeID', '$CurrentTime', '$userID')");    
                                                // Get PaymentID.
                                                $sqlGetPayID = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $PayIDForBill = $sqlGetPayID->fetch_row();
                                                // Generate Bill For Block Manager.
                                                $BMBill = $this->generateBillForBM($BLKID, $APTID, $Longitude, $Latitude, $PayIDForBill[0]);
                                                // Update Payment Record to set Bill ID.
                                                $sqlUpdatePay = $conn->query("UPDATE Payment SET BillID = '$BMBill' WHERE ID = '$PayIDForBill[0]'");
                                            }
                                            else
                                            {
                                                $sqlInsertPay = $conn->query("INSERT INTO Payment (MethodID, OriginalFeeAmount, Amount, Partial, BlockID, ApartmentID, ExpenseID, Attachment, ResidentID, FeeID, CreatedAt, CreatedBy)
                                                                    VALUES ('$PaymentMethod', $feeData[1], '$feeData[1]', 0, '$BLKID', '$APTID', '$feeData[7]', '$attachName', '$userID', '$FeeID', '$CurrentTime', '$userID')");
                                            }
                                                              
                                            if($sqlInsertPay)
                                            {
                                                 // Pay Fee
                                                $Action = "Create New Payment By Resident ID: $userID, Apartment ID: $APTID, Block ID: $BLKID.";
                                                // Log insert Create new Payment.
                                                $PMTID = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $newId = $PMTID->fetch_row();
                                                 $sqlInsertFinLog = $conn->query("INSERT INTO FinancialLog (UserID, BlockID, ApartmentID, LogTypeID, PaymentID, Action, LogRecordInActualTable, LogActualTable, 
                                                                                    ApartmentTotalFee, ApartmentTotalBalance, BlockTotalFee, BlockTotalBalance, Longitude, Latitude, Date, CreatedAt)
                                                                        VALUES ('$userID', '$BLKID', '$APTID', 20, '$newId[0]', '$Action', '$newId[0]', 'Payment', '$AptFin[2]', '$AptFin[1]',
                                                                                '$BlkFin[2]', '$BlkFin[1]', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                                               
                                                $this->returnResponse(200, "Payment Inserted.");
                                            }
                                        }
                                        
                                        // Update All Records with this FeeID To set the remaining to new value.
                                        $sqlUpdatePayRem = $conn->query("UPDATE Payment SET Remaining = '$PaymentRem' WHERE ApartmentID = '$APTID' AND FeeID = '$feeData[0]'");
                                        
                                    }
                                    elseif($sqlCheckPayment->num_rows <= 0)
                                    {
                                        // Partial Payment.
                                        if(!empty($PartialAmount))
                                        {
                                            // Insert Record to table Payment with the partial amount of mony 
                                            if($AptData[3] == '1')
                                            {
                                                $sqlInsertPay = $conn->query("INSERT INTO Payment (MethodID, OriginalFeeAmount, Amount, Partial, BlockID, ApartmentID, ExpenseID, Attachment, Confirm, ResidentID, FeeID, CreatedAt, CreatedBy)
                                                                VALUES ('$PaymentMethod', $feeData[1], '$PartialAmount', 1, '$BLKID', '$APTID', '$feeData[7]', '$attachName', 1, '$userID', '$FeeID', '$CurrentTime', '$userID')");    
                                                
                                                // Get PaymentID.
                                                $sqlGetPayID = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $PayIDForBill = $sqlGetPayID->fetch_row();
                                                // Generate Bill For Block Manager.
                                                $BMBill = $this->generateBillForBM($BLKID, $APTID, $Longitude, $Latitude, $PayIDForBill[0]);
                                                // Update Payment Record to set Bill ID.
                                                $sqlUpdatePay = $conn->query("UPDATE Payment SET BillID = '$BMBill' WHERE ID = '$PayIDForBill[0]'");
                                                                
                                            }
                                            else
                                            {
                                                $sqlInsertPay = $conn->query("INSERT INTO Payment (MethodID, OriginalFeeAmount, Amount, Partial, BlockID, ApartmentID, ExpenseID, Attachment, ResidentID, FeeID, CreatedAt, CreatedBy)
                                                                    VALUES ('$PaymentMethod', $feeData[1], '$PartialAmount', 1, '$BLKID', '$APTID', '$feeData[7]', '$attachName', '$userID', '$FeeID', '$CurrentTime', '$userID')");
                                            }                
    
                                            if($sqlInsertPay)
                                            {
                                                // Pay Fee
                                                $Action = "Create New Payment By Resident ID: $userID, Apartment ID: $APTID, Block ID: $BLKID.";
                                                // Log insert Create new Payment.
                                                $PMTID = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $newId = $PMTID->fetch_row();
                                                 $sqlInsertFinLog = $conn->query("INSERT INTO FinancialLog (UserID, BlockID, ApartmentID, LogTypeID, PaymentID, Action, LogRecordInActualTable, LogActualTable, 
                                                                                    ApartmentTotalFee, ApartmentTotalBalance, BlockTotalFee, BlockTotalBalance, Longitude, Latitude, Date, CreatedAt)
                                                                        VALUES ('$userID', '$BLKID', '$APTID', 20, '$newId[0]', '$Action', '$newId[0]', 'Payment', '$AptFin[2]', '$AptFin[1]',
                                                                                '$BlkFin[2]', '$BlkFin[1]', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                                               
                                                if($AptData[3] == '1')
                                                {
                                                // Get Last Entered Payment.
                                                $sqlGetPayId = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $PayID = $sqlGetPayId->fetch_row();
                                                // Update fee Record if User's role is Block manager.
                                                $Remaining = $feeData[1] - $PartialAmount;
                                                // $Remaining = floatval($Remaining);
                                                $sqlUpdateFee = $conn->query("UPDATE Payment SET Remaining = '$Remaining', Confirm = '1' WHERE ID = '$PayID[0]'");
    
                                                $Action = "Update Payment By Block manager with Resident ID: $userID, Apartment ID: $APTID, Block ID: $BLKID.";
                                                $sqlInsertFinLog = $conn->query("INSERT INTO FinancialLog (UserID, BlockID, ApartmentID, LogTypeID, PaymentID, Action, LogRecordInActualTable, LogActualTable, 
                                                                                        ApartmentTotalFee, ApartmentTotalBalance, BlockTotalFee, BlockTotalBalance, Longitude, Latitude, Date, CreatedAt)
                                                                            VALUES ('$userID', '$BLKID', '$APTID', 15, '$PayID[0]', '$Action', '$newId[0]', 'Payment', '$AptFin[2]', '$AptFin[1]',
                                                                                    '$BlkFin[2]', '$BlkFin[1]', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                                }
                                                
                                                $this->returnResponse(200, "Payment Inserted.");
                                                
                                            }
                                        }
                                        // Full Payment.
                                        elseif(empty($PartialAmount))
                                        {
                                            if($AptData[3] == '1')
                                            {
                                                $sqlInsertPay = $conn->query("INSERT INTO Payment (MethodID, OriginalFeeAmount, Amount, Partial, BlockID, ApartmentID, ExpenseID, Attachment, Confirm, ResidentID, FeeID, CreatedAt, CreatedBy)
                                                                VALUES ('$PaymentMethod', $feeData[1], '$feeData[1]', 0, '$BLKID', '$APTID', '$feeData[7]', '$attachName', 1, '$userID', '$FeeID', '$CurrentTime', '$userID')");
                                                
                                                // Get PaymentID.
                                                $sqlGetPayID = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $PayIDForBill = $sqlGetPayID->fetch_row();
                                                // Generate Bill For Block Manager.
                                                $BMBill = $this->generateBillForBM($BLKID, $APTID, $Longitude, $Latitude, $PayIDForBill[0]);
                                                // Update Payment Record to set Bill ID.
                                                $sqlUpdatePay = $conn->query("UPDATE Payment SET BillID = '$BMBill' WHERE ID = '$PayIDForBill[0]'");
                                                                
                                            }
                                            else
                                            {
                                                $sqlInsertPay = $conn->query("INSERT INTO Payment (MethodID, OriginalFeeAmount, Amount, Partial, BlockID, ApartmentID, ExpenseID, Attachment, ResidentID, FeeID, CreatedAt, CreatedBy)
                                                                    VALUES ('$PaymentMethod', $feeData[1], '$feeData[1]', 0, '$BLKID', '$APTID', '$feeData[7]', '$attachName', '$userID', '$FeeID', '$CurrentTime', '$userID')");
                                            }
                                                              
                                            if($sqlInsertPay)
                                            {
                                                 // Pay Fee
                                                $Action = "Create New Payment By Resident ID: $userID, Apartment ID: $APTID, Block ID: $BLKID.";
                                                // Log insert Create new Payment.
                                                $PMTID = $conn->query("SELECT ID FROM Payment ORDER BY ID DESC LIMIT 1");
                                                $newId = $PMTID->fetch_row();
                                                 $sqlInsertFinLog = $conn->query("INSERT INTO FinancialLog (UserID, BlockID, ApartmentID, LogTypeID, PaymentID, Action, LogRecordInActualTable, LogActualTable, 
                                                                                    ApartmentTotalFee, ApartmentTotalBalance, BlockTotalFee, BlockTotalBalance, Longitude, Latitude, Date, CreatedAt)
                                                                        VALUES ('$userID', '$BLKID', '$APTID', 20, '$newId[0]', '$Action', '$newId[0]', 'Payment', '$AptFin[2]', '$AptFin[1]',
                                                                                '$BlkFin[2]', '$BlkFin[1]', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                                               
                                                $this->returnResponse(200, "Payment Inserted.");
                                            }
                                        }
                                    }
                                    
                                }
                                elseif($sqlGetFeeData->num_rows <= 0)
                                {
                                    $this->throwError(406, "Fee not found.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "User does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Resident does not relate to this block.");
            }
        }
        
    }

    public function showPayments()
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');
        
        $Page = $_POST["page"];
        if(empty($Page))
        {
            $Page = 1;
        }
        $Limit = 10;
        $Start = ($Page - 1) * $Limit;

        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage());
        }

        $userID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $CurrentTime = date("Y/m/d H:i:s");
        $Date = date("Y/m/d h:i:sa");
        
        // Check Block Existence.
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment Not Found.");
                    }
                    elseif($sqlCheckApt->num_rows > 0)
                    {
                        // Check Block manager.
                        $sqlCheckMNG = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$userID' AND BlockID='$BLKID' AND RoleID = '1'");
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check User relation to this apartment is Resident Or manager.
                        if($AptData[2] == $userID || $sqlCheckMNG->num_rows > 0)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            {
                                // Get Payments Data from Payment Table.
                                $sqlGetPayData = $conn->query("SELECT * FROM Payment WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID' ORDER BY CreatedAt DESC LIMIT $Start, $Limit");
                                $sqlGetPayData2 = $conn->query("SELECT * FROM Payment WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                                $RowsNum = $sqlGetPayData2->num_rows;
                                    
                                    $count = 1;
                                    $TotalPaiedAmount = 0;
                                    $Data = [];
                                    if($sqlGetPayData->num_rows > 0)
                                    {
                                        while($PayData = $sqlGetPayData->fetch_row())
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
                                            
                                            // Get Fee Statment from Fee Table.
                                            $sqlGetFeeSTMT = $conn->query("SELECT FeeStatment From Fee Where ID = '$PayData[6]'");
                                            if($sqlGetFeeSTMT->num_rows > 0)
                                            {
                                                $FeeSTMT = $sqlGetFeeSTMT->fetch_row();
                                            }
                                            elseif($sqlGetFeeSTMT->num_rows <= 0)
                                            {
                                                $FeeSTMT[0] = $PayData[6];
                                            }
                                            // Get Payment Method Name from PaymentMethods Table.
                                            $sqlGetMeth = $conn->query("SELECT Name From PaymentMethods WHERE ID = '$PayData[1]'");
                                            if($sqlGetMeth->num_rows > 0)
                                            {
                                                $PayMethod = $sqlGetMeth->fetch_row();
                                            }
                                            elseif($sqlGetMeth->num_rows <= 0)
                                            {
                                                $PayMethod[0] = $PayData[1];
                                            }
                                            // Get Bill Image from BILL table
                                            $sqlGetBill = $conn->query("SELECT BillImage From BILL Where ID = '$PayData[7]'");
                                            if($sqlGetBill->num_rows > 0)
                                            {
                                                $BillImage = $sqlGetBill->fetch_row();
                                                $BillUrl = "https://kcgwebservices.net/omartyapis/Images/BillImages/$BillImage[0]";
                                            }
                                            elseif($sqlGetBill->num_rows <= 0)
                                            {
                                                $BillUrl = "";
                                            }
                                            // Get Expense Name from Expense table
                                            $sqlGetExpenseName = $conn->query("SELECT Name From Expense WHERE ID = '$PayData[13]'");
                                            if($sqlGetExpenseName->num_rows > 0)
                                            {    
                                                $expense = $sqlGetExpenseName->fetch_row();
                                            }
                                            elseif($sqlGetExpenseName->num_rows <= 0)
                                            {
                                                $expense[0] = $PayData[13];
                                            }
                                            // Get User Name.
                                            $sqlGetResData = $conn->query("SELECT Name From Resident_User WHERE ID = '$PayData[15]'");
                                            if($sqlGetResData->num_rows > 0)
                                            {
                                                $ResName = $sqlGetResData->fetch_row();
                                            }
                                            elseif($sqlGetResData->num_rows <= 0)
                                            {
                                                $ResName = $PayData[15];
                                            }
                                            // Get Payment Attachment
                                            if(!empty($PayData[8]))
                                            {
                                                $AttachUrl = "https://kcgwebservices.net/omartyapis/Images/PaymentImages/$PayData[7]";
                                            }
                                            elseif(empty($PayData[8]))
                                            {
                                                $AttachUrl = "";
                                            }
                                            
                                            // Get Apartment Num and Apartment Floor Num.
                                            $sqlGetAptData = $conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = '$PayData[11]'");
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
                                            $sqlGetBlockName = $conn->query("SELECT ID, BlockNum, BlockName FROM Block WHERE ID = '$PayData[10]'");
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
                                            $Data["record$count"] = [
                                                "id" =>                 $PayData[0],
                                                "paymentMethod" =>      $PayMethod[0],
                                                "originalFeeAmount" =>  $PayData[2],
                                                "amount" =>             $PayData[3],
                                                "remainingAmount" =>    $PayData[4],
                                                "partial" =>            $PayData[5],
                                                "feeID" =>              $PayData[6],
                                                "feeStatment" =>        $FeeSTMT[0],
                                                // "billImage" =>          $PayData[5],
                                                "billImage" =>          $BillUrl,
                                                "attachment" =>         $AttachUrl,
                                                "confirm" =>            $PayData[9],
                                                "expenseName" =>        $expense[0],
                                                "residentID" =>         $PayData[12],
                                                "residentName" =>       $ResName[0],
                                                "blockID" =>            $PayData[10],
                                                "blockNumber" =>        $BlkNumC,
                                                "blockName" =>          $BlkNameC,
                                                "apartmentID" =>        $PayData[11],
                                                "apartmentNumber" =>    $AptNumC,
                                                "apartmentName" =>      $AptNameC,
                                                "apartmentFloorNumber" =>   $AptFloorNumC,
                                                "paymentdate" =>        $PayData[14],
                                                "flagLastPage" =>       $FLP
                                            ];
                                            
                                            $count++;
                                        }
                                        // Calculating sum of all payments of this apartment.
                                        while($SumOfPaymentAmount = $sqlGetPayData2->fetch_row())
                                        {
                                            $TotalPaiedAmount += $SumOfPaymentAmount[3];
                                        }
                                        
                                        
                                        $Data += ["recordLast"=>["totalPaiedAmount" => $TotalPaiedAmount]];
                                        $this->returnResponse(200, array_values($Data));
                                    }
                                    else
                                    {
                                        $this->returnResponse(200, array_values($Data));
                                    }
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "User does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Resident does not relate to this block.");
            }
        }
        
    }

    public function blockAccounting()
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');

        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage());
        }

        $userID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $CurrentTime = date("Y/m/d H:i:sa");
        $Date = date("Y/m/d h:i:sa");
        
        // Check Block Existence.
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment Not Found.");
                    }
                    elseif($sqlCheckApt->num_rows > 0)
                    {
                        // Check Block manager.
                        $sqlCheckMNG = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$userID' AND BlockID='$BLKID' AND RoleID = '1'");
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check User relation to this apartment is Resident Or manager.
                        if($AptData[2] == $userID || $sqlCheckMNG->num_rows > 0)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            {
                                // Get Fees Data from Fee Table.
                                $sqlGetFA = $conn->query("SELECT * FROM FinancialAcount WHERE ApartmentID IS NULL AND BlockID = '$BLKID'");
                                // Use th previous sql to get the record where only BlockID is set not BlockID and ApartmentID.
                                // $sqlGetFA = $conn->query("SELECT * FROM FinancialAcount WHERE BlockID = '$BLKID'");
                                
                                // Get Apartment Total amount of fees and total amount of monthly maintenance.
                                $sqlGetAptFeeAmt = $conn->query("SELECT Amount FROM Fee WHERE PaymentDate IS NULL AND ExpenseID != 4");
                                $sqlGetAptMonthlyFeeAmt = $conn->query("SELECT Amount FROM Fee WHERE PaymentDate IS NULL AND RepeatStatusID = 5 AND ExpenseID = 4");
                                $FeeAMT = 0;
                                $MonthlyFeeAMT = 0;
                                    while($TotalFeeAmount = $sqlGetAptFeeAmt->fetch_row())
                                    {
                                        $FeeAMT += $TotalFeeAmount[0];
                                    }
                                    
                                    while($TotalMonthFeeAmount = $sqlGetAptMonthlyFeeAmt->fetch_row())
                                    {
                                        $MonthlyFeeAMT += $TotalMonthFeeAmount[0];
                                    }
                                    $count = 1;
                                    if($sqlGetFA->num_rows > 0)
                                    {
                                        while($FAData = $sqlGetFA->fetch_row())
                                        {
                                            $Data["record$count"] = [
                                                "id" =>             $FAData[0],
                                                "balance" =>        $FAData[1],
                                                "monthIncome" =>    $FAData[2],
                                                "monthExpense" =>   $FAData[3],
                                                "feeAmount" =>      $FAData[4],
                                                "apartmentFeeAmount" => $FeeAMT,
                                                "apartmentMonthlyMaintenance" => $MonthlyFeeAMT
                                            ];
                                            $count++;
                                        }
                                        $this->returnResponse(200, array_values($Data));
                                    }
                                    else
                                    {
                                        
                                    }
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "User does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Resident does not relate to this block.");
            }
        }
        
    }

    public function apartmentAccounting()
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');

        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage());
        }

        $userID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $CurrentTime = date("Y/m/d H:i:sa");
        $Date = date("Y/m/d h:i:sa");
        
        // Check Block Existence.
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment Not Found.");
                    }
                    elseif($sqlCheckApt->num_rows > 0)
                    {
                        // Check Block manager.
                        $sqlCheckMNG = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$userID' AND BlockID='$BLKID' AND RoleID = '1'");
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check User relation to this apartment is Resident Or manager.
                        if($AptData[2] == $userID || $sqlCheckMNG->num_rows > 0)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            {
                                // Get Fees Data from Fee Table.
                                $sqlGetFA = $conn->query("SELECT * FROM FinancialAcount WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                                // Use th previous sql to get the record where only BlockID is set not BlockID and ApartmentID.
                                // $sqlGetFA = $conn->query("SELECT * FROM FinancialAcount WHERE BlockID = '$BLKID'");
                                
                                // Get Apartment Total amount of fees and total amount of monthly maintenance.
                                $sqlGetAptFeeAmt = $conn->query("SELECT Amount FROM Fee WHERE PaymentDate IS NULL AND ExpenseID != 4");
                                $sqlGetAptMonthlyFeeAmt = $conn->query("SELECT Amount FROM Fee WHERE PaymentDate IS NULL AND RepeatStatusID = 5 AND ExpenseID = 4");
                                $FeeAMT = 0;
                                $MonthlyFeeAMT = 0;
                                    while($TotalFeeAmount = $sqlGetAptFeeAmt->fetch_row())
                                    {
                                        $FeeAMT += $TotalFeeAmount[0];
                                    }
                                    
                                    while($TotalMonthFeeAmount = $sqlGetAptMonthlyFeeAmt->fetch_row())
                                    {
                                        $MonthlyFeeAMT += $TotalMonthFeeAmount[0];
                                    }
                                    $count = 1;
                                    if($sqlGetFA->num_rows > 0)
                                    {
                                        while($FAData = $sqlGetFA->fetch_row())
                                        {
                                            $Data["record$count"] = [
                                                "id" =>             $FAData[0],
                                                "balance" =>        $FAData[1],
                                                "monthIncome" =>    $FAData[2],
                                                "monthExpense" =>   $FAData[3],
                                                "feeAmount" =>      $FAData[4],
                                                "apartmentFeeAmount" => $FeeAMT,
                                                "apartmentMonthlyMaintenance" => $MonthlyFeeAMT
                                            ];
                                            $count++;
                                        }
                                        $this->returnResponse(200, array_values($Data));
                                    }
                                    else
                                    {
                                        $this->throwError(304, "Financial Account Not Found.");
                                    }
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "User does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Resident does not relate to this block.");
            }
        }
        
    }

    public function Fundmovementdetection()
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');

        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage());
        }

        $userID = $decode->id;
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $StartDate = $_POST["startDate"];
        $EndDate = $_POST["endDate"];
        $CurrentTime = date("Y-m-d H:i:s");
        $Date = date("Y-m-d h:i:sa");
        
        // Check Block Existence.
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment Not Found.");
                    }
                    elseif($sqlCheckApt->num_rows > 0)
                    {
                        // Check Block manager.
                        $sqlCheckMNG = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$userID' AND BlockID='$BLKID' AND RoleID = '1'");
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check User relation to this apartment is Resident Or manager.
                        if($AptData[2] == $userID || $sqlCheckMNG->num_rows > 0)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            {
                                // Get all records from table FinancialLog where BlockID = @BLKID And date is between @StartDate And @EndDate.
                                if(!empty($StartDate) && !empty($EndDate))
                                {
                                    $sqlGetLog = $conn->query("SELECT * FROM FinancialLog WHERE BlockID = '$BLKID' and CreatedAt BETWEEN '$StartDate' AND '$EndDate'");
                                    if($conn->error)
                                    {
                                        echo $conn->error . " => 1";
                                    }
                                }
                                // Get all records from table FinancialLog where BlockID = @BLKID.
                                if(empty($StartDate) && empty($EndDate))
                                {
                                    $sqlGetLog = $conn->query("SELECT * FROM FinancialLog WHERE BlockID = '$BLKID'");
                                    if($conn->error)
                                    {
                                        echo $conn->error . " => 2";
                                    }
                                }
                                // Get all records from table FinancialLog where BlockID = @BLKID And Date Starts from @StartDate.
                                if(!empty($StartDate) && empty($EndDate))
                                {
                                    $sqlGetLog = $conn->query("SELECT * FROM FinancialLog WHERE BlockID = '$BLKID' and CreatedAt > '$StartDate'");
                                    if($conn->error)
                                    {
                                        echo $conn->error . " => 3";
                                    }
                                }
                                // Get all records from table FinancialLog where BlockID = @BLKID And Date Ends At @EndtDate.
                                if(empty($StartDate) && !empty($EndDate))
                                {
                                    $sqlGetLog = $conn->query("SELECT * FROM FinancialLog WHERE BlockID = '$BLKID' and CreatedAt < '$EndDate'");
                                    if($conn->error)
                                    {
                                        echo $conn->error . " => 4";
                                    }
                                }

                                    $count = 1;
                                    if($sqlGetLog->num_rows > 0)
                                    {
                                        while($LogData = $sqlGetLog->fetch_row())
                                        {
                                            // echo $LogData[5];
                                            // exit;
                                            // Get Log type By LogTypeID
                                            $sqlGetLogType = $conn->query("SELECT Name From LogType WHERE ID = '$LogData[4]'");
                                            if($sqlGetLogType->num_rows > 0)
                                            {
                                                $LogTpeName = $sqlGetLogType->fetch_row();
                                            }
                                            elseif($sqlGetLogType->num_rows <= 0)
                                            {
                                                $LogTpeName[0] = $LogData[4];
                                            }
                                            if(!empty($LogData[5]))
                                            {
                                                // Get Payment Amount and Payment attachment Or BillImage and payment confirmation and payment ExpenseID payment Date For apartment and block.
                                                $sqlGetPayData = $conn->query("SELECT Amount, Attachment, Confirm, ExpenseID, CreatedAt, ResidentID, FeeID, Partial, Remaining FROM Payment WHERE ID = '$LogData[5]'");
                                            }
                                            if($sqlGetPayData->num_rows > 0)
                                            {
                                                $PayentDataBLK = $sqlGetPayData->fetch_row();
                                                $PayAmount = $PayentDataBLK[0];
                                                $PayAttach = "https://kcgwebservices.net/omartyapis/Images/PaymentImages/$PayentDataBLK[1]";
                                                $PayConfirm = $PayentDataBLK[2];
                                                $PayExpenseID = $PayentDataBLK[3];
                                                $PayDate = $PayentDataBLK[4];
                                                $PayCreator = $PayentDataBLK[5];
                                                $FeeID = $PayentDataBLK[6];
                                                $PartialPay = $PayentDataBLK[7];
                                                $Remaining = $PayentDataBLK[8];
                                                
                                                // Get ExpenseName.
                                                $sqlGetExpense = $conn->query("SELECT Name FROM Expense WHERE ID = '$PayentDataBLK[3]'");
                                                if($sqlGetExpense->num_rows > 0)
                                                {
                                                    
                                                    $ExpanseName = $sqlGetExpense->fetch_row();
                                                }
                                                elseif($sqlGetExpense->num_rows <= 0)
                                                {
                                                    $ExpanseName[0] = $PayentDataBLK[3];
                                                }
                                                
                                                // Get Creator Name , Apartment , FloorNumber.
                                                $sqlGetRes = $conn->query("SELECT ResidentID, ApartmentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$PayentDataBLK[5]'");
                                                if($sqlGetRes->num_rows > 0)
                                                {
                                                    $RES = $sqlGetRes->fetch_row();
                                                    // Get Resident Name and Phone Number.
                                                    $sqlGetResName = $conn->query("SELECT Name, PhoneNum From Resident_User WHERE ID = '$RES[0]'");
                                                    if($sqlGetResName->num_rows > 0)
                                                    {
                                                        $ResData = $sqlGetResName->fetch_row();
                                                        $ResName = $ResData[0];
                                                        $ResPN = $ResData[1];
                                                    }
                                                    elseif($sqlGetResName->num_rows <= 0)
                                                    {
                                                        $ResName = $RES[0];
                                                        $ResPN = $Res[0];
                                                    }
                                                    // Get Apartment Number and Floor Number.
                                                    $sqlGetAPT = $conn->query("SELECT ApartmentNumber, FloorNum FROM Apartment WHERE ID = '$RES[1]'");
                                                    if($sqlGetAPT->num_rows > 0)
                                                    {
                                                        $Apt = $sqlGetAPT->fetch_row();
                                                        $AptNum = $Apt[0];
                                                        $AptFloorNum = $Apt[1];
                                                    }
                                                    elseif($sqlGetAPT->num_rows <= 0)
                                                    {
                                                        $AptNum[0] = $RES[1];
                                                        $AptFloorNum[0] = $RES[1];
                                                    }
                                                }
                                                elseif($sqlGetRes->num_rows <= 0)
                                                {
                                                    $ResName = $RES[0];
                                                    $ResPN = $RES[0];
                                                    $AptNum = $RES[1];
                                                    $AptFloorNum = $RES[1];
                                                }
                                                // Get Fee Statment.
                                                $sqlGetFeeSTMT = $conn->query("SELECT FeeStatment FROM Fee WHERE ID = '$FeeID'");
                                                if($sqlGetFeeSTMT->num_rows > 0)
                                                {
                                                    $FeeStmt = $sqlGetFeeSTMT->fetch_row();
                                                }
                                                elseif($sqlGetFeeSTMT->num_rows <= 0)
                                                {
                                                    $FeeStmt[0] = $FeeID;
                                                }
                                                $ResDataArr = [
                                                    "name" => $ResName,
                                                    "phoneNumber" => $ResPN,
                                                    "apartmentNum" => $AptNum,
                                                    "apartmentFloorNum" => $AptFloorNum,
                                                    ];
                                                    
                                                $payment = 
                                                [
                                                    "paymentAmount" => $PayAmount,
                                                    "remaining" => $Remaining,
                                                    "paymentAttachment" => $PayAttach,
                                                    "paymentConfirmation" => $PayConfirm,
                                                    "paymentExpense" => $ExpanseName[0],
                                                    "partialPayment" => $PartialPay,
                                                // Get ExpenseName.
                                                    "paymentDate" => $PayDate,
                                                    "paymentCreator" => $ResDataArr,
                                                    "feeStatment" => $FeeStmt
                                                ];
                                            }
                                            elseif($sqlGetPayData->num_rows <= 0)
                                            {
                                                // empty Array.
                                                $payment = [];
                                                
                                            }
                                            // Get Fee amount for and fee attachment Or BillImage and fee Date block and Apartment.
                                            /*
                                            Amount
                                            PaymentMethod
                                            DueDate
                                            PaymentDate
                                            RepeatStatusID
                                            BillID Get Bill ImageURl
                                            ExpenseID
                                            Date
                                            FeeStatment
                                            PartialAmount
                                            */
                                            $sqlGetFee = $conn->query("SELECT Amount, PaymentDate, FeeStatment, BillID FROM Fee WHERE ID = ''");
                                            // ============================================================================================================================
    
                                            $Data["record$count"] = [
                                                "id" =>                     $LogData[0],
                                                "logTypeID" =>              $LogData[4],
                                                "logName" =>                $LogTpeName[0],
                                                "paymentID" =>              $LogData[5],
                                                "paymentData" =>            $payment,
                                                // "feeData" =>
                                                "action" =>                 $LogData[6],
                                                "logRecordInActualTable" => $LogData[7],
                                                "logActualTable" =>         $LogData[8],
                                                "blockTotalFee" =>          $LogData[11],
                                                "blockTotalBalance" =>      $LogData[12],
                                                "date" => $LogData[15]
                                            ];
                                            $count++;
                                        }
                                        $this->returnResponse(200, array_values($Data));
                                    }
                                    else
                                    {
                                        $this->throwError(200, "Financial Log Error or you dont have any Financial movements.");
                                    }
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "User does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Resident does not relate to this block.");
            }
        }
        
    }
    
    private function makePdf($BLKID, $APTID, $UserID, $PaymentID, $BillID)
    {
        header('Content-Type: text/html; charset=UTF-8');
        include("../Config.php");
        include('../vendor/autoload.php');
        
        $Date = date("Y-m-d h:i:sa");
        // Get Block Data (BlockNum / Country / Gov / City / Region / Compound / Street).
        $sqlGetBlockData = $conn->query("SELECT BlockNum, CountryID, GovernateID, CityID, RegionID, CompoundID, StreetID, Image FROM Block WHERE ID = '$BLKID'");
        if($sqlGetBlockData->num_rows > 0)
        {
            $BlkData = $sqlGetBlockData->fetch_row();
            // Get Country
            $sqlGetCountry = $conn->query("SELECT name, sympol From Country Where ID = $BlkData[1]");
            if($sqlGetCountry->num_rows > 0)
            {
                $CountryName = $sqlGetCountry->fetch_row();
                $Currency = $CountryName[1];
            }
            elseif($sqlGetCountry->num_rows <= 0)
            {
                $CountryName = $BlkData[1];
                $Currency = '';
            }
            // Get Governate
            $sqlGetGov = $conn->query("SELECT GOVName From Governate Where ID = $BlkData[2]");
            if($sqlGetGov->num_rows > 0)
            {
                $GovName = $sqlGetGov->fetch_row();
            }
            elseif($sqlGetGov->num_rows <= 0)
            {
                $GovName = $BlkData[2];
            }
            // Get City
            $sqlGetCity = $conn->query("SELECT Name From City Where ID = $BlkData[3]");
            if($sqlGetCity->num_rows > 0)
            {
                $CityName = $sqlGetCity->fetch_row();
            }
            elseif($sqlGetCity->num_rows <= 0)
            {
                $CityName = $BlkData[3];
            }
            // Get Region
            $sqlGetRegion = $conn->query("SELECT RegionName From Region Where ID = $BlkData[4]");
            if($sqlGetRegion->num_rows > 0)
            {
                $RegionName = $sqlGetRegion->fetch_row();
            }
            elseif($sqlGetRegion->num_rows <= 0)
            {
                $RegionName = $BlkData[4];
            }
            // Get Compound
            $sqlGetCompound = $conn->query("SELECT CompundName From Compound Where ID = $BlkData[5]");
            if($sqlGetCompound->num_rows > 0)
            {
                $CompName = $sqlGetCompound->fetch_row();
            }
            elseif($sqlGetCompound->num_rows <= 0)
            {
                $CompName = $BlkData[5];
            }
            // Get Street
            $sqlGetStreet = $conn->query("SELECT StreetName From Street Where ID = $BlkData[6]");
            if($sqlGetStreet->num_rows > 0)
            {
                $StreetName = $sqlGetStreet->fetch_row();
            }
            elseif($sqlGetStreet->num_rows <= 0)
            {
                $StreetName = $BlkData[6];
            }
            
            if($BlkData[5] == NULL || $BlkData[5] == '1')
            {
                $BlockDataPdf = "اتحاد ملاك عمارة رقم $BlkData[0] شارع $StreetName[0] $CityName[0], $GovName[0], $CountryName[0]";
            }
            elseif($BlkData[5] !== NULL || $BlkData[5] > 1)
            {
                $BlockDataPdf = "اتحاد ملاك عمارة رقم $BlkData[0] شارع $StreetName[0] كمبوند $CompName[0] $CityName[0], $GovName[0], $CountryName[0]";
            }
            if(empty($BlkData[7]))
            {
                $BlkImage = "https://kcgwebservices.net/omartyapis/Images/BlockImages/Default.jpg";
            }
            elseif(!empty($BlkData[7]))
            {
                $BlkImage = "https://kcgwebservices.net/omartyapis/Images/BlockImages/$BlkData[7]";
            }
        }
        
        /*
         * 
         *  استلمنا من السيد/ة أحمد علي محمد شقة 3 الدور 1
            مبلغ #150# جنيه (فقط مائة وخمسون جنيها مصريا لاغير
            وذلك نظير سداد الاشتراك الشهري لصيانة العمارة عن شهر 2/2022
        **/
        // Get Resident Data ()
            // Get Resident Name.
            $sqlGetResName = $conn->query("SELECT Name FROM Resident_User WHERE ID = '$UserID'");
            if($sqlGetResName->num_rows > 0)
            {
                $ResName = $sqlGetResName->fetch_row();
                $ResName = "استلمنا من السيد / ة $ResName[0]";
            }
            else
            {
                $ResName = $UserID;
            }
            // Get Apartment Data.
            $sqlGetAptData = $conn->query("SELECT ApartmentNumber, FloorNum, ApartmentName FROM Apartment WHERE ID = '$APTID'");
            $AptDataPdf = $APTID;
            if($sqlGetAptData->num_rows > 0)
            {
                $AptData = $sqlGetAptData->fetch_row();
                $AptDataPdf = "شقة $AptData[2] الدور $AptData[1]";
            }
            // Get Payment Data.
            $sqlGetPaymentData = $conn->query("SELECT Amount, FeeID FROM Payment WHERE ID = '$PaymentID'");
            $PayAmountPdf = "مبلغ مالي";
            if($sqlGetPaymentData->num_rows > 0)
            {
                $PaymentData = $sqlGetPaymentData->fetch_row();
                $PayAmountPdf = "مبلغ #" . $PaymentData[0] . $Currency . "#";
                // Get Fee Statment.
                $sqlGetFeeStmt = $conn->query("SELECT FeeStatment, Date FROM Fee WHERE ID = '$PaymentData[1]'");
                if($sqlGetFeeStmt->num_rows > 0)
                {
                    $FeeStmt = $sqlGetFeeStmt->fetch_row();
                    $FeeStmtPdf = "وذلك نظير سداد $FeeStmt[0] عن شهر $FeeStmt[1]";
                }
                
            }
            $ReceiptStmt = "$ResName $AptDataPdf" . $PayAmountPdf . $FeeStmtPdf;
            // echo $BlkImage;
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->SetDirectionality('rtl');
        $mpdf->autoLangToFont = true;
        $html = "
        <!DOCTYPE html>
        <html style='direction: rtl;'>
            <head>
                <meta charset='utf-8'>
                <style type='text/css'>
                    #BillID {float:left;}
                    #BillImage {float:right;}
                </style>
            </head>
            <body>
                <div style='display: flex; text-align: right; font-weight: bold; background-color:powderblue; width:100%; height:30px; margin: 10px; padding:10px; border-radius: 8px;;'>
                    <table width='100%' hieght = '100%' style='vertical-align: bottom; font-family: serif; font-size: 10pt; color: #000000; font-weight: bold;'>
                        <tr>
                            <td align:right><pre lang='ar' style = 'font-family:xbriyaz'>إيصال سداد (نقدية)</pre></td>
                            <td align:center><pre lang='ar' style = 'font-family:xbriyaz'>رقم الايصال : $BillID </pre></td>
                            <td align:left><pre lang='ar' style = 'font-family:xbriyaz'>Date : $Date</pre></td>
                        </tr>
                    </table>
                </div>
                    <table width='100%' hieght = '100%' style='vertical-align: bottom; font-family: serif; font-size: 15pt; color: #000000; font-weight: bold;'>
                        <tr>
                            <td width='10%' hieght = '50%' align='center'><img id = 'BillImage' src = '$BlkImage' style = 'vertical-align: bottom; text-align: center; font-weight: bold;'></td>
                            <td width='60%' align='right'>
                                <table width='100%' hieght = '100%' style='vertical-align: top; font-family: serif; font-size: 20px; color: #000000; font-weight: bold;'>
                                    <tr>
                                        <td><pre lang='ar' align:center style = 'font-family:xbriyaz; font-size: 100px;'>$BlockDataPdf</pre></td>
                                    </tr>
                                </table>
                            </td>
                        </tr>
                    </table>
                <div>
                <pre align='right' lang='ar' style = 'font-family:xbriyaz; font-size: 200px;'>$ReceiptStmt</pre>
                </div>
                <table width='100%' hieght = '100%' style='vertical-align: bottom; font-family: serif; font-size: 20pt; color: #000000; font-weight: bold;'>
                    <tr>
                        <td align:right><pre lang='ar' align:center style = 'font-family:xbriyaz; font-size: 100px;'>رئيس اتحاد الملاك</pre></td>
                        <td align:left><pre lang='ar' align:left style = 'font-family:xbriyaz; font-size: 100px;'>تأكيد الاستلام</pre></td>
                    </tr>
                </table>
                </body>
        </html>
        ";
        $mpdf->allow_charset_conversion = true;
        // $mpdf->charset_in = 'cp1252';
        $html = mb_convert_encoding($html, 'UTF-8', 'UTF-8');
        $mpdf->WriteHTML($html);
        $mpdf->Output("../Images/BillImages/$BillID.pdf");
    }
    
    private function generateBillForBM($BLKID, $APTID, $Longitude, $Latitude, $PaymentId) // OK Final
    {
        include("../Config.php");
        date_default_timezone_set('Africa/Cairo');

        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(406, $e->getMessage() . " Token Error");
        }

        $userID = $decode->id;
        // $BLKID = $_POST["blockId"];
        // $APTID = $_POST["managerApartmentId"];
        // $apartmentId = $_POST["apartmentId"];
        // $PaymentId = $_POST["paymentId"];
        // // $FeeID To Add in bill
        // // $PaymentID To Add in bill
        // $Longitude = $_POST["longitude"];
        // $Latitude = $_POST["latitude"];
        $CurrentTime = date("Y/m/d H:i:sa");
        $Date = date("Y/m/d h:i:sa");
        
        /*Generating Bill for Block in the next line format.*/ 
        
        // echo $BillID . "  " . $BLKID . "  " . $APTID . "  " . $LastBillInBlock;
        // exit;
        
        // $BillID = "B".$BLKID."A".$APTID."I".$LastBillInBlock+1;
        
        
        // Check Block Existence.
        
        $sqlCheckBlock = $conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows <= 0)
        {
            $this->throwError(200, "Block Not Found.");
        }
        else
        {
            // Check User relation in block.
            $sqlCheckResBlkRel = $conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID' AND RoleID = 1");
            if($sqlCheckResBlkRel->num_rows > 0)
            {
                // Check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == 3)
                {
                    $this->throwError(406, "Sorry block is Banned by Omarty Super Admin.");
                    exit;
                }
                if($blockData[1] == 1)
                {
                    $this->throwError(406, "Sorry block status is Binding.");
                    exit;
                }
                if($blockData[1] == 2)
                {
               
                    $BlockID = "B$BLKID";
                    // Check Manager Apartment Existence.
                    $sqlCheckApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID' AND RoleID = 1");
                    if($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Manager Apartment Not Found.");
                    }
                    else
                    {
                        $AptData = $sqlCheckApt->fetch_row();
                         // Check Resident relation to this apartment.
                        if($AptData[2] == $userID)
                        {
                            // Check Apartment Status.
                            if($AptData[1] == 1)
                            {
                                $this->throwError(406, "Sorry Manager Apartment status is Binding.");
                                exit;
                            }
                            elseif($AptData[1] == 3)
                            {
                                $this->throwError(406, "Sorry Manager Apartment is Banned.");
                                exit;
                            }
                            elseif($AptData[1] == 2)
                            {
                                // Check Resident apartment existence.
                                $sqlCheckResApt = $conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                                if($sqlCheckResApt->num_rows <= 0)
                                {
                                    $this->throwError(200, "Resident Apartment Not Found.");
                                }
                                else
                                {
                                    $APARTID = "A$APTID";
                                    // get last BillID In Block.
                                    $sqlGetLastBill = $conn->query("SELECT ID FROM BILL Where Block = '$BLKID' AND LastBillInBlock > 0");
                                    // if There are no bills for this block.
                                    if($sqlGetLastBill->num_rows <= 0)
                                    {   
                                        // Generate New Bill (B@BLKID A@APTID I1).
                                        $FirstBill = "B" . $BLKID . "A" . $APTID . "I1";
                                        // Save Bill With its name is its ID
                                        $attachments["newName"] = $FirstBill;
                                        $imageUrl = "https://kcgwebservices.net/omartyapis/Images/BillImages/" . $attachments["newName"];
                                        if(!empty($attachments)) { $location = "../Images/BillImages/". $attachments["newName"]; }
                                        if(!empty($attachments)) { $attachName = $attachments["newName"]; }
                                        else { $attachName = NULL; }
                                        // Generate Bill Receipt.   
                                        $this->makePdf($BLKID, $APTID, $userID, $PaymentId, $FirstBill);
                                        
                                        // Insert First Bill In Block And save Block ID (B@BLKID) and Apartment ID (A@APTID) And set LastBillInBlock Column to 1.
                                        $sqlInsertNewLastBill = $conn->query("INSERT INTO BILL (ID, BillImage, Date, PaymentID, Block, Apartment, LastBillInBlock, CreatedAt, CreatedBy) 
                                                                    VALUES ('$FirstBill', '$FirstBill.pdf', '$Date', '$PaymentId', '$BLKID','$APTID', '1', '$CurrentTime', '$userID')");
                                        if($sqlInsertNewLastBill)
                                        {
                                            // Move Image To BillImages Directory
                                            // if(!empty($attachments)) { move_uploaded_file($attachments["tmp_name"], $location); }
                                            
                                            // $this->returnResponse(200, "Generated New Bill.");
                                            return $FirstBill;
                                            
                                            $Action = "Generate Bill.";
                                            // Insert Log Generate Bill.
                                            $BillID = $conn->query("SELECT ID FROM BILL ORDER BY CreatedAt DESC LIMIT 1");
                                            $newId = $BillID->fetch_row();
                                            $sqlBillLog = $conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                    VALUES ('$userID', '$APTID', '$BLKID', 7, '$Action', '$newId[0]', 'BILL', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                        }
                                        else
                                        {
                                            // $this->throwError(200, "Bill Was not Generated Please try again.");
                                            return FALSE;
                                        }
                                    }
                                    // if There are bills for this block.
                                    elseif($sqlGetLastBill->num_rows > 0)
                                    {
                                        // Assign Last BillId in variable @LastBill.
                                        $LastBill = $sqlGetLastBill->fetch_row();
                                        // Trim the bill to get the number after character I refering to ID 
                                        $trmBillId = substr($LastBill[0], strpos($LastBill[0], "I") + 1);
                                        // increment the trimmed number by 1.
                                        $newID = intval($trmBillId) + 1;
                                        // Generate the new ID.
                                        $BillID = "B".$BLKID."A".$APTID."I".$newID;
                                        // Update Old lastBillInBlock to 0.
                                        
                                        $attachments["newName"] = $BillID;
                                        $imageUrl = "https://kcgwebservices.net/omartyapis/Images/BillImages/" . $attachments["newName"];
                                        if(!empty($attachments)) { $location = "../Images/BillImages/". $attachments["newName"]; }
                                        if(!empty($attachments)) { $attachName = $attachments["newName"]; }
                                        else { $attachName = NULL; }
                                        // Generate Bill Receipt.   
                                        $this->makePdf($BLKID, $APTID, $userID, $PaymentId, $BillID);
                                        
                                        $sqlUpdateLastBillInBlock = $conn->query("UPDATE BILL Set LastBillInBlock = 0 WHERE ID = '$LastBill[0]'");
                                        // Insert New bill With Generated ID @BillID and Set LastBillInBlock to 1.
                                        $sqlInsertNewLastBill = $conn->query("INSERT INTO BILL (ID, BillImage, Date, PaymentID, Block, Apartment, LastBillInBlock, CreatedAt, CreatedBy) 
                                                                    VALUES ('$BillID', '$BillID.pdf', '$Date', '$PaymentId', '$BLKID', '$APTID', '1', '$CurrentTime', '$userID')");

                                        if($conn->error)
                                        {
                                            echo $conn->error;
                                        }
                                         if($sqlInsertNewLastBill)
                                        {
                                            // Move Image To BillImages Directory
                                            // if(!empty($attachments)) { move_uploaded_file($attachments["tmp_name"], $location); }
                                            // $this->returnResponse(200, "Generated New Bill.");
                                            return $BillID;
                                            
                                            $Action = "Generate Bill.";
                                            // Insert Log Generate Bill.
                                            $BillID = $conn->query("SELECT ID FROM BILL ORDER BY CreatedAt DESC LIMIT 1");
                                            $newId = $BillID->fetch_row();
                                            $sqlBillLog = $conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                    VALUES ('$userID', '$APTID', '$BLKID', 7, '$Action', '$newId[0]', 'BILL', '$Longitude', '$Latitude', '$Date', '$CurrentTime')");
                                        }
                                        else
                                        {
                                            // $this->throwError(200, "Bill Was not Generated Please try again.");
                                            return FALSE;
                                        }
                                    }
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Manager Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Manager does not relate to this Apartment.");
                        }
                    }
                }
                else
                {
                    $this->throwError(406, "Block status is not acceptable.");
                }
            }
            else
            {
                $this->throwError(406, "Manager does not relate to this block.");
            }
        }
        
    }

}