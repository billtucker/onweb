<?php
/**
 * approveView.php renders only the approval block
 *
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 7/17/2015
 * Time: 3:49 PM
 *
 * Note:
 * 1. Refactored page to split forms to approval block and notes block so as to granulate saved information. Form
 *    was page wide inclusive form that encompassed both notes and approval blocks. We need to differentiate who
 *    approved from who added a note within FileMaker
 */

if(!session_id()){
    session_start();
}

?>
<!-- TODO set up the labels used to use OAP_PREFS or WEB i table -->
<div class="container-fluid tdc-spotviewer-div-margin"><!-- Start Approval table div -->
	<div class="row">
    <form action="spotViewerResponse.php?pkId=<?php echo urlencode($pkId); ?>" method="post" id="approval_block" name="approval_block">
    <input type="hidden" name="userApprover" id="userApprover" value="<?php echo($_SESSION['userName']); ?>">
    <div class="col-xs-7 col-md-7 col-lg-7">
        <table class="table table-bordered table=responsive">
            <tr>
                <td class="tableTDHeaderDef add_bold_upper">PROMO MASTER</td>
                <td class="tableTDHeaderDef add_bold_upper">DATE</td>
                <td class="tableTDHeaderDef add_bold_upper">BY</td>
            </tr>
            <tr>
                <td style="border-bottom: medium">
                    <?php
                    if(isset($approvalEdit) && $approvalEdit){
                        echo "<fieldset>" .PHP_EOL;
                    }else{
                        echo "<fieldset disabled>" .PHP_EOL;
                    }
                    ?>
                    <?php foreach($approvalvalues as $value){ ?>
                        <input class="approval" name="approval" type="radio" value="<?php echo $value; ?>" onChange='this.form.submit();'
                            <?php if($value == $record->getField('Rough_Cut_Approval_YN_t')) { echo " Checked"; }?>>
                        <?php if($value == "No") { echo '<span style="color:#9E1818;">Killed</span>';}
                        else { if($value == "Yes") { echo '<span style="color:#005EEF;">Approved</span>';}
                        else { echo '<span style="color:#DB2221;">Re-Do</span>';}} ?><br />
                    <?php } ?>
                    </fieldset>
                </td>
                <td style="border-bottom: medium">
                    <?php echo $record->getField('Rough_Cut_Approval_Date_d'); ?>
                </td>
                <td style="border-bottom: medium">
                    <?php
                    $by = $record->getField('Rough_Cut_Approved_By_t');
                    if(!empty($by)){
                        echo $by;
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
    <div class="col-xs-5 col-md-5 col-lg-5">&nbsp;</div>
    </form>
	</div>
</div><!-- End Approval table div -->
<div class="clearfix">&nbsp;</div>