<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 9/22/2015
 * Time: 9:37 AM
 *
 * This file is used to simply test the connection to the FileMaker database and onweb site
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");

$connect = $fmOrderDB->listLayouts();
$error = false;
if(FileMaker::isError($connect)){
    $displayMessage = "Error with connection: " .$connect->getMessage() ." Error Code: " .$connect->getCode();
    $error = true;
}else{
    $displayMessage = "Successfully Connected to database";
}

?>

<!DOCTYPE html>
<html  lang="en">
<head>
    <title>Test FM Connection</title>
    <link href="//netdna.bootstrapcdn.com/bootstrap/3.1.0/css/bootstrap.min.css" rel="stylesheet">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js" type="text/javascript"></script>
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.0/js/bootstrap.min.js" type="text/javascript"></script>
</head>
<body>
    <br>
    <div class="container">
        <div class="row">
            <h3 class="text-center">Connection Results</h3>
        </div>
        <div class="row">
            <?php if($error){ ?>
                <p class="text-danger"><b>Connection Test Result Message:</b> <?php echo ($displayMessage); ?></p>
            <?php } else { ?>
                <p class="text-success"><b>Connection Test Result Message:</b> <?php echo ($displayMessage); ?></p>
            <?php } ?>
        </div>
    </div>
</body>
</html>
