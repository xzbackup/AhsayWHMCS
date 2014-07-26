<?php
//error_reporting(E_ALL);
require_once(dirname(__FILE__) . '/_obs_include.php');
require_once(dirname(dirname(dirname(__FILE__))) . DIRECTORY_SEPARATOR . 'addons' . DIRECTORY_SEPARATOR . 'ahsayobs' . DIRECTORY_SEPARATOR . 'lang' . DIRECTORY_SEPARATOR . 'main.php');

$ahsayobs_licenseData = array();
$licError = null;
$checkLicense = false;
$ahsayobs_addons = array();
/**
 * ["addons"]=> string(128) "name=client area admin;nextduedate=2011-09-16;status=Pending|name=lic support and upgrades;nextduedate=2011-09-16;status=Pending"
 *
 */
$ahsayClientLanguage = array(
	'invalid_directory' => "AhsayWHMCS Billing Module License Suspended. Module not running from original licensed directory. Please re-issue.",
	'invalid_ip' => "AhsayWHMCS Billing Module License Suspended. Module not running from original licensed IP. Please re-issue.",
	'invalid_domain' => "AhsayWHMCS Billing Module License Suspended. Module not running from original licensed domain. Please re-issue.",
	'lic_invalid' => "AhsayWHMCS Billing Module License Is Invalid",
	'lic_expired' => "AhsayWHMCS Billing Module License Is Expired",
	'lic_suspended' => "AhsayWHMCS Billing Module License Is Suspended"
);

class User {
	var $userdata = array();
	var $username;
	var $password;
	var $contact_info;
	var $api;

	var $api_attributes	 = array(
		"username"=>"LoginName",
		"password" => "Password",
		"alias"=>"Alias",
		"type" => "UserType",
		"client" => "ClientType",
		"quota" => "Quota",
		"timezone" => "Timezone",
		"language" => "Language",
		"datafile" => "DataFile",
		"datasize" => "DataSize",
		"retainfile" => "RetainFile",
		"retainsize" => "RetainSize",
		"e_mssql" => "EnableMSSQL",
		"e_msexch" => "EnableMSExchange",
		"e_oracle" => "EnableOracle",
		"e_lotusnotes" => "EnableLotusNotes",
		"e_lotusdomino" => "EnableLotusDomino",
		"e_filedelta" => "EnableInFileDelta",
		"e_shadowcopy" =>"EnableShadowCopy",
		"e_bricks" => "EnableExchangeMailbox",
		"e_shadowprotect" => "EnableShadowProtectBareMetal",
		"e_winserverbaremetal" => "EnableWinServer2008BareMetal",
		"e_cdp" => "EnableCDP",
		"e_brickquota" => "ExchangeMailboxQuota",
		"e_nasclient" => "EnableNASClient",
		"e_deltamerge" => "EnableDeltaMerge",
		"e_msvm" => "EnableMsVm",
		"e_vmware" => "EnableVMware",
		"e_vmwarequota" => "VMwareQuota",
		'replicated' => 'Replicated',
		"bandwidth" => "Bandwidth",
		"notes" => "Notes",
		"status" => "Status",
		"regdate" => "RegistrationDate",
		"email" => "Email",
		"userhome" => "UserHome",
		"sendemail" => "SendWelcomeMail"
	);

	var $config_options = array(
		2 => 'ClientType',
		3 => 'Type',
		5 => 'Quota',
		6 => 'EnableMSExchange',
		7 => 'EnableMSSQL',
		8 => 'EnableOracle',
		9 => 'EnableMySQL',
		10 => 'EnableLotusDomino',
		11 => 'EnableLotusNotes',
		12 => 'EnableInFileDelta',
		13 => 'EnableShadowCopy',
		14 => 'EnableExchangeMailbox',
		15 => 'ExchangeMailboxQuota',
		16 => 'EnableCDP',
		17 => 'EnableShadowProtectBareMetal',
		18 => 'EnableWinServer2008BareMetal',
		19 => 'EnableNASClient',
		20 => 'EnableDeltaMerge',
		21 => 'EnableMsVm',
		22 => 'EnableVMware',
		23 => 'VMwareQuota',
		24 => 'AdGroup',
		25 => 'UserHome'
	);

