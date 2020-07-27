<?php
ini_set('display_errors',1);
error_reporting(E_ALL & -E_DEPRECATED); //set to not show deprecated notices, primarily to allow PDFmerger to work on this PHP7 when it was only written and tested for PHP 5.x

//setup php for working with Unicode data
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
ob_start('mb_output_handler');

header("Cache-Control: private, must-revalidate, max-age=0, no-store, no-cache, post-check=0, pre-check=0");

/* !! IMPORTANT NOTES !!
	1) Remember to remove time(TRUE) from ....JSforAccCcc.js?version=<?php echo (string)time(TRUE); ?>.... in head.php before copying to monytaly.uk
	2) Remember to write obfuscating system for call to updateTableFromJS.php AND ALL OTHER BITS THAT NEED OBFUSCATING !!!
	3) Check operation of calJavaScrpInteractn with hidden textboxes (undefined variable: errors) and fix invalid copying of 31 Feb to date button in Edit Records page
	4) Fix return button not dealing with value entered properly on Edit Records page
	5) Write correct doc for updateTableFromJS.php and update2TableFromJS.php
	6) Limiting dates are set in filteredDocData() (in php/funcsToRdWrTblesForAccCcc.php) and selectDoc.php that will miss-select/display data outside the date range 2000-01-01 - 2030-01-01.
*/

//error_reporting(0);

/*
function myException(PDOException $exception) {
  echo "<b>Exception:</b> " . $exception->getMessage();
}

set_exception_handler('myException');
*/

//error_reporting(0);

$arrayOfPathToThisFile = explode("/", __DIR__); //produces an array of the path to this file (not the simlink to this file)
$dir = end($arrayOfPathToThisFile)."/"; //produces the parent dir of this file
include_once("/var/monytalyData/".$dir."globals.php"); //includes the globals.php file that contains database credentials and other things
$sdir = $dir.$subdir;
$pathToPhpFiles = "./".$sdir."php/";

include_once("./".$sdir."php/funcsForAccCcc.php");
include_once("./".$sdir."php/classesForAccCcc.php");
include_once("./".$sdir."php/funcsToRdWrTblesForAccCcc.php");

//changePwAndClrFlag(1, "r");

//pr("printingIsWorking ");
//pr(ini_get('display_errors'));

/* MAKE SURE TO REPLICATE FOLLOWING SETTINGS IN /etc/php/7.0/apache2.php.ini  ??? (may not be necessary if only using a custom cookie!!!)
	session.name = PUP
	session.cookie_httponly = 1
	session.use_strict_mode = 1 (CONSIDER THIS - PREVENTS COOKIE VALUES CREATED IN BROWSER FROM BEING ACCEPTED AS SESSION ID BY SERVER)
	session.cookie_secure = 1 (SET THIS ON SERVER WHERE https IS IN USE)
*/

/* THINGS TO CONSIDER !!!
	1) What to do if user logs in with different device(s) while session still active on first device. At moment think the second login would simply take over (new session cookie) and expire previous session.
	2) Email user if password changed
	3) Email user if there are several unscuccessful login attempts in a given interval, or between successful attempts (could work without cookie - tie to username)
	4) Facility for usere to review previous login times, dates, durations, ip and browser (include failed attempts to their username, with display of wrong passwords used?). Won't work properly with (1)
	5) Insert a delay before redisplay of login page after any sort of unsuccessful attempt, to put the brakes on any unauthorised someone trying to get in
	6) Allow only so many unsuccessful login attempts from same IP/Browser or username then put a wait time on to inhibit further logins with same signature.
	7) Consider use of sanPost only where it can be, to make it harder for someone to just create a URL with the appropriate $_GET data on it to try and find back doors
	8) Create new user timeout so they will only be albe to use their temporary password for a limited period (say one hour?)
	9) Check password submitted by add user page and return to page if it doesn't pass muster. Also put lower limit on username number of characters.
	10) be more specific when change password or force new password fails.
	11) $userData = getUserData($userId); geta array of all user data from table in one go - consider using to speed things up in the future if it seems appropriate (though some of the data changes!)
*/

