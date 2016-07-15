<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 7/30/2015
 * Time: 10:14 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
include_once($utilities ."utility.php");

$pageUrl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

if(isset($_GET['errorMessage'])){
    $message = urldecode($_GET['errorMessage']);
}else{
    $message = "No specific error message provided for display";
}

if(isset($_GET['messageTitle'])){
    $messageTitle = urldecode($_GET['messageTitle']);
}else{
    $messageTitle = "System Error";
}

$log->error("Error page called with error message: " .$message ." with title: " .$messageTitle);

//include header file dynamically from the page URL
include_once($headerFooter .getHeaderIncludeFileName(urldecode($pageUrl)));

?>

<div class="clearfix">&nbsp;</div>
<div class="container-fluid"><!-- start container div -->
    <!--<p>Base Directory: C:/wamp/www/</p>-->
    <div class="row">
        <div class="co2-md-3 col-xs-3 col-lg-3">&nbsp;</div>
        <div class="col-md-6 col-xs-6 col-lg-6">
            <h2 class="text-center" style="color: red"><?php echo($messageTitle); ?></h2>
            <p>The system encountered the following error while processing your request:</p>
        </div>
        <div class="col-md-3 col-xs-3 col-lg-3">&nbsp;</div>
    </div>
    <!--    <div class="clearfix">&nbsp;</div>-->
    <div class="row">
        <div class="col-md-3 col-xs-3 col-lg-3">&nbsp;</div>
        <div class="col-md-6 col-xs-6 col-lg-6 alert alert-danger" style="border: groove">
            <span class="glyphicon glyphicon-exclamation-sign" aria-hidden="true">&nbsp;</span>
            <?php
            if(isset($message)){
                echo $message .PHP_EOL;
            }
            ?>
        </div>
        <div class="col-md-3 col-xs-3 col-lg-3">&nbsp;</div>
    </div>
    <!--    <div class="clearfix">&nbsp;</div>-->
    <div class="row">
        <div class="col-md-3 col-xs-3 col-lg-3">&nbsp;</div>
        <div class="col-md-6 col-xs-6 col-lg-6">
            <p>If you have any questions, please contact Thought Development Support (818) 781-7887.</p>
        </div>
        <div class="col-md-3 col-xs-3 col-lg-3">&nbsp;</div>
    </div>
</div><!-- end of container div -->
<div class="clearfix">&nbsp;</div>
<?php include_once($headerFooter ."footer.php"); ?>