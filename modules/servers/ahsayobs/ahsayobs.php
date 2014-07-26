<?php
//error_reporting(E_ALL);

define("AHSAY_SERVER_MODULE", dirname(__FILE__) . DIRECTORY_SEPARATOR);
define("AHSAY_ADDON_MODULE", dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'ahsayobs' . DIRECTORY_SEPARATOR);
define("AHSAY_TOOLS_LIBS", AHSAY_SERVER_MODULE . DIRECTORY_SEPARATOR . 'atools-libs' . DIRECTORY_SEPARATOR);

require_once(AHSAY_TOOLS_LIBS . '_atools.php');
require_once(AHSAY_TOOLS_LIBS . '_atools_obs.php');
require_once(AHSAY_TOOLS_LIBS . '_atools_user.php');

require_once(AHSAY_ADDON_MODULE . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'main.php');



function ahsayobs_ConfigOptions() {
	# Should return an array of the module options for each product - maximum of 24
	$configarray = array(
	 //"Package Name" => array( "Type" => "text", "Size" => "50", ),
	 '&nbsp;' => array(),
	 "Backup Client" => array("Type" => "dropdown", "Options" => "ACB,OBM" ),
	 "Trial Account" => array("Type" => "yesno", "Description" => "Is this a trial account?"),
	 "Welcome Email" => array("Type" => "yesno", "Description" => "Send Welcome E-Mail from OBS"),
	 "Backup Quota" => array( "Type" => "text", "Size" => "5", "Description" => "GB" ),
	 "Microsoft Exchange Server" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Microsoft SQL Server" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Oracle DB Server" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "MySQL Database Server" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Lotus Domino" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Lotus Notes" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Shadow Protect Bare Metal" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Windows 2008 Bare Metal" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "NAS Client" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "In-File Delta" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Volume Shadow Copy" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Continuous Data Protection" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Delta Merge" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Microsoft Exchange Mailboxes" => array("Type" => "text","Size" => 5, "Description" => "Mailbox Amount"),
	 "MS VM Quota" => array("Type" => "text","Size" => 5, "Description" => "Amount"),
	 "VMware VM Quota" => array("Type" => "text","Size" => 5, "Description" => "Amount"),
	 "Adgroup" => array("Type" => "text", "Size" => "25"),
	 "User Home" => array("Type" => "text", "Size" => "50"),
	 "Replicated" => array("Type" => "yesno", "Description" => "Enabled?")
	);
	return $configarray;
}

function ahsayobs_CreateAccount($params) {

	# ** The variables listed below are passed into all module functions **

	$serviceid = $params["serviceid"]; # Unique ID of the product/service in the WHMCS Database
	$pid = $params["pid"]; # Product/Service ID
	$producttype = $params["producttype"]; # Product Type: hostingaccount, reselleraccount, server or other
	$domain = $params["domain"];
	$username = $params["username"];
	$password = $params["password"];
	$clientsdetails = $params["clientsdetails"]; # Array of clients details - firstname, lastname, email, country, etc...
	$customfields = $params["customfields"]; # Array of custom field values for the product
	$configoptions = $params["configoptions"]; # Array of configurable option values for the product


	# Product module option settings from ConfigOptions array above
	$configoption1 = $params["configoption1"];
	$configoption2 = $params["configoption2"];
	$configoption3 = $params["configoption3"];
	$configoption4 = $params["configoption4"];

	# Additional variables if the product/service is linked to a server
	$server = $params["server"]; # True if linked to a server
	$serverid = $params["serverid"];
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$serveraccesshash = $params["serveraccesshash"];
	$serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config

	if ($server != false){
		$obs = new OBS();
		$obs->setServer($serverusername, $serverpassword, $serverip, $serversecure);
		$user = new User($obs);
		$user->parseOptions($params);
        //$whmcs = new WHMCS();
        $password = localAPI('encryptpassword', array('password2' => $user->password));
        //$password = $whmcs->encryptPassword($user->password);
		//return var_export($password, true);
        if ($user->username && $password['password']){
        	$update = array('serviceusername' => $user->username, 'servicepassword' => $user->password,'serviceid' => $params['serviceid']);
        	localAPI('updateclientproduct', $update);
            //mysql_query("UPDATE tblhosting SET username='{$user->username}',password='{$password}' WHERE id='".$params["serviceid"]."'");
        }
		if ($obs->AddUser($user, $params['configoption4'] == 'on' ? 'Y' : 'N')){
			update_query("tblhosting",array(
				"diskusage"=>0,
				"disklimit"=> $user->userdata['Quota'] / (1024*1024),
				"bwusage"=>0,
				"bwlimit"=>0,
				"lastupdate"=>"now()",
	        ),array("username"=>$user->username));
			$result = 'success';
		} else {
			$result = 'Server Returned: ' . strip_tags($obs->returned);//. ' '. $obs->current;
		}
		
		return $result;
	}
}