	function User(&$api){
		$this->userdata['Alias'] = 'New User';
		extract(ahsayobs_getUserSettings());

		$this->userdata['Language'] = (!empty($defaultLanguage)) ? $defaultLanguage : 'en';
		$this->userdata['Timezone'] = (!empty($defaultTimezone)) ? $defaultTimezone : 'GMT -05:00 (EST)';
		$this->userdata['Bandwidth'] = (!empty($defaultBandwidth)) ? $defaultBandwidth : '0';

		$this->userdata['Type'] = 'PAID';
		$this->userdata['ClientType'] = 'OBM';
		$this->userdata['AdGroup'] = 'ALL';
		$this->userdata['Quota'] = '104857600';
		$this->userdata['UserHome'] = '';
		$this->userdata['EnableMSSQL'] = 'Y';
		$this->userdata['EnableMSExchange'] = 'Y';
		$this->userdata['EnableOracle'] = 'Y';
		$this->userdata['EnableLotusNotes'] = 'Y';
		$this->userdata['EnableCDP'] = 'Y';
		$this->userdata['EnableLotusDomino'] = 'Y';
		$this->userdata['EnableMySQL'] = 'Y';
		$this->userdata['EnableInFileDelta'] = 'Y';
		$this->userdata['EnableShadowCopy'] = 'Y';
		$this->userdata['EnableExchangeMailbox'] = 'Y';
		$this->userdata['EnableShadowProtectBareMetal'] = 'Y';
		$this->userdata['EnableWinServer2008BareMetal'] = 'Y';
		$this->userdata['EnableNASClient'] = 'Y';
		$this->userdata['EnableDeltaMerge'] = 'Y';
		$this->userdata['EnableMsVm'] = 'Y';
		$this->userdata['EnableVMware'] = 'Y';
		$this->userdata['ExchangeMailboxQuota'] = 0;
		$this->userdata['VMwareQuota'] = 0;
		
		$this->userdata['Status'] = 'ENABLE';
		$this->userdata['Notes'] = 'New User';
		$this->userdata['Email'] = 'user@domain.com';
		$this->userdata['LoginName'] = &$this->username;
		$this->userdata['Password'] = &$this->password;
		$this->api = &$api;
	}
	function parseOptions($params){
		if (is_array($params)){
			foreach($this->config_options as $num => $value){
				if ($num == 15) { $this->userdata[$value] = $params['configoption' . $num] == '' ? 0 : $params['configoption' . $num]; continue;}
				if (($num > 5 && $num < 22) || $num == 4){
					$this->userdata[$value] = $params['configoption' . $num] == 'on' ? 'Y' : 'N';
					continue;
				}
				if ($num == 5) { $this->userdata[$value] = $params['configoption' . $num] * 1073741824; continue; }
				if ($num == 3) { $this->userdata[$value] = $params['configoption' . $num] == 'on' ? 'TRIAL' : 'PAID'; continue; }
				if ($num == 24) { $this->userdata[$value] = $params['configoption' . $num] == '' ? 'NONE' : $params['configoption' . $num]; continue; }
				//if ($num == 23) { $this->userdata[$value] = $params['configoption' . $num] == '' ? 'NONE' : $params['configoption' . $num]; continue; }

				$this->userdata[$value] = $params['configoption' . $num];

			}
		}
		$this->username = $params['customfields']['Username'];
		$this->password = $params['customfields']['Password'];
		$details = $params['clientsdetails'];
		

		$configoptions = $params["configoptions"];
		$addonspace =  0;
		foreach($configoptions as $k => $v){
			if (stripos($k, "GB") && (is_numeric($v) && $v > 0)){
				if (preg_match('/([0-9]+)GB/i', $k, $match)){
					if (isset($match[1])){
						$addonspace += ($match[1] * $v);
					}
				} else if (preg_match('/([0-9]+) GB/i', $k, $match)){
					if (isset($match[1])){
						$addonspace += ($match[1] * $v);
					}
				} else {
					$addonspace += $v;
				}
			}
			if (stripos($k, "VM") && (is_numeric($v) && $v > 0)){
				if (preg_match('/([0-9]+)VM/i', $k, $match)){
					if (isset($match[1])){
						$addonvm += ($match[1] * $v);
					}
				} else if (preg_match('/([0-9]+) VM/i', $k, $match)){
					if (isset($match[1])){
						$addonvm += ($match[1] * $v);
					}
				} else {
					$addonvm += $v;
				}
			}
			if (stripos($k, "Mailbox") && (is_numeric($v) && $v > 0)){
				if (preg_match('/([0-9]+)[Mailbox|Exchange]/i', $k, $match)){
					if (isset($match[1])){
						$addonmail += ($match[1] * $v);
					}
				} else if (preg_match('/([0-9]+) [Mailbox|Exchange]/i', $k, $match)){
					if (isset($match[1])){
						$addonmail += ($match[1] * $v);
					}
				} else {
					$addonmail += $v;
				}
			}
		}

		$this->userdata['VMwareQuota'] += $addonvm;
		$this->userdata['ExchangeMailboxQuota'] += $addonmail;

		$addonspace = $addonspace * 1073741824;
		$quota = $this->userdata['Quota'];
		$this->userdata['Quota'] += $addonspace;

		//file_put_contents('/tmp/test', serialize($this->userdata));

		if (isset($details['firstname']) && isset($details['lastname']) && isset($details['email'])){
			$this->userdata['Alias'] = $details['firstname'] . " " . $details['lastname'];
			$this->userdata['Email'] = $details['email'];
		}

	}
	function loadUserFromArray($user_array){
		foreach ($this->api_attributes as $key => $value){
			$this->userdata[$value] = $user_array[$value];
		}
	}
}
function ahsayobs_web_request($host, $url, $postfields){
	if (function_exists("curl_exec")) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $host . $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);
    } else {
        $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
        if ($fp) {
    		$querystring = "";
            foreach ($postfields AS $k=>$v) {
                $querystring .= "$k=".urlencode($v)."&";
            }
            $header="POST ".$host . $url . " HTTP/1.0\r\n";
    		$header.="Host: ".$host."\r\n";
    		$header.="Content-type: application/x-www-form-urlencoded\r\n";
    		$header.="Content-length: ".@strlen($querystring)."\r\n";
    		$header.="Connection: close\r\n\r\n";
    		$header.=$querystring;
    		$data="";
    		@stream_set_timeout($fp, 20);
    		@fputs($fp, $header);
    		$status = @socket_get_status($fp);
    		while (!@feof($fp)&&$status) {
    		    $data .= @fgets($fp, 1024);
    			$status = @socket_get_status($fp);
    		}
    		@fclose ($fp);
        }
    }
    return $data;
}
function ahsayobs_check_updates(){
	$current = ahsayobs_config();
	$data = ahsayobs_web_request('updater.ahsaytools.com','/update.ver');
	return array($data, version_compare($data, $current['version']));
}
function ahsayobs_get_atools_details(){
	$data = ahsayobs_web_request('updater.ahsaytools.com','/products.desc');
	return $data;
}
function ahsayobs_get_update_details(){
	$data = ahsayobs_web_request('updater.ahsaytools.com','/update.desc');
	return $data;
}

