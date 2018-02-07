<?php
$dbOptions = [
  'driver'  => 'pdo_mysql',
  'charset' => 'utf8', 
  'driverOptions' => array(1002 => 'SET NAMES utf8'),
  
  'host'    => VAX_DB_HOST,
  'user'    => VAX_DB_USER,
  'password'=> VAX_DB_PASSWORD,
  'dbname'  => VAX_DB_DBNAME,
];

$app->register(new Silex\Provider\DoctrineServiceProvider(), [
  'db.options'=> $dbOptions
]);
