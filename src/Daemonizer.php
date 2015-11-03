<?php
namespace Daemonizer;

use System_Daemon;

require_once __DIR__.'/../vendor/kvz/system_daemon/System/Daemon.php';

class Daemonizer {
	private $finish = false;
	private $logfn = null;
	private $restartfn = null;
	private $finishfn = null;
	private $appdir = null;
	private $uid = null;
	private $gid = null;
	private $loglevel = null;

	public function php_errors ($errno, $errstr, $errfile, $errline) {
		call_user_func($this->logfn, "$errstr ($errfile:$errline)");
		return false;
	}

	public function php_exceptions(\Exception $ex) {
		call_user_func($this->logfn, 'Exception: ' . $ex->getMessage());
		call_user_func($this->logfn, $ex->getTraceAsString());
	}

	public function log_shutdown () {
		$error = error_get_last();
		if ($error && in_array($error['type'], array(E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_USER_ERROR, E_RECOVERABLE_ERROR))) {
			call_user_func($this->logfn, "ERROR type {$error['type']}: {$error['message']} at {$error['file']}:{$error['line']}");
		}
	}

	public function finish () {
		if ($this->finishfn !== null) {
			call_user_func($this->finishfn);
		} else {
			call_user_func($this->logfn, 'Finish signal received');
		}
		$this->finish = true;
	}

	public function restart() {
		if ($this->restartfn !== null) {
			call_user_func($this->restartfn);
		} else {
			call_user_func($this->logfn, 'Restart signal received');
		}
	}

	public function log_daemon ($message) {
		echo "$message\n";	// Adds newline
	}

	public function set_logfn ($logfn) {
		$this->logfn = $logfn;
	}

	public function set_finishfn ($finishfn) {
		$this->finishfn = $finishfn;
	}

	public function set_restartfn ($restartfn) {
		$this->restartfn = $restartfn;
	}

	public function set_uid ($uid) {
		$this->uid = $uid;
	}

	public function set_gid ($gid) {
		$this->gid = $gid;
	}

	public function set_loglevel ($loglevel) {
		$this->loglevel = $loglevel;
	}

	public function __construct ($daemon_name, $pid_file) {
		System_Daemon::setOption('usePEAR', false);
		$this->logfn = array($this, 'log_daemon');

		set_error_handler(array($this, 'php_errors'));
		set_exception_handler(array($this, 'php_exceptions'));
		register_shutdown_function(array($this, 'log_shutdown'));

		System_Daemon::setOption('appName', $daemon_name);
		System_Daemon::setOption('logPhpErrors', false);
		System_Daemon::setOption('appExecutable', 'dummy');	// System_Daemon requires it, then doesn't use it
		// It's stupid that System_Daemon requires path to have three slashes '/', and the third must match the name of the daemon...
		System_Daemon::setOption('appPidLocation', $pid_file);

		$this->uid = posix_getuid();
		$this->gid = posix_getgid();
		$this->appdir = getcwd();
		$this->loglevel = System_Daemon::LOG_DEBUG;
	}

	public function run (Daemonizable $process) {
		System_Daemon::setOption('useCustomLogHandler', $this->logfn);
		System_Daemon::setSigHandler(SIGTERM, array($this, 'finish'));
		System_Daemon::setSigHandler(SIGHUP, array($this, 'restart'));
		System_Daemon::setOption('logVerbosity', $this->loglevel);
		System_Daemon::setOption('appRunAsUID', $this->uid);
		System_Daemon::setOption('appRunAsGID', $this->gid);
		System_Daemon::setOption('appDir', $this->appdir);

		System_Daemon::start();
		if (System_Daemon::isInBackground() === false) {
			System_Daemon::stop();
			return false;
		}

		// Daemon process from here
		while (!$this->finish && !System_Daemon::isDying()) {
			if ($process->is_enabled()) {
				$process->run();
			}
			$sleep_time = $process->sleep_time();
			if ($sleep_time > 0) {
				System_Daemon::iterate($sleep_time);
			} else {
				pcntl_signal_dispatch();
			}
		}
		System_Daemon::stop();
		return true;
	}
}
?>
