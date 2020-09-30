<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
API class to execute user requests
****************************************************/

//Include database tools
require_once 'DB_UTILS.php';

class UserAPI extends DB_UTILS {
	
	//Class variables which are updated by Getter / Setter Functions.
	private $_HASH_CLIENT;
	private $_HASH_SERVER;
	private $_API_KEY;

	private $_SIGNATURE_INPUT;
	private $_SIGNATURE_INPUT_JSON;
	
	private $_PAYLOAD_PLAIN;
	private $_PAYLOAD_JSON;
	private $_PAYLOAD_TO_DB;
	
	private $_CLIENT_TIMESTAMP;
	private $_SERVER_TIMESTAMP;
	
	//Getter / Setter Functions
	
	//Check if Input Timestamp complies with requirements (UNIX format) and set as private var _CLIENT_TIMESTAMP if true.
	private function setClientTimestamp($INPUT_TIMESTAMP){
		
		if(Validate::isTimestamp($INPUT_TIMESTAMP)){
			$this->_CLIENT_TIMESTAMP = (int)$INPUT_TIMESTAMP;
			return true;
		}
		else{
			return false;
		}
	}
	
	//Check if Input complies with requirements (64 Bit length) and set as private var _HASH_CLIENT if true
	private function setHash($INPUT_HASH){
		
		if(Validate::isHash($INPUT_HASH)){
			$this->_HASH_CLIENT = $INPUT_HASH;
			return true;
		}
			else{
			return false;
		}
	}
	
	//Read HTTP Headers and set private vars _CHANNELNAME / call Setter Functions
	private function HandleHeader(){
	
		//Read HTTP Headers
		$headers =  array_change_key_case(getallheaders(), CASE_LOWER);
	
		//Read header for Authentication
		if (!($this->Exists($this->_CHANNELNAME))) $this->errorHandler('400 Bad Request', 'Channel not found.');
		if (!isset($headers['x-auth-hash'])) $this->errorHandler('401 Unauthorized', 'x-hash Header not set.');
		if (!$this->setHash($headers['x-auth-hash'])) $this->errorHandler('400 Bad Request', 'Hash Format incorrect.');
		if (!isset($headers['x-auth-timestamp'])) $this->errorHandler('401 Unauthorized', 'Timestamp variable not set.');
		if (!$this->setClientTimestamp($headers['x-auth-timestamp'])) $this->errorHandler('400 Bad Request', 'Timestamp Format incorrect.');
	}
	

	//Read Payload (Body), decode into array, check whether entries comply with requirements
	private function GetPayload(){
		
		$this->_PAYLOAD_JSON = file_get_contents('php://input');
		$this->_PAYLOAD_PLAIN = json_decode($this->_PAYLOAD_JSON);
	
		$SUB_CHANNELS_COUNT = $this->GetSubchannelCount();
		$ARRAY_COUNT = 0;
	
		//Parse Payload array and validate every single entry
		for($i = 0; $i < $this->_COUNT; $i++){
		
			for ($n = 0; $n < $SUB_CHANNELS_COUNT; $n++){
				$VAR_ADD = 'var' . $n;

				if(	isset($this->_PAYLOAD_PLAIN[$i]->{$VAR_ADD}) &&
					Validate::IsEntry($this->_PAYLOAD_PLAIN[$i]->{$VAR_ADD})){
						
					$this->_PAYLOAD_TO_DB[$i][$VAR_ADD] = floatval($this->_PAYLOAD_PLAIN[$i]->{$VAR_ADD});
					
				}
				else{
					$this->_PAYLOAD_TO_DB[$i][$VAR_ADD] = Null;
				}			
			}
		}
	}
	
	//Support Functions.

	//Connect Inputs to String, Calculates SHA256 HMAC Hash
	private function CalcHash($CASE){

		//Put all relevant variables into JSON Array to calcualte Signature
		$this->_SIGNATURE_INPUT['channelname'] = $this->_CHANNELNAME;
		if($CASE != 'REMOVE') $this->_SIGNATURE_INPUT['subchannel'] = $this->_SUB_CHANNELNAME;
		if($CASE != 'REMOVE') $this->_SIGNATURE_INPUT['count'] = $this->_COUNT;
		if($CASE == 'READ' || $CASE == 'WRITE' || $CASE == 'REMOVE') $this->_SIGNATURE_INPUT['timestamp'] = $this->_CLIENT_TIMESTAMP;
		if($CASE == 'READ RETURN' || $CASE == 'WRITE_RETURN') $this->_SIGNATURE_INPUT['timestamp'] = $this->_SERVER_TIMESTAMP;
		
		$this->_SIGNATURE_INPUT_JSON = json_encode($this->_SIGNATURE_INPUT);	
			
		switch($CASE){
			case ('WRITE'): //Check client signature for sent Data and Header
			$HASH_INPUT =  $this->_SIGNATURE_INPUT_JSON . '.' . $this->_PAYLOAD_JSON;
			$this->_API_KEY  = $this->GetKey('WRITE');
			break;
			case ('READ'): //Check client signature for requested Header
			$HASH_INPUT =  $this->_SIGNATURE_INPUT_JSON;
			$this->_API_KEY  = $this->GetKey('READ');
			break;
			case ('READ_RETURN'): //Sign feedback Server --> Client for requested Data and Header to allow client to check consitency
			$HASH_INPUT = $this->_SIGNATURE_INPUT_JSON . '.' . $this->_PAYLOAD_JSON;
			$this->_API_KEY  = $this->GetKey('READ');
			break;
			case ('WRITE_RETURN' || 'REMOVE'): //Sign feedback Server --> Client
			$HASH_INPUT =  $this->_SIGNATURE_INPUT_JSON;
			$this->_API_KEY  = $this->GetKey('WRITE');
			break;
			default:
			$this->errorHandler('401 Unauthorized', 'Authorization input not sufficient.');
		}
		
		$this -> _HASH_SERVER = hash_hmac('sha256',$HASH_INPUT, $this->_API_KEY , false);
	
	}
	
