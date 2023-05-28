<?php
    include("../vendor/autoload.php");
    header("content-type: Application/json");
    use Firebase\JWT\JWT;
    use Firebase\JWT\Key;
    

class Create extends Functions
{   

    public function __construct()
    {
        include("../Config.php");
        
        $this->conn = $conn;
        
    }

    public function AskForApt() // OK Final
    {
        date_default_timezone_set('Africa/Cairo');

        $BLKID = $_POST["blockID"];
        // $APTNUM = $_POST["aptNum"];
        $APTName = $_POST["aptName"];
        $FloorNum= $_POST["aptFloorNum"];
        $Longitude = $_POST["longitude"];
        $Latitude= $_POST["latitude"];
        $Date = date("Y-m-d H:i:s");
        $Date12 = date("Y-m-d h:i:sa");
        
        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        // include("../Config.php");
        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(401, $e->getMessage());
            exit;
        }

        // identify resident by getting ID.

        if(empty($decode->id))
        {
            $this->throwError(403, "User not found.");
            exit;
        }
        elseif( !empty($decode->id) )
        {
            // Get Resident ID.
            $UserID = $decode->id;

                // Check Block Existence.
                $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID, NumberOfAppartments, NumberOfFloors FROM Block WHERE ID='$BLKID'");
                if( $sqlCheckBlock->num_rows <= 0 )
                {
                    $this->throwError(200, "Block doesn't exist.");
                    exit;
                }
                elseif( $sqlCheckBlock->num_rows > 0 )
                {
                    $BlkData = $sqlCheckBlock->fetch_row();
                    // Check Block Status.
                    if($BlkData[1] == '2')
                    {
                        // Get Block's Apartments.
                        $sqlGetAptFromBlk = $this->conn->query("SELECT FloorNum, ApartmentNumber, ApartmentName FROM Apartment WHERE BlockID = '$BLKID'");
                        $AptNameNum = [];
                        $count = 0;
                        
                        while($APTNameInBlk = $sqlGetAptFromBlk->fetch_row())
                        {
                            $AptNameNum[$count] = 
                            [
                                "apartmentNumber" => $APTNameInBlk[1],
                                "apartmentFloorNumber" => $APTNameInBlk[0],
                                "apartmentName" => $APTNameInBlk[2],
                            ];
                            $count++;
                        }
                        $AptNameNum += ["numOfFloors" => $BlkData[3], "numOfApartments" => $BlkData[2] ];
                        // array_push($APTNum, ["numOfFloors" => $BlkData[3], "numOfApartments" => $BlkData[2] ]);
                        // $this->returnResponse(200, $AptNameNum);
                        // Get Apartment ID. 
                        $sqlGetAptID = $this->conn->query("SELECT ID FROM Apartment WHERE ApartmentName = '$APTName' AND BlockID = '$BLKID'");
                        $APTID = $sqlGetAptID->fetch_row();
                        // Check If User already has any relation to this apartment.
                        $sqlCheckResAptRel = $this->conn->query("SELECT * FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$UserID' AND ApartmentID = '$APTID[0]' AND BlockID = '$BLKID'");
                        if($sqlCheckResAptRel->num_rows > 0)
                        {
                            $ResConcData = $sqlCheckResAptRel->fetch_row();
                            switch($ResConcData[4])
                            {
                                case "1" : $Role = "BlockManager";
                                break;
                                case "2" : $Role = "Cashier";
                                break;
                                case "3" : $Role = "Member Of The Board";
                                break;
                                case "4" : $Role = "Vendor";
                                break;
                                case "5" : $Role = "Client";
                                break;
                                case "6" : $Role = "Resident";
                                break;
                                case "7" : $Role = "Watchman";
                                break;
                            }
                            switch($ResConcData[5])
                            {
                                case "1" : $Status = "Pending";
                                break;
                                case "2" : $Status = "Active";
                                break;
                                case "3" : $Status = "Banned";
                                break;
                            }
                            $resConc = 
                            [
                                "residentRole" => $Role,
                                "residentStatus" => $Status
                            ];
                            $this->throwError(200, $resConc);
                        }
                        if($sqlCheckResAptRel->num_rows <= 0)
                        {
                            // Get Number of empty apartments in block.
                            $sqlGetAptNum = $this->conn->query("SELECT ID FROM Apartment WHERE BlockID = '$BLKID' AND ApartmentName IS NULL");
                            $numOfAptsInBlock = $sqlGetAptNum->num_rows;
                            
                            // Get Number Of All Units in block.
                            $sqlNumOfUnitsInBlk = $this->conn->query("SELECT ID FROM Apartment WHERE BlockID = '$BLKID'");
                            
                            $newAptNum = $sqlNumOfUnitsInBlk->num_rows + 1;
                            
                            if($numOfAptsInBlock > '0' )
                            {    
                                // Get Apartment in this block with no Names.
                                $sqlGetRandAptInBlk = $this->conn->query("SELECT ID FROM Apartment WHERE BlockID = '$BLKID' AND ApartmentName IS NULL ORDER BY ID ASC LIMIT 1");
                                $APTID = $sqlGetRandAptInBlk->fetch_row();
                            }
                            // Get Resident Role.
                            $sqlGetResidentRole = $this->conn->query("SELECT RoleID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$UserID' AND BlockID = '$BLKID'");
                            $ResRole = $sqlGetResidentRole->fetch_row();
                            // If User Is Block Manager.
                            if($ResRole[0] == '1')
                            {
                                if(!empty($FloorNum))
                                {
                                    if($numOfAptsInBlock == '0')
                                    {
                                        // Only Block Manager can Insert New Apartments.
                                        $sqlInsertNewApt = $this->conn->query("INSERT INTO Apartment (FloorNum, ApartmentNumber, balance, Fees, BlockID, StatusID, CreatedAt, ApartmentName) 
                                                                                        VALUES ('$FloorNum', '$newAptNum', 0, 0, '$BLKID', 2, '$Date', '$APTName')");
                                        // Get last inserted ID in Apartment table.
                                        $sqlGetLastIdInAptTable = $this->conn->query("SELECT ID FROM Apartment ORDER BY ID DESC LIMIT 1");
                                        $newId = $sqlGetLastIdInAptTable->fetch_row();
                                        
                                        // Create Relation between User And apartment.
                                        $sqlResAptConc = $this->conn->query("INSERT INTO RES_APART_BLOCK_ROLE (ResidentID, ApartmentID, BlockID, RoleID, StatusID) VALUES ('$UserID', '$newId[0]', '$BLKID', 6, 2)");
                                        
                                        // Create Financial account for apartment.
                                        $sqlInsertAPTFA = $this->conn->query("INSERT INTO FinancialAcount (Balance, MonthIncome, MonthExpense, FeeAmount, MonthlyFeeAmount, BlockID, ApartmentID, ResidentID, CreatedAt)
                                                                                            VALUES (0, 0, 0, 0, 0, '$BLKID', '$newId[0]', '$UserID', '$Date')");
                                        
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                        // Creating Apartment Log.
                                        $Action = "Creating New Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$newId[0]', '$BLKID', 6, '$Action', '$newId[0]', 'Apartment', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                                                                    
                                        // Relation Log
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDRel = $this->conn->query("SElECT ID FROM RES_APART_BLOCK_ROLE ORDER BY ID DESC LIMIT 1");
                                        $newId2 = $sqlGetLastIDRel->fetch_row();
                                        $Action2 = "Creating Relation between resident and New Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$newId[0]', '$BLKID', 3, '$Action2', '$newId2[0]', 'RES_APART_BLOCK_ROLE', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                        // Creating FinancialAcount log 
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDFA = $this->conn->query("SElECT ID FROM FinancialAcount ORDER BY ID DESC LIMIT 1");
                                        $newId3 = $sqlGetLastIDFA->fetch_row();
                                        $Action3 = "Creating Financial Acount of the New Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$newId[0]', '$BLKID', 3, '$Action3', '$newId3[0]', 'FinancialAcount', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                                                                    
                                    }
                                    // Check For Number of Units to the number Manager entered.
                                    elseif($numOfAptsInBlock > '0')
                                    {    
                                        // Update Apartment Name And Floor Number.
                                        $sqlUpdateAptName = $this->conn->query("UPDATE Apartment SET FloorNum = '$FloorNum', StatusID = '2', Apartmentname = '$APTName', UpdatedAt = '$Date' WHERE ID = '$APTID[0]'");
                                        // Create Relation between User And apartment.
                                        $sqlResAptConc = $this->conn->query("INSERT INTO RES_APART_BLOCK_ROLE (ResidentID, ApartmentID, BlockID, RoleID, StatusID) VALUES ('$UserID', '$APTID[0]', '$BLKID', 6, 2)");
                                        // Create Financial account for apartment.
                                        $sqlInsertAPTFA = $this->conn->query("INSERT INTO FinancialAcount (Balance, MonthIncome, MonthExpense, FeeAmount, MonthlyFeeAmount, BlockID, ApartmentID, ResidentID, CreatedAt)
                                                                                            VALUES (0, 0, 0, 0, 0, '$BLKID', '$APTID[0]', '$UserID', '$Date')");
                                                                                            
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                        
                                        // Creating Apartment Log.
                                        $Action = "Update Apartment Name And other data";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 15, '$Action', '$APTID[0]', 'Apartment', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                                                                    
                                        // Relation Log
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDRel = $this->conn->query("SElECT ID FROM RES_APART_BLOCK_ROLE ORDER BY ID DESC LIMIT 1");
                                        $newId2 = $sqlGetLastIDRel->fetch_row();
                                        $Action2 = "Creating Relation between resident and Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 3, '$Action2', '$newId2[0]', 'RES_APART_BLOCK_ROLE', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                        // Creating FinancialAcount log 
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDFA = $this->conn->query("SElECT ID FROM FinancialAcount ORDER BY ID DESC LIMIT 1");
                                        $newId3 = $sqlGetLastIDFA->fetch_row();
                                        $Action3 = "Creating Financial Acount of Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 3, '$Action3', '$newId3[0]', 'FinancialAcount', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                    
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                    }
                                }
                                elseif(empty($FloorNum))
                                {
                                    if($numOfAptsInBlock == '0')
                                    {
                                        // Only Block Manager can Insert New Apartments.
                                        $sqlInsertNewApt = $this->conn->query("INSERT INTO Apartment (ApartmentNumber, balance, Fees, BlockID, StatusID, CreatedAt, ApartmentName) 
                                                                                        VALUES ('$newAptNum', 0, 0, '$BLKID', 2, '$Date', '$APTName')");
                                        // Get last inserted ID in Apartment table.
                                        $sqlGetLastIdInAptTable = $this->conn->query("SELECT ID FROM Apartment ORDER BY ID DESC LIMIT 1");
                                        $newId = $sqlGetLastIdInAptTable->fetch_row();
                                        
                                        // Create Relation between User And apartment.
                                        $sqlResAptConc = $this->conn->query("INSERT INTO RES_APART_BLOCK_ROLE (ResidentID, ApartmentID, BlockID, RoleID, StatusID) VALUES ('$UserID', '$newId[0]', '$BLKID', 6, 2)");
                                        
                                        // Create Financial account for apartment.
                                        $sqlInsertAPTFA = $this->conn->query("INSERT INTO FinancialAcount (Balance, MonthIncome, MonthExpense, FeeAmount, MonthlyFeeAmount, BlockID, ApartmentID, ResidentID, CreatedAt)
                                                                                            VALUES (0, 0, 0, 0, 0, '$BLKID', '$newId[0]', '$UserID', '$Date')");
                                        
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                        // Creating Apartment Log.
                                        $Action = "Creating New Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$newId[0]', '$BLKID', 6, '$Action', '$newId[0]', 'Apartment', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                                                                    
                                        // Relation Log
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDRel = $this->conn->query("SElECT ID FROM RES_APART_BLOCK_ROLE ORDER BY ID DESC LIMIT 1");
                                        $newId2 = $sqlGetLastIDRel->fetch_row();
                                        $Action2 = "Creating Relation between resident and New Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$newId[0]', '$BLKID', 3, '$Action2', '$newId2[0]', 'RES_APART_BLOCK_ROLE', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                        // Creating FinancialAcount log 
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDFA = $this->conn->query("SElECT ID FROM FinancialAcount ORDER BY ID DESC LIMIT 1");
                                        $newId3 = $sqlGetLastIDFA->fetch_row();
                                        $Action3 = "Creating Financial Acount of the New Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$newId[0]', '$BLKID', 3, '$Action3', '$newId3[0]', 'FinancialAcount', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                    }
                                    elseif($numOfAptsInBlock > '0')
                                    {    
                                        // Update Apartment Name And Floor Number.
                                        $sqlUpdateAptName = $this->conn->query("UPDATE Apartment SET StatusID = '2', Apartmentname = '$APTName', UpdatedAt = '$Date' WHERE ID = '$APTID[0]'");
                                        // Create Relation between User And apartment.
                                        $sqlResAptConc = $this->conn->query("INSERT INTO RES_APART_BLOCK_ROLE (ResidentID, ApartmentID, BlockID, RoleID, StatusID) VALUES ('$UserID', '$APTID[0]', '$BLKID', 6, 2)");
                                        // Create Financial account for apartment.
                                        $sqlInsertAPTFA = $this->conn->query("INSERT INTO FinancialAcount (Balance, MonthIncome, MonthExpense, FeeAmount, MonthlyFeeAmount, BlockID, ApartmentID, ResidentID, CreatedAt)
                                                                                            VALUES (0, 0, 0, 0, 0, '$BLKID', '$APTID[0]', '$UserID', '$Date')");
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                        
                                        // Creating Apartment Log.
                                        $Action = "Update Apartment Name And other data";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 15, '$Action', '$APTID[0]', 'Apartment', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                                                                    
                                        // Relation Log
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDRel = $this->conn->query("SElECT ID FROM RES_APART_BLOCK_ROLE ORDER BY ID DESC LIMIT 1");
                                        $newId2 = $sqlGetLastIDRel->fetch_row();
                                        $Action2 = "Creating Relation between resident and Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 3, '$Action2', '$newId2[0]', 'RES_APART_BLOCK_ROLE', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                        // Creating FinancialAcount log 
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDFA = $this->conn->query("SElECT ID FROM FinancialAcount ORDER BY ID DESC LIMIT 1");
                                        $newId3 = $sqlGetLastIDFA->fetch_row();
                                        $Action3 = "Creating Financial Acount of Apartment For Block Manager";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 3, '$Action3', '$newId3[0]', 'FinancialAcount', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                    
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                    }
                                }
                            }
                            elseif($ResRole[0] !== '1')
                            {
                                if(!empty($FloorNum))
                                {
                                    if($numOfAptsInBlock > 0)
                                    {
                                        // Update Apartment Name And Floor Number.
                                        $sqlUpdateAptName = $this->conn->query("UPDATE Apartment SET FloorNum = '$FloorNum', StatusID = '1', Apartmentname = '$APTName', UpdatedAt = '$Date' WHERE ID = '$APTID[0]'");
                                        // Create Relation between User And apartment.
                                        $sqlResAptConc = $this->conn->query("INSERT INTO RES_APART_BLOCK_ROLE (ResidentID, ApartmentID, BlockID, RoleID, StatusID) VALUES ('$UserID', '$APTID[0]', '$BLKID', 6, 1)");
                                        // Create Financial account for apartment.
                                        $sqlInsertAPTFA = $this->conn->query("INSERT INTO FinancialAcount (Balance, MonthIncome, MonthExpense, FeeAmount, MonthlyFeeAmount, BlockID, ApartmentID, ResidentID, CreatedAt)
                                                                                            VALUES (0, 0, 0, 0, 0, '$BLKID', '$APTID[0]', '$UserID', '$Date')");
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                        
                                        // Creating Apartment Log.
                                        $Action = "Update Apartment Name And other data";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 15, '$Action', '$APTID[0]', 'Apartment', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                                                                    
                                        // Relation Log
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDRel = $this->conn->query("SElECT ID FROM RES_APART_BLOCK_ROLE ORDER BY ID DESC LIMIT 1");
                                        $newId2 = $sqlGetLastIDRel->fetch_row();
                                        $Action2 = "Creating Relation between resident and Apartment";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 3, '$Action2', '$newId2[0]', 'RES_APART_BLOCK_ROLE', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                        // Creating FinancialAcount log 
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDFA = $this->conn->query("SElECT ID FROM FinancialAcount ORDER BY ID DESC LIMIT 1");
                                        $newId3 = $sqlGetLastIDFA->fetch_row();
                                        $Action3 = "Creating Financial Acount of Apartment";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 3, '$Action3', '$newId3[0]', 'FinancialAcount', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                    
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                    }
                                    else
                                    {
                                        $this->throwError(200, "No more Units in the block");
                                    }
                                }
                                elseif(empty($FloorNum))
                                {
                                    if($numOfAptsInBlock > 0)
                                    {
                                        // Update Apartment Name And Floor Number.
                                        $sqlUpdateAptName = $this->conn->query("UPDATE Apartment SET StatusID = '1', Apartmentname = '$APTName', UpdatedAt = '$Date' WHERE ID = '$APTID[0]'");
                                        // Create Relation between User And apartment.
                                        $sqlResAptConc = $this->conn->query("INSERT INTO RES_APART_BLOCK_ROLE (ResidentID, ApartmentID, BlockID, RoleID, StatusID) VALUES ('$UserID', '$APTID[0]', '$BLKID', 6, 1)");
                                        // Create Financial account for apartment.
                                        $sqlInsertAPTFA = $this->conn->query("INSERT INTO FinancialAcount (Balance, MonthIncome, MonthExpense, FeeAmount, MonthlyFeeAmount, BlockID, ApartmentID, ResidentID, CreatedAt)
                                                                                            VALUES (0, 0, 0, 0, 0, '$BLKID', '$APTID[0]', '$UserID', '$Date')");
                                        
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                        
                                        // Creating Apartment Log.
                                        $Action = "Update Apartment Name And other data";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 15, '$Action', '$APTID[0]', 'Apartment', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                                                                    
                                        // Relation Log
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDRel = $this->conn->query("SElECT ID FROM RES_APART_BLOCK_ROLE ORDER BY ID DESC LIMIT 1");
                                        $newId2 = $sqlGetLastIDRel->fetch_row();
                                        $Action2 = "Creating Relation between resident and Apartment";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 3, '$Action2', '$newId2[0]', 'RES_APART_BLOCK_ROLE', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                        // Creating FinancialAcount log 
                                        // Get Last Inserted record in table RES_APART_BLOCK_ROLE.
                                        $sqlGetLastIDFA = $this->conn->query("SElECT ID FROM FinancialAcount ORDER BY ID DESC LIMIT 1");
                                        $newId3 = $sqlGetLastIDFA->fetch_row();
                                        $Action3 = "Creating Financial Acount of Apartment";
                                        // Insert into Logs table.
                                        $sqlInsertToLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeID, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                                    VALUES ('$UserID', '$APTID[0]', '$BLKID', 3, '$Action3', '$newId3[0]', 'FinancialAcount', '$Longitude', '$Latitude', '$Date12', '$Date')");
                                    
                                        // =====================================================================<><><><><><>Logs<><><><><><>=====================================================================
                                    }
                                    else
                                    {
                                        $this->throwError(200, "No more Units in the block");
                                    }
                                }
                            }
                                
                            // If User Is Not Block Manager.
                            
                        }
                    }
                    elseif($BlkData[1] == '1')
                    {
                        $this->throwError(200, "Block Status is still Binding.");
                    }
                    elseif($BlkData[1] == '3')
                    {
                        $this->throwError(200, "Block is Banned.");
                    }
                    else
                    {
                        $this->throwError(200, "Block Status is not acceptable.");
                    }
                    
                }
            
        }
    }

    public function CreateBlock() // OK Final
    {
        date_default_timezone_set('Africa/Cairo');
        // include("../Config.php");
        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(401, $e->getMessage());
        }

        $BlockNum = $_POST["blockNum"];
        $BlockName = $_POST["blockName"];
        $NumberOfAppartments = $_POST["numOfApartments"];
        $NumberOfFloors = $_POST["numOfFloors"]; // ================================>>
        $Image = $_POST["image"];
        $Password = $_POST["password"];
        $Balance = $_POST["balance"];
        $Fees = $_POST["fees"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $CountryID = $_POST["countryID"];
        $GovernateID = $_POST["governateID"];
        $CityID = $_POST["cityID"];
        $RegionID = $_POST["regionID"];
        $CompoundID = $_POST["compoundID"];
        $StreetID = $_POST["streetID"];
        $BMapartmentNum = $_POST["apartmentNum"]; // ================================>>
        $BMapartmentFlorNum = $_POST["apartmentFloorNum"]; // ================================>>
        $BMapartmentName = $_POST["apartmentName"]; // ================================>>

        if( empty($decode->id) )
        {
            $this->throwError(403, "User not found. please update your Phone Number.");
        }
        elseif( !empty($decode->id) )
        {
            $UserID = $decode->id;
            $CurrentDate = date("Y-m-d H-i-s");
            $date = date("Y-m-d h-i-sa");

                if(empty($BlockNum))                { $this->throwError(406, "Please enter Block Number"); }
                if(empty($BlockName))               { $BlockName = ""; }
                if(empty($NumberOfAppartments))     { $this->throwError(406, "Please enter Number of Apartments"); }
                if(empty($NumberOfFloors))          { $this->throwError(406, "Please enter Number of floors"); }
                if(empty($Image))                   { $Image = 'Default.jpg'; }
                if(empty($Password))                { $Password = ''; }
                if(empty($Balance))                 { $Balance = 0; }
                if(empty($Fees))                    { $Fees = 0; }
                if(empty($Longitude))               { $Longitude = 0; }
                if(empty($Latitude))                { $Latitude = 0; }
                if(empty($BMapartmentNum))          { $this->throwError(406, "Please enter your Apartment Number"); }
                if(empty($BMapartmentFlorNum))      { $this->throwError(406, "Please enter your apartment floor number"); }
                // =============================================Foreign Keys=============================================
                
               if(empty($CountryID))
                {
                    $CountryID = 1;
                    $this->throwError(406, "Please enter Country ID");
                }
                if(empty($GovernateID))
                {
                    $GovernateID = 1;
                    $this->throwError(406, "Please enter GovernateID ID");
                }
                if($GovernateID == -1)
                {
                        $GovernateName = filter_var($_POST["governateName"], FILTER_SANITIZE_STRING);
                        if(!empty($GovernateName))
                        {   // Check if New inserted governate existes in db.
                            $sqlCheckGov = $this->conn->query("SELECT ID FROM Governate WHERE GOVName = '$GovernateName' AND CountryID = '$CountryID'");
                            if($sqlCheckGov->num_rows > 0)
                            {
                                $govid = $sqlCheckGov->fetch_row();
                                $GovernateID = $govid[0];
                                
                                // $this->throwError(205, "Governate name already exists.");
                            }
                            else
                            {
                                 // insert New Governate Name to database.
                                $sqlInsertGovName = $this->conn->query("INSERT INTO Governate (GovName, CountryID) VALUES ('$GovernateName', '$CountryID')");
                                // Insert to Logs table
                                $sqlBlockLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, Date) VALUES ('$UserID', '$APTID', '$BLKID', 2, '$Action1', '$date')");
                                // get last inserted Governate id.
                                $GovId = $this->conn->insert_id;
                                $GovernateID = $GovId;    
                            }
                        }
                        elseif(empty($GovernateName))
                        {
                            $this->throwError(200, "Please enter Governate name.");
                        }
                }
                if(empty($CityID))
                {
                    $CityID = 1;
                    $this->throwError(406, "Please enter City ID");
                }
                if($CityID == -1)
                {
                        $CityName = filter_var($_POST["cityName"], FILTER_SANITIZE_STRING);
                        if(!empty($CityName))
                        {   // Check if New inserted city existes in db.
                            $sqlCheckCity = $this->conn->query("SELECT ID FROM City WHERE Name = '$CityName' AND CountryID = '$CountryID' AND GovID = '$GovernateID'");
                            if($sqlCheckCity->num_rows > 0)
                            {
                                $cityid = $sqlCheckCity->fetch_row();
                                $CityID = $cityid[0];
                                // $this->throwError(205, "City name already exists.");
                            }
                            else
                            {
                                // insert New City Name to database.
                                $sqlInsertCityName = $this->conn->query("INSERT INTO City (Name, CountryID, GovID) VALUES ('$CityName', '$CountryID', '$GovernateID')");
                                // get last inserted City id.
                                $cityId = $this->conn->insert_id;
                                $CityID = $cityId;
                            }
                        }
                        elseif(empty($CityName))
                        {
                            $this->throwError(200, "Please enter City name.");
                        }

                    
                }
                if(empty($RegionID))
                {
                    $RegionID = 1; 
                    // $this->throwError(101, "Please enter Region ID");
                }
                if($RegionID == -1)
                {
                    $RegionName = filter_var($_POST["regionName"], FILTER_SANITIZE_STRING);
                    if(!empty($RegionName))
                    {
                        // Check if New inserted Region existes in db.
                        $sqlCheckRegion = $this->conn->query("SELECT ID FROM Region WHERE RegionName = '$RegionName' AND CountryID = '$CountryID' AND GovID = '$GovernateID' AND CityID = '$CityID'");
                        if($sqlCheckRegion->num_rows > 0)
                        {
                            $regid = $sqlCheckRegion->fetch_row();
                            $RegionID = $regid[0];
                            // $this->throwError(200, "Region name already exists.");
                        }
                        else
                        {
                            // insert New Region Name to database.
                            $sqlInsertRigionName = $this->conn->query("INSERT INTO Region (RegionName, CountryID, GovID, CityID) VALUES ('$RegionName', '$CountryID', '$GovernateID', '$CityID')");
                            // get last inserted Region id.
                            $regionId = $this->conn->insert_id;
                            $RegionID = $regionId;
                        }
                    }
                    elseif(empty($RegionName))
                    {
                        $this->throwError(200, "Please enter Region name.");
                    }
                }
                if(empty($CompoundID))
                {
                    $CompoundID = 1;
                    // $this->throwError(101, "Please enter Compound ID");
                }
                  if($CompoundID == -1)
                {
                    $CompoundName = filter_var($_POST["compoundName"], FILTER_SANITIZE_STRING);
                    if(!empty($CompoundName))
                    {
                        // Check if New inserted Compound existes in db.
                        $sqlCheckCompound = $this->conn->query("SELECT ID FROM Compound WHERE CompundName = '$CompoundName' AND CountryID = '$CountryID' AND GovID = '$GovernateID' AND CityID = '$CityID' AND RegionID = '$RegionID'");
                        if($sqlCheckCompound->num_rows > 0)
                        {
                            $compid = $sqlCheckCompound->fetch_row();
                            $CityID = $cityid[0];
                            // $this->throwError(200, "Compound name already exists.");
                        }
                        else
                        {
                            // insert New Compound Name to database.
                            $sqlInsertCompoundName = $this->conn->query("INSERT INTO Compound (CompundName, CountryID, GovID, CityID, RegionID) VALUES ('$CompoundName', '$CountryID', '$GovernateID', '$CityID', '$RegionID')");
                            // get last inserted Compound id.
                            $compId = $this->conn->insert_id;
                            $CompoundID = $compId;
                        }
                    }
                    elseif(empty($CompoundName))
                    {
                        $this->throwError(200, "Please enter Compound name.");
                    }
                }
                if(empty($StreetID))
                {
                    $StreetID = 1;
                    $this->throwError(406, "Please enter Street ID");

                }
                if($StreetID == -1)
                {
                    $StreetName = filter_var($_POST["streetName"], FILTER_SANITIZE_STRING);
                      if(!empty($StreetName))
                    {
                        // Check if New inserted Street existes in db.
                        $sqlCheckStreet = $this->conn->query("SELECT ID FROM Street WHERE StreetName = '$StreetName' AND CountryID = '$CountryID' AND GovID = '$GovernateID' AND CityID = '$CityID' AND RegionID = '$RegionID' AND CompundID = '$CompoundID'");
                        if($sqlCheckStreet->num_rows > 0)
                        {
                            $streetid = $sqlCheckStreet->fetch_row();
                            $StreetID = $streetid[0];
                            // $this->throwError(205, "Street name already exists.");
                        }
                        else
                        {
                            // insert New Street Name to database.
                            $sqlInsertStreetName = $this->conn->query("INSERT INTO Street (StreetName, CountryID, GovID, CityID, RegionID, CompundID) VALUES ('$StreetName', '$CountryID', '$GovernateID', '$CityID', '$RegionID', '$CompoundID')");
                            // get last inserted Street id.
                            $streetId = $this->conn->insert_id;
                            $StreetID = $streetId;
                        }
                    }
                    elseif(empty($StreetName))
                    {
                        $this->throwError(200, "Please enter Street name.");
                    }
                    
                }
                // ======================================================================================================

                // Check Block Existence.
                // get block id by resident ID.
                $sqlCheckBlock = $this->conn->query("SELECT ID FROM Block Where BlockNum = '$BlockNum' AND CountryID = '$CountryID' AND GovernateID = '$GovernateID' AND CityID = '$CityID' AND RegionID = '$RegionID' AND CompoundID = '$CompoundID' AND StreetID = '$StreetID';");
                if( $sqlCheckBlock->num_rows > 0 )
                {
                    // $BLKID = $sqlCheckBlock->fetch_row();
                    $this->throwError(200, "Block already registered.");
                }
                elseif( $sqlCheckBlock->num_rows <= 0 )
                {
                    // Insert Block Data.
                    $sqlInsertBlock = $this->conn->query("INSERT INTO Block (BlockNum, BlockName, NumberOfAppartments, NumberOfFloors, Image, Password, Balance, Fees, Longitude, Latitude, CountryID, GovernateID, CityID, RegionID, CompoundID, StreetID, StatusID, CreatedAt) 
                                                VALUES ('$BlockNum', '$BlockName', $NumberOfAppartments, $NumberOfFloors, '$Image', '$Password', $Balance, $Fees, '$Longitude', '$Latitude', $CountryID, $GovernateID, $CityID, $RegionID, $CompoundID, $StreetID, 2, '$date');");
                   
                    if($sqlInsertBlock)
                    {
                        // Get last inserted Block ID.
                         $BLKID = $this->conn->insert_id;
                         
                         // Add User To Chat Room.
                        $sqlAddUserToChat = $conn->query("INSERT INTO Chat (BlockID, UserIDs) VALUES ('$BLKID', '$UserID')");
                        
                        // insert block manager apartment.
                        $sqlInsertBMApartment = $this->conn->query("INSERT INTO Apartment (FloorNum, ApartmentNumber, ApartmentName, BlockID, StatusID, CreatedAt) VALUES ('$BMapartmentFlorNum', '$BMapartmentNum', '$BMapartmentName', '$BLKID', '2', '$date');");
                        // Get last inserted apartment ID.
                        $APTID = $this->conn->insert_id;
                        
                        // insert User Relation to block and apartment in RES_APART_BLOCK_ROLE as block manager.
                        $sqlInsertrelationBM = $this->conn->query("INSERT INTO RES_APART_BLOCK_ROLE (ResidentID, ApartmentID, BlockID, RoleID, StatusID) VALUES ('$UserID', '$APTID', '$BLKID', '1', '2');");
                        
                        // Create Financial Block Acount;
                            
                            $sqlInsertBlkFinAcc = $this->conn->query("INSERT INTO FinancialAcount (Balance, FeeAmount, MonthlyFeeAmount, BlockID, ResidentID, CreatedAt) 
                                                VALUES (0, 0, 0, '$BLKID', '$UserID', '$CurrentDate')");
                                                $BLKFAID = $this->conn->insert_id;
                        // Create Financial Block Acount;
                            $sqlInsertAptFinAcc = $this->conn->query("INSERT INTO FinancialAcount (Balance, FeeAmount, MonthlyFeeAmount, BlockID, ApartmentID, ResidentID, CreatedAt) 
                                                VALUES (0, 0, 0, '$BLKID', '$APTID', '$UserID', '$CurrentDate')");
                                                $APTFAID = $this->conn->insert_id;
                        $this->returnResponse(200, "Block Registered");
                        
                        // Log Insertion.
                        $Action1 = "Create New Block";
                        $Action2 = "Create New Apartment for Block Manager";
                        $Action3 = "Create New Financial Account for new Block";
                        $Action4 = "Create New Financial Account for new Apartment";
                        // Log insert Create new block.
                        $sqlBlockLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                VALUES ('$UserID', '$APTID', '$BLKID', 2, '$Action1', '$BLKID', 'Block', '$Longitude', '$Latitude', '$date', '$CurrentDate')");
                        /*  Log insert Create new Apartment for Block manager which will reflect Once 
                        *   in RES_APART_BLOCK_ROLE 1-(as role = block manager).
                        */
                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                VALUES ('$UserID', '$APTID', '$BLKID', 6, '$Action2', '$APTID', 'Apartment', '$Longitude', '$Latitude', '$date', '$CurrentDate')");

                        // Log Insert Create New Financial Account for new Block
                        // Standing HERE
                        $sqlFinBlkLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date ,CreatedAt) 
                                                                VALUES ('$UserID', '$APTID', '$BLKID', 21, '$Action3', '$BLKFAID', 'FinancialAcount', '$Longitude', '$Latitude', '$date', '$CurrentDate')");
                        // Log Insert Create New Financial Account for new Apartment.
                        $sqlFinAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date ,CreatedAt) 
                                                                VALUES ('$UserID', '$APTID', '$BLKID', 21, '$Action4', '$APTFAID', 'FinancialAcount', '$Longitude', '$Latitude', '$date', '$CurrentDate')");
                                                                
                        // Insert Number of Apartments as $NumberOfApartments is their number.
                        // Enter Apartments Per Floor.
                        // 20/4
                        // $NNOA => New Number Of Apartments which equals number of apartments except Block manager's apartment.
                        $NNOA = $NumberOfAppartments;
                        for($i = 1; $i <= $NNOA; $i++)
                        {
                            // Insert $i Number Of Apartments.
                            if($i == $BMapartmentNum)
                            {
                                continue;
                            }
                            $sqlInsertApartment = $this->conn->query("INSERT INTO Apartment (ApartmentNumber, balance, Fees, BlockID, CreatedAt) 
                                                        VALUES ('$i', 0, 0, '$BLKID', '$CurrentDate')");
                                                        
                            // Insert Logs.
                                $Action = "Create New Apartment While Creating New Block With ID = $BLKID";
                                // Get last inserted Block ID.
                                $APTID2 = $this->conn->query("SELECT ID FROM Apartment ORDER BY ID DESC LIMIT 1");
                                $newId = $APTID2->fetch_row();
                                // Log insert Create new block.
                                $sqlBlockLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt)
                                                                        VALUES ('$UserID', '$newId[0]', '$BLKID', 6, '$Action', '$newId[0]', 'Apartment', '$Longitude', '$Latitude', '$date', '$CurrentDate')");
                                                                        
                        }
                        // IF inserted new Places.
                        if($sqlInsertStreetName === true)
                        {
                            
                            $STRID = $this->conn->query("SELECT ID FROM Street ORDER BY ID DESC LIMIT 1");
                            $NewID = $STRID->fetch_row();
                            $Action3 = "Insert New Street";
                            $sqlStreetLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date, CreatedAt)
                                                                    VALUES ('$UserID', '$APTID', '$BLKID', 3, '$Action3', '$NewID[0]', 'Street', '$date', '$date')");
                        }
                        if($sqlInsertCompoundName === true)
                        {
                            // Get Last Id in table Region.
                            $comID = $this->conn->query("SELECT ID FROM Compound ORDER BY ID DESC LIMIT 1");
                            $NewID = $comID->fetch_row();
                            $Action4 = "Insert New Compound";
                            $sqlCompLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date, CreatedAt)
                                                            VALUES ('$UserID', '$APTID', '$BLKID', 3, '$Action4', '$NewID[0]', 'Compound', '$date', '$date')");
                        }
                        if($sqlInsertRigionName === true)
                        {
                            // Get Last Id in table Region.
                            $REGID = $this->conn->query("SELECT ID FROM Region ORDER BY ID DESC LIMIT 1");
                            $NewID = $REGID->fetch_row();
                            $Action5 = "Insert New Region";
                            $sqlRegLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date, CreatedAt)
                                                            VALUES ('$UserID', '$APTID', '$BLKID', 3, '$Action5', '$NewID[0]', 'Region', '$date', '$date')");
                        }
                        if($sqlInsertCityName === true)
                        {
                            // Get Last Id in table City.
                            $CITID = $this->conn->query("SELECT ID FROM City ORDER BY ID DESC LIMIT 1");
                            $NewID = $CITID->fetch_row();
                            $Action6 = "Insert New City";
                            $sqlCityLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date, CreatedAt)
                                                            VALUES ('$UserID', '$APTID', '$BLKID', 3, '$Action6', '$NewID[0]', 'City', '$date', '$date')");
                        }
                        if($sqlInsertGovName === true)
                        {
                            // Get Last Id in table Governate.
                            $govID = $this->conn->query("SELECT ID FROM Governate ORDER BY ID DESC LIMIT 1");
                            $NewID = $govID->fetch_row();
                            $Action7 = "Insert New Governate";
                            $sqlGovLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date, CreatedAt)
                                                            VALUES ('$UserID', '$APTID', '$BLKID', 3, '$Action7', '$NewID[0]', 'Governate', '$date', '$date')");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Data Base Error. $this->conn->error");
                    }
                }
        }
    }

    public function CreateApartment() // OK Final
    {   
        // include("../Config.php");
        date_default_timezone_set('Africa/Cairo');

        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $FloorNum = $_POST["floorNum"];
        $AptNum = $_POST["aptNum"];
        
        try
        {
            $token = $this->getBearerToken();
            $secret = "secret123";
            $decode = JWT::decode($token, new Key($secret, 'HS256'));
        }catch( Exception $e )
        {
            $this->throwError(401, $e->getMessage());
        }
        $UserID = $decode->id;

        if(empty($FloorNum))
        {
            $this->throwError(200, "Please enter floorNum.");
        }
        if(empty($AptNum))
        {
            $this->throwError(200, "Please enter aptNum.");
        }
        if(empty($APTID))
        {
            $this->throwError(200, "Please enter apartmentId.");
        }
        if(empty($BLKID))
        {
            $this->throwError(200, "Please enter blockId.");
        }
        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        // Check User Role.
        $sqlGetRole = $this->conn->query("SELECT RoleID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = $UserID AND BlockID='$BLKID' AND ApartmentID='$APTID'");
        $UserRole = $sqlGetRole->fetch_row();
        if($UserRole[0] !== '1')
        {
            $this->throwError(401, "User doesn't have permissions due to his role in block.");
        }
        elseif($UserRole[0] == '1')
        {
            if(!empty($FloorNum) && !empty($AptNum))
            {
                if(empty($BLKID))
                {
                    $this->throwError(200, "Please enter block id.");
                }
                elseif(!empty($BLKID))
                {
                    // Check Block Existence.
                    $sqlGetBlkID = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID';");
                    if($sqlGetBlkID->num_rows > 0)
                    {
                        // Check Resident relation to Block.
                        $sqlGetBLKMNGID = $this->conn->query("SELECT ResidentID, RoleID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = $UserID AND BlockID='$BLKID' AND ApartmentID='$APTID'");
                        if($sqlGetBLKMNGID->num_rows > 0)
                        {
                            // Check user Identity and role in this block
                            $BlkMngData = $sqlGetBLKMNGID->fetch_row();
                            if($BlkMngData[0] == $UserID && $BlkMngData[1] == '1')
                            {
                                // Check apartment existence.
                                $sqlGetAptID = $this->conn->query("SELECT ID FROM Apartment WHERE ApartmentNumber='$AptNum' AND BlockID='$BLKID';");
                                if( $sqlGetAptID->num_rows > 0 )
                                {
                                    $this->throwError(200, "Apartment already registered.");
                                }
                                    
                                elseif( $sqlGetAptID->num_rows <= 0 )
                                {
                                    $date = date("Y-m-d h-i-sa");
                                    $sqlInsertApt = $this->conn->query("INSERT INTO Apartment SET FloorNum='$FloorNum', ApartmentNumber='$AptNum', BlockID='$BLKID', CreatedAt='$date'");
                                    if($sqlInsertApt)
                                    {
                                        $Action = "Create New Apartment";
                                        // Log insert Create new Apartment.
                                        $aptData = $this->conn->query("SELECT ID FROM Apartment ORDER BY ID DESC LIMIT 1");
                                        $newId = $aptData->fetch_row();
                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                VALUES ('$UserID', '$APTID', '$BLKID', 6, '$Action', '$newId[0]', 'Apartment', '$Longitude', '$Latitude', '$date', '$date')");
                                        // Send OK response.
                                        $this->returnResponse(200, "Apartment registered.");
                                    }
                                    else
                                    {
                                            $this->throwError(304, "Apartment not registered, please try again.");
                                    }
                                }
                            }
                            else
                            {
                                $this->throwError(401, "Resident Not permitted to perform this action.");
                            }
                        }
                        elseif($sqlGetBLKMNGID->num_rows <= 0)
                        {
                            $this->throwError(200, "Block Manager not found.");
                        }
                    }
                    elseif($sqlGetBlkID->num_rows <= 0)
                    {
                        $this->throwError(200, "Block not found.");
                    }
                }       
            }
            else
            {
                $this->throwError(200,"Please enter floor number and apartment number");
            }
        }
    }

    public function requestMeeting() //OK Final
    {
        // include("../Config.php");
        include("../Classes/Notification.php");
        $Notif = new Notification;
        date_default_timezone_set('Africa/Cairo');
        
        // Devices IDs for Notification.
        $registration_ids = [];
        
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
        $userID = $decode->id;
        $extensions = ["jpg", "jpeg", "png", "pdf"];
        //  Get Meeting Data and set aproval to 0.
        $tittle = filter_var($_POST["tittle"], FILTER_SANITIZE_STRING);
        $body = filter_var($_POST["body"], FILTER_SANITIZE_STRING);
        $Location = filter_var($_POST["meetingLocation"], FILTER_SANITIZE_STRING);
        $Decision = $_POST["decision"];
        $Attach = $_FILES["attach"];
        if(isset($Attach))
        {
            $attachments = $this->uploadFile2($userID, $Attach, $extensions);
        }
        $imageUrl = "https://kcgwebservices.net/omartyapis/Images/meetingImages/" . $attachments["newName"];
            if(!empty($attachments)) { $location = "../Images/meetingImages/". $attachments["newName"]; }
        
        $date = filter_var($_POST["date"], FILTER_SANITIZE_STRING);
        $approval = 0;
        $createdAt = date("Y-m-d H:i:sa");
        $Date = date("Y-m-d h:i:sa");

        if(empty($BLKID))
        {
            $this->throwError(200, "Please Enter blockId.");
        }
        if(empty($APTID))
        {
            $this->throwError(200, "Please Enter apartmentId.");
        }
        if(empty($tittle))
        {
            $this->throwError(200, "Please Enter tittle.");
        }
        if(empty($body))
        {
            $this->throwError(200, "Please Enter body.");
        }
        if(empty($date))
        {
            $this->throwError(200, "Please Enter date.");
        }
        if(empty($Location))
        {
            $this->throwError(200, "Please Enter meeting location.");
        }
        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        // Check Block Existence.
        $sqlCheckBlock = $this->conn->query("SELECT ID,StatusID FROM Block WHERE ID='$BLKID' ");
        if($sqlCheckBlock->num_rows > 0)
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
                // Check User relation in block.
                $sqlCheckResBlkRel = $this->conn->query("SELECT ResidentID, StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND ResidentID = '$userID'");
                if($sqlCheckResBlkRel->num_rows > 0)
                {
                    // Check Apartment Existence.
                    $sqlCheckApt = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID='$APTID' AND BlockID='$BLKID'");
                    if($sqlCheckApt->num_rows > 0)
                    {
                        $AptData = $sqlCheckApt->fetch_row();
                        // Check Resident relation to this apartment.
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
                                // Get Block Manager ID and status.
                                $sqlGetBlockManagerID = $this->conn->query("SELECT ResidentID,StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = 1");
                                if($sqlGetBlockManagerID->num_rows > 0)
                                {
                                        
                                    while($BLKMNGData = $sqlGetBlockManagerID->fetch_row())
                                    {
                                        if($BLKMNGData[1] == '1')
                                        {
                                            $this->throwError(406, "Your block manager account status is some how Pending.");
                                            exit;
                                        }
                                        elseif($BLKMNGData[1] == '3')
                                        {
                                            $this->throwError(406, "Your block manager account status is Banned.");
                                            exit;
                                        }
                                        elseif($BLKMNGData[1] == '2')
                                        {
                                                if(!empty($attachments)) { $attachName = $attachments["newName"]; }
                                                else { $attachName = "Default.jpg"; }
                                                $BLKMNGID = $BLKMNGData[0];
                                                // Set Approval to 1 not to wait manager approval as user is already the manager, if user is not block manager then approval already is set to 0 in the beginning of the method.
                                                if($BLKMNGID == $userID)
                                                {
                                                    $approval = 1;
                                                }
                                                $sqlInsertMeetData = $this->conn->query("INSERT INTO Meeting (Tittle, Body, Attachment, Date, MeetingLocation, Approval, UserID, BlockManagerID, BlockID, CreatedAt)
                                                                                        VALUES ('$tittle', '$body', '$attachName', '$date', '$Location', '$approval', '$userID', '$BLKMNGID', '$BLKID', '$createdAt')");
                                                if($approval == 1)
                                                {
                                                    // Get Meeting ID.
                                                    $sqlGetMeetingId = $this->conn->query("SELECT ID FROM Meeting ORDER BY ID DESC LIMIT 1");
                                                    $MeetingID = $sqlGetMeetingId->fetch_row();
                                                    // Get Users In The Block.
                                                    $sqlGetBlockRes = $this->conn->query("SELECT ResidentID FROM RES_APART_BLOCK_ROLE WHERE BLOCKID = '$BLKID'");
                                                    if($sqlGetBlockRes->num_rows > 0)
                                                    {
                                                        $UserIDs = $sqlGetBlockRes->fetch_row();
                                                        $Notif->MeetNoti($UserIDs, $MeetingID[0]);
                                                    }
                                                    
                                                }
                                                if($this->conn->error)
                                                {
                                                    echo $this->conn->error;
                                                    exit;
                                                }
                                                
                                                // ==================================================================================
                                                
                                                if($sqlInsertMeetData == true)
                                                {               
                                                    $Action = "Requst New Meeting from Block Manager";
                                                    $MEETID = $this->conn->query("SELECT ID FROM Meeting ORDER BY ID DESC LIMIT 1");
                                                    $newId = $MEETID->fetch_row();
    
    // ===========================================================================================Send Notification==========================================================================================
                                                    
                                                    // Get Residents Google tokens in Block.
                                                    $sqlGetResIdsInBlk = $this->conn->query("SELECT GoogleToken FROM Resident_Devices_Tokens WHERE BlockID = '$BLKID'");
                                                    if($this->conn->error)
                                                    {
                                                        echo $this->conn->error;
                                                        exit;
                                                    }
                                                    while($registration_ids = $sqlGetResIdsInBlk->fetch_row())
                                                    {
                                                        $Notif->MeetNoti($userID, $newId[0], $registration_ids);
                                                    }
                                                    
    // ===========================================================================================Send Notification==========================================================================================
                                                    if(!empty($Decision))
                                                    {
                                                        $DecArr = explode (",", $Decision);
                                                        foreach($DecArr as $Dec)
                                                        {
                                                            
                                                            $sqlInsertDec = $this->conn->query("INSERT INTO Decision (Decision, MeetingID, BlockManagerID, Date, CreatedAt) VALUES ('$Dec', '$newId[0]', '$BLKMNGData[0]', '$Date', '$createdAt')");
                                                             // Log insert Create new Decision.
                                                            $Action2 = "Create New Decision While creating meeting $newId[0]";
                                                            $DecID = $this->conn->query("SELECT ID FROM Decision ORDER BY ID DESC LIMIT 1");
                                                            $NewIdDec = $DecID->fetch_row();
                                                            $sqlDecLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                            VALUES ('$userID', '$APTID', '$BLKID', 9, '$Action2', '$NewIdDec[0]', 'Decision', '$Longitude', '$Latitude', '$Date', '$createdAt')");
                                                                                            if($this->conn->error)
                                                                                            {
                                                                                                echo $this->conn->error;
                                                                                                exit;
                                                                                            }
                                                        }
                                                    }
                                                    if(!empty($attachments)) { move_uploaded_file($attachments["tmp_name"], $location); }
                                                    
    
                                                    // Log insert Create new Meeting.
                                                    $sqlMeetLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                        VALUES ('$userID', '$APTID', '$BLKID', 7, '$Action', '$newId[0]', 'Meeting', '$Longitude', '$Latitude', '$Date', '$createdAt')");
                                                   
                                                    $this->returnResponse(200,"Meeting request has been sent.");
                                                }
                                                else
                                                {
                                                    $this->throwError(304, "Data did not inserted please check your data and retry again.");
                                                }   
                                                break;
                                            }
                                    }
                                }
                               
                                else
                                {
                                    $this->throwError(200, "Block Manager was not found.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Resident does not relate to this apartment.");
                        }
                    }
                    elseif($sqlCheckApt->num_rows <= 0)
                    {
                        $this->throwError(200, "Apartment not found in Block.");
                    }
                }
                else
                {
                    $this->throwError(406, "Resident does not relate to this block.");
                }
            }
            else
            {
                $this->throwError(406, "Block status is not acceptable.");
            }
        }
        else
        {
            $this->throwError(200, "Block not found.");
        }
    }

    public function createEvent() //OK Final
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
        $Location = $_POST["eventLocation"];
        $userID = $decode->id;
        $Attach = $_FILES["attach"];

        // $email = $decode->email;
        
        // get Event data
        $tittle = filter_var($_POST["tittle"], FILTER_SANITIZE_STRING);
        $body = filter_var($_POST["body"], FILTER_SANITIZE_STRING);
        if(!empty($Attach))
        {
            $image = $this->uploadFile2($userID, $Attach, $extensions);
        }
        if(!empty($image)) { $location = "../Images/eventImages/". $image["newName"]; }
            
        $date = $_POST["date"];
        $CurrentDate = date("Y-m-d h-i-sa");
        
        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        // add event data.
        if(empty($Location))
        {
            $this->throwError(200, "Please enter Event location.");
        }
        if(empty($tittle))
        {
            $this->throwError(200, "Please enter Event tittle.");
        }
        else
        {
            if(empty($body))
            {
                $this->throwError(200, "Please enter Event Body.");
            }
            else
            {
                if(empty($date))
                {
                    $this->throwError(200, "Please enter event date.");
                }
                else
                {
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
                                            // Get Block Manager ID and status.
                                            $sqlGetBlockManagerID = $this->conn->query("SELECT ResidentID,StatusID FROM RES_APART_BLOCK_ROLE WHERE BlockID = '$BLKID' AND RoleID = 1");
                                            if($sqlGetBlockManagerID->num_rows > 0)
                                            {
                                                while($BLKMNGData = $sqlGetBlockManagerID->fetch_row())
                                                {
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
                                                        if(!empty($image)) { $newImage = $image["newName"]; }
                                                        else { $newImage = "Default.jpg"; }
                                                        $sqlAddData = $this->conn->query("INSERT INTO Event (Tittle, Body, Image, Date, EventLocation, UserID, ApartmentID, BlockID, CreatedAt) 
                                                                            VALUES ('$tittle', '$body', '$newImage', '$date', '$Location', '$userID', '$APTID', '$BLKID', '$CurrentDate')");
                                                        if($sqlAddData)
                                                        {
                                                            if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                                    
                                                            $Action = "Create New Event";
                                                            // Log insert Create new Meeting.
                                                            $EVTID = $this->conn->query("SELECT ID FROM Event ORDER BY ID DESC LIMIT 1");
                                                            $newId = $EVTID->fetch_row();
                                                            $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                VALUES ('$userID', '$APTID', '$BLKID', 8, '$Action', '$newId[0]', 'Event', '$Longitude', '$Latitude', '$CurrentDate', '$CurrentDate')");
                                                                                if($this->conn->error)
                                                                                {
                                                                                    echo $this->conn->error;
                                                                                }

                                                            $this->returnResponse(200, "Record Inserted.");
    // ===========================================================================================Send Notification==========================================================================================
                                                    
                                                            // Get Residents Google tokens in Block.
                                                            $sqlGetResIdsInBlk = $this->conn->query("SELECT GoogleToken FROM Resident_Devices_Tokens WHERE BlockID = '$BLKID'");
                                                            while($registration_ids = $sqlGetResIdsInBlk->fetch_row())
                                                            {
                                                                $Notif->EventNoti($userID, $newId[0], $registration_ids);
                                                            }
                                                            
    // ===========================================================================================Send Notification==========================================================================================
                                                        }
                                                        else
                                                        {
                                                            $this->throwError(304, "Record was not inserted, Please try again.");
                                                        }
                                                        break;
                                                    }
                                                    else
                                                    {
                                                        $this->throwError(406, "Block Manager status is un defined.");
                                                    }    
                                                }
                                                
                                            }
                                            else
                                            {
                                                $this->throwError(200, "Block Manager was not found.");
                                            }
                                        }
                                        else
                                        {
                                            $this->throwError(406, "Sorry Apartment status is ot acceptable.");
                                        }
                                    }
                                    else
                                    {
                                        $this->throwError(406, "Resident does not relate to this apartment.");
                                    }
                                }
                                else
                                {
                                    $this->throwError(200, "Apartment not found in Block.");
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
                        $this->throwError(200, "Block not found.");
                    }
                }
                
            }
           
        }
       
    }

    public function createDecision() // Already exist in requesting meetings.
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

        // Check User Role
          
        $BLKID = $_POST["blockId"];
        $APTID = $_POST["apartmentId"];
        $userID = $decode->id;
        
        $Longitude = 0; 
        $Latitude = 0;

        if(empty($BLKID))
        {
            $this->throwError(200, "Please enter block Id.");
            exit;
        }
        if(empty($APTID))
        {
            $this->throwError(200, "Please enter apartment Id.");
            exit;
        }
        // $userRole = $decode->apartmentsAndBlocks->record1->role;
        $sqlCheckRole = $this->conn->query("SELECT ApartmentID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = $userID AND BlockID = '$BLKID' RoleID = '1'");
        
        if($sqlCheckRole->num_rows > 0 )
        {
            $ApartmentId = $sqlCheckRole->fetch_row();
            $apt = $ApartmentId[0];
                // get decision data
                $cause = filter_var($_POST["cause"], FILTER_SANITIZE_STRING);
                if(empty($cause))
                {
                    $this->throwError(200, "Please enter cause.");
                }
                else
                {
                    $decision = filter_var($_POST["decision"], FILTER_SANITIZE_STRING);
                    if(empty($decision))
                    {
                        $this->throwError(200, "Please enter decision.");
                    }
                    else
                    {
                        $date = $_POST["date"];
                        if(empty($date))
                        {
                            $this->throwError(200, "Please enter date.");
                        }
                        else
                        {
                            // $apartmentID = $decode->apartmentsAndBlocks->record1->apartment;
                            // $blockID = $decode->apartmentsAndBlocks->record1->block;
                            $CurrentDate = date("Y-m-d h-i-sa");
                            // add event data.
                            
                            // ===================================================================================================================================================================================================
                           
                            // ===================================================================================================================================================================================================
                            $sqlAddData = $this->conn->query("INSERT INTO Decision (Cause, Decision, BlockManagerID, BlockID, Date, CreatedAt) 
                                                                        VALUES ('$cause', '$decision', '$userID', '$BLKID', '$date', '$CurrentDate')");
                            if($sqlAddData)
                            {
                                // Log insert Create new Apartment.
                                $Action = "Create New Decision";
                                $NEWID = $this->conn->query("SELECT ID FROM News ORDER BY ID DESC LIMIT 1");
                                $newId = $NEWID->fetch_row();
                                $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                    VALUES ('$userID', '$APTID', '$BLKID', 9, '$Action', '$newId[0]', 'Decision', '$Longitude', '$Latitude', '$CurrentDate', '$CurrentDate')");
                                                                    if($this->conn->error)
                                                                    {
                                                                        echo $this->conn->error;
                                                                        exit;
                                                                    }
        
                                $this->returnResponse(200, "Record Inserted.");
                            }
                            else
                            {
                                $this->throwError(304, "Record was not inserted, Please try again.");
                            }
                        }  
                    }   
                }
            
        }
        else
        {
            $this->throwError(401, "User have Role Permissions due to his role in the block.");
        }
        
        
    }

    public function createNews() // OK Final
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        $extensions = ["jpg", "jpeg", "png", "pdf"];

        // $email = $decode->email;
        // get Decision data
        $tittle = filter_var($_POST["tittle"], FILTER_SANITIZE_STRING);
        $body = filter_var($_POST["body"], FILTER_SANITIZE_STRING);
        $Attach = $_POST["attach"];
        if(!empty($Attach))
        {
            $image = $this->uploadFile2($userID, $Attach, $extensions);
        }
         if(!empty($image)) { $location = "../Images/newsImages/". $image["newName"]; }
        
        $CurrentDate = date("Y-m-d H:i:sa");
        $Date = date("Y-m-d h:i:sa");
        // add event data.
        if(empty($BLKID))
        {
            $this->throwError(200, "Please enter blockId.");
        }
        else
        {
            // check Block existence.
            $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
            if($sqlCheckBlock->num_rows > 0)
            {
                // check Block Status.
                $blockData = $sqlCheckBlock->fetch_row();
                if($blockData[1] == '1')
                {
                    $this->throwError(406, "Block status is binding.");
                }
                if($blockData[1] == '3')
                {
                    $this->throwError(406, "Block is Banned.");
                }
                if($blockData[1] == '2')
                {
                    if(empty($APTID))
                    {
                        $this->throwError(200, "Please enter apartmentId.");
                    }
                    else
                    {
                        // Check Apartment existence.
                        $sqlCheckApt = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                        if($sqlCheckApt->num_rows > 0)
                        {
                            $aptData = $sqlCheckApt->fetch_row();
                            // Check Resident relation to the apartment.
                            if($aptData[2] == $userID)
                            {
                                // check Apartment Status.
                                if($aptData[1] == '1')
                                {
                                    $this->throwError(406, "Apartment status is binding.");
                                }
                                if($aptData[1] == '3')
                                {
                                    $this->throwError(406, "Apartment is Banned.");
                                }
                                if($blockData[1] == '2')
                                {
                                    if(empty($tittle))
                                    {
                                        $this->throwError(200, "Please enter tittle.");
                                    }
                                    else
                                    {
                                        if(empty($body))
                                        {
                                            $this->throwError(200, "Please enter Body.");
                                        }
                                        else
                                        {
                                            if(!empty($image)) { $newImage = $image["newName"]; }
                                            else { $newImage = "Default.jpg"; }
                                        
                                            $sqlAddData = $this->conn->query("INSERT INTO News (Tittle, LetterOfNews, Image, Date, ResidentID, ApartmentID, BlockID, CreatedAt) 
                                                                VALUES ('$tittle', '$body', '$newImage', '$Date', '$userID', '$APTID', '$BLKID', '$CurrentDate')");
                                            if($sqlAddData)
                                            {
                                                if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                                                
                                                $Action = "Publish News On block";
                                                // Log insert Create new News.
                                                $NEWID = $this->conn->query("SELECT ID FROM News ORDER BY ID DESC LIMIT 1");
                                                $newId = $NEWID->fetch_row();
                                                $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                                        VALUES ('$userID', '$APTID', '$BLKID', 10, '$Action', '$newId[0]', 'News', '$Longitude', '$Latitude', '$CurrentDate', '$CurrentDate')");
    
                                                $this->returnResponse(200, "Record Inserted.");
                                            }
                                            else
                                            {
                                                $this->throwError(200, "Record was not inserted, Please try again.");
                                            }
                                        }
                                   
                                    }
                                }
                                else
                                {
                                    $this->throwError(406, "Apartment status is not acceptable.");            
                                }
                            }
                            else
                            {
                                $this->throwError(200, "Resident does not relate to this apartment.");
                            }
                        }
                        else
                        {
                            $this->throwError(200, "Apartment not found.");
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
                $this->throwError(200, "Block not found.");
            }
        }
       
    }

    public function createOffer() // OK Final
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
        $userID = $decode->id;

        
        $extensions = ["jpg", "jpeg", "png", "pdf"];

        // $email = $decode->email;
        // get Offer data
        $tittle = filter_var($_POST["tittle"], FILTER_SANITIZE_STRING);
        $body = filter_var($_POST["body"], FILTER_SANITIZE_STRING);
        $owner = filter_var($_POST["owner"], FILTER_SANITIZE_STRING);
        if(empty($owner)) { $owner = $decode->userName; }
        $startDate = filter_var($_POST["startDate"], FILTER_SANITIZE_STRING);
        $endDate = filter_var($_POST["endDate"], FILTER_SANITIZE_STRING);
        $Attach = $_POST["attach"];
        if(!empty($Attach))
        {
            $image = $this->uploadFile2($userID, $Attach, $extensions);
        }
        // Check User entered attachment image and set it's name to a specified location.
        if(!empty($image)) { $location = "../Images/AdsAndOffers/". $image["newName"]; }

        $CurrentDate = date("Y-m-d h-i-sa");

        // Check Offer data entered.
        if(empty($tittle))
        {
            $this->throwError(200, "Please enter Offer tittle.");
        }
        else
        {
            if(empty($body))
            {
                $this->throwError(200, "Please enter Offer Body.");
            }
            else
            {
                // Check User entered image and assign its name to new variable and if he didn't enter it then assign null to the new variable.
                if(!empty($image)) { $imageName = $image["newName"]; }
                else { $imageName = NULL; }
                // Insert Offers and Ads data.
                    $sqlAddData = $this->conn->query("INSERT INTO AdsAndOffers (Tittle, Body, Owner, Image, startDate, endDate, UserID, CreatedAt) 
                                        VALUES ('$tittle', '$body', '$owner', '$imageName', '$startDate', '$endDate', '$userID', '$CurrentDate')");
                    if($sqlAddData)
                    {
                        if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }

                        $Action = "Create Offer or Ads";
                        // Log insert Create new Apartment.
                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, Date) VALUES ('$userID', '$APTID', '$BLKID', 11, '$Action', '$CurrentDate')");

                        $this->returnResponse(200, "Record Inserted.");
                        
                    }
                    else
                    {
                        $this->throwError(304, "Record was not inserted, Please try again.");
                    }
            }
           
        }
       
    }

    public function addService() //OK Final
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
        $userID = $decode->id;

        $extensions = ["jpg", "jpeg", "png", "pdf"];

        // get Service data
        $name = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
        $describtion = filter_var($_POST["description"], FILTER_SANITIZE_STRING);
        $PNI = filter_var($_POST["PNI"], FILTER_SANITIZE_NUMBER_INT);
        $PNII = filter_var($_POST["PNII"], FILTER_SANITIZE_NUMBER_INT);
        $PNIII = filter_var($_POST["PNIII"], FILTER_SANITIZE_NUMBER_INT);
        $PNIV = filter_var($_POST["PNIV"], FILTER_SANITIZE_NUMBER_INT);
        $Attach = $_POST["attach"];
        if(!empty($Attach))
        {
            $image = $this->uploadFile2($userID, $Attach, $extensions);
        }
        $rate = filter_var($_POST["rate"], FILTER_SANITIZE_NUMBER_INT);
        $categoryID = filter_var($_POST["categoryId"], FILTER_SANITIZE_NUMBER_INT);
        $latitude = filter_var($_POST["latitude"], FILTER_SANITIZE_NUMBER_INT);
        $longitude = filter_var($_POST["longitude"], FILTER_SANITIZE_NUMBER_INT);
        $countryID = filter_var($_POST["countryID"], FILTER_SANITIZE_NUMBER_INT);
        $governateID = filter_var($_POST["governateID"], FILTER_SANITIZE_NUMBER_INT);
        $cityID = filter_var($_POST["cityID"], FILTER_SANITIZE_NUMBER_INT);
        $regionID = filter_var($_POST["regionID"], FILTER_SANITIZE_NUMBER_INT);
        $compoundID = filter_var($_POST["compoundID"], FILTER_SANITIZE_NUMBER_INT);
        $streetID = filter_var($_POST["streetID"], FILTER_SANITIZE_NUMBER_INT);

        // =============================================Check user entered all required data=============================================

        if(empty($name)) { $this->throwError(200, "Please enter service name."); }
        if(empty($describtion)) { $this->throwError(200, "Please enter service description."); }
        // PHONE NUMBER NOT UNIQUE PUT REQUIRED.
        if(empty($PNI)) { $this->throwError(200, "Please enter service Phone Number."); }
        if(empty($PNII)) { $PNII = ""; }
        if(empty($PNII)) { $PNIII = ""; }
        if(empty($PNII)) { $PNIV = ""; }
        if(empty($categoryID)) { $this->throwError(200, "Please enter service category ID."); }
        if($categoryID == -1)
        {
            $categoryName = filter_var($_POST["categoryName"], FILTER_SANITIZE_STRING);
            if(!empty($GovernateName))
            {   // Check if New inserted category existes in db.
                $sqlCheckCat = $this->conn->query("SELECT ID FROM ServiceCategory WHERE Name = '$categoryName'");
                if($sqlCheckCat->num_rows > 0)
                {
                    $catid = $sqlCheckCat->fetch_row();
                    $categoryID = $catid[0];
                    // $this->throwError(205, "Category name already exists.");
                }
                else
                {
                    // insert New Category Name to database.
                    $sqlInsertGovName = $this->conn->query("INSERT INTO ServiceCategory (Name) VALUES ('$categoryName')");
                    // get last inserted Category id.
                    $CatId = $this->conn->insert_id;
                    $categoryID = $CatId;
                }  
            }
            elseif(empty($categoryName))
            {
                $this->throwError(200, "Please enter Category name.");
            }
        }
        if(empty($longitude))
        {
            $longitude = 0;
        }
        if(empty($latitude))
        {
            $latitude = 0;
        }
        // =============================================Check user entered required Location data and enter new data if not exist=============================================
        
                if(empty($countryID))
                {
                    $countryID = 1;
                    $this->throwError(200, "Please enter Country ID");
                }
                if(empty($governateID))
                {
                    $governateID = 1;
                    $this->throwError(200, "Please enter GovernateID ID");
                }
                if($governateID == -1)
                {
                        $GovernateName = filter_var($_POST["governateName"], FILTER_SANITIZE_STRING);
                        if(!empty($GovernateName))
                        {   // Check if New inserted governate existes in db.
                            $sqlCheckGov = $this->conn->query("SELECT ID FROM Governate WHERE GOVName = '$GovernateName' AND CountryID = '$countryID'");
                            if($sqlCheckGov->num_rows > 0)
                            {
                                $govid = $sqlCheckGov->fetch_row();
                                $governateID = $govid[0];
                                
                                // $this->throwError(205, "Governate name already exists.");
                            }
                            else
                            {
                                 // insert New Governate Name to database.
                                $sqlInsertGovName = $this->conn->query("INSERT INTO Governate (GovName, CountryID) VALUES ('$GovernateName', '$countryID')");
                                // Insert to Logs table
                                // $sqlBlockLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, Date) VALUES ('$UserID', '$APTID', '$BLKID', 2, '$Action1', '$date')");
                                if($this->conn->error)
                                {
                                    echo "GOV : " . $this->conn->error;
                                }
                                // get last inserted Governate id.
                                $GovId = $this->conn->insert_id;
                                $governateID = $GovId;    
                            }
                        }
                        elseif(empty($GovernateName))
                        {
                            $this->throwError(200, "Please enter Governate name.");
                        }
                }
                if(empty($cityID))
                {
                    $cityID = 1;
                    $this->throwError(200, "Please enter City ID");
                }
                if($cityID == -1)
                {
                        $CityName = filter_var($_POST["cityName"], FILTER_SANITIZE_STRING);
                        if(!empty($CityName))
                        {   // Check if New inserted city existes in db.
                            $sqlCheckCity = $this->conn->query("SELECT ID FROM City WHERE Name = '$CityName' AND CountryID = '$countryID' AND GovID = '$governateID'");
                            if($sqlCheckCity->num_rows > 0)
                            {
                                $cityid = $sqlCheckCity->fetch_row();
                                $cityID = $cityid[0];
                                // $this->throwError(205, "City name already exists.");
                            }
                            else
                            {
                                // insert New City Name to database.
                                $sqlInsertCityName = $this->conn->query("INSERT INTO City (Name, CountryID, GovID) VALUES ('$CityName', '$countryID', '$governateID')");
                                // get last inserted City id.
                                $cityId = $this->conn->insert_id;
                                $cityID = $cityId;
                            }
                        }
                        elseif(empty($CityName))
                        {
                            $this->throwError(200, "Please enter City name.");
                        }

                    
                }
                if(empty($regionID))
                {
                    $regionID = 1; 
                    // $this->throwError(101, "Please enter Region ID");
                }
                if($regionID == -1)
                {
                    $RegionName = filter_var($_POST["regionName"], FILTER_SANITIZE_STRING);
                    if(!empty($RegionName))
                    {
                        // Check if New inserted Region existes in db.
                        $sqlCheckRegion = $this->conn->query("SELECT ID FROM Region WHERE RegionName = '$RegionName' AND CountryID = '$countryID' AND GovID = '$governateID' AND CityID = '$cityID'");
                        if($sqlCheckRegion->num_rows > 0)
                        {
                            $regid = $sqlCheckRegion->fetch_row();
                            $regionID = $regid[0];
                            // $this->throwError(200, "Region name already exists.");
                        }
                        else
                        {
                            // insert New Region Name to database.
                            $sqlInsertRigionName = $this->conn->query("INSERT INTO Region (RegionName, CountryID, GovID, CityID) VALUES ('$RegionName', '$countryID', '$governateID', '$cityID')");
                            // get last inserted Region id.
                            $regionId = $this->conn->insert_id;
                            $regionID = $regionId;
                        }
                    }
                    elseif(empty($RegionName))
                    {
                        $this->throwError(200, "Please enter Region name.");
                    }
                }
                if(empty($compoundID))
                {
                    $compoundID = 1;
                    // $this->throwError(200, "Please enter Compound ID");
                }
                if($compoundID == -1)
                {
                    $CompoundName = filter_var($_POST["compoundName"], FILTER_SANITIZE_STRING);
                    if(!empty($CompoundName))
                    {
                        // Check if New inserted Compound existes in db.
                        $sqlCheckCompound = $this->conn->query("SELECT ID FROM Compound WHERE CompundName = '$CompoundName' AND CountryID = '$countryID' AND GovID = '$governateID' AND CityID = '$cityID' AND RegionID = '$regionID'");
                        if($sqlCheckCompound->num_rows > 0)
                        {
                            $compid = $sqlCheckCompound->fetch_row();
                            $compoundID = $compid[0];
                            // $this->throwError(200, "Compound name already exists.");
                        }
                        else
                        {
                            // insert New Compound Name to database.
                            $sqlInsertCompoundName = $this->conn->query("INSERT INTO Compound (CompundName, CountryID, GovID, CityID, RegionID) VALUES ('$CompoundName', '$countryID', '$governateID', '$cityID', '$regionID')");
                            // get last inserted Compound id.
                            $compId = $this->conn->insert_id;
                            $compoundID = $compId;
                        }
                    }
                    elseif(empty($CompoundName))
                    {
                        $this->throwError(200, "Please enter Compound name.");
                    }
                }
                if(empty($streetID))
                {
                    $streetID = 1;
                    $this->throwError(200, "Please enter Street ID");

                }
                if($streetID == -1)
                {
                    $StreetName = filter_var($_POST["streetName"], FILTER_SANITIZE_STRING);
                      if(!empty($StreetName))
                    {
                        // Check if New inserted Street existes in db.
                        $sqlCheckStreet = $this->conn->query("SELECT ID FROM Street WHERE StreetName = '$StreetName' AND CountryID = '$countryID' AND GovID = '$governateID' AND CityID = '$cityID' AND RegionID = '$regionID' AND CompundID = '$compoundID'");
                        if($sqlCheckStreet->num_rows > 0)
                        {
                            $streetid = $sqlCheckStreet->fetch_row();
                            $streetID = $streetid[0];
                            // $this->throwError(200, "Street name already exists.");
                        }
                        else
                        {
                            // insert New Street Name to database.
                            $sqlInsertStreetName = $this->conn->query("INSERT INTO Street (StreetName, CountryID, GovID, CityID, RegionID, CompundID) VALUES ('$StreetName', '$countryID', '$governateID', '$cityID', '$regionID', '$compoundID')");
                            // get last inserted Street id.
                            $streetId = $this->conn->insert_id;
                            $streetID = $streetId;
                        }
                    }
                    elseif(empty($StreetName))
                    {
                        $this->throwError(200, "Please enter Street name.");
                    }
                    
                }
        // ===================================================================================================================================

        // Check Block existence.
        $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
        if($sqlCheckBlock->num_rows > 0)
        {
            // Check Block status.
            $blockData = $sqlCheckBlock->fetch_row();
            if($blockData[1] == '1')
            {
                $this->throwError(406, "Block status is binding.");
            }
            if($blockData[1] == '3')
            {
                $this->throwError(406, "Block is Banned.");
            }
            if($blockData[1] == '2')
            {
                if(empty($APTID))
                {
                    $this->throwError(200, "Please enter apartmentId.");
                }
                else
                {
                    // Check Apartment existence.
                    $sqlCheckApt = $this->conn->query("SELECT ApartmentID, StatusID, ResidentID FROM RES_APART_BLOCK_ROLE WHERE ApartmentID = '$APTID' AND BlockID = '$BLKID'");
                    if($sqlCheckApt->num_rows > 0)
                    {
                        $aptData = $sqlCheckApt->fetch_row();
                        // Check Resident relation to this apartment.
                        if($aptData[2] == $userID)
                        {
                            // Check Apartment Status.
                            if($aptData[1] == '1')
                            {
                                $this->throwError(401, "Apartment status is binding.");
                            }
                            if($aptData[1] == '3')
                            {
                                $this->throwError(401, "Apartment is Banned.");
                            }
                            if($blockData[1] == '2')
                            {
                                // get current dateTime.
                                $CurrentDate = date("Y-m-d H:i:s");
                        
                                // if User entered attatchment Image assign a specified path with image's encoded name to @location.
                                if(!empty($image)) { $location = "../Images/serviceImages/". $image["newName"]; }
                        
                                if(!empty($image)) { $imageName = $image["newName"]; }
                                else { $imageName = "Default.jpg"; }
                                
                                $sqlAddData = $this->conn->query("INSERT INTO Service (Name, Description, PhoneNumI, PhoneNumII, PhoneNumIII, PhoneNumIV, Image, Rate, CategoryID, ResidentID, ApartmentID, BlockID, Latitude, Longitude, CountryID, GovernateID, CityID, RegionID, CompoundID, StreetID, CreatedAt) 
                                                                        VALUES  ('$name', '$describtion', '$PNI', '$PNII', '$PNIII', '$PNIV', '$imageName', '$rate', '$categoryID', '$userID', '$APTID', '$BLKID', '$latitude', '$longitude', '$countryID', '$governateID', '$cityID', '$regionID', '$compoundID', '$streetID', '$CurrentDate');");
                                
                                // $sqlAddData = $this->conn->query("INSERT INTO Service (Name, Description, PhoneNumI, PhoneNumII, PhoneNumIII, PhoneNumIV, Image, Rate, CategoryID, ResidentID, ApartmentID, BlockID, Latitude, Longitude, CountryID, GovernateID, CityID, RegionID, CompoundID, StreetID, CreatedAt) 
                                //                                             VALUES ('$name', '$describtion', '$PNI', '$PNII', '$PNIII', '$PNIV', '$imageName', '$rate', '$categoryID', '$userID', '$APTID', '$BLKID', '$latitude', '$longitude', '$countryID', '$governateID', '$cityID', '$regionID', '$compoundID', '$streetID', '$CurrentDate')");
                                
                                if($sqlAddData === true)
                                {
                                    // if the insertion query is true then move the file and send response record inserted.
                                    if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                                    $Action = "Add Nearby Service";
                                    // Log insert Create new Service.
                                    $SERID = $this->conn->query("SELECT ID FROM Service ORDER BY ID DESC LIMIT 1");
                                    $newId = $SERID->fetch_row();
                                    $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date, CreatedAt)
                                                                        VALUES ('$userID', '$APTID', '$BLKID', 12, '$Action','$newId[0]', 'Service', '$CurrentDate', '$CurrentDate')");
                        
                                    // IF inserted new places insert to log table.
                                    if($sqlInsertStreetName === true)
                                    {
                                        $STRID = $this->conn->query("SELECT ID FROM Street ORDER BY ID DESC LIMIT 1");
                                        $newId = $STRID->fetch_row();
                                        $Action1 = "Insert New Street";
                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date)
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 3, '$Action1', '$newId[0]', 'Street', '$CurrentDate')");
                                       if($this->conn->error)
                                       {
                                           echo "STREET : " . $this->conn->error;
                                       }
                                    }
                                    if($sqlInsertCompoundName === true)
                                    {
                                        $comID = $this->conn->query("SELECT ID FROM Compound ORDER BY ID DESC LIMIT 1");
                                        $newId = $comID->fetch_row();
                                        $Action2 = "Insert New Compound";
                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date)
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 3, '$Action2', '$newId[0]', 'Compound', '$CurrentDate')");
                                    }
                                    if($sqlInsertRigionName === true)
                                    {
                                        $REGID = $this->conn->query("SELECT ID FROM Region ORDER BY ID DESC LIMIT 1");
                                        $newId = $REGID->fetch_row();
                                        $Action3 = "Insert New Region";
                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date)
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 3, '$Action3', '$newId[0]', 'Region', '$CurrentDate')");
                                    }
                                    if($sqlInsertCityName === true)
                                    {
                                        $CITID = $this->conn->query("SELECT ID FROM City ORDER BY ID DESC LIMIT 1");
                                        $newId = $CITID->fetch_row();
                                        $Action4 = "Insert New City";
                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date)
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 3, '$Action4', '$newId[0]', 'City', '$CurrentDate')");
                                    }
                                    if($sqlInsertGovName === true)
                                    {
                                        $govID = $this->conn->query("SELECT ID FROM Governate ORDER BY ID DESC LIMIT 1");
                                        $newId = $govID->fetch_row();
                                        $Action5 = "Insert New Governate";
                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Date)
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 3, '$Action5', '$newId[0]', 'Governate', '$CurrentDate')");
                                    }
                                    
                                    $this->returnResponse(200, "Record Inserted.");
                                }
                                else
                                {
                                    // if the insertion query is false then send Error record was not inserted.
                                    $this->throwError(304, "Record was not inserted, Please try again.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Apartment status is not acceptable.");            
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Resident does not relate to this apartment.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Apartment not found.");
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
            $this->throwError(200, "Block not found.");
        }
    }

    public function addToFavorite() // OK Final
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        
        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        
        // get News data
        $favourite = filter_var($_POST["favourite"], FILTER_SANITIZE_NUMBER_INT);
        $serviceID = filter_var($_POST["serviceId"], FILTER_SANITIZE_NUMBER_INT);
        $neighbourID = filter_var($_POST["neighbourId"], FILTER_SANITIZE_NUMBER_INT);

        // get User data
        $CurrentDate = date("Y-m-d h:i:s");
        // Check resident relation to block.
        $sqlCheckREL = $this->conn->query("SELECT RoleID, StatusID FROM RES_APART_BLOCK_ROLE WHERE ResidentID = '$userID' AND BlockID = '$BLKID' AND ApartmentID = '$APTID'");
        if($sqlCheckREL->num_rows > 0)
        {   
            // Check Block existence.
            $sqlCheckBlock = $this->conn->query("SELECT ID, StatusID FROM Block WHERE ID = '$BLKID'");
            if($sqlCheckBlock->num_rows > 0)
            {
                // Check Block Status.
                $BlockData = $sqlCheckBlock->fetch_row();
                if($BlockData[1] == '2')
                {
                    // Check Apartment Status
                    $AptStat = $sqlCheckREL->fetch_row();
                    if($AptStat[1] == '2')
                    {
                        if($favourite == 1)
                        {
                            if(!empty($serviceID))
                            {
                                // Get Service Data
                                $sqlGetServiceData = $this->conn->query("SELECT ID, Name, CategoryID FROM Service WHERE ID='$serviceID'");
                                // Check query executed.
                                if($sqlGetServiceData->num_rows > 0)
                                {
                                    $serviceData = $sqlGetServiceData->fetch_row();
                                    $ID = $serviceData[0];
                                    $Name = $serviceData[1];
                                    $CategoryID = $serviceData[2];
                                    
                                    // Check if user already Favourite this service.
                                    $sqlCheckFav = $this->conn->query("SELECT ID FROM Favourite WHERE ServiceID = '$serviceID' AND CategoryID = '$CategoryID'");
                                    if($sqlCheckFav->num_rows > 0)
                                    {
                                        $FavIDArr = $sqlCheckFav->fetch_row();
                                        $FavID = $FavIDArr[0];
                                        // Remove Service From My Favorite.
                                        $sqlDropFav = $this->conn->query("DELETE FROM Favourite WHERE ID = '$FavID'");
                                    }
                                    elseif($sqlCheckFav->num_rows <= 0)
                                    {
                                        // Favourites Will appear in resident's all apartments.
                                        $sqlAddData = $this->conn->query("INSERT INTO Favourite (Name, ResidentID, ApartmentID, BlockID, CategoryID, ServiceID, CreatedAt) VALUES ('$Name', '$userID', '$APTID', '$BLKID', '$CategoryID', '$ID', '$CurrentDate')");
                                    }
                                    if($sqlAddData === true)
                                    {
                                         $Action = "Add to favourite";
                                        // Log insert Create new Favourite.
                                        $FavID = $this->conn->query("SELECT ID FROM Favourite ORDER BY ID DESC LIMIT 1");
                                        $newId = $FavID->fetch_row();

                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 13, '$Action', '$newId[0]', 'Favourite', '$Longitude', '$latitude', '$CurrentDate', '$CurrentDate')");

                                        // $this->returnResponse(200, "Record Inserted.");
                                        $this->GetService($newId[0]);
                                    }
                                    elseif($sqlDropFav === true)
                                    {
                                        $Action = "Remove from favourite";
                                        // Log insert Create new Favourite.
                                        $FavID = $FavID;
                                        // $newId = $FavID->fetch_row();

                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 22, '$Action', $FavID, 'Favourite', '$Longitude', '$latitude', '$CurrentDate', '$CurrentDate')");

                                        $this->returnResponse(200, "Record Removed from Favourites.");
                                    } 
                                    else
                                    {
                                        $this->throwError(200, "Record was not inserted, Please try again.");
                                        echo "OK1";
                                    } 
                                    if($this->conn->error)
                                    {
                                        echo $this->conn->error;
                                    }
                                }
                                else
                                {
                                    $this->throwError(200, "please check of Service id.");
                                }
                            }
                            if(!empty($neighbourID))
                            {
                                // Get Service Data
                                $sqlGetneighbourData = $this->conn->query("SELECT ID, Name FROM Resident_User WHERE ID = '$neighbourID'");
                                // Check query executed.
                                if($sqlGetneighbourData->num_rows > 0)
                                {
                                    $neighData = $sqlGetneighbourData->fetch_row();
                                    $ID = $neighData[0];
                                    $Name = $neighData[1];
                                    $CategoryID = $neighData[2];
                                    
                                    // Check if user already Favourite this neighbour.
                                    $sqlCheckFav = $this->conn->query("SELECT ID FROM Favourite WHERE NeighbourID = '$neighbourID' and CategoryID = 1");
                                    if($sqlCheckFav->num_rows > 0)
                                    {
                                        $FavIDArr = $sqlCheckFav->fetch_row();
                                        $FavID = $FavIDArr[0];
                                        // Remove Service From My Favorite.
                                        $sqlDropFav = $this->conn->query("DELETE FROM Favourite WHERE ID = '$FavID'");
                                    }
                                    elseif($sqlCheckFav->num_rows <= 0)
                                    {
                                        // Favourites Will appear in resident's all apartments.
                                        $sqlAddData = $this->conn->query("INSERT INTO Favourite (Name, ResidentID, ApartmentID, BlockID, CategoryID, NeighbourID, CreatedAt) 
                                                            VALUES ('$Name', '$userID', '$APTID', '$BLKID', '1', '$ID', '$CurrentDate')");
                                    }
                                    if($sqlAddData === true)
                                    {
                                         $Action = "Add to favourite";
                                        // Log insert Create new Favourite.
                                        $FavID = $this->conn->query("SELECT ID FROM Favourite ORDER BY ID DESC LIMIT 1");
                                        $newId = $FavID->fetch_row();

                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 13, '$Action', '$newId[0]', 'Favourite', '$Longitude', '$latitude', '$CurrentDate', '$CurrentDate')");

                                        // $this->returnResponse(200, "Record Inserted.");
                                        $this->GetNeighbour($newId[0]);
                                        
                                    }
                                    elseif($sqlDropFav === true)
                                    {
                                        $Action = "Remove from favourite";
                                        // Log insert Create new Favourite.
                                        $FavID = $FavID;
                                        // $newId = $FavID->fetch_row();

                                        $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 22, '$Action', $FavID, 'Favourite', '$Longitude', '$latitude', '$CurrentDate', '$CurrentDate')");

                                        $this->returnResponse(200, "Record Removed from Favourites.");
                                    } 
                                    else
                                    {
                                        $this->throwError(200, "Record was not inserted, Please try again.");
                                    }
                                }
                                else
                                {
                                    $this->throwError(200, "please check of Neibhbour id.");
                                }
                            }
                            elseif(empty($serviceID) && empty($neighbourID))
                            {
                                $this->throwError(200, "Please Choose Service or Neighbour.");
                            }
                        }
                        else
                        {
                            exit;   
                        }
                    }
                    elseif($AptStat[1] == '1')
                    {
                        $this->throwError(401, "Apartment Status is Binding.");
                    }
                    elseif($AptStat[1] == '3')
                    {
                        $this->throwError(401, "Apartment is Banned.");
                    }
                    else
                    {
                        $this->throwError(401, "Apartment status is not acceptable.");
                    }
                    
                }
                elseif($BlockData[1] == '1')
                {
                    $this->throwError(401, "Block Status is Binding.");
                }
                elseif($BlockData[1] == '3')
                {
                    $this->throwError(401, "Block is Banned.");
                }
                else
                {
                    $this->throwError(401, "Block status is not acceptable.");
                }
            }
            elseif($sqlCheckBlock->num_rows <= 0)
            {
                $this->throwError(200, "Block Not Found.");
            }
        }
        else
        {
            $this->throwError(401, "User Does Not relate to Block.");
        }
    }

    public function createComment() //OK Final
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        $CurrentDate = date("Y-m-d h-i-sa");
        // get Comment data
        $Comment = filter_var($_POST["comment"], FILTER_SANITIZE_STRING);
        $PostID = filter_var($_POST["postId"], FILTER_SANITIZE_STRING);
        $PostTable = filter_var($_POST["pastTable"], FILTER_SANITIZE_STRING);
        
        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        
        // add Comment data.
        if(empty($Comment))
        {
            $this->throwError(200, "Please enter Comment Text.");
        }
        if(empty($PostID))
        {
            $this->throwError(200, "Please enter Original Post ID.");
        }
        if(empty($PostTable))
        {
            $this->throwError(200, "Please enter Original Post Table.");
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
                                $sqlAddData = $this->conn->query("INSERT INTO Comment (CommentText, ResidentID, ApartmentID, BlockID, OriginalPostID, OriginalPostTable, CreatedAt) 
                                                    VALUES ('$Comment', '$userID', '$APTID', '$BLKID', '$PostID', '$PostTable', '$CurrentDate')");
                                if($sqlAddData)
                                {
                                    if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                                
                                    $Action = "Create New Comment to table $PostTable.";
                                    // Log insert Create new Meeting.
                                    $CMTID = $this->conn->query("SELECT ID FROM Comment ORDER BY ID DESC LIMIT 1");
                                    $newId = $CMTID->fetch_row();
                                    $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                        VALUES ('$userID', '$APTID', '$BLKID', 17, '$Action', '$newId[0]', 'Comment', '$Longitude', '$Latitude', '$CurrentDate', '$CurrentDate')");
                                                        if($this->conn->error)
                                                        {
                                                            echo $this->conn->error;
                                                        }
                                                    
                                    $this->returnResponse(200, "Record Inserted.");
                                }
                                else
                                {
                                    $this->throwError(304, "Record was not inserted, Please try again.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Sorry Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Resident does not relate to this apartment.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Apartment not found in Block.");
                    }
                }
                else
                {
                    $this->throwError(406, "Resident does not relate to this block.");
                }
            }
            else
            {
                $this->throwError(406, "Sorry block status is not acceptable.");
            }
        }
        else
        {
            $this->throwError(200, "Block not found.");
        }       
    }

    public function createComplaint() //OK Final
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        $CurrentDate = date("Y-m-d H-i-sa");
        $Date = date("Y-m-d h-i-sa");

        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        
        // get Complaint data
        $Complaint = filter_var($_POST["complaint"], FILTER_SANITIZE_STRING);
        // $Cause = filter_var($_POST["cause"], FILTER_SANITIZE_STRING);
        
        // add Complaint data.
        if(empty($Complaint))
        {
            $this->throwError(200, "Please enter Complaint Text.");
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
                                $sqlAddData = $this->conn->query("INSERT INTO Complaint (Complaint, ResidentID, ApartmentID, BlockID, Date, CreatedAt) 
                                                    VALUES ('$Complaint', '$userID', '$APTID', '$BLKID', '$Date', '$CurrentDate')");
                                                    if($this->conn->error)
                                                        {
                                                            echo $this->conn->error;
                                                        }
                                if($sqlAddData)
                                {
                                    if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                                
                                    $Action = "Create New Complaint.";
                                    // Log insert Create new Complaint.
                                    $CMPID = $this->conn->query("SELECT ID FROM Complaint ORDER BY ID DESC LIMIT 1");
                                    $newId = $CMPID->fetch_row();
                                    $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                        VALUES ('$userID', '$APTID', '$BLKID', 18, '$Action', '$newId[0]', 'Complaint', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                                        if($this->conn->error)
                                                        {
                                                            echo $this->conn->error;
                                                        }
                                                    
                                    $this->returnResponse(200, "Record Inserted.");
                                }
                                else
                                {
                                    $this->throwError(304, "Record was not inserted, Please try again.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Sorry Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Resident does not relate to this apartment.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Apartment not found in Block.");
                    }
                }
                else
                {
                    $this->throwError(406, "Resident does not relate to this block.");
                }
            }
            else
            {
                $this->throwError(406, "Sorry block status is not acceptable.");
            }
        }
        else
        {
            $this->throwError(200, "Block not found.");
        }       
    }

    public function createSuggestion() //OK Final
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        $CurrentDate = date("Y-m-d H-i-sa");
        $Date = date("Y-m-d h-i-sa");

        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }

        // get Complaint data
        $Suggestion = filter_var($_POST["suggestion"], FILTER_SANITIZE_STRING);
        // $Cause = filter_var($_POST["cause"], FILTER_SANITIZE_STRING);
        
        // add Complaint data.
        if(empty($Suggestion))
        {
            $this->throwError(200, "Please enter Suggestion Text.");
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
                                $sqlAddData = $this->conn->query("INSERT INTO Suggestion (Suggest, ResidentID, ApartmentID, BlockID, Date, CreatedAt) 
                                                    VALUES ('$Suggestion', '$userID', '$APTID', '$BLKID', '$Date', '$CurrentDate')");
                                                    if($this->conn->error)
                                                        {
                                                            echo $this->conn->error;
                                                        }
                                if($sqlAddData)
                                {
                                    if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                                
                                    $Action = "Create New Suggestion.";
                                    // Log insert Create new Complaint.
                                    $SGTID = $this->conn->query("SELECT ID FROM Suggestion ORDER BY ID DESC LIMIT 1");
                                    $newId = $SGTID->fetch_row();
                                    $sqlAptLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                        VALUES ('$userID', '$APTID', '$BLKID', 19, '$Action', '$newId[0]', 'Suggestion', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                                        if($this->conn->error)
                                                        {
                                                            echo $this->conn->error;
                                                        }
                                                    
                                    $this->returnResponse(200, "Record Inserted.");
                                }
                                else
                                {
                                    $this->throwError(304, "Record was not inserted, Please try again.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Sorry Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Resident does not relate to this apartment.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Apartment not found in Block.");
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
            $this->throwError(200, "Block not found.");
        }       
    }

    public function attend()
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        $CurrentDate = date("Y-m-d H-i-sa");
        $Date = date("Y-m-d h-i-sa");
        
        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        
        // get Attend data
        $Attend = $_POST["attend"];
        $AttendTable = $_POST["attendTable"];
        $RecordID = $_POST["recordId"];
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
                                if(!empty($Attend))
                                {
                                    // Check if attendee already signed to attend once before.
                                    $checkAttendee = $this->conn->query("SELECT ID, Attend, Absent FROM Attendees WHERE ResidentID = '$userID' AND TableName = '$AttendTable' AND RecordID = '$RecordID'");
                                    if($checkAttendee->num_rows <= 0)
                                    {
                                        // Insert Record in table Attendees.
                                        $sqlInsertAttendee = $this->conn->query("INSERT INTO Attendees (ResidentID, ApartmentID, BlockID, TableName, RecordID, Attend, CreatedAt) 
                                                                                            VALUES ('$userID', '$APTID', '$BLKID', '$AttendTable', '$RecordID', 1, '$CurrentDate')");
                                                                 if($this->conn->error)
                                                                {
                                                                    echo $this->conn->error;
                                                                    exit;
                                                                }                           
                                        // Update Original record numOfAttendees.
                                        $sqlAddData = $this->conn->query("UPDATE $AttendTable SET NumOfAttendees = NumOfAttendees+1 WHERE ID = '$RecordID'");
                                                            if($this->conn->error)
                                                                {
                                                                    echo $this->conn->error;
                                                                }
                                    }
                                    elseif($checkAttendee->num_rows > 0)
                                    {
                                        
                                        $AttendData = $checkAttendee->fetch_row();
                                        if($AttendData[1] == 1)
                                        {
                                            // echo "OK1";
                                            // exit;
                                            $sqlUpdateAttendee = $this->conn->query("UPDATE Attendees SET Attend = 0 , Absent = 1, UpdatedAt = '$CurrentDate', UpdatedBy = '$userID' WHERE ID = '$AttendData[0]'");
                                                                     if($this->conn->error)
                                                                    {
                                                                        echo $this->conn->error;
                                                                        exit;
                                                                    }
                                            // Update Original record numOfAttendees.
                                            $sqlAddData = $this->conn->query("UPDATE $AttendTable SET NumOfAttendees = NumOfAttendees-1 WHERE ID = '$RecordID'");
                                        }
                                        if($AttendData[2] == 1)
                                        {
                                            // echo "OK2";
                                            // exit;
                                            $sqlUpdateAttendee = $this->conn->query("UPDATE Attendees SET Attend = 1 , Absent = 0, UpdatedAt = '$CurrentDate', UpdatedBy = '$userID' WHERE ID = '$AttendData[0]'");
                                                                     if($this->conn->error)
                                                                    {
                                                                        echo $this->conn->error;
                                                                        exit;
                                                                    }
                                            // Update Original record numOfAttendees.
                                            $sqlAddData = $this->conn->query("UPDATE $AttendTable SET NumOfAttendees = NumOfAttendees+1 WHERE ID = '$RecordID'");
                                        }
                                    }
                                }
                                if($sqlAddData)
                                {
                                    if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
                                
                                    $Action = "Insert New Attendee To $AttendTable ID : $RecordID.";
                                    // Log insert Create new Complaint.
                                    $ATTID = $this->conn->query("SELECT ID FROM Attendees ORDER BY ID DESC LIMIT 1");
                                    $newId = $ATTID->fetch_row();
                                    $sqlAttLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                        VALUES ('$userID', '$APTID', '$BLKID', 3, '$Action', '$newId[0]', 'Attendees', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                                        if($this->conn->error)
                                                        {
                                                            echo $this->conn->error;
                                                        }
                                                    
                                    $this->returnResponse(200, "Record Inserted.");
                                }
                                else
                                {
                                    $this->throwError(304, "Record was not inserted, Please try again.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Sorry Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Resident does not relate to this apartment.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Apartment not found in Block.");
                    }
                }
                else
                {
                    $this->throwError(406, "Resident does not relate to this block.");
                }
            }
            else
            {
                $this->throwError(406, "Sorry block status is not acceptable.");
            }
        }
        else
        {
            $this->throwError(200, "Block not found.");
        }       
    }

    public function likeDisLike()
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        $CurrentDate = date("Y-m-d H-i-sa");
        $Date = date("Y-m-d h-i-sa");

        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }

        // get Like data
        $Like = $_POST["like"];
        $DisLike = $_POST["disLike"];
        // THE TABLE IN WICH THE LIKED OR DISLIKED POST.
        $AttendTable = $_POST["tableName"];
        $RecordID = $_POST["recordId"];
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
                                // Get Likes of this user in the selected record.
                                $sqlGetLikes = $this->conn->query("SELECT * FROM Likes WHERE ResidentID = '$userID' AND ApartmentID = '$APTID' AND BlockID = '$BLKID' and TableName = '$AttendTable' AND RecordID = '$RecordID'");
                                if($sqlGetLikes->num_rows <= 0)
                                {
                                    if(!empty($Like) && empty($DisLike))
                                    {
                                        // increase likes on this post with users data.
                                        $sqlInsertLike = $this->conn->query("INSERT INTO Likes (ResidentID, ApartmentID, BlockID, TableName, RecordID, UpLike, CreatedAt)
                                                                VALUES ('$userID', '$APTID', '$BLKID', '$AttendTable', '$RecordID', 1, '$CurrentDate')");
                                        $sqlAddLikeToPost = $this->conn->query("Update $AttendTable SET Likes = Likes+1 WHERE ID = '$RecordID'");
                                    }
                                    if(empty($Like) && !empty($DisLike))
                                    {
                                        // increase likes on this post with users data.
                                        $sqlInsertLike = $this->conn->query("INSERT INTO Likes (ResidentID, ApartmentID, BlockID, TableName, RecordID, DownDisLike, CreatedAt)
                                                                VALUES ('$userID', '$APTID', '$BLKID', '$AttendTable', '$RecordID', 1, '$CurrentDate')");
                                        $sqlAddLikeToPost = $this->conn->query("Update $AttendTable SET DisLikes = DisLikes+1 WHERE ID = '$RecordID'");
                                    }
                                }
                                elseif($sqlGetLikes->num_rows > 0)
                                {
                                    $likes = $sqlGetLikes->fetch_assoc();
                                    // if User disliked this post before.
                                        /*  If user want to like post instead of disliking it
                                            if User clicked Like while he clicked DisLike before.
                                        */
                                        if(!empty($Like) && empty($DisLike))
                                        {
                                            
                                            // If User already Dislikes this post and he clicked like.
                                            if($likes["UpLike"] <= 0 && $likes["DownDisLike"] > 0)
                                            {
                                                // increase likes and decrease DisLikes on this post with users data.
                                                $sqlUpdateLike = $this->conn->query("UPDATE Likes SET UpLike = 1, DownDisLike = 0, UpdatedAt = '$CurrentDate', UpdatedBy = '$userID' WHERE
                                                                                                                                                                                    ResidentID = '$userID' 
                                                                                                                                                                                    AND ApartmentID = '$APTID' 
                                                                                                                                                                                    AND BlockID = '$BLKID' 
                                                                                                                                                                                    AND TableName = '$AttendTable' 
                                                                                                                                                                                    AND RecordID = '$RecordID'");
                                                                                                                            
                                                $sqlUpdatePostLikes = $this->conn->query("Update $AttendTable SET Likes = Likes+1, DisLikes = DisLikes-1 WHERE ID = '$RecordID'");    
                                            }
                                            // If user already likes this post do nothing.
                                            
                                        }
                                        /*  If user clicked the dislike multible times it will only flag its record with 1.
                                            if User clicked dislike while he hit like before
                                        */ 
                                        if(empty($Like) && !empty($DisLike))
                                        {
                                            
                                            // If User already Likes this post and he clicked DisLike.
                                            if($likes["UpLike"] > 0 && $likes["DownDisLike"] <= 0)
                                            {
                                                // Decrease likes and increase DisLikes on this post with users data.
                                                $sqlUpdateLike = $this->conn->query("UPDATE Likes SET Uplike = 0, DownDisLike = 1, UpdatedAt = '$CurrentDate', UpdatedBy = '$userID' WHERE 
                                                                                                                                                                                    ResidentID = '$userID' 
                                                                                                                                                                                    AND ApartmentID = '$APTID' 
                                                                                                                                                                                    AND BlockID = '$BLKID' 
                                                                                                                                                                                    AND TableName = '$AttendTable' 
                                                                                                                                                                                    AND RecordID = '$RecordID'");
                                                                                                                                
                                                $sqlUpdatePostLikes = $this->conn->query("Update $AttendTable SET Likes = Likes-1, DisLikes = DisLikes+1 WHERE ID = '$RecordID'");    
                                            }
                                            // If user already DisLikes this post do nothing.
                                        }
                                    
                                }
                                if($sqlInsertLike)
                                {
                                    $Action = "Add Like or DisLike.";
                                    // Log insert Like Or DisLike.
                                    $LIKEID = $this->conn->query("SELECT ID FROM Likes ORDER BY ID DESC LIMIT 1");
                                    $newId = $LIKEID->fetch_row();
                                    $sqlLikeLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                        VALUES ('$userID', '$APTID', '$BLKID', 3, '$Action', '$newId[0]', 'Likes', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                                        if($this->conn->error)
                                                        {
                                                            echo $this->conn->error;
                                                        }
                                    // ===================================================================================================================================================================================================
                                    if($sqlAddLikeToPost)
                                    {
                                        $Action2 = "Update Table $AttendTable at Record ID : $RecordID, to Set Likes and Dislikes.";
                                        // Log insert Like Or DisLike.
                                        $sqlOriginalTableLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                            VALUES ('$userID', '$APTID', '$BLKID', 15, '$Action2', '$RecordID', '$AttendTable', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                                            if($this->conn->error)
                                                            {
                                                                echo $this->conn->error;
                                                            }
                                        $this->returnResponse(200, "Record Inserted.");
                                    }
                                    
                                }
                                elseif($sqlUpdateLike)
                                {
                                    $Action = "Update Like or DisLike.";
                                    // Log insert Create new Complaint.
                                    $LIKEID = $this->conn->query("SELECT ID FROM Likes ORDER BY ID DESC LIMIT 1");
                                    $newId = $LIKEID->fetch_row();
                                    $sqlLikeLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                        VALUES ('$userID', '$APTID', '$BLKID', 15, '$Action', '$newId[0]', 'Likes', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                                        if($this->conn->error)
                                                        {
                                                            echo $this->conn->error;
                                                        }
                                                        
                                    if($sqlUpdatePostLikes)
                                    {
                                        $Action2 = "Update Table $AttendTable at Record ID : $RecordID, to Set Likes and Dislikes.";
                                        // Log insert Like Or DisLike.
                                        $sqlOriginalTableLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                            VALUES ('$userID', '$APTID', '$BLKID', 15, '$Action2', '$RecordID', '$AttendTable', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                                            if($this->conn->error)
                                                            {
                                                                echo $this->conn->error;
                                                            }
                                        $this->returnResponse(200, "Record Updated.");
                                    }
                                                    
                                }
                                else
                                {
                                    $this->throwError(304, "Record was not inserted, Please try again.");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Sorry Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Resident does not relate to this apartment.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Apartment not found in Block.");
                    }
                }
                else
                {
                    $this->throwError(406, "Resident does not relate to this block.");
                }
            }
            else
            {
                $this->throwError(406, "Sorry block status is not acceptable.");
            }
        }
        else
        {
            $this->throwError(200, "Block not found.");
        }       
    }

    public function rate()
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        $CurrentDate = date("Y-m-d H:i:s");
        $Date = date("Y-m-d h:i:sa");

        if(empty($Longitude))
        {
            $Longitude = 0;
        }
        if(empty($Latitude))
        {
            $Latitude = 0;
        }
        // get Rate data
        $Rate = $_POST["rate"];
        $ServiceID = $_POST["serviceId"];
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
                                // Check Data.
                                // Get Service Rate Occurrence.
                                $sqlRateOccur = $this->conn->query("SELECT ID FROM Rates WHERE ServiceID = '$ServiceID'");
                                $RateOccur = $sqlRateOccur->num_rows;
                                $sqlRateVals = $this->conn->query("SELECT ResidentRate FROM Rates WHERE ServiceID = '$ServiceID'");
                                $RateSum = 0;
                                while($RateVals = $sqlRateVals->fetch_row())
                                {
                                    // if($RateVals[0] === 0)
                                    // {
                                    //     $RateVals[0] = 1;
                                    // }
                                    $RateSum += $RateVals[0];
                                }
                                // Calculate New Rate. Rate = Sum Of Rates / Number of Rates.
                                $NewRate = $RateSum / $RateOccur;
                                
                                // Get Resident Rate Of this Service.
                                $sqlResRateVals = $this->conn->query("SELECT ID, ResidentRate FROM Rates WHERE ResidentID = '$userID' AND ServiceID = '$ServiceID'");
                                if($sqlResRateVals->num_rows <= 0)
                                {
                                    $NewRate1 = ($RateSum + $Rate) / ($RateOccur + 1);
                                    // Update User rate.
                                    $sqlInsertResRate = $this->conn->query("INSERT INTO Rates (ResidentRate, OverAllRate, ServiceID, ResidentID, ApartmentID, BlockID, CreatedAt) 
                                                                                    VALUES ('$Rate', '$NewRate1', '$ServiceID', '$userID', '$APTID', '$BLKID', '$CurrentDate')");
                                    // Update over all rate of this service in all records in Rates table.
                                    $sqlUpdateOverAllRate = $this->conn->query("UPDATE Rates SET OverAllRate = '$NewRate1' WHERE ServiceID = '$ServiceID'");
                                    // Update over all rate of this service in Service table.
                                    $sqlUpdateRate = $this->conn->query("UPDATE Service SET Rate = '$NewRate1' WHERE ID = '$ServiceID'");
                                    
                                    // $this->returnResponse(200, $NewRate1);
                                }
                                if($sqlResRateVals->num_rows > 0)
                                {
                                    
                                    $ResRate = $sqlResRateVals->fetch_row();
                                    $ResRecordInRates = $ResRate[0];
                                    //Subtract $ResRate From $NewRate. @NewRate in next line is the over all rate of the service without resident rate.
                                    $NewRate = $RateSum - $ResRate[1];
                                    // Generate New Rate with the new value of rate that user recently entered.
                                    $NewRate2 = ($NewRate + $Rate) / $RateOccur;
                                    // Update User rate.
                                    $sqlUpdateResRate = $this->conn->query("UPDATE Rates SET ResidentRate = '$Rate', OverAllRate = '$NewRate2', UpdatedAt = '$CurrentDate', UpdatedBy = $userID WHERE ServiceID = '$ServiceID' AND ResidentID = '$userID'");
                                    // Update over all rate of this service in all records in Rates table.
                                    $sqlUpdateOverAllRate = $this->conn->query("UPDATE Rates SET OverAllRate = '$NewRate2', UpdatedAt = '$CurrentDate' WHERE ServiceID = '$ServiceID' ");
                                    // Update over all rate of this service in Service table.
                                    $sqlUpdateRate = $this->conn->query("UPDATE Service SET Rate = '$NewRate2' WHERE ID = '$ServiceID'");
                                    
                                    // $this->returnResponse(200, $NewRate2);
                                }
                                // Insert Logs.
                                if($sqlUpdateRate)
                                {
                                    $Action = "Update Service Rate";
                                    // Log insert Update Service Rate.
                                    $NEWID = $this->conn->query("SELECT ID FROM News ORDER BY ID DESC LIMIT 1");
                                    $newId = $NEWID->fetch_row();
                                    $sqlServiceRateLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 15, '$Action', '$ServiceID', 'Service', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                }
                                if($sqlUpdateOverAllRate === true)
                                {
                                    $Action = "Update Service OverAllRate in all records in Rates Table.";
                                    // Log insert Update Service Rate.
                                    $NEWID = $this->conn->query("SELECT ID FROM News ORDER BY ID DESC LIMIT 1");
                                    $newId = $NEWID->fetch_row();
                                    $sqlServiceRateLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 15, '$Action', 'ALL', 'Rates', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                }
                                if($sqlUpdateResRate)
                                {
                                    $Action = "Update Resident Rate of specific Service in his record in Rates Table.";
                                    // Log insert Update Service Rate.
                                    $sqlServiceRateLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 15, '$Action', '$ResRecordInRates', 'Rates', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                }
                                if($sqlInsertResRate)
                                {
                                    $Action = "Insert New Rate of Service and change OverAllRate value with the new one in all records in Rates Table.";
                                    // Log insert Update Service Rate.
                                    $NEWID = $this->conn->query("SELECT ID FROM Rates ORDER BY ID DESC LIMIT 1");
                                    $newId = $NEWID->fetch_row();
                                    $sqlServiceRateLog = $this->conn->query("INSERT INTO Logs (UserID, ApartmentID, BlockID, LogTypeId, Action, LogRecordIdInActualTable, LogActualTable, Longitude, Latitude, Date, CreatedAt) 
                                                                            VALUES ('$userID', '$APTID', '$BLKID', 3, '$Action', '$newId[0]', 'Rates', '$Longitude', '$Latitude', '$Date', '$CurrentDate')");
                                }
                            }
                            else
                            {
                                $this->throwError(406, "Sorry Apartment status is not acceptable.");
                            }
                        }
                        else
                        {
                            $this->throwError(406, "Resident does not relate to this apartment.");
                        }
                    }
                    else
                    {
                        $this->throwError(200, "Apartment not found in Block.");
                    }
                }
                else
                {
                    $this->throwError(406, "Resident does not relate to this block.");
                }
            }
            else
            {
                $this->throwError(406, "Sorry block status is not acceptable.");
            }
        }
        else
        {
            $this->throwError(200, "Block not found.");
        }       
    }

    public function ChatAddMessage() //OK Final
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
        $Longitude = $_POST["longitude"];
        $Latitude = $_POST["latitude"];
        $userID = $decode->id;
        $CurrentDate = date("Y-m-d H-i-s");
        $Date = date("Y-m-d h-i-sa");

        $extensions = ["jpg", "jpeg", "png", "pdf"];
        $Attach = $_POST["attach"];
        if(!empty($Attach))
        {
            $image = $this->uploadFile2($userID, $Attach, $extensions);
        }
            
        if(!empty($image)) { $location = "../Images/ChatImages/". $image["newName"]; }
        // get Complaint data
        $Message = filter_var($_POST["message"], FILTER_SANITIZE_STRING);
        
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
                                $sqlInsertMessage = $this->conn->query("INSERT INTO Message (Message, Attach, SenderID, BlockID, ApartmentID, CreatedAt)
                                                        VALUES ('$Message', '$newImage', '$userID', '$BLKID', '$APTID', '$CurrentDate')");
                                // Insert Message Data into database (MessageContent / Image OR File / SenderID / Receiver / BlockID / ApartmentID / DateTime).
                                if($sqlInsertMessage)
                                {
                                    if(!empty($image)) { move_uploaded_file($image["tmp_name"], $location); }
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
    
    private function GetNeighbour($FavID)
    {
        // include("../Config.php");
        // Get Service ID.
        // $sqlGetServID = $this->conn->query("SELECT ServiceID FROM Favourite WHERE ID = '$FavID' AND NeighbourID IS NULL");
        // Get Neighbour ID.
        $sqlGetServID = $this->conn->query("SELECT NeighbourID FROM Favourite WHERE ID = $FavID");
        // Get User Phone number.
        
        if($sqlGetServID->num_rows > 0)
        {
            $NeighbourID = $sqlGetServID->fetch_row();
            $sqlGetPN = $this->conn->query("SELECT * FROM Resident_User WHERE ID = $NeighbourID[0]");
            if($sqlGetPN->num_rows > 0)
            {
                $residentPN = $sqlGetPN->fetch_row();
                $RESpn = $residentPN[5];
                $RESname = $residentPN[1];
                // get image.
                if(!empty($residentPN[6]))
                {
                    $ResidentImage = "https://kcgwebservices.net/omartyapis/Images/profilePictures/$residentPN[6]";
                }
                elseif(empty($residentPN[6]))
                {
                    $ResidentImage = "";
                }
            }
            else
            {
                $RESpn = $NeighbourID[0];
            }
            // Get Apatment Number and Floor number..
            $sqlGetAptNum = $this->conn->query("SELECT ApartmentNumber, FloorNum FROM Apartment WHERE ID = $NeighbourID[0]");
            if($sqlGetAptNum->num_rows > 0)
            {
                $AptNum = $sqlGetAptNum->fetch_row();
                $APTNUM = $AptNum[0];
                $APTFLRNUM = $AptNum[1];
            }
            else
            {
                $APTNUM = $NeighbourID[0];
                $APTFLRNUM = $NeighbourID[0];
            }
            $Arr["Record$count"] = 
            [
                "id" => $NeighbourID[0],
                "residentName" => $RESname,
                "residentImage" => $ResidentImage,
                "apartmentNumber" => $APTNUM,
                "apartmentFloorNumber" => $APTFLRNUM,
                "phoneNumber" => $RESpn,
            ];
            $count++;
            
        }
        elseif($sqlGetServID->num_rows <= 0)
            {
                $Arr = [];
            }
            $this->returnResponse(200, array_values($Arr));
    }
    private function GetService($FavID)
    {
        // include("../Config.php");
        
        // Get Service ID.
        $sqlGetServID = $this->conn->query("SELECT ServiceID FROM Favourite WHERE ID = '$FavID'");
        if($sqlGetServID->num_rows > 0)
        {
            $ServiceID = $sqlGetServID->fetch_row();
            // Get Service Data.
            $sqlGetServiceData = $this->conn->query("SELECT * FROM Service WHERE ID = $ServiceID[0]");
            if($sqlGetServiceData->num_rows > 0)
            {
                $ServiceData = $sqlGetServiceData->fetch_row();
                if(empty($ServiceData[7]))
                {
                    $attachmentURL = "";
                }
                elseif(!empty($ServiceData[7]))
                {
                    $attachmentURL = "https://kcgwebservices.net/omartyapis/Images/serviceImages/" . $ServiceData[7];
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
                    $CountryName = $sqlGetCountry->fetch_row();
                }
                elseif($sqlGetCountry->num_rows <= 0)
                {
                    $CountryName = $ServiceData[16];
                }
                // Get Governate
                $sqlGetGov = $this->conn->query("SELECT GOVName From Governate Where ID = $ServiceData[17]");
                if($sqlGetGov->num_rows > 0)
                {
                    $GovName = $sqlGetGov->fetch_row();
                }
                elseif($sqlGetGov->num_rows <= 0)
                {
                    $GovName = $ServiceData[17];
                }
                 // Get City
                $sqlGetCity = $this->conn->query("SELECT Name From City Where ID = $ServiceData[18]");
                if($sqlGetCity->num_rows > 0)
                {
                    $CityName = $sqlGetCity->fetch_row();
                }
                elseif($sqlGetCity->num_rows <= 0)
                {
                    $CityName = $ServiceData[18];
                }
                // Get Region
                $sqlGetRegion = $this->conn->query("SELECT RegionName From Region Where ID = $ServiceData[19]");
                if($sqlGetRegion->num_rows > 0)
                {
                    $RegionName = $sqlGetRegion->fetch_row();
                }
                elseif($sqlGetRegion->num_rows <= 0)
                {
                    $RegionName = $ServiceData[19];
                }
                 // Get Compound
                $sqlGetCompound = $this->conn->query("SELECT CompundName From Compound Where ID = $ServiceData[20]");
                if($sqlGetCompound->num_rows > 0)
                {
                    $CompName = $sqlGetCompound->fetch_row();
                }
                elseif($sqlGetCompound->num_rows <= 0)
                {
                    $CompName = $ServiceData[20];
                }
                // Get Street
                $sqlGetStreet = $this->conn->query("SELECT StreetName From Street Where ID = $ServiceData[21]");
                if($sqlGetStreet->num_rows > 0)
                {
                    $StreetName = $sqlGetStreet->fetch_row();
                }
                elseif($sqlGetStreet->num_rows <= 0)
                {
                    $StreetName = $ServiceData[21];
                }
                
                 $Service["Record$count"] = 
                [
                    "id" => $ServiceData[0],
                    "name" => $ServiceData[1],
                    "description" => $ServiceData[2],
                    "phoneNums" => $PhoneNums,
                    "image" => $attachmentURL,
                    "rate" => $ServiceData[8],
                    "categoryID" => $ServiceData[10],
                    "categoryName" => $CatName[0],
                    "latitude" => $ServiceData[14],
                    "longitude" => $ServiceData[15],      
                    "countryName" => $CountryName[0],
                    "governateName" => $GovName[0],
                    "cityName" => $CityName[0],
                    "regionName" => $RegionName[0],
                    "compoundName" => $CompName[0],
                    "streetName" => $StreetName[0],
                ];
            }
            
        }
        if($sqlGetServID->num_rows <= 0)
        {
            $Service = [];
        }
        $this->returnResponse(200, array_values($Service));                            
                                       
                                       
                                       
                                       

                                    
                                   
    }
    
}

?>