
<?php
// A sample PHP Script to POST data using cURL
// Data in JSON format
 
$API_KEY = '16c32d55eeae7645';

$_TIME = time();

$header = array(
    'channelname' => 'channel8',
	'timestamp' => $_TIME
);

$header = json_encode($header);

$HASH_INPUT = $header;

print $HASH_INPUT;

//print $HASH_INPUT;

$hash = hash_hmac('sha256',$HASH_INPUT, $API_KEY, false);

var_dump($hash);
 
// Prepare new cURL resource
$ch = curl_init('http://localhost/REST_API/public/API/channel8');
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

$headers = explode("\r\n", $headers); // The seperator used in the Response Header is CRLF (Aka. \r\n) 


$headers = array_filter($headers);

$html = '';
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