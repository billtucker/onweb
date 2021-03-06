<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 12/4/2014
 * Time: 9:24 AM
 * A file to Process Post Request from Request_PHP form
 *
 */

include_once dirname(__DIR__) . DIRECTORY_SEPARATOR . "request-config.php";
include_once("$validation" . "user_validation.php");
include_once($errors . "errorProcessing.php");

//on save operations call utility to reset session timeout to avoid lose of data input on session timeouts
include_once($utilities . "utility.php");
resetSessionTimeout();

//if a user attempts to save data without being logged in so after login the user should go to index page
$pageUrl = $site_prefix . "index.php";

//validate user then check if plugin license if active to access on this page
validateUser($site_prefix, $pageUrl, $siteSection, $onRequestModify, $onRequestPlugin);
$canModify = userCanModifyRequest($okModify);

//If the user had view only access then resend the page back to them unmodified
if (!$canModify) {
    header('Location: ' . $request_site_prefix . 'request.php?pkId=' . urlencode($pkId) . '&message=readOnly');
}

include_once($requestInclude . 'requestConnection.php');


if (isset($_POST['requestsubmit']) || isset($_POST['saveDataLink'])) {

    if (isset($_POST['requestPkId']) && !empty($_POST['requestPkId'])){
        $pkId = $_POST['requestPkId'];
    } else {
        $errorTitle = "Missing Key To Process";
        $log->error("Missing PK value redirect user to error page");
        processError("Missing PKID", "Missing PKID", $pageUrl, "NULL", $errorTitle);
        exit;
    }

    $requestFieldArray = array('Work_Order_Title_t', 'Programming_Type_t', 'Work_Order_Notes_t', 'Request_Approver_List_t',
        'Contact_Company_t', 'Contact_Department_t', 'Contact_Phone_t', 'Show_Code_t', 'Show_EpisodeNumber_t',
        'Show_Episode_Title_t', 'Show_AirDate_t', 'Show_AirTime_ti', 'Contact_pk_Contact_ID_n', 'Request_Status_t');

    $deliverableFormFieldArray = array('Spot_Type', 'Length', 'Notes', 'Rough_Cut_Due_d', 'Final_Due_Date',
        'Flight_Date_Start', 'Flight_Date_End', '__pk_ID');

    getRequest($_POST["requestPkId"]); //get the request handle record $request

//Start --> now save the request portion of the form
    foreach($requestFieldArray as $requestKey) {
        if(isset($_POST[$requestKey])){
			$request->setField($requestKey, $_POST[$requestKey]);
		}else{
			$log->debug("This key: " .$requestKey ." is not set in POST array find out why!!");
		}
    }

//TODO: add to request created_by or some DB property to identify Web Site User/username

    /**  Commit data to database (TODO No Validation At this point) **/
    $result = $request->commit();
    if (FileMaker::isError($result)) {
        $errorTitle = "FileMaker Error";
        $log->error("Failure to save " . $deliverableLayout . " " . $result->getMessage() . " " . $result->getCode());
        processError($result->getMessage(), $result->getErrorString(), "processRequest.php", $pkId, $errorTitle);
    }
//Finished --> save request record

    /** $projectReqDelRelatedSets holds Requested Project List (1) items */
    $deliverableLayout = '[WEB] Project Deliverable';
    $DeliverableFind = $fmOrderDB->newFindCommand($deliverableLayout);
    $DeliverableFind->addFindCriterion('_fk_Request_pk_ID', '==' . $pkId);
    $deliverableResults = $DeliverableFind->execute();
    //This is the control if no deliverable records exist the skip the deliverable save operation
    $processDeliverable = true;

    if (FileMaker::isError($deliverableResults)) {
        if ($deliverableResults->getCode() == $noRecordsFound) {
            $processDeliverable = false; //No records found so skip save operation for deliverable records
        } else {
            $errorTitle = "FileMaker Error";
            $log->error("Failure to open " . $deliverableLayout . " " . $deliverableResults->getMessage() . " " . $deliverableResults->getCode());
            processError($deliverableResults->getMessage(), $deliverableResults->getErrorString(), "processRequest.php", $pkId, $errorTitle);
            exit;
        }
    } else {
        $projectReqDelRelatedSets = $deliverableResults->getRecords(); //if no errors then get all records
    }

    if ($processDeliverable) {
        /** Iterate through the related set and save actual form data and commit per record */
        /** TODO lets review this loop to improve the functionality  (should we use a FileMaker function over homemade solution) */
        $pkFieldName = "__pk_ID";
        $dex = 1;
        $underLine = "_";

        //reusing related sets object from original pull of database
        foreach ($projectReqDelRelatedSets as $delSet) {
            foreach ($deliverableFormFieldArray as $field) {
                if ($delSet->getField("__pk_ID") == $_POST[$pkFieldName . $underLine . $dex]) {
                    $rawField = $field . $underLine . $dex;
                    $delSet->SetField($field, $_POST[$rawField]);
                } else {
                    echo("<p>No PK ID Match Found " . $delSet->getField("__pk_ID")
                        . " matching this " . $_POST[$pkFieldName . $underLine . $dex] . " field " . $pkFieldName . $underLine . $dex . "</p>");
                }
            }
            $dex++;

            $deliverableResult = $delSet->commit();
            if (FileMaker::isError($deliverableResult)) {
                $errorTitle = "FileMaker Error";
                $log->error("Failure to delete " . $deliverableLayout . " " . $deliverableResult->getMessage() . " " . $deliverableResult->getCode());
                processError($deliverableResult->getMessage(), $deliverableResult->getErrorString(), "processRequest.php", $pkId, $errorTitle);
            }
        }
    }//end of if processDeliverable


    if (isset($_POST['saveDataLink'])) {
        if (isset($_POST['pkToUse'])) {
            $deliverablePk = $_POST['pkToUse'];
        }


        $log->debug("**** Form data was changed so save data from the form and forward ****");
        $log->debug("With Deliverable PK: " . $deliverablePk);
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Pragma: no-cache');
        header("Location: " . $request_site_prefix . "deliverableview.php?pkId=" . $deliverablePk);
    } else {
        $log->debug("----- Saved Request form data now return to request form ----");
        header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Pragma: no-cache');
        header('Location: ' . $request_site_prefix . 'request.php?pkId=' . urlencode($pkId) . '&message=success');
    }


} elseif (isset($_POST['add-deliverable'])) {

    //TODO: This code is a duplicate of request "All" process figuree this out Soon
    if (isset($_POST['requestPkId']) && !empty($_POST['requestPkId'])){
        $pkId = $_POST['requestPkId'];
    } else {
        $errorTitle = "Missing Key To Process";
        $log->error("Missing PK value redirect user to error page");
        processError("Missing PKID", "Missing PKID", $pageUrl, "NULL", $errorTitle);
        exit;
    }

    $log->debug("Adding deliverable record start with PKID: " . $pkId);

    //This is new definition of calling a script in FM from API 11-16-2015
    $scriptLayout = "[WEB] Project Request";
    $scriptName = "AddDeliverableToRequest(Request_pk_ID)";
    $addProject = $fmOrderDB->newPerformScriptCommand($scriptLayout, $scriptName, $pkId);

    $addProjectResult = $addProject->execute();

    if (FileMaker::isError($addProjectResult)) {
        $errorTitle = "FileMaker Error";
        $log->error("Failure to add deliverable " . $deliverableLayout . " " . $addProjectResult->getMessage() . " " . $addProjectResult->getCode());
        processError($addProjectResult->getMessage(), $addProjectResult->getErrorString(), "processRequest.php", $pkId, $errorTitle);
    }

    $log->debug("Adding deliverable record to Request ID: " . $pkId);

    header('Cache-Control: no-cache, no-store, max-age=0, must-revalidate');
    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Pragma: no-cache');

    header('Location: ' . $request_site_prefix . 'request.php?pkId=' . urlencode($pkId) . '&message=delvAdded');

} else if(isset($_POST['deleteDelivPk'])){
    $pkToDelete = $_POST['deleteDelivPk'];
    $log->debug("Step 1. Did we get the PK: " .$pkToDelete);

    $deliverableLayout = '[WEB] Project Deliverable';
    $DeliverableFind = $fmOrderDB->newFindCommand($deliverableLayout);
    $DeliverableFind->addFindCriterion('__pk_ID', '==' . $pkToDelete);
    $deliverableResults = $DeliverableFind->execute();
    //This is the control if no deliverable records exist the skip the deliverable save operation
    $processDeliverable = true;

    if (FileMaker::isError($deliverableResults)) {
        $errorTitle = "FileMaker Error";
        $log->error("Failure to open " . $deliverableLayout . " " . $deliverableResults->getMessage() . " " . $deliverableResults->getCode());
        processError($deliverableResults->getMessage(), $deliverableResults->getErrorString(), "processRequest.php", $pkId, $errorTitle);
        exit;
    } else {
        //Get all records after error test
        $projectReqDelRelatedSets = $deliverableResults->getRecords();
    }

    $deliveriableRecord = $projectReqDelRelatedSets[0];
    $deliveriableRecId = $deliveriableRecord->getRecordId();

    $log->debug('We have the recordId: ' .$deliveriableRecId);

    $deleteRecord = $fmOrderDB->newDeleteCommand($deliverableLayout, $deliveriableRecId);

    $deleteResults = $deleteRecord->execute();

    if(FileMaker::isError($deleteResults)){
        $errorTitle = "FileMNaker Error";
        $log->error("Failed to delete Deliverable PK: " .$pkToDelete . " RecordId: " .$deliveriableRecId ." Error Message: " .$deleteResults->getMessage());
        processError($deleteResults->getMessage(), $deleteResults->getErrorString(), "processRequest.php", $pkToDelete, $errorTitle);
        exit;
    }
    $log->debug("Successfully deleted Deliveriable Record PK: " .$pkToDelete ." RecId: " .$deliveriableRecId);
    echo "success";
} else {
    //TODO This should go to a clean Error page like error404.php
    $log->debug("This is a error condition and the script or processing never happened");
    header('Location: ' . $request_site_prefix . 'request.php?pkId=' . urlencode($pkId) . '&message=Error');
}
?>