<?php // STEFAN - ova skripta uzima .xsxl fajl, obradjuje podatke i upisuje u tabele "dugorocni_prizvodi" i "weekly_futures"
	// EDIT 24.08.2021. - Dodata je tabela CO2 i logika za upisivanje podataka u tabelu CO2

echo "START: ".date('Y-m-d H:i:s')."<br>";

ini_set('default_charset', 'UTF-8');
ini_set('display_errors', 0);

$db_host	= "/";
$db_user	= "/";
$db_pass	= "/";
$database	= "/";

ini_set ( 'max_execution_time', 1200); 
require('PHPExcel.php'); // na afel serveru
// include './libraries/PHPExcel.php'; // na localhostu

$connection = new mysqli($db_host,$db_user,$db_pass,$database) or die ("Can't connect to host!\r\n");

$directory = "/home/afel/share3/afel_dugorocni_proizvodi/";
$file_list=scandir($directory);
$number_of_files=count($file_list);
echo "Broj fajlova u direktorijumu: ".$number_of_files."<br>"; // 

$date = date('ymd'); // danasnji datum

$currentYear = date('Y'); // tekuca godina
// $currentYear = date("Y",strtotime("-1 year")); // prethodna godina

for ($i=0; $i<$number_of_files; $i++) {  // iteracija kroz fajlove

	$file_name=$file_list[$i];	 // uzima naziv fajla
	$date_file = substr($file_name,12,8); // uzima datum iz naziva fajla	
	$file = $directory."/".$file_name; // fajl koji koristimo za obradu

	if (substr($file_name,0,11) == "EEX futures") { // provera da li je ovo trazeni fajl
		echo "Pronadjen je fajl: ".$file_name."<br>";

		$reader = PHPExcel_IOFactory::createReaderForFile($file); // instanca za citanje Excel fajla
		$reader->setReadDataOnly(true);
		$objExcel = $reader->load($file);

		$product_base = "BASE";
		$product_peak = "PEAK";
		$product_offpeak = "OFFPEAK";
		$berza = "";
		
		for ($j=0; $j < 7; $j++) { // uzimamo redom po jedan Sheet iz Excel fajla i punimo tabelu "dugorocni_proizvoodi"

			if ($j==1) { // 2. Sheet

				$year = substr($currentYear, 2); // uzimaj zadnje dve cifre od tekuce godine (2021 -> 21)
				$sheet =  $objExcel->getSheet($j); // uzmi Sheet
				$berza = "EEX DE AT";
				
				getData($year, $sheet, $berza, $product_base, $connection);
				getData($year, $sheet, $berza, $product_peak, $connection);
				getDataOFFPEAK($year, $sheet, $berza, $product_offpeak, $connection);

			} else { // 1. 3. 4. Sheet

				$year = substr($currentYear, 2); // uzimaj zadnje dve cifre od tekuce godine (2021 -> 21)

				if($j !== 6) { // da ne uzima sedmi sit jer ne postoji nego da odabera default iz switch petlje
					$sheet =  $objExcel->getSheet($j); // uzmi Sheet
				}				
								
				switch ($j) { // popuni colonu BERZA
					case 0:
						$berza = "EEX DE";
						break;
					case 2:
						$berza = "EEX HU";
						break;
					case 3:
						$berza = "EEX RS";
						break;
					case 4:
						$berza = "EUA Futures";
						break;
					case 5:
						$berza = "EUA Spot";
						break;
					default:
						$berza = "ECarbix";
						break;
				}			
				
				if($berza == "EUA Futures") {
					getDataEUA($year, $sheet, $berza, $connection);
				} 
				else if ($berza == "EUA Spot") {
					getDataEUASpot($year, $sheet, $berza, $connection, 'C');
				} 
				else if ($berza == "ECarbix") {
					getDataEUASpot($year, $sheet, $berza, $connection, 'E');
				} 
				else {
					getData($year, $sheet, $berza, $product_base, $connection);
					getData($year, $sheet, $berza, $product_peak, $connection);
				}								
			}
		}

		for ($j=0; $j < 4; $j++) { // uzimamo redom po jedan Sheet iz Excel fajla i punimo tabelu "weekly_futures"
			if ($j==1) {
				echo "preskaci drugi sheet";
			} else {
				$year = substr($currentYear, 2); // uzimaj zadnje dve cifre od tekuce godine (2021 -> 21)
				$sheet =  $objExcel->getSheet($j); // uzmi Sheet
				
				$berza = "";
				switch ($j) { // popuni colonu BERZA
					case 0:
						$berza = "EEX DE";
						break;
					case 2:
						$berza = "EEX HU";
						break;
					case 3:
						$berza = "EEX RS";
						break;
				}

				getDataWK($year, $sheet, $berza, $product_base, $connection, 'N', 'O');
				getDataWK($year, $sheet, $berza, $product_peak, $connection, 'P', 'Q');
			}
		}

		// $destination = "/home/afel/share3/afel_dugorocni_proizvodi/archive/".$file_name;
		$destination = "/home/afel/files/stefan/archive/dugorocni_proizvodi_weekly_futures/".$file_name;
		if (!copy($file, $destination)) {
			echo "Fajl nije iskopiran!"."<br>";
		} else {
			echo "Fajl je uspesno iskopiran u arhivu"."<br>";
		}

		unlink($file); // brise fajl
	}
}


