<?php
// Configuration
$portal = "tv.fusion4k.cc";
$mac = "00:1A:79:00:02:2B";
$user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Step 1: Handshake
$handshakeUrl = "http://$portal/stalker_portal/server/load.php?type=stb&action=handshake&prehash=false&JsHttpRequest=1-xml";

$headers = [
    "Cookie: mac=$mac; stb_lang=en; timezone=GMT",
    "X-Forwarded-For: $user_ip",
    "Referer: http://$portal/stalker_portal/c/",
    "User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3",
    "X-User-Agent: Model: MAG250; Link:"
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $handshakeUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$response = curl_exec($ch);
curl_close($ch);

$data = json_decode($response, true);
if (!isset($data['js']['token'])) {
    echo json_encode(['error' => 'Handshake failed.']);
    exit;
}

$token = $data['js']['token'];

// Step 2: Get Channel List
$channelUrl = "http://$portal/stalker_portal/server/load.php?type=itv&action=get_all_channels&JsHttpRequest=1-xml";

$headers[] = "Authorization: Bearer $token";

$ch2 = curl_init();
curl_setopt($ch2, CURLOPT_URL, $channelUrl);
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
$response2 = curl_exec($ch2);
curl_close($ch2);

$data2 = json_decode($response2, true);

if (isset($data2['js']['data'])) {
    echo json_encode($data2['js']['data'], JSON_PRETTY_PRINT);
} else {
    echo json_encode(['error' => 'No channels found.']);
}
exit;
