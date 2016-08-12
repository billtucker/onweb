<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/4/2016
 * Time: 12:54 PM
 */


//Since I am not using the configuration page I need to setup logging
//set Logger class, setup Log4php configuration, and get Logger that is/can be used on any PHP page
//current configuration "config.xml" is setup for 1 MB max size log file that rolls file name onweb.log
include_once('../vendor/apache/log4php/src/main/php/Logger.php');
Logger::configure("../config.xml");
$log = Logger::getLogger("ONAIRPRO_Logger");

$log->debug("Start testing the LDAP connection at Fox");

//LDAP Definitions to be used in AD login
define("COMPANY_DOMAIN", "ffe.foxeg.com");
define("LDAP_SERVER", "ffeuspladam.ffe.foxeg.com"); //ffeuscnadam.ffe.foxeg.com
define("LDAP_PORT", 636);
//These configuration items are custom per site
$memberOfList = array(" FBC-ONAIRPRO","FOXSPORTS-ONAIRPRO");
$ldapKeySearch = "memberof";

//$useLdap switch is off when set to false and forces all login validation to FileMaker only
//$usesLdap = false;
//This DN is for Thought Development only!!!!
//this wil either ffe or ffe.foxeg
//$dn = "CN=Users,DC=ffe,DC=com";

//Fox Base DN
$dn = "O=FEG,DC=Fox,DC=com";

$tdcServiceAccount = "SVC_FNG_OAP";
$username = "brettwi";
$password = "Thoughtdev#2";

$log->debug("Now process login with TDC LDAP server with username: " .$username . " password: " .$password);

//Add domain name to user name for the bind process
//$ldapRdn = $username ."@" .COMPANY_DOMAIN;
$ldapRdn = COMPANY_DOMAIN .'\\' .$username;

//Port number is optional BUT this could be important if end user has different port number for
// their LDAP/Active Directory.
//TODO Explore SSL LDAP connection
$ldapConn = ldap_connect("ldap://" .LDAP_SERVER ."/", LDAP_PORT) or die("Failed connect to: " .LDAP_SERVER);

if($ldapConn){
    $log->debug("Got LDAP conn to ldap://" .LDAP_SERVER ."/", LDAP_PORT);

    ldap_set_option($ldapConn, LDAP_OPT_PROTOCOL_VERSION, 3); //Specifies the LDAP protocol to be used (V2 or V3)
    ldap_set_option($ldapConn, LDAP_OPT_REFERRALS, 0); //Specifies whether to automatically follow referrals returned by the LDAP server

    $log->debug("Bind using -> Username: " .$username ." Password: " .$password . " LDAP RDN: " .$ldapRdn);

    $bind = @ldap_bind($ldapConn, $ldapRdn, $password);

    $log->error('ldap-errno: '.ldap_errno($ldap) .' ldap-error: '.ldap_error($ldap));

    if($bind){
        $log->debug("Bind happened so run search operation for groups");

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
    }else { //unsuccessful login to LDAP AD (note: authenticate with FileMaker since LDAP failed)
        $log->debug("LDAP-Bind failed use full FM method for login process");
        $log->error("authenticateLdap - Login Error: " . ldap_error($ldapConn) . " username: " . $username);
        ldap_unbind($ldapConn);
    }
}


/** A copy of the method from utility.php of onweb */
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

?>
<html>
<head>
    <title>Test Connect Fox LDAP</title>
</head>
<body>
    <br>
    <h4>Maybe if we see then we did connect</h4>
</body>
</html>