	//Read API Key out of database
	private function GetKey($KEY_TYPE){
		
		switch($KEY_TYPE){
			case 'READ':
			$REQUESTER = 'read_key';
			break;
			case 'WRITE':
			$REQUESTER = 'write_key';
			break;
			default:
			echo 'ERROR';
			return 'ERROR';
			break;
		}

		try  {
			$sql = 'SELECT ' . $REQUESTER
					. ' FROM channel_management
					WHERE channelname = :channelname';
			
			$this->statement = $this->pdo->prepare($sql);
			$this->statement -> bindParam(':channelname', $this->_CHANNELNAME, PDO::PARAM_STR);
			
			$this->statement->execute();
			$result = $this->statement->fetch(PDO::FETCH_OBJ);

			return $result->$REQUESTER;
		
		} catch(PDOException $ERROR) {
			errorHandler('500 Internal Server Error', 'Cannot retrieve API Key.');
		}
	}
	
	//Compare Server and Client Hash, check whether timestamps are not above limit
	
	private function Authenticate(){
		
		$this->_SERVER_TIMESTAMP = time();
		$delta_time = abs($this->_SERVER_TIMESTAMP - $this->_CLIENT_TIMESTAMP);
				
		if(($this -> _HASH_SERVER == $this->_HASH_CLIENT) && ($delta_time < 120)) {
			return true;
		}
		else{
			return false;
		}
	}
	

	//Put all information together and sign response header.
	
	private function prepareResponseHeader($CASE, $ERROR_CODE, $ERROR_MESSAGE){
		
		$SUB_CHANNELS_COUNT = $this->GetSubchannelCount();
		$ARRAY_SUBCHANNELS = $this->GetSubchannelNames();
		
		//If client requests data, add subchannel names to Header
		if($CASE == 'READ'){
			
			//If all subchannels are requested, merge all labels into header
			
			if($this->_SUB_CHANNELNAME == 'all'){
			
				for ($n = 0; $n < $SUB_CHANNELS_COUNT; $n++){
					
					$KEY_VAR = 'var' . $n;
					
				header('x-' . $KEY_VAR . '-label: ' . $ARRAY_SUBCHANNELS[$KEY_VAR]);
				}
			}
			
			//If not, only merge requested subchannels label into header
			
			else{
				$KEY_VAR = $this->_SUB_CHANNELNAME;
				header('x-' . $KEY_VAR . '-label: ' . $ARRAY_SUBCHANNELS[$KEY_VAR]);
			}
			
		}

		//Generate Hash
		if($CASE == 'READ'){
			$this->CalcHash('READ_RETURN');
		}
		if($CASE == 'WRITE' || $CASE == 'REMOVE'){
			$this->CalcHash('WRITE_RETURN');
		}
		
		//Write Header
		header('HTTP/1.1 ' . $ERROR_CODE);
		header('x-auth-type: Signature');
		header('x-auth-alg: HS256');
		header('x-auth-hash: ' . $this -> _HASH_SERVER);
		header('x-auth-timestamp: ' . time());
		header('x-error-message: ' . $ERROR_MESSAGE);
		header('x-entry-count: ' . $this->_COUNT);
		header('Content-Length: 0');
		
	}
	

	//Read from database amount of subchannels for given Channel
	private function GetSubchannelCount(){
		try  {

			$sql = 'SELECT subchannels 
					FROM channel_management
					WHERE channelname = :channelname';
			
			$this->statement = $this->pdo->prepare($sql);
			$this->statement -> bindParam(':channelname', $this->_CHANNELNAME, PDO::PARAM_STR);
			$this->statement->execute();
			$result = $this->statement->fetch(PDO::FETCH_OBJ);

			return $result->subchannels;
    
	
		} catch(PDOException $ERROR) {
			errorHandler('500 Internal Server Error', 'Cannot retrieve Subchannel Count.');
		}
	}
	