function getDataEUA($year, $sheet, $berza, $connection) { // uzima podatke iz EUA Features sheet-a
	$y = 2;
	$array = array();	

	while ($sheet->getCell('D'.$y)->getCalculatedValue() !== null) {	

		if(substr($sheet->getCell('D'.$y)->getCalculatedValue(), 4) == $year) {

				$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
				$row[1] = $berza;
				$row[2] = "20".$year; // dodajemo kolonu GODINA
				$row[3] = NULL;
				
				$array = getDataEUA_M_Values($year, $sheet, $berza, $connection, $y, $row); // uzima vrednosti za datu godinu
				$row = $array[0];
				$y = $array[1];	
				$array = [];
				
				$row[16] = NULL;
				
				insertDataIntoTableCO2($row, $connection);	
				$row = [];
			}			
		$year = $year+1;
	} 	
}

function getDataEUA_M_Values($year, $sheet, $berza, $connection, $y, $row) {

	while($sheet->getCell('D'.$y)->getCalculatedValue() !== null) {
		$cell = $sheet->getCell('D'.$y)->getCalculatedValue();
		if(substr($cell, 4) == $year) {
			switch (substr($cell, 0, 3)) {
					case 'Jan':
						$row[4] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Feb':
						$row[5] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Mar':
						$row[6] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Apr':
						$row[7] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'May':
						$row[8] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Jun':
						$row[9] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Jul':
						$row[10] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Aug':
						$row[11] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Sep':
						$row[12] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Oct':
						$row[13] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;
					case 'Nov':
						$row[14] = $sheet->getCell('E'.$y)->getCalculatedValue();				
						break;
					case 'Dec':
						$row[15] = $sheet->getCell('E'.$y)->getCalculatedValue();
						break;		
				}						
		} else {
			return [$row, $y];
		}
		$y++;
	}	
	return [$row, $y];	
}

function getDataEUASpot($year, $sheet, $berza, $connection, $col) { // uzima podatke iz EUA Spot sheet-a
	$y = 2;		
	
	while ($sheet->getCell($col.$y)->getCalculatedValue() !== null) {
		$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
		$row[1] = $berza;
		$row[2] = "20".$year; // dodajemo kolonu GODINA
		$row[16] = $sheet->getCell($col.$y)->getCalculatedValue(); 
		$y++;
	}	
	insertDataIntoTableCO2($row, $connection);	
	$row = [];
}


