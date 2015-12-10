<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/22/2015
 * Time: 9:37 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");

$connect = $fmOrderDB->listLayouts();

if(FileMaker::isError($connect)){
   echo "<p>Error on connection: " .$connect->getMessage() ."</p>";
}else{
    echo "<p>Successfully Connected to database </p>";
}