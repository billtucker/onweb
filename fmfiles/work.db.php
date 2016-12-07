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

/*
 * The username and password must be set to php until such time validation scripts are modified not not look for
 * the php username
 */
$DB_HOST = 'http://127.0.0.1';
$DB_NAME = 'PRO_WORK';
$DB_USER = 'php';
$DB_PASS = 'php';

//Note this is the future username for PRO_Work and PRO_ORDER
//DB_USER = 'php'
//DB_PASS = '8TS-za3C3euUVuw-h^GB'

$fmWorkDB = new FileMaker($DB_NAME, $DB_HOST, $DB_USER, $DB_PASS);