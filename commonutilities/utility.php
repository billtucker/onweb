<?php
/**
 * utility.php contains a collection of methods as helper function to process, transform, or validate data
 * from the presentation or business lawyers of the site. These functions are interchangeable with other
 * FileMaker site.
 *
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/17/2014
 * Time: 9:39 AM
 *
 * Refactor Notes:
 *
 * 1. 7/28/2015 -- added method canViewOrEditWithOverride() to override current canViewOrEdit() method so as to set as
 *    true any privilege set validation. This was added in anticipation that the user will not want to add privilege
 *    set as required by the application
 * 2. 8/28/2015 -- Added new method to return which header_top_lvl file to use in the include_once method
 *
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb/onweb-config.php");
include_once($errors ."errorProcessing.php");

/**
 * Validate if the items is in the array to set a checked value of input block
 * @param $items target String to validate
 * @param $checkedItem Array of Strings
 * @return bool if found return true
 */
function isItemContainedInArray($items, $checkedItem){
    if (in_array(trim($checkedItem), $items, false)) {
        return true;
    }else{
        return false;
    }
}

/**
 * Convert pipe delimited to Array
 * @param $items pipe delineated array of items or a single entry (i.e. item1|item2|itemn)
 * @return array returns an array or single item string
 */
function convertPipeToArray($items){
    if(isPipeDelaminatedArray($items)) {
        return explode("|", trim($items));
    }else{
        return $items;
    }
}

/**
 * Convert Pipes to PHP array or return the simple selected value
 * @param $checked a String of pipe delaminated oir just String value
 * @return array|string converted String array or just a string
 */
function convertAnswerToValue($checked){
    if(isPipeDelaminatedArray($checked)){
        return convertPipeToArray($checked);
    }else{
        return trim($checked);
    }
}


/**
 * Test if the value is a FileMaker array Pipe Delimited value set.
 * @param $item String pipe delaminated items or just a String
 * @return bool true if pipe delaminated
 */
function isPipeDelaminatedArray($item){
    $searchString = "|";

    if(strstr($item, $searchString)){
        return true;
    }else{
        return false;
    }
}

/**
 * @param $buttonLabel Name of Project Type
 * @param $pkId -- FileMaker primary key for Project Type
 * Function used by index.php to display Project Type buttons
 */
function getButtonCode($buttonLabel, $pkId){
    $button = '<div class="col-xs-2 col-lg-2"><button type="button" class="btn btn-primary btn-block"
        onclick="location.href=\'onrequest/processing/getNewPackageType.php?pkId=' .urlencode($pkId) .'\'">' .$buttonLabel .'</button>';
    echo $button ."\n";
}

/**
 * Utility function to display success dynamic Javascript function
 * This utility relates to Display Image and Display file methods
 * This method shares global variables name with buildUploadClickListener method
 * @param $listenerFunctionName String value of listener name
 * @param $uploadSuccessDivId String <DIV> ID value
 * @param $hiddenDivId String <DIV> ID value
 */
function buildShowSuccessFunction($listenerFunctionName, $uploadSuccessDivId, $hiddenDivId){
    global $sharedFunctionName;

    echo("\t /* This function is dynamically generated */ \n");
    echo("\t function " .$listenerFunctionName ."(){\n");
    echo("\t\t $('#" .$uploadSuccessDivId ."').fadeIn(100).delay(5000).fadeOut(500);\n");
    echo("\t\t $('#" .$hiddenDivId ."').hide();\n");
    echo("\t }\n");
    $sharedFunctionName = $listenerFunctionName;
}


/**
 * Utility function to add Javascript listener function for upload button
 * This utility relates to Display Image and Display file methods
 * This method shares global variables name with buildShowSuccessFunction method
 * @param $questionNumber String meta question number
 * @param $uploadBtnId String ID value assigned to button to upload Item
 */
