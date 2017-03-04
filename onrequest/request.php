<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/3/2014
 * Time: 3:35 PM
 * Modified Date 02-12-2015
 */

include_once("request-config.php");
include_once($requestService .'displayLinkWithJavaScript.php');

//validate user prior to access on this page
include_once("$validation" ."user_validation.php");
include_once($utilities ."utility.php");

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$validateUser = true;
$canModify = false;

if(isset($_GET['p1']) && validatePassThrough($_GET['p1'])){
    $validateUser = false;
    $log->debug("request.php - Print operation invoked and login is being bypassed");
}

if($validateUser) {
    $log->debug("Now validate if Plugin: (" .$onRequestPlugin .") is installed");
    //validate user then check if plugin license if active to access on this page
    validateUser($site_prefix, $pageUrl, $siteSection, $onRequestModify, $onRequestPlugin);
    $canModify = userCanModifyRequest($okModify);
}

//User is now validated so lets start the $_SESSION[] to use the properties on this page
//depending onm the user path through the system we need to ensure that the session was started
if(!session_id()){
    session_start();
}

$log->debug("Start including constants, request connection, and other files");
include_once($requestInclude .'constants.php');
include_once($requestInclude .'requestConnection.php');
include_once($requestService .'requestFieldBuilder.php');
include_once($utilities .'utility.php');
include_once($errors .'errorProcessing.php');
include_once($fmfiles ."order.db.php");
include_once ($requestService ."loadRequestSessionData.php");

//Now start the session if not set.. Maybe overkill with extra code lines but lets check and start if required
//Maybe move this to config file since its included in all files
if(!session_id()){
    session_start();
}

$log->debug("End including constants, request connection, and other files");

if(isset($_GET['pkId']) && !empty($_GET['pkId'])){
    $requestPkId = urldecode($_GET['pkId']);
}else{
    $errorTitle = "FileMaker Error";
    $log->error("No PK supplied with this request in query string");
    processError("No Primary Key Supplied for Request","No Primary Key Supplied for Request", $pageUrl, "NA", $errorTitle );
    exit();
}

//Call function in requestConnection.php to get request handle --> $request
getRequest($requestPkId);

$log->debug("Start Have request PK - do Deliverable Find command");

$deliverableFind = $fmOrderDB -> newFindCommand('[WEB] Project Deliverable');
$deliverableFind ->addFindCriterion('_fk_Request_pk_ID', '==' .$requestPkId);
$deliverableResults = $deliverableFind -> execute();

if(FileMaker::isError($deliverableResults)){
    if($deliverableResults->getCode() == $noRecordsFound){
        $log->debug("No match deliverable records found Request PK: " .$requestPkId);
    }else{
        $errorTitle = "FileMaker Error";
        $log->error($deliverableResults->getMessage(), $deliverableResults->getErrorString(), $pageUrl, $requestPkId, $site_prefix);
        processError($deliverableResults->getMessage(), $deliverableResults->getErrorString(), $pageUrl, $requestPkId, $errorTitle);
        exit;
    }
}else{
    $projectReqDelRelatedSets = $deliverableResults->getRecords();
    $deliverableRecord = $deliverableResults->getFirstRecord();
}

$log->debug("End now have Deliverable record. Start get Show Codes");

//get all available Programming_t and load them to sessions
$requestUIProgammingField = "UI_ValueList_ProgrammingType_ct";
$allProgrammingTypes = convertPipeToAnArray($request->getField($requestUIProgammingField));

$log->debug("Start Show Code list from FM or just Session");
$showCodeFieldName = 'Show_Code_t';

$requestDivisionFieldName = "Programming_Type_t";
$requestDivision = $request->getField($requestDivisionFieldName);

