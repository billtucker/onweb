<?php
/**
 * Created by IntelliJ IDEA.
 * User: billt
 * Date: 4/11/2017
 * Time: 2:51 PM
 */

include_once("onspot-config.php");
include_once($utilities ."utility.php");
include_once ($processing ."spotviewer_utility.php");

$pageUrl = urlencode($port ."$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");



//Added the session check to make sure that I can collect the $_SESSION['userName']. This may be an overkill
// but the if() controls the action
if (!session_id()) {
    session_start();
}

$showTitle = $record->getField('Show_Title_ct');

?>
<!-- Page load scripts used in all sections and columns of the page -->
<script type="text/javascript">
    $(document).ready(function () {
        //Function to control when Project Details chevron is clicked
        $('.glyphicon-chevron-down').click(function(){
            $(this).parent("div").find(".glyphicon-chevron-down")
                .toggleClass("glyphicon-chevron-up");
        });

        //Check if user has access to the Approval block if not hide the entire block
        if (document.getElementById('hideApprovalBlock')) {
            if (document.getElementById('hideApprovalBlock').value == 'hide') {
                if (document.getElementById('approve-row')) {
                    document.getElementById('approve-row').style.display = "";
                    document.getElementById('approve-row').style.display = "none";
                }
            }
        }

        //Check if user has access to notes filed if not hide the entire block
        if (document.getElementById('hideNotesBlock')) {
            if (document.getElementById('hideNotesBlock').value == 'hide') {
                if (document.getElementById('notes-row')) {
                    document.getElementById('notes-row').style.display = "";
                    document.getElementById('notes-row').style.display = "none";
                }
            }
        }

        //Hide the notes and approval blocks when in view only mode
        if(document.getElementById('hideNotesApprover')){
            if(document.getElementById('hideNotesApprover').value == 'hide'){
                if(document.getElementById('columnHide')){
                    console.log('OK we should hide approver notes row');
                    var hDiv = document.getElementById('columnHide');
                    hDiv.style.display = 'none';
                }
            }
        }


    });
</script>

