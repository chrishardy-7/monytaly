<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$timeStart = microtime(true); //use microtime to time how long this page takes to execute
$download = FALSE; //flag to indicate record data should be downloaded

$newRowId = 0; //sets new row id to default

$nonVolatileArray["docNameNumStr"] = ""; //NOT SURE IF THIS IS THE RIGHT PLACE FOR THIS !!!! (to create blank filename so first refreshed page thinks it needs to display a new doc)

include_once("./".$sdir."monthSelProcess.php"); // Ensures empty arrays in $nonVolatileArray exist for holding month and year selections. Takes $subCommand (which will originate from the monthSelSideBar.php script wherever that is included) and uses it to either increment/decrement year or select new (or same) month. Produces start and finish dates that will be used outside this specific script for extracting data for a range of documents from the docCatalog table.


/* Returns TRUE if the button whose random value is returned in $subCommand is the button identified by $butPlainTextStr, otherwise returns FALSE.   */
function isClicked($butPlainTextStr) {
	global $nonVolatileArray;
	global $subCommand;
	$butClicked = FALSE;
	if (array_key_exists ("genrlAryRndms", $nonVolatileArray) && (array_search($subCommand, $nonVolatileArray["genrlAryRndms"]) == $butPlainTextStr)) { //$butPlainTextStr button has been pressed
		$butClicked = TRUE;
	}
	return $butClicked;
}

if (isClicked("nextStatement")) {
	$newRowId = getAdjacentBankStmnt(sanPost("behindBankStatementIdR"), "Forward");
}

if (isClicked("prevStatement")) {
	$newRowId = getAdjacentBankStmnt(sanPost("aheadBankStatementIdR"), "Back");
}


function toggleBut(&$nonVolArry, $genrlAryRndmsKey, $butPlainTextStr, $subCmnd, $butCntrlName) {
	if (!array_key_exists ($butCntrlName, $nonVolArry) || ($subCmnd == "FromMainMenu")) { //create the key $butCntrlName if it doesn't already exist or a new main menu command cancels any previous setting
		$nonVolArry[$butCntrlName] = FALSE; //set to default FALSE
	} 
	$buttonPressed = FALSE;
	if (array_key_exists ($genrlAryRndmsKey, $nonVolArry) && (array_search($subCmnd, $nonVolArry[$genrlAryRndmsKey]) == $butPlainTextStr)) { //$butPlainTextStr button has been pressed
		$buttonPressed = TRUE;
		//toggle $butCntrlName every time the button is preseed
		if ($nonVolArry[$butCntrlName] == "TRUE") { //TRUE so toggle to FALSE
			$nonVolArry[$butCntrlName] = FALSE;
		}
		else {
	 		$nonVolArry[$butCntrlName] = TRUE;
	 	}
	}
	return $buttonPressed; //returns TRUE if button has been pressed, FALSE otherwise
}

toggleBut($nonVolatileArray, "genrlAryRndms", "toggleEditFamilies", $subCommand, "editFamilies"); //toggle "editFamilies"

$bankStatementButPressed = toggleBut($nonVolatileArray, "genrlAryRndms", "toggleBankAccDisplay", $subCommand, "displayBankAcc"); //toggle "editFamilies"
if ($bankStatementButPressed) { //if bank statement display button has been pressed (selected or deselected) set current row to the last statement used
	$newRowId = sanPost("bankStatementIdR");
}



//THIS SECTION PRODUCES A STRING OF FILTER TERMS (" AND docType = 3 AND budget = 7 " etc.) THAT HAVE BEEN cntrl clicked TO CREATE FILTERS - clicking A CELL AGAIN REMOVES ITS TERM FROM THE STRING (TOGGLES IT OFF) OR UPDATES IT IF IT HAS A DIFFERENT VALUE
if (!array_key_exists("filtersAry", $nonVolatileArray) || ($subCommand == "FromMainMenu")) { //create the key "filtersAry" if it doesn't already exist
	$nonVolatileArray["filtersAry"]["fieldName"] = array(); //create new array
	$nonVolatileArray["filtersAry"]["fieldValue"] = array(); //create new array
	$nonVolatileArray["filtersAry"]["filterStr"] = ""; //create blank string
	$nonVolatileArray["filtersAry"]["columnIdx"] = array(); //create blank array
}
$filterTerm = sanPost("filterRecordIdR", "No Filter Clicked"); //filter term from this page if a cntrl click is performed on a cell, default to "No Filter Clicked" by virtue of being initialised to 0
if ($filterTerm != "No Filter Clicked") { //only do this if a filter term has been POSTed
	$filterTermAry = explode("-", $filterTerm);
	$recRowId = $filterTermAry[0];
	$fieldName = $_fieldNameAry[$filterTermAry[1]]; //create fieldName from column id (0 - 11).
	if (($filterTermAry[1] == 7) || ($filterTermAry[1] == 11)) { //if column is reference or notes don't convert filter to key but use string directly from the clicked cell
		$fieldValue = getRecFieldValueAtRow($recRowId, $fieldName);
		$fieldValue = '\''.$fieldValue.'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
	}
	else { //column is one where values are represented as keys in the allRecords table so 
		$fieldValue = getRecFieldValueAtRow($recRowId, $fieldName); //get key of field from allRecords table
	}
	if (FALSE === array_search($fieldName, $nonVolatileArray["filtersAry"]["fieldName"])) { //if fieldName is NOT in array, add it (FALSE is used as a test because an item with key = 0 returns 0 which is accepted as FALSE by php and means the first item added will be added again and again as it is never detected as being in the array)
		$nonVolatileArray["filtersAry"]["fieldName"][] = $fieldName;
		$nonVolatileArray["filtersAry"]["fieldValue"][] = $fieldValue;
		$nonVolatileArray["filtersAry"]["columnIdx"][] = $filterTermAry[1];
	}
	else { //fieldName IS in array, if different value hasn't been clicked remove field name and value, otherwise change value
		$filterKey = array_search($fieldName, $nonVolatileArray["filtersAry"]["fieldName"]); //get the key for the fieldName (keys should stay in sync for fieldName and fieldValue so only this req)
		if ($fieldValue == $nonVolatileArray["filtersAry"]["fieldValue"][$filterKey]) { //fieldName value hasn't changed so remove field name and value (toggle this filter parameter off)
			unset($nonVolatileArray["filtersAry"]["fieldName"][$filterKey]); //remove that key and its value
			$nonVolatileArray["filtersAry"]["fieldName"] = array_values($nonVolatileArray["filtersAry"]["fieldName"]); //reconstruct the fieldName array with contiguous keys
			unset($nonVolatileArray["filtersAry"]["fieldValue"][$filterKey]); //remove that key and its value
			$nonVolatileArray["filtersAry"]["fieldValue"] = array_values($nonVolatileArray["filtersAry"]["fieldValue"]); //reconstruct the fieldValue array with contiguous keys
			unset($nonVolatileArray["filtersAry"]["columnIdx"][$filterKey]); //remove that column index and its value
			$nonVolatileArray["filtersAry"]["columnIdx"] = array_values($nonVolatileArray["filtersAry"]["columnIdx"]); //reconstruct the column index array with contiguous keys
		}
		else { //fieldName value has changed so update fieldValue for with new one (change of filter parameter)
			$nonVolatileArray["filtersAry"]["fieldValue"][$filterKey] = $fieldValue;
		}
	}
}
$nonVolatileArray["filtersAry"]["filterStr"] = "";
foreach ($nonVolatileArray["filtersAry"]["fieldName"] as $curKey => $curFieldName) {
	$nonVolatileArray["filtersAry"]["filterStr"] .= " AND ".$curFieldName." = ".$nonVolatileArray["filtersAry"]["fieldValue"][$curKey];
}


if (!array_key_exists ("familyMaster", $nonVolatileArray) || ($subCommand == "FromMainMenu") || $nonVolatileArray["editFamilies"]) { //create the key "familyMaster" if it doesn't already exist or a new main menu command/editFamilies button cancels any previous grouping
	$nonVolatileArray["familyMaster"] = "NoKids"; //set default value of familyMaster to NoKids
}
if (array_key_exists ("genrlAryRndms", $nonVolatileArray) && (array_search($subCommand, $nonVolatileArray["genrlAryRndms"]) == "toggleEditFamilies")) { //if toggle families button clicked clear to noKids -  used to set to noKids when edit families is unselected
	$nonVolatileArray["familySetting"] = "NoKids";
}
if (array_key_exists ("genrlAryRndms", $nonVolatileArray) && (array_search($subCommand, $nonVolatileArray["genrlAryRndms"]) == "expandFamilies") && !$nonVolatileArray["editFamilies"]) {
	if ($nonVolatileArray["familyMaster"] == "All") { //"All" so toggle to "NoKids"
		$nonVolatileArray["familyMaster"] = "NoKids";
	}
	else {
 		$nonVolatileArray["familyMaster"] = "All"; //"NoKids" so toggle to "All"
 	}
 	if (($nonVolatileArray["familySetting"] == "NoKids") || ($nonVolatileArray["familySetting"] == "All")) { //familySetting is not a family id so impose master setting on to it
 		$nonVolatileArray["familySetting"] = $nonVolatileArray["familyMaster"];
 	}
}