function ahsayobs_TerminateAccount($params) {

	# Code to perform action goes here...

	$serviceid = $params["serviceid"]; # Unique ID of the product/service in the WHMCS Database
	$pid = $params["pid"]; # Product/Service ID
	$producttype = $params["producttype"]; # Product Type: hostingaccount, reselleraccount, server or other
	$domain = $params["domain"];
	$username = $params["username"];
	$password = $params["password"];
	$clientsdetails = $params["clientsdetails"]; # Array of clients details - firstname, lastname, email, country, etc...
	$customfields = $params["customfields"]; # Array of custom field values for the product
	$configoptions = $params["configoptions"]; # Array of configurable option values for the product


	# Product module option settings from ConfigOptions array above
	$configoption1 = $params["configoption1"];
	$configoption2 = $params["configoption2"];
	$configoption3 = $params["configoption3"];
	$configoption4 = $params["configoption4"];

	# Additional variables if the product/service is linked to a server
	$server = $params["server"]; # True if linked to a server
	$serverid = $params["serverid"];
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$serveraccesshash = $params["serveraccesshash"];
	$serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config
	if ($server != false){
		$obs = new OBS();
		$obs->setServer($serverusername, $serverpassword, $serverip, $serversecure);
		
		if ($obs->DeleteUser($username)){
			$result = 'success';
		} else {
			$result = 'Server Returned: ' . $obs->returned. ' '. $obs->current;
		}
		return $result;
	}
}

function ahsayobs_SuspendAccount($params) {

	# Code to perform action goes here...
	$username = $params["username"];
	$server = $params["server"]; # True if linked to a server
	$serverid = $params["serverid"];
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$serveraccesshash = $params["serveraccesshash"];
	$serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config

	if ($server != false){
		$obs = new OBS();
		$obs->setServer($serverusername, $serverpassword, $serverip, $serversecure);
		if ($obs->SuspendUser($username)){
			$result = 'success';
		} else {
			$result = 'Server Returned: ' . $obs->returned;
		}
		return $result;
	}
}

function ahsayobs_UnsuspendAccount($params) {

	# Code to perform action goes here...
	$username = $params["username"];
	$server = $params["server"]; # True if linked to a server
	$serverid = $params["serverid"];
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$serveraccesshash = $params["serveraccesshash"];
	$serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config

	if ($server != false){
		$obs = new OBS();
		$obs->setServer($serverusername, $serverpassword, $serverip, $serversecure);
		if ($obs->SuspendUser($username, true)){
			$result = 'success';
		} else {
			$result = 'Server Returned: ' . $obs->returned;
		}
		return $result;
	}
}

function ahsayobs_ChangePassword($params) {

	# Code to perform action goes here...
	$username = $params["username"];
	$password = $params['password'];
	$server = $params["server"]; # True if linked to a server
	$serverid = $params["serverid"];
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$serveraccesshash = $params["serveraccesshash"];
	$serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config

	if ($server != false){
		$obs = new OBS();
		$obs->setServer($serverusername, $serverpassword, $serverip, $serversecure);

		if ($obs->ChangePassword($username, $password)){
			$result = 'success';
		} else {
			$result = 'Server Returned: ' . $obs->returned;
		}
		return $result;
	}
}


