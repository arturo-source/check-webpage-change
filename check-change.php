<?php

// function from https://stackoverflow.com/a/32875341
function SendMessage($messaggio)
{
    global $settings;

    $url = "https://api.telegram.org/bot" . $settings["bot_token"] . "/sendMessage?chat_id=" . $settings["chat_id"];
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

function ReadJson($settings_filename)
{
    $json_content_str = file_get_contents($settings_filename);
    if ($json_content_str === false) {
        exit("$json_content_str is missing.");
    }

    $settings = json_decode($json_content_str, true);
    if (
        $settings === null
        && json_last_error() !== JSON_ERROR_NONE
    ) {
        exit("$settings_filename is malformed.");
    }

    return $settings;
}

function GetXpath($url)
{
    $html = file_get_contents($url);
    if ($html === false) {
        exit("Request to $url failed.");
    }

    $doc = new DOMDocument();
    // remove @ if you want to see ugly things
    if (@$doc->loadHTML($html) === false) {
        exit("Loading html failed.");
    }

    return new DOMXpath($doc);
}

function CheckChanges(object $xpath, array $xpaths)
{
    global $dir_path;

    $changes_filename = $dir_path . "last_check.json";
    $found_changes = false;
    $nodes = array();
    $last_check = file_get_contents($changes_filename);

    if ($last_check === false) {
        // file not found
        $found_changes = true;
        foreach ($xpaths as $key => $xpath_node) {
            $node = $xpath->query($xpath_node);
            $nodes[$key] = ["changed" => true, "value" => $node[0]->nodeValue];
        }
    } else {
        $prev_values = json_decode($last_check, true);

        // check whether changed or not
        foreach ($xpaths as $key => $xpath_node) {
            $node = $xpath->query($xpath_node);
            $node_value = trim($node[0]->nodeValue);
            $changed = $prev_values[$key]["value"] != $node_value;

            $nodes[$key] = ["changed" => $changed, "value" => $node_value];
            $found_changes |= $changed;
        }
    }

    $no_errors = file_put_contents($changes_filename, json_encode($nodes));
    if ($no_errors === false) {
        exit("Unable to write changes in $changes_filename.");
    }

    if ($found_changes) {
        return $nodes;
    } else {
        return false;
    }
}

function GenerateMessage($xpath)
{
    global $settings;
    $msg = "";

    if (isset($settings["check_changes"]) && $settings["check_changes"]) {
        $nodes = CheckChanges($xpath, $settings["xpaths"]);
        if ($nodes === false) {
            return "";
        }
        $msg = "Variables that changed:\n";
        foreach ($nodes as $key => $node) {
            if ($node["changed"]) {
                $msg .= "$key: {$node['value']}\n";
            }
        }

        $msg .= "\n\nVariables that didn't change:\n";
        foreach ($nodes as $key => $node) {
            if (!$node["changed"]) {
                $msg .= "$key: {$node['value']}\n";
            }
        }
    } else {
        foreach ($settings["xpaths"] as $key => $xpath_node) {
            $node = $xpath->query($xpath_node);
            $msg .= "$key: {$node[0]->nodeValue}\n";
        }
    }

    return $msg;
}

$dir_path = dirname(__FILE__) . "/";
$settings = ReadJson($dir_path . "settings.json");
$xpath = GetXpath($settings["url"]);
$msg = GenerateMessage($xpath);
if ($msg != "" && $settings["notify_telegram"]) {
    if (
        !isset($settings["bot_token"]) || $settings["bot_token"] == ""
        || !isset($settings["chat_id"]) || $settings["chat_id"] == ""
    ) {
        exit("chat_id and bot_token are necessary if you want to send changes by Telegram.");
    }
    $response = SendMessage($msg);
    print_r($response);
} else {
    print($msg);
}
