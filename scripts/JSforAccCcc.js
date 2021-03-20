/*
 * 
 */


 // EXPERIMENTAL GREEN ADDITIVE CLASS IS IN selectTableRowsForDoc() !!

//flags and control variables - self explanatory
var atomicAjaxCallCompleted = true;
var allowSetSticky = true;
var fromClickCellCmnd = false;
var autoClickDwnFromCalOrButPnl = false;
var currentKey = "none"; //stores value of current key - either "none", "Control" etc. or a code number (e.g. 112 = "p"). Shift key produces "Shift" and not the code number for an uppercase (e.g. 80 = "P")
var altGrLastPressedTime = 0;
var compoundNum = 0;

var conLogMode = "Off";
var consoleAryMode = "Off";
var checkTimeMode = "Off"; //set to "Off" or "On" (prints as each START() FINISH() pair complete - for troubleshooting code problems
var checkTimeLastRun = 0;
var checkTimeTabCount = 0;
var checkTimeStack = [];

var cellHidden = true; //controls whether normally hidden compound rows can be edited 


function clickField(event) {

	//################## TEST AREA START



	//################## TEST AREA END

	conLog("PERFORMANCE>NOW() = "+performance.now());
	//STARTinit(); think not needed normally as START() has feature built in to do initialisation
	var id = event.target.id;
	conLog("                                                                        #################### START clickField()     Id = "+id+" ####################");

	START("clickField()");
	if (!fromClickCellCmnd) { //click from normal display area rather than calendar or selection panel buttons so cancel clickDown feature
		autoClickDwnFromCalOrButPnl = false;
		getFunc(autoClickDownSubId, 'changeButClass')(""); //function within subButPanelJSclickDown() to select/unselect button by changing the class - default "" means unselected
	}
	fromClickCellCmnd = false;
	

//alert(compound.mastIdr);
	if (id.split("-")[1] == "piv") { //click comes from pivot table
		document.getElementById("pivCellId").value = id;
		document.getElementById("pivCellVal").value = document.getElementById(id).innerText;
		if ((id.split("-")[0] == "surplus") || (id.split("-")[0] == "bal") || (id.split("-")[0] == "spacer")) { //if any of these rows clicked do nothing as it would be meaningless

		}
		else {
			document.getElementById("m88vof5A73").submit(); //calls showRecsForFullYr.php with filter info from clicked pivot display
			
		}
		FINISH("clickField()")
		return "function exited";
	}

	if(!pivotButIsSet) { //click comes from normal rows display, none of this section is needed for the pivot table
		if (id.split("-")[0] == "sticky") { //a cell in the sticky row has been clicked, cancel sticky function
			inrSet(id, ""); //clear the clicked sticky cell 
			valSet("stickyActive-"+id.split("-")[1], "no"); //clears value holder flag to indicate that the sticky value has been cleared
			FINISH("clickField()")
			return "function exited";
		}

		if (id.split("-")[0] == "heading") { //a cell in the heading row has been clicked clicked
			groupSet(id);
			FINISH("clickField()")
			return "function exited";
		}

		if (id.split("-").length != 2) { //if the mouse is clicked anywhere other than on a valid cell and id is a word instead of (e.g.) 23-74 exit this function to prevent later errors (fixes lock-up problem)
			FINISH("clickField()")
			return;
		}

		doEverything(id, currentKey, " Called From clickField()"); //currentKey comes from function keyPressDetect()

	}
	FINISH("clickField()");
}

function doEverything(id, heldKey, calledFrom) {
	START("doEverything()"+calledFrom);
	//timeToCons();

    // ########################### LOCAL JAVASCRIPT STUFF - DOES NOT INTERACT WITH SERVER ###################
    createParent = "no"; //set to no as default
    var date = new Date();
    var msTime = date.getTime();


    if (((msTime - valGet("mouseClickPreviousTime")) < 400) && (valGet("allowedToEdit") == "Yes") && allowSetSticky) { //double click - call the sticky button function from this 'if' condition (may set a flag and call it at the end of this clickField function) - won't run if call to this function is from clickCellBelow() (prevents accidental setting of sticky with vigorous use of return key!)
    	var simpleColumns = ["1","2","5","6","7","9","10","11", "12"]; //columns that are allowed to set a sticky
		if (simpleColumns.indexOf(id.split("-")[1]) != -1) { //if a simple table update (either direct text string in table or id derived in php writeReadAllRecordsItem() function from relevant category
	    	stickyStr = inrGet(id);
	    	if (stickyStr == "") { //set text to --CLEAR-- if empty string is to be copied by sticky function - so it can be seen in sticky header and clicked on!
	    		stickyStr = "--CLEAR--";
	    	}
	    	inrSet("sticky-"+id.split("-")[1], stickyStr); //set the text of the sticky cell at the heading to the string value of the table cell just clicked on
	    	valSet("stickyActive-"+id.split("-")[1], "yes"); //sets value holder flag to indicate that the sticky value has been set - allows sticky value of "" if desired, to make clearing cells easy
	    }
	    FINISH("doEverything()"+calledFrom)
    	return "function exited";
	}


	allowSetSticky = true; //enable sticky again in case it had been inhibited by 
    valSet("mouseClickPreviousTime", msTime);
    valSet("IncludeFiltIdr", id); //stored the newly clicked cell id in formValHolder for "IncludeFiltIdr" (gets sent as filter term when cntrl click is done)
    valSet("ExcludeFiltIdr", id); //stored the newly clicked cell id in formValHolder for "IncludeFiltIdr" (gets sent as filter term when cntrl click is done)
    valSet("bankStatementIdR", id.split("-")[0]); //stored the newly clicked row idR in formValHolder to identifiy which bank statenment has been selected for display when the statement button is clicked
    valSet("behindBankStatementIdR", id.split("-")[0]); //same for if previous or next buttons are clicked first
    valSet("aheadBankStatementIdR", id.split("-")[0]);
    // ########################### LOCAL JAVASCRIPT STUFF - END ###################



    // ########################### STATEMENTS ALL ACCESS THE SERVER AND DATABASE (AND REFRESH PAGE) #####################
	if (heldKey == "Control") { //call filter Include send function from this if condition and exit this clickField() function so no other server calls are made
		document.getElementById("fn445dya48d").submit(); //calls new (same) page immediately with filter function set
		FINISH("doEverything()"+calledFrom)
		return "function exited";
	}
	if (heldKey == "ControlShift") { //ASCII "e" held down so call filter Exclude send function from this if condition and exit this clickField() function so no other server calls are made
		document.getElementById("2FNPOyN0Pr4").submit(); //calls new (same) page immediately with filter function set
		FINISH("doEverything()"+calledFrom)
		return "function exited";
	}
	if (id.split("-")[1] == 12) { //if family column has been clicked call toggle display of family and exit this clickField() function so no other server calls are made
		if ((valGet("editFamilies") == "Yes") || (valGet("showFamilies") == "Yes")) { //prevents family column click from propogating to new page load and change in family id if Family Edit or Show Families has been selected

		}
		else{
			toggleSingleFamDisplay(id); //calls new (same) page immediately with family display toggled on or off
			FINISH("doEverything()"+calledFrom)
			return "function exited";
		}
	}
	// ########################### STATEMENTS ALL ACCESS THE SERVER AND DATABASE (AND REFRESH PAGE) - END #####################


	// ########################### LOCAL JAVASCRIPT STUFF - DOES NOT INTERACT WITH SERVER ###################

	//showHideCompoundRows(valGet("seltdRowCellId"), compoundGroupIdrAry, compoundTypeAry, compoundHiddenAry, pivotButIsSet, "Hide");

	selectTableRowsForDoc(12, false, colClssAry, compoundTypeAry, compoundColNumAry, moneyDisplayStr, valGet("seltdRowCellId"), "white", valGet("filteredColsCsv"), 'displayCellFilt', 'displayMoneyCellFiltClass', valGet("endDate"), displayCellDescrpAry, "displayCellRcnclBlank", "displayCellRcnclNot", "displayCellRcnclEarly", "", pivotButIsSet, "unselect"); //use the id of the previously clicked cell (stored in formValHolder for "seltdRowCellId") to unselect all the previously selected rows associated with the previous document
	
	if (id.split("-").length == 2) { //only store cell id if it is an actual cell with a hiphon in between the row and column indexes (prevents selectable items in button panels and elsewhere being stored)
		valSet("seltdRowCellId", id); //stored the newly clicked cell id in formValHolder for "seltdRowCellId" (for use to unselect the row on a later pass of this func)
	}

	valSet("storeSelectedRecordIdR", id.split("-")[0]); //store the id of the clicked row, which is the idR of the row in allRecords - used by Duplicate Row and Delete Row
	
	//showHideCompoundRows(id, compoundGroupIdrAry, compoundTypeAry, compoundHiddenAry, pivotButIsSet, "Show");

	selectTableRowsForDoc(12, true, colClssAry, compoundTypeAry, compoundColNumAry, moneyDisplayStr, id, "grey", valGet("filteredColsCsv"), 'displayCellFilt', 'displayMoneyCellFiltClass', valGet("endDate"), displayCellDescrpAry, "displayCellLineSelRcnclBlank", "displayCellRcnclNot", "displayCellRcnclEarly", "docLineCountDispId", pivotButIsSet, "select"); //use the id of the current clicked cell id to select all the rows associated with the current document
	
	selectCell(id, colClssAry, "displayCellSnglSel", "displayCellSnglSelEditable", "displayCellSnglSelMoney", "displayCellSnglSelRcnclBlank", displayCellDescrpAry, "blue", "blueEdit");           //use the id of the current clicked cell to set the current cell to edit
	

	if (valGet("allowedToEdit") == "Yes") {

		selectButPanel(staticArys["displayCellDescrpAry"], fieldNameAry, staticArys["butPanelControlAry"], id, butPanelIdSuffix, dummyButPanelId, noEditButPanelId, "dateAndItemSelectRecnclDivId", {}, restrictionsAry, "Edit"); //use the id of the current clicked cell (freshly stored in formValHolder for "seltdRowCellId") to display the appropriate but panel
	
		var conditionsObj = {"RcnclDate":{"Account":"General"} }; //conditions opject for panel display or not. See description in valueMatchInObj()

		selectButPanel(staticArys["displayCellDescrpAry"], fieldNameAry, staticArys["subButPanelControlAry"], id, subButPanelIdSuffix, dummySubButPanelId, noEditButPanelId, "dateAndItemSelectRecnclDivId", conditionsObj, restrictionsAry, "Edit"); //for subButtons
	}
	else {

		var restrictionsAry = {"allowColumnEdit": ["personOrOrg", "transCatgry", "accWorkedOn", "budget", "umbrella", "docType"]};
		
		selectButPanel(staticArys["displayCellDescrpAry"], fieldNameAry, staticArys["butPanelControlAry"], id, butPanelIdSuffix, dummyButPanelId, noEditButPanelId, "dateAndItemSelectRecnclDivId", {}, restrictionsAry, "No Edit"); //use the id of the current clicked cell (freshly stored in formValHolder for "seltdRowCellId") to display the appropriate but panel

		//selectButPanel(staticArys["displayCellDescrpAry"], fieldNameAry, staticArys["subButPanelControlAry"], id, subButPanelIdSuffix, dummySubButPanelId, noEditButPanelId, "dateAndItemSelectRecnclDivId", {}, restrictionsAry, "Edit"); //for subButtons
	}


	if ((id.split("-")[1] == "12") && (valGet("editFamilies") == "Yes") && ((heldKey == 112) || (heldKey == 80))) { //if a cell in the family column has been clicked, Edit Families is selected, and p or P is held down (creat new parent) clear any sticky setting so there is no confusing operations and only the simple creation of a new parent proceeds
		inrSet("sticky-"+id.split("-")[1], ""); //clear the sticky cell header
		valSet("stickyActive-"+id.split("-")[1], "no"); //clears value holder flag to indicate that the sticky value has been cleared
		createParent = "yes"; //set create parent flag to indicate to following functions that commands to create parent have been detected and any family sticky operations have been cancelled
	}
	// ########################### LOCAL JAVASCRIPT STUFF - END ###################
	
	// ########################### STATEMENTS ALL ACCESS THE SERVER AND DATABASE #####################

	atomicCall(""); //function that combines updateFromSticky(id, valueStr), displayBalances(id), newDocFileName(id) in one atomic to prevent race conditions
	valSet("previousCellId", valGet("seltdRowCellId")); //store current row so that it is available next click (used with shift to copy sticky value to a range of selected cells)
	// ########################### STATEMENTS ALL ACCESS THE SERVER AND DATABASE - END #####################
	FINISH("doEverything()"+calledFrom);
}


/* ajax call function that combines ajaxRecordsItemAndCellUpdate(), ajaxRecordsWithdrawnPaidinAndCellsUpdate(), ajaxGetAndDisplayBals(), ajaxUpdateDocFileName() in one atomic call to prevent race conditions. callSelector csv determines which and how many separate functions are combined during any individual call.  */
function atomicAjaxCall(
	cellId, //ajaxRecordsItemAndCellUpdate()
	itemStr,
	editableCellIdValHldr,
	pathToPhpFile,
	fileRndm,
	cellWarnClass,
	moneyCellWarnClass,
	OrdWithdrawnId,
	OrdPaidInId,
	OrdBalId,
	reconcldWithdrawnId,
	reconcldPaidInId,
	reconcldBalId,
	docOnlyWithdrawnId,
	docOnlyPaidInId,
	docOnlyBalId,
	recStartDate,
	recEndDate,
	heldKey,
	compoundNum,
	compoundColNumAry,
	compoundTypeAry,
	compoundGroupIdrAry,
	altGrLastPressedTime,
	createParent,
	idrArry,
	accountBankLinksArry,
	auxButtonTxt,
	displayCellDescrpAry,
	allRecordsColNameRndAry,
	headingAry,
	bankAccNameAry,
	restrictionsAry, //not sure if needed but ready in case
	moneyDisplayStr
	) { 
	START("atomicAjaxCall()");

	conLog("HERE ##################################### atomicAjaxCall()");
	if (atomicAjaxCallCompleted) { //prevents new calls to server before existing one has completed - NOT SURE IF THIS IS THE OPTIMUM PLACE FOR THIS (BUT COULD BE IF ALL SERVER CALLS COME THROUGH HERE!)
conLog(fileRndm);
conLog("HERE ##################################### atomicAjaxCall()   PRE-AJAX SENDS");

		var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
		var xmlhttp;
		if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
		  	xmlhttp=new XMLHttpRequest();
		}
		else {// code for IE6, IE5
		  	xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		}


		var arry = {};
		arry["NAME"] = "arry";

		if (heldKey == "Shift") { //general code to create an array of idRs for a series of cells selected in a column using 'shift' key - used for sticky function to copy value to a number of cells at once
			var firstArryIndex = idrArry.indexOf(valGet("previousCellId").split("-")[0]);
			var secArryIndex = idrArry.indexOf(valGet("seltdRowCellId").split("-")[0]);
			if (firstArryIndex < secArryIndex) {
				var lowArryIndex = firstArryIndex;
				var highArryIndex = secArryIndex;
			}
			else {
				var lowArryIndex = secArryIndex;
				var highArryIndex = firstArryIndex;
			}
			arry["idRlist"] = idrArry.slice(lowArryIndex, highArryIndex + 1);
		}
		else { //if shift key not used set the array to just one item - the currently clicked cell idR
			arry["idRlist"] = [valGet("seltdRowCellId").split("-")[0]]
		}

		savedCellClassesArry = getAryOfClasses(arry["idRlist"], cellId.split("-")[1]);

		
		arry = clearRowExcptRecDateAjaxSend(arry, cellId, colClssAry, auxButtonTxt, compoundTypeAry);
		
		
		arry = setCompoundTransAjaxSend(arry, cellId, heldKey, compoundNum, auxButtonTxt); //only executes internally if heldKey == "AltGr"
		arry = createParentAjaxSend(arry, cellId, createParent, cellWarnClass, auxButtonTxt); //only executes internally if createParent = "yes" (if this is so stickyAjaxSend() will have already been disabled for families)
		arry = stickyAjaxSend(arry, itemStr, cellId, idrArry, cellWarnClass, displayCellDescrpAry, auxButtonTxt); //only executes internally if sticky item for this column has been set (i.e. isn't "")
		arry = withdrawnPaidinAjaxSend(arry, editableCellIdValHldr, moneyCellWarnClass, displayCellDescrpAry, headingAry, bankAccNameAry, compoundTypeAry, compoundGroupIdrAry, auxButtonTxt, colClssAry); //only executes internally if editableCellIdValHldr value is != 0 (i.e. a withdrawn/paidin value has been changed)	
		arry = directStrEditAjaxSend(arry, editableCellIdValHldr, cellWarnClass, displayCellDescrpAry, allRecordsColNameRndAry, auxButtonTxt); //only executes internally if editableCellIdValHldr value is != 0 (i.e. a  editable string cell value has been clicked)

		arry = getBalDataSend(arry, cellId, recStartDate, recEndDate, valGet("runNormalBalFunc"), auxButtonTxt); //executes if runBalFunc = "Yes" (though php on server may return all balances as "0.00" if nonsensical column like date is clicked)
		arry = docUpdateSend(arry, cellId, accountBankLinksArry, auxButtonTxt); //only executes if currentDocRnd != previousDocRnd or column 8 (reconciliation) has been selected
			
		consoleAry(arry);

		xmlhttp.onreadystatechange=function() {
		    if (xmlhttp.readyState==4 && xmlhttp.status==200) {
		    	START("onreadystatechange(ASYNC)");
		    	//alert (xmlhttp.responseText);
		    	conLog(xmlhttp.responseText);
		      	var arryBackFromPhp = JSON.parse(xmlhttp.responseText);
		      	consoleAry(arryBackFromPhp);

		      	clearRowExcptRecDateAjaxReceive(arry, arryBackFromPhp, cellId, colClssAry);
		      	setCompoundTransAjaxReceive(arry, arryBackFromPhp, cellId, displayCellDescrpAry, compoundTypeAry, compoundColNumAry, colClssAry);
		      	createParentAjaxReceive(arry, arryBackFromPhp, cellId);
		      	stickyAjaxReceive(arry, arryBackFromPhp, cellId, savedCellClassesArry);
		      	withdrawnPaidinAjaxReceive(arry, arryBackFromPhp);
		      	directStrEditAjaxReceive(	arry,
		      								arryBackFromPhp
		      	);
		      	getBalDataReceive(	arry,
		      						arryBackFromPhp,
									OrdWithdrawnId,
									OrdPaidInId,
									OrdBalId,
									reconcldWithdrawnId,
									reconcldPaidInId,
									reconcldBalId,
									docOnlyWithdrawnId,
									docOnlyPaidInId,
									docOnlyBalId,
									moneyDisplayStr
				);
		      	docUpdateReceive(arry, arryBackFromPhp);
		      	atomicAjaxCallCompleted = true;
		      	FINISH("onreadystatechange(ASYNC)");
		    }
		}
		xmlhttp.open("POST", pathToPhpFile, true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.send("command="+fileRndm+"&arryJsonStr="+JSON.stringify(arry)+"&random="+random);
		atomicAjaxCallCompleted = false; //set to false to prevent other attempts at sending ajax data until the current one has completed and this flag has been set to true by return from server above
		conLog("HERE ##################################### atomicAjaxCall()   POST-AJAX SENDS");
		//alert("End Of atomicAjaxCall");
	}
	FINISH("atomicAjaxCall()");
}


//                                                                                                         #########
//                                                                                                         #########
//################################## START OF FUNCTIONS FOR ATOMIC AJAX CALL #######################################
//                                                                                                         #########
//                                                                                                         #########


/* Clears all fields in allRecords table to default values for the displayed row, except recordDate. While the server is carrying out the clear procedure the cells are set to the orange "waitingForServer" colour and later cleared of displayed data and set to normal colour by clearRowExcptRecDateAjaxReceive() when it receives confirmation back from the server. In addition to clearing the visible cells 'compound' and 'reconcileDocId' are cleared as well. Rows that are either a family parent or part of a compound grouping will not attempt to clear but display a message instead.   */
function clearRowExcptRecDateAjaxSend(arry, cellId, colClssAry, auxButtonTxt, compoundTypeAry) {
	if (auxButtonTxt == "Clear") { //only run if Clear button has been clicked
		START("clearRowExcptRecDateAjaxSend()");
		var idR = cellId.split("-")[0];
		if (document.getElementById(idR+"-12").innerText.split(" ")[0] == "OOO") { //row is a parent of family so show alert and inhibit clear action and return from function
			alert("Clear will not work Parent rows !");
			return arry;
		}
		if ((compoundTypeAry[idR] == "Master") || (compoundTypeAry[idR] == "Slave") || (compoundTypeAry[idR] == "FinalSlave")) { //one of several compound row types so show alert and inhibit clear action and return from function
			alert("Clear will not work on Compound rows !");
			return arry;
		}
		else { //not compound so press on
			var recordDate = document.getElementById(idR+"-0").innerText;
			var newValues = [reverseDate(recordDate), "", "", "0", "0", "", "", "", reverseDate("01-01-2000"), "", "", "", "0", "0", "0", reverseDate("01-01-2000")] //these values include 13:compound, 14:reconcileDocId and 15:parentDate that are used on the server to reset table values, but not used here to change any cell values as these display cells don't exist. fileName (for the associated document) is not changed on the server either, so the row still has some meaning before deciding to change the date to 01-01-2000 to finally delete it
			for (var i = 1; i < 13; i++) { //iterate each cell except 0:Date which must remain unchanged in case the row may still be required before deciding to change the date to 01-01-2000 to finally delete it
				//document.getElementById(idR+"-"+i).innerText = newValues[i];
				changeSuffixClass((idR+"-"+i), colClssAry["waitingForServer"]);
			}
			arry["writeValuesAry"] = {[idR]: newValues};
			arry["clearCellId"] = cellId;
			arry["clearRowExcptRecDateAjaxSend"] = true;
		}
		FINISH("clearRowExcptRecDateAjaxSend()");
	}
	return arry;
}

function clearRowExcptRecDateAjaxReceive(arry, arryBackFromPhp, OrigCellId, colClssAry) {
	if (existsAndTrue(arryBackFromPhp, "PHPwriteReadRowHasRun")) { //only run if returning PHP has already run on server
		START("clearRowExcptRecDateAjaxReceive()");
		var rowId = Object.keys(arryBackFromPhp["aryBackFromWriteReadRows"])[0]; //extract rowId from returned array - PHP writeReadRows() can return several rows if required but only single row used here
		var origValueAry = arry["writeValuesAry"][rowId];
		var updatedValueAry = arryBackFromPhp["aryBackFromWriteReadRows"][rowId];	
		if ((updatedValueAry[15] == reverseDate("01-01-2000")) && (updatedValueAry[14] == 0) && (updatedValueAry[13] == 0)) { //check that reconcileDocId and compound have both been set to 0 before setting other cells
			for (var i = 12; 0 <= i; i--) { //start at highest index of 13 and count down to 0 (direction doesn't really matter but continues in order of checking from 14, 13)
				cellId = rowId+"-"+i;
				if ((i == 0) || (i == 8)) { //a date column so reverse date for comparison and display WOULD BE BETTER IF THESE IF ELSE'S TESTED ROW NAMES IN CASE ORDER IS CHANGED OR ADDITIONS MADE !
					origValue = reverseDate(origValueAry[i]);
					updatedValue = reverseDate(updatedValueAry[i]);
				}
				else if ((i == 3) || (i == 4)) { //a money column so format for comparison and display
					origValue = sanTwoDecPlcs(origValueAry[i]);
					updatedValue = sanTwoDecPlcs(updatedValueAry[i]);
				}
				else { //value unchanged
					origValue = origValueAry[i];
					updatedValue = updatedValueAry[i];
				}
				//var origValue = sanTwoDecPlcs(origValueAry[cellId]);
				//var updatedValue = sanTwoDecPlcs(updatedValueAry[cellId]);

				if (updatedValue == origValue) {
					if (i == 12) {
						if (updatedValue == "0") { //if read back for parent field is 0 (indicating this record is no longer a child) convert to "" for display (mimicking php parsing in showRecsForFullYr.php)
				    		updatedValue = "";
				    	}
				    }
					document.getElementById(cellId).innerText = updatedValue;
					if (i == OrigCellId.split("-")[1]) { //column of original cell id matches current loop column so it is a selected cell
						if (i == 8) { //set to cell selected invisible colour for reconciliation date which will have been reset to 01-01-2000
							changeSuffixClass(OrigCellId, colClssAry["cellSelInvisCol"]);
						}
						else { //just set to normal cell selected colour
							changeSuffixClass(OrigCellId, colClssAry["cellSelCol"]);
						}
					}
					else { //not a selected cell but selected row
						if (i == 8) { //set to row selected invisible colour for reconciliation date which will have been reset to 01-01-2000
							changeSuffixClass(cellId, colClssAry["selInvisCol"]);
						}
						else { //just set to normal row selected colour
							changeSuffixClass(cellId, colClssAry["selCol"]);
						}
					}
				}
			}
		}
		FINISH("clearRowExcptRecDateAjaxReceive()");
	}
}

