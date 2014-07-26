<?
//error_reporting(E_ALL);
//require_once(dirname(__FILE__) . '/_xml.php');
class XZXML {
	/**
	 * The array representation of the XML that this object represents.
	 */
	var $arrayData;

	/**
	 * The string representation of the XML that this object represents.
	 */
	var $stringData;

	/**
	 * The string representation of any XML errors we encounter.
	 */
	var $errorData;

	/**
	 * The XML parser to use throughout the life of this object.
	 */
	var $parser;

	/**
	 * 1 if the provided XML string was able to be parsed, or 0 if otherwise.
	 */
	var $valid;

	/**
	 * A constructor for an XZXML object. It stores the given input, and makes it ready for toString() and
	 * toArray().
	 *
	 * @param  string  The string of XML, or array of structured XML to initiate this class with.
	 */
	function XZXML($input) {
		$this->arrayData = array();
		$this->stringData = $this->parseStringData = "";
		$this->valid = 0;
		if(is_array($input)) {
			$this->arrayData = $input;
			$this->valid = 1;
		} else {
			$this->stringData = $input;
			$this->parseStringData = preg_replace("/&/", "#!#ampersand#!#", $input);
		}
	}

	function handleError($parser) {
		global $CONF;

		$context = 40;

		list($err_code, $err_line, $err_col, $err_byte, $err_string) = array(
			xml_get_error_code($parser),
			xml_get_current_line_number($parser),
			 xml_get_current_column_number($parser),
			 xml_get_current_byte_index($parser),
			 xml_error_string(xml_get_error_code($parser)),
		);

		$pre_context = $err_byte - 1 > $context ? $context : $err_byte - 1;
		$left = strlen($this->stringData) - $err_byte;
		$post_context = $left - 1 > $context ? $context : $left - 1;

		$xml_err_pre = substr($this->stringData, $err_byte - $context, $context);
		$xml_err_post = substr($this->stringData, $err_byte, $context);

		$xml_err_pre_lines = explode("\n", $xml_err_pre);
		$xml_err_pre_line = $xml_err_pre_lines[count($xml_err_pre_lines) - 1];

		$xml_err_post_lines = explode("\n", $xml_err_post);
		$xml_err_post_line = $xml_err_post_lines[0];

		$xml_err_html = "<pre>" . htmlentities($xml_err_pre_line) . "<strong>|</strong><em>" . htmlentities($xml_err_post_line) . "</em></pre>";

		$this->errorData = $err_string."<br />\n".$xml_err_html;

		/*$CONF["debug"]->add(
			"XML Parser Failed",
			'Error ' . $err_code
				. ' at line ' . $err_line
				. ', column ' . $err_col
				. ', byte ' . $err_byte
				. ': ' . htmlentities($err_string)
				. '<br />' . $xml_err_html,
			__FILE__, __LINE__
		);
		dbg("XML Parser Failed\n" .
			'Error ' . $err_code
				. ' at line ' . $err_line
				. ', column ' . $err_col
				. ', byte ' . $err_byte
				. ': ' . htmlentities($err_string)
				. '<br />' . $xml_err_html); */
	}

	/**
	 * Returns the array representation of this XZXML object.
	 *
	 * @return  array  The array representation of this XZXML object.
	 */
	function toArray() {
		$parser = xml_parser_create("UTF-8");
		xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
		xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
		if(!count($this->arrayData) && $this->parseStringData) {
			if(xml_parse_into_struct($parser, $this->parseStringData, $vals, $index) === 1
					&& xml_get_error_code($parser) === XML_ERROR_NONE)  {
				$this->valid = 1;
				$i = -1;
				$this->arrayData = $this->getChildren($vals, $i);
			} else {
				$this->handleError($parser);
			}
			xml_parser_free($parser);
		} else {
			$this->handleError($parser);
		}
		return $this->arrayData;
	}

	/**
	 * This private method generates and structures all of the children for the given index i in vals.
	 *
	 * @param  array  The XML structure of values to use in generation.
	 * @param  int  The index to begin at in vals.
	 * @return  array  The structured array of children for the given index.
	 */
	function getChildren($vals, &$i) {
		$children = array();
		if($i > -1 && isset($vals[$i]["value"])) {
			$children["VALUE"] = $vals[$i]["value"];
		}
		while(++$i < count($vals)) {
			$type = $vals[$i]["type"];
			if($type === "complete" || $type === "open") {
				$tag = $this->buildTag($vals[$i], $vals, $i, $type);
				$children[$vals[$i]["tag"]][] = $tag;
			} else if($type === "close") {
				break;
			} else if($type === "cdata") {
				$children["VALUE"] .= $vals[$i]["value"];
			}
		}
		return $children;
	}

	/**
	 * This private method builds out the array for a tag based upon an array that is a structure of XML.
	 *
	 * @param  thisvals  The values of the XML structure that are associated with this tag.
	 * @param  vals  All of the values existing in the XML structure.
	 * @param  i  The index to begin at in vals, passed by reference.
	 * @param  type  The step at which we are in building this tag.
	 * @return  The built tag.
	 */
	function buildTag($thisvals, $vals, &$i, $type) {
		if(isset($thisvals["attributes"])) {
			$tag["_attributes"] = $thisvals["attributes"];
		}

		if($type === "complete") {
			if (@!$tag["_attributes"]) {
				$tag = trim(preg_replace("/#!#ampersand#!#/i", "&", $thisvals["value"]));
			} elseif ($thisvals["value"]) {
				$tag[] = trim(preg_replace("/#!#ampersand#!#/i", "&", $thisvals["value"]));
			}
		} else {
			if(@!is_array($tag)) {
				$tag = array();
			}
			$tag = @array_merge($tag, $this->getChildren($vals, $i));
		}
		return $tag;
	}

	/**
	 * Returns the string representation of this XZXML object.
	 *
	 * @param  array  The input array to use, by default this is an empty array and $this->arrayData is used.
	 * @return  string  The string represenation of this XZXML object.
	 */
	function toString($input = array()) {
		$arr = $input;
		$retval = "";
		if(is_array($arr)) {
			if(!count($arr)) {
				$arr = $this->arrayData;
				$retval =& $this->stringData;
			}
			foreach($arr as $key => $value) {
				if(!eregi("_attributes", $key)) {
					if(is_array($value) && array_key_exists(0, $value)) {
						$c = count($value);
						for($i=0; $i<$c; $i++) {
							$retval .= "<$key";
							if(is_array($value[$i]["_attributes"])) {
								foreach($value[$i]["_attributes"] as $attrName => $attrValue) {
									$retval .= " ".$attrName."=\"".$attrValue."\"";
								}
							}
							$retval .= ">";
							if(is_array($value[$i])) {
								$retval .= "\n".$this->toString($value[$i])."\n";
							} else {
								$retval .= $this->toString($value[$i]);
							}
							$retval .= "</$key>\n";
						}
					} else {
						$retval .= "\t<$key";
						if(is_array($value["_attributes"])) {
							foreach($value["_attributes"] as $attrName => $attrValue) {
								$retval .= " ".$attrName."=\"".$attrValue."\"";
							}
						}
						$retval .= ">";
						$retval .= $this->toString($value);
						$retval .= "</$key>\n";
					}
				}
			}
		} else {
			$retval .= $arr;
		}
		return $retval;
	}

