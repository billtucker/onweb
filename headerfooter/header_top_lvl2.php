<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/16/2014
 * Time: 1:33 PM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");

include_once($utilities ."utility.php");

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

/*
 * Include TDC Application Configuration file if the file exists otherwise skip this include. The file is
 * dynamically generated at login 'only' so if the file is not present then the company small logo will not display.
 * However, the file should be included for each presentation layer PHP page
*/
if(file_exists($root .$appConfigName)){
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

    <!--[if lt IE 9]>
    <script src="//oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="//oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
    <![endif]-->
</head>
<body>
<!-- add this javascript library here because it must be seen in  the DOM -->
<script src="../js/lightbox.min.js" type="text/javascript"></script>
<div class="container-fluid"><!-- start site navigation bar -->
    <div class="row tdc-nav-bar-background-grad">
        <div class="col-xs-1 col-md-1 tdc-nav-bar-header-margin-padding-right">
            <!-- <img src="../images/universal-sports-logo_75x37_org.png" alt="Universal Sports Logo"> -->
            <img class="img-thumbnail center-block" style="height: 42px;" src="../<?php echo($companyLogoSmall);?>" alt="Company Logo">
        </div>
        <form role="form" action="../commonutilities/printPage.php" method="post" id="print" name="print">
            <input type="hidden" name="url" id="url" value="<?php echo($pageUrl);?>">
            <div class="col-xs-4 col-md-4">
                <ul class="nav nav-pills" role="tablist">
                    <li class="tdc-nav-bar-header-divider-left" role="presentation">
                        <a href="<?php echo($homepage);?>"><strong>Home</strong></a>
                    </li>
                    <li class="tdc-nav-bar-header-divider-left" role="presentation">
                        <!-- This configuration works in all browsers -->
                        <a href="#" onclick="history.go(-1);return false;"><strong>Back</strong></a>
                    </li>
                    <li class="tdc-nav-bar-header-divider-left" role="presentation">
                    <li class="tdc-nav-bar-header-divider-left printBtn" role="presentation">
                        <!-- disable the print button functionality on the spotedit.php only -->
                        <?php printEnableDisable($pageUrl); ?>
                    </li>
                    </li>
                    <li class="tdc-nav-bar-header-divider-left" role="presentation">
                        <a href="<?php echo($site_prefix ."logout.php");?>" id="logout"><strong>Logout</strong></a>
                    </li>
                    <li class="tdc-nav-bar-header-divider-left" role="presentation">
                        <a href="#" id="empty_nav_link">&nbsp;</a>
                    </li>
                </ul>
            </div>
        </form>
        <div class="col-xs-6 col-md-6">&nbsp;</div>
        <div class="col-xs-1 col-md-1 pull-right">
            <img src="../images/TD_OAP_logo_small.png" alt="No Alt Available" align="right" width="120" height="35">
        </div>
    </div>
</div><!-- start site navigation bar -->
<div class="tdc-container-fluid"><!-- start container div -->