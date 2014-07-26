<?php

require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'ahsayobs' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'main.php');

function ahsayobs_client_area() {
    # Hook code goes here
	global $smarty;
	require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'ahsayobs' . DIRECTORY_SEPARATOR . 'ahsayobs.php');	
	$cd = $smarty->get_template_vars('clientsdetails');
	$userid = $cd['userid'];
	$user = ahsayobs_getUserFromAhsayServer($userid);
	$pkgs = ahsayobs_getUserAhsayPackages($userid);
}

function ahsayobs_client_header(){
	global $smarty;
//	require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'ahsayobs' . DIRECTORY_SEPARATOR . 'ahsayobs.php');
	$cd = $smarty->get_template_vars('clientsdetails');
	$userid = $cd['userid'];
	$pkgs = ahsayobs_getUserAhsayPackages($userid);
	//var_dump($pkgs);
	$links = array();
	foreach($pkgs as $pkg){
		$link = "clientarea.php?action=productdetails&id=" . $pkg['id'] . "&modop=custom&a=infopage";
		$links[$pkg['username']] = $link;
	}
	if (sizeof($links) == 0){
		$links = null;
	}
	$smarty->assign('backuplinks', $links); 
	ob_start();
	//require_once((dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'header.php');
	$header = ob_get_clean();
	return $header;
}

function ahsayobs_addon_hook_logout($vars) {
    # Hook code goes here
}

function ahsayobs_client_area_valid($vars){
	global $errormessage, $_ADDONLANG;
	require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'ahsayobs' . DIRECTORY_SEPARATOR . 'ahsayobs.php');
	extract($_ADDONLANG);
	/*$data = ahsayobs_getSettings();
	$errormessage = var_export($data, true);
	var_dump($vars);*/
	$username = array_shift($vars['customfield']);
	$password = array_shift($vars['customfield']);
	$users = select_query('tblhosting', '*', array('username' => $username));
	$errormessage .= $username;
	$user = mysql_fetch_assoc($users);
	if (is_array($user)){
		$errormessage .= sprintf($username_exists_error, $username);//"# Username '".$username."' is not available, please choose another. #";
	}
	if (strlen($username) < 4){
		$errormessage .= (empty($errormessage) ? '': '<br/>') . sprintf($username_length_error, $username);
	}
	if (strlen($password) < 6){
		$errormessage .= (empty($errormessage) ? '': '<br/>') . sprintf($password_length_error, $username); //"# Password must contain at least 6 characters. #";
	}
	if (substr($errormessage, -1, 1) == '#'){
		$errormessage = substr($errormessage, 0, -1);
	}
	return $errormessage;
}
//add_hook('ShoppingCartValidateProductUpdate', 1, 'ahsayobs_client_area_valid');
//add_hook("ClientAreaPage",1,"ahsayobs_client_area");
//add_hook("ClientAreaFooterOutput",1,"ahsayobs_client_header");

?>
