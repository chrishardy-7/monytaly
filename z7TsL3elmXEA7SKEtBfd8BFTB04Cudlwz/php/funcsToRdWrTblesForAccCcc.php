
<?php

//$thisFileName = "funcsToRdWrTblesForAccCcc.php";

/* 
 * addNewDocTag
 * addNewDocVariety
 * addNewOrgOrPers
 * changePwAndClrFlag
 * clearSession
 * createNewParent
 * createNewUser
 * deleteRecRow
 * duplicateAllRecRow
 * filteredDocData
 * getAccountList
 * getBudgetList
 * getDateRangeBalData
 * getDbColumn
 * getDbRowsWhereMatching
 * getDocChildrenBalData
 * getdocData  DEPRECATED - REPLACED BY MULTIPURPOSE SearchDocData
 * getDocFileName //MAY NO LONGER BE NEEDED!
 * getdocIdInTable
 * getDocTagData
 * getDocVarietyData
 * getFamilyId
 * getFilteredBalData
 * getNextFileNumFromFileNameDate
 * getOrgOrPersonsList
 * getorgPerCategories
 * getSingleDocDataAry
 * getRecFieldValueAtRow
 * getReconciledDataAry
 * getUserData
 * getUserIdfromCookie
 * inactiveTimeout
 * insertArrayIntoTable
 * loggedIn
 * newCookiesAndResetActivTime
 * oldAndNewPwOK
 * PwResetFlagIsSet
 * recoveredDocRandomsArray
 * recoveredMenuRandomsArray
 * saveDocData
 * saveDocFileName
 * saveDocRandomsArray
 * saveMenuRandomsArray
 * sessionIs
 * sessionIsTEST //MAY NO LONGER BE NEEDED!
 * sessionTimeout
 * startSession
 * statusOfProposedParentDoc
 * updateAllRecsWithNewFileInfo
 * updateDocFilename
 * updateDocsTblWithNewFileInfo
 * updateTable
 * updateWithdrawnPaidin
 * userIdifPasswordMatches
 * writeReadAllRecordsItem
 */


/* 
THIS DESCRIPTION NEEDS UPDATING - docCatalog TABLE IS NO LONGER INVOLVED, ONLY allRecords. $parentDocRef IS NO LONGER USED EITHER, THIS FUNCTION IS PROVIDED AS PART OF showRecsForFullYr.php
Uses data in fileUploadReportArray to create new rows of information in docCatalog table for the document files that have just been uploaded. $subDirName is the subdir the files have been loaded into. If any uploaded file or set of files has the parent id textbox filled in the parent doc with the appropriate id has it's parentDocRef field set to -1 to indicate that it is a parent doc (but this doesn't indicate which docs it is the parent of, just that it is a parent, children would have to be identified by another query when needed). A basic blank record in the allRecords table is also created with the date set to dateEarliestRecord and persOrg set to 0.
--
#### THIS FUNCTION SHOULD ONLY BE RUN AFTER THE PROPOSED PARENT DOC HAS BEEN CHECKED BY statusOfProposedParentDoc() !!! (to prevent a doc that is already a child form being used as a parent doc!) ####
--
 DEPRECATED 2021-04-28 AS THINK NO LONGER REFERENCED BY ANY CODE */
function updateAllRecsWithNewFileInfo($fileUpldReportArry) {
    global $conn;
    global $_docTypeIdForUploadedScan; //from globals.php so it is set correctly for each server (where the doc type id in the docVarieties table may be different)
    $parent = 0;
    $parentDate = "2001-01-01";
    try {
        foreach ($fileUpldReportArry as  $index => $subArry) { 
            if ($subArry[6] == TRUE) { //contains details of a file that was created and not just an error
                $fileName = $subArry[2];
                $rowAry = array($fileName, $_docTypeIdForUploadedScan, dateFromFilenameNumDotPdf($fileName), $parent, $parentDate);
                $stmt = $conn->prepare("UPDATE allRecords SET fileName = ?, docType = ?, dateTimeRecCreated = NOW(), recordDate = ?, parent = ?, parentDate = ?, statusR = 'Live' WHERE statusR = 'Reuse' ORDER BY idR LIMIT 1");
                $stmt->execute($rowAry);
                $reusableRowFound = TRUE;
                if ($stmt->rowCount() == 0) {
                    $reusableRowFound = FALSE;
                }
                if (!$reusableRowFound) { //no reusable rows so create new record
                    $stmt = $conn->prepare("INSERT INTO allRecords (fileName, docType, dateTimeRecCreated, recordDate, parent, parentDate, statusR) VALUES (?, ?, NOW(), ?, ?, ?, 'Live')");
                    $stmt->execute($rowAry);
                }
            }
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      } 
}

/* Writes all values from $inputArry["writeValuesAry"] to rows in all records identified by the keys in $inputArry["writeValuesAry"]. The properties of each key are an array whose keys (indexes) are converted to allRecords table field (column) names like "accWorkedOn", "budget" etc before use. Index 13 is used for compound number and index 14 is used as reconcileDocId  (neither are displayed columns in the GUI). Where table fields are based on indexes to other tables (like 'budget') these indexes are retrieved based on the value passed, e.g. budget cell value of "Capability" = index of 11, so 11 is written to the allRecords table. Values of "" for these index related fields will be written as 0 (which doesn't actually exist in the tables from which the indexes are retrieved) as it is used to denote empty/clear in the allRecords table. After writing all values will be read back from allRecords and unconverted where necessary before being sent back to the client using $outputArry["aryBackFromWriteReadRows"]. This will be used in cell update confirmation on the client. */
function writeReadRows($inputArry, $outputArry, $_fieldNameAry, $tables, $allowedToEdit) {
    global $conn;
    if (array_key_exists("clearRowExcptRecDateAjaxSend", $inputArry)) { //only do update if the calling JS function has run

        foreach ($inputArry["writeValuesAry"] as $idR=>$rowOfCells) { //ROW loop through all idRs doing combined UPDATE followed by SELECT for each row
            $fieldNamesAry = []; //array to hold field names e.g. Array( [0]=>budget [1]=>umbrella )
            $fieldNamesAryQm = []; //array to hold field names with question marks e.g. Array( [0]=>budget=? [1]=>umbrella=? )
            $fieldValuesAry = []; //array to hold values used in $stmt->execute()
            $convertedFieldVal = []; //array to hold values of each row of reads to be fed back to client
            foreach ($rowOfCells as $colIndex=>$cellValue) { //COLUMN loop to format data for UPDATE and SELECT
                $fieldName = $_fieldNameAry[$colIndex];
                if (($fieldName == "recordDate") || ($fieldName == "amountWithdrawn") || ($fieldName == "amountPaidIn") || ($fieldName == "referenceInfo") || ($fieldName == "reconciledDate") || ($fieldName == "recordNotes") || ($fieldName == "parent") || ($fieldName == "compound") || ($fieldName == "reconcileDocId") || ($fieldName == "parentDate")) { //just a string/numeric that needs to be used directly to update table fields, without conversion
                    $fieldVal = $cellValue;
                }
                else { //string value that needs to be converted to index from tables before being used to update the table fields
                    $fieldVal = $tables->getKey($fieldName, $cellValue); //get index of current cell from table of cell values. e.g. budget value of "Capability" = index of 11
                }
                $fieldNamesAry[] = $fieldName; //add to array so it can be imploded to create csv list for inclusion in mariadb SELECT query used in read back section
                $fieldNamesAryQm[] = $fieldName."=?"; //add to array with = ? after each name so it can be imploded to create csv list for inclusion in mariadb UPDATE query
                $fieldValuesAry[] = $fieldVal;
            } //end of COLUMN loop
            $fieldNamesCsv = implode(",", $fieldNamesAry); //convert array of fieldnames into a csv string for insertion into UPDATE query
            $fieldNamesQmCsv = implode(",", $fieldNamesAryQm); //convert array of fieldnames, each with "=?" suffixed, into a csv string for insertion into UPDATE query
            $fieldValuesAry[] = $idR;
            try { //UPDATE and SELECT section
                $stmt = $conn->prepare("UPDATE allRecords SET ".$fieldNamesQmCsv." WHERE idR = ?"); //update all fields in $fieldNamesQmCsv for current row
                $stmt->execute($fieldValuesAry);
                //READ BACK 
                $stmtRead = $conn->prepare("SELECT ".$fieldNamesCsv." FROM allRecords WHERE idR = :idR");
                $stmtRead->execute(array('idR' => $idR));
                $row = $stmtRead->fetch(PDO::FETCH_ASSOC);
                //READ BACK CONVERT SECTION
                foreach ($row as $readFieldName=>$readFieldValue) {
                    if (($readFieldName == "recordDate") || ($readFieldName == "amountWithdrawn") || ($readFieldName == "amountPaidIn") || ($readFieldName == "referenceInfo") || ($readFieldName == "reconciledDate") || ($readFieldName == "recordNotes") || ($readFieldName == "parent") || ($readFieldName == "compound") || ($readFieldName == "reconcileDocId") || ($readFieldName == "parentDate")) { //just a string/numeric that needs to be used directly to feed back to client, without conversion
                        $convertedFieldVal[] = $readFieldValue;
                    }
                    else { //index readFieldValue that needs to be converted to string using tables before being used to feed back to client
                        $convertedFieldVal[] = $tables->getStrValue($readFieldName, $readFieldValue); //convert index of current field e.g. 11, to budget value of "Capability"
                    }
                }
                $outputArry["aryBackFromWriteReadRows"][$idR] = $convertedFieldVal; //add current read back row to array going back to client
            } catch(PDOException $e) {
              $outputArry["REPORT"] = 'ERROR: '.$e->getMessage();
              }
        } //end of ROW loop
        $outputArry["PHPwriteReadRowHasRun"] = TRUE; //flag that indicates this PHP function has run and that the receiving JS function should run to handle the returned data 
    }
    return $outputArry;
}


/* Creates master or slave compound rows by holding down AltGr button and clicking anywhere on row. If a master has already been created and the remains held down any nomber of slave rows can be created. With the initial AltGr button press held the slaves can be toggled by reclicking them. reclicking the master will delete it and any slaves linked to it. A new AltGr press and hold and clicks on existing slaves will delete them and if clicked again will create a new different master. A new AltGr press and hold amd clicks on a master will delete it and all linked slaves. masters and slaves compound rows are designated by the number set in the compound field. If not 0 it is compound. if it equals the idR of the row it is a master. The slave number shows which master (idR) it is linked to. $outputArry["compoundActionAry"] conveys information about what has been created or destroyed back to the client.    */
function setCompoundTrans($inputArry, $outputArry, $allowedToEdit) { 
    global $conn;
    if (array_key_exists("createCompoundTransAjaxSendHasRun", $inputArry) && $allowedToEdit) { //only do update if the calling JS function has run and cellIdForNewParent string exists and allowed to edit
    	$compoundActionAry = [];
        $cellIdAry = explode('-', $inputArry["cellIdForCompoundTrans"]);
        $idR = $cellIdAry[0];
        $compoundNum = $inputArry["compoundNum"];
        $compoundColNum = $inputArry["compoundColNum"]; //used to set compound type in table - for indicating colour of compound marking in display
        try {
            
            $stmt = $conn->prepare('SELECT compound FROM allRecords WHERE idR = ?'); //get compound value for the clicked row
            $stmt->execute(array($idR));
            $compoundRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $compound = $compoundRow["compound"];
            

            if (0 == $compoundNum) { //compoundNum = 0
                if ($compound == 0) { //row not a compound one
                	$compoundActionAry[$idR] = "NewMaster"; //send message back to client JS that new master has been created
                    $stmt = $conn->prepare('UPDATE allRecords SET compound = idR, compoundColNum = ? WHERE idR = ?'); //CREATE MASTER - set clicked row to compound master (compound becomes the idR of this clicked row)
                    $stmt->execute(array($compoundColNum, $idR));
                    $returnCompoundNum = $idR; //set returned compoundNum to idR of clicked line
                }
                else if ($compound == $idR) { //row already a compound master
                	$stmt = $conn->prepare('SELECT idR FROM allRecords WHERE compound = ?'); //get compound value for the clicked row
		            $stmt->execute(array($idR));
		            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			            $compoundActionAry[$row['idR']] = "Destroyed";
			        }
                    $stmt = $conn->prepare('UPDATE allRecords SET compound = 0, compoundColNum = 0 WHERE compound = ?'); //CLEAR ALL - clear all rows where compound == the idR of this clicked master row
                    $stmt->execute(array($idR));
                    $returnCompoundNum = 0; //leave compoundNum at 0 
                }
                else { //row a compound slave
                	$compoundActionAry[$idR] = "Destroyed"; //send message back to client JS that an existing slave has been destroyed
                    $stmt = $conn->prepare('UPDATE allRecords SET compound = 0, compoundColNum = 0 WHERE idR = ?'); //CLEAR SLAVE - clear this slave row
                    $stmt->execute(array($idR));
                    $returnCompoundNum = $compound; //set returned compoundNum to compound value of this clicked slave line
                }
            }
            else { //compoundNum has already been set by a previous click
                if ($compound == 0) { //row not a compound one
                  //  $stmt = $conn->prepare('SELECT recordDate FROM allRecords WHERE idR = ?'); //NOT USED AS THINK BEST TO NOT SYNC SLAVE DATES! get recordDate from row of current Master (idR == $compoundNum) 
                  //  $stmt->execute(array($compoundNum));
                  //  $row = $stmt->fetch(PDO::FETCH_ASSOC);
                  //  $masterDate = $row["recordDate"];

                	$compoundActionAry[$idR] = "NewSlave"; //send message back to client JS that new slave has been created (DATE IS FORCED TO SAME DATE AS MASTER SO THINGS MAKE SENSE!)
                    //NOT USED AS THINK BEST TO NOT SYNC SLAVE DATES!  $stmt = $conn->prepare('UPDATE allRecords SET recordDate = ?, compound = ?, compoundColNum = ? WHERE idR = ?'); //CREATE SLAVE - compound becomes $compoundNum so this slave joins existing master
                    $stmt = $conn->prepare('UPDATE allRecords SET compound = ?, compoundColNum = ? WHERE idR = ?'); //CREATE SLAVE - compound becomes $compoundNum so this slave joins existing master
                    //NOT USED AS THINK BEST TO NOT SYNC SLAVE DATES!  $stmt->execute(array($masterDate, $compoundNum, $compoundColNum, $idR));
                    $stmt->execute(array($compoundNum, $compoundColNum, $idR));
                    $returnCompoundNum = $compoundNum; //set returned compoundNum to compoundNum value that was passed in the inputArry to maintain it on the client
                }
                else if ($compound == $idR) { //row already a compound master
                	$stmt = $conn->prepare('SELECT idR FROM allRecords WHERE compound = ?'); //get compound value for the clicked row
		            $stmt->execute(array($idR));
		            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
			            $compoundActionAry[$row['idR']] = "Destroyed";
			        }
                    $stmt = $conn->prepare('UPDATE allRecords SET compound = 0, compoundColNum = 0 WHERE compound = ?'); //CLEAR ALL - clear all rows where compound == the idR of this clicked master row
                    $stmt->execute(array($idR));
                    $returnCompoundNum = 0; //set returned compoundNum to 0 (clear it)
                }
                else { //row a compound slave
                	$compoundActionAry[$idR] = "Destroyed"; //send message back to client JS that an existing slave has been destroyed
                    $stmt = $conn->prepare('UPDATE allRecords SET compound = 0, compoundColNum = 0 WHERE idR = ?'); //CLEAR SLAVE - clear this slave row
                    $stmt->execute(array($idR));
                    $returnCompoundNum = $compoundNum; //set returned compoundNum to compoundNum value that was passed in the inputArry to maintain it on the client
                }
            }
        } catch(PDOException $e) {
          echo 'ERROR: ' . $e->getMessage();
          }
        $outputArry["compoundActionAry"] = $compoundActionAry;
        $outputArry["returnCompoundNum"] = $returnCompoundNum; 
        $outputArry["PHPsetCompoundTransHasRun"] = TRUE; //flag that indicates this PHP function has run and that the receiving JS function should run to handle the returned data
    }
    return $outputArry;
}


