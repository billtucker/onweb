<?php
/**
 * Created by IntelliJ IDEA.
 * User: billt
 * Date: 4/14/2017
 * Time: 3:07 PM
 *
 * Function to support the build of the container object type loaded in the FileMaker container
 *
 * Note: as of page code creation no formatting (CSS) has been applied but will be at later time
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
function processDisplayType($url, $type, $sourceType){
    global $log;
    switch($type){
        case "audio":
            buildAudioType($url, $sourceType);
            break;
        case "video":
            buildVideoType($url, $sourceType);
            break;
        case "image":
            buildImageType($url);
            break;
        case "application":
            buildApplicationType($url);
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
function buildVideoType($url, $sourceType){
    echo "<video width='100%' controls><!--Make the video fill in all the space allocated -->" .PHP_EOL;
    echo "<source src='$url' type='$sourceType'>" .PHP_EOL;
    echo "Your browser does not support displaying HTML5 video." .PHP_EOL;
    echo "</video>" .PHP_EOL;
}

/**
 * Method to build a audio block
 * @param $url the url to object loaded in FileMaker Container
 * @param $sourceType $sourceType if required audio/mp3 or video/mp4
 */
function buildAudioType($url, $sourceType){
    echo "<audio width='100%' controls>" .PHP_EOL;
    echo "<source src='$url' type='$sourceType'>" .PHP_EOL;
    echo "</audio>" .PHP_EOL;
}

/**
 * Method to build a Image block
 * @param $url the url to object loaded in FileMaker Container
 */
function buildImageType($url){
    echo "<div class='tdc-spotviewer-image'>";
    echo "<img class='ignore_button' src='$url' alt='Rough Cut Image'>" .PHP_EOL;
    echo "</div>";
}

/**
 * Method to build a document or link block with launch in new browser tab
 * @param $url the url to object loaded in FileMaker Container
 */
function buildApplicationType($url){
    echo "<a class='ignore_button' href='$url' target='_blank'>Document</a>" .PHP_EOL;
}