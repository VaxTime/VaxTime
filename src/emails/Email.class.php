<?php

abstract class Email {

    const TYPES = [
        'welcome' => 'WelcomeEmail',
        'reminder' => 'ReminderEmail',
        'massiveimport' => 'MassiveImportEmail'
    ];

    protected $db;
    public $child;
    public $user;
    public $extraData;
    public $templateVars = [];
    public $attachments = [];

    public function __construct($db, $childOrUser = null, $extraData = []) {
        $this->db = $db;
        if (!empty($childOrUser)) {
            if (get_class($childOrUser) == "User") {
                $this->user = $childOrUser;
            } elseif (get_class($childOrUser) == "Child") {
                $this->child = $childOrUser;
            }
        }
        $this->extraData = $extraData;
    }

    abstract public function templateFile();
    abstract public function subject();
    abstract public function sender();
    abstract public function populateForUser();

    public function prepare() {
        if (!empty($this->child)) {
            $this->templateVars['unsubLink'] = VAX_HOME_URL . '/unsubscribe/' . $this->child->childId . '/' . self::userHash($this->child);
            $this->templateVars['child'] = $this->child;
        }
        $this->populateForUser();
    }

    public function addAttachment($path, $name = '') {
        $this->attachments[$path] = $name;
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

    public function getAddresseeEmail() {
        return !empty($this->child) ? $this->child->email : $this->user->email;
    }

    public function getAddresseeName() {
        return !empty($this->child) ? $this->child->firstname : $this->user->contactName;
    }
}