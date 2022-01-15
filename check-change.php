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
    $json_content_str = file_get_contents($setup_filename);
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

function GenerateMessage($xpath)
{
    global $setup;
    $msg = "";

    foreach ($setup["xpaths"] as $key => $xpath_node) {
        $node = $xpath->query($xpath_node);
        $msg .= "$key: {$node[0]->nodeValue} \n";
    }

    return $msg;
}

$setup = ReadJson("");
$xpath = GetXpath($setup["url"]);
$msg = GenerateMessage($xpath);
SendMessage($msg);
