<?php

//$thisFileName = "funcsForAccCcc.php";

function noKidsFilter() {
    return "(
                (:recStartDate <= recordDate) AND (recordDate <= :recEndDate)
            ) 
            AND 
            (
                (parent = 0) OR (parent = idR)
            )";
}


function allFilter() {
    return  "(
                ((parent = 0) AND (:recStartDate <= recordDate) AND (recordDate <= :recEndDate)) 
            OR 
                ((0 < parent) AND (parentDate != '2000-01-01') AND (:recStartDate <= parentDate) AND (parentDate <= :recEndDate))
            OR
                ((0 < parent) AND (parentDate = '2000-01-01') AND (:recStartDate <= recordDate) AND (recordDate <= :recEndDate))
            )"; 

}

// Use the pivot table clicked cell id (e.g. row,col "251-piv-45") and the pivot table row and head names (e.g. "transCatgry-budget") to generate a filter array
// (e.g. array ([transCatgry] => 16,  [budget] => 15)  ) based on pivot table click rules defined in this function. $_fieldNameAry is also passed as it is used to generate the ids of the filtered columns
// from the pivot table row and headings names. (this is quite a hard concept to explain as the words used - and by derivation the variable names - to describe the different names used in the 
// standard display and the pivot table are subject to overlap and confusion!)
function getFiltersAryFromPivotCell($rowFiltId, $colFiltId, $rowAndHeadNames, $pivotCellEmpty, $pivotButMatchedBudgetsIsSet, $moneyDisplay) {

    $rowFiltIdIsNum = is_numeric($rowFiltId); //set to TRUE if $rowFiltId is a number (e,g, 251) but FALSE if it is a string (e.g. "rowName")
    $colFiltIdIsNum = is_numeric($colFiltId); //set to TRUE if $colFiltId is a number (e,g, 45) but FALSE if it is a string (e.g. "credit")

    $rowAndHeadNamesSplit = explode("-", $rowAndHeadNames); //split - as in "transCatgry-budget" becomes $rowFieldName = "transCatgry", $colFieldName = "budget"
    $rowFieldName = $rowAndHeadNamesSplit[0];
    $colFieldName = $rowAndHeadNamesSplit[1];

    

    if (!$colFiltIdIsNum && !$rowFiltIdIsNum) { //header section, 6 rows in either far LH column or far RH column
        if ($rowFiltId == "brtfwd") {           //header section, brought fwd row name - show all brought fwd values
            $filtersAry["include"] =   [];
            $moneyDisplay->setPaidinOnly();
        }
        elseif ($rowFiltId == "credit") {           //header section, credit row name - show all credits (receipts)
            $filtersAry["include"] =   [];
            $moneyDisplay->setPaidinOnly();
        }
        elseif ($rowFiltId == "spend") {            //header section, spend row name - show all spends (payments)
            $filtersAry["include"] =   [];
            $moneyDisplay->setWithdrawnOnly();
        }
        else {
            $filtersAry["include"] =   [];
        }
    }
    else {                                      //in an area that has ids of some sort
        if ($colFiltId == "rowName") {              //main display area, far LH rowName column - ids from the column in the standard display that became rows in the pivot display
            $filtersAry["include"] =   [$rowFieldName => $rowFiltId]; //filter for only transactions for that rowname (e.g. 'Van Crew')
        }
        elseif ($colFiltId == "rowTotal") {         //main display area, far RH totals column - ids from the column in the standard display that became rows in the pivot display
            $filtersAry["include"] =   [$rowFieldName => $rowFiltId];
        }
        elseif ($rowFiltId == "heading") {          //header section, heading row (but not far LH or RH) - ids from the column in the standard display that became columns in the pivot display
            $filtersAry["include"] =   [$colFieldName => $colFiltId]; //filter for only transactions for that colName (e.g. 'FiSCAF Apr20')
        }
        elseif ($rowFiltId == "brtfwd") {           //header section, credit row (but not far LH or RH) - ids from the column in the standard display that became columns in the pivot display
            $filtersAry["include"] =   [$colFieldName => $colFiltId];
            $moneyDisplay->setPaidinOnly();
        }
        elseif ($rowFiltId == "credit") {           //header section, credit row (but not far LH or RH) - ids from the column in the standard display that became columns in the pivot display
            $filtersAry["include"] =   [$colFieldName => $colFiltId];
            $moneyDisplay->setPaidinOnly();
        }
        elseif ($rowFiltId == "spend") {            //header section, spend row (but not far LH or RH) - ids from the column in the standard display that became columns in the pivot display
            $filtersAry["include"] =   [$colFieldName => $colFiltId];
            $moneyDisplay->setWithdrawnOnly();
        }
        elseif ($rowFiltId == "surplus") {          //header section, surplus row (but not far LH or RH) - ids from the column in the standard display that became columns in the pivot display
            $filtersAry["include"] =   [];
        }
        elseif ($rowFiltId == "bal") {              //header section, bal row (but not far LH or RH) - ids from the column in the standard display that became columns in the pivot display
            $filtersAry["include"] =   [];
        }
        elseif ($rowFiltId == "spacer") {           //header section, spacer row (but not far LH or RH) - ids from the column in the standard display that became columns in the pivot display
            $filtersAry["include"] =   [];
        }
        else {                                      //main display area - ids from the two columns in the standard display that became rows and columns in the pivot display
            if ($pivotButMatchedBudgetsIsSet) { //called from pivot display that shows date matched prospective budgets by colouring cells
                $filtersAry["include"] =   [$rowFieldName => $rowFiltId]; //includes only transactions matching the rowName
                $filtersAry["exclude"] =   [$colFieldName => $colFiltId]; //excludes the budget from the column that has been clicked
            }
            elseif ($pivotCellEmpty) { //if pivot cell is empty substitute unallocated column for selected one 
                $filtersAry["include"] =   [$rowFieldName => $rowFiltId, $colFieldName => 0]; //filter for only transactions matching the rowName, and colName = unallocated (0) in the pivot table
            }
            else {
                $filtersAry["include"] =   [$rowFieldName => $rowFiltId, $colFieldName => $colFiltId]; //filter for only transactions matching the rowName and colName in the pivot table
            }
        }
    }

    //pr($filtersAry);
    return $filtersAry;
}


/* Returns an array of record rows and sorted so any compound rows are grouped together inserted in the correct date position, with the Master first in its original position followed by any slaves (which will be in idR order). Any compound rows with the same compound number should already all have the same date from when they were set as compound in the allRecords table, because this was forced by the PHP function setCompoundTrans(). An extra field, "compoundType", is added to each subarry to indicate the kind of compound row - Master, Slave or FinalSlave. (SHOULD ALSO CONSIDER INCORPORATING THIS FORCING ACTION TO KEEP DATES IN SYNC IN OTHER PHP FUNCTIONS THAT ATTEMPT TO CHANGE THEM !!) */
function sortCompoundRows($recordsDataArry) {
	$masterCompoundAry = [];
	$compoundDoneFlagAry = [];
	$slaveCompoundAry = [];
	$outputAry = [];
	foreach ($recordsDataArry as $row) { //loop through all record rows one at a time
		if ($row["compound"] == $row["idR"]) { //row is a master compound one because compuond number same as idR
			$masterCompoundAry[] = $row; //concatonate compound master to $masterCompoundAry
		}
		else if ((0 < $row["compound"]) && (($row["compound"] != $row["idR"]))) { //row is a slave compound one because compound number not 0 and not same as idR
			$slaveCompoundAry[] = $row; //concatonate compound slave to $slaveCompoundAry
		}
	}
    $slaveSortedAry = sortTwoDimAryForTwoSubArys($slaveCompoundAry, "compound", "idR"); //sort $slaveCompoundAry into first compound then idR order
    //$mergedCompoundary = mergeAlternate($masterCompoundAry, $slaveCompoundAry, "compound");
    foreach ($recordsDataArry as $recordsDataRow) {
    	if (0 < $recordsDataRow["compound"]) { //this is a compound row (could be either master or slave)
	    	if (!array_key_exists($recordsDataRow["compound"], $compoundDoneFlagAry)) { //if this compound number set hasn't already been added to $outputAry
	    		foreach ($masterCompoundAry as $masterCompoundrow) {
	    			if ($masterCompoundrow["compound"] == $recordsDataRow["compound"]) { //if the current iteration of $masterCompoundrow has same compound number as the current row in $recordsDataRow
	    				$masterCompoundrow["compoundType"] = "Master"; //add new compound type key/value to subarray
	    				$outputAry[] = $masterCompoundrow;
	    			}
	    		}

	    		foreach ($slaveSortedAry as $slaveCompoundIdx => $slaveCompoundrow) {
	    			if ($slaveCompoundrow["compound"] == $recordsDataRow["compound"]) { //if the current iteration of $masterCompoundrow has same compound number as the current row in $recordsDataRow
	    				if (($slaveCompoundIdx < (count($slaveSortedAry) - 1)) && ($slaveSortedAry[$slaveCompoundIdx + 1]["compound"] == $recordsDataRow["compound"])) { //if $slaveCompoundIdx is at least 1 below the index of the last row and the  next row is same compound number
	    					$slaveCompoundrow["compoundType"] = "Slave"; //add new compound type key/Slave value to subarray
	    				}
	    				else {
	    					$slaveCompoundrow["compoundType"] = "FinalSlave"; //add new compound type key/FinalSlave value to subarray
	    				}
	    				$outputAry[] = $slaveCompoundrow;
	    			}
	    		}

	    		$compoundDoneFlagAry[$recordsDataRow["compound"]] = "AlreadyAdded"; //flag to inhibit adding compound rows that have already been added when a matching compound number is found later in 
	    	}
	    }
	    else { //this is a normal row
	    	$recordsDataRow["compoundType"] = "None";
	    	$outputAry[] = $recordsDataRow;
	    }
    }
	return $outputAry;
} 


/* Merges groups of subarrays from $masterAry and $slaveAry alternately into the returned array of subarrays in order of values pointed to by $subArykey. Subarrays from $masterAry with the first found key value are copied to the merged array first then subarrays with the same key value from $slaveAry. This happens alternately until all the subarrays in $masterAry and $slaveAry have been traversed. If slaves exist without a master they are added on their own */
function mergeAlternate($masterAry, $slaveAry, $subaryKey) {
    $mergeAry = [];
    $slaveAryIdx = 0;
    foreach ($masterAry as $masterRow) { 
        $mergeAry[] = $masterRow; //just add to mergeAry
        foreach ($slaveAry as $slaveRow) {
        	if ($masterRow[$subaryKey] == $slaveRow[$subaryKey]) { //if slave compound number same as master compound number
        		$mergeAry[] = $slaveRow; //add slave row
        	}
        }
	}
	return $mergeAry;
}


/* Converts passed date string, $date, (which is in the form "07-04-2020" or "2020-04-07") by removing the separator "-"s, and returning "07042020" or "20200407" which can be used directly for comparisons such as > < ==. */
function dateRemoveHyphs($date) {
	$dateAry = explode("-", $date);
	return $dateAry[0].$dateAry[1].$dateAry[2];
}


// EXPERIMENTAL GREEN ADDITIVE CLASS IS IN createStndDisplData() BELOW THE SQUARE:


//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################








/*
 * Where functions are used:

 * NEW FUNCTIONS FROM functions2.php
 * uploadJpgFilesToPdfs()
 * parseFileSizeForDisplay()

 */


// UPDATE THIS LIST OF USING FILES ABOVE, THE LAST FEW FUNCTIONS ARE NOT IN IT !!!


/* 


 */
function createPivotDisplData(
        $pivotRecsDataAry,
        $colClssAry,
        $pivotButMatchedBudgetsIsSet,
        $pivotCellClass,
        $pivotCellRowNameClass,
        $pivotCellRowNameRightClass,
        $columnForHeadings,
        $columnForRows,
        $colForBroughtFwdKey,
        $broughtFwdStr,
        $showBudgetsDateValidity //set to TRUE to highlight cells that contain sums of transactions that include expired budgets and also highlight budgets headers that are expired at current date, otherwise FALSE
    ) {
    global $tables;
//pr($pivotRecsDataAry);
    //settings that determine which columns are used for the pivot table (must match settings used for grouping in getPivotTableAry - $columnForHeadings then $columnForRows in group - MAY NOT MATTER!!)
    $spendColumnToSumKey = "amountWithdrawn";
    $creditColumnToSumKey = "amountPaidIn";

    $broughtFwdKey = $tables->getKey($colForBroughtFwdKey, $broughtFwdStr); //DOCUMENT CAVEAT THAT THIS KEY MUST BE PRESENT AND PRODUCED EVEN IF PERSON NAMES ARE BEING USED, OR ANY OTHER COMBINATIONS
    $dataExists = FALSE;

    $rowsAry = 			  [];
    $rowNamesAry = 		  []; //for holding initial row names derived by looping down the whole date range of the designated rows column and adding each new name as it's found, unsorted
    $compoundHiddenAry =  [];

    $headingNamesAry = 						[]; //for holding initial heading names derived by looping down the whole date range of the designated headings column and adding each new name as it's found, unsorted
    $headingsTotalBroughtFwdSumAry = 	[];
    $headingsTotalReceiptsSumAry = 		[];
    $headingsTotalPaymentsSumAry = 		[];
    $headingsTotalSurplusSumAry = 		[];
    $headingsCarriedFwdAry = 				[];
    $spacerRowAry = 					[]; 

    $headingsClassesAry = 					[];
    $headingsTotalBroughtFwdSumClassesAry = [];
    $headingsTotalReceiptsSumClassesAry = 	[];
    $headingsTotalPaymentsSumClassesAry = 		[];
    $headingsTotalSurplusSumClassesAry =	[];
    $headingsCarriedFwdClassesAry = 			[];
    $headingsSpacerClassesAry = 			[];

    $headingCellIdsAry = 					[];
    $headsTotalBroughtFwdSumCellIdsAry =	[];
    $headsTotalReceiptsSumCellIdsAry = 		[];
    $headsTotalPaymentsSumCellIdsAry = 		[];
    $headsTotalSurplusSumCellIdsAry =		[];
    $headsCarriedFwdCellIdsAry = 				[];
    $headsSpacerCellIdsAry = 				[];

    $headingsTotalBroughtFwdSum = 	0.00;
    $headingsTotalReceiptsSum = 		0.00;
    $headingsTotalPaymentsSum = 		0.00;

    foreach ($pivotRecsDataAry as $singleRecArry) { //ROW LOOP - through allRecords DATA, loop through all rows of supplied data from allRecords table creating array of heading names and an array of row names
        $headingVal =  $tables->getStrValue($columnForHeadings, $singleRecArry[$columnForHeadings]); //create a heading from the column selected for headings at the current row iteration
        if (!in_array($headingVal, $headingNamesAry)) { //if it's not in the array already, append it
        	if (substr($headingVal, 0, 8) != "Furlough") { //excludes any column with heading name that has "Furlough" as the the first 8 characters
	            $headingNamesAry[] = $headingVal; //add heading name to array
	            $dataExists = TRUE; 
	        }         
        }
        $rowsNameVal =  $tables->getStrValue($columnForRows, $singleRecArry[$columnForRows]); //create a row name from the column selected for row names at the  current row iteration
        if (!in_array($rowsNameVal, $rowNamesAry)) { //if it's not in the array already, append it
            $rowNamesAry[] = $rowsNameVal;
			$dataExists = TRUE;
        }        
    }

    if (!$dataExists) { //terminate things here, nothing to display!
    	$returnAry["headerAry"] = [];
    	$returnAry["rowsAry"] = [];
    	$returnAry["compoundHiddenAry"] = [];
    	return $returnAry;
    }

    $headingNamesAry = sortAryBySuffixDate($headingNamesAry);
    sort($rowNamesAry);
//pr($headingNamesAry);
//pr($rowNamesAry);

    if ($headingNamesAry[0] == "") { //if first budget is "" this means unallocated 
        $headingNamesAry[0] = "STILL TO ALLOCATE BUDGET!";
    }

    foreach ($headingNamesAry as $headingsIdx=>$headingText) { //COLUMN LOOP create headings section initialised rows that have the same number of positions as there are headings. To be populated with sums/classes/ids later

    	// 7 Lines
    	//$headingNamesAry has been populated in initial row loop above
    	$headingsTotalBroughtFwdSumAry[] = 	0;
        $headingsTotalReceiptsSumAry[] = 	0;
        $headingsTotalPaymentsSumAry[] = 	0;
        $headingsTotalSurplusSumAry[] = 	0;
        $headingsCarriedFwdAry[] = 			0;
        $spacerRowAry[] = 					"ZZZ";

        if (($headingsIdx == 0) || ($showBudgetsDateValidity && (checkBudgetDates(date("Y-m-d"), $headingText) == "Expired" ))) {
            $headingsClassesAry[] = $pivotCellClass." ".$colClssAry["budgetEndInPast"];
        }
        else {
            if ($showBudgetsDateValidity && (checkBudgetDates(date("2021-04-01"), $headingText) == "Expired" )) {
                $headingsClassesAry[] = $pivotCellClass." ".$colClssAry["budgetEndsMarch"];
            }
            else {
                $headingsClassesAry[] = $pivotCellClass." ".$colClssAry["budgetStillCurrent"];
            }
        }


        // 7 Lines
        //$headingsClassesAry[] = 					$pivotCellClass." ".$colClssAry["zeroValueGood"];
        $headingsTotalBroughtFwdSumClassesAry[] = 	$pivotCellClass;
        $headingsTotalReceiptsSumClassesAry[] = 	$pivotCellClass;
        $headingsTotalPaymentsSumClassesAry[] = 	$pivotCellClass;
        $headingsTotalSurplusSumClassesAry[] = 		$pivotCellClass;
        $headingsCarriedFwdClassesAry[] = 			$pivotCellClass;
        $headingsSpacerClassesAry[] = 				$pivotCellClass." ".$colClssAry["unselInvisCol"];

        $colHeadingTblIdx = $tables->getKey($columnForHeadings, $headingText);
        // 7 Lines
        $headingCellIdsAry[] = 					"heading-piv-".$colHeadingTblIdx; //create headings cell ids from column heading table index. Will facilitate click filtering
        $headsTotalBroughtFwdSumCellIdsAry[] = 	"brtfwd-piv-".$colHeadingTblIdx; 
        $headsTotalReceiptsSumCellIdsAry[] = 	"credit-piv-".$colHeadingTblIdx; 
        $headsTotalPaymentsSumCellIdsAry[] = 	"spend-piv-".$colHeadingTblIdx; 
        $headsTotalSurplusSumCellIdsAry[] = 	"surplus-piv-".$colHeadingTblIdx;
        $headsCarriedFwdCellIdsAry[] = 			"bal-piv-".$colHeadingTblIdx; 
        $headsSpacerCellIdsAry[] = 				"spacer-piv-".$colHeadingTblIdx; 
    }

    // 7 Lines
    array_unshift($headingCellIdsAry, 					"heading-piv-rowTotal"); //designates column with row totals, appearing to right of header row name column (so done is here, before row totals below)
    array_unshift($headsTotalBroughtFwdSumCellIdsAry, 	"brtfwd-piv-rowTotal");
    array_unshift($headsTotalReceiptsSumCellIdsAry, 	"credit-piv-rowTotal");
    array_unshift($headsTotalPaymentsSumCellIdsAry, 	"spend-piv-rowTotal");
    array_unshift($headsTotalSurplusSumCellIdsAry, 		"surplus-piv-rowTotal");
    array_unshift($headsCarriedFwdCellIdsAry, 			"bal-piv-rowTotal");
    array_unshift($headsSpacerCellIdsAry, 				"spacer-piv-rowTotal");

    // 7 Lines
    array_unshift($headingCellIdsAry, 					"heading-piv-rowName");  //designates column with header row names, inserted at far left of header rows
    array_unshift($headsTotalBroughtFwdSumCellIdsAry, 	"brtfwd-piv-rowName");
    array_unshift($headsTotalReceiptsSumCellIdsAry, 	"credit-piv-rowName");
    array_unshift($headsTotalPaymentsSumCellIdsAry, 	"spend-piv-rowName");
    array_unshift($headsTotalSurplusSumCellIdsAry, 		"surplus-piv-rowName");
    array_unshift($headsCarriedFwdCellIdsAry, 			"bal-piv-rowName");
    array_unshift($headsSpacerCellIdsAry, 				"spacer-piv-rowName");
    

    foreach ($rowNamesAry as $rowIdx => $pivotRowName) { // row names LOOP       goes through all the predetermined pivot table row names creating and populate 2 dimensional array with summed spend data
        //pr($pivotRowName."</br>");
        $compoundHiddenAry[$singleRecArry["idR"]] = FALSE;
    	$rowContainsSpendData = FALSE; //flag that will be set to true if any cells in the row being created contain anything other than 0
        $rowNameTableIdx = $tables->getKey($columnForRows, $pivotRowName); //gets table index of heading name. If name is "" (empty), 0 is returned in keeping with allRecords column data
        $rowTempAry = [$pivotRowName]; //create row name at index 0
        $rowsClassesTempAry = [];
        $rowCellIdsTempAry = [];
        $rowTableId = $tables->getKey($columnForRows, $pivotRowName);
        $rowSum = 0;


        foreach ($headingNamesAry as $headingIdx => $pivotHeadingName) { // heading names LOOP      goes through all the predetermined pivot table heading names summing all the spend data for the current pivot table row name 



            //############# SINGLE PIVOT TABLE CELL SECTION ################

        	$rowCellIdsTempAry[] = $rowTableId."-piv-".$tables->getKey($columnForHeadings, $pivotHeadingName); //create cell id from row name table index concatonated with "-piv-" and column heading table index. Will facilitate click filtering

            $cellContainsSpendData = FALSE;
            $budgetNotActiveYet = FALSE;
        	$budgetExpired = FALSE;
        	$budgetInDate = FALSE; //used to show indate transactions that are potentials for this budget - a budget may already have been allocated
            $colSum = 0;
            $headingTableIdx = $tables->getKey($columnForHeadings, $pivotHeadingName); //gets table index of heading name. If name is "" (empty), 0 is returned in keeping with allRecords column data



            foreach ($pivotRecsDataAry as $singleRecArry) { //############## allRecords LOOP  ###############   loops through all the selected rows of data from allRecords

                if (($singleRecArry[$columnForRows] == $rowNameTableIdx) && ($singleRecArry[$columnForHeadings] == $headingTableIdx)) { //this records row has matches for the pivot table heading/row names
                    
                    if ($singleRecArry[$spendColumnToSumKey] != 0) { //a value other than 0 exists and is being summed
                        $colSum = $singleRecArry[$spendColumnToSumKey] + $colSum; //add to the value
                    	$rowContainsSpendData = TRUE;
                        $cellContainsSpendData = TRUE;


                        if ($showBudgetsDateValidity && (checkBudgetDates($singleRecArry["maxRecordDate"], $pivotHeadingName) == "Expired")) {
                            $budgetExpired = TRUE;
                        }
                        if ($showBudgetsDateValidity && (checkBudgetDates($singleRecArry["minRecordDate"], $pivotHeadingName) == "NotYetActive")) {
                            $budgetNotActiveYet = TRUE;
                        }

                    }

                    
                    if ($singleRecArry[$colForBroughtFwdKey] == $broughtFwdKey) { //if allRecords cell in column for pivot 'row' display matches the $broughtFwd key, sum to "Brought Fwd" headings row instead
                    	$headingsTotalBroughtFwdSumAry[$headingIdx] =   fourThreeOrTwoDecimals($singleRecArry[$creditColumnToSumKey]    + $headingsTotalBroughtFwdSumAry[$headingIdx], TRUE);
                        $headingsTotalBroughtFwdSum =                   fourThreeOrTwoDecimals($singleRecArry[$creditColumnToSumKey]    + $headingsTotalBroughtFwdSum, TRUE);
                        
                    }
                    else { //not "Brought Fwd" category so sum as normal to "Receipts" row
                        $headingsTotalReceiptsSumAry[$headingIdx] =   fourThreeOrTwoDecimals($singleRecArry[$creditColumnToSumKey]    + $headingsTotalReceiptsSumAry[$headingIdx], TRUE);
                        $headingsTotalReceiptsSum =                   fourThreeOrTwoDecimals($singleRecArry[$creditColumnToSumKey]    + $headingsTotalReceiptsSum, TRUE);
                    }

                    $headingsTotalPaymentsSumAry[$headingIdx] = 	fourThreeOrTwoDecimals($singleRecArry[$spendColumnToSumKey] 	+ $headingsTotalPaymentsSumAry[$headingIdx], TRUE);
                    $headingsTotalPaymentsSum = 					fourThreeOrTwoDecimals($singleRecArry[$spendColumnToSumKey] 	+ $headingsTotalPaymentsSum, TRUE);
                }

                //if $pivotButMatchedBudgetsIsSet (by button) check if any transactions having the current pivot cell row name fall within the date range extracted from the pivot cell budget name
                if ($pivotButMatchedBudgetsIsSet && $showBudgetsDateValidity && ($singleRecArry[$columnForRows] == $rowNameTableIdx) && (checkBudgetDates($singleRecArry["recordDate"], $pivotHeadingName) == "InDate")) {
                    $budgetInDate = TRUE;
                }
                
            }


            

            $rowTempAry[] = fourThreeOrTwoDecimals($colSum); //append sum to array - format to two decimal places with single leading zero for amounts < £1.00
            $rowSum = $colSum + $rowSum;

            if (!$pivotButMatchedBudgetsIsSet && $showBudgetsDateValidity && $budgetNotActiveYet && $budgetExpired) {
            	$rowsClassesTempAry[] = $pivotCellClass." ".$colClssAry["budgetBothExprdAndNyActv"]; //append a warning colour class as budget used in some transaction dates both expired or not yet active
            }
            else if (!$pivotButMatchedBudgetsIsSet && $showBudgetsDateValidity && $budgetNotActiveYet) {
                $rowsClassesTempAry[] = $pivotCellClass." ".$colClssAry["budgetNotYetActive"]; //append a warning colour class as budget used in some transaction dates represesnted by this cell have expired
            }
            else if (!$pivotButMatchedBudgetsIsSet && $showBudgetsDateValidity && $budgetExpired) {
                $rowsClassesTempAry[] = $pivotCellClass." ".$colClssAry["budgetExpired"]; //append a warning colour class as budget used in some transaction dates represesnted by this cell have expired
            }
            else if (!$pivotButMatchedBudgetsIsSet && $cellContainsSpendData) {
            	$rowsClassesTempAry[] = $pivotCellClass; //append a new cell class of pivot cell class
            }
            else if ($budgetInDate) { //transactions with the current pivot cell row name that are within the date range of the budget of the current pivot cell have been found, so colour the pivot cell
                $rowsClassesTempAry[] = $pivotCellClass." ".$colClssAry["budgetStillCurrent"];
            }
            else {
                $rowsClassesTempAry[] = $pivotCellClass; //append a new cell class of pivot cell class
            }


            

            //############# SINGLE PIVOT TABLE CELL SECTION - END ################



        }

        //DO ALL THE FOLLOWING FOR EACH ROW OF THE PIVOT TABLE
        if ($rowContainsSpendData) { //if the row has any spend data (i.e. only create row in array if it has something to display and isn't empty!)
	        $rowSumDecimalised = fourThreeOrTwoDecimals($rowSum, TRUE); 
	        $rowTempAry[] = array_splice($rowTempAry, 1, 0, $rowSumDecimalised); //insert total to right of row name
	        $rowsAry[$rowIdx]["displayRowsAry"] = $rowTempAry;  //append newly populated row to $rowsAry
	        
	        
	        $rowTotalClass = $pivotCellClass; //create default standard cell class for last (totals) column at end of row of classes
	        if ($rowSumDecimalised == 0) {
	        	$rowTotalClass = $pivotCellOrangeClass; //change class to orange for zero value (means no value assigned to row name)
	        }

	        array_unshift($rowsClassesTempAry, $rowTotalClass); //insert modified cell class for totals column to the right of the row name class
	        array_unshift($rowsClassesTempAry, $pivotCellRowNameClass); //insert cell class for first (row names) column at beginning of row of classes - left justified

	        $rowsAry[$rowIdx]["displayRowsClassesAry"] = $rowsClassesTempAry; //append row classes 
	        
	        array_unshift($rowCellIdsTempAry, $rowTableId."-piv-rowTotal"); //use "rowTotal" as the last part of the id to designate column of row totals
	        array_unshift($rowCellIdsTempAry, $rowTableId."-piv-rowName");  //use "rowName" as the last part of the id to designate column with row names
	        $rowsAry[$rowIdx]["displayRowIdsAry"] = $rowCellIdsTempAry; //append standard row classes to show totals column
	    }


    }



    // 7 Lines
    array_unshift($headingNamesAry, "Totals"); //add to right of header names column to name totals column
    array_unshift($headingsTotalBroughtFwdSumAry, fourThreeOrTwoDecimals($headingsTotalBroughtFwdSum, TRUE)); //add credit totals column to right of header row names (LH) column
    array_unshift($headingsTotalReceiptsSumAry, fourThreeOrTwoDecimals($headingsTotalReceiptsSum, TRUE)); // "   "
    array_unshift($headingsTotalPaymentsSumAry, fourThreeOrTwoDecimals($headingsTotalPaymentsSum, TRUE)); //  "   "
    //$headingsTotalSurplusSumAry calculated and populated in column loop below
    //$headingsCarriedFwdAry calculated and populated in column loop below
    array_unshift($spacerRowAry, ""); //add additional rightmost cell to spacer column so it has the same number as all others


    // 7 Lines
    array_unshift($headingsClassesAry, 						$pivotCellClass); 	//insert cell class for totals column to right of header names classes
    array_unshift($headingsTotalBroughtFwdSumClassesAry, 	$pivotCellClass);
    array_unshift($headingsTotalReceiptsSumClassesAry,		$pivotCellClass);
    array_unshift($headingsTotalPaymentsSumClassesAry, 		$pivotCellClass);
    array_unshift($headingsTotalSurplusSumClassesAry,		$pivotCellClass);
    array_unshift($headingsCarriedFwdClassesAry, 			$pivotCellClass);
    array_unshift($headingsSpacerClassesAry,         		$pivotCellClass);


    foreach ($headingsTotalReceiptsSumAry as $sumsIdx => $headingsTotalCredSum) { //COLUMN LOOP run loop to do subtraction on each column total and create balance array (also class choosing and formatting)
        $headingsCarriedFwdAry[$sumsIdx] = fourThreeOrTwoDecimals($headingsTotalBroughtFwdSumAry[$sumsIdx] + $headingsTotalCredSum - $headingsTotalPaymentsSumAry[$sumsIdx], TRUE); //bal from brought fwd + credit - spend
        $headingsTotalSurplusSumAry[$sumsIdx] = fourThreeOrTwoDecimals($headingsTotalCredSum - $headingsTotalPaymentsSumAry[$sumsIdx], TRUE); //bal from credit - spend

        //this section conditionally sets the colour and converts 0 to "-" in some cases according to the values in various cells of the brought fwd and credit (receipts) row
        if ($headingsTotalBroughtFwdSumAry[$sumsIdx] == 0) { //Brought Fwd is 0
        	$headingsTotalBroughtFwdSumAry[$sumsIdx] = "-"; //make Brought Fwd invisible because no value
        	if ($headingsTotalReceiptsSumAry[$sumsIdx] < 0) {
	        	$headingsTotalReceiptsSumClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["negativeValue"]; //set Receipts class to red for -ve value
	        }
	        if ($headingsTotalReceiptsSumAry[$sumsIdx] == 0) {
	        	$headingsTotalReceiptsSumClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["zeroValueBad"]; //set Receipts class to orange for -zero value
	        	$headingsTotalReceiptsSumAry[$sumsIdx] = "-"; //make Receipts invisible because no value
	        	$headingsTotalBroughtFwdSumClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["zeroValueBad"]; //set Brought Fwd class to orange because both it and Receipts are 0
	        }
        }
        else {
        	if ($headingsTotalBroughtFwdSumAry[$sumsIdx] < 0) { //Brought Fwd is -ve
        		$headingsTotalBroughtFwdSumClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["negativeValue"]; //set Brought Fwd class to red for -ve value
        		if ($headingsTotalReceiptsSumAry[$sumsIdx] < 0) {
		        	$headingsTotalReceiptsSumClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["negativeValue"]; //set Receipts class to red for -ve value
		        }
		        if ($headingsTotalReceiptsSumAry[$sumsIdx] == 0) {
		        	$headingsTotalReceiptsSumClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["zeroValueBad"]; //set Receipts class to orange for -zero value
		        }
        	}
        	else {
        		if ($headingsTotalReceiptsSumAry[$sumsIdx] < 0) {
		        	$headingsTotalReceiptsSumClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["negativeValue"]; //set Receipts class to red for -ve value
		        }
		        if ($headingsTotalReceiptsSumAry[$sumsIdx] == 0) {
		        	$headingsTotalReceiptsSumAry[$sumsIdx] = "-"; //make Receipts invisible because no value
		        }
        	}

        }

        //this section conditionally sets the color of the balance row cells according to whether they're 0, +ve or -ve
        if ($headingsCarriedFwdAry[$sumsIdx] < 0) {
        	$headingsCarriedFwdClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["negativeValue"]; //set class to red for -ve value
        }
        if ($headingsCarriedFwdAry[$sumsIdx] == 0) {
        	$headingsCarriedFwdClassesAry[$sumsIdx] = $pivotCellClass." ".$colClssAry["zeroValueGood"]; //set class to green for zero carried forward value (which is normally good)
        }
    }

    // 7 Lines
    array_unshift($headingNamesAry, 							$columnForHeadings); //insert headings title at beginning of headingNamesAry 
    array_unshift($headingsTotalBroughtFwdSumAry, 			"Brought Fwd"); 	 //insert "Brought Fwd" at beginning of headingsTotalBroughtFwdSumAry to move totals over to the right and align things
    array_unshift($headingsTotalReceiptsSumAry, 			"Receipts"); 		 // "   "
    array_unshift($headingsTotalPaymentsSumAry, 			"Payments"); 		 // "   "
    array_unshift($headingsTotalSurplusSumAry, 				"Surplus"); 	 	 // "   "
    array_unshift($headingsCarriedFwdAry, 					"Carried Fwd"); 	 // "   "
    array_unshift($spacerRowAry, 							$columnForRows); 	 // "   "

    // 7 Lines
    array_unshift($headingsClassesAry, 						$pivotCellRowNameRightClass); //insert at beginning class for header names - right justified
    array_unshift($headingsTotalBroughtFwdSumClassesAry, 	$pivotCellRowNameRightClass);
    array_unshift($headingsTotalReceiptsSumClassesAry, 		$pivotCellRowNameRightClass);
    array_unshift($headingsTotalPaymentsSumClassesAry, 		$pivotCellRowNameRightClass);
    array_unshift($headingsTotalSurplusSumClassesAry, 		$pivotCellRowNameRightClass);
    array_unshift($headingsCarriedFwdClassesAry, 			$pivotCellRowNameRightClass);
    array_unshift($headingsSpacerClassesAry, 				$pivotCellRowNameClass); //insert at beginning class for column names title - left justified


    $returnAry["rowAndHeadNames"] = $columnForRows."-".$columnForHeadings;
    
    //add headings, credit, spend, balance and spacer with classes arrays and cellIds arrays - append each to $returnAry["headerAry"]
    // 7 Lines
    $returnAry["headerAry"][] = ["headerRowsAry"=> $headingNamesAry,                 "headerRowsClassesAry"=>$headingsClassesAry, 				    "headerCellIdsAry"=>$headingCellIdsAry];
    $returnAry["headerAry"][] = ["headerRowsAry"=> $headingsTotalBroughtFwdSumAry,   "headerRowsClassesAry"=>$headingsTotalBroughtFwdSumClassesAry,	"headerCellIdsAry"=>$headsTotalBroughtFwdSumCellIdsAry];
    $returnAry["headerAry"][] = ["headerRowsAry"=> $headingsTotalReceiptsSumAry,     "headerRowsClassesAry"=>$headingsTotalReceiptsSumClassesAry,	"headerCellIdsAry"=>$headsTotalReceiptsSumCellIdsAry];
    $returnAry["headerAry"][] = ["headerRowsAry"=> $headingsTotalPaymentsSumAry,     "headerRowsClassesAry"=>$headingsTotalPaymentsSumClassesAry, 	"headerCellIdsAry"=>$headsTotalPaymentsSumCellIdsAry];
    $returnAry["headerAry"][] = ["headerRowsAry"=> $headingsTotalSurplusSumAry,      "headerRowsClassesAry"=>$headingsTotalSurplusSumClassesAry, 	"headerCellIdsAry"=>$headsTotalSurplusSumCellIdsAry];
    $returnAry["headerAry"][] = ["headerRowsAry"=> $headingsCarriedFwdAry,           "headerRowsClassesAry"=>$headingsCarriedFwdClassesAry, 		"headerCellIdsAry"=>$headsCarriedFwdCellIdsAry];
    $returnAry["headerAry"][] = ["headerRowsAry"=> $spacerRowAry,                    "headerRowsClassesAry"=>$headingsSpacerClassesAry, 			"headerCellIdsAry"=>$headsSpacerCellIdsAry]; 

    $returnAry["rowsAry"] = $rowsAry; //add row data
    $returnAry["compoundHiddenAry"] = $compoundHiddenAry; //all positions default to "false" designating row is not hidden - needed to fool flex/none display attribute in php display section into always flex

    return $returnAry;
}



