# Daemonizer

This project aims to greatly simplify the creation of system daemons in PHP, by hidding the low-level details
after a modern class-based interface.

Daemonizer implements a fast and simple main loop for you, so you don't need to take care of the details.
A disadvantage of this is that you *must* let your class' run() function end from time to time (no infinite loops). Failing to do this will make your daemon unresponsive to external signals (i.e. sigterm)

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

$daemon = new Daemonizer\Daemonizer('example', './././example/example.pid');
$daemon->run(new example);
```

Note: 
Daemonizer uses [kvz/system_daemon](https://github.com/kvz/system_daemon) internally
