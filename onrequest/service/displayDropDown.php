<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/17/2014
 * Time: 4:28 PM
 This method will display Dropdown single select. It was decided if a multi-select the a checkbox
 * will be used over the dropdown

 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($utilities ."utility.php");

function processDropDown($items, $name, $question, $question_num, $required, $answerItem){
    $boxItems = convertPipeToArray($items);
    ?>
    <br>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <h6><strong>
                <?php echo($question_num .". ");?><?php echo($question ."."); ?>
                <?php if($required){ ?>
                    <i class="text-danger"> *(REQUIRED)</i>
                <?php } ?>
                </strong></h6>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <select class="form-control" name="<?php echo($name); ?>" id="<?php echo($name); ?>">
                <?php foreach($boxItems as $item){ ?>
                    <?php if($item == $answerItem){ ?>
                        <option name="<?php echo($item); ?>" selected><?php echo($item); ?></option>
                    <?php } else { ?>
                        <option name="<?php echo($item); ?>"><?php echo($item); ?></option>"
                    <?php } ?>
                <?php } ?>
            </select>
        </div>
    </div>
<?php } ?>