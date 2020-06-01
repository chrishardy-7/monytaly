<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

include_once("./".$sdir."head.php");

?>
<center class="message"> <?php echo $message;?> </center>
<form id="loginForm" class="loginForm" ACTION="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" METHOD="post" enctype="multipart/form-data"  onsubmit="">
	</br>
	<center><input class="loginTextBox" type="text" name="username" placeholder="User Name" id="username" onkeydown="testForEnter();" /></center>
	
	<p></p>
	<center><input class="loginTextBox" type="password" name="password" placeholder="Password" id="passwd" onkeydown="testForEnter();" /></center>
	
	<p></p>
	<center><button class="btn" id="loginBut" type="submit" form="loginForm" name="loginBtn" value="login"><i class="fas fa-unlock"></i> Login</button></center>
</form>


<script>
var input = document.getElementById("loginForm");
input.addEventListener("keyup", function(event) {
  if (event.keyCode === 13) {
   event.preventDefault();
   document.getElementById("loginBut").click();

  }
});
</script>

<?php

include_once("./".$sdir."tail.php");
?>
