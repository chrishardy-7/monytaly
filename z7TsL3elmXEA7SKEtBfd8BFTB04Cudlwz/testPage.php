<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$nameOfThisPage = "Test";

//include_once("./".$sdir."createMenuRndms.php");

include_once("./".$sdir."head.php");
include_once("./".$sdir."menu.php");


print_r($message); //USING TEST MESSAGE TEMPORARILY EVEN THOUGH THIS IS NOT AN EXIT PAGE

$numOfRows = 10;
$maxDropdownRows = 8;

$orgOrPersAry = [];
$OrgOrPersons = getOrgOrPersonsList();
foreach ($OrgOrPersons as $OrgOrPers) {
	$orgOrPersAry[] = $OrgOrPers;
}

$reasonAry = [];
$reasonsListAry = getorgPerCategories();           //NEEDS PX VALUES CONVERTED TO VW  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!
foreach ($reasonsListAry as $reason) {
	$reasonAry[] = $reason;
}


?>

<style>
	.pageDiv {
		/*flex-grow:1;
    	overflow:auto;*/
		width: 1200px;
		height: 750px;
		padding-top:30px;
        padding-left:150px;
        background-color: #FFFFFF;
    }

    .heading {
        color: #000000;
        font-size: 14px;
        font-weight: bold;
        word-wrap: break-word;
        width: 168px;
		min-height: 25px;
		border: none;
		margin-left: 20px;
        padding: 3px;
        background-color: #FFFFFF;
        cursor:default;
    }

	.wrapDiv {
		width: 175px;
        border: none;
        margin-left: 20px;
        margin-bottom: 5px;
        background-color: #FFFFFF;
        float:left;
    }

	.editCell {
        color: #000000;
        font-size: 14px;
        word-wrap: break-word;
        width: 170px;
		min-height: 25px;
		border-style: solid;
        border-width: 1px;
        border-color: #C0C0C0;
        padding: 3px;
        background-color: #FFFFFF;
        cursor: text;
       

    }

    .wordChoiceHide {
        color: #000000;
        font-size: 14px;
        word-wrap: break-word;
        width: 170px;
		min-height: 20px;
        border: none;
        padding: 3px;
        background-color: #FFFFFF;
        display:none;
        cursor:default;

    }

    .wordChoiceFromTableShow, .wordChoiceFromPrevChoicesShow, .wordChoiceFromTableShowFirst, .wordChoiceFromPrevChoicesShowFirst {
        color: #000000;
        font-size: 14px;
        line-height: 100%;
        word-wrap: break-word;
        width: 170px;
		min-height: 20px;
        border: none;
        padding-left: 3px;
        padding-right: 3px;
        padding-bottom: 3px;
        cursor:default;

    }

    .wordChoiceFromTableShowFirst {
        text-align: left;
        background-color: #E0E0E0;
    }

    .wordChoiceFromPrevChoicesShowFirst {
        text-align: right;
        background-color: #A0FFA0;
    }

    .wordChoiceFromTableShow {
        text-align: left;
        background-color: #E0E0E0;
    }

    .wordChoiceFromPrevChoicesShow {
        text-align: right;
        background-color: #A0FFA0;
    }

    .paddingTopNormal {
    	padding-top: 3px;
    }

    .paddingTopMore {
    	padding-top: 10px;
    }


</style>
<div class="pageDiv" id="pageDiv" onclick="hideDropDown(maxDropdownRows, 'pageDiv')">
	<div style="display:flex; flex-direction:row; align-items: stretch;">
		<div class="heading">Person</div>
		<div class="heading">Reason</div>
		<div class="heading">Amount</div>
	</div>
	<?php
	for ($row = 0; $row < $numOfRows; $row++) {
	?>
	<div id="rowContainer<?=$row?>" style="display:flex; flex-direction:row; align-items: stretch;">
		<?php
		inputDivWithDropdown("wrapDiv", "editCell", "wordChoiceHide", $row, "Person", $maxDropdownRows);
		inputDivWithDropdown("wrapDiv", "editCell", "wordChoiceHide", $row, "Reason", $maxDropdownRows);
		inputDivWithDropdown("wrapDiv", "editCell", "wordChoiceHide", $row, "Amount", $maxDropdownRows);		
		?>
	</div>
	<?php
	}
	?>
