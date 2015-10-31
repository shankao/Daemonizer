<?php
namespace Daemonizer;

interface Daemonizable {
	public function is_enabled();	// Is the process enabled?
	public function has_work();	// Does the process have any work to do right now? (will sleep otherwise)
	public function run();		// Main work function
}
?>
