<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/12/2015
 * Time: 1:21 PM
 *
 * Method to get the company small image from [WEB] Login layout, write file name with extension to disk, and
 * dynamically create the tdc-app-conf.php with variable assigned as to the page of company logo.
 * Note: This is a one time event
 * Note: This only works with JPEG images for sure. FileMaker makes a copy of image for download as data.jpg
 *
 * Refactor Notes:
 *
 * 08/14/2015 moved getFileType method to utility.php as the method is common to multiple pages.
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");
include_once($utilities .'utility.php');
/**
 * Method to create the tdc-app-config.php file, get small company image file, save image file to disk, and
 * write the image file location to tdc-app-config.php file
 * @param $userRecordHandle FileMaker record handle to current user
 * @param $imageDir Name of image directory (img or images)
 * @param $imageSmallFileName File name where the image must be stored
 * @param $propertySmallName Name of property where the location of the image file is written
 * @param $imageSplashFileName Name of property where splash image is found
 * @param $propertySplashName = Name of property name for Splash image
 * @param $configFileName Name of configuration file (currently td-app-config.php)
 *
 */
function writeFilesDynamically($userRecordHandle, $imageDir, $imageSmallFileName, $propertySmallName,
                               $imageSplashFileName, $propertySplashName, $configFileName){
    global $log, $fmOrderDB, $root;

    $log->debug("--> Start Configuration write and get images from FileMaker");
    $log->debug("Get Small image from FileMaker");
    $logoSmallUrl = $userRecordHandle->getField('z_SYS_Client_Logo_Print_Small_cc');
    $fileTypeSmall = getFileMakerFileType($logoSmallUrl);
    $log->debug("Small Logo URL: " .$logoSmallUrl);
    $fullSmallImagePath = $imageDir ."/" .$imageSmallFileName .$fileTypeSmall;
    $fullSmallImageWritePath = $root .$imageDir ."/" .$imageSmallFileName .$fileTypeSmall;

    //Get the actual image object from the container and then save image to filesystem
    $smallImage = $fmOrderDB->getContainerData($logoSmallUrl);
    $log->debug("Have Small image file, type, and path: " .$fullSmallImagePath);

    $log->debug("Get Splash image from FileMaker");
    $logoSplashURL = $userRecordHandle->getField("z_SYS_Client_Logo_Splash_Screen_cc");
    $fileTypeSplash = getFileMakerFileType($logoSplashURL);
    $fullSplashImagePath = $imageDir ."/" .$imageSplashFileName .$fileTypeSplash;
    $fullSplashImageWritePath = $root .$imageDir ."/" .$imageSplashFileName .$fileTypeSplash;

    $splashImage = $fmOrderDB->getContainerData($logoSplashURL);
    $log->debug("Have Splash image file, type, and path: " .$fullSplashImagePath);

    //If the image exists then write the image file to image directory then create app-config file
    //should this page fail then the user will see the alt text of logo image tag
    if((isset($smallImage) && !empty($smallImage)) && (isset($splashImage) && !empty($splashImage))){
        $log->debug("Got Small image from FileMaker now write the image: " .$fullSmallImagePath ." to disk");
        $log->debug("Got Splash image from FileMaker now write the image: " .$fullSplashImagePath ." to disk");

        $fp = fopen($fullSmallImageWritePath, "w") or $log->error("Unable to open Small file " .$fullSmallImageWritePath);
        fwrite($fp, $smallImage);
        fclose($fp);

        $fp = fopen($fullSplashImageWritePath, "w") or $log->error("Unable to open Splash file " .$fullSplashImageWritePath);
        fwrite($fp, $splashImage);
        fclose($fp);

        //Now create the app-config file
        $phpHeader = "<?php" .PHP_EOL;
        $phpFooter = "?>";
        $propertyOut = $propertySmallName ." \"" .$fullSmallImagePath ."\"" .";" .PHP_EOL;
        $propertySplashOut = $propertySplashName ." \"" .$fullSplashImagePath ."\"" .";" .PHP_EOL;

        $fp = fopen($root .$configFileName, "w") or $log->error("Unable to open file: " .$root .$configFileName);
        fwrite($fp, $phpHeader);
        fwrite($fp, $propertyOut);
        fwrite($fp, $propertySplashOut);
        fwrite($fp, $phpFooter);
        fclose($fp);
        $log->debug("Finished writing both Small and Splash images and app-config files");
    }else{
        $log->error("Image Small or Splash within FileMaker container was empty for user:  " .$userRecordHandle->getField('User_Name_ct'));
    }

    $log->debug("--> End Configuration file write amd image save method");
}


