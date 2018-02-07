<?php
ini_set("memory_limit", "512M");
require_once __DIR__.'/../vendor/autoload.php';
require_once __DIR__ . '/../config/config.php';

function setup_db() {
  global $app;
  $app = new Silex\Application();

  require_once VAX_CONFIG . '/db.php';

  return $app['db'];
}

function save_translations($db, $translations) {
  foreach ($translations as $translation) {
    try {
      $is_ok = $db->insert(
        VAX_DB_PREFIX . 'translations', 
        $translation
      );
    } catch (Exception $e) {
      $db->update(
        VAX_DB_PREFIX . 'translations', 
        [
          'content' => $translation['content']
        ], 
        [
          'id' => $translation['id'],
          'lang_code' => $translation['lang_code']
        ]
      );
    }
  }
}

$db = setup_db();

if (!isset($_GET['lang_code'])) {
  die('No language selected');
}

$lang_code = $_GET['lang_code'];

$file = __DIR__ . "/translations/{$lang_code}.csv";

if (!file_exists($file)) {
  die("File with code {$file} doesn't exist");
}

$translations = [];
if (($handle = fopen($file, 'r')) !== FALSE) {
  while (($row = fgetcsv($handle, 5000, ',')) !== FALSE) {
    if (count($row) == 2) {
      $translations[] = [
        'id' => $row[0],
        'content' => $row[1],
        'lang_code' => $lang_code
      ];
    }
  }
  fclose($handle);

  save_translations($db, $translations);
  echo "Everything's ok for code {$lang_code}";
} else {
  die("The file {$file} couldn't be opened");
}