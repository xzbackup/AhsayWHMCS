<?php
session_start();
$root = dirname(dirname(dirname(dirname(dirname(__FILE__)))));

require($root . DIRECTORY_SEPARATOR . "dbconnect.php");
require($root . DIRECTORY_SEPARATOR . "includes/functions.php");
require($root . DIRECTORY_SEPARATOR . "includes/clientareafunctions.php");
require($root . DIRECTORY_SEPARATOR . "modules/addons/ahsayobs/ahsayobs.php");


if ($_REQUEST['a']){
	switch($_REQUEST['a']){
		
		case 'server':
			if ($_REQUEST['server']){
				list($server, $serverInfo) = ahsayobs_get_server($_REQUEST['server']);
				$users = ahsayobs_getUsersFromServer($serverInfo);
				ahsayobs_outputAsJson(array('data' => $users, 'total' => sizeof($users)));
			} else {
				
			}
			break;
		
		default:
	}
}
?>