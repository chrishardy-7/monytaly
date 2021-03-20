<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$nameOfThisPage = "Show References";

$startFreshPage = new persistVar("startFreshPage", FALSE); //trys to create and initialise a persistant variable called "startFreshPage". If it already exists it will neither be recreated nor initialised
$startFreshPage->set(FALSE); //set to FALSE so that the next call of showRecsForFullYr.php from the main menu button will not clear filters and set buttons etc. (which is the usual behaviour)

//include_once("./".$sdir."createMenuRndms.php");
include_once("./".$sdir."head.php");
include_once("./".$sdir."menu.php");

$recRefsAndDates = getRecRefsAndDates();

$refAry = $recRefsAndDates["refs"];
$dateAry = $recRefsAndDates["dates"];

?>
<div style="height: 44.268vw; width: 98.4312vw; padding-left:1.0416vw; overflow:scroll; background-color: #FFFFFF; float:left;">
<?php

$previousRef = 0;
foreach ($refAry as $idR=>$ref) {
	$filteredRef = (int)filter_var($ref, FILTER_SANITIZE_NUMBER_INT); //remove everything but numbers and convert to int
	$diff = "";
	if  ((1 < ($filteredRef - $previousRef)) &&  $previousRef) { //if there is a gap in the cheque numbers and this isn't the first number to be displayed
		$diff = " ---- ".($filteredRef - $previousRef -1)." ---- ".$dateAry[$idR];
	}
	print_r(" ".$ref.$diff."</br>");
	$previousRef = $filteredRef;
}


?>
</div>
<?php
include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>
