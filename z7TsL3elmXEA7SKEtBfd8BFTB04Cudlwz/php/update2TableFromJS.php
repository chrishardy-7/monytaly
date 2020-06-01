<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//following lines included in index.php - shold be ok to remove them
//$arrayOfPathToThisFile = explode("/", __DIR__); //produces an array of the path to this file (not the simlink to this file - but not sure of this assertion!!)
//$dir = array_reverse($arrayOfPathToThisFile)[2]."/"; //produces the parent dir of this file
//include_once("/var/monytaly.uk.globals/".$dir."globals.php"); //contains database credentials

/* This function is intended to be called from Javascript. Arguments are passed from javascript functions updateTable() and update2Table(). The passed arguments (other than values) are encoded with random keys to obfuscate the true table names and fields as destinations. If only one value is in use it is transmitted unchanged in plain text, if two are in use they are both converted to floating point format which will remove any characters that are not numbers, and if characters precede numbers 0 will be transmitted. */

$tableName  = array_search(sanPost("tableName"), $nonVolatileArray["genrlAryRndms"]);
$fieldName1  = array_search(sanPost("fieldName1"), $nonVolatileArray["genrlAryRndms"]);
$valueFloat1      = floatval(sanPost("value1"));
$value1 = sanPost("value1");
$fieldName2  = array_search(sanPost("fieldName2"), $nonVolatileArray["genrlAryRndms"]);
$valueFloat2      = floatval(sanPost("value2"));
$whereField = array_search(sanPost("whereField"), $nonVolatileArray["genrlAryRndms"]);
$whereValue = sanPost("whereValue");

try {
	if (!$fieldName2) { //if second fieldname not supplied just update one field
    	$stmt = $conn->prepare('UPDATE '.$tableName.' SET '.$fieldName1.'= :value1 WHERE '.$whereField.'= :whereValue');
    	$stmt->execute(array("value1" => $value1, "whereValue" => $whereValue));
    }
    else { //assume both fieldnames supplied so update both fields
    	$stmt = $conn->prepare('UPDATE '.$tableName.' SET '.$fieldName1.'= :value1, '.$fieldName2.'= :value2 WHERE '.$whereField.'= :whereValue');
    	$stmt->execute(array("value1" => $valueFloat1, "value2" => $valueFloat2, "whereValue" => $whereValue));
    }
    
} catch(PDOException $e) {
  echo 'ERROR: ' . $e->getMessage();
  }

//echo "Finished update2TableFromJS.php! ".$value1; //provide feedback for testing
?>


