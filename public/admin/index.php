<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
 
/****************************************************
Show existing channels, visualize data, provide settings.
****************************************************/

session_start();

$HEADLINE = "Manage Channels";

//Include supporting files.
require_once "include/header.php"; 
require_once "../../config/validate.php";
require_once "../../config/common.php";
require_once "../../ressource/channel_management.php";

//Initialize variables.
$toggle = 0;
$POST_TOGGLE = 0;
$POST_LOOP = array('delete', 'empty', 'update_read_key', 'update_write_key', 'update_subchannel');

//Check if page was called by itself to update or delete channel parameters.
foreach($POST_LOOP as $POST_LOOP_ITEM){
	if(isset($_POST[$POST_LOOP_ITEM])){
	$POST_SELECTOR = $POST_LOOP_ITEM;
	$POST_TOGGLE = 1;
	}
}

//Create new instance from channel management class.
$channel = New Channels();

//If page was called by itself, check session and call related channel management functions from class before loading data from database.
if ($POST_TOGGLE) {
    
    if (!isset($_SESSION['csrf']) || !hash_equals($_SESSION['csrf'], $_POST['csrf'])){
	unset($_SESSION['user']);
	header('Location: login.php');
	die();
	}
			
	switch($POST_SELECTOR){
		case 'delete':
			$channel -> delSingle($_POST["delete"]);
			break;
		case 'empty':
			$channel -> emptyChan($_POST["empty"]);
			break;
		case 'update_read_key':
			$channel -> Update($_POST["update_read_key"], "READ");
			$DIV_KEY = "#CONTAINER_KEYS_" . $_POST["update_read_key"];
			$toggle = 1;
			break;
		case 'update_write_key':
			$channel -> Update($_POST["update_write_key"], "WRITE");
			$DIV_KEY = "#CONTAINER_KEYS_" . $_POST["update_write_key"];
			$toggle = 1;
			break;
		case 'update_subchannel':
			$DESERIALIZED_DATA = unserialize($_POST["update_subchannel"]);
			$SUB_CHANNEL_ID = "NAME_" . $DESERIALIZED_DATA['channelname'] . "_" . $DESERIALIZED_DATA['subchannelid'];
			$SUB_CHANNEL_NAME = $_POST[$SUB_CHANNEL_ID];

			$channel -> UpdateSub($DESERIALIZED_DATA['channelname'], $DESERIALIZED_DATA['subchannelid'], $SUB_CHANNEL_NAME);
			$DIV_KEY = "#DIV_" . $DESERIALIZED_DATA['channelname'];
			$toggle = 1;
			break;
		default:
			die("Request unknown");
			break;
	}

}

$result = $channel -> getAll();
?>

<!-- Header and Body included via PHP file -->
<!-- Begin form and call this file in case of any changes -->

<form method="post">
<input name="csrf" type="hidden" value="<?php echo $_SESSION['csrf']; ?>">

<div class="container-fluid mt-3">


<?php 

//display results for each channel found inside the database
foreach ($result as $row) : 

	$ENTRY_COUNT = $channel -> CountEntries($row["channelname"]);

	if(!$channel -> GetLastEntry($row["channelname"], 'date')){
		$LAST_UPDATED = 'No Entries found';
	}
	else{
		$LAST_UPDATED = $channel -> GetLastEntry($row["channelname"], 'date');
	}
?>
  
