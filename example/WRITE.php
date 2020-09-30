
<?php
// A sample PHP Script to POST data using cURL
// Data in JSON format
 
$API_KEY = 'd68f7a000446a373';
 
$body = array(
	0 => array(
    'var0' => (rand(10, 30)/5),
    'var1' => (rand(10, 30)/5),
	'var2' => (rand(10, 30)/5),
	'var3' => (rand(10, 30)/5)),
	1 => array(
    'var0' => (rand(10, 30)/5),
    'var1' => (rand(10, 30)/5),
	'var2' => (rand(10, 30)/5),
	'var3' => (rand(10, 30)/5))
);

var_dump(json_encode($body));
//var_dump(implode(", ", array_keys($body[0])));
//var_dump(implode(", :", array_keys($body[0])));

/*
		$sql = sprintf(
		'INSERT INTO %s (%s) values (%s)',
		'channel1',
		implode(', ', array_keys($body[0])),
		':' . implode(', :', array_keys($body[0]))
		);
		
var_dump($sql);
*/

$_TIME = time();

$header = array(
    'channelname' => 'channel1',
    'subchannel' => 'all',
	'count' => 2,
	'timestamp' => $_TIME
);


$payload_JSON = json_encode($body);
//$payload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload_JSON));

$header = json_encode($header);

$HASH_INPUT = $header . '.' . $payload_JSON;

print $HASH_INPUT;

$hash = hash_hmac('sha256',$HASH_INPUT,$API_KEY, false);

 
// Prepare new cURL resource
$ch = curl_init('http://localhost/REST_API/CODE/public/API/channel1/2');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HEADER, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload_JSON);
 
// Set HTTP Header for POST request 
curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	'x-auth-type: Signature',
	'x-auth-alg: HS256',
	'x-auth-timestamp: ' . $_TIME,
	'x-auth-hash: ' . $hash,
    'Content-Length: ' . strlen($payload_JSON))
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