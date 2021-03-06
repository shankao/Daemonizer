#!/usr/bin/php
<?php
require_once 'vendor/autoload.php';

class example implements Daemonizer\Daemonizable {

	private $time_start;

	public function __construct() {
		$this->time_start = microtime(true);
	}

	public function sleep_time() {
		// This example always has work; never goes to sleep
		return 0;
	}

	// do something 1M times in the background and print how long it took
	public function run() {
		static $i = 0;
		if ($i == 1000000) {
			$now = microtime(true);
			$elapsed = $now - $this->time_start;
			$this->time_start = $now;
			$this->log_daemon($elapsed);
			$i = 0;
		} else {
			$i++;
		}
	}

	// Use our own logging function
	static public function log_daemon($message) {
		$date = date('H:i:s');
		echo "$date $message\n";
	}
}

$daemon = new Daemonizer\Daemonizer('example', '././example/example.pid');
$daemon->set_logfn(array('example', 'log_daemon'));
$daemon->run(new example);
?>
