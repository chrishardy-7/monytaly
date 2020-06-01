<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$monthsNamesArray = array(
	"&#923;",
	"Mar",
	"Apr",
	"May",
	"Jun",
	"Jul",
	"Aug",
	"Sep",
	"Oct",
	"Nov",
	"Dec",
	strval($nonVolatileArray["masterYear"]),
	"Jan",
	"Feb",
	"Mar",
	"Apr",
	"May",
	"Jun",
	"Jul",
	"Aug",
	"Sep",
	"Oct",
	"Nov",
	"V"
);

$monthsAry = array(
	"Back",
	"0"."03",
	"0"."04",
	"0"."05",
	"0"."06",
	"0"."07",
	"0"."08",
	"0"."09",
	"0"."10",
	"0"."11",
	"0"."12",
	"Whole Financial Year",
	"1"."01",
	"1"."02",
	"1"."03",
	"1"."04",
	"1"."05",
	"1"."06",
	"1"."07",
	"1"."08",
	"1"."09",
	"1"."10",
	"1"."11",
	"Forward"
);

//creates new (refreshed) menu randoms that will be saved to the sessionArrays in the tail
$monthsSelRndArray = createKeysAndRandomsArray($monthsAry, $_cmndRndmLngth, $uniqnsChkAryForRndms); //creates new random values for menus - not all generated values will be used via menu buttons, some will be used for doc select buttons etc. to access pages not accessible via menu
?>

<div class="mnthSelContainer">
<form id="monthSelForm" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
	<?php
	$monthIndex = 0;
	foreach($monthsSelRndArray as $monthNum => $monthRandom) {
		$monthNumButId = $monthRandom."mnthNumButId";
		$monthButClass = "monthBtn";
		if (($monthNum == "Back") || ($monthNum == "Forward") || ($monthNum == "Whole Financial Year")) {
			$monthButClass = "monthBtnYearUpDown";
		}
		//elseif (($yrMnthStart <= $monthNum) && ($monthNum <= $yrMnthEnd)) { //if monthNum is definitely as month and not "Back" / "Forward" / "Whole Financial Year", compare with dates to generate class
		elseif (($nonVolatileArray["startYearOffsetPlusMnth"] <= $monthNum) && ($monthNum <= $nonVolatileArray["endYearOffsetPlusMnth"])) { //if monthNum is definitely as month and not "Back" / "Forward" / "Whole Financial Year", compare with dates and generate appropriate class
			$monthButClass = "monthBtnSelected";
		}
		?>
		<button class=<?php echo $monthButClass;?> id=<?php echo $monthNumButId;?>  type="submit" name="command" value=<?php echo $menuRandomsArray[$nameOfThisPage]."-".$monthRandom;?> onclick="detectShiftBut(event, this.id);"><?php echo $monthsNamesArray[$monthIndex];?></button>
	<?php
	//when a month button is pressed while shift button is held down detectShiftBut(event, id) suffixes "-mnthButShift" to the value of that button which is collected as a subSubCommand in monthSelProcess.php and forces selection of a range of months
	$monthIndex++;
	}
	namedValHolder("sessionCommitRnd", $recoveredSessionAryCommitRnd);
	?>
</form>
</div>

