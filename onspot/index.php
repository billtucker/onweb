<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/8/2015
 * Time: 10:56 AM
 */

//This include_once will allow for abstraction from the main configuration file
include_once($spotRoot ."onspot-config.php");

//once the local onspot-config is added then all root variables are exposed
include_once($utilities ."utility.php");
$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");


$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
$log->debug("Header To Use: " .$headerToUse);
include_once($headerFooter .$headerToUse);
?>
    <br>
    <h2>On-Air Pro Spot Viewer Index Page</h2>
    <br>
    <br>

    <a href="http://192.168.0.16/onweb/onspot/spotedit.php?pkId=XY+3K9U9+EYGOJ+S6BY3+A3EE7">Link to Spot</a>
	
<?php include_once($headerFooter ."footer.php");
