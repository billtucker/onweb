<?php
/**
 * Take the username and password from login.php then call generatePasswordPin() method to encrypt password or pin
 * then compare the entered credentials with FileMaker database. If valid forward the user to the page otherwise send
 * the user to login page to try again
 *
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/23/2015
 * Time: 1:18 PM
 *
 * Refactor Notes:
 *
 * 1. 08/13/2015 Moved plugin validation below authentication check to stop (view only) users from plugin error message
 * 2. 08/14/2015 Added if() method around tdc-app-conf.php creation and save operation of small company logo image only
 *    if the configuration is not present in the filesystem
 * 3. 10/26/2015 Added LDAP authentication to the login process. This process will include validation of groups within
 *    the memberOf attribute of the Active Directory/LDAP
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");
include_once($utilities .'utility.php');
include_once($commonprocessing ."passwordPinGenerator.php");
include_once($commonprocessing ."loadLogos.php");
include_once($errors ."errorProcessing.php");

if($_POST['action'] == 'login'){

    if($usesLdap){
        //This login processing requires a further definition as currently developed. The question is how to interact with
        //LDAP then what is validated with FileMaker. This new method is a step 1 to integrate with AD/LDAP
        authenticateLdap($_POST, $site_prefix, $fmOrderDB);
        authenticateLdapUserFM($fmOrderDB, $_POST['username'], $site_prefix);
    }else{
        $log->debug("LDAP not turned on use FM only");
        generatePasswordPin($_POST['username'], $fmOrderDB, $site_prefix);
        authenticateFMOnly($fmOrderDB, $_POST, $site_prefix);
    }

    $log->debug("We fell through user validation without authentication");
}

/**
 * New method to validate user login with LDAP/AD will be step one validation. Step two is to bounce the username and
 * password with FileMaker however it is not clear how the flow is defined 10/27/2015
 * @param $post - $_POST array
 * @param $site_prefix String site homepage prefix from site configuration file
 */
function authenticateLdap($post, $site_prefix, $dbHandle){
    global $log, $memberOfList, $ldapKeySearch, $dn;

    $username = $post['username'];
    $password = $post['password'];

    $log->debug("Now process login with TDC LDAP server with username: " .$username . " password: " .$password);

    //Add domain name to user name for the bind process
    $ldapRdn = $username ."@" .COMPANY_DOMAIN;

    //Port number is optional BUT this could be important if end user has different port number for
    // their LDAP/Active Directory.
    //TODO Explore SSL LDAP connection
    $ldapConn = ldap_connect("ldap://" .LDAP_SERVER ."/", LDAP_PORT) or die("Could not connect to: " .LDAP_SERVER);

    if($ldapConn){ //connection to LDAP server was successful
        ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3); //Specifies the LDAP protocol to be used (V2 or V3)
        ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0); //Specifies whether to automatically follow referrals returned by the LDAP server

        $log->debug("Bind using -> Username: " .$username ." Password: " .$password . " LDAP RDN: " .$ldapRdn);

        $bind = @ldap_bind($ldapConn, $ldapRdn, $password);

        if($bind){ //user successfully logged into LDAP/AD (Bind) server

            //Now setup LDAP search fields and return fields
            $filter = "(&(objectClass=user)(sAMAccountName=$username))";
            $theseFieldOnly = array("cn", "sAMAccountName", "memberOf");
            $result = ldap_search($ldapConn, $dn, $filter, $theseFieldOnly);
            $info = ldap_get_entries($ldapConn, $result);


            //validate that user belongs to (memberOf) OnAir-Pro groups
            if(multiKeyExists($info, $ldapKeySearch, $memberOfList)){
                ldap_unbind($ldapConn); //for disconnect from LDAP once done
                $log->debug("User Logged in via LDAP and groups were validated. Now return and call FM to setup session data");
                return;
            }else{
                //Could not validate user belongs to group memberOf field of LDAP
                ldap_unbind($ldapConn); //for disconnect from LDAP once done
                $log->debug("Group membership validation failed. So call FileMaker to validate if user belongs to site");
                return;
                //header("location: " .$site_prefix ."index.php?error=" .$error);
                //exit;
            }
        }else{ //unsuccessful login to LDAP AD (note: authenticate with FileMaker since LDAP failed)
            $log->debug("LDAP-Bind failed use full FM method for login process");
            $log->error("authenticateLdap - Login Error: " .ldap_error($ldapConn) ." username: " .$username);
            ldap_unbind($ldapConn);
            authenticateFMOnly($dbHandle, $post, $site_prefix);
        }

    }else{
        //This is determined to be a serious error and so the user should be redirected to the site Error Page
        $errorMessage = "authenticateLdap - LDAP server is down or application was unable to connect";
        $log->error($errorMessage ." Error: " .ldap_error($ldapConn));
        ldap_unbind($ldapConn);
        $log->debug("LDAP/AD connection error switch to FM login process");
        authenticateFMOnly($dbHandle, $post, $site_prefix);
    }
}

