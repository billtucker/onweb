<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/3/2014
 * Time: 4:56 PM
 *
 * TODO: add JavaDocs or PHP Docs to this file
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($fmfiles .'order.db.php');
include_once($errors .'errorProcessing.php');

function getRequest($pkId){
    global $fmOrderDB, $request, $site_prefix, $port, $log;

    $log->debug("Get request using PK: " .$pkId);

    $pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

    /* Open the database and add ID to find command **/
    $requestFind = $fmOrderDB->newFindCommand('[WEB] Project Request');
    $requestFind -> addFindCriterion('__pk_ID', '==' .$pkId);
    $requestFinds = $requestFind -> execute();

    /** Check for errors and if so send to error processing and report to user */
    if(FileMaker::isError($requestFinds)){
        $errorTitle = "FileMaker Error";
        $log->error($requestFinds->getMessage(), $requestFinds->getErrorString(), $pageUrl, $pkId, $site_prefix);
        processError($requestFinds->getMessage(), $requestFinds->getErrorString(), $pageUrl, $pkId, $errorTitle);
        exit;
    }

    /** Now pull all records of find command for Requests (Parent of Meta data) and pull the first record of the array */
    $requests = $requestFinds -> getRecords();
    $request = $requests[0];

    $log->debug("Finished now have record with PK: " .$pkId);
}

?>