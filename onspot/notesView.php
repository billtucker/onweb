<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 7/17/2015
 * Time: 4:13 PM
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

<div class="clearfix">&nbsp;</div>
<div class="container-fluid tdc-spotviewer-div-margin"><!-- Start one table div -->
    <div class="row">
	<form action="spotViewerResponse.php?pkId=<?php echo urlencode($pkId); ?>" method="post" id="notes_block" name="notes_block">
    <div class="col-xs-7 col-md-7 col-lg-7">
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
                    <button class="btn btn-link" id="addNote2" name="addNote" type="submit">Add</button>
                    <input type="hidden" id="userNote" name="userNote" value="<?php echo($_SESSION['userName']); ?>">
                    </fieldset>
                </td>
            </tr>
            <tr>
                <td class="tableTDHeaderDef add_bold_upper">BY</td>
                <td class="tableTDHeaderDef add_bold_upper">NOTE</td>
            </tr>
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
    </div>
    <div class="col-xs-5 col-md-5 col-lg-5">&nbsp;</div>
    </form>
	</div>
</div>

