<?php
session_start(); //start session to prepare for use of global variable for passing data to other pages - MUST BE BEFORE ANY HTML TAGS!!
header('Content-Type: text/html;charset=utf-8'); //ensures PHP is set for UTF-8 character set to match html. This should avoid things like Â before £ characters due to PHP defaulting to ISO 8859-1.
include_once("/var/monytalyData/Q3dj4G8/globals.php");
$timeStart = microtime(true); //use microtime to time how long this page takes to execute
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<!-- <!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/REC-html40/strict.dtd">  -->
<!html xmlns="http://www.w3.org/1999/xhtml">

<head>
    <title>Display Table</title> 
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta HTTP-EQUIV="CACHE-CONTROL" CONTENT="NO-CACHE" />
    <META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
    <META HTTP-EQUIV="EXPIRES" CONTENT="0"> <!-- Expires immediately -->
    <meta http-equiv="X-UA-Compatible" content="IE=9">

    <link rel="stylesheet" href="../css/esjr.css" type="text/css" media="all" />
    <link rel="stylesheet" href="../css/expandText.css?version=2016-04-29-1426" type="text/css" media="all" />
    <script type="text/javascript" src="../scripts/scriptsForJobsys.js"></script>

<style>
    .butActiveLinkForDispTbl {
        display: inline-block;
        width: 140px;
        height: 12px;
        color: #444444;
        background-color: #DDDDDD;
        font:10px/100% Verdana;
        text-align: left;
        padding-left: 5px;
        padding-right: 0px;
        padding-top: 2px;
        padding-bottom: 2px;
        text-decoration: none;
        margin-left: 0px;
        margin-top: 0px;
        margin-bottom: 0px;
      /*  border: 1px solid #aaaaaa;
        border-radius: 5px; */
        white-space: nowrap;
    }

    .butActiveLinkForDispTbl:hover {
        background-color: #EEEEEE;
    }

    .butSelectedLinkForDispTbl {
        display: inline-block;
        width: 140px;
        height: 12px;
        color: #000000;
        background-color: #FFFF90;
        font:10px/100% Verdana;
        text-align: left;
        padding-left: 5px;
        padding-right: 0px;
        padding-top: 2px;
        padding-bottom: 2px;
        text-decoration: none;
        margin-left: 0px;
        margin-top: 0px;
        margin-bottom: 0px;
      /*  border: 1px solid #aaaaaa;
        border-radius: 5px; */
        white-space: nowrap;
    }

</style>
</head>

<body>
<?php

/*Creates a button link to call a url, with shape, size, colours and hover characteristics given by $butClass. url and a url payload (which could include ?aaaaa=bbbbb&ccccc=ddd etc.) and button text are passed as variables. */
function buttonLink($butClass, $attribute, $url, $urlPayload, $butText) {
    if ($attribute == "inhibit") {
        echo '<a class='.$butClass.' ></a>'; //no url link - effectively a dead button that does nothing
    }
    else { //render the button
        echo '<a class='.$butClass.' href=\''.$url.$urlPayload.'\'">'.$butText.'</a>';
    }
}

function testInput($data) { //sanitising function to remove undesirable input characters.
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}

function displayHidChars($line) {
    $line = str_replace(" ", "<span style='background-color:#606060; color:#FFFFFF;' >SP</span>", $line);
    $line = str_replace("\r", "<span style='background-color:#A0C0FF; color:#000000;' >CR</span>", $line);
    $line = str_replace("\n", "<span style='background-color:#00FF00; color:#000000;' >LF</span>", $line);
    $line = str_replace("\t", "<span style='background-color:#FFD0D0; color:#000000;' >TAB</span>", $line);
    return $line;
}


/* Creates an expanding textarea that will initially display the string $initialData. It will expand vertically to accommodate the text that is in it, including line breaks. Look and feel will be determined by $expandingTextareaClass. $create true/false argument determines whether the the element will be created or not, $editable determines whether changes will be passed via 'onchange' function. When the text in the box is edited and the mouse clicked away from the box (focus lost) the updateTable() javascript function calls updateTable.php for $tableName and updates $targetFieldName in the row where argument $matchValue finds a matching field in $columnToMatch. If a matching field is not found (a record doesn't exist) a new record is created with the match value set in whatever field $columnToMatch points to and the passed value in whatever $targetFieldName points to. For consistent operation things should be arranged so that only one matching value is found in the table but as long as the table is only accessed by this function this should not be a concern! Doesn't work properly with IE11 due to incomplete implementation of flexbox on IE11 - this is likely never to be fixed as IE11 is superseded by Microsoft Edge browser for that only works on Windows 10 and above!! The main problem on IE11 is. Needs the following javascript to be inserted at the bottom of the php document where expandingTextarea() is used:
<script>
  var areas = document.querySelectorAll('.expandingArea');
  var l = areas.length;
  while (l--) {
    makeExpandingArea(areas[l]);
  }
</script>
 */