$licenseUrls = array('http://billing.xzbackup.com/','http://billing.ahsaytools.com');
function ahsayobs_check_license($licensekey,$localkey="") {
	global $licenseUrls;
	$whmcsurl = array_shift($licenseUrls);
    //$whmcsurl = "http://billing.xzbackup.com/";
    $licensing_secret_key = "Qpc0694ln5ccOg7N"; # Unique value, should match what is set in the product configuration for MD5 Hash Verification
    $check_token = time().md5(mt_rand(1000000000,9999999999).$licensekey);
    $checkdate = date("Ymd"); # Current date
    $usersip = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : $_SERVER['LOCAL_ADDR'];
    $localkeydays = 5; # How long the local key is valid for in between remote checks
    $allowcheckfaildays = 2; # How many days to allow after local key expiry before blocking access if connection cannot be made
    $localkeyvalid = false;
    if ($localkey) {
        $localkey = str_replace("\n",'',$localkey); # Remove the line breaks
		$localdata = substr($localkey,0,strlen($localkey)-32); # Extract License Data
		$md5hash = substr($localkey,strlen($localkey)-32); # Extract MD5 Hash
        if ($md5hash==md5($localdata.$licensing_secret_key)) {
            $localdata = strrev($localdata); # Reverse the string
    		$md5hash = substr($localdata,0,32); # Extract MD5 Hash
    		$localdata = substr($localdata,32); # Extract License Data
    		$localdata = base64_decode($localdata);
    		$localkeyresults = unserialize($localdata);
            $originalcheckdate = $localkeyresults["checkdate"];
            if ($md5hash==md5($originalcheckdate.$licensing_secret_key)) {
                $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-$localkeydays,date("Y")));
                if ($originalcheckdate>$localexpiry) {
                    $localkeyvalid = true;
                    $results = $localkeyresults;
                    $validdomains = explode(",",$results["validdomain"]);
                    if (!in_array($_SERVER['SERVER_NAME'], $validdomains)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                    $validips = explode(",",$results["validip"]);
                    if (!in_array($usersip, $validips)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                    if ($results["validdirectory"]!=dirname(__FILE__)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                }
            }
        }
    }
    if (!$localkeyvalid || true) {
        $postfields["licensekey"] = $licensekey;
        $postfields["domain"] = $_SERVER['SERVER_NAME'];
        $postfields["ip"] = $usersip;
        $postfields["dir"] = dirname(__FILE__);
        if ($check_token) $postfields["check_token"] = $check_token;
        if (function_exists("curl_exec")) {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $whmcsurl."modules/servers/licensing/verify.php");
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $data = curl_exec($ch);
            curl_close($ch);
        } else {
            $fp = fsockopen($whmcsurl, 80, $errno, $errstr, 5);
	        if ($fp) {
        		$querystring = "";
                foreach ($postfields AS $k=>$v) {
                    $querystring .= "$k=".urlencode($v)."&";
                }
                $header="POST ".$whmcsurl."modules/servers/licensing/verify.php HTTP/1.0\r\n";
        		$header.="Host: ".$whmcsurl."\r\n";
        		$header.="Content-type: application/x-www-form-urlencoded\r\n";
        		$header.="Content-length: ".@strlen($querystring)."\r\n";
        		$header.="Connection: close\r\n\r\n";
        		$header.=$querystring;
        		$data="";
        		@stream_set_timeout($fp, 20);
        		@fputs($fp, $header);
        		$status = @socket_get_status($fp);
        		while (!@feof($fp)&&$status) {
        		    $data .= @fgets($fp, 1024);
        			$status = @socket_get_status($fp);
        		}
        		@fclose ($fp);
            }
        }
        if (!$data) {
            $localexpiry = date("Ymd",mktime(0,0,0,date("m"),date("d")-($localkeydays+$allowcheckfaildays),date("Y")));
            if ($originalcheckdate>$localexpiry) {
                $results = $localkeyresults;
            } else {
                $results["status"] = "Invalid";
                $results["description"] = "Remote Check Failed";
                return $results;
            }
        } else {
            preg_match_all('/<(.*?)>([^<]+)<\/\\1>/i', $data, $matches);
            $results = array();
            foreach ($matches[1] AS $k=>$v) {
                $results[$v] = $matches[2][$k];
            }
        }
        if ($results["md5hash"]) {
            if ($results["md5hash"]!=md5($licensing_secret_key.$check_token)) {
                $results["status"] = "Invalid";
                $results["description"] = "MD5 Checksum Verification Failed";
                return $results;
            }
        }
        if ($results["status"]=="Active") {
            $results["checkdate"] = $checkdate;
            $data_encoded = serialize($results);
            $data_encoded = base64_encode($data_encoded);
            $data_encoded = md5($checkdate.$licensing_secret_key).$data_encoded;
            $data_encoded = strrev($data_encoded);
            $data_encoded = $data_encoded.md5($data_encoded.$licensing_secret_key);
            $data_encoded = wordwrap($data_encoded,80,"\n",true);
            $results["localkey"] = $data_encoded;
        }
        $results["remotecheck"] = true;
    }
    unset($postfields,$data,$matches,$whmcsurl,$licensing_secret_key,$checkdate,$usersip,$localkeydays,$allowcheckfaildays,$md5hash);
    return $results;
}

