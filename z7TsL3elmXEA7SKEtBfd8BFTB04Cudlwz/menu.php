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
	?>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Upload Scans"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> Upload Scans</button>
		<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu";?>><i class="fas fa-check"></i> Records</button>

		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Add User"]."-FromMainMenu";?>><i class="fas fa-user-plus"></i> Add User</button>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Show References"]."-FromMainMenu";?>><i class="fas fa-check"></i> Show Refs</button>

		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["New Password"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> New Password</button>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Edit Flex"]."-FromMainMenu";?>><i class="fas fa-check"></i> Edit Flex</button>

		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Test"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> TEST!</button>
		<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Logout"]."-FromMainMenu";?>><i class="fas fa-sign-out-alt"></i> Logout</button>
	<?php
	}
	else {
	?>
	<button class="btnSelected" type="submit" name="command" value=<?php echo $menuRandomsArray["Show Records For Full Year"]."-FromMainMenu";?>><i class="fas fa-check"></i> Records</button>
	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["New Password"]."-FromMainMenu";?>><i class="fas fa-arrow-up"></i> New Password</button>
	<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Logout"]."-FromMainMenu";?>><i class="fas fa-sign-out-alt"></i> Logout</button>
	<?php
	}
	?>

</div>

</form>