//section that handles family display
if (!array_key_exists ("familySetting", $nonVolatileArray)) { //create the key "familySetting" if it doesn't already exist
	$nonVolatileArray["familySetting"] = $nonVolatileArray["familyMaster"]; //set to current family choice (All or NoKids)
}
if (($subCommand == "FromMainMenu") || $newDateSelected) { //used to indicate this page has been called from the main (top) menu or a new date has been sel so familySetting should be cleared for a fresh start
	$nonVolatileArray["familySetting"] = $nonVolatileArray["familyMaster"]; //set to current family choice (All or NoKids)
}
$idRforFamily = sanPost("idRforFamily", "No Family Clicked"); //family row from this page if a family cell is clicked on, default to "No Family Clicked" by virtue of being initialised to 0
if (($idRforFamily != "No Family Clicked") && !$nonVolatileArray["editFamilies"]) { //only do this if a family row has been POSTed and edit families hasn't been selected
	$familyId = getFamilyId($idRforFamily);
	if ($familyId == 0) { //no family selected so set to family master choice (All or NoKids)
		$nonVolatileArray["familySetting"] = $nonVolatileArray["familyMaster"];
	}
	elseif ($familyId == $nonVolatileArray["familySetting"]) { //same family selected so clear to family master choice (All or NoKids) - i.e. toggle single family display off
		$nonVolatileArray["familySetting"] = $nonVolatileArray["familyMaster"];
	}
	else { //a family id has been selected that is not 0, and different from previous one, so set family choice to this id -  - i.e. toggle single family display 0n
		$nonVolatileArray["familySetting"] = $familyId;
	}
}

if ($nonVolatileArray["editFamilies"]) { //used to force family selection to all during edit families operations
	$nonVolatileArray["familySetting"] = "All"; 
}



if (!array_key_exists ("showAbsolutlyEverything", $nonVolatileArray) || ($subCommand == "FromMainMenu")  || $nonVolatileArray["editFamilies"]) { //create the key "showAbsolutlyEverything" if it doesn't already exist or a new main menu command cancels any previous grouping
	$nonVolatileArray["showAbsolutlyEverything"] = FALSE; //set to default showAbsolutlyEverything to NoKids
}
if (array_key_exists ("genrlAryRndms", $nonVolatileArray) && (array_search($subCommand, $nonVolatileArray["genrlAryRndms"]) == "showEverything")  && !$nonVolatileArray["editFamilies"]) {
	if ($nonVolatileArray["showAbsolutlyEverything"] == "TRUE") { //TRUE so toggle to FALSE
		$nonVolatileArray["showAbsolutlyEverything"] = FALSE;
	}
	else {
 		$nonVolatileArray["showAbsolutlyEverything"] = TRUE; //"NoKids" so toggle to "All"
 	}
}





if (!array_key_exists ("headingIdForGroupSel", $nonVolatileArray) || ($subCommand == "FromMainMenu")) { //create the key "headingForGroupSel" if it doesn't already exist or a new main menu command cancels any previous grouping
	$nonVolatileArray["headingIdForGroupSel"] = 0; //set to default heading for group selection
}
$colKeyForGroupAry = array("recordDate", "personOrOrg", "transCatgry", "amountWithdrawn", "amountPaidIn", "accWorkedOn", "budget", "referenceInfo", "reconciledDate", "umbrella", "docType", "recordNotes", "parent");
$headingCol = sanPost("headingCol", "No Heading Clicked"); //heading cell column id from this page (for group selection by clicking a heading), defaults to "No heading Clicked" by virtue of being initialised to 0 (means the date heading - column id = 0 - will only reload the page without doing any grouping)
if ($headingCol != "No Heading Clicked") { //only do this if a heading cell choice has been POSTed
	if ($nonVolatileArray["headingIdForGroupSel"] == $headingCol) { //same heading clicked again so toggle group selector off
		$nonVolatileArray["headingIdForGroupSel"] = 0;
	}
	else { //a new heading has been clicked so toggle heading selector on by copying the table column name
 		$nonVolatileArray["headingIdForGroupSel"] = $headingCol; //convert the heading cell column id to table column name
 	}
}
if ($nonVolatileArray["headingIdForGroupSel"] == 0) { //default column id which corresponds to date column so set groupSelector to "" so no grouping takes place
	$groupColSelector = "";
}
else {
	$groupColSelector = $colKeyForGroupAry[$nonVolatileArray["headingIdForGroupSel"]]; //set the group selector to the field string
	//$groupColSelector = $colKeyForGroupAry[$nonVolatileArray["headingIdForGroupSel"]].", transCatgry"; //set the group selector to the field string
	if (in_array($nonVolatileArray["headingIdForGroupSel"], $nonVolatileArray["filtersAry"]["columnIdx"])) { //if the column desired for group display matches one set for filter, cancel the group display
		$nonVolatileArray["headingIdForGroupSel"] = 0;
		$groupColSelector = "";
	}
}



$showToolTip = FALSE;

$nameOfThisPage = "Show Records For Full Year";
include_once("./".$sdir."createMenuRndms.php");
$indexPage = htmlspecialchars($_SERVER["PHP_SELF"]);

//include_once("./".$sdir."monthSelProcess.php"); // Ensures empty arrays in $nonVolatileArray exist for holding month and year selections. Takes $subCommand (which will originate from the monthSelSideBar.php script wherever that is included) and uses it to either increment/decrement year or select new (or same) month. Produces start and finish dates that will be used outside this specific script for extracting data for a range of documents from the docCatalog table.


$butPanelIdSuffix = 'butPanel';
$calId  = $butPanelIdSuffix.'0'; //random to provide unique id for calendar sidebar and columns
$persOrgId  = $butPanelIdSuffix.'1'; //random to provide unique id for personOrg choice sidebar and columns
$transCatId  = $butPanelIdSuffix.'2'; //random to provide unique id for transaction category choice sidebar and columns
$accId  = $butPanelIdSuffix.'5'; //random to provide unique id for account choice sidebar and columns
$budgId = $butPanelIdSuffix.'6'; //random to provide unique id for budget choice sidebar and columns
$refId = $butPanelIdSuffix.'7'; //random to provide unique id for reference choice sidebar and columns
$recId  = $butPanelIdSuffix.'8'; //random to provide unique id for reconciled calendar sidebar and columns
$umbrlId = $butPanelIdSuffix.'9'; //random to provide unique id for umbrella choice sidebar and columns
$docTypeId = $butPanelIdSuffix.'10'; //random to provide unique id for doc type choice sidebar and columns
$notesId = $butPanelIdSuffix.'11'; //random to provide unique id for notes choice sidebar and columns



$initialRow = 0;
if (array_key_exists ("genrlAryRndms", $nonVolatileArray) && (array_search($subCommand, $nonVolatileArray["genrlAryRndms"]) == "duplicateRec")) { //check "genrlAryRndms" key exists and then that the subarray at that key contains the key "duplicateRec"
	$newRowId = duplicateRecRow(sanpost("storeSelectedRecordIdR")); //sets new row id to value of latest duplicate - used later to set first sellected cell to this after page is loaded from 'duplicate' cmnd
	//$initialRow = sanpost("storeSelectedRowIdx", 0);
}

if (array_key_exists ("genrlAryRndms", $nonVolatileArray) && (array_search($subCommand, $nonVolatileArray["genrlAryRndms"]) == "deleteRec")) {
	//deleteRecRow(sanpost("storeSelectedRecordIdR"));
	pr("DELETE INHIBITED FOR SAFETY JUST NOW! NEEDS ADDITIONAL STUFF TO PREVENT AND WARN OF POTENTIAL BROKEN PARENT-CHILD LINKS");
}

if (array_key_exists ("genrlAryRndms", $nonVolatileArray) && (array_search($subCommand, $nonVolatileArray["genrlAryRndms"]) == "Download")) {
	$download = TRUE;
	$newRowId = sanpost("bankStatementIdRForDownload");
}

$genrlAry = Array("allRecords", "amountWithdrawn", "amountPaidIn", "recordDate", "persOrgCategory", "orgPerCategories", "categoryName", "idR", "duplicateRec", "deleteRec", "nextDocFromSelection", "prevDocFromSelection", "orgsOrPersons", "orgOrPersonName", "referenceInfo", "accWorkedOn", "linkedAccOrBudg", "accounts", "accountName", "budgets", "budgetName", "otherDocsCsv", "docTags", "docTagName", "docVarieties", "docVarietyName", "Download", "expandFamilies", "showEverything", "toggleEditFamilies", "toggleBankAccDisplay", "nextStatement", "prevStatement");
$genrlAryRndms = createKeysAndRandomsArray($genrlAry, $_cmndRndmLngth, $uniqnsChkAryForRndms);
$nonVolatileArray["genrlAryRndms"] = $genrlAryRndms;

//$recordsDataArry = getMultDocDataAry($startDate, $endDate, $nonVolatileArray["filtersAry"]["filterStr"], ""); //moved down to be just above foreach - loop

$targetPageRandom = $menuRandomsArray[$nameOfThisPage]; //get the menu random for this page so the default action of date buttons will be to come back to this page with the new doc selected

$orgPersonsListAry = getOrgOrPersonsList(); //gets array of all possible orgsOrPersons in alphabetical order ie: array([1] => RBS [8] => Robertson Tr [17] => Scottish Pwr [22] => Susan)
$transCatListAry = getorgPerCategories(); //gets array of all possible org/person categories in alphabetical order ie: array([2] => Volunteer [9] => Robertson Trust Budget [1] => Pret a Mange Budget)
$accountListAry = getAccountList(); //gets array of all possible orgsOrPersons in alphabetical order ie: array([1] => General [8] => FP Cash [17] => Church Cash [22] => Build Float, [3] => RBS-00128252)
$budgetListAry = getBudgetList(); //gets array of all possible org/person/account categories in alphabetical order ie: array([2] => Volunteer [9] => Robertson Trust Budget [1] => Pret a Mange Budget)
$docTypeListAry = getDocVarietyData(); //gets array of all possible doc varieties in alphabetical order ie: array([1] => Letter [6] => Minutes [8] => Offering Statement [2] => Receipt [23] => Report [17])
$umbrellaListAry = getDocTagData(); //gets array of all possible doc tags in alphabetical order ie: array([2] => Church Building [9] => Church Flat [1] => Furniture Project [8] => IT Classes [3] => Leaders)


