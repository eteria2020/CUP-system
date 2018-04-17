<?php
if(isset($_GET['r'])&&($_GET['r']=="ok")){
	exec('sudo pm2 restart webservices');
	header("refresh:5;url=system.html");
	echo '<link rel="stylesheet" type="text/css" href="bootstrap/css/bootstrap.min.css" />
    <link href="css/main.css" rel="stylesheet">
    <link href="css/font-style.css" rel="stylesheet">
    <link href="css/flexslider.css" rel="stylesheet">
    <link href="css/leds.css" rel="stylesheet">
    <script type="text/javascript" src="js/jquery-1.10.2.min.js"></script>
	
	<!-- Placed at the end of the document so the pages load faster -->
    <script type="text/javascript" src="js/bootstrap.js"></script>
    <script type="text/javascript" src="js/jquery.peity.min.js"></script>
    <script type="text/javascript" src="js/moment-with-locales.js"></script>

    <script type="text/javascript" src="js/flot/jquery.flot.min.js"></script>
    <script type="text/javascript" src="js/flot/jquery.flot.pie.min.js"></script>
    <script type="text/javascript" src="js/flot/jquery.flot.categories.min.js"></script>
    <script type="text/javascript" src="js/flot/jquery.flot.stack.min.js"></script>

	<script type="text/javascript" src="js/blocks/lineandbars.js"></script>

	<script type="text/javascript" src="js/blocks/dash-charts.js"></script>
	<script type="text/javascript" src="js/blocks/gauge.js"></script>

	<!-- NOTY JAVASCRIPT -->
	<script type="text/javascript" src="js/noty/jquery.noty.js"></script>
	<script type="text/javascript" src="js/noty/top.js"></script>
	<script type="text/javascript" src="js/noty/topLeft.js"></script>
	<script type="text/javascript" src="js/noty/topRight.js"></script>
	<script type="text/javascript" src="js/noty/topCenter.js"></script>

	<!-- You can add more layouts if you want -->
	<script type="text/javascript" src="js/noty/themes/default.js"></script>
    <!-- <script type="text/javascript" src="assets/js/dash-noty.js"></script> This is a Noty bubble when you init the theme-->



	<script type="text/javascript" src="js/jquery.flexslider.js"></script>
    <script type="text/javascript" src="js/blocks/admin.js"></script>

    <style type="text/css">
      body {
        padding-top: 60px;
      }

      hr {
        margin-bottom: 5px;
      }

      div.center {
        display: block;
        margin-left: auto;
        margin-right: auto;
      }

      span.value {
          color:#6AC8CC;
      }

      table.valuebox {
       background-color: #666;
       margin-top: 10px;
       margin-bottom: 5px;
      }

      table.valuebox td {
       border-right: thick solid #3d3d3d;
       border-left: thick solid #3d3d3d
      }


    </style>
	
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
	<script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
   

  	<!-- Google Fonts call. Font Used Open Sans & Raleway -->
	<link href="http://fonts.googleapis.com/css?family=Raleway:400,300" rel="stylesheet" type="text/css">
  	<link href="http://fonts.googleapis.com/css?family=Open+Sans" rel="stylesheet" type="text/css">
	<center><span style="color:#3FCCCC;">Inviato riavvio alle API.</span></center>
	<center><span style="color:#3FCCCC;">Sarai reindirizzato a breve.</span></center>
	<script>setTimeout(function(){window.location.replace("system.html");},6000);</script>';
}else{
	header("location: system.html");
}
?>

