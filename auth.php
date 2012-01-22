<?php

include "twitter-async/EpiCurl.php";
include "twitter-async/EpiOAuth.php";
include "twitter-async/EpiTwitter.php";
include "db.class.php";
include "config.php";
include "secret.php";

function SetUpAuthorisation($oauth_token) {
    $twitterObj = new EpiTwitter(CONSUMER_KEY, CONSUMER_SECRET);

    $twitterObj->setToken($oauth_token);
    $token = $twitterObj->getAccessToken();
    $twitterObj->setToken($token->oauth_token, $token->oauth_token_secret);

    try {
        $twitterInfo = $twitterObj->get("/account/verify_credentials.json");
    } catch (Exception $e) {
        header("Location: sendtweet.php");
        die("ERROR" . $e);
    }

    $twitterID = $twitterInfo->id;

    setcookie("twitterid", $twitterID);
    setcookie("oauth_token", $token->oauth_token);
    setcookie("oauth_token_secret", $token->oauth_token_secret);
}

try {
    // Being pased back from Twitter, time to authenticate and get access
    SetUpAuthorisation($_GET["oauth_token"]);
} catch (EpiTwitterException $e) {
    echo "We caught an EpiOAuthException";
    echo $e->getMessage();
    header("Location: sendtweet.php");
    die("Something went wrong. Please email bugs@omnisoft.co.uk");
} catch (Exception $e) {
    echo "We caught an unexpected " . $e->__toString();
    echo $e->getMessage();
    header("Location: sendtweet.php");
    die("Something went wrong. Please email bugs@omnisoft.co.uk");
}

if ($_COOKIE["broadcast"] == true) {
    header("Location: broadcast.php");
    die();
}

if (isset($_COOKIE["listid"]) && isset($_COOKIE["message"])) {
    $listid = $_COOKIE["listid"];
    $message = $_COOKIE["message"];

    header("Location: sendtweet.php?listid=$listid&message=$message");
}

header("Location: sendtweet.php");
?>