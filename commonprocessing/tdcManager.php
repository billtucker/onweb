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
$dirArray = array("onrequest" => $root ."uploads" .DIRECTORY_SEPARATOR ."onrequest", "onspot" => $root ."uploads" .DIRECTORY_SEPARATOR ."onspot" );

$log->debug("tdcManager called now process the request");

if(isset($_GET['list']) || isset($_GET['dfile'])){
    if(isset($_GET['list'])) {

        $request = $_GET['list'];

        $log->debug("Search param: " .$request);

        if (!empty($request)) {
            echo getDirectoryNoJson($request, $dirArray);
        } else {
            echo "Error No parameters provided";
        }
    }

    if(isset($_GET['dfile'])){

        if(!empty($_GET['dfile'])){
            $log->debug("Delete file operation received to delete file: " .$_GET['dfile']);
            echo deleteUploadedFile($dirArray, $_GET['dfile']);
        }else{
            echo "Error No delete parameters provided";
        }

    }
}else{
    echo "Error no parameters provided";
}