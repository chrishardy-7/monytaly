<?php
/*
 * Populates or updates docVarieties table.
 */

include_once("/var/monytaly.uk.globals/Q3dj4G8/globals.php");
 
try {
    $stmt = $conn->prepare('TRUNCATE TABLE docVarieties');
    $stmt->execute(array());    

    //populate table
    $sql = "INSERT INTO docVarieties (docVarietyName) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array("Letter")); 
    $stmt->execute(array("Receipt")); 
    $stmt->execute(array("Bank Statement"));
    $stmt->execute(array("Bill"));
    $stmt->execute(array("Final Demand"));
    $stmt->execute(array("Minutes"));
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
echo 'docVarieties table has been truncated and repopulated';
?>


