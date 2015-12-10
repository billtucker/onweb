<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/28/2015
 * Time: 10:10 AM
 */

$pageUrl = urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
include_once($errors ."errorProcessing.php");

processError("Test Error Message","FileMaker String error", $pageUrl, "123-456-789", "Test Error Message");
exit;

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Test Page</title>
</head>
<body>
    <h1>This will show only if PHP method calls fail</h1>
</body>
</html>
