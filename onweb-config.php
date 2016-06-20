<?php

/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/26/2015
 * Time: 9:42 AM
 *
 * ON-WEB Configuration file for ON-Request and ON-Spot Viewer sites. The goal is to make the configuration as dynamic
 * as possible.
 *
 */


//$root represents file system root and is not related to TCP/IP protocol
$root = dirname(__FILE__) .DIRECTORY_SEPARATOR;

$headerFooter = $root ."headerfooter" .DIRECTORY_SEPARATOR;
$utilities = $root ."commonutilities" .DIRECTORY_SEPARATOR;
$errors = $root ."errors" .DIRECTORY_SEPARATOR;
$fmfiles = $root ."fmfiles" .DIRECTORY_SEPARATOR;
$login = $root ."login" .DIRECTORY_SEPARATOR;
$commonprocessing = $root ."commonprocessing" .DIRECTORY_SEPARATOR;
$validation = $root ."validation" .DIRECTORY_SEPARATOR;

//LDAP Definitions to be used in AD login
define("COMPANY_DOMAIN", "thoughtdev.com");
define("LDAP_SERVER", "192.168.0.11");
define("LDAP_PORT", 389);
//These configuration items are custom per site
$memberOfList = array("Employees", "Remote Desktop Users");
$ldapKeySearch = "memberof";

//$useLdap switch is off when set to false and forces all login validation to FileMaker only
$usesLdap = false;
//This DN is for Thought Development only!!!!
$dn = "CN=Users,DC=thoughtdev,DC=com";

//This section dynamically sets site address so few hardcoded values are required.
// The $siteRoot is the only concrete value
$port = ($_SERVER['SERVER_PORT'] == '80' ? "http://" : "https://" );
$siteRoot = "onweb";
$homepage = $port .$_SERVER['HTTP_HOST'] ."/" .$siteRoot ."/";
$site_prefix = $homepage;

//use this to replace the container URL for videos if required for videos
$serverIP = $_SERVER['SERVER_NAME'];


//place holder for property used in header_top.php to display the small logo and splash logo on index. This property
// will be overridden by property in dynamically generated tdc-app-config.php
$companyLogoSmall = "";
$companyLogoSplash = "";
$companyLogoSmallPropertyName = '$companyLogoSmall =';
$companyLogoSplashPropertyName = '$companyLogoSplash =';
$imageDir = "images";
$appConfigName = "tdc-app-conf.php";
$imageSmallFileName = "company_logo_small.";
$imageSplashFileName = "company_logo_splash.";


//set Logger class, setup Log4php configuration, and get Logger that is/can be used on any PHP page
//current configuration "config.xml" is setup for 1 MB max size log file that rolls file name onweb.log
include_once('vendor/apache/log4php/src/main/php/Logger.php');
Logger::configure($root ."/config.xml");
$log = Logger::getLogger("ONAIRPRO_Logger");


//Must be enabled from FileMaker to access OnSpot web site. This is a new check to validate the customer purchased
//The package/plugin from Thought-Development
$onWebPlugin = "ON-WEB";
$onSpotPlugin = "ON-SPOT";

//added this variable to deal with canModify related to Request edit commands
$okModify = "RequestModify";

//Sub-directory site definition for Request and Spot viewer
$spotRoot = $root ."onspot/";
$requestRoot = $root ."onrequest/";

//FileMaker Error code no records found on search. Use this error over actual verbiage since least likely to change
$noRecordsFound = "401";

//Flag to enable or disable ajax image/file downloader. The Javascript, if enabled, will try to access the Meta record for
//an image/file URL. Some originzation will want disable this flag to allow for virus validation before uploading to FM
$getContainerUrl = true;
