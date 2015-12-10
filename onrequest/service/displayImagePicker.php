<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/21/2014
 * Time: 2:08 PM
 */

include_once("request-config.php");
include_once($utilities ."utility.php");

function processImagePicker($label, $questionNum, $required, $fileName, $name, $imageUrl, $canModify){
    $pathName = 'pathname_' .$questionNum;
    $documentDiv = 'hiddenDiv_' .$questionNum;
    $uploadBtn = "upload_" .$questionNum;
    $uploadSuccess = "upload-success_" .$questionNum;
    $toolTipMessage = "Only one file may be uploaded per item.";
    $dynamicFunctionName = "showImageUploadSuccess_" .$questionNum;
    ?>
<br>
    <div class="row">
        <input type="hidden" id="<?php echo($pathName);?>" name="<?php echo($pathName);?>" value="">
        <div class="col-xs-12 col-lg-12">
            <h6><strong>
                <?php echo($questionNum . ". " .$label); ?>
                <?php if($required){ ?>
                    <i class="text-danger"> *(REQUIRED)</i>
                <?php } ?>
            </strong></h6>
        </div>
    </div>
    <div class="row">
        <div class="col-xs-12 col-lg-12">
            <div class="form-group">
                <?php if($canModify){ ?>
                    <input class="form-control file ays-ignore" type="file" id="<?php echo($name); ?>"
                           name="<?php echo($name); ?>" accept="image/*">
                <?php } else { ?>
                    <input class="form-control file ays-ignore" type="file" id="<?php echo($name); ?>"
                           name="<?php echo($name); ?>" accept="image/*" disabled="true">
                <?php } ?>
                <div class="tdc-display-none" id="<?php echo($documentDiv); ?>" name="<?php echo($documentDiv); ?>">
                    <div class="text-danger text-right row"><strong>Use Button To Upload File Now</strong></div>
                    <div class="row">
                            <input class="btn btn-primary btn-default pull-right" type="button" id="<?php echo($uploadBtn); ?>"
                               name="<?php echo($uploadBtn); ?>" value="Upload File"
                               data-toggle="tooltip" data-placement="left"
                               data-original-title="<?php echo($toolTipMessage); ?>"
                               onclick="fileUpload(document.getElementById('<?php echo($name);?>'),
                                   document.getElementById('<?php echo($documentDiv); ?>'),
                                   document.getElementById('<?php echo($uploadSuccess); ?>'))">
                    </div>
                </div>
                <div class="row text-right text-success tdc-display-none" id="<?php echo($uploadSuccess) ?>">
                    <strong>Image Successfully Uploaded</strong><br>
                    <i class="text-info"><strong>The preview of the image is not immediate available.</strong></i>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $('<?php echo('#'.$name); ?>').fileinput({
            <?php if(isset($imageUrl) && (strlen($imageUrl) > 0)){ ?>
                initialPreview : ["<img src='readImage.php?url=<?php echo(urlencode($imageUrl)); ?>' class='file-preview-image'"],
                'showUpload' : false,
                'showCaption' : true,
                'browseLabel' : 'Select Image',
                'initialCaption' : '<?php echo($fileName); ?>',
                'allowedFileExtensions' : ['jpg', 'jpeg', 'png', 'gif', 'tif']
            <?php }elseif(isset($fileName) && strlen($fileName) > 0){ ?>
                'showUpload' : false,
                'showCaption' : true,
                'browseLabel' : 'Select Image',
                'initialCaption' : '<?php echo($fileName); ?>',
                'allowedFileExtensions' : ['jpg', 'jpeg', 'png', 'gif', 'tif']
            <?php }else{ ?>
                'showUpload' : false,
                'showCaption' : true,
                'browseLabel' : 'Select Image',
                'allowedFileExtensions' : ['jpg', 'jpeg', 'png', 'gif', 'tif']
            <?php } ?>
        }) ;

<?php echo("\t" .buildShowSuccessFunction($dynamicFunctionName, $uploadSuccess, $documentDiv ) ."\n"); ?>

        $(document).ready(function(){
            $('<?php echo('#'.$uploadBtn); ?>').tooltip();

            $('<?php echo('#'.$name);?>').change(function(){
               if(document.getElementById('<?php echo($name); ?>').files.length == 0){
                   document.getElementById('<?php echo($documentDiv); ?>').style.display = 'none';
               } else{
                   document.getElementById('<?php echo($documentDiv); ?>').style.display = 'block';
               }
            });

<?php echo("\t " .buildUploadClickListener($questionNum, $uploadBtn) ."\n"); ?>

        });
    </script>

<?php } ?>