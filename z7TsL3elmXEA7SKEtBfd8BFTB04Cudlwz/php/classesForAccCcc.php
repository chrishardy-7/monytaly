<?php


/* Provides simple means to interogate table data like budget, personOrg etc. to get string value from a key or a key from a string value. The tables are identified by the field names used in the allRecords table rather than the names of the tables themselves or the function names used to access them (both of which are historical and aren't closely related to the current use) */
class dataBaseTables {
	public $orgPersonsListAry; //array of all possible orgsOrPersons in alphabetical order ie: array([1] => RBS [8] => Robertson Tr [17] => Scottish Pwr [22] => Susan)
	public $transCatListAry;   //array of all possible org/person categories in alphabetical order ie: array([2] => Volunteer [9] => Robertson Trust Budget [1] => Pret a Mange Budget)
	public $accountListAry;    //array of all possible orgsOrPersons in alphabetical order ie: array([1] => General [8] => FP Cash [17] => Church Cash [22] => Build Float, [3] => RBS-00128252)
	public $budgetListAry;     //array of all possible org/person/account categories in alphabetical order ie: array([2] => Volunteer [9] => Robertson Trust Budget [1] => Pret a Mange Budget)
	public $umbrellaListAry;   //array of all possible doc tags in alphabetical order ie: array([2] => Church Building [9] => Church Flat [1] => Furniture Project [8] => IT Classes [3] => Leaders)
	public $docTypeListAry;    //array of all possible doc varieties in alphabetical order ie: array([1] => Letter [6] => Minutes [8] => Offering Statement [2] => Receipt [23] => Report [17])
	
	function __construct() {
		$this->orgPersonsListAry = getOrgOrPersonsList(); //gets array from database table
		$this->transCatListAry   = getorgPerCategories();
		$this->accountListAry    = getAccountList();
		$this->budgetListAry     = getBudgetList();
		$this->umbrellaListAry   = getDocTagData();
		$this->docTypeListAry    = getDocVarietyData();
	}

	function getStrValue($table, $key) { //returns the string value, selected by $key, from the identified table - "" is returned if the table identifier is a rogue or $key is 0. e.g getStrValue("budget", 30) would return "VAF Apr20".
		switch ($table) {
			case "personOrOrg":
				return aryValueOrZeroStr($this->orgPersonsListAry, $key);
			case "transCatgry":
				return aryValueOrZeroStr($this->transCatListAry,   $key);
			case "accWorkedOn":
				return aryValueOrZeroStr($this->accountListAry,    $key);
			case "budget":
				return aryValueOrZeroStr($this->budgetListAry,     $key);
			case "umbrella":
				return aryValueOrZeroStr($this->umbrellaListAry,   $key);
			case "docType":
				return aryValueOrZeroStr($this->docTypeListAry,    $key);
			default:
				return "";
		}
	}

	function getKey($table, $value) { //returns the key as an integer index for the first value found in the identified table - 0 is returned if the table identifier is a rogue or $value is not found. e.g getKey("budget", "VAF Apr20") would return 30.
		switch ($table) {
			case "personOrOrg":
				$key = array_search($value, $this->orgPersonsListAry);
    			break;
			case "transCatgry":
				$key = array_search($value, $this->transCatListAry);
    			break;
			case "accWorkedOn":
				$key = array_search($value, $this->accountListAry);
    			break;
			case "budget":
				$key = array_search($value, $this->budgetListAry);
    			break;
			case "umbrella":
				$key = array_search($value, $this->umbrellaListAry);
    			break;
			case "docType":
				$key = array_search($value, $this->docTypeListAry);
    			break;
			default:
				$key = 0;
    			break;
		}
		if ($key == FALSE) {
			return 0;
		}
		else {
			return $key;
		}
	}

}



/* Creates a variable that is held in $nonVolatileArray so it persists through page loads. The name of the variable is defined (or redefined) by the constructor at every page load. If the variable has not previously been initialised and held in $nonVolatileArray, it will be set to the value of the argument $initValue, which defaults to "" if not passed.  Methods to set and get the value are provided and also to destroy it (which removes the storage location in $nonVolatileArray). If a destroyed and subsequently set() method is used, the storage will be recreated. Using get() after the array has been destroyed will return "". */
class persistVar {
	public $name;

