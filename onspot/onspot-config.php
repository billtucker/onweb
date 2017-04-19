<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/8/2015
 * Time: 3:53 PM
 *
 * **************  This the configuration file that the site should use moving forward *****
 * ************** site will use $spotRoot = $root ."onspot/" as head of directory **********
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."onweb-config.php";

$processing = $spotRoot ."processing" .DIRECTORY_SEPARATOR;

//Start privileges properties
$siteSection = "OnSpot"; //Prefix for On Air On Spot Viewer privilege set so each item of the set will begin with OnSpot
$onSpotView = "View"; //OnSpot and View to access the On Spot Viewer
$onSpotNotesView = "NotesView"; //Notes view allows user to view historical notes from other users
$onSpotNotesEdit = "NotesAdd"; //Notes add allows the user to add a note to the spot regardless of view
$onSpotApprovalView = "ApproveView"; //Approver view allows the user to see approver block
$onSpotApprovalEdit = "ApproveModify"; //Approver modify allows the user to make changes to the approval block
$onSpotVideoDownload = "Download"; //Download is to hide or render video download link below actual video player

//Must be enabled from FileMaker to access OnSpot web site. This is a new check to validate the customer purchased
//The package/plugin from Thought-Development
$onWebPlugin = "ON-WEB";
$OnSpotPluginToValidate = "ON-SPOT";