$copyButStickyValues = getCopyButStickyValues();

$copyDivVis = "none";

//section to set org/pers category copy button state from that saved in all Records table
$catCopyButkey = 0;
$catCopyButStr = "";
$catCopyButClass = "copyBut";
$catCopyButName = "notSet";
if ($copyButStickyValues['persOrgCategory']) {
	$catCopyButkey = $copyButStickyValues['persOrgCategory'];
	$catCopyButStr = $transCatListAry[$copyButStickyValues['persOrgCategory']];
	$catCopyButClass = "copyButSel";
	$catCopyButName = "set";
	$copyDivVis = "inline";
}

//section to set account copy button state from that saved in all Records table
$accCopyButkey = 0;
$accCopyButStr = "";
$accCopyButClass = "copyBut";
$accCopyButName = "notSet";
if ($copyButStickyValues['accWorkedOn']) {
	$accCopyButkey = $copyButStickyValues['accWorkedOn'];
	$accCopyButStr = $accountListAry[$copyButStickyValues['accWorkedOn']];
	$accCopyButClass = "copyButSel";
	$accCopyButName = "set";
	$copyDivVis = "inline";
}

//section to set budget copy button state from that saved in all Records table
$budgCopyButkey = 0;
$budgCopyButStr = "";
$budgCopyButClass = "copyBut";
$budgCopyButName = "notSet";
if ($copyButStickyValues['linkedAccOrBudg']) {
	$budgCopyButkey = $copyButStickyValues['linkedAccOrBudg'];
	$budgCopyButStr = $budgetListAry[$copyButStickyValues['linkedAccOrBudg']];
	$budgCopyButClass = "copyButSel";
	$budgCopyButName = "set";
	$copyDivVis = "inline";
}


$familySetting = $nonVolatileArray["familySetting"];
if ($nonVolatileArray["showAbsolutlyEverything"]) {
	$familySetting = "everything";
}


//GETS RECORD DATA FROM allRecords TABLE !!
if ($nonVolatileArray["displayBankAcc"]) {
	$recordsDataArry = getReconciledDataAry($newRowId); //$newRowId has been set with sanPost("bankStatementIdR") when button was pressed and page reloaded
}
else {
	$recordsDataArry = getMultDocDataAry($startDate, $endDate, $nonVolatileArray["filtersAry"]["filterStr"], "", $familySetting, $groupColSelector);
}

$headingAry = array("Date", "Pers / Org", "Trans Cat", "Withdrawn", "PaidIn", "Account", "Budget", "Reference", "Reconciled", "Umbrella", "Doc Type", "Note", "Family");
$colKeyForDownldAry = array("recordDate", "persOrgStr", "categoryStr", "amountWithdrawn", "amountPaidIn", "accountStr", "budgetStr", "reference", "reconciledDateForDownld", "umbrellaStr", "docVarietyStr", "note", "familyStatus");

$groupColumnSelected = FALSE;
if (0 < $nonVolatileArray["headingIdForGroupSel"]) { //a column has been set to group 
	$groupColumnSelected = TRUE;
	$partOfGroupOrFilter = array();
	$partOfGroupOrFilter[0] = FALSE; //set date display to off
	for ($grpFilIndex = 1; $grpFilIndex <= 12; $grpFilIndex++) {
		if (($grpFilIndex == $nonVolatileArray["headingIdForGroupSel"]) || in_array($grpFilIndex, $nonVolatileArray["filtersAry"]["columnIdx"]) || ($grpFilIndex == 3) || ($grpFilIndex == 4)) { //if index matches either a column selected to display grouped data or columns that are filtered (and therefore showing only one category), or filter or index = 3 or 4 (withdrawn and paid in columns that should always be displayed)
			$partOfGroupOrFilter[$grpFilIndex] = TRUE; //group or filter column so set to TRUE (later used to enable display of that column)
		}
		else {
			$partOfGroupOrFilter[$grpFilIndex] = FALSE; //group or filter column set to FALSE (later used to inhibit display of that column)
		}
	}
	//$partOfGroupOrFilter[2] = TRUE;
}
else { //no grouping in use so set all columns to display
	for ($grpFilIndex = 0; $grpFilIndex <= 12; $grpFilIndex++) {
		$partOfGroupOrFilter[$grpFilIndex] = TRUE; //group or filter column so set to TRUE (later used to enable display of that column)
	}
}

$recsAry = array();
$index = 0;
$totalWithdrawn = 0;
$totalPaidIn = 0;

$bankStmtWithdrawn = 0;
$bankStmtPaidIn = 0;

$totalRecncldDocsWithdrawn = 0;
$totalRecncldDocsPaidIn = 0;

