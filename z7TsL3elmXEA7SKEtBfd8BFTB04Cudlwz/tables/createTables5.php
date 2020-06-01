<?php
ini_set('display_errors',1);
error_reporting(E_ALL);

include_once("/var/monytalyData/Q3dj4G8/globals.php");

print_r("In createTables5.php !! ");

$dropAllTables = FALSE;


/*  LIST OF TABLES - 'S' means static, i.e. not changing with user input but generated as reference data for dropdown boxes etc. Can be replicated by generating fresh table and running php script.
    allTransactions
    docOrImageCatlg
    tablesDescrps 
*/
//$stmt = $conn->prepare('DROP TABLE IF EXISTS personSession'); 
//$stmt->execute(array());
//FOREIGN KEY (x) REFERENCES y(z)     Template for foreign keys.


/* drops existing messages table (if $dropAllTables TRUE) and then creates a new one */
if ($dropAllTables) { 
    $stmt = $conn->prepare('DROP TABLE IF EXISTS messages'); 
    $stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS messages (
    id                      INT             NOT NULL AUTO_INCREMENT,
    messageStr              VARCHAR(1000)   NOT NULL DEFAULT "",
    dateTimeRecCreated      DATETIME        NOT NULL DEFAULT "2000-01-01 00:00:01",
    status                  VARCHAR(50)     NOT NULL DEFAULT "Active",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>messages Table created/renewed :) </h3>"; 
} else {
    echo "<h3>messages Table NOT created :( </h3>"; 
}

if ($dropAllTables) {
    $stmt = $conn->prepare('DROP TABLE IF EXISTS allRecords'); 
    $stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS allRecords (
    idR                     INT             NOT NULL AUTO_INCREMENT,
    docId                   INT             NOT NULL DEFAULT 0,
    recordType              INT             NOT NULL DEFAULT 0,
    dateTimeRecCreated      DATETIME        NOT NULL DEFAULT "2000-01-01 00:00:01",
    recordDate              DATE            NOT NULL DEFAULT "2000-01-01",
    personOrOrg             INT             NOT NULL DEFAULT 0,
    persOrgCategory         INT             NOT NULL DEFAULT 0,
    accWorkedOn             INT             NOT NULL DEFAULT 0,
    referenceInfo           VARCHAR(500)    NOT NULL DEFAULT "",
    linkedAccOrBudg         INT             NOT NULL DEFAULT 0,
    reconcilingAcc          INT             NOT NULL DEFAULT 0,
    reconciledDate          DATE            NOT NULL DEFAULT "2000-01-01",
    reconcileDocId          INT             NOT NULL DEFAULT 0,
    otherDocsCsv            VARCHAR(100)    NOT NULL DEFAULT "",    
    amountWithdrawn         DECIMAL(17,7)   NOT NULL DEFAULT 0,
    amountPaidIn            DECIMAL(17,7)   NOT NULL DEFAULT 0,
    statusR                 VARCHAR(100)    NOT NULL DEFAULT "",
    recordNotes             VARCHAR(500)    NOT NULL DEFAULT "",
    PRIMARY KEY (idR),
    INDEX (docId)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>allRecords Table created/renewed :) </h3>"; 
} else {
    echo "<h3>allRecords Table NOT created :( </h3>"; 
}



