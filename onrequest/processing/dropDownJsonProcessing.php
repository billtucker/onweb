<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 7/21/2016
 * Time: 8:36 AM
 *
 * This file processes POST requests from the request.php page. Each request are related to changes in the
 * Programming_Type_t field of the form. Changes to the drop requires a reload of the Spot_TYpe and Show_COdes
 * drop downs.
 * Currently its assumed that the session variables were set by the request.php page so all that is needed is to grab the
 * session loaded arrays for the dropdown lists
 */

include_once("../request-config.php");
include_once($errors ."errorProcessing.php");
include_once($fmfiles .'order.db.php');
include_once($utilities .'utility.php');


//Now start the session if not set.. Maybe overkill with extra code lines but lets check and start if required
//Maybe move this to config file since its included in all files
if(!session_id()){
    session_start();
}

$showCodeType = "showCodes";
$spotType = "spotTypes";
$returnList = array();

if(isset($_POST['programmingType'])){
    $programmingType = $_POST['programmingType'];
    $log->debug("We have the Programming_Type_t: " .$programmingType);

    if(isset($_POST['listType'])){
        $listType = $_POST['listType'];

        if($listType == $showCodeType){
            if(isset($_SESSION[$showCodesSessionIndex])){
                $log->debug("Dropdown replacement use Session to load Show Codes");
                $showCodes = $_SESSION[$showCodesSessionIndex];
                $returnList = $showCodes[$programmingType];
            }else{
                $log->error("PLace holder for error processing for Show Codes -- Session Not Set!!!");
            }
        }else if($listType == $spotType){
            if(isset($_SESSION[$spotTypesSessionIndex])){
                $log->debug('Dropdown replacement use Session to load Spot Types');
                $spotTypeArray = $_SESSION[$spotTypesSessionIndex];
                $returnList = $spotTypeArray[$programmingType];
            }else{
                $log->error("Place holder for error processing for Spot Types -- Session Not Set!!!");
            }
        }else{
            $log->error("No List type Spot or Show codes were not set for ths send");
        }
    }else{
        $log->error("POST contained no List Type for this request");
    }

    echo json_encode($returnList);
}else{
    $log->error("No Programming_Type_t is set for this POST");
}