function getDataWK($year, $sheet, $berza, $product, $connection, $letter1, $letter2) {
				$k = 2;	
				$datum = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
				$row[0] = $datum;
				$row[1] = $berza;
				$row[2] = $product; // popuni kolonu PRODUKT
				$row[3] = "20".$year; // dodajemo kolonu GODINA	
				$upisaliSmoNesto = false;
				if ($sheet->getCell($letter1.$k)->getCalculatedValue() == null) {
					return;
				}
				while ($sheet->getCell($letter1.$k)->getCalculatedValue() !== null) { // prolazimo hroz N kolonu i popunjavamo $row[], kolone PRODUCT,GODINA,Y u bazi

					$cell = $sheet->getCell($letter1.$k)->getCalculatedValue();
					
					if (substr($cell, 8) == $year) {
				
						$weekNumber = intval(substr($cell, 5, 2)); // uzmi broj sedmice
	
						$row[$weekNumber+3] = $sheet->getCell($letter2.$k)->getCalculatedValue(); 				
						$upisaliSmoNesto = true;
						$k++;
					} else {
						if ($upisaliSmoNesto == true) {						
							insertDataIntoTableWeeklyFutures($row, $connection);	
						echo "zavrsen upis";	
							$upisaliSmoNesto = false;						 
						}
						$row = [];
						$row[0] = $datum;
						$row[1] = $berza;
						$row[2] = $product;
						$year = $year+1;
						$row[3] = "20".$year; 
					}
				}
				
				insertDataIntoTableWeeklyFutures($row, $connection);	
				$row = [];
						
			}

