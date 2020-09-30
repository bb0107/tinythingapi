<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
 
/****************************************************
Define constraints for user inputs and provide 
functions to validate inputs.
****************************************************/

class Validate
{
	
//Channel
public static $CHANNEL_MAX_LENGTH = 30;
public static $CHANNEL_MIN_LENGTH = 3;

//Password
public static $PASSWORD_MAX_LENGTH = 20;
public static $PASSWORD_MIN_LENGTH = 5;

//Username
public static $USER_MAX_LENGTH = 20;
public static $USER_MIN_LENGTH = 4;

//HASH
public static $HASH_LENGTH = 64;
public static $CSRF_LENGTH = 32;


public static $CHANNEL_COUNT_MAX = 8;
public static $MAX_STRING_LENGTH = 20;
public static $MAX_ENTRY_RETURN_COUNT = 100;



public static function isSubchannelDescriptor($INPUT){

	for($i = 0; $i < self::$CHANNEL_COUNT_MAX; $i++){
		
		$SUB_CHANNEL_DESCRIPTOR = 'var' . $i;
	
		if($INPUT == $SUB_CHANNEL_DESCRIPTOR){
			return true;
		}
		else {}
	
	}
	return false;

}

public static function isString($INPUT){

	return is_string($INPUT);

}

public static function isInt($INPUT){

	return is_int($INPUT);

}

public static function isFloat($INPUT){

	return is_float($INPUT);

}

public static function isHash($INPUT){
	
	$STRIPPED_STRING = preg_replace('/[^a-z0-9]/', '', $INPUT);
	
	if (	self::isString($INPUT) &&
			$INPUT == $STRIPPED_STRING &&
			strlen($INPUT) == self::$HASH_LENGTH
	){
	return true;
	}
	else{
	return false;
	}
}

public static function isChannel($INPUT){
	
	$STRIPPED_STRING = preg_replace('/[^a-zA-Z0-9_-]/', '', $INPUT);
	
	if(	self::isString($INPUT) && 
		$INPUT == $STRIPPED_STRING &&
		strlen($INPUT) <= self::$CHANNEL_MAX_LENGTH &&
		strlen($INPUT) >= self::$CHANNEL_MIN_LENGTH){
			
	return true;
	}
	else{
	return false;
	}
}

public static function isPassword($INPUT){
	
	if( self::isString($INPUT) &&
		strlen($INPUT) <= self::$PASSWORD_MAX_LENGTH &&
		strlen($INPUT) >= self::$PASSWORD_MIN_LENGTH){
	return true;
	}
	
	else{
	return false;
	}
	
}

public static function isUsername($INPUT){
	
	$STRIPPED_STRING = preg_replace('/[^a-zA-Z0-9_-]/', '', $INPUT);
	
	if(	self::isString($INPUT) && 
		$INPUT == $STRIPPED_STRING &&
		strlen($INPUT) <= self::$USER_MAX_LENGTH &&
		strlen($INPUT) >= self::$USER_MIN_LENGTH){
			
	return true;
	}
	else{
	return false;
	}
}

public static function StripString($INPUT){
	
	$STRIPPED_STRING = preg_replace('/[^a-zA-Z0-9_-]/', '', $INPUT);
	$STRIPPED_STRING = substr($STRIPPED_STRING, 0, self::$MAX_STRING_LENGTH);
			
	return $STRIPPED_STRING;
}


public static function isTimestamp($INPUT){
	
	if(is_numeric($INPUT)){
	return true;
	}
	
	else{
	return false;
	}
	
}

public static function isCSRF($INPUT){
	
	$STRIPPED_STRING = preg_replace('/[^a-zA-Z0-9]/', '', $INPUT);
	
	if(	self::isString($INPUT) && 
		$INPUT == $STRIPPED_STRING &&
		strlen($INPUT) == self::$CSRF_LENGTH){
			
	return true;
	}
	else{
	return false;
	}
	
}

public static function isSubchannelCount($INPUT){
	
	if( is_numeric($INPUT) &&
		$INPUT <= self::$CHANNEL_COUNT_MAX &&
		$INPUT > 0){
	return true;
	}
	
	else{
	return false;
	}
	
}

public static function isValidEntryRequest($INPUT){
	
	if( is_numeric($INPUT) &&
		$INPUT <= self::$MAX_ENTRY_RETURN_COUNT &&
		$INPUT > 0){
	return true;
	}
	
	else{
	return false;
	}
	
}

public static function isEntry($INPUT){
	
	if(is_numeric($INPUT)){
	return true;
	}
	
	else{
	return false;
	}
	
}


}


?>