function expandingTextarea($expandingTextareaClass, $width, $tableName, $columnToMatch, $matchValue, $targetFieldName, $initialData, $editable, $create) { 
    if ($create) {
        if ($editable) {
            $tableRowColMatchCsv = $tableName.",".$columnToMatch.",".$matchValue.",".$targetFieldName; //will be passed to the javascript 'onchange' function to act as a pointer to mySql table/field to match/row/field. Also acts as unique id for text box 
            $onchangeStr = "updateTableForSubDirUse(encodeURIComponent(document.getElementById('$tableRowColMatchCsv').value), '$tableRowColMatchCsv')";
            //$onchangeStr = "alertBox()";
            $readOnly = "";
        }
        else {
            $tableRowColMatchCsv = $tableName.",".$columnToMatch.",".$matchValue.",".$targetFieldName; 
            $onchangeStr = "";
            $readOnly = "readonly";
        }
        $widthStyle = "width:".$width."px;";
        echo '
            <div class="'.$expandingTextareaClass.'" style="'.$widthStyle.'">
                <pre><span></span><br></pre>
                <p><textarea rows="4" cols="10"  style="white-space:pre-wrap; display:inline-block;" wrap="hard" id="'.$tableRowColMatchCsv.'" '.$readOnly.' onchange="'.$onchangeStr.'" >'.$initialData.'</textarea></p>
            </div>
        ';
    }
} 


/*Uses passed class, width and legend arguments to create an html table cell e.g. <td width=70px class="greyText">JobNum</td>. A true/false argument determines whether the the element will be created or not.*/
function tableCell($cellClass, $width, $legend, $create) {
    if ($create) {
        echo '
            <td    class="'.$cellClass.'"    width="'.$width.'px'.'"  >  '.$legend.'  </td>
        ';
    }
}

/* Gets data from all the fields named by the strings in array $fieldNames from the table named in $tableName from all rows where the field named in $fieldToMatch == $rowMatchValue unless $fieldToMatch and $rowMatchValue are not passed as arguments or = '', in which case all rows will be returned. Results are returned in associative arrays, where the keys are the same as the passed fieldnames array, encapsulated in an indexed array ordered by the field given in $sortByString. */
function getDbRowsWhereMatching($tableName, $fieldNames, $fieldToMatch='', $rowMatchValue='', $sortByString='') {
    global $conn;
    $whereInsert = '';
    if ($fieldToMatch) { //if a field to match is passed a WHERE clause is created, otherwise a WHERE clause will not be inserted and $rowMatchValue will be ignored
        $whereInsert = ' WHERE '.$fieldToMatch.' = "'.$rowMatchValue.'"';
    }
    if ($sortByString) {
        $sortByString = ' ORDER BY '.$sortByString;
    }
    $tableValues = array(); //initialise array.
    $fieldsCsv = implode(",", $fieldNames); //convert array of field names into comma separated list.
    try {
        //$stmt = $conn->prepare('SELECT '.$fieldsCsv.' FROM '.$tableName.' WHERE '.$fieldToMatch.' = :value');    
        //$stmt->execute(array('value' => $rowMatchValue));

        $stmt = $conn->prepare('SELECT '.$fieldsCsv.' FROM '.$tableName.$whereInsert.$sortByString);    
        $stmt->execute(array());
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { //fetch only associative version of array (not the indexed version that would normally be interleaved with the associative if PDO::FETCH_ASSOC isn't used.
                $tableValues[] = $row;
        }
    } catch(PDOException $e) {
         echo 'ERROR: ' . $e->getMessage();
      }
    return $tableValues;
}



$personHash = $_SESSION["personHash"]; //use the personHash value from SESSION variable

if (!empty($_GET['table'])) {
            $tableName = testInput($_GET['table']);
    }
    else {
        $tableName = "";
    }

$OnlyIfSuperUser = FALSE; //if set to true the access rights for the user identified by the "personHash" session variable are checked and used to enable or inhibit this page, if set to FALSE the page just works!

if ($OnlyIfSuperUser == TRUE) {
    //get person id and name from persons table given the hash code for that person - id placed in $personID variable and name placed first location (index = 'name') of associative array.
    $stmt = $conn->prepare('SELECT id FROM persons WHERE (personCurStatus="Active" AND personHash = :someValue)');    
    $stmt->execute(array('someValue' => $personHash));
    $row = $stmt->fetch();
    $personID = $row["id"];
    //get access levels for the person from accessLevels table
    $stmt = $conn->prepare('SELECT superUser FROM accessLevels WHERE (personToGiveAcsID = :someValue)');    
    $stmt->execute(array('someValue' => $personID));   
    $row = $stmt->fetch();
    $superUser = $row["superUser"];
}
else {
    $superUser = TRUE; // $OnlyIfSuperUser set to false above so force $superUser to TRUE to bypass use of access rights in accessLevels table and enable the page regardless of who is using it.
}