/* Returns the next filename number to be used for a given filenameDate. If a file name for the given date doesn't exist 1 will be returned (assumes this is the first time the filename date is to be used).  NOW USE VERSION IN funcForAccCcc.php THAT CHECKS THE UPLOAD DIRECTORY FOR THE FILES DIRECTLY ! */
function getNextFileSufixNumFromFileNameDate_DEPRECATED($fileNameDate) {
    global $conn;
    $nextFileNum = 1; //default value for use when a filename date that's not been previously used is passed
    $fileNumAry = array();
    $likeMatch = $fileNameDate.'%';
    try {
        $stmt = $conn->prepare("SELECT fileName FROM allRecords WHERE fileName LIKE :likeMatch");
        $stmt->execute(array('likeMatch' => $likeMatch));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $fileNumAry[] = strrev(substr(strrev(substr($row['fileName'],11)),4)); //get the substr after the date part of the filename e.g. 2019-04-15-13.pdf becomes 13.pdf then reverse it and use substr to get the number portion e.g. fdp.31 becomes 31 then reverse it to get the correct way round leaving just the number 13 - add this snumber to the array as the next item
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }

    if (0 < count($fileNumAry)) { //check if any numbers have been added to the array
        $nextFileNum = max($fileNumAry) + 1; //add one to the last used filename number to get the next one to be used
    }
    return $nextFileNum;
}


/* updates the oldest row in the messages table. This effectively creates a last in first out system to contain a number of messages giving a history. The message quantity capacity is determined by the the number of rows that have been created in the table as this function uses update only and can't creat new entries.  */
function saveMessage($messageStr) {
	global $conn;
    global $_saveMessageEnabled;
    if ($_saveMessageEnabled) {
        try {
            $stmt = $conn->prepare('LOCK TABLES messages WRITE');
            $stmt->execute(array());

            $stmt = $conn->prepare('SELECT messageStr FROM messages WHERE status = "Index" ');
            $stmt->execute(array());
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $previousIndex = $row["messageStr"];
            $newIndex = $previousIndex - 1;
            if ($newIndex < 1) {
                $newIndex = $newIndex + 10;
            }

            $stmt = $conn->prepare('UPDATE messages SET messageStr = :messageStr, dateTimeRecCreated = NOW() WHERE id = :newIndex');
            $stmt->execute(array('messageStr' => $messageStr, 'newIndex' => $newIndex));

            $stmt = $conn->prepare('UPDATE messages SET messageStr = :messageStr, dateTimeRecCreated = NOW() WHERE status = "Index"');
            $stmt->execute(array('messageStr' => $newIndex));

            $stmt = $conn->prepare('UNLOCK TABLES');
            $stmt->execute(array());

        } catch(PDOException $e) {
          echo 'ERROR: ' . $e->getMessage();
          }
      }
}

function showMessages() {
	global $conn;
    global $_saveMessageEnabled;
    if ($_saveMessageEnabled) {
        try {
            $stmt = $conn->prepare('LOCK TABLES messages WRITE');
            $stmt->execute(array());

            $stmt = $conn->prepare('SELECT messageStr FROM messages WHERE status = "Index" ');
            $stmt->execute(array());
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $latestIndex = $row["messageStr"];
            

            $stmt = $conn->prepare('SELECT id, messageStr FROM messages WHERE status = "Active" ');
            $stmt->execute(array());
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                $messages[$row["id"]] = $row["messageStr"];
            }

            $stmt = $conn->prepare('UNLOCK TABLES');
            $stmt->execute(array());

            foreach ($messages as $idx => $message) {
            	$inOrderIndex = $idx - $latestIndex;
            	if ($inOrderIndex < 0) {
            		$inOrderIndex = $inOrderIndex + 10;
            	}
            	$messageAry[$inOrderIndex] = $message;
            }
            ksort($messageAry);

        } catch(PDOException $e) {
          echo 'ERROR: ' . $e->getMessage();
          }
        pr($messageAry);
    }
}

/* Gets the sum of amountWithdrawn for all rows where idR is in csvListOfIdRs and $columnToMatch field == $matchValue. Similarly gets the sum of amountPaidIn for the same where criteria and subtracts amountWithdrawn from it to create a balance. amountWithdrawn, amountPaidIn and the calculated balance are returned in an associative array */
function testDates($csvIdRList) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT recordDate FROM docCatalog INNER JOIN allRecords ON docCatalog.id=allRecords.docId WHERE FIND_IN_SET(docId, :csvIdRList) ORDER BY recordDate');
        $stmt->execute(array('csvIdRList' => $csvIdRList));    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $date[] = $row["recordDate"];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $date;
}


/*  */
function testFIND_IN_SET() {
    $csvIdList = "20,5,13,12,7";
    global $conn;
    $docVarietyNameArray = array();
    try {
        $stmt = $conn->prepare("SELECT idR, personOrOrg FROM allRecords WHERE FIND_IN_SET(idR, :csvIdList)");
        $stmt->execute(array('csvIdList' => $csvIdList));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row["personOrOrg"]."</br>");
        }

    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Saves new docTagName by adding it to docTags table in docTagName field. */
