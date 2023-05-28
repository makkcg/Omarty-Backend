<?php

header("content-type: Application/json");
class DropDowns extends Functions
{
    public function country()
    {
        include("../Config.php");
        $CountryId = $_POST["id"];
        
        if(empty($CountryId))
        {
            $sqlGetCountriesData2 = $conn->query("SELECT * FROM Country WHERE ID > 1");
            $arr = [];
            $count = 1;
            while($countryData = $sqlGetCountriesData2->fetch_row())
            {
                
                $arr["$count"] = ["id" => "$countryData[0]", "name" => "$countryData[1]", "numberOfGovernates" => "$countryData[2]"];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
        }
        if(!empty($CountryId))
        {
            $sqlGetCountriesData2 = $conn->query("SELECT * FROM Country WHERE ID = '$CountryId'");
            $arr = [];
            $count = 1;
            while($countryData = $sqlGetCountriesData2->fetch_row())
            {
                
                $arr["$count"] = ["id" => "$countryData[0]", "name" => "$countryData[1]", "numberOfGovernates" => "$countryData[2]"];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
        }

        
    }
    
    public function governate()
    {
        include("../Config.php");
        $CountryID = $_POST["countryId"];
        if(empty($CountryID))
        {
            $sqlGetGovernateData2 = $conn->query("SELECT * FROM Governate WHERE ID > 1");
            // $RowsNum = $sqlGetGovernateData2->num_rows;
            $LastPage = ceil($RowsNum / $Limit);
            $arr = [];
            $count = 1;
            while($GovData = $sqlGetGovernateData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$GovData[0]", "name" => "$GovData[1]", "numberOfCities" => "$GovData[2]", "countryId" => "$GovData[3]"/*, "flagLastPage" => $FLP*/];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
        }
        
        if(!empty($CountryID))
        {
            $sqlGetGovernateData2 = $conn->query("SELECT * FROM Governate WHERE ID > 1 AND CountryID = '$CountryID'");
            // $RowsNum = $sqlGetGovernateData2->num_rows;
            $LastPage = ceil($RowsNum / $Limit);
            $arr = [];
            $count = 1;
            while($GovData = $sqlGetGovernateData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$GovData[0]", "name" => "$GovData[1]", "numberOfCities" => "$GovData[2]", "countryId" => "$GovData[3]"];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
        }

    }

    public function city()
    {
        include("../Config.php");
        $GovID = $_POST["governateId"];
        
        if(empty($GovID))
        {
            $sqlGetCityData2 = $conn->query("SELECT * FROM City WHERE ID > 1");
            $arr = [];
            $count = 1;
            while($CityData = $sqlGetCityData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$CityData[0]", "name" => "$CityData[1]", "numberOfRegions" => "$CityData[2]", "countryId" => "$CityData[3]", "governateId" => "$CityData[4]"];
                $count++;
            }
            $this->returnResponse(200, array_values($arr));
        }
        if(!empty($GovID))
        {
            $sqlGetCityData2 = $conn->query("SELECT * FROM City WHERE ID > 1 AND GovID = '$GovID'");
            $arr = [];
            $count = 1;
            while($CityData = $sqlGetCityData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$CityData[0]", "name" => "$CityData[1]", "numberOfRegions" => "$CityData[2]", "countryId" => "$CityData[3]", "governateId" => "$CityData[4]"];
                $count++;
            }
            $this->returnResponse(200, array_values($arr));
        }

        
    }

    public function region()
    {
        include("../Config.php");
        $CityID = $_POST["cityId"];
        if(empty($CityID))
        {
            $sqlGetRegionData2 = $conn->query("SELECT * FROM Region WHERE ID > 1");
            $arr = [];
            $count = 1;
            while($RegionData = $sqlGetRegionData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$RegionData[0]", "name" => "$RegionData[1]", "numberOfCompounds" => "$RegionData[2]", "countryId" => "$RegionData[3]", "governateId" => "$RegionData[4]", "cityId" => "$RegionData[5]"];
                $count++;
            }
            $this->returnResponse(200, array_values($arr));
        }
            

        if(!empty($CityID))
        {
            $sqlGetRegionData2 = $conn->query("SELECT * FROM Region WHERE ID > 1 AND CityID = '$CityID'");
            $arr = [];
            $count = 1;
            while($RegionData = $sqlGetRegionData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$RegionData[0]", "name" => "$RegionData[1]", "numberOfCompounds" => "$RegionData[2]", "countryId" => "$RegionData[3]", "governateId" => "$RegionData[4]", "cityId" => "$RegionData[5]"];
                $count++;
            }
            $this->returnResponse(200, array_values($arr));
        }
        
    }

    public function compound()
    {
        include("../Config.php");
        $RegionID = $_POST["regionId"];
        if(empty($RegionID))
        {
            $sqlGetcompoundData2 = $conn->query("SELECT * FROM Compound WHERE ID > 1");
            $arr = [];
            $count = 1;
            while($CompoundData = $sqlGetcompoundData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$CompoundData[0]", "name" => "$CompoundData[1]", "numberOfBlocks" => "$CompoundData[2]", "countryId" => "$CompoundData[3]", "governateId" => "$CompoundData[4]", "cityId" => "$CompoundData[5]", "regionId" => "$CompoundData[6]"];
                $count++;
            }
                
            $this->returnResponse(200, array_values($arr));
        }
        
        if(!empty($RegionID))
        {
            $sqlGetcompoundData2 = $conn->query("SELECT * FROM Compound WHERE ID > 1 AND RegionID = '$RegionID'");
            $arr = [];
            $count = 1;
            while($CompoundData = $sqlGetcompoundData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$CompoundData[0]", "name" => "$CompoundData[1]", "numberOfBlocks" => "$CompoundData[2]", "countryId" => "$CompoundData[3]", "governateId" => "$CompoundData[4]", "cityId" => "$CompoundData[5]", "regionId" => "$CompoundData[6]"];
                $count++;
            }
                
            $this->returnResponse(200, array_values($arr));
        }
        
    }
  
    public function street()
    {
        include("../Config.php");
        $RegionID = $_POST["regionId"];
        $CompoundID = $_POST["compoundId"];
        if((empty($RegionID) || $RegionID == '1') && (empty($CompoundID) || $CompoundID == '1'))
        {
            $sqlGetStreetData2 = $conn->query("SELECT * FROM Street WHERE ID > 1");
            $arr = [];
            $count = 1;
            while($StreetData = $sqlGetStreetData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$StreetData[0]", "name" => "$StreetData[1]", "numberOfBlocks" => "$StreetData[2]", "countryId" => "$StreetData[3]", "governateId" => "$StreetData[4]", "cityId" => "$StreetData[5]", "regionId" => "$StreetData[6]", "compoundId" => "$StreetData[7]"];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
        }
        elseif((empty($RegionID) || $RegionID == '1') && !empty($CompoundID))
        {
            $sqlGetStreetData2 = $conn->query("SELECT * FROM Street WHERE ID > 1 AND CompundID = '$CompoundID'");
            $arr = [];
            $count = 1;
            while($StreetData = $sqlGetStreetData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$StreetData[0]", "name" => "$StreetData[1]", "numberOfBlocks" => "$StreetData[2]", "countryId" => "$StreetData[3]", "governateId" => "$StreetData[4]", "cityId" => "$StreetData[5]", "regionId" => "$StreetData[6]", "compoundId" => "$StreetData[7]"];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
        }
        elseif(!empty($RegionID) && (empty($CompoundID) || $CompoundID == '1'))
        {
            $sqlGetStreetData2 = $conn->query("SELECT * FROM Street WHERE ID > 1 AND RegionID = '$RegionID'");
            $arr = [];
            $count = 1;
            while($StreetData = $sqlGetStreetData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$StreetData[0]", "name" => "$StreetData[1]", "numberOfBlocks" => "$StreetData[2]", "countryId" => "$StreetData[3]", "governateId" => "$StreetData[4]", "cityId" => "$StreetData[5]", "regionId" => "$StreetData[6]", "compoundId" => "$StreetData[7]"];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
        }
        elseif(!empty($RegionID) && !empty($CompoundID))
        {
            $sqlGetStreetData2 = $conn->query("SELECT * FROM Street WHERE ID > 1 AND RegionID = '$RegionID' AND CompundID = '$CompoundID'");
            $arr = [];
            $count = 1;
            while($StreetData = $sqlGetStreetData2->fetch_row())
            {
                $arr["$count"] = ["id" => "$StreetData[0]", "name" => "$StreetData[1]", "numberOfBlocks" => "$StreetData[2]", "countryId" => "$StreetData[3]", "governateId" => "$StreetData[4]", "cityId" => "$StreetData[5]", "regionId" => "$StreetData[6]", "compoundId" => "$StreetData[7]"];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
        }
        

        
    }
    
    public function repeatStatus()
    {
        include("../Config.php");
            // $Page = $_POST["page"];
            // if(empty($Page))
            // {
            //     $Page = 1;
            // }
            // $Limit = 10;
            // $Start = ($Page - 1) * $Limit;
        try
        {
            // $sqlGetStatusData = $conn->query("SELECT * FROM Status WHERE ID BETWEEN 4 AND 7 LIMIT $Start, $Limit");
            $sqlGetStatusData2 = $conn->query("SELECT * FROM Status WHERE ID BETWEEN 4 AND 7");
            // $RowsNum = $sqlGetStatusData2->num_rows;
            // $LastPage = ceil($RowsNum / $Limit);
            $arr = [];
            $count = 1;
            while($StatusData = $sqlGetStatusData2->fetch_row())
            {
                // Get Last page flag.
                // if(($Limit + $Start) >= $RowsNum)
                // {
                //     $FLP = 1;
                // }
                // elseif(($Limit + $Start) < $RowsNum)
                // {
                //     $FLP = 0;
                // }
                $arr["$count"] = ["id" => "$StatusData[0]", "name" => "$StatusData[1]", "explanation" => "$StatusData[2]"/*, "flagLastPage" => $FLP*/];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));
            
        }catch(Exception $e)
        {
            $this->throwError(304, $e->getMessage());
        }
    }
    
    public function getCategory()
    {
        include("../Config.php");
            // $Page = $_POST["page"];
            // if(empty($Page))
            // {
            //     $Page = 1;
            // }
            // $Limit = 10;
            // $Start = ($Page - 1) * $Limit;
        try
        {
            // $sqlGetCatData = $conn->query("SELECT * FROM ServiceCategory LIMIT $Start, $Limit");
            $sqlGetCatData2 = $conn->query("SELECT * FROM ServiceCategory");
            // $RowsNum = $sqlGetCatData2->num_rows;
            // $LastPage = ceil($RowsNum / $Limit);
            $arr = [];
            $count = 1;
            while($CategoryData = $sqlGetCatData2->fetch_row())
            {
                if($CategoryData[0] == '1')
                {
                    continue;
                }
                $arr["$count"] = ["id" => "$CategoryData[0]", "name" => "$CategoryData[1]", "explanation" => "$CategoryData[2]"/*, "flagLastPage" => $FLP*/];
                $count++;
            }
            
            $this->returnResponse(200, array_values($arr));

        }catch(Exception $e)
        {
            $this->throwError(304, $e->getMessage());
        }
    }
}

?>