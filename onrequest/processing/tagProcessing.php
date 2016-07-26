<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/16/2015
 * Time: 10:14 AM
 * Process Tags associated with a specific deliverable item. Processing is handled via this module which can be
 * snapped in or out depending on need for this module.
 */


include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";

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
            $tagRowArray = getTagInfoFromPost($post, $tagPk);
            if (isset($tagRowArray)) {
                $tagRecord->setField('PromoCode_Descriptor_t', getTagCodeValue($tagRowArray[0]));
                $tagRecord->setField('PromoCode_3_TagVersion_t', getTagCodeValue($tagRowArray[1]));
                $tagRecord->setField('Tag_Version_Description_t', stripHtmlWithSpaces($tagRowArray[2]));
                $tagRecord->setField('House_Number_t', $tagRowArray[3]);
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

    $prefix = "noTagPkId";
    $search = $prefix . "_*"; //Do not forget the wildcard star!!!!!! so anything noTagPkId
    $tagDescriptor = "td";
    $tagVersion = "tv";
    $tagDescription = "tt";
    $tagHouse = "th";
    $us = "_";
    $allFieldsProcessed = 1;
    $maxItemsToProcess = 4;

    echo PHP_EOL ."Now run search for our No Pk Id" .PHP_EOL;

    foreach (array_key_exists_wildcard($post, $search) as $key => $value) {
        $index = getTagIndex($value);

        if(isset($post[$value]) && !empty($post[$value]) && getKetType($value) == $tagDescriptor){
            $noTagPkWriteArray[0] = getTagCodeValue($post[$prefix .$us .$tagDescriptor  .$us .$index]);
        }

        if(isset($post[$value]) && !empty($post[$value]) && getKetType($value) == $tagVersion){
            $noTagPkWriteArray[1] = getTagCodeValue($post[$prefix .$us .$tagVersion  .$us .$index]);
        }

        if(isset($post[$value]) && !empty($post[$value]) && getKetType($value) == $tagDescription){
            $noTagPkWriteArray[2] = $post[$prefix .$us .$tagDescription  .$us .$index];
        }

        if(isset($post[$value]) && !empty($post[$value]) && getKetType($value) == $tagHouse){
            $noTagPkWriteArray[3] = $post[$prefix .$us .$tagHouse  .$us .$index];
        }

        $allFieldsProcessed++;
        //Now we have values or not written to NoPkId tag values if we do the write them to FM otherwise skip
        if(isset($noTagPkWriteArray[0]) || isset($noTagPkWriteArray[1]) || isset($noTagPkWriteArray[2]) || isset($noTagPkWriteArray[3])){
            if($allFieldsProcessed > $maxItemsToProcess){
                $newTagRecord = $orderDBHandle->createRecord($tagsLayout);
                $newTagRecord->setField("_fk_Deliverable_pk_ID", $deliverablePkId);

                if(isset($noTagPkWriteArray[0]) && !empty($noTagPkWriteArray[0])){
                    $newTagRecord->setField('PromoCode_Descriptor_t', $noTagPkWriteArray[0]);
                }

                if(isset($noTagPkWriteArray[1]) && !empty($noTagPkWriteArray[1])){
                    $newTagRecord->setField('PromoCode_3_TagVersion_t', $noTagPkWriteArray[1]);
                }

                if(isset($noTagPkWriteArray[2]) && !empty($noTagPkWriteArray[2])){
                    $newTagRecord->setField('Tag_Version_Description_t', stripHtmlWithSpaces($noTagPkWriteArray[2]));
                }

                if(isset($noTagPkWriteArray[3]) && !empty($noTagPkWriteArray[3])){
                    $newTagRecord->setField('House_Number_t', $noTagPkWriteArray[3]);
                }

                $addTagResults = $newTagRecord->commit();

                if (FileMaker::isError($addTagResults)) {
                    $errorTitle = "FileMaker Error";
                    $log->error("Failure to save tag (processDeliverableTags() ) " .$tagsLayout ." " .$addTagResults->getMessage() ." " .$addTagResults->getCode());
                    processError($addTagResults->getMessage(), $addTagResults->getErrorString(), "tagProcessing.php", "N/A", $errorTitle);
                    exit;
                }
                $allFieldsProcessed = 1;
                $noTagPkWriteArray = array();
            }

        }

    }
}// end of method do not delete

?>