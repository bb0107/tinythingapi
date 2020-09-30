<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
 
/****************************************************
Configuration file for database and error reporting.
****************************************************/

//PHP Error reporting.
error_reporting(E_ALL & ~E_NOTICE);
//error_reporting(E_ALL);


//Database config.
define('DB_HOST', 'localhost');
define('DB_NAME', 'channels');
define('DB_CHARSET', 'utf8');
define('DB_USER', 'root');
define('DB_PASSWORD', ''); 

$options    = array(
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, //for Debugging only
				//PDO::ATTR_ERRMODE => PDO::ERRMODE_SILENT,
				//PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
				//PDO::ATTR_EMULATE_PREPARES => false
              );
			  
define('DB_OPTIONS', $options);

?>