if ($dropAllTables) {
	$stmt = $conn->prepare('DROP TABLE IF EXISTS personSession'); 
	$stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS personSession (
    id                      INT         	NOT NULL AUTO_INCREMENT,
    personName		        VARCHAR(100)    NOT NULL DEFAULT "",
    passwordHash			VARCHAR(300)   	NOT NULL DEFAULT "", 
    forcePwChange           BOOLEAN         NOT NULL DEFAULT TRUE,   
    customSessionCookie    	VARCHAR(200) 	NOT NULL DEFAULT "",
    superuser               BOOLEAN         NOT NULL DEFAULT FALSE,
    loggedIn				BOOLEAN			NOT NULL DEFAULT FALSE,
    clientFingerprint       VARCHAR(200)    NOT NULL DEFAULT "",
    serialMenuArray         MEDIUMBLOB      NOT NULL DEFAULT "",
    menusAvailableCsv		VARCHAR(1000) 	NOT NULL DEFAULT "",
    serialDocArray          MEDIUMBLOB      NOT NULL DEFAULT "",
    docFileName             VARCHAR(50)     NOT NULL DEFAULT "",
    unixSecsSessStartTime   INT             NOT NULL DEFAULT 0,
    unixSecsAtLastAccess	INT             NOT NULL DEFAULT 0,
    dateTimeCreated         DATETIME        NOT NULL DEFAULT "2000-01-01 00:00:01",
    status                  VARCHAR(100)    NOT NULL DEFAULT "",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>personSession Table created/renewed :) </h3>"; 
} else {
    echo "<h3>personSession Table NOT created :( </h3>"; 
}


/* drops existing docOrImageCatlg table (if $dropAllTables TRUE) and then creates a new one.
The information on the filename, say 2018-02-03-003, contains the creation date, 
the number of the file created on that date (in the case that there are say 5 separate files) and the 
number of files (pages) in the document. A scan of 7 pages for one document (say a letter) will only have one 
file name in the table but this would indicate 7 separate files by the numOfPAges field, each with a 3 digit sufix (allowing 999 pages max).
The docOrImageVariety indicates the general type of document i.e. receipt, cheque, statement etc. dateEarliestRecord and dateLatestRecord indicate the earliest date and latest date of record refered to by the document if several receipts or cash transactions are shown in one document - if appropriate. */
if ($dropAllTables) {
	$stmt = $conn->prepare('DROP TABLE IF EXISTS docCatalog'); 
	$stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS docCatalog (
    id                      INT         	NOT NULL AUTO_INCREMENT,    
    fileNameDate   			DATE     		NOT NULL DEFAULT "2000-01-01",
    fileNameNum   			INT         	NOT NULL DEFAULT 0,
    fileExt           		VARCHAR(50) 	NOT NULL DEFAULT "",
    numOfPages              INT             NOT NULL DEFAULT 0,
    subDir					VARCHAR(70)   	NOT NULL DEFAULT "",
    docVariety				INT             NOT NULL DEFAULT 0,
    docTag					INT             NOT NULL DEFAULT 0,
    parentDocRef			INT         	NOT NULL DEFAULT 0,
    docFullyReferenced		BOOLEAN			NOT NULL DEFAULT FALSE,
    docDataCompleted		BOOLEAN			NOT NULL DEFAULT FALSE,
    uploadPersId            INT             NOT NULL DEFAULT 0,
    private                 BOOLEAN         NOT NULL DEFAULT FALSE,
    dateTimeUploaded		DATETIME     	NOT NULL DEFAULT "2000-01-01 00:00:01",
    dateEarliestRecord		DATE     		NOT NULL DEFAULT "2000-01-01",
    dateLatestRecord		DATE    		NOT NULL DEFAULT "2000-01-01",
    status                  VARCHAR(100)    NOT NULL DEFAULT "",
    notes       			VARCHAR(1000)  	NOT NULL DEFAULT "",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>docOrImageCatlg Table created/renewed :) </h3>"; 
} else {
    echo "<h3>docOrImageCatlg Table NOT created :( </h3>"; 
}



/* drops existing tablesDescrps table (if $dropAllTables TRUE) and then creates a new one */
if ($dropAllTables) { 
	$stmt = $conn->prepare('DROP TABLE IF EXISTS tablesDescrps'); 
	$stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS tablesDescrps (
    id                      INT             NOT NULL AUTO_INCREMENT,
    tableName               VARCHAR(70)     NOT NULL DEFAULT "",
    tableDescription        VARCHAR(5000)   NOT NULL DEFAULT "",
    status                  VARCHAR(100)    NOT NULL DEFAULT "",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>tablesDescrps Table created/renewed :) </h3>"; 
} else {
    echo "<h3>tablesDescrps Table NOT created :( </h3>"; 
}


