<?php

require_once("db.class.php");

function Authenticate() {
    if (isset($_GET["listid"]) && isset($_GET["message"])) {
        $listid = $_GET["listid"];
        $message = urlencode($_GET["message"]);

        // ListId and Message were set, remember them
        setcookie("listid", $listid);
        setcookie("message", $message);
    }

    $twitterObject = new EpiTwitter(CONSUMER_KEY, CONSUMER_SECRET);
    header("Location: " . $twitterObject->getAuthenticateUrl());
    die();
}

function SetUpTwitterObject() {

    if (!isset($_COOKIE["twitterid"]) || !isset($_COOKIE["oauth_token"]) || !isset($_COOKIE["oauth_token_secret"])) {
        // Cookies not set, authenticate
        $twitterObject = new EpiTwitter(CONSUMER_KEY, CONSUMER_SECRET);
        Authenticate();
    }

    $twitterObject = new EpiTwitter(CONSUMER_KEY, CONSUMER_SECRET,
                    $_COOKIE["oauth_token"], $_COOKIE["oauth_token_secret"]);

    try {
        $twitterInfo = $twitterObject->get("/account/verify_credentials.json");
    } catch (Exception $e) {
        Authenticate();
    }

    return $twitterObject;
}

?>
