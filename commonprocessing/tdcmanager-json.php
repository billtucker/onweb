<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/31/2016
 * Time: 12:42 PM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($utilities .'utility.php');


//Fixed hardcoded array of directories for all upload files to search (Yea this is problematic)
$dirArray = array("onrequest" => $root ."uploads" .DIRECTORY_SEPARATOR ."onrequest", "onspot" => $root ."uploads"
    .DIRECTORY_SEPARATOR ."onspot" );

if(isset($_GET['list']) || isset($_GET['dfile'])){
    if(isset($_GET['list'])) {

        $request = $_GET['list'];

        if (!empty($request)) {
            if (strtolower($request) == 'all') {
                $dirListOut = getFullDirectoryList($dirArray);
                echo utf8_encode(json_encode($dirListOut));
            } else {
                $findReq = $request;
                $keyArray = array(strtolower($findReq));
                $valueArray = array($dirArray[strtolower($findReq)]);
                $searchArray = array_combine($keyArray, $valueArray);
                $dirListOut = getFullDirectoryList($searchArray);
                echo utf8_encode(json_encode($dirListOut));
            }
        } else {
            echo utf8_encode(json_encode(array("response" => array("Error" => "No parameters provided"))));
        }
    }

    if(isset($_GET['dfile'])){

        if(!empty($_GET['dfile'])){
            echo utf8_encode(json_encode(deleteUploadedFile($dirArray, $_GET['dfile'])));
        }else{
            echo utf8_encode(json_encode(array("response" => array("Error" => "No parameters provided"))));
        }
    }
}else{
    echo utf8_encode(json_encode(array("response" => array("Error" => "No parameters provided"))));
}