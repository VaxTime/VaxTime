<?php 

use Symfony\Component\HttpFoundation\Request;
use Silex\Api\ControllerProviderInterface;

class VaxTimeControllerProvider implements ControllerProviderInterface {
    private $userLanguage;
    private $route;

    public function __construct($userLanguage) {
        $this->userLanguage = $userLanguage;
    }

    private function initWithLang($app) {
        $formatter = new IntlDateFormatter($this->userLanguage, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);

        $app['twig']->addGlobal('userLanguage', $this->userLanguage);
        $app['twig']->addGlobal('lastUpdateDate', $formatter->format(strtotime('2017-12-12')));

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
        $app['twig']->addGlobal('textDirection', isset($userLanguageObject)? $userLanguageObject->direction : 'ltr');
        return $app;
    }

    public function connect(Silex\Application $app) {
        $controller = $app['controllers_factory'];

        // INDEX PAGE
        $controller->get('/', function() use ($app) {
            $app = $this->initWithLang($app);

            if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
                $IParray=array_values(array_filter(explode(',',$_SERVER['HTTP_X_FORWARDED_FOR'])));
                $ip = $IParray[0];
            } else {
                $ip = $_SERVER['REMOTE_ADDR'];
            }

            $countries = Country::getAllWithVaccineSchedules($app['db']);
            $userCountry = Country::getByIp($app['db'], $ip);

            return $app['twig']->render('site/index.twig', ['countries' => $countries, 'userCountry' => $userCountry]);
        })->bind("index_{$this->userLanguage}");


        // MY SCHEDULE PAGE
        $controller->post('/my-schedule', function(Request $request) use ($app) {
            $app = $this->initWithLang($app);

            $day = $request->request->get('day');
            $month = $request->request->get('month');
            $year = $request->request->get('year');
            $gender = $request->request->get('gender');
            $country = $request->request->get('country');
            $zip = $request->request->get('zip');
            $lang = $request->request->get('lang');

            $child = Child::createDummy("{$year}-{$month}-{$day}", $gender, $lang, $country, empty($zip) ? null : $zip);
            $vaccines = Vaccines::getByChild($app['db'], $child);
            $specialSchedules = Vaccines::getSpecialSchedulesByChild($app['db'], $child);
            $myCountry = Country::getById($app['db'], $country);

            $whoUrl = str_replace('{COUNTRY}', $myCountry->iso3, "http://apps.who.int/immunization_monitoring/globalsummary/schedules?sc%5Bc%5D%5B%5D={COUNTRY}&sc%5Bd%5D=&sc%5Bv%5D%5B%5D=AP&sc%5Bv%5D%5B%5D=BCG&sc%5Bv%5D%5B%5D=CHOLERA&sc%5Bv%5D%5B%5D=DIP&sc%5Bv%5D%5B%5D=DIPHTERIA&sc%5Bv%5D%5B%5D=DT&sc%5Bv%5D%5B%5D=DTAP&sc%5Bv%5D%5B%5D=DTAPHEP&sc%5Bv%5D%5B%5D=DTAPHEPBIPV&sc%5Bv%5D%5B%5D=DTAPHEPIPV&sc%5Bv%5D%5B%5D=DTAPHIB&sc%5Bv%5D%5B%5D=DTAPHIBHEPB&sc%5Bv%5D%5B%5D=DTAPHIBHEPIPV&sc%5Bv%5D%5B%5D=DTAPHIBIPV&sc%5Bv%5D%5B%5D=DTAPIPV&sc%5Bv%5D%5B%5D=DTIPV&sc%5Bv%5D%5B%5D=DTPHIBHEP&sc%5Bv%5D%5B%5D=DTWP&sc%5Bv%5D%5B%5D=DTWPHEP&sc%5Bv%5D%5B%5D=DTWPHIB&sc%5Bv%5D%5B%5D=DTWPHIBHEPB&sc%5Bv%5D%5B%5D=DTWPHIBHEPBIPV&sc%5Bv%5D%5B%5D=DTWPHIBIPV&sc%5Bv%5D%5B%5D=DTWPIPV&sc%5Bv%5D%5B%5D=HEPA&sc%5Bv%5D%5B%5D=HEPA_ADULT&sc%5Bv%5D%5B%5D=HEPAHEPB&sc%5Bv%5D%5B%5D=HEPA_PEDIATRIC&sc%5Bv%5D%5B%5D=HEPB&sc%5Bv%5D%5B%5D=HEPB_ADULT&sc%5Bv%5D%5B%5D=HEPB_PEDIATRIC&sc%5Bv%5D%5B%5D=HEPB_PEDIATRIC&sc%5Bv%5D%5B%5D=HFRS&sc%5Bv%5D%5B%5D=HIB&sc%5Bv%5D%5B%5D=HIB&sc%5Bv%5D%5B%5D=HIBMENC&sc%5Bv%5D%5B%5D=HPV&sc%5Bv%5D%5B%5D=INFLUENZA&sc%5Bv%5D%5B%5D=INFLUENZA_ADULT&sc%5Bv%5D%5B%5D=INFLUENZA_PEDIATRIC&sc%5Bv%5D%5B%5D=IPV&sc%5Bv%5D%5B%5D=JAPENC&sc%5Bv%5D%5B%5D=JE_INACTD&sc%5Bv%5D%5B%5D=JE_LIVEATD&sc%5Bv%5D%5B%5D=MEASLES&sc%5Bv%5D%5B%5D=MENA&sc%5Bv%5D%5B%5D=MENAC&sc%5Bv%5D%5B%5D=MENACWY&sc%5Bv%5D%5B%5D=MENACWY-135+CONJ&sc%5Bv%5D%5B%5D=MENACWY-135+PS&sc%5Bv%5D%5B%5D=MENB&sc%5Bv%5D%5B%5D=MENBC&sc%5Bv%5D%5B%5D=MENC_CONJ&sc%5Bv%5D%5B%5D=MM&sc%5Bv%5D%5B%5D=MMR&sc%5Bv%5D%5B%5D=MMRV&sc%5Bv%5D%5B%5D=MR&sc%5Bv%5D%5B%5D=MUMPS&sc%5Bv%5D%5B%5D=OPV&sc%5Bv%5D%5B%5D=PNEUMO_CONJ&sc%5Bv%5D%5B%5D=PNEUMO_PS&sc%5Bv%5D%5B%5D=RABIES&sc%5Bv%5D%5B%5D=ROTAVIRUS&sc%5Bv%5D%5B%5D=RUBELLA&sc%5Bv%5D%5B%5D=TBE&sc%5Bv%5D%5B%5D=TD&sc%5Bv%5D%5B%5D=TDAP&sc%5Bv%5D%5B%5D=TDAP&sc%5Bv%5D%5B%5D=TDAPIPV&sc%5Bv%5D%5B%5D=TDIPV&sc%5Bv%5D%5B%5D=TT&sc%5Bv%5D%5B%5D=TYPHOID&sc%5Bv%5D%5B%5D=TYPHOIDHEPA&sc%5Bv%5D%5B%5D=VARICELLA&sc%5Bv%5D%5B%5D=VITA&sc%5Bv%5D%5B%5D=VITAMINA&sc%5Bv%5D%5B%5D=YF&sc%5Bv%5D%5B%5D=ZOSTER&sc%5BOK%5D=OK");

            return $app['twig']->render('site/myschedule.twig', ['specialSchedules' => $specialSchedules, 'schedules' => $vaccines, 'child' => $child, 'country' => $myCountry->countryName(), 'whoUrl' => $whoUrl]);
        });

