<?php
// SETTINGS
$portal = "tv.fusion4k.cc";
$mac = "00:1A:79:00:02:2B";
$deviceid = "EB33A9633A8664B14E27807A8A53CFF299DD38E76996A8C1D7B5D0E2D32890CF";
$serial = "973DDBA22C9B6";
$user_ip = $_SERVER['REMOTE_ADDR'];

// STEP 1: HANDSHAKE
$handshake_url = "http://$portal/stalker_portal/server/load.php?type=stb&action=handshake&prehash=false&JsHttpRequest=1-xml";
$headers = [
    "Cookie: mac=$mac; stb_lang=en; timezone=GMT",
    "X-Forwarded-For: $user_ip",
    "Referer: http://$portal/stalker_portal/c/",
    "User-Agent: Mozilla/5.0 (QtEmbedded; U; Linux; C) AppleWebKit/533.3 (KHTML, like Gecko) MAG200 stbapp ver: 2 rev: 250 Safari/533.3",
    "X-User-Agent: Model: MAG250; Link:",
];

$ch = curl_init($handshake_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
$handshake_response = curl_exec($ch);
curl_close($ch);

$data = json_decode($handshake_response, true);
if (!isset($data['js']['token']) || !isset($data['js']['random'])) {
    echo json_encode(["error" => "Handshake failed."]);
    exit;
}

$token = $data['js']['token'];
$random = $data['js']['random'];
$auth_headers = $headers;
$auth_headers[] = "Authorization: Bearer $token";

// STEP 2: GET PROFILE (required by some portals)
curl_setopt($ch = curl_init(), CURLOPT_URL, "http://$portal/stalker_portal/server/load.php?type=stb&action=get_profile&hd=1&sn=$serial&device_id=$deviceid&auth_second_step=1&random=$random&JsHttpRequest=1-xml");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $auth_headers);
curl_exec($ch);
curl_close($ch);

// STEP 3: GET CHANNEL LIST
$channel_url = "http://$portal/stalker_portal/server/load.php?type=itv&action=get_all_channels&force_ch_link_check=&JsHttpRequest=1-xml";

curl_setopt($ch = curl_init(), CURLOPT_URL, $channel_url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $auth_headers);
$channel_response = curl_exec($ch);
curl_close($ch);

$channels = json_decode($channel_response, true);
$result = [];

foreach ($channels['js']['data'] ?? [] as $channel) {
    $result[] = [
        'name' => $channel['name'],
        'id' => $channel['id'],
        'cmd' => $channel['cmd'],
        'logo' => $channel['logo'],
    ];
}

header("Content-Type: application/json");
echo json_encode($result, JSON_PRETTY_PRINT);
