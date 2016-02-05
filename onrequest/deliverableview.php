<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/24/2014
 * Time: 5:01 PM
 */


include_once("request-config.php");
include_once($fmfiles ."order.db.php");
include_once($utilities ."utility.php");

//validate user prior to access on this page
include_once("$validation" ."user_validation.php");

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$validateUser = true;
$canModify = false;

if(isset($_GET['p1']) && validatePassThrough($_GET['p1'])){
    $validateUser = false;
	$log->debug("deliverableview.php - Print operation invoked and login is being bypassed");
}

if($validateUser) {
    //validate user then check if plugin license if active to access on this page
    validateUser($site_prefix, $pageUrl, $siteSection, $onRequestModify, $onRequestPlugin);
    $canModify = userCanModifyRequest($okModify);
}

$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
include_once($headerFooter .$headerToUse);

include_once($requestInclude .'constants.php');
include_once($requestInclude .'requestConnection.php');
include_once($requestService .'requestFieldBuilder.php');
include_once($requestService .'metaFieldBuilder.php');
include_once($requestService .'deliverableTagFieldBuilder.php');
include_once($errors .'errorProcessing.php');


/** Assign primary key and item number to be used the db query */
if(isset($_GET['pKid']) && !empty($_GET['pKid'])){
    $deliverablePkId = urldecode($_GET['pKid']);
    $itemId = $_GET['itemId'];
    $log->debug("Deliverable PK: " .$deliverablePkId ." and Item Number: " .$itemId);
}else{
    echo "<p>No PK Issued with URL Stop Processing</p>";
    exit;
}


/** Open the Deliverable Layout and check for errors and assign record found by primary key */
$deliverableView = "[WEB] Project Deliverable";
$deliverableFind = $fmOrderDB->newFindCommand($deliverableView);
$deliverableFind->addFindCriterion('__pk_ID','==' .$deliverablePkId);
$deliverableResults = $deliverableFind->execute();

if(FileMaker::isError($deliverableResults)) {
    $errorTitle = "FileMaker Error";
    $log->error("Deliverable Error Message: " .$deliverableResults->getMessage() ." Error String " .$deliverableResults->getErrorString()
    ." Page URl " .$pageUrl . " PKID " .$deliverablePkId);
    processError($deliverableResults->getMessage(), $deliverableResults->getErrorString(), $pageUrl, $deliverablePkId, $errorTitle);
    exit;
}

$deliverableRecords = $deliverableResults->getRecords();
$deliverableLayout = $deliverableResults->getLayout();
$deliverableRecord = $deliverableRecords[0];

$requestPkId = $deliverableRecord->getField('_fk_Request_pk_ID');

//Call function in requestConnection.php to get $request handle
getRequest($requestPkId);

$log->debug("Begin Find meta records");
$metaLayout = "[WEB] Project Meta Fields";
$metaFind = $fmOrderDB->newFindCommand($metaLayout);
$metaFind->addFindCriterion('_fk_Deliverable_pk_ID','==' .$deliverablePkId);

$metaResults = $metaFind->execute();

if(FileMaker::isError($metaResults)){
    $errorCheck = 'No records match';
    if(strstr($metaResults->getMessage(), $errorCheck) == false){
        $log->error("Meta Search " .$metaResults->getMessage() ." Error String " .$metaResults->getErrorString()
        ." Page URL: " .$pageUrl ." PKID " .$deliverablePkId);
        $errorTitle = "FileMaker Error";
        processError($metaResults->getMessage(), $metaResults->getErrorString(), $pageUrl, $deliverablePkId, $errorTitle);
        exit;
    }
}else{
    $metaRelatedRecords = $metaResults->getRecords();
}

$log->debug("Finished find of meta records");

$log->debug("Begin find of show codes for deliverable view");
$programmingName = "Programming_Type_t";
$webShowCodesLayoutName = "[WEB] Show Codes";
$webShowCodeTitle = "Show_Title_t";
$showCodeName = 'Show_Code_t';
$webShowCodesFind = $fmOrderDB->newFindCommand($webShowCodesLayoutName);
$webShowCodesFind->addFindCriterion($programmingName,'==' .$request->getField($programmingName));
$webShowCodesFind->addFindCriterion($showCodeName,'==' .$request->getField($showCodeName));
$webShowCodesFind->addSortRule($showCodeName, 1, FILEMAKER_SORT_ASCEND);

