<?php

class User
{
    public $id;
    public $contactName;
    public $organisationName;
    public $email;
    public $pwd;
    public $address1;
    public $address2;
    public $city;
    public $countryId;
    public $country;
    private $isAdmin;
    private $status;

    private $hasAllFields = false;
    private $db;

    public function __construct($rawData, $showPassword = false, $db = null)
    {
        $this->db = $db;
        if (!$showPassword) {
            unset($rawData['pwd']);
        } else {
            $this->pwd = $rawData['pwd'];
        }

        $this->hasAllFields = $showPassword;

        $this->id = $rawData['id'] ?? '';
        $this->contactName = $rawData['contactName'] ?? '';
        $this->organisationName = $rawData['organisationName'] ?? '';
        $this->email = $rawData['email'] ?? '';
        $this->address1 = $rawData['address1'] ?? '';
        $this->address2 = $rawData['address2'] ?? '';
        $this->city = $rawData['city'] ?? '';
        $this->countryId = $rawData['countryId'] ?? '';
        $this->isAdmin = intval($rawData['isAdmin']) ?? 0;
        $this->status = intval($rawData['status']) ?? 0;
    }

    public static function getById($db, $id)
    {
        $rawData = $db->fetchAssoc("SELECT * FROM " . VAX_DB_PREFIX . "users WHERE id = ?", [$id]);
        if (empty($rawData)) {
            return false;
        } else {
            return new User($rawData);
        }
    }

    public static function getByEmail($db, $email)
    {
        $rawData = $db->fetchAssoc("SELECT * FROM " . VAX_DB_PREFIX . "users WHERE email = ?", [$email]);
        if (empty($rawData)) {
            return false;
        } else {
            return new User($rawData);
        }
    }

    public static function authorise($db, $email, $pwd)
    {
        $rawData = $db->fetchAssoc("SELECT * FROM " . VAX_DB_PREFIX . "users WHERE email = ? AND pwd = PASSWORD(?)", [$email, $pwd]);
        if (empty($rawData)) {
            return false;
        } else {
            return new User($rawData);
        }
    }

    public function hasAllFields()
    {
        return $this->hasAllFields;
    }

    public function isAdmin() {
        return $this->isAdmin == 1;
    }

    public function isEnabled() {
        return $this->status == 1;
    }
}