	/**
	 * This method returns 1 if this object was able to parse the given input XML, or 0 if otherwise. If this
	 * object was instantiated with an array, this method will always return 1.
	 *
	 * @return  int  1 upon a successful parse of the given XML, or 0 if otherwise. Always returns 1 when this object
	 *               has been instantiated with an array.
	 */
	function isValid() {
		return $this->valid;
	}
} // XZXML

/**
 * DEPRECATED: use correctArray
 * EXPLAINATION: collapseArrayElements will sometimes collapse things when it shouldn't and cause
 *		 you to have to test to see what level you have and correct it on every usage.
 *		 correctArray does not.  correctArray also does collaseSingularsAndPlurals on the results
 *		 which changes $array["items"][0]["item"] = array() to: $array["items"] = array();
 * This method collapses the elements in the given array, basically removing arrays that are one element
 * large and that're associated with a numeric key.
 *
 * @param  array  The array to collapse.
 * @return  array  The collapsed array.
 */
function collapseArrayElements($arr) {
	$retval = $arr;
	if(count($retval) === 1 && is_array($retval) && array_key_exists(0, $retval)) {
		$retval = $retval[0];
	}
	if(is_array($retval)) {
		foreach($retval as $key => $value) {
			$retval[$key] = collapseArrayElements($value);
		}
	}
	return $retval;
}

/**
 * Protect the arrayness of the first level from collapseArrayElements
 *
 * @param  array  to be protected
 *
 * @return  array  protected
 */
function protectArray($data) {
	if(count($data) == 1) {
		$data = array(0 => $data);
	}
	return $data;
}

/**
 * This method takes a raw mbapi result array and intelligently tries to get
 * the results of the query and return it in a collapsed manner.
 *
 * @param  array  The array of raw mbapi results.
 * @return  array  The collapsed array of results we guessed.
 */
function grabSimpleMBAPIResults($result, $checkNumResults=0, $returnFirst=0){
	if (SUPPRESS_SIMPLE_MBAPI_DEBUG<1)dbg("result at top of grab:");
	if (SUPPRESS_SIMPLE_MBAPI_DEBUG<1)dbg($result);
	if (!is_array($result)){
		return $result;
	} else {
		if (isset($result["mbapi"][0]["results"][0])){ // try to handle multiple entry points, most common first
			if (!(int)$result["mbapi"][0]["header"][0]["numResults"][0] && $checkNumResults){
				return array();
			}
			$result = $result["mbapi"][0]["results"][0];
		} elseif (isset($result["results"][0])){
			if (!(int)$result["header"][0]["numResults"][0] && $checkNumResults){
				return array();
			}
			$result = $result["results"][0];
		} elseif (isset($result[0]["results"][0])){
			if (!(int)$result[0]["header"][0]["numResults"][0] && $checkNumResults){
				return array();
			}
			$result = $result[0]["results"][0];
		} else {
			if ((int)$result["mbapi"][0]["header"][0]["errorCount"][0]){
				dbg("had an error!");
				return array();
			}
		}
		if (!is_array($result) || !count($result)){
			if (SUPPRESS_SIMPLE_MBAPI_DEBUG<1)dbg("simple results are blank array");
			return array();
		}
		reset($result);
		if ($result["paginationLinks"])unset($result["paginationLinks"]);
		if (SUPPRESS_SIMPLE_MBAPI_DEBUG<1)dbg("result after going in:");
		if (SUPPRESS_SIMPLE_MBAPI_DEBUG<1)dbg($result);

		if (count($result)==1){
			list($k, $v) = @each($result); // get first key and value off result array
			if (count($v)>1){ // if this key has a 0 object
				dbg("result we are returning ($k):");
				$x = collapseArrayElementsNonArray($v);
				if (SUPPRESS_SIMPLE_MBAPI_DEBUG<1)dbg($x);
				return ($returnFirst)?$x[0]:$x;
			} elseif (isset($v[0]) && $v[0]==""){
				dbg("Blank results, returning blank array");
				return array();
			} else {
				$v[0] = $v[0];
				list($k2, $v) = @each($v[0]);
				dbg("result we are returning ($k / $k2):");
				$x = collapseArrayElementsNonArray($v);
				if (SUPPRESS_SIMPLE_MBAPI_DEBUG<1)dbg($x);
				return ($returnFirst)?$x[0]:$x;
			}
		} else {
			dbg("result count not 1");
			$x = collapseArrayElementsNonArray($result);
			if (SUPPRESS_SIMPLE_MBAPI_DEBUG<1)dbg($x);
			return ($returnFirst)?$x[0]:$x;
		}

	}
}

/**
 * This method collapses mbapi array returns testing for single element arrays of non-arrays.
 *
 * @param  array  The array to collapse.
 * @return  array  The collapsed array.
 */
function collapseArrayElementsNonArray($arg) {
	if (count($arg) === 1 && isset($arg[0]) && is_array($arg) && !is_array($arg[0])){
		$arg = $arg[0];
	} elseif (is_array($arg)){
		foreach ($arg as $k=>$v){
			$arg[$k] = collapseArrayElementsNonArray($v);
		}
	}
	return $arg;
}


/**
 * a method that will intelligently correct and collapse mbapi returns.
 *
 * @param	mbapi results
 * @return    intelligently collapsed array
 */
function correctArray($array, $collapsePluralsAndSingulars=1){
	global $CONF;
	$cols = array_values($CONF["dbCols"]);
	$array = collapseDBXMLCols($array, $cols);

	$array = collapseArrayElementsNonArray($array);
	if ((int)$collapsePluralsAndSingulars){
		$array = collapsePluralsAndSingulars($array);
	}
	return $array;

}

/**
 * a method that will intelligently correct and collapse mbapi returns.
 *
 * @param	mbapi results
 * @return    intelligently collapsed array
 */
function collapseDBXMLCols($array, &$cols){
	if (is_array($array)){
		foreach ($array as $k=>$v){
			if ((in_array($k, $cols) && count($v)==1 && isset($v[0]) && is_array($v)) || (is_array($v) && !is_array($v[0]) && count($v)==1 && isset($v[0]) ) ){
				$array[$k] = $v[0];
			} else {
				if (is_array($v)){
					$array[$k] = collapseDBXMLCols($v, $cols);
				}
			}
		}
	}
	return $array;

}

/**
 * a method that will change column names to xml names
 *
 * @param	array
 * @return    array with names changed.
 */
