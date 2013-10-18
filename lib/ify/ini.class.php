<?php


class ifyConfig {

	// Save mysql session instance
	protected $_db;

	// Save user context
	protected $_user;

	// Save constants
	protected $_const;


	/*
	 * General usage functions:
	 *
	 * construtor: Do some checks and initialise MySQL connection
	 * destructor: Destroy MySQL connection
	 * dump: Dump the whole config
	 */


	// This function read the ini file
	function __construct($configFile = "config.php") {

		// Create global config array
		$this->_const = array();
		$const = $this->_const;

		// Insert some constant in db config
		$const['root'] = realpath(dirname(__FILE__). DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "..");
		$const['config_file_name'] = $configFile;
		$const['config_file'] = $const['root'] . DIRECTORY_SEPARATOR .$const['config_file_name'];


		// Check if the file is OK
		if( !file_exists($const['config_file']) )
			throw New \Exception(sprintf('Config file not found: %s', $const['config_file']));
		if( !is_readable($const['config_file']) )
			throw New \Exception(sprintf('Config file not readable: %s', $const['config_file']));

		// Load the file
		require( $const['config_file']);


		$this->_const["mysql_prefix"] = $mysql_prefix;
		$this->_const["mysql_host"] = $mysql_host;


		// Initialize database
		$this->_db = new Mysqlidb($mysql_host, $mysql_user, $mysql_password, $mysql_db);

	}

	// Destructor function
	function __destruct() {
		unset($this->_db);
	}

	// Display the whole config (Debug ONLY)
	function dumpd($display = true ) {

		$db = $this->_db;
		$dump = $db->query('SELECT * from `' . IFY_MYSQL_PREFIX . 'options` ORDER BY optSection ASC, optOption ASC');

		if ($display == true ) {
			echo '<pre>';
			print_r($dump);
			echo '</pre>';
		}
  
		return $dump;
	}

	// Get MySQL object
	function getMySQLObject(){
		return $this->_db;
	}

	// Get constants
	function getConst($const) {
		return $this->_const[$const];
	}


	/* Global functions:
	 *
	 * get: Get the value of an option
	 * set: Set an option
	 * append: Append a value to array
	 * remove: Remove a value from array
	 *
	 */

	// Check if user can read option
	protected function checkRead($id) {

		if (is_int($id)) {
			$db = $this->db;
			$result = $db->rawQuery(
				'SELECT `aclRead` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `id` = ?',
				array(
					$id
				)
			);
			$acl = explode(' ', $result['aclRead']);
		} elseif (is_string($id)) {
			// Reference to ACL list
			$acl = explode(' ', $id);
		}

		if (in_array($this->_user, $acl) || in_array('all', $acl))
			return 1;
		else
			return 0;
	}

	// Check if user can write option
	protected function checkWrite($id) {

		if (is_int($id)) {
			// Reference to an option ID
			$db = $this->db;
			$result = $db->rawQuery(
				'SELECT `aclWrite` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `id` = ?',
				array(
					$id
				)
			);
			$acl = explode(' ', $result['aclWrite']);
		} elseif (is_string($id)) {
			// Reference to ACL list
			$acl = explode(' ', $id);
		}

		if (in_array($this->_user, $acl))
			return 1;
		else
			return 0;
	}

	// Dump the whole setting from user view
	function dump() {
		$db = $this->_db;

		$result = $db->query(
			'SELECT * FROM `' . IFY_MYSQL_PREFIX . 'options`');

		$object = array();
		foreach ($result as $row) {

			// Check user can read the property
			if( $this->checkRead($row['aclRead']) ) {

				// Create section if does not exist
				if (!isset($object[$row['optSection']])) {
					$object[$row['optSection']] = array();
				}
				$section = &$object[$row['optSection']];

				// Check what kind of option it is (variable/array)
				if ($row['optIndex'] == 'null') {
					// This is a variable
					$section[$row['optOption']] = $row['optValue'];
				} else {
					// This is an array

					// Create array if does not already exist
					if (!isset($section[$row['optOption']])) {
						$section[$row['optOption']] = array();
					}

					// Push directly if no index
					$index = array();
					if (preg_match('!\d+!', $row['optIndex'], $index) != 0) {
						$index = intval($index[0]);
						$section[$row['optOption']][$index] = $row['optValue'];
					} else {
						$section[$row['optOption']][]= $row['optValue'];
					}
				}
			}
		}
		return $object;
	}

