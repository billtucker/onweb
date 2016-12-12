<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/8/2015
 * Time: 3:02 PM
 *
 * FileMaker php connection file to connect to PRO_ORDER databases and specifically login tables For Request
 * operations
 *
 * Note: The IP used in this file 127.0.0.1 is for a local port 80 connection. The IP should be modified to
 * reflect the actual IP of the server
 *
 * 1. Modified username and password for FileMaker On-Air Pro access
 *
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
require_once($fmfiles .'FileMaker/FileMaker.php');

$DB_HOST = 'http://127.0.0.1';
$DB_NAME = 'PRO_ORDER';
//Note this is the new username and password for PRO_Work and PRO_ORDER
$DB_USER = 'php';
$DB_PASS = '8TS-za3C3euUVuw-h^GB';

$fmOrderDB = new FileMaker($DB_NAME, $DB_HOST, $DB_USER, $DB_PASS);