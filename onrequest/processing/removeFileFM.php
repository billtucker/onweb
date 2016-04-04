<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/31/2016
 * Time: 3:50 PM
 */

include_once("../request-config.php");
include_once("$validation" ."user_validation.php");
include_once($errors ."errorProcessing.php");
include_once($fmfiles .'order.db.php');

//on save operations call utility to reset session timeout to avoid lose of data input on session timeouts
include_once($utilities ."utility.php");
resetSessionTimeout();

//if a user attempts to save data without being logged in so after login the user should go to index page
$pageUrl = $site_prefix ."index.php";

//validate user then check if plugin license if active to access on this page
validateUser($site_prefix, $pageUrl, $siteSection, $onRequestModify, $onRequestPlugin);
$canModify = userCanModifyRequest($okModify);

//If the user had view only access then resend the page back to them unmodified
if(!$canModify){
    header('Location: ' . $request_site_prefix . 'request.php?pkId=' .urlencode($pkId) .'&message=readOnly');
}

$log->debug("File remove was called");

if(isset($_POST['pkId'])){
   if(!empty($_POST['pkId'])){
       $pkId = $_POST['pkId'];
   }else{
       $log->error("PK ID is empty was not passed");
       exit();
   }

    $log->debug("Begin Find meta records");
    $metaLayout = "[WEB] Project Meta Fields";
    $metaFind = $fmOrderDB->newFindCommand($metaLayout);
    $metaFind->addFindCriterion('__pk_ID','==' .$pkId);
    $canModify = true;

    $metaResults = $metaFind->execute();

    if(FileMaker::isError($metaResults)){
        $errorCheck = 'No records match';
        if(strstr($metaResults->getMessage(), $errorCheck) == false){
            $log->error("Meta Search " .$metaResults->getMessage() ." Error String " .$metaResults->getErrorString()
                ." Page URL: " .urldecode($pageUrl) ." PKID " .$pkId);
            $errorTitle = "FileMaker Error";
            echo ("<p>" .$metaResults->getMessage() ." " .$metaResults->getErrorString() ." " .urldecode($pageUrl) ." " .$pkId ." " .$errorTitle ."</p>");
            exit;
        }
    }else{
        $metaItem = $metaResults->getFirstRecord();
    }

    $log->debug("Have the meta data record now set the container null/empty to include filename and temp filename");

    //As part of the dselete operation set all fields initially set by PHP during upload to empty strings
    $metaItem->setField('Answer_Container_Data_r',"");
    $metaItem->setField('Upload_Filename_Original_t', "");
    $metaItem->setField('Upload_Filename_Temp_t',"");

    $deleteResult = $metaItem->commit();
    if(FileMaker::isError($deleteResult)){
        $log->error("Failed to delete PK: " .$pkId ." Message: " .$deleteResult->getMessage());
        exit;
    }
}else{
    $log->error("PK ID was not part of the post data array");
    exit;
}

return "success";