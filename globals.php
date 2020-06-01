<?php
date_default_timezone_set("Europe/London");

$host = "localhost";
$username = "xxxxx";
$password = "yyyyy";
$database = "zzzzz";

try {
    $conn = new PDO('mysql:host='.$host.';dbname='.$database.';charset=utf8', "$username", "$password",
    array(
    PDO::ATTR_EMULATE_PREPARES => true,
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_PERSISTENT
    )
);
} 

catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}


$subdir = "z7TsL3elmXEA7SKEtBfd8BFTB04Cudlwz/";

$_fileUploadsDir = "/var/monytalyData/".$dir."uploads"; //if this file (globals.php) is accessed on its own by, say, createTables5.php an error will be produced as $dir is undefimed. It is normally defined in index.php before this file is included. The error may not be of any consequence if this file is only being accessed to get the database credentials,

$_docTypeIdForUploadedScan = ??; //set to the id of the docVarietyName in docVarieties table that will be assigned to newly uploaded scans - displays something that says it's a new doc
?>
