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
 * @param $url - String URL
 * @param $type - String sttribute Application, Image, Audio, Video
 * @param $sourceType - Source Type setting for object display
 * @param $downloadLink - URL to FileMaker Location
 * @param $fullVideoLink - URL to object
 * @param $linkFilename - Actual file name of store object from FileMaker calculation field
 */
function processDisplayType($url, $type, $sourceType, $downloadLink, $fullVideoLink, $linkFilename){
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
            buildImageType($url, $linkFilename);
            break;
        case "application":
            buildApplicationType($url, $linkFilename);
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

    echo "<video width='100%' controls controlsList='nodownload'><!--Make the video fill in all the space allocated -->" .PHP_EOL;
    echo "<source src='$url' type='$sourceType'>" .PHP_EOL;
    echo "Your browser does not support displaying HTML5 video." .PHP_EOL;
    echo "</video>" .PHP_EOL;
    downloadLinkStatus($downloadLink, $fullVideoLink);
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
    echo "<audio width='100%' controls style='margin-top: 5px;' controlsList='nodownload'>" .PHP_EOL;
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
function buildImageType($url, $linkFilename){
    global $log;

    echo "<div class='img-responsive tdc-spotviewer-image'>" .PHP_EOL;
    $log->debug("Image - Test if linkFilename is set: " .$linkFilename);

    if(empty($linkFilename)){
        echo "<img class='ignore_button' src='$url' alt='Rough Cut Image'>" .PHP_EOL;
    }else{
        echo "<img class='ignore_button' src='$url' alt='$linkFilename'>" .PHP_EOL;
    }

    echo "</div>" .PHP_EOL;
}

/**
 * Method to build a document or link block with launch in new browser tab. This function now 05-11-2017 also processes
 * straight links to YouTube and Vimeo videos. A playable link will appear to launch the external site player.
 * @param $url the url to object loaded in FileMaker Container
 */
function buildApplicationType($url, $linkFilename){
    global $log;

    writeAudioLinkDivSpacer();
    echo "<div class='row'><!-- Start of special row to apply audio or image link display formating -->" .PHP_EOL;
    echo "<div class=\"col-md-3 col-xs-12\"></div>" .PHP_EOL;
    echo "<div class='col-md-6 col-xs-12 video-audio-border text-center'>" .PHP_EOL;
    $log->debug("Test For linkFilename: " .$linkFilename);
    if(empty($linkFilename)){
        echo "<a class='ignore_button text-center' href='$url' target='_blank' style='margin-top: 10px; margin-bottom: 10px;'>Click to view</a>" .PHP_EOL;
        echo "<p class='text-center'>" .$url ."</p>" .PHP_EOL;
    }else{
        echo "<a class='ignore_button text-center' href='$url' target='_blank' style='margin-top: 10px; margin-bottom: 10px;'>$linkFilename</a>" .PHP_EOL;
    }
    echo "</div><!-- End of video, audio, document MD-6 column DIV -->" .PHP_EOL;
    echo "<div class=\"col-md-3 col-xs-12\"></div><!-- Last of empty columns foir audio display -->" .PHP_EOL;
    echo "</div><!-- End of second row definition  -->" .PHP_EOL;
}

/**
 * A function to display download link to users who access to access the object outside the application
 * @param $downloadLink - boolean true yes enable
 * @param $fullVideoLink - string link to object directly
 */
function downloadLinkStatus($downloadLink, $fullVideoLink){
    if ($downloadLink) {
        echo "<div style='font-size:10px;'>" .PHP_EOL;
    } else {
        echo "<div style='display: none;'>" .PHP_EOL;
    }
    echo "<a href='$fullVideoLink'>$fullVideoLink</a>" .PHP_EOL;
    echo "</div><!-- downloadlinkstatus method == end of the video link <a> div to set font size -->" .PHP_EOL;
}

/**
 * function to generate enpty columns to take up space for centering of object display to user
 */
function writeAudioLinkDivSpacer(){
    echo "<div class='row'>" .PHP_EOL;
    echo "<div class='col-md-4 col-xs-12 divider-10'>&nbsp;</div>" .PHP_EOL;
    echo "<div class='col-md-4 col-xs-12 divider-10'>&nbsp;</div>" .PHP_EOL;
    echo "<div class='col-md-4 col-xs-12 divider-10'>&nbsp;</div>" .PHP_EOL;
    echo "</div>" .PHP_EOL;
}