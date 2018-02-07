<?php

class Country {
    public $id;
    public $countryCode;
    public $countryNameTxCode;
    public $originalCountryName;
    public $iso3;
    public $numericCode;

    public function __construct($rawData) {
        $this->id = $rawData['id'];
        $this->originalCountryName = $rawData['country_name'];
        $this->countryCode = $rawData['country_code'];
        $this->iso3 = $rawData['iso3'];
        $this->numericCode = $rawData['numeric_code'];
        $this->countryNameTxCode = 'country_name_' . $this->countryCode;
        return $this;
    }

    public function countryName() {
        try {
            return Translation::instance()->show($this->countryNameTxCode);
        } catch (Exception $e) {
            return $this->originalCountryName;
        }
    }

    private static function createCountriesArray($array = []) {
        $items = [];
        foreach ($array as $rawData) {
            $items[] = new Country($rawData);
        }
        return $items;
    }

    public static function getAll($db) {
        return self::createCountriesArray($db->fetchAll("SELECT * FROM " . VAX_DB_PREFIX . "countries ORDER BY country_name ASC"));
    }

    public static function getAllWithVaccineSchedules($db) {
        return self::createCountriesArray($db->fetchAll("SELECT * FROM " . VAX_DB_PREFIX . "countries WHERE id IN (SELECT DISTINCT country_id FROM vax_country_vaccines) ORDER BY country_name ASC"));
    }

    public static function getById($db, $countryId) {
        return new Country($db->fetchAssoc("SELECT * FROM " . VAX_DB_PREFIX . "countries WHERE id = ?", [$countryId]));
    }

    public static function getByIp($db, $ip){
        if (in_array($ip, ['::1', '127.0.0.1'])) {
            return  new Country($db->fetchAssoc("SELECT c.* FROM " . VAX_DB_PREFIX . "countries c LEFT JOIN " . VAX_DB_PREFIX . "ip2nation i2n ON (c.country_code LIKE i2n.country) WHERE c.id = 209"));
        }
        return  new Country($db->fetchAssoc("SELECT c.* FROM " . VAX_DB_PREFIX . "countries c LEFT JOIN " . VAX_DB_PREFIX . "ip2nation i2n ON (c.country_code LIKE i2n.country) WHERE i2n.ip < INET_ATON(?) ORDER BY i2n.ip DESC LIMIT 1", [$ip]));
    }
}