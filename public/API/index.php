<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
API controller that handles user requests
****************************************************/

//Define error handler to inform client about errors during execution.
function errorHandler($ERROR_CODE, $ERROR_MESSAGE){
	header($ERROR_CODE);
	header('x-error-message: ' . $ERROR_MESSAGE);
	die($ERROR_MESSAGE);
}

//Include supporting classes.
require_once "../../config/config.php";
require_once "../../config/validate.php";
require_once "../../ressource/api.php";

$RETURN_ARRAY = new UserAPI();

if(isset($_SERVER['PATH_INFO'])){
$REQUEST_PATH =  explode('/', $_SERVER['PATH_INFO']);
}
else{
	header("HTTP/1.1 400 Bad Request");
	die();
}

try  {

	//Check if user intention is to write new values into database.
	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		
		if(!isset($REQUEST_PATH[1])) errorHandler('HTTP/1.1 400 Bad Request', 'Channel not defined');
		if (!$RETURN_ARRAY->setChannelname($REQUEST_PATH[1])) errorHandler('400 Bad Request', 'Channelname Format incorrect.');
		
		if(!isset($REQUEST_PATH[2])) errorHandler('HTTP/1.1 400 Bad Request', 'Size of requested data not specified');
		if (!$RETURN_ARRAY->setEntryCount($REQUEST_PATH[2])) errorHandler('400 Bad Request', 'Channelname Format incorrect.');
	  
		$RETURN_ARRAY->setSubchannelName('all');
	  
		$RETURN_ARRAY->Write();
		
	}
	//Check if user intention is to read values from database.
	else if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		
		if(!isset($REQUEST_PATH[1])) errorHandler('HTTP/1.1 400 Bad Request', 'Channel not defined');
		if (!$RETURN_ARRAY->setChannelname($REQUEST_PATH[1])) errorHandler('400 Bad Request', 'Channelname Format incorrect.');

		if(!isset($REQUEST_PATH[2])) errorHandler('HTTP/1.1 400 Bad Request', 'Sub-Channel not defined');
		if (!$RETURN_ARRAY->setSubchannelName($REQUEST_PATH[2])) errorHandler('400 Bad Request', 'Channelname Format incorrect.');
		
		if(!isset($REQUEST_PATH[3])) errorHandler('HTTP/1.1 400 Bad Request', 'Size of requested data not specified');
		if (!$RETURN_ARRAY->setEntryCount($REQUEST_PATH[3])) errorHandler('400 Bad Request', 'Channelname Format incorrect.');
		
		$RETURN_ARRAY->Read();

	}

	//Check if user intention is to empty channel from database.
	else if ($_SERVER['REQUEST_METHOD'] == 'DELETE') {
		
		if(!isset($REQUEST_PATH[1])) errorHandler('HTTP/1.1 400 Bad Request', 'Channel not defined');
		if (!$RETURN_ARRAY->setChannelname($REQUEST_PATH[1])) errorHandler('400 Bad Request', 'Channelname Format incorrect.');
				
		$RETURN_ARRAY->Remove();

	}
	
	else {
		header("HTTP/1.1 400 Bad Request");
	}
	

	} catch(exception $error) {
	header("HTTP/1.1 400 Bad Request");
}

?>