//New test added to determine if Request was assigned a division or programming_type_value If not we use 1st value
//from the user_accounts of session. This is considered a temporary fix as future Requests created via index page buttons
//will automatically assign a division or programming_type_t value
if(empty($requestDivision) || $requestDivision == ""){
    $requestDivision = $_SESSION['user_accounts'][0];

    //If programming_type_t is null from FM then use $_SESSION['user_accounts'][0] value. Now write that assignment to the request
    //record to ensure it is set ofr deliverable record
    $saveFind = $fmOrderDB->newFindCommand('[WEB] Project Request');
    $saveFind -> addFindCriterion('__pk_ID', '==' .$requestPkId);
    $saveFinds = $saveFind -> execute();

    /** Check for errors and if so send to error processing and report to user */
    if(FileMaker::isError($saveFinds)){
        $errorTitle = "FileMaker Error";
        $log->error($saveFinds->getMessage(), $saveFinds->getErrorString(), $pageUrl, $pkId, $site_prefix);
        processError($saveFinds->getMessage(), $saveFinds->getErrorString(), $pageUrl, $pkId, $errorTitle);
        exit;
    }

    /** Now pull all records of find command for Requests (Parent of Meta data) and pull the first record of the array */
    $saveRequests = $saveFinds -> getRecords();
    $saveRecord = $saveRequests[0];
    $saveRecord->setField('Programming_Type_t', $requestDivision);
    $saveRequest = $saveRecord->commit();

    if(FileMaker::isError($saveRequest)){
        $errorTitle = "FileMaker Error";
        $log->error("Failed save Programming type to request", $saveRequest->getMessage(), $saveRequest->getErrorString(), $pageUrl, $requestPkId, $site_prefix);
        processError($saveRequest->getMessage(), $saveRequest->getErrorString(), $pageUrl, $requestPkId, $errorTitle);
        exit();
    }

    $log->debug("Programming type was empty is now set to: " .$requestDivision ." For PK: " .$requestPkId);
}

//Now load session data for show codes
if(!isset($_SESSION[$showCodesSessionIndex])){
    $showCodeItems = loadShowCodeSessionData($fmOrderDB, $allProgrammingTypes, $requestPkId, $pageUrl);
}else{
    $log->debug("Use Session to load showCodeItems array");
    $showCodeItems = $_SESSION[$showCodesSessionIndex];
}

//End Code change to use SESSION Programming_Type_t if no value stored on record

$log->debug("End Show Code list from FM or just Session");

//The software is no longer attempting to pull Episode number list 02/12/2015
$requestorEpisodeNumberName = "Show_EpisodeNumber_t";

//This must stay in place until an understanding is had about use case
//$requestDivisionFieldName = "Programming_Type_t";
//Yet another refactor to avoid possible PHP exceptions being thrown by ensuring a List is an array
//Note: the worst case from this code is an empty array to populate the dropdown
$programmingPipeItems = $request->getField('UI_ValueList_ProgrammingType_ct');
$programmingItems = array();
if(isset($programmingPipeItems)){
    $programmingItems = convertPipeToArray($programmingPipeItems);
    if(!is_array($programmingItems)){
        $programmingItems = array($programmingItems);
    }
}

//The following will attempt to load a FileMaker Field with an expected set of values for the status dropdown
//If the field contains one or no values the $requestStatusArray will not throw an exception when processed
//Note: the worst case from this code is an empty array to populate the dropdown
$requestStatusName = 'Request_Status_t';
$requestStatusPipeList = $request->getField('UI_ValueList_RequestStatus_ct');
$requestStatusArray = array();
if(isset($requestStatusPipeList)){
    $requestStatusArray = convertPipeToArray($requestStatusPipeList);
    if(!is_array($requestStatusArray)){
        $requestStatusArray = array($requestStatusArray);
    }
}


//Removed query of list ands modified dropdown to input box 02/12/2015
$requestorsByDepartmentName = "Contact_pk_Contact_ID_n";

//load SpotTypes from FM DB or Session
if(!isset($_SESSION[$spotTypesSessionIndex])){
    $spotTypeArray = loadSpotTypesSessionData($fmOrderDB, $allProgrammingTypes);
}else {
    $log->debug('Using Session to load Spot Types');
    $spotTypeArray = $_SESSION[$spotTypesSessionIndex];
}

$spotTypeName = "Spot_Type";
//This is a raw pipe delimited list so convert everything to use arrays from $_SESSION[]
//$spotTypePipeList = $request->getField('UI_ValueList_Spot_Types_ct');

$lengthName = "Length";
$lengthPipeList = $request->getField('UI_ValueList_ProjectLengths_ct');
if(isset($lengthPipeList)){
    $lengthItems = convertPipeToArray($lengthPipeList);
}

//Load Tag Versions from DB or Session
if(!isset($_SESSION[$tagsSessionIndex])){
    $tagsArray = loadTagsSessionData($fmOrderDB, $allProgrammingTypes);
}else{
    $log->debug("Using Sessiopn to load Tags");
    $tagsArray = $_SESSION[$tagsSessionIndex];
}

