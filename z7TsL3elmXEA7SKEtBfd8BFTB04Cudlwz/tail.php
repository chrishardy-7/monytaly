<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

?>

</div> <!--Closing overall container div for the whole web page -->
</body>

</html>

<?php

/*
$sessionArrays["monthsSelRndArray"] = $monthsSelRndArray; //renews the personSession table serialMenuArray field by saving MonthSelRandmNewArry to it, which contains new random values
$sessionArrays["menuRandomsArray"] = $menuRandomsArray; //renews the personSession table serialMenuArray field by saving menuRandoms to it, which contains new random values
$sessionArrays["nonVolatileArray"] = $nonVolatileArray;
$sessionArrays["docRandomsArray"] = $docRandomsArray;
$sessionArrays["uploadBtnsRndmsArray"] = $uploadBtnsRndmsArray;
$sessionArrays["orgOrPersonsRandomsArray"] = $orgOrPersonsRandomsArray;
$sessionArrays["docVarietyRandomsArray"] = $docVarietyRandomsArray;
$sessionArrays["docTagRandomsArray"] = $docTagRandomsArray;
$sessionArrays["docNotesRandomsAry"] = $docNotesRandomsAry;
$sessionArrays["parentDocRefRandomsAry"] = $parentDocRefRandomsAry;
$sessionArrays["sessionCommitRnd"] = $recoveredSessionAryCommitRnd; //saves a random number that is also passed with any POST call so that when the arrays are reconstructed they can be checked against this for currency
saveMenuRandomsArray($userId, $sessionArrays); // saves all session arrays by serialising them and storing them in personSession table. If userId has not been established (0) everything will be lost
*/
//ob_flush();
//flush();
//sleep(2);

//$a=1/0; //line to check warnings are working!

//echo "AFTER A WEE SLEEP";
$conn=NULL; //close PDO database object
?>
