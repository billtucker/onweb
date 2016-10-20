<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/12/2016
 * Time: 1:27 PM
 */


//Since I am not using the configuration page I need to setup logging
//set Logger class, setup Log4php configuration, and get Logger that is/can be used on any PHP page
//current configuration "config.xml" is setup for 1 MB max size log file that rolls file name onweb.log
include_once('../vendor/apache/log4php/src/main/php/Logger.php');
Logger::configure("../config.xml");
$log = Logger::getLogger("ONAIRPRO_Logger");

$log->debug("Start simple connection to ADAM the LDAP connection at Fox");


//LDAP Definitions to be used in AD login
//define("COMPANY_DOMAIN", "fox.com");
define("COMPANY_DOMAIN", "gsn.com");
//define("LDAP_SERVER", "ffeuscnadam.ffe.foxeg.com"); //ffeuspladam.ffe.foxeg.com
define("LDAP_SERVER", "SM-DC-01.gsn.com");
define("LDAP_PORT", 389);

$letMeIn = "Default is No Way";

//$onAirProGroups = array("FBC-ONAIRPRO","FOXSPORTS-ONAIRPRO");
//$onAirProGroups = array("GSN-OnAirPro");
$onAirProGroups = array("GSN-CAGFullAccess");

//set TCP connection to SSL or open port
if(LDAP_PORT == "636"){
    $ldapPrefix = "ldaps://";
}else{
    $ldapPrefix = "ldap://";
}

//$username = "brettwi";
//$password = "Thoughtdev#2";
$username = "thoughtdev";
$password = "Td3v@a9Q1";

//$baseDn = "O=FEG,DC=fox,DC=com";
$baseDn = "DC=gsn,DC=com";

//Add domain name to user name for the bind process
$ldapRdn = $username ."@" .COMPANY_DOMAIN;


$ldapConnection = ldap_connect($ldapPrefix .LDAP_SERVER, LDAP_PORT);
$log->debug("Connection results: " .$ldapConnection);
$log->debug("Connection String -> Server: " .$ldapPrefix .LDAP_SERVER ." Port: " .LDAP_PORT);
$log->error('Connection ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

if($ldapConnection){
    $log->debug("Connection made Now bind with username: " .$ldapRdn ." password: " .$password);
    ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3); //Specifies the LDAP protocol to be used (V2 or V3)
    ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0); //Specifies whether to automatically follow referrals returned by the LDAP server

    $bind = ldap_bind($ldapConnection, $ldapRdn, $password);

    if($bind){
        $log->debug("We have binded to ldap server now Search groups");
        //$filter = "(&(objectCategory=People)(uid=$username))";
        //$filter = "uid=$username,OU=People,O=FEG,DC=fox,DC=com";
        //$filter = "(&(objectClass=person)(distinguishedName=uid=$username,OU=People,O=FEG,DC=fox,DC=com))"; //Fox Search String
        $filter = "(&(objectClass=user)(CN=$username*))"; //GSN Search String
        $log->debug("Filter Line: " .$filter);

        $theseFieldOnly = array("sn","name","memberOf"); //Let drop this down to just memberOf only
        $result = ldap_search($ldapConnection, $baseDn, $filter, $theseFieldOnly);
        $log->error('LDAP Search ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

        $log->debug("Found this many records: " .ldap_count_entries($ldapConnection, $result));

        if(ldap_errno($ldapConnection) == 0){
            $entries = ldap_get_entries($ldapConnection,$result);
            $log->error('LDAP Get Entries ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

            if(isset($entries) && $entries['count'] > 0){
                $log->debug("We have some entries from search count: " .$entries['count'] ." now list each item");
                for($index = 0;$index < $entries['count']; $index++){
                    $log->debug("SN: " .$entries[$index]['sn'][0] . " Name: " .$entries[$index]['name'][0] ." Member Of:" .$entries[$index]['memberof'][0]);
                }

                if(isset($entries[0]['memberof'])){
                    $log->debug("Ok we have the attribute can we get a count: " .count($entries[0]['memberof']));
                    $members = $entries[0]['memberof'];
                    $ldapGroupList = array();
                    foreach($members as $key => $value){
                        $log->debug("Pusing group name on array: " .$value);
                        array_push($ldapGroupList, $value);
                    }


                    foreach($onAirProGroups as $groupName){
                        if(contains(strtolower($groupName), $ldapGroupList)){
                            $log->debug("We found our group name " .$groupName);
                            $letMeIn = "This person is OK";
                        }
                    }
                }else{
                    $log->debug("We did not find the member of array try again");
                }
            }else{
                $log->debug("No entries found from search");
            }
        }else{
            $log-error("Error found so read LDAP Search line");
        }



    }else{
        $log->error("We failed to bind");
        $log->error('Bind ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));
    }

    $log->debug("Close the connection to clean up");
    ldap_close($ldapConnection);
}else{
    $log->error('Connection to server failed ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));
}

/**
 * A method test the returned memberof list from LDAP server with a specific group names
 * @param $groupName string name to test if exists in member list line
 * @param $fullGroupArray full LDAP memebrof array
 * @return bool true if value found
 */
function contains($groupName, $fullGroupArray){
    foreach($fullGroupArray as $groupLines) {
        if (strpos(strtolower($groupLines), $groupName) !== false) {
            return true;
        }
    }
    return false;
}


?>
<html>
<head>
    <title>Simple LDAP Connection</title>
</head>
<body>
<h3>Connection Test is Completed</h3>
<p>The Let Me In Says <?php echo $letMeIn ?></p>
</body>
</html>
