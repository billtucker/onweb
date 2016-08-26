<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/22/2016
 * Time: 4:04 PM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");
include_once($utilities .'utility.php');

function writeLogosWithLdap(){
    global $log, $fmOrderDB, $root, $appConfigName;

    $companyLogoSmallPropertyName = '$companyLogoSmall =';
    $companyLogoSplashPropertyName = '$companyLogoSplash =';
    $imageDir = "images";
    $imageSmallFileName = "company_logo_small.";
    $imageSplashFileName = "company_logo_splash.";

    $userNamePropertyField = '$username = ""';
    $baseDnPropertyName = '$baseDnValue = ';
    $companyDomainPropertyName = '$companyDomainValue = ';
    $ldapServerPropertyName = '$ldapServerValue = ';
    $ldapPortPropertyName = '$ldapPortValue = ';
    $ldapGroupNamePropertyName = '$ldapGroupNameValue = ';
    $ldapFieldsToSearchPropertyName = '$ldapfieldsToSearchValue = ';
    $ldapRawFilterPropertyName = '$ldapRawFilterValue = ';
    $hasLdapInfoPropertyName = '$hasLdapInfo =';

    $fieldNameArray = array("baseDnField","companyDomainField","ldapServerField","ldapPortField","groupNamesField",
        "fieldsToSearchField","ldapRawFilterField");


    $loginLayout = '[WEB] Login';

    $allRecordSearch = $fmOrderDB->newFindAllCommand($loginLayout);
    $results = $allRecordSearch->execute();

    if(FileMaker::isError($results)){
        $log->error("FindAll user records failed with error message: " .$results->getMessage());
        //for now
        exit();
    }

    $loginRecord = $results->getFirstRecord();

    //*** Start Image fetch and write operation
    $log->debug("--> Start Configuration write and get images from FileMaker");
    $log->debug("Get Small image from FileMaker");
    $logoSmallUrl = $loginRecord->getField('z_SYS_Client_Logo_Print_Small_cc');
    $fileTypeSmall = getFileMakerContainerFileExtension($logoSmallUrl);
    $log->debug("Small Logo URL: " .$logoSmallUrl);
    $fullSmallImagePath = $imageDir ."/" .$imageSmallFileName .$fileTypeSmall;
    $fullSmallImageWritePath = $root .$imageDir ."/" .$imageSmallFileName .$fileTypeSmall;

    //Get the actual image object from the container and then save image to filesystem
    $smallImage = $fmOrderDB->getContainerData($logoSmallUrl);
    $log->debug("Have Small image file, type, and path: " .$fullSmallImagePath);

    $log->debug("Get Splash image from FileMaker");
    $logoSplashURL = $loginRecord->getField("z_SYS_Client_Logo_Splash_Screen_cc");
    $fileTypeSplash = getFileMakerContainerFileExtension($logoSplashURL);
    $fullSplashImagePath = $imageDir ."/" .$imageSplashFileName .$fileTypeSplash;
    $fullSplashImageWritePath = $root .$imageDir ."/" .$imageSplashFileName .$fileTypeSplash;

    $splashImage = $fmOrderDB->getContainerData($logoSplashURL);
    $log->debug("Have Splash image file, type, and path: " .$fullSplashImagePath);
    //*** End Image fetch and right

    //Start loading LDAP information from the [WEB} Login layout
    $baseDnField = $loginRecord->getField("ONWEB_LDAP_base_dn_ct");
    $companyDomainField = $loginRecord->getField('ONWEB_LDAP_company_domain_ct');
    $ldapServerField = $loginRecord->getField('ONWEB_LDAP_server_ct');
    $ldapPortField = $loginRecord->getField('ONWEB_LDAP_port_ct');
    $groupNamesField = array($loginRecord->getField('ONWEB_LDAP_group_name_ct'));
    $fieldsToSearchField = array($loginRecord->getField('ONWEB_LDAP_fieldsToSearch_ct'));
    $ldapRawFilterField = html_entity_decode($loginRecord->getField('ONWEB_LDAP_filter_ct'));
    //End loading LDAP information

    $fieldsNotNull = 1;

    foreach($fieldNameArray as $fieldName){
        if(!isset($$fieldName) || empty($$fieldName)){
            $fieldsNotNull = 0;
        }
    }

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
        $propertyOut = $companyLogoSmallPropertyName ." \"" .$fullSmallImagePath ."\"" .";" .PHP_EOL;
        $propertySplashOut = $companyLogoSplashPropertyName ." \"" .$fullSplashImagePath ."\"" .";" .PHP_EOL;

        $fp = fopen($root .$appConfigName, "w") or $log->error("Unable to open file: " .$root .$appConfigName);
        fwrite($fp, $phpHeader);
        fwrite($fp, $propertyOut);
        fwrite($fp, $propertySplashOut);
        fwrite($fp, $baseDnPropertyName ."\"" .$baseDnField ."\"" .";" .PHP_EOL);
        fwrite($fp, $userNamePropertyField .";" .PHP_EOL);
        fwrite($fp, $companyDomainPropertyName ."\"" .$companyDomainField ."\"" .";" .PHP_EOL);
        fwrite($fp, $ldapServerPropertyName ."\"" .$ldapServerField ."\"" .";" .PHP_EOL);
        fwrite($fp, $ldapPortPropertyName ."\"" .$ldapPortField ."\"" .";" .PHP_EOL);
        fwrite($fp, $ldapGroupNamePropertyName ."\"" .implode(",", $groupNamesField) ."\"" .";" .PHP_EOL);
        fwrite($fp, $ldapFieldsToSearchPropertyName .'\'' .implode(",", $fieldsToSearchField) .'\'' .";" .PHP_EOL);
        fwrite($fp, $ldapRawFilterPropertyName ."\"" .$ldapRawFilterField ."\"" .";" .PHP_EOL);
        fwrite($fp, $hasLdapInfoPropertyName ." " .$fieldsNotNull .";" .PHP_EOL);
        fwrite($fp, $phpFooter);
        fclose($fp);
        $log->debug("Finished writing both Small and Splash images to include LDAP information and app-config files");
    }else{
        $log->error("Image Small or Splash within FileMaker container was empty at time of login");
    }

    $log->debug("Finished loading logos and LDAP information to tdc-app-config.php file");
}