<!-- setup here what is hidden and what is exposed -->
<input id="hideApprovalBlock" type="hidden" value="<?php echo $showApprovalBlock; ?>">
<input id="hideNotesBlock" type="hidden" value="<?php echo $showNotesBlock; ?>">
<input id="hideNotesApprover" type="hidden" value="<?php echo $hideNotesApproval; ?>">
<div class="row">
    <div class="<?php echo $viewerColumnClass; ?>"><!-- start video and project details column -->
        <?php
            processDisplayType($fullVideoLink, $typeResult[0], $typeResult[1], $downloadLink, $fullVideoLink);
        ?>
    </div><!-- end of the video link <a> div to set font size -->
        <br>
    <button class="btn input-group-addon tdc-glyphicon-control ignore_button" type="button" id="open_close"
            data-toggle="collapse" data-target="#hidethisdiv" data-toggle="tooltip"
                title="Click to Show or Hide Project Details">
        <!-- added ignore_button class to glyphicon to fix Edge browser class detection for nprogress.js -->
            <span id="project_info" class="tdc-glyphicon-control glyphicon glyphicon-chevron-down ignore_button"></span>
    </button>
        <label for="open_close"><?php echo($showTitle); ?></label>
        <div id="hidethisdiv" class="row collapse">
            <table class="table table-bordered table-responsive">
                <tr>
                    <td class="col-md-1 col-xs-1 tableTDHeaderDef add_bold_upper">W.O.#</td>
                    <td class="col-md-1 col-xs-1 tableTDHeaderDef add_bold_upper">Prj#</td>
                    <td class="col-md-3 col-xs-3 tableTDHeaderDef add_bold_upper">Show Title</td>
                    <td class="col-md-5 col-xs-5 tableTDHeaderDef add_bold_upper">Project Title:</td>
                    <td class="col-md-2 col-xs-2 tableTDHeaderDef add_bold_upper">Writer Producer:</td>
                </tr>
                <tr>
                    <td>
                        <?php
                        $wo = $record->getField('Work_Order_Num_n');
                        if (empty($wo)) {
                            echo "&nbsp;";
                        } else {
                            echo $wo;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $prjNum = $record->getField('Tag_Group_Number_n');
                        if (empty($prjNum)) {
                            echo "&nbsp;";
                        } else {
                            echo $prjNum;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $showTitle = $record->getField('Show_Title_ct');
                        if (empty($showTitle)) {
                            echo "&nbsp;";
                        } else {
                            echo $showTitle;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $name = $record->getField('Work_Order_Title_t');
                        if (empty($name)) {
                            echo "&nbsp";
                        } else {
                            echo $name;
                        }
                        ?>
                    </td>
                    <td>
                        <?php
                        $wp = $record->getField('Writer_Producer_t');
                        if (empty($wp)) {
                            echo "&nbsp;";
                        } else {
                            echo $wp;
                        }
                        ?>
                    </td>
                </tr>
            </table>
        </div><!-- End of collapse DIV -->
    </div><!-- End of video column -->
    <div id="columnHide"> <!-- This div is to hide the approval/notes blocks when in view only -->
        <div class="col-md-3 col-xs-12 tdc-spotviewer-div-margin approver_notes">
            <table class="table table-responsive" style="border: none">
                <tr>
                    <td id="approve-row" style="border-top: none">
                        <!-- form to process approval buttons only -->
                        <form action="spotViewerResponse.php?pkId=<?php echo urlencode($pkId); ?>" method="post"
                              id="approval_block" name="approval_block">
                            <input type="hidden" name="userApprover" id="userApprover"
                                   value="<?php echo($_SESSION['userName']); ?>">
                            <table class="table table-bordered table-responsive">
                                <tr>
                                    <td class="tableTDHeaderDef add_bold_upper">PROMO MASTER</td>
                                    <td class="tableTDHeaderDef add_bold_upper">DATE</td>
                                    <td class="tableTDHeaderDef add_bold_upper">BY</td>
                                </tr>
                                <tr>
                                    <td style="border-bottom: medium">
                                        <?php
                                        if (isset($approvalEdit) && $approvalEdit) {
                                            echo "<fieldset>" . PHP_EOL;
                                        } else {
                                            echo "<fieldset disabled>" . PHP_EOL;
                                        }
                                        ?>
                                        <?php foreach ($approvalvalues as $value) { ?>
                                            <input class="approval" name="approval" type="radio"
                                                   value="<?php echo $value; ?>" onChange='this.form.submit();'
                                                <?php if ($value == $record->getField('Rough_Cut_Approval_YN_t')) {
                                                    echo " Checked";
                                                } ?>>
                                            <?php if ($value == "No") {
                                                echo '<span style="color:#9E1818;">Killed</span>';
                                            } else {
                                                if ($value == "Yes") {
                                                    echo '<span style="color:#005EEF;">Approved</span>';
                                                } else {
                                                    echo '<span style="color:#DB2221;">Re-Do</span>';
                                                }
                                            } ?><br/>
                                        <?php } ?>
                                        </fieldset>
                                    </td>
                                    <td style="border-bottom: medium">
                                        <?php echo $record->getField('Rough_Cut_Approval_Date_d'); ?>
                                    </td>
                                    <td style="border-bottom: medium">
                                        <?php
                                        $by = $record->getField('Rough_Cut_Approved_By_t');
                                        if (!empty($by)) {
                                            echo $by;
                                        }
                                        ?>
                                    </td>
                                </tr>
                            </table>
                        </form><!-- end of approvaL buttons form action -->
                    </td>
                </tr><!-- end of approval TR -->
                <tr>
                    <td id="notes-row" style="border-top: none">
                        <!-- Start of form to process notes added block -->
                        <form action="spotViewerResponse.php?pkId=<?php echo urlencode($pkId); ?>" method="post"
                              id="notes_block" name="notes_block">
                            <table class="table table-bordered table=responsive">
                                <tr>
                                    <td colspan="2" class="tableTDHeaderDef add_bold_upper">APPROVAL NOTES</td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <?php
                                        if (isset($spotNotesEdit) && $spotNotesEdit) {
                                            echo "<fieldset>" . PHP_EOL;
                                        } else {
                                            echo "<fieldset disabled>" . PHP_EOL;
                                        }
                                        ?>
                                        <textarea id="notes" name="notes" style="width: 90%"></textarea>
                                        <button class="btn btn-link" id="addNote" name="addNote" type="submit">Add
                                        </button>
                                        <input type="hidden" id="userNote" name="userNote"
                                               value="<?php echo($_SESSION['userName']); ?>">
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
                                        echo "<td>" . $entry->getField('z_RecCreatedDate') . "</td>";
                                        echo "<td>" . $entry->getField('Note_Text_t') . "</td>";
                                        echo "</tr>";
                                    }
                                } else {
                                    echo "<tr><td>&nbsp;</td></tr>";
                                }
                                ?>
                            </table>
                        </form><!-- end of notes add form -->
                    </td>
                </tr><!-- end of Notes TR -->
            </table>
        </div><!-- End of approval and notes div -->
    </div><!-- End of hiding Notes and Approval block -->
</div><!-- end of page row as the page is a single row with many columns This will span the entire page and use
    include to bring other column data elements -->

