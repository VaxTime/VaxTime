<?php

require_once VAX_SRC . '/emails/ScheduledEmail.class.php';
require_once VAX_SRC . '/models/Vaccines.class.php';

class ReminderEmail extends ScheduledEmail {
    public function templateFile() {
        return 'email/reminder-email.twig';
    }

    public function subject() {
        return 'Vaccination reminder for ' . $this->child->firstname . ' - ' . VAX_NAME;
    }

    public function sender() {
        return VAX_EMAIL_ACCOUNT_REMINDER;
    }

    public function populateForUser() {
        $this->templateVars['vaccination'] = Vaccines::getByScheduledId($this->db, $this->vaccineScheduleId);
        $this->templateVars['vaccineSchedules'] = $this->child->getMyScheduleFromTo(/*from today, to 2 months*/);

        $vaccinationHumanMoment = $this->templateVars['vaccination']->getHumanInterval();

        $this->templateVars['vaccinationHumanMoment'] = $vaccinationHumanMoment=='birth'? 'At birth' : "{$vaccinationHumanMoment} after birth";
    }

    public static function getPendingEmails($db, $maxBatch = self::MAX_BATCH) {
        $pendingEmails = [];

        $rows = $db->fetchAll("SELECT se.*, l.short_code AS lang_code FROM " . VAX_DB_PREFIX . "scheduled_emails se LEFT JOIN " . VAX_DB_PREFIX . "children c ON (se.child_id = c.child_id) LEFT JOIN " . VAX_DB_PREFIX . "languages l ON (c.lang_id = l.lang_id) WHERE se.status = 0 AND sending_time < NOW() ORDER BY sending_time ASC LIMIT {$maxBatch}");

        foreach ($rows as $row) {
            $pendingEmails[] = new ReminderEmail($db, $row);
        }

        return $pendingEmails;
    }

    public static function getAllEmailsForChild($db, $childId, $fromDate = null, $toDate = null) {
        $allEmails = [];

        if (isset($fromDate) && isset($toDate)) {
            $rows = $db->fetchAll("SELECT se.*, l.short_code AS lang_code FROM " . VAX_DB_PREFIX . "scheduled_emails se LEFT JOIN " . VAX_DB_PREFIX . "children c ON (se.child_id = c.child_id) LEFT JOIN " . VAX_DB_PREFIX . "languages l ON (c.lang_id = l.lang_id) WHERE se.child_id = ? AND sending_time BETWEEN ? AND ? ORDER BY sending_time ASC", [$childId, $fromDate, $toDate]);
        } else {
            $rows = $db->fetchAll("SELECT se.*, l.short_code AS lang_code FROM " . VAX_DB_PREFIX . "scheduled_emails se LEFT JOIN " . VAX_DB_PREFIX . "children c ON (se.child_id = c.child_id) LEFT JOIN " . VAX_DB_PREFIX . "languages l ON (c.lang_id = l.lang_id) WHERE se.child_id = ? ORDER BY sending_time ASC", [$childId]);
        }


        foreach ($rows as $row) {
            $allEmails[] = new ReminderEmail($db, $row);
        }

        return $allEmails;
    }
}