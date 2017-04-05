<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 7/18/2016
 * Time: 4:20 PM
 *
 * This file loads various dropdown data to the session for usage on subsequent page loads. This will hopefully
 * speed up page loads by having data available at the session over connections to FileMaker to access data.
 * Note: Changes made at the FM level after an intial page load in the Request module will require the user to
 * logout of the application and login again to make the new values appear in the dropdown(s)
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($utilities ."utility.php");

/**
 * Method to load Show Codes from FM DB then load data to Session attribute to be used in subsequent page loads
 * @param $dbHandle FileMaker handle to FM database
 * @param $allProgrammingTypes String all possible Programming_Type_t entries for this version of On-Air Pro
 * @param $requestPkId String primary key for this request record
 * @param $pageUrl String URL from calling page used primarily in error reporting and logging
 * @return array return array of all Show Codes grouped by programming type
 */
function loadShowCodeSessionData($dbHandle, $allProgrammingTypes, $requestPkId, $pageUrl){
    global $log, $showCodesSessionIndex;

    if(!session_id()){
        session_start();
    }

    if(!isset($_SESSION[$showCodesSessionIndex])){
        $log->debug("Show codes session is not set so load Session and showCodeItems array");
        $showCodeItems = buildShowCodesSessionArray($dbHandle, $allProgrammingTypes, $requestPkId, $pageUrl);
        $_SESSION[$showCodesSessionIndex] = $showCodeItems;
    }else{
        $log->debug("Use Session to load showCodeItems array");
        $showCodeItems = $_SESSION[$showCodesSessionIndex];
    }

    return $showCodeItems;
}

/**
 * Method to load Spot Types from FM DB then load data to Session attribute to be used in subsequent page loads
 * @param $dbHandle FileMaker handle to FM database
 * @param $allProgrammingTypes String all possible Programming_Type_t entries for this version of On-Air Pro
 * @return array return array of all Spots Types grouped by programming type
 */
function loadSpotTypesSessionData($dbHandle, $allProgrammingTypes){
    global $log, $spotTypesSessionIndex;

    if (!session_id()) {
        session_start();
    }

    $spotTypeCodeDescriptionField = "Spot_Type_t";
    $spotTypeDivision = "Spot_Type_Programming_t";
    $spotListFilter = "Spot Type List";

    if (!isset($_SESSION[$spotTypesSessionIndex])) {
        $spotTypeArray = buildSpotVersionsSessionArray($spotListFilter, $spotTypeCodeDescriptionField, $spotTypeCodeDescriptionField,
            $spotTypeDivision, $allProgrammingTypes, "Spot Types", $dbHandle, $spotTypeCodeDescriptionField);

        $_SESSION[$spotTypesSessionIndex] = $spotTypeArray;
    } else {
        $log->debug('Using Session to load Spot Types');
        $spotTypeArray = $_SESSION[$spotTypesSessionIndex];
    }
    return $spotTypeArray;
}

/**
 * Method to load Version Tags from FM DB then load data to Session attribute to be used in subsequent page loads
 * @param $dbHandle FileMaker handle to FM database
 * @param $allProgrammingTypes String all possible Programming_Type_t entries for this version of On-Air Pro
 * @return array return array of all Version Tags grouped by programming type
 */
function loadTagsSessionData($dbHandle, $allProgrammingTypes){
    global $log, $tagsSessionIndex;

    if (!session_id()) {
        session_start();
    }

    if(!isset($_SESSION[$tagsSessionIndex])){
        $tagFindKey = "Version List";
        $tagCodeField = "Version_List_t";
        $tagDescriptionField = "Version_Description_t";
        $tagDivisionField = "Version_ProgrammingType_t";
        $tagsArray = buildSpotVersionsSessionArray($tagFindKey, $tagCodeField, $tagDescriptionField, $tagDivisionField, $allProgrammingTypes,
            "Tags", $dbHandle, $tagDescriptionField);

        $_SESSION[$tagsSessionIndex] = $tagsArray;
    }else{
        $log->debug("Using Sessiopn to load Tags");
        $tagsArray = $_SESSION[$tagsSessionIndex];
    }

    return $tagsArray;
}

/**
 * Method to load Version Descriptors from FM DB then load data to Session attribute to be used in subsequent page loads
 * @param $dbHandle FileMaker handle to FM database
 * @param $allProgrammingTypes String all possible Programming_Type_t entries for this version of On-Air Pro
 * @return array return array of all Version Descriptors grouped by programming type
 */
function loadVersionDecriptorSessionData($dbHandle, $allProgrammingTypes){
    global $log, $versionDescriptorSessionIndex;

    if (!session_id()) {
        session_start();
    }

    If(!isset($_SESSION[$versionDescriptorSessionIndex])){
        $versionDescriptorFindKey = "Version Descriptor";
        $versionDescriptorCodeField = "Version_Descriptor_t";
        $versionDescriptorDescriptionField = "Version_Descriptor_Description_t";
        $versionDescriptorDivisionField = "Version_Descriptor_Programming_t";

        $versionDescriptorArray = buildSpotVersionsSessionArray($versionDescriptorFindKey, $versionDescriptorCodeField, $versionDescriptorDescriptionField,
            $versionDescriptorDivisionField, $allProgrammingTypes, "Version Descriptors", $dbHandle, $versionDescriptorDescriptionField);

        $_SESSION[$versionDescriptorSessionIndex] = $versionDescriptorArray;
    }else{
        $log->debug("Using Session to load Version Descritors");
        $versionDescriptorArray = $_SESSION[$versionDescriptorSessionIndex];
    }
    return $versionDescriptorArray;
}
