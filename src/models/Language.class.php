<?php

class Language {
    public $langId;
    public $shortCode;
    public $name;
    public $direction;

    public function __construct($rawData) {
        $this->langId = $rawData['lang_id'];
        $this->shortCode = $rawData['short_code'];
        $this->name = $rawData['name'];
        $this->direction = $rawData['direction'];
        return $this;
    }


    private static function createLanguagesArray($array = []) {
        $items = [];
        foreach ($array as $rawData) {
            $items[] = new Language($rawData);
        }
        return $items;
    }

    public static function getAll($db) {
        return self::createLanguagesArray($db->fetchAll("SELECT * FROM " . VAX_DB_PREFIX . "languages ORDER BY short_code ASC"));
    }

    public static function getByIsoCode($db, $code) {
        return self::createLanguagesArray($db->fetchAll("SELECT * FROM " . VAX_DB_PREFIX . "languages WHERE short_code = '{$code}' LIMIT 1"))[0];
    }
}