/* ##########################          ##############          ##############          ##############          #############################
   ##########################          ##############          ##############          ##############          #############################
   ##########################          ##############          ##############          ##############          #############################
   ##########################          ##############          ##############          ##############          #############################
   ##########################          ##############          ##############          ##############          #############################
   ##########################          ##############          ##############          ##############          ############################# */



/* Takes an array in the form [0=>$startDate, 1=>$endDate] and $budgetName in the form "VAF 7Mar21 25Jun21", and modifies $startDate by making it later if the decoded first budget date (7Mar20) is later but never makes it earlier. Similarly if the second decoded budget date (25Jun20) is earlier than $endDate then $endDate is made earlier but it is never made later. If only the one budget date exists it is considered to be the budget end date and only $enDate is modified, in the same manner as previously explained. If the day of month portion of the budget date(s) is not used the budget start date will default to the first day of the month and the budget end date will default to the last day of the month. */
function restrictDates($startAndEndDateAry, $budgetName) {
    $budgetDateLastYYMMDD = getDateSuffix($budgetName, TRUE, TRUE); //get date in YYMMDD format from the last group, last D.O.M. if no D.O.M. prefix - defaults to "NoDate" if doesn't decode to date
     if (subStr($budgetDateLastYYMMDD, -6) == "NoDate") { //last goup doesn't exist therefore there are no dates in budget name
        return $startAndEndDateAry; //no dates in budget so leave date array alone
     }
    $budgetDateSecLastYYMMDD = getDateSuffix($budgetName, FALSE, FALSE); //get date in YYMMDD format from the 2nd last group, 1st D.O.M. if no D.O.M. prefix - defaults to "NoDate" if doesn't decode to date
    if (subStr($budgetDateSecLastYYMMDD, -6) == "NoDate") { //single date (using last group only)
        $budgetDateLastYYMMDDsetFirstDOM = getDateSuffix($budgetName, FALSE, TRUE); //get date in YYMMDD format from the last group, 1st D.O.M. if no D.O.M. prefix - cannot be "NoDate" (would be picked up earlier)
        $startAndEndDateAry[0] = convertShortDateToYYYYMMDD($budgetDateLastYYMMDDsetFirstDOM);
        $startAndEndDateAry[1] = convertShortDateToYYYYMMDD($budgetDateLastYYMMDD);
        return $startAndEndDateAry;
    }
    else { //both start and end dates exist
        $startAndEndDateAry[0] = convertShortDateToYYYYMMDD($budgetDateSecLastYYMMDD);
        $startAndEndDateAry[1] = convertShortDateToYYYYMMDD($budgetDateLastYYMMDD);
        return $startAndEndDateAry;
    }
}



/* Attempts to extract a budget end date from the last group of characters of the budget name, and budget start date from the 2nd last group of characters of the budget name (e.g. a budget name as in "FiSCAF 06Apr21 05Mar22". In each case if the extracted date has a day of month suffix (and isn't simply a month and year - "Apr21") this D.O.M. prefix is used in the creation of the extracted date. If either group contains no prefix then in the case of the last group (end date) the created date defaults to the last day of the month and in the case of the 2nd last group (start date) the created date defaults to the 1st day of the month. If no dates groups can be detected then "NoDatesInBudget" is returned, otherwise comparisons are them made with $recordDate using either both start and end date if they are both available, or just the end date (last group) if that is all that is available. If just the last group (single) date is available it is used as a start date too with either it's D.O.M. prefix (in which case it is a single day budget) or, in the absence of the D.O.M. prefix, the first day of the month "01" is used. The comparison process yields one of three return results: "Expired", "NotYetActive", or "InDate" to indicate the date(s) of the budget in relation to the transaction date. A CORRESPONDING JAVASCRIPT FUNCTION EXISTS. */
function checkBudgetDates($recordDate, $budgetName) {
    $transDateYYMMDD = convertDateToYYMMDD($recordDate); //transaction date as YYMMDD
    $budgetDateLastYYMMDD = getDateSuffix($budgetName, TRUE, TRUE); //get date in YYMMDD format from the last group, last D.O.M. if no D.O.M. prefix - defaults to "NoDate" if doesn't decode to date
     if (subStr($budgetDateLastYYMMDD, -6) == "NoDate") {  //last goup doesn't exist therefore there are no dates in budget name
     	return "InDate";  //"NoDatesInBudget";
     }
    $budgetDateSecLastYYMMDD = getDateSuffix($budgetName, FALSE, FALSE); //get date in YYMMDD format from the 2nd last group, 1st D.O.M. if no D.O.M. prefix - defaults to "NoDate" if doesn't decode to date
    if (subStr($budgetDateSecLastYYMMDD, -6) == "NoDate") { //single date (using last group only)
	   $budgetDateLastYYMMDDsetFirstDOM = getDateSuffix($budgetName, FALSE, TRUE); //get date in YYMMDD format from the last group, 1st D.O.M. if no D.O.M. prefix - cannot be "NoDate" (would be picked up earlier) 

    	if ($budgetDateLastYYMMDD < $transDateYYMMDD) { //budget date is earlier than the transaction date
	        return "Expired";
	    }
	    elseif ($transDateYYMMDD < $budgetDateLastYYMMDDsetFirstDOM) { //budget date is later than the transaction date
	        return "NotYetActive";
	    }
	    else {
	        return "InDate";
	    }
    }
    else { //both dates (using both groups)
	    if ($budgetDateLastYYMMDD < $transDateYYMMDD) { //budget date is earlier than the transaction date
	        return "Expired";
	    }
	    elseif ($transDateYYMMDD < $budgetDateSecLastYYMMDD) { //budget date is later than the transaction date
	        return "NotYetActive";
	    }
	    else {
	        return "InDate";
	    }
	}
}


/* Takes the passed $date string in format "2021-02-09" and converts it to YYMMDD format "210209".  */
function convertDateToYYMMDD($date) {
    return substr($date, 2, 2).substr($date, 5, 2).substr($date, 8, 2); //concatonate extracted two digit year substring, two digit month substring and two digit day of month substring
}


/* Takes the passed $date string in YYMMDD format "210209" format  and converts it to YYYY-MM-DD format "2021-02-09". All dates are assumed to be in year range 2000 to 2099.  */
function convertShortDateToYYYYMMDD($date) {
    return "20".substr($date, 0, 2)."-".substr($date, 2, 2)."-".substr($date, 4, 2); //concatonate two digit year substring, two digit month substring and two digit day of month substring to "20" and arranges "-" between each number group
}


/* If $setForLastDayOfMonth is set to TRUE this function extracts the last group (if $setForLastGroup is TRUE) or 2nd last group (if $setForLastGroup is FALSE) of characters from $value. The extracted group should be an abreviated month-year date string in the form "7Feb20", "15Feb20" or "Feb20") and, if it can be interpreted as a date, it is decoded to a number, reversed, in the form "200207", "200215" or in the case where no day of month suffix is included it sets the day of month output to the last day e.g. "200228" (taking into account that for leap years Feb's last day will be 29). This allows proper sorting using a simple sort algorithm or comparisons with other dates similarly formatted. If any extracted group of characters in the passed $value doesn't properly decode to a date the original $value with "-NoDate" (the preceding hyphen ensures that in sorting routines with SORT_NATURAL a $value of "" will come first before any numbers) concatonated onto it is returned. If $setForLastDayOfMonth is set to FALSE it works in a similar manner when there is a provided day of month suffix, but where there are none the day of month output is now set to "01". NOTE: only works with 2 character year designator and assumes every date is in the century 2000. Months designators must all be 3 character with leading Capital i.e. Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, Oct, Nov, Dec. A CORRESPONDING JAVASCRIPT FUNCTION EXISTS.  */
function getDateSuffix($value, $setForLastDayOfMonth, $setForLastGroup) {
    $groupsAry = explode(" ", trim($value)); //create an array from the groups of characters separated by a space in $value string
    if ($setForLastGroup) { //set group to be extracted according to $setForLastGroup being TRUE (last group) or FALSE (2nd last group)
        $group = $groupsAry[count($groupsAry) -1]; //select last group (string separated by spaces)
    }
    else {
        $group = $groupsAry[count($groupsAry) -2]; //select 2nd last group (string separated by spaces)
    }
    $lastFiveChars = substr($group, -5); //get the last 5 characters of the value which may be a date code e.g. "Feb20"
    $potentialDayOfMonthChars = substr($group, 0, (strlen($group) -5)); //get first, day of month, part of $group if it exists - should be 1 or 2 characters (or 0 characters if it doesn't exist)
    $monthThreeCharName = substr($lastFiveChars, 0, 3); //extract what is potentially the three character month name e.g. 'Feb'
    $yearTwoDigitNum = substr($lastFiveChars, 3);  //extract what is potentially the two character year number e.g. "20"
    if (((strlen($potentialDayOfMonthChars) == 1) || (strlen($potentialDayOfMonthChars) == 2)) && is_numeric($potentialDayOfMonthChars)) { //an actual days of month string so use this in returned date
        if (strlen($potentialDayOfMonthChars) == 1) {
            $dayOfMonthChars = "0".$potentialDayOfMonthChars;
        }
        else {
            $dayOfMonthChars = $potentialDayOfMonthChars;
        }
        $dayInFeb = $dayOfMonthChars;
        $dayInLongMonth = $dayOfMonthChars;
        $dayInShortMonth = $dayOfMonthChars;
    }
    elseif ($setForLastDayOfMonth) { //no days of month string found and End of month requested so use last day of month
        $dayInFeb = cal_days_in_month(CAL_GREGORIAN, 2, "20".$yearTwoDigitNum);
        $dayInLongMonth = "31";
        $dayInShortMonth = "30";
    }
    else { //no days of month string found but no End of month requested so use first day of month
        $dayInFeb = "01";
        $dayInLongMonth = "01";
        $dayInShortMonth = "01";
    }
        if (is_numeric($yearTwoDigitNum)) { //check that the two char year number actually is a number as a partial validation of the five characters being a date code
            switch ($monthThreeCharName) { //do a switch-case iteration to see if the three characters are month abreviation and if so convert to numeric equivalent
                case "Jan":
                    return $yearTwoDigitNum."01".$dayInLongMonth; //return the concatonated revesed date in numeric form e.g. "20"."01"
                    break;
                case "Feb":
                    return $yearTwoDigitNum."02".$dayInFeb;
                    break;
                case "Mar":
                    return $yearTwoDigitNum."03".$dayInLongMonth;
                    break;
                case "Apr":
                    return $yearTwoDigitNum."04".$dayInShortMonth;
                    break;
                case "May":
                    return $yearTwoDigitNum."05".$dayInLongMonth;
                    break;
                case "Jun":
                    return $yearTwoDigitNum."06".$dayInShortMonth;
                    break;
                case "Jul":
                    return $yearTwoDigitNum."07".$dayInLongMonth;
                    break;
                case "Aug":
                    return $yearTwoDigitNum."08".$dayInLongMonth;
                    break;
                case "Sep":
                    return $yearTwoDigitNum."09".$dayInShortMonth;
                    break;
                case "Oct":
                    return $yearTwoDigitNum."10".$dayInLongMonth;
                    break;
                case "Nov":
                    return $yearTwoDigitNum."11".$dayInShortMonth;
                    break;
                case "Dec":
                    return $yearTwoDigitNum."12".$dayInLongMonth;
                    break;
                default: //if the three char month name turns out not to be a month then return value
                    return $value."-NoDate";
            }
        }
        else { //last two characters is not a number so just return $value
            return $value."-NoDate";
        }
}


/* Does in place sort - alphabetical done first - of pivot headings array by date suffix (which could be the end date or only date).  */
function sortAryBySuffixDate($array) {
    $dateKeysAry = []; //temporary array that will have keys of the decoded date suffix or the original value (where date suffix doesn't exist)
    $arrayLength = count($array); //get the number of items in the array which equates to the maximum index (+1)
    $indexMaxCharsNeeded = strlen(strval($arrayLength - 1)); //get the number of string characters that would be needed to represent the maximum possible index for $array
    sort($array); //do initial sort to get in value order (sort of to force a 'sort in place' action)
    foreach ($array as $index=>$value) {
        $dateKeysAry[getDateSuffix($value, TRUE, TRUE).str_pad($index, $indexMaxCharsNeeded, "0", STR_PAD_LEFT)] = $value; //create a new array entry of $value with key set to decoded date suffix or the original value with " No Date" concatonated onto it (when date suffix doesn't exist). Left padded $index is appended to the key so two keys that are the same - which is highly likely - will be made different and will sort in place
    }
    ksort($dateKeysAry, SORT_NATURAL);
    return array_values($dateKeysAry);
}



/* Uses data from allRecords transfered via $recordsDataArry to create an ordinary indexed array to be passed to the display routine that uses divs:


Ideal object steered operations:


$control = [
"index" =>   [ 0,            1,          2,          3,          4,          5,          6,          7,              8,              9,          10,         11,         12,     13      ] 
"colName" => ["TransDate",  "PersOrg",  "TransCat", "MoneyOut", "MoneyIn",  "Account",  "Budget",   "Reference",    "RcnclDate",    "Umbrella", "Umbrella", "DocType",  "Note", "Family" ] 



]



$classNest =    {
                "TransDate":                                            //column
                            {
                            "checkRowStatus"                                //func name - func written to work on specific argument types
                                                    {
                                                    "clear":"displayCellStd",       //cell display status : class to use
                                                    "lineSel":"displayCellRowSel",  //"
                                                    "celSel":"displayCellSnglSel"   //"
                                                    },
                            }
                "RcnclDate" {                                           //column
                            "dateLess":             {                       //func name - func written to work on specific argument types
                                                    "RcnclDate"                     //cell in same row
                                                    "TransDate"                     //"
                                                    "displayCellRcnclEarly"         //class to use
                                                    },
                            "dateBeyondMnthEnd":    {                       //func name
                                                    "monthLastDay"                  //pointer to passed value
                                                    "RcnclDate"                     //cell in same row
                                                    "displayCellRcnclNot"           //class to use
                                                    },

                                    "clear":"displayCellStd",
                                    "lineSel":"displayCellRowSel",
                                    "celSel":"displayCellSnglSel"
                            }
                }
    








THESE ARRAYS NO LONGER COMPLETELY REFLECT WHAT IS BEING GENERATED BY THE FUNCTION - NEED TO PRODUCE A NEW OUTPUT ARRAY AND COPY TO HERE !!!

Array (     
    [displayCellDescrpAry] =>           (instructions to be used by javascript functions)
        Array ( 
            [0] => TransDate 
            [1] => PersOrg 
            [2] => TransCat 
            [3] => MoneyOut 
            [4] => MoneyIn 
            [5] => Account 
            [6] => Budget 
            [7] => Reference 
            [8] => RcnclDate 
            [9] => Umbrella 
            [10] => DocType 
            [11] => Note 
            [12] => Family 
        )
    [butPanelControlAry] =>    (controls visibility of main button panel)
        Array (
            [0] = TransDate
            [1] = PersOrg
            [2] = TransCat
            [3] = None
            [4] = None
            [5] = Account
            [6] = Budget
            [7] = None
            [8] = RcnclDate
            [9] = Umbrella
            [10] = DocType
            [11] = None
            [12] = None
        )
    [subButPanelControlAry] => (controls visibility of sub button panel)
        Array (
            [0] = AutoClickDown
            [1] = AutoClickDown
            [2] = AutoClickDown
            [3] = None
            [4] = None
            [5] = AutoClickDown
            [6] = AutoClickDown
            [7] = None
            [8] = RcnclDate
            [9] = AutoClickDown
            [10] = AutoClickDown
            [11] = None
            [12] = None
        )
    [headingsAry] =>           (heading names for columns)
        Array ( 
            [0] => Date 
            [1] => Pers / Org 
            [2] => Trans Cat 
            [3] => Withdrawn 
            [4] => PaidIn 
            [5] => Account 
            [6] => Budget 
            [7] => Reference 
            [8] => Reconciled 
            [9] => Umbrella 
            [10] => Doc Type 
            [11] => Note 
            [12] => Family
    [allRecordsColNameRndAry] =>       (Random key for name of the columns in the allrecords table that the display columns relate to. The plain text can be retrieved using getPlain() )
        Array ( 
            [0] => Y76ju 
            [1] => f5GHy 
            [2] => 45GTr 
            [3] => etc. 
            [4] => etc. 
            [5] => etc. 
            [6] => etc. 
            [7] => etc. 
            [8] => etc. 
            [9] => etc. 
            [10] => etc. 
            [11] => etc. 
            [12] => etc. 
        )
    [displayCellCntrlStrAry] =>         (provides additional settings for the display div, such as editable)
        Array ( 
            [0] => 
            [1] => 
            [2] => 
            [3] => contentEditable="true" 
            [4] => contentEditable="true" 
            [5] => 
            [6] => 
            [7] => contentEditable="true" 
            [8] => 
            [9] => 
            [10] => 
            [11] => contentEditable="true" 
            [12] => 
        ) 
    [displayStndClassesAry] =>          (classes for an unselected line - white)
        Array ( 
            [0] => displayCellStd 
            [1] => displayCellStd 
            [2] => displayCellStd 
            [3] => displayCellMoney 
            [4] => displayCellMoney 
            [5] => displayCellStd 
            [6] => displayCellStd 
            [7] => displayCellStd 
            [8] => displayCellStd 
            [9] => displayCellStd 
            [10] => displayCellStd 
            [11] => displayCellStd 
            [12] => displayCellStd 
        ) 
    [displayLineSelClassesAry] =>       (classes for a selected line - bluish)
        Array ( 
            [0] => displayCellRowSel 
            [1] => displayCellRowSel 
            [2] => displayCellRowSel 
            [3] => displayCellRowSelMoney 
            [4] => displayCellRowSelMoney 
            [5] => displayCellRowSel 
            [6] => displayCellRowSel 
            [7] => displayCellRowSel 
            [8] => displayCellRowSel 
            [9] => displayCellRowSel 
            [10] => displayCellRowSel 
            [11] => displayCellRowSel 
            [12] => displayCellRowSel 
        )
    [idrArry] => 
        array (
            [0] => 237
            [1] => 322
            [2] => 238
            [3] => 225
            [4] => 227
            [5] => 219
            .....
        )
    [rowsAry] => 
        Array ( 
            [0] => 
                Array ( 
                    [displayRowsAry] => 
                        Array ( 
                            [0] => 09-04-2019 
                            [1] => Alex 
                            [2] => Session Pay 
                            [3] => 67.99 
                            [4] => 
                            [5] => FP Cash Float 
                            [6] => Sessional 
                            [7] => SO 
                            [8] => 01-01-2000 
                            [9] => Church 
                            [10] => Receipt 
                            [11] => Test Note 
                            [12] => prefix 237 
                        ) 
                    [displayRowsClassesAry] =>      (these sections change classes for particular cells dynamically - such as displayCellRcnclBlank/displayCellRcnclEarly so are needed for each data section)
                        Array ( 
                            [0] => displayCellStd 
                            [1] => displayCellStd 
                            [2] => displayCellStd 
                            [3] => displayCellMoney 
                            [4] => displayCellMoney 
                            [5] => displayCellStd 
                            [6] => displayCellStd 
                            [7] => displayCellStd 
                            [8] => displayCellRcnclBlank 
                            [9] => displayCellStd 
                            [10] => displayCellStd 
                            [11] => displayCellStd 
                            [12] => displayCellStd 
                        ) 
                    [fileName] => 2019-05-09-14.pdf
                    [fileNameRand] => t67Ug
                ) 
            [1] => 
                Array ( 
                    [displayRowsAry] => 
                        Array ( 
                            [0] => 09-04-2019 
                            [1] => Cynthia 
                            [2] => Driver Exp 
                            [3] => 
                            [4] => 45.78 
                            [5] => General 
                            [6] => Fiscaf Budget 
                            [7] => 
                            [8] => 01-01-2000 
                            [9] => Furniture Project 
                            [10] => Invoice 
                            [11] => Experiment Note 
                            [12] => 
                        ) 
                    [displayRowsClassesAry] => 
                        Array ( 
                            [0] => displayCellStd 
                            [1] => displayCellStd 
                            [2] => displayCellStd 
                            [3] => displayCellMoney 
                            [4] => displayCellMoney 
                            [5] => displayCellStd 
                            [6] => displayCellStd 
                            [7] => displayCellStd 
                            [8] => displayCellRcnclBlank 
                            [9] => displayCellStd 
                            [10] => displayCellStd 
                            [11] => displayCellStd 
                            [12] => displayCellStd 
                        )  
                    [fileName] => 2019-05-09-15.pdf
                    [fileNameRand] => 4Jyt6
                ) 
        ) 
) 
 */
