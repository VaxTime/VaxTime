<?php
require_once VAX_SRC . '/models/Child.class.php';

abstract class ScheduledEmail extends Email {
    const SEND_TIME_AGO = 172800;
    const MAX_BATCH = 50;

    const SENT = 1;
    const ERRORED = -1;

    public $scheduledEmailId;
    public $child;
    public $childId;
    public $langCode;
    public $vaccineScheduleId;
    public $sendingTime;
    public $sentTime;
    public $params = [];
    public $status;
    public $db;

    public function __construct($db, $row) {
        $this->db = $db;
        $this->scheduledEmailId = $row['scheduled_email_id'];
        $this->childId = $row['child_id'];
        $this->langCode = $row['lang_code'];
        $this->child = Child::getById($db, $this->childId);
        $this->vaccineScheduleId = $row['vaccine_schedule_id'];
        $this->sendingTime = $row['sending_time'];
        $this->sentTime = $row['sent_time'];
        $this->params = json_decode($row['params'], true);
        $this->status = $row['status'];
        return $this;
    }

    public static function schedule($db, $childId, $vaccineScheduleId, $sendingTime, $params = []) {
        $sendingTime = strtotime($sendingTime) - self::SEND_TIME_AGO;

        if ($sendingTime < time()) {
            $status = 1;
        } else {
            $status = 0;      
        }

        $db->insert(VAX_DB_PREFIX . "scheduled_emails", [
            'child_id' => $childId,
            'vaccine_schedule_id' => $vaccineScheduleId,
            'sending_time' => date('Y-m-d H:i:s', $sendingTime),
            'params' => json_encode($params),
            'status' => $status
        ]);
    }

    public static function unschedule($db, $childId) {
        $db->update(VAX_DB_PREFIX . "scheduled_emails", [
            'status' => 2
        ],
        [
            'child_id' => $childId,
            'status' => 0
        ]);
    }

    public function markAs($db, $status) {
        $db->update(VAX_DB_PREFIX . "scheduled_emails", [
            'status' => $status,
            'sent_time' => date('Y-m-d H:i:s')
        ],
        [
            'scheduled_email_id' => $this->scheduledEmailId
        ]);
    }
}