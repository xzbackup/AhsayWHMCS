<?php

if (!defined("WHMCS"))
	die("This file cannot be accessed directly");

define("MODULES_DIR", dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR);
define("AHSAY_SERVER_MODULE", MODULES_DIR . 'servers' . DIRECTORY_SEPARATOR . 'ahsayobs');
define("AHSAY_ADDON_MODULE", MODULES_DIR . 'addons' . DIRECTORY_SEPARATOR . 'ahsayobs');
define("AHSAY_TOOLS_LIBS", AHSAY_SERVER_MODULE . DIRECTORY_SEPARATOR . 'atools-libs' . DIRECTORY_SEPARATOR);

require_once(AHSAY_SERVER_MODULE . DIRECTORY_SEPARATOR . 'ahsayobs.php');

function ahsayobs_config() {
	$configarray = array(
		"name" => "AhsayWHMCS AGPL",
		"version" => "1.0.3",
		"author" => "github.com/xcezzz/",
		"language" => "english",
		"fields" => array(
			"licenseKey" => array ("FriendlyName" => "AhsayOBS License key", "Type" => "text", "Size" => "25", "Description" => "Enter Your License Key")
			));
	return $configarray;
}

function ahsayobs_upgrade($vars) {
 
    $version = $vars['version'];
 
    # Run SQL Updates for V1.0 to V1.1
    if (version_compare($version, "1.0.4", "<")) {
        $query = "DROP TABLE `tblahsayobs`;";
		$result = mysql_query($query);
		$query = "CREATE TABLE IF NOT EXISTS `tblahsayobs` (
			`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
			`name` varchar(255) NOT NULL,
			`data` text NOT NULL,
			`time` int(11) unsigned NOT NULL,
			PRIMARY KEY (`id`),
			KEY `name` (`name`)
			) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
		$result = mysql_query($query);
    }
}

function ahsayobs_activate() {
    # Create Custom DB Table
	$query = "CREATE TABLE IF NOT EXISTS `tblahsayobs` (
		`id` int(11) unsigned NOT NULL AUTO_INCREMENT,
		`name` varchar(255) NOT NULL,
		`data` text NOT NULL,
		`time` int(11) unsigned NOT NULL,
		PRIMARY KEY (`id`),
		KEY `name` (`name`)
		) ENGINE=InnoDB DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;";
	$result = mysql_query($query);
}

function ahsayobs_saveData($key, $data, $timeout = 0, $type = 'serialize'){
	$data = array(
		'name' => $key,
		'time' => date('U'),
		'data' => serialize($data)
	);

	if ($type == 'serialize'){
		$result = mysql_query("DELETE FROM `tblahsayobs` WHERE data = '$key';");
		$data['text'] = unserialize($data);
		insert_query('tblahsayobs', $data);
	}
}

function ahsayobs_getData($key, $timeLimit = '+1 hour', $type = 'serialize'){
	if ($type == 'serialize'){
		$q = select_query('tblahsayobs', '*', array('name' => $key));
		$data = mysql_fetch_assoc($q);
		if ($data['integer'] <= date('U', strtotime($timeLimit))){
			return false;
		}
		return unserialize($data['text']);
	}
}

function ahsayobs_deactivate() {

    # Remove Custom DB Table
	$query = "DROP TABLE `tblahsayobs`;";
	$result = mysql_query($query);
	$result = mysql_query("DELETE FROM `tbladdonmodules` WHERE module = 'ahsayobs';");
	
}

$licenseKey = null;
$localKey = null;

require_once(AHSAY_SERVER_MODULE . DIRECTORY_SEPARATOR . 'atools-libs' . DIRECTORY_SEPARATOR . 'settings.php');

function ahsayobs_output($vars) {
	global $licenseKey, $localKey, $ahsayobs_licenseData, $ahsayobss, $ahsaySettings, $licError, $ahsayClientLanguage;
	$licenseValid = false;
	if (ahsayobs_CheckLicense()){
		$licenseValid = true;
	}
	//var_dump($ahsayobs_licenseData);
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$licenseKey = $vars['licenseKey'];
	$localKey = $vars['localKey'];
	$LANG = $vars['_lang'];
	$helpTabText = ahsayobs_getHelpTabText();
	list($recentVersion, $versionStatus) = ahsayobs_check_updates();
	$updateNews = ahsayobs_get_update_details();	
	include(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates/navigation.php');
	switch($_REQUEST['x']){
		default:
		global $licData;
		if ($_REQUEST['y'] == 'updateLicenseKey'){
			ahsayobs_updateLicenseKey(mysql_real_escape_string($_REQUEST['licenseKey']));
			$licenseKey = $_REQUEST['licenseKey'];
			if (ahsayobs_CheckLicense()){
				$licenseValid = true;
			}
			$resultText = "Updated license key.";
		} else if ($_REQUEST['y'] == 'updateHelpText'){
			ahsayobs_updateHelpTabText($_REQUEST['helpTabText']);
			$resultText = "Updated help tab text.";
			$helpTabText = $_REQUEST['helpTabText'];
				//var_dump($helpTabText);
		} else if ($_REQUEST['y'] == 'updateSettings'){
			if (ahsayobs_updateSettings($_REQUEST)){
				$resultText= "Settings updated.";
			}
		} else if ($_REQUEST['y'] == 'updateUserSettings'){
			if (ahsayobs_updateUserSettings($_REQUEST)){
				$resultText= "Settings updated.";
			}
		} else if ($_REQUEST['y'] == 'updateCustomFields'){
			foreach($_REQUEST['packages'] as $num => $pkg){
				$query = "DELETE FROM tblcustomfields WHERE relid='$pkg'";
				$delete = mysql_query($query);
				ahsayobs_installCustomFields($pkg, $_REQUEST['packagesUsernameDescription'], $_REQUEST['packagesPasswordDescription']);
			}
		} else if ($_REQUEST['y'] == 'updateConfigurableOptions'){
			foreach($_REQUEST['packages'] as $num => $pkg){
				$query = "DELETE FROM tblcustomfields WHERE relid='$pkg'";
				$delete = mysql_query($query);
				ahsayobs_installCustomFields($pkg);
			}
		}
		$servers = ahsayobs_getServerLicensingInfo();
		$packages = ahsayobs_getAhsayPackages();
		extract(ahsayobs_getUserSettings());
		extract(ahsayobs_getSettings());
			//var_dump(ahsayobs_getUsersFromAhsayServer(1));
		$tpl = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'templates/index.php';
		break;
	}
	include($tpl);
}

function ahsayobs_installCustomFields($packageid, $userDesc, $passDesc){

	$userField = array(
		'type' => 'product',
		'relid' => $packageid,
		'fieldname' => 'Username',
		'fieldtype' => 'text',
		'description' => $userDesc,
		'regexpr' => '/^[a-z0-9\.\_]+$/i',
		'adminonly' => '',
		'required' => 'on',
		'showorder' => 'on',
		'showinvoice' => '',
		'sortorder' => 0
		);

	$passField = array(
		'type' => 'product',
		'relid' => $packageid,
		'fieldname' => 'Password',
		'fieldtype' => 'text',
		'description' => $passDesc,
		'regexpr' => '',
		'adminonly' => '',
		'required' => 'on',
		'showorder' => 'on',
		'showinvoice' => '',
		'sortorder' => 1
		);

	if (insert_query('tblcustomfields', $userField) && insert_query('tblcustomfields', $passField)) return true; 
	return false;
}
function ahsayobs_installConfigurableOptions(){

}
function ahsayobs_sidebar($vars) {

	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$option1 = $vars['option1'];
	$option2 = $vars['option2'];
	$option3 = $vars['option3'];
	$option4 = $vars['option4'];
	$option5 = $vars['option5'];
	$LANG = $vars['_lang'];
	list($recent, $status) = ahsayobs_check_updates();
	$versionstatus = ($status > 0) ? '<li><a href="'.$modulelink.'&x=index#update-tab">Latest Version: '.$recent.'</a></li>' : '';
	$sidebar = '

	<ul class="menu">
		<span class="header"><img src="images/icons/addonmodules.png" class="absmiddle" width="16" height="16"> AhsayWHMCS</span>
		<li><a href="'.$modulelink.'&x=index">Main</a></li>

		<li><a href="#">Current Version: '.$version.'</a></li>
		'.$versionstatus.'
	</ul><div id="submenu">'.ahsayobs_get_atools_details().'</div>';
    return $sidebar;// . get_update_details();

}

?>
