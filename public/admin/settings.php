<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
Update user data for Web-GUI login
****************************************************/

session_start();

//Include supporting files.
require_once "../../ressource/user_management.php";
require_once "../../config/common.php";
require_once "../../config/validate.php";

$HEADLINE = "Settings";

//Include HTML header.
require_once "include/header.php";

//If update of username or password was requested, check session cookie and update DB if request was authorized
if (isset($_POST["update"])) {
    
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])){
		unset($_SESSION['user']);
		header('Location: login.php');
		die();
	}

	$USER_NAME_TARGET = $_POST["USER_NAME"];
	$USER_NAME_CURRENT = $_POST["NAME_CURRENT"];
	$USER_PASSWORD = $_POST["USER_PASSWORD"];
	$USER_PASSWORD_CONFIRM = $_POST["USER_PASSWORD_CONFIRM"];
	
	//Make sure, both passwords do match.
	if ($USER_PASSWORD <> $USER_PASSWORD_CONFIRM) die(errorMessage('Password mismatch.'));

	//Update user data in database - Input validation happens inside Users Class
	$User_Class = New Users();
	$User_Class -> edit($USER_NAME_TARGET, $USER_NAME_CURRENT, $USER_PASSWORD);

}

 ?>

<div id="container">


<form method="post" class="mt-3 mx-3">

  <input type="hidden" id="csrf" name="csrf" value="<?php echo escape($_SESSION['csrf']); ?>"/>
  <input type="hidden" id="name_current" name="NAME_CURRENT" value="<?php echo escape($_SESSION['user']); ?>"/>

  <div class="form-group">
  <label for="user_email">User Name:</label>
  <input type="input" class="form-control" name="USER_NAME" id="USER_NAME" value="<?php echo escape($_SESSION['user']); ?>">
  </div>
  <div class="form-group">
  <label for="user_password">New Password:</label>
  <input type="password" class="form-control" name="USER_PASSWORD" id="USER_PASSWORD" placeholder="Enter new Password">
  </div>
  <div class="form-group">
  <label for="user_password">Confirm Password:</label>
  <input type="password" class="form-control" name="USER_PASSWORD_CONFIRM" id="USER_PASSWORD" placeholder="Confirm Password">
  </div>

  <button type="submit" class="btn btn-secondary" name="update" value="update">Update</button>

</form>



</div>
<?php require "include/footer.php"; ?>