//Load version Descriptor from DB or Session Once
If(!isset($_SESSION[$versionDescriptorSessionIndex])){
    $versionDescriptorArray = loadVersionDecriptorSessionData($fmOrderDB, $allProgrammingTypes);
}else{
    $log->debug("Using Session to load Version Descritors");
    $versionDescriptorArray = $_SESSION[$versionDescriptorSessionIndex];
}

//1. Do not include HTML header record until most processing is completed. The error page cannot be called "Redirected"
//after HTML header is called.
//2. Determine which header should be presented based on site level position
$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
include_once($headerFooter .$headerToUse);

// This imports the scrolling Save button for the request view. Placing the declaration here avoids
// null field Javascript errors
//TODO figure this one out as the JS directory dose not exist at the Request level. For now hardcode the value
echo("\n<script type='text/javascript' src='../js/tdc-request-save-scroll-button.js'></script>\n");

$log->debug("Now have required all fields now build HTML");
?>
    <!-- TODO review form and move styles to css form -->
    <!-- form style was added to keep elements  from shifting right (Yes Strange but true) -->
    <form id="request-form" name="request-form" action="processing/processRequest.php" method="post" role="form">

        <!-- added for refactor of requestConnection.php -->
        <input type="hidden" name="requestPkId" id="requestPkId" value="<?php echo($requestPkId);?>">

        <!-- Dynamic Request Type Show Package / Promo .... -->
        <div class="text-center tdc-title-text-color"><h4><strong><?php echo($request->getField('Request_Type_t')); ?></strong></h4></div>

        <!-- scrolling save button which hidden until a form change -->
        <button class="btn btn-info" id="requestsubmit" type="submit" name="requestsubmit" style="visibility: hidden;">Save Changes</button>


        <table class="table table-bordered nopadding" ><!--Start Request Data Table-->
            <thead style = "height: 10px !important;" >
            <th class="tableTDHeaderDef" ><!-- # symbol -->
                <?php echo($labelRecord->getField('#'));?>
            </th >
            <th colspan = "2" class="tableTDHeaderDef"><!-- Request Title Label -->
                Request Title
            </th >
            <th class="tableTDHeaderDef" ><!-- Division Label -->
                <?php echo($labelRecord->getField('Programming_Type_t')); ?>
            </th >
            <th colspan = "2" class="tableTDHeaderDef" ><!-- Request Notes Title -->
                Request Notes
            </th >
            <th class="tableTDHeaderDef" ><!-- Submit To -->
                <?php echo($labelRecord->getField('Submit_To_t')); ?>
            </th>
            <th class="tableTDHeaderDef" ><!-- Status -->
                <?php echo($labelRecord->getField('Status_t')); ?>
            </th>
            </thead>
            <tr >
                <td style = "border-bottom: 0px" ><!-- # Symbol request number Data Field -->
                    <?php echo($request->getField('Project_Request_Num_n'));?>
                </td>
                <td colspan = "2" ><!-- Request Title Data Field -->
                    <input class="tdc-input-xs form-control" type = "text" name="Work_Order_Title_t" id="Work_Order_Title_t" value="<?php echo($request->getField('Work_Order_Title_t')); ?>">
                </td >
                <td ><!-- Division dropdown Data Field -->
                    <!-- TODO Remove this comment line after testing of Refactor (02/13/2015) -->
                    <select class="tdc-input-xs form-control" id="<?php echo($requestDivisionFieldName); ?>" name="<?php echo($requestDivisionFieldName); ?>" onchange="replaceAllDivisionDropdowns(this);">
                        <?php
                        $programmingItem = $request->getField($requestDivisionFieldName);
                        if(isset($programmingItem) && (strlen($programmingItem) > 0)){
                            //TODO create new drop down to select item from  $_SESSION['user_accounts'] session variable 07/7/2016
                            buildRequestDropDownListWithValue($programmingItems, $programmingItem);
                        }else {
                            //This field should never empty so use the [0] value of session array
                            buildRequestDropDownListWithValue($programmingItems, $requestDivision);
                        }
                        ?>
                    </select>
                </td>
                <td colspan = "2" rowspan = "3" ><!-- Request Notes Data Field -->
                <textarea class="tdc-textarea-font-size form-control" cols = "30" rows = "6" id="Work_Order_Notes_t" name="Work_Order_Notes_t"><?php
                    $workOrderNotes = $request->getField('Work_Order_Notes_t');
                    if(isset($workOrderNotes)){
                        echo($workOrderNotes);
                    }
                    ?></textarea>
                </td >
                <td rowspan = "3" ><!-- Submit To -->
                <textarea class="tdc-textarea-font-size form-control" cols = "10" rows = "6" name="Request_Approver_List_t" id="Request_Approver_List_t" disabled><?php
                    $requestApproverList = $request->getField('Request_Approver_List_t');
                    if(isset($requestApproverList) && (strlen($requestApproverList) > 0)){
                        echo($requestApproverList);
                    }
                    ?></textarea>
                </td >
                <td rowspan = "3" ><!-- Status block start -->
                    <select class="tdc-input-xs form-control" id="<?php echo($requestStatusName); ?>" name="<?php echo($requestStatusName); ?>"
                            onchange="displayMetaErrors(this.id, document.getElementById('requestPkId').value,'<?php echo $homepage; ?>');">
                        <?php
                        $requestStatusSelected = $request->getField($requestStatusName);
                        if(isset($requestStatusSelected) && (strlen($requestStatusSelected) > 0)){
                            buildRequestDropDownListWithValue($requestStatusArray, $requestStatusSelected);
                        }else {
                            buildRequestDropDownList($requestStatusArray);
                        }
                        ?>
                    </select>
                    <p class="form-control-static text-center" >
                        <!-- <?php echo($request->getField('Request_Status_Date_d'));?> <?php echo($request->getField('Request_Status_Time_i')); ?> -->
                    </p>
                    <p class="form-control-static text-center" >
                        <!-- <?php echo($request->getField('Request_Status_By_t')); ?> -->
                    </p>
                </td><!-- End Status Block  -->
            </tr>
            <tr>
                <td style = "border-left: 1px solid #d3d3d3; border-top: 0px; border-bottom: 0px;" >&nbsp;</td ><!-- Empty column to support table formatting --><!-- TODO move style to css -->
                <td class="tableTDHeaderDef" ><!-- Primary Contact Label -->
                    Primary Contact Dept/Name
                </td >
                <td class="tableTDHeaderDef" ><!-- Primary Company Department label -->
                    Primary Company/Dept.
                </td>
                <td class="tableTDHeaderDef" ><!-- Contact Phone Label -->
                    Contact Phone
                </td>
            </tr>
            <tr>
                <td style = "border-left: 1px solid #d3d3d3; border-top: 0px" >&nbsp;</td><!-- Empty column to support table formatting --><!-- TODO move style to css -->
                <td><!-- Primary Contact Dept/Name Dropdown list chnage ot text input 02/12/2015-->
                    <input class="tdc-input-xs form-control" type="text" name="<?php echo($requestorsByDepartmentName);?>"
                           id="<?php echo($requestorsByDepartmentName); ?>"
                           value="<?php echo($request->getField($requestorsByDepartmentName));?>">
                </td>
                <td><!-- Contact and Department Fields TODO: This will change so keep this note-->
                    <input type = "text" class="tdc-input-xs form-control" name="Contact_Company_t" id="Contact_Company_t"
                           placeholder="Company" value="<?php echo($request->getField('Contact_Company_t')); ?>">
                    <input type="text" class="tdc-input-xs form-control" name="Contact_Department_t" id="Contact_Department_t"
                           placeholder="Department" value="<?php echo($request->getField('Contact_Department_t')); ?>">
                </td>
                <td><!-- Contact Phone Field -->
                    <input type="text" class="tdc-input-xs form-control" name="Contact_Phone_t" id="Contact_Phone_t"
                           value="<?php echo($request->getField('Contact_Phone_t')); ?>" >
                </td>
            </tr>
            <tr><!-- Put Series Title information here to join the tables -->
                <table class="table table-bordered nopadding" ><!--Start Series Title Information Table-->
                    <thead>
                    <th class="col-xs-2 col-md-2 tableTDHeaderDef" ><!-- Series Title Label -->
                        <?php echo($labelRecord->getField('Show_Title')); ?>
                    </th>
                    <!--needs column < td> span-->
                    <th class="col-xs-1 col-md-1 tableTDHeaderDef" ><!-- Epis # Label -->
                        <?php echo($labelRecord->getField('Epis_Num')); ?>
                    </th>
                    <th class="col-xs-3 col-md-3 tableTDHeaderDef" ><!-- Epis/Movie Title Label -->
                        <?php echo($labelRecord->getField('Episode_Title')); ?>
                    </th>
                    <!--needs column < td> span-->
                    <th class="col-xs-2 col-md-2 tableTDHeaderDef" ><!-- Series Air Date  Label -->
                        <?php echo($labelRecord->getField('Show_Air_Date')); ?>
                    </th>
                    <th class="col-xs-2 col-md-2 tableTDHeaderDef"><!-- Time Label -->
                        <?php echo($labelRecord->getField('Show_AirTime_ti')); ?>
                    </th>
                    </thead>
                    <tr>
                        <td class="col-xs-2 col-md-2"><!-- Series title (NOW) using Show_Code field Data Field Dropdown-->
                            <select class="tdc-input-xs form-control" id="<?php echo($showCodeFieldName); ?>" name="<?php echo($showCodeFieldName); ?>">
                                <?php
                                //TODO use $_SESSION['user_accounts'] session  variable to build the show codes or better yet show titles
                                $showCodeNameSelected = $request->getField($showCodeFieldName);
                                if(isset($showCodeNameSelected) && strlen($showCodeNameSelected) > 0){
                                    buildSessionDropDownMapWithValue($showCodeItems[$requestDivision], $showCodeNameSelected);
                                    //buildDropDownWithFieldsWithValue($showCodeItems, $showCodeNameSelected);
                                }else{
                                    buildSessionDropDown($showCodeItems[$requestDivision]);
                                    //buildRequestDropDownWithFields($showCodeItems);
                                }
                                ?>
                            </select>
                        </td>
                        <!--needs column < td> span-->
                        <td class="col-xs-1 col-md-1"><!-- Episode Number Data Field -->
                            <input class="tdc-input-xs form-control" type="text" name="<?php echo($requestorEpisodeNumberName); ?>" value="<?php echo($request->getField('Show_EpisodeNumber_t')); ?>">
                        </td>
                        <td class="col-xs-3 col-md-3"><!-- Show Episode Title Data Field -->
                            <input class="tdc-input-xs form-control" type = "text" name="Show_Episode_Title_t" id="Show_Episode_Title_t"
                                   value="<?php echo($request->getField('Show_Episode_Title_t')); ?>">
                        </td>
                        <!--needs column < td> span-->
                        <td class="col-xs-2 col-md-2"><!-- Series Air Date Field -->
                            <!-- Note on this Date/Time Field: air_date is used by JQuery and Show_AirDate_t is used by FileMaker -->
                            <div class="input-group date air_date" data-date = "" data-date-format="mm/dd/yyyy"
                                 data-link-field = "Show_AirDate_t" data-link-format = "dd/mm/yyyy">
                                <input class="tdc-input-xs form-control" type = "text" value="<?php echo($request->getField('Show_AirDate_t')); ?>" size = "16">
                                <span class="input-group-addon">
                                    <span class="glyphicon glyphicon-calendar"></span>
                                </span >
                            </div>
                            <input type = "hidden" id = "Show_AirDate_t" name="Show_AirDate_t" value="<?php echo($request->getField('Show_AirDate_t')); ?>" />
                        </td><!-- End Series Air Date Field -->
                        <td class="col-xs-2 col-md-2"><!-- Start Time Data Field -->
                            <!-- Note on this Date/Time Field: air_time is used by JQuery and Show_AirTime_ti is used by FileMaker -->
                            <div class="input-group date air_time" data-date="" data-date-format="hh:ii"
                                 data-link-field="Show_AirTime_ti" data-link-format="hh:ii" >
                                <input class="tdc-input-xs form-control" size="6" type="text" value="<?php echo($request->getField('Show_AirTime_ti')); ?>">
                        <span class="input-group-addon" >
                            <span class="glyphicon glyphicon-time"></span>
                        </span>
                            </div>
                            <input type = "hidden" id="Show_AirTime_ti" name="Show_AirTime_ti" value="<?php echo($request->getField('Show_AirTime_ti')); ?>">
                        </td ><!-- End of Time Data Field -->
                    </tr>
                </table ><!--End Series Title Information Table-->
            </tr>
        </table><!--End Request Data Table-->
        <br>

        <!-- <div class="row"> --><!-- Start Requested Project List header row-->
        <?php if(isset($projectReqDelRelatedSets) && !empty($projectReqDelRelatedSets)){ ?>
            <table class="table table-bordered"><!-- Start Requested Project List Table -->
                <thead>
                <tr>
                    <th class="text-left" colspan="6"><strong>Requested Project</strong></th>
                    <th colspan="2" class="tableTDHeaderDef text-center" style="border-right: 0px;">Live/Flight Dates</th><!-- Live/Flight Dates Label -->
                    <th style="border-left: 0px;">&nbsp;</th>
                </tr>
                </thead>
                <thead class="tableTDHeaderDef">
                <tr><!-- TODO add test for canChange return here -- Added 03242015 -->
                    <th class="col-xs-1 col-md-1"><!-- Blue plus icon to add deliverable to project via FM script   -->
                        <?php if($canModify){ ?>
                            <button id="add-deliverable" name="add-deliverable" value="add-deliverable" type="submit"
                                    class="input-group-addon tdc-glyphicon-control tdc-cell-spacing"
                                    style="color:lightskyblue;border: none" aria-hidden="true" title="Add Deliverable to the Request" data-toggle="tooltip">
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                        <?php } else { ?>
                            <button id="add-deliverable" name="add-deliverable" value="add-deliverable" type="submit"
                                    class="input-group-addon tdc-glyphicon-control tdc-cell-spacing"
                                    style="color:lightskyblue;border: none" aria-hidden="true" disabled title="Disabled">
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                        <?php } ?>
                    </th>
                    <!-- Project Type -->
                    <th class="col-xs-1 col-md-1"><?php echo($labelRecord->getField('Spot_Type_t')); ?></th>
                    <!-- Lgth -->
                    <th class="col-xs-1 col-md-1"><?php echo($labelRecord->getField('Length_Short')); ?></th>
                    <!-- Prj Notes -->
                    <th class="col-xs-4 col-md-4"><?php echo($labelRecord->getField('Tag_Group_Notes_t')); ?></th>
                    <th class="col-xs-1 col-md-1">Creative To Requester</th>
                    <!-- Final Due -->
                    <th class="col-xs-1 col-md-1"><?php echo($labelRecord->getField('Final_Due')); ?></th>
                    <!-- Start -->
                    <th class="col-xs-1 col-md-1"><?php echo($labelRecord->getField('Start')); ?></th>
                    <!-- End -->
                    <th class="col-xs-1 col-md-1"><?php echo($labelRecord->getField('End')); ?></th>
                    <th class="col-xs-1 col-md-1 text-right">Edit</th>
                </tr>
                </thead>

                <?php
                $requestedProjectListCounter = 1;
                $underLine = "_"; //Used for selects to assign name and ID(s) for elements


                if(isset($projectReqDelRelatedSets) && !empty($projectReqDelRelatedSets)){
                    foreach($projectReqDelRelatedSets as $projectRelatedSet){ ?>
                        <!-- Start of data row for Project List --><!-- let make sure to add and if statement for can edit -->
                        <tr id="<?php echo("del_deliverable_" .$projectRelatedSet->getField('#'));?>">
                            <td><!-- start of TD with deliverable index and delete icon -->
                                <?php echo($projectRelatedSet->getField('#')); ?><br><br>
                                <button class='btn btn-default btn-sm deleteRow tdc-tag-delete-icon delgrp'
                                        id="<?php echo('deleteDeliverable_' .$projectRelatedSet->getField('#'));?>"
                                        name="<?php echo('deleteDeliverable_' .$projectRelatedSet->getField('#'));?>"
                                        type='button'
                                        onclick="deleteDeliverable(<?php echo($projectRelatedSet->getField('#'));?>);"
                                        title="Delete this record"
                                        data-toggle="tooltip" data-placement='bottom'>
                                    <span class='glyphicon glyphicon-remove-sign delgrp'></span>
                                </button>
                            </td><!-- end of td with index number and delete icon -->
                            <td><!-- Project Type Dropdown Field -->
                                <select class="tdc-request-list-height" name="<?php echo($spotTypeName .$underLine .$requestedProjectListCounter);?>"
                                        id="<?php echo($spotTypeName .$underLine .$requestedProjectListCounter);?>">
                                    <?php
                                    $fmSpotType = $projectRelatedSet->getField('Spot_Type');  //$request->getField('Spot_Type');
                                    if(isset($fmSpotType) && (strlen($fmSpotType) > 0)){
                                        buildSessionDropDownMapWithValue($spotTypeArray[$requestDivision], $fmSpotType);
                                    }else{
                                        buildSessionDropDown($spotTypeArray[$requestDivision]);
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><!-- Length Dropdown List Field -->
                                <select class="tdc-request-list-height" id="<?php echo($lengthName .$underLine .$requestedProjectListCounter); ?>"
                                        name="<?php echo($lengthName .$underLine .$requestedProjectListCounter); ?>">
                                    <?php
                                    $fmLength = $projectRelatedSet->getField('Length');
                                    if(isset($fmLength) && (strlen($fmLength) > 0)){
                                        buildRequestDropDownListWithValue($lengthItems, $fmLength);
                                    }else{
                                        buildRequestDropDownList($lengthItems);
                                    }
                                    ?>
                                </select>
                            </td>
                            <td><!-- Spot Notes Field -->
                                <textarea rows="3" style="width: 100%" name=<?php echo("Notes" .$underLine .$requestedProjectListCounter);?> id=<?php echo("Notes" .$underLine .$requestedProjectListCounter);?>><?php echo($projectRelatedSet->getField('Notes')); ?></textarea>
                            </td>
                            <td><!-- To Creator field -->
                                <?php
                                $roughCutValue = $projectRelatedSet->getField('Rough_Cut_Due_d');
                                buildDateOnlyField("Rough_Cut_Due_d",$roughCutValue, $requestedProjectListCounter);
                                ?>
                            </td>
                            <td><!-- Final Due Date Field -->
                                <?php
                                $finalDueDate = $projectRelatedSet->getField('Final_Due_Date');
                                buildDateOnlyField("Final_Due_Date", $finalDueDate, $requestedProjectListCounter);
                                ?>
                            </td>
                            <td><!-- Start Field -->
                                <?php
                                $startDate = $projectRelatedSet->getField('Flight_Date_Start');
                                buildDateOnlyField("Flight_Date_Start", $startDate, $requestedProjectListCounter);
                                ?>
                            </td>
                            <td><!-- End Field -->
                                <?php
                                $endDate = $projectRelatedSet->getField('Flight_Date_End');
                                buildDateOnlyField("Flight_Date_End", $endDate, $requestedProjectListCounter);
                                ?>
                            </td>
                            <td class="text-right">
                                <?php displayPencilLinkWithJavaScript($requestedProjectListCounter, $projectRelatedSet->getField('__pk_ID')); ?>
                            </td>
                        </tr>
                        <?php $requestedProjectListCounter += 1; ?>
                    <?php } ?><!-- end foreach loop on related sets -->
                <?php } else { ?>
                    <tr><!-- (TODO Check if this empty row fails on load) Start of data row for Project List Empty Project Row-->
                        <td>
                            <?php echo($requestedProjectListCounter); ?><br>
                            <button class="btn btn-sm btn-primary"></button>
                        </td>
                        <td colspan="3"><!-- Project Type Dropdown Field -->
                            <select class="form-control" name="<?php echo($spotTypeName .$underLine .$requestedProjectListCounter);?>"
                                    id="<?php echo($spotTypeName .$underLine .$requestedProjectListCounter);?>">
                                <?php
                                $fmSpotType = $projectRelatedSet->getField('Spot_Type');  //$request->getField('Spot_Type');
                                if(isset($fmSpotType) && (strlen($fmSpotType) > 0)){
                                    buildRequestDropDownListWithValue(convertPipeToArray($spotTypePipeList), $fmSpotType);
                                }else{
                                    buildRequestDropDownList(convertPipeToArray($spotTypePipeList));
                                }
                                ?>
                            </select>
                        </td>
                        <td><!-- Length Dropdown List Field -->
                            <select id="<?php echo($lengthName .$underLine .$requestedProjectListCounter); ?>"
                                    name="<?php echo($lengthName .$underLine .$requestedProjectListCounter); ?>">
                                <?php
                                $fmLength = $request->getField('Length');
                                if(isset($fmLength) && (strlen($fmLength) > 0)){
                                    buildRequestDropDownMapWithValue($lengthItems, $fmLength);
                                }else{
                                    buildRequestDropDownMap($lengthItems);
                                }
                                ?>
                            </select>
                        </td>
                        <td colspan="4"><!-- Project Notes Field -->
                            <input class="form-control" type="text" value="<?php echo($projectRelatedSet->getField('Notes')); ?>"
                                   name=<?php echo("Notes" .$underLine .$requestedProjectListCounter);?>
                                   id=<?php echo("Notes" .$underLine .$requestedProjectListCounter);?> >
                        </td>
                        <td><!-- To Creator field -->
                            <?php
                            $roughCutValue = $projectRelatedSet->getField('Rough_Cut_Due_d');
                            buildDateOnlyField("Rough_Cut_Due_d",$roughCutValue, $requestedProjectListCounter);
                            ?>
                        </td>
                        <td><!-- Final Due Date Field -->
                            <?php
                            $finalDueDate = $projectRelatedSet->getField('Final_Due_Date');
                            buildDateOnlyField("Final_Due_Date", $finalDueDate, $requestedProjectListCounter);
                            ?>
                        </td>
                        <td><!-- Start Field -->
                            <?php
                            $startDate = $projectRelatedSet->getField('Flight_Date_Start');
                            buildDateOnlyField("Flight_Date_Start", $startDate, $requestedProjectListCounter);
                            ?>
                        </td>
                        <td><!-- End Field -->
                            <?php
                            $endDate = $projectRelatedSet->getField('Flight_Date_End');
                            buildDateOnlyField("Flight_Date_End", $endDate, $requestedProjectListCounter);
                            ?>
                        </td>
                        <td class="text-right">
                            <a href="deliverableview.php?pKid=<?php echo(rawurldecode($projectRelatedSet->getField('__pk_ID')));?>
                            &itemId=<?php echo($requestedProjectListCounter);?>">
                                    <span class="input-group-addon tdc-glyphicon-control tdc-cell-spacing icon-red">
                                        <span class="glyphicon glyphicon-pencil" title="Open and view this record"
                                              data-toggle="tooltip" data-placement='top'></span>
                                    </span>
                            </a>
                            <input type="hidden" name=<?php echo("del_pk_id" .$underLine .$requestedProjectListCounter)?>
                            id=<?php echo("del_pk_id" .$underLine .$requestedProjectListCounter)?>
                                   value="<?php echo(rawurldecode($projectRelatedSet->getField('__pk_ID')));?>">
                        </td>
                    </tr>
                <?php }?>
            </table><!-- End Requested Project List Table -->
            <!-- This hidden field represents the number of distribution record associted with this project-->
            <!-- Used in the update Spot Type dropdown list JavaScript method -->
            <!-- thew minus one accounts for increament command at the end of loop for records -->
            <input type="hidden" id="spotindex" name="spotindex" value="<?php echo($requestedProjectListCounter - 1); ?>">
        <?php }else{ ?>
            <!-- <div class="row"> -->
            <!-- Start deliverable items when there is no deliveriable records-->
            <table class="table table-bordered"><!-- Start Requested Project List Table -->
                <thead>
                <tr>
                    <td class="col-md-12 text-left">
                        <?php if($canModify){ ?>
                            <button id="add-deliverable" name="add-deliverable" value="add-deliverable" type="submit"
                                    class="input-group-addon tdc-glyphicon-control tdc-cell-spacing"
                                    style="color:lightskyblue;border: none" aria-hidden="true" title="Open and view this record" data-toggle="tooltip">
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                        <?php } else { ?>
                            <button id="add-deliverable" name="add-deliverable" value="add-deliverable" type="submit"
                                    class="input-group-addon tdc-glyphicon-control tdc-cell-spacing"
                                    style="color:lightskyblue;border: none" aria-hidden="true" tite="Disabled" disabled>
                                <span class="glyphicon glyphicon-plus"></span>
                            </button>
                        <?php } ?>
                    </td>
                </tr>
                </thead>
                <input type="hidden" id="spotindex" name="spotindex" value="<?php echo($requestedProjectListCounter); ?>">
            </table><!-- End Requested Project List Table -->
        <?php } ?>
        <!-- </div > --><!-- End of DIV for ROW holding Requested Projects -->
    </form><!-- End Request form -->
<?php
include_once($headerFooter .'footer.php');
$log->debug("Rendered all HTML end of file");
?>