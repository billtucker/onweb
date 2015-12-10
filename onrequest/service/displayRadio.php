<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/17/2014
 * Time: 4:43 PM
 */

include_once("request-config.php");
include_once($utilities ."utility.php");

function processRadioButton($rawRadioItems, $name, $label, $questionNum, $required, $answer){
    $radioItems = convertPipeToArray($rawRadioItems);
    ?>

    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <h6><strong>
                <?php echo($questionNum .". "); ?><?php echo($label);?>
                <?php if ($required) { ?>
                    <i class="text-danger"> *(REQUIRED)</i>
                <?php } ?>
            </strong></h6>
        </div>
        <div class="col-xs-12 col-lg-12">
            <?php foreach($radioItems as $item){ ?>
                <div class="radio">
                    <h6>
                        <?php if($item == $answer){ ?>
                            <input type="radio" name="<?php echo($name)?>" value="<?php echo($item); ?>" checked><?php echo($item);?>
                        <?php } else { ?> <!-- end of if -->
                            <input type="radio" name="<?php echo($name)?>" value="<?php echo($item); ?>"><?php echo($item);?>
                        <?php } ?> <!-- end of else to test fro checked value -->
                    </h6>
                </div>
            <?php } ?> <!-- end of foreach block -->
        </div>
    </div>
<?php } ?>