function createStndDisplData(
        $recordsDataArry,
        $IncludeFiltIdxAry,
        $standardCellClass,
        $rowSelCellClass,
        $rowSelMoneyCellClass,
        $filtCellClass,
        $filtMoneyCellClass,
        $moneyCellClass,
        $blankRecncldClass,
        $unRecncldClass,
        $tooEarlyRecncld,
        $endDate,
        $download,
        $allowEdit,
        $allRecordsColNameRndAry,
        $displayBankAcc,
        $colClssAry,
        $_familyPrefixAry,
        $moneyDisplayStr
        ) {
    global $orgPersonsListAry;
    global $transCatListAry;
    global $accountListAry;
    global $budgetListAry;
    global $umbrellaListAry;
    global $docTypeListAry;
    global $_filenameRandLength;

    $index = 0;
    $transactonCount = 0;
    $rowsAry = array();
    $returnAry = array();
    $displayCellDescrpAry = array();
    $headingsAry = array();
    $displayCellCntrlStrAry = array();
    $displayLineSelClassesAry = array();
    $fileNameRands = array();
    $idrArry = array();
    $compoundTypeAry = []; //used to hold idRs of all compound lines. Each idR key will have a corresponding value that is either "Master" or "Slave"
    $compoundHiddenAry = []; //used to hold TRUE for a row that should be normally hidden (e.g. compound rows that are not normally visible because they are exluded by a filter action), FALSE if an ordinary or visible compound row that should normally be displayed. Each idR key will have a corresponding value that is either "Master" or "Slave"
    $rowStatusArray = []; //will store any statuses created here in this php function and will be converted to a javascript array and used to dynamically store any statuses of rows that are changed by clicks etc.
    $previousLoopCompoundNum = -1; //initial setting so first loop iteration will always be seen as a new group - whether an actual group or just 0
    $tempCompoundIdrAry = [];
    $compoundGroupIdrAry = [];
    

    $totalWithdrawn = 0;
    $totalPaidIn = 0;
    $bankStmtWithdrawn = 0;
    $bankStmtPaidIn = 0;
    $bankStmtBal = 0;
    $bankStmtLines = 0;
    $totalRecncldDocsWithdrawn = 0;
    $totalRecncldDocsPaidIn = 0;

    $displayCellDescrpAry[] = "TransDate";
    $displayCellDescrpAry[] = "PersOrg";
    $displayCellDescrpAry[] = "TransCat";
    $displayCellDescrpAry[] = "MoneyOut";
    $displayCellDescrpAry[] = "MoneyIn";
    $displayCellDescrpAry[] = "Account";
    $displayCellDescrpAry[] = "Budget";
    $displayCellDescrpAry[] = "Reference";
    $displayCellDescrpAry[] = "RcnclDate";
    $displayCellDescrpAry[] = "Umbrella";
    $displayCellDescrpAry[] = "DocType";
    $displayCellDescrpAry[] = "Note";
    $displayCellDescrpAry[] = "Family";

    $butPanelControlAry[] = "TransDate";
    $butPanelControlAry[] = "PersOrg";
    $butPanelControlAry[] = "TransCat";
    $butPanelControlAry[] = "None";
    $butPanelControlAry[] = "None";
    $butPanelControlAry[] = "Account";
    $butPanelControlAry[] = "Budget";
    $butPanelControlAry[] = "None";
    $butPanelControlAry[] = "RcnclDate";
    $butPanelControlAry[] = "Umbrella";
    $butPanelControlAry[] = "DocType";
    $butPanelControlAry[] = "None";
    $butPanelControlAry[] = "None";

    $subButPanelControlAry[] = "AutoClickDown";
    $subButPanelControlAry[] = "AutoClickDown";
    $subButPanelControlAry[] = "AutoClickDown";
    $subButPanelControlAry[] = "None";
    $subButPanelControlAry[] = "None";
    $subButPanelControlAry[] = "AutoClickDown";
    $subButPanelControlAry[] = "AutoClickDown";
    $subButPanelControlAry[] = "None";
    $subButPanelControlAry[] = "RcnclDate";
    $subButPanelControlAry[] = "AutoClickDown";
    $subButPanelControlAry[] = "AutoClickDown";
    $subButPanelControlAry[] = "None";
    $subButPanelControlAry[] = "None";

    $headingsAry["headerRowsAry"][] = "Date";
    $headingsAry["headerRowsAry"][] = "Pers / Org";
    $headingsAry["headerRowsAry"][] = "Trans Cat";
    $headingsAry["headerRowsAry"][] = "Withdrawn";
    $headingsAry["headerRowsAry"][] = "PaidIn";
    $headingsAry["headerRowsAry"][] = "Account";
    $headingsAry["headerRowsAry"][] = "Budget";
    $headingsAry["headerRowsAry"][] = "Reference";
    $headingsAry["headerRowsAry"][] = "Reconciled";
    $headingsAry["headerRowsAry"][] = "Umbrella";
    $headingsAry["headerRowsAry"][] = "Doc Type";
    $headingsAry["headerRowsAry"][] = "Note";
    $headingsAry["headerRowsAry"][] = "Family";

    if ($allowEdit) { //set content editable for those cells that are to have direct data entry such as withdrawn, paidin, reference etc.
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = 'contentEditable="true"';
        $displayCellCntrlStrAry[] = 'contentEditable="true"';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = 'contentEditable="true"';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = 'contentEditable="true"';
        $displayCellCntrlStrAry[] = '';
    }
    else { //make all cells none editable as no edit has been selected (makes for a smoother click down experience with return key)
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
        $displayCellCntrlStrAry[] = '';
    }

    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $moneyCellClass;
    $displayStndClassesAry[] = $moneyCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;
    $displayStndClassesAry[] = $standardCellClass;


    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelMoneyCellClass;
    $displayLineSelClassesAry[] = $rowSelMoneyCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;
    $displayLineSelClassesAry[] = $rowSelCellClass;    
    
    $recordsDataAryLength = count($recordsDataArry);


    foreach ($recordsDataArry as $recordsIdx=>$singleRecArry) { //loop through all persOrgs selected for display creating indexed array of values like "idR", "persOrgCategory" for each row to be displayed
        //if (!(($download) && ($singleRecArry["compoundHidden"] == TRUE))) { //in download mode, if the row is a compound one that is normally hidden unless a member of the compound is clicked, don't create it
        if ($singleRecArry["compoundHidden"] == FALSE) { //for the moment abandon hidden compound rows and only create compound rows that are meant to be seen all the time (prevents messing up sticky copy/paste that pastes values to idden rows inadvertantly. Compound show in JS has also been disabled
        	$budgetNotWithinDate = FALSE; //flag that will be set if section that sets colour classes sets a budget not within date colour for the current row
            $displayRowsAry = array();
            $displayRowsClassesAry = array();

            $rowStatusArray[$singleRecArry["idR"]]["rowHidden"] = FALSE;


//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################
//##############################


            $compoundHiddenAry[$singleRecArry["idR"]] = $singleRecArry["compoundHidden"];
            $compoundColNumAry[$singleRecArry["idR"]] = $singleRecArry["compoundColNum"];

            if ($singleRecArry["compoundType"] == "Master") {
                if ($singleRecArry["compoundColNum"] == 0) { //normal compound master colour
            	   $colorSuffixClass = $colClssAry["compoundMaster"];
                }
                else { //alternative (budgets) compound master colour
                    $colorSuffixClass = $colClssAry["compoundMasterAlt"];
                }
            	$compoundTypeAry[$singleRecArry["idR"]] = "Master";
            }
            else if ($singleRecArry["compoundType"] == "Slave") {
                if ($singleRecArry["compoundColNum"] == 0) { //normal compound slave colour
            	   $colorSuffixClass = $colClssAry["compoundSlave"];
                }
                else { //alternative (budgets) compound slave colour
                   $colorSuffixClass = $colClssAry["compoundSlaveAlt"];
                }
            	$compoundTypeAry[$singleRecArry["idR"]] = "Slave";
            }
            else if ($singleRecArry["compoundType"] == "FinalSlave") {
                if ($singleRecArry["compoundColNum"] == 0) { //normal compound final slave colour
            	   $colorSuffixClass = $colClssAry["compoundSlaveFinal"];
                }
                else { //alternative (budgets) compound final slave colour
                   $colorSuffixClass = $colClssAry["compoundSlaveFinalAlt"];
                }
            	$compoundTypeAry[$singleRecArry["idR"]] = "FinalSlave";
            }
            else {
            	$colorSuffixClass = $colClssAry["unselCol"];
            	$compoundTypeAry[$singleRecArry["idR"]] = "None";
            }

            if ($previousLoopCompoundNum != $singleRecArry["compound"]) { //a new compound group has started (or 0s after initial $previousLoopCompoundNum was set to -1 or a compound group has ended)
                $tempCompoundIdrAry = []; //reset ready to accumulate new list of idRs for current compound number
            }
            $tempCompoundIdrAry[] = $singleRecArry["idR"]; //add new idR to temp compound array
            if (0 < $singleRecArry["compound"]) { //a compound row (these rows will be in compound number groups e.g. 34,34,34,0,0,0,0,0,0 77,77,77 with as sorted by getMultDocDataAry() )
                $compoundGroupIdrAry[$singleRecArry["compound"]] = $tempCompoundIdrAry; //add temp compound array to $compoundGroupIdrAry at position keyed by current compound number
            }
            $previousLoopCompoundNum = $singleRecArry["compound"];



            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //record date class
            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; // person-organisation class
            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //transaction class

            if ($moneyDisplayStr == "amountPaidIn") { //apends cells in the whole withdrawn column with blankedMoneyCol to warn that it is not in use
                $displayRowsClassesAry[] = $moneyCellClass." ".$colClssAry["blankedMoneyCol"]; //withdrawn class - blanked
            }
            else {
                $displayRowsClassesAry[] = $moneyCellClass." ".$colorSuffixClass; //withdrawn class - normal or compound
            }

            if ($moneyDisplayStr == "amountWithdrawn") { //apends cells in the whole paidin column with blankedMoneyCol to warn that it is not in use
                $displayRowsClassesAry[] = $moneyCellClass." ".$colClssAry["blankedMoneyCol"]; //paidin class - blanked
            }
            else {
                $displayRowsClassesAry[] = $moneyCellClass." ".$colorSuffixClass; //paidin class - normal or compound
            }

            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //account class

            //SECTION FOR COLOURING BUDGETS THAT HAVE EXPIRED FOR THE TRANSACTION DATE THEY HAVE BEEN APPLIED TO
            $checkBudgetDatesResult = checkBudgetDates($singleRecArry["recordDate"], aryValueOrZeroStr($budgetListAry, $singleRecArry["budget"]));
            if ($checkBudgetDatesResult == "Expired") {
            	$displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["budgetExpired"]; //budget class - budget expiry date earlier than transaction date colour
            	$budgetNotWithinDate = TRUE; //used to inhibit filter colour where a budget notwithin date colour has been applied to a transaction
            }
            else if ($checkBudgetDatesResult == "NotYetActive") {
                $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["budgetNotYetActive"]; //budget class - budget start date later than transaction date colour
                $budgetNotWithinDate = TRUE; //used to inhibit filter colour where a budget notwithin date colour has been applied to a transaction
            }
            else {
        		$displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //budget class - normal
        	}

            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //reference class

                    //set appropriate class for reconcile date display to indicate status (by default it is already set in class sections above to $standardCellClass)
            if ($endDate < $singleRecArry["reconciledDate"]) { //reconcile date is later than the end of the latest displayed month so show as unreconciled (usually red)
                $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["notRcnclCol"];    //$unRecncldClass;
            }
            elseif ($singleRecArry["reconciledDate"] < $singleRecArry["recordDate"]) { //reconcile date is earlier than the transaction date so either...
                if ($singleRecArry["reconciledDate"] == "2000-01-01") { //blank the date as it is the default value (usually by setting the font and the background to the same colour)
                    $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselInvisCol"];
                }
                else { //set a warning colour to indicate the reconcile date has been set to a date earlier than the transaction date but not the default date (usually orange)
                    $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["rcnclTooEarlyCol"];
                }

            }
            else { //none of above apply so set to standard class to just show plain date
                $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"];
            }

            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //umbrella class
            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //doc type class
            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //note class
            $displayRowsClassesAry[] = $standardCellClass." ".$colClssAry["unselCol"]; //family class

            
            //SECTION FOR SETTING FILTER COLOUR FOR FILTERED COLUMNS - OVERRIDES ANY PREVIOUSLY SET COLOUR CLASSES EXCEPT BUDGET EXPIRED COLOURS
            foreach ($IncludeFiltIdxAry as $colIdx) { //set filter class for those columns that have been filtered - shouldn't (don't know if it is explicitly prevented) be used for reconciled date column
                if (($displayCellDescrpAry[$colIdx] == "MoneyOut") || ($displayCellDescrpAry[$colIdx] == "MoneyIn")) { //needs right alignment because withdrawn or paidin cell
                    $displayRowsClassesAry[$colIdx] = $moneyCellClass." ".$colClssAry["columnFiltCol"];
                }
                elseif (($displayCellDescrpAry[$colIdx] == "Budget") && $budgetNotWithinDate)  {
                	//do not set filter colour as budget expired colour has been set and needs to be seen in the filter column. If the whole column is expired the filter colour will be seen in the header
                }
                else { //ordinary cell so normal left alignment
                    $displayRowsClassesAry[$colIdx] = $standardCellClass." ".$colClssAry["columnFiltCol"];
                }
            }

            
            //load data for current row starting at the left column (0)
            $recDateAry = explode("-", $singleRecArry["recordDate"]); 
            $displayRowsAry[] = $recDateAry[2]."-".$recDateAry[1]."-".$recDateAry[0]; //create date in reverse format to that stored in allRecords table (2019-03-23) - i.e. display like 23-03-2019
            $displayRowsAry[] = aryValueOrZeroStr($orgPersonsListAry, $singleRecArry["personOrOrg"]);
            $displayRowsAry[] = aryValueOrZeroStr($transCatListAry, $singleRecArry["transCatgry"]);
            $displayRowsAry[] = fourThreeOrTwoDecimals($singleRecArry["amountWithdrawn"]); //format both withdrawn and paidin to two decimal places with single leading zero for amounts < £1.00
            $displayRowsAry[] = fourThreeOrTwoDecimals($singleRecArry["amountPaidIn"]);
            $displayRowsAry[] = aryValueOrZeroStr($accountListAry, $singleRecArry["accWorkedOn"]);
            $displayRowsAry[] = aryValueOrZeroStr($budgetListAry, $singleRecArry["budget"]);
            $displayRowsAry[] = $singleRecArry["referenceInfo"];
            
            $reconcileDate = $singleRecArry["reconciledDate"];
            $reconcileDateAry = explode("-", $reconcileDate);
            
            if (($download) && ($reconcileDate == "2000-01-01")) { //for downloads set reconcile date to blank "" if it is the default of "2000-01-01" (for the on-screen display this isn't done - the date is made invisible by the class instead, by setting the text and background colour the same, or to some other colour to indicate not reconciled etc.)
                $displayRowsAry[] = "";

            }
            else {
                $displayRowsAry[] = $reconcileDateAry[2]."-".$reconcileDateAry[1]."-".$reconcileDateAry[0]; //create date in reverse format to that stored in allRecords table - i.e. display like 23-03-2019
            }

            //continue loading data for current row starting at next column
            $displayRowsAry[] = aryValueOrZeroStr($umbrellaListAry, $singleRecArry["umbrella"]);
            $displayRowsAry[] = aryValueOrZeroStr($docTypeListAry, $singleRecArry["docType"]);
            $displayRowsAry[] = $singleRecArry["recordNotes"];
           

            //set values and prefixes for family cell (12) - if neither of these criteria are met it defaults to ""
            if ($singleRecArry["idR"] == $singleRecArry["parent"]) { //parent value same as index so this is an actual parent: show family num with prefix
                $displayRowsAry[] = $_familyPrefixAry["parentPrefix"].$singleRecArry["parent"];
            }
            elseif (0 < $singleRecArry["parent"]) { //0 < parent value so this is a child: show family num with "c " prefix
                if ($singleRecArry["parentDate"] != "2000-01-01") {
                    $displayRowsAry[] = $_familyPrefixAry["dependentChildPrefix"].$singleRecArry["parent"];
                }
                else {
                    $displayRowsAry[] = $_familyPrefixAry["independentChildPrefix"].$singleRecArry["parent"];
                }
            }
            else{
                $displayRowsAry[] = ""; //default to show nothing for family status
            }

            //totals use dfor test purposes at moment - may not continue with them as system becomes proven
            $totalWithdrawn = $totalWithdrawn + $singleRecArry["amountWithdrawn"]; //accumulates total amount withdrawn for the displayed page
            $totalPaidIn = $totalPaidIn + $singleRecArry["amountPaidIn"]; //accumulates total amount paidin for the displayed page

            if ($displayBankAcc) { //special section to create display values when bank statement reconcilliation mode is being used
                if ($index == 0) { //this row displays single bank statement for reconcilation checks
                    $bankStmtWithdrawn = $singleRecArry["amountWithdrawn"]; //amount withdrawn for the bank statement
                    $bankStmtPaidIn = $singleRecArry["amountPaidIn"]; //amount paidin for the bank statement
                    $bankStmtLines = $singleRecArry["referenceInfo"]; //referenceInfo is used to hold number of lines in the bankstatement
                    $bankStmtBal = $bankStmtPaidIn - $bankStmtWithdrawn;
                }
                elseif (0 < $index) { //all these other rows display transactions associated with the bank statement so do accumulations (but only if further rows exist!)
                    $totalRecncldDocsWithdrawn += $singleRecArry["amountWithdrawn"]; //accumulates total amount withdrawn for the displayed transactions associated with bank statement
                    $totalRecncldDocsPaidIn += $singleRecArry["amountPaidIn"]; //accumulates total amount paidin for the displayed transactions associated with bank statement
                }
                
            
            }

            $rowsAry[$index]["displayRowsAry"] = $displayRowsAry;
            $rowsAry[$index]["displayRowsClassesAry"] = $displayRowsClassesAry;
            //idR is now a main key with it's own index - think this following line is no longer needed
            //$rowsAry[$index]["idR"] = $singleRecArry["idR"]; //idR for current row (identifies displayed rows with allRecords table rows - used to create unique id for each cell for doing updates of table)
            $rowsAry[$index]["fileName"] = $singleRecArry["fileName"]; //doc file name for current row. There will be repeats of same filename if  the same doc is associated with more than one record

            if (!array_key_exists($singleRecArry["fileName"], $fileNameRands)) { //if a filename random alphanumeric hasn't already been created and stored
                $fileNameRands[$singleRecArry["fileName"]] = getRandNoSave($_filenameRandLength); //create and store a random for the current file name
            }
            $rowsAry[$index]["fileNameRand"] = $fileNameRands[$singleRecArry["fileName"]]; //use the random that has just/previously been created and stored for the current file name
            
            $idrArry[] = $singleRecArry["idR"]; //idR for current row (identifies displayed rows with allRecords table rows

            if ($singleRecArry["compoundHidden"] == FALSE) { //only increment transaction count (to be displayed line 1 bottom right) if the row is not normally hidden by filter action.
                $transactonCount++;
            }

            $index++;
        }
    }

    //CONSIDER REMOVING THESE INDIVIDUAL ARRAYS AND LEAVING JUST THE STATIC ONES - NEED TO CHEQUE THE REST OF THE CODE TO ENSURE THEY ARE NO LONGER NEEDED !

    //BUT NOT THE HEADER ONE AS IT IS NOW USED!
    $returnAry["displayCellDescrpAry"] = $displayCellDescrpAry;
    $returnAry["headerAry"][] = $headingsAry;
    $returnAry["allRecordsColNameRndAry"] = $allRecordsColNameRndAry;
    $returnAry["displayCellCntrlStrAry"] = $displayCellCntrlStrAry;
    $returnAry["displayStndClassesAry"] = $displayStndClassesAry;
    $returnAry["displayLineSelClassesAry"] = $displayLineSelClassesAry;

    //section of static arrays (ones that are for formating and control and are the same for all rows)
    $staticArys["displayCellDescrpAry"] = $displayCellDescrpAry;
    $staticArys["butPanelControlAry"] = $butPanelControlAry;
    $staticArys["subButPanelControlAry"] = $subButPanelControlAry;
    $staticArys["headingsAry"] = $headingsAry;
    $staticArys["allRecordsColNameRndAry"] = $allRecordsColNameRndAry;
    $staticArys["displayCellCntrlStrAry"] = $displayCellCntrlStrAry;
    $staticArys["displayStndClassesAry"] = $displayStndClassesAry;
    $staticArys["displayLineSelClassesAry"] = $displayLineSelClassesAry;
    
    if ($displayBankAcc) {
        $totalRecncldDocsBal = $totalRecncldDocsPaidIn - $totalRecncldDocsWithdrawn;
        $returnAry["bankStmtWithdrawn"] = $bankStmtWithdrawn;
        $returnAry["bankStmtPaidIn"] = $bankStmtPaidIn;
        $returnAry["bankStmtBal"] = $bankStmtBal;
        $returnAry["totalRecncldDocsWithdrawn"] = $totalRecncldDocsWithdrawn;
        $returnAry["totalRecncldDocsPaidIn"] = $totalRecncldDocsPaidIn;
        $returnAry["totalRecncldDocsBal"] = $totalRecncldDocsBal;
        $returnAry["linesDiff"] = ($index -1) - $bankStmtLines;
        $returnAry["withdrawnDiff"] = $totalRecncldDocsWithdrawn - $bankStmtWithdrawn;
        $returnAry["paidinDiff"] = $totalRecncldDocsPaidIn - $bankStmtPaidIn;


        $returnAry["balDiff"] = $bankStmtBal - $totalRecncldDocsBal;
    }
    
    $returnAry["transCount"] = $transactonCount;
    $returnAry["staticArys"] = $staticArys;
    $returnAry["idrArry"] = $idrArry;
    $returnAry["rowsAry"] = $rowsAry;
    $returnAry["compoundHiddenAry"] = $compoundHiddenAry;
    $returnAry["compoundTypeAry"] = $compoundTypeAry;
    $returnAry["compoundColNumAry"] = $compoundColNumAry;
    $returnAry["compoundGroupIdrAry"] = $compoundGroupIdrAry;
    $returnAry["rowStatusArray"] = $rowStatusArray;
    
    return $returnAry;
}





/* If the array and key exist, tests the subarray designated by key to see if it contains the value $valueToTestFor. If conditions are met returns TRUE, otherwise FALSE.   */
function subAryContains($array, $key, $valueToTestFor) {
     if (array_key_exists($key, $array)) {
        return in_array($valueToTestFor, $array[$key]);
     }
     else {
        return FALSE;
     }
}


/* Iterates through the characters in $sourceStr substituting $replacementStr for every occurence of $targetStr returning the result. If target empty or not found source is returned unchanged. */
function replaceTargetStr($sourceStr, $targetStr, $replacementStr) {
	if ($targetStr == "") { //if target is empty return source without processing
		$outputStr = $sourceStr;
	}
	else {
		$outputStr = "";
		$strAry = explode($targetStr, $sourceStr);
		$numOfSegments = count($strAry);
		$segIdx = 0;
		while ($segIdx < ($numOfSegments - 1)) { //
			$outputStr = $outputStr.$strAry[$segIdx].$replacementStr;
			$segIdx++;
		}
		$outputStr = $outputStr.$strAry[$segIdx];
	}
	return $outputStr;
}


/* Splits $sourceStr into segments separated by $targetStr and returns as an indexed array of segments which don't contain $targetStr. If target empty or not found source is returned as single item array. */
function splitOnTargetStr($sourceStr, $targetStr) {
	if ($targetStr == "") { //if target is empty return source without processing
		$outputAry[] = $sourceStr;
	}
	else {
		$outputAry = explode($targetStr, $sourceStr);
	}
	return $outputAry;
}


/* Splits $sourceStr into 2 segments the first is everything up to the last occurance of $targetStr (so will inlude instances of $targetStr if they exist in the first segment), the second is everything after $targetStr. The result is returned in a 2 item array but if target is $targetStr or not found, the source is returned as single item array. e.g. $sourceStr = "The quick brown fox", $targetStr = "", result = array([0] => "The quick brown", [1] => "fox" ) */
function splitInTwoOnLastTargetStr($sourceStr, $targetStr) {
	if ($targetStr == "") { //if target is empty return source without processing
		$outputAry[] = $sourceStr;
	}
	else {
		$firstSegStr = "";
		$strAry = explode($targetStr, $sourceStr);
		$numOfSegments = count($strAry);
		$segIdx = 0;
		while ($segIdx < ($numOfSegments - 1)) { //loop for all except last segment (will not loop if $strAry has only 1 segment stored in it)
			if ($segIdx < ($numOfSegments - 2)) {
				$firstSegStr = $firstSegStr.$strAry[$segIdx].$targetStr;
			}
			else {
				$firstSegStr = $firstSegStr.$strAry[$segIdx];
			}
			$segIdx++;
		}
		if (0 < $segIdx) { //if the loop did run create a 0 index item for the concatonated segments that occured before the last $targetStr
			$outputAry[] = $firstSegStr;
		}
		$outputAry[] = $strAry[$segIdx]; //either creates a 1 index for the everything after $targetStr, OR, if there was only 1 segment in the array this becomes stored as 0 index
	}
	return $outputAry;
}



/* Originally for parsing a css file by changing px to vw and applying a multiplier to the px values.  */
function parseFile($inputFileNameWithPath, $outputFileNameWithPath) {
	//pr(getcwd()." ");
	$fileOutput = fopen($outputFileNameWithPath,"w") or die("Unable to open file!");
	$fileInput = fopen($inputFileNameWithPath,"r");
	while(! feof($fileInput)) {
		$line = fgets($fileInput); //PROCESS ONE LINE AT A TIME!
		$lineWithAddedSpaceAfterColons = replaceTargetStr($line, ":", ": ");
		$outputStr = "";
		$separatedByPx = splitOnTargetStr($lineWithAddedSpaceAfterColons, "px");
		$numOfSegments = count($separatedByPx);
		$segIdx = 0;
		while ($segIdx < ($numOfSegments - 1)) { //
			$twoSegs = splitInTwoOnLastTargetStr($separatedByPx[$segIdx], " ");
			$stuffBeforeLastSpace = $twoSegs[0];
			$numAfterLastSpace = $twoSegs[1];
			$outputStr = $outputStr.$stuffBeforeLastSpace." ".($numAfterLastSpace * 0.05208)."vw";
			$segIdx++;
		}
		$outputStr = $outputStr.$separatedByPx[$segIdx];
		fwrite($fileOutput, $outputStr);
	}
	fclose($fileInput);
	fclose($fileOutput);
}


/* Sorts in ascending order and then return an array of arrays based on the values in two sub arrays indexed by the keys: first in order of values indexed by $subAryKey1, then in order of values indexed by $subAryKey2. The values from $a and $b indexed by $subAryKey2 are first padded with leading zeros as needed to make them the same number of digits before concatonation to enable proper comparisons. No error checking is included to catch out of range indexes.
BEFORE SORT:
array (
    [0] => array (
        [idR] => 6
        [compound] => 2
    )
    [1] => array (
        [idR] => 7
        [compound] => 3
    )
    [2] => array (
        [idR] => 5
        [compound] => 2
    )
)
AFTER SORT:
array (
    [0] => array (
        [idR] => 5
        [compound] => 2
    )
    [1] => array (
        [idR] => 6
        [compound] => 2
    )
    [2] => array (
        [idR] => 7
        [compound] => 3
    )
)
*/
function sortTwoDimAryForTwoSubArys($aryToSort, $subAryKey1, $subAryKey2) {
    usort($aryToSort, function($a, $b) use ($subAryKey1, $subAryKey2) {
        $key2ValA = $a[$subAryKey2]; //extract value for 2nd sub array key
        $key2ValB = $b[$subAryKey2];
        
        while (strlen((string)$key2ValB) < strlen((string)$key2ValA)) { //if number of digits in B < number of digits in A add leading zero to B until they have equal number of digits
            $key2ValB = "0".$key2ValB;
        }
        while (strlen((string)$key2ValA) < strlen((string)$key2ValB)) { //if number of digits in A < number of digits in B add leading zero to A until they have equal number of digits
            $key2ValA = "0".$key2ValA;
        }
        //if neither of these while loops run $key2ValA and $key2ValB already have an equal number of digits
        $comparisonNumA = $a[$subAryKey1].$key2ValA;
        $comparisonNumB = $b[$subAryKey1].$key2ValB;

        if ($comparisonNumA == $comparisonNumB) return 0;
        return ($comparisonNumA < $comparisonNumB) ? -1 : 1;
    });
    return $aryToSort;
}


/* Sorts in ascending order an array of arrays based on the values in the sub arrays designated by the key $subAryKey. e.g. if $subAryKey = 1 then:
    array( [0]=>array([0]=>Dog, [1]=>Cat), [1]=>array([0]=>Zebra, [1]=>Ardvark) )
    is returned as
    array( [0]=>array([0]=>Zebra, [1]=>Ardvark), [1]=>array([0]=>Dog, [1]=>Cat) )
    because sorting is done based on Cat, Ardvark.
    No error checking is included to catch out of range indexes.
*/
function sortTwoDimAry($aryToSort, $subAryKey) {
    usort($aryToSort, function($a, $b) use ($subAryKey) {
        if ($a[$subAryKey] == $b[$subAryKey]) return 0;
        return ($a[$subAryKey] < $b[$subAryKey]) ? -1 : 1;
    });
    return $aryToSort;
}

/* Sorts in ascending order an array of arrays of arrays based on the values in the sub sub arrays designated by the key $subAryKey and subSubAryKey. e.g. if $subAryKey = 0 and subSubAryKey = 1 then:
    array(  [0]=>array( [0]=>array([0]=>Mouse, [1]=>Cat), [1]=>array([0]=>Horse, [1]=>Pig) ),    [1]=>array( [0]=>array([0]=>Dog, [1]=>Badger), [1]=>array([0]=>Zebra, [1]=>Ardvark) )  )
    is returned as
    array(  [0]=>array( [0]=>array([0]=>Dog, [1]=>Badger), [1]=>array([0]=>Zebra, [1]=>Ardvark) ),    [1]=>array( [0]=>array([0]=>Mouse, [1]=>Cat), [1]=>array([0]=>Horse, [1]=>Pig) )  )
    because sorting is done based on Cat, Badger.
    No error checking is included to catch out of range indexes.
*/
function sortThreeDimAry($aryToSort, $subAryKey, $subSubAryKey) {
    usort($aryToSort, function($a, $b) use ($subAryKey, $subSubAryKey) {
        if ($a[$subAryKey][$subSubAryKey] == $b[$subAryKey][$subSubAryKey]) return 0;
        return ($a[$subAryKey][$subSubAryKey] < $b[$subAryKey][$subSubAryKey]) ? -1 : 1;
    });
    return $aryToSort;
}


function getFieldName($key) {
    global $_fieldNameAry;
    return $_fieldNameAry[$key];
}

function getColIndex($value) {
    global $_fieldNameAry;
    return array_search($value, $_fieldNameAry);
}

function getMenuRandomsArray($key) {
	global $menuRandomsArray;
	return $menuRandomsArray[$key];
}

function getGenrlAryRndms($key) {
	global $genrlAryRndms;
	return $genrlAryRndms[$key];
}

function getNonVolAryItem($key) {
	global $nonVolatileArray;
	return $nonVolatileArray[$key];
}

function nonVolAryKeyExists($key) {
	global $nonVolatileArray;
	return array_key_exists ($key, $nonVolatileArray);
}

function setNonVolAryItem($key, $value) {
	global $nonVolatileArray;
	$nonVolatileArray[$key] = $value;
}

function getPlainFromSubCmnd() {
	global $subCommand;
	return getPlain($subCommand);
}

function destroyNonVolAryItem($key) {
    global $nonVolatileArray;
    unset($nonVolatileArray[$key]);
}



function download($displayData) {
    $firstRowOfHeaderDone = FALSE;
    $downloadContent = "";
    foreach ($displayData["headerAry"] as $downloadRow) { //read a header row row at a time
        if ($firstRowOfHeaderDone) {  //miss EOL char if this is the first row fed to the o/p file      
            $downloadContent .= PHP_EOL; //concatonate an end of line character in readiness for adding next row
        }
        else {
            $firstRowOfHeaderDone = TRUE;
        }
        $downloadContent .= implode(",", $downloadRow["headerRowsAry"]); //concatonate another row as csv string from the next row array (which is a simple indexed one)
    }
    foreach ($displayData["rowsAry"] as $downloadRow) { //read a record row at a time
        $downloadContent .= PHP_EOL; //concatonate an end of line character in readiness for adding next row
        $downloadContent .= implode(",", $downloadRow["displayRowsAry"]); //concatonate another row as csv string from the next row array (which is a simple indexed one)
    }
    $fileName = 'FurnProj-'.date("Y-m-d-Hi").'.csv'; //create filename by concatonating Title and Date-Time
    $length = strlen($downloadContent) + 2;
    ob_clean(); //empties output buffer in case anything random (like new lines, spaces rubbish etc.) are in it that would be pre-pended to the downloaded file
    header('Content-Description: File Transfer');
    header('Content-Type: text/plain');
    header('Content-Disposition: attachment; filename='.$fileName);
    header('Content-Transfer-Encoding: binary');
    header('Content-Length: ' . $length);
    header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
    header('Expires: 0');
    header('Pragma: public');
    echo $downloadContent;
    //an exit statement should be placed after this function call - so no saveSession.php is included and any changes to menuRandoms etc. are not saved. Also no display of data, just the download!
}


function aryCons($arrayToDisplay) { //displays a php array in the console in a hierarchical layout - for indexed portions of the array indexes [0] [1]... are not shown! NOT PROVEN TO WORK PROPERLY !!
    ?>
    <script>
    var JSAry = <?php echo json_encode($arrayToDisplay);?>;
    console.log(JSON.stringify(JSAry, null, 4));
    </script>
    <?php
}

/* Rationalises the yearMonth date for monthSelProcess.php to change it from the form: 001 002 003 004 005 006 007 008 009 010 011 012  101 102 103 104 105 106 107 108 109 110 111 112 where the left most character represents the year offset and the right two characters represent the month. The output form is:01 02 03...11 12 13 14 15...22 23 24 that makes it simple to compare the original yearMonth dates. */
function rationaliseYrMnth($yearMonthWithGap) {
    $rationalYearMonthDate = $yearMonthWithGap;
    if (12 < $yearMonthWithGap) {
        $rationalYearMonthDate = $yearMonthWithGap - 88;
    }
    return $rationalYearMonthDate;
}

function saveMessageOnClient($message) {
    ?>
    <script>
        sessionStorage.setItem('message', '<?php echo $message;?>');
    </script>
    <?php
}

function getMessageFromClient() {
    ?>
    <p id="messageDisplay" style="margin: 2.604vw;"> </p>
    <script>
        if(sessionStorage.getItem('message')) {
            document.getElementById("messageDisplay").innerText = sessionStorage.getItem('message');
        }
    </script>
    <?php
}

/* Sends a data array using POST method to the specified url. Data array is encoded thus: array('name1' => value1, 'name2' => value2). Recovery of data in destination page: $_POST('name1') etc. */
function postData($url, $dataAry) {
    ?>
    <form id="834dyw389d38w49" ACTION="<?php echo $url;?>" METHOD="post" enctype="multipart/form-data">
    <?php
    foreach($dataAry as $key=>$value) {
        formValHolder($key, $value);
    }
    ?>
    </form>
    <script type="text/javascript">
        document.getElementById("834dyw389d38w49").submit();
    </script>
    <?php
}


/* Holds a value in a hidden textbox that can be changed by javascript and will be submitted with the form. The initial value is set to $initialValue and the id and name are the same and are set by $idAndName.  */
function formValHolder($idAndName, $initialValue = "") {
    ?>
    <input style="display:none;" type="text" name="<?php echo $idAndName;?>" value="<?php echo $initialValue;?>" id="<?php echo $idAndName; ?>" > </input>
    <?php
}

/* Holds a value identified by name in a hidden textbox that can be changed by javascript and will be submitted with the form. The initial value is set to $initialValue and the name is set by $name. */
function namedValHolder($name, $initialValue = "") {
    ?>
    <input style="display:none;" type="text" name="<?php echo $name;?>"      value="<?php echo $initialValue;?>"> </input>
    <?php
}

/* Holds a name in a hidden p element that can be changed by javascript. The initial name is set to $initialName.  */
function nameAndValHolder($id, $initialName, $initialValue = "") {
    ?>
    <input hidden id="<?php echo $id; ?>" name="<?php echo $initialName;?>" value="<?php echo $initialValue;?>">
    <?php
}

/* If key exists in array the corresponding value is returned - otherwise "" is returned. Creates empty string where id from a column of allRecords is 0 to denote default empty condition.  */
function aryValueOrZeroStr($array, $key) {
    if (array_key_exists($key, $array)) {
        return $array[$key];
    }
    else {
        return "";
    }
}

/* If key exists in array the corresponding value is returned - otherwise 0 is returned. */
function aryValueOrZeroNum($array, $key) {
    if (array_key_exists($key, $array)) {
        return $array[$key];
    }
    else {
        return 0;
    }
}

/* If key exists in array the corresponding value is returned - otherwise 0 is returned. */
function aryKeyOrZeroNum($array, $value) {
    if (in_array($value, $array)) {
        return array_search($value, $array);
    }
    else {
        return 0;
    }
}

/* Takes the passed number and rounds it to four, three or two decimal places so that if it has no decimal places (i.e. 2382) 2,382.00 will be returned, 2382.3 will return 2,382.30 , 2382.36 will return 2,382.36 , 2382.365 will return 2,382.365 , 2,382.3657 will return 2,382.3657 , 2382.36574 will return 2,382.3657 and 2382.36576 will return 2,382.3658 . if 0 or "" is passed "" is returned. */
function fourThreeOrTwoDecimals($number, $showzero = FALSE) {
    $number = number_format($number, 4, '.', ''); //round and fix to 4 decimal places.

  if (substr($number, -1) == "0") { //these two if statements remove trailing zeros beyond 2 decimal places but allow up to 4 decimal places for none zero decimals.
    $number = number_format($number, 3, '.', ''); //fix to 4 decimal places.
  }
  if (substr($number, -1) == "0") {
    $number = number_format($number, 2, '.', ''); //fix to 2 decimal places.
  }
  if (($number == 0) && !$showzero) { //set to "" so 0.00 isn't seen if there is no value set, unless $showzero is TRUE
    $number = ""; 
  }
  return $number;
}

/* Displays text in a button within a div, used to make up pages of tabulated descriptions (like 'Bank Account' or 'Furniture Project') from database tables. The displayed text is selected from $itemListAry by the index $itemIdx. Required classes are passed as arguments, $butClass is the default but if $selected == TRUE $butSelClass is used instead. If $filterIdx == $itemIdx, $filteredClass is appended to whichever button class is currently in use to indicate a button is displaying a description that has been selected as a filter term. $pageAddr is used as the button value to return (usually the randomised index of) a page url that will be navigated to on clicking the button. $subCmd is suffixed (after a separating hyphen) to the value $pageAddr to act as a subsiduary command for whatever purpose (i.e. the filename of the document the button is referencing). At the end of the button value a further (usually as a the randomised index) suffix (again after a separating hyphen) is added to pass the text itself. The displayed text is returned, "" if nothing is displayed.
*/
function docStrButton($divClass, $butClass, $butSelClass, $filteredClass, $selected, $itemIdx, $itemListAry, $itemRandomsAry, $filterIdx, $pageAddr, $subCmd) {
    ?>
    <div class=<?php echo $divClass;?>>
        <?php

        $itemStr = "";
        $docVarietyRandom = "";
        if ($itemIdx) { //if a doc variety number exists in the doc database for the current doc
            $itemStr = $itemListAry[$itemIdx]; //use the number as a key of the array containing doc variety names to get the name
            $itemRandom = $itemRandomsAry[$itemStr];
        }

        if ($itemStr) { 
            if ($selected) {
                $butClass = $butSelClass;
            }

            $butClassFinal = $butClass;
            
            if ($itemIdx == $filterIdx) {
                $butClassFinal = '"'.$butClass.' '.$filteredClass.'"';
            }
            ?>
            <button class=<?php echo $butClassFinal;?> type="submit" name="command" value=<?php echo $pageAddr."-".$subCmd."-".$itemRandom;?>><?php echo $itemStr;?></button>
            <?php 
        } ?>
    </div>
    <?php
    return $itemStr;
}

/* Takes date string in format "2008-07-23" and returns in the format 23 July 2008. */
function dateWithMnthName($date) {
    $mnthNameAry = [1=>"Jan", 2=>"Feb", 3=>"Mar",4 =>"Apr", 5=>"May", 6=>"Jun", 7=>"Jul", 8=>"Aug", 9=>"Sep", 10=>"Oct", 11=>"Nov", 12=>"Dec"];
    return substr($date, 8, 2)." ".$mnthNameAry[(int)substr($date, 5, 2)]." ".substr($date, 0, 4);
}

/* Takes date string in format "2008-07-23" and returns day of month number (1 - 28,29,30,31). */
function dayOfMnth($date) {
    return substr($date, 8, 2);
}

/* Takes date string in format "2008-07-23" and returns month number (1 - 12). */
function mnthNum($date) {
    return substr($date, 5, 2);
}

/* Takes date string in format "2008-07-23" and returns month name ("Jan" ... "Dec"). */
function mnthName($date) {
    $mnthNameAry = [1=>"Jan", 2=>"Feb", 3=>"Mar",4 =>"Apr", 5=>"May", 6=>"Jun", 7=>"Jul", 8=>"Aug", 9=>"Sep", 10=>"Oct", 11=>"Nov", 12=>"Dec"];
    return $mnthNameAry[(int)substr($date, 5, 2)];
}

/* Takes date string in format "2008-07-23" and returns month number (1 - 12). */
function year($date) {
    return substr($date, 0, 4);
}

/* Takes the passed string and places it within <span> tags formatted as html with css $class. */
function strToHtml($str, $class) {
    return '<span class="'.$class.'">'.$str.'</span>';
}


/* Takes date string in format "2008-07-23" and returns unix time (seconds since 1 Jan 1970). Seems to be influenced by the current timezone and daylight saving!! ?? (pretty confusing but probably fine for comparison purposes) */
function getUnixTime($date) {
	return mktime(0, 0, 0, substr($date, 5, 2), substr($date, 8, 2), substr($date, 0, 4));
}


/* Takes the array of arrays of multiple document data and sorts according to date information in $docsData[n]["dateEarliestRecord"] and $docsData[n]["dateLatestRecord"] according to the following rules:
 --
 1) Documents with at least one date within the $startDate - $endDate range are ordered by dateEarliestRecord, failing which dateLatestRecord (if dateEarliestRecord is outwith the range). Where the relevant date is the same as another the order is a) where dateEarliestRecord is before $startDate, b) both dates the same, c) both dates in range, d) dateLatestRecord is after $endDate. In all these cases the order is according to the precedence of the 'other' date (i.e. if two records have dateEarliestRecord outside the $startDate - $endDate range the one with the 'earliest' dateEarliestRecord comes first).
 --
 2) if both subarray dates are outwith the range they are ordered according to group, the first group is where the dateEarliestRecord is further back from $startDate than dateLatestRecord is forward of $endDate. This group is put into order of the average of dateEarliestRecord and dateLatestRecord and placed after the main group created by rule 1). The second group is where the dateLatestRecord is further forward of $endDate than dateEarliestRecord is back from $startDate. This group is put into order of average of dateEarliestRecord and dateLatestRecord and placed before the main group created by rule 1).
 The sorted and joined groups of subarrays are returned.
 --
 !!!! For this function to work it is assumed that the dates doc earliest/latest dates and start/end dates overlap each other (something that should be true if getdocData() is working properly) !!!!
 This function is only valid for doc dates approx 30 yrs ahead and behind the start/end date of the selection because of the addign and subtracting of arond 301.7yrs to force the position of date groups. For more formal and rugged performance with any dates it should be re-written!
  */
