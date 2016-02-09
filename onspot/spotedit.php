<?php
/**
 * The page will dynamically display Spot Viewer and render blocks of HTML based on user privileges. The page no longer
 * contains html rather it assembles various views based on permissions
 *
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 7/17/2015
 * Time: 1:05 PM
 *
 * Refactor Notes:
 * 1. Refactored page to relocate all HTML outside of page to bring in externally what is need per FileMaker
 *    editing and viewing user privileges
 * 2. Added redirects to a dynamic error accessError page to report in English (For Now) an error has occurred
 * 3. 10/30/2015 integrated Spot Viewer in to onweb site start date
 * 4. 11/03/2015 Modified pageURL generation adding dynamic port detection from onweb-config.php
 * 5. 01/12/2016 Modified code to address PHP Notice messages related PHP errors
 * 6. 01/12/2016 Finally made change to fully use spot path from database over path manipulation
 */

include_once("onspot-config.php");
include_once($utilities ."utility.php");
include_once($validation ."user_validation.php");

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$log->debug(PHP_EOL ."****** Start spotedit.php now check PK and run validation ******");

$bypass = false;
$validateUser = true;

if(isset($_GET['p1']) && validatePassThrough($_GET['p1'])){
    $validateUser = false;
    $log->debug("Print operation invoked and login is being bypassed");
}

//If viewOnly is set to true then by pass any validation for the Spot-Viewer
if(isset($_GET['viewOnly'])){
    $bypass = $_GET['viewOnly'];
}

//Put this in ahead of enabling printing so implemented it would skip the check
if($bypass){
    $validateUser = false;
}

//If the PK is not present then there is no reason to validate the user just redirect them to the error page.
if (isset($_GET['pkId'])) {
    $pkId = urldecode($_GET['pkId']);
}else{
    $messageType = "w";
    $message = "You have attempted to access the ON-Spot Viewer without a valid URL or address The ON-Spot Viewer";
    $message .= " is unable to display the spot requested. Please contact the person who sent the URL or address ask";
    $message .= " them to resend or validate what they sent.";
    header('Location: ' .$site_prefix .'errors/accessError.php?errorMessage=' .$message ."&type=" .$messageType);
    exit;
}

if(!session_id()){
    session_start();
}

//set all privilege properties to false as a default
$spotNotesView = false;
$spotNotesEdit = false;
$approvalView = false;
$approvalEdit = false;
$downloadLink = false;
//This var is switch is used only when user has NotesEdit and not NotesView this allows user to the Users own Notes
$userHasNotes = false;

if(isset($_SESSION['accessLevel'])){
	$log->debug("Access level is set to: " .gettype($_SESSION['accessLevel']));
}else{
	$log->debug("Session is started but nothing is set for the print opertion.");
}


//This should and will only perform a login and minimal security check for logged in users
if($validateUser){
    validateUser($site_prefix, $pageUrl, $siteSection, $onSpotView, $OnSpotPluginToValidate);
    //Test edit or add notes with view as note view is just historical notes of previous commenter's
    $spotNotesView = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotNotesView);
    $spotNotesEdit = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotNotesEdit);


    $approvalView = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotApprovalView);

    if($approvalView){
        $approvalEdit = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotApprovalEdit);
    }

    //Should we show the video download link below actaul video
    $downloadLink = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotVideoDownload);
}


//Now load the record fields from FileMaker load
include_once($fmfiles ."work.db.php");

// formats for dates and times
$displayDateFormat = '%m/%d/%Y';
$displayTimeFormat = '%I:%M:%S %P';
$displayDateTimeFormat = '%m/%d/%Y %I:%M:%S %P';
$submitDateOrder = 'mdy';


//1. first get the spot viewer records
$spotViewerLayout = "[WEB] cwp_spotviewer_browse";
$userNotesLayout = "[WEB] UserNotes";

$log->debug("All permissions setup now open layout search for document by PK");

$spot = $fmWorkDB->newFindCommand($spotViewerLayout);
$spot->addFindCriterion('__pk_ID', '==' . $pkId);

$log->debug("We have the PK, DB access handle and now execute");

$spotRecords = $spot->execute();

if (FileMaker::isError($spotRecords)) {
    if($spotRecords->getMessage() == "No records match the request"){
        $messageType="w";
        $log->info("No matching records for __pk_ID: " .$pkId ." User: " .$_SESSION['userName']);
        $message = "No ON-Spot Viewer records were found using the criteria supplied. Please consult with FileMaker,";
        $message .= "Thought Development Support or your system administrator to assist with rectifying the issue.";
        header('Location:accessError.php?errorMessage=' .$message ."&type=" .$messageType);
        exit;
    }else{
        $messageType = "d";
        $message = "A serious error has occurred, please contact Thought Development Support to correct the issue.";
        $log->error("Customer Message: " .$message ." FileMaker Error: " .$spotRecords->getMessage() ." User: " .$_SESSION['userName']);
        header('Location:accessError.php?errorMessage=' .$message ."&type=" .$messageType);
        exit;

    }
}


