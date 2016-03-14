<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/25/2014
 * Time: 4:39 PM
 */

///figure out this error reporting and what is required /////
//ini_set('displays_errors',1);
// Report simple running errors
error_reporting(E_ERROR | E_WARNING | E_PARSE);

include_once("request-config.php");
include_once($utilities ."utility.php");
include_once($requestService .'displayTextbox.php');
include_once($requestService .'displayRadio.php');
include_once($requestService .'displayDatePicker.php');
include_once($requestService .'displayDropDown.php');
include_once($requestService .'displayCheckbox.php');
include_once($requestService .'displayFilePicker.php');
include_once($requestService .'displayImagePicker_old.php');

function buildMetaComponent($metaRelatedRecords, $canModify){
    global $log;

    foreach($metaRelatedRecords as $metaItem) {
        $name = $metaItem->getField('__pk_ID');
        $label = $metaItem -> getField('Label');
        $instructions = $metaItem -> getField('Instructions');
        $order_n = $metaItem->getField('#');
        $input_type = $metaItem->getField('Input_Type');
        $value_required_n = $metaItem -> getField('Value_Required_n');
        $answer_values_pipe_ct = $metaItem -> getField('Answer_Values_Pipe_ct');
        $input_values_pipe_ct = $metaItem -> getField('Input_Values_Pipe_ct');
        $answer = $metaItem->getField('Answer');


        switch (strtolower($input_type)) {
            case "text":
                processTextField($label, $order_n, $name, $value_required_n, $instructions, $answer);
                break;
            case "radio button":
                processRadioButton($input_values_pipe_ct, $name, $label, $order_n, $value_required_n, $answer);
                break;
            case "date calendar":
                processDatePicker($name, $label, $order_n, $value_required_n, $instructions, $answer);
                break;
            case "pop-up menu":
                processDropDown($input_values_pipe_ct, $name, $label, $order_n, $value_required_n, explodedCrString($answer));
                break;
            case 'drop-down list':
                processDropDown($input_values_pipe_ct, $name, $label, $order_n, $value_required_n, explodedCrString($answer));
                break;
            case "checkbox":
                processCheckBox($input_values_pipe_ct, $label, $order_n, $value_required_n, explodedCrString($answer), $name);
                break;
            case "file":
                $fileUrl = $metaItem->getField('Answer_Container_Data_r');
                $fileName = $metaItem->getField('Upload_Filename_Original_t');
                processFilePicker($label, $order_n, $value_required_n, $fileName, $name, $fileUrl, $canModify);
                break;
            case "image":
                $imageUrl = $metaItem->getField('Answer_Container_Data_r');
                $fileName = $metaItem->getField('Upload_Filename_Original_t');
                processImagePicker($label, $order_n, $value_required_n, $fileName, $name, $imageUrl, $canModify);
                break;
            default:
                $log->warn("This Field was not defined input type: " .$input_type ." field");
                break;
        }
    }
}

?>
