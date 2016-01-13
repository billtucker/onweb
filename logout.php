<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/23/2015
 * Time: 4:29 PM
 *
 * 1. 01/12/2016 Modified logout script to log user name at logout or indicate unknown user
 */
include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");

include_once($utilities ."utility.php");

if(!session_id()){
    session_start();
}

if(isset($_SESSION['userName'])){
    $log->debug("User: " .$_SESSION['userName'] ." logged off system and session is destroyed");
}else{
    $log->debug("Unknown user logged off system");
}

destroySession();

header("location: " .$homepage ."login.php");