function getData($year, $sheet, $berza, $product, $connection) {

	$y = 2;
	$q = 2;
	$m = 2;
	$array = array();
	
	if($berza == "EEX DE AT") {
		if($product == 'BASE') {
			$column1 =  'B'; $column11 =  'C'; 
			$column2 =  'H'; $column21 =  'I'; 
			$column3 =  'N'; $column31 =  'O';
		} else {
			$column1 =  'D'; $column11 =  'E'; 
			$column2 =  'J'; $column21 =  'K'; 
			$column3 =  'P'; $column31 =  'Q'; 
		} 
	} 
	else {
		if($product == 'BASE') {
			$column1 =  'B'; $column11 =  'C'; 
			$column2 =  'F'; $column21 =  'G'; 
			$column3 =  'J'; $column31 =  'K';
		} else {
			$column1 =  'D'; $column11 =  'E'; 
			$column2 =  'H'; $column21 =  'I'; 
			$column3 =  'L'; $column31 =  'M'; 
		}
	}

	while ($sheet->getCell($column1.$y)->getCalculatedValue() !== null || $sheet->getCell($column2.$q)->getCalculatedValue() !== null 
			|| $sheet->getCell($column3.$m)->getCalculatedValue() !== null) {					
		if($sheet->getCell($column1.$y)->getCalculatedValue() !== null) {
		
			$n = 0;
			$h = 0;
			if(substr($sheet->getCell($column1.$y)->getCalculatedValue(),6) == $year) {
echo "y: ".$y.", year: ".$year."<br>";
				$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
				$row[1] = $berza;
				$row[2] = $product; // popuni kolonu PRODUKT
				$row[3] = "20".$year; // dodajemo kolonu GODINA
				$row[4] = $sheet->getCell($column11.$y)->getCalculatedValue();	// popunjavamo kolonu Y 
				$y++; $h++;		
			}
			if(substr($sheet->getCell($column2.$q)->getCalculatedValue(),6) == $year AND $sheet->getCell($column2.$q)->getCalculatedValue() !== null) {
				$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
				$row[1] = $berza;
				$row[2] = $product; // popuni kolonu PRODUKT
				$row[3] = "20".$year; // dodajemo kolonu GODINA
				if (empty($row[4])) {
					$row[4] = NULL;	// popunjavamo kolonu Y 
				}
	
				$array = getQValues($row, $year, $sheet, $column2, $column21, $q);
				$row = $array[0];
				$q = $array[1];		
			$array = [];
				$n++;	
			}
			if(substr($sheet->getCell($column3.$m)->getCalculatedValue(),7) == $year AND $sheet->getCell($column3.$m)->			    getCalculatedValue() !== null) {
				$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
				$row[1] = $berza;
				$row[2] = $product; // popuni kolonu PRODUKT
				$row[3] = "20".$year; // dodajemo kolonu GODINA
				if (empty($row[4])) {
					$row[4] = NULL;	// popunjavamo kolonu Y 
				} 
				
				$array = getMValues($row, $year, $sheet, $column3, $column31, 3, $m, 7);
				$row = $array[0];
				$m = $array[1];			
			$array = [];	
				$n++;				
		
			}
			if($n !== 0 OR $h !== 0) {	
				
				insertDataIntoTableDugorocniProizvodi($row, $connection);	
				$row = [];
			}			
			$year = $year+1;	
		
		} 
 		
		else if($sheet->getCell($column2.$q)->getCalculatedValue() !== null) {	
		
			$n = 0;
			$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
			$row[1] = $berza;
			$row[2] = $product; // popuni kolonu PRODUKT
			$row[3] = "20".$year; // dodajemo kolonu GODINA
			$row[4] = NULL;	// popunjavamo kolonu Y 
			if(substr($sheet->getCell($column2.$q)->getCalculatedValue(),6) == $year){
				$array = getQValues($row, $year, $sheet, $column2, $column21, $q);
				$row = $array[0];
				$q = $array[1];	
			$array = [];
				$n++;
			
			}
			if(substr($sheet->getCell($column3.$m)->getCalculatedValue(),7) == $year AND $sheet->getCell($column3.$m)->			    	getCalculatedValue() !== null) {
				$array = getMValues($row, $year, $sheet, $column3, $column31, 3, $m, 7);				
				$row = $array[0];
				$m = $array[1];	
			$array = [];
				$n++;
			
			}
			if($n !== 0 OR $h !== 0) {			

				insertDataIntoTableDugorocniProizvodi($row, $connection);	
				$row = [];
			}
			$year = $year+1;			
		}
		
		else {
		
			$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
			$row[1] = $berza;
			$row[2] = $product; // popuni kolonu PRODUKT
			$row[3] = "20".$year; // dodajemo kolonu GODINA
			$row[4] = NULL;	// popunjavamo kolonu Y 
			if(substr($sheet->getCell($column3.$m)->getCalculatedValue(),7) == $year) {
				$array = getMValues($row, $year, $sheet, $column3, $column31, 3, $m, 7);
				$row = $array[0];
				$m = $array[1];	
			$array = [];

				insertDataIntoTableDugorocniProizvodi($row, $connection);
				$row = [];	
			}			
			$year = $year+1;
			
		}
	} 
}

