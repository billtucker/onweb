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
 * Note: The IP used in this file are related to Fox Sports. Since the configuration for FOX is a simple
 * server (single) server setup. The setting IP should read 127.0.0.1.!!!!!!
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
require_once($fmfiles .'FileMaker/FileMaker.php');

$DB_HOST = 'http://192.168.0.14';
$DB_NAME = 'PRO_WORK';
$DB_USER = 'php';
$DB_PASS = 'php';

$fmWorkDB = new FileMaker($DB_NAME, $DB_HOST, $DB_USER, $DB_PASS);