	// Get global setting
	function get($option, $section = "global") {

		// Sanitisation and extraction
		$regex = '/([a-zA-Z\d_]*)(\[([a-zA-Z\d_]*)\])?/';
		preg_match($regex, $option, $match);

		if ( !isset($match[1]) || empty($match[1]) ) {
			return null;
		} else {
			$option = $match[1];
		}
		if ( !isset($match[4]) || empty($match[4]) ) {
			$index = null;
		} else {
			$index = $match[4];
		}


		// Do the request
		$db = $this->_db;
		$params = array(
			$section,
			$option
		);
		$result = $db->rawQuery(
			'SELECT `optValue`, `optIndex`, `aclRead` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `optSection` = ? AND `optOption` = ?',
			$params
		);

		// Select the proper value depending ACL and index
		$values = array();
		foreach ($result as $row) {
			if ( $this->checkRead($row['aclRead']) ) {
				// User making the request can read the value
				if ( $index == null || $row['optIndex'] == $index) {
					// Select all values, or only corresponding index
					array_push($values, $row['optValue']);
				}
			}
		}


		// Prepare output type, value or index array
		if ( count($values) == 0) {
			// No values found
			return null;
		} elseif ( count($values) == 1) {
			// Return a single value
			return $values[0];
		} else {
			// Return array of values
			return $values;
		}
	}


	// Set global setting
	function set($option, $value, $section = "global") {

		// Sanitisation and extraction
		$value = filter_var($value, FILTER_SANITIZE_STRING);

		$regex = '/([a-zA-Z\d_]*)(\[([a-zA-Z\d_]*)\])?/';
		preg_match($regex, $option, $match);

		if ( !isset($match[1]) || empty($match[1]) ) {
			return null;
		} else {
			$option = $match[1];
		}
		if ( !isset($match[3]) ) {
			$index = null;
		} elseif ( isset($match[4]) ) {
			$index = $match[4];
		} else {
			$index = "";
		}


		// Do basic checks
		$db = $this->_db;
		$params = array(
			$section,
			$option,
			'null'
		);
		$result = $db->rawQuery(
			'SELECT `id`, `optIndex`, `aclWrite` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `optSection` = ? AND `optOption` = ? AND `optIndex` = ?',
			$params
		);


//	var_dump($result);


		// Do the modification
		if ( count( $result ) == 0) {
			// Create new variable or append to array
			if ( $index == null ) {
				$index = 'null';
			}

			// Find an empty id
			$id = 0;
			$result = $db->rawQuery('SELECT `id`  FROM `' . IFY_MYSQL_PREFIX . 'options`');
			foreach ($result as $key) {
				if ( $key['id'] == $id )
					$id++;
				else
					break;
			}
			unset($key);

			var_dump($id); echo "<br>";

			// Create the new set
			$params = array(
				$id,
				$section,
				$option,
				$value,
				$index,
				$this->_user,
				$this->_user
			);

			echo "<pre>";
			var_dump($params);
			echo "</pre>";


			return $db->rawQuery('INSERT INTO `' . IFY_MYSQL_PREFIX . 'options` SET `id` = ?, `optSection` = ?, `optOption` = ?, `optValue` = ?, `optIndex` = ?, `aclRead` = ?, `aclWrite` = ?',
				$params
			);
		} elseif ( count( $result ) == 1) {
			// Update existing variable
			$result = $result[0];
			if ($this->checkWrite($result['aclWrite'])) {
				// User making request can update value
				$params = array(
					'optValue'		=> $value
				);
				var_dump($params);
				$db->where('id', $result['id']);
				return $db->update(IFY_MYSQL_PREFIX . 'options', $params);
			}
		}
	}

