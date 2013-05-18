<?php



// This function is used for debug only
function doLog($message) {
	$log=false;
	$print=true;
	$trace=false;

	if ($log) {
		$filename = "log.txt";
		$fh = fopen($filename, "a") or die("Could not open log file.");
		fwrite($fh, date("d-m-Y, H:i")."\t: $text\n") or die("Could not write file!");
		fclose($fh);
	}
	if ($print) {
		echo "<pre>";
		echo "$message";
		echo "</pre>";
	}
	if ($trace) {
		echo "<pre>";
		debug_print_backtrace();
		echo "</pre>";
	}
}


function grab_dump($var)
{
    ob_start();
    var_dump($var);
    return ob_get_clean();
}


class ifyDebug {

	// Output options
	static $write;
	static $print;

	// Display option
	static $trace;

	// Levels
	static $levels;

	// Log file path
	private $file;


	function __construct($logFile = null)
	{

		$this->print = true;

		$this->levels = array (
			0 => "ERROR",		// Process quit and can't continue
			1 => "WARNING",		// Process error, but can follow
			2 => "INFO",		// Process explain what it does
			3 => "DEBUG"		// Process debug
			);

		return 0;
	}

	// Main function to log messages
	function log()
	{
		
		// Default values
		$level = $this->levels[3];
		$message = "";	
		$args = func_get_args();
		

		echo "<br>get all elements <br>";
		var_dump( $args);
		echo "<br>";


		// Get the first arg
		$arg = array_shift($args);


		// Try to get level
		if (in_array($arg, $this->levels)) 
		{
			$level = $arg;
			$arg = array_shift($args);
		} 
		//elseif (gettype($arg) == "integer" && count($this->levels) <= $arg)
		elseif (gettype($arg) == "integer" && count($this->levels) >= $arg)
		{
			$level = $this->levels[$arg];
			$arg = array_shift($args);
		}
		


		// Get the message
		var_dump($args);
		$message = $arg;
		$last = "";


		//echo "<br>get last <br>";
		//var_dump($args);
		//echo "<br>";


		// Debug all remaining elements
		//$last = array_map( "grab_dump" ,$args);
		$last = grab_dump($args);

		// Display message in HTML
		if ($this->print)
		{
			echo '<pre class="log log_".$level.">';
			echo "$level: $message \n";
			var_dump($args);
			echo '</pre>';
		}
	}


}



// Exports to shortcuts

if ( ! function_exists( 'l' ) ) {
	function l () 
	{
		$args = func_get_args();
		call_user_func_array( 'ifyDebug::log', $args);
	}
}


?>
