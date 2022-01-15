<?php

// function from https://stackoverflow.com/a/32875341
function SendMessage($messaggio)
{
    global $setup;

    $url = "https://api.telegram.org/bot" . $setup["bot_token"] . "/sendMessage?chat_id=" . $setup["chat_id"];
    $url = $url . "&text=" . urlencode($messaggio);
    $ch = curl_init();
    $optArray = array(
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true
    );
    curl_setopt_array($ch, $optArray);
    $result = curl_exec($ch);
    curl_close($ch);
    return $result;
}

function ReadJson($setup_filename)
{
    $json_content_str = file_get_contents($setup_filename); // check error?
    $setup = json_decode($json_content_str, true);

    if (
        $setup === null
        && json_last_error() !== JSON_ERROR_NONE
    ) {
        exit("$setup_filename is malformed.");
    }

    return $setup;
}

function GetXpath($url)
{
    $html = file_get_contents($url);
    if ($html === false) {
        exit("Request to $url failed.");
    }

    $doc = new DOMDocument();
    if ($doc->loadHTML($html) === false) {
        exit("Loading html failed.");
    }

    return new DOMXpath($doc);
}

function CheckChanges($xpaths)
{
    $changes_filename = "";
    $last_check = file_get_contents($changes_filename);
    $no_errors = file_put_contents($changes_filename, $xpaths); // change
    if ($no_errors === false) {
        exit("Unable to write changes in $changes_filename.");
    }

    $found_changes = false;

    if ($last_check === false) {
        // file not found
        $xpaths = array_map(function ($elem) {
            $elem["changed"] = true;
            return $elem;
        }, $xpaths);
    } else {
        // check if that changed or not
        // $xpaths = array_map(function ($key, $elem) {
        //     $elem["changed"] = true;
        //     return $elem;
        // }, $xpaths);
    }

    if($found_changes) {
        return $xpaths;
    } else {
        return false;
    }
}

function GenerateMessage($xpath)
{
    global $setup;
    $msg = "";

    if (isset($setup["check_changes"]) && $setup["check_changes"]) {
        $xpaths = CheckChanges($setup["xpaths"]);
        if ($xpaths === false) {
            return "";
        }
        $msg = "Variables that changed:\n";
        foreach ($xpaths as $key => $xpath_node) {
            if ($xpath_node["changed"]) {
                $node = $xpath->query($xpath_node);
                $msg .= "$key: {$node[0]->nodeValue} \n";
            }
        }

        $msg .= "\n\nVariables that didn't changed:\n";
        foreach ($xpaths as $key => $xpath_node) {
            if (!$xpath_node["changed"]) {
                $node = $xpath->query($xpath_node);
                $msg .= "$key: {$node[0]->nodeValue} \n";
            }
        }
    } else {
        foreach ($setup["xpaths"] as $key => $xpath_node) {
            $node = $xpath->query($xpath_node);
            $msg .= "$key: {$node[0]->nodeValue} \n";
        }
    }

    return $msg;
}

$setup = ReadJson("");
$xpath = GetXpath($setup["url"]);
$msg = GenerateMessage($xpath);
if ($msg != "" && $setup["notify_telegram"]) {
    if (
        !isset($setup["bot_token"]) || $setup["bot_token"] == ""
        || !isset($setup["chat_id"]) || $setup["chat_id"] == ""
    ) {
        exit("chat_id and bot_token are necessary if you want to send changes by Telegram.");
    }
    SendMessage($msg);
} else {
    print($msg);
}
