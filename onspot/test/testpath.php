<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/30/2015
 * Time: 10:23 AM
 */

include_once("onspot-config.php");
//include_once($fmfiles ."work.db.php");
include_once($utilities ."utility.php");

$pageUrl = urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

validateUser($site_prefix, $pageUrl, $siteSection, $onSpotView, $OnSpotPluginToValidate);

?>
<html>
    <head>
        <title>Test of Path Load</title>
    </head>
    <body>
        <h1>Look for errors in the page loader</h1>
        <p>
            Call to Micro Timer:
            <?php
                echo(microTimer());
            ?>
        </p>
        <p>
            Validate Info:
            <?php echo("Prefix(" .$site_prefix .") Page URL(" .urldecode($pageUrl) .") Site Section: (" .$siteSection .") OnSpotView(" .$onSpotView . ") Plugin(" .$OnSpotPluginToValidate .")"); ?>
        </p>
    </body>
</html>