	/*
	// Remove global setting
	function destroy($option, $section = "global", $value = null) {

		// Sanitisation and extraction
		$regex = '/([a-zA-Z\d_]*)(\[([a-zA-Z\d_]*)\])?/';
		preg_match($regex, $option, $match);

		if ( !isset($match[1]) || empty($match[1]) ) {
			return null;
		} else {
			$option = $match[1];
		}
		if ( !isset($match[3]) ) {
			$index = null;
		} elseif ( isset($match[4]) ) {
			$index = $match[4];
		} else {
			$index = null;
		}


		// Do basic checks
		$db = $this->_db;
		$params = array(
			$section,
			$option,
			'null'
		);
		$result = $db->rawQuery(
			'SELECT `id`, `optIndex`, `aclWrite` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `optSection` = ? AND `optOption` = ? AND `optIndex` = ?',
			$params
		);

		// Delete whole section
		// Delete all variables
		// Delete variable with corresponding value
		//
		if ( empty($option) && $section != 'global' && $section != 'user.admin' ) {
			// Delete the whole section
			$params = array(
				$section
			);
			$result = $db->rawQuery('SELECT `aclWrite` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `optSection` = ?', $params);

			// Check ACL before deleting
			if ($this->checkWrite($row['aclWrite'])) {
				// User making request can update value
				$params = array(
					'optValue'		=> $value,
					'optSection'	=> $section,
					'optOption'		=> $option
				);
				return $db->rawQuery(
					'UPDATE `' . IFY_MYSQL_PREFIX . 'options` SET `optValue` = ? WHERE `id`= ?',
					$params
				);
			}

		} elseif ($value == null && $index == null) {
			// Delete all variables
			$params = array(
				$section,
				$option
			);
			$result = $db->rawQuery(
				'SELECT `aclWrite` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `optSection` = ? AND `optOption` = ?',
				$params
			);
		} elseif ($value == null && $index != null) {
			// Delete specific array entry
			$params = array(
				$section,
				$option,
				$index
			);
			$result = $db->rawQuery(
				'SELECT `aclWrite` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `optSection` = ? AND `optOption` = ? AND `optIndex` = ?',
				$params
			);
		} elseif (!empty($value)) {
			// Delete array entry with the corresponding value
			//
		} else {
			// If this case, then error
			echo "DELETING FATAL ERROR!!!";
		}



	}
	*/


	/* User functions:
	 *
	 * setUser: Define user context
	 * getUser: Get user context (login)
	 * setUserPassword: Define user password
	 * checkUserPassword: Compare user password
	 *
	 *
	 *
	 *
	 *
	 */



	// Set user context
	function setUser($user = null) {

		// Sanitize input
		$user = filter_var($user, FILTER_SANITIZE_STRING);

		// Guess user to use if no argument
		if(!$user) {
			$auth = $this->get("auth");
			if ($auth != "users")
				$user = $auth;
			else
				$user = $this->_user;
		}

		// Test if user exists
		$db = $this->_db;
		$params = array(
			'user.' . $user,
			'password'
		);
		$result = $db->rawQuery('SELECT `optSection` FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE `optSection` = ? AND `optOption` = ?', $params);

		// Set the user if valid
		if ( empty($result)) {
			doLog("WARNING: Trying to set context for ".$user." but this user is not valid. Keep $this->_user context");
			return null;
		} else {
			$this->_user = $user;
			//doLog("INFO: Context set for user $user (auth= ".$this->get('auth').")");
			return 0;
		}
	}

	// Get user setting
	function getUser($option) {

		// Sanitize input
		$option = filter_var($option, FILTER_SANITIZE_STRING);

		if (empty($this->_user)) {
			doLog("DEBUG: No context user set. Please setUser() method before");
			return null;
		}

		if ($setting == "current") 
			return $this->_user;

		return get($option, $section = 'user.' . $this->_user);
	}

