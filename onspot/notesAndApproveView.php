<?php

/**
 * notesAndApproveView.php renders both Notes and Approval block to the user
 *
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 7/17/2015
 * Time: 4:04 PM
 *
 * Note:
 * 1. Refactored page to split forms to approval block and notes block so as to granulate saved information. Form
 *    was page wide inclusive form that encompassed both notes and approval blocks. We need to differentiate who
 *    approved from who added a note within FileMaker
 */

//Added the session check to make sure that I can collect the $_SESSION['userName']. This may be an overkill
// but the if() controls the action
if(!session_id()){
    session_start();
}

?>


<div class="container-fluid tdc-spotviewer-div-margin">
        <div class="row">
            <table class="table table-responsive" style="border: none">
                <tr>
                    <td style="border-top: none">
                        <!-- form to process approval buttons only -->
                        <form action="spotViewerResponse.php?pkId=<?php echo urlencode($pkId); ?>" method="post" id="approval_block" name="approval_block">
                            <input type="hidden" name="userApprover" id="userApprover" value="<?php echo($_SESSION['userName']); ?>">
                            <table class="table table-bordered table-responsive">
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
                                        <input class="approval" name="approval" type="radio" value="<?php echo $value; ?>"
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
                        </form><!-- end of approvaL buttons form action -->
                    </td>
                    <td style="border-top: none">
                        <!-- Start of form to process notes added block -->
                        <form action="spotViewerResponse.php?pkId=<?php echo urlencode($pkId); ?>" method="post" id="notes_block" name="notes_block">
                        <table class="table table-bordered table=responsive">
                            <tr>
                                <td colspan="2" class="tableTDHeaderDef add_bold_upper">APPROVAL NOTES</td>
                            </tr>
                            <tr>
                                <td colspan="2">
                                    <?php
                                    if(isset($spotNotesEdit) && $spotNotesEdit){
                                        echo "<fieldset>" .PHP_EOL;
                                    }else{
                                        echo "<fieldset disabled>" .PHP_EOL;
                                    }
                                    ?>
                                    <textarea id="notes" name="notes" style="width: 90%"></textarea>
                                    <button class="btn btn-link" id="addNote" name="addNote" type="submit">Add</button>
                                    <input type="hidden" id="userNote" name="userNote" value="<?php echo($_SESSION['userName']); ?>">
                                    </fieldset>
                                </td>
                            </tr>
                            <tr>
                                <td class="tableTDHeaderDef add_bold_upper">BY</td>
                                <td class="tableTDHeaderDef add_bold_upper">NOTE</td>
                            </tr>
                            <!-- iterator to list notes collected by FileMaker Query -->
                            <?php
                            if ((isset($notes) && is_array($notes)) && ($processNotes || $userHasNotes)) {
                                    foreach ($notes as $entry) {
                                        echo "<tr>";
                                        echo "<td>" .$entry->getField('z_RecCreatedDate') ."</td>";
                                        echo "<td>" .$entry->getField('Note_Text_t') ."</td>";
                                        echo "</tr>";
                                    }
                            }else{
                                echo "<tr><td>&nbsp;</td></tr>";
                            }
                            ?>
                        </table>
                        </form><!-- end of notes add form -->
                    </td>
                </tr>
            </table>
        </div>
</div>
