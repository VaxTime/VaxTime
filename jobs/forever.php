<?php

/**
 * Runs and keeps running any PHP script in a subfolder called forever/. The
 * "dead-time" between which the forevered scripts may not be running depends on
 * the cron time that triggers this. Max recommended time is each hour. Min
 * should be each minute.
 *
 * It will also log everything in a file with the same name as the script, but
 * ending in .log
 *
 * Important. When forever re-runs a script and its log is bigger than 5mb, it
 * gets archived with the file name and the date it was archived, and starts a
 * new script. It never deletes them, keep that in mind!
 */

define('FOREVER_DIR', __DIR__ . '/forever/');
define('LOG_DIR', FOREVER_DIR . 'logs/');
define('PHP_EXECUTABLE', 'php');
define('MAX_LOG_SIZE', 5242880); // 5mb

$files = scandir(FOREVER_DIR);
$php_scripts = [];

foreach ($files as $file) {
  if (preg_match("/(.*)\.forever\.php\d?$/", $file, $matches)) {
    $php_scripts[$file] = $matches[1];
  }
}

$processlist = shell_exec('ps -C php -f');

foreach ($php_scripts as $file => $name) {
  $command = PHP_EXECUTABLE . " " . FOREVER_DIR . "{$file}";

  if (strpos($processlist, $command) === FALSE) {
    $log_file = LOG_DIR . "{$name}.log";
    $now = date("Y-m-d H:i:s");

    if (!file_exists($log_file)) {
      touch($log_file);
    } else if (filesize($log_file) > MAX_LOG_SIZE) {
      rename($log_file, LOG_DIR . "{$name}.{$now}.log");
      touch($log_file);
    }

    file_put_contents($log_file, file_get_contents($log_file) . "\n[Foverer] Script started at {$now}\n\n");
    shell_exec("$command >> $log_file 2>&1 &");
  }
}