function getDataOFFPEAK($year, $sheet, $berza, $product, $connection) {

	$y = 2;
	$q = 2;
	$m = 2;
	$array = array();	

	$column1 =  'F'; $column11 =  'G'; 
	$column2 =  'L'; $column21 =  'M'; 
	$column3 =  'R'; $column31 =  'S';


	while ($sheet->getCell($column1.$y)->getCalculatedValue() !== null || $sheet->getCell($column2.$q)->getCalculatedValue() !== null 
			|| $sheet->getCell($column3.$m)->getCalculatedValue() !== null) {					
		if($sheet->getCell($column1.$y)->getCalculatedValue() !== null) {
		
			$n = 0;
			$h = 0;
			if(substr($sheet->getCell($column1.$y)->getCalculatedValue(),9) == $year) {
echo "y: ".$y.", year: ".$year."<br>";
				$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
				$row[1] = $berza;
				$row[2] = $product; // popuni kolonu PRODUKT
				$row[3] = "20".$year; // dodajemo kolonu GODINA
				$row[4] = $sheet->getCell($column11.$y)->getCalculatedValue();	// popunjavamo kolonu Y 
				$y++; $h++;		
			}
			if(substr($sheet->getCell($column2.$q)->getCalculatedValue(),9) == $year AND $sheet->getCell($column2.$q)->			    getCalculatedValue() !== null) {
				$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
				$row[1] = $berza;
				$row[2] = $product; // popuni kolonu PRODUKT
				$row[3] = "20".$year; // dodajemo kolonu GODINA
				if (empty($row[4])) {
					$row[4] = NULL;	// popunjavamo kolonu Y 
				}
	
				$array = getQValuesOFFPEAK($row, $year, $sheet, $column2, $column21, $q);
				$row = $array[0];
				$q = $array[1];		
			$array = [];
				$n++;	
			}
			if(substr($sheet->getCell($column3.$m)->getCalculatedValue(),10) == $year AND $sheet->getCell($column3.$m)->			    getCalculatedValue() !== null) {
				$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
				$row[1] = $berza;
				$row[2] = $product; // popuni kolonu PRODUKT
				$row[3] = "20".$year; // dodajemo kolonu GODINA
				if (empty($row[4])) {
					$row[4] = NULL;	// popunjavamo kolonu Y 
				} 
				
				$array = getMValues($row, $year, $sheet, $column3, $column31, 6, $m, 10);
				$row = $array[0];
				$m = $array[1];			
			$array = [];	
				$n++;				
		
			}
			if($n !== 0 OR $h !== 0) {	
				
				insertDataIntoTableDugorocniProizvodi($row, $connection);	
				$row = [];
			}			
			$year = $year+1;	
		
		} 
 		
		else if($sheet->getCell($column2.$q)->getCalculatedValue() !== null) {	
		
			$n = 0;
			$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
			$row[1] = $berza;
			$row[2] = $product; // popuni kolonu PRODUKT
			$row[3] = "20".$year; // dodajemo kolonu GODINA
			$row[4] = NULL;	// popunjavamo kolonu Y 
			if(substr($sheet->getCell($column2.$q)->getCalculatedValue(),9) == $year){
				$array = getQValuesOFFPEAK($row, $year, $sheet, $column2, $column21, $q);
				$row = $array[0];
				$q = $array[1];	
			$array = [];
				$n++;
			
			}
			if(substr($sheet->getCell($column3.$m)->getCalculatedValue(),10) == $year AND $sheet->getCell($column3.$m)->			    	getCalculatedValue() !== null) {
				$array = getMValues($row, $year, $sheet, $column3, $column31, 6, $m, 10);				
				$row = $array[0];
				$m = $array[1];	
			$array = [];
				$n++;
			
			}
			if($n !== 0 OR $h !== 0) {			

				insertDataIntoTableDugorocniProizvodi($row, $connection);	
				$row = [];
			}
			$year = $year+1;			
		}
		
		else {
		
			$row[0] = date('Y-m-d', PHPExcel_Shared_Date::ExcelToPHP($sheet->getCell('A1')->getCalculatedValue())); // popuni kolonu DATUM
			$row[1] = $berza;
			$row[2] = $product; // popuni kolonu PRODUKT
			$row[3] = "20".$year; // dodajemo kolonu GODINA
			$row[4] = NULL;	// popunjavamo kolonu Y 
			if(substr($sheet->getCell($column3.$m)->getCalculatedValue(),10) == $year) {
				$array = getMValues($row, $year, $sheet, $column3, $column31, 6, $m, 10);
				$row = $array[0];
				$m = $array[1];	
			$array = [];

				insertDataIntoTableDugorocniProizvodi($row, $connection);		
				$row = [];	
			}			
			$year = $year+1;
			
		}
	} 
}