function buildUploadClickListener($questionNumber, $uploadBtnId){
    global $sharedFunctionName;

    $underscore = '_';
    $uploadImageBtnListener = "uploadImageBtn" .$underscore .$questionNumber;
    echo("\t\t /* This event listener is dynamically generated */ \n");
    echo("\t\t var " .$uploadImageBtnListener ." = document.getElementById('" .$uploadBtnId ."');\n");
    echo("\t\t " .$uploadImageBtnListener .".addEventListener('click', " .$sharedFunctionName .", false);\n");
}

/**
 * A simple function to remove spaces from a string
 * @param $stringValue - String where spaces are to be removed
 * @return mixed - String value with all spaces removed
 */
function removeSpaces($stringValue){
    return preg_replace("/\s+/", "", $stringValue);
}


//******* Start New collection of functions in support of Tag processing of any type *******/

//method returns an array list of items where the key equals wildcard search
function array_key_exists_wildcard ( $array, $search, $return = '' ) {
    $search = str_replace( '\*', '.*?', preg_quote( $search, '/' ) );
    $result = preg_grep( '/^' . $search . '$/i', array_keys( $array ) );
    if ( $return == 'key-value' )
        return array_intersect_key( $array, array_flip( $result ) );
    return $result;
}

//method returned PromoCode from line with PromoCode and FM Description text by first space character
function getPromoCode($codeLine){
    return substr($codeLine, 0, strpos($codeLine, " "));
}

/**
 * method returns PK from line with PK and underscore and index value
 * @param $pkString String value containing the FileMaker item PK
 * @return string value of the PK without unique identifiers
 */
function returnPK($pkString){
    $us = "_";
    return substr($pkString, 0, strpos($pkString, $us));
}

//method tests if Primary Key suffix is a number
// (i.e. NS 66CTT 5UCX4 29ARW SK7E9_1 is true NS 66CTT 5UCX4 29ARW SK7E9_d1 is false)
function isSuffixNumeric($numericStr){
    $us = "_";
    return is_numeric(substr($numericStr, strpos($numericStr, $us) + 1, 2));
}

//method assembles a array of Primary keys and associated value from passed in Primary key
//(i.e. NS 66CTT 5UCX4 29ARW SK7E9 is passed in to method NS 66CTT 5UCX4 29ARW SK7E9_1, NS 66CTT 5UCX4 29ARW SK7E9_d1,
//and NS 66CTT 5UCX4 29ARW SK7E9_h1 are returned)
function getRowPerPkId($_POSTArray, $target){
    global $log;
    $results = array();

    if(isset($_POSTArray)){
        if(is_array($_POSTArray)){
            foreach($_POSTArray as $key => $value){
                if(returnPK($key) == $target){
                    if(isSuffixNumeric($key)){
                        array_push($results, getPromoCode($value));
                    }else{
                        array_push($results, $value);
                    }
                }
            }
        }
    }
    $log->debug("GetRowPerPkId method array size: " .count($results));
    return $results;
}

/**
 * This method is simply to insert two HTML spaces for formatting. This method is subject to change as formatting
 * will more likely change over time.
 * @param $line a tag line DDD separated by a space now add two HTML spaces for formatting
 * @return string return String constructed line
 */
function convertTagDisplay($line){
    $sep = " &nbsp; &nbsp; ";
    //$sep = " &emsp; &emsp; ";
    $code = substr($line, 0, strpos($line, " "));
    $text = substr($line, strpos($line, " ") + 1);
    return $code .$sep .$text;
}

//******* End New collection of functions in support of Tag processing of any type *******/

/**
 * Encrypt function with Salt provides consistent results
 * @param $q String value to encrypt
 * @return string encrypted String value
 */
function encryptPassowrdKey( $q ) {
    $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
    $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
    return( $qEncoded );
}

/**
 * Decrypt function with Salt provides consistent results
 * @param $q String value to decrypt
 * @return string decrypted String
 */
function decryptPassowrdKey( $q ) {
    $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
    $qDecoded      = rtrim( mcrypt_decrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), base64_decode( $q ), MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ), "\0");
    return( $qDecoded );
}

