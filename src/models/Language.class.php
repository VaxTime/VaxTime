<?php

class Language {
    public $langId;
    public $shortCode;
    public $name;
    public $direction;

    private static $allLanguages = null;

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
        self::$allLanguages = self::createLanguagesArray($db->fetchAll("SELECT * FROM " . VAX_DB_PREFIX . "languages ORDER BY short_code ASC"));
        return self::$allLanguages;
    }

    public static function getByIsoCode($db, $code) {
        return self::createLanguagesArray($db->fetchAll("SELECT * FROM " . VAX_DB_PREFIX . "languages WHERE short_code = '{$code}' LIMIT 1"))[0];
    }

    public static function browserLanguage($db) {
        if (empty(self::$allLanguages)) {
            self::getAll($db);
        }

        $languages = explode(',', empty($_SERVER['HTTP_ACCEPT_LANGUAGE'])? 'en' : $_SERVER['HTTP_ACCEPT_LANGUAGE']);
        $cleanedLangs = [];

        foreach($languages as $lang)
        {
            $cleanedLang = explode("-", $lang)[0];
            $cleanedLangs[] = $cleanedLang;
        }

        foreach(self::$allLanguages as $supportedLang) {
            if(in_array($supportedLang, $cleanedLangs))
            {
                return $supportedLang;
            }
        }

        return 'en';
    }

}