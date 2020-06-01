<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$nameOfThisPage = "Add Items";

include_once("./".$sdir."head.php");

if($callingPage == "Add Items") { //if this page has been called from itself write the new item to its database table and go back to the original calling page
	if (sanPost("newItem")) {
		addNewItem($nonVolatileArray["tableNameForAddItems"], $nonVolatileArray["fieldNameForAddItems"], sanPost("newItem"));
	}
	postData(htmlspecialchars($_SERVER["PHP_SELF"]), array("command" => $menuRandomsArray[$nonVolatileArray["callingPageForAddItems"]], "sessionCommitRnd" => $recoveredSessionAryCommitRnd));
}
else { //form to enter new item and then recall this page when the fact it is called from itself will be detected and teh routine above will write the item to the correct table

	$nonVolatileArray["tableNameForAddItems"] = array_search(sanPost("tableName"), $nonVolatileArray["genrlAryRndms"]);
	$nonVolatileArray["fieldNameForAddItems"] = array_search(sanPost("fieldName"), $nonVolatileArray["genrlAryRndms"]);
	$nonVolatileArray["callingPageForAddItems"] = $callingPage; 
	

	?>
	<script>
		var docFilename = "../<?php echo $dir?>obscureTest.php";
		var pageNum = 1;
		function loadIframeDoc() {	//in response to changes in display size from different devices gets width of container div and reloads the document into the iframe using a suitable zoom %
			var outerContainerWidth = window.getComputedStyle( document.getElementById("container"), null).getPropertyValue("width");
			var zoom = "20";
			switch (outerContainerWidth) {
				case "1010px": //iPad 4 landscape
					zoom = "50";
					break;
				case "1350px": //Old laptop screen (1366 x 768)
					zoom = "70";
					break;
				case "1912px": //HD Screen (1920 x 1080)
					zoom = "100";
					break;
			}
			document.getElementById("pdfIframe").src  = "./web/viewer.html?file="+docFilename+"#page="+pageNum+"&zoom="+zoom;
		}
		var myEfficientFn = debounce(function() { 
			loadIframeDoc();
		}, 250);
		$(document).ready(function() { loadIframeDoc(); });
		window.addEventListener('resize', myEfficientFn);
	</script>
	 
	<div style="background-color: #FFFFFF; float:left;">
		<?php
		include_once("./".$sdir."menu.php");
		?>
		<form id="addDocTagForm" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
		<div id="docEdit" class="docEditContainer">
			<input hidden type="input" name="callingPage" value="<?php echo $nameOfThisPage;?>"></input> <!--standard way of passing the name of this php page to the called page-->
			<?php
			$itemsAry = getDbColumn($nonVolatileArray["tableNameForAddItems"], $nonVolatileArray["fieldNameForAddItems"]);
			$itemsAryJson = json_encode(array_values($itemsAry));// encode array for passing as argument to javascript function
			?> 
			<script> 
			var itemsAryJson = <?php echo $itemsAryJson;?>;  //MUST BE CONVERTED TO JS VARIABLE WITH ECHO STATEMENT - WON'T WORK WITH ECHO IN JS FUNCTION ARG !!
			</script> 

			<p>
			<input class="addTextBoxNorm"  type="text" name="newItem" onkeyup="checkForTextInAry(itemsAryJson, this.value, this.id, 'addTextBoxNorm', 'addTextBoxHighlight', 'newItemSubmit', 'newItem')" placeholder="New Document Tag" id="newTag" onkeydown="testForEnter();" />
			<label class="addTextBoxLabel" id="newTaglbl" for="newTag">Already Exists!</label>
			</p>
			<br>
			
			<?PHP
			//BUTTON TOGGLE PANEL TO DISPLAY EXISTING DOC TAGS (NON CLICKABLE)
			buttonsPanel("namesAddPanelDiv", "namesSelPanelAddBut", "", "", "NameSelBtn", "NameSelBtn", TRUE, "Y7f3d", "doesntMatter", $itemsAry, array(), TRUE, TRUE);
			?>
			
			
		</div>
		<div style="clear:both; width:1090px; height:40px; background-color: #AAAAFF;">	
			<?php namedValHolder("sessionCommitRnd", $recoveredSessionAryCommitRnd); ?>
			<button class="subMenuBtn" id="newItemSubmit" type="submit" name="command" value=<?php echo $menuRandomsArray["Add Items"]."-".$menuRandomsArray["Add Items"];?>><i class="fas fa-save"></i> Add New Item</button>
		</div>
		</form>
	</div>

	<iframe id="pdfIframe" name="docIframe" class="docDisplayIframeRecsFullYr" >
	    <p>Your browser does not support iframes.</p>
	</iframe>

	<?php
}

include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>

