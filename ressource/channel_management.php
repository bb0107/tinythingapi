<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
Provide functions to manage Channels and Sub-Channels in Web-GUI
****************************************************/

require_once 'DB_UTILS.php';

class Channels extends DB_UTILS {

  function getAll () {

    $sql = "SELECT * FROM channel_management";
	
	try {
    $this->statement = $this->pdo->prepare($sql);
	$this->statement->execute();
    $entry = $this->statement->fetchAll();
	} catch (Exception $ex) {
      return false;
    }
    return $entry;
  }
  
  
  function delSingle ($CHANNELNAME) {
	  
	
	if (!$this->setChannelname($CHANNELNAME)) die(errorMessage('Channelname not set or format insufficient.'));
	if(!$this->Exists($this->_CHANNELNAME)) die(errorMessage('Channel does not exist.'));

    $sql = "DELETE FROM channel_management WHERE channelname = :id; ";
	$sql2 = "DROP TABLE " . $this->_CHANNELNAME;
	
	$sql = $sql . $sql2;
	
	try {
	$this->statement = $this->pdo->prepare($sql);
    $this->statement->bindValue(':id', $this->_CHANNELNAME);
    $this->statement->execute();
	} catch (Exception $ex) {
      return false;
    }
    return true;
	
  }
  
  function newChan ($CHANNELNAME, $vars) {
	  
	if (!$this->setChannelname($CHANNELNAME)) die(errorMessage('Channelname not set or format insufficient.'));
	if ($this->Exists($this->_CHANNELNAME))die(errorMessage('Channel already exists.'));
	
	if(!Validate::isSubchannelCount($vars))die(errorMessage('Subchannel count not numeric or limit exceeded.'));
	
	$write_key = bin2hex(random_bytes(16));
	$read_key = bin2hex(random_bytes(16));
  
	if($vars>100){
	$vars = 100;
	}
	else{
		
	}
	
	$N_VARS = '';
	$N_VARS_MGMT = '';
	$N_VARS_VAL = '';
	for ($n = 0; $n < $vars; $n++){
		$N_VARS = $N_VARS."var".$n." FLOAT(7, 2), ";
		$N_VARS_MGMT  = $N_VARS_MGMT."var" .$n;
		$N_VARS_VAL  = $N_VARS_VAL."'var" .$n . "'";
		if(($vars-$n) == 1){
		}
		else{
		$N_VARS_MGMT  = $N_VARS_MGMT . ", ";
		$N_VARS_VAL  = $N_VARS_VAL . ", ";
		}
	}
	

	$sql = "CREATE TABLE ".$this->_CHANNELNAME." (
									id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,"
									.$N_VARS."
									date TIMESTAMP
								);";
	$sql2 =	"INSERT INTO channel_management (channelname, read_key, write_key, subchannels, " . $N_VARS_MGMT . ") 
			VALUES (:channelname, :read_key, :write_key, :subchannels, ". $N_VARS_VAL .")";
	
	$sql = $sql.$sql2;
			 
	try {
	
	$this->statement = $this->pdo->prepare($sql);
	$this->statement->bindValue(':channelname', $this->_CHANNELNAME);
	$this->statement->bindValue(':read_key', $read_key);
	$this->statement->bindValue(':write_key', $write_key);
	$this->statement->bindValue(':subchannels', $vars);
	$this->statement->execute();
	} catch (Exception $ex) {
		var_dump($ex);
      return false;
    }
	return true;
	  
  }
  
  function emptyChan ($CHANNELNAME) {
	  
	 
	if (!$this->setChannelname($CHANNELNAME)) die('Channelname not set or format insufficient.');
	if (!$this->Exists($this->_CHANNELNAME))die("Channel does not exist");
	  
	$sql = "TRUNCATE TABLE " . $this->_CHANNELNAME;
	try {
	
	$this->statement = $this->pdo->prepare($sql);
	$this->statement->execute();
	} catch (Exception $ex) {
      return false;
    }
	return true;
	  
  }
  
  function Update($CHANNELNAME, $RW){
	
	if (!$this->setChannelname($CHANNELNAME)) die('Channelname not set or format insufficient.');
	if (!$this->Exists($this->_CHANNELNAME))die("Channel does not exist");
	
	$KEY_UPDATE = bin2hex(random_bytes(16));
	
	switch ($RW){
		case "READ":
		$KEY = "read_key";
		break;
		case "WRITE":
		$KEY = "write_key";
		break;
		default:
		die("Insufficient Input");
	}
		
	$sql = "UPDATE channel_management SET $KEY=:KEY_UPDATE_ WHERE channelname=:CHANNELNAME_";
    try {
      $this->stmt = $this->pdo->prepare($sql);
	  $this -> stmt -> bindValue(':CHANNELNAME_', $this->_CHANNELNAME);
	  //$this -> stmt -> bindValue(':KEY_', $KEY);
	  $this -> stmt -> bindValue(':KEY_UPDATE_', $KEY_UPDATE);
      $this -> stmt -> execute();
    } catch (Exception $ex) {
		echo $ex;
      return false;
    }
    return true;
  
  }
  
  function UpdateSub($CHANNELNAME, $subchannel, $subchannelname){
	  
	if (!$this->setChannelname($CHANNELNAME)) die('Channelname not set or format insufficient.');
	if (!$this->Exists($this->_CHANNELNAME))die("Channel does not exist");
	
	if (!Validate::isChannel($subchannelname))die('Subchannel format incorrect.');
	if(!Validate::isSubchannelDescriptor($subchannel))die('Subchannel format incorrect.');

	$sql = 'UPDATE channel_management 
			SET ' . $subchannel . ' = :SUBCHANNELNAME_ 
			WHERE channelname=:CHANNELNAME_';

    try {
      $this->stmt = $this->pdo->prepare($sql);

	  $this -> stmt -> bindValue(':CHANNELNAME_', $this->_CHANNELNAME);
	  $this -> stmt -> bindValue(':SUBCHANNELNAME_', $subchannelname);
      $this -> stmt -> execute();
    } catch (Exception $ex) {
		echo $ex;
      return false;
    }
    return true;
	
	}
	
  function CountEntries($CHANNELNAME){
	  
	if (!$this->setChannelname($CHANNELNAME)) die('Channelname not set or format insufficient.');
	if (!$this->Exists($this->_CHANNELNAME))die("Channel does not exist");
  
	$sql = 'SELECT COUNT(*) FROM ' . $this->_CHANNELNAME;

	try {
	  $this->stmt = $this->pdo->prepare($sql);
	  $this -> stmt -> execute();
	  $result = $this->stmt->fetch();
	} catch (Exception $ex) {
	  return false;
	}
	return $result[0];

  }
  
  function getLastEntry($CHANNELNAME, $SUBCHANNELNAME){
	  
	if (!$this->setChannelname($CHANNELNAME)) die('Channelname not set or format insufficient.');
	if (!$this->Exists($this->_CHANNELNAME))die("Channel does not exist");
	
	
	if ($SUBCHANNELNAME != 'date'){
	if (!Validate::isSubchannelDescriptor($SUBCHANNELNAME))die('Subchannel Descriptor format incorrect.');
	}
	else {}
	
	
	$sql = 'SELECT *FROM ' . $this->_CHANNELNAME . ' ORDER BY date DESC LIMIT 1';
	
	try {
	  $this->stmt = $this->pdo->prepare($sql);
	  $this -> stmt -> execute();
	  $result = $this->stmt->fetch();
	} catch (Exception $ex) {
	  return false;
	}
	return $result[$SUBCHANNELNAME];
	
  }
  
  
}
?>