/* drops existing orgsOrPersons table (if $dropAllTables TRUE) and then creates a new one */
if ($dropAllTables) { 
    $stmt = $conn->prepare('DROP TABLE IF EXISTS orgsOrPersons'); 
    $stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS orgsOrPersons (
    id                      INT             NOT NULL AUTO_INCREMENT,
    orgOrPersonName         VARCHAR(100)     NOT NULL DEFAULT "",
    status                  VARCHAR(50)     NOT NULL DEFAULT "Active",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>orgsOrPersons Table created/renewed :) </h3>"; 
} else {
    echo "<h3>orgsOrPersons Table NOT created :( </h3>"; 
}


/* drops existing docVarieties table (if $dropAllTables TRUE) and then creates a new one */
if ($dropAllTables) { 
    $stmt = $conn->prepare('DROP TABLE IF EXISTS docVarieties'); 
    $stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS docVarieties (
    id                      INT             NOT NULL AUTO_INCREMENT,
    docVarietyName          VARCHAR(100)     NOT NULL DEFAULT "",
    status                  VARCHAR(50)     NOT NULL DEFAULT "Active",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>docVarieties Table created/renewed :) </h3>"; 
} else {
    echo "<h3>docVarieties Table NOT created :( </h3>"; 
}

/* drops existing docTags table (if $dropAllTables TRUE) and then creates a new one */
if ($dropAllTables) { 
    $stmt = $conn->prepare('DROP TABLE IF EXISTS docTags'); 
    $stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS docTags (
    id                      INT             NOT NULL AUTO_INCREMENT,
    docTagName              VARCHAR(100)     NOT NULL DEFAULT "",
    status                  VARCHAR(50)     NOT NULL DEFAULT "Active",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>docTags Table created/renewed :) </h3>"; 
} else {
    echo "<h3>docTags Table NOT created :( </h3>"; 
}


/* drops existing orgPerCategories table (if $dropAllTables TRUE) and then creates a new one */
if ($dropAllTables) { 
    $stmt = $conn->prepare('DROP TABLE IF EXISTS orgPerCategories'); 
    $stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS orgPerCategories (
    id                      INT             NOT NULL AUTO_INCREMENT,
    categoryName        VARCHAR(100)     NOT NULL DEFAULT "",
    status                  VARCHAR(50)     NOT NULL DEFAULT "Active",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>orgPerCategories Table created/renewed :) </h3>"; 
} else {
    echo "<h3>orgPerCategories Table NOT created :( </h3>"; 
}

/* drops existing budgets table (if $dropAllTables TRUE) and then creates a new one */
if ($dropAllTables) { 
    $stmt = $conn->prepare('DROP TABLE IF EXISTS budgets'); 
    $stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS budgets (
    id                      INT             NOT NULL AUTO_INCREMENT,
    budgetName              VARCHAR(100)    NOT NULL DEFAULT "",
    status                  VARCHAR(50)     NOT NULL DEFAULT "Active",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>budgets Table created/renewed :) </h3>"; 
} else {
    echo "<h3>budgets Table NOT created :( </h3>"; 
}

/* drops existing accounts table (if $dropAllTables TRUE) and then creates a new one */
if ($dropAllTables) { 
    $stmt = $conn->prepare('DROP TABLE IF EXISTS accounts'); 
    $stmt->execute(array());
}
$stmt = $conn->prepare('CREATE TABLE IF NOT EXISTS accounts (
    id                      INT             NOT NULL AUTO_INCREMENT,
    accountName             VARCHAR(100)    NOT NULL DEFAULT "",
    status                  VARCHAR(50)     NOT NULL DEFAULT "Active",
    PRIMARY KEY (id)
    ) ENGINE = InnoDB');
if ($stmt->execute(array())) {
    echo "<h3>accounts Table created/renewed :) </h3>"; 
} else {
    echo "<h3>accounts Table NOT created :( </h3>"; 
}

?>