/**
 * Validate equality of encrypted (password or pin) value from FM database
 * @param $dbValue String encrypted value from FileMaker
 * @param $postValue String encrypted value from login process
 * @return bool match returns true or false if they do not match
 */
function isPasswordPinMatch($dbValue, $postValue){
    if($dbValue == $postValue){
        return true;
    }
    return false;
}

/**
 * The method encrypts and appends pass through for printing or PDF generation
 * if pk param exists then append p1 param with an "&" character if no params exists add p1 with "?" character
 * @param $url -- String full URL
 * @return string -- return URL with encrypted print  pass through
 */
function setPrintEncryptionUrl($url){
    global $log;

    $searchChar = "?";
    $appender = "p1=";
    $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
    $dateFormat = "ndYgi";
    $prefix= '';
    $phrase = "a super secret password phrase$ 5102";

    if(strpos($url, $searchChar)){
        $prefix .= "&" .$appender;
    }else{
        $prefix .= "?" .$appender;
    }

    //$q = date($dateFormat) .$phrase;
	$q = $phrase;
    $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
	$log->debug("setPrintEncryptionUrl() - Data for p1: " .$prefix .$qEncoded);
    $url .= $prefix .$qEncoded;
    return $url;

}

/**
 * Validate that p1 values matches generated print pass through value
 * @param $p1In -- encrypted pass-through value
 * @return bool -- return true if the generated value matches passed in value
 */
function validatePassThrough($p1In){
    global $log;

    $cryptKey  = 'qJB0rGtIn5UB1xG03efyCp';
    $dateFormat = "ndYgi";
    $phrase = "a super secret password phrase$ 5102";
    //$q = date($dateFormat) .$phrase;
	$q = $phrase;
    $qEncoded      = base64_encode( mcrypt_encrypt( MCRYPT_RIJNDAEL_256, md5( $cryptKey ), $q, MCRYPT_MODE_CBC, md5( md5( $cryptKey ) ) ) );
    $log->debug("validatePassThrough() - p1In raw " .$p1In );
	$log->debug("validatePassThrough() - compared with: " .$qEncoded);
    if($qEncoded == $p1In){
        return true;
    }else{
        return false;
    }
}

/**
 * A simple function to centralize session timeout reset functionality
 * per page load. Reset of session time will be checked in user_validation
 * and save operations.
 */
function resetSessionTimeout(){
	global $log;
	
    if(!session_id()){
        session_start();
    }

    //replace value in last activity to current time
    $_SESSION['LAST_ACTIVITY'] = time();
}

/**
 * @param $session_array -- array of permissions from FileMaker user table extracted from accessLevel $_SESSION
 * @param $siteSection -- String value of pages are being accessed (form now request or spotviewer)
 * @return bool -- if site section is found in permission data set return true otherwise false
 */
function canAccessSiteSection($session_array, $siteSection){

    if(!isset($session_array) || empty($session_array)){
        return false;
    }

    if(is_array($session_array)){
        foreach($session_array as $pItem){
            if(strpos($pItem, $siteSection) !== false){
                return true;
            }
        }
    }else{
        $pos = strpos($session_array, $siteSection);
        if($pos !== false){
            return true;
        }
    }
    return false;
}


/**
 * @param $session_array $session_array String privileges from FileMaker of one or more privileges to make modifications
 * @param $siteSection String value of page to access OnSpot or Request (for now)
 * @return bool true if combo of siteSection plus Modify is found in array then edit abilities are granted
 */
function canModify($session_array, $siteSection){
    $searchTerm1 = $siteSection ."Modify";
    $searchTerm2 = $siteSection ."NotesAdd";


    if(!isset($session_array) || empty($session_array)){
        return false;
    }

    if(is_array($session_array)){
        if(in_array($searchTerm1, $session_array) || in_array($searchTerm2, $session_array)){
            return true;
        }
    }else{
        if(($searchTerm1 == $session_array) || ($searchTerm2 == $session_array)){
            return true;
        }
    }
    return false;
}

