<?php

require_once VAX_SRC . '/utils/Calendar.class.php';
require_once VAX_SRC . '/utils/Translation.class.php';
require_once VAX_SRC . '/utils/DateIntervalEnhanced.class.php';

class Vaccines {

    public $antigenId;
    public $vaccineScheduleId;
    public $name;
    public $rounds;
    public $diseases;
    public $targets;
    public $entireCountry;
    public $originalComments;
    public $commentsTxCode;
    public $originalCommentsParsed;
    public $originalDescription;
    public $descriptionTxCode;
    public $interval;

    public function __construct($rawData) {
        $this->vaccineScheduleId = $rawData['vaccine_schedule_id'];
        $this->antigenId = $rawData['antigen_id'];
        $this->name = $rawData['antigen'];
        $this->rounds = $rawData['rounds'];
        $this->diseases = $rawData['diseases'];
        $this->targets = $rawData['targets'];
        $this->originalComments = $rawData['comments'];
        $this->originalCommentsParsed = $this->parseComments();
        $this->commentsTxCode = $rawData['comments_tx_code'];
        $this->entireCountry = $rawData['entire_country'];
        $this->originalDescription = $rawData['description'];
        $this->descriptionTxCode = $rawData['description_tx_code'];
        $this->interval = $rawData['vac_interval'];
        return $this;
    }

    public function description() {
        try {
            return Translation::instance()->show($this->descriptionTxCode);
        } catch (Exception $e) {
            return $this->originalDescription;
        }
    }

    public function comments() {
        try {
            return Translation::instance()->show($this->commentsTxCode);
        } catch (Exception $e) {
            return $this->originalCommentsParsed;
        }
    }

    public static function getByCountryId($db, $countryId) {
        $vaccines = [];
        $rawVaccines = $db->fetchAll("SELECT MIN(vs.id) AS vaccine_schedule_id, MIN(vs.vac_interval) AS vac_interval, vs.rounds, MIN(vs.seconds) AS seconds, a.antigen, a.id as antigen_id, a.description, a.description_tx_code, cv.* FROM " . VAX_DB_PREFIX . "country_vaccines cv LEFT JOIN " . VAX_DB_PREFIX . "vaccine_schedules vs ON (vs.cv_id = cv.id) LEFT JOIN " . VAX_DB_PREFIX . "antigens a ON (a.id = cv.antigen_id) WHERE cv.visible = 1 AND cv.country_id = ? AND vs.id IS NOT NULL GROUP BY a.id, cv.country_id, vs.rounds ORDER BY seconds ASC, vs.id ASC", [$countryId]);

        foreach ($rawVaccines as $vac) {
            $vac['diseases'] = implode(', ', self::getVaccineDiseasesByCountry($db, $vac['antigen_id'], $countryId));
            $vaccines[] = new Vaccines($vac);
        }
        return $vaccines;
    }

