<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//$thisFileName = "ajaxAtomic.php";
//saveMessage($thisFileName);

/* This function is intended to be called from Javascript. Using the input array a value is written to the table the same field is read and echoed back to the calling javascript function using the output array, as a confirmation that the operation has suceeded. */


$inputArry = json_decode(htmlspecialchars_decode(sanPost("arryJsonStr")), TRUE); //convert JSON string of javascript object into associative array (associative indicated by TRUE)
$outputArry = array();
$outputArry["NAME"] = "arryBackFromPhp";


$outputArry = createNewParent($inputArry, $outputArry, $allowedToEdit);

$outputArry = writeReadAllRecordsItem($inputArry, $outputArry, $allowedToEdit);

$outputArry = updateWithdrawnPaidin($inputArry, $outputArry, $allowedToEdit);

$outputArry = updateEditableItem($inputArry, $outputArry, $allowedToEdit); //for updating reference or notes cell

$outputArry = getFilterStrAllBalData($inputArry, $outputArry, $nonVolatileArray["filtersAry"]["filterStr"], $nonVolatileArray["familySetting"]); //uses reconciled dates for calculation

$outputArry = updateDocFilename($inputArry, $outputArry, $allowedToEdit);

//$outputArry["PLAIN-allrecordsColNameRnd"] = $inputArry["allrecordsColNameRnd"];


include_once("./".$sdir."saveSession.php");

echo json_encode($outputArry); //convert php output array back into a JSON string javascript object and echo back to the calling js function

?>



