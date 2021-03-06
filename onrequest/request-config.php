<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/8/2015
 * Time: 3:30 PM
 */

//Get variables for top level "common" directories and setup local variables for On Request "Only"
include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."onweb-config.php";

//add ON_REQUEST directory location relative to root and onweb-config
$requestProcessing = $requestRoot ."processing" .DIRECTORY_SEPARATOR;
$requestService = $requestRoot . "service" .DIRECTORY_SEPARATOR;
$requestInclude = $requestRoot ."include" .DIRECTORY_SEPARATOR;

//Must be enabled from FileMaker to access OnSpot web site. This is a new check to validate the customer purchased
//The package/plugin from Thought-Development
$onWebPlugin = "ON-WEB";
$onRequestPlugin = "ON-REQUEST";
$siteSection = "Request";
$onRequestModify = "Modify";
$okModify = "RequestModify";

$request_site_prefix = $site_prefix ."onrequest/";

