
<!DOCTYPE html PUBLIC>

<head> 
	<title>CCC Accounts</title> 

	<meta name="viewport" content="width=device-width, initial-scale = 0.7, maximum-scale = 1.0, user-scalable=no">
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<meta HTTP-EQUIV="CACHE-CONTROL" CONTENT="no-store" />
	<META HTTP-EQUIV="PRAGMA" CONTENT="NO-CACHE">
	<META HTTP-EQUIV="EXPIRES" CONTENT="0"> <!-- Expires immediately -->
	<meta http-equiv="X-UA-Compatible" content="IE=9">

	<link rel="stylesheet" href="<?php echo $dir?>css/jquery-ui-1.9.2.custom.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo $dir?>css/jsDatePick_ltr.min.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo $dir?>css/expandText.css?version=<?php echo (string)time(TRUE); ?>" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo $dir?>css/jquery.jqplot.css" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo $dir?>css/accCcc.css?version=<?php echo (string)time(TRUE); ?>" type="text/css" media="all" />
	<link rel="stylesheet" href="<?php echo $dir?>css/all.css" type="text/css" media="all" />

	<script type="text/javascript" src="<?php echo $dir?>scripts/jquery-1.8.3.min.js"></script> 
	<script type="text/javascript" src="<?php echo $dir?>scripts/jquery-ui-1.9.2.custom.min.js"></script>
	<script type="text/javascript" src="<?php echo $dir?>scripts/jsDatePick.min.1.3.js"></script>
	<script type="text/javascript" src="<?php echo $dir?>scripts/JSforAccCcc.js?version=<?php echo (string)time(TRUE); ?>"></script>
	<script type="text/javascript" src="<?php echo $dir?>scripts/jquery.jqplot.min.js"></script>
	<script type="text/javascript" src="<?php echo $dir?>scripts/jqplot.pieRenderer.min.js"></script>
	<script type="text/javascript" src="<?php echo $dir?>scripts/pdfDisplay.js"></script>
	<script src='https://cdn.jsdelivr.net/npm/big.js@6.0.0/big.min.js'></script>

	<script>
	$(function() {
	    $( document ).tooltip();
	});

	$( window ).on( "load", function() {
		//alert("Browser Display Width = "+$(window).width()+" Pixels");
		var scaleFactor = $(window).width() / 1920;

		//$('#mainBody').css({ transform: 'translate(-50%, -50%)', 'scale('+scaleFactor+')' });
		//$('#mainBody').css('mozTransform','origin(0px 0px)'); //doesn't seem to work!
		//$('#mainBody').css('MozTransform','scale(' + scaleFactor + ')');
		

	})

	//document.getElementById("mainBody").style.transform = scale(0.7);



	</script>

	<link href="data:image/x-icon;base64,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" rel="icon" type="image/x-icon" />

</head> 

<body id="mainBody" style="background-color:#FFFFFF; " onkeypress="keyPressDetect(event);" onkeyup="keyUpDetect(event);" >

	<div id="container" class="mainContainer"> <!--Overall container div for the whole web page -->

