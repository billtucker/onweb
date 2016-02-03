<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/14/2015
 * Time: 10:09 AM
 *
 * This code is used to display an image directly from the FileMaker database. This code is not in use at the time this
 * file was generated. However, it is expected that at some point this functionality might be required.
 *
 */

include_once("onspot-config.php");
include_once($fmfiles ."work.db.php");
include_once($utilities ."utility.php");

if(isset($_GET['url'])){
    $url = $_GET['url'];
}

if(isset($url) && !empty($url)){

    $fileType = getFileMakerContainerFileExtension($url);

    if($fileType == 'jpg'){
        header("Content-type: image/jpeg");
    }else if($fileType == "gif"){
        header("Content-type: image/gif");
    }else if($fileType == "png") {
        header("Content-type image/png");
    }else if($fileType == "bmp"){
        header("Content-type image/bmp");
    }elseif($fileType == "ico"){
        header("Content-type image/x-icon");
    }else{
        $log->warn("File type is not supported: " .$fileType);
        //This is the recommended default content-type to use over not sending content-type or sending unknown
        header("Content-type application/octet-stream");
    }

    echo $fmDB->getContainerData($url);
}