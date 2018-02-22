<?php

use Silex\Api\ControllerProviderInterface;
use Symfony\Component\HttpFoundation\Request;

class UserControllerProvider implements ControllerProviderInterface
{
    private $userLanguage;
    private $route;
    private $sessionUser;

    public function __construct()
    {
        $this->userLanguage = 'en';
    }

    private function isLoggedIn($app) {
        if (null === $user = $app['session']->get('user')) {
            return false;
        } else {
            $this->sessionUser = $user;
            return true;
        }
    }

    public function connect(\Silex\Application $app)
    {
        $controller = $app['controllers_factory'];

        $controller->get('/', function () use ($app) {
            $app = $this->initWithLang($app);
            if (!$this->isLoggedIn($app)) {
                return $app->redirect('/user/login');
            }
            return $app['twig']->render('users/index.twig', []);
        });

        $controller->get('/login', function (Request $request) use ($app) {
            $app = $this->initWithLang($app);
            return $app['twig']->render('users/login.twig', ['showError' => $request->query->get('error', '') == 'wrong']);
        });

        $controller->post('/login', function (Request $request) use ($app) {
            $app = $this->initWithLang($app);
            $email = $request->request->get('email');
            $password = $request->request->get('pwd');

            $user = User::authorise($app['db'], $email, $password);
            if (!!$user) {
                $app['session']->set('user', ['email' => $email, 'id' => $user->id, 'name' => $user->contactName, 'is_admin' => $user->isAdmin()? 1 : 0]);
                return $app->redirect('/user/upload');
            } else {
                return $app->redirect('/user/login?error=wrong');
            }
        });

        $controller->get('/logout', function () use ($app) {
            $app = $this->initWithLang($app);

        });

        $controller->get('/upload', function () use ($app) {
            $app = $this->initWithLang($app);

            if (!$this->isLoggedIn($app)) {
                return $app->redirect('/user/login');
            }

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $IParray=array_values(array_filter(explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'])));
                $ip = $IParray[0];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            $countries = Country::getAllWithVaccineSchedules($app['db']);
            $userCountry = Country::getByIp($app['db'], $ip);

            return $app['twig']->render('users/upload.twig', ['countries' => $countries, 'userCountry' => $userCountry, 'userLanguage' => Language::browserLanguage($app['db']), 'user' => $this->sessionUser]);
        });

        $controller->post('/upload', function (Request $request) use ($app) {
            $app = $this->initWithLang($app);

            if (!$this->isLoggedIn($app)) {
                return $app->redirect('/user/login');
            }

            $tempFile = $_FILES['file'];
            $defaultLang = $request->request->get('default_lang');
            $defaultCountry = $request->request->get('default_country');
            $hasHeader = isset($_POST['has_header']);
            $hasPermission = isset($_POST['has_permission']);
            $importFile = ImportFile::create($app, $this->sessionUser['id'], $tempFile, $hasHeader, $defaultLang, $defaultCountry, $hasPermission);
            $importFile->process();
            exit;
        });

        $controller->get('/processing', function () use ($app) {
            $app = $this->initWithLang($app);

        });

        return $controller;
    }

    private function initWithLang($app)
    {
        $formatter = new IntlDateFormatter($this->userLanguage, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);

        $app['twig']->addGlobal('userLanguage', $this->userLanguage);
        $app['twig']->addGlobal('lastUpdateDate', $formatter->format(strtotime(VAX_LAST_UPDATE_TIME)));

        Translation::init($app['db'], $this->userLanguage);

        $userLanguageObject = Language::getByIsoCode($app['db'], $this->userLanguage);

        $this->route = $_SERVER['REQUEST_URI'];

        $routeParts = explode('/', $this->route);

        if ($routeParts[1] == $this->userLanguage) {
            unset($routeParts[1]);
            $this->route = implode('/', array_values($routeParts));
        }

        $app['twig']->addGlobal('baseRoute', $this->route);
        $app['twig']->addGlobal('langId', $userLanguageObject->langId);
        $app['twig']->addGlobal('textDirection', isset($userLanguageObject) ? $userLanguageObject->direction : 'ltr');
        return $app;
    }
}