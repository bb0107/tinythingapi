<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
Provide basic database functions to all further classes
****************************************************/

require_once "../../config/config.php";

class DB_UTILS{

	protected $pdo = null;
	protected $statement = null;
	
	protected $_CHANNELNAME;
	protected $_COUNT;
	protected $_SUB_CHANNELNAME;
	

	function __construct () {
		// __construct() : connect to the database
		// PARAM : DB_HOST, DB_CHARSET, DB_NAME, DB_USER, DB_PASSWORD

		try {
		  $this->pdo = new PDO(
			"mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASSWORD, DB_OPTIONS);
		  return true;
		} catch (Exception $ex) {
			var_dump($ex);
			die();
		}
	}

	function __destruct () {
		// __destruct() : close connection when done

		if ($this->statement !== null) {
		  $this->statement = null;
		}
		if ($this->pdo !== null) {
		  $this->pdo = null;
		}
	}



	public function setChannelname($INPUT_STRING){
		
		if(Validate::isChannel($INPUT_STRING)){
		$this->_CHANNELNAME = $INPUT_STRING;
		return true;
		}
		else{
		return false;
		}
	}
	
	public function setSubchannelName($INPUT_STRING){

		if(Validate::isSubchannelDescriptor($INPUT_STRING) || $INPUT_STRING == 'all'){
			$this->_SUB_CHANNELNAME = $INPUT_STRING;
		return true;
		}
		else{
		return false;
		}
	}
	
	public function setEntryCount($INPUT_INT){
		
		if(Validate::isValidEntryRequest($INPUT_INT)){
		$this->_COUNT = (int)$INPUT_INT;
		return true;
		}
		else{
		return false;
		}
	}
	
	protected function Exists($CHANNELNAME){
		
		try  {
		
		$sql = "SELECT COUNT(*) 
				AS num 
				FROM channel_management 
				WHERE channelname = :channelname";

		$this->statement = $this->pdo->prepare($sql);
		$this->statement -> bindParam(":channelname", $CHANNELNAME, PDO::PARAM_STR);
		$this->statement->execute();
		$result = $this->statement->fetch();
		
		if($result['num'] > 0){
		return TRUE;
		} else{
		return FALSE;
		}

		} catch(PDOException $error) {
			var_dump($error);
			die();
		}	
	}

}

?>