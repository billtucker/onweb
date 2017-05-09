<?php
/**
 * The page will dynamically display Spot Viewer and render blocks of HTML based on user privileges. Originally the
 * page was constructed by assembling individual page to make the view. Now the page is controlled via page view
 * and controls will be enabled/disabled via JavaScript and JQuery. The logic on the page will function as previously
 * laid out so the FileMaker interaction code remains in tack and the controls will be set fully via PHP and script.
 *
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 4/12/2017
 * Time: 11:06 AM
 *
 * Note: This page represents a refactor of the spotedit.php to the new controller version.
 *
 */

include_once("onspot-config.php");
include_once($utilities . "utility.php");
include_once($validation . "user_validation.php");
include_once($errors . 'errorProcessing.php');

$pageUrl = urlencode($port . "$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$log->debug("****** Start spotviewercontroller.php now get PK and run validation ******");

$bypass = false;
$validateUser = true;

if (isset($_GET['p1']) && validatePassThrough($_GET['p1'])) {
    $validateUser = false;
    $log->debug("Print operation invoked and login is being bypassed");
}

//If viewOnly is set to true then by pass any validation for the Spot-Viewer
if (isset($_GET['viewOnly'])) {
    $bypass = $_GET['viewOnly'];
}

//Put this in ahead of enabling printing so implemented it would skip the check
if ($bypass) {
    $validateUser = false;
}

//If the PK is not present then there is no reason to validate the user just redirect them to the error page.
if (isset($_GET['pkId'])) {
    $pkId = urldecode($_GET['pkId']);
} else {
    $errorTitle = "FileMaker Error";
    $messageType = "w";
    $message = "You have attempted to access the ON-Spot Viewer without a valid URL or address The ON-Spot Viewer";
    $message .= " is unable to display the spot requested. Please contact the person who sent the URL or address ask";
    $message .= " them to resend or validate what they sent.";
    processError("Missing Primary Key", $message, $pageUrl, "", $errorTitle);
    exit;
}

if (!session_id()) {
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

if (isset($_SESSION['accessLevel'])) {
    $log->debug("Access level is set to: " . gettype($_SESSION['accessLevel']));
} else {
    $log->debug("Session is started but nothing is set for the print opertion.");
}


//This should and will only perform a login and minimal security check for logged in users
if ($validateUser) {
    validateUser($site_prefix, $pageUrl, $siteSection, $onSpotView, $OnSpotPluginToValidate);
    //Test edit or add notes with view as note view is just historical notes of previous commenter's
    $spotNotesView = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotNotesView);
    $spotNotesEdit = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotNotesEdit);


    $approvalView = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotApprovalView);

    if ($approvalView) {
        $approvalEdit = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotApprovalEdit);
    }

    //Should we show the video download link below actaul video
    $downloadLink = canViewOrEdit($_SESSION['accessLevel'], $siteSection, $onSpotVideoDownload);
}


//Now load the record fields from FileMaker load
include_once($fmfiles . "work.db.php");

// formats for dates and times TODO remove this if it the code is not used
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
    if ($spotRecords->getCode() == $noRecordsFound) {
        $messageType = "w";
        $errorTitle = "No matching records found";
        $log->info("No matching records for __pk_ID: " . $pkId . " User: " . $_SESSION['userName']);
        $message = "No ON-Spot Viewer records were found using the criteria supplied. Please consult with FileMaker,";
        $message .= "Thought Development Support or your system administrator to assist with rectifying the issue.";
        processError($spotRecords->getMessage(), $spotRecords->getErrorString(), $pageUrl, $pkId, $errorTitle);
        exit;
    } else {
        $errorTitle = "FileMaker Error";
        $messageType = "d";
        $message = "A serious error has occurred, please contact Thought Development Support to correct the issue.";
        $log->error("Customer Message: " . $message . " FileMaker Error: " . $spotRecords->getMessage() . " User: " . $_SESSION['userName']);
        processError($spotRecords->getMessage(), $spotRecords->getErrorString(), $pageUrl, $pkId, $errorTitle);
        exit;

    }
}


$spots = $spotRecords->getRecords();
$record = $spots[0];

//init the videoPath to use to point to video URL
$videoPath = "";
$videoType = "";
$fullVideoLink = "";
$validVideo = true; //set this as true as we expect that the container will always have valid video extension
$downloadLink = false;
$linkFilename = "";