function addNewDocTag($docTagName) {
    global $conn;
    try {
        $stmt = $conn->prepare('INSERT INTO  docTags (docTagName) VALUES (?)');
        $stmt->execute(array($docTagName));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Saves new docVarietyName by adding it to docVarieties table in docVarietyName field. */
function addNewDocVariety($docVarietyName) {
    global $conn;
    try {
        $stmt = $conn->prepare('INSERT INTO  docVarieties (docVarietyName) VALUES (?)');
        $stmt->execute(array($docVarietyName));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}


/* Inserts new $value into $fieldName in $tableName. */
function addNewItem($tableName, $fieldName, $value) {
    global $conn;
    try {
        $stmt = $conn->prepare('INSERT INTO  '.$tableName.' ('.$fieldName.') VALUES (?)');
        $stmt->execute(array($value));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Saves new orgOrPersonName by adding it to orgsOrPersons table in orgOrPersonName field. */
function addNewOrgOrPers($orgOrPersonName) {
    global $conn;
    try {
        $stmt = $conn->prepare('INSERT INTO  orgsOrPersons (orgOrPersonName) VALUES (?)');
        $stmt->execute(array($orgOrPersonName));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* New password is copied to the personSession table and the forcePwChange flag cleared. */
function changePwAndClrFlag($userId, $newPassword) {
    global $conn;
    try {
        $pwHash = password_hash($newPassword, PASSWORD_DEFAULT, array('cost' => 12));
        $stmt = $conn->prepare('UPDATE personSession SET passwordHash = :passwordHash, forcePwChange = FALSE WHERE id = :userId');
        $stmt->execute(array('passwordHash' => $pwHash, 'userId' => $userId));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Removes session cookie from client, and for passed userId:
    sets new random customSessionCookie
    loggedIn = FALSE    
    clientFingerprint = ""
    serialMenuArray = ""
    serialDocArray = ""
    docFileName = ""
    unixSecsSessStartTime = 0
    unixSecsAtLastAccess = 0    .
  Closes the record of the current session in the sessionLog table by recording the reason. */
function clearSession($userId, $reason, $callingCode = "") {
    saveMessage("clearSession() called from: ".$callingCode);
    global $conn;
    global $_customSessionCookieLength;
    global $_cookieName;
    try {
        $customSessionCookie = randomAlphaString($_customSessionCookieLength);
        deleteCookieOnClient($_cookieName);
        $stmt = $conn->prepare('UPDATE personSession SET customSessionCookie = :customSessionCookie, loggedIn = FALSE, clientFingerprint = "", serialMenuArray = "", serialDocArray = "", docFileName = "", unixSecsSessStartTime = 0, unixSecsAtLastAccess = 0 WHERE id = :userId');
        $stmt->execute(array('customSessionCookie' => $customSessionCookie, 'userId' => $userId));
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Edits the record identified by $inputArry["cellIdForNewParent"] to set up parent credentials - parentDate = recordDate, parent = idR. The function only runs if $inputArry["cellIdForNewParent"] exists and $allowedToEdit is true. After the record is updated parent is read back from the table and returned in $outputArry["createNewParentId"] for confirmation. parentDate is not read back, it is assumed that it will have completed correctly.   */
function createNewParent($inputArry, $outputArry, $allowedToEdit) {
    global $conn;
    if (array_key_exists("createParentAjaxSendHasRun", $inputArry) && array_key_exists("cellIdForNewParent", $inputArry) && $allowedToEdit) { //only do update if the calling JS function has run and cellIdForNewParent string exists and allowed to edit
        $cellIdAry = explode('-', $inputArry["cellIdForNewParent"]);
        $rowId = $cellIdAry[0];
       
        try {
            
            $stmt = $conn->prepare('SELECT recordDate FROM allRecords WHERE idR = ?'); //get record date of transaction that is about to become a parent
            $stmt->execute(array($rowId));
            $newParentRow = $stmt->fetch(PDO::FETCH_ASSOC);
            $newParentDate = $newParentRow["recordDate"];

            $stmt = $conn->prepare('UPDATE allRecords SET parentDate = ?, parent = ? WHERE idR = ?'); //set the parentDate field to the same as record date and parent field to same as idR. parent being the same as idR identifies this transaction as a parent.
            $stmt->execute(array($newParentDate, $rowId, $rowId));

            $stmt = $conn->prepare('SELECT parent FROM allRecords WHERE idR = ?'); //select parent data from the new parent to send back to the client as evidence the parent has been created
            $stmt->execute(array($rowId));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $updatedParentStr = $row["parent"];

        } catch(PDOException $e) {
          echo 'ERROR: ' . $e->getMessage();
          }
        $outputArry["createNewParentId"] = $updatedParentStr; //create output string
        $outputArry["PHPcreateNewParentHasRun"] = TRUE; //flag that indicates this PHP function has run and that the receiving JS function should run to handle the returned data
    }
    return $outputArry;
}

/* Creates a new user in the database table personSession as long as the username doesn't already exist. The forcePwChange flag is set so that the person is forced to change their password on first login before access to services is granted. */
function createNewUser($newUsername, $newPassword, $customSessionCookie, $superuser = FALSE) {
    global $conn;
    if ($superuser) {
        $superuser = 1;
    }
    else {
        $superuser = 0;
    }
    try {
        $stmt = $conn->prepare('SELECT COUNT(id) AS duplicates FROM personSession WHERE personName = :newUsername'); //check to see if the name already exists
        $stmt->execute(array('newUsername' => $newUsername));
        $row = $stmt->fetch();
        if ($row["duplicates"] == 0) { //if doesn't exist go ahead and print the name and password (for sending to the user) and create user in the table
            $pwHash = password_hash($newPassword, PASSWORD_DEFAULT, array('cost' => 12));
            print_r($newUsername." ");
            print_r($newPassword." ");
            $stmt = $conn->prepare('INSERT INTO personSession (personName, passwordHash, forcePwChange, customSessionCookie, superuser, dateTimeCreated, status) VALUES (?, ?, TRUE, ?, ?, NOW(), ?)');
            $stmt->execute(array($newUsername, $pwHash, $customSessionCookie, $superuser, "Active"));
        }
        else { //if the name does exist print a message
            print_r("Name ".$newUsername." Already Exists");
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

function deleteRecRow($idR) {
    global $conn;
    try {

        //TO PREVENT DELETION OF THE LAST REMAINING ROW FROM LEAVING THE NO BASIC RECORD AND THE DOCUMENT AS AN ORPHAN THAT WONT BE SEEN

        //get docId from allRecords at this index

        //check how many identical docIds are in allRecords

        //if only 1
            //get dateEarliestRecord from docCatalog for row index docId
            //replace record in allRecords at $idR leaving docId setting dateTimeRecCreated = NOW(), recordDate (from dateEarliestRecord) and statusR = 'Live' and clearing everything else

        //else - as below

        $stmt = $conn->prepare("UPDATE allRecords SET statusR = 'Deleted' WHERE idR = ?");
        $stmt->execute(array($idR));
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Copies allRecords fields: (see list in statements below) from row with index $idR and writes them to a newly created row. Returns the id of the new row that has been created. */
function duplicateRecRow($idR) {
    global $conn;
    try {
        $stmt = $conn->prepare("SELECT fileName, docType, compoundColNum, recordDate, parent, parentDate, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, linkedAccOrBudg, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, recordNotes FROM allRecords WHERE idR = ?");
        $stmt->execute(array($idR));
        $rowAry = $stmt->fetch(PDO::FETCH_NUM);
            $stmt = $conn->prepare("INSERT INTO allRecords (fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, parentDate, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, linkedAccOrBudg, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes) VALUES (?, ?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Live', ?)");
            $stmt->execute($rowAry);
            $newRowId = $conn->lastInsertId();
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    return $newRowId;
}

/* Returns an array of data relating to the list of documents where there is an overlap of the date range dateEarliestRecord - dateLatestRecord (inclusive) and the date range of passed $startDate - $endDate (inclusive). Only data matching the search terms $docOrganisationOrPerson, $docVariety, $docTag, $note and $parentDocRef will be returned unless they are "" or 0 (see argument defaults below), in which case they will be ignored (they can be left out of the argument list, from $parentDocRef and working back [assuming $userId is also left out], and they will default to ""/0). In the case of $parentDocRef, matches with either id or parentDocRef will be returned, and if parentDocRef is 0 or not passed only the adult is selected - the children will be hidden. Additionally any passed dates are ignored and an all embracing date range is used (to allow for children that might be slightly outside the set dates) if a parentDocRef > 0 is passed. Only non-private document data will be returned unless a valid $userId is supplied upon which ONLY private document data for the given userId will be returned.  Data is ordered by dateEarliestRecord. This function uses a join to access rows in docCatalog and allRecords tables. */
function filteredDocData($startDate, $endDate, $docOrganisationOrPerson = "", $docVariety = 0, $docTag = 0, $notes = "", $parentDocRef = 0, $userId = 0) {
    global $conn;
    $privateStatus = "(private = FALSE)";
    if (0 < $userId) { //if userId given 
        $privateStatus = "(private = TRUE) AND (uploadPersId = ".$userId.")";
    }
    $andDocVariety = "";
    if ($docVariety) {
        $andDocVariety = "AND (docVariety = ".$docVariety.")";
    }
    $andDocTag = "";
    if ($docTag) {
        $andDocTag = "AND (docTag = ".$docTag.")";
    }
    $andNotes = "";
    if ($notes) {
        $andNotes = 'AND (notes = "'.$notes.'")'; //use of "'. ensures variable $notes appears in "" quotes in $stmt, otherwise without quotes it is treated as a column name and generates a mySql error
    }
    $andparentDocRef = "AND (parentDocRef < 1) "; //this makes the default for families that only the parent is selected unless a $parentDocRef > 0 is passed as an argument
    if ($parentDocRef) {
    	$startDate = "2000-01-01"; //to open the date range so that children docs falling outsid the passed dates will still be selected
    	$endDate = "2030-01-01";
        $andparentDocRef = "AND ((id = ".$parentDocRef.") OR (parentDocRef = ".$parentDocRef."))";
    }
    try {
        $stmt = $conn->prepare('SELECT id, fileNameDate, fileNameNum, fileExt, numOfPages, docVariety, personOrOrg, docTag, parentDocRef, docFullyReferenced, docDataCompleted, uploadPersId, private, dateTimeUploaded, dateEarliestRecord, dateLatestRecord, status, notes FROM docCatalog INNER JOIN allRecords ON docCatalog.id=allRecords.docId WHERE (((:startDate <= dateEarliestRecord) AND (dateEarliestRecord <= :endDate)) OR ((:startDate <= dateLatestRecord) AND (dateLatestRecord <= :endDate)) OR ((dateEarliestRecord <= :startDate) AND (:endDate <= dateLatestRecord))) '.$andDocVariety.' '.$andDocTag.'  '.$andNotes.' '.$andparentDocRef.' AND'.$privateStatus.' ORDER BY dateEarliestRecord');
        $stmt->execute(array('startDate' => $startDate, 'endDate' => $endDate));        
        $docsDetails = array();
        $id = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        	if (!$id) { //first pass - happens once only!

        		$id = $row["id"];
        		$rowIndx = 0;
        		$persOrgAry = array();
        		$rowStore = $row;
        	}
        	if ($id != $row["id"]) {
        		if (!$docOrganisationOrPerson || in_array($docOrganisationOrPerson, $persOrgAry)) { //only create next $docsDetails row if either no persOrg filter has been set or the set filter matches a persOrg in the row data that is about to be combined into $docsDetails for a common id
		    		$docsDetails[$rowIndx] = $rowStore;
	        		$docsDetails[$rowIndx]["docOrganisationOrPerson"] = implode(',', $persOrgAry);
	        		$rowIndx++;
		    	}
        		$id = $row["id"];
        		$persOrgAry = array();
        		$rowStore = $row;
        	}
        	$rowStore = $row;
        	if (($row["personOrOrg"]) && (!in_array($row["personOrOrg"], $persOrgAry)) ) { //if this row contains a persOrg (i.e. it incorporates persOrg data from allRecords and not just a 0 persOrg placeholder) and that persOrg has not already been added to $persOrgAry (prevents multiple persOrg ids from being produced where a doc has several records with the same persOrg)
    			$persOrgAry[] = $row["personOrOrg"]; //add latest persOrg id for this doc id section to array
    		}
        }
        if ($id) { //as long as loop has run at least once (so there will be data) complete operations for previous (and last) id
    		if (!$docOrganisationOrPerson || in_array($docOrganisationOrPerson, $persOrgAry)) { //only create next $docsDetails row if either no persOrg filter has been set or the set filter matches a persOrg in the row data that is about to be combined into $docsDetails for a common id
	    		$docsDetails[$rowIndx] = $rowStore;
        		$docsDetails[$rowIndx]["docOrganisationOrPerson"] = implode(',', $persOrgAry);
	    	}
        }        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      //pr($docsDetails);
      return $docsDetails;
}

/* Returns an array of account names indexed by id sorted into alphabetical order (so ids will not necessarily be sequential) */
function getAccountList() {
    global $conn;
    $accountsArray = array();
    try {
        $stmt = $conn->prepare("SELECT id, accountName FROM accounts WHERE status = 'active' ORDER BY accountName");
        $stmt->execute(array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $accountsArray[$row['id']] = $row['accountName'];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $accountsArray;
}

/* Returns an array of budget names indexed by id sorted into alphabetical order (so ids will not necessarily be sequential) */
function getBudgetList() {
    global $conn;
    $budgetsArray = array();
    try {
        $stmt = $conn->prepare("SELECT id, budgetName FROM budgets WHERE status = 'active' ORDER BY budgetName");
        $stmt->execute(array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $budgetsArray[$row['id']] = $row['budgetName'];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $budgetsArray;
}

/* Get cookie from personSession table for given id. */
function getCookieFromTable($id) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT customSessionCookie FROM personSession WHERE id = :id'); //get cookie for id = 1
        $stmt->execute(array('id' => $id));
        $row = $stmt->fetch();
        $cookie = $row["customSessionCookie"];
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    return $cookie;
}

/* Gets all fields (whole column) named $fieldName from the table named in $tableName. Results are returned in alphabetical order in an associative array where the keys are the the table ids. */
function getDbColumn($tableName, $fieldName) {
    global $conn;
    $columnAry = array(); //initialise array.
    try {
        $stmt = $conn->prepare('SELECT id, '.$fieldName.' FROM '.$tableName.' ORDER BY '.$fieldName);    
        $stmt->execute(array());
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { //fetch only associative version of array (not the indexed version that would normally be interleaved with the associative if PDO::FETCH_ASSOC isn't used.
                $columnAry[$row["id"]] = $row[$fieldName];
        }
    } catch(PDOException $e) {
         echo 'ERROR: ' . $e->getMessage();
      }
    return $columnAry;
}

/* Gets data from all the fields named by the strings in array $fieldNames from the table named in $tableName from all rows where the field named in $fieldToMatch == $rowMatchValue unless $fieldToMatch and $rowMatchValue are not passed as arguments or = '', in which case all rows will be returned. Results are returned in associative arrays, where the keys are the same as the passed fieldnames array, encapsulated in an indexed array ordered by the field given in $sortByString. */
function getDbRowsWhereMatching($tableName, $fieldNames, $fieldToMatch='', $rowMatchValue='', $sortByString='') {
    global $conn;
    $whereInsert = '';
    if ($fieldToMatch) { //if a field to match is passed a WHERE clause is created, otherwise a WHERE clause will not be inserted and $rowMatchValue will be ignored
        $whereInsert = ' WHERE '.$fieldToMatch.' = "'.$rowMatchValue.'"';
    }
    if ($sortByString) {
        $sortByString = ' ORDER BY '.$sortByString;
    }
    $tableValues = array(); //initialise array.
    $fieldsCsv = implode(",", $fieldNames); //convert array of field names into comma separated list.
    try {
        //$stmt = $conn->prepare('SELECT '.$fieldsCsv.' FROM '.$tableName.' WHERE '.$fieldToMatch.' = :value');    
        //$stmt->execute(array('value' => $rowMatchValue));

        $stmt = $conn->prepare('SELECT '.$fieldsCsv.' FROM '.$tableName.$whereInsert.$sortByString);    
        $stmt->execute(array());
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { //fetch only associative version of array (not the indexed version that would normally be interleaved with the associative if PDO::FETCH_ASSOC isn't used.
                $tableValues[] = $row;
        }
    } catch(PDOException $e) {
         echo 'ERROR: ' . $e->getMessage();
      }
    return $tableValues;
}

/* Gets the sum of amountWithdrawn for all rows where doc filename is same as that for row indexed by $idR. Similarly gets the sum of amountPaidIn for the same where criteria and subtracts amountWithdrawn from it to create a balance. amountWithdrawn, amountPaidIn and the calculated balance are returned in an associative array. */
function getDocBalData($idR) {
    global $conn;
    $result = array("withdrawn" => 0.00, "paidIn" => 0.00, "balance" => 0.00); //initialise array to sensible default values in case no data is found in both withdrawn and paidIn columns
    try {
        $stmt = $conn->prepare('SELECT fileName FROM allRecords WHERE idR = :idR'); //get doc filename for row matching $idR
        $stmt->execute(array('idR' => $idR));
        $row = $stmt->fetch();
        $fileName = $row["fileName"];
        $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE fileName =:fileName  AND statusR = "Live"');
        $stmt->execute(array('fileName' => $fileName));    
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

/* Returns an array of data arrays relating to the list of documents where there is an overlap of the date range dateEarliestRecord - dateLatestRecord (inclusive) and the date range of passed $startDate - $endDate (inclusive). Only non-private document data will be returned unless a valid $userId is supplied upon which ONLY private document data for the given userId will be returned. Data is ordered by dateEarliestRecord. */
function getdocData($startDate, $endDate, $userId = 0) {
    global $conn;
    $docsDetails = array();
    $privateStatus = "(private = FALSE)";
    if (0 < $userId) { //if userId given 
        $privateStatus = "(private = TRUE) AND (uploadPersId = ".$userId.")";
    }
    try {
        $stmt = $conn->prepare('SELECT id, fileNameDate, fileNameNum, fileExt, numOfPages, docVariety, docOrganisationOrPerson, docTag, parentDocRef, docFullyReferenced, docDataCompleted, uploadPersId, private, dateTimeUploaded, dateEarliestRecord, dateLatestRecord, status, notes FROM docCatalog WHERE ((:startDate <= dateEarliestRecord) AND (dateEarliestRecord <= :endDate)) OR ((:startDate <= dateLatestRecord) AND (dateLatestRecord <= :endDate)) OR ((dateEarliestRecord <= :startDate) AND (:endDate <= dateLatestRecord)) AND'.$privateStatus.' ORDER BY dateEarliestRecord');
        $stmt->execute(array('startDate' => $startDate, 'endDate' => $endDate));
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $docsDetails[] = $row;
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $docsDetails;
}

/* Gets the sum of amountWithdrawn for all rows where parentDocRef field == $familyId and $columnToMatch field == $matchValue. Similarly gets the sum of amountPaidIn for the same where criteria and subtracts amountWithdrawn from it to return the balance. If $familyId is left out of the argument list or set to 0 then the balance is simply calculated from all rows where $columnToMatch field == $matchValue. */
function getDocChildrenBalDataDEPRECATED($columnToMatch, $matchValue, $familyId = 0) {
    global $conn;
    $balance = 0.00;
    $familyEqu = "";
    if (0 < $familyId) {
        $familyEqu = " (parentDocRef = ".$familyId.") AND ";
    }
    try {
        $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM docCatalog INNER JOIN allRecords ON docCatalog.id=allRecords.docId WHERE '.$familyEqu.' ('.$columnToMatch." = ".$matchValue.')');
        $stmt->execute(array());    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $balance = $row["paidIn"] - $row["withdrawn"];
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $balance;
}

/* Returns family id (parent field) from allRecords table where the row idR matches passed $idR argument */
function getFamilyId($idR) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT parent FROM allRecords WHERE idR = :idR'); //get family id for given idR
        $stmt->execute(array('idR' => $idR));
        $row = $stmt->fetch();
        $familyId = $row["parent"];
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    return $familyId;

}

/* Gets the sum of amountWithdrawn for all rows where idR is in csvListOfIdRs and $columnToMatch field == $matchValue. Similarly gets the sum of amountPaidIn for the same where criteria and subtracts amountWithdrawn from it to create a balance. amountWithdrawn, amountPaidIn and the calculated balance are returned in an associative array */
function getFilteredBalData($columnToMatch, $matchValue, $csvIdRList) {
    global $conn;
    $result = array("withdrawn" => 0.00, "paidIn" => 0.00, "balance" => 0.00); //initialise array to sensible default values in case no data is found in both withdrawn and paidIn columns
    try {
        $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM docCatalog INNER JOIN allRecords ON docCatalog.id=allRecords.docId WHERE FIND_IN_SET(docId, :csvIdRList) AND ('.$columnToMatch." = ".$matchValue.')');
        $stmt->execute(array('csvIdRList' => $csvIdRList));    
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


/* Gets the sum of amountWithdrawn for all rows where record date is between the given dates and $fieldName field == $matchValue and $filterStr terms are met. Similarly gets the sum of amountPaidIn for the same where criteria and subtracts amountWithdrawn from it to create a balance. amountWithdrawn, amountPaidIn and the calculated balance are returned in an associative array. If $useReconciledInsteadOfRecordDate is used and set to TRUE the reconciled dates will be used instead, thus providing balance information that should match bank statement balances. Also returns similar information for only the entries that use the same document as that in $recRowId - this gives a quick and easy way of checking the totals for the document on display */
function getFilterStrAllBalData($inputArry, $outputArry, $filterStr, $familyChoice, $restrictFilterStr, $onlyRowsWhereThisFieldNotZero) {
    global $conn;
    global $_fieldNameAry;
    if (array_key_exists("getBalDataSendHasRun", $inputArry) && array_key_exists("cellIdBal", $inputArry)) { //only do update if the calling JS function has run and cellIdBal exists
        $definedColNotZero = "";
        if ($onlyRowsWhereThisFieldNotZero != "") {
            $definedColNotZero = ' AND ('.$onlyRowsWhereThisFieldNotZero.' != 0) ';
        }
        $cellId = $inputArry["cellIdBal"];
        $recStartDate = $inputArry["recStartDate"];
        $recEndDate = $inputArry["recEndDate"];
        $filterTermAry = explode("-", $cellId);
        $recRowId = $filterTermAry[0];
        $fieldName = $_fieldNameAry[$filterTermAry[1]]; //create fieldName from column id (0 - 11).
        $validFieldForBalances = FALSE;
        switch ($fieldName) { //choose the appropriate item list for the field that is being updated.
          case "personOrOrg":
            $matchValue = getRecFieldValueAtRow($recRowId, $fieldName); //get value of field from allRecords table
            $validFieldForBalances = TRUE;
            break;
          case "transCatgry":
            $matchValue = getRecFieldValueAtRow($recRowId, $fieldName); //get value of field from allRecords table
            $validFieldForBalances = TRUE;
            break;
          case "accWorkedOn":
            $matchValue = getRecFieldValueAtRow($recRowId, $fieldName); //get value of field from allRecords table
            $validFieldForBalances = TRUE;
            break;
          case "budget":
            $matchValue = getRecFieldValueAtRow($recRowId, $fieldName); //get value of field from allRecords table
            $validFieldForBalances = TRUE;
            break;
          case "referenceInfo":
            $matchValue = '\''.getRecFieldValueAtRow($recRowId, $fieldName).'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
            $validFieldForBalances = TRUE;
            break;
          case "umbrella":
            $matchValue = getRecFieldValueAtRow($recRowId, $fieldName); //get value of field from allRecords table
            $validFieldForBalances = TRUE;
            break;
          case "docType":
            $matchValue = getRecFieldValueAtRow($recRowId, $fieldName); //get value of field from allRecords table
            $validFieldForBalances = TRUE;
            break;
          case "recordNotes":
            $matchValue = '\''.getRecFieldValueAtRow($recRowId, $fieldName).'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
            $validFieldForBalances = TRUE;
            break;
          default:
            break;
        }
        $defaultValue = "0.00";
        $outputArry["withdrawnNorm"] = $defaultValue; //initialise array to sensible default values in case no data is found in both withdrawn and paidIn columns
        $outputArry["paidInNorm"] = $defaultValue;
        $outputArry["balanceNorm"] = $defaultValue;
        $outputArry["withdrawnRec"] = $defaultValue;
        $outputArry["paidInRec"] = $defaultValue;
        $outputArry["balanceRec"] = $defaultValue;
        $outputArry["withdrawnDoc"] = $defaultValue;
        $outputArry["paidInDoc"] = $defaultValue;
        $outputArry["balanceDoc"] = $defaultValue;
        try {
            if ($validFieldForBalances) {
                if ($familyChoice == "NoKids") {
                	
                    $stmtNorm = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords 
                    WHERE 
                    '.noKidsFilter().' 
                    AND ('.$fieldName.' = '.$matchValue.') '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"'); //use the normal record dates for calculating the amounts

                    $stmtRec =  $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE ((:recStartDate <= recordDate) AND (:recStartDate <= reconciledDate) AND (reconciledDate <= :recEndDate) AND (recordDate <= :recEndDate)) AND ((parent = 0) OR (parent = idR)) AND ('.$fieldName.' = '.$matchValue.') '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"'); //use the reconciled dates for calculating the amounts

                }
                elseif ($familyChoice == "All") {

                	$stmtNorm = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords 
                        WHERE 
                            '.allFilter().'
                        AND  
                            ('.$fieldName.' = '.$matchValue.') '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"');

                    $stmtRec =  $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords 
                        WHERE
                        (
                            (:recStartDate <= reconciledDate) AND (reconciledDate <= :recEndDate)                   #reconciled date must be within the month for all cases
                        AND                                                                                                                 #AND
                            '.allFilter().' 
                        ) 
                        AND  
                            ('.$fieldName.' = '.$matchValue.') '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"');

                }
                else { //$familyChoice contains family number rather than "NoKids" or "All"

                	$stmtNorm = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE parent = '.$familyChoice.' AND  ('.$fieldName.' = '.$matchValue.') '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"');

                    $stmtRec =  $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE parent = '.$familyChoice.' AND  ('.$fieldName.' = '.$matchValue.') '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"');
                    
                }
                $stmtNorm->execute(array('recStartDate' => $recStartDate, 'recEndDate' => $recEndDate));  
                $rowNorm = $stmtNorm->fetch(PDO::FETCH_ASSOC);
                if (!is_null($rowNorm["withdrawn"])) { //make sure data has been found
                    $withdrawnNorm = $rowNorm["withdrawn"];
                    $paidInNorm = $rowNorm["paidIn"];
                    $outputArry["withdrawnNorm"] = $withdrawnNorm;
                    $outputArry["paidInNorm"] = $paidInNorm;
                    $outputArry["balanceNorm"] = (string)($paidInNorm - $withdrawnNorm); //cast to string because calculation changes result to a number (of some sort!) and string is needed to be returned to JS
                }
                $stmtRec->execute(array('recStartDate' => $recStartDate, 'recEndDate' => $recEndDate));    
                $rowRec = $stmtRec->fetch(PDO::FETCH_ASSOC);
                if (!is_null($rowRec["withdrawn"])) { //make sure data has been found
                    $withdrawnRec = $rowRec["withdrawn"];
                    $paidInRec = $rowRec["paidIn"];
                    $outputArry["withdrawnRec"] = $withdrawnRec;
                    $outputArry["paidInRec"] = $paidInRec;
                    $outputArry["balanceRec"] = (string)($paidInRec - $withdrawnRec); //(string)($paidInRec - $withdrawnRec);
                }

                $stmtFileName = $conn->prepare('SELECT fileName FROM allRecords WHERE idR = :idR'); //get doc filename for row matching $idR
                $stmtFileName->execute(array('idR' => $recRowId));
                $rowFileName = $stmtFileName->fetch();
                $fileName = $rowFileName["fileName"];
                $stmtDoc = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM allRecords WHERE fileName =:fileName  AND statusR = "Live"');
                $stmtDoc->execute(array('fileName' => $fileName));    
                $rowDoc = $stmtDoc->fetch(PDO::FETCH_ASSOC);
                if (!is_null($rowDoc["withdrawn"])) { //make sure data has been found
                    $withdrawnDoc = $rowDoc["withdrawn"];
                    $paidInDoc = $rowDoc["paidIn"];
                    $outputArry["withdrawnDoc"] = $withdrawnDoc;
                    $outputArry["paidInDoc"] = $paidInDoc;
                    $outputArry["balanceDoc"] = (string)($paidInDoc - $withdrawnDoc);
                }
            }

        } catch(PDOException $e) {
          $outputArry["ERROR in funcsToRdWrTblesForAccCcc.php / getFilterStrAllBalData()"] = "#".$familyChoice."#"." ". $e->getMessage();
          }
        $outputArry["PHPgetFilterStrAllBalDataHasRun"] = TRUE; //flag that indicates this PHP function has run and that the receiving JS function should run to handle the returned data
        $outputArry["onlyRowsWhereThisFieldNotZero"] = $onlyRowsWhereThisFieldNotZero;
    }
    return $outputArry;
}


/* Gets the sum of amountWithdrawn for all rows where recordDate is between or equal to $recStartDate and $recEndDate, and $columnToMatch field == $matchValue. Similarly gets the sum of amountPaidIn for the same where criteria and subtracts amountWithdrawn from it to create a balance. amountWithdrawn, amountPaidIn and the calculated balance are returned in an associative array */
function getDateRangeBalData($columnToMatch, $matchValue, $recStartDate, $recEndDate) {
    global $conn;
    $result = array("withdrawn" => 0.00, "paidIn" => 0.00, "balance" => 0.00); //initialise array to sensible default values in case no data is found in both withdrawn and paidIn columns
    try {
        $stmt = $conn->prepare('SELECT SUM(amountWithdrawn) AS withdrawn, SUM(amountPaidIn) AS paidIn FROM docCatalog INNER JOIN allRecords ON docCatalog.id=allRecords.docId WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) AND ('.$columnToMatch." = ".$matchValue.')');
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

/* Creates an array of references indexed by idR sorted into alphabetical order (so idRs will not necessarily be sequential). And dates, in the same manner. The two arrays are returned in an array with the keys 'refs' and 'dates'. */
function getRecRefsAndDates() {
    global $conn;
    $refArray = array();
    try {
        $stmt = $conn->prepare("SELECT idR, recordDate, referenceInfo FROM allRecords WHERE (statusR = 'Live') AND (referenceInfo != '') ORDER BY referenceInfo");
        $stmt->execute(array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $refArray[$row['idR']] = $row['referenceInfo'];
            $dateArray[$row['idR']] = $row['recordDate'];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      $returnAry["refs"] = $refArray;
      $returnAry["dates"] = $dateArray;
      return $returnAry;
}


/* PROBABLY NOT MUCH DIFF FROM getMultDocDataAry() YET, BUT MAYBE IF IT IS EDITED A BIT! Need to write a description for this function - the while loop might need to be changed because perhaps record rows where a doc isn't allocated to a persOrg should be returned too. CHANGED TEMPORARILY! */
//$familySetting can be one of 3 values: "NoKids", "All", 527. If the number is sent then only records with the parent value set to that number will be returned (which includes the parent and children)
function getPivotTableAry($recStartDate, $recEndDate, $filterStr, $order, $familyChoice, $restrictFilterStr, $groupBy) {
    global $conn;
    try {
        $groupByStr = "";
        if ($groupBy) { //if $groupBy contains a group info (i.e. 'transCatgry') create a GROUP BY term
            $groupByStr = " GROUP BY ".$groupBy;
        }
        if ($familyChoice == "NoKids") { //only show family parents among other general records
            $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, MIN(recordDate) AS minRecordDate, MAX(recordDate) AS maxRecordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords 
                WHERE 
                    '.noKidsFilter().' 
                    '.$filterStr.$restrictFilterStr.' 
                AND 
                    statusR = "Live"'.$groupByStr.' 
                ORDER BY budget DESC');
        }
        elseif ($familyChoice == "All") { //show all records including general, parents and children
            $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, MIN(recordDate) AS minRecordDate, MAX(recordDate) AS maxRecordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords 
                WHERE 
                    '.allFilter().'
                    '.$filterStr.$restrictFilterStr.' 
                AND 
                statusR = "Live"'.$groupByStr.' 
                ORDER BY sumAmountWithdrawn DESC');
        }
        else { //show only parents and children of the family id passed as a numeric argument in $familyChoice
            $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, MIN(recordDate) AS minRecordDate, MAX(recordDate) AS maxRecordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE parent = '.$familyChoice.' '.$filterStr.$restrictFilterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY sumAmountWithdrawn DESC'); //order by doc filename first so split docs don't occur - may mean docs appear in wierd order
        }
        if ($familyChoice == "everything") { //only show everything in date order without regard for whether it's a child or parent or ordinary item
            $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, MIN(recordDate) AS minRecordDate, MAX(recordDate) AS maxRecordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) '.$filterStr.$restrictFilterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY sumAmountWithdrawn DESC');
        }
        $stmt->execute(array('recStartDate' => $recStartDate, 'recEndDate' => $recEndDate));    
        $docsDetails = array();
        //return multiple rows, one per persOrg - contains columns from allRecords that most probably will be used
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { //copy the summed withdrawn and paidin data to the ordinary withdrawn and paidin array positions
            $row["amountWithdrawn"] = $row["sumAmountWithdrawn"];
            $row["amountPaidIn"] = $row["sumAmountPaidIn"];
            $docsDetails[] = $row; 
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      //pr($docsDetails);
      return $docsDetails;
}



function getDuplicatesDataAry($recStartDate, $recEndDate, $filterStr, $order, $restrictFilterStr) {
    global $conn;
    $docsDetailsAry = [];
    $recordsAry = [];

    try {

        $stmtDupl = $conn->prepare('SELECT recordDate, personOrOrg, amountWithdrawn, amountPaidin FROM allRecords WHERE ((amountWithdrawn != 0) || (amountPaidin != 0)) GROUP BY recordDate, personOrOrg, amountWithdrawn,amountPaidin HAVING COUNT(*) > 1'); //selects one version of each duplicate transaction from the table - no dates or filters are used so data from the whole table is returned
        $stmtDupl->execute(array());
        while ($rowDupl = $stmtDupl->fetch(PDO::FETCH_ASSOC)) { //select all transaction records in the recStartDate recEndDate range that match the duplicate used in this iteration into recordsAry
            //pr($rowDupl);
        

            $stmt = $conn->prepare('SELECT * FROM allRecords 
                WHERE 
                    '.allFilter().' 
                AND 
                    (recordDate = :recDateDupl) 
                AND 
                    (personOrOrg = :persOrgDupl) 
                AND 
                    (amountWithdrawn = :withdrawnDupl) 
                AND 
                    (amountPaidin = :paidinDupl)

                '.$filterStr.$restrictFilterStr.' AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');


            $stmt->execute(array(
                'recStartDate'=>$recStartDate,
                'recEndDate'=>$recEndDate,
                'recDateDupl'=>$rowDupl['recordDate'],
                'persOrgDupl'=>$rowDupl['personOrOrg'],
                'withdrawnDupl'=>$rowDupl['amountWithdrawn'],
                'paidinDupl'=>$rowDupl['amountPaidin']
            )); 

            while ($rowFromTable = $stmt->fetch(PDO::FETCH_ASSOC)) { //load all duplicate transaction records into recordsAry
                $rowFromTable["compoundHidden"] = FALSE;
                $recordsAry[] = $rowFromTable;
            }
        }




        //section that extracts as csv all idRs of compound rows from the main $stmt query, whether master or slave. Also extracts as csv the compound number associated with each of these compound groups
        $compoundIdrAry = [];
        $compoundIdrAryIdx = 0;
        $compoundNumAry = [];
        $compoundNumAryIdx = 0;
        foreach ($recordsAry as $singleRow) {
            if (0 < $singleRow["compound"]) {
                $compoundIdrAry[$compoundIdrAryIdx] = $singleRow["idR"];
                $compoundIdrAryIdx++;
            }
            if ((0 < $singleRow["compound"]) & !in_array($singleRow["compound"], $compoundNumAry)) {
                $compoundNumAry[$compoundNumAryIdx] = $singleRow["compound"];
                $compoundNumAryIdx++;
            }
        }
        $compoundIdrCsv = implode(",", $compoundIdrAry);
        $compoundNumCsv = implode(",", $compoundNumAry);

        if (($compoundNumCsv != "") && ($compoundIdrCsv != "")) { //if compound records exist from main queries (above) then run the subsidiary query to find compound rows that should be added to main rows but hidden until revealed by click on a row that forms part of a compound group
            $stmtCompoundHidden = $conn->prepare('SELECT * FROM allRecords WHERE compound IN ('.$compoundNumCsv.') AND idR NOT IN ('.$compoundIdrCsv.') AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');
            $stmtCompoundHidden->execute(array());
            while ($rowFromTableHidden = $stmtCompoundHidden->fetch(PDO::FETCH_ASSOC)) { //load all transaction records into recordsAry
                $rowFromTableHidden["compoundHidden"] = TRUE;
                $recordsAry[] = $rowFromTableHidden;
            }
        }



        foreach ($recordsAry as $row) {
            if ($groupBy) { //if group by information is passed to this function copy the summed withdrawn and paidin data to the ordinary withdrawn and paidin array positions
                $row["amountWithdrawn"] = $row["sumAmountWithdrawn"];
                $row["amountPaidIn"] = $row["sumAmountPaidIn"];
            }
            $docsDetailsAry[] = $row;
        }

    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      //pr($docsDetailsAry);
      return $docsDetailsAry;
}


/* Need to write a description for this function - the while loop might need to be changed because perhaps record rows where a doc isn't allocated to a persOrg should be returned too. CHANGED TEMPORARILY! */
//$familySetting can be one of 3 values: "NoKids", "All", 527. If the number is sent then only records with the parent value set to that number will be returned (which includes the parent and children)
function getMultDocDataAry($recStartDate, $recEndDate, $filterStr, $order, $familyChoice, $groupBy, $restrictFilterStr, $onlyRowsWhereThisFieldNotZero = "") {
    global $conn;
    $familyOnly = FALSE; //flag that will be set later if $familyChoice is a number indicating just that the family identified will be returned
    $definedColNotZero = "";
    if ($onlyRowsWhereThisFieldNotZero != "") {
        $definedColNotZero = ' AND ('.$onlyRowsWhereThisFieldNotZero.' != 0) ';
    }
    try {
        $groupByStr = "";
        if ($groupBy) { //if $groupBy contains a group info (i.e. 'transCatgry') create a GROUP BY term
            $groupByStr = " GROUP BY ".$groupBy;
        }

        if ($familyChoice == "NoKids") { //only show family parents among other general records
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords 
                    WHERE 
                        '.noKidsFilter().' 
                        '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"'.$groupByStr.' ORDER BY sumAmountWithdrawn DESC');
            }
            else {
                $stmt = $conn->prepare('SELECT * FROM allRecords 
                    WHERE 
                        ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) 
                    AND 
                        ((parent = 0) OR (parent = idR)) 
                    '.$filterStr.$restrictFilterStr.$definedColNotZero.' 
                    AND 
                    statusR = "Live" 
                    ORDER BY '.$order.' recordDate, fileName');
            }
        }
        elseif ($familyChoice == "All") { //show all records including general, parents and children
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords 
                    WHERE 
                        '.allFilter().' 
                        '.$filterStr.$restrictFilterStr.$definedColNotZero.' 
                    AND 
                        statusR = "Live"'.$groupByStr.' 
                        ORDER BY sumAmountWithdrawn DESC');
            }
            else {
                $stmt = $conn->prepare('SELECT * FROM allRecords 
                    WHERE 
                        '.allFilter().'
                        '.$filterStr.$restrictFilterStr.$definedColNotZero.' 
                    AND 
                    statusR = "Live" 
                    ORDER BY '.$order.' recordDate, fileName');

              /*  $stmtCompoundHidden = $conn->prepare('SELECT * FROM allRecords WHERE (((:recStartDate <= recordDate) AND (recordDate <= :recEndDate) AND (parent = 0)) OR ((:recStartDate <= parentDate) AND (parentDate <= :recEndDate))) AND (0 < compound) AND statusR = "Live" AND (idR NOT IN 

                    (SELECT idR FROM allRecords WHERE (((:recStartDate <= recordDate) AND (recordDate <= :recEndDate) AND (parent = 0)) OR ((:recStartDate <= parentDate) AND (parentDate <= :recEndDate))) '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND (0 < compound) AND statusR = "Live")

                    )
                    ORDER BY '.$order.' recordDate, fileName'); */
            }
        }
        else { //show only parents and children of the family id passed as a numeric argument in $familyChoice
            $familyOnly = TRUE; //flag set to indicate that $familyChoice is a number that just the family identified will be returned
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE parent = '.$familyChoice.' '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"'.$groupByStr.' ORDER BY sumAmountWithdrawn DESC'); //order by doc filename first so split docs don't occur - may mean docs appear in wierd order
            }
            else {
                $stmt = $conn->prepare('SELECT * FROM allRecords 
                WHERE parent = '.$familyChoice.'
                 '.$filterStr.$restrictFilterStr.$definedColNotZero.'
                 AND 
                 statusR = "Live" 
                 ORDER BY '.$order.' fileName, recordDate'); //order by doc filename first so split docs don't occur - may mean docs appear in weird order

             /*   $stmtCompoundHidden = $conn->prepare('SELECT * FROM allRecords WHERE parent = '.$familyChoice.' AND (0 < compound) AND statusR = "Live" AND (idR NOT IN 

                    (SELECT idR FROM allRecords WHERE parent = '.$familyChoice.' '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND (0 < compound) AND statusR = "Live")

                    )
                    ORDER BY '.$order.' recordDate, fileName'); */
            }
        }

        /* WAS USED WITH SHOW EVERYTHING BUTTON TO VIEW RECORDS THAT HAD BECOME CORRUPTED - MAY NEED IT AT SOME POINT !
        if ($familyChoice == "everything") { //only show everything in date order without regard for whether it's a child or parent or ordinary item
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live"'.$groupByStr.' ORDER BY sumAmountWithdrawn DESC');
            }
            else {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate))   '.$filterStr.$restrictFilterStr.$definedColNotZero.' AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');
            }
        }
        */

           
        $docsDetailsAry = [];
        $recordsAry = [];

        $stmt->execute(array('recStartDate' => $recStartDate, 'recEndDate' => $recEndDate)); 
        while ($rowFromTable = $stmt->fetch(PDO::FETCH_ASSOC)) { //load all transaction records into recordsAry
            $rowFromTable["compoundHidden"] = FALSE;
            $recordsAry[] = $rowFromTable;
        }

        //section that extracts as csv all idRs of compound rows from the main $stmt query, whether master or slave. Also extracts as csv the compound number associated with each of these compound groups
        $compoundIdrAry = [];
        $compoundIdrAryIdx = 0;
        $compoundNumAry = [];
        $compoundNumAryIdx = 0;
        foreach ($recordsAry as $singleRow) {
            if (0 < $singleRow["compound"]) {
                $compoundIdrAry[$compoundIdrAryIdx] = $singleRow["idR"];
                $compoundIdrAryIdx++;
            }
            if ((0 < $singleRow["compound"]) & !in_array($singleRow["compound"], $compoundNumAry)) {
                $compoundNumAry[$compoundNumAryIdx] = $singleRow["compound"];
                $compoundNumAryIdx++;
            }
        }
        $compoundIdrCsv = implode(",", $compoundIdrAry);
        $compoundNumCsv = implode(",", $compoundNumAry);

        if (($compoundNumCsv != "") && ($compoundIdrCsv != "")) { //if compound records exist from main queries (above) then run the subsidiary query to find compound rows that should be added to main rows but hidden until revealed by click on a row that forms part of a compound group
            $stmtCompoundHidden = $conn->prepare('SELECT * FROM allRecords WHERE compound IN ('.$compoundNumCsv.') AND idR NOT IN ('.$compoundIdrCsv.') AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');
            $stmtCompoundHidden->execute(array());
            while ($rowFromTableHidden = $stmtCompoundHidden->fetch(PDO::FETCH_ASSOC)) { //load all transaction records into recordsAry
                $rowFromTableHidden["compoundHidden"] = TRUE;
                $recordsAry[] = $rowFromTableHidden;
            }
        }
//pr($recordsAry);

        if ($familyOnly && !$groupBy) { //only the family identified will be returned and not as a group, so process to put parent first at the top of the list followed by other linked parent doc transactions
            //the following sort routines assume the data from the allRecords table is already sorted in ascending doc filename then trasaction date !!!
            $parentDocName = "";
            $parentTransactionsAry = [];
            foreach ($recordsAry as $rowToFindParentDoc) { //find the filename of the document attached to the parent tranaction
                if ($rowToFindParentDoc["idR"] == $rowToFindParentDoc["parent"]) {
                    $parentDocName = $rowToFindParentDoc["fileName"];
                    $parentTransactionsAry[] = $rowToFindParentDoc; //put parent first at the top of the list
                }
            }
            $tempOtherParentDocsAry = [];
            foreach ($recordsAry as $rowWithOtherParentDocs) { //assemble transactions that share the same parent doc into a temp array ready for sorting (but omit the parent doc idR)
                if (($rowWithOtherParentDocs["fileName"] == $parentDocName) && ($rowWithOtherParentDocs["idR"] != $rowWithOtherParentDocs["parent"])) {
                    $tempOtherParentDocsAry[] = $rowWithOtherParentDocs;
                }
            }
            $parentTransactionsAry = array_merge($parentTransactionsAry, sortTwoDimAry($tempOtherParentDocsAry, "idR")); //sort order of other parent doc transactions by idR and append to parent transaction
            $otherTransactionsAry = [];
            foreach ($recordsAry as $otherTransactionRow) {
                if ($otherTransactionRow["fileName"] != $parentDocName) { //only those transactions that are not part of the parent doc
                    $otherTransactionsAry[] = $otherTransactionRow;
                }
            }
            $curDocFilename = $otherTransactionsAry[0]["fileName"];
            $tempTransactionSubArray = [];
            $transactionByDocAry = [];
            foreach ($otherTransactionsAry as $otherTransactionToSortRow) { //assemble transactions that share the same doc into sub arrays of a 'transaction by doc' holding array, ready for sorting
                if ($otherTransactionToSortRow["fileName"] == $curDocFilename) { //transactions share the same doc as previous (or initialised) one
                    $tempTransactionSubArray[] = $otherTransactionToSortRow;
                }
                else { //new doc is associated with this transaction
                    $transactionByDocAry[] = $tempTransactionSubArray;
                    $tempTransactionSubArray = [];
                    $curDocFilename = $otherTransactionToSortRow["fileName"];
                    $tempTransactionSubArray[] = $otherTransactionToSortRow;
                }
            }
            $transactionByDocAry[] = $tempTransactionSubArray;
            $sortedTransactionByDocAry = sortThreeDimAry($transactionByDocAry, 0, "recordDate"); //sort $transactionByDocAry ascending according to the date value in the 1st sub sub array of each sub array 

            $completeOrderedOtherTransactionsAry = [];
            foreach ($sortedTransactionByDocAry as $sameDocTransactionsAry) {
                $completeOrderedOtherTransactionsAry = array_merge($completeOrderedOtherTransactionsAry, $sameDocTransactionsAry);
            }
            $docsDetailsAry = array_merge($parentTransactionsAry, $completeOrderedOtherTransactionsAry);
        }
        else { //not a family but either "NoKids" or "All" - so process normally
            foreach ($recordsAry as $row) {
                if ($groupBy) { //if group by information is passed to this function copy the summed withdrawn and paidin data to the ordinary withdrawn and paidin array positions
                    $row["amountWithdrawn"] = $row["sumAmountWithdrawn"];
                    $row["amountPaidIn"] = $row["sumAmountPaidIn"];
                }
                $docsDetailsAry[] = $row;
            }
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      //pr($docsDetailsAry);
      return $docsDetailsAry;
}

/* Need to write a description for this function - the while loop might need to be changed because perhaps record rows where a doc isn't allocated to a persOrg should be returned too. CHANGED TEMPORARILY! */
function getMultDocDataAryBAK($recStartDate, $recEndDate, $filterStr, $order, $familyChoice, $groupBy) {
    global $conn;
    try {
        $groupByStr = "";
        if ($groupBy) { //if $groupBy contains a group info (i.e. 'transCatgry') create a GROUP BY term
            $groupByStr = " GROUP BY ".$groupBy;
        }
        if ($familyChoice == "NoKids") { //only show family parents among other general records
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords 
                    WHERE 
                    '.noKidsFilter().' 
                    '.$filterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY '.$order.' recordDate, fileName');
            }
            else {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords 
                    WHERE 
                    '.noKidsFilter().' 
                    '.$filterStr.' AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');
            }
        }
        elseif ($familyChoice == "All") { //show all records including general, parents and children
            //get list of parent ids for use when familychoice = "All" to ensure the inclusion of children that are outside the date range
            $stmtPar = $conn->prepare('SELECT idR FROM allRecords WHERE ((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) AND (parent = idR) '.$filterStr.' AND statusR = "Live"');
            $stmtPar->execute(array('recStartDate' => $recStartDate, 'recEndDate' => $recEndDate));
            $parentIdAry = array();
            while ($row = $stmtPar->fetch(PDO::FETCH_ASSOC)) {
                $parentIdAry[] = $row["idR"];
            }
            $parentIdCsv = implode(",", $parentIdAry);
            $childSelector = "";
            if (0 < strlen($parentIdCsv)) {
                $childSelector = " OR parent IN(".$parentIdCsv.") ";
            }
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE (((:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) AND (parent = 0) '.$childSelector.') '.$filterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY '.$order.' recordDate, fileName');
            }
            else {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE (((:recStartDate <= recordDate) AND (recordDate <= :recEndDate))  AND (parent = 0) '.$childSelector.') '.$filterStr.' AND statusR = "Live" ORDER BY '.$order.' recordDate, fileName');
            }
        }
        else { //show only parents and children of the family id passed as a numeric argument in $familyChoice
            if ($groupBy) {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, SUM(amountWithdrawn) AS sumAmountWithdrawn, amountPaidIn, SUM(amountPaidIn) AS sumAmountPaidIn, statusR, recordNotes FROM allRecords WHERE parent = '.$familyChoice.' '.$filterStr.' AND statusR = "Live"'.$groupByStr.' ORDER BY '.$order.' fileName, recordDate'); //order by doc filename first so split docs don't occur - may mean docs appear in wierd order
            }
            else {
                $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE parent = '.$familyChoice.' '.$filterStr.' AND statusR = "Live" ORDER BY '.$order.' fileName, recordDate'); //order by doc filename first so split docs don't occur - may mean docs appear in wierd order
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


/* Recovers the document filename from the previous session by selecting it from the docFileName field in personSession table under this userID. */
function getDocFileName($userId) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT docFileName FROM personSession WHERE id = :userId');
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
        return $row["docFileName"];        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Returns id in table of document matching $fileNameDate and $fileNameNum.  */
function getdocIdInTable($fileNameDate, $fileNameNum) {
    global $conn;
    $docId = 0;
    try {
        $stmt = $conn->prepare('SELECT id FROM docCatalog WHERE (fileNameDate = :fileNameDate) AND (fileNameNum = :fileNameNum)');
        $stmt->execute(array('fileNameDate' => $fileNameDate, 'fileNameNum' => $fileNameNum));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $docId = $row["id"];
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $docId;
}

/* Returns an array of document tag (Furniture Project, Church Building, Van etc.) indexed by id sorted into alphabetical order (so ids will not necessarily be sequential) */
function getDocTagData() {
    global $conn;
    $docVarietyNameArray = array();
    try {
        $stmt = $conn->prepare("SELECT id, docTagName, status FROM docTags WHERE status = 'active' ORDER BY docTagName");
        $stmt->execute(array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $docTagNameArray[$row['id']] = $row['docTagName'];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $docTagNameArray;
}

/* Returns an array of document variety (receipt, statement, letter etc.) indexed by id sorted into alphabetical order (so ids will not necessarily be sequential) */
function getDocVarietyData() {
    global $conn;
    $docVarietyNameArray = array();
    try {
        $stmt = $conn->prepare("SELECT id, docVarietyName, status FROM docVarieties WHERE status = 'active' ORDER BY docVarietyName");
        $stmt->execute(array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $docVarietyNameArray[$row['id']] = $row['docVarietyName'];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $docVarietyNameArray;
}

/* Returns the next filename number to be used for a given filename date. If a file name for the given date doesn't exist 1 will be returned (assumes this is the first time the filename date is to be used). */
function getNextFileNumFromFileNameDateBAK($fileNameDate) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT MAX(fileNameNum) AS lastUsedFileNameNum FROM docCatalog WHERE fileNameDate = :fileNameDate');
        $stmt->execute(array('fileNameDate' => $fileNameDate));
        $rowJobs = $stmt->fetch();
        $lastUsedFileNameNum = $rowJobs['lastUsedFileNameNum'];
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    if ($lastUsedFileNameNum) { //check if anything is returned (if the filenam date exists something should be returned - otherwise not)
        $nextFileNameNum = $lastUsedFileNameNum + 1; //add one to the last used filename number to get the next one to be used
    }
    else {
        $nextFileNameNum = 1; //return 1, this is the first time the filename date is to be used
    }
    return $nextFileNameNum;
}

/* Returns an array of org or person names indexed by id sorted into alphabetical order (so ids will not necessarily be sequential) */
function getOrgOrPersonsList() {
    global $conn;
    $orgsOrPersonsArray = array();
    try {
        $stmt = $conn->prepare("SELECT id, orgOrPersonName, status FROM orgsOrPersons WHERE status = 'active' ORDER BY orgOrPersonName");
        $stmt->execute(array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $orgsOrPersonsArray[$row['id']] = $row['orgOrPersonName'];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $orgsOrPersonsArray;
}

/* Returns an array of orgPerAccCat attributes (Volunteer, Robertson Trust Budget, Pret a Mange Budget etc.) indexed by id sorted into alphabetical order (so ids will not necessarily be sequential) */
function getorgPerCategories() {
    global $conn;
    $docTagNameArray = array();
    try {
        $stmt = $conn->prepare("SELECT id, categoryName, status FROM orgPerCategories WHERE status = 'active' ORDER BY categoryName");
        $stmt->execute(array());
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $docTagNameArray[$row['id']] = $row['categoryName'];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $docTagNameArray;
}


/* Returns the value of the field referenced by $fieldName in row $recRowId from allRecords table. */
function getRecFieldValueAtRow($recRowId, $fieldName) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT '.$fieldName.' FROM allRecords WHERE idR = ?');
        $stmt->execute(array($recRowId));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $fieldValue = $row[$fieldName];
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $fieldValue;
}

/* Return an array of arrays each containing the fields of a record where reconcileDocId = $bankStatementIdR. */
function getReconciledDataAry($bankStatementIdR) {
    global $conn;
    $docsDetails = array();
    try {
        $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE idR = :idR AND statusR = "Live" ORDER BY reconciledDate');
        $stmt->execute(array('idR' => $bankStatementIdR));    
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $docsDetails[] = $row;


        $stmt = $conn->prepare('SELECT idR, fileName, docType, compoundColNum, dateTimeRecCreated, recordDate, parent, compound, personOrOrg, transCatgry, accWorkedOn, budget, referenceInfo, umbrella, reconcilingAcc, reconciledDate, reconcileDocId, amountWithdrawn, amountPaidIn, statusR, recordNotes FROM allRecords WHERE reconcileDocId = :reconcileDocId AND statusR = "Live" ORDER BY reconciledDate');
        $stmt->execute(array('reconcileDocId' => $bankStatementIdR));    
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $docsDetails[] = $row;
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    return $docsDetails;
}

/* Returns an array of user data for user $userId */
function getUserData($userId) {
    global $conn;
    $userData = array();
    try {
        $stmt = $conn->prepare('SELECT personName, passwordHash, forcePwChange, customSessionCookie, superuser, loggedIn, clientFingerprint, serialMenuArray, menusAvailableCsv, serialDocArray, docFileName, unixSecsSessStartTime, unixSecsAtLastAccess, dateTimeCreated, status FROM personSession WHERE id = :userId');
        $stmt->execute(array('userId' => $userId));
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
      return $userData;
}

/* checks personSession table for a cookie match - if a match is found returns user id, 0 if not. */
function getUserIdfromCookie($customSessionCookie) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT id FROM personSession WHERE customSessionCookie = :customSessionCookie'); //get the password hash for the provided username
        $stmt->execute(array('customSessionCookie' => $customSessionCookie));
        $row = $stmt->fetch();
        $userId = $row["id"];
        if ($userId) { //if user id has been found return id, else return 0
            return $userId;
        }
        else {
            return 0;
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Checks the time of the last activity recorded in personSession table for the passed user id and if the current time is more than $noActivityTimeLimSecs in advance of it TRUE is returned, otherwise FALSE. */
function inactiveTimeout($userId, $noActivityTimeLimSecs) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT unixSecsAtLastAccess FROM personSession WHERE id = :userId'); //get the unixSecsAtLastAccess for the provided user id
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
        $timeOfLastActivity = $row["unixSecsAtLastAccess"];
        if (($timeOfLastActivity + $noActivityTimeLimSecs) < time()) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Inserts all subarray rows, successively, from $dataArray into $table. $headerRowArray contains the names of the columns that are to be inserted into and must match the data columns in $dataArray. */
function insertArrayIntoTable($table, $headerRowArray, $dataArray) { // ######### NOT YET TESTED !!! #########
    global $conn;
    try {
        $headerNamesCsv = implode(",",$headerRowArray); //implode the row array to produce a csv string of the column  names
        $questionmarkArray = array_fill(0, sizeof($headerRowArray), "?"); //creates an array of questionmarks with the same number of elements as the $headerRow array
        $questionmarksCsv = implode(",",$questionmarkArray); //implode the questionmark array to produce a csv string of questionmarks, one questionmark for each column header names
        foreach($dataArray as $rowArray) {
            $stmt = $conn->prepare("INSERT INTO ".$table." (".$headerNamesCsv.") VALUES (".$questionmarksCsv.")");
            $stmt->execute($rowArray); 
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }    
}

/* Checks loggedIn flag for passed user and if it is set returns TRUE, otherwise FALSE.  */
function loggedIn($userId) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT loggedIn FROM personSession WHERE id = :userId'); //get the loggedIn flag for the provided user id
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
        return $row["loggedIn"];
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Sets unixSecsAtLastAccess to time().  */
function resetActivTime($userId) {
    global $conn;
    global $_customSessionCookieLength;
    global $_cookieName;
    try {
        $timeSecs = time();
        $stmt = $conn->prepare('UPDATE personSession SET unixSecsAtLastAccess = :unixSecsAtLastAccess WHERE id = :userId');
        $stmt->execute(array('userId' => $userId, 'unixSecsAtLastAccess' => $timeSecs));
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Refreshes the cookie in the personSession table for passed user and sets it on the client. Also sets unixSecsAtLastAccess to time().  */
function newCookiesAndResetActivTime($userId) {
    global $conn;
    global $_customSessionCookieLength;
    global $_cookieName;
    try {
        $timeSecs = time();
        $customSessionCookie = randomAlphaString($_customSessionCookieLength);
        setCookieOnClient($_cookieName, $customSessionCookie);
        $stmt = $conn->prepare('UPDATE personSession SET customSessionCookie = :customSessionCookie, unixSecsAtLastAccess = :unixSecsAtLastAccess WHERE id = :userId');
        $stmt->execute(array('customSessionCookie' => $customSessionCookie, 'userId' => $userId, 'unixSecsAtLastAccess' => $timeSecs));
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Checks old password against user's passwowrd hash in personSession table and if OK checks new password and its repeat match each other, and then for validity according to several criteria. If all is well TRUE is returned but FALSE is returned if any test fails. */
function oldAndNewPwOK($userId, $oldPassword, $newPassword, $newPasswordRepeat) {
    global $conn;
    global $_passwordMinLength;
    try {
        $stmt = $conn->prepare('SELECT passwordHash FROM personSession WHERE id = :userId'); //get the password hash for the provided username
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
        $pwHash = $row["passwordHash"];
        if (password_verify($oldPassword, $pwHash)) { //if the old password is legitimate carry on, else return FALSE
            if ($newPassword == $newPasswordRepeat) { //as long as new password and its repeat match proceed to check for validity, else return FALSE
                if ($_passwordMinLength <= strlen($newPassword)) { //as long as new password is long enough proceed to check for validity, else return FALSE
                    $uppercase    = preg_match('@[A-Z]@', $newPassword); //uppercase letter
                    $lowercase    = preg_match('@[a-z]@', $newPassword); //lowercase latter
                    $number       = preg_match('@[0-9]@', $newPassword); //number
                    $specialChars = preg_match('@[^\w]@', $newPassword); //special character
                    if ($uppercase && $lowercase && $number && $specialChars) { //as long as new password contains at least one of each character category save it to personSession and clear forcePwChange flag
                        return TRUE;
                    }
                    else {
                        return FALSE;
                    }
                }
                else {
                    return FALSE;
                }
            }
            else {
                return FALSE;
            }
        }
        else {
            return FALSE;
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Checks force password change flag for passed user and if it is set returns TRUE, otherwise FALSE.  */
function PwResetFlagIsSet($userId) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT forcePwChange FROM personSession WHERE id = :userId'); //get the forcePwChange flag for the provided user id
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
        return $row["forcePwChange"];
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Recovers the random document pointers array from the previous session by unserializing it from the serialDocArray field in personSession table under this userID. */
function recoveredDocRandomsArray($userId) {
	global $conn;
	try {
		$stmt = $conn->prepare('SELECT serialDocArray FROM personSession WHERE id = :userId');
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
    	$serialDocArray = unserialize($row["serialDocArray"]);
    	if (is_array($serialDocArray)) { //do basic check to ensure data that is to be returned is an array!
    		return $serialDocArray;
    	}
    	else {
    		return array();
    	}
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Recovers the restrictions array by unserializing it from the restrictions field in personSession table under this userID. */
function recoveredRestrictionsAry($userId) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT restrictions FROM personSession WHERE id = :userId');
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
        $restrictionsAry = unserialize($row["restrictions"]);
        if (is_array($restrictionsAry)) { //do basic check to ensure data that is to be returned is an array!
            return $restrictionsAry;
        }
        else {
            return [];
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}


/* Recovers the random menu pointers array from the previous session by unserializing it from the serialMenuArray field in personSession table under this userID. */
function recoveredMenuRandomsArray($userId) {
	global $conn;
	try {
		$stmt = $conn->prepare('SELECT serialMenuArray FROM personSession WHERE id = :userId');
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
    	$menuArray = unserialize($row["serialMenuArray"]);
    	if (is_array($menuArray)) { //do basic check to ensure data that is to be returned is an array!
    		return $menuArray;
    	}
    	else {
    		return [];
    	}
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Saves document data to docCatalog and allRecords tables under this doc fileNameNum.ext . It cannot delete persOrgs - only add them! */
function saveDocDataDEPRECTATED($fileNameNumExt, $persOrgsRequiredCsv, $docVariety, $docTag, $docFullyReferenced, $docDataCompleted, $dateEarliestRecord, $dateLatestRecord, $notes) {
    global $conn;
    $nameNumExtArray = explode('.', $fileNameNumExt); //section to derive filename date, number and extension from $fileNameNumExt - [0] => [2018-05-07-02-47], [1] => [pdf]
    $nameNumArray = explode('-', $nameNumExtArray[0]);  //turn date, number back into an array   
    $docYear = $nameNumArray[0];
    $docMonth = $nameNumArray[1];
    $docDayOfMonth = $nameNumArray[2];
    $fileNameDate = $docYear."-".$docMonth."-".$docDayOfMonth;
    $fileNameNum = $nameNumArray[3];
    $fileExt = $nameNumExtArray[1];
    try {
    	//get id for doc file so it can be used in operations below on docCatalog and allRecords
    	$stmt = $conn->prepare('SELECT id FROM docCatalog WHERE (fileNameDate = :fileNameDate) AND (fileNameNum = :fileNameNum) AND (fileExt = :fileExt)');
        $stmt->execute(array('fileNameDate' => $fileNameDate, 'fileNameNum' => $fileNameNum, 'fileExt' => $fileExt));
        $docsDetails = $stmt->fetch(PDO::FETCH_ASSOC);
        $docId = $docsDetails['id'];
        //update doc data like tag, dates, etc in docCatalog
        $stmt = $conn->prepare('UPDATE docCatalog SET docVariety = :docVariety, docTag = :docTag, docFullyReferenced = :docFullyReferenced, docDataCompleted = :docDataCompleted, dateEarliestRecord = :dateEarliestRecord, dateLatestRecord = :dateLatestRecord, notes = :notes WHERE id = :id');
        $stmt->execute(array('docVariety'=>$docVariety, 'id'=>$docId, 'docTag'=>$docTag, 'docFullyReferenced'=>$docFullyReferenced, 'docDataCompleted'=>$docDataCompleted, 'dateEarliestRecord'=>$dateEarliestRecord, 'dateLatestRecord'=>$dateLatestRecord, 'notes'=>$notes));
        //get existing allRecordskl table data associated with this doc id
        $stmt = $conn->prepare('SELECT idR, personOrOrg FROM allRecords WHERE (docId = :docId)');
        $stmt->execute(array('docId' => $docId));
        $idrWithNoPers = 0;
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $idR[] = $row["idR"];
            $persOrgsExistingAry[] = $row["personOrOrg"];
            if (!$row["personOrOrg"]) { //if a row exists with an empty personOrOrg (= 0) save the id for that row so it can be used for the first persOrg record below, if needed
            	$idrWithNoPers = $row["idR"];
            }
        }
        $persOrgsRequestedAry = array();
		if ($persOrgsRequiredCsv) { //check that the csv holds data and is not just "" or else array([0]=>) will be produced instead of an empty array()
        	$persOrgsRequestedAry = explode(',', $persOrgsRequiredCsv);
		}       
        //loop of persOrgs that need to be added to allRecords table
        $persOrgToBeAddedAry = array();
        foreach($persOrgsRequestedAry as $persOrgReq) {
        	if (!in_array($persOrgReq, $persOrgsExistingAry)) { //as long as the persOrg doesn't already exist in allRecords (it may exist more than once if duplication has been used)
        		 $persOrgReq;
        		 if ($idrWithNoPers) { //use existing linked idR - $idrWithNoPers - as it has been produced because no persOrgs are set
        		 	$stmt = $conn->prepare("UPDATE allRecords SET docId = :docId, dateTimeRecCreated = NOW(), recordDate = :dateEarliestRecord, personOrOrg = :persOrgReq, statusR = 'Live' WHERE idR = :idrWithNoPers");
                	$stmt->execute(array('docId'=>$docId, 'dateEarliestRecord'=>$dateEarliestRecord, 'persOrgReq'=>$persOrgReq, 'idrWithNoPers'=>$idrWithNoPers));
        		 	$idrWithNoPers = 0; //reset to 0 so no attempt will be made to use it in a later itteration of this foreach loop
        		 }
        		 else { //try for a 'reuse' row that has been previously used and subsequently released when persOrg deleted
        		 	$statusR = 'Reuse';
        		 	$stmt = $conn->prepare("UPDATE allRecords SET docId = :docId, dateTimeRecCreated = NOW(), recordDate = :dateEarliestRecord, personOrOrg = :persOrgReq, statusR = 'Live' WHERE statusR = :statusR ORDER BY idR LIMIT 1");
                	$stmt->execute(array('docId'=>$docId, 'dateEarliestRecord'=>$dateEarliestRecord, 'persOrgReq'=>$persOrgReq, 'statusR'=>$statusR));
                	$reusableRowFound = TRUE;
                	if ($stmt->rowCount() == 0) {
                		$reusableRowFound = FALSE;
                	}
        		 	if (!$reusableRowFound) { //no reusable rows so create new record
        		 		$stmt = $conn->prepare("INSERT INTO allRecords (docId, dateTimeRecCreated, recordDate, personOrOrg, statusR) VALUES (?, NOW(), ?, ?, 'Live')");
                		$stmt->execute(array($docId, $dateEarliestRecord, $persOrgReq));
        		 	}
        		
                }
        	}
        }

        //STUFF THAT IS PARKED FOR USE IN FUTURE DELETING FUNCTION

		//$stmt = $conn->prepare("INSERT INTO allRecords (docId, dateTimeRecCreated, recordDate, personOrOrg, statusR) VALUES (?, NOW(), ?, ?, 'Reuse')");
        //$stmt->execute(array(0, '2000-01-01', 0 ));

        //get array of idRs of rows that need to be removed from allRecords table //NOT USED IN THIS FUNCTION BUT KEEP FOR POSSIBLE USE IN FUNCTION TO REMOVE INDIVIDUAL RECORDS !!
    /*    $IdrIndex = 0;
        $idRToBeRemovedAry = array();
        foreach($persOrgsExistingAry as $persOrgExstngIdx => $persOrgExstng) {
        	if (!in_array($persOrgExstng, $persOrgsRequestedAry)) {
        		$idRToBeRemovedAry[] = $idR[$IdrIndex];
        	}
        	$IdrIndex++;
        }
		//idRToBeRemovedAry (if all persOrg ids are to be removed leave one docId with persOrg == 0)
         */

    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Saves document filename for next session by storing it in docFileName field in personSession table under this userID. */
function saveDocFileName($userId, $docFileName) {
    global $conn;
    try {
        $stmt = $conn->prepare('UPDATE personSession SET docFileName = :docFileName WHERE id = :userId');
        $stmt->execute(array('docFileName' => $docFileName, 'userId' => $userId));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Saves the random document pointers array for next session by serializing it and storing it in serialDocArray field in personSession table under this userID. */
function saveDocRandomsArray($userId, $docArray) {
	global $conn;
	try {
    	$serialDocArray = serialize($docArray);
    	$stmt = $conn->prepare('UPDATE personSession SET serialDocArray = :serialDocArray WHERE id = :userId');
        $stmt->execute(array('serialDocArray' => $serialDocArray, 'userId' => $userId));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Saves the the restrictions array by serializing it and storing it in restrictions field in personSession table under this userID. */
function saveRestrictionsArray($userId, $restrictionsAry) {
    global $conn;
    try {
        $serialRestrictionsAry = serialize($restrictionsAry);
        $stmt = $conn->prepare('UPDATE personSession SET restrictions = :serialRestrictionsAry WHERE id = :userId');
        $stmt->execute(array('serialRestrictionsAry' => $serialRestrictionsAry, 'userId' => $userId));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    //saveMessage("saveMenuRandomsArray() - time = ".microtime(true));
}

/* Saves the random menu pointers array for next session by serializing it and storing it in serialMenuArray field in personSession table under this userID. */
function saveMenuRandomsArray($userId, $menuArray) {
	global $conn;
	try {
    	$serialMenuArray = serialize($menuArray);
    	$stmt = $conn->prepare('UPDATE personSession SET serialMenuArray = :serialMenuArray WHERE id = :userId');
        $stmt->execute(array('serialMenuArray' => $serialMenuArray, 'userId' => $userId));        
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    //saveMessage("saveMenuRandomsArray() - time = ".microtime(true));
}

/* Returns TRUE if the value in the database table personSession pointed to by the passed personCookie and key IS set */
function sessionIs($personCookie, $dataKey) {
    global $conn;
    switch ($dataKey) {
        case "loggedIn":
            $data = empty($_SESSION["loggedIn"]) ? FALSE : TRUE;
            break;
        case "alreadyWelcomed":
            $data = empty($_SESSION["alreadyWelcomed"]) ? FALSE : TRUE;
            break;
        default:
            $data = "";
            break;
    }
    return $data;
}

/* Returns TRUE if the value in the database table personSession pointed to by the passed personCookie and key IS set. If there is no match for the personCookie FALSE is returned. */
// USE PERSON id FOR ALL THE FOLLOWING TESTS (WHICHEVER ONES ARE STILL GOING TO BE USED)
function sessionIsTEST($personCookie, $dataKey) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT COUNT(id) AS duplicates FROM personSession WHERE personCookie = :personCookie'); //check to see if the name already exists
        $stmt->execute(array('personCookie' => $personCookie));
        $row = $stmt->fetch(); 
        if ($row["duplicates"] == 1) { //if exactly 1 record exists (expected) go ahead and extract the data
            $stmt = $conn->prepare('SELECT '.$dataKey.' FROM personSession WHERE personCookie = :personCookie'); //get the data
            $stmt->execute(array('personCookie' => $personCookie));
            $row = $stmt->fetch();
            return $row[$dataKey];
        }
        else { //if the personCookie does exist
            return FALSE;
        } 
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Checks the time of the last activity recorded in personSession table for the passed user id and if the current time is more than $noActivityTimeLimSecs in advance of it TRUE is returned, otherwise FALSE. */
function sessionTimeout($userId, $sessionTimeLimSecs) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT unixSecsSessStartTime FROM personSession WHERE id = :userId'); //get the unixSecsSessStartTime for the provided user id
        $stmt->execute(array('userId' => $userId));
        $row = $stmt->fetch();
        $timeOfStartOfSession = $row["unixSecsSessStartTime"];
        if (($timeOfStartOfSession + $sessionTimeLimSecs) < time()) {
            return TRUE;
        }
        else {
            return FALSE;
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Creates a new cookie and sets it on client, and for passed userId:
    sets customSessionCookie to same value
    loggedIn = TRUE
    serialDocArray = ""
    serialDocArray = ""
    docFileName = ""  
    unixSecsSessStartTime = time()
  PLAN IN FUTURE - Opens the record of the current session in the sessionLog table by recording the reason. (any open record is closed with the reason that it is supereseded by the current session) */
function startSession($userId) {
    global $conn;
    global $_customSessionCookieLength;
    global $_cookieName;
    try {
        $timeSecs = time();
        $customSessionCookie = randomAlphaString($_customSessionCookieLength);
        setCookieOnClient($_cookieName, $customSessionCookie);
        $stmt = $conn->prepare('UPDATE personSession SET customSessionCookie = :customSessionCookie, loggedIn = TRUE, serialMenuArray = "", serialDocArray = "", docFileName = "", unixSecsSessStartTime = :unixSecsSessStartTime WHERE id = :userId');
        $stmt->execute(array('customSessionCookie' => $customSessionCookie, 'userId' => $userId, 'unixSecsSessStartTime' => $timeSecs));
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Checks that a parent doc exists and is not already a child. A message is returned to indicate its status (see code below). */
function statusOfProposedParentDoc($parentDocId) {
    global $conn;
    if ($parentDocId == 0) { //parentDocId hasn't been specifically set so it defaults to zero
        return "No Parent Doc selected";
    }
    try {
        $stmt = $conn->prepare('SELECT EXISTS(SELECT 1 FROM docCatalog WHERE id = :parentDocId) AS docExists');
        $stmt->execute(array('parentDocId' => $parentDocId));
        $row = $stmt->fetch();
        $docExists = $row["docExists"];
        if ($docExists) {
            $stmt = $conn->prepare('SELECT parentDocRef FROM docCatalog WHERE id = :parentDocId'); //get any existing parentDocRef for the proposed parent doc
            $stmt->execute(array('parentDocId' => $parentDocId));
            $row = $stmt->fetch();
            $proposedParentDocRef = $row["parentDocRef"];
            if ($proposedParentDocRef < 1) { //as long as the proposed parent doc doesn't already have its parentDocRaf set to indicate it is a child
                return "Parent Doc OK";
            }
            else {
                return "Parent Doc already a Child";
            }

        }
        else {
            return "Parent Doc Id not in table";
        }  
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}



/* Replaces the filename in one allrecords transaction row with the new filename of the document that has just been scanned and uploaded. The details of the new filename are held in $fileUpldReportArry,the row to be updated in allRecords is held in $idR. The general purpose behind this function is to be able to swap an existing document for a new one because the new one contains corrected errors or new information, or a new document completely that is more suitable. */
function updateDocFilenameInOneTrans($fileUpldReportArry, $idR) {
    global $conn;
    $notification = "Oops! Problem!";
    try {
        $subArry = $fileUpldReportArry[0]; //only one file should exist in the $fileUpldReportArry to upload, as this function is only used to replace one filename in one transaction row of allRecords
            if ($subArry[6] == TRUE) { //contains details of a file that was created and not just an error
                $fileName = $subArry[2];
                $stmt = $conn->prepare("UPDATE allRecords SET fileName = ? WHERE idR = ?");
                $stmt->execute(array($fileName, $idR));
                if ($stmt->rowCount() == 1) { //test for number of rows and only issue success notification if exactly 1 row has been changed
                    $notification = "Doc Swapped";
                }
            }
    } catch(PDOException $e) {
        $notification = "Oops! Error!";
      echo 'ERROR: ' . $e->getMessage();
      }
    return $notification;
}


/* Replaces the filename in several allrecords transaction rows, that currently share the same document, with the new filename of the document that has just been scanned and uploaded. The details of the new filename are held in $fileUpldReportArry. The rows to be updated in allRecords are all the ones that match the existing filename passed in $docFileNameToSwap. The general purpose behind this function is to be able to swap an existing document linked to several transactions for a new one in all those transactions because the new one contains corrected errors or new information, or a new document completely that is more suitable. */
function updateDocFilenameInSeveralTrans($fileUpldReportArry, $docFileNameToSwap) {
    global $conn;
    $notification = "Oops! Problem!";
    try {
        $subArry = $fileUpldReportArry[0]; //only one file should exist in the $fileUpldReportArry to upload, as this function is only used to replace one filename in one transaction row of allRecords
            if ($subArry[6] == TRUE) { //contains details of a file that was created and not just an error
                $fileName = $subArry[2];
                $stmt = $conn->prepare("UPDATE allRecords SET fileName = ? WHERE fileName = ?");
                $stmt->execute(array($fileName, $docFileNameToSwap));
                if (0 < $stmt->rowCount()) { //test for number of rows and only issue success notification if exactly 1 row has been changed
                    $notification = "Grp Doc Swapped";
                }
            }
    } catch(PDOException $e) {
        $notification = "Oops! Error!";
      echo 'ERROR: ' . $e->getMessage();
      }
    return $notification;
}


/* Gets idR of later or earlier dated bank statement (or any other account of same type as curBankStatementIdR - not just bank statements) from allRecords table. If no earlier or later one exists the current idR is returned.  */
function getAdjacentBankStmnt($curBankStatementIdR, $fwdBack = "Forward") {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT recordDate, accWorkedOn FROM allRecords WHERE idR = ?'); //get recordDate and accWorkedOn (bank id) for current bank statement - used to search for the adjecent one
        $stmt->execute(array($curBankStatementIdR));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $bankStatementDate = $row['recordDate'];
        $accWorkedOn = $row['accWorkedOn'];

        if ($fwdBack == "Forward") { //get later statement idR
            $stmt = $conn->prepare('SELECT idR FROM allRecords WHERE (accWorkedOn = ?) AND (statusR = "Live") AND (? < recordDate) ORDER BY recordDate ASC LIMIT 1');
        }
        else { //get earlier statement idR
            $stmt = $conn->prepare('SELECT idR FROM allRecords WHERE (accWorkedOn = ?) AND (statusR = "Live") AND (recordDate < ?) ORDER BY recordDate DESC LIMIT 1');
        }
        $stmt->execute(array($accWorkedOn, $bankStatementDate));
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        $newBankStatementIdR = $row['idR'];

        if (!isset($newBankStatementIdR)) { //check that there is an earlier/later dated bank statement to display, if not, retain the existing bank statement idR (the last valid one to have been found)
            $newBankStatementIdR = $curBankStatementIdR;
        }

    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
    return $newBankStatementIdR;
}


/* use cellId (in inputArry) to update the document fileName for the pdf that will be downloaded via obscureTest.php or obscureTest2.php. If column 8 (bank reconcilation date) is selected the bank statement (instead of a normal document) before the date of the clicked record is used for the filename if the record doesn't already contain an id other than 0 in its reconcileDocId field. The reconcileDocId field will be updated with the selected bank statement id at this time. In the case of reconcileDocId already containing the id of a bank statement this is used to update the doc fileName unless data from the statement up/down buttons indicate the next statement date above or below the current one is to be used, in which case this is what happens and the reconcileDocId is updated appropriately.  */
function updateDocFilename($inputArry, $outputArry, $_fieldNameAry, $tables, $allowedToEdit) {
    global $conn;
    global $nonVolatileArray;
    $outputArry["docChanged"] = "No";
    if (array_key_exists("docUpdateSendHasRun", $inputArry)) { //only do update if the calling JS function has run 
        try {
            $cellIdAry = explode('-', $inputArry["docUpdateCellId"]);
            $idR = $cellIdAry[0];
            $column = $cellIdAry[1];
            $auxButtonTxt = $inputArry["auxButtonTxt"];
            if (($_fieldNameAry[$column] == "reconciledDate" ) && (array_key_exists("bankAccName", $inputArry))) { //reconcilation column so set filename for bank statement doc if the row is for the appropriate account
                if (($auxButtonTxt == "Reset accWorkedOn") && $allowedToEdit) { //reset accWorkedOn to 0 to make things just as if no bank statement had ever been selected
                    $stmt = $conn->prepare('UPDATE allRecords SET reconcileDocId = 0 WHERE idR = :idR'); //set idR for latest selected bank statement
                    $stmt->execute(array('idR' => $idR));
                    $bankStatementIdR = $idR; //set to normal document for this clicked record 
                }
                else { //carry on with bank statement selection processes
                    $stmt = $conn->prepare('SELECT recordDate, reconcileDocId FROM allRecords WHERE idR = ?'); //get recordDate and reconcileDocId for current row in case they are needed for showing initial bank statement before further choice is made
                    $stmt->execute(array($idR));
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $rowRecordDate = $row['recordDate'];
                    $reconcileDocId = $row['reconcileDocId']; //used when the current row already has been assigned the id of a bank statement that will be used to search for earlier and later ones
                    //get accWorkedOn id from the account name (string i.e. "RBS 8252") passed in the array
                    $stmt = $conn->prepare('SELECT id FROM accounts WHERE accountName = ?'); //get id for linked (bank) account name so the bank statements can be selected from the right bank
                    $stmt->execute(array($inputArry["bankAccName"]));
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                    $accountId = $row['id'];
                    if ((0 < $reconcileDocId) && ($auxButtonTxt == "Later Statement")) { //reconcileDocId already exists and the call is from the  "Later Statement" button 
                        $stmt = $conn->prepare('SELECT recordDate FROM allRecords WHERE idR = ?'); //get recordDate for current bank statement that will be used to search for the next highest dated one
                        $stmt->execute(array($reconcileDocId));
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $bankStatementDate = $row['recordDate'];
                        $stmt = $conn->prepare('SELECT idR FROM allRecords WHERE (accWorkedOn = ?) AND (statusR = "Live") AND (? < recordDate) ORDER BY recordDate ASC LIMIT 1'); //gets next highest bankStmt idR
                        $stmt->execute(array($accountId, $bankStatementDate));
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $bankStatementIdR = $row['idR'];
                        if (isset($bankStatementIdR) && $allowedToEdit) { //check that there actually is another later dated bank statement to display
                            $stmt = $conn->prepare('UPDATE allRecords SET reconcileDocId = :reconcileDocId WHERE idR = :idR'); //set idR for latest selected bank statement
                            $stmt->execute(array('reconcileDocId' => $bankStatementIdR, 'idR' => $idR)); 
                        }
                        else { //if not, retain the existing bank statement idR (the last valid one to have been found)
                            $bankStatementIdR = $reconcileDocId;
                        }
                    }
                    else if ((0 < $reconcileDocId) && ($auxButtonTxt == "Earlier Statement")) { //reconcileDocId already exists and the call is from the  "Earlier Statement" button 
                        $stmt = $conn->prepare('SELECT recordDate FROM allRecords WHERE idR = ?'); //get recordDate for current bank statement that will be used to search for the next lowest dated one
                        $stmt->execute(array($reconcileDocId));
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $bankStatementDate = $row['recordDate'];
                        $stmt = $conn->prepare('SELECT idR FROM allRecords WHERE (accWorkedOn = ?) AND (statusR = "Live") AND (recordDate < ?) ORDER BY recordDate DESC LIMIT 1'); //gets next lowest bankStmt idR
                        $stmt->execute(array($accountId, $bankStatementDate));
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $bankStatementIdR = $row['idR'];
                        if (isset($bankStatementIdR) && $allowedToEdit) { //check that there actually is another later dated bank statement to display
                            $stmt = $conn->prepare('UPDATE allRecords SET reconcileDocId = :reconcileDocId WHERE idR = :idR'); //set idR for latest selected bank statement
                            $stmt->execute(array('reconcileDocId' => $bankStatementIdR, 'idR' => $idR)); 
                        }
                        else { //if not, retain the existing bank statement idR (the last valid one to have been found)
                            $bankStatementIdR = $reconcileDocId;
                        }
                    }
                    else if (0 < $reconcileDocId) { //reconcileDocId already exists
                        $bankStatementIdR = $reconcileDocId; //no change required so just copy the existing reconcileDocId
                    }
                    else { //no previously saved reconcileDocId or button press so retrieve the latest dated bank statement idR that is before the date of the clicked record
                        $stmt = $conn->prepare('SELECT idR FROM allRecords WHERE (accWorkedOn = ?) AND (statusR = "Live") AND (recordDate < ?) ORDER BY recordDate DESC LIMIT 1'); //selects bank statement idR
                        $stmt->execute(array($accountId, $rowRecordDate));
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $bankStatementIdR = $row['idR'];
                        if (isset($bankStatementIdR) && $allowedToEdit && (($auxButtonTxt == "Later Statement") || ($auxButtonTxt == "Earlier Statement"))) { //check that there actually is a bank statement to display and it has been selected by clicking later or earlier buttons (i.e. not by just clicking the cell)
                            $stmt = $conn->prepare('UPDATE allRecords SET reconcileDocId = :reconcileDocId WHERE idR = :idR'); //set idR for latest selected bank statement
                            $stmt->execute(array('reconcileDocId' => $bankStatementIdR, 'idR' => $idR)); 
                        }
                        else { //if not, retain the idR of the clicked record, just displays the record document because no bank statement can be found
                            $bankStatementIdR = $idR;
                        }
                    }
                }
                $stmt = $conn->prepare('SELECT fileName FROM allRecords WHERE idR = ?'); //get filename of selected bank statement or original record doc if that has been reverted to
                $stmt->execute(array($bankStatementIdR));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);  
            }
            elseif ($_fieldNameAry[$column] == "budget" ) { //budget cell and not an inhibited value (like "SPLIT", "None", "" etc.) 
                $stmtBudg = $conn->prepare('SELECT budget FROM allRecords WHERE idR = ?');
                $stmtBudg->execute(array($idR));
                $row = $stmtBudg->fetch(PDO::FETCH_ASSOC);
                $budgetId = $row['budget'];
                $budgetName = $tables->getStrValue("budget", $budgetId);
                if (($budgetName == "") || ($budgetName == "Reserves") || ($budgetName == "SPLIT") || ($budgetName == "None") || ($budgetName == "Church Main")) { //budget doc filename display not sensible so just do original record doc
                    $stmt = $conn->prepare('SELECT fileName FROM allRecords WHERE idR = ?');
                    $stmt->execute(array($idR));
                    $row = $stmt->fetch(PDO::FETCH_ASSOC);
                }
                else {
                    $stmtBudgFile = $conn->prepare('SELECT fileName FROM allRecords WHERE (budget = ?) AND (amountPaidIn != 0)');
                    $stmtBudgFile->execute(array($budgetId));
                    $row = $stmtBudgFile->fetch(PDO::FETCH_ASSOC);
                }
                //$outputArry["BUDGET-ID"] = "";
            }
            else { //normal column so set usual doc
                $stmt = $conn->prepare('SELECT fileName FROM allRecords WHERE idR = ?');
                $stmt->execute(array($idR));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
            }
            $docFileName = $row['fileName'];
            if ($docFileName != $nonVolatileArray["docNameNumStr"]) {
                $outputArry["docChanged"] = "Yes";
            }
            $nonVolatileArray["docNameNumStr"] = $docFileName;
        } catch(PDOException $e) {
          $outputArry["ERROR"] = ': '.$e->getMessage();
          }
        $outputArry["PHPupdateDocFilenameHasRun"] = TRUE; //flag that indicates this PHP function has run and that the receiving JS function should run to handle the returned data
    }
    return $outputArry;
}


/* Uses data in fileUploadReportArray to create new rows of information in docCatalog table for the document files that have just been uploaded. $subDirName is the subdir the files have been loaded into. If any uploaded file or set of files has the parent id textbox filled in the parent doc with the appropriate id has it's parentDocRef field set to -1 to indicate that it is a parent doc (but this doesn't indicate which docs it is the parent of, just that it is a parent, children would have to be identified by another query when needed). A basic blank record in the allRecords table is also created with the date set to dateEarliestRecord and persOrg set to 0.
--
#### THIS FUNCTION SHOULD ONLY BE RUN AFTER THE PROPOSED PARENT DOC HAS BEEN CHECKED BY statusOfProposedParentDoc() !!! (to prevent a doc that is already a child form being used as a parent doc!) ####
--
 */
function updateDocsTblWithNewFileInfoDEPRECATED($fileUpldReportArry, $subDirName, $parentDocRef) {
    global $conn;
    try {
        $parentDocSet = FALSE; //flag to register when the parent doc parentDocRef field has been set to -1 (indicating it is a parent doc)
        foreach ($fileUpldReportArry as $subArry) {
            if ($subArry[8] == TRUE) { //contains details of a file that was created and not just an error or details of source files for a multifile pdf
                $stmt = $conn->prepare("INSERT INTO docCatalog (fileNameDate, fileNameNum, fileExt, numOfPages, subDir, parentDocRef, dateTimeUploaded, dateEarliestRecord, dateLatestRecord, status) VALUES (?, ?, ?, ?, ?, ?, NOW(), ?, ?, 'Current')");
                $stmt->execute(array($subArry[0], $subArry[1], $subArry[2], $subArry[3], $subDirName, $parentDocRef, $subArry[0], $subArry[0]));
                if (!$parentDocSet) {
                    $stmt = $conn->prepare('UPDATE docCatalog SET parentDocRef = -1 WHERE id = :parentDocId'); //set the parent doc parentDocRef field to -1 to indicate that it is a parent doc
                    $stmt->execute(array('parentDocId' => $parentDocRef));
                    $parentDocSet = TRUE; //set to true so this action isn't attempted multiple times!
                }
            }
            //get id for doc file so it can be used in operations below on docCatalog and allRecords
	    	$stmt = $conn->prepare('SELECT id FROM docCatalog WHERE (fileNameDate = :fileNameDate) AND (fileNameNum = :fileNameNum) AND (fileExt = :fileExt)');
	        $stmt->execute(array('fileNameDate' => $subArry[0], 'fileNameNum' => $subArry[1], 'fileExt' => $subArry[2]));
	        $row = $stmt->fetch(PDO::FETCH_ASSOC);
	        $docId = $row['id'];

         	//try to create 0 persOrg record in row that has had its data deleted and made available for reuse
            $statusR = 'Reuse';
		 	$stmt = $conn->prepare("UPDATE allRecords SET docId = :docId, dateTimeRecCreated = NOW(), recordDate = :recordDate, statusR = 'Live' WHERE statusR = :statusR ORDER BY idR LIMIT 1");
        	$stmt->execute(array('docId'=>$docId, 'recordDate'=>$subArry[0], 'statusR'=>$statusR));
        	$reusableRowFound = TRUE;
        	if ($stmt->rowCount() == 0) {
        		$reusableRowFound = FALSE;
        	}
		 	if (!$reusableRowFound) { //no reusable rows so use INSERT to create new record
		 		$stmt = $conn->prepare("INSERT INTO allRecords (docId, dateTimeRecCreated, recordDate, statusR) VALUES (?, NOW(), ?, 'Live')");
        		$stmt->execute(array($docId, $subArry[0]));
		 	}
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      } 
}


/* If potential calling JS function directStrEditAjaxSend() has run this function extracts value, row id and randomised field name from $inputArry and as long as editing is allowed the relevant field is updated. Regardless of whether the update is allowed the same field is always read and the value returned in $outputArry to either confirm the edit or replace a changed cell value with the original if the edit was not allowed (this scenario is unlikely as editing should be prevented at a higher level - it is only a last ditch scheme).  */
function updateEditableItem($inputArry, $outputArry, $allowedToEdit) {
    global $conn;
    global $plainItemsWithRandKeysAry;
    if (array_key_exists("directStrEditAjaxSendHasRun", $inputArry)) { //only do update if the calling JS function has run 
        $fieldName = getPlain($inputArry["allrecordsColNameRnd"]);
        $str = $inputArry["editableCellVal"];
        $rowId = $inputArry["editableCellIdR"];
        try {
            if ($allowedToEdit) { //only update the table if editing is allowed
                $stmt = $conn->prepare('UPDATE allRecords SET '.$fieldName.'=? WHERE idR = ?');
                $stmt->execute(array($str, $rowId));
            }
            $stmt = $conn->prepare('SELECT '.$fieldName.' FROM allRecords WHERE idR = ?');
            $stmt->execute(array($rowId));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $outputArry["updatedEditableStr"] = $row[$fieldName];
        } catch(PDOException $e) {
          echo 'ERROR: ' . $e->getMessage();
          }
        $outputArry["PHPupdateEditableItemHasRun"] = TRUE; //flag that indicates this PHP function has run and that the receiving JS function should run to handle the returned data
    }
    return $outputArry;
}


/* Takes the passed arguments and uses them to update the specified field of the mariadb table with $value in row(s) where $whereField == $whereValue. */
function updateTable($tableName, $fieldName, $value, $whereField, $whereValue) {
    global $conn;
    try {
        $stmt = $conn->prepare('UPDATE '.$tableName.' SET '.$fieldName.'=? WHERE '.$whereField.'=?');
        $stmt->execute(array($value, $whereValue));
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}


/* Updates a string field in allRecords table with values passed in $inputArry["string"] at field pointed to by $inputArry["fieldId"]. Modifies $outputArry by adding $outputArry["string"] read back from the table at the same field as a confirmation that the update occurred. Any existing values in $outputArry will be passed unchanged. If $inputArry["string"] doesn't exist this function does nothing but return the $outputArry. If $allowedToEdit is FALSE the data is not written to the table but it is still read back and returned in $outputArry.  */
/*function updateString($inputArry, $outputArry, $allowedToEdit) {
    global $conn;
    if (array_key_exists("fieldId", $inputArry) && ($inputArry["string"] != null)) { //only do update from string if the array item that points to the table field exists (used as a means to select whether the function needs to run) and nothing stupid has been entered that was not caught by any javascript regex expression
        try {
            if ($allowedToEdit) { //only allow updateing if permissions exist
                $stmt = $conn->prepare('UPDATE allRecords SET amountWithdrawn = ?, amountPaidIn = ? WHERE idR = ?');
                $stmt->execute(array($inputArry["withdrawn"], $inputArry["paidin"], $inputArry["moneyIdR"]));
            }
            $stmt = $conn->prepare('SELECT amountWithdrawn, amountPaidIn FROM allRecords WHERE idR = ?');
            $stmt->execute(array($inputArry["moneyIdR"]));
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $updatedAmountWithdrawn = $row['amountWithdrawn'];
            $updatedAmountPaidIn = $row['amountPaidIn'];
        } catch(PDOException $e) {
          $outputArry["ERROR"] = ': '.$e->getMessage();
          }
        $outputArry["string"] = $updatedAmountWithdrawn;
    }
    return $outputArry;
} */

/* Updates withdrawn and paidin amounts in allRecords table with values passed in $inputArry. This may be just one row or several rows as indicated by idrAry. All changes are read back from updated table and sent back to calling JS funcion in $outputArry. A general principle of passing all modifying data like paidinOrgSuffixClassAry through this php function is applied so that the calling function will only succesfully update the live cells and give confidence that they reflect the state of the table on the server if this php function has succesfully executed. If $allowedToEdit is FALSE the data is not written to the table but it is still read back and returned in $outputArry.  */
function updateWithdrawnPaidin($inputArry, $outputArry, $allowedToEdit) {
    global $conn;
    $updatedAmountWithdrawnAry = [];
    $updatedAmountPaidInAry = [];
    if (array_key_exists("withdrawnPaidinAjaxSendHasRun", $inputArry) && array_key_exists("compoundGroupAry", $inputArry)) { //only do update if the calling JS function has run and compoundGroupAry exists
        $compoundGroupAry = $inputArry["compoundGroupAry"];
        $idrAry = $compoundGroupAry["idrAry"];
        $withdrawnAry = $compoundGroupAry["withdrawnAry"];
        $paidinAry = $compoundGroupAry["paidinAry"];
        try {
            if ($allowedToEdit) { //only allow updating if permissions exist
                foreach ($idrAry as $idrIdx=>$idR) { //loops through all rows indicated by idrAry of withdrawn, paidin and idR arrays and UPDATES allRecords table
                    $withdrawnValue = $withdrawnAry[$idrIdx]; //uses $idR index
                    $paidinValue = $paidinAry[$idrIdx]; //uses $idR index
                    $stmt = $conn->prepare('UPDATE allRecords SET amountWithdrawn = ?, amountPaidIn = ? WHERE idR = ?');
                    $stmt->execute(array($withdrawnValue, $paidinValue, $idR));
                }
            }
            foreach ($idrAry as $idR) { //loops through all rows indicated by idrAry of withdrawn, paidin and idR arrays and SELECTS values from allRecords table
                $stmt = $conn->prepare('SELECT amountWithdrawn, amountPaidIn FROM allRecords WHERE idR = ?');
                $stmt->execute(array($idR));
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                $updatedAmountWithdrawnAry[] = $row['amountWithdrawn'];
                $updatedAmountPaidInAry[] = $row['amountPaidIn'];
            }
        } catch(PDOException $e) {
          $outputArry["ERROR"] = ': '.$e->getMessage();
          }
        $outputArry["compoundGroupAryBack"]["idrAry"] = $compoundGroupAry["idrAry"];
        $outputArry["compoundGroupAryBack"]["withdrawnColId"] = $compoundGroupAry["withdrawnColId"];
        $outputArry["compoundGroupAryBack"]["paidinColId"] = $compoundGroupAry["paidinColId"];
        $outputArry["compoundGroupAryBack"]["updatedWithdrawnAry"] = $updatedAmountWithdrawnAry;
        $outputArry["compoundGroupAryBack"]["updatedPaidInAry"] = $updatedAmountPaidInAry;
        $outputArry["compoundGroupAryBack"]["withdrnOrgSuffixClassAry"] = $compoundGroupAry["withdrnOrgSuffixClassAry"];
        $outputArry["compoundGroupAryBack"]["paidinOrgSuffixClassAry"] = $compoundGroupAry["paidinOrgSuffixClassAry"];
        $outputArry["PHPupdateWithdrawnPaidinHasRun"] = TRUE; //flag that indicates this PHP function has run and that the receiving JS function should run to handle the returned data
    }
    return $outputArry;
}

/* checks password against the one held in database table personSession against the provided username - returns user id if they match, 0 if they don't. */
function userIdifPasswordMatches($username, $password) {
    global $conn;
    try {
        $stmt = $conn->prepare('SELECT id, passwordHash FROM personSession WHERE personName = :username'); //get the password hash for the provided username
        $stmt->execute(array('username' => $username));
        $row = $stmt->fetch();
        $pwHash = $row["passwordHash"];
        if (password_verify($password, $pwHash)) { //if the password is legitimate return id, else return 0
            return $row["id"];
        }
        else {
            return 0;
        }
    } catch(PDOException $e) {
      echo 'ERROR: ' . $e->getMessage();
      }
}

/* Used to do sticky updates. Updates either the item id or actual item string (depending on what type of data the field holds) in the allRecords table field pointed to by $cellId. After updating, the data is read back from the table and if need be converted to a string again and returned to the calling command. Item lists from the database are used to create arrays to generate required ids and recover strings. This function is quite complex and includes code to create new children and parents.  */
function writeReadAllRecordsItem($inputArry, $outputArry, $allowedToEdit) {
    global $conn;
    //$outputArry["parentAndNotChildless"] = "no";
    if (array_key_exists("stickyAjaxSendHasRun", $inputArry) && array_key_exists("itemStr", $inputArry)) { //only do update if the calling JS function has run and item string exists 
        $cellIdAry = explode('-', $inputArry["cellId"]);
        $rowId = $cellIdAry[0];
        $columnId  = $cellIdAry[1];
        $fieldNameAry = array("recordDate", "personOrOrg", "transCatgry", "", "", "accWorkedOn", "budget", "referenceInfo", "reconciledDate", "umbrella", "docType", "recordNotes", "parent");
        $fieldName = $fieldNameAry[$columnId]; //create fieldName from column id (0 - 12). The array would need to be changed if the column order (and therefore the cell ids) on the display page changes!!
        $strInsteadOfKey = FALSE;
        $makeChild = FALSE;
        switch ($fieldName) { //choose the appropriate item list for the field that is being updated.
          case "personOrOrg":
            $itemAry = getOrgOrPersonsList(); //gets array of all possible orgsOrPersons in alphabetical order ie: array([1] => RBS [8] => Robertson Tr [17] => Scottish Pwr [22] => Susan)
            break;
          case "transCatgry":
            $itemAry = getorgPerCategories();
            break;
          case "accWorkedOn":
            $itemAry = getAccountList();
            break;
          case "budget":
            $itemAry = getBudgetList();
            break;
          case "referenceInfo":
            $strInsteadOfKey = TRUE;
            break;
          case "umbrella":
            $itemAry = getDocTagData();
            break;
          case "docType":
            $itemAry = getDocVarietyData();
            break;
          case "recordNotes":
            $strInsteadOfKey = TRUE;
            break;
          case "parent":
            $strInsteadOfKey = TRUE;
            $makeChild = TRUE;
            break;
          default:
            break;
        }
        if ($strInsteadOfKey) { //direct string write to table without converting to key
            $fieldVal = $inputArry["itemStr"];
        }
        else { //first convert the string to the relevent key before writing to table (and convert back to string afterwards - at end of this function)
            $fieldVal = array_search($inputArry["itemStr"], $itemAry);
            if (!$fieldVal) { //if fieldVal = FALSE (indicating the search string was not found, probably because it was intentionally "") set the fieldVal to 0 which effectively clears the field in allRecords to its default value
              $fieldVal = 0;
            }
        }
        try {
            if ($allowedToEdit) {
                $csvIdRList = implode(",", $inputArry["idRlist"]); //convert array of idRs to csv list



                //MAKE/DELETE CHILD
                if ($makeChild) { // ###### MAKE CHILD! do additional operations to make the record into a child of the record id denoted by $fieldVal
                    $doMultiRowUpdate = FALSE; //flag to indicate which type of update to do
                    if (count($inputArry["idRlist"]) == 1) { // ###### SINGLE ROW! to update
                        $stmt = $conn->prepare('SELECT parent FROM allRecords WHERE FIND_IN_SET(idR, ?)'); //get parent value (id) - only one row looked at even though FIND_IN SET is used for harmonisation with multirow update when $doMultiRowUpdate == TRUE in else section below
                        $stmt->execute(array($csvIdRList));
                        $targetRow = $stmt->fetch(PDO::FETCH_ASSOC);
                        $parent = $targetRow["parent"];
                        $idR = $inputArry["idRlist"][0]; //create simple variable name for idR of row, index set to 0 as only one element in array (because it's a single row update)
                        if ($idR == (string)$parent) { // ###### ROW FOR IMPENDING UPDATE IS A PARENT! because idR for the row in question matches the parent value for that row
                            $stmt = $conn->prepare('SELECT COUNT(idR) AS familyCount FROM allRecords WHERE parent = :parent'); //check to see how many rows have the parent field set to the same number
                            $stmt->execute(array('parent' => $parent));
                            $row = $stmt->fetch(); 
                            $numberInFamily = $row["familyCount"]; //number of members of family (including parent)
                            if ($numberInFamily == 1) { // ###### NO CHILDREN! as only one member in the family (the parent itself)
                                if ($fieldVal == "") { //$fieldVal is just "" indicating the record is to be reset from a parent to become an ordinary record, so clear parentDate and parent to default values
                                    $stmt = $conn->prepare('UPDATE allRecords SET parentDate = ?, parent = ? WHERE idR = ?'); // ~~~~~~~~~~~ clear parent
                                    $stmt->execute(array("2000-01-01", 0, $idR));
                                }
                                else { //$fieldVal is a number representing the idR of a different parent so is used to change this parent (already confirmed to have no children) into a child of that different parent. If the field value is the idR of this same parent then although the date will be written agaim nothing will change, it will remain the same parent
                                    $diffParentStmt = $conn->prepare('SELECT recordDate FROM allRecords WHERE idR = ?'); //get record date of different (or could be same) parent
                                    $diffParentStmt->execute(array($fieldVal));
                                    $diffParentRow = $diffParentStmt->fetch(PDO::FETCH_ASSOC);
                                    $diffParentDate = $diffParentRow["recordDate"];
                                    $stmt = $conn->prepare('UPDATE allRecords SET parentDate = ?, parent = ? WHERE idR = ?'); // ~~~~~~~~~~~ create different parent 
                                    $stmt->execute(array($diffParentDate, $fieldVal, $idR));
                                }
                            }
                        }
                        else { //NOT A PARENT - DO SINGLE ROW UPDATE BUT USE COMMON MULTIROW PROCEDURE TO SAVE EXTRA CODE
                            $doMultiRowUpdate = TRUE;
                        }
                    }
                    else { //NOT A SINGLE ROW - DO MULTIROW UPDATE
                        $doMultiRowUpdate = TRUE;
                    }
                    if ($doMultiRowUpdate) { // ###### DO MULTIROW (OR COULD JUST BE A SINGLE ROW IF FROM PARENT SECTION ABOVE) UPDATES - BUT LEAVE PARENTS ALONE! (because multiple rows are presented)
                        if ($fieldVal == "") { //if $fieldVal is just "" indicating the record is to be reset from a child to become an ordinary record, clear parentDate and parent to default values
                            $stmt = $conn->prepare('UPDATE allRecords SET parentDate = ?, parent = ? WHERE FIND_IN_SET(idR, ?) AND (idR != parent)'); // ~~~~~~~~~~~clear children - but leaves parents alone
                            $stmt->execute(array("2000-01-01", 0, $csvIdRList));
                        }
                        else { //$fieldVal is a number representing the idR of the parent and is to be used to change the record at $idR to a child so get parent record date
                            $stmt = $conn->prepare('SELECT recordDate FROM allRecords WHERE idR = ?'); //get record date of parent
                            $stmt->execute(array($fieldVal));
                            $parentRow = $stmt->fetch(PDO::FETCH_ASSOC);
                            if ($inputArry["lockChildToParentDate"] == "No") { //created child is intended to be its own person, displays and sums  when it's own recordDate is in the range of startDate - endDate
                                $parentDate = "2000-01-01";
                            }
                            else {
                                $parentDate = $parentRow["recordDate"]; //created child is intended to be tied to its parent, displays and sums  when it's parent's recordDate is in the range of startDate - endDate
                            }
                            $stmt = $conn->prepare('UPDATE allRecords SET parentDate = ?, parent = ? WHERE FIND_IN_SET(idR, ?) AND (idR != parent)'); // ~~~~~~~~~~~create new child - but leaves parents alone
                            $stmt->execute(array($parentDate, $fieldVal, $csvIdRList));
                        }
                    }
                } 



                //UPDATE STRING - EITHER SINGLE OR MULTIPLE FIELDS IN COLUMN
                else { //just ordinary string record, not a child update
                    $stmt = $conn->prepare('UPDATE allRecords SET '.$fieldName.'= ? WHERE FIND_IN_SET(idR, ?)'); //NEED TO MAKE THIS UPDATE ALL idRs IN COLUMN !!
                    $stmt->execute(array($fieldVal, $csvIdRList));
                }
            }
            //READ BACK FIELD 
            $csvIdRList = implode(",", $inputArry["idRlist"]); //convert array of idRs to csv list
            $stmt = $conn->prepare('SELECT idR, parent, '.$fieldName.' FROM allRecords WHERE FIND_IN_SET(idR, ?)');
            $stmt->execute(array($csvIdRList));
            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {

				if ($strInsteadOfKey) { //direct string use without converting from key
		            $outputValue = $row[$fieldName]; //create output string
		        }
		        else { //first convert the string to the relevent key before writing to outputArry
		            $outputValue = aryValueOrZeroStr($itemAry, $row[$fieldName]); //recovers the string from the item array - if the key is not found in the array (most likely because it is 0) return ""
		        }

                $parent = "No"; //in this section check if a row is a parent and set flag to yes if it is
                if($row["idR"] == $row["parent"]) {
                    $parent = "Yes";
                }

                $outputArry["stickyItemsUpdatedObjects"][$row["idR"]] = $outputValue;
                $outputArry["stickyItemsUpdatedParentFlagObjects"][$row["idR"]] = $parent;
            }
        } catch(PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
            }
        $outputArry["PHPwriteReadAllRecordsItemHasRun"] = TRUE; //flag that indicates this PHP function has run and that the receiving JS function should run to handle the returned data
    }
    return $outputArry;
}

?>