function sortDocDataByDates($docsData, $startDate, $endDate) {
	$startDateUSecs = getUnixTime($startDate); //convert dates to unix time so calculations and adds/subtracts can be done for sorting purposes
	$endDateUSecs = getUnixTime($endDate);
	$sortedDocsData = array(); //initialise things so no errors are thrown if $docsData is null! (or not an array)
	$manipulatedDatesAry = array(); //array to store dates that have been manipulated by foreach() loop so they will sort as desired
	foreach($docsData as $key=>$subAry) { //go through data for each document one at a time to create an array of date values that will sort as described in function comment above
		$earliestDateUSecs = getUnixTime($subAry["dateEarliestRecord"]);
		$latestDateUSecs = getUnixTime($subAry["dateLatestRecord"]);
		$earliestDateInrange = (($startDateUSecs <= $earliestDateUSecs) && ($earliestDateUSecs <= $endDateUSecs)); //TRUE/FALSE
		$latestDateInrange = (($startDateUSecs <= $latestDateUSecs) && ($latestDateUSecs <= $endDateUSecs)); //TRUE/FALSE
		if (($earliestDateInrange && $latestDateInrange) || $earliestDateInrange) { //both dates or earliest date within range
			$manipulatedDatesAry[$key] = $earliestDateUSecs + ($latestDateUSecs - $earliestDateUSecs) / 86400; //use earliest date + 1 unixsec/day of difference between dates (ensures all with diff date are in order but come after those with same date)
		}
		elseif ($latestDateInrange) { //latest date in range earliest date before range
			$manipulatedDatesAry[$key] = $latestDateUSecs - ($latestDateUSecs - $earliestDateUSecs) / 86400;
		}
		else { //both dates out of range (earliest is before start date and latest is after end date)
			$meanDocDate = intdiv(($earliestDateUSecs + $latestDateUSecs), 2);
			if (($startDateUSecs - $earliestDateUSecs) >= ($latestDateUSecs - $endDateUSecs)) { //earliest date is furthest away from date range - place sorted data at end of doc list
				$manipulatedDatesAry[$key] = $meanDocDate + 1000000000; //add 31.7 yrs to force this group to end of ordering
			}
			else { //latest date is furthest away from date range - place sorted data at beginning of doc list
				$manipulatedDatesAry[$key] = $meanDocDate - 1000000000; //subtract 31.7 yrs to force this group to beginning of ordering
			}
		}
		
	}
	asort($manipulatedDatesAry); //sort newly the generated date keys into numerical order
	foreach($manipulatedDatesAry as $sortedKey=>$sortedManipulatedDates) { //go throough the keys in the new order, $sortedCreatedDates is not used - only there to complete statement and ensure keys are produced
		$sortedDocsData[] = $docsData[$sortedKey]; //recreate the doc data in the new order one subarray at a time using the newly ordered keys.
	}
	return $sortedDocsData;
}


/* Displays buttons, arranged in 3 adjacent columns, for day of month, month, and year. The buttons can be selected in any order and will update hidden text boxes named $uniqueId."dayOfMonth", $uniqueId."month", and $uniqueId."year" respectively. The $uniqueId can be anything that allows more than one set of calendar buttons to work independently without interacting with each other (as javascript is used to change the text boxes and set/unset buttons). Each column of buttons (dayOfMnth, month, year) are contained in their own div, the 3 divs are contained in an outer div and classed for these divs and the buttons themselves are passed as arguments - the names are self descriptive. The initial date can be set and is passed as $setDate in the form "2008-07-23". If $viewOnly is TRUE the buttons are set to $setDate but clicking them will have no effect. The days of the month buttons are intelligent in that the max day number changes according to the month selected (sets for initial month too) i.e. Jun = 30, Feb = 28 (unless a leap year in which case = 29). If, say, 31 is selected and then the month is changed to, say, Jun, day 31 is commuted to 30 (max day for Jun) and the button background changed to amber until 30 is clicked to acknowledge or some other button selected. This ensures only valid dates are created and the user is warned if a valid date is as a result of a forced commutation in response to a month change. $uniqueIdOfEarlierDateTwin is not yet used - it is proposed as a means to give some interaction (like a date copy function) between sets of calendar buttons! */
function calendar($uniqueId, $setDate, $viewOnly, $outerDivClass, $outerDivClassWarning, $calDaysOfMnthDiv, $calMnthDiv, $calYrDiv, $dayOfMnthBtnClass, $dayOfMnthBtnSelectedClass, $mnthBtnClass, $mnthBtnSelectedClass, $yrBtnClass, $yrBtnSelectedClass, $uniqueIdOfEarlierDateTwin = "") {
    $outerDivId = $uniqueId."-outerDivId";
    //-----
    $calDateAry = explode('-', $setDate);
    $dayOfMnthSelected = $calDateAry[2];
    $dayOfMnthsAllAry = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");
    $dayOfMnthMaxId = sizeof($dayOfMnthsAllAry) -1;
    $dayOfMnthUniqueId = $uniqueId."-dayOfMnth";
    $dayOfMnthTextBoxName = $uniqueId."dayOfMonth";
    $dayOfMnthTextBoxId = $dayOfMnthUniqueId.'textBx';
    //-----
    $mnthSelected = $calDateAry[1];
    $mnthsAllAry = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
    $mnthMaxId = sizeof($mnthsAllAry) -1;
    $mnthUniqueId = $uniqueId."-mnth";
    $mnthTextBoxName = $uniqueId."month";
    $mnthTextBoxId = $mnthUniqueId.'textBx';
    //-----
    $yrSelected = $calDateAry[0];
    $yrsAllAry = array("2009", "2010", "2011", "2012", "2013", "2014", "2015", "2016", "2017", "2018", "2019");
    $baseYr = $yrsAllAry[0];
    $yrMaxId = sizeof($yrsAllAry) -1;
    $yrUniqueId = $uniqueId."-yr";
    $yrTextBoxName = $uniqueId."year";
    $yrTextBoxId = $yrUniqueId.'textBx';
    echo '<div class='.$outerDivClass.' id='.$outerDivId.'>' ;
        //-----
        echo '<div class='.$calDaysOfMnthDiv.'>';            
            $dayOfMnthButIdx = 0;
            foreach ($dayOfMnthsAllAry as $id => $dayOfMnth) {
                $dayOfMnthClass = $dayOfMnthBtnClass;
                if ($dayOfMnth == $dayOfMnthSelected) { //if current id is found in the array of items selected
                    $dayOfMnthClass = $dayOfMnthBtnSelectedClass; //change button class to 'selected'
                }                   
                if ($viewOnly) {
                    ?> <button class="<?php echo $dayOfMnthClass; ?>" type="button" id="<?php echo $dayOfMnthUniqueId.$dayOfMnthButIdx; ?>" ><?php echo $dayOfMnth; ?></button><?php
                }
                else {
                    ?> <button class="<?php echo $dayOfMnthClass; ?>" type="button" id="<?php echo $dayOfMnthUniqueId.$dayOfMnthButIdx; ?>" value="<?php echo $dayOfMnth; ?>" onclick="setClassAndCopyNameUnique('<?php echo $dayOfMnthUniqueId;?>', 1, '<?php echo $dayOfMnthMaxId;?>', '<?php echo $dayOfMnthBtnSelectedClass;?>', '<?php echo $dayOfMnthBtnClass;?>', this.value); forceClass('<?php echo $outerDivId; ?>', '<?php echo $outerDivClass; ?>');"><?php echo $dayOfMnth; ?></button><?php
                }
                $dayOfMnthButIdx++;
            }  
            ?>
            <input style="display:none; width:1.0416vw;" id="<?php echo $dayOfMnthTextBoxId; ?>" type="text" name="<?php echo $dayOfMnthTextBoxName;?>" value="<?php echo $dayOfMnthSelected; ?>"  > </input>
        </div>
        <!--     -->
        <?php echo '<div class='.$calMnthDiv.'>';
            $mnthButIdx = 0;
            foreach ($mnthsAllAry as $id => $mnth) {
                $mnthClass = $mnthBtnClass;
                if ($mnth == $mnthSelected) { //if current id is found in the array of items selected
                    $mnthClass = $mnthBtnSelectedClass; //change button class to 'selected'
                }                   
                if ($viewOnly) {
                    ?> <button class="<?php echo $mnthClass; ?>" type="button" id="<?php echo $mnthUniqueId.$mnthButIdx; ?>" ><?php echo $mnth; ?></button><?php
                }                   
                else {
                    ?> <button class="<?php echo $mnthClass; ?>" type="button" id="<?php echo $mnthUniqueId.$mnthButIdx; ?>" value="<?php echo $mnth; ?>" onclick="setClassAndCopyNameUnique('<?php echo $mnthUniqueId;?>', 1, '<?php echo $mnthMaxId;?>', '<?php echo $mnthBtnSelectedClass;?>', '<?php echo $mnthBtnClass;?>', this.value); setMaxDayOfMnth('<?php echo $yrTextBoxId; ?>', '<?php echo $mnthTextBoxId; ?>', '<?php echo $dayOfMnthTextBoxId; ?>', '<?php echo $dayOfMnthUniqueId; ?>', '<?php echo $dayOfMnthBtnSelectedClass; ?>', '<?php echo $dayOfMnthBtnClass; ?>', '<?php echo $outerDivId; ?>', '<?php echo $outerDivClassWarning; ?>');"><?php echo $mnth; ?></button><?php
                }
                $mnthButIdx++;
            } 
            ?>
            <input style="display:none; width:1.0416vw;" id="<?php echo $mnthTextBoxId; ?>" type="text" name="<?php echo $mnthTextBoxName;?>" value="<?php echo $mnthSelected; ?>"  > </input>
        </div>
        <!--     -->
        <?php echo '<div class='.$calYrDiv.'>';
            
            $yrButIdx = 0;
            foreach ($yrsAllAry as $id => $yr) {
                $yrClass = $yrBtnClass;
                if ($yr == $yrSelected) { //if current id is found in the array of items selected
                    $yrClass = $yrBtnSelectedClass; //change button class to 'selected'
                }                   
                if ($viewOnly) {
                    ?> <button class="<?php echo $yrClass; ?>" type="button" id="<?php echo $yrUniqueId.$yrButIdx; ?>" ><?php echo $yr; ?></button><?php
                }                   
                else {
                    ?> <button class="<?php echo $yrClass; ?>" type="button" id="<?php echo $yrUniqueId.$yrButIdx; ?>" value="<?php echo $yr; ?>" onclick="setClassAndCopyNameUnique('<?php echo $yrUniqueId;?>', '<?php echo $baseYr;?>', '<?php echo $yrMaxId;?>', '<?php echo $yrBtnSelectedClass;?>', '<?php echo $yrBtnClass;?>', this.value); setMaxDayOfMnth('<?php echo $yrTextBoxId; ?>', '<?php echo $mnthTextBoxId; ?>', '<?php echo $dayOfMnthTextBoxId; ?>', '<?php echo $dayOfMnthUniqueId; ?>', '<?php echo $dayOfMnthBtnSelectedClass; ?>', '<?php echo $dayOfMnthBtnClass; ?>', '<?php echo $outerDivId; ?>', '<?php echo $outerDivClassWarning; ?>');"><?php echo $yr; ?></button><?php
                }   
                $yrButIdx++;
            } 
            ?>
            <input style="display:none; width:2.3436vw;" id="<?php echo $yrTextBoxId; ?>" type="text" name="<?php echo $yrTextBoxName;?>" value="<?php echo $yrSelected; ?>"  > </input>
        </div>             
    </div>
    <script type="text/javascript"> setMaxDayOfMnth('<?php echo $yrTextBoxId; ?>', '<?php echo $mnthTextBoxId; ?>', '<?php echo $dayOfMnthTextBoxId; ?>', '<?php echo $dayOfMnthUniqueId; ?>', '<?php echo $dayOfMnthBtnSelectedClass; ?>', '<?php echo $dayOfMnthBtnClass; ?>', '<?php echo $outerDivId; ?>', '<?php echo $outerDivClassWarning; ?>'); </script> <!-- sets display of day of month buttons in keeping with initial date values -->
    <?php
}


/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
/* Displays buttons, arranged in 3 adjacent columns, for day of month, month, and year. The buttons can be selected in any order and will update hidden text boxes named $uniqueId."dayOfMonth", $uniqueId."month", and $uniqueId."year" respectively. The $uniqueId can be anything that allows more than one set of calendar buttons to work independently without interacting with each other (as javascript is used to change the text boxes and set/unset buttons). Each column of buttons (dayOfMnth, month, year) are contained in their own div, the 3 divs are contained in an outer div and classed for these divs and the buttons themselves are passed as arguments - the names are self descriptive. The initial date can be set by calling javascript function contained in this function: setDate(recdate) with recdate in the form "2008-07-23". If $viewOnly is TRUE the buttons are set to $setDate but clicking them will have no effect. The days of the month buttons are intelligent in that the max day number changes according to the month selected (sets for initial month too) i.e. Jun = 30, Feb = 28 (unless a leap year in which case = 29). If, say, 31 is selected and then the month is changed to, say, Jun, day 31 is commuted to 30 (max day for Jun) and the button background changed to amber until 30 is clicked to acknowledge or some other button selected. This ensures only valid dates are created and the user is warned if a valid date is as a result of a forced commutation in response to a month change. */
function calJavaScrpInteractn($uniqueId, $viewOnly, $outerDivClass, $outerDivClassWarning, $calDaysOfMnthDiv, $calMnthDiv, $calYrDiv, $dayOfMnthBtnClass, $dayOfMnthBtnSelectedClass, $mnthBtnClass, $mnthBtnSelectedClass, $yrBtnClass, $yrBtnSelectedClass, $filepath, $fileRndm) {
    global $pathToPhpFiles;
    $outerDivId = $uniqueId;
    $dateUniqueId = $uniqueId."-date";
    $recordUniqueId = $uniqueId."-record"; //record in allRecords table
    $dateButUniqueId = $uniqueId."-dateBut"; //row in display page
    //-----
    $dayOfMnthsAllAry = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");
    $dayOfMnthMaxId = sizeof($dayOfMnthsAllAry) -1;
    $dayOfMnthUniqueId = $uniqueId."-dayOfMnth";
    $dayOfMnthTextBoxName = $uniqueId."dayOfMonth";
    $dayOfMnthTextBoxId = $dayOfMnthUniqueId.'textBx';
    //-----
    $mnthsAllAry = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
    $mnthMaxId = sizeof($mnthsAllAry) -1;
    $mnthUniqueId = $uniqueId."-mnth";
    $mnthTextBoxName = $uniqueId."month";
    $mnthTextBoxId = $mnthUniqueId.'textBx';
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
    //-----
    $yrsAllAry = array("2009", "2010", "2011", "2012", "2013", "2014", "2015", "2016", "2017", "2018", "2019");
    $baseYr = $yrsAllAry[0];
    $yrMaxId = sizeof($yrsAllAry) -1;
    $yrUniqueId = $uniqueId."-yr";
    $yrTextBoxName = $uniqueId."year";
    $yrTextBoxId = $yrUniqueId.'textBx';
    echo '<div class='.$outerDivClass.' id='.$outerDivId.' style="display:none;">' ;
        //-----
        echo '<div class='.$calDaysOfMnthDiv.'>';            
            $dayOfMnthButIdx = 0;
            foreach ($dayOfMnthsAllAry as $idDom => $dayOfMnth) {
                $dayOfMnthClass = $dayOfMnthBtnClass;
                if ($viewOnly) {
                    ?> <button
                            class="<?php echo $dayOfMnthClass;?>"
                            type="button"
                            id="<?php echo $dayOfMnthUniqueId.$dayOfMnthButIdx;?>"
                        >
                        <?php echo $dayOfMnth;?>
                        </button><?php
                }
                else {
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
                    ?> <button
                            class="<?php echo $dayOfMnthClass;?>"
                            type="button"
                            id="<?php echo $dayOfMnthUniqueId.$dayOfMnthButIdx;?>"
                            value="<?php echo $dayOfMnth;?>"
                            onclick="setClassAndCopyNameUnique(
                                        '<?php echo $dayOfMnthUniqueId;?>',
                                        1,
                                        '<?php echo $dayOfMnthMaxId;?>',
                                        '<?php echo $dayOfMnthBtnSelectedClass;?>',
                                        '<?php echo $dayOfMnthBtnClass;?>',
                                        this.value
                                    );
                                    setMaxDayOfMnth(
                                        '<?php echo $yrTextBoxId;?>',
                                        '<?php echo $mnthTextBoxId;?>',
                                        '<?php echo $dayOfMnthTextBoxId;?>',
                                        '<?php echo $dayOfMnthUniqueId; ?>',
                                        '<?php echo $dayOfMnthBtnSelectedClass;?>',
                                        '<?php echo $dayOfMnthBtnClass;?>',
                                        '<?php echo $outerDivId;?>',
                                        '<?php echo $outerDivClassWarning;?>',
                                        '<?php echo $dateUniqueId;?>'
                                    );
                                    forceClass(
                                        '<?php echo $outerDivId;?>',
                                        '<?php echo $outerDivClass;?>'
                                    );
                                    updateRecordsDateAndButton(
                                        'allRecords',
                                        'recordDate',
                                        '<?php echo $dateUniqueId;?>',
                                        'idR',
                                        '<?php echo $recordUniqueId;?>',
                                        '<?php echo $dateButUniqueId;?>',
                                        '<?php echo $filepath;?>',
                                        '<?php echo $fileRndm;?>'
                                    )"
                        >
                        <?php echo $dayOfMnth;?>
                        </button><?php
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
                }
                $dayOfMnthButIdx++;
            }  
            ?>
            <input style="display:none; width:1.0416vw;" id="<?php echo $dayOfMnthTextBoxId; ?>" type="text" name="<?php echo $dayOfMnthTextBoxName;?>" value="<?php echo $dayOfMnthSelected; ?>"  > </input>
        </div>
        <!--     -->
        <?php echo '<div class='.$calMnthDiv.'>';
            $mnthButIdx = 0;
            foreach ($mnthsAllAry as $idM => $mnth) {
                $mnthClass = $mnthBtnClass;
                if ($viewOnly) {
                    ?> <button
                            class="<?php echo $mnthClass;?>"
                            type="button"
                            id="<?php echo $mnthUniqueId.$mnthButIdx;?>"
                        >
                        <?php echo $mnth;?>
                        </button><?php
                }                   
                else {
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
                    ?> <button
                            class="<?php echo $mnthClass;?>"
                            type="button"
                            id="<?php echo $mnthUniqueId.$mnthButIdx;?>"
                            value="<?php echo $mnth;?>"
                            onclick="setClassAndCopyNameUnique('<?php echo $mnthUniqueId;?>',
                                        1,
                                        '<?php echo $mnthMaxId;?>',
                                        '<?php echo $mnthBtnSelectedClass;?>',
                                        '<?php echo $mnthBtnClass;?>',
                                        this.value
                                    );
                                    setMaxDayOfMnth(
                                        '<?php echo $yrTextBoxId;?>',
                                        '<?php echo $mnthTextBoxId;?>',
                                        '<?php echo $dayOfMnthTextBoxId;?>',
                                        '<?php echo $dayOfMnthUniqueId;?>',
                                        '<?php echo $dayOfMnthBtnSelectedClass;?>',
                                        '<?php echo $dayOfMnthBtnClass;?>',
                                        '<?php echo $outerDivId;?>',
                                        '<?php echo $outerDivClassWarning;?>',
                                        '<?php echo $dateUniqueId;?>'
                                    );
                                    updateRecordsDateAndButton(
                                        'allRecords',
                                        'recordDate',
                                        '<?php echo $dateUniqueId;?>',
                                        'idR',
                                        '<?php echo $recordUniqueId;?>',
                                        '<?php echo $dateButUniqueId;?>',
                                        '<?php echo $filepath;?>',
                                        '<?php echo $fileRndm;?>'
                                    )"
                        >
                        <?php echo $mnth;?>
                        </button><?php
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
                }
                $mnthButIdx++;
            } 
            ?>
            <input style="display:none; width:1.0416vw;" id="<?php echo $mnthTextBoxId; ?>" type="text" name="<?php echo $mnthTextBoxName;?>" value="<?php echo $mnthSelected; ?>"  > </input>
        </div>
        <!--     -->
        <?php echo '<div class='.$calYrDiv.'>';
            
            $yrButIdx = 0;
            foreach ($yrsAllAry as $idY => $yr) {
                $yrClass = $yrBtnClass;
                if ($viewOnly) {
                    ?> <button
                            class="<?php echo $yrClass?>"
                            type="button"
                            id="<?php echo $yrUniqueId.$yrButIdx;?>"
                        >
                        <?php echo $yr;?>
                        </button><?php
                }                   
                else {
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
                    ?> <button
                            class="<?php echo $yrClass;?>"
                            type="button"
                            id="<?php echo $yrUniqueId.$yrButIdx;?>"
                            value="<?php echo $yr; ?>"
                            onclick="setClassAndCopyNameUnique(
                                        '<?php echo $yrUniqueId;?>',
                                        '<?php echo $baseYr;?>',
                                        '<?php echo $yrMaxId;?>',
                                        '<?php echo $yrBtnSelectedClass;?>',
                                        '<?php echo $yrBtnClass;?>',
                                        this.value
                                    );
                                    setMaxDayOfMnth(
                                        '<?php echo $yrTextBoxId;?>',
                                        '<?php echo $mnthTextBoxId;?>',
                                        '<?php echo $dayOfMnthTextBoxId;?>',
                                        '<?php echo $dayOfMnthUniqueId;?>',
                                        '<?php echo $dayOfMnthBtnSelectedClass;?>',
                                        '<?php echo $dayOfMnthBtnClass;?>',
                                        '<?php echo $outerDivId;?>',
                                        '<?php echo $outerDivClassWarning;?>',
                                        '<?php echo $dateUniqueId;?>'
                                    );
                                    updateRecordsDateAndButton(
                                        'allRecords',
                                        'recordDate',
                                        '<?php echo $dateUniqueId;?>',
                                        'idR',
                                        '<?php echo $recordUniqueId;?>',
                                        '<?php echo $dateButUniqueId;?>',
                                        '<?php echo $filepath;?>',
                                        '<?php echo $fileRndm;?>'
                                    )"
                        >
                        <?php echo $yr; ?>
                        </button><?php
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
                }   
                $yrButIdx++;
            } 
            ?>
            <input style="display:none; width:2.3436vw;" id="<?php echo $yrTextBoxId; ?>" type="text" name="<?php echo $yrTextBoxName;?>" value="<?php echo $yrSelected; ?>"  > </input>
        </div>             
    </div>
    <input hidden id="<?php echo $dateUniqueId;?>" type="text"></input>
    <input hidden id="<?php echo $recordUniqueId;?>" type="text"></input>
    <input hidden id="<?php echo $dateButUniqueId;?>" type="text"></input>
    <script type="text/javascript"> 
        function setDateCal(recdate, recIdR, butId) { 
        	document.getElementById('<?php echo $recordUniqueId;?>').value = recIdR; //save record idR to hidden textbox for use in changing record after different date has been selected
            document.getElementById('<?php echo $dateButUniqueId;?>').value = butId; //save id of selected display row to hidden textbox for use in changing record after different date has been selected
        	var recDateAry = recdate.split("-"); //convert date csv string into array to extract year, month, and day of month
            setClassAndCopyNameUnique(
                '<?php echo $dayOfMnthUniqueId;?>',
                1,
                '<?php echo $dayOfMnthMaxId;?>',
                '<?php echo $dayOfMnthBtnSelectedClass;?>',
                '<?php echo $dayOfMnthBtnClass;?>',
                recDateAry[2]
            );
            setClassAndCopyNameUnique(
                '<?php echo $mnthUniqueId;?>',
                1,
                '<?php echo $mnthMaxId;?>',
                '<?php echo $mnthBtnSelectedClass;?>',
                '<?php echo $mnthBtnClass;?>',
                recDateAry[1]
            );
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
            setClassAndCopyNameUnique(
                '<?php echo $yrUniqueId;?>',
                '<?php echo $baseYr;?>',
                '<?php echo $yrMaxId;?>',
                '<?php echo $yrBtnSelectedClass;?>',
                '<?php echo $yrBtnClass;?>',
                recDateAry[0]
            );
            setMaxDayOfMnth(
                '<?php echo $yrTextBoxId;?>',
                '<?php echo $mnthTextBoxId;?>',
                '<?php echo $dayOfMnthTextBoxId;?>',
                '<?php echo $dayOfMnthUniqueId;?>',
                '<?php echo $dayOfMnthBtnSelectedClass;?>',
                '<?php echo $dayOfMnthBtnClass;?>',
                '<?php echo $outerDivId;?>',
                '<?php echo $outerDivClassWarning;?>',
                '<?php echo $dateUniqueId;?>'
            );
        }
    </script> 
    <?php
}




/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
/* Creates a scrolling panel of buttons that can be used to choose an item (such as budget) that when clicked will update an element that is referenced by formValHolder($itemButUniqueId) with the item key and string, and at the same time will update a field pointed to be formValHolder($recFieldUniqueId) (probably a random number equivelent) in a table pointed to by $recTableName (probably a random number equivelent) in the row where $whereField matches formValHolder($recordUniqueId) with just the item key. An item add button is also provided, the operation of which can be deduced from the descriptions of the passed arguments. Initially the outer div is set to display:none so the button panel will be invisible unless it is specifically set to display:inline by javascript. */
function butPanelJavaScrpInteractn(
    $uniqueId,          //id to provide target info and distinguish this from other instances of this button panel (for javascript interaction with panel elements and displaying/hiding outer div)
    $viewOnly,          //TRUE = view only, no clicking or editing. FALSE = fully functional
    $outerDivClass,     //class of containing div that acts as a container for the buttons
    $btnClass,          //class for all unselected ordinary buttons
    $btnSelectedClass,  //class for the the selected button - shows which button has been clicked
    $itemAry,           //array of items used to create the column of buttons: array(3=>"dog", 7=>"puppy", 1=>"tail") - the array keys set the values of each button, the corresponding stings are displayed
    $recTableName,      //the name of the table of records (probably a random number equivelent) that any keyclicks are to update
    $whereField,        //the name of the table field that is to be matched with formValHolder($recordUniqueId) to decide which row of the table is to be updated
    $filepath,          //the path/name of the php file that is a target (usually index.php)
    $fileRndm,          //the filename random that will be interpreted probably by index.php as the actual php file that will write to the database table
    $addButName,        //the filename random that will be interpreted probably by index.php as the actual php file that will present an interface to add new items to the table that $itemAry is from
    $addButVal,         //the filename random that will be interpreted probably by index.php as the php page this function is used in - so it can be returned to once the new item has been added
    $addButClass,       //class of the add button (which will be at the top) to make it distinct from the other item select buttons
    $addTableName,      //the name of the table of items (probably a random number equivelent) that will be added to
    $addField)          //the name of the table field (probably a random number equivelent) to which the new item will be inserted
    {
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
    global $pathToPhpFiles;
    $recordUniqueId = $uniqueId."recordIdPlaceholder"; //record in allRecords table
    $recFieldUniqueId = $uniqueId."recFieldIdPlaceholder"; //row in display page
    $itemButUniqueId = $uniqueId."itemButIdPlaceholder"; //row in display page
    $itemKeysAry = array();
    foreach($itemAry as $key => $item) {
        $itemKeysAry[] = $key;
    }
    $itemKeysCsv = implode(',', $itemKeysAry);
    $itemUniqueId = $uniqueId."itemId";
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
    echo '<div class='.$outerDivClass.' id='.$uniqueId.' style="display:none;">' ;
        ?>
        <form style="float:left;" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
        <?php
		if (($addButName) && !($viewOnly)) { //only show 'ADD' button if name is provided and viewOnly is set to FALSE
	        echo '<button class='.$addButClass.' type="submit" name='.$addButName.' value='.$addButVal.'>ADD</button>';
	    }
        formValHolder("tableName", $addTableName);
        formValHolder("fieldName", $addField);
        ?> </form> <?php
        $index = 0;
        foreach ($itemAry as $key => $itemStr) {
            $yrClass = $btnClass;
            if ($viewOnly) {
                ?> <button class="<?php echo $btnClass; ?>" type="button" id="<?php echo $itemUniqueId.$index; ?>" ><?php echo $yr; ?></button><?php
            }                   
            else {
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
                ?> <button  
                        class="<?php echo $btnClass;?>"
                        type="button"
                        id="<?php echo $itemUniqueId.$index;?>"
                        value="<?php echo $key;?>" 
                        onclick="setOneClassUnsetRest(
                                    '<?php echo $itemUniqueId;?>',
                                    '<?php echo $btnSelectedClass;?>',
                                    '<?php echo $btnClass;?>',
                                    '<?php echo $itemKeysCsv;?>',
                                    this.value,
                                );
                                updateRecordsAndButton(
                                    '<?php echo $recTableName;?>',
                                    '<?php echo $recFieldUniqueId;?>',
                                    '<?php echo $key;?>',
                                    '<?php echo $itemStr;?>',
                                    '<?php echo $whereField;?>',
                                    '<?php echo $recordUniqueId;?>',
                                    '<?php echo $itemButUniqueId;?>',
                                    '<?php echo $filepath;?>',
                                    '<?php echo $fileRndm;?>'
                                )"
                    >
                    <?php echo $itemStr;?>
                    </button><?php
            }  
            $index++; 
        } 
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
        ?>                      
    </div>
    <?php
    formValHolder($recordUniqueId);   //holds the id of the record (row number) that has been selected when the editing element was clicked
    formValHolder($recFieldUniqueId); //holds the name of the record field to be updated when an item button is clicked
    formValHolder($itemButUniqueId);  //holds the element id that is also to be updated when an item button is clicked
    return $itemKeysCsv;
}


