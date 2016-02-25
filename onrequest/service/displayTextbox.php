<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/17/2014
 * Time: 5:17 PM
 * This method processes data to display a text box field from FileMaker database data. The method
 * accounts for instructions for text box and display previously entered data
 *
 * $question text label for questions
 * $question_num text number to display to user
 * $name text field to display FileMaker __pk_ID as name/id fields
 * $required numeric/boolean 1 on blank/zero/blank off tro denote field is required
 * $instructions text field used as Bootstrap placeholder
 * $answer text previously entered data or post processing data for this field
 * Added display of textarea or display a single line text input box or empty text box with placeholder
*/

function processTextField($label, $question_num, $name, $required, $instructions, $answer)
{

//Setup controls for textarea so no more that 5 rows appear in the viewing area
$maxRows = 5;
$rows = 0;
$lineCount = count(explodedCrString($answer));
($lineCount <= $maxRows) ? $rows = $lineCount : $rows = $maxRows;

?>

<div class="row">
    <div class="col-xs-12 col-lg-12">
        <h6><strong>
            <?php echo($question_num . ". "); ?><?php echo($label); ?>
            <?php if ($required) { ?>
                <i class="text-danger"> *(REQUIRED)</i>
            <?php } ?>
        </strong></h6>
    </div>
</div>
<div class="row">
    <div class="col-xs-12 col-lg-12">
        <?php if($rows > 1){ ?>
            <textarea class="form-control" rows="<?php echo($rows); ?>" placeholder="<?php echo($instructions);?>" id="<?php echo($name);?>" name="<?php echo($name); ?>"><?php echo($answer); ?></textarea>
        <?php }else if(strlen($answer) > 0){ ?>
            <input type="text" class="form-control" placeholder="<?php echo($instructions);?>" value="<?php echo($answer); ?>" name="<?php echo($name); ?>" id="<?php echo($name);?>"/>
        <?php }else{ ?><!-- End of if answer has value -->
            <input type="text" class="form-control" placeholder="<?php echo($instructions);?>" name="<?php echo($name); ?>" id="<?php echo($name);?>"/>
        <?php } ?>
    </div>
</div>

<?php } ?><!-- end of function processTextField method -->