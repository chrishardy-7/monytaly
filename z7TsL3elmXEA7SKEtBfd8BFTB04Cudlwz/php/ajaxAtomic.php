<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//$thisFileName = "ajaxAtomic.php";
//saveMessage($thisFileName);

/* This function is intended to be called from Javascript. Using the input array a value is written to the table the same field is read and echoed back to the calling javascript function using the output array, as a confirmation that the operation has suceeded. */

//these objects are instantiated to allow the use of their respective 'get and 'isSet' methods by the functions and if statements on this page - buttons will not be drawn!
$showFamBut = new toggleBut("Show Families", "fas fa-plus-square", "subMenuBtn", "subMenuBtnSel", FALSE);
$editFamBut = new toggleBut("Family Edit", "fas fa-users", "subMenuBtn", "subMenuBtnSel", FALSE);
$fam = new familyCommand("FamId", $editFamBut->isSet(), $showFamBut->isSet(), FALSE);

$tables = new dataBaseTables(); //used by custom buttons to get filter keys from string values
$genFilter = new filterColumns("genFilter", $tables); //filter from showRecsForFullYr.php for column filtering

$inputArry = json_decode(htmlspecialchars_decode(sanPost("arryJsonStr")), TRUE); //convert JSON string of javascript object into associative array (associative indicated by TRUE)
$outputArry = array();
$outputArry["NAME"] = "arryBackFromPhp";


saveMessage("In ajaxAtomic!");

$outputArry = setCompoundTrans($inputArry, $outputArry, $allowedToEdit);

$outputArry = createNewParent($inputArry, $outputArry, $allowedToEdit);

$outputArry = writeReadAllRecordsItem($inputArry, $outputArry, $allowedToEdit);

$outputArry = updateWithdrawnPaidin($inputArry, $outputArry, $allowedToEdit);

$outputArry = updateEditableItem($inputArry, $outputArry, $allowedToEdit); //for updating reference or notes cell

if ($fam->justFam()) { //a specific family has been chosen to display and neither Show Families or Family Edit have been selected so remove all column filters so family can be displayed complete
	$outputArry = getFilterStrAllBalData($inputArry, $outputArry, "", $fam->getCmnd()); //uses reconciled dates for calculation
}
else {
	$outputArry = getFilterStrAllBalData($inputArry, $outputArry, $genFilter->getFiltStr(), $fam->getCmnd()); //uses reconciled dates for calculation
}

$outputArry = updateDocFilename($inputArry, $outputArry, $allowedToEdit);

//$outputArry["PLAIN-allrecordsColNameRnd"] = $inputArry["allrecordsColNameRnd"];


include_once("./".$sdir."saveSession.php");

echo json_encode($outputArry); //convert php output array back into a JSON string javascript object and echo back to the calling js function

?>



