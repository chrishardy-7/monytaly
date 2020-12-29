<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//$thisFileName = "ajaxAllRecsBothways.php";        ######## USED BY DATE COLUMNS - Date & Reconciled ########
//saveMessage($thisFileName);

//USED BY calJavaScrpInteractnLite() PHP FUNCTION VIA ajaxRecordsDateAndCellUpdate() JS FUNCTION

/* This function is intended to be called from Javascript. The cellId argument is split into the pre '-' rowId part, and post '-' columnId part which is used as an index in an array of table field names to get the field name that is to be updated with value. After value is written to the table the same field is read and echoed back to the calling javascript function as a confirmation that the operation has succeeded. */

$value      = sanPost("value");
$cellId = sanPost("cellId");

$cellIdAry = explode('-', $cellId);
$rowId = $cellIdAry[0];
$columnId  = $cellIdAry[1];

$fieldNameAry = array("recordDate", "personOrOrg", "transCatgry", "", "", "accWorkedOn", "budget", "referenceInfo", "reconciledDate", "umbrella", "docType", "recordNotes");
$fieldName = $fieldNameAry[$columnId]; //create fieldName from column id (0 - 11). The array would need to be changed if the column order (and therefore the cell ids) on the display page changes!!

try {
    $stmt = $conn->prepare('UPDATE allRecords SET '.$fieldName.'=? WHERE idR = ?'); //change record field
    $stmt->execute(array($value, $rowId));

    if ($fieldName == "recordDate") { //if the field being changed is a record date check if it is a parent being changed, and if so change the parentDate for it and all children
        $stmt = $conn->prepare('SELECT parent FROM allRecords WHERE idR = ?'); //get the parent id from the row that has just been changed
        $stmt->execute(array($rowId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $parent = $row['parent'];
        if ($parent == $rowId) { //if the row is a parent row set parentDate for it and all child rows to the new date
            $stmt = $conn->prepare('UPDATE allRecords SET parentDate = :parentDate WHERE parent = :parentIdR');
            $stmt->execute(array('parentDate' => $value, "parentIdR" => $rowId));
        }
    }

    $stmt = $conn->prepare('SELECT '.$fieldName.' FROM allRecords WHERE idR = ?'); //get the value of the updated field to feed back to the calling javascript function as confirmation
    $stmt->execute(array($rowId));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $updatedField = $row[$fieldName];

} catch(PDOException $e) {
  echo 'ERROR: ' . $e->getMessage();
  }

echo $updatedField;
?>