if ($groupColumnSelected) { //if $groupColumnSelected is TRUE loop through all records that have been retrieved from allRecords using the column group selector that groups categories and sums money values
	$headingAry[0] = "DateRange";
	$headingAry[8] = "Balance";
	foreach ($recordsDataArry as $singleRecArry) { //loop through all records selected for display creating indexed array of values like "idR", "persOrgCategory" for each row to be displayed
		$recsAry[$index]["idR"] = $singleRecArry["idR"];
		$docFileNamesAry[$index] = $singleRecArry["fileName"]; //create array of doc file names. There will be repeats if more than one record is associated with the same doc.

		if ($partOfGroupOrFilter[0]) {
			$recDateAry = explode("-", $singleRecArry["recordDate"]);
			$recsAry[$index]["recordDate"] = $recDateAry[2]."-".$recDateAry[1]."-".$recDateAry[0];
		}
		else {
			$recsAry[$index]["recordDate"] = "";
		}


		if ($partOfGroupOrFilter[1]) {
			$recsAry[$index]["persOrgStr"] = aryValueOrZeroStr($orgPersonsListAry, $singleRecArry["personOrOrg"]);
		}
		else {
			$recsAry[$index]["persOrgStr"] = "";
		}

		


		if ($partOfGroupOrFilter[2]) { //if the selected group is the transaction type save the particulatr type to this recsArry index
			$recsAry[$index]["categoryStr"] = aryValueOrZeroStr($transCatListAry, $singleRecArry["transCatgry"]);
		}
		else { //if not the selected group set recsArry index to display nothing
			$recsAry[$index]["categoryStr"] = "";
		}


		if ($partOfGroupOrFilter[3]) {
			$recsAry[$index]["amountWithdrawn"] = $singleRecArry["amountWithdrawn"];
		}
		else {
			$recsAry[$index]["amountWithdrawn"] = "";
		}


		if ($partOfGroupOrFilter[4]) {
			$recsAry[$index]["amountPaidIn"] = $singleRecArry["amountPaidIn"];
		}
		else {
			$recsAry[$index]["amountPaidIn"] = "";
		}

		$balance = $singleRecArry["amountPaidIn"] - $singleRecArry["amountWithdrawn"];


		$totalWithdrawn = $totalWithdrawn + $singleRecArry["amountWithdrawn"]; //accumulates total amount withdrawn for the displayed page (maybe just used for a test)
		$totalPaidIn = $totalPaidIn + $singleRecArry["amountPaidIn"]; //accumulates total amount paidin for the displayed page (maybe just used for a test)


		if ($partOfGroupOrFilter[5]) {
			$recsAry[$index]["accountStr"] = aryValueOrZeroStr($accountListAry, $singleRecArry["accWorkedOn"]);
		}
		else {
			$recsAry[$index]["accountStr"] = "";
		}


		if ($partOfGroupOrFilter[6]) {
			$recsAry[$index]["budgetStr"] = aryValueOrZeroStr($budgetListAry, $singleRecArry["budget"]);
		}
		else {
			$recsAry[$index]["budgetStr"] = "";
		}


		if ($partOfGroupOrFilter[7]) {
			$recsAry[$index]["reference"] = $singleRecArry["referenceInfo"];
		}
		else {
			$recsAry[$index]["reference"] = "";
		}

		
		if ($partOfGroupOrFilter[8]) {
			$reconcileDate = $singleRecArry["reconciledDate"];
			$recsAry[$index]["reconciled"] = "Yes";
			if ($endDate < $reconcileDate) {
				$recsAry[$index]["reconciled"] = "No";
			}
			if ($reconcileDate < $startDate) {
				$recsAry[$index]["reconciled"] = "Error";
			}
			$reconcileDateAry = explode("-", $reconcileDate);
			$recsAry[$index]["reconciledDate"] = $reconcileDateAry[2]."-".$reconcileDateAry[1]."-".$reconcileDateAry[0];
			$recsAry[$index]["reconciledDateForDownld"] = $recsAry[$index]["reconciledDate"];
			if ($recsAry[$index]["reconciled"] == "Error") {
				if (!($singleRecArry["reconciledDate"] == "2000-01-01")) {
					$recsAry[$index]["reconciledDate"] = $reconcileDateAry[2]."#".$reconcileDateAry[1]."#".$reconcileDateAry[0];
				}
			}
			if ($recsAry[$index]["reconciledDateForDownld"] == "01-01-2000") {
					$recsAry[$index]["reconciledDateForDownld"] = "";
			}
		}
		else {
			$recsAry[$index]["reconciled"] = "Yes";
			$recsAry[$index]["reconciledDate"] = $balance;
			$recsAry[$index]["reconciledDateForDownld"] = $balance;
		}



		//$recsAry[$index]["parentDocRef"] = $singleRecArry["parent"];


		if ($partOfGroupOrFilter[9]) {
			$recsAry[$index]["umbrellaStr"] = aryValueOrZeroStr($umbrellaListAry, $singleRecArry["umbrella"]);
		}
		else {
			$recsAry[$index]["umbrellaStr"] = "";
		}


		

		if ($partOfGroupOrFilter[10]) {
			$recsAry[$index]["docVarietyStr"] = aryValueOrZeroStr($docTypeListAry, $singleRecArry["docType"]);
		}
		else {
			$recsAry[$index]["docVarietyStr"] = "";
		}


		

		if ($partOfGroupOrFilter[11]) {
			$recsAry[$index]["note"] = $singleRecArry["recordNotes"];
		}
		else {
			$recsAry[$index]["note"] = "";
		}


		if ($partOfGroupOrFilter[12]) {
			$recsAry[$index]["familyStatus"] = ""; //default to show nothing for family status
			if ($singleRecArry["idR"] == $singleRecArry["parent"]) { //parent value same as index so this is an actual parent: show family num with 'P' symbol prefix
				$recsAry[$index]["familyStatus"] = "OOO ".$singleRecArry["parent"];
			}
			elseif (0 < $singleRecArry["parent"]) { //parent value < 0 so this is a child: show family num with 'c' symbol prifix
				$recsAry[$index]["familyStatus"] = "% ".$singleRecArry["parent"];
			}
		}
		else {
			$recsAry[$index]["familyStatus"] = "";
		}


		$initRecIdR = $recsAry[0]["idR"]; //will happen with same value every iteratioin, but simple way to make sure it only runs if there is data in $recordsDataArry
		$initRecDate = $recsAry[0]["recordDate"]; //will happen with same value every iteratioin, but simple way to make sure it only runs if there is data in $recordsDataArry
		$index++;
	}
	$recsAry[0]["recordDate"] = $startDate." ".$endDate;
}
else { //loop through all records that have been retrieved from the allRecords table using the normal procedure (not column group selector)
	foreach ($recordsDataArry as $singleRecArry) { //loop through all persOrgs selected for display creating indexed array of values like "idR", "persOrgCategory" for each row to be displayed
		$recsAry[$index]["idR"] = $singleRecArry["idR"];
		$docFileNamesAry[$index] = $singleRecArry["fileName"]; //create array of doc file names. There will be repeats if more than one record is associated with the same doc.
		$recDateAry = explode("-", $singleRecArry["recordDate"]);
		$recsAry[$index]["recordDate"] = $recDateAry[2]."-".$recDateAry[1]."-".$recDateAry[0];
		$recsAry[$index]["persOrgStr"] = aryValueOrZeroStr($orgPersonsListAry, $singleRecArry["personOrOrg"]);

		$recsAry[$index]["familyStatus"] = ""; //default to show nothing for family status
		if ($singleRecArry["idR"] == $singleRecArry["parent"]) { //parent value same as index so this is an actual parent: show family num with 'P' symbol prefix
			$recsAry[$index]["familyStatus"] = "OOO ".$singleRecArry["parent"];
		}
		elseif (0 < $singleRecArry["parent"]) { //parent value < 0 so this is a child: show family num with 'c' symbol prifix
			$recsAry[$index]["familyStatus"] = "% ".$singleRecArry["parent"];
			//$recsAry[$index]["familyStatus"] = $singleRecArry["parent"];
		}

		$recsAry[$index]["categoryStr"] = aryValueOrZeroStr($transCatListAry, $singleRecArry["transCatgry"]);

		$recsAry[$index]["amountWithdrawn"] = $singleRecArry["amountWithdrawn"];
		$recsAry[$index]["amountPaidIn"] = $singleRecArry["amountPaidIn"];

		$totalWithdrawn = $totalWithdrawn + $singleRecArry["amountWithdrawn"]; //accumulates total amount withdrawn for the displayed page (maybe just used for a test)
		$totalPaidIn = $totalPaidIn + $singleRecArry["amountPaidIn"]; //accumulates total amount paidin for the displayed page (maybe just used for a test)


		if ($index == 0) { //this row displays single bank statement for reconcilation checks
			$bankStmtWithdrawn = $singleRecArry["amountWithdrawn"]; //amount withdrawn for the bank statement
			$bankStmtPaidIn = $singleRecArry["amountPaidIn"]; //amount paidin for the bank statement
			$bankStmtLines = $singleRecArry["referenceInfo"]; //referenceInfo is used to hold number of lines in the bankstatement
		}
		else { //all these other rows display transactions associated with the bank statement
			$totalRecncldDocsWithdrawn = $totalRecncldDocsWithdrawn + $singleRecArry["amountWithdrawn"]; //accumulates total amount withdrawn for the displayed transactions associated with bank statement
			$totalRecncldDocsPaidIn = $totalRecncldDocsPaidIn + $singleRecArry["amountPaidIn"]; //accumulates total amount paidin for the displayed transactions associated with bank statement
		}

		$recsAry[$index]["accountStr"] = aryValueOrZeroStr($accountListAry, $singleRecArry["accWorkedOn"]);


		$recsAry[$index]["budgetStr"] = aryValueOrZeroStr($budgetListAry, $singleRecArry["budget"]);

		$recsAry[$index]["reference"] = $singleRecArry["referenceInfo"];

		
		$reconcileDate = $singleRecArry["reconciledDate"];
		$recsAry[$index]["reconciled"] = "Yes";
		if ($endDate < $reconcileDate) {
			$recsAry[$index]["reconciled"] = "No";
		}
		if ($reconcileDate < $startDate) {
			$recsAry[$index]["reconciled"] = "Error";
		}
		$reconcileDateAry = explode("-", $reconcileDate);
		$recsAry[$index]["reconciledDate"] = $reconcileDateAry[2]."-".$reconcileDateAry[1]."-".$reconcileDateAry[0];
		$recsAry[$index]["reconciledDateForDownld"] = $recsAry[$index]["reconciledDate"];
		if ($recsAry[$index]["reconciled"] == "Error") {
			if (!($singleRecArry["reconciledDate"] == "2000-01-01")) {
				$recsAry[$index]["reconciledDate"] = $reconcileDateAry[2]."#".$reconcileDateAry[1]."#".$reconcileDateAry[0];
			}
		}
		if ($recsAry[$index]["reconciledDateForDownld"] == "01-01-2000") {
				$recsAry[$index]["reconciledDateForDownld"] = "";
			}

		//$recsAry[$index]["parentDocRef"] = $singleRecArry["parent"];

		$recsAry[$index]["note"] = $singleRecArry["recordNotes"];

		$recsAry[$index]["docVarietyStr"] = aryValueOrZeroStr($docTypeListAry, $singleRecArry["docType"]);
		$recsAry[$index]["umbrellaStr"] = aryValueOrZeroStr($umbrellaListAry, $singleRecArry["umbrella"]);
		$initRecIdR = $recsAry[0]["idR"]; //will happen with same value every iteratioin, but simple way to make sure it only runs if there is data in $recordsDataArry
		$initRecDate = $recsAry[0]["recordDate"]; //will happen with same value every iteratioin, but simple way to make sure it only runs if there is data in $recordsDataArry
		$index++;
	}
}





$lineCount = $index;

if ($download) { //this file (showRecsForFullYr.php) is being run on the server again for the purpose of downloading the same data that has been displayed by its previous run, using the same filters and dates so the download will reflect exactly what is being displayed
	$downloadStr = implode(",", $headingAry); //create header csv line for file download
	foreach ($recsAry as $downloadRow) { //read a record row at a time
		$downloadStr .= PHP_EOL; //concatonate and of line character
		$downloadRowAry = array(); //create empty array to contain next row
		foreach ($colKeyForDownldAry as $colKeyForDownld) { //go through a row one column at a time
			$downloadRowAry[] = $downloadRow[$colKeyForDownld]; //create array using the column keys
		}
		$downloadStr .= implode(",", $downloadRowAry); //concatonate next row as csv string
	}
	$todaysDateTime = date("Y-m-d-Hi"); //in 2015-11-23-1425 format
	$fileName = 'FurnProj-'.$todaysDateTime.'.csv';
	$content = $downloadStr;
	$length = strlen($content) + 2;

	header('Content-Description: File Transfer');
	header('Content-Type: text/plain');//<<<<
	header('Content-Disposition: attachment; filename='.$fileName);
	header('Content-Transfer-Encoding: binary');
	header('Content-Length: ' . $length);
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Expires: 0');
	header('Pragma: public');
	echo $content;
	exit; //exits this file here after download - so no saveSession.php is included and any changes to menuRandoms etc. are not saved. Also no display of data, just the download
}



if ($index) { //only if index has been incremented from 0 - indicating data exists to display
	$docFileNameAryRndms = createKeysAndRandomsArray(array_unique($docFileNamesAry), $_cmndRndmLngth, $uniqnsChkAryForRndms); //create an array of random numbers with each unique doc file name as keys, so there will be only one random per filename
}

