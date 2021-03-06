<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/10/2015
 * Time: 8:37 AM
 */


include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");

//validate user prior to access on this page
include_once("$validation" ."user_validation.php");
include_once($utilities ."utility.php");
include_once($errors .'errorProcessing.php');
include_once($fmfiles .'order.db.php');

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

/*
* Include TDC Application Configuration file if the file exists otherwise skip this include. The file is
* dynamically generated at login 'only' so if the file is not present then the company small logo will not display.
 * However, the file should be included for each presentation layer PHP page
*/
if(file_exists($root .$appConfigName)){
    include_once($root .$appConfigName);
}


$validateUser = true;
$canModify = false;
$siteSection = "onweb";
//added to account for users with OnRequest module enabled in FileMaker (Hide buttons associated with OnRequest)
$showProjectTypes = true;
$showSpotTypes = true;

if(isset($_GET['p1']) && validatePassThrough($_GET['p1'])){
    $validateUser = false;
    $log->debug("request.php - Print operation invoked and login is being bypassed");
}

if($validateUser) {
    $log->debug("Now validate if Plugin: (" .$onWebPlugin .") is installed");
    //validate user then check if plugin license if active to access on this page
    validateUser($site_prefix, $pageUrl, $siteSection, "", $onWebPlugin);
    $canModify = userCanModifyRequest($okModify);
}

//Check if user has ON-REQUEST PLUGIN and if shutdown the ability to view sections of the site
$showProjectTypes = userAccess("ON-REQUEST");
$showSpotTypes = userAccess("ON-SPOT");

$projectTypesFind = $fmOrderDB->newFindCommand('[WEB] Project Request Types');
//Note: Locally 192.168.0.14 this field is 'Request Type' however in main line the field is 'Request_Type'
$projectTypesFind->addFindCriterion("Request_Type", "*");
$projectTypesResults = $projectTypesFind->execute();

if(FileMaker::isError($projectTypesResults)){
    if($projectTypesResults->getCode() == $noRecordsFound){
        $log->debug("No records found in search of project types disable buttons and display");
        //created and empty project records types array to account for system that do not use have authorization to use OnRequest
        $projectTypeRecords = array();
        $showProjectTypes = false;
    }else{
        $errorTitle = "FileMaker Error";
        $log->error($projectTypesResults->getMessage(), $projectTypesResults->getErrorString(), $pageUrl, "N/A", $site_prefix);
        processError($projectTypesResults->getMessage(), $projectTypesResults->getErrorString(), $pageUrl, "N/A", $errorTitle);
        exit;
    }
}else{
    $projectTypeRecords = $projectTypesResults->getRecords();
}



//Variables used in the Algorithm to display project type button array of 4 by X buttons per row of 12 column grid
$projectTypeRecordCount = count($projectTypeRecords);
$maxTypesPerRow = 4;
$dynamicColumnLgClassText = "col-lg-";
$dynamicColumnXsClassText = "col-xs-";
$decrementColumnBy = 2;

//1. Do not include HTML header record until most processing is completed. The error page cannot be called "Redirected"
//after HTML header is called.
//2. Determine which header should be presented based on site level position
$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
include_once($headerFooter .$headerToUse);


?>
    <br>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <img class="img-responsive center-block" src="<?php echo($companyLogoSplash); ?>" alt="Login Splash Screen Image" align="center">
        </div>
    </div>
    <br>
    <!-- hopefully this is a blank row -->
