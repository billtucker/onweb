<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/26/2015
 * Time: 10:54 AM
 */


include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($utilities ."utility.php");

$pageUrl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
$log->debug("Header To Use: " .$headerToUse);

include_once($headerFooter .$headerToUse);

if(isset($_GET['error'])){
    $error = $_GET['error'];
}

?>

<br>
<h4 class="text-center">ON-AIR Pro Login</h4>
<div class="row">
    <div class="col-xs-1 col-md-1">&nbsp;</div>
    <div class="col-xs-4 col-md-4">
        <?php
        if(isset($error) && strlen($error) > 0){
            echo(
                "<p class='alert alert-danger'><strong>" .$error ."</strong></p>"
            );
        }
        ?>
        <p class="text-primary"><strong>Please enter your ON-AIR Pro credentials below</strong></p>
    </div>
    <div class="col-xs-7 col-md-7">&nbsp;</div>
</div>
<form role="form" id="oap_login" name="oap_login" action="login/loginProcessing.php" method="post">
    <input type="hidden" name="action" value="login"><!-- hidden field used by loginProcessing.php -->
    <div class="form-group">
        <div class="row">
            <div class="col-xs-1 col-md-1">&nbsp;</div>
            <div class="col-xs-3 col-md-3">
                <label for="username">Username:</label>
                <input class="form-control" type="text" id="username" name="username">
            </div>
            <div class="col-xs-8 col-md-8">&nbsp;</div>
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-xs-1 col-md-1">&nbsp;</div>
            <div class="col-xs-3 col-md-3">
                <label for="password">Password:</label>
                <input class="form-control" type="password" id="password" name="password" required>
            </div>
            <div class="col-xs-8 col-md-8">&nbsp;</div>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-1 col-md-1">&nbsp;</div>
        <div class="col-xs-11">
            <button class="btn btn-primary" type="submit">Login</button>
        </div>
    </div>
</form>
<br>
<hr>
<?php include_once($headerFooter .'footer.php'); ?>


