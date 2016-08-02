<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 8/02/2016
 * Time: 11:02 AM
 * Code to communicate with the RESTfm service to connect to FM and query if any meta records are incomplete
 * (answers field is empty or null) using Value_Required_Failed_cn field of [WEB] Project Meta Fields layout
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";

//init this JSON array container so it can be used almost anywhere
$dataErrorRecordMaster = array();

if(isset($_POST['pk'])){
    $requestPk = $_POST['pk'];
}else{
    $log->error("No PK Sent from calling page");
    $dataErrorRecordMaster['error'] = true;
    $dataErrorRecordMaster['message'] = "No PK Sent from calling page";
    echo json_encode($dataErrorRecordMaster);
    exit();
}

//Now build an HTTP client using cUrl commands
$requestPkFieldName = "_fk_Request_pk_ID";
$encodeQuery = $requestPkFieldName ."=" .$requestPk;
$errorField = "Value_Required_Fail_cn";

$encodeLayoutName = urlencode("[WEB] Project Meta Fields");
$databaseName = "PRO_ORDER";

$orderUrl = $restFMPath .$databaseName ."/layout/" .$encodeLayoutName .".json?RFMsF1=" .$requestPkFieldName
    ."&RFMsV1" ."=" .$requestPk ."&RFMsF2=" .$errorField ."&RFMsV2=1";

$log->debug("URL: " .$orderUrl);

//Pro_Order username password cUrl will encrypt the credentials
$orderUsername = "0ap\$Admin";
$orderPassword = "gui39vp4s";

$opts = array(
    'http'=>array(
        'method'=>'GET',
        'header' => "Authorization: Basic " . base64_encode("$orderUsername:$orderPassword"))
);

$context = stream_context_create($opts);

//1. Using curl get the JSON data back from PHP RESTfm call
//Using @ symbnol to suppress Warniing message and to capture the return message
//RESTfm has confused data errors No Records Found with HTTP error
$data = @file_get_contents($orderUrl, false, $context);
//$data = file_get_contents($orderUrl, false, $context);

//2. If we have found an error test for which error (serious or just no documents found)
if($data === false){
    if(in_array("X-RESTfm-FM-Status: 401", $http_response_header)){
        $log->debug("We found our 401 error");
        echo null; // send an empty null back to receiver
        exit();
    }else{
        $error = error_get_last();
        $log->error("http error code3: " .$http_response_header[4]);
        $log->error("Now has @ symbol HTTP request failed. Error was: " . $error['message']);
        $dataErrorRecordMaster['error'] = true;
        $dataErrorRecordMaster['message'] = $error['message'];
        echo json_encode($dataErrorRecordMaster);
        exit();
    }
}

//3. Decode the JSON returned
$decodedJson = json_decode($data);

$log->debug("Last JSON Error: " .json_last_error() ." Message " .json_last_error_msg());

//4. Access the data element of the JSON array (FM records in JSON)
$dataRecords = $decodedJson->{'data'};

$dataRecordIndex = 1;
foreach($dataRecords as $records) {
    $dataErrorRecord = array();
    foreach ($records as $key => $value) {
        if($key == "Deliverable_Description_ct"){
            $dataErrorRecord["Deliverable_Description_ct"] = $value;
        }

        if($key == 'Order_n'){
            $dataErrorRecord['Order_n'] = $value;
        }
    }
    $dataErrorRecordMaster[$dataRecordIndex] = $dataErrorRecord;
    $dataRecordIndex++;
}

echo json_encode($dataErrorRecordMaster);
exit();
?>