function setCompoundTransAjaxSend(arry, cellId, heldKey, compoundNum, auxButtonTxt) {
	if (((heldKey == "AltGr") || (heldKey == "RightShift")) && (auxButtonTxt != "Clear")) { //only run if "AltGr" or "ShiftRight" is held down
		arry["compoundColNum"] = 0;
		if (heldKey == "RightShift") {
			arry["compoundColNum"] = 1;
		}
		START("setCompoundTransAjaxSend()");
		arry["cellIdForCompoundTrans"] = cellId;
		arry["compoundNum"] = compoundNum;
		arry["createCompoundTransAjaxSendHasRun"] = true;
		//alert("setCompoundTransAjaxSend has run "+compoundNum);
		FINISH("setCompoundTransAjaxSend()");
	}
	return arry;
}

function setCompoundTransAjaxReceive(arry, arryBackFromPhp, cellId, displayCellDescrpAry, compoundTypeAry, compoundColNumAry, colClssAry) {
	if (existsAndTrue(arryBackFromPhp, "PHPsetCompoundTransHasRun")) { //only run if returning PHP has already run on server
		START("setCompoundTransAjaxReceive()");
		var maxColIdx = displayCellDescrpAry.length - 1; //derive maximum column index from displayCellDescrpAry which holds single word descriptions for each column
		var familyColKeyStr = String(getKeyFromValue(displayCellDescrpAry, "Family"));
		compoundNum = arryBackFromPhp["returnCompoundNum"]; //used to set value of this global external to this function (also cleared by both press or release of AltGr keyboard button)
		var compoundActionAry = arryBackFromPhp["compoundActionAry"];
		for (let key in compoundActionAry) { //loops through all the positions in the returned compoundActionAry
			if (compoundActionAry[key] == "NewMaster") {
				compoundTypeAry[key] = "Master"; //change global variable to record new master creation
				for(i = 0; i <= maxColIdx; i++) { //loop through all the columns in the row
					if ((displayCellDescrpAry[i] == "MoneyOut") || (displayCellDescrpAry[i] == "MoneyIn")) { //check for this section and use compound colours if necessary
						if (arry["compoundColNum"] == 0) {
				    		changeSuffixClass(key+"-"+i, colClssAry["compoundMaster"]);
				    		compoundColNumAry[key] = 0;
						}
						else {
							changeSuffixClass(key+"-"+i, colClssAry["compoundMasterAlt"]);
							compoundColNumAry[key] = 1;
						}
				    }
			    } 
			}
			if (compoundActionAry[key] == "NewSlave") {
				compoundTypeAry[key] = "FinalSlave"; //change global variable to record new slave creation
				for(i = 0; i <= maxColIdx; i++) { //loop through all the columns in the row
					if ((displayCellDescrpAry[i] == "MoneyOut") || (displayCellDescrpAry[i] == "MoneyIn")) { //check for this section and use compound colours if necessary
						if (arry["compoundColNum"] == 0) {
				    		changeSuffixClass(key+"-"+i, colClssAry["compoundSlaveFinal"]);
				    		compoundColNumAry[key] = 0;
						}
						else {
							changeSuffixClass(key+"-"+i, colClssAry["compoundSlaveFinalAlt"]);
							compoundColNumAry[key] = 1;
						}
				    }
			    } 
			}
			if (compoundActionAry[key] == "Destroyed") {
				compoundTypeAry[key] = "None"; //change global variable to record destruction of compound state for this row
				for(i = 0; i <= maxColIdx; i++) { //loop through all the columns in the row
					if ((displayCellDescrpAry[i] == "MoneyOut") || (displayCellDescrpAry[i] == "MoneyIn")) { //check for this section and use compound colours if necessary
				    	var adjacentClass = getSuffixClass(key+"-"+familyColKeyStr); //get selection class for row from the family cell in the row (not normally selectable)
				    	changeSuffixClass(key+"-"+i, adjacentClass);
				    }
			    } 
			}
		  	
		}
		FINISH("setCompoundTransAjaxReceive()");
	}
	//consoleAry(compoundTypeAry);
}

function createParentAjaxSend(arry, cellId, createParent, cellWarnClass, auxButtonTxt) {
	if ((createParent == "yes") && (inrGet(cellId).substring(0, 2) != "OO") && (auxButtonTxt != "Clear")) { //only run if createParent = "yes" and target row not already parent (THIS USES A SIMPLE CHECK OF FIRST TWO DISPLAYED CHARACTERS OF "OO" - WOULD NEED TO CHANGE THIS IF A FUTURE FEATURE OF COLOURS OR SHAPES FOR PARENTS AND CHILDREN IS ADOPTED!!!)
		START("createParentAjaxSend()");
		arry["cellIdForNewParent"] = cellId;
		arry["NewParentOrgClass"] = document.getElementById(cellId).className; //save original class for re-enstatement later
		document.getElementById(cellId).className = cellWarnClass; //set the cell class to warning until it has been properly updated with data back from the table
		arry["createParentAjaxSendHasRun"] = true;
		FINISH("createParentAjaxSend()");
	}
	return arry;
}

function createParentAjaxReceive(arry, arryBackFromPhp, cellId) {
	if (existsAndTrue(arryBackFromPhp, "PHPcreateNewParentHasRun")) { //only run if complementary send function has already run
		START("createParentAjaxReceive()");
		itemStrFromTable = arryBackFromPhp["createNewParentId"];
	    cleanedItemStrFromTable = itemStrFromTable.trim(); //removes unwanted spaces.	    
	    document.getElementById(cellId).className = arry["NewParentOrgClass"]; //re-enstate original class	    
	    cleanedItemStrFromTable = "OOO "+cleanedItemStrFromTable; //prepend with parent pattern
	    document.getElementById(cellId).innerText = cleanedItemStrFromTable; //write returned confirmatory string to cell
	    FINISH("createParentAjaxReceive()");
	}
}

function stickyAjaxSend(arry, itemStr, cellId, idrArry, cellWarnClass, displayCellDescrpAry, auxButtonTxt) {
	 if (auxButtonTxt != "Clear") {
	 	START("stickyAjaxSend()");
		conLog("HERE ##################################### stickyAjaxSend()");
		var colId = cellId.split("-")[1];
		var dispCellDscrp = displayCellDescrpAry[colId];
		if ((dispCellDscrp == 'PersOrg') || (dispCellDscrp == 'TransCat') || (dispCellDscrp == 'Account') || (dispCellDscrp == 'Budget') || (dispCellDscrp == 'Reference') || (dispCellDscrp == 'Umbrella') || (dispCellDscrp == 'DocType') || (dispCellDscrp == 'Note')) {
			if (valGet("stickyActive-"+cellId.split("-")[1]) == "yes") { //check to make sure flag indicates a sticky itemStr for this column has been set so the function should be run
				if (itemStr == "--CLEAR--") {
					itemStr = ""; //set to real intended value for updating table, "--CLEAR--" is just for display in the sticky heading because "" would be invisible
				}
				arry["itemStr"] = itemStr;
				arry["cellId"] = cellId;
				arry["stickyOrgClass"] = document.getElementById(cellId).className; //save original class for re-enstatement later
				arry["idRlist"].forEach(function(value) { //cycle through all the cell idRs selected by 'shift' click
					document.getElementById(value+"-"+cellId.split("-")[1]).className = cellWarnClass; //set the cell class to warning until it has been properly updated with data back from the table
				});
				arry["stickyAjaxSendHasRun"] = true;
			}
		}
		if (displayCellDescrpAry[colId] == 'Family') { //if a child update - also uses different checking and update code in writeReadAllRecordsItem() php function
			if ((valGet("stickyActive-"+cellId.split("-")[1]) == "yes")) { //check to make sure flag indicates a sticky itemStr for this column has been set so the function should be run 
				arry["itemStr"] = itemStr.replace(/\D/g,''); //removes all characters except numbers 0-9 - returns empty string "" if no numeric characters are in itemStr
				arry["cellId"] = cellId;
				arry["stickyOrgClass"] = document.getElementById(cellId).className; //save original class for re-enstatement later
				arry["idRlist"].forEach(function(value) { //cycle through all the cell idRs selected by 'shift' click
					document.getElementById(value+"-"+cellId.split("-")[1]).className = cellWarnClass; //set the cell class to warning until it has been properly updated with data back from the table
				});
				arry["stickyAjaxSendHasRun"] = true;
			}
		}
		FINISH("stickyAjaxSend()");
	}

	return arry;
}

function stickyAjaxReceive(arry, arryBackFromPhp, cellId, savedCellClassesArry) {
	if (existsAndTrue(arryBackFromPhp, "PHPwriteReadAllRecordsItemHasRun")) { //only run if complementary send function has already run
		START("stickyAjaxReceive()");
		conLog("In stickyAjaxReceive() !");
		var column = cellId.split("-")[1];
		for (var idR in arryBackFromPhp["stickyItemsUpdatedObjects"]) {
			var prependPattern = ""; //default, so nothing will be changed if it is prefixed to any cell retValue regardless (this may not actually happen)
			var recreatedCellId = idR+"-"+column;
			var retValue = arryBackFromPhp["stickyItemsUpdatedObjects"][idR].trim(); //removes unwanted spaces;
			var parentFlag = arryBackFromPhp["stickyItemsUpdatedParentFlagObjects"][idR];
			var savedClass = savedCellClassesArry[idR];
			conLog("key = "+idR+" retValue = "+retValue+" parent = "+parentFlag+" class = "+savedClass);
			if (column == "12") { //FAMILY COLUMN - a make child sticky operation, so format the returned
				if (retValue == "0") { //if read back for parent field is 0 (indicating this record is no longer a child) convert to "" for display (mimicking php parsing in showRecsForFullYr.php)
		    		retValue = "";
		    	}
		    	else { //an actual number, indicating it is a child or parent
		    		prependPattern = "% ";
		    		if (parentFlag == "Yes") {
		    			prependPattern = "OOO ";
		    		}
		    	}
			}
			if (retValue == arry["itemStr"]) { //check that the returned data matches that sent before removing the warning class from the display cell
				document.getElementById(recreatedCellId).innerText = prependPattern+retValue;
		      	document.getElementById(recreatedCellId).className = savedClass; //re-enstate original class
		    }
		}
		FINISH("stickyAjaxReceive()");
	}
}

/*   */
function withdrawnPaidinAjaxSend(arry, editableCellIdValHldr, moneyWarnClass, displayCellDescrpAry, headingAry, bankAccNameAry, compoundTypeAry, compoundGroupIdrAry, auxButtonTxt, colClssAry) {
	var cellId = valGet(editableCellIdValHldr); 
	var colId = cellId.split("-")[1]; //get the column number that was clicked
	if (((displayCellDescrpAry[colId] == "MoneyOut") || (displayCellDescrpAry[colId] == "MoneyIn")) && (auxButtonTxt != "Clear")) { //withdrawn or paidin cell so run this function	
		START("withdrawnPaidinAjaxSend()");	
		valSet(editableCellIdValHldr, 0); //resets the id value holder pointed to by editableCellIdValHldr
		if (cellId != 0) { //the cell that was previously in focus before the current cell that triggered this atomicAjaxCall was an editable one, and may have a new value in it
			if (!compoundHiddenAry[cellId.split("-")[0]]) { //only run this function to update the tables on the server if the row being edited isn't normally a hidden but has only been displayed because it's part of a filtered compound
				var accountName = document.getElementById(cellId.split("-")[0]+"-"+getKeyFromValue(headingAry, "Account")).innerText //gets the name from the Account column
				var isBankAcc = false;
				if (-1 < bankAccNameAry.indexOf(accountName)) { //if the name from the account column is one of names that have been designated as a bank account
					isBankAcc = true;
				}
				if (displayCellDescrpAry[colId] == 'MoneyOut') {
					var withdrnId = cellId;
					var paidinId = cellId.split("-")[0]+"-"+(parseInt(cellId.split("-")[1]) + 1);
				}
				if (displayCellDescrpAry[colId] == 'MoneyIn') {
					var withdrnId = cellId.split("-")[0]+"-"+(parseInt(cellId.split("-")[1]) - 1);
					var paidinId = cellId;
				}
				arry["compoundGroupAry"] = compoundGroupAry(cellId, withdrnId, paidinId, compoundGroupIdrAry, colClssAry, isBankAcc); //create an array of cellIds corresponding to the compound group of withdrawn values potentially affected by the withdrawn or paidin edit
				arry["withdrawnPaidinAjaxSendHasRun"] = true;
			}
	        else {
	            messageChangeInhibited();
	        }	
		}
		FINISH("withdrawnPaidinAjaxSend()");
	}
	return arry;
}

function withdrawnPaidinAjaxReceive(arry, arryBackFromPhp) {
	if (existsAndTrue(arryBackFromPhp, "PHPupdateWithdrawnPaidinHasRun")) { //only run if complementary send function has already run
		START("withdrawnPaidinAjaxReceive()");
		var compoundGroupAry = arry["compoundGroupAry"];
		var withdrawnAry = compoundGroupAry["withdrawnAry"];
		var paidinAry = compoundGroupAry["paidinAry"];

		var compoundGroupAryBack = arryBackFromPhp["compoundGroupAryBack"];
		var idrAry = compoundGroupAryBack["idrAry"];
		var withdrawnColId = compoundGroupAryBack["withdrawnColId"];
		var paidinColId = compoundGroupAryBack["paidinColId"];
		var updatedWithdrawnAry = compoundGroupAryBack["updatedWithdrawnAry"];
		var updatedPaidInAry = compoundGroupAryBack["updatedPaidInAry"];
		var withdrnOrgSuffixClassAry = compoundGroupAryBack["withdrnOrgSuffixClassAry"];
		var paidinOrgSuffixClassAry = compoundGroupAryBack["paidinOrgSuffixClassAry"];
		chkAndUpdtValueAndClasSfx(idrAry, withdrawnColId, updatedWithdrawnAry, withdrawnAry, withdrnOrgSuffixClassAry);
		chkAndUpdtValueAndClasSfx(idrAry, paidinColId, updatedPaidInAry, paidinAry, paidinOrgSuffixClassAry);
		FINISH("withdrawnPaidinAjaxReceive()");
	}
}


function directStrEditAjaxSend(arry, editableCellIdValHldr, cellWarnClass, displayCellDescrpAry, allRecordsColNameRndAry, auxButtonTxt) {
	cellId = valGet(editableCellIdValHldr);
	var colId = cellId.split("-")[1]; //get the column number that was clicked
	if (((displayCellDescrpAry[colId] == "Reference") || (displayCellDescrpAry[colId] == "Note")) && (auxButtonTxt != "Clear")) { //an editable cell so run this function - DON'T run if Clear clicked
		START("directStrEditAjaxSend()");
		valSet(editableCellIdValHldr, 0); //resets the id value holder pointed to by editableCellIdValHldr
		if (cellId != 0) { //the cell that was previously in focus before the current cell that triggered this atomicAjaxCall was an editable one, and may have a new value in it
			if (!compoundHiddenAry[cellId.split("-")[0]]) { //only run this function to update the tables on the server if the row being edited isn't normally a hidden but has only been displayed because it's part of a filtered compound
				var value = document.getElementById(cellId).innerText; //get string value held in the cell
				var recordId = cellId.split("-")[0]; //row in allRecords table that needs to be updated
				var allrecordsColNameRnd = allRecordsColNameRndAry[colId]; //random alphanumeric that corresponds to the column (field) name that needs to be updated - will be decoded by php on server		
				arry["cellId"] = cellId; //save the id of the ecitable cell for use when the update confirmation comes back from the table on the server
				arry["editableCellOrgClass"] = document.getElementById(cellId).className; //save original class for re-enstatement later
				document.getElementById(cellId).className = cellWarnClass; //set the editable cell class to warning until it has been properly updated with data back from the table 
				arry["editableCellIdR"] = recordId;
				arry["editableCellId"] = cellId;
				arry["allrecordsColNameRnd"] = allrecordsColNameRnd;
				arry["editableCellVal"] = sanitiseText(value);
				arry["directStrEditAjaxSendHasRun"] = true;
			}
			else {
	            messageChangeInhibited();
	        }		
		}
		FINISH("directStrEditAjaxSend()");
	}
	return arry;
}


function directStrEditAjaxReceive(arry, arryBackFromPhp) {
	if (existsAndTrue(arryBackFromPhp, "PHPupdateEditableItemHasRun")) { //only run if complementary send function has already run
		START("directStrEditAjaxReceive()");
	    var updatedEditableStr = arryBackFromPhp["updatedEditableStr"]; //returned  value
	    if (valGet("allowedToEdit") == "Yes") { //only check for match of sent and return data before removing warning class if editing rights are given - with no editing rights the current table values will always be returned and the table will remain unaltered

	        if (updatedEditableStr == arry["editableCellVal"]) { //check that the returned data matches that sent before removing the warning class from the display cell and replacing it with ordinary cl
	          document.getElementById(arry["editableCellId"]).className = arry["editableCellOrgClass"];
	        }
	    }
	    else { //if not allowed to edit remove warning class anyway as the value from table will be original value that will be re-enstated below
	    	document.getElementById(arry["editableCellId"]).className = arry["editableCellOrgClass"];
	    }
	    document.getElementById(arry["editableCellId"]).innerText = updatedEditableStr; //copy value read from table for complete confirmation that the edit has completed (or orig value re-enstated)
	    FINISH("directStrEditAjaxReceive()");
	}
}


function getBalDataSend(arry, cellId, recStartDate, recEndDate, runBalFunc) {
	if (runBalFunc == "Yes") {
		START("getBalDataSend()");
		arry["cellIdBal"] = cellId;
		arry["recStartDate"] = recStartDate;
		arry["recEndDate"] = recEndDate;
		arry["getBalDataSendHasRun"] = true;
		FINISH("getBalDataSend()");
	}
	return arry;
}

function getBalDataReceive(
	arry,
	arryBackFromPhp,
	OrdWithdrawnId,
	OrdPaidInId,
	OrdBalId,
	reconcldWithdrawnId,
	reconcldPaidInId,
	reconcldBalId,
	docOnlyWithdrawnId,
	docOnlyPaidInId,
	docOnlyBalId,
	moneyDisplayStr
	) {
	if (existsAndTrue(arryBackFromPhp, "PHPgetFilterStrAllBalDataHasRun")) { //only run if complementary send function has already run
		START("getBalDataReceive()");
		if (moneyDisplayStr == "amountWithdrawn") {
			document.getElementById(OrdWithdrawnId).innerText = formatTo2DecPlcs(arryBackFromPhp["withdrawnNorm"], true); //set element to cleaned withdrawn value.
			document.getElementById(reconcldWithdrawnId).innerText = formatTo2DecPlcs(arryBackFromPhp["withdrawnRec"], true);
		}
		else if (moneyDisplayStr == "amountPaidIn") {
			document.getElementById(OrdPaidInId).innerText = formatTo2DecPlcs(arryBackFromPhp["paidInNorm"], true);
	    	document.getElementById(reconcldPaidInId).innerText = formatTo2DecPlcs(arryBackFromPhp["paidInRec"], true);
		}
		else {
			document.getElementById(OrdWithdrawnId).innerText = formatTo2DecPlcs(arryBackFromPhp["withdrawnNorm"], true); //set element to cleaned withdrawn value.
			document.getElementById(reconcldWithdrawnId).innerText = formatTo2DecPlcs(arryBackFromPhp["withdrawnRec"], true);

		    document.getElementById(OrdPaidInId).innerText = formatTo2DecPlcs(arryBackFromPhp["paidInNorm"], true);
		    document.getElementById(reconcldPaidInId).innerText = formatTo2DecPlcs(arryBackFromPhp["paidInRec"], true);

		    document.getElementById(OrdBalId).innerText = formatTo2DecPlcs(arryBackFromPhp["balanceNorm"], true);
		    document.getElementById(reconcldBalId).innerText = formatTo2DecPlcs(arryBackFromPhp["balanceRec"], true);
		}
	    document.getElementById(docOnlyWithdrawnId).innerText = formatTo2DecPlcs(arryBackFromPhp["withdrawnDoc"], true);
	    document.getElementById(docOnlyPaidInId).innerText = formatTo2DecPlcs(arryBackFromPhp["paidInDoc"], true);
	    document.getElementById(docOnlyBalId).innerText = formatTo2DecPlcs(arryBackFromPhp["balanceDoc"], true);
	    FINISH("getBalDataReceive()");
	}
}

function docUpdateSend(arry, docUpdateCellId, accountBankLinksArry, auxButtonTxt) {
	START("docUpdateSend()");
	var currentDocRnd = document.getElementById(docUpdateCellId.split("-")[0]+"-docRnd").name;
	var previousDocRnd = valGet("previousDocRnd");
	//arry["accountIsRelevant"] = "No"; //set flag to default "No" that indicates to updateDocFilename() that the account is relevant (i.e. "General") when request is to display a reconciling bank statement
	var accountName = document.getElementById(docUpdateCellId.split("-")[0]+"-5").innerText; //get value of account cell at column 5
	if (accountBankLinksArry.hasOwnProperty(accountName)) {
		//alert("ACCOUNT!");
		//arry["accountIsRelevant"] = "Yes"; //the selected row is a valid account in terms of showing a bank statement for reconciling purposes
		arry["bankAccName"] = accountBankLinksArry[accountName];
	}
	if ((currentDocRnd != previousDocRnd) || (docUpdateCellId.split("-")[1] == 8) || (valGet("previousCellId").split("-")[1] == 8) || (docUpdateCellId.split("-")[1] == 6) || (valGet("previousCellId").split("-")[1] == 6)) { //only if new doc random has been selected or column 8 (reconcilation column to show relevant bank statement) or previous selection was column 8 so the basic document or a different reconciliation bank statement needs to be displayed again or column 6 (budget column to show relevant grant info) or previous selection was column 6 so the basic document or a different budget doc needs to be displayed again - otherwise this routine doesn't run as the doc doesn't need to be updated
		valSet("previousDocRnd", currentDocRnd); //doc has changed so update placeholder for previous doc random so it can be used to check if the next clicked record represents a doc change
		arry["docUpdateCellId"] = docUpdateCellId;
		arry["auxButtonTxt"] = auxButtonTxt;
		arry["docUpdateSendHasRun"] = true;
	}
	FINISH("docUpdateSend()");
	return arry;
}
    
