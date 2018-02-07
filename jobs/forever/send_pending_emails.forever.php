<?php
require __DIR__ . '/forevercron.class.php';

require_once VAX_SRC . '/emails/Email.class.php';
require_once VAX_SRC . '/emails/ScheduledEmail.class.php';
require_once VAX_SRC . '/emails/ReminderEmail.class.php';
require_once VAX_SRC . '/utils/EmailProvider.class.php';
require_once VAX_SRC . '/models/Child.class.php';


ini_set('memory_limit', '256M');

class SendPendingEmails extends ForeverCron {
  const MAX_BATCH = 20000;

  const WAIT_MULTIPLIER = 2;
  const WAIT_MIN = 5;
  const WAIT_MAX = 3600;

  private $emailsToSend;
  private $waitFor;

  public function __construct() {
    parent::__construct();

    $this->emailsToSend = 0;
    $this->waitFor = self::WAIT_MIN;
  }

  public function run() {
    while (true) {
      $this->pingOrDie();
      do {
        $pendingEmails = ReminderEmail::getPendingEmails($this->db, self::MAX_BATCH);
        $this->emailsToSend = count($pendingEmails);
        $currentEmail = 1;
        $this->log("Sending " . $this->emailsToSend . " emails");

        foreach ($pendingEmails as $email) {
          try {
            Translation::instance()->resetLanguage($email->langCode);

            $emailProvider = new EmailProvider($this->app);
            $status = $emailProvider->sendEmail($email);
            $this->log("\tEmail $currentEmail/" . $this->emailsToSend . " #" . $email->scheduledEmailId . " sent: " . ($status == 1? 'yes' : 'no'));
            if ($status == 1) {
              $email->markAs($this->db, ScheduledEmail::SENT);
            } else {
              $email->markAs($this->db, ScheduledEmail::ERRORED);
            }
          } catch (Exception $e) {
            if ($e->getMessage() == "The child no longer exists") {
              $this->log("\tError: The child " . $email->childId . " no longer exists");
              $this->db->update(VAX_DB_PREFIX . "scheduled_emails", [
                'status' => -2
              ], [
                'child_id' => $email->childId, 
                'status' => 0 
              ]);
            } else {
              $this->log("\tError: " . $e->getMessage());
            }
          }
          $currentEmail++;
        }
        if ($this->emailsToSend > 0) {
          $this->waitFor = self::WAIT_MIN;
        } else {
          unset($pendingEmails);
          sleep(self::WAIT_MIN);
        }
      }
      while ($this->emailsToSend > 0);

      $this->waitFor = min($this->waitFor * self::WAIT_MULTIPLIER, self::WAIT_MAX);

      $this->log("-- Waiting for " . $this->waitFor . "s");
      sleep($this->waitFor);
    }
  }
}

$forever = new SendPendingEmails;
$forever->run();
