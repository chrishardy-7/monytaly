<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$nameOfThisPage = "Edit Flex";

//include_once("./".$sdir."createMenuRndms.php");



$divEachWidth = 15.46776;
$col1W = 2.3436;
$col2W = 7.812;
$col3W = 2.604;

$genrlAry = Array("id", "accounts", "accountName", "status", "budgetName", "budgets", "docTags", "docTagName", "docVarieties", "docVarietyName", "orgPerCategories", "categoryName", "orgsOrPersons", "orgOrPersonName", );
$genAryRnds = createKeysAndRandomsArray($genrlAry, $_cmndRndmLngth, $uniqnsChkAryForRndms);
$nonVolatileArray["genrlAryRndms"] = $genAryRnds;

include_once("./".$sdir."saveSession.php");

$pathToPhpFile = htmlspecialchars($_SERVER["PHP_SELF"]);
$fileRndm = $menuRandomsArray["Update2 Table From Javascript"];


date_default_timezone_set("Europe/London");


$expClass = "expandingArea";

include_once("./".$sdir."head.php");
include_once("./".$sdir."menu.php");
?>

<body class="calendarBody">



<!--- ACCOUNTS -->
<div style="height: 45.0492vw; width: <?php echo $divEachWidth;?>vw; padding-left:1.0416vw; overflow:scroll; background-color: #FFE0FF; float:left;">
<div class="shrinkfitDiv" >
    <?php
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",  $col1W, "", "", "", "",  "id",          TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",  $col2W, "", "", "", "",  "accountName", TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",  $col3W, "", "", "", "",  "status",      TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>
</div>
<?php

