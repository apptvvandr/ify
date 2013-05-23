<?php

class ifyConfig {

	// Ini config file path
	protected $_iniFile;

	// Raw data extracted from ini file
	protected $_raw;


	// Save user context
	protected $_user;

	// Save backend context
	protected $_backend;

	// Store application settings
	protected $_app;

	// This function read the ini file
	function __construct($iniFile = "config.ini") {

		$this->_app = array();

		// Initialise application root path
		// Note: if you move this file class, you have to define its relative path from
		// the root application
		$this->_app["root"] = realpath(dirname(__FILE__). DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
		$iniFile = $this->_app["root"] . DIRECTORY_SEPARATOR . $iniFile;

		// Ini config file
		//////////////////

		// Tests if the file exists and is readable
		if( !file_exists($iniFile) )
			throw New \Exception(sprintf('Config file not found: %s', $iniFile));
		if( !is_readable($iniFile) )
			throw New \Exception(sprintf('Config file not readable: %s', $iniFile));
		// Save file path
		$this->_iniFile = $iniFile;

		// Parse the ini file
		$this->_raw = parse_ini_file($iniFile, true);

		//echo "Real path is: " . $this->_app["root"] . "<br>";
		//echo "Real path is: " . $this->getApp("root") . "<br>" ;

	}

	// Display raw config data (debug purpose only)
	function raw() {
		
		echo '<pre>';
		print_r($this->_raw);
		echo '</pre>';
  
		return $this->_raw;
	}

	// Display the whole config
	function dump() {
		
		echo '<pre>';
		print_r($this->_app);
		print_r($this->_raw);
		echo '</pre>';
  
		return $this->_raw;
	}

	// Get global setting
	function get($setting, $section = "global") {
		if (!empty($this->_raw[$section][$setting]) ) {
			return $this->_raw[$section][$setting];
		} else {
			l("DEBUG", "$setting is not set in $section section in ".$this->_iniFile);
			return null;
		}
	}

	// Set user context
	function setUser($user = null) {

		// Guess user to use if no argument
		if(!$user) {
			$auth = $this->get("auth");
			if ($auth != "users")
				$user = $auth;
			else
				$user = $this->_user;
		}

		// Test the user and set context
		if ( $this->testUser($user)) {
			doLog("WARNING: Trying to set context for ".$user." but this user is not valid. Keep $this->_user context");
			return 1;
		} else {
			$this->_user = $user;
			//doLog("INFO: Context set for user $user (auth= ".$this->get('auth').")");
			return 0;
		}
	}

	// Get user setting
	function getUser($setting) {

		if (empty($this->_user)) {
			doLog("DEBUG: No context user set. Please setUser() method before");
			return null;
		}

		if ($setting == "login") 
			return $this->_user;

		return $this->get($setting, "user.".$this->_user);
	}

	// Set application config setting
	function setApp($setting, $value) {
		$this->_app[$setting] = $value;
		return 1;
	}

	// Get application config settings
	function getApp($setting) {
		if (!empty($setting) && !empty($this->_app[$setting]))
			return $this->_app[$setting];
		else
			return null;
	}


	//
	// Private functions
	////////////////////

	// Test user exists and if it's corectly set
	protected function testUser($user) {

		if (!empty($this->_raw["user.".$user])) {

			if (!empty($this->_raw["user.".$user]["password"]))
				return 0;
			else
				doLog("INFO: Missing 'password' setting for ".$user." in ".$this->_iniFile);
				return 1;
		} else {
			doLog("INFO: User $user not set in $this->_iniFile (auth= ".$this->get('auth').")");
			return 1;
		}
	}

}
?>
