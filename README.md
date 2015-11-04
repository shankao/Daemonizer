# Daemonizer

This project aims to greatly simplify the creation of system daemons in PHP, by hidding the low-level details
after a modern class-based interface.

### A very simple example

Daemon that echoes a string once per second:

```php
class example implements Daemonizer\Daemonizable {
  public function sleep_time() {
    return 1; // In seconds
  }
  
  // You can switch on/off the daemon
  public function is_enabled() {
    return true;
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
