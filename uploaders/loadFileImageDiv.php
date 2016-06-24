<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 6/17/2016
 * Time: 9:45 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once ($fmfiles ."order.db.php");
include_once($utilities .'utility.php');

if(isset($_POST['pk'])){
    $log->debug("PK: " .$_POST['pk']);
}

if(isset($_POST['url'])){
    $log->debug("URL: " .$_POST['url']);
}

if(isset($_POST['questNum'])){
    $log->debug("Question Number: " .$_POST['questNum']);
}

if(isset($_POST['filename'])){
    $log->debug("Filename: " .$_POST['filename']);
}


if(isset($_POST['pk']) && isset($_POST['url']) && isset($_POST['questNum']) && isset($_POST['filename'])){
    $pkId = $_POST['pk'];
    $url = "../readImage.php?url=" .urlencode($_POST['url']);
    $questionNum = $_POST['questNum'];
    $fileName = $_POST['filename'];
    $log->debug("PKID: " .$pkId ." URL: " .$url ." Question Number: " .$questionNum ." File Name: " .$fileName);

    $removeFilesLabel = "removeFile" .$questionNum;
    $removeFileParam = "pkid-" .$questionNum;
    $dataTitleLabel = $fileName;
    $fullImageLabel = "fullImage" .$questionNum;
    $removeSuccessId = "remove-success-" .$questionNum;
    $spanId = $pkId ."_remove";

    //new variables to support both 6 column div(s) added 06/22/2016
    $formDropzoneId = "image_dropzone" .$questionNum;
    $submitImageButtonId = "submit-all-" .$questionNum;
    $removeImageButtonId = "remove-all-" .$questionNum;
    $uploadButtonLabel = "Upload Image";
    $removeButtonLabel = "Remove File";
    $uploadSuccessId = "upload-success-" .$questionNum;
    $initDzId = "#" .$formDropzoneId;

    //display replace div (in this case) an image file
    //Note If an image added as net new or replacement image the entire section image div and dropzone div are replaced
    echo "<div class='col-xs-6 col-md-6'><!-- Start of column 6 div -->";
    echo "<span class='glyphicon glyphicon-remove pull-right remove-cross' title='Remove File From Server' data-toggle='tooltip' ";
    echo "id='$spanId' onclick='$removeFilesLabel('$removeFileParam');'></span>";
    echo "<div class='row image_container'>";
    echo "<a href='$url' alt='Full Image' data-lightbox='$pkId' data-title='$dataTitleLabel'>";
    echo "<img class='img-responsive preview-image' src='$url' align='left' id='$fullImageLabel'></a>";
    echo "</div>";
    echo "<div class='row text-left text-success tdc-display-none' id='$removeSuccessId'>";
    echo "<strong>File Successfully Deleted From FileMaker</strong><br>";
    echo "</div>";
    echo "</div><!--End column 6 div -->";
    echo "<div class='col-xs-6 col-md-6'>";
    echo "<div class='row'>";
    echo "<div id='$formDropzoneId' class='decoration  dropzone'></div>";
    echo "</div>";
    echo "<div class='row pull-right upload-btn-position'>";
    echo "<button type='button' id='$submitImageButtonId' class='btn btn-primary upload-btn'>$uploadButtonLabel</button>";
    echo "&nbsp;<button type='button' id='$removeImageButtonId' class='btn btn-danger upload-btn'>$removeButtonLabel</button>";
    echo "</div>";
    echo "<div class='row text-right text-success tdc-display-none' id='$uploadSuccessId'>";
    echo "<strong>Image Successfully Uploaded</strong><br>";
    echo "<i class='text-info'><strong>The preview of the image is not immediate available.</strong></i>";
    echo "</div>";
    echo "</div><!-- end of 2nd column 6 div -->";
    echo "<script> new Dropzone('$initDzId'); </script>";
    $log->debug("Done with replacing both div(s) using echo and Ajax load method");
}else{
    $log->debug("One of the parameter is missing");
}