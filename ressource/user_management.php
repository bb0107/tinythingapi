<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
Provide functions to manage Users and login to Web-GUI
****************************************************/


//Include class to connect to database.
require_once 'DB_UTILS.php';

class Users extends DB_UTILS {

	//Edit existing username or realted password
	function edit ($name_target, $name_current, $password) {

		//Validate Input data before writing into DB
		if (!Validate::IsPassword($password)) die(errorMessage('Password Format incorrect'));
		if (!Validate::IsUsername($name_target)) die(errorMessage('Username Format incorrect'));
		if (!Validate::IsUsername($name_current)) die(errorMessage('Username Format incorrect'));
		
		//Check whether username exists before writing to DB
		if (!$this->getUser($name_current)) die(errorMessage('Username does not exist'));

		//Create SQL Query.
		$sql = "UPDATE users SET user_name=:name_target, user_password=:user_password WHERE user_name=:name_current";
			
		try {
			$this->statement = $this->pdo->prepare($sql);
			
			//bind input parameters to query
			$this->statement -> bindParam(':name_target', $name_target, PDO::PARAM_STR);
			$this->statement -> bindParam(':name_current', $name_current, PDO::PARAM_STR);
			//hash password before writing it to DB
			$this->statement -> bindParam(':user_password', password_hash($password, PASSWORD_DEFAULT), PDO::PARAM_STR);

			$this->statement->execute();
			return true;
		} catch (Exception $ex) {
			return false;
		}
	}

	//Read existing Username and corresponding password from DB
	function getUser ($name) {
  
		//Validate Input data before writing into DB
		if (!Validate::IsUsername($name)) die(errorMessage('Username Format incorrect'));

			$sql = "SELECT * FROM users WHERE user_name=:user_name";

		try {
			$this->statement = $this->pdo->prepare($sql);
			$this->statement -> bindParam(':user_name', $name, PDO::PARAM_STR);
			$this->statement->execute();
			$entry = $this->statement->fetchAll();
			return count($entry)==0 ? false : $entry[0] ;
		} catch (Exception $ex) {
			return false;
		}
		}
  
  
}
?>