	function __construct($name, $initValue = "") { //name that will be used as $nonVolatileArray key to preserve this variable value through page loads, also a reset flag
		$this->name = $name; // save locally so $nonVolatileArray can be modified as needed
		if (!nonVolAryKeyExists($name)) { //if $nonVolatileArray key doesn't exist create an empty array entry - ""
	    	setNonVolAryItem($name, $initValue);
	    }
	}

	function set($value) {
		setNonVolAryItem($this->name, $value);
	}

	function get() {
		if (!nonVolAryKeyExists($this->name)) { //if $nonVolatileArray key doesn't exist return ""
	    	return "";
	    }
	    else {
			return getNonVolAryItem($this->name); //return value in array
		}
	}

	function destroy() {
		if (nonVolAryKeyExists($this->name)) { //check that array key esists before trying to destroy item!
			destroyNonVolAryItem($this->name);
		}
	}
}


/* Provides control over which money columns are displayed so that rows with either withdrawn or paidin can be selectively hidden, or both can be displayed.  */
class moneyCols {
	public $name;

	function __construct($name, $reset) {
		$this->name = $name; // save locally so $nonVolatileArray can be modified as needed
		if (!nonVolAryKeyExists($name) || $reset) { //if $nonVolatileArray key doesn't exist, or reset is true, set array entry to default "Both"
	    	setNonVolAryItem($name, ""); //reset by creating non volatile array key and setting value to ""
	    }
	}

	function setWithdrawnOnly() {
		setNonVolAryItem($this->name, "amountWithdrawn"); //copy to non vol array
	}

	function setPaidinOnly() {
		setNonVolAryItem($this->name, "amountPaidIn"); //copy to non vol array
	}

	function setBoth() {
		setNonVolAryItem($this->name, ""); //copy to non vol array
	}

	function getStr() {
		return getNonVolAryItem($this->name);
	}
}


/*   */
class familyCommand {
	public $name;
	public $editFamButSet;
	public $showFamButSet;

	function __construct($name, $editFamButSet, $showFamButSet, $reset) { //name that will be used as $nonVolatileArray key to preserve the family id through page loads, also a reset flag
		$this->name = $name; // save locally so $nonVolatileArray can be modified as needed
		$this->editFamButSet = $editFamButSet;
		$this->showFamButSet = $showFamButSet;
		if (!nonVolAryKeyExists($name) || $reset) { //if $nonVolatileArray key doesn't exist, or reset is true, create an array entry - 0
	    	setNonVolAryItem($name, 0);
	    }
	}

	function rememberShowFamButIsSet() {
		$this->showFamButSet = TRUE;
	}

	function inputFamId($value) { //inputs the current clicked family id
		if (getNonVolAryItem($this->name) == getFamilyId($value)) { //if NonVolAryItem family id is already set to passed value, set it to 0, else set it to passed value - this is a toggle action
			setNonVolAryItem($this->name, 0);
		}
		else {
			setNonVolAryItem($this->name, $value);
		}
	}

	function getCmnd() {
		if ($this->editFamButSet || $this->showFamButSet) {
			return "All";
		}
		else {
			if (0 < getNonVolAryItem($this->name)) {
				return getNonVolAryItem($this->name); //return value in array
			}
			else {
				return "NoKids";
			}
		}
	}

	function justFam() { //not sure where/if this is used
		if ($this->editFamButSet || $this->showFamButSet) {
			return FALSE;
		}
		else {
			if (0 < getNonVolAryItem($this->name)) {
				return TRUE; //a family number is set and neither editFamily nor showFamily buttons have been selected, so just the family on its own should be shown
			}
			else {
				return FALSE;
			}
		}
	}

	function getFiltInhib() {
		if ((getNonVolAryItem($this->name) != 0) && !$this->editFamButSet && !$this->showFamButSet) {
			return true;
		}
		else {
			return false;
		}
	}

	function destroy() {
		if (nonVolAryKeyExists($this->name)) { //check that array key esists before trying to destroy item!
			destroyNonVolAryItem($this->name);
		}
	}
}



/* Creates set of buttons with different legends. They work like radio buttons where selecting one deselects all the others, initial state will be all buttons deselected.  */
class buttonSet {
	public $setName;
	public $butClass;
	public $buttonClassOff;
	public $buttonClassOn;
	public $localLegendsAry = [];

