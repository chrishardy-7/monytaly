<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//include_once("./".$sdir."createMenuRndms.php");

include_once("./".$sdir."head.php");




print_r("</br>");
?>
<div style="background-color: #FFFFFF; float:left;">
	<div class="newUserPwContainer">
		<div style="width:550px; float:left; margin-left:20px ">
			<form id="passwordReset" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data"  onsubmit="">
				</br>
				<span><input class="loginTextBox" type="password" name="oldPassword" placeholder="Temporary Password" id="oldPassword" onkeydown="testForEnter();" /></span>
				</br></br></br>
				<p></p>
				<span><input class="loginTextBox" type="password" name="newPassword" placeholder="New Password" id="newPassword" onkeydown="testForEnter();" /></span>

				<p></p>
				<span><input class="loginTextBox" type="password" name="newPasswordRepeat" placeholder="New Password (repeat)" id="newPasswordRepeat" onkeydown="testForEnter();" /></span>
				
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<span><button class="btn" type="submit" form="passwordReset" name="resetPWbuttonBtn" value="login"><i class="fas fa-unlock"></i> Change Password</button></span>
			</form>
		</div>
		<div style="width:500px; float:left;">
			</br>
			<p style="font-weight:bold">Passwords must have at least 8 characters and must have at least:</p>
			<li>one UPPER case letter</li>
			<li>one lower case letter</li>
			<li>one non-alphanumeric character   #  {  $  % * &nbsp;&nbsp;&nbsp; etc.</li>
			<li>one number 0 – 9</li>
			</br>
			<p>A monitor resolution of at least HD (1080 x 1920) will be needed.</p>
		</div>
	</div>
</div>
<?php
include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>