/* Global section for setting various constants */
//$_masterYear = date("Y"); //set to current year (i.e. 2019)
$_masterYear = "2020"; //set to 2019 for current year
$_startMonth = "04"; // sets start month in previous year
$_endMonth = "03"; // sets end month in current year
$_calledFromIndexPage = TRUE; //used as a test in the included pages to stop them being called directly from a browser
$_customSessionCookieLength = 50;
$_cookieName = "ID";
$_sessionTimeLimSecs = 10800; // session expires 3 hrs after login (consider javascript early warning for user)
$_noActivityTimeLimSecs = 2700; //session expires after 45 min of inactivity
$_noActivityTimeLimSecsForPWreset = 180; //session expires if new password isn't presented within 3 min of the password reset page being displayed
$_passwordMinLength = 8; //minimum length allowed when creating new password
$_menuRndmLngth = 7;
$_cmndRndmLngth = 7;
$_onTheHoofRandsLength = 5;
$_filenameRandLength = 5;
$_ImagickExceptionVisibility = TRUE;
$_fieldNameAry = array("recordDate", "personOrOrg", "transCatgry", "amountWithdrawn", "amountPaidIn", "accWorkedOn", "budget", "referenceInfo", "reconciledDate", "umbrella", "docType", "recordNotes", "parent"); //fieldNames for column ids (0 - 11), the array would need to be changed if the column order (and therefore the cell ids) on the display page changes!!
$_commitLoopCountMax = 30;

/* End of global section for setting various constants */

/* Initialisation of global variables */
$clientSessCookie = "";
$userId = 0;
$menuMainIndicator = ""; //used to pass which button is selected to the main menu
$message = ""; //message for various pages or exit conditions
$docFileNameNum = "";
$sessionArrays = array(); //initialise array to save individual randoms arrays for main menu and things like the docs page etc. (and any array that needs to be carried over in the same session)
$uniqnsChkAryForRndms = array(); //

$genrlAry = Array("allRecords", "amountWithdrawn", "amountPaidIn", "recordDate", "persOrgCategory", "orgPerCategories", "categoryName", "idR", "duplicateRec", "deleteRec", "nextDocFromSelection", "prevDocFromSelection", "orgsOrPersons", "orgOrPersonName", "referenceInfo", "accWorkedOn", "linkedAccOrBudg", "accounts", "accountName", "budgets", "budgetName", "otherDocsCsv", "docTags", "docTagName", "docVarieties", "docVarietyName", "Download", "expandFamilies", "showEverything", "toggleEditFamilies", "toggleBankAccDisplay", "nextStatement", "prevStatement");
$genrlAryRndms = createKeysAndRandomsArray($genrlAry, $_cmndRndmLngth, $uniqnsChkAryForRndms);

/* End of initialisation of global variables  */

if(isset($_COOKIE[$_cookieName])) {  //if the specifically named cookie has been received from client the value is placed in $clientSessCookie, otherwise the value is 0
    $clientSessCookie = $_COOKIE[$_cookieName];
}

//SECTION DEALING WITH NO COOKIE FROM CLIENT (This 'if' section always terminates internally with an exit(""). During a sucessful login it will present the Welcome Page)
if (!$clientSessCookie) { //no session cookie
	$monthsSelRndArray = array(); //initialise session arrays at this initial login stage - this will not happen again as this section of code will always be bypassed as long as cookie exists on client
	$menuRandomsArray = array();
	$nonVolatileArray = array();
	$docRandomsArray = array();
	$uploadBtnsRndmsArray = array();
	$orgOrPersonsRandomsArray = array();
	$docVarietyRandomsArray = array();
	$docTagRandomsArray = array();
	$docNotesRandomsAry = array();
	$parentDocRefRandomsAry = array();

	$username = sanPost('username');
	$password = sanPost('password');
	if (!(($username) && ($password))) { //if username and password don't exist go to login page
		$message = "Logged Out -  Either or both username and password not supplied";
		include_once("./".$sdir."login.php");
		exit("");
	}
	$userId = userIdifPasswordMatches($username, $password); //attempt to get user id - 0 should be returned if no matching user
	$userData = getUserData($userId);
	$superUser = $userData["superuser"];
	$allowedToEdit = $superUser; //at the moment only allow super users editing rights
	if (!$userId) {  //if user id doesn't exist go to login page
		$message = "Logged Out -  Login credentials don't match any user";
		include_once("./".$sdir."login.php");
		exit("");
	}

	if (PwResetFlagIsSet($userId)) { //user identified, if password reset flag is set force a password reset by going to password reset page
		resetActivTime($userId); //Sets the activity time in table
		startSession($userId);
		include_once("./".$sdir."createMenuRndms.php");
		include_once("./".$sdir."forcePasswordReset.php");
		exit("");
	}
	startSession($userId);
	resetActivTime($userId); //Sets the activity time in table
	include_once("./".$sdir."createMenuRndms.php");
	include_once("./".$sdir."welcome.php");
	exit("");
}

