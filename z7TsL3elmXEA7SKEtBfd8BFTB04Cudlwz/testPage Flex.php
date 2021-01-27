<?php
if (empty($_calledFromIndexPage)) { //prevents someone trying to open this page directly.
	print_r("Access Denied!");
	exit("");
}

$nameOfThisPage = "Test";          //NEEDS PX VALUES CONVERTED TO VW  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!

//include_once("./".$sdir."createMenuRndms.php");

include_once("./".$sdir."head.php");
include_once("./".$sdir."menu.php");


print_r($message); //USING TEST MESSAGE TEMPORARILY EVEN THOUGH THIS IS NOT AN EXIT PAGE

// float:left;

?>
<div style="display:flex; flex-direction:column; height: 850px; width: 1200px; background-color: #8080AA;">


	<div id="a" style="height:150px; width:1200px; background-color:#FF0000;" onclick="document.getElementById('a').style.height='150px';">
	</div>

	<div id="b" style="flex-grow:1; height:150px; width:1200px; background-color:#00FF00; font-size: 24px">
		Once when a lion, the king of the jungle, was asleep, a little mouse began running up and down on him. This soon awakened the lion, who placed his huge paw on the mouse, and opened his big jaws to swallow him."Pardon, O King!" cried the little mouse. "Forgive me this time. I shall never repeat it and I shall never forget your kindness. And who knows, I may be able to do you a good turn one of these days!” The lion was so tickled by the idea of the mouse being able to help him that he lifted his paw and let him go.Once when a lion, the king of the jungle, was asleep, a little mouse began running up and down on him. This soon awakened the lion, who placed his huge paw on the mouse, and opened his big jaws to swallow him."Pardon, O King!" cried the little mouse. "Forgive me this time. I shall never repeat it and I shall never forget your kindness. And who knows, I may be able to do you a good turn one of these days!” The lion was so tickled by the idea of the mouse being able to help him that he lifted his paw and let him go.Once when a lion, the king of the jungle, was asleep, a little mouse began running up and down on him. This soon awakened the lion, who placed his huge paw on the mouse, and opened his big jaws to swallow him."Pardon, O King!" cried the little mouse. "Forgive me this time. I shall never repeat it and I shall never forget your kindness. And who knows, I may be able to do you a good turn one of these days!” The lion was so tickled by the idea of the mouse being able to help him that he lifted his paw and let him go.Once when a lion, the king of the jungle, was asleep, a little mouse began running up and down on him.
	</div>

	<div id="c" style=" height:150px; width:1200px; background-color:#0000FF;" onclick="document.getElementById('a').style.height='200px';">
	</div>
	


</div>
<?php

include_once("./".$sdir."saveSession.php");
include_once("./".$sdir."tail.php");
?>
