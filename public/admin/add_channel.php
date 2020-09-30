<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
Add new channel to database
****************************************************/

session_start();

//Include supporting files.
require_once "../../config/common.php";
require_once "../../config/validate.php";

$HEADLINE = "Create Channel";

require_once "include/header.php"; 
require_once "../../ressource/channel_management.php";

//Check whether data for a new channel was submitted. If so, check whether the session ID is set and matches with the submitted one.
if (isset($_POST['create'])) {
	
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])){
	unset($_SESSION['user']);
	header('Location: login.php');
	die();
	}
	
	//Create new instance of channel management class.
	$channel = New Channels();
	//Call related function, inputs are validated within channel management class.
	$channel -> newChan($_POST['channelname'], $_POST['channelno']);
  
}

?>        

<form method="post" class="mt-3 mx-3">
  <input name="csrf" type="hidden" value="<?php echo escape($_SESSION['csrf']); ?>">
	<div class="form-group">
  <label for="channelname">Name of Channel</label>
  <input class="form-control"  type="text" id="channelname" name="channelname">
	</div>
	<div class="form-group">
  <label for="channelno">Number of Sub-Channels</label>
  <input class="form-control" type="text" id="channelno" name="channelno">
	</div>
  <input type="submit" name="create" class="btn btn-secondary" value="Create Channel">
</form>


<div id="container"></div>

<?php require "include/footer.php"; ?>