/**
 * A consolidated method to determine if user has view privilege for a page and has ability to modify/edit/view a given
 * page element such as <textarea> notes or the <radio> button approval block associated with OnSpot Viewer
 * @param $session_privilege_set String privilege(s) from FileMaker of one or more privileges to make modifications
 * @param $section String value of page to access OnSpot or Request (for now)
 * @param $item String representation of the request to view what page or modify an element like Notes or approval block
 * @return bool true if permission is granted or false to block/disable/hide element
 */
function canViewOrEdit($session_privilege_set, $section, $item){
    $accessKeyWord = $section .$item;

    if(!isset($session_privilege_set) || empty($session_privilege_set)){
        return false;
    }

    if(is_array($session_privilege_set)){
        if(isItemContainedInArray($session_privilege_set, $accessKeyWord)){
            return true;
        }
    }else{
        if($session_privilege_set == $accessKeyWord){
            return true;
        }
    }
    return false;
}

/**
 * A consolidated method to determine if user has view privilege for a page and has ability to modify/edit/view a given
 * page element such as <textarea> notes or the <radio> button approval block associated with OnSpot Viewer
 * @param $session_privilege_set String privilege(s) from FileMaker of one or more privileges to make modifications
 * @param $section String value of page to access OnSpot or Request (for now)
 * @param $item String representation of the request to view what page or modify an element like Notes or approval block
 * @param $override boolean item will return true without regard to the actual privilege set.
 * @return bool true if permission is granted or false to block/disable/hide element
 */
function canViewOrEditWithOverride($session_privilege_set, $section, $item, $override){
    $accessKeyWord = $section .$item;

    //if set this return true to override the actual privilege set. This was added in anticipation of a future requirement
    if($override){
        return true;
    }

    if(!isset($session_privilege_set) || empty($session_privilege_set)){
        return false;
    }

    if(is_array($session_privilege_set)){
        if(isItemContainedInArray($session_privilege_set, $accessKeyWord)){
            return true;
        }
    }else{
        if($session_privilege_set == $accessKeyWord){
            return true;
        }
    }
    return false;
}

/**
 * A centralized method to destroy/unset all session attributes
 */
function destroySession(){
    if(!session_id()){
        session_start();
    }

    unset($_SESSION['authenticated']);
    unset($_SESSION['firstName']);
    unset($_SESSION['lastName']);
    unset($_SESSION['accessLevel']);
    unset($_SESSION['userName']);
    unset( $_SESSION['installedPlugins']);
    session_destroy();
}

/**
 * This generic method validates that the user record contains at least the ON-WEB plugin to access the site.
 * If not then redirect user to error page and inform them they do not have privilege to access the site
 * @param $userName String username form login page
 * @param $installedPlugins converted SESSION array from FM database field using the user record in [WEB] Login layout
 * @param $pluginName String name of the plugin to validate
 */
function validatePlugin($userName, $installedPlugins, $pluginName){
    global $log;

    if(!isset($installedPlugins) || empty($installedPlugins)){
        destroySession();
        $log->debug("Test for empty - User does not have: " .$pluginName ." access now redirect to error username: " .$userName);
        $errorMessage = "The " .$pluginName ." plug-in has not been licensed";
        $messageTitle = "Plug-in Not Installed";
        $log->debug("Plugin field is empty: " .$pluginName ." username: " . $userName);
        processError($errorMessage, "N/A", "utility.php", "N/A", $messageTitle);
    }

    if(is_array($installedPlugins)){
        if(!in_array($pluginName, $installedPlugins)) {
            destroySession();
            $errorMessage = "The " .$pluginName ." plug-in has not been licensed";
            $messageTitle = "Plug-in Not Installed";
            $log->debug("Test in array - User does not have: " .$pluginName ." access now redirect to error username: "
                . $userName);
            processError($errorMessage, "N/A", "utility.php", "N/A", $messageTitle);
        }
    }else{
        if ($installedPlugins != $pluginName) {
            destroySession();
            $errorMessage = "The " .$pluginName ." plug-in has not been licensed";
            $messageTitle = "Plug-in Not Installed";
            $log->debug("Test in String - User does not have: " .$pluginName ." access now redirect to error username: "
                . $userName);
            processError($errorMessage, "N/A", "utility.php", "N/A", $messageTitle);
        }
    }
}

