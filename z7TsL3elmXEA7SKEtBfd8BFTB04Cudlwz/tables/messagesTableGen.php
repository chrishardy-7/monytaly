<?php
/*
 * Populates or updates orgsOrPersons table.
 */

include_once("/var/monytalyData/Q3dj4G8/globals.php");
 
try {
    $stmt = $conn->prepare('TRUNCATE TABLE messages');
    $stmt->execute(array());    

    //populate table
    $sql = "INSERT INTO messages (messageStr, dateTimeRecCreated) VALUES (?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array("One")); 
    sleep(1);
    $stmt->execute(array("Two")); 
    sleep(1);
    $stmt->execute(array("Three")); 
    sleep(1);
    $stmt->execute(array("Four")); 
    sleep(1);
    $stmt->execute(array("Five")); 
    sleep(1);
    $stmt->execute(array("Six")); 
    sleep(1);
    $stmt->execute(array("Seven")); 
    sleep(1);
    $stmt->execute(array("Eight")); 
    sleep(1);
    $stmt->execute(array("Nine")); 
    sleep(1);
    $stmt->execute(array("Ten")); 

    $sql = "INSERT INTO messages (messageStr, status, dateTimeRecCreated) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array("1", "Index")); 

} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
echo 'messages table has been truncated and repopulated';
?>