$trialTime = 15;
function ahsayobs_CheckLicense(){
	global $ahsayobs_licenseData,$licError,$ahsayobss, $ahsayBillLicenseKey, $licenseKey, $localKey;
	$addonData = array();
	if (empty($localKey) && empty($licenseKey)){
		$query = select_query('tbladdonmodules', 'setting,value', array('module' => 'ahsayobs'));
		while($queryData = mysql_fetch_array($query)){ $addonData[$queryData['setting']] = $queryData['value']; };
		extract($addonData);
	}

	$results = ahsayobs_check_license($licenseKey,$localKey);
	if (!$results){
		$results = ahsayobs_check_license($licenseKey,$localKey);
	}
	$reggedDate = date("U", $results['regdate'] . "00:00:00");
	$expireDate = $reggedDate + 1296000;
	$now = date("U");
	if ($now > $expireDate){
		$expired = true;
	}
	$ahsayobs_licenseData = $results;
	if (array_key_exists('addons', $results)){
		$addons = explode("|", $results['addons']);
		foreach($addons as $num => $addon){
			$addonData = explode(';',$addon);
			foreach($addonData as $ad){
				list($name, $value) = explode("=", $ad);
				if ($name == 'name'){
					$cadd = $value;
				}
				$addonOutput[$name] = $value;
			}
			$ahsayobss[$cadd] = $addonOutput;
		}
		
	}
	if ($results["status"]=="Active") {
		
		$valid_domain = $_SERVER["HTTP_HOST"]; 
		$valid_directory = dirname(__FILE__); 
		$valid_ip = $_SERVER['SERVER_ADDR']; 

		if ($valid_domain = $results["validdomain"]) { 
			if ($valid_ip == $results["validip"]) { 
				if ($valid_directory == $results["validdirectory"]){
					if ($results["localkey"]) {
				        # Save Updated Local Key to DB or File

				        $localkeydata = $results["localkey"];
						ahsayobs_updateLocalKey($localkeydata);
						
				    }
					return true;
				} else {
					$licError = "invalid_directory";
				}
			} else {
				$licError = "invalid_ip";
			}
		} else {
			$licError = "invalid_domain";
		}	   
		
	} elseif ($results["status"]=="Invalid") {
	    $licError = "lic_invalid";
	} elseif ($results["status"]=="Expired") {
	    $licError = "lic_expired";
	} elseif ($results["status"]=="Suspended") {
	    $licError = "lic_suspended";
	}
	return false;
}


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
	 "In-File Delta" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Volume Shadow Copy" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Microsoft Exchange Mailbox" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Microsoft Exchange Mailboxes" => array("Type" => "text","Size" => 5, "Description" => "Mailbox Amount"),
	 "Continuous Data Protection" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Shadow Protect Bare Metal" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Windows 2008 Bare Metal" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "NAS Client" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "Delta Merge" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "MS VM" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "VMware" => array("Type" => "yesno", "Description" => "Enabled?"),
	 "VMware VM Quota" => array("Type" => "text","Size" => 5, "Description" => "VM Amount"),
	 "Adgroup" => array("Type" => "text", "Size" => "25"),
	 "User Home" => array("Type" => "text", "Size" => "50"),
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

