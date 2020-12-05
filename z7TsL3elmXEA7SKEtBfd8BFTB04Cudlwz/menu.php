<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}



?>


<form id="menu" class="form" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data">
	<?php
		if (!isset($recoveredSessionAryCommitRnd)) {
			$recoveredSessionAryCommitRnd = "9405f94w3ufu94fn"; //set to random random first time as menu will be called directly without going through saving of session arrays etc.
		}
	 namedValHolder("sessionCommitRnd", $recoveredSessionAryCommitRnd); 
	?>

<div class="menuBar">
	<?php
	if ($allowedToEdit) {
		//$mainMenu->drawBut("Test",		"Show Records For Full Year", 		"fas fa-plus-square", 	FALSE);
		//$mainMenu->drawBut("Second",	"Show Records For Full Year", 		"fas fa-check", 		FALSE);
		//$mainMenu->drawBut("Third",		"Test", 							"fas fa-arrow-up", 		FALSE);

	?>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Upload Scans"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> Upload Scans</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu";?>><i class="fas fa-check"></i> Records</button>

		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Eileen1920";?>><i class="fas fa-check"></i> Restricted 2019-20</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Unrestricted1920";?>><i class="fas fa-check"></i> Unrestricted 2019-20</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Bank1920";?>><i class="fas fa-check"></i> Bank 2019-20</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Eileen2021";?>><i class="fas fa-check"></i> Restricted 2020-21</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-EileenReclaim";?>><i class="fas fa-check"></i> Oct Reclaim</button>

	<!--	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Add User"]."-FromMainMenu";?>><i class="fas fa-user-plus"></i> Add User</button>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show References"]."-FromMainMenu";?>><i class="fas fa-check"></i> Refs</button> -->

		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["New Password"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> New Password</button>
	<!--	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Edit Flex"]."-FromMainMenu";?>><i class="fas fa-check"></i> Flex</button> -->

		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Test"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> TEST!</button>
	<!--	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Logout"]."-FromMainMenu";?>><i class="fas fa-sign-out-alt"></i> Logout</button>  -->
	<?php
	}
	else {
	?>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu";?>><i class="fas fa-check"></i> Records</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Eileen1920";?>><i class="fas fa-check"></i> Restricted 2019-20</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Unrestricted1920";?>><i class="fas fa-check"></i> Unrestricted 2019-20</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Bank1920";?>><i class="fas fa-check"></i> Bank 2019-20</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Eileen2021";?>><i class="fas fa-check"></i> Restricted 2020-21</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-EileenReclaim";?>><i class="fas fa-check"></i> Oct Reclaim</button>
	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["New Password"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> New Password</button>
	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Logout"]."-FromMainMenu";?>><i class="fas fa-sign-out-alt"></i> Logout</button>
	<?php
	}
	?>

</div>

</form>


