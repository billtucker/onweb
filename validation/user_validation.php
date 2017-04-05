<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/23/2015
 * Time: 3:17 PM
 *
 * Validate the user is logged in to the system and verifies session timeout has not been exceeded. If the user was
 * attempting to access a particular page it will be stored in the session so that the user is redirected to the page
 * following successful login. Page also performs preliminary privilege check on page access specifically to
 * On-Spot Viewer.
 *
 * Refactor Notes:
 *
 * 07/30/2014 Modified error messages to login.php page
 * 08/13/2015 Moved validation of installed plugin below authentication validation
 * 10/30/2015 Configured file to process generic Spot viewer or Request user validations
 *
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($utilities ."utility.php");

include_once($errors ."errorProcessing.php");


//we need to run this for each page to validate that the user is logged in and authenticated with an Access Level above
//none or null. If the user is not logged in then redirect user to login page. If user has no access level then send
//user to a page to let them know they have no access to the site.
//TODO check what site_prefix means now that the site contains three home pages main/ - main/onspot - main/onrequest
function validateUser($site_prefix, $fullUrl, $siteSection, $viewCheck, $pluginToValidate){
    global $log;

    //currently set at 2 hour time out and is only checked per page load
    $sessionTimeoutMax = 7200;

    $log->debug("validateUser() - method called for section: " .$siteSection);

    if(!session_id()){
        session_start();
    }


    //Added this method to detect session timeout of no more than hours now if set
    if(isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $sessionTimeoutMax)){
        $log->debug("Session timed out Username: " .$_SESSION['userName']);
        $errorMsg = "You have been logged out due to inactivity. Please login.";
        session_unset();
        session_destroy();

        if(!session_id()){
            session_start();
        }

        $_SESSION['forwardingUrl'] = urldecode($fullUrl);
        header("location: " .$site_prefix ."login.php?error=" .$errorMsg);
		exit;
    }


    if(!isset($_SESSION['authenticated'])){
        $log->debug("user is not authenticated for page: " .urldecode($fullUrl));
        $indexPage = "index.php";
        $phpSuffix = "php";
        if(!strpos(urldecode($fullUrl),$phpSuffix) || strpos(urldecode($fullUrl), $indexPage)){
            header("location: " .$site_prefix ."login.php");
            exit;
        }else{
            $_SESSION['forwardingUrl'] = urldecode($fullUrl);
            $errorMsg = "User must be logged in to access the site";
            header("location: " .$site_prefix ."login.php?error=" .$errorMsg);
            exit;
        }
    }

    //Test if user has licensed ON-SPOT plugin on user record. If not redirect the user to error page
    //Note this validation was moved below authentication check
    validatePlugin($_SESSION['userName'], $_SESSION['installedPlugins'], $pluginToValidate);


    if(empty($_SESSION['accessLevel'])){
        $log->debug("validateUser() - user access level is set to null/empty send that user to error page");
        $errorMessage = "You do not have the necessary access rights in " .strtoupper($siteSection);
        $messageTitle = "Access Denied";
        processError($errorMessage, "N/A", "user_validate.php", "N/A", $messageTitle);
    }else{
        if($siteSection == "View"){
			$log->debug("Validate user can View or edit spot viewer");
            //this test is specific to OnSpot/OnSpotView for viewing the page
            //TODO we need to figure out the privs for Request side of the site. For now we skip this as can edit method controls this
            if(!canViewOrEdit($_SESSION['accessLevel'], $siteSection, $viewCheck)){
                $log->debug("User does not have access privilege to the site section: " .$siteSection ." Username: " .$_SESSION['userName']);
                $errorMessage = "You do not have the necessary access rights in " .strtoupper($siteSection);
                $messageTitle = "Access Denied";
                processError($errorMessage, "N/A", "user_validate.php", "N/A", $messageTitle);
            }
        }
    }

    //Update session timer for each page visited. Once the session is dormant for 2 hours the test for session
    //session timeout is caught by timeout test ahead of this reset method
    resetSessionTimeout();
    $log->debug("User is fully validated so redirect to page URL: " .urldecode($fullUrl));
}

/**
 * Method will validate if user access purchased plugin for web access
 * @param $access  initially ON-SPOT or/and ON-REQUEST test if plugin is authorized
 * @return bool if access is found in session array then return true other wise return false
 */
function userAccess($access){
    global $log;

    if(!session_id()){
        session_start();
    }

    if(!empty($_SESSION['installedPlugins'])){
        if(is_array($_SESSION['installedPlugins'])){
            if(in_array($access, $_SESSION['installedPlugins'])){
                $log->debug("User has an Access Level: " .$access);
                return true;
            }
        }
    }

    $log->debug("User does not have Access Level: " .$access);
    return false;
}

//This method will currently process queries from ON-Request pages if user is allowed to edit Request
//TODO this method should be replaced by the SpotViewer methods that determine if a user can access or edit sections
function userCanModifyRequest($phraseToTest){
    global $log;

    if(!session_id()){
        session_start();
    }

    if(!empty($_SESSION['accessLevel'])){
        if(is_array($_SESSION['accessLevel'])){
            if(in_array($phraseToTest, $_SESSION['accessLevel'])){
                $log->debug("True: userCanModifyRequest() method");
                return true;
            }else{
                if($_SESSION['accessLevel'] == "$phraseToTest"){
                    return true;
                }
            }
        }
    }
	
	$log->debug("Failed to find Request Modify for user: " .$_SESSION['userName']);
    return false;
}

/**
 * Method to preform simple validation if user is logged in to site and redirect if user is logged in to site
 * @param $site_prefix top URL prefix
 * @param $fullUrl URL of calling page
 */
function printValidation($site_prefix, $fullUrl){
    global $log, $site_prefix;

	$log->debug("Validation of print operation using Site Prefix: " .$site_prefix  ." With URL: " .$fullUrl);
	
    //currently set at 2 hour time out and is only checked per page load
    $sessionTimeoutMax = 7200;

    $log->debug("validateUser() - method called for during Print Operation");

    if(!session_id()){
        session_start();
    }


    //Added this method to detect session timeout of no more than hours now if set
    if(isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $sessionTimeoutMax)){
        $log->debug("Session timed out Username: " .$_SESSION['userName']);
        $errorMsg = "You have been logged out due to inactivity. Please login.";
        session_unset();
        session_destroy();

        if(!session_id()){
            session_start();
        }

        $_SESSION['forwardingUrl'] = urldecode($fullUrl);
        header("location: " .$site_prefix ."login.php?error=" .$errorMsg);
        exit;
    }


    if(!isset($_SESSION['authenticated'])){
        $log->debug("user is not authenticated for page: " .urldecode($fullUrl));
        $indexPage = "index.php";
        $phpSuffix = "php";
        if(!strpos(urldecode($fullUrl),$phpSuffix) || strpos(urldecode($fullUrl), $indexPage)){
            header("location: " .$site_prefix ."login.php");
            exit;
        }else{
            $_SESSION['forwardingUrl'] = urldecode($fullUrl);
            $errorMsg = "User must be logged in to access the site";
            header("location: " .$site_prefix ."login.php?error=" .$errorMsg);
            exit;
        }
    }
}
