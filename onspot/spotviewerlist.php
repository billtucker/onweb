<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/10/2015
 * Time: 12:56 PM
 */


include_once("onspot-config.php");
include_once($utilities ."utility.php");
include_once("$validation" ."user_validation.php");
include_once($fmfiles ."work.db.php");
include_once($errors .'errorProcessing.php');

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$validateUser = true;

if(isset($_GET['p1']) && validatePassThrough($_GET['p1'])){
    $validateUser = false;
    $log->debug("request.php - Print operation invoked and login is being bypassed");
}

if($validateUser) {
    $log->debug("Now do validation for Spot Viewer List");
    validateUser($site_prefix, $pageUrl, $siteSection, $onSpotView, $OnSpotPluginToValidate);
}


$spotViewerLayout = "[WEB] cwp_spotviewer_browse";

$spotList = $fmWorkDB->newFindCommand($spotViewerLayout);
$spotResults = $spotList->execute();

if(FileMaker::isError($spotResults)){
    $errorTitle = "FileMaker Error";
    $log->error($spotResults->getMessage(), $spotResults->getErrorString(), $pageUrl, "N/A", $site_prefix);
    processError($spotResults->getMessage(), $spotResults->getErrorString(), $pageUrl, "N/A", $errorTitle);
    exit;
}

$spotRecords = $spotResults->getRecords();

$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
include_once($headerFooter .$headerToUse);

$log->debug("Finished reading FM, got header, and now display HTML to display list");

?>
<br>
<h2 class="text-center">Promo Master List</h2>
<br>
<div class="row">
    <table class="table table-bordered">
        <thead class="tableTDHeaderDef">
        <tr>
            <td class="col-lg-1 col-md-1">W.O.#</td>
            <td class="col-lg-1 col-md-1">Prj#</td>
            <td class="col-lg-2 col-md-2">Show Title</td>
            <td class="col-lg-4 col-md-4">Project Title</td>
            <td class="col-lg-1 col-md-1">Writer</td>
            <td class="col-lg-2 col-md-2">Sub-Master</td>
            <td class="col-lg-1 col-md-1">Apprv</td>
            <td class="col-lg-1 col-md-1">&nbsp;</td>
        </tr>
        </thead>
        <tbody>
        <?php
            foreach($spotRecords as $record) {
        ?>
                <tr>
                    <td><?php echo($record->getField("Work_Order_Num_n")); ?></td>
                    <td><?php echo($record->getField("Tag_Group_Number_n")); ?></td>
                    <td><?php echo($record->getField("Show_Title_ct")); ?></td>
                    <td><?php echo($record->getField('Work_Order_Title_t')); ?></td>
                    <td><?php echo($record->getField('Writer_Producer_t')); ?></td>
                    <td><?php echo($record->getField("z_ONSPOT_Rough_Filename_t"));?></td>
                    <td><?php echo($record->getField('Rough_Cut_Approval_YN_t')); ?></td>
                    <td>
                        <a href="spotedit.php?pkId=<?php echo(urlencode($record->getField('__pk_ID')));?>">
                        <img src="../images/leftarrow_icon.jpg" alt="Left Arrow Icon"></a>
                    </td>
                </tr>
        <?php
            }
        ?>
        </tbody>
    </table>
</div>

<?php
include_once($headerFooter ."footer.php");
?>


