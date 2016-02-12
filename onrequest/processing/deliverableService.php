<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 12/10/2014
 * Time: 10:06 AM
 */


include_once("../request-config.php");

//validate user prior to access on this page
include_once("$validation" ."user_validation.php");
include_once($utilities ."utility.php");
include_once($errors .'errorProcessing.php');

//reset session when save operation is executed to avoid loss of data
resetSessionTimeout();

//if a user attempts to save data without being logged in so after login the user should go to index page
$pageUrl = $site_prefix ."/index.php";

//validate user then check if plugin license if active to access on this page
validateUser($site_prefix, $pageUrl, $siteSection, $onRequestModify, $onRequestPlugin);
$canModify = userCanModifyRequest($okModify);

$deliverablePkId = $_POST['del_pk_id'];
$itemId =  ""; //$_POST['itemId'];

$log->debug("Now Save processing PK: " .$deliverablePkId);

if(!$canModify){
    $message = "Page is read only";
    header('Location: ' .$request_site_prefix . 'deliverableview.php?pKid=' .urlencode($deliverablePkId) .'&itemId=' .$itemId .'&message=' .$message);
    exit;
}

include_once($fmfiles .'order.db.php');
include_once($requestProcessing ."tagProcessing.php");

/** Open the Deliverable Layout and check for errors and assign record found by primary key */
$deliverableView = "[WEB] Project Deliverable";
$deliverableFind = $fmOrderDB->newFindCommand($deliverableView);
$deliverableFind->addFindCriterion('__pk_ID','==' .$deliverablePkId);
$deliverableResults = $deliverableFind->execute();

if(FileMaker::isError($deliverableResults)){
    $errorTitle = "FileMaker Error";
    $log->error("deliverableService.php - Failure to open " .$deliverableView ." " .$deliverableResults->getMessage() ." " .$deliverableResults->getCode());
    processError($deliverableResults->getMessage(), $deliverableResults->getErrorString(), "deliverableService.php", $deliverablePkId, $errorTitle);
    exit;
}

$deliverableRecords = $deliverableResults->getRecords();

$log->debug("Found record count: " .$deliverableResults->getFetchCount());

$deliverableRecord = $deliverableRecords[0];
$requestPkId = $deliverableRecord->getField('_fk_Request_pk_ID');

/* TODO need to investigate why getRelatedRecords() Stopped Working */
$metaFind = $fmOrderDB->newFindCommand('[WEB] Project Meta Fields');
$metaFind->addFindCriterion('_fk_Deliverable_pk_ID','==' .$deliverablePkId);
$metaResults = $metaFind->execute();

//TODO do we need this check for a specific error message related to no records found. Will this use case ever exist?
if(FileMaker::isError($metaResults)){
    $errorCheck = 'No records match';
    if(strstr($metaResults->getMessage(), $errorCheck) == false){
        $errorTitle = "FileMaker Error";
        $log->error("deliverableService.php - Failed to load Meta Records for: " .$deliverablePkId ." Message: " .$metaResults->getMessage());
        processError($metaResults->getMessage(), $metaResults->getErrorString(), "deliverableService.php", $deliverablePkId, $errorTitle);
        exit;
    }
}else{
    $metaRelatedRecords = $metaResults->getRecords();
}

$deliverableArray = array('Notes','Rough_Cut_Due_d','Final_Due_Date','Flight_Date_Start','Flight_Date_End');
$metaFieldName = 'Answer';
$metaFieldType = 'Input_Type';


foreach($deliverableArray as $delName){
    $deliverableRecord->setField($delName,$_POST[$delName]);
}

$deliverableRecordResults = $deliverableRecord->commit();
if(FileMaker::isError($deliverableRecordResults)){
    $errorTitle = "FileMaker Error";
    $log->error("Deliverable Record Set (Save) Error: " .$deliverableRecordResults->getMessage() ." " .$deliverableRecordResults->getCode());
    processError($deliverableRecordResults->getMessage(), $deliverableRecordResults->getErrorString(), "deliverableService.php", $deliverablePkId, $errorTitle);
    exit;
}

//Process the Deliverable Tags ahead of the meta data
processDeliverableTags($_POST, $deliverablePkId, $fmOrderDB);

//Process Meta records for this deliverable and project
if(isset($metaRelatedRecords)){
    foreach($metaRelatedRecords as $meta){
        $fieldType = $meta->getField($metaFieldType);
        $metaPkId = $meta->getField('__pk_ID');

        if($fieldType != 'Image' && $fieldType != 'File'){
            if(!empty($_POST[$metaPkId])) {
                $meta->setField($metaFieldName, $_POST[$metaPkId]);
                $metaResults = $meta->commit();
                if (FileMaker::isError($metaResults)) {
                    $log->error("deliverableService.php - Meta Record Set Error: " . $metaResults->getMessage() . " " . $metaResults->getCode());
                    $errorTitle = "FileMaker Error";
                    processError($metaResults->getMessage(), $metaResults->getErrorString(), "deliverableService.php", $deliverablePkId, $errorTitle);
                    exit;
                }
            }
        }
    }
}

$message = "Success";
header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
header('Pragma: no-cache');
header('Location: ' .$request_site_prefix . 'deliverableview.php?pKid=' .urlencode($deliverablePkId) .'&itemId=' .$itemId .'&message=' .$message);

?>