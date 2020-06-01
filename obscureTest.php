<?php
//ini_set('display_errors',1);
//error_reporting(E_ALL);
//error_reporting(0);

$arrayOfPathToThisFile = explode("/", __DIR__); //produces an array of the path to this file (not the simlink to this file)
$dir = end($arrayOfPathToThisFile)."/"; //produces the parent dir of this file
include_once("/var/monytalyData/".$dir."globals.php");
include_once("./".$subdir."php/funcsForAccCcc.php");
include_once("./".$subdir."php/funcsToRdWrTblesForAccCcc.php");

/* Global section for setting various constants */
$_fileRequestTimeLimSecs = 3; //time in seconds for this script to be called following the last call to indexPage.php that would have set the fileNameNum data in the personSession table.
$_cookieName = "ID";
/* End of global section for setting various constants */

/* Initialisation of global variables */
$clientSessCookie = "";
$userId = 0;
$message = ""; //message for various pages or exit conditions
/* End of initialisation of global variables  */

if(isset($_COOKIE[$_cookieName])) {  //if the specifically named cookie has been received from client the value is placed in $clientSessCookie, otherwise the value is 0
    $clientSessCookie = $_COOKIE[$_cookieName];
}

//SECTION DEALING WITH NO COOKIE FROM CLIENT (This 'if' section always terminates internally with an exit("") )
if (!$clientSessCookie) { //no session cookie go to login page
	$message = "Logged Out -  mandatory cookie for obscureTest.php not supplied";
	saveMessage($message." Cookie: ".$clientSessCookie);
	include_once("./".$subdir."login.php");
	exit("");
}

//SECTION DEALING WITH COOKIE FROM CLIENT
$userId = getUserIdfromCookie($clientSessCookie); //uses cookie to get user id from personSession table, 0 returned if no match is found
if (!$userId) {  //if user id doesn't exist go to login page
	$message = "Logged Out -  Cookie for obscureTest.php, but not current for any user";
	saveMessage($message." Cookie: ".$clientSessCookie);
	deleteCookieOnClient($_cookieName);
	include_once("./".$subdir."login.php");
	exit("");
}

//VALID USER SO CHECK THAT TOO MUCH TIME HASN'T PASSED SINCE FILE NAME WAS SET IN personSession TABLE READY FOR REQUESTING
if (inactiveTimeout($userId, $_fileRequestTimeLimSecs)) { //if more than the set maximum time since last user activity has passed

	//THIS SECTION IS MEANT TO SHOW A DEFAULT PDF THAT SAYS SOMETHING LIKE"DOC REQUEST TIMEOUT" BUT THAT MAKES THE NEXT LOGIN SECTION SUPERFLUOUS - NEED TO MAKE A DECISIOIN !!!!!
	$fileName = "2000-01-01-2.pdf";
	$fileNameDateNumArray = (explode("-", $fileName)); //split file into array of [0] YYYY, [1] MM, [2] DD [3] NUM [4] SEPARATOR (.) [5] EXTENSION
	$docDir = $fileNameDateNumArray[0]."-".$fileNameDateNumArray[1]; //assemble year-month i.e. 2018-03 for use as subfolder name
	$filePath = $_fileUploadsDir."/".$docDir."/".$fileName;
	downloadPdfFile($filePath);
	exit("");

	$message = "obscureTest.php waiting for file request for Too Long - Please Login Again !";
	saveMessage($message." Cookie: ".$clientSessCookie);
	clearSession($userId, $message);
	include_once("./".$sdir."login.php");
	exit("");
}

$recoveredSessionArrays = recoveredmenuRandomsArray($userId); //get session array of arrays from personSession table
if (array_key_exists ("nonVolatileArray", $recoveredSessionArrays)) { //recover nonVolatileArray array
	$nonVolatileArray = $recoveredSessionArrays["nonVolatileArray"];
}
$fileName = $nonVolatileArray["docNameNumStr"];


if (!$fileName) {
	$fileName = "2000-01-01-1.pdf";
}

$fileNameDateNumArray = (explode("-", $fileName)); //split file into array of [0] YYYY, [1] MM, [2] DD [3] NUM [4] SEPARATOR (.) [5] EXTENSION
$docDir = $fileNameDateNumArray[0]."-".$fileNameDateNumArray[1]; //assemble year-month i.e. 2018-03 for use as subfolder name
$filePath = $_fileUploadsDir."/".$docDir."/".$fileName;

downloadPdfFile($filePath);
exit("");
?>
