<?

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
		12 => 'EnableShadowProtectBareMetal',
		13 => 'EnableWinServer2008BareMetal',
		14 => 'EnableNASClient',
		15 => 'EnableInFileDelta',
		16 => 'EnableShadowCopy',
		17 => 'EnableCDP',
		18 => 'EnableDeltaMerge',
		19 => 'ExchangeMailboxQuota',
		20 => 'MsVmQuota',
		21 => 'VMwareQuota',
		22 => 'AdGroup',
		23 => 'UserHome',
		24 => 'Replicated'
	);

	function User(&$api){
		$this->userdata['Alias'] = 'New User';
		extract(ahsayobs_getUserSettings());

		$this->userdata['Language'] = (!empty($defaultLanguage)) ? $defaultLanguage : 'en';
		$this->userdata['Timezone'] = (!empty($defaultTimezone)) ? $defaultTimezone : 'GMT -05:00 (EST)';
		$this->userdata['Bandwidth'] = (!empty($defaultBandwidth)) ? $defaultBandwidth : '0';

		if (!empty($defaultOwner)){
			$this->userdata['Owner'] = (!empty($defaultOwner)) ? $defaultOwner : '';
		}
		
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
		$this->userdata['MsVmQuota'] = 0;
		$this->userdata['VMwareQuota'] = 0;
		$this->userdata['Replicated'] = 'N';
		
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
				if ($num == 19) { $this->userdata[$value] = $params['configoption' . $num] == '' ? 0 : $params['configoption' . $num]; continue;}
				if ($num == 20) { $this->userdata[$value] = $params['configoption' . $num] == '' ? 0 : $params['configoption' . $num]; continue;}
				if ($num == 21) { $this->userdata[$value] = $params['configoption' . $num] == '' ? 0 : $params['configoption' . $num]; continue;}
				if (($num > 5 && $num < 19) || $num == 4 || $num == 24){
					$this->userdata[$value] = $params['configoption' . $num] == 'on' ? 'Y' : 'N';
					continue;
				}
				if ($num == 5) { $this->userdata[$value] = $params['configoption' . $num] * 1073741824; continue; }
				if ($num == 3) { $this->userdata[$value] = $params['configoption' . $num] == 'on' ? 'TRIAL' : 'PAID'; continue; }
				if ($num == 22) { $this->userdata[$value] = $params['configoption' . $num] == '' ? 'NONE' : $params['configoption' . $num]; continue; }
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
			if (stripos($k, "VMw") && (is_numeric($v) && $v > 0)){
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
			if (stripos($k, "Hyper") && (is_numeric($v) && $v > 0)){
				if (preg_match('/([0-9]+)hyper/i', $k, $match)){
					if (isset($match[1])){
						$addonvm += ($match[1] * $v);
					}
				} else if (preg_match('/([0-9]+) hyper/i', $k, $match)){
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
?>