<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 1/13/2015
 * Time: 2:43 PM
 *
 * This file use snappy and wkhtmltopdf to produce PDF of the page. Class support can be found under the vendor folder
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
include_once('utility.php');
include_once("$validation" ."user_validation.php");
include_once($root .'/vendor/autoload.php');
include_once($errors .'errorProcessing.php');

$pageUrl = urlencode("https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");

//validate the user before sending the print operation. This ensures that no 
//call to print page can be executed without user session enabled
printValidation($site_prefix, $pageUrl);

use \Knp\Snappy\Pdf;

//Pay attention to the path statement to executable -- escape 'spaces with slashes
//Note: When installing the executable make not to install outside of Program Files directory
//The try catch Exception block may not catch anything thrown as the KNLabs-SNAPPY
// classes throw minimal set of Exception
//TODO route exception thrown to site Error page
try{
    $log->debug("printPage.php - set exe location and options");
    $snappy = new Pdf('C://"Program Files/"/wkhtmltopdf/bin/wkhtmltopdf.exe');
    $snappy->setOption('page-size', 'A3');
    $snappy->setOption('disable-external-links', true);
}catch(Exception $e){
    $log->error("printPage.php - New PDF Class Exception: " .$e->getMessage());
}

if(isset($_POST['url'])){
    $urlIn = urldecode($_POST['url']);

    $url = setPrintEncryptionUrl($urlIn);

    $log->debug("Use Post to get URL: " .$url);

    header('Content-Type: application/pdf');
    header('Cache-Control: public, must-revalidate, max-age=0'); // HTTP/1.1
    header('Pragma: public');
    header('Expires: Sat, 26 Jul 1997 05:00:00 GMT'); // Date in the past
    header('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT');
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="print_page.pdf"');

    try{
        echo $snappy->getOutput($url);
    }catch (Exception $e){
        $log->error("Exception thrown PDF Generation error: " .$e->getMessage());
    }
}else{
    $errorTitle = "Print Page Error";
    $log->error("Error Print Operation", "No URL sent from page to print", $pageUrl, null, null);
    processError("Error Print Operation", "Error Print Operation", $pageUrl, null, null, $errorTitle);
    exit();
}