/**
 * This method is to validate username after LDAP/AD username/password authentication. This method will gather
 * user record information located in FileMaker to be used in setting the session information
 * Note: There is a a separate method to process a pure FileMaker authentication of username and password
 * @param $dbHandle -- FileMaker DB connection handle
 * @param $userName -- String value representing the username to be found in FM
 * @param $site_prefix -- Site prefix to include port type SSL or 80
 */
function authenticateLdapUserFM($dbHandle, $userName, $site_prefix){
    global $log;
    $loginLayout = '[WEB] Login';

    $log->debug("Start authentication of FileMaker after LDAP and get User Record");

    $searchHandle = $dbHandle->newFindCommand($loginLayout);
    $searchHandle->addFindCriterion('User_Name_ct', "==" . $userName);
    $loginResult = $searchHandle->execute();

    if (FileMaker::isError($loginResult)) {
        $error = "Authentication Failure";
        $log->error("authenticateUserFM - Login Error: " . $loginResult->getMessage() . " username " . $userName);
        header("location: " . $site_prefix . "login.php?error=" .$error);
        exit();
    }


    //More than one user record with the same username value is a serious error
    //TODO: redirect this error to the access error page as this is not something the user can correct on their own
    if ($loginResult->getFoundSetCount() > 1) {
        $error = "Contact System Administrator";
        $log->error("authenticateUserFM - More than 1 username Error: " . $error . " username: " . $userName);
        header("location: " . $site_prefix . "login.php?error=" .$error);
        exit();
    }

    $userRecord = $loginResult->getFirstRecord();
    $log->debug("Have user record now set Session array");
    setSessionData($userRecord, $site_prefix);
}

/**
 * Pure FileMaker login function to authenticate user with login form and FileMaker
 * @param $dbHandle -- FileMaker DB connection handle
 * @param $post -- $_POST[] array
 * @param $site_prefix -- Site prefix to include port type SSL or 80
 */
function authenticateFMOnly($dbHandle, $post, $site_prefix){
    global $log;
    $loginLayout = '[WEB] Login';

    $log->debug("Now entering FileMaker Authentication processing");

    $ePassword = encryptPassowrdKey($post['password']);

    $searchHandle = $dbHandle->newFindCommand($loginLayout);
    $searchHandle->addFindCriterion('User_Name_ct', "==" . $post['username']);
    $loginResult = $searchHandle->execute();

    if (FileMaker::isError($loginResult)) {
        $error = "Authentication Failure";
        $log->error("authenticateFMOnly - Login Error: " . $loginResult->getMessage() . " username " . $post['username']);
        header("location: " . $site_prefix . "login.php?error=" .$error);
        exit();
    }


    //More than one user record with the same username value is a serious error
    //TODO: redirect this error to the access error page as this is not something the user can correct on their own
    if ($loginResult->getFoundSetCount() > 1) {
        $error = "Contact System Adminstrator";
        $log->error("authenticateFMOnly - More than 1 username Error: " . $error . " username: " . $post['username']);
        header("location: " . $site_prefix . "login.php?error=" .$error);
        exit();
    }

    $userRecord = $loginResult->getFirstRecord();

    $log->debug("Now have user record check if Pin or password matches");

    if(isPasswordPinMatch($userRecord->getField('User_Password_Hash_t'), $ePassword) ||
        isPasswordPinMatch($userRecord->getField('User_Pin_Hash_t'), $ePassword)){
        $log->debug("Now call session creation for FM full authentication");
            setSessionData($userRecord, $site_prefix);
    }else{
        $error = "Authentication Failure";
        $log->info("loginProcessing.php - processLogin() - Last Check Authentication Error: " .$error ." username: " .$post['username']);
        header("location: " .$site_prefix ."login.php?error=" .$error);
        exit();
    }
}