<?php if($showProjectTypes){ ?><!-- Block these out if no project types exist -->
    <div class="row" id="empty_row_1"></div>
    <div class="row">
        <div class="col-xs-2 col-lg-2">&nbsp;</div>
        <div class="col-xs-4 col-lg-4">
            <p class="text-primary" style="color: blue"><Strong>Existing Requests: Use Button Below</Strong></p>
        </div>
        <div class="col-xs-6 col-lg-6">&nbsp;</div>
    </div>
    <div class="row">
        <div class="col-xs-2 col-lg-2">&nbsp;</div>
        <div class="col-xs-4 col-lg-4">
            <button type="button" class="btn btn-primary btn-block" onclick="window.location.href='onrequest/projectlist.php'">My Open Requests</button>
        </div>
        <div class="col-xs-6 col-lg-6">&nbsp;</div>
    </div>
    <br>
    <div class="row">
        <div class="col-xs-2 col-lg-2">&nbsp;</div>
        <div class="col-xs-4 col-lg-4">
            <p class="text-primary" style="color: blue"><Strong>New Requests: Select Your Project Type Below</Strong></p>
        </div>
        <div class="col-xs-6 col-lg-6">&nbsp;</div>
    </div>

    <!-- Start of new dynamic work for more than 4 buttons -->
<?php for($rowCounter = 0; $rowCounter < $projectTypeRecordCount; $rowCounter += $maxTypesPerRow){
    $columnWidthTotal = 8; ?>
    <div class="row"><!-- Start 4 button Project Types for Bootstrap grid system -->
    <div class="col-xs-2 col-lg-2">&nbsp;</div><!-- Number one blank column for Bootstrap 12 column grid system -->
    <?php for($columnTypeIndex = $rowCounter; $columnTypeIndex < ($rowCounter + $maxTypesPerRow); $columnTypeIndex++){
        if($columnTypeIndex < ($projectTypeRecordCount)) {
            if(($columnTypeIndex + $maxTypesPerRow) > $projectTypeRecordCount) {
                $columnWidthTotal -= $decrementColumnBy;//decrement column counter
                getButtonCode($projectTypeRecords[$columnTypeIndex]->getField('Request_Type'), $projectTypeRecords[$columnTypeIndex]->getField('__pk_ID'));
                $items = convertPipeToArray($projectTypeRecords[$columnTypeIndex]->getField('Examples_List_Pipe_ct'));
                if(is_array($items)){
                    echo("<p class='text-muted'>");
                    foreach($items as $item){
                        echo($item ."<br>\n");
                    }
                    echo("</p></div>\n");
                }else{
                    echo("<p class='text-muted'>" .$items ."</p></div>\n");
                }
                if($columnTypeIndex == ($projectTypeRecordCount - 1) && ($projectTypeRecordCount % $maxTypesPerRow) != 0){
                    echo("<div class='" .$dynamicColumnXsClassText .$columnWidthTotal  ." " .$dynamicColumnLgClassText .$columnWidthTotal ."'>&nbsp;</div>\n");//Empty row dynamic column width text
                }
            }else{
                getButtonCode($projectTypeRecords[$columnTypeIndex]->getField('Request_Type'), $projectTypeRecords[$columnTypeIndex]->getField('__pk_ID'));
                $items = convertPipeToArray($projectTypeRecords[$columnTypeIndex]->getField('Examples_List_Pipe_ct'));
                if(is_array($items)){
                    echo("<p class='text-muted'>");
                    foreach($items as $item){
                        echo($item ."<br>\n");
                    }
                    echo("</p></div>\n");
                }else{
                    echo("<p class='text-muted'>" .$items ."</p></div>\n");
                }
            }
        }
        ?><?php
    } //End for dynamic cell generation for loop
    echo("<div class='col-xs-2 col-lg-2'>&nbsp;</div><!-- Last blank column for Bootstrap 12 column grid system -->\n");
    echo("</div><!-- end of row for this 12 columns -->\n");
} ?>
<?php } ?><!-- End of test for ON-REQUEST -->
    </div><!-- End 4 button Project Types for Bootstrap grid system -->
    <!-- End of new dynamic work for more than 4 buttons -->

    <!-- Start add button for Spot Viewer -->
    <hr>
    <?php if($showSpotTypes) { ?>
    <div class="tdc-container-fluid">
        <div class="row">
            <div class="col-xs-2 col-lg-2">&nbsp;</div>
            <div class="col-xs-4 col-lg-4">
                <p class="text-primary" style="color: blue"><Strong>Existing Spots Review: Use Button Below</Strong></p>
            </div>
            <div class="col-xs-6 col-lg-6">&nbsp;</div>
        </div>
        <div class="row">
            <div class="col-xs-2 col-lg-2">&nbsp;</div>
            <div class="col-xs-4 col-lg-4">
                <button type="button" class="btn btn-primary btn-block" onclick="window.location.href='onspot/spotviewerlist.php'">My Spots To Review</button>
            </div>
            <div class="col-xs-6 col-lg-6">&nbsp;</div>
        </div>
    </div>
    <?php } ?> <!-- end of check for ON-SPOT access -->
    <!-- End of adding button for SpotViewer -->
    <br>
<?php include_once($headerFooter .'footer.php'); ?>