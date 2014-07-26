<?php

//error_reporting(E_ALL);

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
	$data = ahsayobs_web_request('updater.ahsaytools.com','/update.ver.php');
	return array($data, version_compare($data, $current['version']));
}

function ahsayobs_get_atools_details(){
	$data = ahsayobs_web_request('updater.ahsaytools.com','/products.desc.php');
	return $data;
}

function ahsayobs_get_update_details(){
	$data = ahsayobs_web_request('updater.ahsaytools.com','/update.desc.php');
	return $data;
}

$licenseUrls = array('http://members.ahsaytools.com/');
function ahsayobs_check_license($licensekey,$localkey="") {
	global $licenseUrls;
	
	$whmcsurl = $licenseUrls[0];
    $licensing_secret_key = ""; # Unique value, should match what is set in the product configuration for MD5 Hash Verification
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
                    if (!in_array($_SERVER['SERVER_NAME'], $validdomains) && false) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                    $validips = explode(",",$results["validip"]);
		    /*
                    if (!in_array($usersip, $validips)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }*/
                    if ($results["validdirectory"]!=dirname(__FILE__)) {
                        $localkeyvalid = false;
                        $localkeyresults["status"] = "Invalid";
                        $results = array();
                    }
                }
            }
        }
    }

    if (!$localkeyvalid) {
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
	return true;
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

	if ($results["status"] == "Active") {
		$valid_domain = $_SERVER["HTTP_HOST"]; 
		$valid_directory = dirname(dirname(__FILE__)); 
		$valid_ip = $_SERVER['SERVER_ADDR'];
		
		if (strpos($results['validdomain'], $valid_domain) !== false || true){ 
			if (strpos($results['validip'], $valid_ip) !== false || true){
				if (strpos($results['validdirectory'], $valid_directory) !== false || true){
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
		'backupTabTitle' => $request['backupTabTitle'],
		'displayPassword' => $request['displayPassword'],
		'showProfileButton' => $request['showProfileButton'],
		'showLogButton' => $request['showLogButton']
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
		'defaultTimezone' => $request['defaultTimezone'],
		'defaultOwner' => $request['defaultOwner']
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

function ahsayobs_cssColorClassForPercentage($pct, $nr = false){
    if ($pct > 95 && !$nr){
        return 'red';
    } else if ($pct > 75){
        return 'orange';
    } else if ($pct > 25){
        return 'green';
    } else if ($pct > 0){
        return 'blue';
    }
    return 'blue';
}

function ahsayobs_cssClassForModuleIcon($module){
	$map = array(
		'OBM' => 'obm',
		'ACB' => 'acb',
		'MSExchange' => 'msexchange',
		'MSExchangeMail' => 'exmail',
		'MSSQL' => 'mssql',
		'Oracle' => 'oracle',
		'LotusDomino' => 'lotusDomino',
		'LotusNotes' => 'lotusNotes',
		'InFileDelta' => 'infileDelta',
		'VolumeShadowCopy' => 'vss',
		'MsVm' => 'msvm',
		'VMWare' => 'vmware',
		'DeltaMerge' => 'deltaMerging',
		'ClientJVMRoyalty' => '',
		'OracleUsers' => 'oracle',
		'MySQL' => 'mysql',
		'ShadowProtect',
		'WinServer2008BareMetal'
	);
	$class = 'mod_' . $map[$module] . '_enabled';
	return $class;
}

function ahsayobs_getGB($num){
    return round($num/1073741824,2);
}

function ahsayobs_bigNumberify($n){
    if ($n >= 1000000000){
        return round($n/1000000000,2) . 'B';
    }
    if ($n >= 1000000){
        return round($n/1000000,2) . 'M';
    }
    if ($n >= 10000){
        return round($n/1000,2) . 'K';
    }
    return $n;
}

function ahsayobs_getMB($num){
    return round($num/(1024*1024),2);
}

function ahsayobs_getKB($num){
    return round($num/(1024),2);
}

function ahsayobs_outputAsJson($data, $callback = false, $html = false, $fields = null) {
	if (is_array($data)){
		ahsayobs_cleanOutputFields($data, $fields);
	}
	if ($_REQUEST['debug']){
		var_dump($data);
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