function docUpdateReceive(arry, arryBackFromPhp) {
	if ((existsAndTrue(arryBackFromPhp, "PHPupdateDocFilenameHasRun")) && (arryBackFromPhp["docChanged"] == "Yes")) { //only run if complementary send function has already run
		START("docUpdateReceive()");
		if (valGet("previousObscureFile") == "obscureTest.php") { //set previousObscureFile value holder to obscureTest2.php and obscureTest.php alternately to fool pdfjs
			valSet("previousObscureFile", "obscureTest2.php"); //toggle file name
			document.getElementById("pdfIframe").src  = "./web/viewer.html?file="+docFilename2+"#page="+pageNum+"&zoom=95";
		}
		if (valGet("previousObscureFile") == "obscureTest2.php") {
			valSet("previousObscureFile", "obscureTest.php"); //toggle file name
			document.getElementById("pdfIframe").src  = "./web/viewer.html?file="+docFilename+"#page="+pageNum+"&zoom=95";
		}
		FINISH("docUpdateReceive()");
	}
}

//                                                                                                 #################
//                                                                                                 #################
//                                                                                                 #################
//################################## END OF FUNCTIONS FOR ATOMIC AJAX CALL #########################################
//                                                                                                 #################
//                                                                                                 #################
//                                                                                                 #################


/* Attempts to extract a budget end date from the last group of characters of the budget name, and budget start date from the 2nd last group of characters of the budget name (e.g. a budget name as in "FiSCAF 06Apr21 05Mar22". In each case if the extracted date has a day of month suffix (and isn't simply a month and year - "Apr21") this D.O.M. prefix is used in the creation of the extracted date. If either group contains no prefix then in the case of the last group (end date) the created date defaults to the last day of the month and in the case of the 2nd last group (start date) the created date defaults to the 1st day of the month. If no dates groups can be detected then "NoDatesInBudget" is returned, otherwise comparisons are them made with recordDate using either both start and end date if they are both available, or just the end date (last group) if that is all that is available. If just the last group (single) date is available it is used as a start date too with either it's D.O.M. prefix (in which case it is a single day budget) or, in the absence of the D.O.M. prefix, the first day of the month "01" is used. The comparison process yields one of three return results: "Expired", "NotYetActive", or "InDate" to indicate the date(s) of the budget in relation to the transaction date. A CORRESPONDING PHP FUNCTION EXISTS. */
function checkBudgetDatesJS(recordDate, budgetName) {
    transDateYYMMDD = convertYYYYMMDDdateToYYMMDD(recordDate); //transaction date as YYMMDD
    budgetDateLastYYMMDD = getDateSuffixYYMMDD(budgetName, true, true); //get date in YYMMDD format from the last group, last D.O.M. if no D.O.M. prefix - defaults to "NoDate" if doesn't decode to date
     if (budgetDateLastYYMMDD.substr(-6) == "NoDate") {
     	return "NoDatesInBudget";
     }
    budgetDateSecLastYYMMDD = getDateSuffixYYMMDD(budgetName, false, false); //get date in YYMMDD format from the 2nd last group, 1st D.O.M. if no D.O.M. prefix - defaults to "NoDate" if doesn't decode to date
    if (budgetDateSecLastYYMMDD.substr(-6) == "NoDate") { //single date (using last group only)
	budgetDateLastYYMMDDsetFirstDOM = getDateSuffixYYMMDD(budgetName, false, true); //get date in YYMMDD format from the last group, 1st D.O.M. if no D.O.M. prefix - cannot be "NoDate" 

    	if (budgetDateLastYYMMDD < transDateYYMMDD) { //budget date is earlier than the transaction date
	        return "Expired";
	    }
	    else if (transDateYYMMDD < budgetDateLastYYMMDDsetFirstDOM) { //budget date is later than the transaction date
	        return "NotYetActive";
	    }
	    else {
	        return "InDate";
	    }
    }
    else { //both dates (using both groups)
	    if (budgetDateLastYYMMDD < transDateYYMMDD) { //budget date is earlier than the transaction date
	        return "Expired";
	    }
	    else if (transDateYYMMDD < budgetDateSecLastYYMMDD) { //budget date is later than the transaction date
	        return "NotYetActive";
	    }
	    else {
	        return "InDate";
	    }
	}
}

/* Attempts to extract a budget end date from the last group of characters of the budget name - using getDateSuffixYYMMDD() - and compares the date with the transaction date. If the budget start date is later than the transaction date TRUE is returned. Otherwise FALSE. A CORRESPONDING PHP FUNCTION EXISTS. */
function budgetNotYetActiveJS(recordDate, budgetName) {
	var transDateYYMMDD = convertYYYYMMDDdateToYYMMDD(recordDate);
	var budgetDateYYMMDD = getDateSuffixYYMMDD(budgetName, false, false);
	if ((budgetDateYYMMDD.substr(-6) != "NoDate") && (transDateYYMMDD < budgetDateYYMMDD)) {
		return true;
	}
	else {
		return false;
	}
}

/* Attempts to extract a budget end date from the last group of characters of the budget name - using getDateSuffixYYMMDD() - and compares the date with the transaction date. If the transaction date is later than the budget end date TRUE is returned. Otherwise FALSE. A CORRESPONDING PHP FUNCTION EXISTS. */
function budgetExpiredJS(recordDate, budgetName) {
	var transDateYYMMDD = convertYYYYMMDDdateToYYMMDD(recordDate);
	var budgetDateYYMMDD = getDateSuffixYYMMDD(budgetName, true, true);
	if ((budgetDateYYMMDD.substr(-6) != "NoDate") && (budgetDateYYMMDD < transDateYYMMDD)) {
		return true;
	}
	else {
		return false;
	}
}

/* Takes the passed date string in format "09-02-2021" and converts it to YYMMDD format "210209".  */
function convertYYYYMMDDdateToYYMMDD(date) {
    return date.substr(8, 2)+date.substr(3, 2)+date.substr(0, 2); //extract two digit year substring and concatonate it with extracted month substring
}

/* If setForLastDayOfMonth is set to TRUE this function extracts the last group (if setForLastGroup is TRUE) or 2nd last group (if setForLastGroup is FALSE) of characters from value. The extracted group should be an abreviated month-year date string in the form "7Feb20", "15Feb20" or "Feb20") and, if it can be interpreted as a date, it is decoded to a number, reversed, in the form "200207", "200215" or in the case where no day of month suffix is included it sets the day of month output to the last day e.g. "200228" (taking into account that for leap years Feb's last day will be 29). This allows proper sorting using a simple sort algorithm or comparisons with other dates similarly formatted. If any extracted group of characters in the passed value doesn't properly decode to a date the original value with "-NoDate" (the preceding hyphen ensures that in sorting routines with SORT_NATURAL a value of "" will come first before any numbers) concatonated onto it is returned. If setForLastDayOfMonth is set to FALSE it works in a similar manner when there is a provided day of month suffix, but where there are none the day of month output is now set to "01". NOTE: only works with 2 character year designator and assumes every date is in the century 2000. Months designators must all be 3 character with leading Capital i.e. Jan, Feb, Mar, Apr, May, Jun, Jul, Aug, Sep, Oct, Nov, Dec.  A CORRESPONDING PHP FUNCTION EXISTS. */
function getDateSuffixYYMMDD(value, setForLastDayOfMonth, setForLastGroup) {
	var groupsAry = value.trim().split(" ");  //create an array from the groups of characters separated by a space in value string
	if (setForLastGroup) { //set group to be extracted according to setForLastGroup being TRUE (last group) or FALSE (2nd last group)
        var group = (groupsAry.length -1) in groupsAry ? groupsAry[groupsAry.length -1] : ""; //get last group (string separated by spaces). Uses in operator to check index exists and defaults to "" if not
    }
    else {
        var group = (groupsAry.length -2) in groupsAry ? groupsAry[groupsAry.length -2] : ""; //get 2nd last group (string separated by spaces). Uses in operator to check index exists and defaults to "" if not
    }
    var lastFiveChars = group.substr(-5); //get the last 5 characters of the value which may be a date code e.g. "Feb20"
    var potentialDayOfMonthChars = group.substr(0, (group.length -5)); //get first, day of month, part of group if it exists - should be 1 or 2 characters (or 0 characters if it doesn't exist)
    var monthThreeCharName = lastFiveChars.substr(0, 3); //extract what is potentially the three character month name e.g. 'Feb'
    var yearTwoDigitNum = lastFiveChars.substr(3);  //extract what is potentially the two character year number e.g. "20"
    if (((potentialDayOfMonthChars.length == 1) || (potentialDayOfMonthChars.length == 2)) && !isNaN(potentialDayOfMonthChars)) { //an actual days of month string so use this in returned date
        if (potentialDayOfMonthChars.length == 1) {
            var dayOfMonthChars = "0"+potentialDayOfMonthChars;
        }
        else {
            var dayOfMonthChars = potentialDayOfMonthChars;
        }
        var dayInFeb = dayOfMonthChars;
        var dayInLongMonth = dayOfMonthChars;
        var dayInShortMonth = dayOfMonthChars;
    }
    else if (setForLastDayOfMonth) { //no days of month string found and End of month requested so use last day of month
    	var dateOfLastDayOfFebForGivenYear = new Date("20"+yearTwoDigitNum, "02", 0); //generates the date for the last day of february taking into account the given year
        var dayInFeb = dateOfLastDayOfFebForGivenYear.getDate();
        var dayInLongMonth = "31";
        var dayInShortMonth = "30";
    }
    else { //no days of month string found but no End of month requested so use first day of month
        var dayInFeb = "01";
        var dayInLongMonth = "01";
        var dayInShortMonth = "01";
    }
        if (!isNaN(yearTwoDigitNum)) { //check that the two char year number actually is a number as a partial validation of the five characters being a date code
            switch (monthThreeCharName) { //do a switch-case iteration to see if the three characters are month abreviation and if so convert to numeric equivalent
                case "Jan":
                    return yearTwoDigitNum+"01"+dayInLongMonth; //return the concatonated revesed date in numeric form e+g+ "20"+"01"
                    break;
                case "Feb":
                    return yearTwoDigitNum+"02"+dayInFeb;
                    break;
                case "Mar":
                    return yearTwoDigitNum+"03"+dayInLongMonth;
                    break;
                case "Apr":
                    return yearTwoDigitNum+"04"+dayInShortMonth;
                    break;
                case "May":
                    return yearTwoDigitNum+"05"+dayInLongMonth;
                    break;
                case "Jun":
                    return yearTwoDigitNum+"06"+dayInShortMonth;
                    break;
                case "Jul":
                    return yearTwoDigitNum+"07"+dayInLongMonth;
                    break;
                case "Aug":
                    return yearTwoDigitNum+"08"+dayInLongMonth;
                    break;
                case "Sep":
                    return yearTwoDigitNum+"09"+dayInShortMonth;
                    break;
                case "Oct":
                    return yearTwoDigitNum+"10"+dayInLongMonth;
                    break;
                case "Nov":
                    return yearTwoDigitNum+"11"+dayInShortMonth;
                    break;
                case "Dec":
                    return yearTwoDigitNum+"12"+dayInLongMonth;
                    break;
                default: //if the three char month name turns out not to be a month then return value
                    return value+"-NoDate";
            }
        }
        else { //last two characters is not a number so just return value
            return value+"-NoDate";
        }
}

/* Returns false if the currently selected row is a compound row that was hidden because of a filtering action but has been revealed because it's visible row was clicked. This is used to prevent edit actions on the normally hidden rows. Otherwise true is returned.  */
function notHiddenCompound() {
	return !compoundHiddenAry[valGet("storeSelectedRecordIdR")]; // //THIS IS A GLOBAL ARRAY - NOT PASSED AS AN ARGUMENT !!
}

function messageChangeInhibited() {
	alert("Please remove filters before trying to change this row!");
}

function msgEditDenied() {
	alert("Sorry, you don't have editing privileges!");
}

/* Used to replace eval() which is deprecated and not advised. Creates a function name out of two passed strings, nameFirstPart, nameSecondPart, and returns a reference to the actual function so it can be called using the form:
getFunc(nameFirstPart, nameSecondPart)(arguments...) 
If the strings don't form a known function an alert with the concatonated strings suffixed by " Not Function!" will be generated and "" returned.  */
function getFunc(nameFirstPart, nameSecondPart) {
	var fn = window[nameFirstPart+nameSecondPart];
	if (typeof fn === "function") {
		return fn;
	}
	alert(nameFirstPart+nameSecondPart+" Not Function!");
	return "";
}

/* Changes the visibility of rows that exist but are hidden by default in the current display view because they are excluded by filter settings. The criteria for controlling a row's visibility is that it has a matching compound number. showHide parameter determines whether the row will be be made visible or invisible by calling this function. */
function showHideCompoundRows(cellId, compoundGroupIdrAry, compoundTypeAry, compoundHiddenAry, pivotButIsSet, showHide) {
	START("showHideCompoundRows()");
	var rowId = cellId.split("-")[0];
	if (!pivotButIsSet && (compoundTypeAry[rowId] != "None")) {
		var compoundNumIdrAry = getcompoundNumIdrAry(compoundGroupIdrAry, rowId); //indexed subarray of idRs grouped by a common compound number
		for (var i = 0; i < compoundNumIdrAry.length; i++) { //loop through all idRs of compound group belonging to clicked cell
			if (compoundHiddenAry[compoundNumIdrAry[i]] == true) { //only process if row belongs to the list of compounds hidden by default (filtered)
				if (showHide == "Show") {
					document.getElementById(compoundNumIdrAry[i]).style.display = 'flex';
				}
				else {
					document.getElementById(compoundNumIdrAry[i]).style.display = 'none';
				}
			}
		}
	}
	FINISH("showHideCompoundRows()");
}


/* Uses passed cell ids, amount, array of compound numbers linked to idRs, colour suffix class array, and bank account flag to calculate amount values for withdrawn and paidin to maintain a consistant sum for all cells in the compound group. Returns an object (array) with subarrays of idRs, colIds, amounts and original class suffixes (which this function replaces with "waitingForServer" ones) in the form:
{
    "idrAry": [
        "348",
        "351",
        "495"
    ],
    "withdrawnColId": "3",
    "paidinColId": "4",
    "withdrawnAry": [
        3,
        0,
        0
    ],
    "paidinAry": [
        0,
        12,
        53.77
    ],
    "withdrnOrgSuffixClassAry": [
        "yellowGradientHardBot",
        "green",
        "greenGradientHardTop"
    ],
    "paidinOrgSuffixClassAry": [
        "yellowGradientHardBot",
        "green",
        "greenGradientHardTop"
    ]
}
} */
function compoundGroupAry(cellId, withdrnId, paidinId, compoundGroupIdrAry, colClssAry, isBankAcc) {
	START("compoundGroupAry()");
	var compoundNumIdrAry = getcompoundNumIdrAry(compoundGroupIdrAry, withdrnId.split("-")[0] ); //indexed subarray of idRs grouped by a common compound number with the edit cell idR extracted from withdrnId
	var keyOfEditIdr = getKeyFromValue(compoundNumIdrAry, withdrnId.split("-")[0]);
	var withdrawnAry = compoundNumIdrAry.map(getCellValues, {cellId: withdrnId}); //map via compoundNumIdrAry() to get an array of withdrawn ammounts in the compound group
	var paidinAry = compoundNumIdrAry.map(getCellValues, {cellId: paidinId});

	var withdrnOrgSuffixClassAry = compoundNumIdrAry.map(changeAndSaveClassSuffixes, {cellId: withdrnId, colClssAry: colClssAry}); //map via compoundNumIdrAry() to save (original) and replace class suffix
	var paidinOrgSuffixClassAry = compoundNumIdrAry.map(changeAndSaveClassSuffixes, {cellId: paidinId, colClssAry: colClssAry});

	var amountsAry = {"cellId":cellId, "idrAry":compoundNumIdrAry, "withdrawnColId":withdrnId.split("-")[1], "paidinColId":paidinId.split("-")[1], "withdrawnAry":withdrawnAry, "paidinAry":paidinAry, "withdrnOrgSuffixClassAry":withdrnOrgSuffixClassAry, "paidinOrgSuffixClassAry":paidinOrgSuffixClassAry, "isBankAcc":isBankAcc};
	function getCellValues(value) {
		var colId = this.cellId.split("-")[1];
		var rowId = value;
	  	return Number(sanTwoDecPlcs(document.getElementById(rowId+"-"+colId).innerText)); //get cell value
	}
	function changeAndSaveClassSuffixes(value) {
		var colId = this.cellId.split("-")[1];
		var rowId = value;
		return changeSuffixClass((rowId+"-"+colId), this.colClssAry["waitingForServer"]); //return original suffix for assembly into an array and replace it with temporary "waitingForServer" one
	}
	applyAmountRules(amountsAry); //amountsAry is passed by reference so can be modified within function an doesn't need to be returned (THIS IS THE CASE FOR MANY ajax FUNCTIONS BUT RETURNS ARE USED FOR CLARITY TO INDICATE WHAT'S GOING ON !)
	FINISH("compoundGroupAry()");
	return amountsAry;
}




function applyAmountRules(amountsAry) {
	START("applyAmountRules()");
	var isBankAcc = amountsAry["isBankAcc"];
	var editCellId = amountsAry["cellId"];
	var editColId = editCellId.split("-")[1];
	var editIdR = editCellId.split("-")[0];
	if (editColId == amountsAry["withdrawnColId"]) { //creates col id for other pair of the column that is being edited, 
		editColName = "withdrawnColId";
		otherColName = "paidinColId";
	}
	else {
		editColName = "paidinColId";
		otherColName = "withdrawnColId";
	}
	var idrAry = amountsAry["idrAry"];
	var keyOfEditIdr = getKeyFromValue(idrAry, editIdR);
	var withdrawnAry = amountsAry["withdrawnAry"]; //reflects existing and edited cell
	var paidinAry = amountsAry["paidinAry"]; //reflects existing and edited cell
	var withdrawnResultsAry = []; //created to receive amount values modified by rules
	var paidinResultssAry = []; //created to receive amount values modified by rules
	if (keyOfEditIdr == 0) { //editing either the master of a compound group, or a single transaction on it's own - if master then it can take any value and slaves will be processed below according to rules
		if ((isBankAcc) && (idrAry.length == 1)) { //editing a single line bank account row so both withdrawn and paidin can contain values - no other rows to consider
			//do nothing - the entered value and any existing value of the other cell of the pair can go to the server for updating the table
		}
		else { //normal edit of a master of a compound group, or a single transaction on it's own
			if (withdrawnAry[0] == paidinAry[0]) { //master edit on other side with same value has forced change of sides
				swapAllExceptKeyOfEditIdr(withdrawnAry, paidinAry, keyOfEditIdr); //swap values from one side to another except that at index keyOfEditIdr
				if (editColName == "withdrawnColId") {
					amountsAry["paidinAry"][0] = 0; //zero paidin master because it will still have original matching value in it
				}
				else {
					amountsAry["withdrawnAry"][0] = 0; //zero withdrawn master because it will still have original matching value in it
				}
				alert("Changing Sides!");
			}
			else {
				var slaveDivisor = (idrAry.length -1); //number to divide first slave by
				if (editColName == "withdrawnColId") { //withdrawn cell is the one edited
					withdrawnResultsAry[0] = withdrawnAry[0]; //simply copy value received from edited cell to results array witn no changes
					paidinResultssAry[0] = 0; //clear other side of pair
					var valueLeftForNextSlave = withdrawnAry[0]; //start with value of master
					for (var slaveIdx = 1; slaveIdx < idrAry.length; slaveIdx++) { //loop through all withdrawn slaves
						withdrawnResultsAry[slaveIdx] = smartMoneyDiv(valueLeftForNextSlave, slaveDivisor); //set slave to current valueLeftForNextSlave divided by current slaveDivisor (rounded to 2 dec places)
						valueLeftForNextSlave -= withdrawnResultsAry[slaveIdx];
						slaveDivisor--;
						paidinResultssAry[slaveIdx] = 0; //clear other side of pair
					}
					sortNumAryDesPosAndNeg(withdrawnResultsAry);
				}
				else { //paidin cell is the one edited
					paidinResultssAry[0] = paidinAry[0]; //simply copy value received from edited cell to results array witn no changes
					withdrawnResultsAry[0] = 0; //clear other side of pair
					var valueLeftForNextSlave = paidinAry[0]; //start with value of master
					for (var slaveIdx = 1; slaveIdx < idrAry.length; slaveIdx++) { //loop through all withdrawn slaves
						paidinResultssAry[slaveIdx] = smartMoneyDiv(valueLeftForNextSlave, slaveDivisor); //set slave to current valueLeftForNextSlave divided by current slaveDivisor (rounded to 2 dec places)
						valueLeftForNextSlave -= paidinResultssAry[slaveIdx];
						slaveDivisor--;
						withdrawnResultsAry[slaveIdx] = 0; //clear other side of pair
					}
					sortNumAryDesPosAndNeg(paidinResultssAry);
				}
				amountsAry["withdrawnAry"] = withdrawnResultsAry; //assign the results to original amounts subarray here so that if isBankAcc is imposed the original amounts will remain for double entry
				amountsAry["paidinAry"] = paidinResultssAry;
			}
		}
	}
	else { //editing a slave
		if (idrAry.length < 3) { //only one master and one slave so editing single slave (id = 1)
			alert("Can't change single slave!");
			withdrawnResultsAry[0] = withdrawnAry[0]; //simply copy master value witn no changes
			paidinResultssAry[0] = paidinAry[0]; //simply copy master value witn no changes
			withdrawnResultsAry[1] = withdrawnAry[0]; //simply copy value received from edited slave to results array witn no changes
			paidinResultssAry[1] = paidinAry[0]; //simply copy value received from edited slave to results array witn no changes
			amountsAry["withdrawnAry"] = withdrawnResultsAry; //assign the results to original amounts subarray here so that if isBankAcc is imposed the original amounts will remain for double entry
			amountsAry["paidinAry"] = paidinResultssAry;
		}
		else { //more than one slave so apply rules to alter them
			
			if (editColName == "withdrawnColId") { //withdrawn slave is the one edited
				if (0 < paidinAry[0]) { //slave edit has forced change of sides
					swapAllExceptKeyOfEditIdr(withdrawnAry, paidinAry, keyOfEditIdr)
					alert("Changing Sides!");
				}
				withdrawnResultsAry[0] = withdrawnAry[0]; //simply copy master value witn no changes
				paidinResultssAry[0] = paidinAry[0]; //simply copy master value witn no changes
				applySlaveRules(withdrawnAry, keyOfEditIdr, withdrawnResultsAry, paidinResultssAry);
			}
			else { //paidin cell is the one edited
				if (0 < withdrawnAry[0]) {
					swapAllExceptKeyOfEditIdr(withdrawnAry, paidinAry, keyOfEditIdr)
					alert("Changing Sides!");
				}
				withdrawnResultsAry[0] = withdrawnAry[0]; //simply copy master value witn no changes
				paidinResultssAry[0] = paidinAry[0]; //simply copy master value witn no changes
				applySlaveRules(paidinAry, keyOfEditIdr, paidinResultssAry, withdrawnResultsAry);
			}
			amountsAry["withdrawnAry"] = withdrawnResultsAry; //assign the results to original amounts subarray here so that if isBankAcc is imposed the original amounts will remain for double entry
			amountsAry["paidinAry"] = paidinResultssAry;
		}
	}
	FINISH("applyAmountRules()");
}

/* Swaps all values from one array to the other, except at index position keyOfEditIdr.  */
function swapAllExceptKeyOfEditIdr(sourceAry1, sourceAry2, keyOfEditIdr) {
	START("swapAllExceptKeyOfEditIdr()");
	for (var slaveIdx = 0; slaveIdx < sourceAry1.length; slaveIdx++) {
		if (slaveIdx == keyOfEditIdr)  { //slave matching keyOfEditIdr
			//leave arrays as they are at thie position
		}
		else { //slave that is none of the above
			var sourceAry1Temp = sourceAry1[slaveIdx]; //swap paid in and withdrawn (two lines below as well)
			sourceAry1[slaveIdx] = sourceAry2[slaveIdx]; 
			sourceAry2[slaveIdx] = sourceAry1Temp;
		}
	}
	FINISH("swapAllExceptKeyOfEditIdr()");
}

