<?php
/*
 * Populates or updates docTags table.
 */

include_once("/var/monytaly.uk.globals/Q3dj4G8/globals.php");
 
try {
    $stmt = $conn->prepare('TRUNCATE TABLE docTags');
    $stmt->execute(array());    

    //populate table
    $sql = "INSERT INTO docTags (docTagName) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array("Furniture Project")); 
    $stmt->execute(array("Church Building")); 
    $stmt->execute(array("Leaders"));
    $stmt->execute(array("Church"));
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
echo 'docTags table has been truncated and repopulated';
?>


