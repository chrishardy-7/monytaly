<?php
/*
 * Truncates and repopulates the tablesDescrps table
 */

include_once("../php/dbaseCreds.php");
 
try {
    $stmt = $conn->prepare('TRUNCATE TABLE tablesDescrps');
    $stmt->execute(array());   

    //populate table
    $sql = "INSERT INTO tablesDescrps (tableName, tableDescription) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    $stmt->execute(array("accessLevels",         "accessLevels:

Contains csv fields used to control the access privileges of individual users.

Updated by editAccesses.php

Mainly accessed via persHashAccessLevels($personHash) in header&menu.php

Modified by tableDescrps.php!
"));
    

} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
echo 'tablesDescrps table has been truncated and repopulated';
?>


