<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/5/2014
 * Time: 10:17 AM
 *
 * Dynamic field builder relative to the ON-AIR Pro Request form
 */

/**
 * Method used to process a dropdown. Method also adds separators for dashes
 * Comments added 02/13/2015
 * @param $valueList -- an array or a single value
 */
function buildRequestDropDownList($valueList){
    if(isset($valueList)){
        echo("<option></option>\n");
        if(is_array($valueList)) {
            foreach ($valueList as $value) {
                if ($value == "-") {
                    echo("<option disabled>--------------------</option>\n");
                } else {
                    echo("<option value=" . $value . ">" . $value . "</option>\n");
                }
            }
        }else{
            echo("<option value=" . $valueList . ">" . $valueList . "</option>\n");
        }
    }else{
        echo("<option></option>");
    }
}


function buildRequestDropDownMap($valueMap){
    if(isset($valueMap)){
        echo("<option></option>\n");
        foreach($valueMap as $key => $value){
            echo("<option value=" .$value .">" .$key ."</option>\n");
        }
    }else{
        echo("<option></option>\n");
    }
}

function buildRequestDropDownWithFields($records){
    if(isset($records)){
        echo("<option></option>\n");
        foreach($records as $record){
            echo("<option value='" .$record->getField('Show_Code_t') ."'>" .$record->getField('Show_Title_t') ."</option>\n");
        }
    }else{
        echo("<option></option>\n");
    }
}


/**
 * This method processes a converted Pipe Array to dropdown box and also add separators for dashed line
 * Comment add 02/13/2015
 * @param $valueList -- an array of values or single value
 * @param $selectedValue -- a single value used to set selected on option list
 */
function buildRequestDropDownListWithValue($valueList, $selectedValue){
    if(isset($valueList)){
        if(is_array($valueList)){
            foreach($valueList as $value){
                if($value == $selectedValue) {
                    echo("<option value='" . $value . "' selected>" . $value . "</option>\n");
                }elseif($value == "-"){
                    echo("<option disabled>--------------------</option>\n");
                }else{
                    echo("<option value='" .$value ."'>" .$value ."</option>\n");
                }
            }
        }else{
            if($valueList = $selectedValue){
                echo("<option value='" . $valueList . "' selected>" .$valueList ."</option>\n");
            }else{
                echo("<option value='" .$valueList ."'>" .$valueList ."</option>\n");
            }
        }
    }else{
        echo("<option></option>\n");
    }
}

function buildRequestDropDownMapWithValue($valueMap, $selectedValue){
    if(isset($valueMap)){
        foreach($valueMap as $key => $value){
            if($selectedValue == $value){
                echo("<option value='" .$value ."' selected>" .$key ."</option>\n");
            }else{
                echo("<option value='" .$value ."'>" .$key ."</option>\n");
            }
        }
    }else{
        echo("<option></option>");
    }
}

function buildSessionDropDownMapWithValue($valueMap, $selectedValue){
    if(isset($valueMap)){
        foreach($valueMap as $key => $value){
            if($selectedValue == $key){
                echo("<option value='" .$key ."' selected>" .$value ."</option>\n");
            }else{
                echo("<option value='" .$key ."'>" .$value ."</option>\n");
            }
        }
    }else{
        echo("<option></option>");
    }
}

function buildSessionDropDown($valueMap){
    echo("<option></option>"); //start with an empty value

    if(isset($valueMap)){
        foreach($valueMap as $key => $value){
            echo("<option value='" .$key ."'>" .$value ."</option>\n");
        }
    }
}

/**
 * New function to process Show Codes view 02/17/2015
 * @param $records -- show code record handle to layout
 * @param $selectedValue -- field value if set
 */
function buildDropDownWithFieldsWithValue($records, $selectedValue){
    if(isset($records)){
        foreach($records as $record){
            if($selectedValue == $record->getField('Show_Code_t')){
                echo("<option value='" .$record->getField('Show_Code_t') ."' selected>" .$record->getField('Show_Title_t') ."</option>\n");
            }else{
                echo("<option value='" .$record->getField('Show_Code_t') ."'>" .$record->getField('Show_Title_t') ."</option>\n");
            }
        }
    }else{
        echo("<option></option>\n");
    }
}

/**
 * This is a dynamic builder method to generate Date Only fields of Request form
 * @param $fieldName -- The FileMaker field name which will have a numeric value appended per list counter
 * @param $value -- if user had previous selected dates
 * @param $counter -- The counter or list number appended to ID and Name filed value of hidden text field
 */
function buildDateOnlyField($fieldName, $value, $counter){
    $underScore = "_";
    $idName = $fieldName .$underScore .$counter;
    echo("<div class=\"form-group\">");
    echo("<div class=\"input-group date date_only\" data_date=\"\" data-date-format=\"mm/dd/yyyy\"
            data-link-field=\"$idName\" data-link-format=\"mm/dd/yyyy\">\n");

    if(isset($value)){
        echo("\t\t<input class=\"tdc-request-list-height\" type=\"text\" value=\"$value\" size=\"16\">\n");
    }else{
        echo("\t\t<input class=\"tdc-request-list-height\" type=\"text\" value=\"\" size=\"16\">\n");
    }

    echo("\t\t<span class=\"input-group-addon\">\n");
    echo("\t\t\t<span class=\"glyphicon glyphicon-calendar\"></span>\n");
    echo("\t\t</span>\n");
    echo("\t\t</div>\n");
    echo("\t\t</div>\n");
    echo("\t\t<input type=\"hidden\" id=\"$idName\" name=\"$idName\" value=\"$value\">\n");
}

function buildDateOnlyFieldDeliverable($fieldName, $value, $counter){
    $idName = $fieldName; //TODO remove this assignment and refactor the name
    echo("<div class=\"form-group\">");
    echo("<div class=\"input-group date date_only\" data_date=\"\" data-date-format=\"mm/dd/yyyy\"
            data-link-field=\"$idName\" data-link-format=\"mm/dd/yyyy\">\n");

    if(isset($value)){
        echo("\t\t<input class='tdc-deliverable-input-height' type=\"text\" value=\"$value\" size=\"16\">\n");
    }else{
        echo("\t\t<input type=\"text\" value=\"\" size=\"16\">\n");
    }

    echo("\t\t<span class=\"input-group-addon\">\n");
    echo("\t\t\t<span class=\"glyphicon glyphicon-calendar\"></span>\n");
    echo("\t\t</span>\n");
    echo("\t\t</div>\n");
    echo("\t\t</div>\n");
    echo("\t\t<input type=\"hidden\" id=\"$idName\" name=\"$idName\" value=\"$value\">\n");
}

?>