//SECTION DEALING WITH COOKIE FROM CLIENT
$userId = getUserIdfromCookie($clientSessCookie); //uses cookie to get user id from personSession table, 0 returned if no match is found
if (!$userId) {  //if user id doesn't exist go to login page
	$message = "Logged Out -  Cookie, but not current for any user";
	deleteCookieOnClient($_cookieName);
	include_once("./".$sdir."login.php");
	exit("");
	}

//FROM HERE ON THE USER IS IDENTIFIED BY THE COOKIE AND ALL SESSION STUFF CAN BE USED
 
$userData = getUserData($userId); //determine if user is a superuser
$superUser = $userData["superuser"];
$allowedToEdit = $superUser; //at the moment only allow super users editing rights

$recoveredSessionArrays = recoveredmenuRandomsArray($userId); //get session array of arrays from personSession table

/*
// THIS SECTION THAT CHECKS THE COMMIT RANDOM TO ENSURE SESSION ARRAYS ARE CURRENT MAY BE REMED OUT IF IT IS PREVENTING SOME FEATURES FROM WORKING - IT MAY NOT BE NEEDED!
$sessionAryCommitRnd = sanPost('sessionCommitRnd','47dw847-NoPOSTedRAndom!'); //get POSTed session commit random for comparison with the one from the recovered from the personSession table (below). Default to random random so no likely match can occur unless real recovered random exists (probably not needed!)
$recoveredSessionAryCommitRnd = "";
if (array_key_exists ("sessionCommitRnd", $recoveredSessionArrays)) { //recover nonVolatileArray string that is session commit random used to check session arrays for currency
	$recoveredSessionAryCommitRnd = $recoveredSessionArrays["sessionCommitRnd"]; //get the recovered session commit random for comparison with POSTed one
}
$commitLoopCount = 0;
while (($recoveredSessionAryCommitRnd) && ($recoveredSessionAryCommitRnd != $sessionAryCommitRnd) && ($commitLoopCount <= $_commitLoopCountMax)) { //as long as a random has been recovered and they are not equal (max $_commitLoopCountMax shots)
	usleep(50000); //50ms delay to allow any other php script that was updating session arrays to complete
	$recoveredSessionArrays = recoveredmenuRandomsArray($userId); //get session array of arrays again from personSession table
	if (array_key_exists ("sessionCommitRnd", $recoveredSessionArrays)) { //recover nonVolatileArray string that is session commit random used to check session arrays for currency
		$recoveredSessionAryCommitRnd = $recoveredSessionArrays["sessionCommitRnd"];
	}
	$commitLoopCount++;
}
if ($_commitLoopCountMax < $commitLoopCount) { //looped for 0 - $_commitLoopCountMax but no current commitRnd materialised so end session
	$message = "Logged Out -  No current commitRnd materialised ";
	clearSession($userId, "", "if ($_commitLoopCountMax < $commitLoopCount) in indexPage.php - recovrdRand = ".$recoveredSessionAryCommitRnd." POSTedRandom = ".$sessionAryCommitRnd.", time = ".microtime(true));
	include_once("./".$sdir."login.php");
	exit("");
}
else {
	saveMessage("Successful sessionAryCommit Match - loop count = ".$commitLoopCount.", time = ".microtime(true));
}
*/




