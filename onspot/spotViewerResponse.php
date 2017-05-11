<?php

/**
 * spotViewerResponse.php
 * Bill Tucker
 * 07/23/2015
 *
 * This code processes action taken from spotviewercontroller.php. The user will have two options to make a change to the approval
 * status radio buttons or to add a new note. The processor takes in to account the dual form arrangement
 * so adding a note is one option while approving a spot.
 *
 * Refactor Notes:
 *
 */

include_once("onspot-config.php");
include_once($fmfiles ."work.db.php");
include_once($utilities ."utility.php");
include_once($errors .'errorProcessing.php');

include_once($validation ."user_validation.php");

if (isset($_GET['pkId'])) {
    $pkId = urldecode($_GET['pkId']);
}else{
    $messageType = "d";
    $errorTitle = "Serious Error";
    $log->error("Serious Error: PK Missing at save time User " .$_SESSION['userName']);
    $message = "A serious error has occurred and you should immediately contact Thought-Development Support for assistance.";
    processError("Missing Primary Key", $message, "spotViewerResponse.php", "", $errorTitle);
    exit;
}

if(!session_id()){
    session_start();
}


//reset the session timeout before saving the data. This will avoid data loss due to session timeout
resetSessionTimeout();

//validate the user prior to save operation do avoid anyone attempting to load data to database without using the form
//if this user was not logged in at save time the redirect them to index page.
$pageUrl = $site_prefix ."/onspot/index.php";
validateUser($site_prefix, $pageUrl, $siteSection, $onSpotView, $OnSpotPluginToValidate);


//Globals used by process notes or approval block information
$spotViewerLayout = "[WEB] cwp_spotviewer_browse";
$userNotesLayout = "[WEB] UserNotes";
$noteType = "Rough Cut Approval";

//Process the Approval information only as a user selected a Approval Radio button
//Since the calling page is now a dual form this code accepts a click event occurred so the data should be
// persisted to FileMaker for each section
if(isset($_POST['userApprover'])){
    $log->debug("Processing approver selection");
    $spotViewerFind = $fmWorkDB->newFindCommand($spotViewerLayout);
    $spotViewerFind->addFindCriterion('__pk_ID', '==' . $pkId);
    $spotResult = $spotViewerFind->execute();

    if(FileMaker::isError($spotResult)){
        $errorTitle = "FileMaker Error";
        $log->error("Search of Spot Viewer record failed on PKID: " .$pkId . " Error: " .$spotResult->getMessage() ." User: " .$_SESSION['userName']);
        $message = "A serious error has occurred and you should immediately contact Thought-Development Support for assistance.";
        processError($spotResult->getMessage(), $spotResult->getErrorString(), "spotViewerResponse.php", $pkId, $errorTitle);
        exit;
    }

    $spotRecords = $spotResult->getRecords();
    $spotRecord = $spotRecords[0];
    $recordItemSet = false;

    if(isset($_POST['approval']))
        $approval = $_POST['approval'];

    if(isset($approval)){
        $recordItemSet = true;
        $spotRecord->setField('Rough_Cut_Approval_YN_t', $approval);
        $spotRecord->setField('Rough_Cut_Approval_Date_d', date('m/d/y'));
        $spotRecord->setField('Rough_Cut_Approved_By_t', $_SESSION['userName']);
    }

    if($recordItemSet){
        $spotSave = $spotRecord->commit();

        if(FileMaker::isError($spotSave)){
            $errorTitle = "FileMaker Error";
            $log->error("Spot Viewer Save operation save failed: " .$spotSave->getMessage() ." User: " .$_SESSION['userName']);
            $message = "A serious error has occurred and you should immediately contact Thought-Development Support for assistance.";
            processError($spotSave->getMessage(), $spotSave->getErrorString(), "spotViewerResponse.php", $pkId, $errorTitle);
            exit;
        }
    }
}

//Process the note information only as a user pushed the Add button/Link
//Since the calling page is now a dual form this code accepts a click event occurred so the data should be
// persisted to FileMaker for each section
if(isset($_POST['userNote'])){
    $log->debug("userNote is set and running");
    if(isset($_POST['notes'])){
        $notes = $_POST['notes'];
    }

    //If the notes record has data then create the record in the layout (No empty record need to be created)
    if(isset($notes) && !empty($notes)){
        $newNoteRecord = $fmWorkDB->createRecord($userNotesLayout);
        $newNoteRecord->setField('Note_Type_t', $noteType);
        $newNoteRecord->setField('_fk_LinkedObject_ID',$pkId);
        $newNoteRecord->setField('Note_Text_t', $notes);
        $newNoteRecord->setField('z_RecCreatedBy', $_SESSION['userName']);

        $addNote = $newNoteRecord->commit();

        if(FileMaker::isError($addNote)){
            $errorTitle = "FileMaker Error";
            $messageType = "d";
            $log->error("Save user note Error: " .$addNote->getMessage() ." User: " .$_SESSION['userName']);
            $message = "A serious error has occurred and you should immediately contact Thought-Development Support for assistance.";
            processError($addNote->getMessage(), $addNote->getErrorString(), "spotViewerResponse.php", $pkId, $errorTitle);
            exit;
        }

        $log->debug("Added record to database with user name: " .$_SESSION['userName']);
    }
}

//redirect to spot viewer page for user to see note added or approver change
header("Location: spotviewercontroller.php?pkId=" .urlencode($pkId));
exit;

?>