$spots = $spotRecords->getRecords();
$record = $spots[0];

//init the videoPath to use to point to video URL
$videoPath = "";
$videoType = "";
$fullVideoLink = "";

//TODO modify this record to use the container if URL exists for container if not then use full path ct field
if(!empty($record->getField('z_ONSPOT_Rough_Media_Store_con'))){
    $container_url = $record->getField('z_ONSPOT_Rough_Media_Store_con');
    $ext = getFileMakerContainerFileExtension($container_url);
    $videoType = getVideoSourceType($ext);
    $fullVideoLink = $fmWorkDB->getContainerDataURL($container_url);
    $log->debug("URL from Container value: " .$fullVideoLink ." video type: " .$videoType);
}else{
    $fullPathField = "z_ONSPOT_Rough_Full_Path_ct";
    $fullVideoLink = $record->getField($fullPathField);
    $ext = getFileExtensionFromURL($fullVideoLink);
    $videoType = getVideoSourceType($ext);
    $log->debug("URL from field Full Path value: " .$fullVideoLink ." video type: " .$videoType);
}
//This will now extract Spot Image full path from FileMaker without injecting path information manually


//----> At this point if viewOnly is true then we want to skip the query for user notes or loading any value lists. <----////

if(!$bypass){
    
    //Approval Value List
    //TODO Uncomment field when the field is available -- Workaround to remove usage of getValueListTwoFields
//    $layout = $fmDB->getLayout($spotViewerLayout);
//    $approvalvalues = $layout->getValueListTwoFields('Approval_List');
    
    $pipedApprovalList = "Yes|No|Re-do";
    $approvalvalues  = convertPipeToArray($pipedApprovalList);

    //Now get the User Notes layout for historical comments
    $sortFieldOne = 'z_RecCreatedDate';
    $sortFieldTwo = "z_RecCreatedTime";
    $note = $fmWorkDB->newFindCommand($userNotesLayout);
    $note->addFindCriterion('_fk_LinkedObject_ID', '==' . $pkId);
    if(!$spotNotesView && $spotNotesEdit){
        $log->info("Secondary search for user records for user: " .$_SESSION['userName']);
        $note->addFindCriterion("z_RecCreatedBy", "==" .$_SESSION['userName']);
        $userHasNotes = true;
    }
    $note->addSortRule($sortFieldOne, 1, FILEMAKER_SORT_DESCEND);
    $note->addSortRule($sortFieldTwo, 2, FILEMAKER_SORT_DESCEND);
    $noteRecords = $note->execute();
    $processNotes = false;

    //Check for errors and no records found is not a true error as it is possible that no comments have been made
    if (FileMaker::isError($noteRecords)) {
        if ($noteRecords->getMessage() != "No records match the request") {
            $messageType = "d";
            $log->error("Serous  Note Error: " . $noteRecords->getMessage() ." User " .$_SESSION['userName']);
            $message = "A serious error has occurred and you should immediately contact Thought-Development Support for assistance.";
            header("Location: accessError.php?errorMessage=" .$message ."&type=" .$messageType);
            exit;
        } elseif ($noteRecords->getMessage() == "No records match the request") {
            $processNotes = false;
            $log->debug("Spot Viewer Notes No user records found using pk: " .$pkId ." User: " .$_SESSION['userName']);
        }
    }else {
        if($userHasNotes || $spotNotesView){
            $processNotes = true;
            $notes = $noteRecords->getRecords();
        }
    }

    $headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
    include_once($headerFooter .$headerToUse);
    include_once($spotRoot ."titleWithVideoView.php");

    //put the if switch here as to which file to include
    //OnSpotView must be enabled to view page this will be caught in user validation and the user will be redirected
    //Editing or viewing blocks are dependant on privileges enabled by the FileMaker [WEB]Login layout in PRO_ORDER
    //If the user falls through the tests then only the upper header with video will appear
    if(($spotNotesView || $spotNotesEdit) && $approvalView){
        $log->debug("Display notesAndApprovalView PHP Code");
        include_once($spotRoot ."notesAndApproveView.php");
    }elseif(($spotNotesView || $spotNotesEdit) && !$approvalView){
        $log->debug("Display notesView PHP Code");
        include_once($spotRoot ."notesView.php");
    }elseif((!$spotNotesView && !$spotNotesEdit) && $approvalView){
        $log->debug("Display approveView PHP Code");
        include_once($spotRoot ."approveView.php");
    }
}else{
    $headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
    include_once($headerFooter .$headerToUse);
    include_once($spotRoot ."titleWithVideoView.php");
}



include_once($headerFooter ."footer.php");
$log->debug("Completed spotedit.php code now display HTML code".PHP_EOL);

?>