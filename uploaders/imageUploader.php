<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/14/2016
 * Time: 9:32 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
include_once($utilities ."utility.php");
include_once($errors ."errorProcessing.php");

$log->debug("Upload Test was called");

if(isset($_FILES) && !empty($_FILES)){
    $log->debug("We have files with data");

    $name = $_FILES['file']['name'];

    $log->debug("The file name is as follows: " .$name);

    if(isset($_POST['pkId'])){
        $filenameTemp = transformFilename($_FILES['file']['name'], $_POST['pkId']);
        $log->debug("The Temp filename is " .$filenameTemp);
    }else{
        $log->debug("POST array was no set fix the issue");
    }

}else{
    $log->debug("The FILES array was not set");
}
