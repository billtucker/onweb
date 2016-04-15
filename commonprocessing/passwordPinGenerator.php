<?php
/**
 * The page takes username and password from the login.php and perform some number of steps:
 * 1. Pass the password then determine if the what was entered was passoword or pin. Take what was in FileMaker field
 *    encrypt value and then store the value
 * 2. Validate that username only returns one record
 * 3. Ensure that password or pin are encrypted
 *
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/25/2015
 * Time: 1:35 PM
 *
 * Refactor Notes:
 *
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");
include_once($utilities .'utility.php');

if(isset($_POST) && $_POST['action'] == "encrypt"){
    $manualUserName = $_POST['id'];
    manualPasswordPinGeneration($manualUserName, $fmOrderDB, $site_prefix);
}

//Post or manual password or pin encryption generation method
function manualPasswordPinGeneration($username, $dbHandle, $site_prefix){
    global $log;
    $userLayout = "[WEB] Login";
    $commit = false;

    $searchHandle = $dbHandle -> newFindCommand($userLayout);
    $searchHandle->addFindCriterion('User_Name_ct',"==" .$username);
    $loginResult = $searchHandle->execute();

    if(FileMaker::isError($loginResult)){
        $error = "Authentication Failure";
        $log->error("FileMaker loginResult - manualPasswordPinGeneration() - Error: " .$loginResult->getMessage() ." username: " .$username);
        header("location: " .$site_prefix ."admin/manualResult.php?message=" .$error ."&id=" .$username);
		exit();
    }

    //More than one user record with the same username value
    if($loginResult->getFoundSetCount() > 1){
        $error = "Contact System Adminstrator";
        $log->error("FoundSetCount - manualPasswordPinGeneration() - Error: " .$loginResult->getMessage() ." username: " .$username);
        header("location: " .$site_prefix ."admin/manualResult.php?message=" .$error ."&id=" .$username);
		exit();
    }

    $loginRecord = $loginResult->getFirstRecord();
    $rawPassword = $loginRecord->getField('User_Password_t');
    $rawPin = $loginRecord->getField('User_Pin_t');

    if(empty($rawPassword) && empty($rawPin)){
        $error = "Missing Required Data";
        $log->error("No Password and Pin - manualPasswordPinGeneration() - Error: " .$loginResult->getMessage() ." username: " .$username);
        header("location: " .$site_prefix ."admin/manualResult.php?message=" .$error ."&id=" .$username);
		exit();
    }

    if(!empty($rawPassword)){
        $userPasswordHash = $loginRecord->getField('User_Password_Hash_t');
        if(empty($userPasswordHash)) {
            $loginRecord->setField('User_Password_Hash_t', encryptPassowrdKey($rawPassword));
            $commit = true;
        }
    }

    if(!empty($rawPin)){
        $userPinHash = $loginRecord->getField('User_Pin_Hash_t');
        if(empty($userPinHash)){
            $loginRecord->setField('User_Pin_Hash_t', encryptPassowrdKey($rawPin));
            $commit = true;
        }
    }

    if($commit){
        $loginRecordResult = $loginRecord->commit();
        if(FileMaker::isError($loginRecordResult)){
            $error = "passwordPinGenrator.php - manualPasswordPinGeneration() - failed to write record to database with message: " .$loginRecordResult->getMessage()
                ." username: " .$username;
            $log->error($error);
            header("location: " .$site_prefix ."admin/manualResult.php?message=" .$error ."&id=" .$username);
			exit();
        }else{
            header("location: " .$site_prefix ."admin/manualResult.php?message=success&id=" .$username);
			exit();
        }
    }
}

/**
 * Generate password and pin hash value for a given username
 * @param $username -- targeted string username
 */
function generatePasswordPin($username, $dbHandle, $site_prefix){
    global $log;

    $log->debug("generatePasswordPin for user: " .$username ." site prefix " .$site_prefix);

    $userLayout = "[WEB] Login";
    $commit = false;

    $searchHandle = $dbHandle -> newFindCommand($userLayout);
    $searchHandle->addFindCriterion('User_Name_ct',"==" .$username);
    $loginResult = $searchHandle->execute();

    $log->debug("Now connect to layout: " .$userLayout ." for user: " .$username);

    if(FileMaker::isError($loginResult)){
        $error = "Authentication Failure";
        $log->error("FileMaker loginResult - generatePasswordPin() - Login Error: " .$loginResult->getMessage() ." Error String: " .$loginResult->getErrorString()  ." username " .$username);
        header("location: " .$site_prefix ."login.php?error=" .$error);
		exit();
    }

    $log->debug("Connected and finished searching for user: " .$username);

    //More than one user record with the same username value
    if($loginResult->getFoundSetCount() > 1){
        $error = "Contact System Adminstrator";
        $log->error("FoundSetCount - generatePasswordPin() - More than 1 username Error: " .$loginResult->getMessage() ." username " .$username);
        header("location: " .$site_prefix ."login.php?error=" .$error);
		exit();
    }

    $loginRecord = $loginResult->getFirstRecord();
    $rawPassword = $loginRecord->getField('User_Password_t');
    $rawPin = $loginRecord->getField('User_Pin_t');

    if(empty($rawPassword) && empty($rawPin)){
        $error = "Missing Required Data";
        $log->error("No Password and Pin - generatePasswordPin() - Encryption Check Error: " .$loginResult->getMessage() ." username " .$username);
        header("location: " .$site_prefix ."login.php?error=" .$error);
		exit();
    }

    if(!empty($rawPassword)){
        $userPasswordHash = $loginRecord->getField('User_Password_Hash_t');
        if(empty($userPasswordHash)) {
            $loginRecord->setField('User_Password_Hash_t', encryptPassowrdKey($rawPassword));
            $commit = true;
        }
    }

    if(!empty($rawPin)){
        $userPinHash = $loginRecord->getField('User_Pin_Hash_t');
        if(empty($userPinHash)){
            $loginRecord->setField('User_Pin_Hash_t', encryptPassowrdKey($rawPin));
            $commit = true;
        }
    }

    if($commit){
        $loginRecordResult = $loginRecord->commit();
        if(FileMaker::isError($loginRecordResult)){
            $error = "loginRecordResult - generatePasswordPin() - failed to write record to database with message: " .$loginRecordResult->getMessage() ." username " .$username;
            $log->error($error);
            die($error);
        }
    }
}