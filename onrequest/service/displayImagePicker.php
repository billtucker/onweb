<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 3/11/2016
 * Time: 7:54 AM
 *
 * Image picker dynamically builds fields to support a Dropzone object to include Lightbox object to display
 * an existing image from FileMaker or image Drag-n-Drop image from the local system
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."request-config.php";
include_once($utilities ."utility.php");

function processImagePicker($label, $questionNum, $required, $fmFilename, $pkId, $containerUrl, $canModify){
    global $log, $site_prefix, $getContainerUrl;

    $submitImageButtonId = "submit-all-" .$questionNum;
    $removeImageButtonId = "remove-all-" .$questionNum;
    $glyphiconRemoveId = $pkId ."_remove";
    $formDropzoneId = "image_dropzone" .$questionNum;
    $fullImageId = "fullImage" .$questionNum;
    $uploadSuccessId = "upload-success-" .$questionNum;

    $uploadButtonLabel = "Upload Image";
    $removeButtonLabel = "Remove File";
    $primaryKeyId = "pkid-" .$questionNum;

    //support file delete from FileMaker functionality
    $deleteSuccessId = "remove-success-" .$questionNum;
    $removeMethodName = "removeFile" .$questionNum;

    //Added question number hidden field to work with the image/file downloader for display
    $hiddenQuestNumId = $pkId ."_" .$questionNum;
    $replacementDivId = $pkId ."_replace";
    $getContainerDataMethod = "getContainerData" .$questionNum;
    $rewriteImageDivMethod = "rewriteImageDiv" .$questionNum;
    $fileTypeID = $pkId ."_" .$questionNum."_fileType";

    if(!empty($containerUrl)){
        $imageCaption = getLightBoxCaption($containerUrl);
    }

?>

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
    <div class="tdc-container-fluid">
        <div class="row">
            <input type="hidden" id="<?php echo($primaryKeyId);?>" name="<?php echo($primaryKeyId);?>" value="<?php echo($pkId);?>">
            <input type="hidden" id="<?php echo($hiddenQuestNumId);?>" name="<?php echo($hiddenQuestNumId);?>" value="<?php echo($questionNum);?>">
            <input type="hidden" id="<?php echo($fileTypeID); ?>" name="<?php echo($fileTypeID); ?>" value="image">
            <?php if($canModify) {?>
                <?php if(!empty($containerUrl)){?> <!-- container has data so display image in Lightbox-->
                    <div id="<?php echo($replacementDivId);?>" name="<?php echo($replacementDivId);?>"><!-- start of image replacement div -->
                        <div class="col-xs-6 col-md-6"><!-- move this outside of if() test to line 190 -->
                            <span class="glyphicon glyphicon-remove pull-right remove-cross" title="Remove File From Server" data-toggle="tooltip"
                                id="<?php echo($glyphiconRemoveId); ?>" onclick="<?php echo($removeMethodName); ?>('<?php echo($primaryKeyId);?>');"></span>
                            <div class="row image_container">
                                <a href="../readImage.php?url=<?php echo(urlencode($containerUrl)); ?>" alt="Full Image" data-lightbox="<?php echo($pkId);?>"
                                    data-title="<?php echo($imageCaption);?>">
                                    <img class="img-responsive preview-image" src="../readImage.php?url=<?php echo(urlencode($containerUrl)); ?>"
                                        align="left" id="<?php echo ($fullImageId); ?>">
                                </a>
                            </div>
                            <div class="row text-left text-success tdc-display-none" id="<?php echo($deleteSuccessId); ?>">
                                <strong>File Successfully Deleted From FileMaker</strong><br>
                            </div>
                        </div><!--end of 1st column 6 div -->
                        <div class="col-xs-6 col-md-6">
                            <div class="row">
                                <div id="<?php echo ($formDropzoneId); ?>" class="decoration  dropzone"></div>
                            </div>
                            <div class="row pull-right upload-btn-position">
                                <button type="button" id="<?php echo($submitImageButtonId); ?>"
                                        class="btn btn-primary upload-btn"><?php echo($uploadButtonLabel); ?>
                                </button>
                                &nbsp;<button type="button" id="<?php echo($removeImageButtonId); ?>"
                                              class="btn btn-danger upload-btn"><?php echo ($removeButtonLabel); ?>
                                </button>
                            </div>
                            <div class="row text-right text-success tdc-display-none" id="<?php echo($uploadSuccessId); ?>">
                                <strong>Image Successfully Uploaded</strong><br>
                                <i class="text-info"><strong>The preview of the image is not immediate available.</strong></i>
                            </div>
                        </div><!-- end of 2nd column 6 div -->
                    </div><!-- End of replacement div -->
            <?php }else if(empty($containerUrl) && !empty($fmFilename)){ ?><!-- container is empty but the filename field exists -->
                <div id="<?php echo($replacementDivId);?>" name="<?php echo($replacementDivId);?>"><!-- start of image replacement div -->
                    <div class="col-xs-6 col-md-6 empty_image_container"><!-- move this outside of if() test to line 190 -->
                        <p class="text-center">Import still processing file <?php echo($fmFilename); ?>.</p>
                        <p class="text-center"> Reload page when complete.</p>
                    </div><!-- end of 1st column 6 div -->
                    <div class="col-xs-6 col-md-6">
                        <div class="row">
                            <div id="<?php echo ($formDropzoneId); ?>" class="decoration  dropzone"></div>
                        </div>
                        <div class="row pull-right upload-btn-position">
                            <button type="button" id="<?php echo($submitImageButtonId); ?>"
                                class="btn btn-primary upload-btn"><?php echo($uploadButtonLabel); ?>
                            </button>
                                &nbsp;<button type="button" id="<?php echo($removeImageButtonId); ?>"
                                      class="btn btn-danger upload-btn"><?php echo ($removeButtonLabel); ?>
                            </button>
                        </div>
                        <div class="row text-right text-success tdc-display-none" id="<?php echo($uploadSuccessId); ?>">
                            <strong>Image Successfully Uploaded</strong><br>
                            <i class="text-info"><strong>The preview of the image is not immediate available.</strong></i>
                        </div>
                    </div><!-- end of 2nd column 6 div -->
                </div><!-- end of image replacement div -->
            <?php } else { ?><!-- No container data or filename just display Dropzone box -->
            <div id="<?php echo($replacementDivId);?>" name="<?php echo($replacementDivId);?>"><!-- start of image replacement div -->
                <div class="col-xs-12 col-md-12">
                    <div class="row">
                        <div id="<?php echo ($formDropzoneId); ?>" class="decoration  dropzone"></div>
                    </div>
                    <div class="row pull-right upload-btn-position">
                        <button type="button" id="<?php echo($submitImageButtonId); ?>"
                                class="btn btn-primary upload-btn"><?php echo($uploadButtonLabel); ?>
                        </button>
                        &nbsp;<button type="button" id="<?php echo($removeImageButtonId); ?>" c
                                      lass="btn btn-danger upload-btn"><?php echo ($removeButtonLabel); ?>
                        </button>
                    </div>
                    <div class="row text-right text-success tdc-display-none" id="<?php echo($uploadSuccessId); ?>">
                        <strong>Image Successfully Uploaded</strong><br>
                        <i class="text-info"><strong>The preview of the image is not immediate available.</strong></i>
                    </div>
                </div><!-- end of 12 column div -->
            </div><!-- end of replacement div -->
            <?php } ?>
        <?php } else{ ?>
            <?php if(!empty($containerUrl)){?> <!-- container has data so display image in Lightbox-->
                <div class="col-xs-12 col-md-12 image_container"><!-- move this outside of if() test to line 190 -->
                    <a href="../readImage.php?url=<?php echo(urlencode($containerUrl)); ?>" alt="Full Image" data-lightbox="<?php echo($pkId); ?>"
                       data-title="<?php echo($imageCaption) ?>">
                        <img class="img-responsive preview-image" src="../readImage.php?url=<?php echo(urlencode($containerUrl)); ?>"
                             align="left" id="<?php echo ($fullImageId); ?>">
                    </a>
                </div>
            <?php } else { ?>
                <div class="col-xs-12 col-md-12 image_container"></div>
            <?php } ?>
        <?php } ?>
        </div><!-- end of row div -->
    </div><!-- end of container for image display -->

    <script>

        //init button display by hiding all buttons associated with file upload
        $(document).ready(function () {
            $('<?php echo('#' .$submitImageButtonId);?>').hide();
            $('<?php echo('#' .$removeImageButtonId); ?>').hide();
            $('<?php echo('#' .$uploadSuccessId); ?>').hide();
            $('<?php echo('#' .$deleteSuccessId); ?>').hide();
        });


        //Remove the image from the FileMaker (null) the container then remove the image from the page
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

            var imageSelected = document.getElementById('<?php echo ($fullImageId); ?>');
            imageSelected.parentNode.removeChild(imageSelected);
            $('<?php echo('#' .$deleteSuccessId); ?>').fadeIn(100).delay(5000).fadeOut(500);
        }

        Dropzone.options.<?php echo(getCamelCase($formDropzoneId)); ?> = {
            //URL to submit the for
            //TODO make this more dynamic Remove all hardcoded path elements in this page
            url: '../uploaders/imageUploader.php',

            //Prevent dropzone from uploading files immediately
            autoProcessQueue: false,

            //Dropzone accepts images and video file only
            acceptedFiles: "image/*",

            //For now we are only accepting a single image file
            maxFiles: 1,

            init: function(){
                var submitButton = document.querySelector('<?php echo('#' .$submitImageButtonId);?>');
                var myDropzone = this; //closure

                this.on('dragover drop', function(e) {
                    e.preventDefault();
                });

                submitButton.addEventListener("click", function(e){
                    console.log("Event listener fired");
                    e.preventDefault();
                    myDropzone.processQueue(); //Tell Dropzone to process all queued files
                });

                this.on("sending", function(file, xhr, data) {
                    console.log("Adding Meta record PKID to Post array");
                    data.append("pkId", document.getElementById('<?php echo($primaryKeyId);?>').value);
                });

                //if an error is found with mime type of file hide the submit button
                this.on("error", function(file, response){
                    console.log("Error has occurred");
                    $('<?php echo('#' .$submitImageButtonId);?>').hide();
                });

                this.on("success", function(file, response){
                    console.log("Successful upload");
                    $('<?php echo('#' .$submitImageButtonId);?>').hide();
                    $('<?php echo('#' .$removeImageButtonId); ?>').hide();
                    $('<?php echo('#' .$uploadSuccessId); ?>').fadeIn(100).delay(5000).fadeOut(500);

                    //Now we need to get the flag to run get the 'Just' uploaded image/file URL
                    var runContainerUrl = <?php echo $getContainerUrl ? 'true' : 'false' ?>;
                    var jsonData = JSON.parse(response);
                    if(jsonData && jsonData.container_status){
                        if(jsonData.container_status != '' && jsonData.container_status == 'empty'){
                            console.log("We have container status of empty so now get the container URL");
                            <?php echo($getContainerDataMethod);?>(document.getElementById('<?php echo($primaryKeyId);?>').value,
                                myDropzone);
                        }
                    }
                });

                //When a file is dropped inside Dropzone show box show button controls
                //TODO would like to use the success return to ensure that the button will only show when mine
                // type matches what we expect
                this.on("addedfile", function(){
                    $('<?php echo('#' .$removeImageButtonId); ?>').show();
                    $('<?php echo('#' .$submitImageButtonId);?>').show();
                });

                //removes files previous loaded in the Dropzone upload box
                //Also hides upload and reset buttons
                document.querySelector("button#<?php echo($removeImageButtonId);?>").addEventListener("click", function(){
                    myDropzone.removeAllFiles();
                    $('<?php echo('#' .$submitImageButtonId);?>').hide();
                    $('<?php echo('#' .$removeImageButtonId); ?>').hide();
                });
            }
        }

        /**
         * Method to recursively call a PHP file and get the container URL for a given META PK
         * @param pkId string value of the PK for a given meta record
         * Note: TODO: can this be a generic method to replace a DIV encapsulating a image or file
         */
        function <?php echo($getContainerDataMethod);?>(pkId, myDropzone){
            var getFileTimer = setTimeout(function() {
                console.log("getContainerData called with PKID: " + pkId);
                $.ajax({
                    url: "../uploaders/urlReloader.php", //get the file name of PHP processor to get container URL
                    data: {"pk":pkId},
                    type: "POST",
                    success: function(response){
                        console.log("getContainerDataMethod Success " + response);
                        var jsonData = JSON.parse(response);
                        if(jsonData.container_status == "empty"){
                            console.log("Conatiner is empty so call method again");
                            <?php echo($getContainerDataMethod);?>(pkId, myDropzone);
                            //now run this method ever 5 seconds as a thread to release browser
                        }else{
                            if(jsonData.container_url) {
                                <?php echo($rewriteImageDivMethod);?>(pkId,
                                    document.getElementById('<?php echo($hiddenQuestNumId);?>').value,
                                    jsonData.container_url,jsonData.filename, myDropzone,
                                document.getElementById('<?php echo($fileTypeID)?>').value);
                                clearTimeout(getFileTimer);
                                return false;
                            }else{
                                console.log("Strange condition - container status not empty without a URL in JSON array");
                            }
                        }
                    },
                    error: function(response){
                        console.log("Error: " + response);
                        jsonErrorData = JSON.parse(response);
                        if(jsonErrorData.status == "error"){
                            console.log("Error occurred kill get URL process");
                            clearTimeout(getFileTimer);
                            return false;
                        }
                    }
                });
            }, 90000); //This setup to run ever 1.5 minutes
        }

        /**
         * Rewrite the Div srounding the Image or file and replace it with the URL from the FM container
         * @param pk Primary meta file record id/key UUID
         * @param questNum the meta question number associated with item
         * @param url the container URL
         * @param filename the filename of the object file/image uploaded
         */
        function <?php echo($rewriteImageDivMethod);?>(pk, questNum, url, filename, dzHandle, filetype){
            console.log("rewriteImageDiv PK: " + pk + " Question Number: " + questNum + " URL: " + url);

            //Call the php file with POST format and query string attributes
            $('<?php echo('#' .$replacementDivId);?>').load('../uploaders/loadFileImageDiv.php',
                {'pk':pk, 'url':url, 'questNum':questNum, 'filename':filename,'filetype':filetype}, function(){
                    console.log("Load complete now hide buttons");
                    $('<?php echo('#' .$submitImageButtonId);?>').hide();
                    $('<?php echo('#' .$removeImageButtonId); ?>').hide();
                });
            dzHandle.removeAllFiles();
        }

    </script>
<?php } ?>