	function __construct($setName, $buttonClassOff, $buttonClassOn, $reset) {
	    $this->setName = $setName;
	    $this->buttonClassOff = $buttonClassOff;
	    $this->buttonClassOn = $buttonClassOn;

	    if (!nonVolAryKeyExists($this->setName) || $reset) { //check that the key held in $nonVolatileArray for this button sequence exists and if it doesn't, or reset imposed, create and set to []
	    	setNonVolAryItem($this->setName, []);
	    }
	    else {
	    	$this->localLegendsAry = getNonVolAryItem($this->setName);
	    	$returnedSubCmnd = getPlainFromSubCmnd();
	    	if (array_key_exists($returnedSubCmnd, $this->localLegendsAry)) { //the returned subcommand matches one of the keys in the local array
	    		foreach ($this->localLegendsAry as $buttonKey => $state) {
	    			if ($buttonKey == $returnedSubCmnd) {
	    				$this->localLegendsAry[$buttonKey] = TRUE;
	    			}
	    			else {
	    				$this->localLegendsAry[$buttonKey] = FALSE;
	    			}
	    		}
	    		setNonVolAryItem($this->setName, $this->localLegendsAry);
	    	}
	    }
	}

	function drawBut($legend, $command, $fontAwesomeClass, $visibleTo = []) {		
		if(!array_key_exists($legend, $this->localLegendsAry)) {
			$this->localLegendsAry[$legend] = FALSE;
			setNonVolAryItem($this->setName, $this->localLegendsAry);
		}
		if ($this->localLegendsAry[$legend]) {
			?>
		    <button class='<?php echo $this->buttonClassOff;?>' type="submit" name="command" value=<?php echo getMenuRandomsArray("Show Records For Full Year")."-".getRand($legend);?>><i class='<?php echo $fontAwesomeClass;?>'></i> <?php echo $legend;?></button>
		    <?php
		}
		else {
			?>
		    <button class='<?php echo $this->buttonClassOn;?>' type="submit" name="command" value=<?php echo getMenuRandomsArray($command)."-".getRand($legend);?>><i class='<?php echo $fontAwesomeClass;?>'></i> <?php echo $legend;?></button>
		    <?php
		}
	}

	function getButLegend() {
		$returnedSubCmnd = getPlainFromSubCmnd();
		if (array_key_exists($returnedSubCmnd, $this->localLegendsAry)) {
			return $returnedSubCmnd;
		}
		else {
			return "";
		}
	}
 

}





/* Creates toggle button logic and draws button on the screen with fontawesome icon preceding the legend. ->drawBut() must come after new toggleBut() in the PHP code. The state will survive a new page load and only override to FALSE if $reset is TRUE. The current state (TRUE||FALSE) can be queried by ->isSet(). States for all buttons are held in $nonVolatileArray using the button legends as keys. The formatting for the button is passed to the constructor when it is created: new toggleBut($legend, $fontAwesomeClass, $buttonClassOff, $buttonClassOn, $reset). set() unset() methods allows the button to be set to on or off programatically after it has been instantiated BUT before it has been drawn.
-
SOME ASPECTS OF THE BUTTON ARE HARD CODED!!: 
type="submit" name="command"
value=<?php echo getMenuRandomsArray("Show Records For Full Year")."-".getRand($this->legend);?> 
*/
class toggleBut {
	public $legend;
	public $butClass;
	public $fontAwesomeClass;
	public $buttonClassOff;
	public $buttonClassOn;


	function __construct($legend, $fontAwesomeClass, $buttonClassOff, $buttonClassOn, $reset) {
	    $this->legend = $legend;
	    $this->fontAwesomeClass = $fontAwesomeClass;
	    $this->buttonClassOff = $buttonClassOff;
	    $this->buttonClassOn = $buttonClassOn;
	    if (!nonVolAryKeyExists($this->legend) || $reset) { //check that the key held in $nonVolatileArray for the instantiated button exists and if it doesn't, or reset imposed, create and set to FALSE
	    	setNonVolAryItem($this->legend, FALSE);
	    }
	    if ((getPlainFromSubCmnd() == $this->legend) && !$reset) { //if button clicked and reset not imposed, toggle the state held in $nonVolatileArray for the instantiated button
			if (getNonVolAryItem($this->legend) == TRUE) {
			    setNonVolAryItem($this->legend, FALSE);
			}
			else {
				setNonVolAryItem($this->legend, TRUE);
			}
		}
		if (getNonVolAryItem($this->legend) == TRUE) {
		    $this->butClass = $buttonClassOn;
		}
		else {
			$this->butClass = $buttonClassOff;
		}
	}

