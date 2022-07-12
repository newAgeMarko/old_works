<?php

/* da se podaci sa izvora preuzimaju uvek iz poslednje verzije dostavljenog fajla, ne uzimajuci u obzir kada je fajl poslat. Provera da li je nova verzija dostavljena vrsi se 20 i poslednjeg dana u mesecu u 23h. Na EMFIP se dostavljaju fajlovi po mesecima u skladu sa dostavljenim vrednostima. */
// POKRRETANJE 20TGO I POSLEDNJEG DANA U 23H
// AKO POSTOJE VREDNOSTI RADI UPDATE - AKO NE INSERT
// DOSTAVITI NA EMFIP KREIRANE FAJLOVE PO MESECIMA


ini_set('max_execution_time', 300);
ini_set('/');
ini_set('smtp_port',25);
include('PhpImap\Mailbox.php');


echo "START: ".date('Y-m-d H:i:s')."\r\n";

function weekOfMonth($datum) {
	$week = date('W', strtotime($datum)); 
	//echo "WEEK IS :".$week. "\r\n";
	$firstMondayOfMonth = date('W', strtotime('Monday'.date('F o',strtotime($datum))));
	//echo "firstMondayOfMonth IS :".$firstMondayOfMonth. "\r\n";
	$firstMondayOfPastMonth = date('W', strtotime('Monday'.date('F o',strtotime("$datum - 1month"))));
	//echo "firstMondayOfPastMonth IS :".$firstMondayOfPastMonth. "\r\n";
	$firstWeekOfMonth = date('W', strtotime(date('Y-m-01',strtotime($datum))));
	//echo "firstWeekOfMonth IS :".$firstWeekOfMonth. "\r\n";
	return 0 + ($week <= $firstWeekOfMonth ? $week-$firstMondayOfPastMonth : $week - $firstMondayOfMonth);
}


$file_name='monthly_load_forecast_2022_'.date('m',strtotime('+1month')).'.xml';

$found_mail=0;
$found_data = 0;
$query_check = 0;
$status_xml='false';
$datetime = date('Y-m-d H:i:s');
$version=1;
$count=1;


$server_name="192.168.25.33";
$server_database = "transparency";
$server_username = "newemf164";
$server_password = " newemfip2016_04----";

$connect=mysql_connect($server_name,$server_username,$server_password) or die ("Server error : Server information is wrong.\r\n");   
if($connect){
	echo "DB connect OK.\r\n";
}
$db=mysql_select_db($server_database,$connect) or die ("Database not found in your server.\r\n");


$select="SELECT * FROM monthly_load_forecast_status ORDER BY(datetime)";
$query=mysql_query($select,$connect) or die (mysql_error());

$data_check=array();
$data_check=mysql_fetch_assoc($query);

$select1="SELECT * FROM monthly_load_forecast WHERE DT_WEEK='".date('Y-m-d',strtotime('first day of next month'))."'";
$query1=mysql_query($select1,$connect) or die (mysql_error());
$data=mysql_fetch_assoc($query1);


