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


if(isset($_GET['list']) || isset($_GET['dfile'])){
    if(isset($_GET['list'])) {

        $request = $_GET['list'];

        if (!empty($request)) {
            echo getDirectoryNoJson($request, $dirArray);
        } else {
            $log->error("No Parameters provided for directory search");
            echo "Error No parameters provided";
        }
    }

    if(isset($_GET['dfile'])){

        if(!empty($_GET['dfile'])){
            echo deleteUploadedFile($dirArray, $_GET['dfile']);
        }else{
            echo "Error No delete parameters provided";
            $log->error("No parameters provided for delete of file");
        }

    }
}else{
    $log->error("No parameters for search or delete");
    echo "Error no parameters provided";
}