/* Applies the rules by taking the values from the source array and creating an editedResultsAry from the original [].  */
function applySlaveRules(sourceAry, keyOfEditIdr, editedResultsAry, zeroedResultsAry) {
	START("applySlaveRules()");
	//var editedResultsAry = [];
	//var zeroedResultsAry = [];
	var sumOfAllExceptTwo = getsumOfAllExceptTwo(sourceAry, keyOfEditIdr); //gets sum of all the rest of the slaves except the two that will be changed
	for (var slaveIdx = 1; slaveIdx < sourceAry.length; slaveIdx++) {
		if (slaveIdx == keyOfEditIdr)  { //slave matching keyOfEditIdr
			editedResultsAry[slaveIdx] = sourceAry[slaveIdx]; //set editedResultsAry to edited value
		}
		else if ((slaveIdx - 1) == keyOfEditIdr) { //slave below slave matching keyOfEditIdr
			editedResultsAry[slaveIdx] = sourceAry[0] - (sumOfAllExceptTwo + sourceAry[slaveIdx - 1]); //
		}
		else if (((slaveIdx + 1) == keyOfEditIdr) && ((slaveIdx + 2) == sourceAry.length)) { //slave above slave matching keyOfEditIdr (which is at last position)
			editedResultsAry[slaveIdx] = sourceAry[0] - (sumOfAllExceptTwo + sourceAry[slaveIdx + 1]); //
		}
		else { //slave that is none of the above
			editedResultsAry[slaveIdx] = sourceAry[slaveIdx]; //set editedResultsAry to edited value
		}
		zeroedResultsAry[slaveIdx] = 0; //clear other side of pair
	}
	FINISH("applySlaveRules()");
}


/* Sorts and array in numerically, in terms of absolute value disrigarding sign. e.g. [7,2,5] becomes [7,5,2] and [-4,-8,-3] becomes [-8,-4,-3]. The array doesn't need to be returned as it is passed by reference. */
function sortNumAryDesPosAndNeg(aryToSort) {
	START("sortNumAryDesPosAndNeg()");
	aryToSort.sort(function(a, b){return Math.abs(b) - Math.abs(a)});
	FINISH("sortNumAryDesPosAndNeg()");
}


/* Returns the sum of the values in all cellValuesAry positions greater than index 0, except the one indexed by keyOfEditIdr and the one below it (or above it if it's the last one). keyOfEditIdr index values of 0 or greater than the last index in the array will return 0. */
function getsumOfAllExceptTwo(cellValuesAry, keyOfEditIdr) {
	START("getsumOfAllExceptTwo()");
	var acruedSlaveValues = 0;
	if 	((cellValuesAry.length <= keyOfEditIdr) || (keyOfEditIdr == 0)) { 
		return 0;
	}
	for (var slaveIdx = 1; slaveIdx < cellValuesAry.length; slaveIdx++) { //loop through all array except index 0
		if (slaveIdx == keyOfEditIdr)  { //slave matching keyOfEditIdr
			//do nothing
		}
		else if ((slaveIdx - 1) == keyOfEditIdr) { //slave below slave matching keyOfEditIdr
			//do nothing
		}
		else if (((slaveIdx + 1) == keyOfEditIdr) && ((slaveIdx + 2) == cellValuesAry.length)) { //slave above slave matching keyOfEditIdr (which is at last slave)
			//do nothing
		}
		else { //slave that is none of the above
			acruedSlaveValues += cellValuesAry[slaveIdx]; //acrue the values of all except the one indexed by keyOfEditIdr and the one below it (or above it if it's the last one)
		}
	}
	FINISH("getsumOfAllExceptTwo()");
	return (acruedSlaveValues);
}


function smartMoneyDiv(dividend, divisor) {
	START("smartMoneyDiv()");
	if (dividend == 0) {
		return 0;
	}
	else {
		if (dividend < 0) { //dividend is -ve
			conLog((dividend / divisor) + 0.003);
			return Number ( (-( ((-dividend / divisor) + 0.003) .toFixed(2) )).toFixed(2)  ); 
		}
		else {
			conLog((dividend / divisor) + 0.003);			
			return Number(((dividend / divisor) + 0.003).toFixed(2));
		}
	}
	FINISH("smartMoneyDiv()");
}


/* Returns the indexed subarray of idRs grouped by a common compound number with the passed idR. If no match is found (say if idR is not part of a compound group) a one item array of idR is returned  */
function getcompoundNumIdrAry(compoundGroupIdrAry, idR) {
	START("getcompoundNumIdrAry()");
	for (let key in compoundGroupIdrAry) { //loop through all objects (indexed subarrays) in the compoundGroupIdrAry
		subarray = compoundGroupIdrAry[key]; //extract each subarry as the loop iterates
		for (var i = 0; i < subarray.length; i++) { //go through each position in the current subarray
			if (subarray[i] == idR) { //if given idR matches a subarray value (idR)
				FINISH("getcompoundNumIdrAry()");
				return compoundGroupIdrAry[key]; //use the key of the subarray to return the indexed subarray of idRs in teh compound group
			}
		}
	}
	FINISH("getcompoundNumIdrAry()");
	return [idR]; //no matching idR found (idR is not part of a compound group?) so simiply return the search idR itself
}

function chkAndUpdtValueAndClasSfx (idrAry, colId, updatedValueAry, origValueAry, orgSuffixClassAry) {
	START("chkAndUpdtValueAndClasSfx()");
	//alert("In chkAndUpdtValueAndClasSfx !");
	for (var i = 0; i < idrAry.length; i++) { //loop through all idr indexes (same index will be used for updatedValueAry and orgSuffixClassAry)
		cellId = idrAry[i]+"-"+colId;
		var updatedValueFmtd = sanTwoDecPlcs(updatedValueAry[i]);
		var origValueFmtd = sanTwoDecPlcs(origValueAry[i]);
		if (updatedValueFmtd == origValueFmtd) {
			document.getElementById(cellId).innerText = updatedValueFmtd;
			changeSuffixClass(cellId, orgSuffixClassAry[i]);
		}
	}
	FINISH("chkAndUpdtValueAndClasSfx()");
}



/* Goes down column of cells, determined by array of row idRs and colId, copying classes to an array indexed by idR and returns the array. */
function getAryOfClasses(idrAry, colId) {
	START("getAryOfClasses()");
	var savedCellClassesArry = {};
	idrAry.forEach(function(idR) { //loop through all rows
		savedCellClassesArry[idR] = document.getElementById(idR+"-"+colId).className; //save class
		
	});
	FINISH("getAryOfClasses()");
	return savedCellClassesArry;
}


function setSeveralClasses(elementId, arrayOfClasses) {
	START("setSeveralClasses()");
	var elementClass = "";
	arrayOfClasses.forEach(myFunction);
	function myFunction(value, index) {
	  	if (index == 0) {
	  		elementClass = value;
	  	}
	  	else {
	  		elementClass = elementClass + " " + value;
	  	}
	}
	document.getElementById(elementId).className = elementClass; //set to selected class
	FINISH("setSeveralClasses()");
}



/* For the element identified by elementId any preexisting OldsuffixClass of a compound class (like "mainClass oldSuffixClass") is returned. If there is no preexisting OldsuffixClass "" is returned.  */
function getSuffixClass(elementId) {
	START("getSuffixClass()");
	var combinationClass = document.getElementById(elementId).className.split(" ");
	if (2 == combinationClass.length) { //a preexisting oldSuffixClass exists
		return combinationClass[1];
	}
	else { //no preexisting oldSuffixClass so return ""
		return "";
	}
	FINISH("getSuffixClass()");
}

/* For the element identified by elementId any preexisting OldsuffixClass of a compound class (like "mainClass oldSuffixClass") is returned for storage (if required) and replaced by the passed newSuffixClass. If there is no preexisting OldsuffixClass "" is returned, and the mainClass is suffixed with a space followed by the passed newSuffixClass to create a new compound class. If newSuffixClass is not passed or it is "" any preexisting OldsuffixClass is removed along with its preceding space.  */
function changeSuffixClass(elementId, newSuffixClass = "") {
	START("changeSuffixClass()");
	var combinationClass = document.getElementById(elementId).className.split(" ");
	var mainClass = combinationClass[0];
	if(2 == combinationClass.length) { //a preexisting oldSuffixClass exists
		var oldSuffixClass = combinationClass[1];
		if (0 < newSuffixClass.length) { //passed newSuffixClass contains a string so add it to preexisting mainClass
			document.getElementById(elementId).className = mainClass + " " + newSuffixClass; //set class to mainClass + newSuffixClass
		}
		else { //passed newSuffixClass empty or not given so just set mainClass (effectively removing any preexisting oldSuffixClass and preceding space)
			document.getElementById(elementId).className = mainClass;
		}
		FINISH("changeSuffixClass()");
		return oldSuffixClass;
	}
	else { //no preexisting oldSuffixClass so if it exists concatonate newSuffixClass to mainClass and return ""
		if (0 < newSuffixClass.length) { //passed newSuffixClass contains a string so add it to preexisting mainClass
			document.getElementById(elementId).className = mainClass + " " + newSuffixClass; //set class to mainClass + newSuffixClass
		}
		else {
			//do nothing - existing mainClass will remain undisturbed
		}
		FINISH("changeSuffixClass()");
		return "";
	}
}



/* For the element identified by elementId a suffix class is concatonated to the already existing class with, a space in between. If no suffix class is passed or it is "" nothing will change.  */
function addSuffixClass(elementId, suffixClass = "") {
	START("addSuffixClass()");
	if (0 < suffixClass.length) {
		document.getElementById(elementId).className = document.getElementById(elementId).className + " " + suffixClass; //set to selected class
	}
	FINISH("addSuffixClass()");
}

/* For the element identified by elementId that has a compound class (like "mainClass suffixClass") the suffix class is removed from the element and returned by this function for storage if required. For elements that already have only a mainClass with no space and suffixClass, nothing happens and "" is returned. */
function getAndRemoveSuffixClass(elementId) {
	START("getAndRemoveSuffixClass()");
	var combinationClass = document.getElementById(elementId).className.split(" ");
	if(1 < combinationClass.length) { //check that suffix class exists
		var mainClass = classCombination[0];
		var suffixClass = classCombination[1];
		document.getElementById(elementId).className = mainClass; //set class to mainClass only (suffixClass is gone)
		return suffixClass;
	}
	else { //no suffix class so return ""
		return "";
	}
	FINISH("getAndRemoveSuffixClass()");
}

function toggleClickDown() {
	START("toggleClickDown()");
	if (autoClickDwnFromCalOrButPnl) { //toggle autoClickDwnFromCalOrButPnl to false
		autoClickDwnFromCalOrButPnl = false;
		getFunc(autoClickDownSubId, 'changeButClass')(""); //function within subButPanelJSclickDown() to select/unselect button by changing the class - default "" means unselected
	}
	else {  //toggle autoClickDwnFromCalOrButPnl to true
		autoClickDwnFromCalOrButPnl = true;
		
		getFunc(autoClickDownSubId, 'changeButClass')("Selected"); //function within subButPanelJSclickDown() to select/unselect button by changing the class		
	}
	var colName =  staticArys["displayCellDescrpAry"][valGet("seltdRowCellId").split("-")[1]];
	getFunc(butPanelIdSuffix+colName, 'getFocusBack')(); //target the function in the current button panel to shift focus from the auto button
	FINISH("toggleClickDown()");
}

function consoleAry(arrayToDisplayInConsole) {
	if (consoleAryMode == "On") {
		console.log(JSON.stringify(arrayToDisplayInConsole, null, 4));
	}
}

function conLog(valueToDisplay) {
	if (conLogMode == "On") {
		console.log(valueToDisplay);
	}
}


/* checks to see if an object is empty and returns true if it is (from stackoverflow)  */
function isEmpty(obj) {
	START("isEmpty()");
    for(var prop in obj) {
        if(obj.hasOwnProperty(prop))
            return false;
    }
    FINISH("isEmpty()");
    return true;
}


/* Checks to see if the passed characters match the first few (or all if necessary) characters of a test string - case independent. Returns true for match, false for no match */
function charsMatchStringStart(charsToMatch, testString) {
	START("charsMatchStringStart()");
	charsLowerCase = charsToMatch.toLowerCase();
	testStringLowerCase = testString.toLowerCase();
	if (testStringLowerCase.search(charsLowerCase) == 0) {
		FINISH("charsMatchStringStart()");
		return true;
	}
	else {
		FINISH("charsMatchStringStart()");
		return false;
	}
}


/* Returns the key or index for the given value. An array of values is passed as an argument for the key (index) selection to be made from. If no match is found -1 is returned  */
function getKeyFromValue(aryOfValues, value) {
	START("getKeyFromValue()");
	for (i = 0; i < aryOfValues.length; i++) {
		if (aryOfValues[i] == value) {
			return i;
		}
	}
	FINISH("getKeyFromValue()");
	return -1;
}


