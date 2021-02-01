<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//$thisFileName = "ajaxAtomic.php";
//saveMessage($thisFileName);

/* This function is intended to be called from Javascript. Using the input array value(s) are written to the allRecords table, the same field(s) are read and echoed to the calling javascript function using the output array, as a confirmation that the operation has succeeded. THESE FUNCTIONS ARE ONLY USED TO CHANGE MONEY/STRING VALUES AND INDICATORS USED FOR FAMILY AND COMPOUND ROWS EXCEPT IN COPYING STICKY VALUES. ALL NORMAL CHANGES TO FIELDS IN THE allRecords TABLE THAT ARE INDEXES OF OTHER TABLES SUCH AS budgets ARE DONE THROUGH JAVASCRIPT CALLS FROM THE BUTTON PANELS AND PROCESSED THROUGH SEPARATE SPECIFIC PHP FILES. */

//these objects are instantiated to allow the use of their respective 'get and 'isSet' methods by the functions and if statements on this page - buttons will not be drawn!
$showFamBut = new toggleBut("Show Families", "fas fa-plus-square", "subMenuBtn", "subMenuBtnSel", FALSE);
$editFamBut = new toggleBut("Family Edit", "fas fa-users", "subMenuBtn", "subMenuBtnSel", FALSE);
$fam = new familyCommand("FamId", $editFamBut->isSet(), $showFamBut->isSet(), FALSE);

$tables = new dataBaseTables(); //used by custom buttons to get filter keys from string values
$genFilter = new filterColumns("genFilter", $tables, FALSE); //filter from showRecsForFullYr.php for column filtering
$restrictFilter = new filterColumns("restrictFilter", $tables, FALSE); //create new restriction filter with $nonVolatileArray key of "genFilter" and reset all filters if this page called from main menu
$moneyDisplay = new moneyCols("monyColmnDisply", FALSE);

$inputArry = json_decode(htmlspecialchars_decode(sanPost("arryJsonStr")), TRUE); //convert JSON string of javascript object into associative array (associative indicated by TRUE)
$outputArry = array();
$outputArry["NAME"] = "arryBackFromPhp";


saveMessage("In ajaxAtomic!");

$outputArry = writeReadRows($inputArry, $outputArry, $_fieldNameAry, $tables, $allowedToEdit); //clears row when called via ajax routine clearRowAjaxSend (originating from 'Clear' button)

$outputArry = setCompoundTrans($inputArry, $outputArry, $allowedToEdit); //creates/deletes compound rows in allRecords table when AltGr is held

$outputArry = createNewParent($inputArry, $outputArry, $allowedToEdit); //creates new parent in allRecords table when 'P' is held

$outputArry = writeReadAllRecordsItem($inputArry, $outputArry, $allowedToEdit); //copies sticky value to clicked cell(s) in allRecords table - either single row or multiple rows (if Shift is held)

$outputArry = updateWithdrawnPaidin($inputArry, $outputArry, $allowedToEdit); //updates withdrawn and paidin values in allrecords table - either single row or auto calc for multiple rows (for compound sections)

$outputArry = updateEditableItem($inputArry, $outputArry, $allowedToEdit); //updates reference or notes cell in allRecords table

if ($fam->justFam()) { //a specific family has been chosen to display and neither Show Families or Family Edit have been selected so remove all column filters other than restricted so family can be displayed complete
	$outputArry = getFilterStrAllBalData($inputArry, $outputArry, "", $fam->getCmnd(), $restrictFilter->getFiltStr(), ""); //uses reconciled dates for calculation
}
else {
	$outputArry = getFilterStrAllBalData($inputArry, $outputArry, $genFilter->getFiltStr(), $fam->getCmnd(), $restrictFilter->getFiltStr(), $moneyDisplay->getStr()); //uses reconciled dates for calculation
}

$outputArry = updateDocFilename($inputArry, $outputArry, $allowedToEdit);

//$outputArry["PLAIN-allrecordsColNameRnd"] = $inputArry["allrecordsColNameRnd"];


include_once("./".$sdir."saveSession.php");

echo json_encode($outputArry); //convert php output array back into a JSON string javascript object and echo back to the calling js function

?>



