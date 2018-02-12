<?php 
global $app;

require_once __DIR__ . '/../config/config.php';

$app['debug'] = VAX_DEBUG;

require_once VAX_CONFIG . '/db.php';
require_once VAX_CONFIG . '/templates.php';

require_once VAX_SRC . '/models/Child.class.php';
require_once VAX_SRC . '/models/Language.class.php';
require_once VAX_SRC . '/models/Country.class.php';
require_once VAX_SRC . '/models/Vaccines.class.php';

require_once VAX_SRC . '/emails/Email.class.php';
require_once VAX_SRC . '/emails/ReminderEmail.class.php';
require_once VAX_SRC . '/emails/ScheduledEmail.class.php';

require_once VAX_SRC . '/utils/Translation.class.php';
require_once VAX_SRC . '/utils/EmailProvider.class.php';
require_once VAX_SRC . '/utils/VaxTimeControllerProvider.class.php';

$languages = Language::getAll($app['db']);
$currentYear = date("Y");

$app['twig']->addGlobal('languages', $languages);
$app['twig']->addGlobal('currentYear', $currentYear);