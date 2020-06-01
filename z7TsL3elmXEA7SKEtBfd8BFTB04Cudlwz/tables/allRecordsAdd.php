<?php
/*
 * Alter various tables to change VARCHAR(??)  NOT NULL, to INT NOT NULL and DATE to DATETIME etc.
 */

include_once("/var/monytalyData/Q3dj4G8/globals.php");
 
try {

/*
$stmt = $conn->prepare('ALTER TABLE allRecords ADD COLUMN fileName VARCHAR(50) NOT NULL DEFAULT "" AFTER docId'); //####### ADD TO createTables5.php
//$stmt->execute(array());

$stmt = $conn->prepare('UPDATE allRecords INNER JOIN docCatalog ON (docId = id) SET fileName = CONCAT(fileNameDate, "-", fileNameNum, ".", fileExt)');
//$stmt->execute(array());


$stmt = $conn->prepare('ALTER TABLE allRecords ADD COLUMN docType INT NOT NULL DEFAULT 0 AFTER fileName'); //####### ADD TO createTables5.php
//$stmt->execute(array());

$stmt = $conn->prepare('UPDATE allRecords INNER JOIN docCatalog ON (docId = id) SET allRecords.docType = docCatalog.docVariety');
$stmt->execute(array());


$stmt = $conn->prepare('ALTER TABLE allRecords ADD COLUMN umbrella INT NOT NULL DEFAULT 0 AFTER linkedAccOrBudg'); //####### ADD TO createTables5.php
$stmt->execute(array());

$stmt = $conn->prepare('UPDATE allRecords INNER JOIN docCatalog ON (docId = id) SET allRecords.umbrella = docCatalog.docTag');
$stmt->execute(array());


$stmt = $conn->prepare('ALTER TABLE allRecords ADD COLUMN parent INT NOT NULL DEFAULT 0 AFTER recordDate'); //####### ADD TO createTables5.php
$stmt->execute(array());

$stmt = $conn->prepare('UPDATE allRecords INNER JOIN docCatalog ON (docId = id) SET allRecords.parent = docCatalog.parentDocRef');
$stmt->execute(array());


$stmt = $conn->prepare('SELECT idR, docId FROM allRecords WHERE parent = -1');
$stmt->execute(array());
$stmt2 = $conn->prepare('UPDATE allRecords SET parent = :idR WHERE (parent = :docId) OR (docId = :docId)');
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	$stmt2->execute(array('idR' => $row['idR'], 'docId' => $row['docId']));
	print_r($row['idR']." ".$row['docId']."</br>");
}


$stmt = $conn->prepare('UPDATE allRecords INNER JOIN docCatalog ON (docId = id) SET allRecords.recordNotes = docCatalog.notes');
$stmt->execute(array());


$stmt = $conn->prepare('ALTER TABLE allRecords ADD COLUMN compound INT NOT NULL DEFAULT 0 AFTER parent'); //####### ADD TO createTables5.php
$stmt->execute(array());


$stmt = $conn->prepare('ALTER TABLE allRecords ADD COLUMN transCatgry INT NOT NULL DEFAULT 0 AFTER personOrOrg'); //####### ADD TO createTables5.php
$stmt->execute(array());

$stmt = $conn->prepare('UPDATE allRecords SET transCatgry = persOrgCategory ');
$stmt->execute(array());


$stmt = $conn->prepare('ALTER TABLE allRecords ADD COLUMN budget INT NOT NULL DEFAULT 0 AFTER accWorkedOn'); //####### ADD TO createTables5.php
$stmt->execute(array());

$stmt = $conn->prepare('UPDATE allRecords SET budget = linkedAccOrBudg ');   
$stmt->execute(array());
*/

$stmt = $conn->prepare('UPDATE allRecords SET reconciledDate = otherDocsCsv WHERE CHAR_LENGTH(otherDocsCsv) = 10');
$stmt->execute(array());

$stmt = $conn->prepare('UPDATE allRecords SET referenceInfo = otherDocsCsv WHERE CHAR_LENGTH(otherDocsCsv) = 9');
$stmt->execute(array());


/*
$stmt = $conn->prepare('       SELECT SUBSTRING_INDEX(fileName, ".", 1) AS nameNum FROM allRecords WHERE fileName LIKE CONCAT(:file, "%")        ');
$stmt->execute(array('file'=>'2018-06-04'));
while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
	print_r($row["nameNum"]."</br>");
}
//SUBSTRING_INDEX(nameNum, "-", -3) AS num */

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

//$stmt = $conn->prepare('ALTER TABLE rrrr CHANGE oldColumn newColumn INT NOT NULL DEFAULT 0'); //change orgPerAccCatName to categoryName in all php docs
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

//$stmt = $conn->prepare('UPDATE xxxx SET budget = CONCAT("1",budget,"-01") WHERE character_length(budget) = 5');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE ttttt SET ccccc = 0');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE gggg SET sssss = "reuse" WHERE uuuu = "copyButSticky"');
//$stmt->execute(array());

//$stmt = $conn->prepare('UPDATE dddd SET jjjjj = "2018-02-28" WHERE id = 505');
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


