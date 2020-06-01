<?php
/*
 * Alter various tables to change VARCHAR(??)  NOT NULL, to INT NOT NULL and DATE to DATETIME etc.
 */

include_once("/var/monytaly.uk.globals/Q3dj4G8/globals.php");
 
try {



$stmt = $conn->prepare('INSERT INTO personSession (personName, passwordHash, customSessionCookie, forcePwChange, superuser, loggedIn, clientFingerprint, serialMenuArray, serialDocArray, unixSecsSessStartTime, unixSecsAtLastAccess, dateTimeCreated, status) VALUES ("Chris", "$2y$12$h2kUUyXg/.gZuO6Oefk0RepIHH6BhL4h.J5WPb0jfkj4mm/sUu5bG", "f4u8f9ju549854u", FALSE, TRUE, FALSE, "Mozilla", "MenuArray()", "DocArray()", 777, 888, NOW(), "Active")'); 
$stmt->execute(array());



} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
//header( "Location: tablesChanged.php"); //jump to this page to prevent alterTables.php from executing more than once in case of jittery hand on return key!!

?>