$nonVolatileArray = array(); //create array to act as general non-volatile storage for session (this is to prevent warnings - it will be replaced by the real recovered array below if it exists)
if (array_key_exists ("nonVolatileArray", $recoveredSessionArrays)) { //recover nonVolatileArray array
	$nonVolatileArray = $recoveredSessionArrays["nonVolatileArray"];
}
$menuRandomsArray = array();
if (array_key_exists ("menuRandomsArray", $recoveredSessionArrays)) { //recover menu randoms array
	$menuRandomsArray = $recoveredSessionArrays["menuRandomsArray"];
}
$docRandomsArray = array();
if (array_key_exists ("docRandomsArray", $recoveredSessionArrays)) { //recover subCommand randoms array
	$docRandomsArray = $recoveredSessionArrays["docRandomsArray"];
}
$monthsSelRndArray = array();
if (array_key_exists ("monthsSelRndArray", $recoveredSessionArrays)) { //recover monthsSelRndArray randoms array
	$monthsSelRndArray = $recoveredSessionArrays["monthsSelRndArray"];
}
$uploadBtnsRndmsArray = array();
if (array_key_exists ("uploadBtnsRndmsArray", $recoveredSessionArrays)) { //recover uploadBtnsRndmsArray randoms array
	$uploadBtnsRndmsArray = $recoveredSessionArrays["uploadBtnsRndmsArray"];
}
$orgOrPersonsRandomsArray = array();
if (array_key_exists ("orgOrPersonsRandomsArray", $recoveredSessionArrays)) { //recover uploadBtnsRndmsArray randoms array
	$orgOrPersonsRandomsArray = $recoveredSessionArrays["orgOrPersonsRandomsArray"];
}
$docVarietyRandomsArray = array();
if (array_key_exists ("docVarietyRandomsArray", $recoveredSessionArrays)) { //recover uploadBtnsRndmsArray randoms array
	$docVarietyRandomsArray = $recoveredSessionArrays["docVarietyRandomsArray"];
}
$docTagRandomsArray = array();
if (array_key_exists ("docTagRandomsArray", $recoveredSessionArrays)) { //recover uploadBtnsRndmsArray randoms array
	$docTagRandomsArray = $recoveredSessionArrays["docTagRandomsArray"];
}
$docNotesRandomsAry = array();
if (array_key_exists ("docNotesRandomsAry", $recoveredSessionArrays)) { //recover uploadBtnsRndmsArray randoms array
	$docNotesRandomsAry = $recoveredSessionArrays["docNotesRandomsAry"];
}
$parentDocRefRandomsAry = array();
if (array_key_exists ("parentDocRefRandomsAry", $recoveredSessionArrays)) { //recover uploadBtnsRndmsArray randoms array
	$parentDocRefRandomsAry = $recoveredSessionArrays["parentDocRefRandomsAry"];
}

$prevMenuRandomsArray = $menuRandomsArray; //copy menuRandoms before they are changed by createMenuRndms.php a little later in this script - for use by additems.php  and others to determine calling page

if (!array_key_exists ("onTheHoofRandsAry", $nonVolatileArray)) { //create the key "onTheHoofRandsAry" if it doesn't already exist
	$nonVolatileArray["onTheHoofRandsAry"] = array();
}
$plainItemsWithRandKeysAry = array_flip($nonVolatileArray["onTheHoofRandsAry"]);