function ahsayobs_getUserAhsayPackages($userid){
	$where = array('userid' => $userid);
	$userQ = select_query('tblhosting', '*', $where);

	$pkgs = array();
	while($user = mysql_fetch_assoc($userQ)){
		$where = array('id' => $user['packageid']);
		$packageQ = select_query('tblproducts', 'servertype', $where);
		$package = mysql_fetch_assoc($packageQ);
		if ($package['servertype'] == 'ahsayobs'){
			$pkgs[] = $user;
		}
	}
	return $pkgs;
}

function ahsayobs_getAhsayPackages($pkgid = null){
	if ($pkgid){
		$where = array('id' => $pkgid);
	} else {
		$where = array('servertype' => 'ahsayobs');
	}
	$userQ = select_query('tblproducts', '*', $where, 'order', 'ASC');

	$pkgs = array();
	while($pkg = mysql_fetch_assoc($userQ)){
		$pkgs[$pkg['id']] = $pkg;
		$pkgs[$pkg['id']]['customfields'] = ahsayobs_getCustomFields($pkg['id']);
	}
	return $pkgs;
}

function ahsayobs_getCustomFields($pkg){
	$fieldQ = select_query('tblcustomfields', '*', array('relid' => $pkg));

	$fields = array();
	while($field = mysql_fetch_assoc($fieldQ)){
		$fields[$field['id']] = $field;
	}
	return $fields;
}

function ahsayobs_getUserFromAhsayServer($userid){
	$where = array('userid' => $userid);
	$userQ = select_query('tblhosting', '*', $where);

	$users = array();
	$ahsayPkgs = ahsayobs_getAhsayPackages();
	while($user = mysql_fetch_assoc($userQ)){
		if (!array_key_exists($user['packageid'],$ahsayPkgs)) continue;
		$where = array('id' => $user['server']);
		$serverQ = select_query('tblservers', '*', $where);
		$server = mysql_fetch_assoc($serverQ);
		$password = localAPI('decryptpassword', array('password2' => $server['password']));

		$obs = new OBS();
		$obs->setServer($server['username'], $password['password'], $server['ipaddress'], $server['secure'] == 'on' ? 1 : 0);
		$user = $obs->GetUser($user['username']);
		$users[] = $user;
	}
	return $users;
}
function ahsayobs_getUsersFromAhsayServer($serverid){
	$where = array('id' => $serverid);
	$serverQ = select_query('tblservers', '*', $where);
	$server = mysql_fetch_assoc($serverQ);
	
	$password = localAPI('decryptpassword', array('password2' => $server['password']));

	$obs = new OBS();
	$obs->setServer($server['username'], $password['password'], $server['ipaddress'], $server['secure'] == 'on' ? 1 : 0);
	$user = $obs->ListUsers();
	$users = array();
	foreach($user as $u){

		$users[$u->username] = $u;
	}
	return $users;
}

function ahsayobs_getServers(){
	$where = array('type' => 'ahsayobs');
	$serverQ = select_query('tblservers', '*', $where);
	$servers = array();
	while($server = mysql_fetch_assoc($serverQ)){
		$servers[$server['id']] = $server;
	}
	return $servers;
}

function ahsayobs_get_server($server){
    $serverId = $server;
    $where = array('id' => $serverId);
    $query = select_query('tblservers','*', $where);
    $server = mysql_fetch_array($query);
    $password = localAPI('decryptpassword', array('password2' => $server['password']));
    $serverInfo = array('serverhostname' => $server['hostname'], 'serverusername' => $server['username'], 'serverpassword' => $password['password']);
    return array($server, $serverInfo);
}

function ahsayobs_getServerLicensingInfo(){
	$servers = ahsayobs_getServers();
	$licenses = array();
	foreach($servers as $num => $server){
		$obs = new OBS();
		$password = localAPI('decryptpassword', array('password2' => $server['password']));
		$obs->setServer($server['username'], $password['password'], $server['ipaddress'], $server['secure'] == 'on' ? 1 : 0);
		$licenseInfo = $obs->GetLicense();
		$servers[$num]['licenses'] = ahsayobs_normalizeLicensesInfo($licenseInfo);
	}
	return $servers;
}

function ahsayobs_getUsersFromServer($serverInfo){
	$obs = new OBS();
	$obs->setServer($serverInfo['serverusername'], $serverInfo['serverpassword'], $serverInfo['serverhostname'], 0);
	$userInfo = $obs->ListUsers();

	$userInfo = ahsayobs_cleanResponses(collapsePluralsAndSingulars($userInfo['Users'][0]['User']));
	return $userInfo;
}