/* Displays buttons, arranged in 3 adjacent columns, for day of month, month, and year. The buttons can be selected in any order and will update hidden text boxes named $uniqueId."dayOfMonth", $uniqueId."month", and $uniqueId."year" respectively. The $uniqueId can be anything that allows more than one set of calendar buttons to work independently without interacting with each other (as javascript is used to change the text boxes and set/unset buttons). Each column of buttons (dayOfMnth, month, year) are contained in their own div, the 3 divs are contained in an outer div and classed for these divs and the buttons themselves are passed as arguments - the names are self descriptive. The initial date can be set by calling javascript function contained in this function: setDate(recdate) with recdate in the form "2008-07-23". If $viewOnly is TRUE the buttons are set to $setDate but clicking them will have no effect. The days of the month buttons are intelligent in that the max day number changes according to the month selected (sets for initial month too) i.e. Jun = 30, Feb = 28 (unless a leap year in which case = 29). If, say, 31 is selected and then the month is changed to, say, Jun, day 31 is commuted to 30 (max day for Jun) and the button background changed to amber until 30 is clicked to acknowledge or some other button selected. This ensures only valid dates are created and the user is warned if a valid date is as a result of a forced commutation in response to a month change. */
function calJavaScrpInteractnLite($uniqueId, $viewOnly, $outerDivClass, $outerDivClassWarning, $calDaysOfMnthDiv, $calMnthDiv, $calYrDiv, $dayOfMnthBtnClass, $dayOfMnthBtnSelectedClass, $mnthBtnClass, $mnthBtnSelectedClass, $yrBtnClass, $yrBtnSelectedClass, $filepath, $fileRndm, $cellClassWarn, $cellClassEdit, $recoveredSessionAryCommitRnd) {
    global $pathToPhpFiles;
    $outerDivId = $uniqueId;
    $dateUniqueId = $uniqueId."-date";
    $recordUniqueId = $uniqueId."-record"; //record in allRecords table
    $dateCellUniqueId = $uniqueId."-dateCell"; //row in display page
    $miniButId = $uniqueId."miniBut"; //id of button that is zero dimensions that is set to be in focus on in the cal panel so return works
    $clickDown = $uniqueId."clickDown"; //name of JS variable that controls whether auto update on day of month button click is operational
    //-----
    $dayOfMnthsAllAry = array("1", "2", "3", "4", "5", "6", "7", "8", "9", "10", "11", "12", "13", "14", "15", "16", "17", "18", "19", "20", "21", "22", "23", "24", "25", "26", "27", "28", "29", "30", "31");
    $dayOfMnthMaxId = sizeof($dayOfMnthsAllAry) -1;
    $dayOfMnthUniqueId = $uniqueId."-dayOfMnth";
    $dayOfMnthTextBoxName = $uniqueId."dayOfMonth";
    $dayOfMnthTextBoxId = $dayOfMnthUniqueId.'textBx';
    //-----
    $mnthsAllAry = array("01", "02", "03", "04", "05", "06", "07", "08", "09", "10", "11", "12");
    $mnthMaxId = sizeof($mnthsAllAry) -1;
    $mnthUniqueId = $uniqueId."-mnth";
    $mnthTextBoxName = $uniqueId."month";
    $mnthTextBoxId = $mnthUniqueId.'textBx';
    //-----
    $yrsAllAry = array("2000", "2001", "2002", "2003", "2004", "2005", "2006", "2007", "2008", "2009", "2010", "2011", "2012", "2013", "2014", "2015", "2016", "2017", "2018", "2019", "2020", "2021");
    $baseYr = $yrsAllAry[0];
    $yrMaxId = sizeof($yrsAllAry) -1;
    $yrUniqueId = $uniqueId."-yr";
    $yrTextBoxName = $uniqueId."year";
    $yrTextBoxId = $yrUniqueId.'textBx';
    echo '<div class='.$outerDivClass.' id='.$outerDivId.' style="display:none;">' ;
        //-----
        echo '<div class='.$calDaysOfMnthDiv.'>';            
            $dayOfMnthButIdx = 0;
            foreach ($dayOfMnthsAllAry as $dayOfMnthStr) { //DAYS OF MONTH LOOP
                $dayOfMnthClass = $dayOfMnthBtnClass;
                if ($viewOnly) {
                    ?> <button
                            class="<?php echo $dayOfMnthClass;?>"
                            type="button"
                            id="<?php echo $dayOfMnthUniqueId.$dayOfMnthButIdx;?>"
                        >
                        <?php echo $dayOfMnthStr;?>
                        </button><?php
                }
                else {
                    ?> <button
                            class="<?php echo $dayOfMnthClass;?>"
                            type="button"
                            id="<?php echo $dayOfMnthUniqueId.$dayOfMnthButIdx;?>"
                            value="<?php echo $dayOfMnthStr;?>"
                            onclick="
                                if (notHiddenCompound()) { //only run this function to update the tables on the server as long as the row being edited hasn't been set to hidden (but temporarily visible)
                                    setClassAndCopyNameUnique(
                                        '<?php echo $dayOfMnthUniqueId;?>',
                                        1,
                                        '<?php echo $dayOfMnthMaxId;?>',
                                        '<?php echo $dayOfMnthBtnSelectedClass;?>',
                                        '<?php echo $dayOfMnthBtnClass;?>',
                                        this.value
                                    );
                                    <?php echo $uniqueId;?>setMaxDom();
                                    forceClass( //switches off the warning class for the outer div
                                        '<?php echo $outerDivId;?>',
                                        '<?php echo $outerDivClass;?>'
                                    );
                                    <?php echo $uniqueId;?>updateRecsAndCellAutoDayOfMonth();
                                }
                                else {
                                    messageChangeInhibited();
                                }
                            "
                        >
                        <?php echo $dayOfMnthStr;?>
                        </button><?php
                }
                $dayOfMnthButIdx++;
            }  
            ?>
            <button class="calDaysOfMnthButHidden" type="button" id="<?php echo $miniButId; ?>"></button> <!-- button that is zero dimensions that is set to be in focus on in the cal panel so return works -->
            <input style="display:none; width:1.0416vw;" id="<?php echo $dayOfMnthTextBoxId; ?>" type="text" name="<?php echo $dayOfMnthTextBoxName;?>" value="<?php echo $dayOfMnthSelected; ?>"  > </input>
        </div>
        <!--     -->
        <?php echo '<div class='.$calMnthDiv.'>';
            $mnthButIdx = 0;
            foreach ($mnthsAllAry as $mnthStr) { //MONTH LOOP
                $mnthClass = $mnthBtnClass;
                if ($viewOnly) {
                    ?> <button
                            class="<?php echo $mnthClass;?>"
                            type="button"
                            id="<?php echo $mnthUniqueId.$mnthButIdx;?>"
                        >
                        <?php echo $mnthStr;?>
                        </button><?php
                }                   
                else {
                    ?> <button
                            class="<?php echo $mnthClass;?>"
                            type="button"
                            id="<?php echo $mnthUniqueId.$mnthButIdx;?>"
                            value="<?php echo $mnthStr;?>"
                            onclick="{
                                if (notHiddenCompound()) { //only run this function to update the tables on the server as long as the row being edited hasn't been set to hidden (but temporarily visible)
                                    setClassAndCopyNameUnique( //sets the clicked panel button to selected class and all others to unselected. Also copies selected date (i.e. 3) to month text box
                                        '<?php echo $mnthUniqueId;?>',
                                        1,
                                        '<?php echo $mnthMaxId;?>',
                                        '<?php echo $mnthBtnSelectedClass;?>',
                                        '<?php echo $mnthBtnClass;?>',
                                        this.value
                                    );
                                    <?php echo $uniqueId;?>setMaxDom(); //hides days of month higher than possible (i.e. 30 Feb!) and if one was previously set commutes it to next legal lower day and highlights cal in orange, also copies aggregate of day of month, month and year hidden text boxes to dateUniqueId text box as whole date: 2018-03-4                                   
                                    <?php echo $uniqueId;?>updateRecsAndCell(); //updates recordDate pointed to by recordUniqueId (row) and also updates the original selected element in the row an dcolumn display
                                }
                                else {
                                    messageChangeInhibited();
                                }
                            }
                            "
                        >
                        <?php echo $mnthStr;?>
                        </button><?php
                }
                $mnthButIdx++;
            } 
            ?>
            <input style="display:none; width:1.0416vw;" id="<?php echo $mnthTextBoxId; ?>" type="text" name="<?php echo $mnthTextBoxName;?>" value="<?php echo $mnthSelected; ?>"  > </input>
        </div>
        <!--     -->
        <?php echo '<div class='.$calYrDiv.'>';    
            $yrButIdx = 0;
            foreach ($yrsAllAry as $yearStr) { //YEARS LOOP
                $yrClass = $yrBtnClass;
                if ($viewOnly) {
                    ?> <button
                            class="<?php echo $yrClass?>"
                            type="button"
                            id="<?php echo $yrUniqueId.$yrButIdx;?>"
                        >
                        <?php echo $yearStr;?>
                        </button><?php
                }                   
                else {
                    ?> <button
                            class="<?php echo $yrClass;?>"
                            type="button"
                            id="<?php echo $yrUniqueId.$yrButIdx;?>"
                            value="<?php echo $yearStr; ?>"
                            onclick="
                                if (notHiddenCompound()) { //only run this function to update the tables on the server as long as the row being edited hasn't been set to hidden (but temporarily visible)
                                    setClassAndCopyNameUnique(
                                        '<?php echo $yrUniqueId;?>',
                                        '<?php echo $baseYr;?>',
                                        '<?php echo $yrMaxId;?>',
                                        '<?php echo $yrBtnSelectedClass;?>',
                                        '<?php echo $yrBtnClass;?>',
                                        this.value
                                    );
                                    <?php echo $uniqueId;?>setMaxDom();
                                    <?php echo $uniqueId;?>updateRecsAndCell();
                                }
                                else {
                                    messageChangeInhibited();
                                }
                            "
                        >
                        <?php echo $yearStr; ?>
                        </button><?php
                }   
                $yrButIdx++;
            } 
            ?>
            <input style="display:none; width:2.3436vw;" id="<?php echo $yrTextBoxId; ?>" type="text" name="<?php echo $yrTextBoxName;?>" value="<?php echo $yrSelected; ?>"  > </input>
        </div>             
    </div>
    <input hidden id="<?php echo $dateUniqueId;?>" type="text"></input>
    <input hidden id="<?php echo $recordUniqueId;?>" type="text"></input>
    <input hidden id="<?php echo $dateCellUniqueId;?>" type="text"></input>
    <script type="text/javascript"> 
        function <?php echo $uniqueId;?>getFocusBack() { //called from toggleClickDown() to remove focus from the auto button and return it to the minimulist button
            document.getElementById('<?php echo $miniButId;?>').focus(); //focuses on the minimulist button
        }
        function <?php echo $uniqueId;?>initButPanel(cellId, show, editAllowed) { //uses cellId to get cell inner text, splits into date parts and compares them with each panel button text for each column to set the matching ones to 'selected' and others to 'not selected'
            START("initButPanel() - in calJavaScrpInteractnLite()");
            document.getElementById('<?php echo $miniButId;?>').focus(); //focuses on the minimulist button
            var domMaxId = <?php echo $dayOfMnthMaxId;?>; //maximum day of month panel button id (they start at 0)
            var mnthMaxId = <?php echo $mnthMaxId;?>; //maximum month panel button id (they start at 0)
            var yearMaxId = <?php echo $yrMaxId;?>; //maximum panel year button id (they start at 0)
            var idDomPrefix = '<?php echo $dayOfMnthUniqueId;?>'; //unique prefix (derived in php code above) to be used to distinguish these buttons from those in other instances of button panel
            var idMnthPrefix = '<?php echo $mnthUniqueId;?>'; 
            var idYearPrefix = '<?php echo $yrUniqueId;?>'; 
            var cellStr = inrGet(cellId); //the text in the cell that initiated the call to this button panel
            var cellStrAry = cellStr.split("-");
            var dayOfMnthInt = Number(cellStrAry[0]);
            var mnthInt = Number(cellStrAry[1]);
            var yearInt = Number(cellStrAry[2]);
            document.getElementById('<?php echo $dayOfMnthTextBoxId;?>').value = dayOfMnthInt;
            document.getElementById('<?php echo $mnthTextBoxId;?>').value = mnthInt;
            document.getElementById('<?php echo $yrTextBoxId;?>').value = yearInt;

            document.getElementById('<?php echo $recordUniqueId;?>').value = cellId.split("-")[0]; //save record idR to hidden textbox for use in changing record after different date has been selected
            document.getElementById('<?php echo $dateCellUniqueId;?>').value = cellId; //save id of selected display row to hidden textbox for use in changing record after different date has been selected
            
            for (i = 0; i <= domMaxId; i++) { //loop through all the panel button ids
                var domButInt = Number(inrGet(idDomPrefix+i)); //for each iteration of the loop get the text from the current button  
                if (dayOfMnthInt == domButInt) { //if the calling cell text matches the current loop button set that button to 'selected' by changing the class
                    document.getElementById(idDomPrefix+i).className = '<?php echo $dayOfMnthBtnSelectedClass;?>';
                    //document.getElementById(idDomPrefix+i).focus(); //focuses on the selected button
                }
                else { //if the calling cell text doesn't match the current loop button set that button to 'not selected' by changing the class
                    document.getElementById(idDomPrefix+i).className = '<?php echo $dayOfMnthBtnClass;?>';
                }
            }

            for (i = 0; i <= mnthMaxId; i++) { //loop through all the panel button ids
                var mnthButInt = Number(inrGet(idMnthPrefix+i)); //for each iteration of the loop get the text from the current button  
                if (mnthInt == mnthButInt) { //if the calling cell text matches the current loop button set that button to 'selected' by changing the class
                    document.getElementById(idMnthPrefix+i).className = '<?php echo $mnthBtnSelectedClass;?>';
                }
                else { //if the calling cell text doesn't match the current loop button set that button to 'not selected' by changing the class
                    document.getElementById(idMnthPrefix+i).className = '<?php echo $mnthBtnClass;?>';
                }
            }

            for (i = 0; i <= yearMaxId; i++) { //loop through all the panel button ids
                var yearButStr = Number(inrGet(idYearPrefix+i)); //for each iteration of the loop get the text from the current button  
                if (yearInt == yearButStr) { //if the calling cell text matches the current loop button set that button to 'selected' by changing the class
                    document.getElementById(idYearPrefix+i).className = '<?php echo $yrBtnSelectedClass;?>';
                }
                else { //if the calling cell text doesn't match the current loop button set that button to 'not selected' by changing the class
                    document.getElementById(idYearPrefix+i).className = '<?php echo $yrBtnClass;?>';
                }
            }
            <?php echo $uniqueId;?>setMaxDom();
            FINISH("initButPanel() - in calJavaScrpInteractnLite()");
        }
        function <?php echo $uniqueId;?>updateRecsAndCell() {
            var newDateValue = document.getElementById('<?php echo $dateUniqueId;?>').value; //get date in 2018-08-23 format
            var cellId = document.getElementById('<?php echo $dateCellUniqueId;?>').value; //gets cell to be changed id in "253-7" format
            ajaxRecordsDateAndCellUpdate(
                newDateValue,
                cellId,
                '<?php echo $filepath;?>',
                '<?php echo $fileRndm;?>',
                '<?php echo $cellClassWarn;?>'
            )
        }
        function <?php echo $uniqueId;?>updateRecsAndCellAutoDayOfMonth() {
            var newDateValue = document.getElementById('<?php echo $dateUniqueId;?>').value; //get date in 2018-08-23 format
            var cellId = document.getElementById('<?php echo $dateCellUniqueId;?>').value; //gets cell to be changed id in "253-7" format
                clickCellBelow(valGet("seltdRowCellId"), idrAry, "From Buttons"); //selects same cell in row below the current one, same as clicking by the mouse - this function may be internally allowed / inhibited as required
            ajaxRecordsDateAndCellUpdate(
                newDateValue,
                cellId,
                '<?php echo $filepath;?>',
                '<?php echo $fileRndm;?>',
                '<?php echo $cellClassWarn;?>'
            )
        }
        function <?php echo $uniqueId;?>setMaxDom() {
            setMaxDayOfMnth(
                '<?php echo $yrTextBoxId;?>',
                '<?php echo $mnthTextBoxId;?>',
                '<?php echo $dayOfMnthTextBoxId;?>',
                '<?php echo $dayOfMnthUniqueId;?>',
                '<?php echo $dayOfMnthBtnSelectedClass;?>',
                '<?php echo $dayOfMnthBtnClass;?>',
                '<?php echo $outerDivId;?>',
                '<?php echo $outerDivClassWarning;?>', //NOT SURE IF STILL USED IN THE FUNCTION
                '<?php echo $dateUniqueId;?>'
            );
        }
    </script> 
    <?php
}


/* Creates a fixed empty panel to be displayed and take up the appropriate space in the display area when active panels like calendar or item selection panel of buttons are not needed.  Initially the outer div is set to display:none so the panel will be invisible unless it is specifically set to display:inline by javascript. Large text is displayed in this panel - "Key In Data Directly". */
function butPanelJSdummy(
    $uniqueId,          //id to provide target info and distinguish this from other instances of this button panel (for javascript interaction with panel elements)
    $outerDivClass)      //class of containing div that acts as a container for the buttons
    {
    echo '<div class='.$outerDivClass.' id='.$uniqueId.' style="display:none;">';
    echo 'Key In Data Directly';
    echo '</div>';
    ?><script> //dummy function no longer needed as it now isn't called by JS selectButPanel()
        function <?php echo $uniqueId;?>initButPanel(cellId, show, editAllowed) {
            START("initButPanel() - in butPanelJSdummy()");
            //dummy function - here so initilisatioin call from selectButPanel() doesn't cause things to hang!
            FINISH("initButPanel() - in butPanelJSdummy()");
        }
    </script><?php
}

/* Creates a fixed empty panel to be displayed and take up the appropriate space in the display area when in no edit mode.  Initially the outer div is set to display:none so the panel will be invisible unless it is specifically set to display:inline by javascript. A minimulist (invisible but not hidden) button is focused on initialisation to allow return key clickDown to operate when in no edit mode. */
function butPanelJSNoEdit(
    $uniqueId,          //id to provide target info and distinguish this from other instances of this button panel (for javascript interaction with panel elements)
    $outerDivClass)      //class of containing div that acts as a container for the buttons
    {
    $miniButId = $uniqueId."miniBut";
    echo '<div class='.$outerDivClass.' id='.$uniqueId.' style="display:none;">';
    ?>
        <button class="calDaysOfMnthButHidden" type="button" id="<?php echo $miniButId; ?>"></button> <!-- button that is zero dimensions that is set to be in focus on in the cal panel so return works -->
    <?php
    echo '</div>';
    ?><script> //dummy function no longer needed as it now isn't called by JS selectButPanel()
        function <?php echo $uniqueId;?>initButPanel(cellId, show, editAllowed) {
            START("initButPanel() - in butPanelJSNoEdit()");
            //dummy function - here so initilisatioin call from selectButPanel() doesn't cause things to hang!
            document.getElementById('<?php echo $miniButId;?>').focus(); //focuses on the minimulist button (to enable return key clickDown when in no edit mode)
            FINISH("initButPanel() - in butPanelJSNoEdit()");
        }
    </script><?php
}

/* Initially the outer div is set to display:none so the panel will be invisible unless it is specifically set to display:inline by javascript. */
function subButPanelJSreconcile(
    $uniqueId,          //id to provide target info and distinguish this from other instances of this button panel (for javascript interaction with panel elements)
    $outerDivClass,      //class of containing div that acts as a container for the buttons
    $butClass)
    {
    ?>
    <div class=<?php echo $outerDivClass;?>  id=<?php echo $uniqueId;?>> <!-- submenu outer container to hold reconciliation setup buttons  -->
        <button class=<?php echo $butClass;?> type="button" onclick="atomicCall('Earlier Statement')"><i class="fas fa-arrow-up"></i></button>
        <button class=<?php echo $butClass;?> type="button" onclick="atomicCall('Later Statement')"><i class="fas fa-arrow-down"></i></button>     
        <button class=<?php echo $butClass;?> type="button" onclick="atomicCall('Reset accWorkedOn')"><i class="fas fa-trash"></i></button>
    </div>
    <script> //dummy function no longer needed as it now isn't called by JS selectButPanel()
        function <?php echo $uniqueId;?>initButPanel(cellId, show, editAllowed) {
            START("initButPanel() - in subButPanelJSreconcile()");
            //dummy function - here so initilisatioin call from selectButPanel() doesn't cause things to hang!
            FINISH("initButPanel() - in subButPanelJSreconcile()");
        }
    </script>
    <?php
}

/* Initially the outer div is set to display:none so the panel will be invisible unless it is specifically set to display:inline by javascript. */
function subButPanelJSclickDown(
    $uniqueId,          //id to provide target info and distinguish this from other instances of this button panel (for javascript interaction with panel elements)
    $outerDivClass,      //class of containing div that acts as a container for the buttons
    $butClass,
    $butClassSelected)
    {
    ?>
    <div class=<?php echo $outerDivClass;?>  id=<?php echo $uniqueId;?>> <!-- submenu outer container to hold reconciliation setup buttons  -->
        <button class=<?php echo $butClass;?> type="button" id=<?php echo 'but1'.$uniqueId;?> onclick="toggleClickDown()"><i class="fas fa-magic"></i> Auto</button>
    </div>
    <script> //dummy function no longer needed as it now isn't called by JS selectButPanel()
        function <?php echo $uniqueId;?>initButPanel(cellId, show, editAllowed) {
            START("initButPanel() - in subButPanelJSclickDown()");
            //dummy function - here so initilisation call from selectButPanel() doesn't cause things to hang!
            FINISH("initButPanel() - in subButPanelJSclickDown()");
        }
        var but1Id = <?php echo json_encode('but1'.$uniqueId);?>;
        var unselectedClass = <?php echo json_encode($butClass);?>;
        var selectedClass = <?php echo json_encode($butClassSelected);?>;

        function <?php echo $uniqueId;?>changeButClass(selectedStatus) {
            if (selectedStatus == "Selected") {
                document.getElementById(but1Id).className = selectedClass;
            }
            else {
                document.getElementById(but1Id).className = unselectedClass;
            }
        }
    </script>
    <?php
}

/* Initially the outer div is set to display:none so the panel will be invisible unless it is specifically set to display:inline by javascript. */
function subButPanelJSDummy(
    $uniqueId,          //id to provide target info and distinguish this from other instances of this button panel (for javascript interaction with panel elements)
    $outerDivClass)      //class of containing div that acts as a container for the buttons
    {
    ?>
    <div class=<?php echo $outerDivClass;?>  id=<?php echo $uniqueId;?>> <!-- submenu outer container to hold reconciliation setup buttons  -->
    </div>
    <script> //dummy function no longer needed as it now isn't called by JS selectButPanel()
        function <?php echo $uniqueId;?>initButPanel(cellId, show, editAllowed) {
            START("initButPanel() - in subButPanelJSDummy()");
            //dummy function - here so initilisatioin call from selectButPanel() doesn't cause things to hang!
            FINISH("initButPanel() - in subButPanelJSDummy()");
        }
    </script>
    <?php
}


/* Creates a scrolling panel of buttons that can be used to choose an item (such as budget) that when clicked will update an element that is referenced by formValHolder($itemButUniqueId) with the item key and string, and at the same time will update a field pointed to by formValHolder($recFieldUniqueId) (probably a random number equivelent) in a table pointed to by $recTableName (probably a random number equivelent) in the row where $whereField matches formValHolder($recordUniqueId) with just the item key. An item add button is also provided, the operation of which can be deduced from the descriptions of the passed arguments.  Initially the outer div is set to display:none so the button panel will be invisible unless it is specifically set to display:inline by javascript. */
function butPanelJSInteracStrOnly(
    $uniqueId,          //id to provide target info and distinguish this from other instances of this button panel (for javascript interaction with panel elements)
    $viewOnly,          //TRUE = view only, no clicking or editing. FALSE = fully functional
    $outerDivClass,     //class of containing div that acts as a container for the buttons
    $butPanelInnerScrlContainer, //inner scrolling container that holds all the buttons for selection but not the ADD, CLEAR or div for home-in characters
    $btnClass,          //class for all unselected ordinary buttons
    $btnSelectedClass,  //class for the the selected button - shows which button has been clicked
    $homeInDiv,         //class of the home in div area where a few characters are typed in to reduce the number of buttons and focus in on the desired few
    $itemAry,           //array of items used to create the column of buttons: array(3=>"dog", 7=>"puppy", 1=>"tail") - the array keys set the values of each button, the corresponding stings are displayed
    $filepath,          //the path/name of the php file that is a target (usually index.php)
    $fileRndm,          //the filename random that will be interpreted probably by index.php as the actual php file that will write to the database table
    $addButName,        //the filename random that will be interpreted probably by index.php as the actual php file that will present an interface to add new items to the table that $itemAry is from
    $addButVal,         //the filename random that will be interpreted probably by index.php as the php page this function is used in - so it can be returned to once the new item has been added
    $addButClass,       //class of the add button (which will be at the top) to make it distinct from the other item select buttons
    $addTableName,      //the name of the table of items (probably a random number equivelent) that will be added to
    $addField,          //the name of the table field (probably a random number equivelent) to which the new item will be inserted
    $cellClassWarn,
    $cellClassEdit,
    $recoveredSessionAryCommitRnd,
    $presetVal = "")
    {
    global $pathToPhpFiles;
    $homeInDivId = $uniqueId."homeIn"; //id of div where letters can be typed to home in on buttons for particular name or entity
    $recordUniqueId = $uniqueId."recordIdPlaceholder"; //record in allRecords table
    $recFieldUniqueId = $uniqueId."recFieldIdPlaceholder"; //row in display page
    $itemButUniqueId = $uniqueId."itemButIdPlaceholder"; //row in display page
    $itemCellUniqueId = $uniqueId."itemCellIdPlaceholder"; //row in display page
    $itemKeysAry = array();
    foreach($itemAry as $key => $item) {
        $itemKeysAry[] = $key;
    }
    $itemKeysCsv = implode(',', $itemKeysAry);
    $itemUniqueId = $uniqueId."itemId";
    echo '<div class='.$outerDivClass.' id='.$uniqueId.' style="display:none;">' ;
        ?>
        <form style="float:left;" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
        <?php
        if (($addButName) && !($viewOnly)) { //only show 'ADD' button if name is provided and viewOnly is set to FALSE
            echo '<button
                class='.$addButClass.' 
                type="submit" name='.$addButName.' 
                value='.$addButVal.'
                >
                ADD
                </button>';
        }
        formValHolder("tableName", $addTableName);
        formValHolder("fieldName", $addField);
        namedValHolder("sessionCommitRnd", $recoveredSessionAryCommitRnd);
        ?> 
        <div class="<?php echo $homeInDiv;?>" id="<?php echo $homeInDivId; ?>" contentEditable="true" onkeyup="<?php echo $uniqueId;?>homeInOnButtons(event)"> </div>

        </form> 
            <button
                class="<?php echo $btnClass;?>"
                type="button"
                value="CLEAR"
                id="<?php echo $itemUniqueId."0";?>"
                onclick="<?php echo $uniqueId;?>clickPanelButs(event)"
            > <!-- Blank button for clearing a cell -->
            <?php echo "";?>
            </button>

        <div class="<?php echo $butPanelInnerScrlContainer; ?>" onclick="<?php echo $uniqueId;?>clickPanelButs(event)">
            
            <?php
            $index = 1;
            foreach ($itemAry as $key => $itemStr) {
                $yrClass = $btnClass;
                if ($viewOnly) {
                    ?> <button class="<?php echo $btnClass; ?>" type="button" id="<?php echo $itemUniqueId.$index; ?>" ><?php echo $yr; ?></button><?php
                }                   
                else {
                    ?> <button
                            class="<?php echo $btnClass;?>"
                            type="button"
                            id="<?php echo $itemUniqueId.$index;?>"
                        >
                        <?php echo $itemStr;?>
                        </button><?php
                }  
                $index++; 
            } 
            ?>  
        </div>                    
    </div>
    <input hidden id="<?php echo $itemCellUniqueId;?>" type="text"></input>
    <script>
    var editingEnabled = false;
    var <?php echo $uniqueId;?>presetValNotUsedYet = true; //flag to show whether preset value has been loaded at initialisation or not
    var <?php echo $uniqueId;?>presetVal = <?php echo json_encode($presetVal);?>; //value passed as argument that if other than "" will be used to a home in on buttons at panel initialisation. Could select a single button.
    function <?php echo $uniqueId;?>homeInOnButtons(event) { //button filtering function - only shows those buttons whose first few characters matched what is typed in the 'home-in div' box
        var idxMax = <?php echo $index - 1;?>; //maximum panel button id (they start at 0)     
        var idPrefix = '<?php echo $itemUniqueId;?>'; //unique prefix (derived in php code above) to be used to distinguish these buttons from those in other instances of button panel
        if (<?php echo $uniqueId;?>presetValNotUsedYet) {  //if presetVal passed in argument has not yet been used to preselect a button - will only happen at page load when this php function is run, and initialised at first click on column - thereafter everything will act normally with no reference to the presetVal
            var charsToMatch = <?php echo $uniqueId;?>presetVal //set charsToMatch to passed presetVal argument 
        }
        else { //operate normally with no reference to the presetVal
            var charsToMatch = document.getElementById(event.target.id).innerText.trim(); //the text in the cell that initiated the call to this button panel
        }
        //charsToMatch = "Reserves";
        for (i = 1; i <= idxMax; i++) { //loop through all the panel button ids
            var butStr = inrGet(idPrefix+i).trim(); //for each iteration of the loop get the text from the relevant button
            //console.log("butStr = "+butStr);
            if (  charsMatchStringStart(charsToMatch, butStr)  ) { //if the characters to match match the beginning of the name of the current button show that button
                document.getElementById(idPrefix+i).style.display = 'inline';
            }
            else { //if the calling cell text doesn't match the current loop button set that button to 'not selected' by changing the class
                document.getElementById(idPrefix+i).style.display = 'none'; //the characters to match don't match the beginning of the name of the current button so hide that button
            }
        } 
    }
    function <?php echo $uniqueId;?>getFocusBack() { //called from toggleClickDown() to remove focus from the auto button and return it to the home in area
            document.getElementById('<?php echo $homeInDivId;?>').focus(); //focuses on the home in area ready for typing without having to select it with the mouse first
        }
    function <?php echo $uniqueId;?>initButPanel(cellId, show, editAllowed) { //uses cellId to get cell inner text and compare it with each panel button text to set the matching one to 'selected' and others to 'not selected'
        START('<?php echo $uniqueId;?>'+"initButPanel() - in butPanelJSInteracStrOnly()");
        editingEnabled = editAllowed;
        document.getElementById('<?php echo $homeInDivId;?>').innerText = ""; //clears inner text at initialisation so any characters from previous uses are deleted
        document.getElementById('<?php echo $homeInDivId;?>').focus(); //focuses on the home in area ready for typing without having to select it with the mouse first
        valSet("<?php echo $itemCellUniqueId;?>", cellId); //copy cellId for use in ajaxRecordsItemAndCellUpdate() below
        var idxMax = <?php echo $index - 1;?>; //maximum panel button id (they start at 0)     
        var idPrefix = '<?php echo $itemUniqueId;?>'; //unique prefix (derived in php code above) to be used to distinguish these buttons from those in other instances of button panel
        var cellStr = inrGet(cellId); //the text in the cell that initiated the call to this button panel
        //console.log("cell value in initButPanel() = "+cellStr);
        START("1st for loop in initButPanel()  - in butPanelJSInteracStrOnly()");       
        for (i = 0; i <= idxMax; i++) { //loop through all the panel button ids
            var butStr = inrGet(idPrefix+i); //for each iteration of the loop get the text from the relevant button        
            if (cellStr == butStr) { //if the calling cell text matches the current loop button set that button to 'selected' by changing the class
                document.getElementById(idPrefix+i).className = '<?php echo $btnSelectedClass;?>';
            }
            else { //if the calling cell text doesn't match the current loop button set that button to 'not selected' by changing the class
                document.getElementById(idPrefix+i).className = '<?php echo $btnClass;?>';
            }
        }
        FINISH("1st for loop in initButPanel()  - in butPanelJSInteracStrOnly()");
        START("2nd for loop in initButPanel()  - in butPanelJSInteracStrOnly()");
        for (i = 0; i <= idxMax; i++) { //loop through all the panel button ids - this was in loop above but seems to work quicker when done separately here
            document.getElementById(idPrefix+i).style.display = 'inline'; //set all buttons to visible as a new cell is selected - clears any button filtering from previous operation
        }
        FINISH("2nd for loop in initButPanel()  - in butPanelJSInteracStrOnly()");
        if (<?php echo $uniqueId;?>presetValNotUsedYet && (<?php echo $uniqueId;?>presetVal != "")) { //if presetVal has not been used since this php function was run at page load, and argument is not empty
            <?php echo $uniqueId;?>homeInOnButtons(); //run the homeInOnButtons() function where presetVal will be used to select a button
        }
        <?php echo $uniqueId;?>presetValNotUsedYet = false; //set to false so from here on the button function will operate normally with no reference to the presetVal
        FINISH('<?php echo $uniqueId;?>'+"initButPanel() - in butPanelJSInteracStrOnly()");
    }
    function <?php echo $uniqueId;?>clickPanelButs(event) { //uses clicked button id to get inner text and compare it with each panel button text to set the matching one to 'selected' and others to 'not selected'. Then calls ajaxRecordsItemAndCellUpdate() to update table record on server with clicked button value and eventually from data echoed back from the server update the cell that has activated this button panel
        var selButId = event.target.id;
        var idxMax = <?php echo $index - 1;?>; //maximum panel button id (they start at 0)     
        var idPrefix = '<?php echo $itemUniqueId;?>'; //unique prefix (derived in php code above) to be used to distinguish these buttons from those in other instances of button panel
        var selButStr = inrGet(selButId); //the text of the selected button on this button panel
        if (currentKey == "Control") { //if control key is being held down initiate filter action using the clicked panel button instead of the normal cell update
            valSet("SearchFiltCellId", valGet("<?php echo $itemCellUniqueId;?>")); //set "SearchFiltCellId" to the currently selected cell on the display - used to get column to filter
            valSet("SearchFiltStrValue", selButStr); //the value of the clicked panel button is set in "SearchFiltStrValue"
            document.getElementById("q2ZKxPKThZP").submit(); //calls new (same) page immediately with search filter set
            return "function exited";
        }
        if (editingEnabled && notHiddenCompound()) { //only run this function to update the tables on the server as long as the row being edited hasn't been set to hidden (but temporarily visible)
            for (i = 0; i <= idxMax; i++) { //loop through all the panel button ids
                var butStr = inrGet(idPrefix+i); //for each iteration of the loop get the text from the relevant button        
                if (selButStr == butStr) { //if the calling cell text matches the current loop button set that button to 'selected' by changing the class
                    document.getElementById(idPrefix+i).className = '<?php echo $btnSelectedClass;?>';
                }
                else { //if the calling cell text doesn't match the current loop button set that button to 'not selected' by changing the class
                    document.getElementById(idPrefix+i).className = '<?php echo $btnClass;?>';
                }
            } 
            idOfCellToUpdate = valGet("<?php echo $itemCellUniqueId;?>"); //save current cell id so it can be used by ajaxRecordsItemAndCellUpdate() below (it will be altered by clickCellBelow() in next line)  
            clickCellBelow(valGet("seltdRowCellId"), idrAry, "From Buttons"); //selects same cell in row below the current one, same as clicking by the mouse - this function may be internally allowed / inhibited as required
            document.getElementById('<?php echo $homeInDivId;?>').focus(); //focuses on the home in area to blur (deselect) the button that has just been clicked
            ajaxRecordsItemAndCellUpdate(
                idOfCellToUpdate,
                selButStr,
                '<?php echo $filepath;?>',
                '<?php echo $fileRndm;?>',
                '<?php echo $cellClassWarn;?>'
            )
        }
        else {
            if (!editingEnabled) {
                msgEditDenied();
            }
            else {
                messageChangeInhibited();
            }
        }
    }

    </script>
    <?php
    formValHolder($recordUniqueId);   //holds the id of the record (row number) that has been selected when the editing element was clicked
    formValHolder($recFieldUniqueId); //holds the name of the record field to be updated when an item button is clicked
    formValHolder($itemButUniqueId);  //holds the element id that is also to be updated when an item button is clicked
    return $itemKeysCsv;
}


/* NEED TO WRITE DESCRIPTION!! LOTS OF THEARGUMENTS ARE NOT NEEDED AND CAN BE REMOVED !! */
function textPanelJSInteracStrOnlyDEPR(
    $uniqueId,          //id to provide target info and distinguish this from other instances of this button panel (for javascript interaction with panel elements)
    $outerDivClass,     //class of containing div that acts as a container for the buttons
    $filepath,          //the path/name of the php file that is a target (usually index.php)
    $fileRndm,          //the filename random that will be interpreted probably by index.php as the actual php file that will write to the database table
    $cellClassWarn,
    $cellClassEdit 
    ){
    global $pathToPhpFiles;
    $textAreaUniqueId = $uniqueId."textArea";
    $itemCellUniqueId = $uniqueId."itemCellIdPlaceholder"; //row in display page
    echo '<div class='.$outerDivClass.' id='.$uniqueId.' style="display:none;">' ;
        ?> 
        <textarea style="width:6.92664vw; height:3.6456vw;" id='<?php echo $textAreaUniqueId;?>' onchange="<?php echo $uniqueId;?>testAreaChange(event)"></textarea>         
    </div>
    <input hidden id="<?php echo $itemCellUniqueId;?>" type="text"></input>
    <script>
    function <?php echo $uniqueId;?>initButPanel(cellId, show, editAllowed) { //uses cellId to get cell inner text 
        START("initButPanel() - in textPanelJSInteracStrOnlyDEPR()");
        valSet("<?php echo $itemCellUniqueId;?>", cellId); //copy cellId for use in ajaxRecordsItemAndCellUpdate() below
        valSet("<?php echo $textAreaUniqueId;?>", inrGet(cellId)); //the text in the cell that initiated the call to this button panel
        FINISH("initButPanel() - in textPanelJSInteracStrOnlyDEPR()");
    }
    function <?php echo $uniqueId;?>testAreaChange(event) { //uses clicked button id to get inner text . Then calls ajaxRecordsItemAndCellUpdate() to update table record on server with clicked button value and eventually from data echoed back from the server update the cell that has activated this button panel
        var selButId = event.target.id;
        var selButStr = valGet("<?php echo $textAreaUniqueId;?>"); //the text of the selected button on this button panel
        ajaxRecordsItemAndCellUpdate(
            valGet("<?php echo $itemCellUniqueId;?>"),
            selButStr,
            '<?php echo $filepath;?>',
            '<?php echo $fileRndm;?>',
            '<?php echo $cellClassWarn;?>',
            '<?php echo $cellClassEdit;?>',
        )
    }
    </script>
    <?php
}