    public static function getSpecialSchedulesByChild($db, Child $child) {
        $specialVaccines = [];
        $rawVaccines = $db->fetchAll("SELECT MIN(vs.id) AS vaccine_schedule_id, MIN(vs.vac_interval) AS vac_interval, vs.rounds, MIN(vs.seconds) AS seconds, a.antigen, a.id as antigen_id, a.description, a.description_tx_code, cv.* FROM " . VAX_DB_PREFIX . "country_vaccines cv LEFT JOIN " . VAX_DB_PREFIX . "special_schedules vs ON (vs.cv_id = cv.id) LEFT JOIN " . VAX_DB_PREFIX . "antigens a ON (a.id = cv.antigen_id) WHERE cv.visible = 1 AND cv.country_id = ? AND vs.id IS NOT NULL GROUP BY a.id, cv.country_id, vs.rounds ORDER BY seconds ASC, vs.rounds ASC, vs.id ASC", [$child->countryId]);

        foreach ($rawVaccines as $special) {
            if ($child->gender == $special['targets'] || $special['targets'] == 'all') {
                $special['diseases'] = implode(', ', self::getVaccineDiseasesByCountry($db, $special['antigen_id'], $child->countryId));
                $specialVaccines[] = new Vaccines($special);
            }
        }
        return !empty($specialVaccines) ? $specialVaccines : false;
    }

/**
* Return the vaccines a child would receive. It only considers the gender and
* country as filterable data
*
* @param  DoctrineServiceProvider $db    The database object
* @param  Child $child The child to get info from
* @return array The vaccines that should be shot to this child
*/
public static function getByChild($db, Child $child) {
    $filteredVaccines = [];
    $countryVaccines = Vaccines::getByCountryId($db, $child->countryId);

    foreach ($countryVaccines as $vaccine) {
        if ($child->gender == $vaccine->targets || $vaccine->targets=='all') {
            $filteredVaccines[] = $vaccine;
        }
    }
    return $filteredVaccines;
}

public static function getVaccineDiseasesByCountry($db, $antigenId, $countryId) {
    $diseases = $db->fetchAll("SELECT DISTINCT(disease), disease_tx_code FROM " . VAX_DB_PREFIX . "country_vaccines WHERE visible = 1 AND antigen_id = ? and country_id = ?", [$antigenId, $countryId]);

    return array_map(function($a) use ($diseases) {
        try {
            return Translation::instance()->show($a['disease_tx_code']);
        } catch (Exception $e) {
            return $a['disease'];
        }
    }, $diseases);
}

public function getDateByLocale($date, $lang='EN') {
    $formatter = new IntlDateFormatter($lang, IntlDateFormatter::MEDIUM, IntlDateFormatter::NONE);

    return $formatter->format(strtotime($date));
}

public function getVaccineDate($birthday) {
    $date = new DateTime($birthday);
    $date->add(new DateInterval($this->interval));

    return $date->format("Y-m-d");
}

public function getFormattedInterval() {
    $format = [];
    $hfi = new DateIntervalEnhanced($this->interval);

    if($hfi->y !== 0) {
        $format[] = Translation::instance()->show('period_years', ['<NUM_YEARS&&>' => '%d' ]);
    }

    if($hfi->m !== 0) {
        $format[] = Translation::instance()->show('period_months', ['<NUM_MONTHS&&>' => '%d' ]);
    }

    if($hfi->d !== 0) {
        $format[] = Translation::instance()->show('period_days', ['<NUM_DAYS&&>' => '%d' ]);
    }

    if(count($format) > 1) {
        $format = sprintf("%s & %s", array_shift($format), array_shift($format));
    } else {
        $format = array_pop($format);
    }

    if ($this->interval == 'P0Y0M0D') {
        return Translation::instance()->show('first_contact');
    } else if ($this->interval == 'P0D') {
        return Translation::instance()->show('special_shot');
    } else {
        return $hfi->format($format);
    }
}

public function getHumanInterval() {
    preg_match("/P(\d+)(\w+)/", $this->interval, $matches);

    if ($matches[1] == 0) {
        return Translation::instance()->show('period_birth');
    }

    switch ($matches[2]) {
        case 'D':
        return Translation::instance()->show('period_days', ['<NUM_DAYS&&>' => $matches[1] ]);
        case 'W':
        return Translation::instance()->show('period_weeks', ['<NUM_WEEKS&&>' => $matches[1] ]);
        case 'M':
        return Translation::instance()->show('period_months', ['<NUM_MONTHS&&>' => $matches[1] ]);
        case 'Y':
        return Translation::instance()->show('period_years', ['<NUM_YEARS&&>' => $matches[1] ]);
    }
}

private function parseComments() {
    $parsed = '';
    $acronyms = ['cbaw', 'hcw', 'pw'];
    $dict = ['child bearing age women', 'health care workers', 'pregnant women'];

    $timeIntervals = array (
        '/[=]/' => 'or ',
// '/<\s([\d])+\syear\schildren/i' => 'children less than Y$1',
        '/([YMWD])(\d+)/' => '$2$1',
        '/(\d+)\s?yrs/i' => '$1Y',
        '/>/' => 'greater than ',
        '/</' => 'less than ',
        '/(\d+)Y/' => '$1 years of age',
        '/(\d+)M/' => '$1 months of age',
        '/(\d+)W/' => '$1 weeks of age',
        '/D(\d+)|(\d+)D/' => '$1 days of age');

    $parsed = preg_replace(array_keys($timeIntervals), array_values($timeIntervals), $this->originalComments);

    return str_ireplace($acronyms, $dict, $parsed);
}

private function eventName() {
    return Translation::instance()->show('important_title') . ' ' . (empty($this->description()) ? $this->name : $this->description());
}

public function getGoogleCalendarLink($birthday) {
    return Calendar::google($this->eventName(), $this->getVaccineDate($birthday));
}

public function getOutlookCalendarLink($birthday) {
    return Calendar::outlook($this->eventName(), $this->getVaccineDate($birthday));
}

public function shouldOfferCalendarLinks($birthday) {
    return strtotime($this->getVaccineDate($birthday)) > time();
}

public static function getByScheduledId($db, $vaccineScheduleId) {
    $rawVaccine = $db->fetchAssoc("SELECT MIN(vs.id) AS vaccine_schedule_id, MIN(vs.vac_interval) AS vac_interval, vs.rounds, MIN(vs.seconds) AS seconds, a.antigen, a.id as antigen_id, a.description, a.description_tx_code, cv.* FROM " . VAX_DB_PREFIX . "country_vaccines cv LEFT JOIN " . VAX_DB_PREFIX . "vaccine_schedules vs ON (vs.cv_id = cv.id) LEFT JOIN " . VAX_DB_PREFIX . "antigens a ON (a.id = cv.antigen_id) WHERE cv.visible = 1 AND vs.id = ? AND vs.id IS NOT NULL GROUP BY a.id, cv.country_id, vs.rounds ORDER BY seconds ASC, vs.id ASC", [$vaccineScheduleId]);
    $rawVaccine['diseases'] = implode(', ', self::getVaccineDiseasesByCountry($db, $rawVaccine['antigen_id'], $rawVaccine['country_id']));
    $vaccine = [];
    $vaccine = new Vaccines($rawVaccine);

    return $vaccine;
}

public static function generateAntigensListByScheduledEmails($db, $scheduledEmails) {
    $vaxAntigens = [];

    foreach ($scheduledEmails as $scheduledEmail) {
        $vaxAntigens[] = self::getByScheduledId($db, $scheduledEmail->vaccineScheduleId);
    }
    return $vaxAntigens;
}
}