$maxRowIdx = sizeof($recsAry) - 1;
$maxColIdx = 11;
$selectedRowCell = '0-0';
if (sizeof($recsAry) != 0) { //only sets the index for the array if array has something in it! Prevents 'undefined offset' if array is empty because date(s) have been chosen that contain no data
	$selectedRowCell = $recsAry[0]["idR"]."-0";
}

if (0 < $newRowId) { //sets initially selected row to the one of interest for the duplicate row after it is created and the last bank statement displayed row after bank statement button pressed
	$selectedRowCell = $newRowId."-0";
}


include_once("./".$sdir."head.php");

if ($allowedToEdit) {
	formValHolder("allowedToEdit", "Yes");
}
else {
	formValHolder("allowedToEdit", "No");
}

//pr($nonVolatileArray["editFamilies"]);


if ($nonVolatileArray["editFamilies"]) {
	formValHolder("editFamilies", "Yes");
}
else {
	formValHolder("editFamilies", "No");
}


formValHolder("seltdRowCellId", $selectedRowCell);
formValHolder("previousCellId", $selectedRowCell);
formValHolder("moneyCellIdHldr", 0); //used to hold cell id for updating withdrawn/paidin values in table - set by changeField()
?>
<script> //this whole script section is to deal with changes in browser display window size. It sets and refreshes the iframe document display to suit different standard sizes such as HD, 768, iPad etc.
	var docFilename = "../<?php echo $dir?>obscureTest.php";
	var docFilename2 = "../<?php echo $dir?>obscureTest2.php";
	var pageNum = 1;
/*	function loadIframeDoc() {	//in response to changes in display size from different devices gets width of container div and reloads the document into the iframe using a suitable zoom %
		var outerContainerWidth = window.getComputedStyle( document.getElementById("container"), null).getPropertyValue("width");
		var zoom = "20";
		switch (outerContainerWidth) {
			case "1010px": //iPad 4 landscape
				zoom = "50";
				break;
			case "1350px": //Old laptop screen (1366 x 768)
				zoom = "70";
				break;
			case "1912px": //HD Screen (1920 x 1080)
				zoom = "100";
				break;
		}
		document.getElementById("pdfIframe").src  = "./web/viewer.html?file="+docFilename+"#page="+pageNum+"&zoom="+zoom;
	}
	var myEfficientFn = debounce(function() { 
		loadIframeDoc();
	}, 250);
	$(document).ready(function() { loadIframeDoc(); });
	window.addEventListener('resize', myEfficientFn); */

	//stores scroll position to make working on a particular edit line easier
	function storeScrollPos() {
	    var y = document.getElementById("docScrollDiv").scrollTop;
	    sessionStorage.setItem('docScrollpos', y);
	}

	//recovers scroll position (only pertinant if same session and same year/month) to make working on a particular edit line easier
	window.onload = function() {
		if(sessionStorage.getItem('docScrollpos')) {
			document.getElementById("docScrollDiv").scrollTop = sessionStorage.getItem('docScrollpos');
		}
	};

</script>

<style>
.recHeadingCell, .recHeadingCellFilt {
	font-weight: bold;
	width: 70px;
	height: 27px;
	margin: 5px;
	border-style: solid;
    border-width: 1px;
    border-color: #C0C0C0;
    padding: 3px;
	background-color: #FFFFFF;
	cursor: pointer;
}

.recHeadingCellFilt {
	background-color: #FFFF80;
}

.recStickyCell {
	width: 70px;
	height: 20px;
	margin: 5px;
	border-style: solid;
    border-width: 1px;
    border-color: #C0C0C0;
    padding: 3px;
	background-color: #eaeafa;
	cursor: pointer;
}

.recTotalsCell {
	width: 70px;
	height: 20px;
	margin: 5px;
	border-style: solid;
    border-width: 1px;
    border-color: #C0C0C0;
    padding: 3px;
	background-color: #FF80FF;
	cursor: pointer;
}

.recTotalsCellGood {
	width: 70px;
	height: 20px;
	margin: 5px;
	border-style: solid;
    border-width: 1px;
    border-color: #C0C0C0;
    padding: 3px;
	background-color: #00FF00;
	cursor: pointer;
}

.recTotalsCellBad {
	width: 70px;
	height: 20px;
	margin: 5px;
	border-style: solid;
    border-width: 1px;
    border-color: #C0C0C0;
    padding: 3px;
    color: #FFFFFF;
	background-color: #FF0000;
	cursor: pointer;
}

.recGroupIndicationCell {
	width: 70px;
	height: 10px;
	margin: 5px;
	border-style: solid;
    border-width: 1px;
    border-color: #C0C0C0;
    padding: 3px;
	background-color: #FFFFFF;
	cursor: pointer;
}

.recGroupIndicationCellSel {
	background-color: #000000;
}

.recDisplayCell, .recDisplayCellSel, .recDisplayCellEdit, .recDisplayCellWarn, .recDisplayCommonDoc, .recDisplayCellFilter, .recDisplayCellNotReconciled, .recDisplayCellBlankOut, .recDisplayCellEditBlank, .recDisplayCommonDocBlank, .recDisplayCellBlank, .recFamDisplayCell {
	color: #000000;
	font-size: 11px;
	width: 70px;
	height: 22px;
	margin: 5px;
	border-style: solid;
    border-width: 1px;
    border-color: #C0C0C0;
    padding: 3px;
	background-color: #FFFFFF;
	cursor: pointer;
	-webkit-user-select: none;  /* Chrome all / Safari all */
    -moz-user-select: none;     /* Firefox all */
    -ms-user-select: none;      /* IE 10+ */
    user-select: none;          /* Likely future */  
}

.recDisplayCellBlank {
	color: #FFFFFF;
	background-color: #FFFFFF;
}

.recDisplayCellSel {
	background-color: #C0C0F0;
}

.recDisplayCellEdit {
	color: #000000;
	background-color: #9090FF;
}

.recDisplayCellEditBlank {
	color: #9090FF;
	background-color: #9090FF;
}

.recDisplayCellWarn {
	background-color: #FFD030;
}

.recDisplayCommonDoc {
	color: #000000;
	background-color: #E0E0E0;
}

.recDisplayCommonDocBlank {
	color: #E0E0E0;
	background-color: #E0E0E0;
}

.textBoxEditClass, .textBoxWarnClass {
	font-size: 11px;
	width:70px;
	height:20px;
	border:none;
	text-align:right;
}

.textBoxEditClass {
	background: rgba(255, 255, 255, 0.0);
}

.textBoxWarnClass {
	background: rgba(255, 208, 48, 1.0);
}

.recDisplayCellFilter {
	background-color: #FFFF80;
}

.recDisplayCellNotReconciled {
	color: #FFFFFF;
	background-color: #FF0000;
}

.recDisplayCellBlankOut {
	color: #FFFFFF;
}

.recFamDisplayCell {
	width: 50px;
}






</style>

<div style="float:left; background-color:#8800FF;">

<?php
include_once("./".$sdir."menu.php");
include_once("./".$sdir."monthSelSideBar.php"); //months select sidebar
//$nonVolatileArray["genrlAryRndms"] = $genrlAryRndms;
include_once("./".$sdir."saveSession.php");
?>

