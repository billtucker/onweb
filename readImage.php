<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 1/8/2015
 * Time: 10:20 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");

include_once($fmfiles ."order.db.php");

$url = $_GET["url"];

// Search for the extension of the file
$url = substr($url, 0, strpos($url, "?"));
$url = substr($url, strrpos($url, ".") + 1);

$log->debug("Get image from database with extension: " .$url);

// Send the correct Content-Type header
if($url == "jpg"){
    $log->debug("JPG image");
    header('Content-type: image/jpeg');
} else if($url == "gif"){
    $log->debug("GIF image");
    header('Content-type: image/gif');
} else{
    $log->debug("Octet-Stream image");
    header('Content-type: application/octet-stream');
}

//header('Content-type: image/jpg');
echo $fmOrderDB->getContainerData($_GET["url"]);

?>