/**
 * This purely a test method to track at microseconds of any given operation/function/page load 
 * @return mixed returns an String representation of time
 */
function microTimer(){
    $time = explode(' ', microtime());
    return $time[0] + $time[1];
}

/**
 * A method utility used to troubleshoot performance issues.
 * @return string current server time
 */
function getCurrentTime(){
    $nowDate = getDate();
    $currentTime = $nowDate['hours'] .":" .$nowDate['minutes'] .":" .$nowDate['seconds'];
    return $currentTime;
}

/**
 * Take the page URL and determine which header_top_level php file name to return
 * @param $url string url to page location ($pageURL
 * @return null|string file name of include top level header file
 */
function getHeaderIncludeFileName($url){
    global $log;
    $fileSuf = ".php";
    $subLevel = "header_top_lvl";

    $parsedPath = parse_url($url, PHP_URL_PATH);
    $log->debug("Now working URL path: " .$parsedPath);
    $depth = (count(explode("/", $parsedPath)) - 2);

    $log->debug("Depth calculation: " .$depth);

    //These hard coded titles and error message are just for testing only
    $errorMessage = "Internal System Error";
    $messageTitle = "System Error";

    if($depth == 0 || $depth == 1){
        return $subLevel .$fileSuf;
    }elseif($depth == 2 || $depth == 3){
        return $subLevel .$depth .$fileSuf;
    }else{
        $log->error("Unable to determine the header top file name from parsed URL: " .$parsedPath);
        processError($errorMessage, "N/A", "utility.php", "N/A", $messageTitle);
        exit;
    }
}

/**
 * Recursive Method to search a multi dimensional array returned by LDAP query. Currently the key is memberOf to extract
 * all group names. The method appends each (example CN=Remote Desktop Users,CN=Builtin,DC=thoughtdev,DC=com)
 * array item
 * @param array $arr an array of Strings of groups names
 * @param $key the target array/map key (currently memberOf)
 * @param $searchGroups String or array of Strings to find
 * @return bool return if key and group name found
 */
function multiKeyExists($arr, $key, $searchGroups){
    if(array_key_exists($key, $arr)){
        return true;
    }

    foreach($arr as $element){
        if(is_array($element)){
            $searchString = "";
            if(multiKeyExists($element, $key, $searchGroups)){
                $memOfArray = $element[$key];
                if(is_array($memOfArray)){
                    for($index = 0; $index < $memOfArray['count']; $index++){
                        //added this space in search string results to account exploded array from LDAP return
                        $searchString .= " " .$memOfArray[$index];
                    }

                    $searchRet = inSearchString($searchString, $searchGroups);
                    return $searchRet;
                }else{
                    echo $memOfArray .PHP_EOL;
                }

            }
        }
    }
    return false;
}

/**
 * Method to search group(s) returned from target LDAP field
 * Note: search value is now hardcoded but more than likely that will change and search array will be part of method
 * signature in the future
 * @param $searchLine String to search for targeted word/group name
 * @param $searchTerms A string word or array of Strings used to search group list from LDAP
 * @return bool found return true otherwise false
 */
function inSearchString($searchLine, $searchTerms){

    if((isset($searchTerms) && isset($searchLine)) && (count($searchTerms) > 0) && (count($searchLine) > 0)){
        if(is_array($searchTerms)){
            $searchTermsCount = count($searchTerms);
            $found = 0;

            foreach($searchTerms as $item){
                if(findTarget($searchLine, $item)){
                    $found++;
                }
            }
            if($found == $searchTermsCount) {
                return true;
            }
        }else{
            if(findTarget($searchLine, $searchTerms)){
                return true;
            }
        }
    }
    return false; //default return value if system falls through all tests
}

