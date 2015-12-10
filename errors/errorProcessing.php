<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/30/2015
 * Time: 3:06 PM
 * This methods allows for a blending the logger() method and what is reported to user at the presentation layer.
 *
 * Refactor Notes:
 *
 * 1. 7/30/2015 Start of integration with the Spot Viewer site. First to integrate are the license measures put in
 *    place.
 */


include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");

function processError($errorMessage, $fmStringErrorMessage, $url, $pk, $errorTitle){
    global $log, $homepage;

    $log->debug("processError method fired");

    if(!session_id()){
        session_start();
    }

    if(isset($_SESSION['userName'])){
        $username = $_SESSION['userName'];
    }else{
        $username = "unknown";
    }

    $error = "Error: Page URL: " .urldecode($url) ." Error Message: " .urldecode($errorMessage)
        .". Error String: " .urldecode($fmStringErrorMessage) .".  Primary Key: " .urldecode($pk) ." userId: " .urldecode($username);

    $log->error($error);

    header("location: " .$homepage ."errors/accessError.php?errorMessage=" .urlencode($errorMessage) ."&messageTitle=" .urlencode($errorTitle));
    exit;
}