	function isSet() {
		return getNonVolAryItem($this->legend);
	}

	function set() {
		setNonVolAryItem($this->legend, TRUE);
		$this->butClass = $this->buttonClassOn;
	}

	function unSet() {
		setNonVolAryItem($this->legend, FALSE);
		$this->butClass = $this->buttonClassOff;
	}

	function drawBut() {
	?>
	    <button class='<?php echo $this->butClass;?>' type="submit" name="command" value=<?php echo getMenuRandomsArray("Show Records For Full Year")."-".getRand($this->legend);?>><i class='<?php echo $this->fontAwesomeClass;?>'></i> <?php echo $this->legend;?></button>
	    <?php
	}
}




/* Creates a filter array that is synced to $nonVolatileArray to carry it over between page loads. The array holds any number of fieldname => fieldvalue pairs (e.g. budget => 34) that are used to provide column filtering (e.g. show only records that have the budget column set to "FiSCAF Apr19"). For filtering use, a filter string is returned by getFiltStr() in the form "AND personOrOrg = 5 AND budget = 15" etc. for use in mariadb WHERE statements. The filter terms are updated by setIncludeFilter($cellId) which uses a clicked cell id to get the allRecords row idR and the display column index from which the field name and value of the clicked cell is derived. Once a fieldname => fieldvalue pair has been stored in the array, if the same pair are derived again by clicking a displayed filtered column this is interpreted as a cancel filter command for that column, and the fieldname => fieldvalue are removed. If the same fieldname but a different fieldvalue are derived from a click the existing pair is edited to keep the same field name but record the new field value. Methods getColIdxsAry(), replaceIncludeFiltAry() and mergeAryToIncludeFiltAry() allow interaction with the persistant filter data. */
class filterColumns {
	public $includeFiltersAry = [];
	public $excludeFiltersAry = [];
	public $nonVolFiltAryKey = "";
	public $tables = "";
	public $inhibitFilt = FALSE; 

	function __construct($nonVolFiltAryKey, $tables, $reset = FALSE) { //key for external filters array that will be used to preserve this class when it is instantiated, also reset command
		$this->nonVolFiltAryKey = $nonVolFiltAryKey; // save locally so $nonVolatileArray can be modified as needed
		$this->tables = $tables; // save locally so $tables can be used as needed
		if (!nonVolAryKeyExists($nonVolFiltAryKey."Include") || !nonVolAryKeyExists($nonVolFiltAryKey."Exclude") || $reset) { //if $nonVolatileArray key for the instantiated filter doesnt exists or reset is TRUE, create a new array entry and set to [] (cleared)
	    	setNonVolAryItem($nonVolFiltAryKey."Include", []);
	    	setNonVolAryItem($nonVolFiltAryKey."Exclude", []);
	    }
	    else {
	    	$this->includeFiltersAry = getNonVolAryItem($nonVolFiltAryKey."Include");
	    	$this->excludeFiltersAry = getNonVolAryItem($nonVolFiltAryKey."Exclude");
	    }
	}

