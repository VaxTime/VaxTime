<?php

abstract class Email {

    const TYPES = [
        'welcome' => 'WelcomeEmail',
        'reminder' => 'ReminderEmail'
    ]; 

    protected $db;
    public $child;
    public $extraData;
    public $templateVars = [];

    public function __construct($db, $child, $extraData) {
        $this->db = $db;
        $this->child = $child;
        $this->extraData = $extraData;
    }

    public abstract function templateFile();
    public abstract function subject();
    public abstract function sender();
    public abstract function populateForUser();

    public function prepare() {
        $this->templateVars['unsubLink'] = VAX_HOME_URL . '/unsubscribe/' . $this->child->childId . '/' . self::userHash($this->child);
        $this->templateVars['child'] = $this->child;
        $this->populateForUser();
    }

    public static function userHash($child) {
        return md5($child->childId . VAX_HASH_SALT);
    }

    public static function isHashCorrect($child, $hash) {
        return $hash == self::userHash($child);
    }

    public static function factory($db, $type, $child, $extraData) {
        foreach (self::TYPES as $prepType => $classname) {
            if ($prepType == $type) {
                require_once __DIR__ . "/{$classname}.class.php";

                $emailItem = new $classname($db, $child, $extraData);

                return $emailItem;
            }
        }
    }
}