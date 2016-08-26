<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/26/2016
 * Time: 10:02 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");
include_once($commonprocessing ."loadLogosWithLdap.php");


$log->debug("Start Login using tdc-app-config file to authenticate LDAP");

if(file_exists($root .$appConfigName)){
    include_once($root .$appConfigName);
}else{
    writeLogosWithLdap();
    include_once($root .$appConfigName);
}

$letMeIn = "Not Authenticated";

$username = 'brettwi';
$password = 'Thoughtdev#2';

//LDAP Definitions to be used in AD login
define("COMPANY_DOMAIN", $companyDomainValue);
define("LDAP_SERVER", $ldapServerValue); //ffeuspladam.ffe.foxeg.com
define("LDAP_PORT", $ldapPortValue);

$log->debug("1. Checkpoint: Domain: " .COMPANY_DOMAIN ." LDAP Server: " .LDAP_SERVER ." Port: " .LDAP_PORT);

//set TCP connection to SSL or open port
if(LDAP_PORT == "636"){
    $ldapPrefix = "ldaps://";
}else{
    $ldapPrefix = "ldap://";
}

//$baseDn = "O=FEG,DC=fox,DC=com";

//Add domain name to user name for the bind process
$ldapRdn = $username ."@" .COMPANY_DOMAIN;

//We need to replace the placeholder for username with the actual username
$ldapFilter = str_replace('$username',$username,$ldapRawFilterValue);
$log->debug("2. Checkpoint filter: " .$ldapFilter);

$ldapConnection = ldap_connect($ldapPrefix .LDAP_SERVER, LDAP_PORT);
$log->debug("3. Checkpoint Connection String -> Server: " .$ldapPrefix .LDAP_SERVER ." Port: " .LDAP_PORT);
$log->error('Connection ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

if($ldapConnection){
    $log->debug("Connection made Now bind with username: " .$ldapRdn ." password: " .$password);
    ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3); //Specifies the LDAP protocol to be used (V2 or V3)
    ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0); //Specifies whether to automatically follow referrals returned by the LDAP server

    $bind = ldap_bind($ldapConnection, $ldapRdn, $password);

    if($bind){
        $log->debug("We have binded to ldap server now Search groups");
        $filter =   $ldapFilter;   //"(&(objectClass=person)(distinguishedName=uid=$username,OU=People,O=FEG,DC=fox,DC=com))";
        $log->debug("Search-Filter: " .$filter);

        $theseFieldOnly = array($ldapfieldsToSearchValue);  //array("sn","name","memberOf");
        $result = ldap_search($ldapConnection, $baseDnValue, $filter, $theseFieldOnly);
        $log->error('LDAP Search ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

        $log->debug("Found Records: " .ldap_count_entries($ldapConnection, $result));

        if(ldap_errno($ldapConnection) == 0){
            $entries = ldap_get_entries($ldapConnection,$result);
            $log->error('LDAP Get Entries ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

            if(isset($entries) && $entries['count'] > 0){
                $groupNames = array($ldapGroupNameValue);
                $log->debug("We have some entries from search count: " .$entries['count'] ." now list each item");

                if(isset($entries[0]['memberof'])){
                    $log->debug("Ok we have the attribute can we get a count: " .count($entries[0]['memberof']));
                    $members = $entries[0]['memberof'];
                    $ldapGroupList = array();
                    foreach($members as $key => $value){
                        array_push($ldapGroupList, $value);
                    }

                    $letMeIn = "Default is No Way";
                    foreach($groupNames as $groupName){
                        if(contains(strtolower($groupName), $ldapGroupList)){
                            $letMeIn = "This person is OK";
                        }
                    }
                }else{
                    $log->debug("We did not find the member of array try again");
                }
            }else{
                $log->debug("No entries found from search");
            }
        }
        ldap_close($ldapConnection);
    }
}




/**
 * A method test the returned memberof list from LDAP server with a specific group names
 * @param $groupName string name to test if exists in member list line
 * @param $fullLdapGroupArray full LDAP memebrof array
 * @return bool true if value found
 */
function contains($groupName, $fullLdapGroupArray){
    global $log;
    foreach($fullLdapGroupArray as $LdapGroupName) {
        $log->debug("Search for " .$groupName ." using " .$LdapGroupName);
        if (strpos($LdapGroupName, $groupName) !== false) {
            return true;
        }
    }
    return false;
}

$log->debug("End of PHP code reading file to authenticate user with LDAP");

?>

<html>
<head>
    <title>Search LDAP tdc-app-config</title>
</head>
<body>
    <h1>Done with LDAP search</h1>
    <p>Result: <?php echo $letMeIn ?></p>
</body>
</html>
