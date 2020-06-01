<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//$thisFileName = "ajaxAllRecsItems2ways.php";
//saveMessage($thisFileName);

/* This function is intended to be called from Javascript. The cellId argument is split into the pre '-' rowId part, and post '-' columnId part which is used as an index in an array of table field names to get the field name that is to be updated with value. After value is written to the table the same field is read and echoed back to the calling javascript function as a confirmation that the operation has suceeded. */

$value  = sanPost("value");
$cellId = sanPost("cellId");

$cellIdAry = explode('-', $cellId);
$rowId = $cellIdAry[0];
$columnId  = $cellIdAry[1];

$fieldNameAry = array("recordDate", "personOrOrg", "transCatgry", "", "", "accWorkedOn", "budget", "referenceInfo", "reconciledDate", "umbrella", "docType", "recordNotes");
$fieldName = $fieldNameAry[$columnId]; //create fieldName from column id (0 - 11). The array would need to be changed if the column order (and therefore the cell ids) on the display page changes!!

$strInsteadOfKey = FALSE;
switch ($fieldName) { //choose the appropriate item list for the field that is being updated.
  case "personOrOrg":
    $itemAry = getOrgOrPersonsList(); //gets array of all possible orgsOrPersons in alphabetical order ie: array([1] => RBS [8] => Robertson Tr [17] => Scottish Pwr [22] => Susan)
    break;
  case "transCatgry":
    $itemAry = getorgPerCategories();
    break;
  case "accWorkedOn":
    $itemAry = getAccountList();
    break;
  case "budget":
    $itemAry = getBudgetList();
    break;
  case "referenceInfo":
    $strInsteadOfKey = TRUE;
    break;
  case "umbrella":
    $itemAry = getDocTagData();
    break;
  case "docType":
    $itemAry = getDocVarietyData();
    break;
  case "recordNotes":
    $strInsteadOfKey = TRUE;
    break;
  default:
    break;
}


if ($strInsteadOfKey) { //direct string write to table without converting to key
    $str = $value;

    try {
        $stmt = $conn->prepare('UPDATE allRecords SET '.$fieldName.'=? WHERE idR = ?');
        $stmt->execute(array($str, $rowId));

        $stmt = $conn->prepare('SELECT '.$fieldName.' FROM allRecords WHERE idR = ?');
        $stmt->execute(array($rowId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $updatedFieldStr = $row[$fieldName];

    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }

    echo $updatedFieldStr; //send the string
}
else { //first convert the string to the relevent key before writing to table and convert back to string afterwards
    $key = array_search($value, $itemAry);
    if (!$key) { //if key = FALSE (indicating the search string was not found, probably because it was intentionally "") set the key to 0 which effectively clears the field in allRecords to its default value
      $key = 0;
    }

    try {
        if ($allowedToEdit) {
          $stmt = $conn->prepare('UPDATE allRecords SET '.$fieldName.'=? WHERE idR = ?');
          $stmt->execute(array($key, $rowId));
        }

        $stmt = $conn->prepare('SELECT '.$fieldName.' FROM allRecords WHERE idR = ?');
        $stmt->execute(array($rowId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $updatedField = $row[$fieldName];

    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    $itemStr = aryValueOrZeroStr($itemAry, $updatedField); //recovers the string from the item array - if the key is not found in the array (most likely because it is 0) return ""

    echo $itemStr;
}
?>



