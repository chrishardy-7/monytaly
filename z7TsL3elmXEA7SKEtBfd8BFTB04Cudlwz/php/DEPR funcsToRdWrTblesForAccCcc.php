
<?php

/* Gets the sum of amountWithdrawn for all rows where record date is between the given dates and $columnToMatch field == $matchValue and $filterStr terms are met. Similarly gets the sum of amountPaidIn for the same where criteria and subtracts amountWithdrawn from it to create a balance. amountWithdrawn, amountPaidIn and the calculated balance are returned in an associative array. If $useReconciledInsteadOfRecordDate is used and set to TRUE the reconciled dates will be used instead, thus providing balance information that should match bank statement balances.
DEPRECATED 2021-04-28 AS SUSPECT NO LONGER REFERENCED BY ANY CODE */
function getFilterStrBalDataDEPR($columnToMatch, $recRowId, $recStartDate, $recEndDate, $filterStr, $familyChoice, $useReconciledInsteadOfRecordDate = FALSE) {
    global $conn;

    switch ($columnToMatch) { //choose the appropriate item list for the field that is being updated.
      case "personOrOrg":
        $matchValue = getRecFieldValueAtRow($recRowId, $columnToMatch); //get value of field from allRecords table
        break;
      case "transCatgry":
        $matchValue = getRecFieldValueAtRow($recRowId, $columnToMatch); //get value of field from allRecords table
        break;
      case "accWorkedOn":
        $matchValue = getRecFieldValueAtRow($recRowId, $columnToMatch); //get value of field from allRecords table
        break;
      case "budget":
        $matchValue = getRecFieldValueAtRow($recRowId, $columnToMatch); //get value of field from allRecords table
        break;
      case "referenceInfo":
        $matchValue = '\''.getRecFieldValueAtRow($recRowId, $columnToMatch).'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
        break;
      case "umbrella":
        $matchValue = getRecFieldValueAtRow($recRowId, $columnToMatch); //get value of field from allRecords table
        break;
      case "docType":
        $matchValue = getRecFieldValueAtRow($recRowId, $columnToMatch); //get value of field from allRecords table
        break;
      case "recordNotes":
        $matchValue = '\''.getRecFieldValueAtRow($recRowId, $columnToMatch).'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
        break;
      default:
        break;
    }

    $result = array("withdrawn" => 0.00, "paidIn" => 0.00, "balance" => 0.00); //initialise array to sensible default values in case no data is found in both withdrawn and paidIn columns
    try {
        if ($familyChoice == "NoKids") {
            if ($useReconciledInsteadOfRecordDate) { //use the reconciled dates for calculating the amounts instead of the normal record dates
                $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE ((:recStartDate <= reconciledDate) AND (reconciledDate <= :recEndDate)) AND ((parent = 0) OR (parent = idR)) AND ('.$columnToMatch.' = '.$matchValue.') '.$filterStr.' AND statusR = "Live"');
            }
            else { //use the record dates for calculating the amounts
                $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) AND ((parent = 0) OR (parent = idR)) AND ('.$columnToMatch.' = '.$matchValue.') '.$filterStr.' AND statusR = "Live"');
            }
        }
        elseif ($familyChoice == "All") {
            if ($useReconciledInsteadOfRecordDate) { //use the reconciled dates for calculating the amounts instead of the normal record dates
                $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE (((:recStartDate <= recordDate) AND (recordDate <= :recEndDate) AND (parent = 0)) OR ((:recStartDate <= parentDate) AND (parentDate <= :recEndDate))) AND  ('.$columnToMatch.' = '.$matchValue.') '.$filterStr.' AND statusR = "Live"');
            }
            else { //use the record dates for calculating the amounts
                $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE (((:recStartDate <= recordDate) AND (recordDate <= :recEndDate) AND (parent = 0)) OR ((:recStartDate <= parentDate) AND (parentDate <= :recEndDate))) AND  ('.$columnToMatch.' = '.$matchValue.') '.$filterStr.' AND statusR = "Live"');
            }
        }
        else {
            if ($useReconciledInsteadOfRecordDate) { //use the reconciled dates for calculating the amounts instead of the normal record dates
                $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE parent = '.$familyChoice.' AND  ('.$columnToMatch.' = '.$matchValue.') '.$filterStr.' AND statusR = "Live"');
            }
            else { //use the record dates for calculating the amounts
                $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE parent = '.$familyChoice.' AND  ('.$columnToMatch.' = '.$matchValue.') '.$filterStr.' AND statusR = "Live"');
            }
        }
        $stmt->execute(array('recStartDate' => $recStartDate, 'recEndDate' => $recEndDate));    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($row) { //make sure data has been found
            $withdrawn = $row["withdrawn"];
            $paidIn = $row["paidIn"];
            $result["withdrawn"] = $withdrawn;
            $result["paidIn"] = $paidIn;
            $result["balance"] = $paidIn - $withdrawn;
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $result;
}

/* PROBABLY NOT MUCH DIFF FROM getMultDocDataAry() YET, BUT MAYBE IF IT IS EDITED A BIT! Need to write a description for this function - the while loop might need to be changed because perhaps record rows where a doc isn't allocated to a persOrg should be returned too. CHANGED TEMPORARILY! */
//$familySetting can be one of 3 values: "NoKids", "All", 527. If the number is sent then only records with the parent value set to that number will be returned (which includes the parent and children)
function getPivotTableAryDEPR($recStartDate, $recEndDate, $filterStr, $order, $familyChoice, $restrictFilterStr, $groupBy) {
    global $conn;
    try {
        $groupByStr = "";
        if ($groupBy) { //if $groupBy contains a group info (i.e. 'transCatgry') create a GROUP BY term
            $groupByStr = " GROUP BY ".$groupBy;
        }
        if ($familyChoice == "NoKids") { //only show family parents among other general records
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) AND ((parent = 0) OR (parent = idR)) '.$filterStr.$restrictFilterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY budget DESC');
            }
            else {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) AND ((parent = 0) OR (parent = idR)) '.$filterStr.$restrictFilterStr.' AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');
            }
        }
        elseif ($familyChoice == "All") { //show all records including general, parents and children
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE (((:recStartDate <= recordDate) AND (recordDate <= :recEndDate) AND (parent = 0)) OR ((:recStartDate <= parentDate) AND (parentDate <= :recEndDate))) '.$filterStr.$restrictFilterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY sumAmountWithdrawn DESC');
            }
            else {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE (((:recStartDate <= recordDate) AND (recordDate <= :recEndDate) AND (parent = 0)) OR ((:recStartDate <= parentDate) AND (parentDate <= :recEndDate))) '.$filterStr.$restrictFilterStr.' AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');
            }
        }
        else { //show only parents and children of the family id passed as a numeric argument in $familyChoice
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE parent = '.$familyChoice.' '.$filterStr.$restrictFilterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY sumAmountWithdrawn DESC'); //order by doc filename first so split docs don't occur - may mean docs appear in wierd order
            }
            else {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE parent = '.$familyChoice.' '.$filterStr.$restrictFilterStr.' AND statusR = "Live" ORDER BY '.$order.' fileName, recordDate'); //order by doc filename first so split docs don't occur - may mean docs appear in wierd order
            }
        }
        if ($familyChoice == "everything") { //only show everything in date order without regard for whether it's a child or parent or ordinary item
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) '.$filterStr.$restrictFilterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY sumAmountWithdrawn DESC');
            }
            else {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate))   '.$filterStr.$restrictFilterStr.' AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');
            }
        }
        $stmt->execute(array('recStartDate' => $recStartDate, 'recEndDate' => $recEndDate));    
        $docsDetails = array();
        //return multiple rows, one per persOrg - contains columns from allRecords that most probably will be used
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            if ($groupBy) { //if group by information is passed to this function copy the summed withdrawn and paidin data to the ordinary withdrawn and paidin array positions
                $row["amountWithdrawn"] = $row["sumAmountWithdrawn"];
                $row["amountPaidIn"] = $row["sumAmountPaidIn"];
            }
            //if ($row["personOrOrg"]) { //only accrue data if this row contains a persOrg (i.e. it incorporates persOrg data from allRecords and not just a 0 persOrg placeholder)
            if (TRUE) { //accrue all data rows so if personOrg set to empty (0) it will not be lost from view! (eventually get rid of this if statement if happy with this)
                $docsDetails[] = $row;
            }
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      //pr($docsDetails);
      return $docsDetails;
}



