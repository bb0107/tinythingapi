<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
 
/****************************************************
Installation script
****************************************************/

//Include Config File
require_once '../config/config.php';

//Initialize Status Variable
$FEEDBACK = '';
$error = false;
$connection = false;

//Check PHP Version
$php_version = phpversion();

	if($php_version<5)
	{
	 $error=true;
	 $PHP_VERSION = 'PHP version '. $php_version .' is too old!';
	}
	else{
	 $PHP_VERSION = 'PHP version '. $php_version .' - OK!<br>';
	}

//Check Sessions
$_SESSION['myscriptname_sessions_work']=1;
	if(empty($_SESSION['myscriptname_sessions_work']))
	{
	  $error=true;
	  $SESSIONS_CHECK = 'Sessions not enabled!<br>';
	}
	else{
	  $SESSIONS_CHECK = 'Sessions enabled - OK!<br>';
	}

//Connect to DB
try {
	  $pdo = new PDO(
		"mysql:host=" . DB_HOST . ";charset=" . DB_CHARSET, DB_USER, DB_PASSWORD, DB_OPTIONS);
	  $connection = true;
	} catch (Exception $ex) {
	  $error= true ;
	  $MYSQL_VERSION = 'Connection to MySQL Database not possible.';
}

//Check MySQL Version only if connection to DB was possible
if($connection == true){

	try {
	$MYSQL_VERSION = $pdo->getAttribute(constant("PDO::ATTR_SERVER_VERSION")) . ' - OK!';
	}
	catch (Exception $ex){
	}
}

//Redirect to login page after successfull setup
if(isset($_POST['start'])){
header('Location: ../public/admin/');
}

//Remove installation file
if(isset($_POST['remove'])){
	unlink('install.php');
	$FEEDBACK = 'File sucessfully removed from server!';
	$_SUCCESS['removal'] = true;
}

//Create Database
if(isset($_POST['step1']) && isset($_POST['install'])){

try {
	$sql = 'CREATE DATABASE ' . DB_NAME;
	$statement = $pdo->prepare($sql);
	$statement->execute();
	
	$FEEDBACK = $FEEDBACK .  'Database '. DB_NAME .' created successfully.<br>';
	$_SUCCESS['database'] = true;
	
} catch(Exception $ex) {
	$FEEDBACK = $FEEDBACK . $ex->getMessage() . '<br>';
	$_SUCCESS['database'] = false;
}

}

//Create Tables
if(isset($_POST['step2']) && isset($_POST['install'])){

try {
	$sql = 
	'CREATE TABLE '. DB_NAME .' . channel_management (
	id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY NOT NULL, 
	channelname VARCHAR(30) NOT NULL,
	read_key VARCHAR(16) NOT NULL,
	write_key VARCHAR(16) NOT NULL,
	subchannels INT(11) NOT NULL,
	date TIMESTAMP ON UPDATE CURRENT_TIMESTAMP NOT NULL,
	var0 VARCHAR(15) NOT NULL,
	var1 VARCHAR(15) NOT NULL,
	var2 VARCHAR(15) NOT NULL,
	var3 VARCHAR(15) NOT NULL,
	var4 VARCHAR(15) NOT NULL,
	var5 VARCHAR(15) NOT NULL,
	var6 VARCHAR(15) NOT NULL,
	var7 VARCHAR(15) NOT NULL);';
	
	$statement = $pdo->prepare($sql);
	$statement->execute();

	$FEEDBACK = $FEEDBACK . 'Table "channel_management" created successfully.<br>';
	$_SUCCESS['channel_management'] = true;
	
	$sql = 
	'CREATE TABLE '. DB_NAME .' . users (
		user_id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY, 
		user_name VARCHAR(255) NOT NULL,
		user_password VARCHAR(255) NOT NULL
	);';
	
	$statement = $pdo->prepare($sql);
	$statement->execute();
	
	$FEEDBACK = $FEEDBACK . 'Table "users" created successfully.<br>';
	$_SUCCESS['users'] = true;
	
	$sql = 
	'INSERT INTO '. DB_NAME .' . users 
		(`user_id`, `user_name`, `user_password`) 
		VALUES 
		("1", "John", "$2y$10$Hh9.erKpXL8U9hwrlV0oDuDEuzidocudhUh5qD1npqrXmvIZU7k5u");';
	
	$statement = $pdo->prepare($sql);
	$statement->execute();
	
	$FEEDBACK = $FEEDBACK . 'Default User created successfully.<br>';
	$_SUCCESS['default_user'] = true;
	
} catch(Exception $ex) {
	$FEEDBACK = $FEEDBACK .  $sql . "<br>" . $ex->getMessage() . '<br>';
	$_SUCCESS['default_user'] = false;
}
}
	
?>

<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Channel DB Setup</title>
  
  <link rel="stylesheet" href="install.css">

   
</head>
<body>


<div class="PAGE_CONTAINER">
<form method="post">

<h1>Channel DB Setup</h1>
<hr>
<div>
	<div class="DIV_LEFT"><b>PHP Version </b></div>
	<div class="DIV_RIGHT"><?php echo $PHP_VERSION;?></div>
</div>
<div>
	<div class="DIV_LEFT"><b>PHP Sessions </b></div>
	<div class="DIV_RIGHT"><?php echo $SESSIONS_CHECK;?></div>
</div>
<div>
	<div class="DIV_LEFT"><b>MySQL Check </b></div>
	<div class="DIV_RIGHT"><?php echo $MYSQL_VERSION;?></div>
</div>
<hr>
<div>
	<div class="DIV_LEFT">Create Database <?php echo DB_NAME;?></div>
	<div class="DIV_RIGHT"><input type="checkbox" name="step1" checked id="step1_toggle"></div>
</div>
<div>
	<div class="DIV_LEFT">Create Tables </div>
	<div class="DIV_RIGHT"><input type="checkbox" name="step2" checked id="step2_toggle"></div>
</div>
<hr>
<?php if($_SUCCESS['default_user'] == true){echo '
<div>
	<div class="DIV_LEFT">Username: </div>
	<div class="DIV_RIGHT">John</div>
</div>
<div>
	<div class="DIV_LEFT">Initial Password: </div>
	<div class="DIV_RIGHT">123456</div>
</div>
<hr>
';}
if(isset($_SUCCESS)){echo '
<div style="overflow: auto;">
	<div class="DIV_LEFT">' . $FEEDBACK . '</div>
</div>
<hr>
';}
if($_SUCCESS['default_user']){
echo '
<div style="overflow: auto;">
	<div class="DIV_LEFT">Please remove folder "install" from Server to make sure no one can access your installation before continuing!</div>
</div>
<hr>';
} 
?> 
<div style="overflow: auto;">
	<div class="DIV_LEFT"><button type="submit" name="install" id="button" value="install" <?php if($error || $_SUCCESS['default_user'] || $_SUCCESS['removal']){echo 'disabled';} ?>>Install</button></div>
	<?php if($_SUCCESS['default_user']){
	echo '
	<div class="DIV_RIGHT"><button type="submit" name="remove" id="remove" value="remove">Delete Installation File</button><button type="submit" name="start" id="start" value="start">Go to Login Page</button></div>';
	} ?>
	</div>

</form>
</div>
</body>
</html>