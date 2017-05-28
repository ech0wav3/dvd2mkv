<?php

/*******************************************************************************

 WINBINDER - A native Windows binding for PHP

 Copyright © 2004-2005 Hypervisual - see LICENSE.TXT for details
 Author: Rubem Pechansky (http://www.hypervisual.com/winbinder/contact.php)

 Main inclusion file for WinBinder

*******************************************************************************/

if(PHP_VERSION < "4.3.0")
	die("WinBinder needs at least PHP version 4.3.\n");

if(!extension_loaded('winbinder'))
	if(!dl('php_winbinder.dll'))
		trigger_error("WinBinder extension could not be loaded.\n", E_USER_ERROR);

$_mainpath = pathinfo(__FILE__);
$_mainpath = $_mainpath["dirname"] . "/";

// WinBinder PHP functions

include $_mainpath . "wb_windows.inc.php";
include $_mainpath . "wb_generic.inc.php";
include $_mainpath . "wb_resources.inc.php";

//------------------------------------------------------------------ END OF FILE

?>
