<?php
global $app;
require_once __DIR__ . '/../config/config.php';

use Symfony\Component\HttpFoundation\Session\Storage\Handler\PdoSessionHandler;

$app['debug'] = VAX_DEBUG;

require_once VAX_CONFIG . '/db.php';
require_once VAX_CONFIG . '/templates.php';

require_once VAX_SRC . '/models/Child.class.php';
require_once VAX_SRC . '/models/Language.class.php';
require_once VAX_SRC . '/models/Country.class.php';
require_once VAX_SRC . '/models/Vaccines.class.php';
require_once VAX_SRC . '/models/User.class.php';
require_once VAX_SRC . '/models/ImportFile.class.php';

require_once VAX_SRC . '/emails/Email.class.php';
require_once VAX_SRC . '/emails/ReminderEmail.class.php';
require_once VAX_SRC . '/emails/ScheduledEmail.class.php';

require_once VAX_SRC . '/utils/Translation.class.php';
require_once VAX_SRC . '/utils/EmailProvider.class.php';
require_once VAX_SRC . '/utils/VaxTimeControllerProvider.class.php';
require_once VAX_SRC . '/utils/UserControllerProvider.class.php';

$languages = Language::getAll($app['db']);
$currentYear = date("Y");

$app['twig']->addGlobal('languages', $languages);
$app['twig']->addGlobal('currentYear', $currentYear);


$app->register(new Silex\Provider\SessionServiceProvider());
$app['session.storage.handler'] = function () use ($app) {
    return new PdoSessionHandler(
        $app['db']->getWrappedConnection(),
        [
            'db_table'        => VAX_DB_PREFIX . 'session',
            'db_id_col'       => 'session_id',
            'db_data_col'     => 'session_value',
            'db_lifetime_col' => 'session_lifetime',
            'db_time_col'     => 'session_time',
        ]
    );
};