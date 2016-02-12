<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/16/2015
 * Time: 10:14 AM
 * Process Tags associated with a specific deliverable item. Processing is handled via this module which can be
 * snapped in or out depending on need for this module.
 */


include_once("../request-config.php");

//validate user prior to access on this page
include_once($fmfiles . 'order.db.php');
include_once($utilities ."utility.php");
include_once($errors .'errorProcessing.php');

function processDeliverableTags($post, $deliverablePkId, $orderDBHandle){
    global $log;

    //set this to make sure the tag processor does not fail
    $tagRecords = array();

    $tagsLayout = "[WEB] Project Deliverable Tags";

    $tagsFind = $orderDBHandle->newFindCommand($tagsLayout);
    $tagsFind->addFindCriterion("_fk_Deliverable_pk_ID", '==' . $deliverablePkId);
    $tagsResults = $tagsFind->execute();

    if (FileMaker::isError($tagsResults)) {
        if($tagsResults->getMessage() == "No records match the request"){
            $log->debug("No Tag records found");
        }else{
            $errorTitle = "FileMaker Error";
            $log->error("Failure to open (processDeliverableTags() ) " .$tagsLayout ." " .$tagsResults->getMessage() ." " .$tagsResults->getCode());
            processError($tagsResults->getMessage(), $tagsResults->getErrorString(), "tagProcessing.php", $deliverablePkId, $errorTitle);
            exit;
        }

    }else{
        $tagRecords = $tagsResults->getRecords();
    }

    //Get __pk_ID from FM tag layout feed PK to POST array to get values
    foreach ($tagRecords as $tagRecord) {
        $tagPk = $tagRecord->getField('__pk_ID');
        if (isset($post[$tagPk])) {
            if ($post[$tagPk] == 'delete') {
                $deleteResult = $tagRecord->delete(); //This is a FileMaker delete of a record
                if (FileMaker::isError($deleteResult)) {
                    $errorTitle = "FileMaker Error";
                    $log->error("Failure to delete tag (processDeliverableTags() ) " .$tagsLayout ." " .$deleteResult->getMessage() ." " .$deleteResult->getCode());
                    processError($deleteResult->getMessage(), $deleteResult->getErrorString(), "tagProcessing.php", $tagPk, $errorTitle);
                    exit;
                }
            }
        } else {
            $tagRowArray = getRowPerPkId($post, $tagPk);
            if (isset($tagRowArray)) {
                $tagRecord->setField('PromoCode_3_TagVersion_t', $tagRowArray[0]);
                $tagRecord->setField('Tag_Version_Description_t', $tagRowArray[1]);
                $tagRecord->setField('House_Number_t', $tagRowArray[2]);
            }

            $tagCommit = $tagRecord->commit();
            if (FileMaker::isError($tagCommit)) {
                $errorTitle = "FileMaker Error";
                $log->error("Failure to save tag (processDeliverableTags() ) " .$tagsLayout ." " .$tagCommit->getMessage() ." " .$tagCommit->getCode());
                processError($tagCommit->getMessage(), $tagCommit->getErrorString(), "tagProcessing.php", "N/A", $errorTitle);
                exit;
            }
        }
    }

    $desc = "d";
    $house = "h";
    $us = "_";
    $prefix = "noTagPkId";
    $search = $prefix . "_*"; //Do not forget the wildcard star!!!!!!
    $codeDex = 0;
    $descDex = 1;
    $houseDex = 2;
    $rowArray = array();

    //Loop to test if user add data to a noTagPkId field. Loop through all noTagPkId fields if data is found create
    // new Tag Record
    foreach (array_key_exists_wildcard($post, $search) as $key => $value) {
        $itemNum = substr($value, strpos($value, $us) + 1);
        if (is_numeric($itemNum)) {
            if (isset($post[$value]) && !empty($post[$value])) {
                $rowArray[$codeDex] = getPromoCode($post[$value]);
            }

            if (isset($post[$prefix . $us . $desc . $itemNum]) && !empty($post[$prefix . $us . $desc . $itemNum])) {
                if (isset($post[$prefix . $us . $desc . $itemNum]) && strlen($post[$prefix . $us . $desc . $itemNum]) > 0) {
                    $rowArray[$descDex] = $post[$prefix . $us . $desc . $itemNum];
                }
            }

            if (isset($post[$prefix . $us . $house . $itemNum]) && !empty($post[$prefix . $us . $house . $itemNum])) {
                if (isset($post[$prefix . $us . $house . $itemNum]) && strlen($post[$prefix . $us . $house . $itemNum]) > 0) {
                    $rowArray[$houseDex] = $post[$prefix . $us . $house . $itemNum];
                }
            }

            if (isset($rowArray[$codeDex]) || isset($rowArray[$descDex]) || isset($rowArray[$houseDex])) {
                $newTagRecord = $orderDBHandle->createRecord($tagsLayout);
                $newTagRecord->setField("_fk_Deliverable_pk_ID", $deliverablePkId);
                if (isset($rowArray[$codeDex])) {
                    $newTagRecord->setField('PromoCode_3_TagVersion_t', $rowArray[$codeDex]);
                }
                if (isset($rowArray[$descDex])) {
                    $newTagRecord->setField('Tag_Version_Description_t', $rowArray[$descDex]);
                }
                if (isset($rowArray[$houseDex])) {
                    $newTagRecord->setField('House_Number_t', $rowArray[$houseDex]);
                }
                $addTagResults = $newTagRecord->commit();

                if (FileMaker::isError($addTagResults)) {
                    $errorTitle = "FileMaker Error";
                    $log->error("Failure to save tag (processDeliverableTags() ) " .$tagsLayout ." " .$addTagResults->getMessage() ." " .$addTagResults->getCode());
                    processError($addTagResults->getMessage(), $addTagResults->getErrorString(), "tagProcessing.php", "N/A", $errorTitle);
                    exit;
                }
                $rowArray = array();
            }
        }
    }
}

?>