</div>


<script>

//var dropDownRow '<div class="<?=$dropdownDivsClass?>" id="<?=$rowNum?>-<?=$columnName?>-<?=$dropdownNum?>" onclick="copySelectedDropdown(event)"></div>'

var orgOrPersAry = <?=json_encode($orgOrPersAry)?>;
var reasonAry = <?=json_encode($reasonAry)?>;
var masterChoiceAry = {"Person":orgOrPersAry, "Amount":[],  "Reason":reasonAry};
var masterShiftAry = {"Person":[], "Amount":[],  "Reason":[]};
var numOfRows = <?=$numOfRows?>;
var maxDropdownRows = <?=$maxDropdownRows?>;
var idCurDroppedDown = "";
var dropDownShowClass = ""; //set by homeInOnStr() to set the class to either left aligned for original table values or right aligned for list of values already chosen

//consoleAry(homeInOnStr("a", orgOrPersAry, 6));


/* Array filtering function - returns an array of those items from inputAry whose first few characters match selectStr. The output arrays max length is determined by outputAryLength. If selectStr = "" alternativeAry is returned. alternativeAry is also returned if inputAry is empty (so that alternative array can be used for things like numbers entry using shift arrays). The max length of the reurned is determined by outputAryLength. */
function homeInOnStr(selectStr, inputAry, alternativeAry, outputAryLength) { 
	var outputAry = [];
	var outputAryCounter = 0;
	if (selectStr.trim() != "*") { //as long as wild card has not been entered
		if (((0 < selectStr.trim().length) || (alternativeAry.length == 0)) && (0 < inputAry.length)) { //don't run if selectStr is empty (no characters have been keyed in to match against) - trim is needed because otherwise invisible chars (suspect " " after backspace). DO run though if alternative array is empty as there will be nothing to accumulate in the 'else' statement below. if inputAry is empty the default is not to run! (so that alternative array can be used for things like numbers entry using shift arrays)
		    var idxMax = inputAry.length - 1; //maximum inputAry index (it starts at 0)     
		    for (i = 0; i <= idxMax; i++) { //loop through all the inputAry indexes
		        var value = inputAry[i].trim(); //for each iteration of the loop get the corresponding value from the input array
		        if (  charsMatchStringStart(selectStr.trim(), value) && (0 < selectStr.trim().length)  ) { //if the characters to match are greater than just "" and match the beginning of the name of the current array value copy to output array
		            if (outputAryCounter < outputAryLength) { //as long as the output array hasn't already grown too big
		            	outputAry.push(value);
		            }
		            outputAryCounter++;
		        }
			}
			dropDownShowClass = "wordChoiceFromTableShow";
		}
	    else { //selector string for doing match/home in is empty so use alternative array instead (probably latest choices that were made) 
	    	var idxMax = alternativeAry.length - 1; //maximum inputAry index (it starts at 0)     
		    for (i = 0; i <= idxMax; i++) { //loop through all the panel button ids
		    	outputAry.push(alternativeAry[i].trim());
		    	outputAryCounter++;
		    }
		    dropDownShowClass = "wordChoiceFromPrevChoicesShow";
		}
	}
	else { //wildcard has been entered so simply copy whole of inputAry to outputAry
		outputAry = inputAry;
		dropDownShowClass = "wordChoiceFromTableShow";
	}

    return outputAry;
}


