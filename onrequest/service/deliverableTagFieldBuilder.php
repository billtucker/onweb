<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/19/2015
 * Time: 10:22 AM
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($utilities ."utility.php");

/**
 * These function were added below to building Tags area of Deliverable View
 * @param $itemList -- a list of selections from the [WEB] Project Deliverable Tags layout
 * @param $promoCode -- the actual selected code for this given record
 * @param $pkId -- PkId of the tag
 * @param $index -- row number of item being added
 * @param $displayList -- A list of tags with description separator of &nbsp
 */
function buildTagDropDownNew($itemList, $promoCode, $pkId, $index, $displayList){
    $selectPk = $pkId ."_" .$index;
    echo("<select class='tags' style='width: 60px;' id='" .$selectPk ."' name='" .$selectPk ."' onchange='copyRowData(this.id);'>\n");
    echo("<option value='' style='width: 160px;'></option>\n");
    for($index = 0; $index < count($itemList); $index++){
        if(isset($promoCode)){
            if($promoCode == strtok($itemList[$index], " ")){
                echo("<option value='" .trim($itemList[$index]) ."' selected style='width: 150px;'>" .trim($displayList[$index]) ."</option>\n");
            }else{
                echo("<option value='" .trim($itemList[$index]) ."' style='width: 150px;'>" .trim($displayList[$index]) ."</option>\n");
            }
        }else{
            echo("<option value='" .trim($itemList[$index]) ."' style='width: 150px;'>" .trim($displayList[$index]) ."</option>\n");
        }
    }
    echo("</select>\n");
}

function buildTagDescription($description, $pkId, $index){
    $descriptionPk = $pkId ."_d" .$index;
    echo("<input id='" .$descriptionPk ."' name='" .$descriptionPk ."' style='width: 100%;' value='" .$description ."'>\n");
}

function buildTagHouse($houseNumber, $pkId, $index){
    $houseNumberPk = $pkId ."_h" .$index;
    echo("<input id='" .$houseNumberPk ."' name='" .$houseNumberPk ."' value='" .$houseNumber ."'>\n");
}

/**
 * Methods to display empty plus one row to allow user the ability to add an additional tag without Plus 'add' icon
 * @param $itemList -- a list of selections from the [WEB] Project Deliverable Tags layout
 * @param $index -- row number of item being added
 * @param $displayList -- A list of tags with description separator of &nbsp
 */
function buildTagsDropDownPlusOneNew($itemList, $index, $displayList){
    global $log;

    $log->debug("deliverableTagFieldBuilder - buildTagsDropDownPlusOneNew() - Running Plus One Time: " .getCurrentTime());

    $noTagPkId = "noTagPkId" ."_" .$index;
    echo("<select class='tags' style='width: 60px;' id='" .$noTagPkId ."' name='" .$noTagPkId ."' onchange='copyRowData(this.id);'>\n");
    echo("<option value='' style='width: 160px;'></option>\n");
    for($index = 0; $index < count($itemList); $index++){
        echo("<option value='" .trim($itemList[$index]) ."' style='width: 150px;'>" .trim($displayList[$index]) ."</option>\n");
    }
    echo("</select>\n");
}

function buildTagDescriptionPlusOne($index){
    $descriptionPk = "noTagPkId" ."_d" .$index;
    echo("<input id='" .$descriptionPk ."' name='" .$descriptionPk ."' style='width: 100%;' value=''>\n");
}

function buildTagHousePlusOne($index){
    $houseNumberPk = "noTagPkId" ."_h" .$index;
    echo("<input id='" .$houseNumberPk ."' name='" .$houseNumberPk ."' value=''>\n");
}

function buildTagDeleteCell($index, $canModify){
    $tableId = "tag-table";
    $idNamePrefix = "delRowId" ."_" .$index;
    $titleText = "Click to delete this row";
    $disabledBtn = "Delete row disabled";
    $spanIdName = "delRowSpan" ."_" .$index;
    $clickRowId = "this.parentNode.parentNode.rowIndex";

    if($canModify){
        echo("<button class='btn btn-default btn-sm deleteRow tdc-tag-delete-icon delgrp' id='"
            .$idNamePrefix ."' name='" .$idNamePrefix ."' type='button'
        onclick=delRow('" .$tableId ."'," .$clickRowId ."); title='" .$titleText ."' data-placement='bottom' data-toggle='tooltip'>\n");
        echo("<span class='glyphicon glyphicon-remove-sign delgrp' id='" .$spanIdName ."'></span>\n");
        echo("</button>");
    }else{
        echo("<button class='btn btn-default btn-sm deleteRow tdc-tag-delete-icon delgrp' id='"
            .$idNamePrefix ."' name='" .$idNamePrefix ."' type='button'
        onclick=delRow('" .$tableId ."'," .$clickRowId ."); title='" .$disabledBtn ."' data-placement='bottom' disabled>\n");
        echo("<span class='glyphicon glyphicon-remove-sign delgrp' id='" .$spanIdName ."'></span>\n");
        echo("</button>");
    }
}

?>