function changeColumnNamesToXML($array){
	if (is_array($array)){
		$ret = array();
		foreach ($array as $k=>$v){
			if (getDBXMLCol($k)!=""){
				$k = getDBXMLCol($k);
			}
			$ret[$k] = changeColumnNamesToXML($v);
		}
		return $ret;
	} else {
		return $array;
	}

}

/**
 * a method that will intelligently collapse arrays of form ["someItems"][0]["someItem"] = array, to ["someItems"] = array.
 *
 * @param	array to change
 * @return    intelligently collapsed array
 */
function collapsePluralsAndSingulars($array){
	// overrides array so that we can also map things like ["countries"][0]["country"]
	$overrides = array(
		"tests" => "test",
		"productChildren" => "productChild",
		"journalEntries" => "entry"
	);
	if (is_array($array)){
		foreach ($array as $k=>$v){
			if (is_array($v)){
				if (count($v)==1 && isset($v[0])){
					if (isset($overrides[$k])){
						$lookfor = $overrides[$k];
						
					} else {
						$lookfor = substr($k, 0, -1);
					}

					if (isset($v[0][$lookfor]) && is_array($v[0][$lookfor])){
						$array[$k] = collapsePluralsAndSingulars($v[0][$lookfor]);
					} else {
						$array[$k] = collapsePluralsAndSingulars($v);
					}

				} else {
					$array[$k] = collapsePluralsAndSingulars($v);
				}
			}
		}
	}
	return $array;
}

/**
 * This method takes an array and two columns and returns an associateive array
 * meant to be put in html_options for smarty.
 *
 * @param  array  The array.
 * @param  string  the key name.
 * @param  string  the value name, or, if not in the array, the callback function name.
 * @param  array	the array of keys to ignore
 * @return  array  The keyed array.
 */
function grabHTMLOpsFromArray($arg, $key, $value, $ignoreKeys=array()) {
	$ret = array();
	$arg = (array)$arg;
	dbg("getting $key and $value from args");
	dbg("arg sent to html ops");
	dbg($arg);
	foreach ($arg as $row){
		if (!in_array($row[$key], $ignoreKeys)){
			if (!isset($row[$value]) && function_exists($value)){
				$ret[$row[$key]] = $value($row);
			} else {
				$ret[$row[$key]] = $row[$value];
			}
		}
	}
	return $ret;
}

/**
 * function to quickly grab mbapi results from an array form of an mbapi command
 */
function grabResultsFromMBAPIArray($array){
	return grabSimpleMBAPIResults(dispatchMBAPI(toXML($array), 1));
}

/**
 * function to quickly grab mbapi results from an array form of an mbapi command
 */
function grabFirstResultFromMBAPIArray($array){
	return grabSimpleMBAPIResults(dispatchMBAPI(toXML($array), 1), 0, 1);
}

/**
 * Convert XML datestamp (Tue Apr 27 10:42:14 EDT 2004) to a Unix timestamp in seconds.
 *
 * @param string  $datestamp XML formatted date stamp
 * @return int  unix timestamp
 */
function xmlstr2unixtime($string_date) {
	$retval = 0;
	if (!empty($string_date)) {
		list($wday,$month,$day,$time,$zone,$year) = explode(' ',$string_date);
		$retval = strtotime("$wday $month $day $time $year");
	}
	return $retval;
}

/**
 * Convert XML timestamp (2004-04-26T04:06:29-04:00) to a Unix timestamp in seconds.
 *
 * @param  string  $datestamp XML formatted date stamp
 * @return  int  unix timestamp
 */
function xmldate2unixtime($timestamp) {
	$retval = 0;
	if (!empty($timestamp)) {
		list($temp_date,$temp_time) = explode('T',$timestamp);
		list($yy,$mm,$dd)      = explode('-',$temp_date);
		list($time,$zone)      = explode('-',$temp_time);
		list($hour,$min,$sec)  = explode(':',$time);
		$retval = mktime($hour,$min,$sec,$mm,$dd,$yy);
	}
	return $retval;
}

/**
 * This method will return nested XML strings if data exists.
 *
 * @return  string  The nested XML string.
 * @author  Michael Fountain
 */
function getNestedXML($array) {
	global $loopSpacer;
	$currentSpacer = $loopSpacer."  ";
	$result = "";
	if (is_array($array)) {
		foreach($array as $key => $value) {
			if (is_array($value)) {
				$result .= $currentSpacer."      <$key>\n";
				$result .= getNestedXML($value);
				$result .= $currentSpacer."      </$key>\n";
			} else {
				$result .= $currentSpacer."        <$key>$value</$key>\n";
			}
		}
	}
	return $result;
}

/**
 * This method will return a sanitized array (add the 0 elements to be exactly like a parsed xml array)
 *
 * @return array The sanitized array
 * @author Brian Smith
 */
function padMBAPIQueryArray( &$array, $prevType=false ) {
	$ret=array();

	if (is_array( $array ))
	foreach( $array as $k => $v ) {
		if (is_array( $v )) {
			if (is_numeric( $k )) {
				if ($prevType != "i") {
					$ret[ $k ] = padMBAPIQueryArray($v, "i");
				} elseif (count($array) <= 1) {
					$ret = padMBAPIQueryArray($v, "i");
				} else {
					$ret[ $k ] = padMBAPIQueryArray($v, "s");
				}
				continue;
			} else {
				// the key is of type string!
				$ret[ $k ] = array(padMBAPIQueryArray($v, "i"));
				continue;
			}
		} else {
			if (is_numeric( $k )) {
				if (is_numeric($k) && (int)$k === 0) {
					$ret = $v;
				} else {
					$ret[ $k ] = $v;
				}
			} else {
				switch($prevType) {
				case "i":
					$ret[ $k ] = array($v);
				break;
				case "s":
					$ret[ $k ] = $v;
				break;
				}
			}
		}
	}
	$array = $ret;
	return $ret;
}

