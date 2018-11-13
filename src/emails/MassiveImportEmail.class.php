<?php

require_once VAX_SRC . '/emails/Email.class.php';
require_once VAX_SRC . '/models/Vaccines.class.php';

class MassiveImportEmail extends Email {

    public function templateFile() {
        return 'email/massive-import-email.twig';
    }

    public function subject() {
        return 'Massive import completed - ' . VAX_NAME;
    }

    public function sender() {
        return VAX_EMAIL_ACCOUNT_HELLO;
    }

    public function populateForUser() {}
}