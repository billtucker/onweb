<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 1/8/2015
 * Time: 10:20 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");

include_once($fmfiles ."order.db.php");
include_once ($utilities ."utility.php");

$url = $_GET["url"];

$fileTypeSmall = getFileMakerContainerFileExtension($url);
$imageType = getImageHeaderType($fileTypeSmall);

$log->debug("Get image from database with extension: " .$fileTypeSmall ." and type is: " .$imageType ." from url: " .$url);

header('Content-type: ' .$imageType);
echo $fmOrderDB->getContainerData($_GET["url"]);

?>