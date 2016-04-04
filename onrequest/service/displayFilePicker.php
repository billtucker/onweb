<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/21/2014
 * Time: 1:28 PM
 */

include_once("request-config.php");
include_once($utilities ."utility.php");

function processFilePicker($label, $questionNum, $required, $fileName, $pkId, $containerUrl, $canModify){
    global $log, $site_prefix;

    //Start of refactor work
    $submitFileButtonId = "submit-all-" .$questionNum;
    $removeFileButtonId = "remove-all-" .$questionNum;
    $glyphiconRemoveId = $pkId ."_remove";
    $formDropzoneId = "file_dropzone" .$questionNum;
    $fullFileId = "fullImage" .$questionNum;
    $uploadSuccessId = "upload-success-" .$questionNum;

    $uploadButtonLabel = "Upload File";
    $removeButtonLabel = "Remove File";
    $primaryKeyId = "pkid-" .$questionNum;

    $fileTypeIcon = "";

    //support file delete from FileMaker functionality
    $deleteSuccessId = "remove-success-" .$questionNum;
    $removeMethodName = "removeFile" .$questionNum;

    if(isset($containerUrl) && !empty($containerUrl)){
        $extension = getFileMakerContainerFileExtension($containerUrl);
        $fileTypeIcon = getFileIcon($extension);
    }


    if((!isset($fileName) || empty($fileName)) && (isset($containerUrl) && !empty($containerUrl))){
        $fileName = getLightBoxCaption($containerUrl);
    }

?>

    <br>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <h6><b>
                    <?php echo($questionNum . ". " .$label); ?>
                    <?php if($required){ ?>
                        <i class="text-danger"> *(REQUIRED)</i>
                    <?php } ?>
                </b>
            </h6>
        </div>
    </div>
    <div class="row">
    <input type="hidden" id="<?php echo($primaryKeyId);?>" name="<?php echo($primaryKeyId);?>" value="<?php echo($pkId); ?>">
    <?php if($canModify) { ?>
        <?php if(!empty($containerUrl)){?> <!-- container has data so display image in Lightbox-->
            <div class="col-xs-6 col-md-6"><!-- move this outside of if() test to line 190 -->
                <span class="glyphicon glyphicon-remove pull-right remove-cross" title="Remove File From Server" data-toggle="tooltip"
                      id="<?php echo($glyphiconRemoveId); ?>" onclick="<?php echo($removeMethodName); ?>('<?php echo($primaryKeyId);?>');"
                      style="margin-right: 10px;">
                </span>
                <div class="image_container">
                    <a href="<?php echo($containerUrl); ?>" alt="Full File">
                        <img class="img-responsive preview-image" src="<?php echo($fileTypeIcon); ?>"
                         align="left" id="<?php echo ($fullFileId); ?>">
                        <!-- <p class="text-center" style="padding-left: 20px;"><?php echo(urldecode($fileName)); ?></p> -->
                    </a>
                </div>
                <div class="row text-left text-success tdc-display-none" id="<?php echo($deleteSuccessId); ?>">
                    <strong>File Successfully Deleted From FileMaker</strong><br>
                </div>
            </div>
            <div class="col-xs-6 col-md-6 tdc-cell-spacing">
                <div class="row">
                    <div id="<?php echo ($formDropzoneId); ?>" class="decoration  dropzone"></div>
                </div>
                <div class="row pull-right upload-btn-position">
                    <button type="button" id="<?php echo($submitFileButtonId); ?>" class="btn btn-primary upload-btn"><?php echo($uploadButtonLabel); ?></button>
                    &nbsp;<button type="button" id="<?php echo($removeFileButtonId); ?>" class="btn btn-danger upload-btn"><?php echo ($removeButtonLabel); ?></button>
                </div>
                <div class="row text-right text-success tdc-display-none" id="<?php echo($uploadSuccessId); ?>">
                    <strong>Image Successfully Uploaded</strong><br>
                    <i class="text-info"><strong>The preview of the image is not immediate available.</strong></i>
                </div>
            </div>
        <?php }else if(empty($containerUrl) && !empty($fileName)){ ?><!-- container is empty but the filename field exists -->
            <div class="col-xs-6 col-md-6 empty_image_container"><!-- move this outside of if() test to line 190 -->
                <p class="text-center">Import still processing file <?php echo($fileName); ?>.</p>
                <p class="text-center"> Reload page when complete.</p>
            </div>
            <div class="col-xs-6 col-md-6">
                <div class="row">
                    <div id="<?php echo ($formDropzoneId); ?>" class="decoration  dropzone"></div>
                </div>
                <div class="row pull-right upload-btn-position">
                    <button type="button" id="<?php echo($submitFileButtonId); ?>" class="btn btn-primary upload-btn"><?php echo($uploadButtonLabel); ?></button>
                    &nbsp;<button type="button" id="<?php echo($removeFileButtonId); ?>" class="btn btn-danger upload-btn"><?php echo ($removeButtonLabel); ?></button>
                </div>
                <div class="row text-right text-success tdc-display-none" id="<?php echo($uploadSuccessId); ?>">
                    <strong>Image Successfully Uploaded</strong><br>
                    <i class="text-info"><strong>The preview of the image is not immediate available.</strong></i>
                </div>
            </div>
        <?php } else { ?><!-- No container data or filename just display Dropzone box -->
            <div class="col-xs-12 col-md-12">
                <div class="row">
                    <!-- <form action="<?php echo($site_prefix .'uploaders/imageUploader.php'); ?>" class="dropzone decoration" id="<?php echo ($formDropzoneId); ?>"></form> -->
                    <div id="<?php echo ($formDropzoneId); ?>" class="decoration  dropzone"></div>
                </div>
                <div class="row pull-right upload-btn-position">
                    <button type="button" id="<?php echo($submitFileButtonId); ?>" class="btn btn-primary upload-btn"><?php echo($uploadButtonLabel); ?></button>
                    &nbsp;<button type="button" id="<?php echo($removeFileButtonId); ?>" class="btn btn-danger upload-btn"><?php echo ($removeButtonLabel); ?></button>
                </div>
                <div class="row text-right text-success tdc-display-none" id="<?php echo($uploadSuccessId); ?>">
                    <strong>Image Successfully Uploaded</strong><br>
                    <i class="text-info"><strong>The preview of the image is not immediate available.</strong></i>
                </div>
            </div>
        <?php } ?>
    <?php } else { ?>
    <!-- The user cannot remove or add the file downloaded from FileMaker or else just show empty bordered DIV -->
        <?php if($containerUrl){ ?>
            <div class="col-xs-12 col-md-12 image_container">
                <a href="<?php echo($containerUrl); ?>" alt="Full File">
                    <img class="img-responsive preview-image" src="<?php echo($fileTypeIcon); ?>"
                        align="left" id="<?php echo ($fullFileId); ?>">
                    <p class="text-center" style="padding-left: 20px;"><?php echo(urldecode($fileName)); ?></p>
                </a>
            </div>
        <?php } else { ?>
            <div class="col-xs-12 col-md-12 image_container"></div>
        <?php } ?>
    <?php } ?>
    </div>

    <script>

        $(document).ready(function () {
            $('<?php echo('#' .$submitFileButtonId);?>').hide();
            $('<?php echo('#' .$removeFileButtonId); ?>').hide();
            $('<?php echo('#' .$uploadSuccessId); ?>').hide();
            $('<?php echo('#' .$deleteSuccessId); ?>').hide();
        });


        function <?php echo($removeMethodName); ?>(elmId){
            var pkid = document.getElementById(elmId).value;
            console.log("PK: " + pkid);

            $.ajax({
                data: 'pkId=' + pkid,
                url: 'processing/removeFileFM.php',
                method: 'POST',
                success: function(msg){
                    console.log(msg);
                },

                error: function(msg){
                    console.log("Error: " + msg);
                }
            });

            var fileSelected = document.getElementById('<?php echo ($fullFileId); ?>');
            fileSelected.parentNode.removeChild(fileSelected);
            $('<?php echo('#' .$deleteSuccessId); ?>').fadeIn(100).delay(5000).fadeOut(500);
        }

        Dropzone.options.<?php echo(getCamelCase($formDropzoneId)); ?> = {
            //URL to submit the for
            //TODO make this more dynamic Remove all hardcoded path elements in this page
            url: '../uploaders/imageUploader.php',

            //Prevent dropzone from uploading files immediately
            autoProcessQueue: false,

            //Dropzone accepts the file types to upload
            acceptedFiles: ".pdf, .docx, .doc, .txt, .xls, .xlsx",

            init: function(){
                var submitButton = document.querySelector('<?php echo('#' .$submitFileButtonId);?>');
                var myDropzone = this; //closure

                submitButton.addEventListener("click", function(){
                    console.log("Event listener fired");
                    myDropzone.processQueue(); //Tell Dropzone to process all queued files
                });

                this.on("sending", function(file, xhr, data) {
                    console.log("Adding Meta record PKID to Post array");
                    data.append("pkId", document.getElementById('<?php echo($primaryKeyId);?>').value);
                });

                //if an error is found with mime type of file hide the submit button
                this.on("error", function(file, response){
                    console.log("Error has occurred");
                    $('<?php echo('#' .$submitFileButtonId);?>').hide();
                });

                this.on("success", function(file, response){
                    console.log("Successful upload");
                    $('<?php echo('#' .$submitFileButtonId);?>').hide();
                    $('<?php echo('#' .$removeFileButtonId); ?>').hide();
                    $('<?php echo('#' .$uploadSuccessId); ?>').fadeIn(100).delay(5000).fadeOut(500);
                });

                //When a file is dropped inside Dropzone show box show button controls
                //TODO would like to use the success return to ensure that the button will only show when mine
                // type matches what we expect
                this.on("addedfile", function(file){
                    var ext = file.name.split('.').pop();

                    console.log("File Extension for added file: " + ext);

                    if(ext == 'pdf'){
                        $(file.previewElement).find(".dz-image img").attr("src", "../images/PDF-icon_128px_red.png");
                    }else if(ext.indexOf('doc') != -1) { //this test was doc or docx
                        $(file.previewElement).find(".dz-image img").attr("src", "../images/Office-Word-icon_128px_blue.png");
                    }else if(ext.indexOf('xls') != -1) { //this test was xls or xslx
                        console.log("Show excel icon");
                        $(file.previewElement).find(".dz-image img").attr("src", "../images/excel-icon-128px.png");
                    }else if(ext == 'txt'){
                        $(file.previewElement).find(".dz-image img").attr("src", "../images/text-icon_128px.png");
                    }

                    $('<?php echo('#' .$removeFileButtonId); ?>').show();
                    $('<?php echo('#' .$submitFileButtonId);?>').show();
                });

                //removes files previous loaded in the Dropzone upload box
                //Also hides upload and reset buttons
                document.querySelector("button#<?php echo($removeFileButtonId);?>").addEventListener("click", function(){
                    myDropzone.removeAllFiles();
                    $('<?php echo('#' .$submitFileButtonId);?>').hide();
                    $('<?php echo('#' .$removeFileButtonId); ?>').hide();
                });
            }
        }
    </script>

<?php } ?>