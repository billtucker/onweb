<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/8/2015
 * Time: 11:17 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onairpro" ."/onweb-config.php");
include_once($utilities ."utility.php");

$pageUrl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
$log->debug("Header To Use: " .$headerToUse);

include_once($headerFooter .$headerToUse);

?>

    <br>
    <h2>On-Air Pro Spot Viewer Test</h2>
    <br>
<?php include_once($headerFooter ."footer.php");