$accfromDB = getDbRowsWhereMatching("accounts", array("id", "accountName", "status")); //get all rows
foreach ($accfromDB as $dBRow) { //works one at a time through all the rows of data produced by the getDbRowsWhereBetween().
?>    
<div class="shrinkfitDiv" >

    <?php
        expandingTextarea($expClass, $col1W, "",                      "",                "",           "",                         $dBRow["id"],          TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col2W, $genAryRnds["accounts"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["accountName"], $dBRow["accountName"], TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col3W, $genAryRnds["accounts"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["status"],      $dBRow["status"],      TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>

</div>
<?php
}

?> 
</div>


<!--- BUDGETS -->
<div style="height: 45.0492vw; width: <?php echo $divEachWidth;?>vw; padding-left:1.0416vw; overflow:scroll; background-color: #FFFFE0; float:left;">
<div class="shrinkfitDiv" >
    <?php
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col1W, "", "", "", "",  "id",         TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col2W, "", "", "", "",  "budgetName", TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col3W, "", "", "", "",  "status",     TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>
</div>
<?php

$budgfromDB = getDbRowsWhereMatching("budgets", array("id", "budgetName", "status")); //get all rows
foreach ($budgfromDB as $dBRow) { //works one at a time through all the rows of data produced by the getDbRowsWhereBetween().
?>    
<div class="shrinkfitDiv" >

    <?php
        expandingTextarea($expClass, $col1W, "",                     "",                "",           "",                        $dBRow["id"],         TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col2W, $genAryRnds["budgets"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["budgetName"], $dBRow["budgetName"], TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col3W, $genAryRnds["budgets"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["status"],     $dBRow["status"],     TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>

</div>
<?php
}

?> 
</div>


<!--- TAGS -->
<div style="height: 45.0492vw; width: <?php echo $divEachWidth;?>vw; padding-left:1.0416vw; overflow:scroll; background-color: #FFFFE0; float:left;">
<div class="shrinkfitDiv" >
    <?php
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col1W, "", "", "", "",  "id",         TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col2W, "", "", "", "",  "Umbrella", TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col3W, "", "", "", "",  "status",     TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>
</div>
<?php

$tagfromDB = getDbRowsWhereMatching("docTags", array("id", "docTagName", "status")); //get all rows
foreach ($tagfromDB as $dBRow) { //works one at a time through all the rows of data produced by the getDbRowsWhereBetween().
?>    
<div class="shrinkfitDiv" >

    <?php
        expandingTextarea($expClass, $col1W, "",                     "",                "",           "",                         $dBRow["id"],         TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col2W, $genAryRnds["docTags"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["docTagName"],  $dBRow["docTagName"], TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col3W, $genAryRnds["docTags"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["status"],      $dBRow["status"],     TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>

</div>
<?php
}

?> 
</div>


<!--- VARIETIES -->
<div style="height: 45.0492vw; width: <?php echo $divEachWidth;?>vw; padding-left:1.0416vw; overflow:scroll; background-color: #FFFFE0; float:left;">
<div class="shrinkfitDiv" >
    <?php
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col1W, "", "", "", "",  "id",             TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col2W, "", "", "", "",  "docVarietyName", TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col3W, "", "", "", "",  "status",         TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>
</div>
<?php

$varfromDB = getDbRowsWhereMatching("docVarieties", array("id", "docVarietyName", "status")); //get all rows
foreach ($varfromDB as $dBRow) { //works one at a time through all the rows of data produced by the getDbRowsWhereBetween().
?>    
<div class="shrinkfitDiv" >

    <?php
        expandingTextarea($expClass, $col1W, "",                          "",                "",           "",                             $dBRow["id"],             TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col2W, $genAryRnds["docVarieties"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["docVarietyName"],  $dBRow["docVarietyName"], TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col3W, $genAryRnds["docVarieties"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["status"],          $dBRow["status"],         TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>

</div>
<?php
}

?> 
</div>



<!--- CATEGORIES -->
<div style="height: 45.0492vw; width: <?php echo $divEachWidth;?>vw; padding-left:1.0416vw; overflow:scroll; background-color: #FFFFE0; float:left;">
<div class="shrinkfitDiv" >
    <?php
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col1W, "", "", "", "",  "id",           TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col2W, "", "", "", "",  "categoryName", TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col3W, "", "", "", "",  "status",       TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>
</div>
<?php

$catfromDB = getDbRowsWhereMatching("orgPerCategories", array("id", "categoryName", "status")); //get all rows
foreach ($catfromDB as $dBRow) { //works one at a time through all the rows of data produced by the getDbRowsWhereBetween().
?>    
<div class="shrinkfitDiv" >

    <?php
        expandingTextarea($expClass, $col1W, "",                              "",                "",           "",                          $dBRow["id"],           TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col2W, $genAryRnds["orgPerCategories"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["categoryName"], $dBRow["categoryName"], TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col3W, $genAryRnds["orgPerCategories"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["status"],       $dBRow["status"],       TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>

</div>
<?php
}

?> 
</div>



<!--- PERSORGS -->
<div style="height: 45.0492vw; width: <?php echo $divEachWidth;?>vw; padding-left:1.0416vw; overflow:scroll; background-color: #FFFFE0; float:left;">
<div class="shrinkfitDiv" >
    <?php
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col1W, "", "", "", "",  "id",              TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col2W, "", "", "", "",  "orgOrPersonName", TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea("expandingArea expandingAreaWhiteBG expandingAreaGreyText",   $col3W, "", "", "", "",  "status",          TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>
</div>
<?php

$orgPerfromDB = getDbRowsWhereMatching("orgsOrPersons", array("id", "orgOrPersonName", "status")); //get all rows
foreach ($orgPerfromDB as $dBRow) { //works one at a time through all the rows of data produced by the getDbRowsWhereBetween().
?>    
<div class="shrinkfitDiv" >

    <?php
        expandingTextarea($expClass, $col1W, "",                           "",                "",           "",                             $dBRow["id"],              TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col2W, $genAryRnds["orgsOrPersons"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["orgOrPersonName"], $dBRow["orgOrPersonName"], TRUE, TRUE, $pathToPhpFile, $fileRndm);
        expandingTextarea($expClass, $col3W, $genAryRnds["orgsOrPersons"], $genAryRnds["id"], $dBRow["id"], $genAryRnds["status"],          $dBRow["status"],          TRUE, TRUE, $pathToPhpFile, $fileRndm);
    ?>

</div>
<?php
}

?> 
</div>




<!-- script that is part of the expanding area system  -->
<script>
  var areas = document.querySelectorAll('.expandingArea');
  var l = areas.length;
  while (l--) {
    makeExpandingArea(areas[l]);
  }
</script>
    
<?php


include_once("./".$sdir."tail.php");
?>