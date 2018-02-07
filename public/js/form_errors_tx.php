<?php 

$lang_code = isset($_GET['lang_code'])? $_GET['lang_code'] : 'en';

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../../config/config.php';
require_once VAX_SRC . '/utils/Translation.class.php';
global $app;

$app = new Silex\Application();

require_once VAX_CONFIG . '/db.php';

Translation::init($app['db'], $lang_code);

$translations = [
  'day_required' => Translation::instance()->show('error_text_day_required'),
  'email_invalid' => Translation::instance()->show('error_text_email_invalid'),
  'email_required' => Translation::instance()->show('error_text_email_required'),
  'gender_required' => Translation::instance()->show('error_text_gender_required'),
  'month_required' => Translation::instance()->show('error_text_month_required'),
  'name_required' => Translation::instance()->show('error_text_name_required'),
  'year_required' => Translation::instance()->show('error_text_year_required')
];

header('Content-Type: text/javascript;charset=UTF-8');
echo 'var error_text_translations = ' . json_encode($translations);