/* Creates a scrolling containing div and populates it with buttons left to right row upon row:
A  B  C
D  E  F
G  H  I
J  K  L
----------
An 'ADD' button sits at the top left of the div, this doesn't toggle but is a submit button for the form that buttonsPanel() sits in. It would be normally used to navigate to a page that allowed extra items to be added to the $allItemsAry and it is only displayed if $addButName has a value other than "" and $viewOnly is FALSE.  
----------
Self descriptive classes are passed to the function, an integer to set the number of inner divs, a name and value for the 'ADD' button, a boolean to determine whether the panel buttons are editable, a boolean $makeMultiSelect to determine whether the buttons are all togglable or only one (or no) button at a time is selected and an id for the whole panel that should be unique to prevent it interfering with any additional panels that might be in use on the webpage. If $makeMultiSelect is TRUE and $allowDeselect is set to FALSE, buttons already selected (set to on) by $itemsSelectedAry cannot be deselected.
----------
If $viewOnly is set to TRUE only those buttons that are already selected are displayed unless $displayAllWhenViewOnly is set to TRUE. Clicking in the viewOnly mode will have no effect.
----------
$outputAryName sets the name of the array that will be passed (usually by POST method) when the form is submitted. $allItemsAry contains all the key and name values of the items that are being selected from. itemsSelectedAry contains values that equate to the keys of the $allItemsAry that have been selected and will determine which buttons are initially shown as 'selected' or 'deselected'.
----------
e.g. 
$allItemsAry = array([8]=>'grass' [3]=>'dog' [5]=>'mouse' [1]=>'car' [2]=>'Katya' [7]=>'Roman' [6]=>'book' [0]=>'Chris' [4]=>'orange')
itemsSelectedAry = array([0]=>5 [1]=>7 [2]=>8 )
POSTed array defined by $outputAryName = array([8]=>1 [3]=>'' [5]=>1 [1]=>'' [2]=>'' [7]=>1 [6]=>'' [0]=>'' [4]=>'').
----------
Thus the output array holds the keys of all the items but with a value of 1 in the positions where items have been selected ('' where not selected). */
function buttonsPanel ($outerDivClass, $addButClass, $addButName, $addButVal, $SelBtnClass, $selBtnSelectedClass, $viewOnly, $uniqueId, $outputAryName, $allItemsAry, $itemsSelectedAry, $makeMultiSelect = FALSE, $displayAllWhenViewOnly = FALSE, $allowDeselect = TRUE) {
    echo '
    <div class='.$outerDivClass.'>
    ';
    if (($addButName) && !($viewOnly)) { //only show 'ADD' button if name is provided and viewOnly is set to FALSE
        echo '<button class='.$addButClass.' type="submit" name='.$addButName.' value='.$addButVal.'>ADD</button>';
    }
    $totalItems = sizeof($allItemsAry);
    $maxId = $totalItems -1;
    $butIndex = 0;
    foreach ($allItemsAry as $id => $itemName) {
        $itemSelected = FALSE;
        if (in_array($id, $itemsSelectedAry)) { //if item id in $itemsSelectedAry matches the current id
            $itemSelected = TRUE;
        }
        $initClass = $SelBtnClass;
        if ($itemSelected) {
            $initClass = $selBtnSelectedClass;
        }
        $enableDeselect = TRUE;
        if ($itemSelected && !$allowDeselect) {
        	$enableDeselect = FALSE;
        }
        ?> <input style="display:none;" id="<?php echo $uniqueId.$butIndex.'textBx'; ?>" type="text" name="<?php echo $outputAryName."[".$id."]"; ?>" value="<?php echo $itemSelected; ?>"  > </input>
         <?php
        if ($viewOnly) {
            if (($itemSelected) or ($displayAllWhenViewOnly)) {
                ?> <button class="<?php echo $initClass; ?>" type="button" id="<?php echo $uniqueId.$butIndex; ?>" ><?php echo $itemName; ?></button>
                <?php
            }
        }
        elseif ($makeMultiSelect){
           ?> 
           <button class="<?php echo $initClass; ?>" type="button" id="<?php echo $uniqueId.$butIndex; ?>" onclick="setClass(this.id, '<?php echo $selBtnSelectedClass; ?>', '<?php echo $SelBtnClass; ?>', '<?php echo json_encode($enableDeselect); ?>')"><?php echo $itemName; ?></button>
           <?php
        }
        else {
            ?> <button class="<?php echo $initClass; ?>" type="button" id="<?php echo $uniqueId.$butIndex; ?>" onclick="setClassAndValueUnique('<?php echo $uniqueId;?>', '<?php echo $butIndex;?>', '<?php echo $maxId;?>', '<?php echo $selBtnSelectedClass;?>', '<?php echo $SelBtnClass;?>')"><?php echo $itemName; ?></button>
            <?php
        }
        $butIndex++;
    }  
    echo '
    </div>
    ';
}


/* Creates 1 to n div panels (determined by $numInnerDivs) within a scrolling outer containing div (at least the inner divs should be float:left) and equitably populates the inner divs with buttons with roughly equal number per inner div. The first div contains the earliest button items in the allItemsAry and the last div contains the latest button items:
A  E  I
B  F  J
C  G  K
D  H  L
----------
An 'ADD' button sits at the top of the first div, this doesn't toggle but is a submit button for the form that buttonsPanel() sits in. It would be normally used to navigate to a page that allowed extra items to be added to the $allItemsAry and it is only displayed if $addButName has a value other than "" and $viewOnly is FALSE.  
----------
Self descriptive classes are passed to the function, an integer to set the number of inner divs, a name and value for the 'ADD' button, a boolean to determine whether the panel buttons are editable, a boolean $makeMultiSelect to determine whether the buttons are all togglable or only one (or no) button at a time is selected and an id for the whole panel that should be unique to prevent it interfering with any additional panels that might be in use on the webpage. 
----------
If $viewOnly is set to TRUE only those buttons that are already selected are displayed unless $displayAllWhenViewOnly is set to TRUE. Clicking in the viewOnly mode will have no effect.
----------
$outputAryName sets the name of the array that will be passed (usually by POST method) when the form is submitted. $allItemsAry contains all the key and name values of the items that are being selected from. itemsSelectedAry contains values that equate to the keys of the $allItemsAry that have been selected and will determine which buttons are initially shown as 'selected' or 'deselected'.
----------
e.g. 
$allItemsAry = array([8]=>'grass' [3]=>'dog' [5]=>'mouse' [1]=>'car' [2]=>'Katya' [7]=>'Roman' [6]=>'book' [0]=>'Chris' [4]=>'orange')
itemsSelectedAry = array([0]=>5 [1]=>7 [2]=>8 )
POSTed array defined by $outputAryName = array([8]=>1 [3]=>'' [5]=>1 [1]=>'' [2]=>'' [7]=>1 [6]=>'' [0]=>'' [4]=>'').
----------
Thus the output array holds the keys of all the items but with a value of 1 in the positions where items have been selected ('' where not selected). */
function buttonsPanelColumns ($outerDivClass, $innerDivsClass, $numInnerDivs, $addButClass, $addButName, $addButVal, $SelBtnClass, $selBtnSelectedClass, $viewOnly, $uniqueId, $outputAryName, $allItemsAry, $itemsSelectedAry, $makeMultiSelect = FALSE, $displayAllWhenViewOnly = FALSE) {
    echo '
    <div class='.$outerDivClass.'>
    <div class='.$innerDivsClass.'>
    ';
    if (($addButName) && !($viewOnly)) { //only show 'ADD' button if name is provided and viewOnly is set to FALSE
        echo '<button class='.$addButClass.' type="submit" name='.$addButName.' value='.$addButVal.'>ADD</button>';
    }
    $totalItems = sizeof($allItemsAry);
    $maxId = $totalItems -1;
    $maxPerColumn = intdiv($totalItems, $numInnerDivs) +1;
    $butIndex = 0;
    foreach ($allItemsAry as $id => $itemName) {
        $itemSelected = FALSE;
        if (in_array($id, $itemsSelectedAry)) { //if item id in $itemsSelectedAry matches the current id
            $itemSelected = TRUE;
        }
        if ((($butIndex % $maxPerColumn) == 0) && (0 < $butIndex)) { //spawn another column if current column has got to total buttons / $numInnerDivs
            ?>
            </div>
            <div class="<?php echo $innerDivsClass; ?>">
            <?php
        }
        $initClass = $SelBtnClass;
        if ($itemSelected) {
            $initClass = $selBtnSelectedClass;
        }
        ?> <input style="display:none;" id="<?php echo $uniqueId.$butIndex.'textBx'; ?>" type="text" name="<?php echo $outputAryName."[".$id."]"; ?>" value="<?php echo $itemSelected; ?>"  > </input>
         <?php
        if ($viewOnly) {
            if (($itemSelected) or ($displayAllWhenViewOnly)) {
                ?> <button class="<?php echo $initClass; ?>" type="button" id="<?php echo $uniqueId.$butIndex; ?>" ><?php echo $itemName; ?></button>
                <?php
            }
        }
        elseif ($makeMultiSelect){
           ?> <button class="<?php echo $initClass; ?>" type="button" id="<?php echo $uniqueId.$butIndex; ?>" onclick="setClass(this.id, '<?php echo $selBtnSelectedClass; ?>', '<?php echo $SelBtnClass; ?>')"><?php echo $itemName; ?></button>
           <?php
        }
        else {
            ?> <button class="<?php echo $initClass; ?>" type="button" id="<?php echo $uniqueId.$butIndex; ?>" onclick="setClassAndValueUnique('<?php echo $uniqueId;?>', '<?php echo $butIndex;?>', '<?php echo $maxId;?>', '<?php echo $selBtnSelectedClass;?>', '<?php echo $SelBtnClass;?>')"><?php echo $itemName; ?></button>
            <?php
        }
        $butIndex++;
    }  
    echo '
    </div>
    </div>
    ';
}



/* Selects values from an array whose keys are found in a csv string. The array is then put in alphabetical order of values, keeping the original keys, and returned. */
function getAryOfItemsSelectedByCsv($keyAndItemsAry, $selectorCsv) {
    $itemsAry = array();
    if ($selectorCsv) { //only continue if there is csv data
        $selectorAry = explode(',', $selectorCsv);
        foreach($selectorAry as $index=>$itemId) {
            $itemsAry[$itemId] = $keyAndItemsAry[$itemId];
        }
        asort($itemsAry);
    }
    return $itemsAry;
}


/* Selects values from an array whose keys are found in a csv string. The values are put in alphabetical order and returned with "-" separators */
function itemsSelectedByCsv($keyAndItemsAry, $selectorCsv) { //rename arrayValueSelectedByCsvOfKeys?
	$itemsAry = array();
	if ($selectorCsv) { //only continue if there is csv data
		$selectorAry = explode(',', $selectorCsv);
		foreach($selectorAry as $index=>$itemId) {
			$itemsAry[] = $keyAndItemsAry[$itemId];
		}
        sort($itemsAry);
	}
	return implode(' + ', $itemsAry); //convert to ' + ' separated string of items and return
}


/* From an associative array that has unique integers (identities) as keys and 1s or 0s [or ""] as values (which designate whether a particular identity key is selected or not), this function generates a csv string of integers corresponding to the multiselected identity keys. The csv string is ordered numerically: e.g. array([23]=>[11]=>1[4]=>[3]=>1[15]=>[7]=>1[2]=>) returns "3,7,11" */
function getCsvOfIdsFromMultSeltdArray ($identityKeyAry) {
    $identityValueAry = array();
    foreach($identityKeyAry as $key => $selected) { //loop through the input array
        if ($selected == 1) { //filter the array by creating a simple array of keys that have been selected (value = 1)
            $identityValueAry[] = $key;
        }
    }
    sort($identityValueAry); //sort into numeric order for easier visualisation when checking table
    return implode(',', $identityValueAry); //convert to csv and return
}

/* From an associative array that has unique integers (identities) as keys and 1s or 0s [or 1/""] as values (which designate whether a particular identity key is selected or not), this function generates a an integer corresponding to the selected identity key. e.g. array([23]=>[11]=>1[4]=>[3]=>[15]=>[7]=>[2]=>) returns 11. If nothing is selected 0 is returned. */
function getIdFromSingleSeltdAry ($identityKeyAry) {
    $id = 0;
    foreach($identityKeyAry as $key => $selected) { //loop through the input array
        if ($selected == 1) { //filter the array by creating a simple array of keys that have been selected (value = 1)
            $id = (int)$key;
        }
    }
    return $id;
}



/* Sets cookie on client using the cookie name and data (random alphanumeeric). Cookie is set to expire on browser exit, nothing special is set for path or domain, secure is set to false, httpOnly is set to true */
function setCookieOnClient($cookieName, $cookieData) {
    setcookie($cookieName, $cookieData, 0, "/", "", FALSE, TRUE);
}

/* Deletes the cookie on client that matches the cookie name. */
function deleteCookieOnClient($cookieName) {
    setcookie($cookieName, "", time() - 100000, "/", "", FALSE, TRUE); //time is set to over a day ago to force deletion
}


/* Creates header and echos pdf file contents. Used to obscure the real location of the file. 
   GOR METHOD CURRENTLY USED - readfile($filePathName) - FROM https://idiallo.com/blog/making-php-as-fast-as-nginx-or-apache , COULD BE IMPROVED EVEN MORE PERHAPS!! */
function downloadPdfFile($filePathName) {
	//Consider: https://idiallo.com/blog/making-php-as-fast-as-nginx-or-apache
    $size = filesize($filePathName);
  /*  $openedFile = fopen($filePathName, "rb");
    header("Accept-Ranges: bytes");
    header("Content-length: ".$size);
    header("Content-type: application/pdf");
    echo fread($openedFile, $size);
    fclose($openedFile); */

    header("Content-Type: text/plain");
    readfile($filePathName); // Reading the file into the output buffer
    exit;

}

/* returns a cryptographically secure random string to the base 62 (i.e. using all characters 0-9, a-z, A-Z) of the length designated by the passed argument $length .
   A length of 50 is approx equivialent to a 298 bit binary key (50*log 62 / log 2) */
function randomAlphaString($length) {
	$AlphaNumericBet = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z');
	$output = '';
	$index = 0;
	while ($index < $length) {
		$output .= $AlphaNumericBet[random_int(0, 61)];
		$index++;
	}
	return $output;
}


/* Checks if there is an active session and if so regenerates the session ID. */
function sessIdRegen() {
    if(session_status() == PHP_SESSION_ACTIVE) {
    session_regenerate_id();
    }
}

/*Gets $_SESSION[] value for passed key after testing for existance and substituting "" (or passed default argument) if it doesn't exist. */
function sessionValue($key, $emptyReturn = '') {
    return empty($_SESSION[$key]) ? $emptyReturn : $_SESSION[$key];
}


/* creates a random alphaNumeric. The length of the alphanumeric is set by the argument $randLength and the random is checked for uniqueness against all other randoms generated in the current session. */
function getRandNoSave($randLength) {
    global $uniqnsChkAryForRndms;
    $randLength;
    $limitIndex = 0;
    $randomAlphNum = randomAlphaString($randLength);
    while (in_array($randomAlphNum, $uniqnsChkAryForRndms) && ($limitIndex < 10)) { //checks to see whether generated $randomAlphNum has already been used, and if so continues to generate a new one until a unique one is found. Limits tries to 10 to prevent an endless loop if there are too many keys and too short a random length has been chosen. This means that when the total number of theoretically possible randoms is not much more than the number of keys there is no guarantee of all unique randoms.
        $randomAlphNum = randomAlphaString($randLength);
        $limitIndex++;
    }
    $uniqnsChkAryForRndms[] = $randomAlphNum; //add to $uniqnsChkAryForRndms for further randoms generated in this function call but also in future calls, until $uniqueCheckKeysAry is reset.
    return $randomAlphNum;
}


/* creates a random alphaNumeric that is added to the global array $nonVolatileArray["onTheHoofRandsAry"] using $inputString as the key, and also returned. The length of the alphanumeric is set by the global variable $_onTheHoofRandsLength and the random is checked for uniqueness against all other randoms generated in the current session. $nonVolatileArray["onTheHoofRandsAry"] saved by saveSession.php. This routine doesn't run if a random has already been created and stored for an identical input string since $nonVolatileArray["onTheHoofRandsAry"] was last reset, instead the random already stored is returned.  */
function getRand($inputString) {
    global $nonVolatileArray;
    global $uniqnsChkAryForRndms;
    global $_onTheHoofRandsLength;
    if (!array_key_exists($inputString, $nonVolatileArray["onTheHoofRandsAry"])) { //only generates random and stores it if it hasn't already been done for identical input string since $nonVolatileArray["onTheHoofRandsAry"] was last reset
        $limitIndex = 0;
        $randomAlphNum = randomAlphaString($_onTheHoofRandsLength);
        while (in_array($randomAlphNum, $uniqnsChkAryForRndms) && ($limitIndex < 10)) { //checks to see whether generated $randomAlphNum has already been used, and if so continues to generate a new one until a unique one is found. Limits tries to 10 to prevent an endless loop if there are too many keys and too short a random length has been chosen. This means that when the total number of theoretically possible randoms is not much more than the number of keys there is no guarantee of all unique randoms.
            $randomAlphNum = randomAlphaString($_onTheHoofRandsLength);
            $limitIndex++;
        }
        $nonVolatileArray["onTheHoofRandsAry"][$inputString] = $randomAlphNum; //add this alphanumeric random to $nonVolatileArray["onTheHoofRandsAry"] with $inputString as the key - saved by saveSession.php
        $uniqnsChkAryForRndms[] = $randomAlphNum; //add to $uniqnsChkAryForRndms for further randoms generated in this function call but also in future calls, until $uniqueCheckKeysAry is reset.
    }
    return $nonVolatileArray["onTheHoofRandsAry"][$inputString];
}

/* Returns the plain text that has been stored in a nonvolatile array on the server and is recovered using a key that is a random alphanumeric.  */
function getPlain($randomsKey) {
    global $plainItemsWithRandKeysAry;
    $plain = ""; //default in case key is non-existent
    if (array_key_exists ($randomsKey, $plainItemsWithRandKeysAry)) {
        $plain = $plainItemsWithRandKeysAry[$randomsKey];
    }
    return $plain;
}


/* creates an array using the values in the passed input array as associative indexes (keys) for random AlphaNumerics that are to be used as menu or other values. $randomLength determines the length of the random. Tries to ensure uniqueness! (see comment for while loop). $uniqueCheckKeysAry is passed by reference and acts as a running cumulative check on generated randoms to try and prevent duplication, even across different randoms arrays. */
function createKeysAndRandomsArray($valuesAry, $randomLength, &$uniqueCheckKeysAry) {
    $KeysAndRandomsArray = array();
    foreach($valuesAry as $value) {
    	$limitIndex = 0;
        $randomAlphNum = randomAlphaString($randomLength);
        while (in_array($randomAlphNum, $uniqueCheckKeysAry) && ($limitIndex < 10)) { //checks to see whether generated $randomAlphNum has already been used, and if so continues to generate a new one until a unique one is found. Limits tries to 10 to prevent an endless loop if there are too many keys and too short a random length has been chosen. This means that when the total number of theoretically possible randoms is not much more than the number of keys there is no guarantee of all unique randoms.
            $randomAlphNum = randomAlphaString($randomLength);
            $limitIndex++;
        }
        $KeysAndRandomsArray[$value] = $randomAlphNum;
        $uniqueCheckKeysAry[] = $randomAlphNum; //add to $uniqueCheckKeysAry for further randoms generated in this function call but also in future calls, until $uniqueCheckKeysAry is reset.
    }
    return $KeysAndRandomsArray;
}



/* Uses multidimensional array $filesToBeUploaded to get a list of file names and details that are to be uploaded. Attempts to convert files to pdfs and move them (they will have already been uploaded from the client and given temporary names) to a subdirectory $subDirForThisUpload (i.e. 2018-03) of $uploadsDir (i.e. "../uploads", which must be in correct relationship to the directory in which this script is run). The pdf filenames will follow the regime given in $pdfDateName and $pdfFileNum which give the date name of the file (i.e. 2018-02-11) and the number of the file for that date name if more than one file will exist for that date name i.e. 2018-05-14-12.pdf . File numbers will be automatically incremented where more than one file is to be created. Uploaded files can be either one or more individual jpg or pdf files that will be converted into individual pdfs, or several jpg files making up a multipage document that will be converted into one pdf. A mixed jpg/pdf upload file list cannot be merge into a single pdf output file!! If $createMultiPagePdf is TRUE the upload will be treated as a multipage doument. Files will only be converted and moved as long as each individual one meets the constraints placed on it:
	allowed file extensions  "jpg", "JPG", "jpeg", "JPEG", "pdf", "PDF".
    $maxSize - max allowed upload size for each file in bytes.   
    $allowedTypes - allowed file upload mime types passed as an array, e.g. array("text/plain", "application/pdf", "image/jpeg") etc.
If the subdirectory doesn't exist one will be created with write permissions for the php installation on the server as long as there are error free files to copy (must be all error free for multipage pdf).
A fileUploadReport array that records details of files that have been successfully uploaded or have failed will be returned. Its structure is:
array([0]=>array(  [0]=>pdfDateName, [1]=>pdfFileNum, [2]=>"pdf", [3]=>numOfPages [4]=>sourceFileName  [5]=>Filesize, [6]=>Success/Failure-Reason, [7]=>Success-TRUE/FALSE, [8]=>IsOutputFile    ), [1]=>array([0]=>Filename, [1]=>Filesize, [2].. ), [2]... etc.)
If no file(s) have been selected for upload no new subdirectory of $uploadsDir on the server will be created and an empty array is returned.
*/
function uploadJpgFilesToPdfs_DEPRECATED($filesToBeUploaded, $maxSize, $uploadsDir, $subDirForThisUpload, $pdfDateName, $pdfFileNum, $createMultiPagePdf) {
    global $_ImagickExceptionVisibility;
    $fileUploadReport = Array(); //initialise file upload array that will be used to record details of files that have been successfully uploaded or have failed.
    $sourceFilesErrorFree = TRUE;
    $sourceFilesExist = FALSE; //flag to allow multifile pdf routine to run needs to be set by foreach ($filesToBeUploaded['name'] as $fileIndex=>$srcFileName) loop below
    $destinationPath = $uploadsDir."/".$subDirForThisUpload."/"; //set destination path (permissions must allow php on the server to write to this path)
    $allowedExts = array("jpg", "JPG", "jpeg", "JPEG", "pdf", "PDF");
    $allowedTypes = array("image/jpeg", "image/jpg", "application/pdf", "application/x-pdf"); //mime file types that will be accepted
    foreach ($filesToBeUploaded['name'] as $fileIndex=>$srcFileName) { //runs this loop for each uploaded file. If no files have been selected for upload the loop will never run.    	
        if ($filesToBeUploaded['error'][$fileIndex] == 4) { //if no file has been uploaded.
            continue; //don't process what follows but continue from start of 'foreach' loop with next index.
        }
        $sourceFilesExist = TRUE; //set this flag to true to allow multifile pdf routine to run (would inhibit it and thus the unwanted creation of a subdir if no files are uploaded)
        if ($filesToBeUploaded['error'][$fileIndex] == 0) { //if no file errors.
            $srcFileType = $filesToBeUploaded['type'][$fileIndex]; //get file details
            $fileSize = $filesToBeUploaded['size'][$fileIndex];
            $tempFileName = $filesToBeUploaded['tmp_name'][$fileIndex];
            $fileSizeMKB = parseFileSizeForDisplay($fileSize); //convert file size to be human readable in MB / KB / B.
            $nameExtArray = (explode(".", $srcFileName)); //split file into array of [0] name, [1] extension
            $extension = end($nameExtArray); //get file extension - last (end) element of array
            $destinationfileName = $pdfDateName."-".$pdfFileNum.".pdf"; //set destination filename
            if (in_array($srcFileType, $allowedTypes)) { //check for allowed file type.
                if (in_array($extension, $allowedExts)) { //check for allowed file .ext.
                    if ($fileSize < $maxSize) { //check for allowed file size.
                        if ($createMultiPagePdf) { //if the files uploaded are intended to be combined in a multipage pdf add the current name to the filenames array for use below
                    		$fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, " ", TRUE, FALSE); //create item in array to record source file names for multipage destination pdf
                    	}
                    	else { //create several individual pdf files (one on each iteration of this foreach loop)
                    		if (!file_exists($uploadsDir."/".$subDirForThisUpload)) { //create subdirectory of uploads dir for this upload.
				                mkdir($uploadsDir."/".$subDirForThisUpload, 0755, TRUE);
				            }
                            $pdfFileCreated = TRUE; //flag to indicate to error routine whether pdf file has been created
                            $Image = "";
                            try {
                            	if (($extension == "pdf") || ($extension == "PDF")) { //not a jpg file so simply move to destination and don't process with imageMagick
                            		if (move_uploaded_file($tempFileName, $destinationPath.$destinationfileName)) { //move file to designated destination giving it the original name.
			                            $fileUploadReport[] = array($pdfDateName, $pdfFileNum, "pdf", 1, $srcFileName, $fileSizeMKB, "converted successfully", TRUE, TRUE); //create item in array to record file name and size of successfully uploaded file.
										$pdfFileNum++; //increment pdf filename number for use with individual files (not incremented with createMultiPagePdf TRUE so just the single given filename number will be used)
			                        }
			                        else {
			                            $pdfFileCreated = FALSE;
			                        }
                            	}
                            	else { //THIS IS THE IMAGEMAGICK SECTION !!
                            		$image = array($tempFileName); //create an array with the single current filename for this loop in it
									$pdfFileObject = new Imagick($image); //creates an Imagick object of the current uploaded jpeg file
									$pdfFileObject->setImageFormat('pdf'); //sets the type to be converted to to pdf
									$pdfFileObject->setImageFilename($destinationPath.$destinationfileName); //sets the destination folder/filename for the file to be written once it is converted to a pdf
									//LATEST: all files were set to www-data:chris and 770 because cron job for dumping the database and backing up at 3am was not working because it runs under chris. CH 2018-10-22
									//OLDER: if ($pdfFileObject->writeImages($destinationPath.$destinationfileName, TRUE)) { //used originally - commented out and above and below lines were added instead while trying to solve a write 'exception: unauthorised... ' that seemed to arise spontaniously (though could have been as a result of some update or change) but turned out to be the pdf section in /etc/ImageMagick-6/policy.xml having 'none' instead of 'read|write' in it. Even though these changes in uploadJpgFilesToPdfs() turned out not to have any effect it was left in the new form. To get file writes to work fully (and not just to a '/chtest' dir that had been set up with www-data:www-data and 775 permisions) a final 'sudo -R chown www-data:www-data monytalyData' was performed from /var (all with 755 permisions too). This seemed to cure a permission problem when attempting to write even though all the folders in the tree already had these ownerships/group as far as was known. CH 2018-10-06
									if ($pdfFileObject->writeImage()) {
										$fileUploadReport[] = array($pdfDateName, $pdfFileNum, "pdf", 1, $srcFileName, $fileSizeMKB, "converted successfully", TRUE, TRUE); //create item in array to record file name and size of successfully uploaded file.
										$pdfFileNum++; //increment pdf filename number for use with individual files (not incremented with createMultiPagePdf TRUE so just the single given filename number will be used)
		                            }
		                            else {
		                            	$pdfFileCreated = FALSE;
		                            }
	                            }	                                	                            	
                        	}
	                        catch(ImageException $e) {
							  	$pdfFileCreated = FALSE;
                                if ($_ImagickExceptionVisibility) {
                                	echo 'Message: ' .$e->getMessage();
                                    $Image = $e;
                                }
							}
							if (!$pdfFileCreated) {
								$fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, "not converted! [".$srcFileType."] ".$ImagickError, FALSE, FALSE);
								continue; //go straight back to start of 'foreach' loop with next index.
							}
						}
                    }
                    else {
                        $fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, "too big (max 10MB). [".$srcFileType."]", FALSE, FALSE);
                        $sourceFilesErrorFree = FALSE;
                        continue; //go straight back to start of 'foreach' loop with next index.
                    }
                }
                else {
                    $fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, "invalid extension. [".$srcFileType."]", FALSE, FALSE); //include details of file type in case it is wrong as well as ext.
                    $sourceFilesErrorFree = FALSE;
                    continue; //go straight back to start of 'foreach' loop with next index.
                }
            }
            else {
                $fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, "invalid type. [".$srcFileType."]", FALSE, FALSE);
                $sourceFilesErrorFree = FALSE;
                continue; //go straight back to start of 'foreach' loop with next index.
            }
        }
    }
	if ($createMultiPagePdf && $sourceFilesExist) { //if the files uploaded are intended to be combined in a multipage pdf and there is at least one source file
	    $pdfMultiFileCreated = TRUE; //flag to indicate to error routine whether pdf file has been created
	    $images = $filesToBeUploaded['tmp_name']; //array of temp file names with path (/tmp)
	    $numOfPages = sizeof($filesToBeUploaded['tmp_name']); //get the number of elements in the array of temp source file names to indicate how many pages in output pdf (if converted successfully)
        $destinationfileName = $pdfDateName."-".$pdfFileNum.".pdf"; //set destination filename
		if ($sourceFilesErrorFree) {
			if (!file_exists($uploadsDir."/".$subDirForThisUpload)) { //create subdirectory of uploads dir for this upload.
                mkdir($uploadsDir."/".$subDirForThisUpload, 0755, TRUE);
            }
		}
		try { //THIS IS THE IMAGEMAGICK SECTION !!
			$pdfFileObject = new Imagick($images);
			$pdfFileObject->setImageFormat('pdf');
			if ($pdfFileObject->writeImages($destinationPath.$destinationfileName, TRUE)) { // if file write successful and no error in uploaded files has been found (indicated by $allowMultiPagePdf)
				$reportTemp = array($pdfDateName, $pdfFileNum, "pdf", $numOfPages, " ", parseFileSizeForDisplay(filesize($destinationPath.$destinationfileName)), "created successfully", TRUE, TRUE); //create item in array to record file details
				array_unshift($fileUploadReport, $reportTemp); //inserts this report element in at position [0] of $fileUploadReport[] and reindexes other elements 1,2,3...
            }
            else {
            	$pdfMultiFileCreated = FALSE;
            }	                                	                            	
    	}
        catch(Exception $e) {
		  	$pdfMultiFileCreated = FALSE;
		}
		if (!$pdfMultiFileCreated) {
			$reportTemp = array("", "", "", 0, "", "", "NOT CREATED!", FALSE, FALSE);
			array_unshift($fileUploadReport, $reportTemp); //inserts this report element in at position [0] of $fileUploadReport[] and reindexes other elements 1,2,3...
		}
	}
    return $fileUploadReport; //report of file uploads/failures. If no file has been selected for upload an empty array will be returned.
}


/* Returns date from $filenameNumDotPdf i.e. "2020-03-07-4.pdf". If the month or day of month are single digits without leading zeros then leading zeros will be added. No checks are made for date validity or outlandish numbers or formats. */
function dateFromFilenameNumDotPdf($filenameNumDotPdf) {
    $filenameNumDotPdfAry = (explode("-", $filenameNumDotPdf)); //split input filename into array of [0]=>YYYY, [1]=>MM, [2]=>DD, [3]=>Num.pdf
    $year = $filenameNumDotPdfAry[0];
    $month = $filenameNumDotPdfAry[1];
    $dayOfMonth = $filenameNumDotPdfAry[2];
    if (strlen($month) < 2) {
        $month = "0".$month;
    }
    if (strlen($dayOfMonth) < 2) {
        $dayOfMonth = "0".$dayOfMonth;
    }
    return $year."-".$month."-".$dayOfMonth; //assemble date from the filenameNum array i.e. "2020-07-09"
}

/* Returns number from $filenameNumDotPdf i.e. "2020-03-07-4.pdf". If the number has leading zeros then they will be removed. Will produce same result if just a date is passed. No checks are made for date validity or outlandish numbers or formats. */
function numFromFilenameNumDotPdf($filenameNumDotPdf) {
    $filenameNumDotPdfAry = (explode("-", $filenameNumDotPdf)); //split input filename into array of [0]=>YYYY, [1]=>MM, [2]=>DD, [3]=>Num.pdf
    $numDotPdf = $filenameNumDotPdfAry[3]; //get Num.pdf
    $numDotPdfAry = (explode(".", $numDotPdf)); //split Num.pdf into [0]=>Num, [1]=>pdf
    $number = $numDotPdfAry[0]; //get the number
    while ((1 < strlen($number)) && (substr($number, 0, 1) == "0")) { //if more than one char the leading character is "0" remove it - then test again
        $number = substr($number, 1);
    }
    return $number;
}

/* subdir name from date i.e. "2020-03-07" or full file name with number i.e. "2020-03-07-4.pdf". If the month is single digits without a leading zero then a leading zero will be added. No checks are made for date validity or outlandish numbers or formats. */
function subDirNameFromDate($date) {
    $filenameNumDotPdfAry = (explode("-", $date)); //split input date into array of [0]=>YYYY, [1]=>MM, [2]=>DD,
    $year = $filenameNumDotPdfAry[0];
    $month = $filenameNumDotPdfAry[1];
    if (strlen($month) < 2) {
        $month = "0".$month;
    }
    return $year."-".$month; //assemble subdir name from the filenameNum array i.e. "2020-07"
}


