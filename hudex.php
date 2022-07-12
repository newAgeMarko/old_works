<?php
include_once('simple_html_dom.php');

//uzima se danasnji datum, schedulira se za 20h
$berza="HUDEX";

$datum = date('Y-m-d', strtotime("+0 day"));



$trenutna_god=date("Y"); 
$skr_trenutna_god=substr($trenutna_god,2,2);
echo "Skracena trenutna godina je...".$skr_trenutna_god;

$sledeca_god=date("Y",strtotime("+1 year"));
$skr_sledeca_god=substr($sledeca_god,2,2);
echo "Skracena sledeca godina je...".$skr_sledeca_god;



//echo date("Y",strtotime("-1 year"));  //last year "2013"

$month_case = array ('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
$datum_dan=strtotime($datum);
$trenutni_mesec=date('M',strtotime("-1 day"));
 
$weekday= date("l", $datum_dan);

    if ($weekday =="Saturday" OR $weekday =="Sunday") { 
	 echo "Vikend je..."; 
	} 
	else
		echo "Radni je dan...".$weekday;


//Short Term
$html_string = file_get_html("https://hudex.hu/en/market-data/power/daily-data?date={$datum}#short-term");

$html = str_get_html($html_string);
$tables = $html->find('table');
$rows = $tables[0]->find('tr'); //svi redovi prve tabele na html strani


foreach ($rows as $row) {
    foreach ($row->children() as $cell) {
		 $tabela_1[]=$cell->plaintext; // Display the contents of each cell - this is the value you want to extract
		//echo '<br>';
    }
	
}	



$file = fopen("/home/afel/weekly_futures/power_daily_data_export_$datum.csv",'w');
//$file = fopen("power_daily_data_export_{$datum}.csv","w");
 $line=array($tabela_1[0],$tabela_1[1],$tabela_1[2],$tabela_1[3],$tabela_1[4],$tabela_1[5],$tabela_1[6],$tabela_1[7],$tabela_1[8],$tabela_1[9],$tabela_1[10],$tabela_1[11],$tabela_1[12]);
 fputcsv($file,$line);
  
 for ($i=13; $i<count($tabela_1)-12;$i++)
   {
   $line=array($tabela_1[$i+1],$tabela_1[$i+2],$tabela_1[$i+3],$tabela_1[$i+4],$tabela_1[$i+5],$tabela_1[$i+6],$tabela_1[$i+7],$tabela_1[$i+8],$tabela_1[$i+9],$tabela_1[$i+10],$tabela_1[$i+11],$tabela_1[$i+12],$tabela_1[$i+13]);
   fputcsv($file,$line);
   $i=$i+14;
   }
    
//Weekly data  
$tables = $html->find('table');
$rows = $tables[2]->find('tr'); //svi redovi druge tabele na html strani


foreach ($rows as $row) {
    foreach ($row->children() as $cell) {
	     $tabela_week[]=$cell->plaintext; // Display the contents of each cell - this is the value you want to extract
		 
    }
	
}  
for ($i=13; $i<count($tabela_week)-12;$i++)
   {
   $line=array($tabela_week[$i+1],$tabela_week[$i+2],$tabela_week[$i+3],$tabela_week[$i+4],$tabela_week[$i+5],$tabela_week[$i+6],$tabela_week[$i+7],$tabela_week[$i+8],$tabela_week[$i+9],$tabela_week[$i+10],$tabela_week[$i+11],$tabela_week[$i+12],$tabela_week[$i+13]);
   fputcsv($file,$line);
   $i=$i+14;
   }
   
//Monthly data Base
$tables = $html->find('table');
$rows = $tables[4]->find('tr'); //svi redovi druge tabele na html strani

foreach ($rows as $row) {
    foreach ($row->children() as $cell) {
	    $tabela_month[]=$cell->plaintext; // Display the contents of each cell - this is the value you want to extract
		
    }
	
}    
 for ($i=13; $i<count($tabela_month)-12;$i++)
   {
   $line=array($tabela_month[$i+1],$tabela_month[$i+2],$tabela_month[$i+3],$tabela_month[$i+4],$tabela_month[$i+5],$tabela_month[$i+6],$tabela_month[$i+7],$tabela_month[$i+8],$tabela_month[$i+9],$tabela_month[$i+10],$tabela_month[$i+11],$tabela_month[$i+12],$tabela_month[$i+13]);
   fputcsv($file,$line);
   $i=$i+14;
   }  
//BL Q
$tables = $html->find('table');
$rows = $tables[6]->find('tr'); //svi redovi druge tabele na html strani


foreach ($rows as $row) {
   foreach ($row->children() as $cell) {
	    $tabela_bl_q[]=$cell->plaintext; // Display the contents of each cell - this is the value you want to extract
		}
  } 
	for ($i=13; $i<count($tabela_bl_q)-12;$i++)
   {
   $line=array($tabela_bl_q[$i+1],$tabela_bl_q[$i+2],$tabela_bl_q[$i+3],$tabela_bl_q[$i+4],$tabela_bl_q[$i+5],$tabela_bl_q[$i+6],$tabela_bl_q[$i+7],$tabela_bl_q[$i+8],$tabela_bl_q[$i+9],$tabela_bl_q[$i+10],$tabela_bl_q[$i+11],$tabela_bl_q[$i+12],$tabela_bl_q[$i+13]);
   fputcsv($file,$line);
   $i=$i+14;
   } 
   
//BL Y 
$tables = $html->find('table');
$rows = $tables[8]->find('tr'); //svi redovi druge tabele na html strani


foreach ($rows as $row) {
    foreach ($row->children() as $cell) {
	     $tabela_bl_y[]=$cell->plaintext; // Display the contents of each cell - this is the value you want to extract
		
    }
  }   
 for ($i=13; $i<count($tabela_bl_y)-12;$i++)
   {
   $line=array($tabela_bl_y[$i+1],$tabela_bl_y[$i+2],$tabela_bl_y[$i+3],$tabela_bl_y[$i+4],$tabela_bl_y[$i+5],$tabela_bl_y[$i+6],$tabela_bl_y[$i+7],$tabela_bl_y[$i+8],$tabela_bl_y[$i+9],$tabela_bl_y[$i+10],$tabela_bl_y[$i+11],$tabela_bl_y[$i+12],$tabela_bl_y[$i+13]);
   fputcsv($file,$line);
   $i=$i+14;
   }   
   
//Monthly data Peak  
$tables = $html->find('table');
$rows = $tables[5]->find('tr'); //svi redovi druge tabele na html strani 
  foreach ($rows as $row) {
    foreach ($row->children() as $cell) {
	     $tabela_month_peak[]=$cell->plaintext; // Display the contents of each cell - this is the value you want to extract
		
    }
	
}   

for ($i=13; $i<count($tabela_month_peak)-12;$i++)
   {
   $line=array($tabela_month_peak[$i+1],$tabela_month_peak[$i+2],$tabela_month_peak[$i+3],$tabela_month_peak[$i+4],$tabela_month_peak[$i+5],$tabela_month_peak[$i+6],$tabela_month_peak[$i+7],$tabela_month_peak[$i+8],$tabela_month_peak[$i+9],$tabela_month_peak[$i+10],$tabela_month_peak[$i+11],$tabela_month_peak[$i+12],$tabela_month_peak[$i+13]);
   fputcsv($file,$line);
   $i=$i+14;
   }  
   
 //PL Quater
$tables = $html->find('table');
$rows = $tables[7]->find('tr'); //svi redovi druge tabele na html strani 
  foreach ($rows as $row) {
    foreach ($row->children() as $cell) {
	      $tabela_month_pl_q[]=$cell->plaintext; // Display the contents of each cell - this is the value you want to extract
		 }
	
}   
 for ($i=13; $i<count($tabela_month_pl_q)-12;$i++)
   {
   $line=array($tabela_month_pl_q[$i+1],$tabela_month_pl_q[$i+2],$tabela_month_pl_q[$i+3],$tabela_month_pl_q[$i+4],$tabela_month_pl_q[$i+5],$tabela_month_pl_q[$i+6],$tabela_month_pl_q[$i+7],$tabela_month_pl_q[$i+8],$tabela_month_pl_q[$i+9],$tabela_month_pl_q[$i+10],$tabela_month_pl_q[$i+11],$tabela_month_pl_q[$i+12],$tabela_month_pl_q[$i+13]);
   fputcsv($file,$line);
   $i=$i+14;
   } 
 
  //PL Yearly
$tables = $html->find('table');
$rows = $tables[9]->find('tr'); //svi redovi druge tabele na html strani 
  foreach ($rows as $row) {
    foreach ($row->children() as $cell) {
		$tabela_month_pl_y[]=$cell->plaintext; // Display the contents of each cell - this is the value you want to extract	  
    }
} 
	for ($i=13; $i<count($tabela_month_pl_y)-12;$i++)
   {
   $line=array($tabela_month_pl_y[$i+1],$tabela_month_pl_y[$i+2],$tabela_month_pl_y[$i+3],$tabela_month_pl_y[$i+4],$tabela_month_pl_y[$i+5],$tabela_month_pl_y[$i+6],$tabela_month_pl_y[$i+7],$tabela_month_pl_y[$i+8],$tabela_month_pl_y[$i+9],$tabela_month_pl_y[$i+10],$tabela_month_pl_y[$i+11],$tabela_month_pl_y[$i+12],$tabela_month_pl_y[$i+13]);
   fputcsv($file,$line);
   $i=$i+14;
   } 
   
  fclose($file); 
  
//Ubaci weekly i dugorocne vrednosti u bazu
/*
$db_host	= "localhost";
$db_user	= "root";
$db_pass	= "";
$database	= "afel"; 
*/


$db_host	= "192.168.36.48";
$db_user	= "afel";
$db_pass	= "afel_afeldb_psw";
$database	= "afel"; 

$connect = mysql_connect($db_host,$db_user,$db_pass) or die ('Unable to connect to host!\r\n');
$db_select = mysql_select_db($database,$connect) or die ('Unable to connect to database!\r\n');

//$connect = mysqli_connect($db_host,$db_user,$db_pass,$database) or die ('Unable to connect to host!\r\n');
//$db_select = mysql_select_db($database,$connect) or die ('Unable to connect to database!\r\n');
//$db_select = mysqli_select_db($connect,$database) or die ('Unable to connect to database!\r\n');
$datetime=date('Y-m-d H:i:s');

      
  if (($handle = fopen("/home/afel/weekly_futures/power_daily_data_export_{$datum}.csv", "r")) !== FALSE) { // Check the resource is valid
    while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) { // Check opening the file is OK!
        
        for ($i = 0; $i < count($data); $i++) { // Loop over the data using $i as index pointer
            //echo $data[$i] . "<br />\n";
			
                      

			
			if(substr($data[$i],0,5)=="BL Wk" && substr($data[$i],0,7)!="BL Wknd" && substr($data[$i],8,2)==$skr_trenutna_god) {
				//echo "Nadjen je Base weekly element...";
				//echo '<br>';
                      $god=substr($data[$i],8,2);
                      echo "Skracena Godina je...".$god;
		              $godina="20$god";
                        //$godina="2019";

                    
                      echo "Godina je...".$godina;
			        $berza='HUDEX';
				    $produkt="BASE";
				//$godina=substr($datum,0,4);
 
				$settlement_price=$data[$i+10];
				$week=substr($data[$i],5,2);
				$week_column="W".$week;
			

             $result = mysql_query("SELECT * FROM `Weekly_futures` WHERE datum ='$datum' AND produkt='BASE' AND godina='$godina'");
				  if( mysql_num_rows($result) > 0) {
				   $sql="UPDATE `Weekly_futures` SET $week_column='$settlement_price' WHERE datum='$datum' AND produkt='BASE' AND godina='$godina'";
				  }
                else{
               $sql="INSERT INTO `Weekly_futures` (datum,berza,produkt,godina,$week_column,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";    
			     }
			  mysql_query($sql);
			  //mysqli_query($connect,$sql);
            
				//echo "Settlement price za Weekly unos je...".$settlement_price;
				//echo '<br>';
			}
          			if(substr($data[$i],0,5)=="BL Wk" && substr($data[$i],0,7)!="BL Wknd" && substr($data[$i],8,2)==$skr_sledeca_god) {
				//echo "Nadjen je Base weekly element...";
				//echo '<br>';
                      $god=substr($data[$i],8,2);
                    echo "Skracena Godina je...".$god;
		        $godina="20$god";
                        //$godina="2019";

                    
                      echo "Godina je...".$godina;
			        $berza='HUDEX';
				$produkt="BASE";
				//$godina=substr($datum,0,4);
 
				$settlement_price=$data[$i+10];
				$week=substr($data[$i],5,2);
				$week_column="W".$week;
			

             $result = mysql_query("SELECT * FROM `Weekly_futures` WHERE datum ='$datum' AND produkt='BASE' AND godina='$godina'");
				  if( mysql_num_rows($result) > 0) {
				   $sql="UPDATE `Weekly_futures` SET $week_column='$settlement_price' WHERE datum='$datum' AND produkt='BASE' AND godina='$godina'";
				  }
                else{
               $sql="INSERT INTO `Weekly_futures` (datum,berza,produkt,godina,$week_column,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";    
			     }
			  mysql_query($sql);
			  //mysqli_query($connect,$sql);
            
				//echo "Settlement price za Weekly unos je...".$settlement_price;
				//echo '<br>';
			}

			//za PL 
			
			if(substr($data[$i],0,5)=="PL Wk" && substr($data[$i],0,7)!="PL Wknd") {
				//echo "Nadjen je Peak Weekly element...";
				//echo '<br>';
			    $berza='HUDEX';
				$produkt="PEAK";
				$godina=substr($datum,0,4);
				$settlement_price=$data[$i+10];
				$week=substr($data[$i],5,2);
				$week_column="W".$week;
				
			 $result = mysql_query("SELECT * FROM `Weekly_futures` WHERE datum ='$datum' AND produkt='PEAK'");
				  if( mysql_num_rows($result) > 0) {
				   $sql="UPDATE `Weekly_futures` SET $week_column='$settlement_price' WHERE datum='$datum' AND produkt='PEAK'";
				  }
                else{
               $sql="INSERT INTO `Weekly_futures` (datum,berza,produkt,godina,$week_column,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";    
			     }
			  mysql_query($sql);
			  //mysqli_query($connect,$sql);
    
				//echo "Settlement price za Weekly unos je...".$settlement_price;
				//echo '<br>';
			}


             
  ///////////za BASE Y,Q,M jedan zapis u dugorocni_proizvodi
           if(substr($data[$i],3,2)=="YR" && substr($data[$i],0,2)=="BL") {
				
				$berza="HUDEX";
				$produkt="BASE";
			    $settlement_price=$data[$i+10];
		        $god=substr($data[$i],6,2);
		        $godina="20$god";
		         
				
				 //$result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE'");
				 $result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE' AND godina='$godina'") or die (mysql_error());
				  if( mysql_num_rows($result) > 0) {
				   $sql="UPDATE `dugorocni_proizvodi` SET Y='$settlement_price' WHERE datum='$datum' AND produkt='BASE' AND godina='$godina'";
				  }
                else{
               $sql="INSERT INTO `dugorocni_proizvodi` (datum,berza,produkt,godina,Y,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";     
			     }
			  mysql_query($sql);
			  //mysqli_query($connect,$sql);
				
			}
			//Quarter
			if(substr($data[$i],3,1)=="Q" && substr($data[$i],0,2)=="BL") {
				$berza="HUDEX";
				$produkt="BASE";
				$quarter=substr($data[$i],3,2);
			    $settlement_price=$data[$i+10];
				$god=substr($data[$i],6,2);
				$godina="20$god";
				
                
				
				//$result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE'");
				$result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE' AND godina='$godina'");
				  if( mysql_num_rows($result) > 0) {
					  $sql="UPDATE `dugorocni_proizvodi` SET $quarter='$settlement_price' WHERE datum='$datum' AND produkt='BASE' AND godina='$godina'";
				  }
                else{
			    $sql="INSERT INTO `dugorocni_proizvodi` (datum,berza,produkt,godina, $quarter,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";
                
			     }
				 mysql_query($sql);
				 //mysqli_query($connect,$sql);

			}
			//Monthly 
		     if(in_array(substr($data[$i],3,3),$month_case) && substr($data[$i],0,2)=="BL"){
				if(substr($data[$i],3,3)!==date('M',strtotime($datum))){
					//$month = date('n',strtotime(substr($data[$i],3,3)));
					$month = substr($data[$i],3,3);
					$produkt="BASE";
					$god=substr($data[$i],7,2);
				    $godina="20$god";
					$settlement_price=$data[$i+10];
                    $month_number = date("n",strtotime($month));
					
                    
					
					$result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE' AND godina='$godina'");
					//$result = mysqli_query($connect,"SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE'");
				    if( mysql_num_rows($result) > 0) {
						$sql="UPDATE `dugorocni_proizvodi` SET M$month_number='$settlement_price' WHERE datum ='$datum' AND produkt='BASE' AND godina='$godina'";
				       
				     }
                   else{
					   $sql="INSERT INTO `dugorocni_proizvodi` (datum,berza,produkt,godina, M$month_number,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";
                  
			        }
				   mysql_query($sql);
				   //mysqli_query($connect,$sql);
				}
					
			}
			
			

/////////////////// PEAK 
            if(substr($data[$i],3,2)=="YR" && substr($data[$i],0,2)=="PL") {
				
				$berza="HUDEX";
				$produkt="PEAK";
			    $settlement_price=$data[$i+10];
		        $god=substr($data[$i],6,2);
		        $godina="20$god";
		         
				
				 //$result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE'");
				 $result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='PEAK' AND godina='$godina'") or die (mysql_error());
				  if( mysql_num_rows($result) > 0) {
				   $sql="UPDATE `dugorocni_proizvodi` SET Y='$settlement_price' WHERE datum='$datum' AND produkt='PEAK' AND godina='$godina'";
				  }
                else{
               $sql="INSERT INTO `dugorocni_proizvodi` (datum,berza,produkt,godina,Y,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";     
			     }
			  mysql_query($sql);
			  //mysqli_query($connect,$sql);
				
			}
			//Quarter
			if(substr($data[$i],3,1)=="Q" && substr($data[$i],0,2)=="PL") {
				$berza="HUDEX";
				$produkt="PEAK";
				$quarter=substr($data[$i],3,2);
			    $settlement_price=$data[$i+10];
				$god=substr($data[$i],6,2);
				$godina="20$god";
				
                
				
				//$result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE'");
				$result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='PEAK' AND godina='$godina'");
				  if( mysql_num_rows($result) > 0) {
					  $sql="UPDATE `dugorocni_proizvodi` SET $quarter='$settlement_price' WHERE datum='$datum' AND produkt='PEAK' AND godina='$godina'";
				  }
                else{
			    $sql="INSERT INTO `dugorocni_proizvodi` (datum,berza,produkt,godina, $quarter,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";
                
			     }
				 mysql_query($sql);
				 //mysqli_query($connect,$sql);

			}
			//Monthly 
		     if(in_array(substr($data[$i],3,3),$month_case) && substr($data[$i],0,2)=="PL"){
				if(substr($data[$i],3,3)!==date('M',strtotime($datum))){
					$month = substr($data[$i],3,3);
					$produkt="PEAK";
					$god=substr($data[$i],7,2);
				    $godina="20$god";
					$settlement_price=$data[$i+10];
                    $month_number = date("n",strtotime($month));
					
                    
					
					$result = mysql_query("SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='PEAK' AND godina='$godina'");
					//$result = mysqli_query($connect,"SELECT * FROM `dugorocni_proizvodi` WHERE datum ='$datum' AND produkt='BASE'");
				    if( mysql_num_rows($result) > 0) {
						$sql="UPDATE `dugorocni_proizvodi` SET M$month_number='$settlement_price' WHERE datum ='$datum' AND produkt='PEAK' AND godina='$godina'";
				       
				     }
                   else{
					   $sql="INSERT INTO `dugorocni_proizvodi` (datum,berza,produkt,godina, M$month_number,datetime) VALUES ('$datum','$berza','$produkt','$godina','$settlement_price','$datetime')";
                  
			        }
				   mysql_query($sql);
				   //mysqli_query($connect,$sql);
				}
        }
    }
    

}


  
 fclose($handle);
}
 
$file="power_daily_data_export_{$datum}.csv";
echo "Ime fajla je..".$file;
$directory = "/home/afel/weekly_futures/";
$file2 = $directory.$file;

$archive = "/home/afel/files/marko/weekly_futures/$file";

copy($file2,$archive)or die("Unsuccessufully copy to file_archive!\r\n");
unlink($file2);


?>
