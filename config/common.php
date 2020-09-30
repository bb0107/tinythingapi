<?php
/* This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at https://mozilla.org/MPL/2.0/. */
 
/****************************************************
Global functions
****************************************************/

function escape($html) {
    return htmlspecialchars($html, ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
	
function errorMessage($INPUT){
	
$ERROR_MESSAGE = '<div class="mx-3 mt-3"><p>' . $INPUT . '</p><p><button class="btn btn-secondary" onclick="window.history.back();">Go Back</button></p></div>';
return $ERROR_MESSAGE;

}

?>