function getQValues($row, $year, $sheet, $letter1, $letter2, $q) {
	while($sheet->getCell($letter1.$q)->getCalculatedValue() !== null) {
		$cell = $sheet->getCell($letter1.$q)->getCalculatedValue();		
		if(substr($cell, 6) == $year) {			 		
			switch(substr($cell, 3, 2)) { // primer: Q1
				case 'Q1':
					$row[5] = $sheet->getCell($letter2.$q)->getCalculatedValue();		
					break;
				case 'Q2':
					$row[6] = $sheet->getCell($letter2.$q)->getCalculatedValue();						
					break;
				case 'Q3':
					$row[7] = $sheet->getCell($letter2.$q)->getCalculatedValue();			
					break;
				case 'Q4':
					$row[8] = $sheet->getCell($letter2.$q)->getCalculatedValue();		
					break;
			}
			$q++;			
		} else {
			return [$row, $q];	
		}			
	}		
	return [$row, $q];	
}

function getQValuesOFFPEAK($row, $year, $sheet, $letter1, $letter2, $q) {
	while($sheet->getCell($letter1.$q)->getCalculatedValue() !== null) {
		$cell = $sheet->getCell($letter1.$q)->getCalculatedValue();		
		if(substr($cell, 9) == $year) {			 		
			switch(substr($cell, 6, 2)) { // primer: Q1
				case 'Q1':
					$row[5] = $sheet->getCell($letter2.$q)->getCalculatedValue();	
					break;
				case 'Q2':
					$row[6] = $sheet->getCell($letter2.$q)->getCalculatedValue();			
					break;
				case 'Q3':
					$row[7] = $sheet->getCell($letter2.$q)->getCalculatedValue();			
					break;
				case 'Q4':
					$row[8] = $sheet->getCell($letter2.$q)->getCalculatedValue();		
					break;
			}
			$q++;			
		} else {
			return [$row, $q];	
		}			
	}		
	return [$row, $q];	
}

