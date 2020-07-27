<?php


/* Creates a variable that is held in $nonVolatileArray so it persists through page loads. The name of the variable is defined (or redefined) by the constructor at every page load. If the variable has not previously been initialised and held in $nonVolatileArray, it will be set to the value of the argument $initValue, which defaults to "" if not passed.  Methods to set and get the value are provided and also to destroy it (which removes the storage location in $nonVolatileArray). If a destroyed and subsequently set() method is used, the storage will be recreated. Using get() after the array has been destroyed will return "". */
class persistVar {
	public $name;

	function __construct($name, $initValue = "") { //name that will be used as $nonVolatileArray key to preserve this variable value through page loads, also a reset flag
		$this->name = $name; // save locally so $nonVolatileArray can be modified as needed
		if (!ifNonVolAryKeyExists($name)) { //if $nonVolatileArray key doesn't exist create an empty array entry - ""
	    	setNonVolAryItem($name, $initValue);
	    }
	}

	function set($value) {
		setNonVolAryItem($this->name, $value);
	}

	function get() {
		if (!ifNonVolAryKeyExists($this->name)) { //if $nonVolatileArray key doesn't exist return ""
	    	return "";
	    }
	    else {
			return getNonVolAryItem($this->name); //return value in array
		}
	}

	function destroy() {
		if (ifNonVolAryKeyExists($this->name)) { //check that array key esists before trying to destroy item!
			destroyNonVolAryItem($this->name);
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
	    if (!ifNonVolAryKeyExists($this->legend) || $reset) { //check that the key held in $nonVolatileArray for the instantiated button exists and if it doesn't, or reset imposed, create and set to FALSE
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


/* Creates a filter array that is synced to $nonVolatileArray to carry it over between page loads. The array holds any number of fieldname => fieldvalue pairs (e.g. budget => 34) that are used to provide column filtering (e.g. show only records that have the budget column set to "FiSCAF Apr19"). For filtering use, a filter string is returned by getFiltStr() in the form "AND personOrOrg = 5 AND budget = 15" etc. for use in mariadb WHERE statements. The filter terms are updated by setFilter($cellId) which uses a clicked cell id to get the allRecords row idR and the display column index from which the field name and value of the clicked cell is derived. Once a fieldname => fieldvalue pair has been stored in the array, if the same pair are derived again by clicking a displayed filtered column this is interpreted as a cancel filter command for that column, and the fieldname => fieldvalue are removed. If the same fieldname but a different fieldvalue are derived from a click the existing pair is edited to keep the same field name but record the new field value. Methods getColIdxsAry(), replaceFilt() and mergeAryToFilt() allow interaction with the persistant filter data. */
class filterColumns {
	public $filtersAry = [];
	public $nonVolFiltAryKey = "";
	public $inhibitFilt = FALSE; 

	function __construct($nonVolFiltAryKey, $reset = FALSE) { //key for external filters array that will be used to preserve this class when it is instantiated
		$this->nonVolFiltAryKey = $nonVolFiltAryKey; // save locally so $nonVolatileArray can be modified as needed
		if (!ifNonVolAryKeyExists($nonVolFiltAryKey) || $reset) { //if $nonVolatileArray key for the instantiated filter doesnt exists or reset is TRUE, create a new array entry and set to [] (cleared)
	    	setNonVolAryItem($nonVolFiltAryKey, []);
	    }
	    else {
	    	$this->filtersAry = getNonVolAryItem($nonVolFiltAryKey);
	    }
	}

	function setFilter($cellId) {
		$cellIdAry = explode("-", $cellId);
		$recRowId = $cellIdAry[0];
		$colID = $cellIdAry[1];
		$fieldName = getFieldName($colID); //create fieldName from column id (0 - 12) e.g. "transCatgry" or "budget"
		//pr("Check = ".$recRowId." end check! ");
		$fieldValue = getRecFieldValueAtRow($recRowId, $fieldName); //gets the record value e.g. 5, 7, or "BAC"
		if (($fieldName == "referenceInfo") || ($fieldName == "recordNotes")) { //as value is not an index key but string enclose in single quotes for mariaDb query so it is not interpreted as a field name!
			$fieldValue = '\''.$fieldValue.'\''; //create string enclosed in single quotes for mariaDb query so it is not interpreted as a field name!!
		}
		if (array_key_exists($fieldName, $this->filtersAry)) { //requested filter column already exists in $filtersAry

			if ($this->filtersAry[$fieldName] == $fieldValue) { //same filter value has been chosen so interpret this as command to cancel this filter
				unset($this->filtersAry[$fieldName]); //remove key ($fieldName, e.g. "budget") and value (e.g. 7) from array so this column is no longer filtered
			}
			else { //set a new value for the filter
				$this->filtersAry[$fieldName] = $fieldValue;
			}
		}
		else { //create new filter with $fieldName (e.g. "budget") as key and $fieldValue (e.g. 7) as value
			$this->filtersAry[$fieldName] = $fieldValue;
		}
		setNonVolAryItem($this->nonVolFiltAryKey, $this->filtersAry);
	}

	function getFiltStr() {
		$filtStr = "";
		if (!$this->inhibitFilt) { //only return valid filter if inhibit is not TRUE, otherwise return ""
			foreach ($this->filtersAry as $curKey => $curFieldValue) {
				$filtStr .= " AND ".$curKey." = ".$curFieldValue;
			}
		}
		return $filtStr;
	}

	function getColIdxsAry() {
		$colIdxsAry = [];
		if (!$this->inhibitFilt) { //only return valid filter if inhibit is not TRUE, otherwise return []
			foreach ($this->filtersAry as $curKey => $curFieldValue) {
				$colIdxsAry[] = getColIndex($curKey);
			}
		}
		return $colIdxsAry;
	}

	function replaceFilt($newFiltAry) {
		$this->filtersAry = $newFiltAry;
		setNonVolAryItem($this->nonVolFiltAryKey, $this->filtersAry);
	}

	function mergeAryToFilt($newFiltAry) { //merges argument filterAry with the one held internally. Any existing key (e.g.$fieldName = "budget") will be overwritten with incoming value (e.g. $fieldValue = 7)
		foreach ($newFiltAry as $fieldName => $fieldValue) {
			$this->filtersAry[$fieldName] = $fieldValue;
		}
		setNonVolAryItem($this->nonVolFiltAryKey, $this->filtersAry);
	}

	function inhibit() {
		$this->inhibitFilt = TRUE;
	}
}

?>