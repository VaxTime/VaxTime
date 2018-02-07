<?php
require_once VAX_SRC . '/models/Country.class.php';

class WelcomeEmail extends Email {

    public function templateFile() {
        return 'email/vaccination-email.twig';
    }

    public function subject() {
        return 'Vaccination schedule for ' . $this->child->firstname . ' - ' . VAX_NAME;
    }

    public function sender() {
        return VAX_EMAIL_ACCOUNT_HELLO;
    }

    public function populateForUser() {
        $this->templateVars['country'] = Country::getById($this->db, $this->child->countryId);

        $this->templateVars['vaccineSchedules'] = $this->child->getMySchedule();
    }
}