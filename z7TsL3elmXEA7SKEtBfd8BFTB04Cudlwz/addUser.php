<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//include_once("./".$sdir."createMenuRndms.php");

include_once("./".$sdir."head.php");
include_once("./".$sdir."menu.php");



$newUsername = sanPost('newUsername');
$newPassword = sanPost('newPassword');

if (($newUsername) && ($newPassword)) { //if newusername and newpassword exist
	createNewUser($newUsername, $newPassword, randomAlphaString($_customSessionCookieLength), FALSE);
}



print_r("</br>");
?>
<div style="background-color: #FFFFFF; float:left;">
	<div class="newUserPwContainer">
		<div style="width:23.436vw; float:left; margin-left:1.0416vw">
			<form id="newUserForm" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data"  onsubmit="">
				</br>
				<span><input class="loginTextBox" type="text" name="newUsername" placeholder="Username" id="newUsername" onkeydown="testForEnter();" /></span>
				
				<p></p>
				<span><input class="loginTextBox" type="text" name="newPassword" placeholder="Temporary Password" id="newPassword" onkeydown="testForEnter();" /></span>
				
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<button class="btn" type="submit" name="command" value=<?php echo $menuRandomsArray["Add User"];?>><i class="fas fa-sign-out-alt"></i>Create New User</button>
			</form>
		</div>
	</div>
</div>
<?php

include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>
