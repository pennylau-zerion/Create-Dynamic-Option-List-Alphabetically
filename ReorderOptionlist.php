<?php
header('Access-Control-Allow-Methods: GET, POST');
header("Access-Control-Allow-Headers: X-Requested-With");

require_once "iFormTokenResolver.php";
/*use iForm\Auth\iFormTokenResolver;*/

//::::::::::::::  DECLARE VARIABLES   ::::::::::::::
$recordList = json_decode(file_get_contents("php://input"), true);
$recordInfo = array_change_key_case($recordList[0]["record"], CASE_LOWER);

$servername = "XXX";
$profileID = "XXX"; 
$optionlistID = "XXX";
$student_name_country = trim($recordInfo['student_name'])."_".trim($recordInfo['country']);
$student_name_label = trim($recordInfo['student_name'])."(".trim($recordInfo['country']).")";



$url = 'https://'.$servername.'.zerionsandbox.com/exzact/api/oauth/token';
$client = 'XXXX';
$secret = 'XXXX';

// Couldn't wrap method call in PHP 5.3 so this has to become two separate variables
$tokenFetcher = new iFormTokenResolver($url, $client, $secret);
$token = $tokenFetcher->getToken();
echo $token;



/*  Insert New Option  */

$new_option = array ();
$new_option["key_value"] = $student_name_country;
$new_option["label"] = $student_name_label;
$new_option["sort_order"] = "0";
$new_option = json_encode($new_option);;

var_dump($new_option);

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, 'https://'.$servername.'.zerionsandbox.com/exzact/api/v60/profiles/'.$profileID.'/optionlists/'.$optionlistID.'/options');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

curl_setopt($ch, CURLOPT_POST, TRUE);

curl_setopt($ch, CURLOPT_POSTFIELDS, $new_option);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-Type: application/json",
  "Authorization: Bearer " .$token
));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

var_dump($response);



/*  Get All Options in an Option List; Check if more than 1000 */

    $myObj=[];
    $length_start=0;
    $i=0;

do {
    $url = 'https://'.$servername.'.zerionsandbox.com/exzact/api/v60/profiles/'.$profileID.'/optionlists/'.$optionlistID.'/options?fields=label&limit=1000&offset='.$length_start;
    $ch = curl_init("$url");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
   "Authorization: Bearer " .$token
    ));
    $json_options = curl_exec($ch);
    curl_close($ch);
    $length=count(json_decode($json_options,TRUE));
     $myObj = array_merge($myObj,json_decode($json_options,TRUE));
    $i=$i+1;
    $length_start=$i*1000;

} while ($length==1000);
//var_dump($myObj);
   

/*

$ch = curl_init();
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_URL, 'https://'.$servername.'.zerionsandbox.com/exzact/api/v60/profiles/'.$profileID.'/optionlists/'.$optionlistID.'/options?fields=label');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($ch, CURLOPT_HEADER, FALSE);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Authorization: Bearer " .$token
));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response1 = curl_exec($ch);
curl_close($ch);


$response1 = json_decode($response1,true);
//var_dump($response1);

*/

/*  Re Order Option List Alphabetically   */


//sort($myObj);
usort($myObj,function($a,$b) {return strnatcasecmp($a['label'],$b['label']);});

foreach ($myObj as $key => $val) {
  $k=array_search($val, $myObj); 
  $myObj[$k]['sort_order']=$k;
}

$myObj = json_encode($myObj);;
var_dump($myObj);


/*  Re Insert Options   */

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://'.$servername.'.zerionsandbox.com/exzact/api/v60/profiles/'.$profileID.'/optionlists/'.$optionlistID.'/options');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, $myObj);

curl_setopt($ch, CURLOPT_HTTPHEADER, array(
  "Content-Type: application/json",
  "Authorization: Bearer " .$token
));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response2 = curl_exec($ch);
curl_close($ch);

//var_dump($response2);



?>
