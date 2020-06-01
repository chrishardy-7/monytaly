<?php
/*
 * Alter various tables to change VARCHAR(??)  NOT NULL, to INT NOT NULL and DATE to DATETIME etc.
 */

include_once("/var/monytalyData/Q3dj4G8/globals.php");
 
try {

//$stmt = $conn->prepare('ALTER TABLE cccc CHANGE zzzz zzzz VARCHAR(5000) NOT NULL');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE xxxx ADD COLUMN ssssss INT NOT NULL DEFAULT 0 AFTER vvvvv');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE zzz ADD COLUMN private BOOLEAN NOT NULL DEFAULT FALSE AFTER uploadPersId');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE cccc DROP INDEX hhhh');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE xxxx ADD UNIQUE (customerHash)');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE vvvv DROP COLUMN xxxx');
//$stmt->execute(array());


//$stmt = $conn->prepare('CREATE TABLE fffff LIKE vvvvv'); //change orgPerAccCatList to orgPerCategories in all php docs
//$stmt->execute(array());
//$stmt = $conn->prepare('INSERT fffff SELECT * FROM vvvvv');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE fffff CHANGE oldname newname VARCHAR(100) NOT NULL DEFAULT ""'); //change orgPerAccCatName to categoryName in all php docs
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE mmmm MODIFY ttt INT(11) NOT NULL DEFAULT 0');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE eeee MODIFY cccc INT(11) NOT NULL DEFAULT 0');
//$stmt->execute(array());


//$stmt = $conn->prepare('ALTER TABLE xxxx ADD INDEX jobIdIndx (jobID)');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE vvvv ADD INDEX jobAssignmentIdIndx (jobAssignmentID)');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE fff SET dddd="1,3,6,9" WHERE FIND_IN_SET(personToGiveAcsID, "1,2,3,11,12,13,14,15,16,17,18")');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE ddddd SET wwww = "2020-04-26-4.pdf", llll = "2020-04-04" WHERE zzz = 408');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE ttttt SET ccccc = 0');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE personSession SET passwordHash = "$2y$12$h2kUUyXg/.gZuO6Oefk0RepIHH6BhL4h.J5WPb0jfkj4mm/sUu5bG   " WHERE id = 1');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE rrrr SET reconciledDate = "2017-06-01" WHERE idR = 1729');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE ssss SET vvvv = "2018-02-28" WHERE idR = 1024');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE personSession SET loggedIn=FALSE WHERE id=1'); 
//$stmt->execute(array());

//$stmt = $conn->prepare('INSERT INTO vvvvv (persommName, passuywordHash) VALUES ("Chris", "")'); 
//$stmt->execute(array());

//$stmt = $conn->prepare('DROP TABLE ccc'); 
//$stmt->execute(array());

//$stmt = $conn->prepare('CHECK TABLE bbbb'); 
//$stmt->execute(array());

//$stmt = $conn->prepare('DROP INDEX column ON table'); //removes unique key
//$stmt->execute(array());

//$stmt = $conn->prepare('TRUNCATE TABLE ddddd');
//$stmt->execute(array());

//$stmt = $conn->prepare('TRUNCATE TABLE ffff');
//$stmt->execute(array());

//$stmt = $conn->prepare('ALTER TABLE xxxxx ADD UNIQUE INDEX yyyyy (jobID, allocatedPersonID, statusOnJob)');
//$stmt->execute(array());

//$stmt = $conn->prepare('DELETE FROM xxxx WHERE jobNumPrefix="OD"'); 
//$stmt->execute(array());

} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
//header( "Location: tablesChanged.php"); //jump to this page to prevent alterTables.php from executing more than once in case of jittery hand on return key!!

?>