        $controller->get('/my-schedule', function() use ($app) {
            $app = $this->initWithLang($app);

            return $app->redirect($app['url_generator']->generate("index_{$this->userLanguage}"));
        })->bind("my-schedule-error_{$this->userLanguage}");

        // THANK YOU PAGE
        $controller->get('/thank-you/{childId}/{hash}', function($childId, $hash = "") use ($app) {
            $app = $this->initWithLang($app);

            $child = Child::getById($app['db'], $childId, true);
            if ($child && Email::isHashCorrect($child, $hash)) {
                return $app['twig']->render('site/thankyou.twig', ['child' => $child, 'specialSchedules' => Vaccines::getSpecialSchedulesByChild($app['db'], $child), 'schedules' => $child->getMySchedule(), 'country' => Country::getById($app['db'], $child->countryId)->countryName()]);
            } else {
                return "Sorry, but it seems the data ({$childId} and {$hash}) is not correct.";
            }
        })->bind("thank-you_{$this->userLanguage}");

        $controller->get('/thank-you/{childId}', function($childId) {
            return "Sorry, but the url should be /thank-you/CHILD_NUMBER/SPECIAL_CODE";
        })->bind("thank-you-error_{$this->userLanguage}");

        // SUBSCRIBE CALL
        $controller->post('/subscribe', function(Request $request) use ($app) {
            $app = $this->initWithLang($app);

            $email = $request->request->get('email');
            $name = $request->request->get('name');
            $birthday = $request->request->get('birthday');
            $gender = $request->request->get('gender');
            $country = $request->request->get('country');
            $zip = $request->request->get('zip');
            $lang = $request->request->get('lang');

            $child = new Child($app['db'], $email, $name, $birthday, $gender, $lang, $country, empty($zip) ? null : $zip);
            $specialSchedules = Vaccines::getSpecialSchedulesByChild($app['db'], $child);
            $child->subscribe();

            $emailProvider = new EmailProvider($app);
            $welcomeEmail = Email::factory($app['db'], 'welcome', $child, []);
            $emailProvider->sendEmail($welcomeEmail);

            return $app->redirect($app['url_generator']->generate("thank-you_{$this->userLanguage}", ['childId' => $child->childId, 'hash' => Email::userHash($child)]));
        });

        // UNSUBSCRIBE PAGE
        $controller->get('/unsubscribe/{childId}/{hash}', function(Request $request, $childId, $hash) use ($app) {
            $app = $this->initWithLang($app);

            $child = Child::getById($app['db'], $childId, true);

            if ($child && Email::isHashCorrect($child, $hash)) {
                $child->unsubscribe();
                return $app['twig']->render('site/unsubscribed.twig');
            } else {
                return "Sorry, but it seems the data ({$childId} and {$hash}) is not correct.";
            }
        })->bind("unsubscribe_{$this->userLanguage}");

        return $controller;
    }
}