/* Drops down rows of values to choose from. The values displayed are determined by homeInOnStr(). Sets idCurDroppedDown to indicate which editDiv has focus. */
function showDropDowns(event, maxDropdownRows) {
	//alert(event.target.id+" # "+event.target.parentElement.id+" # "+event.target.parentElement.parentElement.id+" # "+event.target.parentElement.parentElement.parentElement.id+" # "+event.target.parentElement.parentElement.parentElement.parentElement.id);
	event.stopPropagation(); //prevents clicks that are being handled by this div being propagated up to the containing div(s) and having unwanted consequencies
	var id = event.target.id;
	if (event.keyCode == 13) { //return pressed so click row below (defaults to top row if at bottom)
		var existingValue = sanitiseText(document.getElementById(id).innerText).trim();
		document.getElementById(id).innerText = existingValue;
		if (existingValue != "") { //don't click to the next cell if current one is empty and still requires an entry
			clickNextDivDown(id);
		}
		return; //exit from this function (it will be re-entered by the click function)
	}
	if (idCurDroppedDown == id) {
		var calledFrom = "Same Div"
	}
	else {
		var calledFrom = "Diff Div"
	}
	hideDropDown(maxDropdownRows, calledFrom);
	var rowId = id.split("-")[0];
	var colName = id.split("-")[1];
	var selectStr = document.getElementById(id).innerText;
	var selectedAry = homeInOnStr(selectStr, masterChoiceAry[colName.toString()], masterShiftAry[colName.toString()], maxDropdownRows); //CREATE FUNCTION TO ARRANGE masterShiftAry ALPHABETICALLY/NUMERICALLY
	selectedAry.sort();
	if (selectStr.trim() == "*") { //wildcard entered so lift max dropdown rows - they will scroll if selectedAry is larger than maxDropdownRows
		var dropdownRows = selectedAry.length;
	}
	else { //limit max dropdown rows to whichever is the least of maxDropdownRows and selectedAry.length
		var dropdownRows = Math.min(maxDropdownRows, selectedAry.length);
	}
	var dropdownNum;
	for (dropdownNum = 0; dropdownNum < dropdownRows; dropdownNum++) { //section that loops to display the dropdown choices
		appendDiv("dropDownDiv"+rowId+"-"+colName,   dropDownShowClass,   rowId+'-'+colName+'-'+dropdownNum,   selectedAry[dropdownNum],  "copySelectedDropdown(event)"); //create div with text in it
		if (dropdownNum == 0) { //concatonate classes to give extra padding at top of first row of dropdown - for clearance below the text box
			document.getElementById(rowId+'-'+colName+'-'+dropdownNum).className = dropDownShowClass+" paddingTopMore";
		}
		else { //normal padding at top
			document.getElementById(rowId+'-'+colName+'-'+dropdownNum).className = dropDownShowClass+" paddingTopNormal";
		}
	}
	if (id != "pageDiv") {
		idCurDroppedDown = id;
	}
}


/* Uses jQuery to append a new div to existing elements in the container designated by containerId. The properties of the new div are determined by divClass, divId and displayedTxt. If a click response is required onClickStr must contain an appropriate string, e.g. "copySelectedDropdown(event)" to create this functionality. If onClickStr = "" or is omitted there will be no click functionality.  */
function appendDiv(containerId, divClass, divId, displayedTxt, onClickStr = "") {
	$("#"+containerId).append('<div class='+divClass+' id='+divId+'  onclick='+onClickStr+'  >'+displayedTxt+'</div>');
}


/* When called with the id of a cell in the form "3-Person" the cell immediately below the one referenced by id will be selected and brought into focus exactly as if it had been clicked with the mouse. If there is no cell below the cell in the top row of the same column will be selected.  */
function clickNextDivDown(id) {
	var newRow = (1*id.split("-")[0]) + 1;
	var column = id.split("-")[1];
	if (newRow < numOfRows) { //check that there is still a row below the current one (length is always 1 greater than the highest index)
		var newCellId = newRow+"-"+column;
	}
	else { //no more rows so go to top row
		var newCellId = "0-"+column;
	}
	document.getElementById(newCellId).click();
	document.getElementById(newCellId).focus();
}


/* Hides the most recent dropdown rows (reference stored in global idCurDroppedDown). Does nothing if idCurDroppedDown is empty but always sets it to "" at the end of the function regardless. */
function hideDropDown(maxDropdownRows, calledFrom) {
	if (0 < idCurDroppedDown.length) {
		var rowId = idCurDroppedDown.split("-")[0];
		var colName = idCurDroppedDown.split("-")[1];
		var dropdownNum;
		for (dropdownNum = 0; dropdownNum < maxDropdownRows; dropdownNum++) {
			$("#dropDownDiv"+rowId+"-"+colName).empty();
			//document.getElementById(rowId+"-"+colName+"-"+dropdownNum).className = "wordChoiceHide";
		}
		if ((calledFrom == "pageDiv") || (calledFrom == "Diff Div")) {
			checkForValidity(idCurDroppedDown);
		}
	}
	
	idCurDroppedDown = "";
}


