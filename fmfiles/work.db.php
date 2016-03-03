<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/8/2015
 * Time: 3:19 PM
 *
 * FileMaker php connection file to connect to PRO_WORK databases and specifically login tables For Spot Viewer
 * operations
 *
 *  Note: The IP used in this file 127.0.0.1 is for a local port 80 connection. The IP should be modified to
 * reflect the actual IP of the server
 *
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
require_once($fmfiles .'FileMaker/FileMaker.php');

$DB_HOST = 'http://127.0.0.1';
$DB_NAME = 'PRO_WORK';
$DB_USER = '0ap$Admin';
$DB_PASS = 'gui39vp4s';

$fmWorkDB = new FileMaker($DB_NAME, $DB_HOST, $DB_USER, $DB_PASS);