$log->debug("Search Programming_Type_t using: " .$request->getField($programmingName) ." To search field: " .$programmingName);
$log->debug("Search Show_code_t using: " .$request->getField($showCodeName) ." to search field: " .$showCodeName);

$webShowCodesResults = $webShowCodesFind->execute();

if(FileMaker::isError($webShowCodesResults)){
    if($webShowCodesResults->getMessage() == "No records match the request"){
        $log->debug("No matching records for search of show codes");
    }else{
        $log->error("Search For Show Codes Error: " .$webShowCodesResults->getMessage() ." Error String: " .$webShowCodesResults->getErrorString()
            . " Page URL: " .$pageUrl ." PK ID: " .$deliverablePkId);
        $errorTitle = "FileMaker Error";
        processError($webShowCodesResults->getMessage(), $webShowCodesResults->getErrorString(), $pageUrl, $deliverablePkId, $errorTitle);
        exit;
    }
}else{
    $webShowCodeRecords = $webShowCodesResults->getRecords();
    $webShowCodeRecord = $webShowCodesResults->getFirstRecord();
}

$log->debug("End find of show codes for deliverable view");

/** Project Type uses UI_Spot_Types_ValueList_ct field a special Pipe Delimited values **/
$spotTypeName = "Spot_Type";
$spotTypePipeList = $request->getField('UI_ValueList_Spot_Types_ct');


$lengthName = "Length";
$lengthPipeList = $request->getField('UI_ValueList_ProjectLengths_ct');
if(isset($lengthPipeList)){
    $lengthItems = convertPipeToArray($lengthPipeList);
}

$requestStatusName = 'Request_Status_t';

$log->debug("Begin find tags command");
//Tags layout FM API access
$tagsLayout = "[WEB] Project Deliverable Tags";
$tagsFind = $fmOrderDB->newFindCommand($tagsLayout);
$log->debug("Add search criteria Deliverable PKID: " .$deliverablePkId);
$tagsFind->addFindCriterion("_fk_Deliverable_pk_ID", '==' . $deliverablePkId);
$tagsResults = $tagsFind->execute();

if (FileMaker::isError($tagsResults)) {
    if($tagsResults->getMessage() == "No records match the request"){
        $log->debug("No matching Tag records found found for deliveriable PKID: " .$deliverablePkId);
    }else{
        $log->error("Tag Search Error Message: " .$tagsResults->getMessage() ." Error String: " .$tagsResults->getErrorString()
            . " Page URL: " .$pageUrl ." PK ID: " .$deliverablePkId);
        $errorTitle = "FileMaker Error";
        processError($tagsResults->getMessage(), $tagsResults->getErrorString(), $pageUrl, $deliverablePkId, $errorTitle);
        exit;
    }
}

$tagRecords = $tagsResults->getRecords();
$tagRecordCount = $tagsResults->getFoundSetCount();
$log->debug("End find tags command");

$promoCodeVersionValueList = array();
$promoCodeVersionDisplayList = array();

$deliverableTagsField = "UI_ValueList_Tag_Vers_ct";
$deliverableTagList = $deliverableRecord->getField($deliverableTagsField);

//TODO ddd this back in when FM script is fixed
if(!empty($deliverableTagList)){
    foreach(convertPipeToArray($deliverableTagList) as $tag){
        array_push($promoCodeVersionValueList, $tag);
        array_push($promoCodeVersionDisplayList, convertTagDisplay($tag));
    }
}
$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
include_once($headerFooter .$headerToUse);

// This imports the scrolling Save button for the deliverable view. Placing the declaration here avoids
// fixes null field Javascript errors
//TODO adjust this hardcoded reference to the JS directory
echo("\n<script type='text/javascript' src='../js/tdc-deliverable-save-scroll-button.js'></script>\n");

?><!-- end of supporting PHP code -->

