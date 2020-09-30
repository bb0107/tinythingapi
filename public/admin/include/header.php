<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */

/****************************************************
Web-GUI Header file, includes routines to check 
authorization and redirects to login page in case of
unauthorized access.
****************************************************/

//Check whether Logout button was triggered and remove session cookie if so.
if (isset($_POST['Logout'])) {
	unset($_SESSION['user']);
	header('Location: login.php');
    die();
}

//Check if user session cookie is set.
if (isset($_SESSION['user'])){
$_ADMIN = is_string($_SESSION['user']);
}
else {
$_ADMIN = false;
}


//Check whether file was included from login page
if (!$_ADMIN && basename($_SERVER["SCRIPT_FILENAME"], '.php')!="login") {
  header('Location: login.php');
  die();
}

//Check whether file was included from index page and azthorization was successfull
if (basename($_SERVER["SCRIPT_FILENAME"], '.php')=="login" && $_ADMIN){
  header('Location: index.php');
  die();
}



?>
<!DOCTYPE html>
<html lang="en">

<head>

  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <meta name="description" content="">
  <meta name="author" content="">

  <title>Channel Admin Interface</title>

  <!-- Bootstrap core CSS -->
  <link href="css/bootstrap.min.css" rel="stylesheet">

  <!-- Custom styles for this template -->
  <link href="css/simple-sidebar.css" rel="stylesheet">

  <script>
  function openNav() {
	  document.getElementById("side-nav").style.width = "250px";
	  document.getElementById("page-overlay").style.display = "block";
	}

	/* Set the width of the side navigation to 0 */
	function closeNav() {
	  document.getElementById("side-nav").style.width = "0";
	  document.getElementById("page-overlay").style.display = "none";
	}
  </script>

</head>

<body class="bg-light">

  <!-- Main Page container -->

  <div class="d-flex" id="page-container">
   <div class="page-overlay" id="page-overlay"></div>
  <?php if ($_ADMIN) { ?>
    <!-- Navigation bar left -->
	<!-- Navigation anpassen, statt verschieben, überlagern. -->
    
	
	<nav class="bg-dark side-nav" id="side-nav">
	  <a href="javascript:void(0)" class="closebtn text-light" onclick="closeNav()">&times;</a>
	  <div class="sidebar-heading text-light">Channel Admin</div>
	  
      <div class="list-group list-group-flush">
		<a href="index.php" class="list-group-item list-group-item-action bg-dark text-light">Manage Channels</a>
        <a href="add_channel.php" class="list-group-item list-group-item-action bg-dark text-light">Add Channel</a>
        <a href="settings.php" class="list-group-item list-group-item-action bg-dark text-light">Settings</a>
		<a class="list-group-item bg-dark">
		<form id="logout-form" method="post" class="form-inline align-center">
		<input type="submit" name="Logout" class="btn btn-danger" value="Logout"/>
		</form>
		</a>
      </div>
	  
	</nav>
    
	<?php } ?>
	
	<!-- Container for Navbar and dynamic page content -->
    <div class="" id="page-content">
	<?php if ($_ADMIN) { ?>
	  <!-- Top Info bar -->
      <nav class="navbar navbar-dark bg-dark border-bottom">
      <button class="navbar-toggler" onclick="openNav()" id="menu-toggle"><span class="navbar-toggler-icon"></span></button>
		<a class="navbar-text text-light mr-1"><?php echo $HEADLINE; ?></a>
      </nav>


	<?php } ?>
	  <!-- Page Content -->
      <main class="container-fluid" id="main-content">
