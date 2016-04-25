<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/9/2015
 * Time: 9:47 AM
 *
 * Note: This page was included as a just in case if some user attempts to reach http:<site_DNS>/onweb/onrequest/
 * redirect the user to the project list page. This actual routes users attempting to use manual URL entry. The last
 * iteration of the 04/22/2016 page had the validation method. That method was removed since the user will be validated
 * on the project list.
 */


include_once("request-config.php");

//After the user is Validated redirect the user to the Request Project list page or login page
header('Location: ' . $request_site_prefix . 'projectlist.php');
exit();

?>