<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 12/15/2014
 * Time: 9:25 AM
 */


include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($fmfiles .'work.db.php');
include_once($errors .'errorProcessing.php');

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$sortField = "Request_Created_Date_d";
$requestListFind = $fmOrderDB->newFindCommand('[WEB] Project Request List');

//Added new sort order for Project List on Created Date
$requestListFind->addSortRule($sortField, 1, FILEMAKER_SORT_DESCEND);
$requestListFinds = $requestListFind -> execute();

if(FileMaker::isError($requestListFinds)){
    $log->error($requestFinds->getMessage(), $requestFinds->getErrorString(), $pageUrl, $pkId, $site_prefix);
    processError($requestFinds->getMessage(), $requestFinds->getErrorString(), $pageUrl, "N?A", $errorTitle);
    exit;
}

$requestList = $requestListFinds->getRecords();

?>