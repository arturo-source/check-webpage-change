<?php 

// function from https://stackoverflow.com/a/32875341
function sendMessage($messaggio) {
    $chatID = "";
    $token = "";

    $url = "https://api.telegram.org/bot" . $token . "/sendMessage?chat_id=" . $chatID;
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

$url = "";
$xpath_nodes = [];

$html = file_get_contents($url);
$doc  =	new DOMDocument();
@$doc->loadHTML($html);
$xpath = new DOMXpath($doc);

$msg = "";
foreach ($xpath_nodes as $xpath_node) {
    $node = $xpath->query($xpath_node);
    $msg .= $node[0]->nodeValue . "\n";
}

sendMessage($msg);
