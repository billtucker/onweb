<?php
/**
 * Created by IntelliJ IDEA.
 * User: billt
 * Date: 10/10/2016
 * Time: 10:24 AM
 */

include_once($_SERVER["DOCUMENT_ROOT"] ."/onweb" ."/onweb-config.php");

$fullVideoPath = "\\\\taf-file\\Jobmasters\\OAP_test\\hugo.mov";
$pathToRootDir = "\\\\taf-file\\Jobmasters\\OAP_test";

$targetFile = "hugo.mov";
$foundFile = false;
$searchFileCounter = 0;

$log->debug("Start Test using path: " .$pathToRootDir ." Searching for file: " .$targetFile);

//if($uncDir = opendir($pathToRootDir)){
//    while(($file == readdir($uncDir)) !== false){
//        if($file == '.' || $file == '..'){
//            continue;
//        }
//    }
//}


foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathToRootDir)) as $file){
    if($file->getFilename() != '.' || $file->getFilename() != '..'){
        $fileCount++;
        if($file->getFilename() == $targetFile){
            $fSize = $file->getSize();
            $pathToFile = $file->getPathname();
            //echo("-- Size of file attribute: " .$fileSize .PHP_EOL);
            $result = array("status" => "success","filename" => $filename, "path" => $file->getPathname());
            $foundFile = true;
            //echo json_encode($result) .PHP_EOL;
            //echo "Total file count: " .$fileCount .PHP_EOL;
            //echo("We found our file now PHP exit()") .PHP_EOL;
            exit();
        }
    }
}

$log->debug("Finished test now display results in HTML");

?>

<html>
<head>
    <title>Test Open UNC Path</title>
</head>
<body>
    <br>
    <p>Error Messages</p>
    <pre>
        <?php
            if($foundFile){
                echo "<p> We found the file Movie file: " .$targetFile ." With File Size: " .$fSize ."</p>";
                echo "<p>At Path: " .$pathToFile ."</p>";
            }else{
                echo "<p>Unable to find file: " .$targetFile ."</p>";
            }
        ?>
    </pre>
</body>
</html>
