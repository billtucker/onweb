<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/23/2015
 * Time: 4:29 PM
 */
include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");

include_once($utilities ."utility.php");

destroySession();

header("location: " .$homepage ."login.php");