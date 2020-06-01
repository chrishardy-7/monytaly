<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$nameOfThisPage = "Test";

//include_once("./".$sdir."createMenuRndms.php");

include_once("./".$sdir."head.php");
include_once("./".$sdir."menu.php");


print_r($message); //USING TEST MESSAGE TEMPORARILY EVEN THOUGH THIS IS NOT AN EXIT PAGE

// float:left;

?>

<div style="float:left;  width: 1200px; height: 800px; background-color: #AAFFAA; font-size: 20px;">

	<div style="display:flex; flex-direction:row; align-items: stretch; background-color: #8080AA; font-size: 20px;">


		<div id="a" style="padding: 5px; width:110px; background-color:#FF0000;">
			Test Test Test Test
		</div>

		<div id="b" style="padding: 5px; width:20%; background-color:#FF00EE;">
			Words Words
		</div>

		<div id="c" contentEditable="true" style="padding: 5px; width:20%; background-color:#00AAEE;">
			More More More More More More More More More
		</div>	

	</div>

</div>

<script>
	document.getElementById("b").innerText = document.getElementById("c").innerText;
	document.getElementById("c").innerText = "Less Less Less Less Less Less Less Less Less";
</script>


<?php

include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>