/* removes all special/awkward characters and only passes through those that can faithfully be written up to the database table and got back again. REMOVES NEWLINE CHARACTERS!! */
function sanitiseText(inputText) {
	START("sanitiseText()");
	var firstPass = inputText.replace(/\r?\n|\r/g, ' '); //removes \n and \r or combination(?) and replaces them with a single space character
	var secPass = firstPass.replace(/\"|\&|\+|\\/g, ''); //removes " & + \  (these symbols upset things! Not sure exactly at what point in the process - needs further research/workarounds)
	var thirdPass = secPass.replace(/\,/g, ''); //removes ,  as it upsets downloaded csv file (obviously!!)
	var fourthPass = thirdPass.trim(); //removes leading and trailing whitespace
	FINISH("sanitiseText()");
	return fourthPass;
}

/* Removes any consecutive space characters leaving just single ones between other characters. Also removes any leading or trailing spaces. So "  One    Two  " returns as "One Two". Use after sanitiseText() to really clean input! */
function removeExtraSpaces(inputText) {
	return inputText.replace(/\s+/g,' ').trim();
}

function replaceNewLine(inputText) {
	START("replaceNewLine()");
	var replacedText = inputText.replace( /\r?\n/gi, '' );
	FINISH("replaceNewLine()");
	return replacedText;
}


function strToHex(strng){
	START("strToHex()");
    var hex, i, singleHex;
    var result = "";
    for (i=0; i < strng.length; i++) {
        hex = strng.charCodeAt(i).toString(16);
        result += ("000"+hex).slice(-4)+" ";
    }
    FINISH("strToHex()");
    return result
}

document.addEventListener("keydown", function(event) {
	//alert(event.code);
	if (((event.code =="ControlLeft") && (currentKey == "Shift")) || ((event.code =="ShiftLeft") && (currentKey == "Control"))) {
		currentKey = "ControlShift";
	}
	else if (event.code =="ControlLeft") {
		currentKey = "Control";
	}
	else if (event.code =="ShiftLeft") {
		currentKey = "Shift";
	}
	else if (event.code =="ShiftRight") {
		currentKey = "RightShift";
	}
	else if (event.code =="AltRight") {
		altGrLastPressedTime = (new Date).getTime() % 1000000000; //time in milliseconds, truncated to 9 digits from 1970 time, to rollover in 11.574 days (so it can fit in a mariadb INT)
		currentKey = "AltGr";
		compoundNum = 0;
	}
	else {
		currentKey = event.which || event.keyCode; //an effort to capture the key number for different operating systems/browsers. different number for uppercase/lowercase
	}
//alert(currentKey);
})

document.addEventListener("keyup", function(event) { //the if and if elses are a bit redundent as currentKey is always set to "None" on keyup!
	if ((currentKey == "AltGr") || (currentKey == "RightShift")) { //AltGr key has just been released
		document.getElementById("7EKR03N0CJ").submit(); //calls new (same) page immediately (refresh to impose compound order for newly created)
	}
  	if ((event.code =="ControlLeft") && (currentKey == "Control")) {
		currentKey = "None";
	}
	else if ((event.code =="ShiftLeft") && (currentKey == "Shift")) {
		currentKey = "None";
	}
	else if ((event.code =="ShiftRight") && (currentKey == "ShiftRight")) {
		currentKey = "None";
	}
	else if ((event.code =="AltRight") && (currentKey == "AltGr")) {
		currentKey = "None";
		compoundNum = 0;
	}
	else {
		currentKey = "None";
	}
})

function keyPressDetectDEPR(event) { //this function is called by the system whenever a non-special key (like shift or control) is pressed
	//currentKey = event.which || event.keyCode; //an effort to capture the key number for different operating systems/browsers. different number for uppercase/lowercase
}

function keyUpDetectDEPR(event) {
	//currentKey = "none";
}

/* 
Called by monthSelSideBar.php - when a month button is pressed while shift button is held down this function suffixes "-mnthButShift" to the value of that button which is collected as a subSubCommand in monthSelProcess.php and forces selection of a range of months. */
function detectShiftBut(event, id) {
	START("detectShiftBut()");
	if (event.shiftKey) {
		var command = document.getElementById(id).value;
		document.getElementById(id).value = command+"-mnthButShift";
	}
	FINISH("detectShiftBut()");
}

/* Deals with clicks on the header section ACTIONS HAVE BEEN MOVED TO clickField() */
function clickHeaderDEPRECATED(event) {
	var id = event.target.id
	if (id.split("-")[0] == "sticky") { //a cell in the sticky row has been clicked
		inrSet(id, ""); //clear the clicked sticky cell 
		valSet("stickyActive-"+id.split("-")[1], "no"); //clears value holder flag to indicate that the sticky value has been cleared

	}
	if (id.split("-")[0] == "heading") { //a cell in the heading row has been clicked clicked
		groupSet(id);
	}
}


/* called when something is keyed or pasted into an editable div - records the id of the div in editableCellIdHldr so the data in the newly changed cell can be accessed when it loses focus.  */
function changeField(event) {
	START("changeField()");
	var id = event.target.id;
	//alert(id);
	if (event.keyCode != 13) { //key other than return pressed so log cell id
		valSet("editableCellIdHldr", id);
	}
	else { //Return has been pressed (only works if an element covered by onkeyup="changeField(event)" is in focus)
		if (!autoClickDwnFromCalOrButPnl) { //not in auto clickdown from calendar of button panel so just click to next cell down in response to return key without changing anything
			document.getElementById(id).blur(); //unselect div cell so the innerText command gets an uncorrupted version that is not messed up by browsere weirdness like divs within divs while editing
			document.getElementById(id).innerText = sanitiseText(document.getElementById(id).innerText); //sanitise text by removing unwanted characters and newlines back to cell
			document.getElementById(id).focus();
			clickCellBelow(valGet("seltdRowCellId"), idrAry, "From Return Key");
		}
		else { //auto clickdown from calendar of buton panel enables do click and get focus on the current cell in response to the return key which will cancel the auto click down as clickField() is entered
			document.getElementById(valGet("seltdRowCellId")).click();
			document.getElementById(valGet("seltdRowCellId")).focus();
		}
	}
	FINISH("changeField()");
}


/* when called with the id of a cell in the form "342-7" and an indexed array of the idRs for all rows, in display order, in the form ["321", "342", "129", ...] the cell immediately below the one referenced by id will be selected and brought into focus exactly as if it had been clicked with the mouse. If there is no cell below the cell in the top row of the same column will be selected. */
function clickCellBelow(id, idrAry, source) {
	START("clickCellBelow()");
	allowSetSticky = false; //inhibit sticky in case rapid clicking of sucessive calendar or butPanel buttons triggers it - it is re-enabled after double click detection block at beginning of doEverything()
	fromClickCellCmnd = true; //indicates that the cell click has come from one of the button panels and not a mouse click on the display area
	if ((autoClickDwnFromCalOrButPnl || (source == "From Return Key")) && Array.isArray(idrAry) && (0 < idrAry.length) && atomicAjaxCallCompleted) { //check to see that some idRs exist - indicates page isn't empty! Also that an ajax call isn't still waiting to complete
		var idR = id.split("-")[0];
		var column = id.split("-")[1];
		var idrIndexNew = idrAry.indexOf(idR) + 1;
		if (idrIndexNew < idrAry.length) { //check that there is still a row below the current one (length is always 1 greater thatn the highest index)
			var newCellId = idrAry[idrIndexNew]+"-"+column;
		}
		else { //no more rows so go to top row
			var newCellId = idrAry[0]+"-"+column;
		}
		document.getElementById(newCellId).click();
		//doEverything(newCellId, false, false, ""); //alternate way (with focus) of selecting the cell without using click() - seems to work and may be useful in some circumstances
		document.getElementById(newCellId).focus();
	}
	FINISH("clickCellBelow()");
}


function returnPress(event) { //BECAUSE changeField() IS NOW EXECUTED IN clickField(event) PRESSING RETURN ONLY UPDATES paidin and withdrawn fields WHEN A CELL IS CLICKED (NEED TO FIX THIS !!)
	START("returnPress()");
  	if (event.keyCode === 13) {
    document.getElementById(valGet("seltdRowCellId")).blur(); //used to remove focus from the selected item. same effect as onchange() for paidin and withdrawn fields - does a submit of the value to server if there has been a change of value
    valSet("editableCellIdHldr", valGet("seltdRowCellId"));
   // upDatewithdrnPaidin(); // DEPRECATED - redirects to ajaxRecordsWithdrawnPaidinAndCellsUpdate() in this file, which makes a server call to update the record for the clicked field to  the withdrawn or paidin value
  	}
  	FINISH("returnPress()");
}


/* Sets class of item pointed to by itemKeySelected (uses baseIdRand for disambiguation) to onClass and all other classes (designated by baseIdRand along with each id from itemKeysCsv) to offClass. */
function setOneStrClassUnsetRest(baseId, onClass, offClass, itemKeysCsv, itemKeySelected) {
	START("setOneStrClassUnsetRest()");
    if ((2 < itemKeysCsv.length) && (0 < itemKeySelected)) { //this function body is only allowed to run if there is at least "1,2" in the itemKeysCsv and itemKeySelected contains an actual value other than 0. This is because it (probably the split or loop) misbehaves and doesn't complete. The consequence of this is that any buttons don't have their classes changed unless there are at least 3 of them. This could be rewritten to fix.
      var itemKeysAry = itemKeysCsv.split(',');
      var idxSelected = itemKeysAry.indexOf(itemKeySelected.toString());
      maxId = itemKeysAry.length - 1;
      for (i = 0; i <= maxId; i++) { //clear all values to "" and button classes to offClass
          document.getElementById(baseId+i).className = offClass;
      }
      document.getElementById(baseId+idxSelected).className = onClass;
    }
    FINISH("setOneStrClassUnsetRest()");
}

/* Converts passed date string by reversing it, e.g. "07-04-2020" to "2020-04-07" or "2020-04-07" to "07-04-2020" to change to and from database table format when required for display in "07-04-2020" format. */
function reverseDate(date) {
	START("reverseDate()");
	var dateAry = date.split("-");
	var reversedDate = dateAry[2]+"-"+dateAry[1]+"-"+dateAry[0]; 
	FINISH("reverseDate()");
	return reversedDate;
}

/* Converts passed date string, date, (which is in the form "07-04-2020") by reversing it, removing the separator "-"s, and returning "20200407" which can be used directly for comparisons such as > < ==. */
function reverseDateNumsOnly(date) {
	START("reverseDateNumsOnly()");
	var dateAry = date.split("-");
	var reversedDateNumsOnly = dateAry[2]+dateAry[1]+dateAry[0];
	FINISH("reverseDateNumsOnly()");
	return reversedDateNumsOnly; 
}

/* Converts passed date string, date, (which is in the form "07-04-2020" or "2020-04-07") by removing the separator "-"s, and returning "07042020" or "20200407" which can be used directly for comparisons such as > < ==. */
function dateNumsOnly(date) {
	START("dateNumsOnly()");
	var dateAry = date.split("-");
	var dateNumbersOnly =dateAry[0]+dateAry[1]+dateAry[2];
	FINISH("dateNumsOnly()");
	return dateNumbersOnly; 
}

/* highlights all the rows that use the displayed document. Does lots of additional things too! */
function selectTableRowsForDoc(
	maxColIdx,			//the maximum column index that needs to be highlighted (starts at 0)
	rowSel,				//if true indicates row should be selected, false indicates row should be unselected
	colClssAry,			// array of suffix classes that are all used to difine the color of cells and rows
	compoundTypeAry,	//array of row types for compound rows - like Master, Slave, FinalSlave, None
	compoundColNumAry,  //array of comound colour numbers - 0 = normal colours, 1 = budget onlu colours
	moneyDisplayStr,    //string to inform if either withdrawn or paidin are to be blanked out
	elementId,			//id of clicked element
	rowSelColorClass,	//sets background colour to indicate element has been chosen - tailored to each cell depending on array element for column and is a light grey colour
	columnCsv,			//cvs of all column numbers that have been set to filter, i.e. "2, 5"
	filterClass,		//sets filtered columns in selected rows to the filter colour - pale yellow
	filterClassRightAlign, //sets filtered columns in selected rows to the filter colour - pale yellow, but with right alignment for withdrawn and  paidin cells
	endDate,			//the end date of all the displayed records - used to determine if any reconciled dates are in the future with respect to the displayed rows so they can be set to reconcileWarnClass
	displayCellDescrpAry,	//array of column names - accessed with column id to determine which operations need to be done for different columns
	chosenClassBlank,	//sets background colour to indicate element has been chosen and sets font colour the same to hide text - applied to reconciled date only if "01-01-2000" (default) - white/light grey
	reconcileWarnClass,	//sets background and font colour to indicate reconciled date is in the future with respect to the displayed rows
	reconcileEarlyClass,	//classs to indicate that the reconciliation date has been set earlier than the transaction date - orange?
	docLineCountDispId,
	from
	) {
	START("selectTableRowsForDoc()");
	if (!pivotButIsSet) {
		var columnAry = columnCsv.split(",");
		var currentDocRnd = document.getElementById(elementId.split("-")[0]+"-docRnd").name; //get the random that is associated with the current doc
		var recsAry = document.getElementsByName(currentDocRnd); //get an array of all the elements that have the name attribute set to the same random as the current doc 
		for(idx = 0; idx < recsAry.length; idx++) { //ROW LOOP - loop through all records that have the same doc random
			var rowId = recsAry[idx].id.split("-")[0]; //get the idR of the current element and extract the first integer - the bit before the '-' which is the row id
			//conLog(recsAry[idx].value+" "+rowId);
			for(i = 0; i <= maxColIdx; i++) { //COLUMN LOOP - loop through all the columns in the current row
			    cellId = rowId+"-"+i; //reconstruct the element id for each element the loop addresses
			    

			    	if (displayCellDescrpAry[i] == "MoneyOut") { //check for this section and use compound colours if necessary
			    		if (moneyDisplayStr == "amountPaidIn") {
			    			changeSuffixClass(cellId, colClssAry["blankedMoneyCol"]); //blank
			    		}
			    		else if (compoundTypeAry[rowId] == "Master") {
                            if (compoundColNumAry[rowId] == 0) {
                                changeSuffixClass(cellId, colClssAry["compoundMaster"]); //normal compound master colour
                            }
                            else {
                                changeSuffixClass(cellId, colClssAry["compoundMasterAlt"]); //alternative (budgets) compound master colour
                            }
                        }
                        else if (compoundTypeAry[rowId] == "Slave") {
                            if (compoundColNumAry[rowId] == 0) {
                                changeSuffixClass(cellId, colClssAry["compoundSlave"]); //normal compound slave colour
                            }
                            else {
                                changeSuffixClass(cellId, colClssAry["compoundSlaveAlt"]); //alternative (budgets) compound slave colour
                            }

                        }
                        else if (compoundTypeAry[rowId] == "FinalSlave") {
                            if (compoundColNumAry[rowId] == 0) {
                                changeSuffixClass(cellId, colClssAry["compoundSlaveFinal"]); //normal compound final slave colour
                            }
                            else {
                                changeSuffixClass(cellId, colClssAry["compoundSlaveFinalAlt"]); //alternative (budgets) compound final slave colour
                            }
                        }
			    		else {
			    			if (rowSel) { //not compound rows so use normal select or unselect colours
					    		changeSuffixClass(cellId, colClssAry["selCol"]);
					    	}
					    	else {
					    		changeSuffixClass(cellId, colClssAry["unselCol"]);
					    	}
			    		}
			    	}
			    	else if (displayCellDescrpAry[i] == "MoneyIn") { //check for this section and use compound colours if necessary
			    		if (moneyDisplayStr == "amountWithdrawn") {
			    			changeSuffixClass(cellId, colClssAry["blankedMoneyCol"]); //blank
			    		}
			    		else if (compoundTypeAry[rowId] == "Master") {
                            if (compoundColNumAry[rowId] == 0) {
                                changeSuffixClass(cellId, colClssAry["compoundMaster"]); //normal compound master colour
                            }
                            else {
                                changeSuffixClass(cellId, colClssAry["compoundMasterAlt"]); //alternative (budgets) compound master colour
                            }
                        }
                        else if (compoundTypeAry[rowId] == "Slave") {
                            if (compoundColNumAry[rowId] == 0) {
                                changeSuffixClass(cellId, colClssAry["compoundSlave"]); //normal compound slave colour
                            }
                            else {
                                changeSuffixClass(cellId, colClssAry["compoundSlaveAlt"]); //alternative (budgets) compound slave colour
                            }

                        }
                        else if (compoundTypeAry[rowId] == "FinalSlave") {
                            if (compoundColNumAry[rowId] == 0) {
                                changeSuffixClass(cellId, colClssAry["compoundSlaveFinal"]); //normal compound final slave colour
                            }
                            else {
                                changeSuffixClass(cellId, colClssAry["compoundSlaveFinalAlt"]); //alternative (budgets) compound final slave colour
                            }
                        }
			    		else {
			    			if (rowSel) { //not compound rows so use normal select or unselect colours
					    		changeSuffixClass(cellId, colClssAry["selCol"]);
					    	}
					    	else {
					    		changeSuffixClass(cellId, colClssAry["unselCol"]);
					    	}
			    		}			    	
			    	}
			    	else if (displayCellDescrpAry[i] == "Budget") {
			    		let checkBudgetDatesJSresult = checkBudgetDatesJS(document.getElementById(rowId+"-0").innerText, document.getElementById(cellId).innerText);
			    		if (checkBudgetDatesJSresult == "NotYetActive") {
			    			changeSuffixClass(cellId, colClssAry["budgetNotYetActive"]);
			    		}
			    		else if (checkBudgetDatesJSresult == "Expired") {
			    			changeSuffixClass(cellId, colClssAry["budgetExpired"]);
			    		}
			    		else {
			    			if (-1 < columnAry.indexOf(i.toString())) { //if column is found in columnAry (derived from columnCsv) it is a filtered column
						    	changeSuffixClass(cellId, colClssAry["columnFiltCol"]); //set to filter colour
						    }
			    			else if (rowSel) { //not compound rows so use normal select or unselect colours
					    		changeSuffixClass(cellId, colClssAry["selCol"]);
					    	}
					    	else {
					    		changeSuffixClass(cellId, colClssAry["unselCol"]);
					    	}
			    		}
			    	}
			    	else if (displayCellDescrpAry[i] == "RcnclDate") { //process if loop is at appropriate column ######EVERYTHING IN THIS IF STATEMENT IS FOR THE RECONCILE COLUMN!! #######
				    	var endDateRev = dateNumsOnly(endDate); //the endDate just needs the "-"s removed as it is already in the correct order "2020-04-03"
				    	var transDateRev = reverseDateNumsOnly(document.getElementById(rowId+"-"+displayCellDescrpAry.indexOf("TransDate")).innerText);
				    	var recnclDateRev = reverseDateNumsOnly(document.getElementById(cellId).innerText);
				    	//console.log("TRANS REC "+transDateRev+" "+recnclDateRev);
				    	if (recnclDateRev == "20000101") { //default date so blank display of this by setting font to same color as background
				    		if (rowSel) {
					    		changeSuffixClass(cellId, colClssAry["selInvisCol"]);
					    	}
					    	else {
					    		changeSuffixClass(cellId, colClssAry["unselInvisCol"]);
					    	}
			    		}
				     	else if (endDateRev < recnclDateRev) { //if reconciled date is ahead of the end date of the selection of records so set to warning class
				    		changeSuffixClass(cellId, colClssAry["notRcnclCol"]); //set the reconciled cell to warning class 'reconcileWarnClass' (probably red)
				    	}
				    	else if (recnclDateRev < transDateRev) { //if reconciled date is earlier than the transaction date so set to error class
				    		changeSuffixClass(cellId, colClssAry["rcnclTooEarlyCol"]); //set the reconciled cell to early class (probably orangeish)
				    	}
				    	else {
				    		if (rowSel) {
					    		changeSuffixClass(cellId, colClssAry["selCol"]);
					    	}
					    	else {
					    		changeSuffixClass(cellId, colClssAry["unselCol"]);
					    	}
				    	} //do nothing - leave the reconcile date cell whatever background color has been set as it is a normal date
				    }
					else if (-1 < columnAry.indexOf(i.toString())) { //if column is found in columnAry (derived from columnCsv) it is a filtered column
				    	changeSuffixClass(cellId, colClssAry["columnFiltCol"]); //set to filter colour
				    }
		    		else { //not compound rows so use normal select or unselect colours
		    			if (rowSel) {
				    		changeSuffixClass(cellId, colClssAry["selCol"]);
				    	}
				    	else {
				    		changeSuffixClass(cellId, colClssAry["unselCol"]);
				    	}
		    		}

				    
			}
			//checkTimeout("selectTableRowsForDoc("+from+") col Loop", 0);
		}
		if (docLineCountDispId != "") { //"" is used if clearing a document selection with this function, the id of the doc line count display cell will only have a valid value during doc selection
			document.getElementById(docLineCountDispId).innerText = idx; //display number of transactions associated with the selected document document
		}
	}
	FINISH("selectTableRowsForDoc()");
}




/* Defaults to returning "Nothing To Test" unless the column name (from dispCellDescrpAry) of the cell indexed by elementId matches any property in conditionsObj, in which case returns "Match Fail" unless any specified cell on the same row as the cell indexed by elementId has matching cell csv values as set out in conditionsObj in which case "Match Success" is returned. i.e. if conditionsObj is:
--
{"RcnclDate":{"Account":"General,Cash", "Budget":"FiSCAF,Reserved"} }
--
and elementId resolves to cell "RcnclDate" the function examines (on the same row) the value in the "Account" cell to see if it equals "General" or "Cash" and the value in the "Budget" cell to see if it equals "FiSCAF" or "Reserved", if any case is a match "Match Success" will be returned, "Match Fail" otherwise. If conditionsObj contains nothing it is assumed the test is not required so "Nothing To Test" is returned. 
USED TO BE SIMPLY true OR false WITH true RETURNED FOR BOTH "Match Success" AND "Nothing To Test" BUT IT WAS DECIDED TO DIFFERENTIATE THESE RESULTS TO ALLOW NUANCES, ALTHOUGH THIS FEATURE WAS NOT USED AS OF 2020-05-13. */
function cellMatchInObj(dispCellDescrpAry, elementId, conditionsObj) {
	START("cellMatchInObj()");
	var match = "Match Fail";
	var rowSelected = elementId.split("-")[0]; //extract the first integer of id - the bit before the first '-' which is the selected row id
  	var colSelectedValue = dispCellDescrpAry[elementId.split("-")[1]]; //extract the column string value using the second integer of id, the bit after the first '-', as an index
  	if (conditionsObj.hasOwnProperty(colSelectedValue)) { //check in the conditionsObj for match with the name of the column i.e. "RcnclDate"
  		var colSelectedTestSubObj = conditionsObj[colSelectedValue]; //if the column name is there get the sub object i.e. {"Account":"General,Cash", "Budget":"FiSCAF,Reserved"}
  		for(var csvPropertyName in colSelectedTestSubObj) { //go through each property in the subObject and get the property name (key)
		   var valueCsv = colSelectedTestSubObj[csvPropertyName]; //get values in form of csv - may only be one value
		   valueCsvAry = valueCsv.split(",");
		   for (index = 0; index < valueCsvAry.length; index++) {
		   		var value = valueCsvAry[index];
		   		var colIndex = getKeyFromValue(dispCellDescrpAry, csvPropertyName);
		   		var testCellId = rowSelected+"-"+colIndex;
			    if (document.getElementById(testCellId).innerText == value) { //test conditional row and if it meets condition set match to true
    				match = "Match Success";
    			}
			} 
		}
  	}
  	else { //no properties in conditionsObj match with the name of the column i.e. "RcnclDate" (this could also mean conditionsObj is empty) so default to true as decision is not required for this elementId
  		match = "Nothing To Test";
  	}
  	FINISH("cellMatchInObj()");
  	return match;
}


/* First all button panels are made invisible and then, for the selected cell, the relevent identifier from the butPanelControlAry is used to unhide the specified button panel. Unless the test with conditionsObj succeeds or there is no relevant data for the current column (see description in cellMatchInObj() function) only the panel referenced by dummyButPanelId will displayed. If "None" is the identifier the panel referenced by dummyButPanelId will be displayed. Facilitates granular overriding of general no-edit directive for specific button panels (e.g. budget column) for specific users. */
function selectButPanel(displayCellDescrpAry, fieldNameAry, butPanelControlAry, elementId, prefix, dummyButPanelId, noEditButPanelId, outerContainerForPanel, conditionsObj, restrictionsAry, edit) {
	START("selectButPanel()");
	let show = true;
	let editAllowed = true;
	document.getElementById(outerContainerForPanel).style.display = 'inline'; //makes containing div visible (it is hidden by default so display area sits at the left for 'non-editing' users)
	document.getElementById(noEditButPanelId).style.display = 'none';
	for (index = 0; index < butPanelControlAry.length; index++) { //start by hiding all button panels
		document.getElementById(prefix+butPanelControlAry[index]).style.display = 'none';
	}
	if ((edit == "Edit") || (subAryHasValue(restrictionsAry, "allowColumnEdit", fieldNameAry[elementId.split("-")[1]])  )){ //only allow display of button panel for current column if edit is set, OR the subarry of restrictionsAry selected by "allowColumnEdit" contains column (field) name derived from fieldNameAry by indexing with column number (this allows granular setting of edit privilages to be applied to chosen columns when a general - default condition - no editing directive has been enforced)
		panelIdStrValue = butPanelControlAry[elementId.split("-")[1]]; //extract the panel to be displayed id string value using the second integer of id, the bit after the first '-', as an index
		var objTestResult = cellMatchInObj(displayCellDescrpAry, elementId, conditionsObj);
		if ((panelIdStrValue != "None") && ((objTestResult == "Match Success") || (objTestResult == "Nothing To Test")) ) { //the butPanelControlAry value indexed by elementId is not "None" and cellMatchInObj() passes test
			document.getElementById(prefix+panelIdStrValue).style.display = 'inline';
			START("1st getFunc statement - in selectButPanel()");
			getFunc(prefix+butPanelControlAry[elementId.split("-")[1]], 'initButPanel')(elementId, show, editAllowed); //target the initialisation function in the selected button panel using getFunc to assemble the name of the function which has been dynamically created in each button panel
			FINISH("1st getFunc statement - in selectButPanel()");
		}
		else {
			document.getElementById(dummyButPanelId).style.display = 'inline'; //make visible the dummy button panel - no prefix required for this as it is incorporated into dummyButPanelId
			START("2nd getFunc statement - in selectButPanel()");
			getFunc(dummyButPanelId, 'initButPanel')(elementId, show, editAllowed); //target the initialisation function in the selected button panel using getFunc to assemble the name of the function which has been dynamically created in each button panel
			FINISH("2nd getFunc statement - in selectButPanel()");
		}
	}
	else { //set for no edit so display default empty panel
		document.getElementById(noEditButPanelId).style.display = 'inline'; //make visible the empty no edit button panel - no prefix required for this as it is incorporated into noEditButPanelId
		getFunc(noEditButPanelId, 'initButPanel')(elementId, show, editAllowed); //target the initialisation function in the selected button panel using getFunc to assemble the name of the function which has been dynamically created in each button panel
	}
	//checkTimeout("selectButPanel", 0);
	FINISH("selectButPanel()");
}



/* Applies the appropriate class to a cell that has been selected to give the correct selection colour */
function selectCell(elementId, colClssAry, standardSelClass, SnglSelEditableClass, rightAlignSelClass, blankClass, displayCellDescrpAry, cellSelectColorClass, cellSelectEditColorClass) {
	START("selectCell()");
	var colId = elementId.split("-")[1];		
	var displayCellDescrp = displayCellDescrpAry[colId];
	if ((displayCellDescrp == "MoneyOut") || (displayCellDescrp == "MoneyIn") || (displayCellDescrp == "Reference") || (displayCellDescrp == "Note")) { //editable cell
		changeSuffixClass(elementId, colClssAry["cellSelEditCol"]);
	}
	else { //use normal select class
		changeSuffixClass(elementId, colClssAry["cellSelCol"]);
	}
	if (displayCellDescrp == "RcnclDate") { //reconcile date cell 
		var recnclDate = document.getElementById(elementId).innerText; //get reconciled date from selected cell
		if (recnclDate == "01-01-2000") { //if default set to same text and background colour class to make invisible
			changeSuffixClass(elementId, colClssAry["cellSelInvisCol"]);
		}
	} 
	FINISH("selectCell()");
}

//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ
//ZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZZ



/* PROBABLY NOT GOING TO WORK BECAUSE JAVASCRIPT IS NOT MULTITHREADED SO THIS FUNCTION LOOP WILL JUST RUN AND HOGG ALL THE PROCESSOR TIME SO NOTHING EXTERNAL CAN MODIFY THE LOCKED VAR. Enters a loop that continually checks the lock flag to see if it is empty (" "). When it is empty a unique id is loaded into it and then it is checked again to make sure that the unique id is still the correct one and hasn't been replaced by another because of some race condition. Once verified the function exits.  */
function waitForUnlock(lockFlagId, uniqueId) {
	var locked = true;
	do {
	  if (document.getElementById(lockFlagId).value == "") {
	  	document.getElementById(lockFlagId).value = uniqueId;
	  }
	  if (document.getElementById(lockFlagId).value == uniqueId) {
	  	locked = false;
	  }
	}
	while (locked);
}

/* Clears the lock flag to "". */
function clearLock(lockFlagId) {

}

/* sets the class for the selected row (indicated by rowIdx) to classSel and classes for all other rows to classNorm. All elements in each row are set using a loop and maxColIdx. The class of the button idButEdit is set to classEdit to highlight its edit function. Also sets the value of the elements valHolderForSelRowIdxId to rowIdx and valHolderForSelRecIdRId to recordIdR.  */
function selectTableRowBAK(maxColIdx, id, previousId, editPanelsSufixCsvs, editPanelPrefix, valHolderForSelRowIdxId, valHolderForSelRecIdRId, classNorm, classSel, classEdit, idButEdit) {
  var rowId = id.split("-")[0];
  /*  for(rowI = 0; rowI <= maxRowIdx; rowI++) {
        for(i = 0; i <= maxColIdx; i++) {
            id = rowI+"but"+i;
            document.getElementById(id).className = classNorm;
        }
    } */
    for(i = 0; i <= maxColIdx; i++) {
        idx = rowId+"-"+i;
        document.getElementById(idx).className = classSel;
    }
    document.getElementById(id).className = classEdit;
    //document.getElementById(valHolderForSelRowIdxId).value = rowIdx;
    //document.getElementById(valHolderForSelRecIdRId).value = recordIdR;
}


/* THINK THIS ISN'T USED FOR ANYTHING
function select(id) {
    alert(inrGet(id));
    inrSet(id, valGet("tbl"));
}
*/

/* Tests object that is being used as an associative array to see if the key exists and is set to true. Returns true if this is the case, but false otherwise. */
function existsAndTrue(obj, key) {
	if (obj.hasOwnProperty(key)) {
		if (obj[key] == true) {
			return true;
		}
	}
	return false;
}

/* Returns the value of the element pointed to by id  */
function valGet(id) {
  return document.getElementById(id).value;
}

function valSet(id, value) {
  document.getElementById(id).value = value;
}

function inrGet(id) {
  return document.getElementById(id).innerText;
}

function inrSet(id, value) {
  document.getElementById(id).innerText = value;
}

/* Calls pathToPhpFile (usually index.php) with fileRndm as a command to route to php script that will use cellId to update the document fileName for the pdf that will be downloaded via obscureTest.php. When xmlhttp.readyState is actioned after the doc filename has been updated the document displayed in the iFrame is renewed using alternately docFilename2 or docFilename because the pdf file display module is clever enough to know that if the same filename is being used the document need not be fetched again. By using this subterfuge it will always fetch a new document. The session commit random is also passed to the php script to allow it to verify currency of the nonVolatile variable arrays.  */
function ajaxUpdateDocFileName(
	docUpdateCellId,
	pathToPhpFile,
	fileRndm
	) {
	var currentDocRnd = document.getElementById(docUpdateCellId.split("-")[0]+"-docRnd").name;
	var previousDocRnd = valGet("previousDocRnd");
	if (currentDocRnd != previousDocRnd) { //only if new doc random has been selected - otherwise this routine doesn't run as the doc doesn't need to be updated
		valSet("previousDocRnd", currentDocRnd); //doc has changed so update placeholder for previous doc random so it can be used to check if the next clicked record represents a doc change
		var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
		var xmlhttp;
		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  }
		else
		  {// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
		xmlhttp.onreadystatechange=function() //
		    {
		    if (xmlhttp.readyState==4 && xmlhttp.status==200) //once the update filename php script has completed
		      {
		      	if (valGet("previousObscureFile") == "obscureTest.php") { //set previousObscureFile value holder to obscureTest2.php and obscureTest.php alternately to fool pdfjs
					valSet("previousObscureFile", "obscureTest2.php"); //toggle file name
					document.getElementById("pdfIframe").src  = "./web/viewer.html?file="+docFilename2+"#page="+pageNum+"&zoom=100";
				}
				if (valGet("previousObscureFile") == "obscureTest2.php") {
					valSet("previousObscureFile", "obscureTest.php"); //toggle file name
					document.getElementById("pdfIframe").src  = "./web/viewer.html?file="+docFilename+"#page="+pageNum+"&zoom=100";
				}
		      }
		    }
		xmlhttp.open("POST", pathToPhpFile, true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.send("command="+fileRndm+"&cellId="+docUpdateCellId+"&random="+random);
		
	}
}


/* If the array and key exist, tests the subarray designated by key to see if it contains the value valueToTestFor. If conditions are met returns true, otherwise false.   */
function subAryHasValue(ary, key, valueToTestFor) {
     if (ary.hasOwnProperty(key)) {
        if (Object.values(ary[key]).indexOf(valueToTestFor) > -1) {
		   return true;
		}
		else {
			return false;
		}
     }
     else {
        return false;
     }
}

/* sets the value displayed by the element identified by id to 2 decimal places. Up to 4 decimal places will be shown if more figures are entered. nothing is shown if value is 0. */
function twoDecPlaces(id) {
  unitCostVal = document.getElementById(id).value.toString(); //convert number to string so it can be used by the string replace method.
  unitCostVal = Number(unitCostVal.replace(/[^0-9.]/g,'')); //use string replace with reg expression to replace anything but 0-9 and '.' with '' (nothing). Removes unwanted £ symbols etc. Convert back to Number
  unitCostVal = unitCostVal.toFixed(4); //set to 4 decimal places (0.01p) to cope with surface mount components etc. that have fractional penny values.
  if (unitCostVal.toString().charAt(unitCostVal.toString().length-1) == "0") { //these two if statements remove trailing zeros beyond 2 decimal places but allow up to 4 decimal places for none zero decimals.
    unitCostVal = Number(unitCostVal).toFixed(3);
  }
  if (unitCostVal.toString().charAt(unitCostVal.toString().length-1) == "0") {
    unitCostVal = Number(unitCostVal).toFixed(2);
  }
  if (unitCostVal == 0) {
    unitCostVal = ""; //set to "" so 0.0000 isn't seen if there is no value set
  } 
  document.getElementById(id).value = unitCostVal; //replace unit cost value on form with cleaned value (£s etc removed).
} 

/* Returns the passed value as a string set to 2 decimal places. Up to 4 decimal places will be shown if more figures are entered. Nothing is shown if value is 0. */
function getTwoDecPlacesAndSan(value, showZeroAsFloatStr = false) {
  unitCostVal = value.toString(); //convert number to string so it can be used by the string replace method.
  unitCostVal = Number(unitCostVal.replace(/[^0-9.-]/g,'')); //use string replace with reg expression to replace anything but 0-9 and '.' and '-' with '' (nothing). Removes unwanted £ symbols etc. Convert back to Number (doesn't deal with stupid things like two decimal points - these will produce NaN and be caught in updateWithdrawnPaidin() on the server by simply not updating the allRecords table)

  unitCostVal = unitCostVal.toFixed(4); //set to 4 decimal places (0.01p) to cope with surface mount components etc. that have fractional penny values.
  if (unitCostVal.toString().charAt(unitCostVal.toString().length-1) == "0") { //these two if statements remove trailing zeros beyond 2 decimal places but allow up to 4 decimal places for none zero decimals.
    unitCostVal = Number(unitCostVal).toFixed(3);
  }
  if (unitCostVal.toString().charAt(unitCostVal.toString().length-1) == "0") {
    unitCostVal = Number(unitCostVal).toFixed(2);
  }
  if ((unitCostVal == 0) && (!showZeroAsFloatStr)) {
    unitCostVal = ""; //set to "" so 0.0000 isn't seen if there is no value set
  } 
  return unitCostVal; //return cost value on form with cleaned value (£s etc removed).
} 

/* Returns the passed value as a string set to 2 decimal places. Nothing is shown if value is 0. */
function sanTwoDecPlcs(value) {
  unitCostVal = value.toString(); //convert number to string so it can be used by the string replace method.
  unitCostVal = Number(unitCostVal.replace(/[^0-9.-]/g,'')); //use string replace with reg expression to replace anything but 0-9 and '.' and '-' with '' (nothing). Removes unwanted £ symbols etc. Convert back to Number (doesn't deal with stupid things like two decimal points - these will produce NaN and be caught in updateWithdrawnPaidin() on the server by simply not updating the allRecords table)
  unitCostVal = unitCostVal.toFixed(2); //set to 2 decimal places (1.77)
  if (unitCostVal == 0) {
    unitCostVal = ""; //set to "" so 0.0000 isn't seen if there is no value set
  } 
  return unitCostVal; //return cost value on form with cleaned value (£s etc removed).
}


/* sets the class of element identified by Id to theClass. */
function forceClass(Id, theClass) { 
    document.getElementById(Id).className = theClass;
}


/* Used by calendar php function to set the maximum day of month according to the selected year/month. The first 3 arguments are for the ids of the hidden text boxes that hold the current set year, month and dayOfMonth. The 4th argument is the base id of the dayOfMonth buttons with out the 0-30 index as a suffix. A whole date (i.e. 2018-07-23) is placed in the textbox wholeDateTxtBoxId. */
function setMaxDayOfMnth(yearTxtBoxId, monthTxtBoxId, dayOfMnthTxtBoxId, dayOfMnthUniqueId, onClass, offClass, outerDivId, outerDivClassWarning, wholeDateTxtBoxId) { 
	var year = document.getElementById(yearTxtBoxId).value; //year as 2017, 2018 etc.
	var month = document.getElementById(monthTxtBoxId).value; //month as 1 - 12
  if (month.length < 2) { //pad month with leading zero if it is a single digit
    month = '0'+month;
  }
  var dayOfMnth = document.getElementById(dayOfMnthTxtBoxId).value; //day of month as 1 - 31
  if (dayOfMnth.length < 2) { //pad day of month with leading zero if it is a single digit
    dayOfMnth = '0'+dayOfMnth;
  }
	var lastDayOfMonth = new Date(year, month, 0); //year as it is, month as next one as JS Date works on 0 - 11 months (with rollover of Dec to Jan). 0 day rolls date back to end of previous month
    daysInMnth = lastDayOfMonth.getDate(); //gets the date (day of month) from the Date object
  if (daysInMnth < dayOfMnth) { //dayOfMonth set too high for current month NOT SURE IF THIS IS STILL NEEDED AS DAYS OF MONTH UPPER VALUE IS AUTOMATICALLY SET IN CALANDER FUNCTION
	dayOfMnth = daysInMnth; //force dayOfMnth down to lower legal value
	for (i = 0; i <= 30; i++) { //hide buttons for daysOfMnth above the daysInMnth (this works straightforwardly with 0 - 30 index but 1 - 31 dayOfMnth - i.e. didn't need to add 1 to  daysInMnth)
	  document.getElementById(dayOfMnthUniqueId+i).className = offClass; //set all daysOfMonth to off (unselected) class
	}
	var daysInMonthMinusOne = daysInMnth - 1;
	document.getElementById(dayOfMnthUniqueId+daysInMonthMinusOne).className = onClass; //set dayOfMonth that is equal to the last day in month to on (selected) class
	document.getElementById(dayOfMnthTxtBoxId).value = daysInMnth; //set hidden text box to value of last day in month (e.g. 28)
	document.getElementById(outerDivId).className = outerDivClassWarning;
  }
  for (i = 0; i <= 30; i++) { //as a preliminary display all daysOfMnth buttons
	document.getElementById(dayOfMnthUniqueId+i).style.display = 'inline';
  }
  for (i = daysInMnth; i <= 30; i++) { //hide buttons for daysOfMnth above the daysInMnth (this works straightforwardly with 0 - 30 index but 1 - 31 dayOfMnth - i.e. didn't need to add 1 to  daysInMnth)
	document.getElementById(dayOfMnthUniqueId+i).style.display = 'none';
  }
  var wholeDate = year+"-"+month+"-"+dayOfMnth;
	document.getElementById(wholeDateTxtBoxId).value = wholeDate;
}


/*   */
function checkForTextInAry(textListAry, text, id, normalClass, highlightClass, submitButId, name) {
  //window.alert(text);
  //document.getElementById(id).value = removeExtraSpaces(sanitiseText(text));

  var indexMax = textListAry.length -1;
  document.getElementById(id+"lbl").style.display = 'none';
  document.getElementById(id).className = normalClass;
  document.getElementById(id).name = name;
  document.getElementById(submitButId).style.display = 'inline';
  document.getElementById(submitButId).type = 'submit';
  for (i = 0; i <= indexMax; i++) {
    if (text.toLowerCase() == textListAry[i].toLowerCase()) {
    	document.getElementById(id+"lbl").style.display = 'inline';
    	document.getElementById(id).className = highlightClass;
      document.getElementById(id).name = '';
      document.getElementById(submitButId).style.display = 'none';
      document.getElementById(submitButId).type = '';
      	//window.alert("Test!");
    }
  }
}

//COPIED FROM https://davidwalsh.name/javascript-debounce-function
// Returns a function, that, as long as it continues to be invoked, will not
// be triggered. The function will be called after it stops being called for
// N milliseconds. If `immediate` is passed, trigger the function on the
// leading edge, instead of the trailing.
function debounce(func, wait, immediate) {
  var timeout;
  return function() {
    var context = this, args = arguments;
    var later = function() {
      timeout = null;
      if (!immediate) func.apply(context, args);
    };
    var callNow = immediate && !timeout;
    clearTimeout(timeout);
    timeout = setTimeout(later, wait);
    if (callNow) func.apply(context, args);
  };
};


/* Wee test function to check that this javascript file is baing accessed ok */
function alertBox() {
    alert("I am in scriptsForJobsys.js!");
}


/* Validates three text fields returning true if they all contain data, false if any or all are empty. An alert message displays a title string followed by a combination of three strings (passed as arguments) depending on which field combination is empty. The empty field(s) are also changed by adding a colour class to highlight them. */
function validateThreeFields(colorClass, textFieldID1, textFieldID2, textFieldID3, baseMessage, emptyMessage1, emptyMessage2, emptyMessage3) {
  var message = " "+baseMessage;
  var dataFieldsStatus = true;
  if (document.getElementById(textFieldID1).value == "") {
    document.getElementById(textFieldID1).className = colorClass;
    message = message+" "+emptyMessage1;
    dataFieldsStatus = false;
  }


  if (document.getElementById(textFieldID2).value == "") {
    document.getElementById(textFieldID2).className = colorClass;
    if ((document.getElementById(textFieldID1).value == "") && (document.getElementById(textFieldID3).value == "")) { //if there is a preceding and following message insert ', '.
      message = message+", "+emptyMessage2;
    }
    else if (document.getElementById(textFieldID1).value == "") { //if there is just a preceding message insert ' and '.
      message = message+" and "+emptyMessage2;
    }
    else { //if this is the only message just insert it without an ' and ' or ', '
      message = message+" "+emptyMessage2;
    }
    dataFieldsStatus = false;
  }


  if (document.getElementById(textFieldID3).value == "") {
    document.getElementById(textFieldID3).className = colorClass;
    if ((document.getElementById(textFieldID1).value == "") || (document.getElementById(textFieldID2).value == "")) { //if there are preceding messages insert ' and '.
      message = message+" and "+emptyMessage3;
    }
    else {
      message = message+" "+emptyMessage3;
    }
    dataFieldsStatus = false;
  }
  if (dataFieldsStatus == false) {
    alert(message);
  }
  return dataFieldsStatus; //if return is false the line onsubmit="return validateThreeFields()" in the form heading prevents the form from submitting. If true the form submits.
}



/* Initially all displays referenced by displayID0 - displayID4 are set to 'none' (invisible).
   Then working from displayID0 - displayID4 if the csv? character pointed to by 'value' (used as an index) = 1 the  displayID? is set to 'inline' (visible).
   If in the html code the same identifier is used for an associated label, but with the suffix 'lbl' concatonated onto it, then the label will also be made visible/invisible accordingly.
   A test is done to see if the label exists so a null label reference doesn't cause a function exit at that point and prevent the rest of it executing.
   If a string argument that is an identifier for an HTML tag ID is empty any changes to that tag visibility will not be implemented. (this means the function can be used with fewer parameters if desired).
*/
function showAccordingToFamily(value, csv0, displayID0, csv1, displayID1, csv2, displayID2, csv3, displayID3, csv4, displayID4) 
{ 
  value = value - 1; //subtract 1 from value to make it work with 0-n array indexing
  var array0 = csv0.split(","); //convert csv string into array
  var array1 = csv1.split(",");
  var array2 = csv2.split(",");
  var array3 = csv3.split(",");
  var array4 = csv4.split(",");
  if (displayID0.length > 0) { //check for existance of this reference and set to invisible
    document.getElementById(displayID0).style.display       = 'none';
    if (document.getElementById(displayID0+"lbl") != undefined) {
      document.getElementById(displayID0+"lbl").style.display = 'none';
    }
  }
  if (displayID1.length > 0) {
    document.getElementById(displayID1).style.display       = 'none';
    if (document.getElementById(displayID1+"lbl") != undefined) {
      document.getElementById(displayID1+"lbl").style.display = 'none';
    }
  }
  if (displayID2.length > 0) {
    document.getElementById(displayID2).style.display       = 'none';
    if (document.getElementById(displayID2+"lbl") != undefined) {
      document.getElementById(displayID2+"lbl").style.display = 'none';
    }
  }
  if (displayID3.length > 0) {
    document.getElementById(displayID3).style.display       = 'none';
    if (document.getElementById(displayID3+"lbl") != undefined) {
      document.getElementById(displayID3+"lbl").style.display = 'none';
    }
  }
  if (displayID4.length > 0) {
    document.getElementById(displayID4).style.display       = 'none';
    if (document.getElementById(displayID4+"lbl") != undefined) {
      document.getElementById(displayID4+"lbl").style.display = 'none';
    }
  }
  if (displayID0.length > 0) {
    if(array0[value] == 1) {                   //set to visible if array contains 1 at index pointed to by value
      document.getElementById(displayID0).style.display       = 'inline';
      if (document.getElementById(displayID0+"lbl") != undefined) {
        document.getElementById(displayID0+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID1.length > 0) {
    if(array1[value] == 1) {
      document.getElementById(displayID1).style.display       = 'inline';
      if (document.getElementById(displayID1+"lbl") != undefined) {
        document.getElementById(displayID1+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID2.length > 0) {
    if(array2[value] == 1) {
      document.getElementById(displayID2).style.display       = 'inline';
      if (document.getElementById(displayID2+"lbl") != undefined) {
        document.getElementById(displayID2+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID3.length > 0) {
    if(array3[value] == 1) {
      document.getElementById(displayID3).style.display       = 'inline';
      if (document.getElementById(displayID3+"lbl") != undefined) {
        document.getElementById(displayID3+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID4.length > 0) {
    if(array4[value] == 1) {
      document.getElementById(displayID4).style.display       = 'inline';
      if (document.getElementById(displayID4+"lbl") != undefined) {
        document.getElementById(displayID4+"lbl").style.display = 'inline';
      }
    }
  }
}


function calcTotalCost(QuantityVal, unitCostElement, totCostID, sumOfTotalsID, moreOrderLinesID, index, partIndex, maxIndex) {
  unitCostVal = unitCostElement.value.toString(); //convert number to string so it can be used by the string replace method.
  unitCostVal = Number(unitCostVal.replace(/[^0-9.]/g,'')); //use string replace with reg expression to replace anything but 0-9 and '.' with '' (nothing). Removes unwanted £ symbols etc. Convert back to Number
  unitCostVal = unitCostVal.toFixed(4); //set to 4 decimal places (0.01p) to cope with surface mount components etc. that have fractional penny values.
  if (unitCostVal.toString().charAt(unitCostVal.toString().length-1) == "0") { //these two if statements remove trailing zeros beyond 2 decimal places but allow up to 4 decimal places for none zero decimals.
    unitCostVal = Number(unitCostVal).toFixed(3);
  }
  if (unitCostVal.toString().charAt(unitCostVal.toString().length-1) == "0") {
    unitCostVal = Number(unitCostVal).toFixed(2);
  }
  if (unitCostVal == 0) {
    unitCostVal = ""; //set to "" so 0.0000 isn't seen if there is no value set
  } 
  unitCostElement.value = unitCostVal; //replace unit cost value on form with cleaned value (£s etc removed).
  var totalCost = QuantityVal * unitCostVal;
  totalCost = totalCost.toFixed(4);
  if (totalCost.toString().charAt(totalCost.toString().length-1) == "0") { //these two if statements remove trailing zeros beyond 2 decimal places but allow up to 4 decimal places for none zero decimals.
    totalCost = Number(totalCost).toFixed(3);
  }
  if (totalCost.toString().charAt(totalCost.toString().length-1) == "0") {
    totalCost = Number(totalCost).toFixed(2);
  }
  if (totalCost == 0) {
    totalCost = ""; //set to "" so 0.0000 isn't seen if there is no value set
  }
  document.getElementById(totCostID+index).value=totalCost;
  var sumOfTotals = Number(0);
  for(i=1; i<=maxIndex; i++) { //cumulatively accrues total values from all order lines to be able to give grand total - sumOfTotals
    sumOfTotals = sumOfTotals + Number(document.getElementById(totCostID+i).value);
  }
  sumOfTotals = sumOfTotals.toFixed(4);
  if (sumOfTotals.toString().charAt(sumOfTotals.toString().length-1) == "0") { //these two if statements remove trailing zeros beyond 2 decimal places but allow up to 4 decimal places for none zero decimals.
    sumOfTotals = Number(sumOfTotals).toFixed(3);
  }
  if (sumOfTotals.toString().charAt(sumOfTotals.toString().length-1) == "0") {
    sumOfTotals = Number(sumOfTotals).toFixed(2);
  }
  if (sumOfTotals == 0) {
    sumOfTotals = ""; //set to "" so 0.0000 isn't seen if there is no value set
  }
  document.getElementById(sumOfTotalsID).value=sumOfTotals;
  if (index == partIndex) { //if index in use shows that the bottom of the current displayed order lines has been reached display the rest of the order lines to enable more items to be entered.
    document.getElementById(moreOrderLinesID).style.display = 'inline';
  }
} 



/* Takes a customer email address and calls php script getCustDetails.php. If the customer email address exists in an active record of the table 'customers' the php script returns all the data for that customer in a '~' delimitted string which is converted to an array and then indexed one item at a time to update the relevant fields on the calling form. Once all the field values have been updated fields IDcustName and IDfamilySelection and their labels are made visible and the onchange function for IDfamilySelection is run which will make visible any other fields as dictated by the final sselection value of IDfamilySelection. */
function populateDetails(custEmailval, IDcustName, IDfamilySelection, IDfamilyDescrpt, IDteamNameOrNum, IDsupervisor) {
  var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
  var xmlhttpName;
  if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttpName=new XMLHttpRequest();
  }
  else
  {// code for IE6, IE5
  xmlhttpName=new ActiveXObject("Microsoft.XMLHTTP");
  }
  xmlhttpName.onreadystatechange=function()
  {
  if (xmlhttpName.readyState==4 && xmlhttpName.status==200)
    {
    var custDetailsArray = xmlhttpName.responseText.split("~");
    document.getElementById(IDcustName).value=custDetailsArray[0];
    
    if (custDetailsArray[1] == "") {
      document.getElementById(IDfamilySelection).value=1;
    }
    else {
      document.getElementById(IDfamilySelection).value=custDetailsArray[1];
    }
    document.getElementById(IDfamilyDescrpt).value=custDetailsArray[2];
    document.getElementById(IDteamNameOrNum).value=custDetailsArray[3];
    document.getElementById(IDsupervisor).value=custDetailsArray[4];
    document.getElementById(IDcustName).style.display = 'inline';
    document.getElementById(IDfamilySelection).style.display = 'inline';
    document.getElementById(IDcustName+"lbl").style.display = 'inline';
    document.getElementById(IDfamilySelection+"lbl").style.display = 'inline';
    document.getElementById(IDfamilySelection).onchange(); //automatically runs onchange function of element with ID IDofOnchangeToBeRun.
    }
  }
  xmlhttpName.open("GET", "./php/getCustDetails.php?custEmail="+custEmailval+"&random="+random, true);
  xmlhttpName.send();
  //alert("At end of populateDetails() function!"+" passed argument was - "+custEmail);
}


/* Validates two text fields returning true if they both contain data, false if either or both are empty. An alert message displays one of three strings (passed as arguments) depending on which field combination is empty. The empty field(s) are also changed by adding a colour class to it to highlight them. */
function validateTwoFields(alertClass, textFieldID1, textFieldID2, emptyMessage1, emptyMessage2, emptyMessageBoth) {
  if ((document.getElementById(textFieldID1).value.search(/^[^.][^@\s]+@[^@\s]+\.[^‌​@\s]+[^.]$/) == -1) && (document.getElementById(textFieldID2).value == "")) {
    document.getElementById(textFieldID1).className = alertClass;
    document.getElementById(textFieldID2).className = alertClass;
    alert(emptyMessageBoth);
    return false;
  }
  /*else if (document.getElementById(textFieldID1).value == "") {
    document.getElementById(textFieldID1).className = alertClass;
    alert(emptyMessage1);
    return false;*/

  else if (document.getElementById(textFieldID1).value.search(/^[^.][^@\s]+@[^@\s]+\.[^‌​@\s]+[^.]$/) == -1) { //only one @, at least one character before the @, before the period and after it, and no white spaces.
    document.getElementById(textFieldID1).className = alertClass;
    alert(emptyMessage1);
    return false;
  }
  else if (document.getElementById(textFieldID2).value == "") {
    document.getElementById(textFieldID2).className = alertClass;
    alert(emptyMessage2);
    return false;
  }
  else {
    return true; //if return is false the line onsubmit="return validateNameAndSuperisor()" in the form heading prevents the form from submitting. If true the form submits.
  }
}


/* Test Javascript function that calls a PHP testFromJavascript.php function and feeds back from all stages to the calling webpage (which is called testJavascriptCallingPHP.php).  */
function testFunc(value1, value2) 
{
document.getElementById("first").innerHTML = "Entered Javascript function: testFunc(value1, value2)"; //sends text to first reporting area on web page
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (value1=="")
  {
  document.getElementById("second").innerHTML="empty"; //sends text to second reporting area on web page
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("third").innerHTML=xmlhttp.responseText; //sends text coming back from testFromJavascript.php to third reporting area on web page
    }
  }
xmlhttp.open("GET", "./php/testFromJavascript.php?value1="+value1+"&value2="+value2+"&random="+random, true); //calls php script passing arguments as part of the calling url ($_GET)
xmlhttp.send();
}


function testWithAlert(a, b) {
	window.alert("Test with alert!");
	window.alert(a);
	window.alert(b);
}

function testWithSingleAlert() {
  window.alert("In testWithSingleAlert !");
}


function makeExpandingArea(container) {
  var area = container.querySelector('textarea');
  var span = container.querySelector('span');
  if (area.addEventListener) {
    area.addEventListener('input', function() {
    span.textContent = area.value;
  }, false);
  span.textContent = area.value;
  }
  else if (area.attachEvent) {
    // IE8 compatibility
    area.attachEvent('onpropertychange', function() {
      span.innerText = area.value;
    });
    span.innerText = area.value;
  }
  // Enable extra CSS
  container.className += ' active';
}

/* In tableName this function updates the field fieldName with value in key. This is only in the row(s) where whereField contains whereValue (that is pointed to by whereValueId). If a matching whereField is not found (a matching record doesn't exist) nothing happens. Uses xmlhttp.open to call updateTableFromJS.php which is in path pathToPhpFile. The text of button with id stored in butIdHolderId text element is also updated. */
function updateRecordsAndButton(tableName, fieldNameId, key, itemStr, whereField, whereValueId, butIdHolderId, pathToPhpFile, fileRndm) {
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
var fieldName = document.getElementById(fieldNameId).value;
var whereValue = document.getElementById(whereValueId).value;
var butId = document.getElementById(butIdHolderId).value;
document.getElementById(butId).value = key;
document.getElementById(butId).innerText = itemStr;
/* //start of test section
xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
{
if (xmlhttp.readyState==4 && xmlhttp.status==200)
  {
    alert(xmlhttp.responseText);
  //document.getElementById("qwerty").innerHTML=xmlhttp.responseText;

  }
} 
*/  //end of test section
xmlhttp.open("POST", pathToPhpFile, true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("command="+fileRndm+"&tableName="+tableName+"&fieldName="+fieldName+"&value="+key+"&whereField="+whereField+"&whereValue="+whereValue+"&random="+random);
}


/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
/* In tableName this function updates the field fieldName with value in the element pointed to by valueId. This is only in the row(s) where whereField contains whereValue (that is pointed to by whereValueId). If a matching whereField is not found (a matching record doesn't exist) nothing happens. Uses xmlhttp.open to call updateTableFromJS.php which is in path pathToPhpFile. The text of button with id stored in butIdHolderId text element is also updated. */
function updateRecordsDateAndButton(tableName, fieldName, valueId, whereField, whereValueId, butIdHolderId, pathToPhpFile, fileRndm) {
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
/* ############################################ DON'T EDIT THIS ONE !!!!! #############################################  */
var value = document.getElementById(valueId).value; //get date in 2018-08-23 format
var whereValue = document.getElementById(whereValueId).value; //get the value that is used to find a match in the whereField column to determine the row where the date will be changed
//Takes date string in format "2008-07-23" and returns in the format 23 Aug 2018
var mnthNameAry = ["dummyForElement-0", "Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
var mnthName = mnthNameAry[parseInt(value.substring(5, 7))];
var dateWithMnthName = value.substring(8)+" "+mnthName+" "+value.substring(0, 4);
var butId = document.getElementById(butIdHolderId).value;
document.getElementById(butId).value = value; //store the date in 2018-08-23 format in the value attribute of the button so it can be retrieved to set the calendar when the row is selected
document.getElementById(butId).innerText = dateWithMnthName; //display the date as the button text in 23 Aug 2018 format
xmlhttp.open("POST", pathToPhpFile, true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("command="+fileRndm+"&tableName="+tableName+"&fieldName="+fieldName+"&value="+value+"&whereField="+whereField+"&whereValue="+whereValue+"&random="+random);
}



/* valueId points to an element containing a date string in the format 2018-10-23 that is used to update the table at the row pointed to by the part before the '-' in the cellId held in cellIdHolderId. The field that is updated is decided by the part after the '-' by using it as an index in an array of table field names. This is all done in the php script called by this function using pathToPhpFile and fileRndm. The php script inserts the data into the table and then reads it back out and echoes it back to this function. While this function is waiting for the returned data it sets the cell pointed to by cellIdHolderId to a warning class (usually orange background) and only clears it to normal class and updates it once the data is received back. This provides comfirmation that the table has been updated! The data from the table is cleaned to leave only 0-9 and '-'' and it is split and reordered to display the date in 23-10-2018 format.*/
function ajaxRecordsDateAndCellUpdate(value, cellId, pathToPhpFile, fileRndm, cellWarnClass) {
	var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
	var sessionCommitRnd = ""; //legacy item - no longer used but needs to be removed throughout system
	var xmlhttp;    
	if (window.XMLHttpRequest)
	  {// code for IE7+, Firefox, Chrome, Opera, Safari
	  xmlhttp=new XMLHttpRequest();
	  }
	else
	  {// code for IE6, IE5
	  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	  }
	//var value = document.getElementById(valueId).value; //get date in 2018-08-23 format
	var origClass = document.getElementById(cellId).className; //save original class for re-enstatement later
	document.getElementById(cellId).className = cellWarnClass; //set the cell class to warning until it has been properly updated with data back from the table
	xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
	    {
	    if (xmlhttp.readyState==4 && xmlhttp.status==200)
	      {
	        dateFromTable = xmlhttp.responseText;
	        cleanedDateFromTable = dateFromTable.replace(/[^0-9-]/g,''); //use string replace with reg expression to replace anything but 0-9 and '-' with '' (nothing). Removes unwanted spaces/symbols etc. 
	        if (cleanedDateFromTable == value) { //check that the returned data matches that sent before removing the warning class from the display cell
	          document.getElementById(cellId).className = origClass;
	        }
	        dateAry = cleanedDateFromTable.split('-');
	        document.getElementById(cellId).innerText = dateAry[2]+'-'+dateAry[1]+'-'+dateAry[0]; //reverse year/month/dayOfMonth
	      }
	    } 
	xmlhttp.open("POST", pathToPhpFile, true);
	xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	xmlhttp.send("command="+fileRndm+"&value="+value+"&cellId="+cellId+"&sessionCommitRnd="+sessionCommitRnd+"&random="+random);
}


/* value is a string that is used to update the table at the row pointed to by the part before the '-' in the cellId held in cellIdHolderId. The field that is updated is decided by the part after the '-' by using it as an index in an array of table field names. This is all done in the php script called by this function using pathToPhpFile and fileRndm. The php script inserts the data into the table and then reads it back out and echoes it back to this function. Before the call to the php sript on the server the cell pointed to by cellIdHolderId is set to a warning class (usually orange background) and only cleared back to normal class and its contents updated once the data is received back. This provides comfirmation that the table has been updated! The data from the table is cleaned to leave only 0-9 and '-'' .*/
function ajaxRecordsItemAndCellUpdate(
	cellId,
	value,
	pathToPhpFile,
	fileRndm,
	cellWarnClass
	) {
	//IN THE EQUIVIALENT atomicAjaxCall() FUNCTION THE NEXT LINE TESTS FOR A STICKY VALUE SET BEFORE UPDATING THE SERVER - THIS MEANS IT WOULDN'T WORK WITH "" FOR RESETTING TO DEFAULT BLANK VALUES!
	//if (0 < value.length) { //check to make sure a sticky value for this column contains a string value, indicating it has been set and the function should be run
		var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
		var xmlhttp;
		if (window.XMLHttpRequest)
		  {// code for IE7+, Firefox, Chrome, Opera, Safari
		  xmlhttp=new XMLHttpRequest();
		  }
		else
		  {// code for IE6, IE5
		  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
		  }
		var origClass = document.getElementById(cellId).className; //save original class for re-enstatement later
		document.getElementById(cellId).className = cellWarnClass; //set the cell class to warning until it has been properly updated with data back from the table 
		xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
		    {
		    if (xmlhttp.readyState==4 && xmlhttp.status==200)
		      {
		        itemStrFromTable = xmlhttp.responseText.split('#')[0];
		        itemStrFromTableTrimmed = itemStrFromTable.trim(); //removes unwanted white space at start/end of returned string 
		        if (itemStrFromTableTrimmed == value) { //check that the returned data matches that sent before removing the warning class from the display cell
		          document.getElementById(cellId).className = origClass;
		        }
		        document.getElementById(cellId).innerText = itemStrFromTableTrimmed; 
		      }
		    }
		xmlhttp.open("POST", pathToPhpFile, true);
		xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
		xmlhttp.send("command="+fileRndm+"&value="+value+"&cellId="+cellId+"&random="+random);
	//}
}



/* In tableName this function updates the field fieldName with value. This is only in the row(s) where whereField contains whereValue. If a matching whereField is not found (a matching record doesn't exist) nothing happens. Uses xmlhttp.open to call updateTableFromJS.php which is in path pathToPhpFile. */
function updateTable(tableName, fieldName, value, whereField, whereValue, pathToPhpFile, fileRndm) {
      //alert("In updateTable! "+tableName+" "+fieldName+" "+value+" "+whereField+" "+whereValue+" "+pathToPhpFile+" "+fileRndm);
    var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
    var xmlhttp;    
    if (window.XMLHttpRequest)
      {// code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp=new XMLHttpRequest();
      }
    else
      {// code for IE6, IE5
      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
     //start of test section
    xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
    {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
      {
        //alert(xmlhttp.responseText);
      //document.getElementById("qwerty").innerHTML=xmlhttp.responseText;

      }
    } 
      //end of test section 
    xmlhttp.open("POST", pathToPhpFile, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("command="+fileRndm+"&tableName="+tableName+"&fieldName1="+fieldName+"&value1="+value+"&whereField="+whereField+"&whereValue="+whereValue+"&random="+random);
}

/* In tableName this function updates the fields fieldName1 and fieldName2 with value1 and value2. This is only in the row(s) where whereField contains whereValue. If a matching whereField is not found (a matching record doesn't exist) nothing happens. Uses xmlhttp.open to call updateTableFromJS.php which is in path pathToPhpFile. */
function update2Table(tableName, fieldName1, value1, fieldName2, value2, whereField, whereValue, pathToPhpFile, fileRndm) {
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
/* //start of test section
xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
{
if (xmlhttp.readyState==4 && xmlhttp.status==200)
  {
    alert(xmlhttp.responseText);
  //document.getElementById("qwerty").innerHTML=xmlhttp.responseText;

  }
} 
*/  //end of test section
xmlhttp.open("POST", pathToPhpFile, true);
xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
xmlhttp.send("command="+fileRndm+"&tableName="+tableName+"&fieldName1="+fieldName1+"&fieldName2="+fieldName2+"&value1="+value1+"&value2="+value2+"&whereField="+whereField+"&whereValue="+whereValue+"&random="+random);
}


/* Updates the row pointed to by $rowID and field pointed to by $fieldName in the table pointed to by $tableName (these are combined in a CSV string, tableRowColIdCsv) with value. If a matching field is not found (a record doesn't exist) a new record is created with the match value and value. (this version accesses ./php/updateTable.php from a subdirectory - like ./tables - by putting an extra '.'' on  thus - ../php/updateTable.php) */
function updateTableForSubDirUse(value, tableRowColMatchCsv) 
{
//alert("In updateTable javascript function!");
//document.getElementById("qwerty").innerHTML = "Jan 24th Entered updateTable() javascript!"; //needs: <p id="qwerty">Reporting Area</p> in the calling page to be able to work!
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
/* xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("qwerty").innerHTML=xmlhttp.responseText;

    }
  } */ //end of test
xmlhttp.open("GET", "../php/updateTable.php?value="+value+"&tableRowColMatchCsv="+tableRowColMatchCsv+"&random="+random, true);
xmlhttp.send();
}

/* Updates the row pointed to by $rowID and field pointed to by $fieldName in the table pointed to by $tableName (these are combined in a CSV string, tableRowColIdCsv) with value. If a matching field is not found (a record doesn't exist) a new record is created with the match value and value.  */
function updateTableViaPhp(value, tableRowColMatchCsv) 
{
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.open("GET", "./php/updateTable.php?value="+value+"&tableRowColMatchCsv="+tableRowColMatchCsv+"&random="+random, true);
xmlhttp.send();
}


/* sets the class for the selected row (indicated by rowIdx) to classSel and classes for all other rows to classNorm. All elements in each row are set using a loop and maxColIdx. The class of the button idButEdit is set to classEdit to highlight its edit function. Also sets the value of the elements valHolderForSelRowIdxId to rowIdx and valHolderForSelRecIdRId to recordIdR.  */
function selectRow(maxRowIdx, maxColIdx, rowIdx, recordIdR, valHolderForSelRowIdxId, valHolderForSelRecIdRId, classNorm, classSel, classEdit, idButEdit) {
    for(rowI = 0; rowI <= maxRowIdx; rowI++) {
        for(i = 0; i <= maxColIdx; i++) {
            id = rowI+"but"+i;
            document.getElementById(id).className = classNorm;
        }
    }
    for(i = 0; i <= maxColIdx; i++) {
        id = rowIdx+"but"+i;
        document.getElementById(id).className = classSel;
    }
    document.getElementById(idButEdit).className = classEdit;
    document.getElementById(valHolderForSelRowIdxId).value = rowIdx;
    document.getElementById(valHolderForSelRecIdRId).value = recordIdR;
}



/* toggle class of item pointed to by ID. Also sets an item whose id is ID+"textbx" (normally a text box!) to "" or "1" depending on the toggle state. */
function setClass(ID, onClass, offClass, allowDeselect) {
    currClass = document.getElementById(ID).className;
    if (currClass == onClass) {
        if (allowDeselect == 'true') { //inexplicably allowselect will not work on its own here, even though it is cast by json_encode() to a javascript boolean. And true has to be in '' for some reason!
            document.getElementById(ID).className = offClass;
            document.getElementById(ID+"textBx").value = "";
        }
    }
    if (currClass == offClass) {
        document.getElementById(ID).className = onClass;
        document.getElementById(ID+"textBx").value = 1;
    }
}

/* sets class of item pointed to by ID created from randId+IdfromValueOffset to onClass and all other randId+IDs to offClass. IdfromValueOffset is used in place of an ID passed as an argument, this is done by calculating it from the offset from the value. Also sets value of an element whose id is randId+"textbx" (normally a text box!) to value. */
function setClassAndCopyNameUnique(randId, valueOffset, maxId, onClass, offClass, value) {
    for (i = 0; i <= maxId; i++) { //set button classes to offClass
        document.getElementById(randId+i).className = offClass;
    }
    IdfromValueOffset = value - valueOffset;
    document.getElementById(randId+IdfromValueOffset).className = onClass;
    document.getElementById(randId+"textBx").value = value;
}


/* sets class of item pointed to by ID+randId to onClass and all other IDs+randId to offClass. Also sets name of an element whose id is ID+randId+"textbx" (normally a text box!) to butName and names of all other IDs+randId+"textbox" to "". */
function setClassAndNameUnique(randId, ID, maxId, onClass, offClass, butName) {
    for (i = 0; i <= maxId; i++) { //clear all names to "" and button classes to offClass
        document.getElementById(i+randId).className = offClass;
        document.getElementById(i+randId+"textBx").name = "";
    }
    document.getElementById(ID+randId).className = onClass;
    document.getElementById(ID+randId+"textBx").name = butName;
}

/* sets class of item pointed to by ID+randId to onClass and all other IDs+randId to offClass. Also sets value of an element whose id is ID+randId+"textbx" (normally a text box!) to 1 and the value of all other IDs+randId+"textbox" to "". */
function setClassAndValueUnique(randId, ID, maxId, onClass, offClass) {
    for (i = 0; i <= maxId; i++) { //clear all values to "" and button classes to offClass
        document.getElementById(randId+i).className = offClass;
        document.getElementById(randId+i+"textBx").value = "";
    }
    document.getElementById(randId+ID).className = onClass;
    document.getElementById(randId+ID+"textBx").value = 1;
}


/* toggle class of item pointed to by ID and toggles the visibility of two items pointed to by IdVis1 and IdVis2. When the class toggles to 'onClass' IdVis1 will be set to invisible, IdVis2 to visible and the textbox IdYesNoTxt references will be set to 'Yes'. When the class toggles to offClass IdVis1 will be set to visible, IdVis2 to invisible and the textbox IdYesNoTxt references will be set to 'No'. IdVis1 and IdVis2 can have suffixes added, IdVis1Sufx, and IdVis2Sufx and the suffixed ids will also be toggled. */
function setClassAnd2Visibilities(ID, onClass, offClass, IdVis1, IdVis1Sufx, IdVis2, IdVis2Sufx, IdYesNoTxt) {
    currClass = document.getElementById(ID).className;
    if (currClass == offClass) {
        document.getElementById(ID).className = onClass;
        document.getElementById(IdVis1).style.display = 'none';
        document.getElementById(IdVis2).style.display = 'inline';
        document.getElementById(IdVis1+IdVis1Sufx).style.display = 'none';
        document.getElementById(IdVis2+IdVis2Sufx).style.display = 'inline';
        document.getElementById(IdYesNoTxt).value = 'Yes';
    }
    if (currClass == onClass) {
        document.getElementById(ID).className = offClass;
        document.getElementById(IdVis1).style.display = 'inline';
        document.getElementById(IdVis2).style.display = 'none';
        document.getElementById(IdVis1+IdVis1Sufx).style.display = 'inline';
        document.getElementById(IdVis2+IdVis2Sufx).style.display = 'none';
        document.getElementById(IdYesNoTxt).value = 'No';
    }
}

/* Seats the classes of all the elements identified by the ids passed in the csv string (can be just one id i.e. 'but0', or several 'but0,but1,but2') to classReq. */
function setClassesFromCsv(csvIds, classReq) {
    idsAry = csvIds.split(',');
    maxI = idsAry.length - 1;
    for (i = 0; i <= maxI; i++)
    document.getElementById(idsAry[i]).className = classReq;
}


/* Sets class of item pointed to by itemKeySelected (uses baseIdRand for disambiguation) to onClass and all other classes (designated by baseIdRand along with each id from itemKeysCsv) to offClass. */
function setOneClassUnsetRest(baseIdRand, onClass, offClass, itemKeysCsv, itemKeySelected) {
    if ((2 < itemKeysCsv.length) && (0 < itemKeySelected)) { //this function body is only allowed to run if there is at least "1,2" in the itemKeysCsv and itemKeySelected contains an actual value other than 0. This is because it (probably the split or loop) misbehaves and doesn't complete. The consequence of this is that any buttons don't have their classes changed unless there are at least 3 of them. This could be rewritten to fix.
      var itemKeysAry = itemKeysCsv.split(',');
      var idxSelected = itemKeysAry.indexOf(itemKeySelected.toString());
      maxId = itemKeysAry.length - 1;
      for (i = 0; i <= maxId; i++) { //clear all values to "" and button classes to offClass
          document.getElementById(baseIdRand+i).className = offClass;
      }
      document.getElementById(baseIdRand+idxSelected).className = onClass;
    }
}

/* If the class of the button (pointed to by id) that has been clicked is already editClass, the div pointed to by copyDivId is made visible and the button (contained within the div) pointed to by copyButId has its class set to copyButSelClass, and its value and inner Text set to the the same as the clicked button, and its name changed to 'set'. Additionally the value of the copy button is sent by POST to a php script that writes it to a row in a table to make it sticky. */
function setAndMakeStickyCopyBut(id, editClass, copyDivId, copyButId, copyButSelClass, fieldRndm, indexPagePath, phpFileRndm) {
    if (document.getElementById(id).className == editClass) {
        document.getElementById(copyDivId).style.display = 'inline';
        document.getElementById(copyButId).innerText = document.getElementById(id).innerText;
        document.getElementById(copyButId).value = document.getElementById(id).value;
        document.getElementById(copyButId).name = 'set';
        document.getElementById(copyButId).className = copyButSelClass;

        var value = document.getElementById(id).value;
        var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
        var xmlhttp;    
        if (window.XMLHttpRequest)
          {// code for IE7+, Firefox, Chrome, Opera, Safari
          xmlhttp=new XMLHttpRequest();
          }
        else
          {// code for IE6, IE5
          xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
          }
      /*   //start of test section
        xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
        {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
          {
            alert(xmlhttp.responseText);
          }
        } 
        //end of test section */
        xmlhttp.open("POST", indexPagePath, true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("command="+phpFileRndm+"&fieldRndm="+fieldRndm+"&value="+value+"&random="+random);
    }
}


/* Sends POST data to the php file identified by phpFileRndm (accessed via the main index page - indexPagePath). The php file calls a function that selects data from a table to show the difference between withdrawn totals and paidin totals which is passed back to this function as a balance, formatted to 2 decimal places (also removing any unwanted characters) and written to the innerText pointed to by butId.  */
function setButToBalDEPRECATED(whereFieldRndm, whereValue, familyId, butId, indexPagePath, phpFileRndm) {
        var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
        var xmlhttp;    
        if (window.XMLHttpRequest)
          {// code for IE7+, Firefox, Chrome, Opera, Safari
          xmlhttp=new XMLHttpRequest();
          }
        else
          {// code for IE6, IE5
          xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
          }
         //start of test section
        xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
        {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
          {
          	  unitCostVal = xmlhttp.responseText;
          	  unitCostVal = Number(unitCostVal.replace(/[^0-9.-]/g,'')); //use string replace with reg expression to replace anything but 0-9 and '.' with '' (nothing). Removes unwanted £ symbols etc. Convert back to Number
			  unitCostVal = unitCostVal.toFixed(4); //set to 4 decimal places (0.01p) to cope with surface mount components etc. that have fractional penny values.
			  if (unitCostVal.toString().charAt(unitCostVal.toString().length-1) == "0") { //these two if statements remove trailing zeros beyond 2 decimal places but allow up to 4 decimal places for none zero decimals.
			    unitCostVal = Number(unitCostVal).toFixed(3);
			  }
			  if (unitCostVal.toString().charAt(unitCostVal.toString().length-1) == "0") {
			    unitCostVal = Number(unitCostVal).toFixed(2);
			  }
			  if (unitCostVal == 0) {
			    unitCostVal = ""; //set to "" so 0.0000 isn't seen if there is no value set
			  } 
			  document.getElementById(butId).innerText = unitCostVal; //set value on form to cleaned value (£s etc removed).
          }
        } 
          //end of test section 
        xmlhttp.open("POST", indexPagePath, true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("command="+phpFileRndm+"&whereFieldRndm="+whereFieldRndm+"&whereValue="+whereValue+"&familyId="+familyId+"&random="+random);
}

 /* Returns the passed number formatted to 2 decimal places (so if the number is an integer value, say 7 it will be returned as a decimal with 2 trailing 0s - 7.00). Also removes unwanted characters. If showZeroValue is true 0.00 will be shown when the value is zero, if false nothing will be shown (an empty string). This function has the property of allowing up to 4 decimal places to be processed and displayed for cases where a fraction of a penny need to be used. */
function formatTo2DecPlcs(number, showZeroValue) {
    number = Number(number.replace(/[^0-9.-]/g,'')); //use string replace with reg expression to replace anything but 0-9 and '.' with '' (nothing). Removes unwanted £ symbols etc. Convert back to Number
    number = number.toFixed(4); //set to 4 decimal places (0.01p) to cope with surface mount components etc. that have fractional penny values.
    if (number.toString().charAt(number.toString().length-1) == "0") { //these two if statements remove trailing zeros beyond 2 decimal places but allow up to 4 decimal places for none zero decimals.
      number = Number(number).toFixed(3);
    }
    if (number.toString().charAt(number.toString().length-1) == "0") {
      number = Number(number).toFixed(2);
    }
    if ((number == 0) && (showZeroValue == false)) {
      number = ""; //set to "" so 0.00 isn't seen if there is no value set
    }
    return number;
}

/* Sends POST data to the php file identified by phpFileRndm (accessed via the main index page - indexPagePath). The php file calls a function that selects withdrawn and paidIn totals from a table (normally allRecords) and calculates the difference between withdrawn totals and paidin totals passing all 3 values back to this function as a csv to be formatted to 2 decimal places (also removing any unwanted characters) and written to the innerText elements pointed to by withdrawnId, paidInId and balId.  */
function ajaxGetAndDisplayBals(
	cellIdBal,
	OrdWithdrawnId,
	OrdPaidInId,
	OrdBalId,
	reconcldWithdrawnId,
	reconcldPaidInId,
	reconcldBalId,
	docOnlyWithdrawnId,
	docOnlyPaidInId,
	docOnlyBalId,
	recStartDate,
	recEndDate,
	indexPagePath,
	phpFileRndm
	) {
	//alert(cellId+" "+filterdWithdrawnId+" "+filterdPaidInId+" "+filterdBalId+" "+finYrWithdrawnId+" "+finYrPaidInId+" "+finYrBalId+" "+recStartDate+" "+recEndDate+" "+indexPagePath+" "+phpFileRndm);
    var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
    var xmlhttp;    
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function() { //only use this for test purposes to display the addressed column in the reporting area on the html page
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var valuesAry = xmlhttp.responseText.split(',');
            //alert(xmlhttp.responseText);
            document.getElementById(OrdWithdrawnId).innerText = formatTo2DecPlcs(valuesAry[0], true); //set element to cleaned withdrawn value.
            document.getElementById(OrdPaidInId).innerText = formatTo2DecPlcs(valuesAry[1], true); //set element to cleaned paidIn value.
            document.getElementById(OrdBalId).innerText = formatTo2DecPlcs(valuesAry[2], true); //set element to cleaned balance value.

            document.getElementById(reconcldWithdrawnId).innerText = formatTo2DecPlcs(valuesAry[3], true); //set element to cleaned withdrawn value.
            document.getElementById(reconcldPaidInId).innerText = formatTo2DecPlcs(valuesAry[4], true); //set element to cleaned paidIn value.
            document.getElementById(reconcldBalId).innerText = formatTo2DecPlcs(valuesAry[5], true); //set element to cleaned balance value.

            document.getElementById(docOnlyWithdrawnId).innerText = formatTo2DecPlcs(valuesAry[6], true); //set element to cleaned withdrawn value.
            document.getElementById(docOnlyPaidInId).innerText = formatTo2DecPlcs(valuesAry[7], true); //set element to cleaned paidIn value.
            document.getElementById(docOnlyBalId).innerText = formatTo2DecPlcs(valuesAry[8], true); //set element to cleaned balance value.
            //alert(valuesAry[9]);
        }
    } 
    xmlhttp.open("POST", indexPagePath, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("command="+phpFileRndm+"&cellId="+cellIdBal+"&recStartDate="+recStartDate+"&recEndDate="+recEndDate+"&random="+random);
}

/* Sends POST data to the php file identified by phpFileRndm (accessed via the main index page - indexPagePath). The php file calls a function that selects withdrawn and paidIn totals from a table (normally allRecords) and calculates the difference between withdrawn totals and paidin totals passing all 3 values back to this function as a csv to be formatted to 2 decimal places (also removing any unwanted characters) and written to the innerText elements pointed to by withdrawnId, paidInId and balId.  */
function setButToFilteredBal(whereFieldRndm, whereValue, idRsCsv, filterdWithdrawnId, filterdPaidInId, filterdBalId, finYrWithdrawnId, finYrPaidInId, finYrBalId, recStartDate, recEndDate, indexPagePath, phpFileRndm) {
    var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
    var xmlhttp;    
    if (window.XMLHttpRequest) {// code for IE7+, Firefox, Chrome, Opera, Safari
        xmlhttp=new XMLHttpRequest();
    }
    else {// code for IE6, IE5
        xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
    }
    xmlhttp.onreadystatechange=function() { //only use this for test purposes to display the addressed column in the reporting area on the html page
        if (xmlhttp.readyState==4 && xmlhttp.status==200) {
            var valuesAry = xmlhttp.responseText.split(',');
            //alert(xmlhttp.responseText);
            document.getElementById(filterdWithdrawnId).innerText = formatTo2DecPlcs(valuesAry[0], true); //set element to cleaned withdrawn value.
            document.getElementById(filterdPaidInId).innerText = formatTo2DecPlcs(valuesAry[1], true); //set element to cleaned paidIn value.
            document.getElementById(filterdBalId).innerText = formatTo2DecPlcs(valuesAry[2], true); //set element to cleaned balance value.

            document.getElementById(finYrWithdrawnId).innerText = formatTo2DecPlcs(valuesAry[3], true); //set element to cleaned withdrawn value.
            document.getElementById(finYrPaidInId).innerText = formatTo2DecPlcs(valuesAry[4], true); //set element to cleaned paidIn value.
            document.getElementById(finYrBalId).innerText = formatTo2DecPlcs(valuesAry[5], true); //set element to cleaned balance value.
        }
    } 
    xmlhttp.open("POST", indexPagePath, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("command="+phpFileRndm+"&whereFieldRndm="+whereFieldRndm+"&whereValue="+whereValue+"&idRsCsv="+idRsCsv+"&recStartDate="+recStartDate+"&recEndDate="+recEndDate+"&random="+random);
}

/* If the button pointed to by copyButId has the name 'set' its value and innerText are copied to the button pointed to by id. POST data is also sent to a php script that updates fields fieldName with value of button pointed to by copyButId where whereField contains whereValue. */
function pasteFromCopyBtn(id, copyButId, tableName, fieldName, whereField, whereValue, indexPagePath, fileRndm) {
    if (document.getElementById(copyButId).name == 'set') {
        document.getElementById(id).innerText = document.getElementById(copyButId).innerText;
        document.getElementById(id).value = document.getElementById(copyButId).value;
        var value = document.getElementById(copyButId).value;
        var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
        var xmlhttp;    
        if (window.XMLHttpRequest)
          {// code for IE7+, Firefox, Chrome, Opera, Safari
          xmlhttp=new XMLHttpRequest();
          }
        else
          {// code for IE6, IE5
          xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
          }
        /* //start of test section
        xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
        {
        if (xmlhttp.readyState==4 && xmlhttp.status==200)
          {
            alert(xmlhttp.responseText);
          //document.getElementById("qwerty").innerHTML=xmlhttp.responseText;

          }
        } 
          //end of test section */
        xmlhttp.open("POST", indexPagePath, true);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.send("command="+fileRndm+"&tableName="+tableName+"&fieldName1="+fieldName+"&value1="+value+"&whereField="+whereField+"&whereValue="+whereValue+"&random="+random);
    }
}

/*   */
function clearCopyBut(copyButId, copyButClass, fieldRndm, indexPagePath, phpFileRndm) {
    document.getElementById(copyButId).name = 'notSet';
    document.getElementById(copyButId).innerText = '';
    document.getElementById(copyButId).value = 0;
    document.getElementById(copyButId).className = copyButClass;
    var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
    var xmlhttp;    
    if (window.XMLHttpRequest)
      {// code for IE7+, Firefox, Chrome, Opera, Safari
      xmlhttp=new XMLHttpRequest();
      }
    else
      {// code for IE6, IE5
      xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
      }
  /*   //start of test section
    xmlhttp.onreadystatechange=function() //only use this for test purposes to display the addressed column in the reporting area on the html page
    {
    if (xmlhttp.readyState==4 && xmlhttp.status==200)
      {
        alert(xmlhttp.responseText);
      }
    } 
    //end of test section */
    xmlhttp.open("POST", indexPagePath, true);
    xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xmlhttp.send("command="+phpFileRndm+"&fieldRndm="+fieldRndm+"&value="+0+"&random="+random);
}


/* Seats the visibilities of all the elements identified by the ids passed in the csv string (can be just one id i.e. 'but0', or several 'but0,but1,but2') to visibility (i.e. 'none' or 'inline'. */
function setVisibilitiesFromCsv(csvIds, visibility) {
    idsAry = csvIds.split(',');
    maxI = idsAry.length - 1;
    for (i = 0; i <= maxI; i++)
    document.getElementById(idsAry[i]).style.display = visibility;
}

/* Seets the value of a placeholder element (usually a hidden textbox) pointed to by placeholderId to the passed value. */
function setPlacehldr(value, placeholderId) {
    document.getElementById(placeholderId).value = value;
}

function openPage(selection, url, payload) { //opens a url with the payload attributes ($_GET) contained in payload which is suffixed with selection (an integer).
     window.location=url+payload+selection;
}


function piechart(divsArrayCsv, hoursArrayCsv) {
    var divsArray = divsArrayCsv.split("~");
    var hoursArray = hoursArrayCsv.split("~");
    var data = [];
    for(i=0; i<divsArray.length; i++) {
        divs = String(divsArray[i]);
        hours = Number(hoursArray[i]);
	    data = data.concat([ [divs, hours] ]);
    }

    var plot1 = jQuery.jqplot ('chart1', [data], { 
        seriesDefaults: {
            // Make this a pie chart.
            renderer: jQuery.jqplot.PieRenderer, 
            rendererOptions: {
                dataLabelPositionFactor: 0.8,
                dataLabelThreshold: 0.5,
                // Put data labels on the pie slices.
                // By default, labels show the percentage of the slice.
                showDataLabels: true
            }
        }, 
        legend: { show:true, location: 'e' }
    }
    );
}


/* Prevents enter key submitting form */
function testForEnter() 
   {    
      if (event.keyCode == 13) 
      {
         event.cancelBubble = true;
         event.returnValue = false;
      }
   }

function getTitle(ID)
{
document.getElementById(ID).setAttribute('title', 'after click');;
}

/* This AJAX function takes a passed values from an onchange event in a text box or similar and clears the text box (pointed to by sourceID) then calls the 1st php function which is pointed to by phpScriptURL (passing the variable passedValue as a $_GET) and outputs the new updated hours text produced by the script in the label attached to the text box or other item pointed to by targetID. Additionally the person making the call to this script is passed to the php function as personHash. Once the 1st script is finished a 2nd script is called that calculates the new total hours and outputs the hours text to the label idd by targetIDTotalHrs.  */
function updateHours(passedValue, personHashMakingCall, sourceID, targetID, targetIDTotalHrs, jobID) 
{
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp, xmlhttp2;    
if (passedValue=="")
  {
  document.getElementById(targetID).innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  xmlhttp2=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  xmlhttp2=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById(sourceID).value="";
    document.getElementById(targetID).innerHTML=xmlhttp.responseText;
    xmlhttp2.open("GET", "./php/updateTotalTime.php?jobID="+jobID+"&random="+random, true);
    xmlhttp2.send();
    }
  }
xmlhttp2.onreadystatechange=function()
  {
  if (xmlhttp2.readyState==4 && xmlhttp2.status==200)
    {
    document.getElementById(targetIDTotalHrs).innerHTML=xmlhttp2.responseText;
    }
  }
xmlhttp.open("GET", "./php/updateTime.php?personHashEnteringData="+personHashMakingCall+"&value="+passedValue+"&jobAssnmtID="+sourceID+"&random="+random, true);
xmlhttp.send();
}


/* This function takes a passed values from an onchange event in a text box or similar and clears the text box (pointed to by sourceID) then calls the php function which is pointed to by phpScriptURL (passing the variable passedValue as a $_GET) and outputs the new updated hours text produced by the script in the label attached to the text box or other item pointed to by targetID. Additionally the person making the call to this script is passed to the php function as personHash. The title attribute (tooltip) of the targetID will be updated with the latest list of historic time records. This function is for the individual rows on a persons recordTime page. */
function updateHoursIndvdl(personHashDoingJob, hours, sourceID, targetID, jobID) 
{
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp, xmlhttp2;
var xmlhttpDone = false;    
if (hours=="")
  {
  document.getElementById(targetID).innerHTML="";
  return;
  }
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  xmlhttp2=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  xmlhttp2=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById(sourceID).value="";
    document.getElementById(targetID).innerHTML=xmlhttp.responseText;
    xmlhttp2.open("GET", "./php/getTimeRecords.php?jobID="+jobID+"&personHashDoingJob="+personHashDoingJob+"&random="+random, true); //gets the last 10 time update records for display by title attribute
    xmlhttp2.send();
    }
  }
xmlhttp2.onreadystatechange=function()
  {
  if (xmlhttp2.readyState==4 && xmlhttp2.status==200)
    {
    document.getElementById(sourceID).setAttribute('title', xmlhttp2.responseText);
    //alert(xmlhttp2.responseText);
    }
  }
xmlhttp.open("GET", "./php/updateTime.php?personHashEnteringData="+personHashDoingJob+"&value="+hours+"&jobAssnmtID="+sourceID+"&random="+random, true);
xmlhttp.send();
}


/*does the initial population of the tooltip for the current row  */
function initIndvdlToolTip(personHashDoingJob, targetID, jobID) 
{
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById(targetID).setAttribute('title', xmlhttp.responseText);
    }
  }
xmlhttp.open("GET", "./php/getTimeRecords.php?jobID="+jobID+"&personHashDoingJob="+personHashDoingJob+"&random="+random, true);
xmlhttp.send();
}


/* Hides the table row pointed to by targetID and set status of job to complete */
function changeJobCntrlValue(jobID, jobCntrlValue) 
{
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.open("GET", "./php/setUnsetJobPerm.php?jobID="+jobID+"&jobCntrlValue="+jobCntrlValue+"&random="+random, true);
xmlhttp.send(); 
}




/* Hides the table row pointed to by targetID and set status of job to complete */
function hideRowComplete(targetID, jobID, personHash) 
{
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function()
  {

  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    var rowID = targetID.split(",");
        for(i=0; i<rowID.length; i++) {
	    document.getElementById(rowID[i]).style.display = 'none';
        }        
    } 
  }
xmlhttp.open("GET", "./php/signOffJob.php?jobID="+jobID+"&personHash="+personHash+"&random="+random, true);
xmlhttp.send(); 
}


/* Hides the table row pointed to by targetID and set status of job to archive */
function hideRowArchive(personID, targetID, jobID) 
{
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function()
  {

  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    var rowID = targetID.split(",");
        for(i=0; i<rowID.length; i++) {
      document.getElementById(rowID[i]).style.display = 'none';
        }        
    } 
  }
xmlhttp.open("GET", "./php/archiveJob.php?personID="+personID+"&jobID="+jobID+"&random="+random, true);
xmlhttp.send(); 
}



/* Hides the table row pointed to by targetID and set status of allocated person in jobAssignments table to "Retired". personHash is used to record who is making this change */
function hideRowCompleteIndvdl(targetID, jobAssignmentID, personHash) 
{
var random = (new Date).getTime(); //random number to add as GET variable to php calls to prevent xmlHttpReq caching (not used by php script).
var xmlhttp;    
if (window.XMLHttpRequest)
  {// code for IE7+, Firefox, Chrome, Opera, Safari
  xmlhttp=new XMLHttpRequest();
  }
else
  {// code for IE6, IE5
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }

xmlhttp.onreadystatechange=function()
  {

  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
	    document.getElementById(targetID).style.display = 'none';
    } 
  }

xmlhttp.open("GET", "./php/signOffAlloctdPers.php?jobAssignmentID="+jobAssignmentID+"&personHash="+personHash+"&random="+random, true);
xmlhttp.send(); 
}


/* Initially all displays referenced by displayID0 - displayID5 are set to 'none' (invisible).
   Then for working from displayID0 - displayID5 if 'value' is between or equal to lower? and upper? displayID? is set to 'inline' (visible).
   If in the html code the same identifier is used for an associated label, but with the suffix 'lbl' concatonated onto it, then the label will also be made visible/invisible accordingly.
   A test is done to see if the label exists so a null label reference doesn't cause a function exit at that point and prevent the rest of it executing.
   If a string argument that is an identifier for an HTML tag ID is empty any changes to that tag visibility will not be implemented. (this means the function can be used with fewer parameters if desired).
*/
function showIfCertainValues6(value, lower0, upper0, displayID0, lower1, upper1, displayID1, lower2, upper2, displayID2, lower3, upper3, displayID3, lower4, upper4, displayID4, lower5, upper5, displayID5) 
{
  if (displayID0.length > 0) {
    document.getElementById(displayID0).style.display       = 'none';
    if (document.getElementById(displayID0+"lbl") != undefined) {
      document.getElementById(displayID0+"lbl").style.display = 'none';
    }
  }
  if (displayID1.length > 0) {
    document.getElementById(displayID1).style.display       = 'none';
    if (document.getElementById(displayID1+"lbl") != undefined) {
      document.getElementById(displayID1+"lbl").style.display = 'none';
    }
  }
  if (displayID2.length > 0) {
    document.getElementById(displayID2).style.display       = 'none';
    if (document.getElementById(displayID2+"lbl") != undefined) {
      document.getElementById(displayID2+"lbl").style.display = 'none';
    }
  }
  if (displayID3.length > 0) {
    document.getElementById(displayID3).style.display       = 'none';
    if (document.getElementById(displayID3+"lbl") != undefined) {
      document.getElementById(displayID3+"lbl").style.display = 'none';
    }
  }
  if (displayID4.length > 0) {
    document.getElementById(displayID4).style.display       = 'none';
    if (document.getElementById(displayID4+"lbl") != undefined) {
      document.getElementById(displayID4+"lbl").style.display = 'none';
    }
  }
  if (displayID5.length > 0) {
    document.getElementById(displayID5).style.display       = 'none';
    if (document.getElementById(displayID5+"lbl") != undefined) {
      document.getElementById(displayID5+"lbl").style.display = 'none';
    }
  }
  if (displayID0.length > 0) {
    if(lower0 <= value && value <= upper0) {
      document.getElementById(displayID0).style.display       = 'inline';
      if (document.getElementById(displayID0+"lbl") != undefined) {
        document.getElementById(displayID0+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID1.length > 0) {
    if(lower1 <= value && value <= upper1) {
      document.getElementById(displayID1).style.display       = 'inline';
      if (document.getElementById(displayID1+"lbl") != undefined) {
        document.getElementById(displayID1+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID2.length > 0) {
    if(lower2 <= value && value <= upper2) {
      document.getElementById(displayID2).style.display       = 'inline';
      if (document.getElementById(displayID2+"lbl") != undefined) {
        document.getElementById(displayID2+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID3.length > 0) {
    if(lower3 <= value && value <= upper3) {
      document.getElementById(displayID3).style.display       = 'inline';
      if (document.getElementById(displayID3+"lbl") != undefined) {
        document.getElementById(displayID3+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID4.length > 0) {
    if(lower4 <= value && value <= upper4) {
      document.getElementById(displayID4).style.display       = 'inline';
      if (document.getElementById(displayID4+"lbl") != undefined) {
        document.getElementById(displayID4+"lbl").style.display = 'inline';
      }
    }
  }
  if (displayID5.length > 0) {
    if(lower5 <= value && value <= upper5) {
      document.getElementById(displayID5).style.display       = 'inline';
      if (document.getElementById(displayID5+"lbl") != undefined) {
        document.getElementById(displayID5+"lbl").style.display = 'inline';
      }
    }
  }
}


/* If values are not equal displayID is made visible else it is made hidden. */
function showIfValuesNotEqual(value1, value2, displayID) {
    if (value1 == value2) {
        document.getElementById(displayID).style.display       = 'none';
        document.getElementById(displayID+"lbl").style.display = 'none';
    }
    else {
        document.getElementById(displayID).style.display       = 'inline';
        document.getElementById(displayID+"lbl").style.display = 'inline';
    }
}


/* If values are equal displayID is made visible else it is made hidden. */
function showIfValuesEqual(value1, value2, displayID) {
    if (value1 == value2) {
        document.getElementById(displayID).style.display       = 'inline';
        document.getElementById(displayID+"lbl").style.display = 'inline';
    }
    else {
        document.getElementById(displayID).style.display       = 'none';
        document.getElementById(displayID+"lbl").style.display = 'none';
    }
}

/* Shows some items pointed to by IDs and hides others */
function showHide(showID1, showID2, hideID2, hideID3, hideID4, hideID5) 
{
document.getElementById(hideID2).style.display = 'none';
document.getElementById(hideID2+"lbl").style.display = 'none';
document.getElementById(hideID3).style.display = 'none';
document.getElementById(hideID3+"lbl").style.display = 'none';

document.getElementById(hideID4).style.display = 'none';
document.getElementById(hideID4+"lbl").style.display = 'none';
document.getElementById(hideID5).style.display = 'none';
document.getElementById(hideID5+"lbl").style.display = 'none';

document.getElementById(showID1).style.display = 'inline';
document.getElementById(showID1+"lbl").style.display = 'inline';
document.getElementById(showID2).style.display = 'inline';
document.getElementById(showID2+"lbl").style.display = 'inline';
}


/* Shows some items pointed to by IDs and hides others. Has the additional feature that if values 1 & 2 are not equal displayID is made visible else it is made hidden. */
function showHideWithNotEqual(showID1, showID2, hideID2, hideID3, hideID4, hideID5, value1, value2, displayID) 
{
document.getElementById(hideID2).style.display = 'none';
document.getElementById(hideID2+"lbl").style.display = 'none';
document.getElementById(hideID3).style.display = 'none';
document.getElementById(hideID3+"lbl").style.display = 'none';

document.getElementById(hideID4).style.display = 'none';
document.getElementById(hideID4+"lbl").style.display = 'none';
document.getElementById(hideID5).style.display = 'none';
document.getElementById(hideID5+"lbl").style.display = 'none';

document.getElementById(showID1).style.display = 'inline';
document.getElementById(showID1+"lbl").style.display = 'inline';
document.getElementById(showID2).style.display = 'inline';
document.getElementById(showID2+"lbl").style.display = 'inline';

if (value1 == value2) {
    document.getElementById(displayID).style.display       = 'none';
    document.getElementById(displayID+"lbl").style.display = 'none';
}
else {
    document.getElementById(displayID).style.display       = 'inline';
    document.getElementById(displayID+"lbl").style.display = 'inline';
}
}


/* Shows item pointed to by showID1 and showID2 (and its label) and hides item pointed to by hideID3 (and its label). In addition showHideID4 (and its label) are shown if valueA == valueB, hidden if not.*/
function showHidePCB(valueA, valueB, showID1, showID2, hideID3, showHideID4) 
{
document.getElementById(showID1).style.display = 'inline';
document.getElementById(showID2).style.display = 'inline';
document.getElementById(showID2+"lbl").style.display = 'inline';
document.getElementById(hideID3).style.display = 'none';
document.getElementById(hideID3+"lbl").style.display = 'none';
if (valueA == valueB) {
    document.getElementById(showHideID4).style.display = 'inline';
    document.getElementById(showHideID4+"lbl").style.display = 'inline';
}
else {
    document.getElementById(showHideID4).style.display = 'none';
    document.getElementById(showHideID4+"lbl").style.display = 'none';
}
}


/* Shows some items pointed to by IDs and hides others */
function showHide3(showID1, hideID2, hideID3) 
{
document.getElementById(hideID2).style.display = 'none';
document.getElementById(hideID2+"lbl").style.display = 'none';
document.getElementById(hideID3).style.display = 'none';
document.getElementById(hideID3+"lbl").style.display = 'none';
document.getElementById(showID1).style.display = 'inline';
document.getElementById(showID1+"lbl").style.display = 'inline';
}

/* hides items pointed to by ID, show last item ID */
function hideAll(hideID1, hideID2, hideID3, hideID4, showID5) 
{
document.getElementById(hideID2).style.display = 'none';
document.getElementById(hideID2+"lbl").style.display = 'none';
document.getElementById(hideID1).style.display = 'none';
document.getElementById(hideID1+"lbl").style.display = 'none';
document.getElementById(hideID3).style.display = 'none';
document.getElementById(hideID3+"lbl").style.display = 'none';
document.getElementById(hideID4).style.display = 'none';
document.getElementById(hideID4+"lbl").style.display = 'none';
document.getElementById(showID5).style.display = 'inline';
}



/* Shows item pointed to by ID */
function show(showID1) 
{
document.getElementById(showID1).style.display = 'inline';
}

/* Shows item pointed to by ID */
function clearSingle(clearID) 
{
document.getElementById(clearID).value="";
}

String.prototype.escapeSpecialChars = function() {
    return this.replace(/\\n/g, "\\n")
               .replace(/\\'/g, "\\'")
               .replace(/\\"/g, '\\"')
               .replace(/\\&/g, "\\&")
               .replace(/\\r/g, "\\r")
               .replace(/\\t/g, "\\t")
               .replace(/\\b/g, "\\b")
               .replace(/\\f/g, "\\f");
};




function START(itemName) {
	if (checkTimeMode != "Off") {
		if (2000 < (timeMs() - checkTimeLastRun)) { //if it is more than 2 secs since last checktime START reset all variables
			checkTimeLastRun = timeMs();
			checkTimeStack = [];
			checkTimeTabCount = 0;
			console.log("EVERYTHING CLEARED !!");
		}
		console.log(getTab(checkTimeTabCount)+itemName+" {");
		var curTimeAry = {"name":itemName, "time":timeMs(), "start":true, "tabCount":checkTimeTabCount};
		checkTimeStack.push(curTimeAry); //push onto stack (really an array)
		checkTimeTabCount++; //increment tabValue 
	}
}

function FINISH(itemName) {
	if (checkTimeMode != "Off") {
		var curTimeAry = {"name":itemName, "time":timeMs(), "start":false};
		checkTimeStack.push(curTimeAry); //push FINISH name etc. onto stack (really an array)
		while ((2 <= checkTimeStack.length) && (checkTimeStack[checkTimeStack.length - 2]["start"] == true) && (checkTimeStack[checkTimeStack.length - 1]["start"] == false)) { // top 2 stack positions are a start and finish meaning, if nothing has gone wrong and all previous starts on same tab level have been finished, they should also have matching names
			poppedFinish = popStack(checkTimeStack); //pop top of stack - this contains FINISH time of item displayed after closing "}"
			poppedStart = popStack(checkTimeStack); //pop the START item too to get rid of it
			var elapsedTime = poppedFinish["time"] - poppedStart["time"];
			checkTimeTabCount--; //decrement tabValue
			if (poppedFinish["name"] != poppedStart["name"]) {
				console.log(getTab(checkTimeTabCount)+"}  "+elapsedTime+" NOT MATCHED! CLOSING BRACKET FOR - "+itemName);
			}
			else {
				console.log(getTab(checkTimeTabCount)+"}  "+elapsedTime);
			}
		}
	}
}





function getTab(count) {
	var tab = "";
	if (count == 0) {
		return "";
	}
	else {
		for (var i = 1; i <= count; i++) {
			tab = tab+".  ";
		}
		return tab;
	}
}

function popStack(ary) {
	if (0 < ary.length) {
		return ary.pop();
	}
	else {
		return -1;
	}
}

function timeMs() {
	var date = new Date();
	return date.getTime();
}


function timeToCons(descriptionPrefix) {
	var dateTest = new Date();
    var msTimeMeasure = dateTest.getTime();
    msTimeDiv100000 = msTimeMeasure / 100000;
    console.log(descriptionPrefix+": Time = "+ (((msTimeDiv100000)-Math.floor(msTimeDiv100000))*100).toFixed(3)  );
}

