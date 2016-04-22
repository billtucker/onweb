<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/9/2015
 * Time: 9:47 AM
 *
 * Note: This page was included as a just in case if some user attempts to reach http:<site_DNS>/onweb/onrequest/
 * redirect the user to the project list page. This actual routes users attempting to use manual URL entry
 */


include_once("request-config.php");

//validate user prior to access on this page
include_once("$validation" ."user_validation.php");
include_once($utilities ."utility.php");
include_once($errors .'errorProcessing.php');

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$validateUser = true;

//even though this is a redirect page lets validate them to force a user to login if required
if($validateUser) {
    $log->debug("Now validate if Plugin: (" .$onRequestPlugin .") is installed");
    //validate user then check if plugin license if active to access on this page
    validateUser($site_prefix, $pageUrl, $siteSection, $onRequestModify, $onRequestPlugin);
}

//After the user is Validated redirect the user to the Request Project list page or login page
header('Location: ' . $request_site_prefix . 'projectlist.php');
exit();

?>