/* Returns true if innerText from the div referenced by id can be found in the subarray that is selected by id from masterChoiceAry. id is resolved to column name which is used as the key to select the relevent subarray. If there is no match, or subarray or item string or id is empty, false is returned. */
function itemIsInIdAry(id) {
	if (0 < id.length) { //ensure id isn't empty
		var colName = id.split("-")[1];
		var itemStr = document.getElementById(id).innerText.trim();
		document.getElementById(id).innerText = itemStr;
		if (0 < itemStr.length) { //ensure itemStr isn't empty
			if (masterChoiceAry.hasOwnProperty(colName.toString())) { //ensure the subarray exists
				if (-1 < Object.values(masterChoiceAry[colName.toString()]).indexOf(itemStr)) {
					return true;
				}
			}
		}
		else { //itemStr empty so return true as it doesn't constitute a word that will be written to allRecords (will need to prevent this at submit stage!)
			return true;
		}
	}
	return false;
}

/* Checks the text in the calling editable div and if it doesn't match any value derived from the table array for the current column it is deleted. If it does match it is added to the shift array for the column. */
function checkForValidity(id) {
	if (!itemIsInIdAry(id)) {
		document.getElementById(id).innerText = "";
		alert("Not in Database !");
	}
	else {
		masterShiftAryIn(id, masterShiftAry, maxDropdownRows, "divLossOfFocus()");
	}
}


/* Copies the value from the clicked drop down div to the associated editing div, updates masterShiftAry and hides dropdown. */
function copySelectedDropdown(event) {
	event.stopPropagation(); //prevents clicks that are being handled by this div being propagated up to the containing div(s) and having unwanted consequencies
	var id = event.target.id;
	var calledFrom = id;
	rowId = id.split("-")[0];
	colName = id.split("-")[1];
	var newStr = document.getElementById(event.target.id).innerText;
	document.getElementById(rowId+"-"+colName).innerText = newStr;
	masterShiftAryIn(id, masterShiftAry, maxDropdownRows, "copySelectedDropdown()");
	hideDropDown(maxDropdownRows, "copySelectedDropdown()");
}



/* As long as it exists, and doesn't already exist in the sub array, pushes newStr onto the end of sub array of masterShiftAry designated by colName. If the array length is the maximum determined by maxShiftLength an item is removed from position 0 first. */
//function masterShiftAryIn(newStr, id, masterShiftAry, maxShiftLength) {
function masterShiftAryIn(id, masterShiftAry, maxShiftLength, from) {
	colName = id.split("-")[1];
	newStr = document.getElementById(id).innerText;
	if ((0 < newStr.trim().length) && (Object.values(masterShiftAry[colName.toString()]).indexOf(newStr.trim()) == -1)) { //if newStr exists is not already in the array go ahead with sub array push
		if (maxShiftLength <= masterShiftAry[colName.toString()].length) { //if there is no room for another item on the end of the array
			masterShiftAry[colName.toString()].shift(); //remove the first item
		}
		masterShiftAry[colName.toString()].push(newStr.trim()); //add item to end of array
	}
	consoleAry(masterShiftAry[colName.toString()]);
}


</script>


<?php


/* Creates a wrapper div with an internal visible edititable div for entering data and a column of hidden divs below it, to show word choices. The hidden divs are populated with text and displayed by showDropDowns() when the editable div is clicked. When one of the dropdown divs is clicked copySelectedDropdown() copies its text into the editable div and hides all the dropdown divs again.  */
function inputDivWithDropdown($wrapperDivClass, $inputDivClass, $dropdownDivsClass, $rowNum, $columnName, $maxDropdownRows) {
?>
	<div class="<?=$wrapperDivClass?>" id="<?=$rowNum.$columnName.'wrapper'?>">
		<div class="<?=$inputDivClass?>" id="<?=$rowNum?>-<?=$columnName?>" contentEditable="true" onclick="showDropDowns(event, <?=$maxDropdownRows?>)" onKeyup="showDropDowns(event, <?=$maxDropdownRows?>)"></div>
		<div id="dropDownDiv<?=$rowNum?>-<?=$columnName?>" style="max-height:220px; overflow-y:auto; overflow-x:hidden">
		</div>
	</div>

<?php
}

include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>
