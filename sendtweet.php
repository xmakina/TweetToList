<?php
include "twitter-async/EpiCurl.php";
include "twitter-async/EpiOAuth.php";
include "twitter-async/EpiTwitter.php";
require_once "db.class.php";
include "config.php";
include "secret.php";
include "SetUpTwitterObject.php";

$db = new db($mysql_host, $mysql_user, $mysql_password, $mysql_database, false);
?>

<!--
To change this template, choose Tools | Templates
and open the template in the editor.
-->
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link type="text/css" rel="stylesheet" href="style.css" />
        <title>Send a Tweet to a List</title>
        <script type="text/javascript">
            function CreateLink() {
                list = document.getElementById("list");
                listid = list[list.selectedIndex].value;
                message = encodeURIComponent(document.getElementById("message").value);
                
                linkbox = document.getElementById("linkbox");
                linkbox.style.visibility = "visible";
                linkbox.value="http://omnisoft.co.uk/TweetToList/sendtweet.php?listid=" + listid + "&message=" + message;
                
                window.prompt ("Copy to clipboard: Ctrl+C, Enter", linkbox.value);
            }
            
            /// Replaces commonly-used Windows 1252 encoded chars that do not exist in ASCII or ISO-8859-1 with ISO-8859-1 cognates.
            function ReplaceWordChars(text) {
                var s = text;
                // smart single quotes and apostrophe
                s = s.replace(/[\u2018|\u2019|\u201A]/g, "\'");
                // smart double quotes
                s = s.replace(/[\u201C|\u201D|\u201E]/g, "\"");
                // ellipsis
                s = s.replace(/\u2026/g, "...");
                // dashes
                s = s.replace(/[\u2013|\u2014]/g, "-");
                // circumflex
                s = s.replace(/\u02C6/g, "^");
                // open angle bracket
                s = s.replace(/\u2039/g, "<");
                // close angle bracket
                s = s.replace(/\u203A/g, ">");
                // spaces
                s = s.replace(/[\u02DC|\u00A0]/g, " ");
    
                return s;
            }
            
            function CheckURL() { 
                if(window.location.href.indexOf("#") > 0){
                    var message = window.location.href.substr(window.location.href.indexOf("message=")+8);
                    message = decodeURIComponent(message);
                    while(message != message.replace("+", " ")) {
                        message = message.replace("+", " ");
                    }
                    
                    message = ReplaceWordChars(message);
                    
                    message = encodeURIComponent(message);
                    
                    list = document.getElementById("list");
                    listid = list[list.selectedIndex].value;
                    message ="http://omnisoft.co.uk/TweetToList/sendtweet.php?listid=" + listid + "&message=" + message;
                    window.location.replace(message);
                }
            }
        </script>
    </head>
    <body onload="CheckURL()">
        <div id="container">
            <div id="header">Tweet To List</div>
            <div id="content">
                <?php
                if ($_GET["sent"] == 1) {
                    echo("<div class=\"success\">");
                    echo("Tweet sent!");
                    echo("</div>");
                }

                if (isset($_GET["error"])) {
                    $errorCode = $_GET["error"];
                    echo("<div class=\"error\">");
                    switch ($errorCode) {
                        case "shortmessage":
                            echo("Please provide a message longer than 10 chars");
                            break;
                        case "alreadysent":
                            echo("You've already sent a message to this list in the last 24 hours");
                            break;
                    }
                    echo("</div>");
                }
                ?>

                <form action="broadcast.php" method="get">
                    <label for="list">List:</label>
                    <select name="listid" id="list">
                        <?php
                        $lists = $db->select("id, slug, owner_screen_name", "lists", "1 = 1");

                        if (mysql_num_rows($lists) == 0) {
                            echo("<option>No Lists Available</option>");
                        }

                        while ($row = mysql_fetch_array($lists)) {
                            echo("<option value=" . $row["id"]);
                            if ($_GET["listid"] == $row["id"]) {
                                echo (" selected=true");
                            }
                            echo(">");

                            echo($row["owner_screen_name"] . "/" . $row["slug"]);

                            echo("</option>");
                        }
                        ?>
                    </select><br />
                    <label for="message">Message:</label>
                    <input name="message" id="message" type="text" size="110" maxlength="124" value="<?php echo($_GET["message"]) ?>" /><br />
                    <input type="submit" value="Tweet To List" onclick="this.disabled=true; this.value='Sending…'; this.form.submit();"></input>
                </form>
                <p>IMPORTANT: You can only tweet to a list once every 24 hours, so make it count!</p>
                <h2>Share</h2>
                <p>Want to share with others? Choose a list, enter your message and click:</p>
                <input type="submit" value="Create Link" onclick="CreateLink()" />
                <input type="text" id="linkbox" style="visibility: hidden;" />
            </div>
            <div id="footer">
                <p>Have any feedback? <a href="https://twitter.com/#!/xmakina" target="_blank">Tell me on Twitter</a></p>
                <p>Want to add a list? Email <a href="mailto:tweettolist@omnicronsoftware.co.uk?subject=New List">tweettolist@omnicronsoftware.co.uk</a> and we'll be in touch!</p>
            </div>
        </div>
    </body>
</html>
