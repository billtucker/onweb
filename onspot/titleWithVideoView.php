<?php
/**
 * This page displays spot header information and the HTML5 block. This page will always appear if the user
 * has OnSpotViewer privilege
 *
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 7/17/2015
 * Time: 3:24 PM
 *
 * Refactor Notes:
 *
 */

if(!session_id()){
    session_start();
}

?>

<div class="clearfix">&nbsp;</div>
<!-- TODO Should we pull this static table from the I or OAP_PREFS table -->
<!-- Start titleWithVideoView HTML Code -->
<div class="container-fluid tdc-spotviewer-div-margin"><!-- Start Page Title -->
    <div class="row">
        <table class="table table-bordered table-responsive">
            <tr>
                <td class="col-md-1 col-xs-1 tableTDHeaderDef add_bold_upper">W.O.#</td>
                <td class="col-md-1 col-xs-1 tableTDHeaderDef add_bold_upper">Prj#</td>
                <td class="col-md-3 col-xs-3 tableTDHeaderDef add_bold_upper">Show Title</td>
                <td class="col-md-5 col-xs-5 tableTDHeaderDef add_bold_upper">Project Title:</td>
                <td class="col-md-2 col-xs-2 tableTDHeaderDef add_bold_upper">Writer Producer:</td>
            </tr>
            <tr>
                <td>
                    <?php
                    $wo = $record->getField('Work_Order_Num_n');
                    if(empty($wo)){
                        echo "&nbsp;";
                    }else{
                        echo $wo;
                    }
                    ?>
                </td>
                <td>
                    <?php
                    $prjNum = $record->getField('Tag_Group_Number_n');
                    if(empty($prjNum)){
                        echo "&nbsp;";
                    }else{
                        echo $prjNum;
                    }
                    ?>
                </td>
                <td>
                    <?php
                    $showTitle = $record->getField('Show_Title_ct');
                    if(empty($showTitle)){
                        echo "&nbsp;";
                    }else{
                        echo $showTitle;
                    }

                    ?>
                </td>
                <td>
                    <?php
                    $name = $record->getField('Work_Order_Title_t');
                    if(empty($name)){
                        echo "&nbsp";
                    }else{
                        echo $name;
                    }
                    ?>
                </td>
                <td>
                    <?php
                    $wp = $record->getField('Writer_Producer_t');
                    if(empty($wp)){
                        echo "&nbsp;";
                    }else{
                        echo $wp;
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>
</div><!-- End Page Title -->
<div class="clearfix"></div>
<div class="container-fluid tdc-spotviewer-div-margin"><!-- Start Player DIV -->
    <div class="row">
        <div class="col-md-12 col-xs-12">
            <video height="100%" width="100%" controls>
                <source src="<?php echo $fullPath_ct ?>">
                Your browser does not support displaying HTML5 video.
            </video>
            <?php
                if(isset($downloadLink) && $downloadLink){
                    echo "<div style='font-size:10px;'><!-- Video Link Start -->";
                }else{
                    echo "<div style='display: none;'><!-- Video Link Start hidden  -->";
                }
            ?>
                <a href="<?php echo $fullPath_ct ?>">
                    <?php echo $fullPath_ct ?>
                </a>
            </div><!-- Video Link End -->
        </div>
    </div>
</div><!-- End Player DIV -->
<div class="clearfix">&nbsp;</div>
<!-- End titleWithVideoView HTML Code -->
