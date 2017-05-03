<?php
/**
 * Created by Intellij User: billt
 * Date: 4/14/2017
 * Time: 3:07 PM
 *
 * Function to support the build of the container object type loaded in the FileMaker container
 * Note: Take information and present that to the user based on what detected within the container dynamically
 *
 */

include_once dirname(__DIR__) .DIRECTORY_SEPARATOR ."onspot-config.php";


/**
 * The spotviewer will load the appropriate type to support display of the object loaded in the FileMaker Container
 * on the [WEB] cwp_spotviewer_browse (Rough Cut) layout. The processDisplayType method will call out the required
 * method to echo supporting type.
 * @param $url the url to object loaded in FileMaker Container
 * @param $type the type audio, video, image, or application
 * @param $sourceType if required audio/mp3 or video/mp4
 */
function processDisplayType($url, $type, $sourceType, $downloadLink, $fullVideoLink){
    global $log;

    $log->debug("Working type: " .$sourceType ." with URL: " .$url ." Type is: " .$type);
    switch($type){
        case "audio":
            buildAudioType($url, $sourceType, $downloadLink, $fullVideoLink);
            break;
        case "video":
            buildVideoType($url, $sourceType, $downloadLink, $fullVideoLink);
            break;
        case "image":
            buildImageType($url);
            break;
        case "application":
            buildApplicationType($url);
            break;
        case "unknown": //we were unable to get a filename or extension so tell user we cannot display information
            $log->debug("Found an unknow type so now just present a error unable to decode");
            buildUnknownType();
            break;
        default:
            $log->error("No defined type for this type: " .$type);
            return;
            break;
    }
}

/**
 * Method to build a video block
 * @param $url the url to object loaded in FileMaker Container
 * @param $sourceType $sourceType if required audio/mp3 or video/mp4
 */
function buildVideoType($url, $sourceType, $downloadLink, $fullVideoLink){
    global $log;

    echo "<video width='100%' controls><!--Make the video fill in all the space allocated -->" .PHP_EOL;
    echo "<source src='$url' type='$sourceType'>" .PHP_EOL;
    echo "Your browser does not support displaying HTML5 video." .PHP_EOL;
    echo "</video>" .PHP_EOL;
    downloadLinkStatus($downloadLink, $fullVideoLink);
    //echo "</div><!-- Ending of DIV(s) for video Not sure about this -->" .PHP_EOL;
}

/**
 * Method to build a audio block
 * @param $url the url to object loaded in FileMaker Container
 * @param $sourceType $sourceType if required audio/mp3 or video/mp4
 */
function buildAudioType($url, $sourceType, $downloadLink, $fullVideoLink){
    writeAudioLinkDivSpacer();
    echo "<div class='row'><!-- Start of special row to apply audio display formating -->" .PHP_EOL;
    echo "<div class=\"col-md-3 col-xs-12\"></div>" .PHP_EOL;
    echo "<div class='col-md-6 col-xs-12 video-audio-border text-center'>" .PHP_EOL;
    //TODO: take this style and add it to the main CSS file
    echo "<audio width='100%' controls style='margin-top: 5px;'>" .PHP_EOL;
    echo "<source src='$url' type='$sourceType'>" .PHP_EOL;
    echo "</audio>" .PHP_EOL;
    downloadLinkStatus($downloadLink, $fullVideoLink);
    echo "</div><!-- Some end div that seems to work not sure why -->" .PHP_EOL;
    echo "<div class=\"col-md-3 col-xs-12\"></div><!-- Last of empty columns foir audio display -->" .PHP_EOL;
    echo "</div><!-- add this div to fix audio display issues. -->";
}

function buildUnknownType(){
    writeAudioLinkDivSpacer();
    echo "<div class='row'><!-- Start of special row to apply audio display formating -->" .PHP_EOL;
    echo "<div class=\"col-md-3 col-xs-12\"></div>" .PHP_EOL;
    echo "<div class='col-md-6 col-xs-12 video-audio-border text-center'>" .PHP_EOL;
    echo "" .PHP_EOL;
    echo "<h4 class='text-center text-danger'>Unable to display file from Rough Cut tab in Project</h4>" .PHP_EOL;
    echo "</div><!-- Some end div that seems to work not sure why -->" .PHP_EOL;
    echo "<div class=\"col-md-3 col-xs-12\"></div><!-- Last of empty columns foir audio display -->" .PHP_EOL;
    echo "</div><!-- add this div to fix audio display issues. -->";
    //**
//    echo "<div class='img-responsive tdc-spotviewer-image'>" .PHP_EOL;
//    echo "<h4 class='text-center'>Unable to determine type of object in rough cut</h4>" .PHP_EOL;
//    echo "</div>" .PHP_EOL;
}

/**
 * Method to build a Image block
 * @param $url the url to object loaded in FileMaker Container
 */
function buildImageType($url){
    echo "<div class='img-responsive tdc-spotviewer-image'>" .PHP_EOL;
    echo "<img class='ignore_button' src='$url' alt='Rough Cut Image'>" .PHP_EOL;
    echo "</div>" .PHP_EOL;
}

/**
 * Method to build a document or link block with launch in new browser tab
 * @param $url the url to object loaded in FileMaker Container
 */
function buildApplicationType($url){
    writeAudioLinkDivSpacer();
    echo "<div class='row'><!-- Start of special row to apply audio or image link display formating -->" .PHP_EOL;
    echo "<div class=\"col-md-3 col-xs-12\"></div>" .PHP_EOL;
    echo "<div class='col-md-6 col-xs-12 video-audio-border text-center'>" .PHP_EOL;
    echo "<a class='ignore_button text-center' href='$url' target='_blank' style='margin-top: 10px; margin-bottom: 10px;'>Document From Rough Cut</a>" .PHP_EOL;
    echo "<div class=\"col-md-3 col-xs-12\"></div><!-- Last of empty columns foir audio display -->" .PHP_EOL;
    echo "</div><!-- add this div to fix audio display issues. -->";
    echo "</div><!-- End of second row definition  -->" .PHP_EOL;
}


function downloadLinkStatus($downloadLink, $fullVideoLink){
    if ($downloadLink) {
        echo "<div style='font-size:10px;'>" .PHP_EOL;
    } else {
        echo "<div style='display: none;'>" .PHP_EOL;
    }
    echo "<a href='$fullVideoLink'>$fullVideoLink</a>" .PHP_EOL;
    echo "</div><!-- downloadlinkstatus method == end of the video link <a> div to set font size -->" .PHP_EOL;
}

function writeAudioLinkDivSpacer(){
    echo "<div class='row'>" .PHP_EOL;
    echo "<div class='col-md-4 col-xs-12 divider-10'>&nbsp;</div>" .PHP_EOL;
    echo "<div class='col-md-4 col-xs-12 divider-10'>&nbsp;</div>" .PHP_EOL;
    echo "<div class='col-md-4 col-xs-12 divider-10'>&nbsp;</div>" .PHP_EOL;
    echo "</div>" .PHP_EOL;
}