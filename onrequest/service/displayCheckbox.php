<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/16/2014
 * Time: 1:44 PM
 * A function to display checkbox elements dynamically from FileMaker On-Air Pro database
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($utilities ."utility.php");

function processCheckBox($rawItems, $question, $questionNum, $required, $answerItem, $name){
    global $log;
    $log->debug("Inside processCheckBox method");
    $items = convertPipeToArray($rawItems);
    $convertedAnswer = $answerItem;
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
    <?php foreach($items as $item){ ?>
        <div class="checkbox">
            <?php if((is_array($convertedAnswer))){ ?>
                <?php if(isItemContainedInArray($convertedAnswer, $item)){ ?>
                    <h6><input type="checkbox" id="<?php echo($name ."_" .removeSpaces($item)); ?>" name="<?php echo($name); ?>" value="<?php echo($item); ?>" checked><?php echo($item); ?></h6>
                <?php } else { ?> <!-- end of test is item contained in array -->
                    <h6><input type="checkbox" id="<?php echo($name ."_" .removeSpaces($item)); ?>" name="<?php echo($name); ?>" value="<?php echo($item); ?>"><?php echo($item); ?></h6>
                <?php } ?> <!-- end of else that item was not array -->
            <?php }else{ ?> <!-- end of if to to test for array -->
                <?php if((strlen($convertedAnswer) > 0) && $item == $convertedAnswer){ ?>
                    <h6><input type="checkbox" id="<?php echo($name ."_" .removeSpaces($item)); ?>" name="<?php echo($name); ?>" value="<?php echo($item);?>" checked><?php echo($item); ?></h6>
                <?php } else { ?> <!-- end of test if single item is contained in list -->
                    <h6><input type="checkbox" id="<?php echo($name ."_" .removeSpaces($item)); ?>" name="<?php echo($name); ?>" value="<?php echo($item); ?>"><?php echo($item); ?></h6>
                <?php } ?>
            <?php } ?>
        </div>
    <?php } ?> <!-- end of foreach loop on all items -->
<?php } ?>