/**
 * A single method to setup Session information regardless which authentication method was used.
 * @param $userRecord FileMaker user record handle
 * @param $site_prefix String site URL address
 */
function setSessionData($userRecord, $site_prefix){
    global $log, $onWebPlugin, $companyLogoSmallPropertyName, $imageDir, $appConfigName, $imageSmallFileName,
           $imageSplashFileName,$companyLogoSplashPropertyName, $root;

        if(!session_id()){
            session_start();
        }

        $log->debug("setSessionData - Session was started now populate session Array");

        $_SESSION['authenticated'] = true;
        $_SESSION['firstName'] = $userRecord->getField('User_FirstName_ct');
        $_SESSION['lastName'] = $userRecord->getField('User_LastName_ct');

        $accessLevel = $userRecord->getField('User_Privs_t');
        $log->debug("User Privi Set: " .$accessLevel);
        $_SESSION['accessLevel'] = convertPipeToArray($accessLevel);

        $_SESSION['userName'] = $userRecord->getField('User_Name_ct');
        $_SESSION['LAST_ACTIVITY'] = time();

        $pipeInstalledPlugins = $userRecord->getField('z_SYS_LicensedPlugins_ct');
        //Force the Array items to uppercase just in case the character case was mixed at entry
        $_SESSION['installedPlugins'] = array_map("strtoupper", convertPipeToArray($pipeInstalledPlugins));

        //New values to capture from [WEB] Login view to be used when determining which users can view Spots
        // based on account name and we also need to capture PK for the contact ID
        $_SESSION['contact_pk'] = $userRecord->getField('User_Contact__pk_ID_ct');

        //System preference apply if 1 or else value is null
        $_SESSION['system_preference'] = $userRecord->getField('z_PRO_SeparateWorkByPrograming_cn');

        //Accounts associated with user in cr delaminated field
        $_SESSION['user_accounts'] = array(stripControlChars($userRecord->getField('User_Contact_Programming_Type_Associations_ct')));

        //Now test for ON-WEB from the PLUGIN array to validate that the user has the License authority to access web
        validatePlugin($_SESSION['userName'], $_SESSION['installedPlugins'], $onWebPlugin);

        //Now the login ands plugin validation is processed now write the tdc-app-config.php and set small logo location
        //Only perform this operation if the tdc-app-conf.php is not present in the directory. If the file exists then
        //it is ass-u-me(d) that the logo name and location 'was' resolved and is available to the presentation layer
        //This is a run once method as once the file is written is should never run unless the file is deleted

        //TODO remove these comments lines after the authentication flow is resolved
        if(!file_exists($root .$appConfigName)){
            writeFilesDynamically($userRecord, $imageDir, $imageSmallFileName,$companyLogoSmallPropertyName,
                $imageSplashFileName,$companyLogoSplashPropertyName, $appConfigName);
        }

        if(!empty($_SESSION['forwardingUrl'])){
            $log->debug("setSessionData - User logged in and is being forwarded to: " .$_SESSION['forwardingUrl']);
            //added this fix to forward user to page they expected to see prior to login. Assigned session item to var
            //then unset session item then forward user
            $forwardingUrl = $_SESSION['forwardingUrl'];
            unset($_SESSION['forwardingUrl']);
            header("location:" .$forwardingUrl);
            exit;
        }else{
            $log->debug("setSessionData - No previous forwarding is defined so go to index page");
            header("location: " .$site_prefix ."index.php");
            exit;
        }
}