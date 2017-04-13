<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/16/2014
 * Time: 1:33 PM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");

include_once($utilities ."utility.php");
include_once($commonprocessing ."loadLogosWithLdap.php");

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

/*
 * Include TDC Application Configuration file if the file exists otherwise skip this include. The file is
 * dynamically generated at login 'only' so if the file is not present then the company small logo will not display.
 * However, the file should be included for each presentation layer PHP page
*/
if(file_exists($root .$appConfigName)){
    include_once($root .$appConfigName);
}else{
    writeLogosWithLdap();
    include_once($root .$appConfigName);
}

?>

<!DOCTYPE html>
<html  lang="en">
<head>
    <title>ON-AIR Pro</title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Add these header values to reset the page when save operation has occurred -->
    <meta http-equiv="cache-control" content="no-cache"> <!-- tells browser not to cache -->
    <meta http-equiv="expires" content="0"> <!-- says that the cache expires 'now' -->
    <meta http-equiv="pragma" content="no-cache"> <!-- says not to use cached stuff, if there is any -->

    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet" type="text/css">
    <!--CSS support Bootstrap dialog -->
    <link href="//cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.2/css/bootstrap-dialog.css" rel="stylesheet" type="text/css" />
    <link href="../css/bootstrap-datetimepicker.min.css" rel="stylesheet" media="screen">
    <link href="../css/fileinput.css" media="all" rel="stylesheet" type="text/css" />
    <link href="//cdnjs.cloudflare.com/ajax/libs/nprogress/0.1.6/nprogress.css" rel="stylesheet">
    <link href="../css/lightbox.min.css" rel="stylesheet" media="screen" type="text/css">
    <link href="../css/dropzone.css" rel="stylesheet" media="screen" type="text/css">
    <link href="../css/tdc-main.css" rel="stylesheet" media="screen">

    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" type="text/javascript"></script>

    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js" type="text/javascript"></script>
    <!--JS support for Bootstrap dialog -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/bootstrap3-dialog/1.35.2/js/bootstrap-dialog.js"></script>
    <script type="text/javascript" src="../js/bootstrap-datetimepicker.js" charset="UTF-8"></script>
    <script src="../js/fileinput.js" type="text/javascript"></script>

    <!-- TODO remove filestyle.js from the imports as it is no longer needed -->
    <script type="text/javascript" src="../js/bootstrap-filestyle.js"></script>
    <script type="text/javascript" src="../js/jquery.are-you-sure.js"></script>
    <script type="text/javascript" src="../js/ays-beforeunload-shim.js"></script>
    <script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/nprogress/0.1.6/nprogress.js"></script>
    <script type="text/javascript" src="../js/dropzone.js"></script>
    <script type="text/javascript" src="../js/tdc.app.js"></script>
	
	<!-- favicon links -->
	<link rel="apple-touch-icon" sizes="180x180" href="../images/favicons/apple-touch-icon.png">
	<link rel="icon" type="image/png" href="../images/favicons/favicon-32x32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="../images/favicons/favicon-194x194.png" sizes="194x194">
	<link rel="icon" type="image/png" href="../images/favicons/android-chrome-192x192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="../images/favicons/favicon-16x16.png" sizes="16x16">
	<link rel="manifest" href="../images/favicons/manifest.json">
	<link rel="mask-icon" href="../images/favicons/safari-pinned-tab.svg" color="#2d89ef">
	<meta name="apple-mobile-web-app-title" content="ON-AIR Pro Mobile">
	<meta name="application-name" content="ON-AIR Pro Mobile">


    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<!-- add this javascript library here because it must be seen in  the DOM -->
<script src="../js/lightbox.min.js" type="text/javascript"></script>
<nav class="navbar navbar-default" role="navigation">
    <!-- Brand and toggle get grouped for better mobile display -->
    <div class="navbar-header">
        <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
            <span class="sr-only">Toggle navigation</span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
            <span class="icon-bar"></span>
        </button>
    </div>
    <a class="navbar-left hidden-xs"><img class="img-thumbnail center-block" style="height: 42px;margin-left: 10px;" src="../<?php echo($companyLogoSmall);?>" alt="Company Logo"></a>
    <a class="navbar-brand pull-right hidden-xs" href="#"><img src="../images/TD_OAP_logo_small.png" alt="No Alt Available" align="right" width="120" height="35" style="padding-bottom: 5px;"></a>
    <!-- Collect the nav links, forms, and other content for toggling -->
    <form role="form" action="../commonutilities/printPage.php" method="post" id="print" name="print" style="position: relative;">
        <input type="hidden" name="url" id="url" value="<?php echo($pageUrl); ?>"><!-- hidden element used in print operation when enabled -->
        <div class="collapse navbar-collapse">
            <ul class="nav navbar-nav navbar-right">
                <li class="active"><a href="<?php echo($homepage);?>">Home</a></li>
                <li><a href="#" onclick="history.go(-1);return false;">Back</a></li>
                <li><?php printEnableDisable($pageUrl);?></li><!-- print <a> Tag enabled or disabled note: removed strong label-->
                <li><a href="<?php echo($site_prefix ."logout.php");?>">Logout</a> </li>
            </ul>
        </div><!-- /.navbar-collapse -->
    </form><!-- end of form for wkhtmltopdf printer -->
</nav>
<div class="tdc-container-fluid"><!-- start container div -->