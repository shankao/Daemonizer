# Daemonizer

This project aims to greatly simplify the creation of system daemons in PHP, by hidding the low-level details
after a modern class-based interface.

Daemonizer implements a fast and simple main loop for you, so you don't need to take care of the details.
A disadvantage of this is that you *must* let your class' run() function end from time to time (no infinite loops). Failing to do this will make your daemon unresponsive to external signals (i.e. sigterm)

Daemonizer uses a version of PEAR's System_Daemon internally. Find it here: [kvz/system_daemon](https://github.com/kvz/system_daemon)

### A very simple example

Daemon that echoes a string once per second:

```php
class example implements Daemonizer\Daemonizable {
  public function sleep_time() {
    return 1; // In seconds
  }
  
  public function run() {
    echo "Hey there!\n";
  }
}

$daemon = new Daemonizer\Daemonizer('example', '././example/example.pid');
$daemon->run(new example);
```

### About the PID location parameter

Sadly, using System_Daemon internally has its drawbacks. One of the most visible in Daemonizer is the way that the PID file location has to be indicated: 

1. It **requires** to have at least two backslashes to start. In our example, the first part of "././example/example.pid" is only used to refer to the current folder. This requirement is due to System_Daemon expecting PID's to be system-wide located, like in /var/run (see the two backslashes)
2. It **requires** that, after the two slashes, comes a folder with the same name as the daemon's name. In the example before, the "example" folder part. The reason is the same: System_Daemon requires to follow a UNIX system-wide protocol for PID file locations.

This problems will be fixed with the depart from System_Daemon in the future

### Next steps

For next releases, I intend to keep the same functionality, without using System_Daemon internally, as its code has become old and bloated. Given that I intend to keep Daemonizer simple and lightweight, that last part is not good.
Also, the PID location quirk is painful for a real-world project.
