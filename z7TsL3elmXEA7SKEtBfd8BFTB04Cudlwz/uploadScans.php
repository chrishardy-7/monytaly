<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}



//PDFMerger version is from: https://github.com/libremente/PDFMerger - it causes some deprecated notices because it was written/tested for PHP 5.x and not PHP 7.x - see error settings in indexPage.php
include_once("./".$sdir."PDFMerger/PDFMerger.php"); //to facilitate PDF Merger use in doc upload and conversion - done on this page because nothing else uses it (at the moment)
require_once("./".$sdir."PDFMerger/tcpdf/tcpdf.php"); //additional to cure error in PDFMerger - see: https://stackoverflow.com/questions/41298752/fatal-error-class-tcpdf-not-found-in-another-path

$nameOfThisPage = "Upload Scans";
//include_once("./".$sdir."createMenuRndms.php");
include_once("./".$sdir."head.php");

$startFreshPage = new persistVar("startFreshPage", FALSE); //trys to create and initialise a persistant variable called "startFreshPage". If it already exists it will neither be recreated nor initialised
$startFreshPage->set(FALSE); //set to FALSE so that the next call of showRecsForFullYr.php from the main menu button will not clear filters and set buttons etc. (which is the usual behaviour)

$nonVolatileArray["onTheHoofRandsAry"] = array(); //clear the array so any old plain-random pairs are deleted.


//NEED TO INSTALL IMAGEMAGICK FOR PHP: sudo apt-get install php-imagick (to check installation: php -m | grep imagick)
/* TO FACILITATE REASONABLE NUMBER AND SIZE OF FILES TO BE UPLOADABLE, INCREASE VALUES IN /etc/php/7.0/apache2/php.ini:
post_max_size: 50M
upload_max_filesize: 5M
max_file_uploads: 50

The files upload to month folders within upload folder /var/www/cuibhrig.com/accountsccc/uploads on the server. This upload folder is synced using Syncthing to a hidden folder "/media/data/Data/0_acountscccSncdDocs HiddenFoldWithin/.accountscccSyncdDocs" which in turn is mounted read only on dir /home/chris/accountscccSncdDocs to allow no destructive access for copying back to the server in the event of loss or damage to the files on the server or recreation on a new server. This read only mounting is performed at bootup by a line: mount --bind -r "/media/data/Data/0_acountscccSncdDocs HiddenFoldWithin/.accountscccSyncdDocs" /home/chris/accountscccSncdDocs within the system /etc/rc.local script. */


/* added margin-top:13.02vw; to .JsDatePickBox{} (first line in jsDatePick_ltr.min.css) to make calendar display below date box, in fixed relation to it. Copyright 2010 Itamar Arjuan jsDatePick is distributed under the terms of the GNU General Public License. */

?>
<script type="text/javascript">
	window.onload = function() {
		new JsDatePick({
			useMode:2,
			target:"dateReq",
			dateFormat:"%Y-%m-%d",
			imgPath:"./img/"
		});
	};
</script>
<?php

//gather incoming data and controls
$fileNameOfMultiRecordDocToSwap = "";
if (!empty($nonVolatileArray["docNameNumStr"])) {
	$docFileNameToSwap = $nonVolatileArray["docNameNumStr"];
}
$subSubCmndStr = getPlain($subSubCommand);
$swapFilenameNotification = $subSubCmndStr;
$idR = sanPost("storeSelectedRecordIdR");
$replaceDocStatus = getRand($subSubCmndStr);


$scanNameDate = sanPost('dateStamp');


