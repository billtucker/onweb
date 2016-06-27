<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 6/17/2016
 * Time: 9:45 AM
 *
 * TODO this file requires rework as variables should aline so we do not need separate variables for File and then Image
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");
include_once($fmfiles ."order.db.php");
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

if(isset($_POST['filetype'])){
    $log->debug("File Type: " .$_POST['filetype']);
}

if(isset($_POST['pk']) && isset($_POST['url']) && isset($_POST['questNum']) && isset($_POST['filename']) && isset($_POST['filetype'])){
    $pkId = $_POST['pk'];
    $questionNum = $_POST['questNum'];
    $fileName = $_POST['filename'];
    $fileType = $_POST['filetype'];
    $log->debug("PKID: " .$pkId ." Question Number: " .$questionNum ." File Name: " .$fileName, " Filetype: " .$fileType);

    $removeFilesLabel = "removeFile" .$questionNum;
    $removeFileParam = "pkid-" .$questionNum;
    $dataTitleLabel = $fileName;
    $fullImageLabel = "fullImage" .$questionNum;
    $removeSuccessId = "remove-success-" .$questionNum;
    $spanId = $pkId ."_remove";

    $uploadButtonLabel = "Upload Image";
    $removeButtonLabel = "Remove File";
    $uploadSuccessId = "upload-success-" .$questionNum;


    if($fileType == 'image'){
        $imageUrl = "../readImage.php?url=" .urlencode($_POST['url']);
        $log->debug("Image process for URL: " .$imageUrl);
        $submitImageButtonId = "submit-all-" .$questionNum;
        $removeImageButtonId = "remove-all-" .$questionNum;

        //new variables to support both 6 column div(s) added 06/22/2016
        $imageDropzoneId = "image_dropzone" .$questionNum;
        $initDzId = "#" .$imageDropzoneId;

        //display replace div (in this case) an image file
        //Note If an image added as net new or replacement image the entire section image div and dropzone div are replaced
        echo "<div class='col-xs-6 col-md-6'><!-- Start of column 6 div -->";
        echo "<span class='glyphicon glyphicon-remove pull-right remove-cross' title='Remove File From Server' data-toggle='tooltip' ";
        echo "id='$spanId' onclick='$removeFilesLabel('$removeFileParam');'></span>";
        echo "<div class='row image_container'>";
        echo "<a href='$imageUrl' alt='Full Image' data-lightbox='$pkId' data-title='$dataTitleLabel'>";
        echo "<img class='img-responsive preview-image' src='$imageUrl' align='left' id='$fullImageLabel'></a>";
        echo "</div>";
        echo "<div class='row text-left text-success tdc-display-none' id='$removeSuccessId'>";
        echo "<strong>File Successfully Deleted From FileMaker</strong><br>";
        echo "</div>";
        echo "</div><!--End column 6 div -->";
        echo "<div class='col-xs-6 col-md-6'>";
        echo "<div class='row'>";
        echo "<div id='$imageDropzoneId' class='decoration  dropzone'></div>";
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
    }else if($fileType == 'file'){
        //Variables used in File mapulation TODO: align variables between Image and File
        $deleteSuccessId = "remove-success-" .$questionNum;
        $removeMethodName = "removeFile" .$questionNum;
        $fullFileId = "fullImage" .$questionNum;
        $submitFileButtonId = "submit-all-" .$questionNum;
        $removeFileButtonId = "remove-all-" .$questionNum;
        $submitFileButtonId = "submit-all-" .$questionNum;
        $removeFileButtonId = "remove-all-" .$questionNum;
        $primaryKeyId = "pkid-" .$questionNum;
        $glyphiconRemoveId = $pkId ."_remove";

        $fileDropzoneId = "file_dropzone" .$questionNum;
        $initDzId = "#" .$fileDropzoneId;

        $rawUrl = $_POST['url'];
        $fileUrl = urlencode($_POST['url']);
        $log->debug("File operation URL: " .$fileUrl);
        $fileTypeIcon = "";

        if(isset($rawUrl) && !empty($rawUrl)){
            $extension = getFileMakerContainerFileExtension($rawUrl);
            $fileTypeIcon = getFileIcon($extension);
            $log->debug("File Extension is: " .$extension ." File Type icon is: " .$fileTypeIcon);
        }else{
            $log->error("File url is empty");
        }

        //display replacement div for files
        echo "<div class='col-xs-6 col-md-6'><!-- move this outside of if() test to line 190 -->";
        echo "<span class='glyphicon glyphicon-remove pull-right remove-cross' title='Remove File From Server' data-toggle='tooltip' ";
        echo "id='$glyphiconRemoveId' onclick='$removeMethodName(\"$primaryKeyId\");' style='margin-right: 10px;'></span>";
        echo "<div class='image_container'>";
        echo "<a href='$fileUrl' alt='Full File'>";
        echo "<img class='img-responsive preview-image' src='$fileTypeIcon' align='left' id='$fullFileId'></a>";
        echo "</div>";
        echo "<div class='row text-left text-success tdc-display-none' id='$deleteSuccessId'>";
        echo "<strong>File Successfully Deleted From FileMaker</strong><br>";
        echo "</div>";
        echo "</div><!-- end of first 6 column div -->";
        echo "<div class='col-xs-6 col-md-6 tdc-cell-spacing'>";
        echo "<div class='row'>";
        echo "<div id='$fileDropzoneId' class='decoration  dropzone'></div>";
        echo "</div>";
        echo "<div class='row pull-right upload-btn-position'>";
        echo "<button type='button' id='$submitFileButtonId' class='btn btn-primary upload-btn'>$uploadButtonLabel</button>";
        echo "&nbsp;<button type='button' id='$removeFileButtonId' class='btn btn-danger upload-btn'>$removeButtonLabel</button>";
        echo "</div>";
        echo "<div class='row text-right text-success tdc-display-none' id='$uploadSuccessId'>";
        echo "<strong>Image Successfully Uploaded</strong><br>";
        echo "<i class='text-info'><strong>The preview of the image is not immediate available.</strong></i>";
        echo "</div>";
        echo "</div><!-- end of second 6 column div -->";
        echo "<script> new Dropzone('$initDzId'); </script>";
    }else{
        $log->error("Unknown file type sent to Div loader");
    }

    $log->debug("Done with replacing either a Image or File div using echo and Ajax load method");
}else{
    $log->error("A required parameter for page rewrite is missing");
}