<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/19/2016
 * Time: 11:06 AM
 */


include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");

$log->debug(" ");
$log->debug("********* Start Marker Testing LDAP Connection With FM ***********");
$letMeIn = "";

$username = "brettwi";
$password = "Thoughtdev#2";

$loginLayout = "[WEB] Login";

$searchHandle = $fmOrderDB->newFindCommand($loginLayout);
$searchHandle->addFindCriterion("User_Name_ct","==" .$username);
$searchResults = $searchHandle->execute();

if(FileMaker::isError($searchResults)){
    $log->error("Failed on search: " .$searchResults->getMessage());
    exit();
}

$userRecord = $searchResults->getFirstRecord();

$baseDn = $userRecord->getField("ONWEB_LDAP_base_dn_ct");
$companyDomain = $userRecord->getField('ONWEB_LDAP_company_domain_ct');
$ldapServer = $userRecord->getField('ONWEB_LDAP_server_ct');
$ldapPort = $userRecord->getField('ONWEB_LDAP_port_ct');
$groupNames = array($userRecord->getField('ONWEB_LDAP_group_name_ct'));
$fieldsToSearch = array($userRecord->getField('ONWEB_LDAP_fieldsToSearch_ct'));
$ldapRawFilter = html_entity_decode($userRecord->getField('ONWEB_LDAP_filter_ct'));

//This was added to work around a single quoted string PHP cannot use variable names inside a single quoted string
$ldapFilter = str_replace('$username',$username,$ldapRawFilter);


//LDAP Definitions to be used in AD login
define("COMPANYDOMAIN", $companyDomain);
define("LDAPSERVER", $ldapServer); //ffeuspladam.ffe.foxeg.com
define("LDAPPORT", $ldapPort);

$onAirProGroups = $groupNames;

//set TCP connection to SSL or open port
if(LDAPPORT == "636"){
    $ldapPrefix = "ldaps://";
}else{
    $ldapPrefix = "ldap://";
}

//$baseDn = "O=FEG,DC=fox,DC=com";

//Add domain name to user name for the bind process
$ldapRdn = $username ."@" .COMPANYDOMAIN;


$ldapConnection = ldap_connect($ldapPrefix .LDAPSERVER, LDAPPORT);
$log->debug("Connection results: " .$ldapConnection);
$log->debug("Connection String -> Server: " .$ldapPrefix .LDAPSERVER ." Port: " .LDAPPORT);
$log->error('Connection ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

if($ldapConnection){
    $log->debug("Connection made Now bind with username: " .$ldapRdn ." password: " .$password);
    ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3); //Specifies the LDAP protocol to be used (V2 or V3)
    ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0); //Specifies whether to automatically follow referrals returned by the LDAP server

    $bind = ldap_bind($ldapConnection, $ldapRdn, $password);

    if($bind){
        $log->debug("We have binded to ldap server now Search groups");
        $filter =   $ldapFilter;   //"(&(objectClass=person)(distinguishedName=uid=$username,OU=People,O=FEG,DC=fox,DC=com))";
        $log->debug("Filter Line: " .$filter);

        $theseFieldOnly = $fieldsToSearch;  //array("sn","name","memberOf");
        $result = ldap_search($ldapConnection, $baseDn, $filter, $theseFieldOnly);
        $log->error('LDAP Search ldap-errno: '.ldap_errno($ldapConnection) .' ldap-error: '.ldap_error($ldapConnection));

        $log->debug("Found Records: " .ldap_count_entries($ldapConnection, $result));

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
    global $log;
    foreach($fullGroupArray as $groupLines) {
        $log->debug("Search for " .$groupName ." using " .$groupLines);
        if (strpos($groupLines, $groupName) !== false) {
            return true;
        }
    }
    return false;
}

$log->debug("********* End Marker Testing LDAP Connection With FM ***********");
$log->debug(" ");

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