function ahsayobs_ChangePackage($params) {

	# Code to perform action goes here...
	$username = $params["username"];
	$server = $params["server"]; # True if linked to a server
	$serverid = $params["serverid"];
	$serverip = $params["serverip"];
	$serverusername = $params["serverusername"];
	$serverpassword = $params["serverpassword"];
	$serveraccesshash = $params["serveraccesshash"];
	$serversecure = $params["serversecure"]; # If set, SSL Mode is enabled in the server config

	if ($server != false){
		$obs = new OBS();
		$obs->setServer($serverusername, $serverpassword, $serverip, $serversecure);
		$user = $obs->GetUser($username);
		$user->parseOptions($params);
		if ($obs->ModifyUser($user)){
			$result = 'success';
		} else {
			$result = $obs->returned;
		}
		return $result;
	}
}

function ahsayobs_UsageUpdate($params) {
 	
	$serverid = $params['serverid'];
	$serverhostname = $params['serverhostname'];
	$serverip = $params['serverip'];
	$serverusername = $params['serverusername'];
	$serverpassword = $params['serverpassword'];
	$serveraccesshash = $params['serveraccesshash'];
	$serversecure = $params['serversecure'];
	$where = array('server' => $serverid,'domainstatus' => 'Active');

	# Run connection to retrieve usage for all domains/accounts on $serverid

	# Now loop through results and update DB

	$users = select_query('tblhosting', 'id,username', $where);

	while($data = mysql_fetch_array($users)){
		$obs = new OBS();
		$obs->setServer($serverusername, $serverpassword, $serverip, $serversecure);
		$user = $obs->GetUser($data['username']);
		$quota = ($user->userdata['Quota']) / (1024*1024);
		$diskused = ($user->userdata['DataSize'] + $user->userdata['RetainSize']) / (1024*1024);

		update_query("tblhosting",array(
         "diskusage"=>$diskused,
         "disklimit"=>$quota,
         "bwusage"=>0,
         "bwlimit"=>0,
         "lastupdate"=>"now()",
        ),array("id"=>$data['id']));
	}
}

function ahsayobs_PredefinedAddons(){
	return array(
		'Stuff' => 'That'
	);
}

function ahsayobs_ClientArea($params) {
	global $smarty;
	
	//$smarty->register_block('ahsayobs_clientinfopage', 'ahsayobs_smartyInfoPageBlock');
	$smarty->register_block('ahsayobs_productdetails', 'ahsayobs_smartyProductDetailsBlock');
	$smarty->register_prefilter('ahsayobs_removeSmartyPhpBlocks');
	
	return array(
		'templatefile' => 'clientarea',
		'vars' => array()
	);
}

function ahsayobs_removeSmartyPhpBlocks($tpl, &$smarty){
	$tpl = preg_replace("/\{php.*?\}.*?\{.*?php\}/i", '', $tpl);
	return $tpl;
}

function ahsayobs_smartyInfoPageBlock($params, $content, &$smarty, &$repeat){
	extract($smarty->_tpl_vars);
	ob_start();
	//require_once(dirname(dirname(dirname(dirname(__FILE__)))) . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'servers' . DIRECTORY_SEPARATOR . 'ahsayobs' . DIRECTORY_SEPARATOR . 'ahsay_template.php');
	require_once(AHSAY_TOOLS_LIBS . 'ahsay_template.php');
	$contents = ob_get_clean();
	echo $contents;
	return $contents;
}

function ahsayobs_LoginLink($params) {
	echo "";
}

