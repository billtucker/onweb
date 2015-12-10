<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/8/2014
 * Time: 2:30 PM
 * This page reads dynamic data and generates HTML code to support a Bootstrap Date selection object
 */

function processDatePicker($name, $question, $questionNum, $required, $instruction, $answer){
    ?>
    <br>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <h6><strong>
                <?php echo($questionNum . ". " .$question); ?>
                <?php if($required){ ?>
                    <i class="text-danger"> *(REQUIRED)</i>
                <?php } ?>
                </strong></h6>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <div class="form-group">
                <div class="input-group date date_only" data-date="" data-date-format="mm/dd/yyyy" data-link-field="<?php echo($name);?>" data-link-format="mm/dd/yyyy">
                    <input type="text" class="form-control" placeholder="<?php echo($instruction);?>" value="<?php echo($answer); ?>">
                    <span class="input-group-addon"><span class="glyphicon glyphicon-calendar"></span> </span>
                </div>
            </div>
            <input type="hidden" id="<?php echo($name);?>" name="<?php echo($name);?>" value="<?php echo($answer); ?>">
        </div>
    </div>

<?php } ?>