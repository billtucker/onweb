<?php
/**
 * Created by IntelliJ IDEA.
 * User: Bill
 * Date: 11/3/2015
 * Time: 10:11 AM
 */

$port = ($_SERVER['SERVER_PORT'] == '80' ? "http://" : "https://" );
$siteRoot = "onweb";
$sitePrefix = $port .$_SERVER[HTTP_HOST] ."/" .$siteRoot ."/";