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
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Upload Scans"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> Docs</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu";?>><i class="fas fa-stream"></i> Records</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Reclaims";?>><i class="fas fa-stream"></i> Reclaims</button>

		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Restricted";?>><i class="fas fa-lock"></i> Restricted</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-RestrictedMatchBudgets";?>><i class="fas fa-lock"></i> Restricted Budg Match</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Unrestricted";?>><i class="fas fa-unlock"></i> Unrestricted</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-RBS";?>><i class="fas fa-university"></i> RBS</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Unity";?>><i class="fas fa-university"></i> Unity</button>
		<div style="width:0.7vw; height:1vw; display:inline-block;"></div>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Add User"]."-FromMainMenu";?>><i class="fas fa-user-plus"></i> User</button>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show References"]."-FromMainMenu";?>><i class="fas fa-check"></i> Refs</button>

		<!--<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["New Password"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> New Password</button> -->
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Edit Flex"]."-FromMainMenu";?>><i class="fas fa-check"></i> Flex</button>

		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Test"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> TEST!</button>
		<div style="width:0.25vw; height:1vw; display:inline-block;"></div>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Help Page"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> Help</button>
	<!--	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Logout"]."-FromMainMenu";?>><i class="fas fa-sign-out-alt"></i> Logout</button>  -->
	<?php
	}
	else {
	?>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-EileenReclaim1";?>><i class="fas fa-check"></i> Jan Reclaim</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu";?>><i class="fas fa-stream"></i> Records</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Reclaims";?>><i class="fas fa-stream"></i> Reclaims</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Restricted";?>><i class="fas fa-lock"></i> Restricted</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-RestrictedMatchBudgets";?>><i class="fas fa-lock"></i> Restricted Budg Match</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Unrestricted";?>><i class="fas fa-unlock"></i> Unrestricted</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-RBS";?>><i class="fas fa-university"></i> RBS</button>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu-Unity";?>><i class="fas fa-university"></i> Unity</button>
	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["New Password"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> Change PW</button>
	<div style="width:2vw; height:1vw; display:inline-block;"></div>
	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Help Page"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> Help</button>
	<div style="width:0.8vw; height:1vw; display:inline-block;"></div>
	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Logout"]."-FromMainMenu";?>><i class="fas fa-sign-out-alt"></i> Logout</button>
	<?php
	}
	?>

</div>

</form>


