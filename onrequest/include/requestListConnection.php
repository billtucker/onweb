<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 12/15/2014
 * Time: 9:25 AM
 */


include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($fmfiles .'order.db.php');
include_once($errors .'errorProcessing.php');

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$sortField = "Request_Created_Date_d";
$requestListFind = $fmOrderDB->newFindCommand('[WEB] Project Request List');

//Added new sort order for Project List on Created Date
$requestListFind->addSortRule($sortField, 1, FILEMAKER_SORT_DESCEND);
$requestListFinds = $requestListFind -> execute();

if(FileMaker::isError($requestListFinds)){
    if($requestListFinds->getCode() == $noRecordsFound){
        $requestList = array();
        $log->debug("Request list is empty no records found. Now display empty Request table view");
    }else{
        $log->error($saveFinds->getMessage(), $saveFinds->getErrorString(), $pageUrl, "NA", $site_prefix);
        processError($saveFinds->getMessage(), $saveFinds->getErrorString(), $pageUrl, "NA", $errorTitle);
        exit;
    }
}else{
    $requestList = $requestListFinds->getRecords();
}


?>