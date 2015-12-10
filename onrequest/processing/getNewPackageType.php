<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 1/8/2015
 * Time: 1:23 PM
 */

//TODO: figure out this error reporting and what is required /////
//ini_set('displays_errors',1);
// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("../request-config.php");
include_once($fmfiles ."order.db.php");
include_once($errors .'errorProcessing.php');

$typePkId = urldecode($_GET['typePkId']);

$newProjectType = $fmOrderDB->newPerformScriptCommand('[WEB] Project Request Types','NewProjectRequest(RequestType_pk_ID)',$typePkId);
$newProjectTypeResults = $newProjectType->execute();

if(FileMaker::isError($newProjectTypeResults)){
    $errorTitle = "FileMaker Error";
    $log->error("Failure to open " .$deliverableView ." " .$newProjectTypeResults->getMessage() ." " .$newProjectTypeResults->getCode());
    processError($newProjectTypeResults->getMessage(), $newProjectTypeResults->getErrorString(), "getNewPackageType.php", $typePkId, $errorTitle);
}

$typeRecord = $newProjectTypeResults->getFirstRecord();

$projectId = $typeRecord->getField('__pk_ID');

header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Pragma: no-cache');
header('Location: ' .$site_prefix . '/request.php?pkId=' .urlencode($projectId));
?>

