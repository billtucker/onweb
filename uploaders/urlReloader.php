<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 6/20/2016
 * Time: 11:10 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
include_once($utilities ."utility.php");
include_once($errors ."errorProcessing.php");
include_once ($fmfiles ."order.db.php");
include_once($errors .'errorProcessing.php');

$status = "";

if(isset($_POST['pk']) && !empty($_POST['pk'])){
    $pkId = $_POST['pk'];
    $log->debug("WE have the pkId: " .$pkId);
}else{
    $log->error("No PK provided to get conatiner URL");
    $status = "error";
    die();
}

$metaFind = $fmOrderDB->newFindCommand('[WEB] Project Meta Fields');
$metaFind->addFindCriterion('__pk_ID','==' .$pkId);
$metaResults = $metaFind->execute();

if(FileMaker::isError($metaResults)){
    $errorTitle = "Failed To Upload File";
    $error = "Meta Search Results Error: " .$metaResults->getMessage() ." " .$metaResults-getCode();
    $log->error($error);
    $status = "error";
    processError("Failed in get conatiner URL: " .$originalName, $metaResults->getMessage(), "imageUploader.php", $pkId, $errorTitle );
    exit();
}

$metaRelatedRecords = $metaResults->getRecords();
$metaRecord = $metaRelatedRecords[0];

$containerUrl = $metaRecord->getField('Answer_Container_Data_r');
$originalFileName = $metaRecord->getField('Upload_Filename_Original_t');

$containerStatus = "";
if(empty($containerUrl)){
    $containerStatus = "empty";
}else{
    $containerStatus = "have_url";
}

returnJsonToAjax($status, $containerStatus, $containerUrl, $originalFileName);

//function to return a json array for calling JQuery call
function returnJsonToAjax($status, $containerStatus, $urlToUse, $fileName){
    $responseArray = array("status"=>$status,"container_status"=>$containerStatus, "container_url"=>$urlToUse,"filename"=>$fileName);
    echo utf8_encode(json_encode($responseArray));
}