function getMValues($row, $year, $sheet, $letter1, $letter2, $s, $m, $p) {
	while ($sheet->getCell($letter1.$m)->getCalculatedValue() !== null) {
		$cell = $sheet->getCell($letter1.$m)->getCalculatedValue();
		if(substr($cell, $p) == $year) {
			switch (substr($cell, $s, 3)) {
				case 'Jan':
					$row[9] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Feb':
					$row[10] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Mar':
					$row[11] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Apr':
					$row[12] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'May':
					$row[13] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Jun':
					$row[14] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Jul':
					$row[15] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Aug':
					$row[16] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Sep':
					$row[17] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Oct':
					$row[18] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;
				case 'Nov':
					$row[19] = $sheet->getCell($letter2.$m)->getCalculatedValue();				
					break;
				case 'Dec':
					$row[20] = $sheet->getCell($letter2.$m)->getCalculatedValue();
					break;		
			}	
			$m++;			
		} else {	
			return [$row, $m];	
		}		
	}		
	return [$row, $m];	
}


			function insertDataIntoTableDugorocniProizvodi($row, $connection) { // upisi u bazu		
				$sql = "INSERT INTO dugorocni_proizvodi (datum,berza,produkt,godina,Y,Q1,Q2,Q3,Q4,M1,M2,M3,M4,M5,M6,M7,M8,M9,M10,M11,M12,datetime) 
				VALUES (
				".(($row[0]=='')?"NULL":("'".$row[0]."'")).",
				".(($row[1]=='')?"NULL":("'".$row[1]."'")).",
				".(($row[2]=='')?"NULL":("'".$row[2]."'")).",
				".(($row[3]=='')?"NULL":("'".$row[3]."'")).",
				".(($row[4]=='')?"NULL":("'".$row[4]."'")).",
				".(($row[5]=='')?"NULL":("'".$row[5]."'")).",
				".(($row[6]=='')?"NULL":("'".$row[6]."'")).",
				".(($row[7]=='')?"NULL":("'".$row[7]."'")).",
				".(($row[8]=='')?"NULL":("'".$row[8]."'")).",
				".(($row[9]=='')?"NULL":("'".$row[9]."'")).",
				".(($row[10]=='')?"NULL":("'".$row[10]."'")).",
				".(($row[11]=='')?"NULL":("'".$row[11]."'")).",
				".(($row[12]=='')?"NULL":("'".$row[12]."'")).",
				".(($row[13]=='')?"NULL":("'".$row[13]."'")).",
				".(($row[14]=='')?"NULL":("'".$row[14]."'")).",
				".(($row[15]=='')?"NULL":("'".$row[15]."'")).",
				".(($row[16]=='')?"NULL":("'".$row[16]."'")).",
				".(($row[17]=='')?"NULL":("'".$row[17]."'")).",
				".(($row[18]=='')?"NULL":("'".$row[18]."'")).",
				".(($row[19]=='')?"NULL":("'".$row[19]."'")).",
				".(($row[20]=='')?"NULL":("'".$row[20]."'")).",
				now())"
				;	
echo "Dugorocni proizvodi: ".$sql;
				$res = $connection->query($sql); 				
				$row = [];
				return;
			}

			function insertDataIntoTableWeeklyFutures($row, $connection) {		
				$sql = "INSERT INTO Weekly_futures (datum,berza,produkt,godina,W01,W02,W03,W04,W05,W06,W07,W08,W09,W10,W11,W12,W13,W14,W15,W16,W17,W18,W19,W20,W21,W22,W23,W24,W25,W26,W27,W28,W29,W30,W31,W32,W33,W34,W35,W36,W37,W38,W39,W40,W41,W42,W43,W44,W45,W46,W47,W48,W49,W50,W51,W52,W53,datetime) 
				VALUES (
				".(($row[0]=='')?"NULL":("'".$row[0]."'")).",
				".(($row[1]=='')?"NULL":("'".$row[1]."'")).",
				".(($row[2]=='')?"NULL":("'".$row[2]."'")).",
				".(($row[3]=='')?"NULL":("'".$row[3]."'")).",
				".(($row[4]=='')?"NULL":("'".$row[4]."'")).",
				".(($row[5]=='')?"NULL":("'".$row[5]."'")).",
				".(($row[6]=='')?"NULL":("'".$row[6]."'")).",
				".(($row[7]=='')?"NULL":("'".$row[7]."'")).",
				".(($row[8]=='')?"NULL":("'".$row[8]."'")).",
				".(($row[9]=='')?"NULL":("'".$row[9]."'")).",
				".(($row[10]=='')?"NULL":("'".$row[10]."'")).",
				".(($row[11]=='')?"NULL":("'".$row[11]."'")).",
				".(($row[12]=='')?"NULL":("'".$row[12]."'")).",
				".(($row[13]=='')?"NULL":("'".$row[13]."'")).",
				".(($row[14]=='')?"NULL":("'".$row[14]."'")).",
				".(($row[15]=='')?"NULL":("'".$row[15]."'")).",
				".(($row[16]=='')?"NULL":("'".$row[16]."'")).",
				".(($row[17]=='')?"NULL":("'".$row[17]."'")).",
				".(($row[18]=='')?"NULL":("'".$row[18]."'")).",
				".(($row[19]=='')?"NULL":("'".$row[19]."'")).",
				".(($row[20]=='')?"NULL":("'".$row[20]."'")).",
				".(($row[21]=='')?"NULL":("'".$row[21]."'")).",
				".(($row[22]=='')?"NULL":("'".$row[22]."'")).",
				".(($row[23]=='')?"NULL":("'".$row[23]."'")).",
				".(($row[24]=='')?"NULL":("'".$row[24]."'")).",
				".(($row[25]=='')?"NULL":("'".$row[25]."'")).",
				".(($row[26]=='')?"NULL":("'".$row[26]."'")).",
				".(($row[27]=='')?"NULL":("'".$row[27]."'")).",
				".(($row[28]=='')?"NULL":("'".$row[28]."'")).",
				".(($row[29]=='')?"NULL":("'".$row[29]."'")).",
				".(($row[30]=='')?"NULL":("'".$row[30]."'")).",
				".(($row[31]=='')?"NULL":("'".$row[31]."'")).",
				".(($row[32]=='')?"NULL":("'".$row[32]."'")).",
				".(($row[33]=='')?"NULL":("'".$row[33]."'")).",
				".(($row[34]=='')?"NULL":("'".$row[34]."'")).",
				".(($row[35]=='')?"NULL":("'".$row[35]."'")).",
				".(($row[36]=='')?"NULL":("'".$row[36]."'")).",
				".(($row[37]=='')?"NULL":("'".$row[37]."'")).",
				".(($row[38]=='')?"NULL":("'".$row[38]."'")).",
				".(($row[39]=='')?"NULL":("'".$row[39]."'")).",
				".(($row[40]=='')?"NULL":("'".$row[40]."'")).",
				".(($row[41]=='')?"NULL":("'".$row[41]."'")).",
				".(($row[42]=='')?"NULL":("'".$row[42]."'")).",
				".(($row[43]=='')?"NULL":("'".$row[43]."'")).",
				".(($row[44]=='')?"NULL":("'".$row[44]."'")).",
				".(($row[45]=='')?"NULL":("'".$row[45]."'")).",
				".(($row[46]=='')?"NULL":("'".$row[46]."'")).",
				".(($row[47]=='')?"NULL":("'".$row[47]."'")).",
				".(($row[48]=='')?"NULL":("'".$row[48]."'")).",
				".(($row[49]=='')?"NULL":("'".$row[49]."'")).",
				".(($row[50]=='')?"NULL":("'".$row[50]."'")).",
				".(($row[51]=='')?"NULL":("'".$row[51]."'")).",
				".(($row[52]=='')?"NULL":("'".$row[52]."'")).",
				".(($row[53]=='')?"NULL":("'".$row[53]."'")).",
				".(($row[54]=='')?"NULL":("'".$row[54]."'")).",
				".(($row[55]=='')?"NULL":("'".$row[55]."'")).",
				".(($row[56]=='')?"NULL":("'".$row[56]."'")).",
				now())"
				;	
echo "Weekly futures: ".$sql;
				$res = $connection->query($sql); 
				$row = [];
				return;
			}
			
				// Upis u tabelu CO2
			function insertDataIntoTableCO2($row, $connection) {	
				$sql = "INSERT INTO CO2 (datum,berza,godina,Y,M1,M2,M3,M4,M5,M6,M7,M8,M9,M10,M11,M12,Spot,datetime) 
				VALUES (
				".(($row[0]=='')?"NULL":("'".$row[0]."'")).",
				".(($row[1]=='')?"NULL":("'".$row[1]."'")).",
				".(($row[2]=='')?"NULL":("'".$row[2]."'")).",
				".(($row[3]=='')?"NULL":("'".$row[3]."'")).",
				".(($row[4]=='')?"NULL":("'".$row[4]."'")).",
				".(($row[5]=='')?"NULL":("'".$row[5]."'")).",
				".(($row[6]=='')?"NULL":("'".$row[6]."'")).",
				".(($row[7]=='')?"NULL":("'".$row[7]."'")).",
				".(($row[8]=='')?"NULL":("'".$row[8]."'")).",
				".(($row[9]=='')?"NULL":("'".$row[9]."'")).",
				".(($row[10]=='')?"NULL":("'".$row[10]."'")).",
				".(($row[11]=='')?"NULL":("'".$row[11]."'")).",
				".(($row[12]=='')?"NULL":("'".$row[12]."'")).",
				".(($row[13]=='')?"NULL":("'".$row[13]."'")).",
				".(($row[14]=='')?"NULL":("'".$row[14]."'")).",
				".(($row[15]=='')?"NULL":("'".$row[15]."'")).",
				".(($row[16]=='')?"NULL":("'".$row[16]."'")).",
				now())"
				;	
echo "CO2: ".$sql;
				$res = $connection->query($sql); 				
				$row = [];
				return;
			}

?>

