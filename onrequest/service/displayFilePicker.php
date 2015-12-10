<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 10/21/2014
 * Time: 1:28 PM
 */

include_once("request-config.php");
include_once($utilities ."utility.php");

function processFilePicker($label, $questionNum, $required, $fileName, $name, $fileUrl, $canModify){
    $pathName = 'pathname_' .$questionNum;
    $documentDiv = 'hiddenDiv_' .$questionNum;
    $uploadBtn = "upload_" .$questionNum;
    $uploadSuccess = "upload-success_" .$questionNum;
    $toolTipMessage = "Only one file may be uploaded per item.";
    $dynamicFunctionName = "showFileUploadSuccess_" .$questionNum;
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
                <?php if($canModify) { ?>
                    <input class="form-control file ays-ignore" type="file" id="<?php echo($name); ?>" name="<?php echo($name); ?>">
                <?php } else { ?>
                    <input class="form-control file ays-ignore" type="file" id="<?php echo($name); ?>" name="<?php echo($name); ?>" disabled="true">
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
                                   document.getElementById('<?php echo($uploadSuccess)?>'))">
                    </div>
                </div>
                <div class="row text-right text-info tdc-display-none" id="<?php echo($uploadSuccess) ?>"><strong>File Successfully Uploaded</strong></div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        $('<?php echo('#'.$name); ?>').fileinput({
            <?php if(isset($fileName) && (strlen($fileName) > 0)){ ?>
                'showUpload' : false,
                'showCaption' : true,
                'showPreview' : false,
                'browseLabel' : 'Select File',
                'initialCaption' : '<?php echo($fileName); ?>'
            <?php }else { ?>
                'showUpload' : false,
                'showCaption' : true,
                'showPreview' : false,
                'browseLabel' : 'Select File'
            <?php } ?>
        });

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