if (PwResetFlagIsSet($userId)) { //user identified - if password reset flag is set proceed with password reset section
	if (inactiveTimeout($userId, $_noActivityTimeLimSecsForPWreset)) { //if time allocated for user to respond to password reset request has expired
		$message = "Password Reset Time Expired !";
		clearSession($userId, $message, "PwResetFlagIsSet($userId) then inactiveTimeout($userId, $_noActivityTimeLimSecsForPWreset) in indexPage.php");
		include_once("./".$sdir."login.php");
		exit("");
	}
	$oldPassword = sanPost('oldPassword');
	$newPassword = sanPost('newPassword');
	$newPasswordRepeat = sanPost('newPasswordRepeat');
	if (!oldAndNewPwOK($userId, $oldPassword, $newPassword, $newPasswordRepeat)) { //if password reset fails (because the old and new password (and repeat of the new password) don't pass muster)
		$message = "New Password doesen't meet criteria !";
		clearSession($userId, $message, "PwResetFlagIsSet($userId) then !oldAndNewPwOK($userId, $oldPassword, $newPassword, $newPasswordRepeat) in indexPage.php");
		include_once("./".$sdir."login.php");
		exit("");
	}
	changePwAndClrFlag($userId, $newPassword);
	$message = "Password Successfully Changed";
	startSession($userId);
	include_once("./".$sdir."createMenuRndms.php");
	include_once("./".$sdir."welcome.php");
	exit("");
}

if (!loggedIn($userId)) { //cookie exists but loggedIn flag not set - don't display message in case page has been called with a stolen cookie
	$message = "Logged Out -  Cookie, but no logged in flag set";
	clearSession($userId, "", "!loggedIn($userId) in indexPage.php");
	include_once("./".$sdir."login.php");
	exit("");
}

if (sessionTimeout($userId, $_sessionTimeLimSecs)) { //if more than the set maximum time for the session has passed
	$message = "Session Timed Out - Please Login Again !";
	clearSession($userId, $message, "sessionTimeout($userId, $_sessionTimeLimSecs) in indexPage.php");
	include_once("./".$sdir."login.php");
	exit("");
}

if (inactiveTimeout($userId, $_noActivityTimeLimSecs)) { //if more than the set maximum time since last user activity has passed
		$message = "User Inactive for Too Long - Please Login Again !";
		clearSession($userId, $message, "inactiveTimeout($userId, $_noActivityTimeLimSecs) in indexPage.php");
		include_once("./".$sdir."login.php");
		exit("");
	}

resetActivTime($userId); //Sets the activity time in table

$command = sanPost('command'); //save command single or double (hyphenated) random strings from post data
if (!$command) { //if NOT called from command random
	clearSession($userId, "", "!$command in indexPage.php"); //don't display message in case page has been called with a stolen cookie
	$message = "Logged Out -  No Menu or subCommand values";
	include_once("./".$sdir."login.php");
	exit("");
}

$menuBtnPlusSubCmndArray = explode('-', $command); //splits command into a two item array of menu-subCommand
$menuBtn = $menuBtnPlusSubCmndArray[0]; //return the menuBtn random from the array and assigns it to $menuBtn so the program can continue
$subCommand = "";
if (array_key_exists (1, $menuBtnPlusSubCmndArray)) {
	$subCommand = $menuBtnPlusSubCmndArray[1]; //returns the subCommand random alphanumeric from the array and assigns it to $subCommand
}
$subSubCommand = "";
if (array_key_exists (2, $menuBtnPlusSubCmndArray)) {
	$subSubCommand = $menuBtnPlusSubCmndArray[2]; //returns the subSubCommand random alphanumeric from the array and assigns it to $subSubCommand
}


if (!in_array($menuBtn, $menuRandomsArray)) { //if the random alphanumeric representing a file description doesn't exist in the array clear session and go to login
	clearSession($userId, "", "!in_array(menuBtn, menuRandomsArray) in indexPage.php"); 
	$message = "Logged Out -  Menu random not recognised ".$menuBtn;
	include_once("./".$sdir."login.php");
	exit("");
}
$menuRandomsArrayArrayFlipped = array_flip($menuRandomsArray); //flip the array so the random alphanumerics become the keys and the file description the values - allows next line to get the file description
$menuBtnStr = $menuRandomsArrayArrayFlipped[$menuBtn];