/* Looks at appropriate directory and returns the next filename number to be used for a given upload date i.e. "2020-07-24" - can also use full filename i.e. "2020-07-24-4.pdf" if desired. If the directory or a file name for the given date doesn't exist  "1"  will be returned (on the assumption this is the first time the filename date is to be used). */
function getNextFileSufixNumFromUploadDate($uploadDate, $uploadsDir) {
    $nextFileNum = 0; //initial value filename date
    $subDir = subDirNameFromDate($uploadDate); //create year-month i.e. 2018-03 for use as subfolder name
    $uploadDateClean = dateFromFilenameNumDotPdf($uploadDate); //extract date from the filenameNum array i.e. "2020-07-09" to remove Num.pdf if a filename is used instead of just a date
    if (is_dir($uploadsDir."/".$subDir)) { //checks to see if the directory name calculated from the input filename actually exists before trying to calculate $nextFileNum
        $listOfFilenameNumsAry = array_slice(scandir($uploadsDir."/".$subDir),2); //get directory file listing and remove first 2 positions that are "." and ".."
        foreach ($listOfFilenameNumsAry as $listedFilenameNum) { //process the file names one at a time extracting the numbers...
            $listedDate = dateFromFilenameNumDotPdf($listedFilenameNum); //extract date from the filenameNum array i.e. "2020-07-09"
            if ($listedDate == $uploadDateClean) { //the current date is the same as the upload date so process to see if the $nextFileNum needs to be greater
                $listedFileNum = numFromFilenameNumDotPdf($listedFilenameNum); //extract number from current filename
                if ($nextFileNum < $listedFileNum) { //the number extrated from the current matching filename is greater than the previously stored $nextFileNum so make $nextFileNum the new value 
                    $nextFileNum = $listedFileNum;
                }
            }
        }
    }
    $nextFileNum ++; //increment the number to make it the next unused value
    return $nextFileNum;
}


/* Takes already uploaded, converted and numbered pdf files - detailed in $fileUploadReportSinglePdfs - and merges them together into one high quality pdf in the calculated subdir of $uploadsDir (compared with Imagick's effort at merging mixed jpeg/pdf files). An array is returned with the following information:
array ([0]=>array(  [0]=>, [1]=>pageCount, [2]=>MergedPdf, [3]=>MergedFileSize, [4]=>Success/Failure-Reason, [5]=>Success-TRUE/FALSE ) )
-
TO REMOVE AN INTRUSIVE LINE THAT APPEARED NEAR THE TOP OF EACH MERGED PDF PAGE (TO DO WITH HEADER ACCORDING TO FORUMS) THE BODY OF function header() wAs removed from ./PDFMerger/tcpdf/tcpdf.php line 3481
 */
function mergePdfs_DEPRICATED_PDFMerger ($fileUploadReportSinglePdfs, $uploadsDir) {
    $fileMergeReport = Array("", "", "", "", "", "No uploaded pdfs", FALSE); //default report for if there are no previously upladed and converted pdfs to merge!
    if (0 < count($fileUploadReportSinglePdfs)) { //as long as there are some uploaded pdfs to merge
        $allUploadsSuccess = TRUE;
        foreach ($fileUploadReportSinglePdfs as $indvidualPdfDetails) { //checks all uploaded converted files and sets $allUploadsSuccess to FALSE if any file had an error
            if (!$indvidualPdfDetails[6]) { //execute if statement if error in current file
                $allUploadsSuccess = FALSE;
            }
        }
        if ($allUploadsSuccess) { //as long as there wern't any errors during the upload of the files
            $subDirOfUpload = subDirNameFromDate($fileUploadReportSinglePdfs[0][2]); //create year-month i.e. 2018-03 for use as subfolder name from the first listed filename in the upload report
            $destinationPath = $uploadsDir."/".$subDirOfUpload."/"; //path where the existing pdfs are and where the resultant multi one is to go (permissions must allow server php to write to this path)
            $pdfMergr = "PDFMerger"; //do this in an effort to avoid deprecation error of calling an instance the same name as the class (apparently! - according to a forum)
            $nextUnusedFileNum = getNextFileSufixNumFromUploadDate($fileUploadReportSinglePdfs[0][2], $uploadsDir); //used to create the file number that comes after the the last one that is used in the current upload - to temporarily store multi file. This place holder should be unused as the file namNumbers are allocated in order of need and are never released for use except by this function itself when they are used temporarily
            $tempMultiFilename = dateFromFilenameNumDotPdf($fileUploadReportSinglePdfs[0][2])."-".$nextUnusedFileNum.".pdf"; //create a temporary file name using the original file nameDate and the next free file number

            //pdfMerger SECTION THAT MERGES ALL THE SELECTED EXISTING PDFS INTO ONE FILE
            $multiPdf = new $pdfMergr();            
            foreach ($fileUploadReportSinglePdfs as $indvidualPdfDetails) { //process all uploaded converted files one at a time
                $filename = $indvidualPdfDetails[2]; //get filename i.e. 2018-02-07-5.pdf
                $multiPdf->addPDF($destinationPath.$filename, 'all');
            }
            $multiPdf->merge('file', $destinationPath.$tempMultiFilename); //MAIN COMMAND THAT MERGES THE UPLOADED PDF FILES INTO ONE FILE !


            foreach ($fileUploadReportSinglePdfs as $indvidualPdfDetails) { //goes through all uploaded filenames and deletes them
                $filenameToDelete = $indvidualPdfDetails[2]; //extract filename i.e. 2018-02-07-5.pdf
                unlink($destinationPath.$filenameToDelete); //delete file
            }
            rename($destinationPath.$tempMultiFilename, $destinationPath.$fileUploadReportSinglePdfs[0][2]); //change num of temporarily saved merged file to take the place of the lowest numbered file in the sequence of files that have just been deleted
            $convertedFileSize = parseFileSizeForDisplay(filesize($destinationPath.$fileUploadReportSinglePdfs[0][2])); //get filesize of newly renamed merged file
            $imageForPageCount = new Imagick();
            $imageForPageCount->pingImage($destinationPath.$fileUploadReportSinglePdfs[0][2]);            
            $pageCount = $imageForPageCount->getNumberImages();
            $fileMergeReport = Array("", "", $fileUploadReportSinglePdfs[0][2], $convertedFileSize, $pageCount, "Merge success", TRUE); //report on success of pdf merge
        }
        else {
            $fileMergeReport = Array("", "", "", "", "", "Error in some pdfs", FALSE);
        }
    }
    return array($fileMergeReport); //return as index [0] of an array to match the way data is presented in  uploadJpgFilesToSnglPdfs()
}



/* Takes already uploaded, converted and numbered pdf files - detailed in $fileUploadReportSinglePdfs - and merges them together into one high quality pdf in the calculated subdir of $uploadsDir (compared with Imagick's effort at merging mixed jpeg/pdf files). An array is returned with the following information:
array ([0]=>array(  [0]=>, [1]=>pageCount, [2]=>MergedPdf, [3]=>MergedFileSize, [4]=>Success/Failure-Reason, [5]=>Success-TRUE/FALSE ) )
-
Uses pdftk (linux shell program that must be installed) to improve on original PDFMerger (that was php based) because it had page format (size) and clipping problems.
 */
function mergePdfs($fileUploadReportSinglePdfs, $uploadsDir) {
    $fileMergeReport = Array("", "", "", "", "", "No uploaded pdfs", FALSE); //default report for if there are no previously upladed and converted pdfs to merge!
    if (0 < count($fileUploadReportSinglePdfs)) { //as long as there are some uploaded pdfs to merge
        $allUploadsSuccess = TRUE;
        foreach ($fileUploadReportSinglePdfs as $indvidualPdfDetails) { //checks all uploaded converted files and sets $allUploadsSuccess to FALSE if any file had an error
            if (!$indvidualPdfDetails[6]) { //execute if statement if error in current file
                $allUploadsSuccess = FALSE;
            }
        }
        if ($allUploadsSuccess) { //as long as there wern't any errors during the upload of the files
            $subDirOfUpload = subDirNameFromDate($fileUploadReportSinglePdfs[0][2]); //create year-month i.e. 2018-03 for use as subfolder name from the first listed filename in the upload report
            $destinationPath = $uploadsDir."/".$subDirOfUpload."/"; //path where the existing pdfs are and where the resultant multi one is to go (permissions must allow server php to write to this path)
            $nextUnusedFileNum = getNextFileSufixNumFromUploadDate($fileUploadReportSinglePdfs[0][2], $uploadsDir); //used to create the file number that comes after the the last one that is used in the current upload - to temporarily store multi file. This place holder should be unused as the file namNumbers are allocated in order of need and are never released for use except by this function itself when they are used temporarily
            $tempMultiFilename = dateFromFilenameNumDotPdf($fileUploadReportSinglePdfs[0][2])."-".$nextUnusedFileNum.".pdf"; //create a temporary file name using the original file nameDate and the next free file number
            //pdftk SECTION THAT MERGES ALL THE SELECTED EXISTING PDFS INTO ONE FILE
            $fileListCsv = ""; //empty string that will contain list of filenames (with paths) to be merged           
            foreach ($fileUploadReportSinglePdfs as $indvidualPdfDetails) { //process all uploaded converted files one at a time
                $filename = $indvidualPdfDetails[2]; //get filename i.e. 2018-02-07-5.pdf
                $fileListCsv = $fileListCsv." ".$destinationPath.$filename;
            }
            shell_exec("pdftk ".$fileListCsv." cat output ".$destinationPath.$tempMultiFilename);
            foreach ($fileUploadReportSinglePdfs as $indvidualPdfDetails) { //goes through all uploaded filenames and deletes them
                $filenameToDelete = $indvidualPdfDetails[2]; //extract filename i.e. 2018-02-07-5.pdf
                unlink($destinationPath.$filenameToDelete); //delete file
            }
            rename($destinationPath.$tempMultiFilename, $destinationPath.$fileUploadReportSinglePdfs[0][2]); //change num of temporarily saved merged file to take the place of the lowest numbered file in the sequence of files that have just been deleted
            $convertedFileSize = parseFileSizeForDisplay(filesize($destinationPath.$fileUploadReportSinglePdfs[0][2])); //get filesize of newly renamed merged file
            $imageForPageCount = new Imagick();
            $imageForPageCount->pingImage($destinationPath.$fileUploadReportSinglePdfs[0][2]);            
            $pageCount = $imageForPageCount->getNumberImages();
            $fileMergeReport = Array("", "", $fileUploadReportSinglePdfs[0][2], $convertedFileSize, $pageCount, "Merge success", TRUE); //report on success of pdf merge
        }
        else {
            $fileMergeReport = Array("", "", "", "", "", "Error in some pdfs", FALSE);
        }
    }
    return array($fileMergeReport); //return as index [0] of an array to match the way data is presented in  uploadJpgFilesToSnglPdfs()
}




/* Uses multidimensional array $filesToBeUploaded to get a list of file names and details that are to be uploaded. Attempts to convert files to pdfs and move them (they will have already been uploaded from the client and given temporary names) to a subdirectory $subDirForThisUpload (i.e. 2018-03) of $uploadsDir (i.e. "../uploads", which must be in correct relationship to the directory in which this script is run). The pdf filenames will follow the regime given in $pdfDateName and $pdfFileNum which give the date name of the file (i.e. 2018-02-11) and the number of the file for that date name if more than one file will exist for that date name i.e. 2018-05-14-12.pdf . File numbers will be automatically incremented where more than one file is to be created. Uploaded files can be either one or more individual jpg or pdf files that will be converted into individual pdfs.
    allowed file extensions  "jpg", "JPG", "jpeg", "JPEG", "pdf", "PDF".
    $maxSize - max allowed upload size for each file in bytes.   
    $allowedTypes - allowed file upload mime types passed as an array, e.g. array("text/plain", "application/pdf", "image/jpeg") etc.
If the subdirectory doesn't exist one will be created with write permissions for the php installation on the server as long as there are error free files to copy.
A fileUploadReport array that records details of files that have been successfully uploaded or have failed will be returned. Its structure is:
array([0]=>array(  [0]=>sourceFileName  [1]=>sourceFilesize, [2]=>pdfDateName, [3]=>convertedFileSize, [4]=>numberOfPages [5]=>Success/Failure-Reason, [6]=>Success-TRUE/FALSE ), 
      [1]=>array(  [0]=>sourceFileName, [1]=>sourceFilesize, [2]... etc.)... etc.)
If no file(s) have been selected for upload no new subdirectory of $uploadsDir on the server will be created and an empty array is returned.
*/
function uploadJpgFilesToSnglPdfs($filesToBeUploaded, $maxSize, $uploadsDir, $pdfDateName) {
    global $_ImagickExceptionVisibility;
    $pdfFileNum = getNextFileSufixNumFromUploadDate($pdfDateName, $uploadsDir);
    $subDirForThisUpload = subDirNameFromDate($pdfDateName); //create year-month i.e. 2018-03 for use as subfolder name
    $fileUploadReport = Array(); //initialise file upload array that will be used to record details of files that have been successfully uploaded or have failed.
    $sourceFilesErrorFree = TRUE;
    $sourceFilesExist = FALSE; //flag to allow multifile pdf routine to run needs to be set by foreach ($filesToBeUploaded['name'] as $fileIndex=>$srcFileName) loop below
    $destinationPath = $uploadsDir."/".$subDirForThisUpload."/"; //set destination path (permissions must allow php on the server to write to this path)
    $allowedExts = array("jpg", "JPG", "jpeg", "JPEG", "pdf", "PDF");
    $allowedTypes = array("image/jpeg", "image/jpg", "application/pdf", "application/x-pdf"); //mime file types that will be accepted
    foreach ($filesToBeUploaded['name'] as $fileIndex=>$srcFileName) { //runs this loop for each uploaded file. If no files have been selected for upload the loop will never run.      
        if ($filesToBeUploaded['error'][$fileIndex] == 4) { //if no file has been uploaded.
            continue; //don't process what follows but continue from start of 'foreach' loop with next index.
        }
        $sourceFilesExist = TRUE; //set this flag to true to allow multifile pdf routine to run (would inhibit it and thus the unwanted creation of a subdir if no files are uploaded)
        if ($filesToBeUploaded['error'][$fileIndex] == 0) { //if no file errors.
            $srcFileType = $filesToBeUploaded['type'][$fileIndex]; //get file details
            $fileSize = $filesToBeUploaded['size'][$fileIndex];
            $tempFileName = $filesToBeUploaded['tmp_name'][$fileIndex];
            $fileSizeMKB = parseFileSizeForDisplay($fileSize); //convert file size to be human readable in MB / KB / B.
            $nameExtArray = (explode(".", $srcFileName)); //split file into array of [0] name, [1] extension
            $extension = end($nameExtArray); //get file extension - last (end) element of array
            $destinationfileName = $pdfDateName."-".$pdfFileNum.".pdf"; //set destination filename
            if (in_array($srcFileType, $allowedTypes)) { //check for allowed file type.
                if (in_array($extension, $allowedExts)) { //check for allowed file .ext.
                    if ($fileSize < $maxSize) { //check for allowed file size.
                        

                        //create several individual pdf files (one on each iteration of this foreach loop)
                        if (!file_exists($uploadsDir."/".$subDirForThisUpload)) { //create subdirectory of uploads dir for this upload.
                            mkdir($uploadsDir."/".$subDirForThisUpload, 0755, TRUE);
                        }
                        $pdfFileCreated = TRUE; //flag to indicate to error routine whether pdf file has been created
                        $Image = "";
                        try {
                            if (($extension == "pdf") || ($extension == "PDF")) { //not a jpg file so simply move to destination and don't process with imageMagick
                                if (move_uploaded_file($tempFileName, $destinationPath.$destinationfileName)) { //move file to designated destination giving it the original name.
                                    $imageForPageCount = new Imagick();
                                    $imageForPageCount->pingImage($destinationPath.$destinationfileName);
                                    $pageCount = $imageForPageCount->getNumberImages();
                                    $fileUploadReport[] = array($srcFileName, $fileSizeMKB, $pdfDateName."-".$pdfFileNum.".pdf", $fileSizeMKB, $pageCount, "converted successfully", TRUE); //create item in array to record file name and size of successfully uploaded file (no converted size difference but it's included for uniformity.
                                    $pdfFileNum++; //increment pdf filename number
                                }
                                else {
                                    $pdfFileCreated = FALSE;
                                }
                            }
                            else { //THIS IS THE IMAGEMAGICK SECTION - CONVERT SINGLE JPEG TO SINGLE PAGE PDF !!
                                $image = array($tempFileName); //create an array with the single current filename for this loop in it
                                $pdfFileObject = new Imagick($image); //creates an Imagick object of the current uploaded jpeg file
                                $pdfFileObject->setImageFormat('pdf'); //sets the type to be converted to to pdf
                                $pageCount = $pdfFileObject->getNumberImages();
                                $pdfFileObject->setImageFilename($destinationPath.$destinationfileName); //sets the destination folder/filename for the file to be written once it is converted to a pdf
                                //LATEST: all files were set to www-data:chris and 770 because cron job for dumping the database and backing up at 3am was not working because it runs under chris. CH 2018-10-22
                                //OLDER: if ($pdfFileObject->writeImages($destinationPath.$destinationfileName, TRUE)) { //used originally - commented out and above and below lines were added instead while trying to solve a write 'exception: unauthorised... ' that seemed to arise spontaniously (though could have been as a result of some update or change) but turned out to be the pdf section in /etc/ImageMagick-6/policy.xml having 'none' instead of 'read|write' in it. Even though these changes in uploadJpgFilesToPdfs() turned out not to have any effect it was left in the new form. To get file writes to work fully (and not just to a '/chtest' dir that had been set up with www-data:www-data and 775 permisions) a final 'sudo -R chown www-data:www-data monytalyData' was performed from /var (all with 755 permisions too). This seemed to cure a permission problem when attempting to write even though all the folders in the tree already had these ownerships/group as far as was known. CH 2018-10-06
                                if ($pdfFileObject->writeImage()) {
                                    $convertedFileSize = parseFileSizeForDisplay(filesize($destinationPath.$destinationfileName));
                                    $fileUploadReport[] = array($srcFileName, $fileSizeMKB, $pdfDateName."-".$pdfFileNum.".pdf", $convertedFileSize, $pageCount, "converted successfully", TRUE); //create item in array to record file name and size of successfully uploaded file.
                                    $pdfFileNum++; //increment pdf filename number for use with individual files (not incremented with createMultiPagePdf TRUE so just the single given filename number will be used)
                                }
                                else {
                                    $pdfFileCreated = FALSE;
                                }
                            }                                                                       
                        }
                        catch(ImageException $e) {
                            $pdfFileCreated = FALSE;
                            if ($_ImagickExceptionVisibility) {
                                echo 'Message: ' .$e->getMessage();
                                $Image = $e;
                            }
                        }
                        if (!$pdfFileCreated) {
                            $fileUploadReport[] = array($srcFileName, $fileSizeMKB, "", "0kB", "", "not converted! [".$srcFileType."] ".$ImagickError, FALSE);
                            continue; //go straight back to start of 'foreach' loop with next index.
                        }



                    }
                    else {
                        $fileUploadReport[] = array($srcFileName, $fileSizeMKB, "", "0kB", "", "too big (max 10MB). [".$srcFileType."]", FALSE);
                        $sourceFilesErrorFree = FALSE;
                        continue; //go straight back to start of 'foreach' loop with next index.
                    }
                }
                else {
                    $fileUploadReport[] = array($srcFileName, $fileSizeMKB, "", "0kB", "", "invalid extension. [".$srcFileType."]", FALSE); //include details of file type in case it is wrong as well as ext.
                    $sourceFilesErrorFree = FALSE;
                    continue; //go straight back to start of 'foreach' loop with next index.
                }
            }
            else {
                $fileUploadReport[] = array($srcFileName, $fileSizeMKB, "", "0kB", "", "invalid type. [".$srcFileType."]", FALSE);
                $sourceFilesErrorFree = FALSE;
                continue; //go straight back to start of 'foreach' loop with next index.
            }
        }
    }
    return $fileUploadReport; //report of file uploads/failures. If no file has been selected for upload an empty array will be returned.
}



/* Uses multidimensional array $filesToBeUploaded to get a list of file names and details that are to be uploaded. Attempts to convert files to pdfs and move them (they will have already been uploaded from the client and given temporary names) to a subdirectory $subDirForThisUpload (i.e. 2018-03) of $uploadsDir (i.e. "../uploads", which must be in correct relationship to the directory in which this script is run). The pdf filenames will follow the regime given in $pdfDateName and $pdfFileNum which give the date name of the file (i.e. 2018-02-11) and the number of the file for that date name if more than one file will exist for that date name i.e. 2018-05-14-12.pdf . File numbers will be automatically incremented where more than one file is to be created. Uploaded jpg files can be either one or more individual files that will be converted into individual pdfs, or several files making up a multipage document that will be converted into one pdf. If $createMultiPagePdf is TRUE the upload will be treated as a multipage doument. Files will only be converted and moved as long as each individual one meets the constraints placed on it:
	allowed file extensions  "jpg", "JPG", "jpeg", "JPEG".
    $maxSize - max allowed upload size for each file in bytes.
    $allowedTypes - allowed file upload mime types passed as an array, e.g. array("text/plain", "application/pdf", "image/jpeg") etc.
If the subdirectory doesn't exist one will be created with write permissions for the php installation on the server as long as there are error free files to copy (must be all error free for multipage pdf).
A fileUploadReport array that records details of files that have been successfully uploaded or have failed will be returned. Its structure is:
array([0]=>array(  [0]=>pdfDateName, [1]=>pdfFileNum, [2]=>"pdf", [3]=>numOfPages [4]=>sourceFileName  [5]=>Filesize, [6]=>Success/Failure-Reason, [7]=>Success-TRUE/FALSE, [8]=>IsOutputFile    ), [1]=>array([0]=>Filename, [1]=>Filesize, [2].. ), [2]... etc.)
If no file(s) have been selected for upload no new subdirectory of $uploadsDir on the server will be created and an empty array is returned.
*/
function uploadJpgFilesToPdfsDEPRECATED($filesToBeUploaded, $maxSize, $uploadsDir, $subDirForThisUpload, $pdfDateName, $pdfFileNum, $createMultiPagePdf) {
    global $_ImagickExceptionVisibility;
    $fileUploadReport = Array(); //initialise file upload array that will be used to record details of files that have been successfully uploaded or have failed.
    $sourceFilesErrorFree = TRUE;
    $sourceFilesExist = FALSE; //flag to allow multifile pdf routine to run needs to be set by foreach ($filesToBeUploaded['name'] as $fileIndex=>$srcFileName) loop below
    $destinationPath = $uploadsDir."/".$subDirForThisUpload."/"; //set destination path (permissions must allow php on the server to write to this path)
    $allowedExts = array("jpg", "JPG", "jpeg", "JPEG", "pdf", "PDF");
    $allowedTypes = array("image/jpeg", "image/jpg", "application/pdf", "application/x-pdf"); //mime file types that will be accepted
    foreach ($filesToBeUploaded['name'] as $fileIndex=>$srcFileName) { //runs this loop for each uploaded file. If no files have been selected for upload the loop will never run.    	
        if ($filesToBeUploaded['error'][$fileIndex] == 4) { //if no file has been uploaded.
            continue; //don't process what follows but continue from start of 'foreach' loop with next index.
        }
        $sourceFilesExist = TRUE; //set this flag to true to allow multifile pdf routine to run (would inhibit it and thus the unwanted creation of a subdir if no files are uploaded)
        if ($filesToBeUploaded['error'][$fileIndex] == 0) { //if no file errors.
            $srcFileType = $filesToBeUploaded['type'][$fileIndex]; //get file details
            $fileSize = $filesToBeUploaded['size'][$fileIndex];
            $tempFileName = $filesToBeUploaded['tmp_name'][$fileIndex];
            $fileSizeMKB = parseFileSizeForDisplay($fileSize); //convert file size to be human readable in MB / KB / B.
            $nameExtArray = (explode(".", $srcFileName)); //split file into array of [0] name, [1] extension
            $extension = end($nameExtArray); //get file extension - last (end) element of array
            $destinationfileName = $pdfDateName."-".$pdfFileNum.".pdf"; //set destination filename
            if (in_array($srcFileType, $allowedTypes)) { //check for allowed file type.
                if (in_array($extension, $allowedExts)) { //check for allowed file .ext.
                    if ($fileSize < $maxSize) { //check for allowed file size.
                        if ($createMultiPagePdf) { //if the files uploaded are intended to be combined in a multipage pdf add the current name to the filenames array for use below
                    		$fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, " ", TRUE, FALSE); //create item in array to record source file names for multipage destination pdf
                    	}
                    	else { //create several individual pdf files (one on each iteration of this foreach loop)
                    		if (!file_exists($uploadsDir."/".$subDirForThisUpload)) { //create subdirectory of uploads dir for this upload.
				                mkdir($uploadsDir."/".$subDirForThisUpload, 0755, TRUE);
				            }
                            $pdfFileCreated = TRUE; //flag to indicate to error routine whether pdf file has been created
                            $ImagickError = "";
                            $image = array($tempFileName); //create an array with the single current filename for this loop in it
                            try { //THIS IS THE IMAGEMAGICK SECTION !!
								$pdfFileObject = new Imagick($image);
								$pdfFileObject->setImageFormat('jpg');
								$pdfFileObject->setImageFilename($destinationPath.$destinationfileName);
								//if ($pdfFileObject->writeImages($destinationPath.$destinationfileName, TRUE)) { //used originally - commented out and above and below lines were added instead while trying to solve a write 'exception: unauthorised... ' that seemed to arise spontaniously (though could have been as a result of some update or change) but turned out to be the pdf section in /etc/ImageMagick-6/policy.xml having 'none' instead of 'read|write' in it. Even though these changes in uploadJpgFilesToPdfs() turned out not to have any effect it was left in the new form. To get file writes to work fully (and not just to a '/chtest' dir that had been set up with www-data:www-data and 775 permisions) a final 'sudo -R chown www-data:www-data monytalyData' was performed from /var (all with 755 permisions too). This seemed to cure a permission problem when attempting to write even though all the folders in the tree already had these ownerships/group as far as was known. CH 2018-10-06
								if ($pdfFileObject->writeImage()) {
									$fileUploadReport[] = array($pdfDateName, $pdfFileNum, "pdf", 1, $srcFileName, $fileSizeMKB, "converted successfully", TRUE, TRUE); //create item in array to record file name and size of successfully uploaded file.
									$pdfFileNum++; //increment pdf filename number for use with individual files (not incremented with createMultiPagePdf TRUE so just the single given filename number will be used)
	                            }
	                            else {
	                            	$pdfFileCreated = FALSE;
	                            }	                                	                            	
                        	}
	                        catch(ImagickException $e) {
							  	$pdfFileCreated = FALSE;
                                if ($_ImagickExceptionVisibility) {
                                	echo 'Message: ' .$e->getMessage();
                                    $ImagickError = $e;
                                }
							}
							if (!$pdfFileCreated) {
								$fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, "not converted! [".$srcFileType."] ".$ImagickError, FALSE, FALSE);
								continue; //go straight back to start of 'foreach' loop with next index.
							}
						}
                    }
                    else {
                        $fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, "too big (max 10MB). [".$srcFileType."]", FALSE, FALSE);
                        $sourceFilesErrorFree = FALSE;
                        continue; //go straight back to start of 'foreach' loop with next index.
                    }
                }
                else {
                    $fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, "invalid extension. [".$srcFileType."]", FALSE, FALSE); //include details of file type in case it is wrong as well as ext.
                    $sourceFilesErrorFree = FALSE;
                    continue; //go straight back to start of 'foreach' loop with next index.
                }
            }
            else {
                $fileUploadReport[] = array("", "", "", 0, $srcFileName, $fileSizeMKB, "invalid type. [".$srcFileType."]", FALSE, FALSE);
                $sourceFilesErrorFree = FALSE;
                continue; //go straight back to start of 'foreach' loop with next index.
            }
        }
    }
	if ($createMultiPagePdf && $sourceFilesExist) { //if the files uploaded are intended to be combined in a multipage pdf and there is at least one source file
	    $pdfMultiFileCreated = TRUE; //flag to indicate to error routine whether pdf file has been created
	    $images = $filesToBeUploaded['tmp_name']; //array of temp file names with path (/tmp)
	    $numOfPages = sizeof($filesToBeUploaded['tmp_name']); //get the number of elements in the array of temp source file names to indicate how many pages in output pdf (if converted successfully)
        $destinationfileName = $pdfDateName."-".$pdfFileNum.".pdf"; //set destination filename
		if ($sourceFilesErrorFree) {
			if (!file_exists($uploadsDir."/".$subDirForThisUpload)) { //create subdirectory of uploads dir for this upload.
                mkdir($uploadsDir."/".$subDirForThisUpload, 0755, TRUE);
            }
		}
		try { //THIS IS THE IMAGEMAGICK SECTION !!
			$pdfFileObject = new Imagick($images);
			$pdfFileObject->setImageFormat('pdf');
			if ($pdfFileObject->writeImages($destinationPath.$destinationfileName, TRUE)) { // if file write successful and no error in uploaded files has been found (indicated by $allowMultiPagePdf)
				$reportTemp = array($pdfDateName, $pdfFileNum, "pdf", $numOfPages, " ", parseFileSizeForDisplay(filesize($destinationPath.$destinationfileName)), "created successfully", TRUE, TRUE); //create item in array to record file details
				array_unshift($fileUploadReport, $reportTemp); //inserts this report element in at position [0] of $fileUploadReport[] and reindexes other elements 1,2,3...
            }
            else {
            	$pdfMultiFileCreated = FALSE;
            }	                                	                            	
    	}
        catch(Exception $e) {
		  	$pdfMultiFileCreated = FALSE;
		}
		if (!$pdfMultiFileCreated) {
			$reportTemp = array("", "", "", 0, "", "", "NOT CREATED!", FALSE, FALSE);
			array_unshift($fileUploadReport, $reportTemp); //inserts this report element in at position [0] of $fileUploadReport[] and reindexes other elements 1,2,3...
		}
	}
    return $fileUploadReport; //report of file uploads/failures. If no file has been selected for upload an empty array will be returned.
}


/* Returns passed filesize (as an integer) formatted for display i.e: 256000 becomes 256KB, 3256000 becomes 3,256MB etc. */
function parseFileSizeForDisplay($fileSize) {
$fileSizeMKB = 0; //initialise variable to hold file size configured to be human readable in MB / kB / B.
    if ($fileSize >= 1048576) {
        $fileSizeMKB = $fileSize/1048576;
        $fileSizeMKB = number_format($fileSizeMKB, 1); //set to one decimal place with "," separating thousands.
        $fileSizeMKB .= "MB";
    }
    elseif ($fileSize >= 1024) {
        $fileSizeMKB = $fileSize/1024;
        $fileSizeMKB = number_format($fileSizeMKB, 1); //set to one decimal place with "," separating thousands.
        $fileSizeMKB .= "KB";
    }
    else {
        $fileSizeMKB = $fileSize;
        $fileSizeMKB .= "bytes"; 
    }
    return $fileSizeMKB;        
}



/* Returns a primary indexed array containing lines of the uploaded text file (already uploaded and stored on the server under a temporary file name) described by $fileToBeUploaded (which is itself an array containing name, type, tmp_name, error, size). The array starts with an index of 1 for the first line 2 for the second etc. Two versions of this returned array can be selected:
1) Each line is copied whole as a string into each index position of the primary array
2) If argument $fieldSeparater contains a value (such as comma "," or tab "\t") each index position in the primary array will contain a sub array made of sections of the current line split by $fieldSeparater.
 The file will only be loaded into the array if it meets the constraints placed on it:
    $maxSize - max allowed upload size for each file in bytes.
    $allowedExts - allowed file extensions passed as an array, e.g. array("doc", "txt", "jpg") etc.
    $allowedTypes - allowed file upload mime types passed as an array, e.g. array("text/plain", "application/pdf", "image/jpeg") etc.
A fileUploadReport that records details of the file that has been successfully uploaded, or has failed, will be returned as a subarray in the 0 index of the primary array. Its structure is:
Array ( [0] => TRUE/FALSE [1] => Success/Failure-Reason [2] => Filename [3] => Filesize (human readable) [4] => Filetype (mime) )
If no file has been selected for upload a 'FALSE, failed - no file uploaded' message is returned in index 0.
*/
function uploadFileToArray($fileToBeUploaded, $maxSize, $allowedTypes, $allowedExts, $fieldSeparater = "") {
    $fileUploadReport = ""; //initialise file upload report string that will be used to record details of files that have been successfully uploaded or have failed.
    $fileGood = TRUE; //will be set to FALSE if any of tests fail.
    $linesArray = Array(); //initialise output array.
    $linesArray[0] = ""; //initialise reporting section [0] set to "".
    $lineIndex = 1;
    if ($fileToBeUploaded['error'] == 0) { //if no file errors.
        $fileName = $fileToBeUploaded['name']; //get file details
        $fileType = $fileToBeUploaded['type'];
        $fileSize = $fileToBeUploaded['size'];
        $tempFileName = $fileToBeUploaded['tmp_name'];
        $fileSizeMKB = 0; //initialise variable to hold file size configured to be human readable in MB / kB / B.
        if ($fileSize >= 1048576) {
            $fileSizeMKB = $fileSize/1048576;
            $fileSizeMKB = number_format($fileSizeMKB, 1); //set to one decimal place with "," separating thousands.
            $fileSizeMKB .= "MB";
        }
        elseif ($fileSize >= 1024) {
            $fileSizeMKB = $fileSize/1024;
            $fileSizeMKB = number_format($fileSizeMKB, 1); //set to one decimal place with "," separating thousands.
            $fileSizeMKB .= "KB";
        }
        else {
            $fileSizeMKB = $fileSize;
            $fileSizeMKB .= "Bytes"; 
        }
        if (!in_array($fileType, $allowedTypes)) { //check for allowed file type.
            $fileUploadReport = "FALSE, failed - invalid file type ";
            $fileGood = FALSE;
        }
        $splitFileName = explode(".", $fileName); //split file into array of name and extension
        $extension = end($splitFileName); //separate off file extension
        if (!in_array($extension, $allowedExts)) { //check for allowed file .ext.
            $fileUploadReport = "FALSE, failed - invalid extension ";
            $fileGood = FALSE;
        }
        if ($fileSize > $maxSize) { //check for allowed file size.
            $fileUploadReport = "FALSE, failed - file too big (max 10MB) ";
            $fileGood = FALSE;
        }
        if ($fileGood) { //all checks complete, continue with converting file to an array
            $file_handle=fopen($tempFileName, "r"); //open the temp version of the file (which will be deleted once this php script ends).
            try {
                while (($line_of_text = fgets($file_handle)) !== false) { //as long as there is still a line to read
                    if ($fieldSeparater) { //if a field separator value has been passed, break the current line into sections, creating a subarray which is added to the primary array at the current index pos.
                        $linesArray[$lineIndex] = explode($fieldSeparater, $line_of_text);
                    }
                    else { //put current line in indexed position in array as a string.
                        $linesArray[$lineIndex] = $line_of_text;
                    }
                    $lineIndex++; //increment index to next position in array                          
                }
            } catch(PDOException $e) {
                $fileUploadReport = "FALSE, ".$e->getMessage(); //if some other file read problem has occurred use error message to show why
                $fileGood = FALSE;
            }
        }
    }
    elseif ($fileToBeUploaded['error'] == 4) { //no file uploaded so return simple error message in array [0]
        $linesArray[0] = explode(",", "FALSE, failed - no file uploaded ");
        return $linesArray; //EXIT BY RETURN HERE
    }
    if ($fileGood) {
        $fileUploadReport = "TRUE, file successfully loaded into array ";
    }
    $fileUploadReport = $fileUploadReport.", ".$fileName.", ".$fileSizeMKB.", ".$fileType;
    $linesArray[0] = explode(",", $fileUploadReport); //convert csv $fileUploadReport to an array and load into primary array index 0
    return $linesArray;
}