class OBS {
	var $name = "OBS";
	var $user = null;
	var $disableStartup = true;
	var $loginInfo = array(
		"sysuser" => "system",
		"syspass" => "system",
		"host"    => "127.0.0.1"
	);
	var $current = "";
	var $sysuser;
	var $syspass;
	var $host;
	var $host_dir;
	var $secure;
	var $api_files = array(
	    "AuthUser"                  => "AuthUser.do",
	    "AddUser"                   => "AddUser.do",
	    "ModifyUser"                => "ModifyUser.do",
		"ModifyUserStatus"          => "ModifyUserStatus.do",
	    "ListUser"                  => "ListUsers.do",
	    "GetUser"                   => "GetUser.do",
	    "DeleteUser"                => "RemoveUser.do",
	    "AddBackupSet"              => "AddBackupSet.do",
	    "DeleteBackupSet"           => "DeleteBackupSet.do",
		"DeleteBackupData"          => "DeleteBackupData.do",
	    "ListBackupSets"            => "ListBackupSets.do",
	    "GetBackupSet"              => "GetBackupSet.do",
	    "UpdateBackupSet"           => "UpdateBackupSet.do",
	    "ListBackupJobs"            => "ListBackupJobs.do",
	    "ListBackupJobStatus"       => "ListBackupJobStatus.do",
	    "GetBackupJobReport"        => "GetBackupJobReport.do",
	    "GetBackupJobReportSummary" => "GetBackupJobReportSummary.do",
	    "ListBackupFiles"           => "ListBackupFiles.do",
	    "GetUserStorageStat"        => "GetUserStorageStat.do",
	    "ListUsersStorage"          => "ListUsersStorage.do",
	    "isAlive"                   => "alive.txt",
	    "GetLicense"                => "GetLicense.do",
	    'DeleteAdvertisement'       => 'DeleteAdvertisement.do',
	    'AddAdvertisement'          => 'AddAdvertisement.do',
	    'ModifyAdvertisement'       => 'ModifyAdvertisement.do',
	    'ListAdvertisements'         => 'ListAdvertisements.do',
	    'AddAdGroup'                => 'AddAdGroup.do',
	    'ListAdGroups'              => 'ListAdGroups.do',
	    'ModifyAdGroups'            => 'ModifyAdGroups.do',
	    'DeleteAdGroups'            => 'DeleteAdGroups.do',
	    'RunBackup'                 => 'RunBackup.do',
	    'GetBackupJobProgress'      => 'GetBackupJobProgress.do',
	    'ListBackupJobMode'         => 'ListBackupJobMode.do'
    );
    var $api_fields = array("AuthUser" => array(
		"aSysUser",
		"aUserLogin"
	));

	var $aSysUser;
	var $aUserLogin;
	var $aUser;
	var $aOptions;
	var $aTime;
	var $returned;

	function startup(&$controller){
		$this->host    =$this->loginInfo['host'];
		$this->host_dir="/obs/api/";
		$this->secure  =0;
		$this->sysuser =$this->loginInfo['sysuser'];
		$this->syspass =$this->loginInfo['syspass'];

		$this->aSysUser=array
			(
			$this->sysuser,
			$this->syspass
			);
	}
	function setServer($user, $pass, $host, $secure = 1){
		$this->host = $host;
		$this->host_dir="/obs/api/";
		$this->aSysUser = array($user, $pass);
		$this->secure = $secure;
	}

	/**
     *
     * @param string $func
     * @return string
     */
    function getFunctionUrl($func) {
        $url=$this->secure == 1 ? "https://" : "http://";
		if ($this->api_files[$func]){
			$url.=$this->host . $this->host_dir . $this->api_files[$func];
		} else {
			$url .= $this->host . $func;
		}
        
        return $url;
    }
    /**
     *
     * @param string $func
     * @return string
     */
    function setupUrl($func) {
        $url=$this->getFunctionUrl($func);
        $url.="?";
        $url.="SysUser=" . $this->aSysUser[0] . "&";
        $url.="SysPwd=" . $this->aSysUser[1] . "&";
        return $url;
    }
    /**
     *
     * @param string $url
     * @return string
     */
    function doIt($url) {
        $this->current = $url;
        return file_get_contents($url);
        
    }
    /**
     *
     * @param string $return
     * @return boolean
     */
    function isOk($return) {
        $GLOBALS['return01']=$return;
        $this->returned=$return;
                if (eregi("<OK/>", $return)) {
            return true;
        }
        return false;
    }
	/**
     *
     * @param string $return
     * @return boolean
     */
    function checkResponse($return) {
        if (eregi("<err/>", $return)) {
            if (strpos($return, 'NoSuchUserExpt') !== false){
								return false;
			}	
        }
        return true;
    }
	/**
     *
     * @param string $return
     * @return boolean
     */
    function checkUserSpace($data) {
        if (strpos(" ", $data) !== false){
			return urlencode($data);
		}
		return urlencode($data);
        //return $data;
    }
	
    /**
     *
     * @param string $user Username of user
     * @return AhsayUser
     */
    function GetUser($username) {
        $url=$this->setupUrl("GetUser");
        $url.="LoginName=" . $this->checkUserSpace($username);

		$userArray = $this->read_mixed_xml($this->doIt($url));
		$user = new AhsayUser();
        $user->loadUserFromArray(ahsayobs_cleanResponses($userArray, false));
        if (eregi('err', $this->returned)) return false;
        return $user;
    }

    /**
     * Run Backup Job
     * @param string $user Username of user
     * @return AhsayUser
     */
    function RunBackup($username, $bsid, $type = '', $cancel = false) {
        $url = $this->setupUrl("RunBackup");
        $url .= "LoginName=" . $this->checkUserSpace($username) . "&";
        $url .= "BackupSetID=" . $bsid . "&";
        $url .= "BackupType=" . $type . "&";

        if ($cancel){
            $url .= "CancelBackup=Y&";
        } else {
            $url .= "CancelBackup=N&";
        }
        
        if ($this->isOk($this->doIt($url))){
            return true;
        } else {
            return false;
        }
    }
    /**
     * Add Advertisement
     * @param string $user Username of user
     * @return AhsayUser
     */
    function AddAdvertisement($name, $display_time, $target_url, $description, $animation, $align, $tracking, $adgroup, $gif_url, $swf_url) {
        $url = $this->setupUrl("AddAdvertisement");
        $url .= "AdvertisementName=" . urlencode($name) . "&";
        $url .= "DisplayTime=" . urlencode($display_time) . "&";
        $url .= "Target=" . urlencode($target_url) . "&";
        $url .= "Text=" . urlencode($description) . "&";
        $url .= "TextAnimation=" . urlencode($animation) . "&";
        $url .= "TextAlign=" . urlencode($align) . "&";
        $url .= "Tracking=" . urlencode($tracking) . "&";
        
        if (is_array($adgroup)){
            $adgrouplist = implode(",", $adgroup);
        }
        $url .= "AdGroupList=" . $adgrouplist . "&";

        $url .= "GifImage=" . urlencode($gif_url) . "&";
        $url .= "SwfImage=" . urlencode($swf_url) . "&";
        
        $xml = $this->doIt($url);

        if ($xml){
            $this->returned = $xml;
            $ads = $this->read_mixed_xml($xml);
            if (array_key_exists('err', $ads)){
                return false;
            }
            return $ads;
        } else {
            return false;
        }
    }

    /**
     * List Advertisement
     * @param string $user Username of user
     * @return AhsayUser
     */
    function ListAdvertisements($advertisement = null, $nameOnly = 'N') {
        $url = $this->setupUrl("ListAdvertisements");
        if ($advertisement){
            $url .= "AdvertisementID=" . $advertisement . "&";
        }

        $url .= "AdvertisementNameOnly=" . $nameOnly . "&";
        $xml = $this->doIt($url);
        
        if ($xml){
            $this->returned = $xml;
            $ads = $this->read_mixed_xml($xml);
            return $ads;
        } else {
            return false;
        }
        
    }