/* Temp function, for one off use, to create parentDate for all child records using the recordDate from each parent when indicated by parent == idR. 
DEPRECATED 2021-04-28 AS NO LONGER REFERENCED BY ANY CODE.  */
function createParentDatesDEPR() {
    global $conn;
    try {
        $stmtPar = $conn->prepare('SELECT idR, recordDate FROM allRecords WHERE (parent = idR) AND statusR = "Live"');
        $stmtPar->execute(array());
        $parentIdAry = array();
        while ($row = $stmtPar->fetch(PDO::FETCH_ASSOC)) {
            $parentDataAry[] = array("idR" => $row["idR"], "recordDate" => $row["recordDate"]);
        }
        foreach ($parentDataAry as $parentData) {
            //pr($parentData);
            $stmt = $conn->prepare('UPDATE allRecords SET parentDate = :parentDate WHERE parent = :parentIdR');
            $stmt->execute(array('parentDate' => $parentData["recordDate"], "parentIdR" => $parentData["idR"]));
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}


/* Returns an array of data for a single document specified by $fileNameNumExt. Only non-private document data will be returned unless a valid $userId is supplied upon which ONLY private document data for the given userId will be returned. This function uses a join to access rows in docCatalog and allRecords tables. If $returnCsvPersOrg is set to TRUE (default) a single row with docOrganisationOrPerson as csv is returned (contains columns from allRecords that may not be used as they are not complete). If $returnCsvPersOrg is set to FALSE multiple rows, one per persOrg are returned containing all columns from allRecords, and in this cases rows from allRecords containing just a 0 persOrg placeholder will not be returned. */
function getSingleDocDataAryDEPR($fileNameNumExt, $returnCsvPersOrg = TRUE, $userId = 0) {
    global $conn;
    $nameNumExtArray = explode('.', $fileNameNumExt); //section to derive filename date, number and extension from $fileNameNumExt - [0] => [2018-05-07-02], [1] => [pdf]
    $nameNumArray = explode('-', $nameNumExtArray[0]);  //turn date, number back into an array   
    $docYear = $nameNumArray[0];
    $docMonth = $nameNumArray[1];
    $docDayOfMonth = $nameNumArray[2];
    $fileNameDate = $docYear."-".$docMonth."-".$docDayOfMonth;
    $fileNameNum = $nameNumArray[3];
    $fileExt = $nameNumExtArray[1];
    $privateStatus = "(private = FALSE)";
    if (0 < $userId) { //if userId given 
        $privateStatus = "(private = TRUE) AND (uploadPersId = ".$userId.")";
    }
    try {
        $stmt = $conn->prepare('SELECT id, fileNameDate, fileNameNum, fileExt, numOfPages, docVariety, docTag, parentDocRef, docFullyReferenced, docDataCompleted, uploadPersId, private, dateTimeUploaded, dateEarliestRecord, dateLatestRecord, status, notes, idR, compoundColNum, dateTimeRecCreated, recordDate, personOrOrg, persOrgCategory, accWorkedOn, referenceInfo, linkedAccOrBudg, reconcilingAcc, reconciledDate, reconcileDocId, otherDocsCsv, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM docCatalog INNER JOIN allRecords ON docCatalog.id=allRecords.docId WHERE (fileNameDate = :fileNameDate) AND (fileNameNum = :fileNameNum) AND (fileExt = :fileExt) AND'.$privateStatus);
        $stmt->execute(array('fileNameDate' => $fileNameDate, 'fileNameNum' => $fileNameNum, 'fileExt' => $fileExt));    
        $docsDetails = array();
        if ($returnCsvPersOrg) { //return single row with docOrganisationOrPerson as csv - contains columns from allRecords that may not be used
            $firstPass = TRUE;
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($firstPass) { //first pass - happens once only!
                    $firstPass = FALSE;
                    $persOrgAry = array();
                    $docsDetails = $row;
                }
                if ($row["personOrOrg"]) { //if this row contains a persOrg (i.e. it incorporates persOrg data from allRecords and not just a 0 persOrg placeholder)
                    $persOrgAry[] = $row["personOrOrg"]; //add latest persOrg id for this doc id section to array
                }
            }
            if (!$firstPass) { //as long as loop has run at least once (so there will be data) save csv docOrganisationOrPerson to output array
                $docsDetails["docOrganisationOrPerson"] = implode(',', $persOrgAry);
            } 
        }
        else { //return multiple rows, one per persOrg - contains columns from allRecords that most probably will be used
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                if ($row["personOrOrg"]) { //only accrue data if this row contains a persOrg (i.e. it incorporates persOrg data from allRecords and not just a 0 persOrg placeholder)
                    $docsDetails[] = $row;
                }
            }
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      //pr($docsDetails);
      return $docsDetails;
}

/* Returns an array of copy button sticky values in an array('accWorkedOn' => 3, 'linkedAccOrBudg' => 7) etc. The data is stored in a single row where statusR = 'copyButSticky'. */
function getCopyButStickyValuesDEPR() {
    global $conn;
    $docVarietyNameArray = array();
    try {
        $stmt = $conn->prepare("SELECT personOrOrg, persOrgCategory, accWorkedOn, linkedAccOrBudg FROM allRecords WHERE statusR = 'copyButSticky'");
        $stmt->execute(array());
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $row;
}

?>


