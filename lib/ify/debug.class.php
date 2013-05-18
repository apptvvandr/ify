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
	
	// Timer
	private $timer;


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
		

		// Get the first arg
		if (count($args) > 1)
			$arg = array_shift($args);
		else
			$arg = $args;


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
		$message = $arg;
		$last = "";


		//echo "<br>get all elements <br>";
		//var_dump( $args);
		//echo "<br>";


		// Display message in HTML
		if ($this->print)
		{
			echo '<div class="log log_'.$level.'">';
			echo "$level: $message \n";
			if (count($args) == 1 )
			{
				echo '<pre>';
				var_dump( $args[0]);
				echo '</pre>';
			}
			elseif (count($args) > 1 )
			{
				echo "<pre>";
				var_dump( $args);
				echo '</pre>';
			}
			echo '</div>';
		}
	}

	public function timerStart() {
		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];

		$this->timer = $time;
	}

	public function timerGet() {
		$start = $this->timer;

		$time = microtime();
		$time = explode(' ', $time);
		$time = $time[1] + $time[0];
		$finish = $time;
		$total_time = round(($finish - $start), 4);
		echo '<br><br><br><hr>Page generated in '.$total_time.' seconds.</br>';
	}
}



// Declare debug variable
global $d;
$d = new ifyDebug;

// Exports to shortcuts
if ( ! function_exists( 'l' ) ) {
	function l () 
	{
		global $d;
		$args = func_get_args();
		call_user_func_array( array($d, "log"), $args);
	}
}


//$d->print = false;

?>
