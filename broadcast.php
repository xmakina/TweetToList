<?php

$message = "";
$listid = -1;

if ($_COOKIE["broadcast"] == true) {
    $listid = $_COOKIE["listid"];
    $message = $_COOKIE["message"];
    
    // However allow override with GET
    if(isset($_GET["message"])) $message = $_GET["message"];
    if(isset($_GET["listid"])) $listid = $_GET["listid"];
} else {
    if (!isset($_GET["listid"]) || !isset($_GET["message"])
            || !is_numeric($_GET["listid"])) {
        header("location:sendtweet.php?error=nogets");
        die();
    }
    $message = $_GET["message"];
    $listid = $_GET["listid"];
}

$message = urldecode($message);

if (strlen($message) < 10) {
    header("location:sendtweet.php?error=shortmessage");
    die();
}

if(strlen($message) > 124) {
    header("location:sendtweet.php?error=longmessage");
    die();
}

setcookie("broadcast", true);
setcookie("listid", $listid);
setcookie("message", $message);

include "twitter-async/EpiCurl.php";
include "twitter-async/EpiOAuth.php";
include "twitter-async/EpiTwitter.php";
include "db.class.php";
include "config.php";
include "secret.php";
include "SetUpTwitterObject.php";

$twitterObj = SetUpTwitterObject();

try {
    $twitterInfo = json_decode($twitterObj->get("/account/verify_credentials.json")->responseText, true);
} catch (Exception $e) {
    // User is not authenticated, bounce over to the relevant screen
    header("Location:" . $twitterObj->getAuthorizeUrl());
}

$db = new db($mysql_host, $mysql_user, $mysql_password, $mysql_database, false);

// has the user sent a message to this list in the last 24 hours
$lastBroadcast = $db->select("date", "users_lists", "twitterid = " . $twitterInfo["id"] . " AND listid = $listid AND date > " . strtotime("-1 days"));

if (mysql_num_rows($lastBroadcast) > 0) {
    header("location:sendtweet.php?error=alreadysent&listid=$listid");
    die();
}

$listDetails = $db->select("slug, owner_screen_name", "lists", "id=$listid");

$listDetails = mysql_fetch_array($listDetails);

try {
    $listMembers = json_decode($twitterObj->get("/lists/members.json?slug=" . $listDetails["slug"] . "&owner_screen_name=" . $listDetails["owner_screen_name"])->responseText, true);
} catch (Exception $e) {
    die($e->getMessage());
}

$listMembers = $listMembers["users"];

try {
    for ($i = 0; $i < count($listMembers); $i++) {
        $twitterObj->post_statusesUpdate(array("status" => "@" . $listMembers[$i]["screen_name"] . " $message"));
    }
} catch (EpiTwitterNotAuthorizedException $e) {
    die("EpiTwitterNotAuthorizedException $e");
} catch (Exception $e) {
    die("GENERIC $e");
}

// Tweets sent, ban the user from sending again to this list
$db->insert("users_lists", $twitterInfo["id"] . ", $listid, " . time());

setcookie("broadcast", false);

header("location:sendtweet.php?sent=1");
?>