<!-- create new container for each channel -->
  <div class="card mb-2 bg-light">
	<!-- create new container for each channel and display options -->
    <div class="card-header" id="heading_<?php echo "DIV_" . $row["channelname"]; ?>">
		  <div class="row align-items-center">
			<div class="col-lg-2" id="<?php echo "CHANNEL_NAME_" . $row["channelname"]; ?>">
			   <b><?php echo $row["channelname"]; ?></b>
			</div>
			<div class="col-lg-3">
			  Last entry: <?php echo $LAST_UPDATED ; ?>
			</div>
			<div class="col-lg-2">
			  Sub Channels: <span class="badge badge-pill badge-secondary"><?php echo $row["subchannels"]; ?></span>
			</div>
			<div class="col-lg-1">
			  Entries: <span class="badge badge-pill badge-secondary"><?php echo $ENTRY_COUNT; ?></span>
			</div>
			
			<div class="col-lg-4 text-right">
				<div class="btn-group" role="group">
					<button type="submit" class="btn btn-danger border" name="delete" value="<?php echo $row["channelname"]; ?>">Delete</button>
					<button type="submit" class="btn btn-secondary border" name="empty" value="<?php echo $row["channelname"]; ?>">Empty</button>
					<button type="button" class="btn btn-secondary border" name="key_management" data-toggle="collapse" data-target="#<?php echo "CONTAINER_KEYS_" . $row["channelname"]; ?>">View Keys</button>
					<button type="button" class="btn btn-secondary border" name="empty" data-toggle="collapse" data-target="#<?php echo "DIV_" . $row["channelname"]; ?>">Subchannels</button>
		
				</div>
			</div>
		  </div>
    </div>

	<!-- create new hidden container for each channel and display the read and write keys -->
    <div class="card-body collapse" id="<?php echo "CONTAINER_KEYS_" . $row["channelname"]; ?>">

		<div class="row align-items-center card-text">
			<div class="col-sm-auto">
			  Read Key:
			</div>
			<div class="col-sm-auto">
			  <?php echo $row["read_key"]; ?>
			</div>
			<div class="col-sm-auto">
			  <button type="submit" class="btn btn-secondary" name="update_read_key" value="<?php echo $row["channelname"]; ?>">Get new key</button>
			</div>
		</div>
		
		<hr>
		
		<div class="row align-items-center card-text">
			<div class="col-sm-auto">
			  Write Key:
			</div>
			<div class="col-sm-auto">
			  <?php echo $row["write_key"]; ?>
			</div>
			<div class="col-sm-auto">
			  <button type="submit" class="btn btn-secondary" name="update_write_key" value="<?php echo $row["channelname"]; ?>">Get new key</button>
			</div>
		</div>	


	</div>
	
	<!-- create new hidden container for subchannels -->
	<div class="card-body collapse" id="<?php echo "DIV_" . $row["channelname"]; ?>">		
			
	<?php for ($n_row = 0; $n_row < $row["subchannels"]; $n_row++){
				
	$SUB_CHANNEL_VAR = "var" . $n_row;
	$CHANNEL_AR = array ( 'channelname' => $row["channelname"], 'subchannelid' => $SUB_CHANNEL_VAR);
	$CHANNEL_AR_SERIALIZED = serialize($CHANNEL_AR);
	
	$LAST_ENTRY = $channel -> GetLastEntry($row["channelname"], $SUB_CHANNEL_VAR);
	
	?>
				
		<!-- create new hidden container for each subchannel -->
		<div class="row align-items-center card-text">
		
			<div class="input-group col-md-2">
			  <b><?php echo $SUB_CHANNEL_VAR; ?></b>
			</div>
			<div class="input-group col-md-7">
			  Last entry value: <?php echo $LAST_ENTRY; ?>
			</div>

			
			<div class="col-md-3 text-right">
				<div class="btn-group" role="group">
					<button class="btn btn-secondary border" type="button" name="subchannelname" data-toggle="collapse" data-target="#<?php echo "CONTAINER_SUBCHANNEL_NAME_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>">Change Name</button>
					<button class="btn btn-secondary border" type="button" name="visualize" data-toggle="collapse" data-target="#<?php echo "DIV_SUBCHANNEL_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>" onClick="showChart('<?php echo $row["channelname"]; ?>', '<?php echo $SUB_CHANNEL_VAR; ?>', '<?php echo $row["read_key"]; ?>', 'SHOW')" value='<?php echo $CHANNEL_AR_SERIALIZED; ?>'>Visualize Data</button>
		
				</div>
			</div>


		</div>
		
		<!-- create new hidden container for subchannel options -->
		<div class="row align-items-center card-text collapse my-2" id="<?php echo "CONTAINER_SUBCHANNEL_NAME_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>">

			<div class="col-sm-2"></div>
			<label class="col-sm-2 col-form-label" for="<?php echo "NAME_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>">Update subchannel name: </label>
			<div class="input-group col-md-2">
			  <input type="text" name="<?php echo "NAME_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>" id="<?php echo "NAME_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>" class="form-control" value="<?php echo $row[$SUB_CHANNEL_VAR]; ?>">
			  <div class="input-group-append">
				<button class="btn btn-secondary" type="submit" name="update_subchannel" value='<?php echo $CHANNEL_AR_SERIALIZED; ?>'>Update</button>
			  </div>

			</div>
		
		</div>
		
		<!-- create new hidden container for subchannel visualization -->
		<div class="row align-items-center card-text collapse" id="<?php echo "DIV_SUBCHANNEL_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>">
			
			<div class="input-group card-text" id="chart-control-<?php echo $row["channelname"] . "_" . $SUB_CHANNEL_VAR;?>">
			<div class="col-md-2"></div>
			<label class="col-md-2 col-form-label" for="<?php echo "COUNT_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>">Show last entries: </label>
				<div class="col-md-2">
				<select class="form-control" id="<?php echo "COUNT_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>" onChange="showChart('<?php echo $row["channelname"]; ?>', '<?php echo $SUB_CHANNEL_VAR; ?>', '<?php echo $row["read_key"]; ?>', 'UPDATE')">
				  <option>10</option>
				  <option>20</option>
				  <option>50</option>
				</select>
				</div>
			
			</div>
			<div class="container-fluid">
				<div style="position: relative; height:50vh; width:90vw" class="chart-container" id="<?php echo "DIV_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>">
					<canvas id="<?php echo "CANVAS_" . $row["channelname"] . "_" . $SUB_CHANNEL_VAR; ?>"></canvas>
				</div>
			</div>
		</div>
		
	<?php		
	if(($n_row+1) < $row["subchannels"]){
	echo "<hr>";
	}
	
	} 
	?>

    </div>

  </div>
 
  <?php endforeach; ?>
  
</div>
	
</form>

<!-- include javascript libs -->
<script type="text/javascript" src="js/jquery.min.js"></script>
<script type="text/javascript" src="js/sha256.min.js"></script>
<script type="text/javascript" src="js/moment.min.js"></script>
<script type="text/javascript" src="js/Chart.min.js"></script>
<script type="text/javascript" src="js/timegraph.js"></script>

<?php
	if ($toggle == 1){
		$toggle = 0;
		echo '<script>';
		echo "\n";
		echo '$(document).ready(function(){';
		echo "\n";
		echo '$("' . $DIV_KEY . '").collapse();';
		echo "\n";
		echo '});';
		echo "\n";
		echo '</script>';
	}
	else{}
	
?>


<?php require "include/footer.php"; ?>