	//Read Subchannel Names out of Database
	private function GetSubchannelNames(){

		$COUNT = $this->GetSubchannelCount();
	
		try  {

			$SELECTOR = '';
			for ($N_COUNT = 0; $N_COUNT < $COUNT; $N_COUNT++){
				$SELECTOR = $SELECTOR . 'var' . $N_COUNT;
				if(($COUNT-$N_COUNT) == 1){
				}
				else{
				$SELECTOR = $SELECTOR . ', ';
				}
			}

			$sql = 'SELECT ' . $SELECTOR . 
					' FROM channel_management
					WHERE channelname = :channelname';
			
			$this->statement = $this->pdo->prepare($sql);
			$this->statement->bindParam(':channelname', $this->_CHANNELNAME, PDO::PARAM_STR);
			$this->statement->execute();
			
			$result = $this->statement->fetch(PDO::FETCH_ASSOC);

			return $result;
		
		} catch(PDOException $ERROR) {
			$this->errorHandler('500 Internal Server Error', 'Cannot retrieve Subchannel Names.');
		}
	}

	private function errorHandler($ERROR_CODE, $ERROR_MESSAGE){
		
		$this->prepareResponseHeader('', $ERROR_CODE, $ERROR_MESSAGE);
		die();
	}
	

	//Gets all necessary data and writes it into Database
	public function Write(){
		
		try  {
		
			//Read Header and Check whether channel is defined / exists
			$this->HandleHeader();
		
			$this->GetPayload();
			
			//Check whether checksum equals client checksum
			
			$this->CalcHash('WRITE');
			
			if(!$this->Authenticate()) {
				$this->errorHandler('403 Forbidden', 'Authorization failed due to Timeout error or invalid key');
			}

			$sql = sprintf(
			'INSERT INTO %s (%s) values (%s)',
			$this->_CHANNELNAME,
			implode(', ', array_keys($this->_PAYLOAD_TO_DB[0])),
			':' . implode(', :', array_keys($this->_PAYLOAD_TO_DB[0]))
			);
		
			$this->statement = $this->pdo->prepare($sql);
			
			for($i=0;$i<$this->_COUNT; $i++) {
			$this->statement->execute($this->_PAYLOAD_TO_DB[$i]);
			}
		  
			$this->prepareResponseHeader('WRITE', '200 OK', 'no errors');

		} catch(PDOException $error) {
			errorHandler('500 Internal Server Error', 'Cannot write data to Database.');
		}
	}
	

	//Reads from Database requested Data
	public function Read(){
		try  {

			//Read Headers 
			$this->HandleHeader();
			
			//Check if Channel exists
			$this->CalcHash('READ');
			
			if(!$this->Authenticate()) {
				$this->errorHandler('403 Forbidden', 'Authorization failed due to Timeout error or invalid key');
			}
			
			//Read GET ARRAY
			
			//If whole channel is requested, get all subchannels out of DB
			if($this->_SUB_CHANNELNAME == 'all'){
				$SUB_CHANNELS_COUNT = $this->GetSubchannelCount();
				
				for ($n = 0; $n < $SUB_CHANNELS_COUNT; $n++){
					$N_VARS_MGMT  = $N_VARS_MGMT.'var' .$n;
					$N_VARS_MGMT  = $N_VARS_MGMT . ', ';
				}
			}
			//If only one subchannel is requested, get only this one out of DB
			else{
				$N_VARS_MGMT = $this->_SUB_CHANNELNAME . ', ';
			}
			
			$sql = 'SELECT id, ' . $N_VARS_MGMT . ' date 
					FROM ' . $this->_CHANNELNAME 
					. ' ORDER BY id DESC LIMIT :count';

			$this->statement = $this->pdo->prepare($sql);
			$this->statement -> bindParam(':count', $this->_COUNT, PDO::PARAM_INT);
			
			$this->statement->execute();

			$result = $this->statement->fetchAll(PDO::FETCH_ASSOC);
			
			$this -> _PAYLOAD_PLAIN = json_encode($result);
			$this -> _PAYLOAD_JSON = json_encode($this -> _PAYLOAD_PLAIN);
			
			$this->prepareResponseHeader('READ', '200 OK', 'no errors');		
			print $this -> _PAYLOAD_PLAIN;
			
	  } catch(PDOException $error) {
			errorHandler('500 Internal Server Error', 'Cannot retrieve requested data from Database.');
	  }
	}


	//Empty table for given channel id
	public function Remove(){
		
		try  {

			//Read Headers, e.g. {"typ":"JWT","channelname":"channel12","alg":"HS256","timestamp":1565711276}
			$this->HandleHeader();
				  
			$this->CalcHash('REMOVE');

			if($this->Authenticate()){

			$sql = 'TRUNCATE TABLE ' . $this->_CHANNELNAME;

			$this->statement = $this->pdo->prepare($sql);
			$this->statement->execute();
				
			$this->prepareResponseHeader('WRITE', '200 OK', 'no errors');	
			}

			else{
			$this->errorHandler('403 Forbidden', 'Authorization failed due to Timeout error or invalid key');
			}	
		} catch(PDOException $error) {
			errorHandler('500 Internal Server Error', 'Cannot empty Channel in Database.');
		}
	}
	
}
?>