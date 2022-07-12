<?php 

echo "START: ".date('Y-m-d H:i:s')."\r\n";

include_once('simple_html_dom.php');


$db_host	= "/";
$db_user	= "/";
$db_pass	= "/";
$database	= "/";

$connection = new mysqli($db_host,$db_user,$db_pass,$database) or die ("Can't connect to host!\r\n");


echo 'Connected to the database.';

// -------------- PRIKUPLJANJE PODATAKA SA BERZI: HUPX(hu), OTE(cz), OKTE(sk), OPCOM(ro)    

function getHUPXData( $url, $connection ) {
	$html_string = file_get_html($url);

    $html = str_get_html($html_string);
    $tables = $html->find('table');
	$rows = $tables[3]->find('tr'); //svi redovi druge tabele na html strani

    // initialize empty array to store the data array from each row
    $theData = array();
    // initialize array to store the direction data: hu,sk,cz,ro...
	$berza= 'HUPX';
	
    foreach ($rows as $k => $row) {

        // initialize array to store the cell data from each row
        $rowData = '';
        foreach ($row->find('td') as $cell) {
			 // push the cell's text to the variable
			$rowData = $cell->innertext;	          
        }
		
		if($k % 2 != 0) {
			 // push the row's data from variable to the array
			$theData[] = $rowData;          	
		}
    }
	
	foreach($theData as $data) {
			echo "Data: ".$data."\n";
		}

    $date = date('Y-m-d');
	$date_tomorow = date('Y-m-d', strtotime(' +1 day'));	


    // --------- AKO NEMA PODATAKA UPISI SVUDA NULL
    if (empty($theData[1]) && empty($theData[2]) && empty($theData[3])) { 

        $sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,datetime)
				VALUES ('$date_tomorow','".$berza."',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,now())";   

            mysql_query($sql) or die(mysql_error());
  
		echo "NEMA PODATAKA SA BERZI HUPX(hu): ".$date_tomorow."\r\n";
    }
    // ------------  VIDI DA LI JE BILA PROMENA SA ZIMSKOG NA LETNJE  ----------------
    elseif ($theData[2]=='-' && (date("m",strtotime($date_tomorow))==='03')) {

            $sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,datetime)
            VALUES ('$date_tomorow','".$berza."','".$theData[0]."','".$theData[1]."','".$theData[3]."','".$theData[4]."','".$theData[5]."','".$theData[6]."','".$theData[7]."','".$theData[8]."','".$theData[9]."','".$theData[10]."','".$theData[11]."','".$theData[12]."','".$theData[13]."','".$theData[14]."','".$theData[15]."','".$theData[16]."','".$theData[17]."','".$theData[18]."','".$theData[19]."','".$theData[20]."','".$theData[21]."','".$theData[22]."','".$theData[23]."',NULL,now())";   

            mysql_query($sql) or die(mysql_error());			
       
		echo "USPRESAN UPIS PODATAKA SA BERZI HUPX(hu) / PROMENA VREMENA SA ZIMSKOG NA LETNJE: ".$date_tomorow."\r\n";
    } // ------------    DA LI JE BILA PROMENA SA LETNJEG NA ZIMSKO    ----------
	elseif ((date("m",strtotime($date_tomorow))==='10') && !empty($theData[24])) {

            $sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,v25,datetime)
            VALUES ('$date_tomorow','".$berza."','".$theData[0]."','".$theData[1]."','".$theData[2]."','".$theData[3]."','".$theData[4]."','".$theData[5]."','".$theData[6]."','".$theData[7]."','".$theData[8]."','".$theData[9]."','".$theData[10]."','".$theData[11]."','".$theData[12]."','".$theData[13]."','".$theData[14]."','".$theData[15]."','".$theData[16]."','".$theData[17]."','".$theData[18]."','".$theData[19]."','".$theData[20]."','".$theData[21]."','".$theData[22]."','".$theData[23]."','".$theData[24]."',now())";   

            mysql_query($sql) or die(mysql_error());			
         
		echo "USPRESAN UPIS PODATAKA SA BERZI HUPX(hu) / PROMENA VREMENA SA LETNJEG NA ZIMSKO: ".$date_tomorow."\r\n";
    } else {  // UPISI VREDNOSTI U TABELU KADA JE NORMALAN DAN(NIJE PROMENA VREMENA)

            // $sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,datetime)
            // VALUES ('$date_tomorow','".$berza."','".$theData[1]."','".$theData[2]."','".$theData[3]."','".$theData[4]."','".$theData[5]."','".$theData[6]."','".$theData[7]."','".$theData[8]."','".$theData[9]."','".$theData[10]."','".$theData[11]."','".$theData[12]."','".$theData[13]."','".$theData[14]."','".$theData[15]."','".$theData[16]."','".$theData[17]."','".$theData[18]."','".$theData[19]."','".$theData[20]."','".$theData[21]."','".$theData[22]."','".$theData[23]."','".$theData[24]."',now())";   
			$sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,datetime)
             VALUES ('$date_tomorow','".$berza."','".$theData[0]."','".$theData[1]."','".$theData[2]."','".$theData[3]."','".$theData[4]."','".$theData[5]."','".$theData[6]."','".$theData[7]."','".$theData[8]."','".$theData[9]."','".$theData[10]."','".$theData[11]."','".$theData[12]."','".$theData[13]."','".$theData[14]."','".$theData[15]."','".$theData[16]."','".$theData[17]."','".$theData[18]."','".$theData[19]."','".$theData[20]."','".$theData[21]."','".$theData[22]."','".$theData[23]."',now())";   

            // mysql_query($sql) or die(mysql_error());	
			$connection->query($sql); 			
          
		echo "USPRESAN UPIS PODATAKA SA BERZI HUPX(hu) : ".$date_tomorow."\r\n";
    }
}


