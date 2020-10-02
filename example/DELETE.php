<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
The example removes all data channel
****************************************************/

//Configuration.
$API_KEY = 'd68f7a000446a373';; //get your key e.g. from admin panel
$CHANNEL_NAME = 'channel1'; //make sure the channel is available e.g. via admin panel
$API_URL = 'http://localhost/REST_API/CODE/public/API/' . $CHANNEL_NAME;

//Get current UNIX timestamp.
$_TIME = time();

//Create header.
$header = array(
    'channelname' => $CHANNEL_NAME,
	'timestamp' => $_TIME
);

//Convert header into json object.
$header = json_encode($header);

$HASH_INPUT = $header;

//Calculate Hash out of combined data.
$hash = hash_hmac('sha256',$HASH_INPUT, $API_KEY, false);
 
// Prepare new cURL resource.
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HEADER, 1);
 
// Set HTTP Header for POST request 
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'x-auth-type: Signature',
	'x-auth-alg: HS256',
	'x-auth-timestamp: ' . $_TIME,
	'x-auth-hash: ' . $hash,
    'Content-Length: 0')
);
 
// Submit the POST request
$response = curl_exec($ch);
$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
$headers = substr($response, 0, $header_size);
$body = substr($response, $header_size);
curl_close($ch);

//Separate response header into array.
$headers = explode("\r\n", $headers);
$headers = array_filter($headers);

$html = '';
//Print response.
foreach ($headers as &$value) {
	
	$teile = explode(": ", $value);
	if($teile[0] == "x-header"){
	print base64_decode($teile[1]);
	}
	
    $html .= '<li>' . $value . '</li>';
}
$html = '<ol>' . $html . '</ol>';

header("Content-Type:text/html; charset=UTF-8");
echo $html;
 
?>