	// Set password user
	function setUserPassword($password, $user = null) {

		// Sanitize input
		$user = filter_var($user, FILTER_SANITIZE_STRING);
		if ($user == null)
			$user = $this->user;


		// format: algorithm:iterations:salt:hash
		$salt = base64_encode(mcrypt_create_iv(PBKDF2_SALT_BYTES, MCRYPT_DEV_URANDOM));

		$params = array( 
			PBKDF2_HASH_ALGORITHM . ":" . PBKDF2_ITERATIONS . ":" .  $salt . ":" .
			base64_encode(pbkdf2(
				PBKDF2_HASH_ALGORITHM,
				$password,
				$salt,
				PBKDF2_ITERATIONS,
				PBKDF2_HASH_BYTES,
				true)),
			'user.'.$user,
			'password',
			$user
			);

		$db = $this->db;
		return $db->update('UPDATE FROM `' . IFY_MYSQL_PREFIX . 'options` SET value = ? WHERE section == ? AND option == ? AND aclWrite == ?', $params);
	}

	// Check user password
	function checkUserPassword($password, $user = null) {

		// Sanitize input
		$user = filter_var($user, FILTER_SANITIZE_STRING);
		if ($user == null)
			$user = $this->user;

		// Get hash
		$db = $this->db;
		$params = array(
			'user.' . $user,
			'password'
		);
		$result = $db->rawQuery('SELECT value FROM `' . IFY_MYSQL_PREFIX . 'options` WHERE section == ? AND option = ?', $params);

		if ( empty($result) ) {
			return null;
		} else {
			$good_hash = $result[0];
		}

		$params = explode(":", $good_hash);
		if(count($params) < HASH_SECTIONS)
		   return false;
		$this->pbkdf2 = base64_decode($params[HASH_PBKDF2_INDEX]);
		return $this->slow_equals(
			$this->pbkdf2,
			pbkdf2(
				$params[HASH_ALGORITHM_INDEX],
				$password,
				$params[HASH_SALT_INDEX],
				(int)$params[HASH_ITERATION_INDEX],
				strlen($this->pbkdf2),
				true
			)
		);
	}




	/*
	 * Private functions
	 *
	 * slow_equals: Password comparison function
	 * pbkdf2: Password hash algorithm function
	 * Source: http://crackstation.net/hashing-security.htm
	*/

	protected function slow_equals($a, $b) {
		$diff = strlen($a) ^ strlen($b);
		for($i = 0; $i < strlen($a) && $i < strlen($b); $i++)
		{
			$diff |= ord($a[$i]) ^ ord($b[$i]);
		}
		return $diff === 0;
	}


	protected function pbkdf2($algorithm, $password, $salt, $count, $key_length, $raw_output = false) {
		$algorithm = strtolower($algorithm);
		if(!in_array($algorithm, hash_algos(), true))
			die('PBKDF2 ERROR: Invalid hash algorithm.');
		if($count <= 0 || $key_length <= 0)
			die('PBKDF2 ERROR: Invalid parameters.');

		$hash_length = strlen(hash($algorithm, "", true));
		$block_count = ceil($key_length / $hash_length);

		$output = "";
		for($i = 1; $i <= $block_count; $i++) {
			// $i encoded as 4 bytes, big endian.
			$last = $salt . pack("N", $i);
			// first iteration
			$last = $xorsum = hash_hmac($algorithm, $last, $password, true);
			// perform the other $count - 1 iterations
			for ($j = 1; $j < $count; $j++) {
				$xorsum ^= ($last = hash_hmac($algorithm, $last, $password, true));
			}
			$output .= $xorsum;
		}

		if($raw_output)
			return substr($output, 0, $key_length);
		else
			return bin2hex(substr($output, 0, $key_length));
	}

}
?>
