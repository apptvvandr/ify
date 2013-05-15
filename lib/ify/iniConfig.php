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


	// This function read the ini file
	function __construct($iniFile = null) {


		// Define hardcoded default config file if not set
		if (!$iniFile) $iniFile = "/var/www/ify/config.ini";


		// Tests if the file exists and is readable
		if( !file_exists($iniFile) )
			throw New \Exception(sprintf('Config file not found: %s', $fileName));
		if( !is_readable($iniFile) )
			throw New \Exception(sprintf('Config file not readable: %s', $fileName));
		// Save file path
		$this->_iniFile = $iniFile;

		// Parse the ini file
		$this->_raw = parse_ini_file($iniFile, true);
	}

	// Display raw config data (debug purpose only)
	function raw() {
		
		echo '<pre>';
		print_r($this->_raw);
		echo '</pre>';
  
		return $this->_raw;
	}

	// Get global setting
	function get($setting) {
		if (!empty($this->_raw["global"][$setting]) ) {
			return $this->_raw["global"][$setting];
		} else {
			doLog("DEBUG: ".$setting." is not set in 'global' in ".$this->iniFile);
			return null;
		}
	}

	// Test user exists and if it's corectly set
	function testUser($user) {

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
			doLog("INFO: Context set for user $user (auth= ".$this->get('auth').")");
			return 0;
		}
	}


	// Get user setting
	function getUser($setting) {

		if (empty($this->_user)) {
			doLog("DEBUG: No context user set. Please setUser() method before");
			return null;
		}

		if ($setting == "login") {
			return $this->_user;
		}

		if (!empty($this->_raw["user.".$this->_user][$setting]))
			return $this->_raw["user.".$this->_user][$setting];
		else
			return null;
	}

}
?>
