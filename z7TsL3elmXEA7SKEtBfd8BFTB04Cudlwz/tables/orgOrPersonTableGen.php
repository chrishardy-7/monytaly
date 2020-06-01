<?php
/*
 * Populates or updates orgsOrPersons table.
 */

include_once("/var/monytalyData/Q3dj4G8/globals.php");
 
try {
    $stmt = $conn->prepare('TRUNCATE TABLE orgsOrPersons');
    $stmt->execute(array());    

    //populate table
    $sql = "INSERT INTO orgsOrPersons (orgOrPersonName) VALUES (?)";
    $stmt = $conn->prepare($sql);
    $stmt->execute(array("RBS")); 
    $stmt->execute(array("Jean Claude")); 
    $stmt->execute(array("Angel"));
    $stmt->execute(array("Patient"));
    $stmt->execute(array("BT"));
    $stmt->execute(array("Scottish Water"));
    $stmt->execute(array("S Lanarkshire"));
    $stmt->execute(array("Robertson Tr"));
    $stmt->execute(array("Victor"));
    $stmt->execute(array("Lem Lem"));
    $stmt->execute(array("Max"));
    $stmt->execute(array("Didi"));
    $stmt->execute(array("Grace"));
    $stmt->execute(array("David"));
    $stmt->execute(array("Hacking & Patr"));
    $stmt->execute(array("ASCO Fire"));
    $stmt->execute(array("Scottish Pwr"));
    $stmt->execute(array("HMRC"));
    $stmt->execute(array("Chris"));
    $stmt->execute(array("Dot"));
    $stmt->execute(array("Jurgen"));
    $stmt->execute(array("Susan"));
    $stmt->execute(array("Pret a Mange"));
    $stmt->execute(array("Gregs"));
    $stmt->execute(array("Ardenglen Ho"));
    $stmt->execute(array("BUS"));
    $stmt->execute(array("BU Insurance"));
    $stmt->execute(array("Dance Club"));
    $stmt->execute(array("Anne Luti"));
    $stmt->execute(array("Keith"));
    $stmt->execute(array("Eileen"));
    $stmt->execute(array("Alex"));
    $stmt->execute(array("Anne Mamba"));
    $stmt->execute(array("Post Office"));
    $stmt->execute(array("Viking"));
    $stmt->execute(array("Ke Ke"));
    $stmt->execute(array("Alexi"));
    $stmt->execute(array("B&Q"));
    $stmt->execute(array("Gla Private Hr"));
    $stmt->execute(array("Busn Stream"));
    $stmt->execute(array("Cynthia"));
    $stmt->execute(array("Darlinda"));
    $stmt->execute(array("Gla Council"));
    $stmt->execute(array("Therese"));
    $stmt->execute(array("Bible Centre"));
    $stmt->execute(array("Jenny Wong"));
    $stmt->execute(array("Community Info Src"));
    $stmt->execute(array("Adrian Flux"));
    $stmt->execute(array("CCLI"));
    $stmt->execute(array("Gordon"));
    $stmt->execute(array("Scott Cunningham"));
    $stmt->execute(array("David MacF"));
    $stmt->execute(array("Emadelddin H"));
    $stmt->execute(array("Indi Skills"));
    $stmt->execute(array("TWAM"));
} catch(PDOException $e) {
    echo 'ERROR: ' . $e->getMessage();
}
echo 'orgsOrPersons table has been truncated and repopulated';
?>