if ($superUser == "Yes") {

    echo '<span style="color:#000000;">'.$database.'</span><span style="color:#A0A0A0;"> - database name</span>';
    echo '<br/>';
    echo '<br/>';
    try {
        $stmt = $conn->prepare('SELECT TABLE_NAME, ENGINE FROM information_schema.TABLES where TABLE_SCHEMA = "'.$database.'" AND ENGINE IS NOT NULL');
        $stmt->execute(array());
        $rows = $stmt->fetchall(PDO::FETCH_ASSOC);  //gets all rows as an indexed array, each row is an associative array


        ?><div style="height:auto; width:180px; background-color: #FFFFFF; float:left;">
        <table border=0><?php
        foreach($rows as $data) {
            $butClass = "butActiveLinkForDispTbl";
            if ($data["TABLE_NAME"] == $tableName) {
                $butClass = "butSelectedLinkForDispTbl";
            }
            $engine = '<span style="color:#AAAAAA;">'.$data["ENGINE"].' - </span>';
            ?>
            <tr>
                <td><?php buttonLink($butClass, "", $_SERVER["PHP_SELF"], "?table=".$data["TABLE_NAME"], $engine.$data["TABLE_NAME"]); ?></td>
            </tr>
            <?php
        }
        ?></table>
        </div>
        <?php
    } catch(PDOException $e) {
         echo 'ERROR: ' . $e->getMessage();
      }
    
    if ($tableName) { //if table has been selected (not the first run of this code) display table info

    /*displays the given table description the database pointed to by $conn. */
        try {
            ?><div style="height:auto; width:520px; background-color: #FFFFFF; float:left;"><?php
            $stmt = $conn->prepare('DESCRIBE '.$tableName);
            echo '<span style="color:#000000;">'.$tableName.'</span><span style="color:#A0A0A0;"> - table description</span>';
            ?><table border=0 style="font:10px/100% Verdana;"><?php
            $stmt->execute(array());
            $row = $stmt->fetch(PDO::FETCH_ASSOC)
            ?><tr style="background:#FFFF90;"><?php
            foreach ($row as $columnName=>$data) { //for each row deals with all the columns in that row one at a time
                ?><td><?php echo "$columnName"."&nbsp;&nbsp;&nbsp;";?></td><?php
            }
            ?></tr><?php
            $stmt->execute(array());
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { //gets one row at a time
                ?><tr><?php
                foreach ($row as $columnName=>$data) { //for each row deals with all the columns in that row one at a time

                        ?><td><?php echo "$data"."&nbsp;&nbsp;&nbsp;";?></td><?php
                }
                ?></tr><?php
            }
            ?></table>
            </div><?php
        } catch(PDOException $e) {
             echo 'ERROR: ' . $e->getMessage();
          }


    ?><div style="height:auto; width:450px; background-color: #FFFFFF; float:left;"><?php

    $datafromDB = getDbRowsWhereMatching("tablesDescrps", array("tableDescription"), "tableName", $tableName);

    expandingTextarea("expandingArea", 400, "tablesDescrps", "tableName", $tableName, "tableDescription", $datafromDB[0]["tableDescription"], TRUE, TRUE);

    ?></div><?php



    /*displays the given table fields from the database pointed to by $conn. */
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    try {
        ?> <div style="height:590px; width:100%; background-color: #FFFFFF; float:left;"> <?php
        $stmt = $conn->prepare('SELECT * FROM '.$tableName);
        echo '<br/>';
        echo '<span style="color:#000000;">'.$tableName.'</span><span style="color:#A0A0A0;"> - table content</span>';
        ?><table border=0 style="table-layout:fixed;  font:10px/100% Verdana;"><?php
        $stmt->execute(array());
        $row = $stmt->fetch(PDO::FETCH_ASSOC)
        ?><tr style="background:#90FF90;"><?php
        foreach ($row as $columnName=>$data) { //for each row deals with all the columns in that row one at a time
            ?><td><?php echo "$columnName"."&nbsp;&nbsp;&nbsp;";?></td><?php
        }
        ?></tr><?php
        $stmt->execute(array());
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) { //gets one row at a time
            ?><tr><?php
            foreach ($row as $columnName=>$data) { //for each row deals with all the columns in that row one at a time
                $display = displayHidChars($data);
                    ?><td style="max-width:250px;"><?php echo "$display"."&nbsp;&nbsp;&nbsp;";?></td><?php
            }
            ?></tr><?php
        }
        ?></table>
        </div>
        <?php
    } catch(PDOException $e) {
         echo 'ERROR: ' . $e->getMessage();
      }
    }

$timeEnd = microtime(true); //use microtime to time how long this page takes to execute
$timeTaken = $timeEnd - $timeStart;
print_r("Time Taken = ".$timeTaken." secs");
}
else {
    echo "You don't have permissions for this action!";
}

?>
<script>
  var areas = document.querySelectorAll('.expandingArea');
  var l = areas.length;
  while (l--) {
    makeExpandingArea(areas[l]);
  }
</script>


</body>