/**
 * Consolidated String Position method to remove duplicate code from other methods
 * @param $sLine - String line or phrase to search
 * @param $target - String word to find
 * @return bool -- Return was target found
 */
function findTarget($sLine, $target){
    $position = strpos($sLine, $target);

    if($position === false)
        return false;
    else
        return true;

    return false;
}


/**
 * Method determines which onclick method to invoke based on URL. If the url contains spotedit.php the
 * Print button in the navigation bar will display an JavaScript Alert to the user
 * @param $url String HTTP url
 */
function printEnableDisable($url){
    $pageToDisablePrint = "spotedit.php";

    if(strpos($url, $pageToDisablePrint)) {
        echo("<a href=\"#\" onclick=\"disabledPrintBtnMessage()\"><strong>Print</strong></a>");
    }else{
        echo("<a href=\"#\" onclick=\"document.print.submit()\"><strong>Print</strong></a>");
    }
}

/**
 * Method will remove CR(s) and replace with "," to form array formatted string for FileMaker fields with CR(s)
 * imbedded within the field
 * @param $textIn -- Multi text value FileMaker field
 * @return mixed - return comma delaminated string
 */
function stripControlChars($textIn){
    return str_replace("\n", ",", $textIn);
}

/**
 * Method to take in full URL path to a file and return extension of file. This method is used when using a hardcoded
 * URL within a FileMaker layout and NOT the FileMaker container object
 * @param $pathToFile String value of URL with file extension
 * @return mixed extension from pathInfo array
 */
function getFileExtensionFromURL($pathToFile){
    $pathInfo = pathinfo($pathToFile);

    return $pathInfo['extension'];
}

/**
 * From file extension return video type attribute to caller
 * @param $extension String value ofd file extension
 * @return null|string type attribute for HTML5 <source> tag or null if no match is detected
 */
function getVideoSourceType($extension){

    switch($extension){
        case "mp4":
            return "video/mp4";
            break;
        case "webm":
            return "video/webm";
            break;
        case "ogv":
            return "video/ogv";
            break;
        case "mov":
            return "video/mov";
            break;
        default :
            return null;
    }
}

/**
 * Method to process FM 11/12/13 container url(s) as well as FM 14 url styles
 * @param $fileUrl String representation of location in FileMaker where image file is stored
 * @return string String extension of image file (png, jpg, gif, etc...)
 */
function getFileMakerContainerFileExtension($fileUrl){
    $urlPath = parse_url($fileUrl, PHP_URL_PATH);
    return substr($urlPath, strpos($urlPath, ".") + 1);
}

/**
 * Method takes in CR delimited string and returns array. This method is primarily used to build of Meta fields
 * checkboxes however not exclusively.
 * @param $crArray CR delimited string
 * @return exploded array of string input
 */
function explodedCrString($crArray){
    return explode("\n", $crArray);
}

/**
* Method takes in file 'Image' extension and returns header type
* i.e. image type is inserted in to placeholder header('Content-Type: {image/gif}')
* @param $extension string extension of currently gif, png, jpg
* @return null|string returns image/(type)
*/
function getImageHeaderType($extension){

    switch( $extension ) {
        case "gif":
            return "image/gif";
            break;
        case "png":
            return "image/png";
            break;
        case "jpg":
            return "image/jpeg";
            break;
        case "bmp":
            return "image/bmp";
            break;
        case "ico":
            return "image/ico";
            break;
        case "jpeg": //on the off chance we gif this jpeg extension
            return "image/jpeg";
            break;
        case "tif":
            return "image/tiff";
            break;
        default:
            return "application/octet-stream"; //this default should cover most image types not defined
    }
}

?>