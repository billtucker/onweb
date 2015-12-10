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
*/

function processTextField($label, $question_num, $name, $required, $instructions, $answer)
{
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
        <?php if((strlen($answer) > 0)){ ?>
            <input type="text" class="form-control" placeholder="<?php echo($instructions);?>" value="<?php echo($answer); ?>" id="<?php echo($name);?>" name="<?php echo($name); ?>"/>
        <?php }else{ ?><!-- End of if answer has value -->
            <input type="text" class="form-control" placeholder="<?php echo($instructions);?>" name="<?php echo($name); ?>" id="<?php echo($name);?>"/>
        <?php } ?><!-- End of else statement -->
    </div>
</div>

<?php } ?><!-- end of function processTextField method -->