function ahsayobs_AdminCustomButtonArray() {
	# This function can define additional functions your module supports, the example here is a reboot button and then the reboot function is defined below
	$buttonarray = array(
	 
	);
	return $buttonarray;
}
function ahsayobs_AdminLink($params) {
	$code = '<form action="http://'.$params["serverip"].'/obs/system/logon.do" method="post" target="_blank">
<input type="hidden" name="systemLoginName" value="'.$params["serverusername"].'" />
<input type="hidden" name="systemPassword" value="'.$params["serverpassword"].'" />
<input type="submit" value="Login to OBS" />
</form>';
	return $code;
}
function ahsayobs_ClientAreaCustomButtonArray() {
    $buttonarray = array(
		"Access Backup Account" => "infopage"
	);
	return $buttonarray;
}

function ahsayobs_getBackupAccountInfoPage($params){
	global $LANG, $ahsay_licenseData, $smarty;
	$licensekey = $ahsayBillLicenseKey;

	$pagetitle = "Backup Account";
	$pageicon = "images/support/clientarea.gif";
	$breadcrumbnav = '<a href="clientarea.php">'.$_LANG['globalsystemname'].'</a>';
	$breadcrumbnav .= ' > <a href="#">Manage Backup Account</a>';

	if (!ahsayobs_CheckLicense()){
		global $licError, $ahsayClientLanguage;
		$pagearray = array(
			'pageicon' => $pageicon,
			'pagetitle' => $pagetitle,
			'templatefile' => 'ahsay',
			'breadcrumb' => $breadcrumbnav,
			'vars' => array(
				'licenseError' => $ahsayClientLanguage[$licError],
				'brand' => true
			),
	    );
		return $pagearray;
	}
	//var_dump($params);
	$smartyvalues['user_name'] = $params['username'];
	$smartyvalues['user_pass'] = $params['password'];
	$server = $params["server"]; # True if linked to a server
	$smartyvalues['serverip'] = $params["serverip"];
	$smartyvalues['serverhostname'] = $params["serverhostname"];
	$smartyvalues['help_tab'] = html_entity_decode(ahsayobs_getHelpTabText());
    $smartyvalues = array_merge($smartyvalues, ahsayobs_getSettings());
    //var_dump($smartyvalues);
    $smartyvalues['userData'] = ahsayobs_getUserFromAhsayServer($params['clientsdetails']['userid']);
    //var_dump($smartyvalues['userData']);
	$pagearray = array(
		'pageicon' => $pageicon,
		'pagetitle' => $pagetitle,
		'templatefile' => 'ahsay',
		'breadcrumb' => ' > <a href="javascript:void(0);">Manage Backup Account</a>',
		'vars' => $smartyvalues
    );

	$smarty->register_block('ahsayobs_clientinfopage', 'ahsayobs_smartyInfoPageBlock');
	//$smarty->register_block('ahsayobs_productdetails', 'ahsayobs_smartyProductDetailsBlock');
	$smarty->register_prefilter('ahsayobs_removeSmartyPhpBlocks');

	return $pagearray;
}

function ahsayobs_getAddonLicenseInfo(){
	global $ahsay_addons;
	$results = array(
		'branding' => true
	);
	foreach($ahsay_addons as $name => $data){
		if (eregi('brand', $name)){
			if ($data['status'] == 'Active'){
				$results['branding'] = false;
			}
		}
		if (eregi('expir', $name)){
			$results['expire'] = false;
		}
	}
	return $results;
}
function ahsayobs_extra($params){
	return $pagearray = array(
		'templatefile' => 'ahsay',
		'breadcrumb' => ' > <a href="javascript:void(0);">Manage Backup Account</a>',
		'vars' => array(
				'licenseError' => $ahsayClientLanguage[$licError],
				'brand' => true
			),
    );
}
function ahsayobs_infopage($params) {
	//var_dump($params);
	return ahsayobs_getBackupAccountInfoPage($params);
}

function ahsayobs_reboot($params) {

	# Code to perform action goes here...

	if ($successful) {
		$result = "success";
	} else {
		$result = "Error Message Goes Here...";
	}
	return $result;
}

?>