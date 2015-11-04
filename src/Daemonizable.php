<?php
namespace Daemonizer;

interface Daemonizable {
	public function sleep_time();	// Time to sleep in seconds; you can return 0
	public function run();		// Main loop function
}
?>