<form style="float:left;" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">

	<!-- Start of overall records display - headings, sticky buttons, scrollable records, and totals-->
	<div class="docDisplayContainerRecs">

		<!-- Start of headings div -->
		<div id="headings" style="width:1020px; border-bottom:1px solid #808080; margin-bottom:5px;  margin-left:5px; background-color:#FFFFFF;">
			<table border=0 style="border-collapse: separate; border-spacing: 0px; font-size: 11px; "onclick="clickHeader(event)" >
			<?php
			$recDisplayCell = array();
			for ($colIdxAryIdx = 0; $colIdxAryIdx <= 12; $colIdxAryIdx++) { 
				if (FALSE === array_search($colIdxAryIdx, $nonVolatileArray["filtersAry"]["columnIdx"])) {
					$headingClass[] = "recHeadingCell";
					$recDisplayCell[] = "recDisplayCell";
				}
				else {
					$headingClass[] = "recHeadingCellFilt";
					$recDisplayCell[] = "recDisplayCellFilter";
				}
				if (($nonVolatileArray["headingIdForGroupSel"] == $colIdxAryIdx) &&  ($nonVolatileArray["headingIdForGroupSel"] != 0)) { //group column so highlight top bar, but not for default of 0 (date)
					$groupIndicatorClass[] = "recGroupIndicationCellSel";
				}
				else { //ordinary so no highlight
					$groupIndicatorClass[] = "recGroupIndicationCell";
				}
			}

			formValHolder("stickyActive-0", "no"); //value holder flags that indicate whether a sticky value has been set or not - allows sticky value of "" if desired, to make clearing cells easy
			formValHolder("stickyActive-1", "no");
			formValHolder("stickyActive-2", "no");
			formValHolder("stickyActive-3", "no");
			formValHolder("stickyActive-4", "no");
			formValHolder("stickyActive-5", "no");
			formValHolder("stickyActive-6", "no");
			formValHolder("stickyActive-7", "no");
			formValHolder("stickyActive-8", "no");
			formValHolder("stickyActive-9", "no");
			formValHolder("stickyActive-10", "no");
			formValHolder("stickyActive-11", "no");
			formValHolder("stickyActive-12", "no");

			$headingWidth = "";
			
			    tableStartRow("", "", "",            TRUE);
			    	tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-0");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-1");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-2");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-3");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-4");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-5");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-6");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-7");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-8");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-9");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-10");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-11");
			        tableCell("recStickyCell", $headingWidth, "", TRUE, "sticky-12");
			    tableEndRow(TRUE); 
			
			    tableStartRow("", "", "",            TRUE);
			    	tableCell($headingClass[0], $headingWidth, $headingAry[0],   TRUE, "heading-0");
			        tableCell($headingClass[1], $headingWidth, $headingAry[1],   TRUE, "heading-1");
			        tableCell($headingClass[2], $headingWidth, $headingAry[2],   TRUE, "heading-2");
			        tableCell($headingClass[3], $headingWidth, $headingAry[3],   TRUE, "heading-3");
			        tableCell($headingClass[4], $headingWidth, $headingAry[4],   TRUE, "heading-4");
			        tableCell($headingClass[5], $headingWidth, $headingAry[5],   TRUE, "heading-5");
			        tableCell($headingClass[6], $headingWidth, $headingAry[6],   TRUE, "heading-6");
			        tableCell($headingClass[7], $headingWidth, $headingAry[7],   TRUE, "heading-7");
			        tableCell($headingClass[8], $headingWidth, $headingAry[8],   TRUE, "heading-8");
			        tableCell($headingClass[9], $headingWidth, $headingAry[9],   TRUE, "heading-9");
			        tableCell($headingClass[10], $headingWidth, $headingAry[10], TRUE, "heading-10");
			        tableCell($headingClass[11], $headingWidth, $headingAry[11], TRUE, "heading-11");
			        tableCell($headingClass[12], $headingWidth, $headingAry[12], TRUE, "heading-11");
			    tableEndRow(TRUE);

			    tableStartRow("", "", "",            TRUE);
			    	tableCell($groupIndicatorClass[0], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[1], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[2], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[3], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[4], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[5], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[6], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[7], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[8], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[9], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[10], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[11], $headingWidth, "", TRUE, "");
			        tableCell($groupIndicatorClass[12], $headingWidth, "", TRUE, "");
			        
			    tableEndRow(TRUE);

			?>
			</table>
		</div>
		<!-- End of headings div -->

		<!-- Start of scrollable records div -->
		<div id="docScrollDiv" onscroll="storeScrollPos()" style="flex-grow:1; height:400px; width:1020px; margin-left:5px;  background-color:#FFFFFF; overflow:auto;">
			<table border=0 style="border-collapse: separate; border-spacing: 0px; font-size: 12px;" onclick="clickField(event)" onchange="changeField(event)">
			<?php
			$idrArry = array();
			$cellWidth = "";
			$docFileNamesIdx = 0;
			$readOnly = "";
			//if ($allowedToEdit) { //sets readonly for withdrawn and paidin text boxes
				$readOnly = "readonly";
			//}
			foreach ($recsAry as $rowIdx => $recd) { //loop through all persOrgs selected for display
				$idrArry[] = $recd["idR"]; //add next idR to idrArry to create array of idRs to be converted to javascript array for use by sticky-shift column filling routine
				nameHolder($recd["idR"]."-docRnd", $docFileNameAryRndms[$docFileNamesAry[$docFileNamesIdx]]); //assign to name holder the random for the current doc file name
				$withdrawnId = $recd["idR"]."-3-wthdrwn";
				$paidInId = $recd["idR"]."-4-paidin";
				$withdrawn = fourThreeOrTwoDecimals($recd["amountWithdrawn"]);
				$paidIn = fourThreeOrTwoDecimals($recd["amountPaidIn"]);
				$reconciledClass = $recDisplayCell[8];
				if ($recd["reconciled"] == "No") {
					$reconciledClass = "recDisplayCellNotReconciled";
				}
				if ($recd["reconciledDate"] == "01-01-2000") {
					$reconciledClass = "recDisplayCellBlankOut";
				}

	            tableStartRow("", "", "", TRUE);
	        		tableCell($recDisplayCell[0], $cellWidth, $recd["recordDate"],    TRUE, $recd["idR"]."-0");
	                tableCell($recDisplayCell[1], $cellWidth, $recd["persOrgStr"],    TRUE, $recd["idR"]."-1");
	                tableCell($recDisplayCell[2], $cellWidth, $recd["categoryStr"],   TRUE, $recd["idR"]."-2");
	                if ($allowedToEdit) { //no readonly for withdrawn and paidin text boxes COULD BE A MORE ELEGENT WAY TO DO THIS - DOESN'T WORK WITH VARIABLE FOR READONLY FOR SOME REASON
		                tableCell($recDisplayCell[3], $cellWidth, 
		                	"<input class = 'textBoxEditClass' id='$withdrawnId' type='text' value='$withdrawn' size='12px'/>",             
		                																TRUE, $recd["idR"]."-3");
		                tableCell($recDisplayCell[4], $cellWidth, 
		                	"<input class = 'textBoxEditClass' id='$paidInId' type='text' value='$paidIn' size='12px'/>",                
		                																TRUE, $recd["idR"]."-4");
		            }
		            else  { //sets readonly for withdrawn and paidin text boxes
		                tableCell($recDisplayCell[3], $cellWidth, 
		                	"<input class = 'textBoxEditClass' id='$withdrawnId' type='text' value='$withdrawn' size='12px' readonly/>",             
		                																TRUE, $recd["idR"]."-3");
		                tableCell($recDisplayCell[4], $cellWidth, 
		                	"<input class = 'textBoxEditClass' id='$paidInId' type='text' value='$paidIn' size='12px' readonly/>",                
		                																TRUE, $recd["idR"]."-4");
		            }
	                tableCell($recDisplayCell[5], $cellWidth, $recd["accountStr"],    TRUE, $recd["idR"]."-5");
	                tableCell($recDisplayCell[6], $cellWidth, $recd["budgetStr"],     TRUE, $recd["idR"]."-6");
	                tableCell($recDisplayCell[7], $cellWidth, $recd["reference"],     TRUE, $recd["idR"]."-7");
	                tableCell($reconciledClass,   $cellWidth, $recd["reconciledDate"],     TRUE, $recd["idR"]."-8");
	                tableCell($recDisplayCell[9], $cellWidth, $recd["umbrellaStr"],     TRUE, $recd["idR"]."-9");
	                tableCell($recDisplayCell[10], $cellWidth, $recd["docVarietyStr"], TRUE, $recd["idR"]."-10");
	                tableCell($recDisplayCell[11], $cellWidth, $recd["note"],          TRUE, $recd["idR"]."-11");
	                tableCell("recFamDisplayCell", $cellWidth, $recd["familyStatus"],          TRUE, $recd["idR"]."-12");
	            tableEndRow(TRUE);
	            $docFileNamesIdx++;
			}     
			?>
			</table>
		</div>
		<!-- End of scrollable records div -->

		<!-- Start of totals outer container div  -->
		<div  style="width:1020px; height:75px; border-top:1px solid #808080; margin-top:5px; margin-left:5px;  background-color:#FF6060;">

			<table border=0 style="border-collapse: separate; border-spacing: 0px; font-size: 12px;" >
			<?php
			if ($nonVolatileArray["displayBankAcc"]) { //display sum and comparison data specifically for bank account reconciliation at bottom of table 
				$headingWidth = "";
				$headingClass = "recDisplayCell";
				$linesDiff = ($lineCount -1) - $bankStmtLines;
				$diff = fourThreeOrTwoDecimals(($totalRecncldDocsPaidIn - $totalRecncldDocsWithdrawn) - ($bankStmtPaidIn - $bankStmtWithdrawn), TRUE);
				$diffClass = "recTotalsCellBad";
				if ($diff == "0.00") {
					$diffClass = "recTotalsCellGood";
				}
				$linesDiffClass = "recTotalsCellBad";
				if ($linesDiff == 0) {
					$linesDiffClass = "recTotalsCellGood";
				}
				//filtered totals display
			    tableStartRow("", "", "",            TRUE);
			    	tableCell("recTotalsCell", $headingWidth, "NOT",         TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        tableCell("recTotalsCell", $headingWidth, "Bank", 		TRUE);
			        tableCell("recTotalsCell", $headingWidth, fourThreeOrTwoDecimals($bankStmtWithdrawn, TRUE),         TRUE);
			        tableCell("recTotalsCell", $headingWidth, fourThreeOrTwoDecimals($bankStmtPaidIn, TRUE),         TRUE);
			        tableCell("recTotalsCell", $headingWidth, fourThreeOrTwoDecimals($bankStmtPaidIn - $bankStmtWithdrawn, TRUE),         TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",         TRUE);
		        	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
		        	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
		        	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
		        	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        tableCell("recTotalsCell", $headingWidth, "Lines Diff",         TRUE);
			        tableCell($linesDiffClass, $headingWidth, $linesDiff,         TRUE);
			    tableEndRow(TRUE);
			    //financial year totals display
			    tableStartRow("", "", "",            TRUE);
			    	tableCell("recTotalsCell", $headingWidth, "INTERACTIVE!",              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "", 			   TRUE);
			        tableCell("recTotalsCell", $headingWidth, "Trans", 		TRUE);
			        tableCell("recTotalsCell", $headingWidth, fourThreeOrTwoDecimals($totalRecncldDocsWithdrawn, TRUE),         TRUE);
			        tableCell("recTotalsCell", $headingWidth, fourThreeOrTwoDecimals($totalRecncldDocsPaidIn, TRUE),         TRUE);
			        tableCell("recTotalsCell", $headingWidth, fourThreeOrTwoDecimals($totalRecncldDocsPaidIn - $totalRecncldDocsWithdrawn, TRUE),              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",  TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "Tot Diff",    TRUE);
			        tableCell($diffClass, $headingWidth, $diff,      TRUE);
			    tableEndRow(TRUE);
			}
			else { //normal display of totals and sums at bottom of table 
				$headingWidth = "";
				$headingClass = "recDisplayCell";
				//filtered totals display
			    tableStartRow("", "", "",            TRUE);
			    	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        tableCell("recTotalsCell", $headingWidth, "Filtered", TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",         TRUE, 'filtWithdrawnTotalsBut');
			        tableCell("recTotalsCell", $headingWidth, "",         TRUE, 'filtPaidInTotalsId');
			        tableCell("recTotalsCell", $headingWidth, "",         TRUE, 'filtBalId');
			        tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        if ($allowedToEdit) { //show these trouble shooting real html page totals if edit allowed
				        tableCell("recTotalsCell", $headingWidth, "Tot Withdrn",   TRUE);
				        tableCell("recTotalsCell", $headingWidth, $totalWithdrawn, TRUE);
				        tableCell("recTotalsCell", $headingWidth, "Tot Paidin",    TRUE);
				        tableCell("recTotalsCell", $headingWidth, $totalPaidIn,    TRUE);
			        }
			        else { //just show empty cells
			        	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        	tableCell("recTotalsCell", $headingWidth, "",         TRUE);
			        }
			        tableCell("recTotalsCell", $headingWidth, "Line Count",         TRUE);
			        tableCell("recTotalsCell", $headingWidth, $lineCount,         TRUE);
			    tableEndRow(TRUE);
			    //financial year totals display
			    tableStartRow("", "", "",            TRUE);
			    	tableCell("recTotalsCell", $headingWidth, "",              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "", 			   TRUE);
			        tableCell("recTotalsCell", $headingWidth, "Reconciled",  TRUE);
			        tableCell("recTotalsCell", $headingWidth, "", 			   TRUE, 'reconciledWithdrawnTotalsId');
			        tableCell("recTotalsCell", $headingWidth, "",     		   TRUE, 'reconciledPaidInTotalsId');
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE, 'reconciledBalId');
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "Doc Totals",              TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE, 'docOnlyWithdrawnId');
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE, 'docOnlyPaidInId');
			        tableCell("recTotalsCell", $headingWidth, "",              TRUE, 'docOnlyBalId');
			        tableCell("recTotalsCell", $headingWidth, "",    TRUE);
			        tableCell("recTotalsCell", $headingWidth, "",      TRUE);
			    tableEndRow(TRUE);
			} 
			?>
			</table>
			
		</div>
		<!-- End of totals outer container div  -->	

		</form>
		
	</div>
	<!-- End of overall records display - headings, sticky buttons, scrollable records, and totals-->


<div style="float:left;">
	<?php
	calJavaScrpInteractnLite($calId, FALSE, "calContainer", "calContainerWarning", "calDaysOfMnthDiv", "calMnthsDiv", "calYearsDiv", "calDaysOfMnthBut", "calDaysOfMnthButSelected", "calMnthBut", "calMnthButSelected", "calYearBut", "calYearButSelected", htmlspecialchars($_SERVER["PHP_SELF"]), $menuRandomsArray["Ajax both ways with All Records"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	butPanelJSInteracStrOnly($persOrgId, FALSE, "calContainer", "NameSelBtn", "nameSelBtnBlank", "NameSelBtnSelected", $orgPersonsListAry, $indexPage, $menuRandomsArray["Ajax Items 2 ways with All Records"], "command", $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Show Records For Full Year"], "namesSelPanelAddBut", $genrlAryRndms["orgsOrPersons"], $genrlAryRndms["orgOrPersonName"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	butPanelJSInteracStrOnly($transCatId, FALSE, "calContainer", "NameSelBtn", "nameSelBtnBlank", "NameSelBtnSelected", $transCatListAry, $indexPage, $menuRandomsArray["Ajax Items 2 ways with All Records"], "command", $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Show Records For Full Year"], "namesSelPanelAddBut", $genrlAryRndms["orgPerCategories"], $genrlAryRndms["categoryName"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	butPanelJSInteracStrOnly($accId, FALSE, "calContainer", "NameSelBtn", "nameSelBtnBlank", "NameSelBtnSelected", $accountListAry, $indexPage, $menuRandomsArray["Ajax Items 2 ways with All Records"], "command", $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Show Records For Full Year"], "namesSelPanelAddBut", $genrlAryRndms["accounts"], $genrlAryRndms["accountName"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	butPanelJSInteracStrOnly($budgId, FALSE, "calContainer", "NameSelBtn", "nameSelBtnBlank", "NameSelBtnSelected", $budgetListAry, $indexPage, $menuRandomsArray["Ajax Items 2 ways with All Records"], "command", $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Show Records For Full Year"], "namesSelPanelAddBut", $genrlAryRndms["budgets"], $genrlAryRndms["budgetName"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	textPanelJSInteracStrOnly($refId, FALSE, "calContainer", "NameSelBtn", "nameSelBtnBlank", "NameSelBtnSelected", $budgetListAry, $indexPage, $menuRandomsArray["Ajax Items 2 ways with All Records"], "command", $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Show Records For Full Year"], "namesSelPanelAddBut", $genrlAryRndms["budgets"], $genrlAryRndms["budgetName"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);


	calJavaScrpInteractnLite($recId, FALSE, "calContainer", "calContainerWarning", "calDaysOfMnthDiv", "calMnthsDiv", "calYearsDiv", "calDaysOfMnthBut", "calDaysOfMnthButSelected", "calMnthBut", "calMnthButSelected", "calYearBut", "calYearButSelected", htmlspecialchars($_SERVER["PHP_SELF"]), $menuRandomsArray["Ajax both ways with All Records"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	butPanelJSInteracStrOnly($umbrlId, FALSE, "calContainer", "NameSelBtn", "nameSelBtnBlank", "NameSelBtnSelected", $umbrellaListAry, $indexPage, $menuRandomsArray["Ajax Items 2 ways with All Records"], "command", $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Show Records For Full Year"], "namesSelPanelAddBut", $genrlAryRndms["docTags"], $genrlAryRndms["docTagName"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	butPanelJSInteracStrOnly($docTypeId, FALSE, "calContainer", "NameSelBtn", "nameSelBtnBlank", "NameSelBtnSelected", $docTypeListAry, $indexPage, $menuRandomsArray["Ajax Items 2 ways with All Records"], "command", $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Show Records For Full Year"], "namesSelPanelAddBut", $genrlAryRndms["docVarieties"], $genrlAryRndms["docVarietyName"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	textPanelJSInteracStrOnly($notesId, FALSE, "calContainer", "NameSelBtn", "nameSelBtnBlank", "NameSelBtnSelected", $budgetListAry, $indexPage, $menuRandomsArray["Ajax Items 2 ways with All Records"], "command", $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Show Records For Full Year"], "namesSelPanelAddBut", $genrlAryRndms["budgets"], $genrlAryRndms["budgetName"], "recDisplayCellWarn", "recDisplayCellEdit", $recoveredSessionAryCommitRnd);

	?>
	<div class="calContainer" id="defaultButPanel" style="display:none"> </div> <!-- default container for when calendar or items select panels are not shown, takes up space as filler -->

	<div id="subButPanel8" style="clear:both; width:135px; background-color:#FFFF00; height:44px; float:left; display:none;">
		<button class="subMenuBtn" type="button" onclick="atomicCall(false, 'Earlier Statement')"><i class="fas fa-arrow-left"></i></button>
		<button class="subMenuBtn" type="button" onclick="atomicCall(false, 'Later Statement')"><i class="fas fa-arrow-right"></i></button>		
		<button class="subMenuBtn" type="button" onclick="atomicCall(false, 'Reset accWorkedOn')"><i class="fas fa-trash"></i></button>
	</div>

	<div id="subButPanelDeflt" style="clear:both; width:135px; background-color:#FFFF80; height:44px; float:left; display:none;">
	</div>

</div>

	<div style="clear:both; width:1210px; background-color:#D0D0FF; height:40px; float:left;">
		<form id="docEdit" class="form" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
			<?php
			formValHolder("filteredColsCsv", implode(",", $nonVolatileArray["filtersAry"]["columnIdx"])); //placeholder for 
			formValHolder("previousDocRnd", "x"); //placeholder for previous doc random - used to check if there has been a change of document when a new record is clicked on
			formValHolder("mouseClickPreviousTime", 100); //a small number so the difference between the current timeand it, for first mouse click, will always be larger than the double click limit
			formValHolder("storeSelectedRowIdx", 0);
			formValHolder("storeSelectedRecordIdR", 0);
			formValHolder("bankStatementIdRForDownload", $newRowId);
			formValHolder("previousObscureFile", "obscureTest.php");
			formValHolder("endDate", $endDate);
			namedValHolder("sessionCommitRnd", $recoveredSessionAryCommitRnd);
			$displayEditButton = $allowedToEdit;
			if ($displayEditButton) {
			?>
				<button class="subMenuBtn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["duplicateRec"];?>><i class="fas fa-clone"></i> Duplicate Row</button>
				<button class="subMenuBtn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["deleteRec"];?>><i class="fas fa-file-excel"></i> Delete Row</button>
			<?php
			}
			?>


				<?php
				if (!$nonVolatileArray["editFamilies"]) {
					if ($nonVolatileArray["familyMaster"] == "All") { //include kids in display so show button as set
					?>
				    <button class="subMenuBtnSel" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["expandFamilies"];?>><i class="fas fa-plus-square"></i> Expand Families</button>
				    <?php
					}
					else { //no kids in display so show button as unset
				    ?>
				    <button class="subMenuBtn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["expandFamilies"];?>><i class="fas fa-plus-square"></i> Expand Families</button>
				    <?php
					}
				}
			    ?>



				<?php
				if ($allowedToEdit && !$nonVolatileArray["editFamilies"]) { //inhibit show everything button unless allowed to edit
					if ($nonVolatileArray["showAbsolutlyEverything"]) { //include everything in display so show button as set
					?>
				    <button class="subMenuBtnSel" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["showEverything"];?>><i class="fas fa-file-excel"></i> Show Everything</button>
				    <?php
					}
					else { //not everything in display so show button as unset
				    ?>
				    <button class="subMenuBtn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["showEverything"];?>><i class="fas fa-file-excel"></i> Show Everything</button>
				    <?php
					}
				}
			    ?>

			    <?php
				if ($allowedToEdit) { //inhibit show everything button unless allowed to edit
					if ($nonVolatileArray["editFamilies"]) { //include everything in display so show button as set
					?>
				    <button class="subMenuBtnSel" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["toggleEditFamilies"];?>><i class="fas fa-file-excel"></i> Edit Families</button>
				    <?php
					}
					else { //not everything in display so show button as unset
				    ?>
				    <button class="subMenuBtn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["toggleEditFamilies"];?>><i class="fas fa-file-excel"></i> Edit Families</button>
				    <?php
					}
				}
			    ?>


			    



				<button class="subMenuBtn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["Download"];?>><i class="fas fa-download"></i> Download as spreadsheet (csv file)</button>


				<?php //button for selecting bank statement reconciliation view (bank statement first line then all debits/credits below and red/green indicators)
				if ($nonVolatileArray["displayBankAcc"]) { //include everything in display so show button as set
					formValHolder("runNormalBalFunc", "No");
					?>
				    <button class="subMenuBtnSel" type="button" onclick="document.getElementById('9EqXb73R1Pg').submit()"><i class="fas fa-tasks"></i></button>
				    <?php
				}
				else { //not everything in display so show button as unset
					formValHolder("runNormalBalFunc", "Yes");
				    ?>
				    <button class="subMenuBtn" type="button" onclick="document.getElementById('9EqXb73R1Pg').submit()"><i class="fas fa-tasks"></i></button>
				    <?php
				}
			    ?>

				<button class="subMenuBtn" type="button" onclick="document.getElementById('xPKThZPMNO8').submit()"><i class="fas fa-arrow-up"></i></button>  <!-- get previous bank statement -->
				<button class="subMenuBtn" type="button" onclick="document.getElementById('uO6Oefk0Rep').submit()"><i class="fas fa-arrow-down"></i></button> <!-- get next bank statement -->
				
		</form>
	</div>

</div>


	<form id="xPKThZPMNO8" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
    <?php
    	//this form is submited by javascript 'document.getElementById("xPKThZPMNO8").submit()' which is implemented by 'previous bank statement' key
        formValHolder("command", $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["prevStatement"]); //this page!
        formValHolder("aheadBankStatementIdR", $newRowId); //defaults to newRowId (passed and possibly increnmented/decremented from this button click) and is further set in 'clickField(event)' whenever a cell is clicked on
    ?>
    </form>

    <form id="uO6Oefk0Rep" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
    <?php
    	//this form is submited by javascript 'document.getElementById("uO6Oefk0Rep").submit()' which is implemented by 'next bank statement' key
        formValHolder("command", $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["nextStatement"]); //this page!
        formValHolder("behindBankStatementIdR", $newRowId); //defaults to newRowId (passed and possibly increnmented/decremented from this button click) and is further set in 'clickField(event)' whenever a cell is clicked on
    ?>
    </form>

	<form id="9EqXb73R1Pg" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
    <?php
    	//this form is submited by javascript 'document.getElementById("9EqXb73R1Pg").submit()' which is implemented by 'display bank statement with reconciled records' key
        formValHolder("command", $menuRandomsArray["Show Records For Full Year"]."-".$genrlAryRndms["toggleBankAccDisplay"]); //this page!
        formValHolder("bankStatementIdR", $newRowId); //defaults to newRowId (passed and possibly increnmented/decremented from this button click) and is further set in 'clickField(event)' whenever a cell is clicked on
    ?>
    </form>

    <form id="fn445dya48d" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
    <?php
    	//this form is submited by javascript 'document.getElementById("fn445dya48d").submit();'' which is implemented by 'if (event.ctrlKey)' in 'function clickField(event)' it passes filter settings
    	namedValHolder("sessionCommitRnd", $recoveredSessionAryCommitRnd); //used to verify currency of session array
        formValHolder("command", $menuRandomsArray["Show Records For Full Year"]); //this page!
        formValHolder("filterRecordIdR", 0); //this value is set in 'clickField(event)' whenever a cell is clicked on'
    ?>
    </form>

    <form id="e7j4UT42v4x" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
    <?php
    	//this form is submited by javascript 'document.getElementById("e7j4UT42v4x").submit();' via 'function toggleSingleFamDisplay(id)' < 'toggleClickedFamily(id, 12);' < 'function clickField(event)'
    	namedValHolder("sessionCommitRnd", $recoveredSessionAryCommitRnd); //used to verify currency of session array
        formValHolder("command", $menuRandomsArray["Show Records For Full Year"]); //this page!
        formValHolder("idRforFamily", 0); //this value is set in 'function toggleSingleFamDisplay(id)' whenever a family column cell is clicked
    ?>
    </form>

    <form id="ff48f454n8f" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
    <?php
    	//this form is submited by javascript 'document.getElementById("ff48f454n8f").submit();' via 'function groupSet(id)' < 'function clickField(event)'
    	namedValHolder("sessionCommitRnd", $recoveredSessionAryCommitRnd); //used to verify currency of session array
        formValHolder("command", $menuRandomsArray["Show Records For Full Year"]); //this page!
        formValHolder("headingCol", 0); //this value is set in 'function toggleSingleFamDisplay(id)' whenever a family column cell is clicked
    ?>
    </form>

<iframe id="pdfIframe" name="docIframe" class="docDisplayIframeRecsFullYr" >
    <p>Your browser does not support iframes.</p>
</iframe>

<script type="text/javascript">
	var currentKey = "none"; //holds the keyboard key that is currently held down - for use when a cell is clicked to know if a particular command (like create new parent) has been selected
	var createParent = "no"; //flag to indicate to JS functions that create new parent is in operation
	var accountBankLinksArry = {"General":"RBS 8252", "Reserved":"Clyde 5477"}; //proxy for database table that will be created and editable - to describe the relationships between working accounts and the bank accounts they are linked to. The array provides the information required to display the correct bank's statements for a given working account and to enable the buttons that select the statements by date

	// INITIALISATION SECTION TO SET SELECTION TO ROW 0 AND SET UP THE CALENDAR, CATEGORY, ACCOUNT, AND BUDGET SELECTION PANELS TO ROW 0 VALUES. INITIALLY ALL PANELS EXCEPT CALENDAR WILL BE HIDDEN
	doEverything(valGet("seltdRowCellId"), false, false, 0); //initialise to top row left cell selecting all rows woth same doc. All panels except calendar (and that only if edit allowed) are hidden.

	function toggleSingleFamDisplay(id) { //function to toggle the currently selected family on and off - if id doesn't represent a family the destination php script does nothing
		valSet("idRforFamily", id.split("-")[0])
		document.getElementById("e7j4UT42v4x").submit();
	}

	function groupSet(id) {
		valSet("headingCol", id.split("-")[1])
		document.getElementById("ff48f454n8f").submit();
	}

	function atomicCall(shiftKeyPressed, auxButtonTxt) {
		atomicAjaxCall(  //function that combines updateFromSticky(id, valueStr), displayBalances(id), upDatewithdrnPaidin(id), newDocFileName(id) in one atomic ajax call to server to prevent race conditions
			valGet("seltdRowCellId"),
			inrGet("sticky-"+valGet("seltdRowCellId").split("-")[1]),
			'moneyCellIdHldr',
            '<?php echo $indexPage;?>',
            '<?php echo $menuRandomsArray["Ajax Atomic"];?>',
            'recDisplayCellWarn',
            'textBoxWarnClass',
        	'filtWithdrawnTotalsBut',
        	'filtPaidInTotalsId',
        	'filtBalId',
        	'reconciledWithdrawnTotalsId',
        	'reconciledPaidInTotalsId',
        	'reconciledBalId',
        	'docOnlyWithdrawnId',
        	'docOnlyPaidInId',
        	'docOnlyBalId',
        	'<?php echo $startDate;?>',
        	'<?php echo $endDate;?>',
        	currentKey,
        	createParent,
        	<?php echo json_encode($idrArry);?>, //convert php array of all idRs disiplayed to javascript array and pass as argument
        	shiftKeyPressed,
        	accountBankLinksArry,
        	auxButtonTxt
		);
	}

	function newDocFileName(id) { //function to update displayed doc according to the cell that has been selected
		ajaxUpdateDocFileName(
			id,
			'<?php echo $indexPage;?>',
			'<?php echo $menuRandomsArray["Update Doc File Name"];?>',
		);
	}
</script>

<?php

/* THINK THIS ISN'T USED FOR ANYTHING
formValHolder("tbl", $genrlAryRndms["allRecords"]);
*/




$timeEnd = microtime(true); //use microtime to time how long this page takes to execute
$timeTaken = $timeEnd - $timeStart;
//print_r("Time Taken = ".$timeTaken." secs");

include_once("./".$sdir."tail.php");
?>

