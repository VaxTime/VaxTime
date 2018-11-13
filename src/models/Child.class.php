<?php

class Child {

    public $childId;
    public $email;
    public $firstname;
    public $gender;
    public $countryId;
    public $zipcode;
    public $birthday;
    public $langId;
    public $userId;

    private $db;

    public static function createDummy($birthday, $gender, $langId, $countryId, $zipcode = null) {
        return new Child(null, null, null, $birthday, $gender, $langId, $countryId, $zipcode, false, null, null);
    }

    public function __construct($db, $email, $firstname, $birthday, $gender, $langId, $countryId, $zipcode = null, $doInsert = true, $childId = null, $userId = null) {
        if ($doInsert) {
            $isOk = $db->insert(VAX_DB_PREFIX . "children", [
                'email' => $email,
                'first_name' => $firstname,
                'gender' => $gender,
                'country_id' => $countryId,
                'zipcode' => $zipcode,
                'birthday' => $birthday,
                'lang_id' => $langId,
                'user_id' => $userId
            ]);

            if ($isOk) {
                $this->childId = intval($db->lastInsertId());
                $this->email = $email;
                $this->firstname = $firstname;
                $this->gender = $gender;
                $this->countryId = $countryId;
                $this->zipcode = $zipcode;
                $this->birthday = $birthday;
                $this->langId = $langId;
                $this->userId = $userId;
                $this->db = $db;
                return $this;
            }
        } else {
            $this->childId = $childId;
            $this->email = $email;
            $this->firstname = $firstname;
            $this->gender = $gender;
            $this->countryId = $countryId;
            $this->zipcode = $zipcode;
            $this->birthday = $birthday;
            $this->langId = $langId;
            $this->userId = $userId;
            $this->db = $db;
            return $this;
        }
    }

    public static function getByIdAndEmail($db, $childId, $email) {
        $child = $db->fetchAssoc("SELECT * FROM " . VAX_DB_PREFIX . "children WHERE child_id = ? AND email = ?", [$childId, $email]);

        if (!empty($child)) {
            return $child;
        } else {
            return false;
        }
    }

    public static function getById($db, $childId) {
        $child = $db->fetchAssoc("SELECT * FROM " . VAX_DB_PREFIX . "children WHERE child_id = ?", [$childId]);

        if (!empty($child)) {
            return new Child($db, $child['email'], $child['first_name'],  $child['birthday'],  $child['gender'],  $child['lang_id'],  $child['country_id'], $child['zipcode'], false, $child['child_id']);
        } else {
            return false;
        }
    }

    public function getAge() {
        $now = new DateTime();
        $birth = new DateTime($this->birthday);

        $interval = $now->diff($birth);

        if ($interval->y > 0) {
            return $interval->y . ' years old';
        }

        if ($interval->m > 0) {
            return $interval->m . ' months old';
        }

        if ($interval->d > 0) {
            return $interval->d . ' days old';
        }

        if ($interval->d == 0) {
            return 'newborn';
        }
    }

    public function subscribe() {
        $countryVaccines = Vaccines::getByCountryId($this->db, $this->countryId);

        foreach ($countryVaccines as $vaccine) {
            if ($this->gender == $vaccine->targets || $vaccine->targets=='all') {
                $vaccinationDate = $vaccine->getVaccineDate($this->birthday);
                ReminderEmail::schedule($this->db, $this->childId, $vaccine->vaccineScheduleId, $vaccinationDate, $params = ['vaccination_date' => $vaccinationDate]);        
            }      
        }
    }

    public function unsubscribe() {
        ReminderEmail::unschedule($this->db, $this->childId);

        $this->db->update(VAX_DB_PREFIX . "children", [
            'status' => '-1'
        ], ['child_id' => $this->childId]);
    }

    public function getMySchedule() {
        return Vaccines::generateAntigensListByScheduledEmails($this->db, ReminderEmail::getAllEmailsForChild($this->db, $this->childId));
    }

    public function getMyScheduleFromTo($from = null, $to = null) {
        if (!isset($from)) {
            $from = date('Y-m-d 00:00:00');
        }
        if (!isset($to)) {
            $to = date('Y-m-d 00:00:00', strtotime('2 months'));
        }
        return Vaccines::generateAntigensListByScheduledEmails($this->db, ReminderEmail::getAllEmailsForChild($this->db, $this->childId, $from, $to));
    }
}