// PHP ONLY SECTION THAT UPLOADS THE JPEGs OR PDFs 
$uploadBtnNamesStr = array_search($subCommand, $uploadBtnsRndmsArray);
if(($uploadBtnNamesStr == "Individual pdfs") or ($uploadBtnNamesStr == "Multipage pdf")) { //data is result of a form post from this page
	$makeMultipagePdf = FALSE;
    if ($uploadBtnNamesStr == "Multipage pdf") {
        $makeMultipagePdf = TRUE;
    }

	//SECTION TO UPLOAD ALL SELECTED FILES TO THE SERVER
	$errorMessage = "";
	try {
		$filesUploaded = sanFile('filesToUpload'); //sanitising specifically for $_FILES arrays.

        //UPLOAD THE SELECTED FILES $_fileUploadsDir is defined in /var/monytaly.uk.globals/xxxxxxx/globals.php
	    $fileUploadReportArray = uploadJpgFilesToSnglPdfs($filesUploaded, 10000000, $_fileUploadsDir, $scanNameDate); //upload doc scan files and produce report array of details. 

	    if ($makeMultipagePdf) {
	    	$mergeReportAry = mergePdfs($fileUploadReportArray, $_fileUploadsDir); //merge already uploaded named pdfs into a single pdf, then delete these uploaded source pdfs and rename the merged one to the lowest number
	    }

	} catch(PDOException $e) { //CLOSE OF Try STATEMENT THAT APPEARS NEAR BEGINNING OF THIS PHP FILE
	    $errorMessage = $e->getMessage();
	}




	//UPDATE THE allRecords TABLE WITH THE UPLOADED FILE(S) DETAILS BY CREATING A NEW BLANK RECORD FOR EACH FILE OR SWAPPING FILE NAMES FOR EXISTING RECORD(S)
	if ($subSubCmndStr == "Swap Doc") { //swap document for a single transaction - uses report array from mergePdfs() function to update allRecords table as maybe a merged doc
		$swapFilenameNotification = updateDocFilenameInOneTrans($mergeReportAry, $idR);
	}
	elseif($subSubCmndStr == "Swap Group Doc") { //swap document for a multiple transactions that share the same document - uses report array from mergePdfs() function to update allRecords table as maybe a merged doc
		$swapFilenameNotification = updateDocFilenameInSeveralTrans($mergeReportAry, $docFileNameToSwap);
	}
	else { //create new blank record(s) for files that have been uploaded in the code section above
		if ($makeMultipagePdf) { //one multi-page pdf so use report array from mergePdfs() function to update allRecords table
			updateAllRecsWithNewFileInfo($mergeReportAry);
		}
		else { //one or several single pdfs so use report array from uploadJpgFilesToSnglPdfs() function to update allRecords table
			pr("3 On upload page ");
			updateAllRecsWithNewFileInfo($fileUploadReportArray);
		}
	}

	
    
	
}




$fileAcceptExts = fileUploadExtensions("getString"); //get allowed file extensions as a string, e.g. ".doc, .txt, .jpg" .
if (!$scanNameDate) {
	$scanNameDate = date("Y-m-d");
}
$uploadBtnsNames = array("Individual pdfs", "Multipage pdf");
$uploadBtnsRndmsArray = createKeysAndRandomsArray($uploadBtnsNames, $_cmndRndmLngth, $uniqnsChkAryForRndms); //creates new button randoms that will be saved to sessionArrays
		


?>

