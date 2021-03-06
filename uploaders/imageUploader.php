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
include_once($errors .'errorProcessing.php');

$log->debug("Upload a file was called start processing");

//This directory is strictly associated with Request uploads images and files (not video files)
$uploadDir = "../uploads/onrequest/";

//executable and other dangerous file types NOT to upload (block all these extensions)
$blockedExt = array("exe", "php", "zip", "com", "rar", "bat", "scr", "pif", "hta", "sys", "cmd", "vb", "vbe", "ws",
    "js", "shs");

function outputJSON($msg, $status = 'error'){
    header('Content-Type application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

//function to return a json array for calling JQuery call
function returnJsonToAjax($status, $containerStatus, $urlToUse, $fileName){
    $responseArray = array("status"=>$status,"container_status"=>$containerStatus, "container_url"=>$urlToUse,"filename"=>$fileName);
    echo utf8_encode(json_encode($responseArray));
}

if(isset($_FILES) && !empty($_FILES)){
    $log->debug("We have files to upload to the onweb uploads directory");

    if($_FILES['file']['error'] > 0){
        //We need to know what error was found in the upload process
        $log->error("Errors were detected in upload now parse the error");

        if($_FILES['file']['error'] > 0){
            switch ($_FILES['file']['error']) {
                case UPLOAD_ERR_OK:
                    break;
                case UPLOAD_ERR_NO_FILE:
                    $log->error('No file or files sent');
                    break;
                case UPLOAD_ERR_INI_SIZE:
                    $log->error('File Size error in php.ini file');
                    break;
                case UPLOAD_ERR_FORM_SIZE:
                    $log->error('Exceeded file size limit.');
                    break;
                default:
                    $log->error('Unable to determine error');
                    break;
            }
        }
        outputJSON("File errors detected on upload");
    }

    if(!isset($_POST['pkId'])){
        $log->error("Missing primary key as PK was not in POST[] array");
        outputJSON("Missing Primary Key value for upload");
    }else{
        $pkId = $_POST['pkId'];
    }

    $ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
    $originalFileName = $_FILES['file']['name'];

    if(in_array($ext, $blockedExt)){
        $log->error("File extension is not supported for upload: " .$_FILES['file']['type']);
        outputJSON('Unsupported file type uploaded: ' .$_FILES['file']['type']);
    }

    $filenameTemp = transformFilename($_FILES['file']['name'], $_POST['pkId']);
    $log->debug("The FileMaker temporary name " .$filenameTemp);

    if(!move_uploaded_file($_FILES['file']['tmp_name'], $uploadDir .$filenameTemp)){
        $log->error("Error uploading file: " .$_FILES['file']['name']);
        outputJSON("Error uploading file: " .$_FILES['file']['name']);
    }

    $log->debug("Copied temp file name: " .$_FILES['file']['tmp_name'] ." to file " .$filenameTemp);

    pushImageToFM($originalFileName, $filenameTemp, $pkId);

    $containerUrl = getFileContainerURL($pkId, $originalFileName);
    $containerStatus = "";
    if(empty($containerUrl)){
        $containerStatus = "empty";
    }else{
        $containerStatus = "have_url";
    }

    returnJsonToAjax("success", $containerStatus, $containerUrl, $originalFileName);
}

function getFileContainerURL($pkId, $originalName){
    global $log, $fmOrderDB;

    $metaFind = $fmOrderDB->newFindCommand('[WEB] Project Meta Fields');
    $metaFind->addFindCriterion('__pk_ID','==' .$pkId);
    $metaResults = $metaFind->execute();

    if(FileMaker::isError($metaResults)){
        $errorTitle = "Failed To Upload File";
        $error = "Meta Search Results Error: " .$metaResults->getMessage() ." " .$metaResults-getCode();
        $log->error($error);
        processError("Failed in get conatiner URL: " .$originalName, $metaResults->getMessage(), "imageUploader.php", $pkId, $errorTitle );
        exit();
    }

    $metaRelatedRecords = $metaResults->getRecords();
    $metaRecord = $metaRelatedRecords[0];

    $containerUrl = $metaRecord->getField('Answer_Container_Data_r');

    return $containerUrl;
}

function pushImageToFM($originalName, $tempName, $pkId){
    global $log, $fmOrderDB;

    $metaFind = $fmOrderDB->newFindCommand('[WEB] Project Meta Fields');
    $metaFind->addFindCriterion('__pk_ID','==' .$pkId);
    $metaResults = $metaFind->execute();

    if(FileMaker::isError($metaResults)){
        $errorTitle = "Failed To Upload File";
        $error = "Meta Search Results Error: " .$metaResults->getMessage() ." " .$metaResults-getCode();
        $log->error($error);
        processError("Failed To upload File: " .$originalName,$metaResults->getMessage(), "imageUploader.php", $pkId, $errorTitle );
        exit();
    }

    $metaRelatedRecords = $metaResults->getRecords();
    $metaRecord = $metaRelatedRecords[0];

    $metaRecord->setField('Upload_Filename_Original_t', $originalName);
    $metaRecord->setField("Upload_Filename_Temp_t", $tempName);

    //set container field within record to empty initially so FM script can upload the image or file to container
    $metaRecord->setField("Answer_Container_Data_r", "");

    $result = $metaRecord->commit();
    if(FileMaker::isError($result)){
        $errorTitle = "Failed To Upload File";
        $error = "Error Saving Record " .$pkId ." Error Message: " .$result->getMessage() ." Error Code " .$result->getCode();
        $log->error($error);
        processError("Failed To upload File: " .$originalName,$result->getMessage(), "imageUploader.php", $pkId, $errorTitle );
        exit();
    }

    $log->debug("Completed writing original and PK filenames to FileMaker record");
}
