<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/14/2016
 * Time: 9:32 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
include_once($utilities ."utility.php");
include_once($errors ."errorProcessing.php");
include_once ($fmfiles ."order.db.php");

$log->debug("Upload Test was called");

$uploadDir = "../uploads/onrequest/";

//executable and other dangerous file types NOT to upload
$blockedExt = array("exe", "php", "zip", "com", "rar", "bat", "scr", "pif", "hta", "sys", "cmd", "vb", "vbe", "ws",
    "js", "shs");

function outputJSON($msg, $status = 'error'){
    header('Content-Type application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

if(isset($_FILES) && !empty($_FILES)){
    $log->debug("We have files with data");

    if($_FILES['file']['error'] > 0){
        outputJSON("File errors detected on upload");
    }

    if(!isset($_POST['pkId'])){
        outputJSON("Missing Primary Key value for upload");
    }else{
        $pkId = $_POST['pkId'];
    }

    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $originalFileName = $_FILES['file']['name'];

    if(in_array($ext, $blockedExt)){
        outputJSON('Unsupported file type uploaded: ' .$_FILES['file']['type']);
    }

    $filenameTemp = transformFilename($_FILES['file']['name'], $_POST['pkId']);
    $log->debug("The FileMaker temporary name " .$filenameTemp);

    if(!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir .$filenameTemp)){
        outputJSON("Error uploading file: " .$_FILES['file']['name']);
    }

    pushImageToFM($originalFileName, $filenameTemp, $pkId);

}

function pushImageToFM($originalName, $tempName, $pkId){
    global $log, $fmOrderDB;

    $metaFind = $fmOrderDB->newFindCommand('[WEB] Project Meta Fields');
    $metaFind->addFindCriterion('__pk_ID','==' .$pkId);
    $metaResults = $metaFind->execute();

    if(FileMaker::isError($metaResults)){
        $error = "documentUploader.php - Meta Search Results Error: " .$metaResults->getMessage() ." " .$metaResults-getCode();
        $log->error($error);
        die($error);
    }

    $metaRelatedRecords = $metaResults->getRecords();
    $metaRecord = $metaRelatedRecords[0];

    $metaRecord->setField('Upload_Filename_Original_t', $originalName);
    $metaRecord->setField("Upload_Filename_Temp_t", $tempName);

    $result = $metaRecord->commit();
    if(FileMaker::isError($result)){
        $error = "documentUploader.php - Error Saving Record " .$pkId ." Error Message: " .$result->getMessage() ." Error Code " .$result->getCode();
        $log->error($error);
        die($error);
    }
}