<!-- Dynamic Request Type Show Package / Promo .... -->
<div class="text-center tdc-title-text-color"><h4><strong><?php echo($request->getField('Request_Type_t')); ?></strong></h4></div>

<table class="table table-bordered nopadding"><!-- Start request Data Table -->
    <thead>
    <tr class="tableTDHeaderDef">
        <th><!-- # symbol -->
            <?php echo($labelRecord->getField('#'));?>
        </th>
        <th colspan="2"><!-- Request Title Label -->
            Request Title <!-- TODO add title Request Title to i Table -->
        </th>
        <th><!-- Network Label -->
            <?php echo($labelRecord->getField('Programming_Type_t')); ?>
        </th >
        <th colspan = "2"><!-- Request Notes Title -->
            Request Notes <!-- TODO add title Request Notes to i Table -->
        </th >
        <th><!-- Submit To -->
            <?php echo($labelRecord->getField('Submit_To_t')); ?>
        </th>
        <th><!-- Status -->
            <?php echo($labelRecord->getField('Status_t')); ?>
        </th>
    </tr>
    </thead>
    <tbody>
    <tr>
        <td><!-- # Symbol request number Data Field -->
            <?php echo($request->getField('Project_Request_Num_n'));?>
        </td>
        <td colspan = "2" ><!-- Request Title Data Field -->
            <?php echo($request->getField('Work_Order_Title_t')); ?>
        </td>
        <td><!-- Network selection -->
            <?php echo($request->getField($programmingName));?>
        </td>
        <td colspan = "2"><!-- Request Notes Data Field -->
            <?php
            $workOrderNotes = $request->getField('Work_Order_Notes_t');
            if(isset($workOrderNotes)){
                echo($workOrderNotes);
            }
            ?>
        </td >
        <td><!-- Submit To -->
            <?php
            $requestApproverList = $request->getField('Request_Approver_List_t');
            if(isset($requestApproverList) && (strlen($requestApproverList) > 0)){
                echo($requestApproverList);
            }
            ?>
        </td>
        <td><!-- Status field -->
            <?php
                echo($request->getField($requestStatusName));
            ?>
        </td>
    </tr>
    <tr>
        <table class="table table-bordered nopadding"><!-- Series Table -->
            <thead>
            <tr class="tableTDHeaderDef">
                <th class="col-xs-2 col-md-2" ><!-- Series Title Label -->
                    <?php echo($labelRecord->getField('Show_Title')); ?>
                </th>
                <!--needs column < td> span-->
                <th class="col-xs-1 col-md-1" ><!-- Epis # Label -->
                    <?php echo($labelRecord->getField('Epis_Num')); ?>
                </th>
                <th class="col-xs-3 col-md-3"><!-- Epis/Movie Title Label -->
                    <?php echo($labelRecord->getField('Episode_Title')); ?>
                </th>
                <!--needs column < td> span-->
                <th class="col-xs-2 col-md-2" ><!-- Series Air Date  Label -->
                    <?php echo($labelRecord->getField('Show_Air_Date')); ?>
                </th>
                <th class="col-xs-2 col-md-2"><!-- Time Label -->
                    <?php echo($labelRecord->getField('Show_AirTime_ti')); ?>
                </th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td><!-- Series Title -->
                    <?php
                    if(isset($webShowCodeRecord)){
                        echo($webShowCodeRecord->getField($webShowCodeTitle));
                    }else{
                        echo "&nbsp;";
                    }
                    ?>
                </td>
                <td><!-- Episode # ala Number -->
                    <?php
                    $episodeNumber = $request->getField('Show_EpisodeNumber_t');
                    echo($episodeNumber);
                    ?>
                </td>
                <td><!-- EPIS Movie Title -->
                    <?php echo($request->getField('Show_Episode_Title_t')); ?>
                </td>
                <td>
                    <?php echo($request->getField('Show_AirDate_t')); ?>
                </td>
                <td>
                    <?php echo($request->getField('Show_AirTime_ti')); ?>
                </td>
            </tr>
            </tbody>
        </table>
    </tr>
    </tbody>