	function setIncludeFilterUsingCellId($cellId) { //uses the clicked cell id to set an include only filter corresponding to the item in the clicked row/column i.e. "umbrella" column and item in row "Furniture Project"
		$cellIdAry = explode("-", $cellId);
		$recRowId = $cellIdAry[0];
		$colID = $cellIdAry[1];
		$fieldName = getFieldName($colID); //create fieldName from column id (0 - 12) e.g. "transCatgry" or "budget"
		//pr("Check = ".$recRowId." end check! ");
		$fieldValue = getRecFieldValueAtRow($recRowId, $fieldName); //gets the record value e.g. 5, 7, or "BAC"
		if (($fieldName == "referenceInfo") || ($fieldName == "recordNotes")) { //as value is not an index key but string enclose in single quotes for mariaDb query so it is not interpreted as a field name!
			$fieldValue = '\''.$fieldValue.'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
		}
		if (array_key_exists($fieldName, $this->includeFiltersAry)) { //requested filter column already exists in $includeFiltersAry

			if ($this->includeFiltersAry[$fieldName] == $fieldValue) { //same filter value has been chosen so interpret this as command to cancel this filter
				unset($this->includeFiltersAry[$fieldName]); //remove key ($fieldName, e.g. "budget") and value (e.g. 7) from array so this column is no longer filtered
			}
			else { //set a new value for the filter
				$this->includeFiltersAry[$fieldName] = $fieldValue;
			}
		}
		else { //create new filter with $fieldName (e.g. "budget") as key and $fieldValue (e.g. 7) as value
			$this->includeFiltersAry[$fieldName] = $fieldValue;
		}
		setNonVolAryItem($this->nonVolFiltAryKey."Include", $this->includeFiltersAry);
	}

	function setExcludeFilterUsingCellId($cellId) { //uses the clicked cell id to set an exclude filter corresponding to the item in the clicked row/column i.e. "umbrella" column and item in row "Furniture Project"
		$cellIdAry = explode("-", $cellId);
		$recRowId = $cellIdAry[0];
		$colID = $cellIdAry[1];
		$fieldName = getFieldName($colID); //create fieldName from column id (0 - 12) e.g. "transCatgry" or "budget"
		//pr("Check = ".$recRowId." end check! ");
		$fieldValue = getRecFieldValueAtRow($recRowId, $fieldName); //gets the record value e.g. 5, 7, or "BAC"
		if (($fieldName == "referenceInfo") || ($fieldName == "recordNotes")) { //as value is not an index key but string enclose in single quotes for mariaDb query so it is not interpreted as a field name!
			$fieldValue = '\''.$fieldValue.'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
		}
		if (array_key_exists($fieldName, $this->excludeFiltersAry)) { //requested filter column already exists in $excludeFiltersAry
			$this->excludeFiltersAry[$fieldName] = $this->excludeFiltersAry[$fieldName].",".$fieldValue; //add a new value filter value to the csv for this $fieldName
		}
		else { //create new filter with $fieldName (e.g. "budget") as key and $fieldValue (e.g. 7) as value
			$this->excludeFiltersAry[$fieldName] = $fieldValue;
		}
		setNonVolAryItem($this->nonVolFiltAryKey."Exclude", $this->excludeFiltersAry);
		//pr($this->excludeFiltersAry);
	}

	function setIncludeFilterUsingCellIdAndCellContentStr($cellId, $filterValueStr) { //uses the clicked cell id and the passed $filterValueStr to set an include only filter for the clicked column i.e. "umbrella" column (derived from the $cellId) and string value "Furniture Project"
		$cellIdAry = explode("-", $cellId);
		$recRowId = $cellIdAry[0];
		$colID = $cellIdAry[1];
		$fieldName = getFieldName($colID); //create fieldName from column id (0 - 12) e.g. "transCatgry" or "budget"
		//pr("Check = ".$recRowId." end check! ");
		$fieldValue = $this->tables->getKey($fieldName, $filterValueStr); //converts the filter string value to index number from appropriate table e.g. 5, 7, 
		if (($fieldName == "referenceInfo") || ($fieldName == "recordNotes")) { //as value is not an index key but string enclose in single quotes for mariaDb query so it is not interpreted as a field name!
			$fieldValue = '\''.$fieldValue.'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
		}
		if (array_key_exists($fieldName, $this->includeFiltersAry)) { //requested filter column already exists in $includeFiltersAry

			if ($this->includeFiltersAry[$fieldName] == $fieldValue) { //same filter value has been chosen so interpret this as command to cancel this filter
				unset($this->includeFiltersAry[$fieldName]); //remove key ($fieldName, e.g. "budget") and value (e.g. 7) from array so this column is no longer filtered
			}
			else { //set a new value for the filter
				$this->includeFiltersAry[$fieldName] = $fieldValue;
			}
		}
		else { //create new filter with $fieldName (e.g. "budget") as key and $fieldValue (e.g. 7) as value
			$this->includeFiltersAry[$fieldName] = $fieldValue;
		}
		setNonVolAryItem($this->nonVolFiltAryKey."Include", $this->includeFiltersAry);
	}

