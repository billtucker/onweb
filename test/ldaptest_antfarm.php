<?php
/**
 * Created by IntelliJ IDEA.
 * User: billt
 * Date: 1/6/2017
 * Time: 9:59 AM
 *
 * This file is strictly for Ant Farm LDAP connection and validation
 *
 */

//Since I am not using the configuration page I need to setup logging
//set Logger class, setup Log4php configuration, and get Logger that is/can be used on any PHP page
//current configuration "config.xml" is setup for 1 MB max size log file that rolls file name onweb.log
include_once('../vendor/apache/log4php/src/main/php/Logger.php');
Logger::configure("../config.xml");
$log = Logger::getLogger("ONAIRPRO_Logger");

$log->debug("Start simple connection to ADAM the LDAP connection at Ant Farm");

$username = "btucker";
$password = "zfbkEY5B&p";

//**** Note: This file is currently configured to connect to GSN LDAP ********

//LDAP Definitions to be used in AD login
define("COMPANY_DOMAIN", "theantfarm.net");
define("LDAP_SERVER", "Taf-dc-01.theantfarm.net");
define("LDAP_PORT", 389);

$letMeIn = "Default is No Way";

//$onAirProGroups = array("FBC-ONAIRPRO","FOXSPORTS-ONAIRPRO");
//$onAirProGroups = array("GSN-OnAirPro");
$onAirProGroups = array("TDC_OnAirPro");

$baseDN = "dc=theantfarm,dc=net";
//Add domain name to user name for the bind process
$ldapRdn = $username ."@" .COMPANY_DOMAIN;

//All the server are inside the firewall so for now we are not using SSL to connect to LDAP
$ldapPrefix = "ldap://";

//Now test connection to LDAP server
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
        $filter = "(&(objectClass=user)(CN=$username*))"; //The Ant Farm Search String
        $log->debug("Filter Line: " .$filter);

        $theseFieldOnly = array("memberOf"); //memberOf only search
        $result = ldap_search($ldapConnection, $baseDn, $filter, $theseFieldOnly);
        $log->error('LDAP Search ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

        //Did we find anything outside of errors
        $log->debug("Found this many records: " .ldap_count_entries($ldapConnection, $result));

        if(ldap_errno($ldapConnection) == 0){ //test for errors after search
            $entries = ldap_get_entries($ldapConnection,$result); //How many record were returned Then test for errors
            $log->error('LDAP Get Entries ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

            if(isset($entries) && $entries['count'] > 0){
                $log->debug("We have some entries from search count: " .$entries['count'] ." now list each item");
                for($index = 0;$index < $entries['count']; $index++){
                    $log->debug("MemberOf: " .$entries[$index]['memberof'][0]);
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
                            $log->debug("We found our group name match " .$groupName);
                            $letMeIn = "This person is OK";
                        }
                    }
                }else{
                    $log->debug("We did not find the member of array try again");
                }
            }else{
                $log->debug("Entries array is empty try again");
            }
        }else{
            $log->debug("Close the connection because the search failed with errors");
            ldap_close($ldapConnection); //close the connection because search errors
        }
    }else{ //bind failed
        ldap_close($ldapConnection); //close the LDAP connection before exit
    }

}else{
    if($ldapConnection){
        ldap_close($ldapConnection);
    }
    $log->error("Could not connect to LDAP server! Review log files for error messages");
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