</table>
<br><!-- temp br to separate table from border -->
<form id="deliverable-form" name="deliverable-form" action="processing/deliverableService.php" method="post"><!-- Begin Deliverable and Meta form -->

    <!-- scrolling save button which hidden until a form change -->
    <button class="btn btn-info" id="deliverablesubmit" type="submit" name="deliverablesubmit" style="visibility: hidden;">Save Changes</button>

    <input type="hidden" id="del_pk_id" name="del_pk_id" value="<?php echo($deliverablePkId); ?>">

    <table class="table table-bordered nopadding">
        <thead>
        <tr>
            <th class="text-left" colspan="8"><strong>Requested Project</strong></th>
        </tr>
        </thead>
        <!-- <table class="table table-bordered nopadding"> -->
        <thead>
        <tr class="tableTDHeaderDef">
            <th class="col-xs-1 col-md-1"><?php echo($labelRecord->getField('Spot_Type_t'));?></th><!-- Project Type label -->
            <th class="col-xs-1 col-md-1"><?php echo($labelRecord->getField('Length_Short')); ?></th><!-- Length label -->
            <th class="col-xs-2 col-md-2"><?php echo($labelRecord->getField('Tag_Group_Notes_t')); ?></th><!-- Prj Notes Label -->
            <!-- TODO investigate adding labels below to I table -->
            <th class="col-xs-1 col-md-1">Creative Dates</th>
            <th class="col-xs-1 col-md-1">Live/Flight Dates</th><!-- Final Due -->
            <th class="col-xs-6 col-md-6">Media Ver. List</th><!-- Start -->
        </tr>
        </thead>
        <tbody>
        <tr><!-- Start of data row for Project List -->
            <td><!-- Project Type Dropdown Field -->
                <select class="tdc-deliverable-input-height" name="<?php echo($spotTypeName);?>" id="<?php echo($spotTypeName);?>">
                    <?php
                    $fmSpotType = $deliverableRecord->getField('Spot_Type');
                    if(isset($fmSpotType) && (strlen($fmSpotType) > 0)){
                        buildRequestDropDownListWithValue(convertPipeToArray($spotTypePipeList), $fmSpotType);
                    }else{
                        buildRequestDropDownList(convertPipeToArray($spotTypePipeList));
                    }
                    ?>
                </select>
            </td>
            <td><!-- Length Dropdown List Field -->
                <select class="tdc-deliverable-input-height" id="<?php echo($lengthName); ?>" name="<?php echo($lengthName); ?>">
                    <?php
                    $fmLength = $deliverableRecord->getField('Length');
                    if(isset($fmLength) && (strlen($fmLength) > 0)){
                        buildRequestDropDownListWithValue($lengthItems, $fmLength);
                    }else{
                        buildRequestDropDownList($lengthItems);
                    }
                    ?>
                </select>
            </td>
            <td><!-- Project Notes Field -->
                <textarea name="Notes" id="Notes" rows="7" cols="40"><?php echo($deliverableRecord->getField('Notes')); ?></textarea>
            </td>

            <!-- TODO Start Refactor of layout start here -->
            <td><!-- Creative Dates -->
                <table class="table table-condensed" id="toCreative" name="toCreative">
                    <tbody>
                        <tr>
                            <td style="border-top: none; border-bottom: none;">
                                <strong>Creative to requestor</strong><br>
                                <?php
                                $roughCutValue = $deliverableRecord->getField('Rough_Cut_Due_d');
                                buildDateOnlyFieldDeliverable("Rough_Cut_Due_d",$roughCutValue, $requestedProjectListCounter);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-top: none; border-bottom: none;">
                                <strong>Final Due</strong><br>
                                <?php
                                $finalDueDate = $deliverableRecord->getField('Final_Due_Date');
                                buildDateOnlyFieldDeliverable("Final_Due_Date", $finalDueDate, $requestedProjectListCounter);
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td style="border: none;">
                <table class="table table-condensed" id="flightDates" name="flightDates">
                    <tbody>
                        <tr>
                            <td style="border-top: none; border-bottom: none;">
                                <strong>Start</strong><br>
                                <?php
                                $startDate = $deliverableRecord->getField('Flight_Date_Start');
                                buildDateOnlyFieldDeliverable("Flight_Date_Start", $startDate, $requestedProjectListCounter);
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td style="border-top: none; border-bottom: none;">
                                <strong>End</strong><br>
                                <?php
                                $endDate = $deliverableRecord->getField('Flight_Date_End');
                                buildDateOnlyFieldDeliverable("Flight_Date_End", $endDate, $requestedProjectListCounter);
                                ?>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td>

                <div class="row">
                    <div class="col-xs-12 col-md-12">
                        <?php if($canModify){ ?>
                            <button class="input-group-addon addrow tdc-tag-add-icon" id="addRow" name="addRow" type="button"
                                onclick="cloneTagRow('tag-table');"
                                title="Click to add new row" data-placement="bottom">
                            <span class="glyphicon glyphicon-plus" id="addRowSpan" style="align-content: center;"></span>
                        </button>
                        <?php } else { ?>
                        <button class="input-group-addon addrow tdc-tag-add-icon" id="addRow" name="addRow" type="button"
                                onclick="cloneTagRow('tag-table');"
                                title="Click to add new row" data-placement="bottom" disabled>
                            <span class="glyphicon glyphicon-plus" id="addRowSpan" style="align-content: center;"></span>
                        </button>
                        <?php } ?>
                    </div>
                </div>
                <table class="table table-bordered table-condensed" id="tag-table" name="tag-table">
                    <thead>
                    <tr>
                        <th class="col-xs-2 col-md-2">Media Ver</th>
                        <th class="col-xs-7 col-md-7">Media Description</th>
                        <th class="col-xs-2 col-md-2">ISCI HD Code</th>
                        <th class="col-xs-1 col-md-1">&nbsp;</th> <!-- delete row icon column -->
                    </tr>
                    </thead>
                    <tbody>
                    <?php
                    $tagIndex = 1;
                    foreach ($tagRecords as $tagRecord) {
                        echo("<tr>\n");
                        echo("<td>\n");
                        $tagPk = $tagRecord->getField('__pk_ID');
                        buildTagDropDownNew($promoCodeVersionValueList, $tagRecord->getField('PromoCode_3_TagVersion_t'),
                            $tagPk, $tagIndex, $promoCodeVersionDisplayList);
                        echo("</td>\n");
                        echo("<td>\n");
                        buildTagDescription($tagRecord->getField('Tag_Version_Description_t'), $tagPk, $tagIndex);
                        echo("</td>\n");
                        echo("<td>\n");
                        buildTagHouse($tagRecord->getField('House_Number_t'), $tagPk, $tagIndex);
                        echo("</td>\n");
                        echo("<td>\n");
                        buildTagDeleteCell($tagIndex, $canModify);
                        echo("</td>\n");
                        $tagIndex++;
                        echo("</tr>\n");
                    }
                    echo("<tr>");
                    echo("<td>");
                    buildTagsDropDownPlusOneNew($promoCodeVersionValueList, $tagIndex, $promoCodeVersionDisplayList);
                    echo("</td>");
                    echo("<td>");
                    buildTagDescriptionPlusOne($tagIndex);
                    echo("</td>");
                    echo("<td>");
                    buildTagHousePlusOne($tagIndex);
                    echo("</td>");
                    echo("<td>\n");
                    buildTagDeleteCell($tagIndex, $canModify);
                    echo("</td>\n");
                    echo("</tr>");
                    ?>
                    </tbody>
                </table>
            </td>
            <!-- TODO End of refactor of Layout ends here -->
        </tr>
        </tbody>
        <!-- </table> -->
    </table>
    <input type="hidden" id="tagIndex" name="tagIndex" value="<?php echo($tagIndex); ?>">
    <br>
    <hr>
    <?php
    if(isset($metaRelatedRecords)){
        buildMetaComponent($metaRelatedRecords, $canModify);
    }else{
        echo("<br><br>");
    }
    ?>
</form>

<br><!-- temp br to separate table from border -->
<?php include_once($headerFooter .'footer.php'); ?>