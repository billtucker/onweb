<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/3/2015
 * Time: 9:48 AM
 */

include_once("config.php");
$serverPort = $_SERVER['SERVER_PORT'];
$pageURL = urlencode(($_SERVER['SERVER_PORT'] == '80' ? "http://" : "https://" ) ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

?>

<html>
<head><title>Page Protocol</title></head>
<body>
    <h3>Server Port and Page URL</h3>
    <p>Server Port: <?php echo($serverPort); ?></p>
    <p>Page URL no port: <?php echo(urldecode($pageURL)); ?></p>
    <p>Site Prefix: <?php echo($sitePrefix); ?></p>
</body>
</html>
