<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/8/2015
 * Time: 10:56 AM
 * Simply redirect the user to the spot view list if for some reason the user manually types URL in the address bar
 */

//This include_once will allow for abstraction from the main configuration file
include_once "onspot-config.php";

//once the local onspot-config is added then all root variables are exposed
include_once($utilities ."utility.php");
$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$log->debug("Send user: " .$site_prefix ."onspot/spotviewerlist.php");

header('Location:' .$site_prefix ."onspot/spotviewerlist.php");
exit;