if(mysql_num_rows($query1)!==0) { 
	$found_data=1; 
	echo "Postoje podaci u bazi za mesec: ".date('Y-m',strtotime('+1month'))."\r\n"; 
}


	 if ((mysql_num_rows($query1)==0) OR (mysql_num_rows($query1)!==0 AND date('m',strtotime($data['datetime']))!==date('m'))){ 
		// AKO NEMA PODATAKA ILI POSTOJE PODACI I NISU UNETI OVOG MESECA, PROVERI MEJL I UPISI PODATKE AKO NADJES 
		echo "Nema podataka u bazi, ili ih ima ali poslati prethodnih meseci. \r\n";
		
		$mailbox = new PhpImap\Mailbox('{192.168.36.55:995/pop3/ssl/novalidate-cert}Inbox', 'EMS/emfip.prepare', 'Priprema16', '\\\\192.168.25.29\\month_ahead_total_load_forecast_v2');
		// PROVERA MEJL

		$from = DATE('Y-m-01');
		
		$senders=array("marija.djordjevic@ems.rs", "irena.savkovic@ems.rs");
		foreach($senders as $sender){
			$mailsIds = $mailbox->searchMailbox('FROM "'.$sender.'" SINCE "'.$from.'" SUBJECT "monthlyloadforecast"');
			if(!$mailsIds) {
				echo "Odgovarajuci mejl nije poslat.-$sender \r\n";
			}else {
				echo "Pronadjen odgovarajuci mejl. Poslao: ".$sender."\r\n";
				$found_mail=1;
				$mail = $mailbox->getMail($mailsIds[key($mailsIds)]);
				var_dump($mail->getAttachments());
				end($mailsIds);
			}
		}

		if ($found_mail==0 AND mysql_num_rows($query1)==0 and date('d')== ('29')){  
			// PROVERA U SLUCAJU DA NEMA FAJLA I DA NE POSTOJI UPIS U TABELU MAKAR ZA 1 SLEDECI MESEC
			echo "Saljem mejl obavestenja o nedostavljanju fajla. \r\n";
			$to= "/"; 
			
			$from = "automatsko_obavestenje_o_nedostavljenosti_fajla_monthlyloadforecast@ems.rs";
			$subject = "Obavestenje o nedostavljenosti fajla 'monthlyloadforecast'";

			$message = "<body style=\"font-family:Calibri, Verdana, Geneva, sans-serif; \">";
			$message= "Postovani, <br />";
			$message.= "<br> Fajl <b> monthlyloadforecast </b> nije dostavljen ovog meseca. <br /><br /><br />";
			$message.= "Datum: ". DATE('d.m.Y'). "    Vreme: ".DATE('H:i:s')."<br />";
			$message.= "Poruka je automatski generisana, nemojte odgovarati na istu.";
			$message.= "</body>";
			$headers = "From:" . $from . "\r\n". "CC: ";  // irena.savkovic@ems.rs, kovica.bibic@ems.rs
			$headers.= "\r\nContent-Type: text/html";

			$send_mail = mail($to,$subject,$message,$headers);
			if ($send_mail) {
				echo "Mail obavestenja poslat to: ".$to."!\r\n";
			}else{
				echo "Mail could not be sent!\r\n";
			}
		}


$directory='';
$csv_file_number = count(glob($directory . "*.csv"));

// VRSIM UPIS PODATAKA AKO SAM PRONASAO EMAIL
if($found_mail == 1 OR $csv_file_number >= 1) {

$count = 1;
$i = 0;
foreach (new DirectoryIterator($directory) as $fileInfo) {
	if ($fileInfo->isDot()) continue;
	if ($fileInfo->isFile()) {
		$imecsvfajla[$i] = $fileInfo->getFilename();

		$allowed_extensions = array('csv');
		$file = explode(".", $imecsvfajla[$i]);    //provera csv ekstenzije
		$extension = array_pop($file);                //array_pop - Pop the element off the end of array
		if (($csv_file_number == 1) and in_array($extension, $allowed_extensions)) {
			echo "Pronadjen fajl " . $imecsvfajla[$i] . ".\r\n";
			//arhiviranje originalnog fajla
			$originalfajl[$i] = $directory . $imecsvfajla[$i];
			$archivefajl[$i] = $directoryarchive . $imecsvfajla[$i];
			copy($originalfajl[$i], $archivefajl[$i]);
			$read = fopen($originalfajl[$i], 'r');
			if ($read) {
				$line = 1;
				while (($data = fgetcsv($read)) !== FALSE) {
					if (isset($data[0]) and ($line == 1)) {
						if (!(trim($data[0]) == "DT_WEEK" and trim($data[1]) == "FCSTE_WEEK" and trim($data[2]) == "MIN(FCLD_HOUR)" and trim($data[3]) == "MAX(FCLD_HOUR)")) {
							die("Neispravan csv fajl.\r\n");
						}
					}

			
			if (isset($data[0]) and ($line > 1)){
		
				switch (substr(trim($data[0]), 3, 3)) {
					case 'Jan': $mesec = "01"; break;
					case 'Feb': $mesec = "02"; break;
					case 'Mar': $mesec = "03"; break;
					case 'Apr': $mesec = "04"; break;
					case 'May': $mesec = "05"; break;
					case 'Jun': $mesec = "06"; break;
					case 'Jul': $mesec = "07"; break;
					case 'Aug': $mesec = "08"; break;
					case 'Sep': $mesec = "09"; break;
					case 'Oct': $mesec = "10"; break;
					case 'Nov': $mesec = "11"; break;
					case 'Dec': $mesec = "12"; break;
					default: $mesec = "XX";
				}
				$dan = substr(trim($data[0]), 0, 2);
				$godina = substr(trim($data[0]), 7, 4);
				$datum = $godina.'-'.$mesec.'-'.$dan;
				$datum = date("Y-m-d", strtotime($datum));
				$month=date('m',strtotime($datum));
				
				$select="SELECT * FROM monthly_load_forecast WHERE DT_WEEK='".$datum."'"; //PROVERA DA LI POSTOJI ZAPIS ZA TAJ PERIOD u BAZI
				$query=mysql_query($select,$connect) or die (mysql_error());
				
				if($month >= date('m') AND $month<= date('m',strtotime('+4month'))){ // PROMENITI USLOV DA UPISE DO 3+meseca i zadnje nedelje u tom mesecu 
				
					$found_data=1;
					$week_info = weekOfMonth($datum);
					
		
					if(mysql_num_rows($query)==0){
						$query_check=1;
						$import="INSERT INTO monthly_load_forecast (DT_WEEK, FCSTE_WEEK, MIN_FCLD_HOUR, MAX_FCLD_HOUR, datetime, week) values('$datum', $data[1], $data[2], $data[3], '$datetime', $week_info)";
						mysql_query($import,$connect) or die(mysql_error());
					} else {
						$query_check=1;
						$update="UPDATE monthly_load_forecast SET FCSTE_WEEK='".$data[1]."', MIN_FCLD_HOUR='".$data[2]."', MAX_FCLD_HOUR='".$data[3]."', week='".$week_info."' datetime='".$datetime."' WHERE DT_WEEK='".$datum."'";
						mysql_query($update,$connect) or die(mysql_error());
						
					}
				}
			}
			$line++;
			}
		}
		fclose($read);
		unlink($originalfajl[$i]);
	}
		$brisati_fajl = $directory . $imecsvfajla[$i];
		unlink($brisati_fajl);
		$i++;
	}
	}
}
}else{
	echo "Ne postoje podaci u bazi. \r\n";
};



