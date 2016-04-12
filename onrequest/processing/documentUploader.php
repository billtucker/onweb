<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 12/22/2014
 * Time: 2:36 PM
 * This file takes File array from Javascript, processes/tests, and physically moves file to server
 */


include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($fmfiles ."order.db.php");
include_once($errors .'errorProcessing.php');

$upload_dir = $root ."upload/";
$allowedFileExt = array('pdf', 'doc', 'docx', 'xls', 'xlsx', 'csv', 'txt', 'rtf', 'html', 'zip', 'mp3', 'wma', 'mpg',
    'flv', 'avi', 'jpg', 'jpeg', 'png', 'gif', 'tif');
$error;

function outputJSON($msg, $status = 'error'){
    header('Content-Type application/json');
    die(json_encode(array(
        'data' => $msg,
        'status' => $status
    )));
}

if($_FILES['SelectedFile']['error'] > 0){
    outputJSON('An error occurred when uploading');
}

/** Replace file name with Meta __pk_ID */
$filenameTemp = transformFilename($_FILES['SelectedFile']['name'], $_POST['pkId']);
$originalFilename = $_FILES['SelectedFile']['name'];
$ext = pathinfo($originalFilename, PATHINFO_EXTENSION);
$metaId = $_POST['pkId'];
if(!in_array($ext, $allowedFileExt)){
    outputJSON('Unsupported file type uploaded: ' .$_FILES['SelectedFile']['type']);
}

if($_FILES['SelectedFile']['size'] > 2000000){
    outputJSON('File size exceeds maximum upload size');
}


//TODO Have we decided if the filename exists in directory we will not delete the file then replace the file???
if(file_exists('upload/' .$filenameTemp)){
    outputJSON('File with that name already exists');
}


if(!move_uploaded_file($_FILES['SelectedFile']['tmp_name'], '../upload/' .$filenameTemp)){
    $log->error("Error on moving file to upload location");
    outputJSON('Error uploading the file - check destination has write access');
}

//The file was successfully uploaded to the server now push the original filename and temp name to FileMaker view
pushDocumentDataToFM($originalFilename, $filenameTemp, $metaId);
if(isset($error)){
    outputJSON($error);
}

outputJSON('upload/' .$filenameTemp, 'success');

/** Now replace the file name prefix with Meta __pk_ID */
function transformFilename($actual, $metapkId){
    $ext = pathinfo($actual, PATHINFO_EXTENSION);
    return $metapkId ."." .$ext;
}


//This method opens the WEB Meta View version and pushes filename and original name to the text fields below:
//Upload_Filename_Original_t	---> this represent the actual file name like 'a.png'
//Upload_Filename_Temp_t		---> this represents the Meta _pk_ID prefix with extension
function pushDocumentDataToFM($original, $temp, $pkId){
    global $fmOrderDB, $log;

    /* TODO need to investigate why getRelatedRecords() Stopped Working */
    $metaFind = $fmOrderDB->newFindCommand('[WEB] Project Meta Fields');
    $metaFind->addFindCriterion('__pk_ID','==' .$pkId);
    $metaResults = $metaFind->execute();

    if(FileMaker::isError($metaResults)){
        $errorTitle = "FileMaker Error";
        $log->error("pushDocumentDataToFM - Failure to open " .$metaResults ." " .$metaResults->getMessage() ." " .$metaResults->getCode());
        processError($metaResults->getMessage(), $metaResults->getErrorString(), "deliverableService.php", $pkId, $errorTitle);
        exit;
    }

    $metaRelatedRecords = $metaResults->getRecords();
    $metaRecord = $metaRelatedRecords[0];

    $metaRecord->setField('Upload_Filename_Original_t', $original);
    $metaRecord->setField("Upload_Filename_Temp_t", $temp);

    $result = $metaRecord->commit();
    if(FileMaker::isError($result)){
        $errorTitle = "FileMaker Error";
        $log->error("pushDocumentDataToFM - Failure to open " .$metaResults ." " .$result->getMessage() ." " .$result->getCode());
        processError($result->getMessage(), $result->getErrorString(), "deliverableService.php", $pkId, $errorTitle);
        exit;
    }
}