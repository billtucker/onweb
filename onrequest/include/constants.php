<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/3/2014
 * Time: 3:51 PM
 * TODO: add comments to describe the file functionality of this php file
 */


include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
include_once($fmfiles .'order.db.php');
include_once($errors .'errorProcessing.php');

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$log->debug("Start get I Table records");

$findLabels = $fmOrderDB->newFindCommand("[WEB] i_Table");
$results = $findLabels->execute();

if(FileMaker::IsError($results)){
    $log->error($results->getMessage(), $results->getErrorString(), $pageUrl, $requestPkId, $site_prefix);
    $errorTitle = "FileMaker Error";
    processError($results->getMessage(), $results->getErrorString(), $pageUrl, "N/A", $errorTitle);
    exit;
}

$labels = $results->getRecords();
$labelRecord = $labels[0];

$log->debug("End -- Now have I Table records");