function ahsayobs_normalizeLicensesInfo($license){
	$license = array_pop($license['License']);
	$licenseData = array();
	foreach($license as $k => $info){
		if ($k == '_attributes'){
			$licenseData = array_merge($licenseData, $info);
		} else {
			$data = array_pop($info);
			$licenseData['licensing'][$k] = $data['_attributes'];
		}
	}
	return $licenseData;
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
	$code = '';
	return $code;
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
function ahsayobs_normalizeSizes($usage, $units){
	if ($units == 'Bytes'){
		$usageUnit = 'b';
		$rawSize = $usage;
	} else if ($units == 'KiloBytes'){
		$usageUnit = 'Kb';
		$rawSize = $usage * (1024);
	} else if ($units == 'MegaBytes'){
		$usageUnit = 'Mb';
		$rawSize = $usage * (1024 * 1024);
	} else if ($units == 'GigaBytes'){
		$usageUnit = 'Gb';
		$rawSize = $usage * (1024 * 1024 * 1024);
	} else if ($units == 'TeraBytes'){
		$usageUnit = 'Tb';
		$rawSize = $usage * (1024 * 1024 * 1024 * 1024);
	} else if ($units == 'PetaBytes'){
		$usageUnit = 'Pb';
		$rawSize = $usage * (1024 * 1024 * 1024 * 1024 * 1024);
	}
	return array($rawSize, $usageUnit);
}

function ahsayobs_updateLocalKey($key){
	//$currentKey = select_query('tbladdonmodules','setting,value', array('module' => 'ahsayobs','setting' => 'localKey'));
	$newKey = mysql_query('DELETE FROM `tbladdonmodules` WHERE `tbladdonmodules`.`module` = "ahsayobs" AND `tbladdonmodules`.`setting` = "localKey" LIMIT 1');
	$newKey = insert_query('tbladdonmodules', array('module' => 'ahsayobs','setting' => 'localKey', 'value' => $key));
}
function ahsayobs_updateLicenseKey($key){
	//$currentKey = select_query('tbladdonmodules','setting,value', array('module' => 'ahsayobs','setting' => 'localKey'));
	ahsayobs_updateLocalKey("");
	$newKey = mysql_query('DELETE FROM `tbladdonmodules` WHERE `tbladdonmodules`.`module` = "ahsayobs" AND `tbladdonmodules`.`setting` = "licenseKey" LIMIT 1');
	$newKey = insert_query('tbladdonmodules', array('module' => 'ahsayobs','setting' => 'licenseKey', 'value' => $key));
}
function ahsayobs_getHelpTabText(){
	
	$newKey = mysql_query('SELECT value FROM `tbladdonmodules` WHERE `tbladdonmodules`.`module` = "ahsayobs" AND `tbladdonmodules`.`setting` = "help_tab" LIMIT 1');
	$data = mysql_fetch_array($newKey);
	if (empty($data['value'])){
		$result = mysql_query("INSERT INTO `tbladdonmodules` (`module`, `setting`, `value`) VALUES ('ahsayobs', 'help_tab', UNHEX('266C743B7461626C6520636C6173733D2671756F743B67656E6572616C2671756F743B20626F726465723D2671756F743B302671756F743B2063656C6C73706163696E673D2671756F743B302671756F743B2077696474683D2671756F743B313030252671756F743B2667743B0D0A266C743B74626F64792667743B0D0A266C743B74722667743B0D0A266C743B746420726F777370616E3D2671756F743B332671756F743B2667743B266C743B7374726F6E672667743B4261636B757020536F667477617265202D20557365722773204775696465266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B746420636F6C7370616E3D2671756F743B322671756F743B2667743B266C743B6120636C6173733D2671756F743B6172726F77656E642671756F743B20687265663D2671756F743B687474703A2F2F6D616E75616C732E776562736974652E636F6D2F7573657267756964652F656E2F2671756F743B207461726765743D2671756F743B5F626C616E6B2671756F743B2667743B56494557204F4E4C494E45266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B746420636C6173733D2671756F743B66697273742671756F743B2667743B332E39204D42266C743B2F74642667743B0D0A266C743B74642667743B266C743B6120636C6173733D2671756F743B646F776E6C6F61642671756F743B20687265663D2671756F743B687474703A2F2F7777772E776562736974652E636F6D2F757365722D67756964652E7A69702671756F743B2667743B444F574E4C4F41442048544D4C20285A495029266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B7472207374796C653D2671756F743B626F726465722D626F74746F6D3A32707820736F6C696420233030303B2671756F743B2667743B0D0A266C743B746420636C6173733D2671756F743B66697273742671756F743B2667743B332E36204D42266C743B2F74642667743B0D0A266C743B74642667743B266C743B6120636C6173733D2671756F743B646F776E6C6F61642671756F743B20687265663D2671756F743B687474703A2F2F7777772E776562736974652E636F6D2F757365722D67756964652E7064662671756F743B2667743B444F574E4C4F414420504446266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B746420726F777370616E3D2671756F743B342671756F743B2667743B266C743B7374726F6E672667743B486F6D652045646974696F6E20446F776E6C6F6164266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B746420636F6C7370616E3D2671756F743B322671756F743B2667743B266C743B7374726F6E672667743B57696E646F7773266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B746420636C6173733D2671756F743B66697273742671756F743B2667743B3530204D42266C743B2F74642667743B0D0A266C743B74642667743B266C743B6120636C6173733D2671756F743B646F776E6C6F61642671756F743B20687265663D2671756F743B687474703A2F2F7777772E776562736974652E636F6D2F686F6D652D77696E646F77732D736F6674776172652E6578652671756F743B2667743B444F574E4C4F4144266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B746420636C6173733D2671756F743B66697273742671756F743B2667743B266C743B7374726F6E672667743B4D6163204F532058266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B74642667743B266C743B6272202F2667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B7472207374796C653D2671756F743B626F726465722D626F74746F6D3A32707820736F6C696420233030303B2671756F743B2667743B0D0A266C743B74642667743B3130204D42266C743B2F74642667743B0D0A266C743B74642667743B266C743B6120636C6173733D2671756F743B646F776E6C6F61642671756F743B20687265663D2671756F743B687474703A2F2F7777772E776562736974652E636F6D2F686F6D652D6D61632D736F6674776172652E7A69702671756F743B2667743B444F574E4C4F4144266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B746420726F777370616E3D2671756F743B382671756F743B2667743B266C743B7374726F6E672667743B427573696E6573732045646974696F6E20446F776E6C6F6164266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B746420636F6C7370616E3D2671756F743B322671756F743B2667743B266C743B7374726F6E672667743B57696E646F7773266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B746420636C6173733D2671756F743B66697273742671756F743B2667743B3530204D42266C743B2F74642667743B0D0A266C743B74642667743B266C743B6120636C6173733D2671756F743B646F776E6C6F61642671756F743B20687265663D2671756F743B687474703A2F2F7777772E776562736974652E636F6D2F627573696E6573732D77696E2D736F6674776172652E6578652671756F743B2667743B444F574E4C4F4144266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B746420636C6173733D2671756F743B66697273742671756F743B2667743B266C743B7374726F6E672667743B4D6163204F532058266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B74642667743B266C743B6272202F2667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B74642667743B3130204D42266C743B2F74642667743B0D0A266C743B74642667743B266C743B6120636C6173733D2671756F743B646F776E6C6F61642671756F743B20687265663D2671756F743B687474703A2F2F7777772E776562736974652E636F6D2F627573696E6573732D6D61632D736F6674776172652E7A69702671756F743B2667743B444F574E4C4F4144266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B74642667743B266C743B7374726F6E672667743B4C696E7578266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B74642667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B74642667743B3730204D42266C743B2F74642667743B0D0A266C743B74642667743B266C743B6120636C6173733D2671756F743B646F776E6C6F61642671756F743B20687265663D2671756F743B687474703A2F2F7777772E776562736974652E636F6D2F627573696E6573732D6C696E75782D736F6674776172652E7461722E677A2671756F743B2667743B444F574E4C4F4144266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B74642667743B266C743B7374726F6E672667743B4E6F76656C6C266C743B2F7374726F6E672667743B266C743B2F74642667743B0D0A266C743B74642667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B74722667743B0D0A266C743B74642667743B3330204D42266C743B2F74642667743B0D0A266C743B74642667743B266C743B6120636C6173733D2671756F743B646F776E6C6F61642671756F743B20687265663D2671756F743B687474703A2F2F7777772E776562736974652E636F6D2F627573696E6573732D6E6F76656C6C2D736F6674776172652E7A69702671756F743B2667743B444F574E4C4F4144266C743B2F612667743B266C743B2F74642667743B0D0A266C743B2F74722667743B0D0A266C743B2F74626F64792667743B0D0A266C743B2F7461626C652667743B'));");
		$newKey = mysql_query('SELECT value FROM `tbladdonmodules` WHERE `tbladdonmodules`.`module` = "ahsayobs" AND `tbladdonmodules`.`setting` = "help_tab" LIMIT 1');
		$data = mysql_fetch_array($newKey);
	}
	return $data['value'];
}
function ahsayobs_updateHelpTabText($text){
	$newKey = mysql_query('DELETE FROM `tbladdonmodules` WHERE `tbladdonmodules`.`module` = "ahsayobs" AND `tbladdonmodules`.`setting` = "help_tab" LIMIT 1');
	$newKey = insert_query('tbladdonmodules', array('module' => 'ahsayobs','setting' => 'help_tab', 'value' => $text));
}
function ahsayobs_updateSettings($request){
	$data = array(
		'rdrServer' => $request['rdrServer'],
		'sslServer' => $request['sslServer'],
		'directUrl' => $request['directUrl'],
		'backupTab' => $request['backupTab'],
		'backupTabTitle' => $request['backupTabTitle']
	);
	$newKey = mysql_query('DELETE FROM `tbladdonmodules` WHERE `tbladdonmodules`.`module` = "ahsayobs" AND `tbladdonmodules`.`setting` = "settings" LIMIT 1');
	$newKey = insert_query('tbladdonmodules', array('module' => 'ahsayobs','setting' => 'settings', 'value' => serialize($data)));
	return true;
}

function ahsayobs_getSettings(){
	$query = select_query('tbladdonmodules','*', array('module' => 'ahsayobs','setting' => 'settings'));
	$result = mysql_fetch_assoc($query);
	//var_dump($result);
	return unserialize($result['value']);
}
function ahsayobs_updateUserSettings($request){
	$data = array(
		'defaultBandwidth' => $request['defaultBandwidth'],
		'defaultLanguage' => $request['defaultLanguage'],
		'defaultTimezone' => $request['defaultTimezone']
	);
	$newKey = mysql_query('DELETE FROM `tbladdonmodules` WHERE `tbladdonmodules`.`module` = "ahsayobs" AND `tbladdonmodules`.`setting` = "user_settings" LIMIT 1');
	$newKey = insert_query('tbladdonmodules', array('module' => 'ahsayobs','setting' => 'user_settings', 'value' => serialize($data)));
	return true;
}

function ahsayobs_getUserSettings(){
	$query = select_query('tbladdonmodules','*', array('module' => 'ahsayobs','setting' => 'user_settings'));
	$result = mysql_fetch_assoc($query);
	//var_dump($result);
	return unserialize($result['value']);
}

function ahsayobs_getBackupAccountInfoPage($params){
	global $LANG, $ahsay_licenseData;
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
    $smartyvalues['userData'] = ahsayobs_getUserFromAhsayServer($params['clientsdetails']['userid']);
    //var_dump($smartyvalues['userData']);
	$pagearray = array(
		'pageicon' => $pageicon,
		'pagetitle' => $pagetitle,
		'templatefile' => 'ahsay',
		'breadcrumb' => ' > <a href="javascript:void(0);">Manage Backup Account</a>',
		'vars' => $smartyvalues,
    );

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
if (!function_exists('fe') && !function_exists('fd')){
	if(function_exists('gzcompress')){
		function fe($data){return strtr(base64_encode(addslashes(gzcompress(serialize($data),9))),'+/=','-_,');}
		function fd($data){return unserialize(gzuncompress(stripslashes(base64_decode(strtr($data,'-_,','+/=')))));}
	} else {
		function lzw_compress($string) {
			$dictionary = array_flip(range("\0", "\xFF"));
			$word = "";
			$codes = array();
			for ($i=0; $i <= strlen($string); $i++) {
				$x = $string[$i];
				if (strlen($x) && isset($dictionary[$word . $x])) {
					$word .= $x;
				} elseif ($i) {
					$codes[] = $dictionary[$word];
					$dictionary[$word . $x] = count($dictionary);
					$word = $x;
				}
			}

			$dictionary_count = 256;
			$bits = 8; 
			$return = "";
			$rest = 0;
			$rest_length = 0;
			foreach ($codes as $code) {
				$rest = ($rest << $bits) + $code;
				$rest_length += $bits;
				$dictionary_count++;
				if ($dictionary_count >> $bits) {
					$bits++;
				}
				while ($rest_length > 7) {
					$rest_length -= 8;
					$return .= chr($rest >> $rest_length);
					$rest &= (1 << $rest_length) - 1;
				}
			}
			return $return . ($rest_length ? chr($rest << (8 - $rest_length)) : "");
		}

		function lzw_decompress($binary) {
			$dictionary_count = 256;
			$bits = 8;
			$codes = array();
			$rest = 0;
			$rest_length = 0;
			for ($i=0; $i < strlen($binary); $i++) {
				$rest = ($rest << 8) + ord($binary[$i]);
				$rest_length += 8;
				if ($rest_length >= $bits) {
					$rest_length -= $bits;
					$codes[] = $rest >> $rest_length;
					$rest &= (1 << $rest_length) - 1;
					$dictionary_count++;
					if ($dictionary_count >> $bits) {
						$bits++;
					}
				}
			}
			$dictionary = range("\0", "\xFF");
			$return = "";
			foreach ($codes as $i => $code) {
				$element = $dictionary[$code];
				if (!isset($element)) {
					$element = $word . $word[0];
				}
				$return .= $element;
				if ($i) {
					$dictionary[] = $word . $element[0];
				}
				$word = $element;
			}
			return $return;
		}
		function fe($data){return strtr(base64_encode(addslashes(lzw_compress(serialize($data),9))),'+/=','-_,');}
		function fd($data){return unserialize(lzw_decompress(stripslashes(base64_decode(strtr($data,'-_,','+/=')))));}
	}
}

$outputFields = null;
function ahsayobs_cleanFields(&$i, $k, $fields){
	if (in_array($k, $fields) && !is_numeric($k)){
		if (is_array($i)){
			$i = array();
		} else {
			$i = '!!HIDDEN!!';
		}
	}
}

function ahsayobs_cleanOutputFields(&$array, $fields = null){
	global $outputFields;
	if (!$fields){
		$fields = array('password','Obsserver','ssh_pass','ssh_port','ssh_user');
	}
	$outputFields = null;
	array_walk_recursive($array, 'ahsayobs_cleanFields', $fields);
	return $array;
}

function ahsayobs_outputAsJson($data, $callback = false, $html = false, $fields = null) {
	if (is_array($data)){
		ahsayobs_cleanOutputFields($data, $fields);
	}
	if ($_REQUEST['debug']){
		//var_dump($data);
	}
	if (!$html)
		header('Content-type: application/json');
	else
		header('Content-type: application/html');
		
	if ($callback) {
		$callback = $_POST['callback'];
		echo "$callback" . '(' . json_encode($data) . ')';
    } else {
        echo json_encode($data);
    }
}
?>