<div style="float:left;">
	<?php
	include_once("./".$sdir."menu.php");
	?>

	<div id="uploadDetails" class="scansForUpldContainer">
		<div style="float:left;"  >
			<form id="selScansForm" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
				<?php formValHolder("storeSelectedRecordIdR", $idR); ?>
				<br>
				<input type="hidden" name="MAX_FILE_SIZE" value="10485760" /> <!-- set max file size in html to 10MB, helps against failure and lockup if too large a file is attempted. Size is for each file. -->
				<input type="file" style="width:13.02vw;" name="filesToUpload[]" id="filesToUpload"  multiple accept="<?php echo $fileAcceptExts; ?>"> Scans Upload? (max size 10MB)</input>
				<br>
				<br>
				<input class="loginTextBox" type="text" name="dateStamp" value=<?php echo $scanNameDate;?> id="dateReq" onkeydown="testForEnter();" />
			    <label for="dateReq"> Uploaded File Datename (YYYY-MM-DD) - defaults to today</label> 
				<br>
				<br>

				<!-- subSubCommand is used with this button to get additional instructions back to this page relating to whether a single or group document is to be swapped if swap is selected -->
				<button class="btn" id="Individuals But" type="submit" name="command" value=<?php echo $menuRandomsArray[$nameOfThisPage]."-".$uploadBtnsRndmsArray["Individual pdfs"]."-".$replaceDocStatus;?>><i class="fas fa-minus"></i> Individual pdfs</button>

				<button class="btn" id="Combination But" hidden type="submit" name="command" value=<?php echo $menuRandomsArray[$nameOfThisPage]."-".$uploadBtnsRndmsArray["Multipage pdf"]."-".$replaceDocStatus;?>><i class="fas fa-list-ol"></i> Individual or Multipage pdf</button>

				<!-- subSubCommand is used with this button to get additional instructions back to this page relating to whether a single or group document is to be swapped if swap is selected -->
				<button class="btn" id="Multi But" style="margin-left:100px" type="submit" name="command" value=<?php echo $menuRandomsArray[$nameOfThisPage]."-".$uploadBtnsRndmsArray["Multipage pdf"]."-".$replaceDocStatus;?>><i class="fas fa-list-ol"></i> Multipage pdf</button>

			</form>
		</div>
		<div id="swapDocDiv" style="float:right; color:#FFFF00; background:#FF6060; display:none; padding-right: 2.604vw; padding-top: 8.3328vw; font-size: 2.604vw; text-align: right;">
			<?php echo $swapFilenameNotification."!"; ?>
		</div>
	</div>


	<div class="scanListContainer">
		<?php