//05-02-2017: Refactor notes
//Test if a determination can be made as to the container object or URL path field type or can the code make sense
// of the URL from the container field. If no such determination can be made then send an unknown type to the viewer page.
if (!empty($record->getField('z_ONSPOT_Rough_Media_Store_con'))) {
    $container_url = $record->getField('z_ONSPOT_Rough_Media_Store_con');
    //add 05-09-2017 In the case where a link is required as in the case of a PDF we can now show the filename rather than
    // Rough Cut Object
    $linkFilename = $record->getField('z_ONSPOT_Rough_Filename_ct z_ONSPOT_Rough_Filename_ct');
    $log->debug("Did we get the filename from the FileMaker field: " .$linkFilename);
    $fileName = getLightBoxCaption($container_url);
    $typeResult = array();
    if(!empty($fileName)){
        $typeResult = getFileTypeInformation($fileName);
        $ext = getFileMakerContainerFileExtension($container_url);
        $videoType = getVideoSourceType($ext);
        //We still need this to substitute if the user is using an internal server IP from DMZ server
        $getContainerUrl = $fmWorkDB->getContainerDataURL($container_url);
        $fullVideoLink = getUserVideoUrl($getContainerUrl, $serverIP);
        $log->debug("URL from Container value: " . $fullVideoLink . " video type: " . $videoType);
    }else{
        $typeResult[0] = "unknown";
        $typeResult[1] = "unknown";
    }

} else {
    $fullPathField = "z_ONSPOT_Rough_Full_Path_ct";
    $fullVideoLink = $record->getField($fullPathField);
    $fileName = getLightBoxCaption($fullVideoLink);
    $typeResult = array();
    if(!empty($fileName)){
        $typeResult = getFileTypeInformation($fileName);
        $log->debug("URL from field Full Path value: " . $fullVideoLink . " Player Type: " .$typeResult[0]
            ." Source Type: " .$typeResult[1]);
    }else{
        $typeResult[0] = "unknown";
        $typeResult[1] = "unknown";
        $log->debug("Cannot get type of file so send in unknown type");
    }




}
//This will now extract Spot Image full path from FileMaker without injecting path information manually


//----> At this point if viewOnly is true then we want to skip the query for user notes or loading any value lists. <----////

if (!$bypass) {
//This user is fully validated and should be setup to view the page based in this users access level
//TODO get these selections from FM and not using these hard coded values (see TODO above)
    $pipedApprovalList = "Yes|No|Re-do";
    $approvalvalues = convertPipeToArray($pipedApprovalList);

    $noteType = "Rough Cut Approval";
//Now get the User Notes layout for historical comments
    $sortFieldOne = 'z_RecCreatedDate';
    $sortFieldTwo = "z_RecCreatedTime";
    $note = $fmWorkDB->newFindCommand($userNotesLayout);
    $note->addFindCriterion('_fk_LinkedObject_ID', '==' . $pkId);
    $note->addFindCriterion('Note_Type_t', '==' . $noteType);
    if (!$spotNotesView && $spotNotesEdit) {
        $log->info("Secondary search for user records for user: " . $_SESSION['userName']);
        $note->addFindCriterion("z_RecCreatedBy", "==" . $_SESSION['userName']);
        $userHasNotes = true;
    }
    $note->addSortRule($sortFieldOne, 1, FILEMAKER_SORT_DESCEND);
    $note->addSortRule($sortFieldTwo, 2, FILEMAKER_SORT_DESCEND);
    $noteRecords = $note->execute();
    $processNotes = false;

//Check for errors and no records found is not a true error as it is possible that no comments have been made
    if (FileMaker::isError($noteRecords)) {
        if ($noteRecords->getCode() != $noRecordsFound) {
            $errorTitle = "FileMaker Error";
            $messageType = "d";
            $log->error("Serous  Note Error: " . $noteRecords->getMessage() . " User " . $_SESSION['userName']);
            $message = "A serious error has occurred and you should immediately contact Thought-Development Support for assistance.";
            processError($noteRecords->getMessage(), $noteRecords->getErrorString(), $pageUrl, $pkId, $errorTitle);
            exit;
        } elseif ($noteRecords->getCode() == $noRecordsFound) {
            $processNotes = false;
            $log->debug("Spot Viewer Notes No user records found using pk: " . $pkId . " User: " . $_SESSION['userName']);
        }
    } else {
        if ($userHasNotes || $spotNotesView) {
            $processNotes = true;
            $notes = $noteRecords->getRecords();
        }
    }

//Start -- We now have the access level so now setup the JavaScript/JQuery hide/disable variables
    $showApprovalBlock = "show";
    if (!$approvalView) {
        $showApprovalBlock = "hide";
    }

    $showNotesBlock = "show";
    if (!$spotNotesView) {
        $showNotesBlock = "hide";
    }

    //This controls the number of columns allocated to the video/audio/images/documents when not in viedoOnly mode
    //When also do not hide the second column
    $viewerColumnClass = "col-md-9 col-xs-12";
    $hideNotesApproval = "show";
//End -- We now have the access level so now setup the JavaScript/JQuery hide/disable variables
}else{ //If this is a bypass then hide notes and approval blocks (set the disable bit to keep out undefined variables
    $showApprovalBlock = "hide";
    $showNotesBlock = "hide";

    //This controls to hide the Notes Approve blocks and expand the column width to full 12 columns for viewOnly mode
    $viewerColumnClass = "col-md-12 col-xs-12";
    $hideNotesApproval = "hide";
}

$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
include_once($headerFooter . $headerToUse);
include_once("spotviewer.php");

$log->debug("Called in header and header to use " .$headerToUse);

include_once($headerFooter . "footer.php");
$log->debug("Completed spotedit.php code now display HTML code" . PHP_EOL);

?>