/*Returns an array of strings of permitted file types, array("text/plain", "application/pdf", "image/jpeg") etc. */
function fileUploadTypes() {
    return array("text/plain", "application/pdf", "application/x-pdf", "image/jpeg", "image/jpg", "image/gif", "image/png", "image/bmp", "image/tif", "image/x-tif", "image/tiff", "image/x-tiff", "application/tif", "application/x-tif", "application/tiff", "application/x-tiff", "application/msword", "application/rtf", "application/vnd.ms-excel", "application/zip", "application/x-zip-compressed", "application/vnd.openxmlformats-officedocument.wordprocessingml.document", "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet", "application/vnd.oasis.opendocument.text", "application/vnd.oasis.opendocument.spreadsheet", "application/vnd.oasis.opendocument.graphics", "application/octet-stream", "application/vnd.cadsoft.eagle.board", "application/vnd.rs-274x", "application/vnd.ms-pki.stl", "application/vnd.corel-draw", "application/x-coreldraw", "application/vnd.openxmlformats-officedocument.presentationml.presentation", "model/iges", "text/plain", "application/x-forcedownload", "application/x-download", "application/vnd.ms-powerpoint", "application/x-octetstream", "text/tab-separated-values", "application/postscript", "text/csv"); //mime file types that will be accepted
}


/*Depending on argument returns either an array of strings of permitted file extensions, array("txt", "jpg", "doc"), or the same data as one single string in the form ".txt, .jpg, .doc" etc. */
function fileUploadExtensions($arrayOrString) {
    $arrayOfFileExtsLowrCase = array("mp3", "txt", "asc", "jpg", "jpeg", "gif", "png", "bmp", "tiff", "tif", "doc", "docx", "pdf", "zip", "iges", "igs", "dxf", "step", "stp", "sat", "stl", "prt", "sldprt", "asm", "sldasm", "drw", "slddrw", "ipt", "iam", "xls", "xlsx", "odt", "ods", "odg", "rtf", "dsn", "opj", "brd", "art", "drl", "rou", "bom", "dwg", "grb", "gto", "gtp", "gts", "gtl", "gp1", "g1", "gp2", "g2", "g3", "g4", "gbl", "gbs", "gbp", "gbo", "gko", "gm1", "gpt", "ncd", "cdr", "ppt", "pptx", "rpt", "rar", "skp", "tsv", "eps", "csv"); //allowed to browse and upload files with these extensions. MP3 IS INCLUDED TO ALLOW ERROR CHECKING OF FILE TYPES TO BE TESTED, IT IS NOT AN ACCEPTED TYPE.
    $arrayOfFileExtsUprAndLowrCase = array_merge($arrayOfFileExtsLowrCase, array_map("strtoupper", $arrayOfFileExtsLowrCase));
    if ($arrayOrString =="getArray") {
        return $arrayOfFileExtsUprAndLowrCase;  //return plain array as created above.
    }
    elseif ($arrayOrString =="getString") {
        return ".".implode(", .", $arrayOfFileExtsUprAndLowrCase);  //return sequence of file exts in string separated by a comma & space. Each will be pre-pended with a "." . (for use with <input type="file">).
    }
    else {
        return ""; //default return of empty string in case 'returnArray' is not set properly.
    }
}

/* Same as standard implode function but puts a space between each part of the string derived from the array argument without needing a separator parameter so good for use with array_map(). */
function implodeWithSpace($arry) {
    return implode(" ", $arry);
}

/* Takes a two dimensional indexed array and implodes each item in the inner array with spaces as separators and then implodes the outer array using "\n" (new line) as separators. */
function implodeTwoDimArrayWithNewLines($twoDimArray) {
    $flattenedTwoDimArray = "";
    foreach($twoDimArray as $index=>$innerArray) {
        $flattenedTwoDimArray .= implodeWithSpace($innerArray);
        $flattenedTwoDimArray .= "\n";
    }
    return $flattenedTwoDimArray;
}
























/*Creates a toggle button with shape, size, colours and hover characteristics given by $butOffClass when it is unselected and $butOnClass when it is selected. A <button> is used to form the button on screen with the css style set to cursor:pointer to indicate it is a button to be clicked. A none displayed text box is used to hold the value as the button is toggled and a javascript function setClass() changes the class of the <button> and the value of the text box (either "" or 1) to reflect the current state of the button. If editability is set to "ViewOnly" the button will only be displayed if its value is 1 and it won't be clickable. */
function buttonToggleSelector($butOffClass, $butOnClass, $editability, $id, $name, $value, $butText) {
    $initClass = $butOffClass;
    if ($value == 1) {
        $initClass = $butOnClass;
    }

    ?> <input style="display:none;" id="<?php echo $id.'textBx'; ?>" type="text" name="<?php echo $name; ?>" value="<?php echo $value; ?>"  > </input> <?php
    if ($editability == "ViewOnly") {
        if ($value == 1) {
            ?> <button class="<?php echo $initClass; ?>" type="button" id="<?php echo $id; ?>" ><?php echo $butText; ?></button><?php
        }
    }
    else{
        ?> <button class="<?php echo $initClass; ?>" type="button" id="<?php echo $id; ?>" onclick="setClass(this.id, '<?php echo $butOnClass; ?>', '<?php echo $butOffClass; ?>')"><?php echo $butText; ?></button><?php
    }
}


/*Creates a button with shape, size, colours and hover characteristics given by $butOffClass when it is unselected and $butOnClass when it is selected. A <button> element is used to form the button on screen and the css style should be set to cursor:pointer to indicate it is a button to be clicked. A none displayed text box is used to hold the value and the button is named $name if it is selected and "" if it isn't by the javascript function setClassUnique() that changes the class of the <button> to reflect the current state of the button. If the button is clicked it is changed to an 'on' state and all other buttuns with the same $randId will be changed to an off state and their names changed to "". The initial state is determined by $initialState (1 or 0). If editability is set to "ViewOnly" the button will be displayed but won't be clickable. $randId is used to introduce a unique element to the button id so different ranks of buttons on the same form don't interfere with one another. $maxId is to set the loop max index in setClassUnique() when it is deselecting all buttons other than the one that has just been selected (id must begin at 0). */
function buttonUniqueSelector($butOffClass, $butOnClass, $editability, $id, $maxId, $randId, $name, $value, $initialState, $butText) {
    $butClass = $butOffClass;
    $butName = "";
    if ($initialState == 1) {
        $butClass = $butOnClass;
        $butName = $name;
    }

    ?> <input style="display:none;" id="<?php echo $id.$randId.'textBx'; ?>" type="text" name="<?php echo $butName; ?>" value="<?php echo $value; ?>"  > </input> <?php

    if ($editability == "ViewOnly") {
        ?> <button class="<?php echo $butClass; ?>" type="button" id="<?php echo $id.$randId; ?>" ><?php echo $butText; ?></button><?php
    }
    else{
        ?> <button class="<?php echo $butClass; ?>" type="button" id="<?php echo $id.$randId; ?>" onclick="setClassAndNameUnique('<?php echo $randId;?>', '<?php echo $id;?>', '<?php echo $maxId;?>', '<?php echo $butOnClass;?>', '<?php echo $butOffClass;?>', '<?php echo $name;?>')"><?php echo $butText; ?></button><?php
    }
}


/*Creates a button link in a table cell to call a url, with shape, size, colours and hover characteristics given by $butClass. url and a url payload (which could include ?aaaaa=bbbbb&ccccc=ddd etc.) and button text are passed as variables. */
function tableCellButtonLink($butClass, $attribute, $url, $urlPayload, $butText) {
    if ($attribute == "inhibit") {
        echo '<td><a class='.$butClass.' ></a></td>'; //no url link - effectively a dead button that does nothing
    }
    else { //render the button
        echo '<td><a class='.$butClass.' href=\''.$url.$urlPayload.'\'">'.$butText.'</a></td>';
    }
}


/* Creates an html link in a table cell linking to $url with $urlPayload concatonated. If a class is specified this will determine the look and feel. The displayed text is given by $text and the link is only created if $create is true. */
function tableCellHtmlLink($htmlLinkClass, $url, $urlPayload, $text, $create) {
    if ($create) {
        echo '<td><a class="'.$htmlLinkClass.'" href="'.$url.$urlPayload.'">'.$text.'</a></td>';
    }
}


/* Creates a text box into which hours can be entered and a live label showing accrued hours for the job allocation referenced by $personJobAssnmID. Classes determining the look and feel of the text box and label are passed as arguments as is the personhash of the person using the hoursBox (for the recording who added hours). $create true/false argument determines whether the the element will be created or not. In addition to updating the hours shown by the label the updateHours() script calls a another php script that calculates the new total hours for job referenced by $jobID and outputs the hours text to the label referenced by 'sumOfhours'.$jobID. */
function tableCellHoursBox($hoursClass, $labelClass, $personHash, $personJobAssnmID, $jobID, $hoursAllocated, $editable, $create) { 
    if ($create) {
        if ($editable) {
            $onchangeStr = "updateHours(this.value, '$personHash', '$personJobAssnmID', 'hours$personJobAssnmID', 'sumOfhours$jobID', '$jobID')";
        }
        else {
            $onchangeStr = "";
        }
        echo '
            <td> <input class="'.$hoursClass.'" type="text" id="'.$personJobAssnmID.'"    
            onchange="'.$onchangeStr.'"
            onkeydown="testForEnter();"
            >
            <label class="'.$labelClass.'" id="hours'.$personJobAssnmID.'" >'.$hoursAllocated.'</label> </td>

        ';
    }
} 


/* Creates a text box that will initially display the string $initialData. $classDefault determines the look and feel of the text box unless $dataToMatch1 == $dataToMatch2 in which case (as long as neither of them are empty) look and feel will be determined by $classDataMatched. $create true/false argument determines whether the the element will be created or not. When the text in the box is edited and the mouse clicked away from the box (focus lost) the updateTable() javascript calls updateTable.php which updates $fieldName in the row where argument $rowMatchValue matches the field $fieldToMatch in the table pointed to by $tableName. */
function tableCellEditableTxt($classDefault, $classDataMatched, $tableName, $fieldToMatch, $rowMatchValue, $fieldName, $initialData, $dataToMatch1, $dataToMatch2, $editable, $create) { 
    if ($create) {
        $class = $classDefault; //sets $class to default which remains as it is unless $initialData == $dataToMatch in which case (as long as $dataToMatch is not empty) $class will become $classDataMatched.

        if ($dataToMatch1 && $dataToMatch2 && ($dataToMatch1 == $dataToMatch2)) { //check that $dataToMatch1(&2) contains something (is not empty or "") AND $dataToMatch1 == $dataToMatch2.
            $class = $classDataMatched; //sets $class to $classDataMatched.
        }
        if ($editable) {
            $tableRowColMatchCsv = $tableName.",".$fieldToMatch.",".$rowMatchValue.",".$fieldName; //will be passed to the javascript 'onchange' function to act as a pointer to mySql table/field to match/row/field. Also acts as unique id for text box 
            $onchangeStr = "updateTable(this.value, '$tableRowColMatchCsv')";
            $readOnly = "";
        }
        else {
            $onchangeStr = "";
            $readOnly = "readonly";
        }
        echo '
            <td>
                <input class="'.$class.'" type="text" id="'.$tableRowColMatchCsv.'" value="'.$initialData.'" "'.$readOnly.'" onchange="'.$onchangeStr.'" onkeydown="testForEnter();">
            </td>

        ';
    }
} 


/* Creates an expanding textarea that will initially display the string $initialData. It will expand vertically to accommodate the text that is in it, including line breaks. Look and feel will be determined by $class. $create true/false argument determines whether the the element will be created or not, $editable determines whether changes will be passed via 'onchange' function. When the text in the box is edited and the mouse clicked away from the box (focus lost) the updateTable() javascript function calls updateTable.php for $tableName and updates $targetFieldName in the row where argument $matchValue finds a matching field in $columnToMatch. For consistent operation things should be arranged so that only one matching value is found in the table! Doesn't work properly with IE11 due to incomplete implementation of flexbox on IE11 - this is likely never to be fixed as IE11 is superseded by Microsoft Edge browser for that only works on Windows 10 and above!! The main problem on IE11 is. Needs the following javascript to be inserted at the bottom of the php document where expandingTextarea() is used:
<script>
  var areas = document.querySelectorAll('.expandingArea');
  var l = areas.length;
  while (l--) {
    makeExpandingArea(areas[l]);
  }
</script>
 */
function expandingTextarea($expandingTextareaClass, $width, $tableName, $columnToMatch, $matchValue, $targetFieldName, $initialData, $editable, $create, $pathToPhpFile, $fileRndm) { 
    if ($create) {
        if ($editable) {
            $tableRowColMatchCsv = $tableName.",".$columnToMatch.",".$matchValue.",".$targetFieldName; //will be passed to the javascript 'onchange' function to act as a pointer to mySql table/field to match/row/field. Also acts as unique id for text box 
            $onchangeStr = "updateTable('$tableName', '$targetFieldName', this.value, '$columnToMatch', '$matchValue', '$pathToPhpFile', '$fileRndm')";
            $readOnly = "";
        }
        else {
            $tableRowColMatchCsv = $tableName.",".$columnToMatch.",".$matchValue.",".$targetFieldName; 
            $onchangeStr = "";
            $readOnly = "readonly";
        }
        $widthStyle = "width:".$width."vw;";
        echo '
            <div class="'.$expandingTextareaClass.'" style="'.$widthStyle.'">
                <pre><span></span><br></pre>
                <p><textarea rows="4" cols="10"  style="white-space:pre-wrap; display:inline-block;" wrap="hard" id="'.$tableRowColMatchCsv.'" '.$readOnly.' onchange="'.$onchangeStr.'" >'.$initialData.'</textarea></p>
            </div>
        ';
    }
} 


/*Uses passed class, width and legend arguments to create an html table cell e.g. <td width=70px class="greyText">JobNum</td>. A true/false argument determines whether the the element will be created or not. If $onclickStr is passed as an argument the appropriate function will be executed - default is to do nothing */
function tableCell($cellClass, $width, $legend, $create, $id ="", $onclickStr = "") {
    if ($create) {
        echo '
            <td class="'.$cellClass.'"  id="'.$id.'"  width="'.$width.'px'.'" onClick="'.$onclickStr.'"  >  '.$legend.'  </td>
        ';
    }
}

/*Uses passed class, width and legend arguments to create an html table cell e.g. <td width=70px class="greyText">JobNum</td>. A true/false argument determines whether the the element will be created or not. If $onclickStr is passed as an argument the appropriate function will be executed - default is to do nothing */
function tableCellEditable($cellClass, $width, $legend, $create, $id ="", $onclickStr = "") {
    if ($create) {
        echo '
            <td class="'.$cellClass.'" contenteditable="true"  id="'.$id.'"  width="'.$width.'px'.'" onClick="'.$onclickStr.'"  >  '.$legend.'  </td>
        ';
    }
}

/*Uses passed class, width and legend arguments to create an html table cell e.g. <td width=70px class="greyText">JobNum</td>. A true/false argument determines whether the the element will be created or not. If $onclickStr is passed as an argument the appropriate function will be executed - default is to do nothing */
function tableCellHeadr($cellClass, $width, $legend, $create, $id ="", $onclickStr = "") {
    if ($create) {
        echo '
            <th    class="'.$cellClass.'"  id="'.$id.'"  width="'.$width.'px'.'" onClick="'.$onclickStr.'"  >  '.$legend.'  </th>
        ';
    }
}


/*Start table row using passed class and height arguments. A true/false argument determines whether the the element will be created or not. */
function tableStartRow($rowClass, $rowID, $height, $create) {
    if ($create) {
        echo '
            <tr    class="'.$rowClass.'"    id="'.$rowID.'"   height="'.$height.'px'.'"   >
        ';
    }
}


/*End table row. A true/false argument determines whether the the element will be created or not. */
function tableEndRow($create) {
    if ($create) {
        echo '
            </tr>
        ';
    }
}


/*splits a csv coordinate corner ('topLeftXY' or 'botRightXY' etc.) into X and Y components, adds xOffset to the X values and then reassembles and returns as csv. */
function offsetCsvXcoords($csvCoord, $xOffset) {
    $coordArray = explode(",", $csvCoord); //convert csv string to array
    $isXcoord = TRUE; //set to true as first coord is always an X.
    $index = 0;
    while($coordArray[$index]) {
        if($isXcoord) {
            $coordArray[$index] = $coordArray[$index] + $xOffset;
            $isXcoord = FALSE; //toggle to false 
        }
        else {
            $isXcoord = TRUE;
        }
    $index++;
    }
    return implode(",", $coordArray);
}


/*creates a checkbox for a form. If the csv string contains any values that match the checkbox value the checkbox will be ticked.*/
function checkBox($checkBoxClass, $checkBoxID, $onClick, $newLineStatus, $checkBoxVisibility, $checkBoxName, $checkBoxValue, $csvOfPreSelects, $checkBoxLabel) {
    $hide = "";
    $checked = "";
    if ($checkBoxVisibility == "hidden") {
        $hide = 'style="display:none;"';
    }
    if ($newLineStatus == "newLine") {
        echo'</br>';
    }
    if (in_array($checkBoxValue, explode(',', $csvOfPreSelects))) {
        $checked = "checked";
    }
    echo ' <span style="display:inline;">';
    echo '
        <input class="'.$checkBoxClass.'" '.$hide.' id="'.$checkBoxID.'" onClick="'.$onClick.'" type="checkbox" name="'.$checkBoxName.'" value="'.$checkBoxValue.'", '.$checked.'> </input>

    ';
    $checkBoxLabelID = $checkBoxID."lbl";
    echo '

        <label '.$hide.' id="'.$checkBoxLabelID.'" >'.$checkBoxLabel.'</label>
    ';
    echo'</span>';
}



function sendEmail($to, $message, $subject, $from) { //uses the server to send an email with the data passed in the arguments.
    $headers = "From:".$from;
    mail($to, $subject, $message, $headers);
    //echo "Mail Sent.\n";
}


/* Sends email with attachment. If attachment doesn't exist the attachment section is not implemented. The reply and from addresses should be included and only contain the part up to the '@' they will be suffixed with '@web.eng.gla.ac.uk'. Supposed to take HTML in the body of the message but doesn't seem to - needs more research. */
function sendEmailWithAttachment($to, $htmlbody, $subject, $from, $replyTo, $attachmentStr, $attachmentFileName, $attachmentMimeType) {
    $headers = "From: ".$from."\r\nReply-To:".$replyTo;
    $random_hash = md5(date('r', time()));
    $headers .= "\r\nContent-Type: multipart/mixed; 
    boundary=\"PHP-mixed-".$random_hash."\"";
    //define the body of the message.
    $message = "--PHP-mixed-$random_hash\r\n"."Content-Type: multipart/alternative; 
    boundary=\"PHP-alt-$random_hash\"\r\n\r\n";
    $message .= "--PHP-alt-$random_hash\r\n"."Content-Type: text/plain; 
    charset=\"iso-8859-1\"\r\n"."Content-Transfer-Encoding: 7bit\r\n\r\n";    
    $message .= $htmlbody; //Insert the html message.
    $message .="\r\n\r\n--PHP-alt-$random_hash--\r\n\r\n";
    if ($attachmentStr) { //if attachment exists (is not just "") append it to the message
    $attachment = chunk_split(base64_encode($attachmentStr));
    $message .= "--PHP-mixed-$random_hash\r\n";
    $message .= "Content-Type: ".$attachmentMimeType.";";
    $message .= " name=\"".$attachmentFileName."\"\r\n";
    $message .= "Content-Transfer-Encoding: base64\r\n";
    $message .= "Content-Disposition: attachment\r\n\r\n";
    $message .= $attachment; 
    $message .= "\r\n--PHP-mixed-$random_hash--";
    }
    $mail = mail( $to, $subject , $message, $headers ); //send email. if successful $mail is true, otherwise false.
    return $mail;
}


/*Appends the passed data to the passed file name. If the write fails for any reason "Failed is returned, otherwise the number of bytes written is returned.
 If the file doesn't exist it will be created. */
function appendToFile($filePathAndName, $dataToWrite) {
print_r(" ".$filePathAndName." ".$dataToWrite." ");
    $fileWriteResult = file_put_contents($filePathAndName, $dataToWrite, FILE_APPEND | LOCK_EX);
    if ($fileWriteResult === FALSE) {
        return "Failed";
    }
    else {
        return "Number of Bytes written = ".$fileWriteResult;
    }
}


/*Reads a file and calculates and returns the MD5 value for it. */
function getFileMD5($filePathAndName) {
    $fileContents = file_get_contents($filePathAndName);
    if ($fileContents === FALSE) {
        return "Failed";
    }
    else {
        return md5($fileContents);
    }
}


/*creates a dropdown selection box for a form. $selectClass = style data from style sheet, $selectName = name that will be returned with the POST method of the form, $selectBoxID is the id that will be used to refer to this selection box from javascript etc (i.e. to make it visible/invisible) - the label has the same id except it is suffixed with "lbl". $optionSelected is an input value which will pre-select an option value that it matches, $optionValuesArray = list of un-shown values one of which will be selected and returned, $optionDisplayArray = list of corresponding values that will be displayed in the drop down list (if this value is "Spacer" the word is not displayed but ----------- in its place). For any display values other than "Spacer" if the corresponding value in $optionStatusArray is 'Deprecated' nothing will be displayed unless the display value is also selected in which case it will be displayed with the word 'Deprecated' suffixed to it. $selectionLabel = name label for drop down box. If $selectBoxAttribute = "hidden" (instead of "visible") a hidden attribute is created in the select tag and label tag, this will make the selection box and label initially invisible though they should still pass the requisite data via the POST method. $paragraphStatus set to 'newPara' enables <p> tags to create select box on a new line, otherwise it can be set in-line beside another item. $onChange is for passing the function and arguments that will be called when a selection is made. BEWARE THAT IF NEW PARAGRAPH IS SET IT DOESN'T HAVE AN ID SO ATTEMPTS TO SHOW OR HIDE IT WITH JAVASCRIPT WILL NOT WORK, UNLIKE THE ACTUAL SELECTION BOX AND LABEL WHOSE VISIBILITY CAN BE CONTROLED VIA THEIR ID (AND ID+SUFFIX) IN THE LABEL'S CASE. */
function selectBox($selectClass, $selectBoxID, $onChange, $paragraphStatus, $selectBoxAttribute, $selectName, $optionSelected, $optionValuesArray, $optionDisplayArray, $optionStatusArray, $selectionLabel) {
    $hide = "";
    if ($selectBoxAttribute == "hidden") {
        $hide = 'style="display:none;"';
    }
    if ($paragraphStatus == "newPara") {
        echo'<p> </p>';
    }
    echo ' 
        <span style="display:inline;">
        <select class="'.$selectClass.'" id="'.$selectBoxID.'" onchange="'.$onChange.'" '.$hide.' type="text" name="'.$selectName.'"> 
    ';
    //counts up through the options list from $optionValuesArray & $optionDisplayArray creating a selectable option for each one, where $optionValuesArray matches $optionSelected it is set to "selected", although deprecated options will not normally be shown they will if they are selected
    $statIndex = 0;
    $arraySize = count($optionValuesArray);
    while ($statIndex < $arraySize) {
        if ($optionDisplayArray[$statIndex] == "Spacer") {
            echo '
                <option value = "'.$optionValuesArray[$statIndex].'" >-----------</option>
            ';
        }
        else {
            if ($optionValuesArray[$statIndex] == $optionSelected) {
                $selected = "selected";
            }
            else {
                $selected = "";
            }
            if (($optionStatusArray[$statIndex] == "Deprecated") && ($selected == "selected")) {
                $display = $optionDisplayArray[$statIndex]." (deprecated)";
                echo '
                    <option value = "'.$optionValuesArray[$statIndex].'" '.$selected.'  >'.$display.'</option>
                ';
            } 
            if ($optionStatusArray[$statIndex] == "Active") {
                echo '
                    <option value = "'.$optionValuesArray[$statIndex].'" '.$selected.'  >'.$optionDisplayArray[$statIndex].'</option>
                ';
            }
        }
        $statIndex++;
    }
    $selectBoxID = $selectBoxID."lbl";
    echo ' 
        </select>
        <label '.$hide.' id="'.$selectBoxID.'" >'.$selectionLabel.'</label>
        </span>
    ';
} 


/*creates a single line text box for a form. An onchange javascript function string can optionally be included at the end of the argument list. */
function textBox($textBoxClass, $textBoxID, $paragraphStatus, $textBoxVisibility, $textBoxName, $textBoxValue, $textBoxLabel, $onChangeStr="") {
    $hide = "";
    if ($textBoxVisibility == "hidden") {
        $hide = 'style="display:none;"';
    }
    if ($paragraphStatus == "newPara") {echo'<p></p>';}
    echo ' <span style="display:inline;">';
    echo '
        <input class="'.$textBoxClass.'" '.$hide.' onchange="'.$onChangeStr.'" id="'.$textBoxID.'"   type="text"     name="'.$textBoxName.'"   value="'.$textBoxValue.'"   onkeydown="testForEnter();" > </input>
    ';
    $textBoxID = $textBoxID."lbl";
    echo '
        <label '.$hide.' id="'.$textBoxID.'" >'.$textBoxLabel.'</label>
    ';
    echo'</span>';
}


/*creates a single line text box for a form. It is dissabled and cannot be edited and will not return a value when the form is submitted. Its purpose is to display things (line totals of a form). */
function textBoxDisabled($textBoxClass, $textBoxID, $paragraphStatus, $textBoxVisibility, $textBoxName, $textBoxValue, $textBoxLabel) {
    $hide = "";
    if ($textBoxVisibility == "hidden") {
        $hide = 'style="display:none;"';
    }
    if ($paragraphStatus == "newPara") {echo'<p></p>';}
    echo ' <span style="display:inline;">';
    echo '
        <input class="'.$textBoxClass.'" '.$hide.' id="'.$textBoxID.'"   type="text"     name="'.$textBoxName.'"   value="'.$textBoxValue.'" disabled  onkeydown="testForEnter();" > </input>
    ';
    $textBoxID = $textBoxID."lbl";
    echo '
        <label '.$hide.' id="'.$textBoxID.'" >'.$textBoxLabel.'</label>
    ';
    echo'</span>';
}

/*creates a multi line text box for a form. */
function textArea($textAreaClass, $paragraphStatus, $textAreaName, $textAreaValue, $textAreaLabel) {
    if ($paragraphStatus == "newPara") {echo'<p></p>';}
    echo '
        <span style="display:inline;">
              <textarea class="'.$textAreaClass.'" name="'.$textAreaName.'">'.$textAreaValue.'</textarea>
              <label>'.$textAreaLabel.'</label>
        </span>
   ';
}









/* returns a random $length digit hash for use with persons table (or anything else) */
function rndmHashUpTo32($length) {
return substr(str_shuffle(MD5(microtime())), 0, $length);
}




















/* Sanitise function that cleans up the passed variable. */
function sanitise($dirty) { 
    $cleaner = trim($dirty); //removes characters as below:
        // "\0" - NULL
        // "\t" - tab
        // "\n" - new line
        // "\x0B" - vertical tab
        // "\r" - carriage return
        // " " - ordinary white space.
    $evenCleaner = stripslashes($cleaner); //removes back slashes. ($_GET and $_POST use addslashes() to automatically add a back slash to each single quote ('), double quote ("), backslash (\), NULL.
    $clean = htmlspecialchars($evenCleaner); //converts some predefined characters to HTML entities as below:
        // & (ampersand) becomes &amp; (the entity includes the ";" at the end).
        // " (double quote) becomes &quot;
        // ' (single quote) becomes &#039;
        // < (less than) becomes &lt;
        // > (greater than) becomes &gt;
    return $clean;
}


/* Sanitising function to remove undesirable input characters. Works for single variables straight arrays and multi-dimensional arrays. */
function recursiveSanitiser($data) {
    if (is_array($data)) { //if the passed data is an array, deal with each element individually by re-entering this function.
        foreach($data as $key=>$element) {
            $sanitised[$key] = recursiveSanitiser($element);
        }
    }
    else { //the passed data is a siimple variable so it is processed by the sanitise function
        $sanitised = sanitise($data);
    }
    return $sanitised; 
}


/* Tests for presence of POST data and if it is present it is first imploded (converted from array to string) using either the default separator ',' or the passed separator. The string is then passed to recursiveSanitiser for sanitising and returned as a result. In the absence of POST data '' is returned by default or if a value has been passed to $emptyReturn it is returned. */
function sanAndImplodePost($pointerToPostData, $separator = ',', $emptyReturn = '') { 
    return empty($_POST[$pointerToPostData]) ? $emptyReturn : recursiveSanitiser(implode(',', $_POST[$pointerToPostData]));
}


/* Tests for presence of $_FILES data and if it is present it is passed to recursiveSanitiser for sanitising and returned as a result. In the absence of FILES data '' is returned by default or if a value has been passed to $emptyReturn it is returned. */
function sanFile($pointerToFileData, $emptyReturn = '') { 
    return empty($_FILES[$pointerToFileData]) ? $emptyReturn : recursiveSanitiser($_FILES[$pointerToFileData]);
}


/* CURRENTLY SET TO 'BAK' TO DETECT UNWITTING USE IN MONYTALY! (WILL PRODUCE ERROR MESSAGE)
Tests for presence of GET data and if it is present it is passed to recursiveSanitiser for sanitising and returned as a result. In the absence of GET data tests for presence of POST data with the same pointer. If present it is passed to recursiveSanitiser for sanitising and returned as a result. If POST is also empty '' is returned by default or if a value has been passed to $emptyReturn it is returned. */
function sanGetThenPostBAK($pointerToGetPostData, $emptyReturn = '') { 
    return empty($_GET[$pointerToGetPostData]) ?   (empty($_POST[$pointerToGetPostData]) ? $emptyReturn : recursiveSanitiser($_POST[$pointerToGetPostData])) : recursiveSanitiser($_GET[$pointerToGetPostData]);
}

/* Tests for presence of $_POST data and if it is present it is passed to recursiveSanitiser for sanitising and returned as a result. In the absence of POST data '' is returned by default or if a value has been passed to $emptyReturn it is returned. */
function sanPost($pointerToPostData, $emptyReturn = '') { 
    return empty($_POST[$pointerToPostData]) ? $emptyReturn : recursiveSanitiser($_POST[$pointerToPostData]);
}


/*Test function*/
function test() {
print_r("The test function in ./php/functions.php is working OK internally. ");
return "Stuff is being returned from the function too!";
}






function displayHiddenChars($line) {
    $line = str_replace(" ", "<span style='background-color:#E0E0E0; color:#FFFFFF;' >S</span>", $line);
    $line = str_replace("\r", "<span style='background-color:#A0C0FF; color:#FFFFFF;' >CR</span>", $line);
    $line = str_replace("\n", "<span style='background-color:#80E080; color:#FFFFFF;' >LF</span>", $line);
    $line = str_replace("\t", "<span style='background-color:#E0E0E0; color:#FFFFFF;' >TAB</span>", $line);
    return $line;
}


/* Prints an array in a hierarchical display using shifts to the right and newlines to help readability. Uses this function recursively! (difficult to explain how it all works)  */
function ary($maybeAry, $outerKey = "", $tab = "") {
    
    if (is_array($maybeAry)) {
        print_r($tab);
        if (($outerKey) || ($outerKey === 0)) {
            print_r("[".displayHiddenChars($outerKey)."] => ");
        }
        print_r("array (");
        print_r("</br>");
        $prevTab = $tab;
        $tab .= "&emsp;&emsp;&emsp;&emsp;";
        foreach ($maybeAry as $key => $value) {
            ary($value, $key, $tab);
        }
        print_r($prevTab);
        print_r(")");
        print_r("</br>");
    }
    else {
        print_r($tab);
        
        //print_r("[".displayHiddenChars($outerKey)."] => ".displayHiddenChars($maybeAry));
        print_r("[");
        pr($outerKey);
        print_r("] => ");
        pr($maybeAry);

        print_r("</br>");
    }
}


/* Special print function that will reveal the real value of a variable, i.e. NULL etc. It also prints an array in a display friendly way using ary() */
function pr($input) {
    if (is_array($input)) {
        ary($input);
    }
    elseif (is_null($input)) {
        print_r("!Null!");
    }
    elseif ($input === "") {
        print_r("!Empty zero length string!");
    }
    elseif ($input === FALSE) {
        print_r("!FALSE!");
    }
    elseif ($input === TRUE) {
        print_r("!TRUE!");
    }
    else {
        print_r(displayHiddenChars($input));
    }

}

function csvFromAry($array) {
    return implode(",", $array);
}

function aryFromCsv($csvString) {
    return explode(",", $csvString);
}

?>