	function getFiltStr() {
		$filtStr = "";
		if (!$this->inhibitFilt) { //only return valid filter if inhibit is not TRUE, otherwise return ""
			foreach ($this->includeFiltersAry as $curInclKey => $curInclFieldValue) { //construct Include filter string
				$filtStr .= " AND ".$curInclKey." = ".$curInclFieldValue." ";
			}
			foreach ($this->excludeFiltersAry as $curExclKey => $curExclFieldValue) { //construct Include filter string
				$filtStr .= " AND ".$curExclKey." NOT IN (".$curExclFieldValue.") ";
			}
		}
		return $filtStr;
	}

	function getInclColIdxsAry() { //used by createStndDisplData() to provide colouring for filtered columns and also by group functions
		$colIdxsAry = [];
		if (!$this->inhibitFilt) { //only return valid filter if inhibit is not TRUE, otherwise return []
			foreach ($this->includeFiltersAry as $curKey => $curFieldValue) {
				$colIdxsAry[] = getColIndex($curKey);
			}
		}
		return $colIdxsAry;
	}

	function replaceIncludeFiltStrValAry($newInclFiltAry) { //replaces the filter array with a new one. Passed array is in the form columnName => strValue e.g. [["budget" => "Church Main"], ["Umbrella" => ...], ..]
		$convertedAry = [];
		foreach ($newInclFiltAry as $key => $value) { //convert columnName => strValue into columnName => dbTableIndex e.g. [["budget" => 7], ["Umbrella" => 23] etc.]
			$convertedAry[$key] = $this->tables->getKey($key, $value); //a bit confusing - think a bit about which key is which, i.e. the key of the passed filter array vs the key (index) of the DB table
		}
		$this->includeFiltersAry = $convertedAry;
		setNonVolAryItem($this->nonVolFiltAryKey."Include", $this->includeFiltersAry);
	}

	function replaceExcludeFiltStrValAry($newExclFiltAry) { //replaces the filter array with a new one. Passed array is in the form of indexed subarrays - columnName => strValue e.g.                     [["budget" => ["Church Main","None"], ["Umbrella" => ["Furniture Project"]], ..]
		$convertedAry = [];
		foreach ($newExclFiltAry as $subArray) { //$subArray = e.g. ["budget" => ["Church Main","None"]]
			$itemNameAry = [];
			foreach($subArray as $subAryKey => $subSubArray) { //only one iteration will happen - just a simple way of extracting the key and subSubArray
				foreach($subSubArray as $value) { 
					$itemNameAry[] = $this->tables->getKey($subAryKey, $value); //a bit confusing - think a bit about which key is which, i.e. the key of the passed filter array vs the key (index) of the table
				}
			}
			$convertedAry[$subAryKey] = csvFromAry($itemNameAry);
		}
		$this->excludeFiltersAry = $convertedAry;
		setNonVolAryItem($this->nonVolFiltAryKey."Exclude", $this->excludeFiltersAry);
	}

	function mergeAryToIncludeFiltAry($newInclFiltAry) { //merges argument filterAry with the one held internally. Any existing key (e.g.$fieldName = "budget") will be overwritten with incoming value (e.g. $fieldValue = 7)
		foreach ($newInclFiltAry as $fieldName => $fieldValue) {
			$this->includeFiltersAry[$fieldName] = $fieldValue;
		}
		setNonVolAryItem($this->nonVolFiltAryKey."Include", $this->includeFiltersAry);
	}

	function mergeAryToExcludeFiltAry($newExclFiltAry) { //merges argument filterAry with the one held internally. Any existing key (e.g.$fieldName = "budget") will be overwritten with incoming value (e.g. $fieldValue = 7)
		foreach ($newExclFiltAry as $fieldName => $fieldValue) {
			$this->excludeFiltersAry[$fieldName] = $fieldValue;
		}
		setNonVolAryItem($this->nonVolFiltAryKey."Exclude", $this->excludeFiltersAry);
	}

	function inhibit() {
		$this->inhibitFilt = TRUE;
	}
}

?>