if ($menuBtnStr == "Submit New Password") { //call from 'new password page' do password change routine
	resetActivTime($userId);
 	$oldPassword = sanPost('oldPassword');
	$newPassword = sanPost('newPassword');
	$newPasswordRepeat = sanPost('newPasswordRepeat');
	if (!oldAndNewPwOK($userId, $oldPassword, $newPassword, $newPasswordRepeat)) { //if password reset fails (because the old and new password (and repeat of the new password) don't pass muster)
		$message = "New Password doesen't meet criteria !";
		include_once("./".$sdir."createMenuRndms.php");
		include_once("./".$sdir."changePassword.php");
		exit("");
	}
	changePwAndClrFlag($userId, $newPassword); //clear force password change flag not really needed as it will not be set but used for economy so new function not needed - it does no harm.
	$message = "Password Successfully Changed";
	startSession($userId);
	include_once("./".$sdir."createMenuRndms.php");
	include_once("./".$sdir."welcome.php");
	exit("");
}

if ($menuBtnStr == "Ajax Atomic") {
	include_once("./".$sdir."php/ajaxAtomic.php");
	exit("");
}

saveMessage($menuBtnStr.", time = ".microtime(true));

$callingPage = array_search($subCommand, $prevMenuRandomsArray); //saves name of calling page if it has been used
include_once("./".$sdir."createMenuRndms.php"); //create new menu randoms for next set of menu buttons
switch ($menuBtnStr) {
	case "Upload Scans":
		//resetActivTime($userId);
		include_once("./".$sdir."uploadScans.php");
		break;
	case "Add User": //initial call to add user page
		//resetActivTime($userId);
		include_once("./".$sdir."addUser.php");
		break;
	case "New Password":
		//resetActivTime($userId);
		include_once("./".$sdir."changePassword.php");
		break;
	case "Show References":
		//resetActivTime($userId);
		include_once("./".$sdir."showRefs.php");
		break;
	case "Show Records For Full Year":
		//resetActivTime($userId);
		include_once("./".$sdir."showRecsForFullYr.php");
		break;
	case "Test":
		//resetActivTime($userId);
		include_once("./".$sdir."testPage.php");
		break;
	case "Edit Flex":
		//resetActivTime($userId);
		include_once("./".$sdir."editFlex.php");
		break;	
	case "Ajax both ways with All Records": //USED BY calJavaScrpInteractnLite() (2 off) PHP FUNCTION VIA ajaxRecordsDateAndCellUpdate() JS FUNCTION to change dates in allRecords table
		include_once("./".$sdir."php/ajaxAllRecsBothways.php");
		break;
	case "Ajax Items 2 ways with All Records": //USED BY butPanelJSInteracStrOnly() (8 off) PHP FUNCTION VIA ajaxRecordsItemAndCellUpdate() JS FUNCTION to change item ids in allRecords table. Also used by clickField() > updateFromSticky() > ajaxRecordsItemAndCellUpdate()
		include_once("./".$sdir."php/ajaxAllRecsItems2ways.php");
		break;
	case "Ajax MoneyIn0ut 2 ways with All Records": //USED BY clickField() > upDatewithdrnPaidin() > upDatewithdrnPaidin()
		include_once("./".$sdir."php/ajaxAllRecsMoney2ways.php");
		break;
	case "Ajax Get Balance Data": //USED BY clickField() > displayBalances(id) > ajaxGetAndDisplayBals()
		include_once("./".$sdir."php/ajaxGetBalData.php");
		break;
	case "Update Doc File Name": //USED BY clickField() > newDocFileName(id) > ajaxUpdateDocFileName()
		//resetActivTime($userId);
		include_once("./".$sdir."php/updateFileName.php");
		break;

	case "Update2 Table From Javascript": //USED BY editFlex.php
		include_once("./".$sdir."php/update2TableFromJS.php");
		break;

	case "Add Items": //USED BY butPanelJSInteracStrOnly() (8 off) PHP FUNCTION VIA to CALL add items page.
		//resetActivTime($userId);
		include_once("./".$sdir."addItems.php");
		break;
	case "Logout":
		$message = "Logged Out -  Login Again?";
		clearSession($userId, $message);
		include_once("./".$sdir."login.php");
		break;
	//case "Ajax Atomic":
		//include_once("./".$sdir."php/ajaxAtomic.php");
	//	break;
	default:
		$message = "Logged Out -  Menu Default";
		clearSession($userId, "");
		include_once("./".$sdir."login.php");
		break;
}


?>
