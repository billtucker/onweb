<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 12/15/2014
 * Time: 9:24 AM
 */

include_once("request-config.php");
include_once($utilities ."utility.php");

//validate user prior to access on this page and any HTML elements
include_once("$validation" ."user_validation.php");

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

$validateUser = true;
$canModify = false;

if(isset($_GET['p1']) && validatePassThrough($_GET['p1'])){
    $log->debug("projectlist.php - Print operation invoked and login is being bypassed");
    $validateUser = false;
}

if($validateUser) {
    $log->debug("Now validate if Plugin: (" .$onRequestPlugin .") is installed");
    //validate user then check if plugin license if active to access on this page
    validateUser($site_prefix, $pageUrl, $siteSection, $onRequestModify, $onRequestPlugin);
    $canModify = userCanModifyRequest($okModify);
}

$headerToUse = getHeaderIncludeFileName(urldecode($pageUrl));
include_once($headerFooter .$headerToUse);
include_once($headerFooter .'header_request_list.php');
include_once($requestInclude .'constants.php');
include_once($requestInclude .'requestListConnection.php');

?>

    <br>
    <div class="row"><!-- Start Div Request List -->
        <table class="table table-bordered">
            <thead class="tableTDHeaderDef">
            <tr>
                <th class="col-md-1"><?php echo($labelRecord->getField('Detail')); ?></th>
                <th class="col-md-1"><?php echo($labelRecord->getField('Request')); ?></th>
                <th class="col-md-4"><?php echo($labelRecord->getField('Project')); ?> </th>
                <th class="col-md-1">Request Type</th>
                <th class="col-md-1">Request Status</th>
                <th class="col-md-1">Status Date</th>
                <th class="col-md-1"><?php echo($labelRecord->getField('Requested_By')); ?></th>
                <th class="col-md-1"><?php echo($labelRecord->getField('Submit_To_t')); ?></th>
                <th class="col-md-1"><?php echo($labelRecord->getField('Created_Date')); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $requestCounter = 0;
            foreach($requestList as $requestRecord){
                $requestCounter++;
                ?>
                <tr>
                    <td>
                        <?php echo($requestCounter);?>&nbsp;&nbsp;<a href="request.php?pkId=<?php echo(urlencode($requestRecord->getField('__pk_ID')));?>">
                            <img src="../images/leftarrow_icon.jpg" alt="Left Arrow Icon">
                        </a>
                    </td><!-- Detail -->
                    <td><?php echo($requestRecord->getField('Project_Request_Num_n')); ?></td>
                    <td><?php echo($requestRecord->getField('Work_Order_Title_t')); ?></td>
                    <td><?php echo($requestRecord->getField('Request_Type_t')); ?></td>
                    <td><?php echo($requestRecord->getField('Request_Status_t')); ?></td>
                    <td><?php echo($requestRecord->getField('Request_Status_Date_d')); ?></td>
                    <td><?php echo($requestRecord->getField('Contact_Name_t')); ?></td>
                    <td><?php echo($requestRecord->getField('Request_Approver_List_t')) ;?></td>
                    <td><?php echo($requestRecord->getField('Request_Created_Date_d')) ;?></td>
                </tr>

            <?php } ?>
            </tbody>
        </table>

    </div> <!-- End Div Request List -->

<?php include_once($headerFooter .'footer.php'); ?>