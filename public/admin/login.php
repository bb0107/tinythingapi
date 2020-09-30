<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
Provide login panel and identify user
****************************************************/

session_start(); //Initialize session

//Check if session cookies is already set. If not create a new one.
if (empty($_SESSION['csrf'])) {
	if (function_exists('random_bytes')) {
		$_SESSION['csrf'] = bin2hex(random_bytes(32));
	} else if (function_exists('mcrypt_create_iv')) {
		$_SESSION['csrf'] = bin2hex(mcrypt_create_iv(32, MCRYPT_DEV_URANDOM));
	} else {
		$_SESSION['csrf'] = bin2hex(openssl_random_pseudo_bytes(32));
	}
}

//Include supporting files.
require "../../config/common.php";
require "../../config/validate.php";

//Include HTML header.
require "include/header.php"; 


//If Login button is triggered, check authorization.
if (isset($_POST['submit']) && Validate::StripString($_POST['submit']) === "Login") {
	
	//If session cookie differs from past site, stop execution of script.
	if (!hash_equals($_SESSION['csrf'], $_POST['csrf'])) die("CSRF Session failure");
	
	//Else, include supporting files.
	else {
		  require "../../ressource/user_management.php";
		  $User_Class = new Users();

		  //Get password to submitted user from database and compare with given one.
		  $user = $User_Class->getUser($_POST['user_name']); //Input Validation within Users() Class
		  $pass = is_array($user);
		  //Check whether user is found within DB and whether password is matching
		  if ($pass) {
			$pass = password_verify($_POST['user_password'], $user['user_password']);
		  }
		  else{
			die("Invalid User or Password");
		  }
		  //Set session Cookie and redirect to Web-GUI
		  if ($pass) {
			$_SESSION['user'] = $user['user_name'];
	  		header("Location: index.php");
		  }
		  else{
			die(errorMessage('Invalid User or Password'));

		  }
		  
	}
}


?>
<div class="container-fluid bg-secondary row justify-content-center align-items-center" id="login-div">
<form id="login-form" class="bg-light" method="post">

  <input type="hidden" id="csrf" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>"/>

  <h1>Admin Panel</h1>
  <div class="form-group">
  <label for="user_email">User Name:</label>
  <input type="input" id="user_name" name="user_name" class="form-control" required value="John"/>
  </div>
  <div class="form-group">
  <label for="user_password">Password:</label>
  <input type="password" id="user_password" name="user_password" class="form-control" required value="12345"/>
  </div>
  <input type="submit" name="submit" class="btn btn-secondary btn-block" value="Login"/>

</form>
</div>
<?php require "include/footer.php"; ?>