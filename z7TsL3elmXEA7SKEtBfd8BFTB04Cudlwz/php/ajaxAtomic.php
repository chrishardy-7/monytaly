<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//$thisFileName = "ajaxAtomic.php";
//saveMessage($thisFileName);

/* This function is intended to be called from Javascript. Using the input array a value is written to the table the same field is read and echoed back to the calling javascript function using the output array, as a confirmation that the operation has suceeded. */

//these objects are instantiated to allow the use of their respective 'get and 'isSet' methods by the functions and if statements on this page - buttons will not be drawn!
$showFamBut = new toggleBut("Show Families", "fas fa-plus-square", "subMenuBtn", "subMenuBtnSel", ($subCommand == "FromMainMenu"));
$editFamBut = new toggleBut("Family Edit", "fas fa-users", "subMenuBtn", "subMenuBtnSel", ($subCommand == "FromMainMenu"));
$famId = new persistVar("famId", 0); //holds the 2 possible states derived from the family column - "" or an id number for the family that has been clicked
$genFilter = new filterColumns("genFilter"); //filter from showRecsForFullYr.php for column filtering
$familyCmnd = new persistVar("familyCmnd"); //from showRecsForFullYr.php, holds family settings - "NoKids", "All" or the family id for the displayed family (e.g. 325)


$inputArry = json_decode(htmlspecialchars_decode(sanPost("arryJsonStr")), TRUE); //convert JSON string of javascript object into associative array (associative indicated by TRUE)
$outputArry = array();
$outputArry["NAME"] = "arryBackFromPhp";


$outputArry = createNewParent($inputArry, $outputArry, $allowedToEdit);

$outputArry = writeReadAllRecordsItem($inputArry, $outputArry, $allowedToEdit);

$outputArry = updateWithdrawnPaidin($inputArry, $outputArry, $allowedToEdit);

$outputArry = updateEditableItem($inputArry, $outputArry, $allowedToEdit); //for updating reference or notes cell

if (($famId->get() != 0) && !$showFamBut->isSet() && !$editFamBut->isSet()) { //a specific family has been chosen to display and neither Show Families or Family Edit have been selected so remove all column filters so family can be displayed complete
	$outputArry = getFilterStrAllBalData($inputArry, $outputArry, "", $familyCmnd->get()); //uses reconciled dates for calculation
}
else {
	$outputArry = getFilterStrAllBalData($inputArry, $outputArry, $genFilter->getFiltStr(), $familyCmnd->get()); //uses reconciled dates for calculation
}

$outputArry = updateDocFilename($inputArry, $outputArry, $allowedToEdit);

//$outputArry["PLAIN-allrecordsColNameRnd"] = $inputArry["allrecordsColNameRnd"];


include_once("./".$sdir."saveSession.php");

echo json_encode($outputArry); //convert php output array back into a JSON string javascript object and echo back to the calling js function

?>



