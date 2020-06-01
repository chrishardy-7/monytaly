<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

//include_once("./".$sdir."createMenuRndms.php");

include_once("./".$sdir."head.php");
include_once("./".$sdir."menu.php");
include_once("./".$sdir."saveSession.php");
print_r($message);

?>
<div style="height: 300px; width: 500px; background-color: #AAAAFF; float:left;">
	<div style="margin: 100px">
		<H1>Welcome!</H1>
	</div>
</div>


<?php
include_once("./".$sdir."tail.php");
?>