//pr("merge report<br>");
//pr($mergeReportAry);
//pr("<br>");
//pr("singles upload report<br>");
//pr($fileUploadReportArray);
//pr("<br>");

		if (!empty($fileUploadReportArray)) {
			?>
			<table border=0 style="margin-left:2.604vw;">
			<?php 

			    tableStartRow("tableForFileUploadHeading", 	"", "", TRUE); //header row for column names
			    	tableCell("", 270, 		"Source file", 	TRUE);
			        tableCell("", 250, 		"Size", 		TRUE);
			        tableCell("", 150, 		"Destination",	TRUE);
			        tableCell("", 100, 		"Size", 		TRUE);
			        tableCell("", 80, 		"Pages", 		TRUE);
			        tableCell("", 350, 		"Details", 		TRUE);
			    tableEndRow(TRUE);


			if (!empty($mergeReportAry) && (1 < count($fileUploadReportArray))) { //merged pdf AND more than 1 source so display the row for the output file and then the abridged details of source files
				
				foreach ($mergeReportAry as $mergeMsg) {
					if ($mergeMsg[6]) { //no errors - display in black...
						tableStartRow("tableForFileUploadSuccess", "", "", TRUE);
			        		tableCell("", 270,  $mergeMsg[0], TRUE);
			                tableCell("", 250, 	$mergeMsg[1], TRUE);
			                tableCell("", 150,  $mergeMsg[2], TRUE);
			                tableCell("", 100, 	$mergeMsg[3], TRUE);
			                tableCell("", 80, 	$mergeMsg[4], TRUE);
			                tableCell("", 350, 	$mergeMsg[5], TRUE);
		                tableEndRow(TRUE);
	            	}
	            	else { //errors so display in red...
		            	tableStartRow("tableForFileUploadFail", "", "", TRUE);
			        		tableCell("", 270,  $mergeMsg[0], TRUE);
			                tableCell("", 250, 	$mergeMsg[1], TRUE);
			                tableCell("", 150,  $mergeMsg[2], TRUE);
			                tableCell("", 100, 	$mergeMsg[3], TRUE);
			                tableCell("", 80, 	$mergeMsg[4], TRUE);
			                tableCell("", 350, 	$mergeMsg[5], TRUE);
		                tableEndRow(TRUE);
	            	}
            	}

            	foreach ($fileUploadReportArray as $indvdMsg) {
					if ($indvdMsg[6]) { //no errors - display in black...
						tableStartRow("tableForFileUploadSuccess", "", "", TRUE);
			        		tableCell("", 270,  $indvdMsg[0], TRUE);
			                tableCell("", 250, 	$indvdMsg[1], TRUE);
			                tableCell("", 150,  "", 		  TRUE);
			                tableCell("", 100, 	"", 		  TRUE);
			                tableCell("", 80, 	$indvdMsg[4], TRUE);
			                tableCell("", 350, 	$indvdMsg[5], TRUE);
		                tableEndRow(TRUE);
	            	}
	            	else { //errors so display in red...
		            	tableStartRow("tableForFileUploadFail", "", "", TRUE);
			        		tableCell("", 270,  $indvdMsg[0], TRUE);
			                tableCell("", 250, 	$indvdMsg[1], TRUE);
			                tableCell("", 150,  "", 		  TRUE);
			                tableCell("", 100, 	"", 		  TRUE);
			                tableCell("", 80, 	$indvdMsg[4], TRUE);
			                tableCell("", 350, 	$indvdMsg[5], TRUE);
		                tableEndRow(TRUE);
	            	}
            	}
				
			}
			else { //full display of single pdf file upload details
				foreach ($fileUploadReportArray as $indvdMsg) {
					if ($indvdMsg[6]) { //no errors - display in black...
						tableStartRow("tableForFileUploadSuccess", "", "", TRUE);
			        		tableCell("", 270,  $indvdMsg[0], TRUE);
			                tableCell("", 250, 	$indvdMsg[1], TRUE);
			                tableCell("", 150,  $indvdMsg[2], TRUE);
			                tableCell("", 100, 	$indvdMsg[3], TRUE);
			                tableCell("", 80, 	$indvdMsg[4], TRUE);
			                tableCell("", 350, 	$indvdMsg[5], TRUE);
		                tableEndRow(TRUE);
	            	}
	            	else { //errors so display in red...
		            	tableStartRow("tableForFileUploadFail", "", "", TRUE);
			        		tableCell("", 270,  $indvdMsg[0], TRUE);
			                tableCell("", 250, 	$indvdMsg[1], TRUE);
			                tableCell("", 150,  $indvdMsg[2], TRUE);
			                tableCell("", 100, 	$indvdMsg[3], TRUE);
			                tableCell("", 80, 	$indvdMsg[4], TRUE);
			                tableCell("", 350, 	$indvdMsg[5], TRUE);
		                tableEndRow(TRUE);
	            	}
            	}

			}
		    
		?>
		</table>
		<?php

		}

		?>
	</div>

</div>

<iframe id="pdfIframe" name="docIframe" class="docDisplayIframeRecsFullYr" style="background:#AAFFAA;" >
    <p>Your browser does not support iframes.</p>
</iframe>

<script>
<?php
if (($subSubCmndStr == "Swap Doc") || ($subSubCmndStr == "Swap Group Doc")) {
?>
	document.getElementById("uploadDetails").className = "scansForUpldContainerSwapDoc";
	document.getElementById("swapDocDiv").style.display = "inline";
	document.getElementById("Individuals But").style.display = "none";
	document.getElementById("Multi But").style.display = "none";
	document.getElementById("Combination But").style.display = "inline";
<?php
}
?>
var docFilename = "../<?php echo $dir?>obscureTest.php";
document.getElementById("pdfIframe").src  = "./web/viewer.html?file="+docFilename+"#page="+pageNum+"&zoom=100";
</script>

<?php
include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>