// --------------------   PRIKUPLJANJE PODATAKA SA OSTALIH BERZI POMOCU Curl funkcije -------------------------

// funkcija za skremblovanje html strane
function curlGetPageContent( $path, $auth = '' ) {

    // Get cURL resource
    $curl = curl_init();
    // Set some options - we are passing in a useragent too here
    curl_setopt_array($curl, array(
        CURLOPT_USERPWD => $auth,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_URL => $path,
        CURLOPT_SSL_VERIFYPEER => 0,
        CURLOPT_FOLLOWLOCATION => true
    ));
    // Send the request & save response to $resp
    $resp = curl_exec($curl);
    if(curl_errno($curl)){
        $result = 'Error';

    } else {
        // check the HTTP status code of the request
        $resultStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($resultStatus == 200) {
            // everything went better than expected
            $result = $resp;
        } else {
            // the request did not complete as expected. common errors are 4xx
            // (not found, bad request, etc.) and 5xx (usually concerning
            // errors/exceptions in the remote script execution)
            $result = $resultStatus;
        }
    }
    // Close request to clear up some resources
    curl_close($curl);

    return $result;
}


// --------------------   uzima podatke sa berzi EXEP-FR, EPEX-AT, EPEX-DELU, EPEX-CH i upisuj ih u bazu   -----------
function getDataInsertData( $api_path, $granica, $brTabele, $connection ) {

    $get_xml = curlGetPageContent($api_path);

    $dom = new DOMDocument;
    $dom->loadHTML($get_xml);
    $tables = $dom->getElementsByTagName('table');

    $rows = array();
    foreach ($tables as $table) {
        $rows = $table->getElementsByTagName('tr');
        break;
    }

    $date = date('Y-m-d');
	$date_tomorow = date('Y-m-d', strtotime(' +1 day'));
	

// initialize empty array to store the data array from each row
    $theData = array();
    foreach ($rows as $row) {
        $theData[] = $row->getElementsByTagName('td')->item(3)->textContent;				
    }
    
    // ----------------- DAN SA PROMENOM VREMENA SA ZIMSKOG NA LETNJE ----------------------
    if ((empty($theData[8]) || $theData[8]=='-') && (!empty($theData[9]) || !$theData[8]=='-') && date("m",strtotime($date_tomorow))==='03') {

        $sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,datetime)
        VALUES ('$date_tomorow','".$granica."','".$theData[6]."','".$theData[7]."','".$theData[9]."','".$theData[10]."','".$theData[11]."','".$theData[12]."','".$theData[13]."','".$theData[14]."','".$theData[15]."','".$theData[16]."','".$theData[17]."','".$theData[18]."','".$theData[19]."','".$theData[20]."','".$theData[21]."','".$theData[22]."','".$theData[23]."','".$theData[24]."','".$theData[25]."','".$theData[26]."','".$theData[27]."','".$theData[28]."','".$theData[29]."',NULL,now())"; 
        
        // mysql_query($sql) or die(mysql_error());
		$connection->query($sql); 
		echo "USPESAN UPIS PODATAKA SA BERZI EXEP-FR, EPEX-AT, EPEX-DELU, EPEX-CH / PROMENA SA ZIMSKOG NA LETNJE: ".$date_tomorow."\r\n";
    } 
	elseif (date("m",strtotime($date_tomorow))==='10' && !empty($theData[30]) && !empty($theData[29])) { 
        // ----------------- DAN SA PROMENOM VREMENA SA LETNJEG NA ZIMSKO ----------------------
    
        $sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,datetime)
        VALUES ('$date_tomorow','".$granica."','".$theData[6]."','".$theData[7]."','".$theData[8]."','".$theData[9]."','".$theData[10]."','".$theData[11]."','".$theData[12]."','".$theData[13]."','".$theData[14]."','".$theData[15]."','".$theData[16]."','".$theData[17]."','".$theData[18]."','".$theData[19]."','".$theData[20]."','".$theData[21]."','".$theData[22]."','".$theData[23]."','".$theData[24]."','".$theData[25]."','".$theData[26]."','".$theData[27]."','".$theData[28]."','".$theData[29]."','".$theData[30]."',now())";  

        // mysql_query($sql) or die(mysql_error());
		$connection->query($sql); 
		echo "USPESAN UPIS PODATAKA SA BERZI EXEP-FR, EPEX-AT, EPEX-DELU, EPEX-CH / PROMENA VREMENA SA LETNJEG NA ZIMSKO: ".$date_tomorow."\r\n";
    } 
	elseif (empty($theData[6]) && empty($theData[7]) && empty($theData[8])){
		echo "Nema podataka za ".$granica;
		echo '<br>';
		$sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,datetime)
        VALUES ('$date_tomorow','".$granica."',NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,NULL,now())";    

        // mysql_query($sql) or die(mysql_error());
		$connection->query($sql); 
		echo "NEMA PODATAKA SA BERZI EXEP-FR, EPEX-AT, EPEX-DELU, EPEX-CH: ".$date_tomorow."\r\n";
	}
	else { // ----------------- DAN BEZ PROMENE VREMENA ---------------

        $sql = "INSERT INTO dam_test (datum, berza, v1,v2,v3,v4,v5,v6,v7,v8,v9,v10,v11,v12,v13,v14,v15,v16,v17,v18,v19,v20,v21,v22,v23,v24,datetime)
        VALUES ('$date_tomorow','".$granica."','".$theData[6]."','".$theData[7]."','".$theData[8]."','".$theData[9]."','".$theData[10]."','".$theData[11]."','".$theData[12]."','".$theData[13]."','".$theData[14]."','".$theData[15]."','".$theData[16]."','".$theData[17]."','".$theData[18]."','".$theData[19]."','".$theData[20]."','".$theData[21]."','".$theData[22]."','".$theData[23]."','".$theData[24]."','".$theData[25]."','".$theData[26]."','".$theData[27]."','".$theData[28]."','".$theData[29]."',now())";  

        // mysql_query($sql) or die(mysql_error());
		$connection->query($sql); 
		echo "USPESAN UPIS PODATAKA SA BERZI EXEP-FR, EPEX-AT, EPEX-DELU, EPEX-CH: ".$date_tomorow."\r\n";
    }       
	
}