if($found_data==1 AND $query_check==1){ echo "Izvrsen upis podataka.\r\n"; }
if($found_data==0 AND $query_check==0){ echo "Ne postoje podaci! \r\n"; }

if($found_data==1){
echo "START CREATE XML: ".date('Y-m-d H:i:s')."\r\n";//vreme kraja izvrsenja prvog dela skripte skripte i pocetka izvrsenja drugog dela skripte

$godina1 = date('Y');
$mesec1 = date('m',strtotime('+1month'));

$datum1=date($godina1."-".$mesec1."-".date('d',strtotime('first Monday of next Month')));
//echo "237: First monday of next month".$datum1;

$datum2=date($godina1."-".$mesec1."-".date('d',strtotime('last day of next Month')));
//echo "240 last day of next Month is:".$datum1.'\r\n';
$datum2=date('Y-m-d',strtotime('Sunday this week',strtotime($datum2)));



echo "First monday of next month: ".$datum1."\r\n";
echo "Last Sunday of next month: : ".$datum2."\r\n";


$directory = "E:/month_ahead_total_load_forecast_v2/". $file_name;;
$directory_upload = "\\\\local\\xml_for_upload\\" . $file_name;
$directory_archive = "E:/month_ahead_total_load_forecast_v2/archive/". $file_name;;


$query_recSetFilter = "SELECT min(MIN_FCLD_HOUR) as minimum, max(MAX_FCLD_HOUR) as maximum, week FROM monthly_load_forecast WHERE DT_WEEK >= '".$datum1."' and DT_WEEK < '".$datum2."' group by week";

$recSetFilter = mysql_query($query_recSetFilter, $connect) or die(mysql_error());

$totalRows_recSetFilter = mysql_num_rows($recSetFilter);
if ($totalRows_recSetFilter != 0) {
	$file= fopen("m_l_f.xml", "w");
	$_xml ="<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\r\n";
	$_xml .="<GL_MarketDocument xmlns=\"urn:iec62325.351:tc57wg16:451-6:generationloaddocument:3:0\">\r\n";
	$_xml .="\t<mRID>monthly_load_forecast_".$godina1."_".$mesec1."</mRID>\r\n";
	$_xml .="\t<revisionNumber>"."1"."</revisionNumber>\r\n";
	$_xml .="\t<type>A65</type>\r\n";
	$_xml .="\t<process.processType>A32</process.processType>\r\n";
	$_xml .="\t<sender_MarketParticipant.mRID codingScheme=\"A01\">10XCS-SERBIATSO8</sender_MarketParticipant.mRID>\r\n";
	$_xml .="\t<sender_MarketParticipant.marketRole.type>A04</sender_MarketParticipant.marketRole.type>\r\n";
	$_xml .="\t<receiver_MarketParticipant.mRID codingScheme=\"A01\">10X1001A1001A450</receiver_MarketParticipant.mRID>\r\n";
	$_xml .="\t<receiver_MarketParticipant.marketRole.type>A32</receiver_MarketParticipant.marketRole.type>\r\n";
	$_xml .="\t<createdDateTime>". DATE('Y-m-d') . "T" . DATE('H:i:s') ."Z</createdDateTime>\r\n";
	$TimeIntervalod = date('Y-m-d', strtotime("$datum1-1 day"));
	$TimeIntervaldo = $datum2;
	if (date("I", strtotime($TimeIntervalod . date(" 22:00:00")))){$hour1="T22:00Z";}else{$hour1="T23:00Z";} 
	if (date("I", strtotime($TimeIntervaldo . date(" 22:00:00")))){$hour2="T22:00Z";}else{$hour2="T23:00Z";}
	$_xml .="\t<time_Period.timeInterval>\r\n";
	$_xml .="\t\t<start>". $TimeIntervalod . $hour1 ."</start>\r\n";
	$_xml .="\t\t<end>".   $TimeIntervaldo . $hour2 ."</end>\r\n";	
	$_xml .="\t</time_Period.timeInterval>\r\n";
		// MINIMUM
		$_xml .="\t<TimeSeries>\r\n";
		$_xml .="\t\t<mRID>"."1"."</mRID>\r\n";
		$_xml .="\t\t<businessType>A60</businessType>\r\n";
		$_xml .="\t\t<objectAggregation>A01</objectAggregation>\r\n";
		$_xml .="\t\t<outBiddingZone_Domain.mRID codingScheme=\"A01\">10YCS-SERBIATSOV</outBiddingZone_Domain.mRID>\r\n";
		$_xml .="\t\t<quantity_Measure_Unit.name>MAW</quantity_Measure_Unit.name>\r\n";
		$_xml .="\t\t<curveType>A01</curveType>\r\n";
		$_xml .="\t\t<Period>\r\n";
		$_xml .="\t\t\t<timeInterval>\r\n";
		$_xml .="\t\t\t\t<start>". $TimeIntervalod . $hour1 ."</start>\r\n";
		$_xml .="\t\t\t\t<end>".   $TimeIntervaldo . $hour2 ."</end>\r\n";
		$_xml .="\t\t\t</timeInterval>\r\n";
		$_xml .="\t\t\t<resolution>P7D</resolution>\r\n";
		$i=0;
	while ($row = mysql_fetch_assoc($recSetFilter)) {
		if ($row["minimum"]) {
				$maximum[$i] = round($row["maximum"]);
				$i++;
				$_xml .="\t\t\t<Point>\r\n";
				$_xml .="\t\t\t\t<position>".($row["week"]+1)."</position>\r\n";
				$_xml .="\t\t\t\t<quantity>".round($row["minimum"])."</quantity>\r\n";
				$_xml .="\t\t\t</Point>\r\n";
		}else{
			$_xml .="\t<DATE TIME=\"Nothing Returned\">\r\n";
			$_xml .="\t\t<FILE>empty</FILE>\r\n";
			$_xml .="\t</DATE>\r\n";
		}
	}
		$_xml .="\t\t</Period>\r\n";
		$_xml .="\t</TimeSeries>\r\n";
		// MAXIMUM
		$_xml .="\t<TimeSeries>\r\n";
		$_xml .="\t\t<mRID>"."2"."</mRID>\r\n";
		$_xml .="\t\t<businessType>A61</businessType>\r\n";
		$_xml .="\t\t<objectAggregation>A01</objectAggregation>\r\n";
		$_xml .="\t\t<outBiddingZone_Domain.mRID codingScheme=\"A01\">10YCS-SERBIATSOV</outBiddingZone_Domain.mRID>\r\n";
		$_xml .="\t\t<quantity_Measure_Unit.name>MAW</quantity_Measure_Unit.name>\r\n";
		$_xml .="\t\t<curveType>A01</curveType>\r\n";
		$_xml .="\t\t<Period>\r\n";
		$_xml .="\t\t\t<timeInterval>\r\n";
		$_xml .="\t\t\t\t<start>". $TimeIntervalod . $hour1 ."</start>\r\n";
		$_xml .="\t\t\t\t<end>".   $TimeIntervaldo . $hour2 ."</end>\r\n";
		$_xml .="\t\t\t</timeInterval>\r\n";
		$_xml .="\t\t\t<resolution>P7D</resolution>\r\n";
	for ($k = 0; $k<$i; $k++){
			$_xml .="\t\t\t<Point>\r\n";
			$_xml .="\t\t\t\t<position>".($k+1)."</position>\r\n";
			$_xml .="\t\t\t\t<quantity>".$maximum[$k]."</quantity>\r\n";
			$_xml .="\t\t\t</Point>\r\n";
	}
		$_xml .="\t\t</Period>\r\n";
		$_xml .="\t</TimeSeries>\r\n";
	$_xml .="</GL_MarketDocument>";
	fwrite($file, $_xml) or die("Upis podataka na fajl nije moguc!\r\n");
	fclose($file) or die("fajl nije moguce zatvoriti!\r\n");
	sleep(1);
	
	
	rename("m_l_f.xml", $file_name)  or die("Rename nije moguc!\r\n");
	copy($directory, $directory_upload)  or die("Copy to upload nije moguc!\r\n");
	rename($file_name, $directory_archive) or die("Copy to archive nije moguc!\r\n");
	echo "XML je formiran.\r\n";

}else{
	echo "Nema pronadjenih zapisa!\r\n";
} 
}
echo "END: ".date('Y-m-d H:i:s')."\r\n";
echo "----------------------------\r\n";


?>