<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$recoveredSessionAryCommitRnd = randomAlphaString($_cmndRndmLngth); //new random to be passed with any POST calls from forms so that when serialised, saved in table, session arrays are reconstructed above they can be checked against this for currency

//creates new (refreshed) menu randoms that will be saved to the sessionArrays in the tail
$menuRandomsArray = createKeysAndRandomsArray(array(
	"Upload Scans",
	"checkServerFlag",
	"Add User",
	"New Password",
	"Submit New Password",
	"Test",
	"Logout",
	"Show References",
	"Show Records For Full Year",
	"Ajax both ways with All Records",
	"Ajax Items 2 ways with All Records",
	"Update Doc File Name",
	"Add Items",
	"Edit Flex",
	"Ajax Atomic",
	"Update2 Table From Javascript",
	"Help Page"
), $_menuRndmLngth, $uniqnsChkAryForRndms); //creates new random values for menus - not all generated values will be used via menu buttons, some will be used for doc select buttons etc. to access pages not accessible via menu


?>