$date_today = date('Y-m-d');
$date_tomorow = date('Y-m-d', strtotime(' +1 day'));


 // HUPX(hu), 
getHUPXData("https://hupx.hu/en/market-data/dam/weekly-data", $connection);

 //EPEX-FR, EPEX-AT, EPEX-DELU, EPEX-CH 
getDataInsertData("https://www.epexspot.com/en/market-data?market_area=FR&trading_date=$date_today&delivery_date=$date_tomorow&underlying_year=&modality=Auction&sub_modality=DayAhead&product=60&data_mode=table&period=", 'EPEX-FR', 0, $connection);
getDataInsertData("https://www.epexspot.com/en/market-data?market_area=AT&trading_date=$date_today&delivery_date=$date_tomorow&underlying_year=&modality=Auction&sub_modality=DayAhead&product=60&data_mode=table&period=", 'EPEX-AT', 0, $connection);
getDataInsertData("https://www.epexspot.com/en/market-data?market_area=DE-LU&trading_date=$date_today&delivery_date=$date_tomorow&underlying_year=&modality=Auction&sub_modality=DayAhead&product=60&data_mode=table&period=", 'EPEX-DELU', 0, $connection);
getDataInsertData("https://www.epexspot.com/en/market-data?market_area=CH&trading_date=$date_today&delivery_date=$date_tomorow&underlying_year=&modality=Auction&sub_modality=DayAhead&product=60&data_mode=table&period=", 'EPEX-CH', 0, $connection);

 // SEEPEX


echo "END: ".date('Y-m-d H:i:s')."\r\n";

?>