    /**
     * List Advertisement
     * @param string $user Username of user
     * @return AhsayUser
     */
    function DeleteAdvertisement($advertisement) {
        $url = $this->setupUrl("DeleteAdvertisement");
        if ($advertisement){
            $url .= "AdvertisementID=" . $advertisement . "&";
        }

        if ($this->isOk($this->doIt($url))){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Add AdGroup
     * @param string $user Username of user
     * @return AhsayUser
     */
    function AddAdGroup($name) {
        $url = $this->setupUrl("AddAdGroup");
        $url .= "AdGroupName=" . $name . "&";

        if ($this->isOk($this->doIt($url))){
            return true;
        } else {
            return false;
        }
    }

    /**
     * Delete AdGroup
     * @param string $user Username of user
     * @return AhsayUser
     */
    function DeleteAdGroup($id) {
        $url = $this->setupUrl("DeleteAdGroup");
        $url .= "AdGroupID=" . $name . "&";

        if ($this->isOk($this->doIt($url))){
            return true;
        } else {
            return false;
        }
    }

    /**
     * List AdGroup
     * @param string $user Username of user
     * @return AhsayUser
     */
    function ListAdGroup($name) {
        $url = $this->setupUrl("ListAdGroups");
        $xml = $this->doIt($url);
        $adGroups = $this->read_mixed_xml($xml);
        if ($adGroups){
            return $adGroups;
        }
        return false;
    }

    /**
     * Get Backup Job Progress
     * @param string $user Username of user
     * @return AhsayUser
     */
    function GetBackupJobProgress($username, $bsid, $jobid) {
        $url = $this->setupUrl("GetBackupJobProgress");
        $url .= "LoginName=" . $this->checkUserSpace($username) . "&";
        $url .= "BackupSetID=" . $bsid . "&";
        $url .= "BackupJobID=" . $jobid . "&";
        
        $xml = $this->doIt($url);
        $this->returned = $xml;
        $job = $this->read_mixed_xml($xml);
        
        return $job;
    }
    
    /**
     * List Backup Job Execution Styles
     * @param string $user Username of user
     * @return AhsayUser
     */
    function ListBackupJobMode($date = null, $username = null, $bsid = null) {
        if (!$date){
            $date = date("Y-m-d");
        }
        $url = $this->setupUrl("ListBackupJobMode");
        if ($username) $url .= "LoginName=" . $this->checkUserSpace($username) . "&";
        if ($bsid) $url .= "BackupSetID=" . $bsid . "&";
        if ($date) $url .= "BackupDate=" . $date . "&";
        
        $xml = $this->doIt($url);
        $this->returned = $xml;
        $jobModes = $this->read_mixed_xml($xml);
        
        return $jobModes;
    }
    

    /**
     *
     * @param string $username
     * @param string $date
     * @return string XML
     */
    function GetBackupJobs($username = null, $date = null) {
        if ($date == null) $date = date("Y-m-d");
        $url = $this->setupUrl("ListBackupJobStatus");
        $url .= 'BackupDate=' . $date . "&";
        if ($username != null) $url .= 'LoginName=' . $this->checkUserSpace($username) . "&";
        $xml = $this->doIt($url);
        $this->returned = $xml;
        $jobs = $this->read_mixed_xml($xml);
        return $jobs;
    }
    /**
     *
     * @param string $user
     * @return array
     */
    function GetLicense(){
        $url = $this->setupUrl('GetLicense');
        $xml = $this->doIt($url);
        $this->returned = $xml;
        
        $license = $this->read_mixed_xml($xml);
        
        return $license;
    }

    /**
     *
     * @param string $user
     * @return array
     */
    function GetUserStorage($username) {
        $url=$this->setupUrl("GetUserStorageStat");
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";
        $url.="YearMonth=" . date("Y-m");
        $xml = $this->doIt($url);
        $this->returned = $xml;
        $users=$this->loadStatsFromXml($xml);
        return $users;
    }
    /**
     *
     * @param string $f
     * @return array
     */
    function read_mixed_xml($f) {
        //App::Import('Vendor','Xmls');
        //$x = new Xmls($f,array('encoding' => 'US-ASCII', 'User' => array('format' => 'attributes')));
        //return collapseArrayElements($x->toArray());
		$x=new XZXML($f);
		return $x->toArray();
    }
    /**
     *
     * @param array $contact_array
     * @return array
     */
    function get_contact_info($contact_array) {
        if (is_array($contact_array)) {
            $return = array();
            $count = 0;
            if (!empty($contact_array['Name'])){
                $contact_array = array($contact_array);
            }
            foreach ($contact_array as $array) {
                $array = $array;

                $return[$count++]=array
                    (
                    "Name"  => $array['Name'],
                    "Email" => $array['Email']
                );
            }
        }
        return $return;
    }
    /**
     *
     * @param string $xml
     * @return array
     */
    function loadStatsFromXml($xml) {
        $user_stats=$this->read_mixed_xml($xml);
        return $user_stats;
    }
    /**
     *
     * @param string $xml
     * @return array
     */
    function loadUsersFromXml($xml) {
        $user_array=$this->read_mixed_xml($xml);
        
        $users=array();
        $usercount=0;
        if (!empty($user_array["Users"])) {
            $user_array=$user_array["Users"];
        }
        if (array_key_exists('LoginName', $user_array['User'])) $user_array['User'] = array($user_array['User']);
        if (!empty($user_array["User"])) {
            foreach ($user_array['User'] as $key => $user_info) {
                $users[$usercount++] = new AhsayUser($user_info);
            }
            return $users;
        }
    }
    /**
     *
     * @return array<AhsayUser>
     */
    function ListUsers() {
        $url=$this->setupUrl("ListUser");
        $xml = $this->doIt($url);
        //var_dump($url);
        $this->returned = $xml;
        //var_dump($xml);
        return $this->loadStatsFromXml($xml);
    }

    /**
     *
     * @param AhsayUser $user
     * @param boolean $newpassword
     * @return boolean
     */
    function ModifyUser($user, $newpassword = false, $appendContacts = false) {
        $url_vars="";
		        if (is_array($user->userdata)) {
            foreach ($user->userdata as $key => $value) {
                if (!$newpassword && $key == 'Password')
                    continue;
                $url_vars.= "$key=" . urlencode($value) . "&";
            }
        }

        if (is_array($user->contact_info)) {
            if ($appendContacts) $appendContacts = 'Y';
            else $appendContacts = 'N';
            $url_vars.="AppendContact=".$appendContacts."&";
            foreach ($user->contact_info as $num => $info) {
                $url_vars.="Contact" . ($num+1) . "=" . urlencode($info['Name']) . "&";
                $url_vars.="Email" . ($num+1) . "=" . urlencode($info['Email']) . "&";
            }
        }

        $url=$this->setupUrl("ModifyUser") . $url_vars;

        if ($this->isOk($this->doIt($url))) {
            return true;
        }

        return false;
    }
	/**
     *
     * @param AhsayUser $user
     * @param boolean $newpassword
     * @return boolean
     */
    function ModifyUserLite($username, $user, $contacts, $newpassword = false) {
        $url_vars="";
		$api_map = Configure::read('BA.user_api_map');
		
		$url_vars = "LoginName=" . urlencode($username) . "&";
		
        if (is_array($user)) {
			foreach($user as $key => $value){
				if (!$newpassword && $key == 'password')
                    continue;
				if (array_key_exists($key, $api_map) && !empty($value)){
					$url_vars.= $api_map[$key]."=" . urlencode($value) . "&";
				}
			}
        }

        if (is_array($contacts)) {
			$appendContacts = 'N';
            $url_vars.="AppendContact=".$appendContacts."&";
            foreach ($contacts as $num => $info) {
                $url_vars.="Contact" . ($num) . "=" . urlencode($info['name']) . "&";
                $url_vars.="Email" . ($num) . "=" . urlencode($info['email']) . "&";
            }
        }

        $url=$this->setupUrl("ModifyUser") . $url_vars;

        if ($this->isOk($this->doIt($url))) {
            return true;
        }

        return false;
    }
	function GetUserStorageStatistics($username, $ym = false){
		$url = $this->setupUrl('GetUserStorageStat');
		$url .= "LoginName=" . $this->checkUserSpace($username) . "&";
		if ($ym) $url .= "YearMonth=" . $ym . "&";
		else $url .= "YearMonth=" . date("Y-m") . "&";
		
		$xml = $this->doIt($url);
		return $xml;
	}
    /**
     *
     * @param string $username
     * @return string XML
     */
    function GetBackupSetsXml($username) {
        $url = $this->setupUrl('ListBackupSets');
        $url.= "LoginName=" . $this->checkUserSpace($username) . "&";

        $xml = $this->doIt($url);
        return $xml;
    }

    /**
     *
     * @param string $username
     * @param long $backupset_id
     * @return string XML
     */
    function GetBackupSetXml($username, $backupset_id) {
		if (!$backupset_id){
			return false;
		}
        $url=$this->setupUrl('GetBackupSet');
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";
        $url.="BackupSetID=" . $backupset_id . "&";

        $xml = $this->doIt($url);
        return $xml;
    }

	/**
     *
     * @param string $username
     * @param long $backupset_id
     * @return string XML
     */
    function UpdateBackupSetXml($username, $backupset) {
        $url=$this->setupUrl('UpdateBackupSet');
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";
        //$url.="BackupSetID=" . $backupset_id . "&";
		$response = $this->curl_post($url, $backupset, 80);
        //$xml = $this->doIt($url);
        return $response;
    }
	/**
     *
     * @param string $username
     * @param long $backupset_id
     * @return string XML
     */
    function AddBackupSet($username) {
        $url=$this->setupUrl('AddBackupSet');
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";
        //$url.="BackupSetID=" . $backupset_id . "&";
		//$response = $this->curl_post($url, $backupset, 80);
        $xml = $this->doIt($url);
		preg_match('/ID=\"(.*)\"/im', $xml, $matches);
		if (strlen($matches[1]) == strlen("1295895494670")){
			return $matches[1];
		}
		return false;
    }
    /**
     *
     * @param string $username
     * @return string XML
     */
    function GetBackupJobsXml($username) {
        $url=$this->setupUrl('ListBackupJobs');
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";
        $xml = $this->doIt($url);
        return $xml;
    }

    /**
     *
     * @param string $username
     * @param long $backupset_id
     * @param long $backupjob_id
     * @return string XML
     */
    function GetBackupReportXml($username, $backupset_id, $backupjob_id) {
        $url = $this->setupUrl('GetBackupJobReport');
        $url .= "LoginName=" . $this->checkUserSpace($username) . "&";
        $url .= "BackupSetID=" . $backupset_id . "&";
        $url .= "BackupJobID=" . $backupjob_id . "&";

        $xml = $this->doIt($url);
        return $xml;
    }

    /**
     *
     * @param string $username
     * @param long $backupset_id
     * @param long $backupjob_id
     * @return string XML
     */
    function GetBackupJobReportSummaryXml($username, $backupset_id, $backupjob_id) {
        $url = $this->setupUrl('GetBackupJobReportSummary');
        $url .= "LoginName=" . $this->checkUserSpace($username) . "&";
        $url .= "BackupSetID=" . $backupset_id . "&";
        $url .= "BackupJobID=" . $backupjob_id . "&";

        $xml = $this->doIt($url);
        return $xml;
    }

    /**
     *
     * @param string $username
     * @param long $backupset_id
     * @param long $backupjob_id
     * @param string $path
     * @return string XML
     */
    function ListbackupFilesXml($username, $backupset_id, $backupjob_id, $path) {
        $url = $this->setupUrl('ListBackupFiles');
        $url .= "LoginName=" . $this->checkUserSpace($username) . "&";
        $url .= "BackupSetID=" . urlencode($backupset_id) . "&";
        $url .= "BackupJobID=" . urlencode($backupjob_id) . "&";
        $url .= "Path=" . urlencode($path) . "&";

        $xml = $this->doIt($url);
        return $xml;
    }

    /**
     *
     * @param string $username
     * @param string $yearmonth
     * @return string XML
     */
    function GetUserStorageStatXml($username, $yearmonth=null) {
        if ($yearmonth == null) $yearmonth = date("Y-m");
        $url = $this->setupUrl('GetUserStorageStat');
        $url .= "LoginName=" . $this->checkUserSpace($username) . "&";
        $url .= "YearMonth=" . $yearmonth . "&";

        $xml = $this->doIt($url);
        return $xml;
    }

    /**
     *
     * @param string $user
     * @return boolean
     */
    function DeleteUser($username) {
        $url=$this->setupUrl("DeleteUser");
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";

        if ($this->isOk($this->doIt($url))) {
            return true;
        }

        return false;
    }
 	/**
     * 
     * @param string $user
     * @return boolean
     */
    function DeleteBackupData($username, $backupsetid = null, $backupsetname = null) {
        $url=$this->setupUrl("DeleteBackupData");
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";

		if (!$backupsetid && !$backupsetname){
			return false;
		}
		
		if ($backupsetid){
			$url.="BackupSetID=" . $backupsetid . "&";
		} else if ($backupsetname){
			$url.="BackupSetName=" . urlencode($backupsetname) . "&";
		}
		
        if ($this->isOk($this->doIt($url))) {
            return true;
        }

        return false;
    }
	/**
     * 
     * @param string $user
     * @return boolean
     */
    function DeleteBackupSet($username, $backupsetid = null) {
        $url=$this->setupUrl("DeleteBackupSet");
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";

		if (!$backupsetid){
			return false;
		}
		
		if ($backupsetid){
			$url.="BackupSetID=" . $backupsetid . "&";
		}
		
        if ($this->isOk($this->doIt($url))) {
            return true;
        }

        return false;
    }
    /**
     *
     * @param string $username
     * @param boolean $enable
     * @return boolean
     */
    function SuspendUser($username, $enable = false) {
        if (!$username) {
            return false;
        }
        $url=$this->setupUrl("ModifyUserStatus");
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";
		if ($enable){
			$url .= "Status=ENABLE&";
		} else {
			$url .= "Status=SUSPENDED&";
		}
        if ($this->isOk($this->doIt($url))) {
            return true;
        }
        return false;
    }

    /**
     *
     * @param string $username
     * @param string $password
     * @return boolean
     */
    function AuthUser($username, $password) {
        $url=$this->setupUrl("AuthUser");
        $url.="LoginName=" . $this->checkUserSpace($username) . "&";
        $url.="Password=" . urlencode($password);
        return $this->doIt($url);
    }
	
    /**
     *
     * @param string $user
     * @param string $sendwelcome
     * @return boolean
     */
    function AddUser($user, $sendwelcome = "Y") {
        $url_vars="";
        foreach ($user->userdata as $key => $value) {
            $url_vars.="$key=" . urlencode($value) . "&";
        }
        $c=1;
        if (is_array($user->contact_info)) {
            foreach ($user->contact_info as $value) {
                $url_vars.="Contact$c=" . urlencode($value['Name']);
                $url_vars.="Email$c=" . urlencode($value['Email']);
                $c++;
            }
        }
        $url_vars.="SendWelcomeMail=N";// . $sendwelcome;
        $url=$this->setupUrl("AddUser") . $url_vars;
        //var_dump($url);
        if ($this->isOk($this->doIt($url))) {
            return true;
        }
        return false;
    }
	function curl_post($URL, $postData, $port = 8000){
			    $c = curl_init();
	    curl_setopt($c, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($c, CURLOPT_PORT, $port);
	    curl_setopt($c, CURLOPT_URL, $URL);
		curl_setopt($c, CURLOPT_POST, true);
		curl_setopt($c, CURLOPT_POSTFIELDS, $postData);
	    $contents = curl_exec($c);
	    curl_close($c);
		
	    if ($contents) return $contents;
	        else return FALSE;
	}
}
function ahsayobs_collapseAtts(&$i, $k, $arrays){
	if (array_key_exists('_attributes', $i)){
		$atts = $i['_attributes'];
		$c = null;
		if (array_key_exists('Contact', $i) && is_array($i['Contact'])){
			$c = $i['Contact'];
			array_walk($c, 'ahsayobs_collapseAtts');
		}
		if ($c){
			$i = array_merge($atts, array('Contact' => $c));
		} else {
			$i = $atts;
		}	
	}
}

function ahsayobs_strtolowerKeys(&$i, &$k, $arrays){
	$k = strtolower($k);
}

function ahsayobs_cleanResponses(&$arr, $case = true){
	array_walk($arr, 'ahsayobs_collapseAtts');
	if ($case){
		$arr = array_change_key_case_recursive($arr, CASE_LOWER);
	}
	return $arr;
}

function array_change_key_case_recursive($input, $case = null){ 
	if(!is_array($input)){ 
		trigger_error("Invalid input array '{$array}'",E_USER_NOTICE); exit; 
	} 
	// CASE_UPPER|CASE_LOWER 
	if(null === $case){ 
		$case = CASE_LOWER; 
	}
	if(!in_array($case, array(CASE_UPPER, CASE_LOWER))){
		trigger_error("Case parameter '{$case}' is invalid.", E_USER_NOTICE); exit;
	}
	$input = array_change_key_case($input, $case);
	foreach($input as $key=>$array){
		if(is_array($array)){
			$input[$key] = array_change_key_case_recursive($array, $case);
		}
	}
	return $input; 
}
?>
<?php

class AhsayUser {
    var $name = 'AhsayUser';
    var $useTable = false;
    var $api_map = array("username"=>"LoginName",
    "password" => "Password",
    "name"=>"Alias",
    "type" => "Type",
    "client_type" => "ClientType",
    "quota" => "Quota",
    "timezone" => "Timezone",
    "language" => "Language",
    "datafile" => "DataFile",
    "datasize" => "DataSize",
    "retainfile" => "RetainFile",
    "retainsize" => "RetainSize",
    "mssql" => "EnableMSSQL",
    "mysql" => "EnableMySQL",
    "msexchange" => "EnableMSExchange",
    "oracle" => "EnableOracle",
    "lotusnotes" => "EnableLotusNotes",
    "lotusdomino" => "EnableLotusDomino",
    "cdp" => "EnableCDP",
    //"nasclient" => "EnableNASClient",
    "infiledelta" => "EnableInFileDelta",
    "shadowcopy" =>"EnableShadowCopy",
    "exchangemailbox" => "EnableExchangeMailbox",
    "mailboxquota" => "ExchangeMailboxQuota",
    "shadowprotect" => "EnableShadowProtectBareMetal",
    "winserver2008" => "EnableWinServer2008BareMetal",
    "deltamerge" => "EnableDeltaMerge",
    "msvm" => "EnableMsVm",
    "msvmquota" => "MsVmQuota",
    "vmware" => "EnableVMware",
    "vmwarequota" => "VMwareQuota",
    'replicated' => 'Replicated',
    "bandwidth" => "Bandwidth",
    "notes" => "Notes",
    "status" => "Status",
    "regdate" => "RegistrationDate",
    "email" => "Email",
    "userhome" => "UserHome",
    //"sendemail" => "SendWelcomeMail"
);

    var $core = array();
    var $loaded = false;
    //user data

    var $username;
    var $euser_id;
    var $userdata = null;
    var $userdb;
    var $contact_info = array();
    var $contacts = 0;

    var $api;
    var $booltoy = array(
        "db"=>array("mssql", "msexchange", "oracle", "lotusnotes", "lotusdomino", "mysql", "infiledelta", "shadowcopy", "exchangemailbox","cdp", "nasclient",'shadowprotect','winserver2008','deltamerge','msvm','vmware','replicated'),
        "api"=>array("EnableMSSQL", "EnableMSExchange", "EnableOracle", "EnableLotusNotes", "EnableLotusDomino", "EnableMySQL", "EnableInFileDelta", "EnableShadowCopy", "EnableExchangeMailbox", "EnableCDP", "EnableNASClient",'EnableShadowProtectBareMetal','EnableWinServer2008BareMetal','EnableDeltaMerge','EnableMsVm','EnableVMware','Replicated'));
    
    function AhsayUser($userdata = null) {
        $this->defaults();
        $this->userdata['LoginName'] = &$this->username;
        $this->userdata['Password'] = &$this->password;
        foreach ($this->api_map as $db=>$api) {
            $this->core[$db] = "";
			if ($db == 'type'){
				$this->userdata['UserType'] = &$this->core[$db];
	            $this->userdb['type'] = &$this->core[$db];
			}
            $this->userdata[$api] = &$this->core[$db];
            $this->userdb[$db] = &$this->core[$db];
        }
        if (is_array($userdata)){
            return $this->loadUserFromArray($userdata);
        } else if (is_string($userdata)){
            return $this->loadUserFromXml($userdata);
        }
    }

    function updateContactInfo($ci) {
        if (is_array($ci)) {
            foreach ($ci as $c) {
                $this->contact_info[$this->contacts++] = array("Name" => $c['Name'], "Email" => $c['Email']);
            }
        }
    }
    function defaults() {
        if (!defined('XAPI_ENABLED')){
            define('XAPI_ENABLED', 'Y');
        }
        if (!defined('XAPI_CLIENTOBM')){
            define('XAPI_CLIENTOBM', 'OBM');
        }
        if (!defined('XAPI_DEFAULT_ALIAS')){
            define('XAPI_DEFAULT_ALIAS', 'User Description');
        }
        if (!defined('XAPI_DEFAULT_LANGUAGE')){
            define('XAPI_DEFAULT_LANGUAGE', 'en');
        }
        if (!defined('XAPI_DEFAULT_LANGUAGE')){
            define('XAPI_DEFAULT_LANGUAGE', 'en');
        }
        $this->userdata['Alias'] = XAPI_DEFAULT_ALIAS;
        $this->userdata['Language'] = XAPI_DEFAULT_LANGUAGE;
        $this->userdata['Type'] = XAPI_PAID_USER;
        $this->userdata['ClientType'] = XAPI_CLIENTOBM;
        $this->userdata['AdGroup'] = XAPI_ADGROUPALL;
        $this->userdata['Quota'] = XAPI_DEFAULT_QUOTA;
        $this->userdata['UserHome'] = XAPI_DEFAULT_USER_HOME;
        $this->userdata['EnableMSSQL'] = XAPI_ENABLED;
        $this->userdata['EnableMSExchange'] = XAPI_ENABLED;
        $this->userdata['EnableOracle'] = XAPI_ENABLED;
        $this->userdata['EnableLotusNotes'] = XAPI_ENABLED;
        $this->userdata['EnableLotusDomino'] = XAPI_ENABLED;
        $this->userdata['EnableMySQL'] = XAPI_ENABLED;
        $this->userdata['EnableInFileDelta'] = XAPI_ENABLED;
        $this->userdata['EnableCDP'] = XAPI_ENABLED;
        $this->userdata['EnableNASClient'] = 'N';
        $this->userdata['EnableShadowCopy'] = XAPI_ENABLED;
        $this->userdata['EnableExchangeMailbox'] = XAPI_ENABLED;
        $this->userdata['ExchangeMailboxQuota'] = 0; //XAPI_DEFAULT_EXCHQUOTA;
        $this->userdata['EnableDeltaMerge'] = 'N';
        $this->userdata['EnableMsVm'] = XAPI_ENABLED;
        $this->userdata['EnableVMware'] = XAPI_ENABLED;
        $this->userdata['VMwareQuota'] = 0; //XAPI_DEFAULT_EXCHQUOTA;
        $this->userdata['MsVmQuota'] = 0; //XAPI_DEFAULT_EXCHQUOTA;
        $this->userdata['Replicated'] = XAPI_ENABLED;
        $this->userdata['Timezone'] = XAPI_DEFAULT_TIMEZONE;
        $this->userdata['Bandwidth'] = XAPI_DEFAULT_BANDWIDTH;
        $this->userdata['Status'] = XAPI_STATUS_ENABLED;
        $this->userdata['Notes'] = XAPI_DEFAULT_NOTE;
        $this->userdata['Email'] = XAPI_DEFAULT_EMAIL;
        $this->userdata['Password'] = "";
        $this->userdata['Email'] = "";
    }
    function convertXmlToArray($xml){
    	$arr = ahsayobs_cleanResponses(OBS::read_mixed_xml($xml), false);
        return $arr;
    }
    function loadUserFromXml($xml){
        return $this->loadUserFromArray($this->convertXmlToArray($xml));
    }
    function getContactInfo($contact_array) {
        if (is_array($contact_array)) {
            $return = array();
            $count = 0;
            if (!empty($contact_array['Name'])){
                $contact_array = array($contact_array);
            }
            foreach ($contact_array as $array) {
                $return[$count++]=$array;
            }
            return $return;
        }
        return false;
    }
    function loadUserFromArray($user_array){
        if (!empty($user_array['User'])){
            $user_array = $user_array['User'];
        }
        if (!empty($user_array['LoginName'])){
            $this->userdata['LoginName'] = $user_array['LoginName'];
            $this->makeUser($user_array, 'api');
            $this->updateContactInfo($this->getContactInfo($user_array['Contact']));
            return $this;
        }
        return false;
    }
    function convertBoolToYes(){
        foreach ($this->booltoy['api'] as $key){
            $this->userdata[$key] = ($this->userdata[$key] == 1) ? 'Y' : 'N';
        }
        return true;
    }
    function makeUser($userinfo, $fields = "db", $newpass = null) {

        if ($fields == "api") {
            foreach ($this->api_map as $key => $value) {
                if (in_array($value, $this->booltoy['api'])) {
                    $this->userdb[$key] = (@$userinfo[$value] == 'Y') ? 1 : 0;
                }/* elseif ($key == 'quota'){
					$this->userdb[$key] = ($userinfo[$value] / 1048576);
				} */ elseif ($key == 'password' && $newpass != null) {
                    $this->userdb[$key] = $newpass;
                } else {
                    $this->userdb[$key] = (!empty($userinfo[$value])) ? $userinfo[$value] : "";
                }
            }
            $this->loaded = true;
            return $this;
        }
        if (!is_array($this->userdata)) {
            $this->defaults();
        }

        if (is_array($userinfo)) {
            foreach($userinfo as $k => $ui) {
                if (!empty($this->api_map[$k])) {
                    if (in_array($k, $this->booltoy['db'])) {
                        $this->userdata[$this->api_map[$k]] = ($ui == 1) ? 'Y' : 'N';
                    } /* elseif ($k == 'quota'){
						$this->userdata[$this->api_map[$k]] = ($ui * 1048576);
					} */else if ($k == 'password' && $newpass != null) {
                            $this->userdata[$this->api_map[$k]] = $newpass;
                        } else {
                            $this->userdata[$this->api_map[$k]] = $ui;
                        }

                }
            }
            $this->loaded = true;
        }
        
        return $this;
    }

}
?>
