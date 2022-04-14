<?php
ini_set("soap.wsdl_cache_enabled", "0");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once('./soapclient/SforceEnterpriseClient.php');
require_once('./sfphptest-connection.php');
$mySforceConnection = new SforceEnterpriseClient();
$mySoapClient = $mySforceConnection->createConnection("./soapclient/enterprise.wsdl.xml");
$mySforceConnection->login(USERNAME, PASSWORD.SECURITY_TOKEN);


$curr_timestamp = date('Y-m-d');

$query = "SELECT Formateurpicklist__c, CERT_Formation__c, Id, Langue__c, Produit__r.Name, Produit__r.DescriptionEng__c, Salle__c, date_jour_1__c, Dernier_jour__c, Name, Nombre_de_jours_auto__c,Bureau_Automatique__c, Bureau_Automatique_ENG__c from Classe__c where Statut__c = 'Ouverte' AND Salle__c NOT IN ('Web prive','Chez le client','Montreal prive','Quebec prive','Nashua prive') AND Produit__r.DescriptionEng__c NOT IN ('Custom Training (USA)','Custom Training') AND Langue__c = 'English' AND date_jour_1__c >= ".$curr_timestamp." order by date_jour_1__c";

$response = $mySforceConnection->query(utf8_encode($query));									
									
//$line = '';
$line = "Id, Title, Location, Start Date, Duration (days)"."\r\n";
//$line .= "\xEF\xBB\xBF";
foreach ($response->records as $record) {

$line .= '"'.$record->Id.'"'.",";

if ($record->Langue__c =='English'){
	if ($record->CERT_Formation__c !=''){
	$line .= '"'.str_ireplace("solidworks", "SOLIDWORKS", $record->CERT_Formation__c).'"'.",";
	//$line .= '"'.$record->CERT_Formation__c.'"'.",";
	}else{
	$line .= '"'.str_ireplace("solidworks", "SOLIDWORKS", $record->Produit__r->DescriptionEng__c).'"'.",";
	//$line .= '"'.$record->Produit__r->DescriptionEng__c.'"'.",";
	}
	
}else{
	
	if ($record->CERT_Formation__c !=''){
	$line .= '"'.str_ireplace("solidworks", "SOLIDWORKS", $record->CERT_Formation__c).'"'.",";
	//$line .= '"'.$record->CERT_Formation__c.'"'.",";
	}else{
	$line .= '"'.str_ireplace("solidworks", "SOLIDWORKS", $record->Produit__r->Name).'"'.",";
	//$line .= '"'.$record->Produit__r->Name.'"'.",";
	}
	
}

//on truncate le nom de la salle au premier mot. ie: "Montr al" au lieu de "Montr al conf"
$arr = explode(' ',trim($record->Salle__c));
//$line .= '"'.$arr[0].'"'.",";

if(strpos(strtolower($arr[0]), 'web') !== false) {
$line .= '"'.$arr[0].'"'.",";
}else{
$line .= '"'.$arr[0]." & Web".'"'.",";
}

//Date de debut
$line .= '"'.$record->date_jour_1__c.'"'.",";

//Duree de la formation
$line .= '"'.$record->Nombre_de_jours_auto__c.'"';

$line .= "\r\n";
}


file_put_contents('formations-en.csv', $line );
print "<pre>".$line."</pre>";

?>