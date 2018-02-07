<?php
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';

abstract class ForeverCron {
  protected $app;
  protected $db;



function setup_db() {
  global $app;
  $app = new Silex\Application();


  return $app['db'];
}

  public function __construct() {
    global $app;
    $app = new Silex\Application();
    
    require_once VAX_CONFIG . '/db.php';
    require_once VAX_CONFIG . '/templates.php';

    $this->app = $app;

    $this->db = $this->app['db'];

    Translation::init($this->db, 'en');
  }
  public abstract function run();
  public function log($text) {
    echo "[" . date('Y-m-d H:i:s') . "] " . $text . PHP_EOL;
  }
  public function sleep($seconds) {
    sleep($seconds);
  }

  public function usleep($microseconds) {
    usleep($microseconds);
  }

  protected function pingOrDie() {
    if (!$this->db->ping()) {
      $this->log("Connection was lost. Killing the script");
      die();
    }
  }
}
