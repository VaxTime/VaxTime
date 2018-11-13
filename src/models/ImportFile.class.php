<?php

use League\Csv\Reader;
use League\Csv\Writer;

class ImportFile
{
    private $id;
    private $userId;
    private $filePath;
    private $outputFilePath;
    private $hasHeader;
    private $uploadTime;
    private $defaultLang;
    private $defaultCountry;
    private $hasPermission;
    private $completionTime;
    private $status;


    private $reader;
    private $rows = [];
    private $incorrectRows = [];
    private $repeatedRows = [];
    private $correctRows = [];
    private $rowHashes = [];

    private $tempEmail;
    private $tempDate;
    private $tempFirstName;
    private $tempCountry;
    private $tempLang;
    private $tempUniqueID;

    private $app;
    private $db;

    public function __construct($rowData)
    {
        $this->filePath = VAX_UPLOADED_CSV . '/' . $rowData['file'];
        $this->outputFilePath = VAX_UPLOADED_CSV . '/output_' . $rowData['file'];
        $this->reader = Reader::createFromPath($this->filePath, 'r');
        $this->id = $rowData['id'];
        $this->userId = $rowData['user_id'];
        $this->defaultLang = $rowData['default_lang'];
        $this->defaultCountry = $rowData['default_country'];
        $this->hasPermission = intval($rowData['has_permission']);
        $this->hasHeader = intval($rowData['has_header']);
        $this->uploadTime = $rowData['upload_time'];
        $this->completionTime = $rowData['completion_time'];
        $this->status = intval($rowData['status']);
    }

    public static function create($app, $userId, $tempFile, $hasHeader, $defaultLang, $defaultCountry, $hasPermission) {
        $filePath = self::saveFile($tempFile, $userId);
        if ($filePath !== false) {

            $db = $app['db'];
            $now = $db->fetchColumn("SELECT NOW()");
            $importData = [
                'user_id' => $userId,
                'default_lang' => $defaultLang,
                'default_country' => $defaultCountry,
                'has_permission' => $hasPermission? 1 : 0,
                'has_header' => $hasHeader? 1 : 0,
                'upload_time' => $now,
                'file' => $filePath
            ];



            if ($db->insert(VAX_DB_PREFIX."import_files", $importData)) {
                $importData = $db->fetchAssoc("SELECT * FROM " . VAX_DB_PREFIX . "import_files WHERE id = ?", [$db->lastInsertId()]);
                $importFile = new ImportFile($importData);
                $importFile->app = $app;
                $importFile->db = $db;

                return $importFile;
            }
        }

        return null;
    }

    private static function saveFile($tempFile, $userId) {
        if ($tempFile['type'] != 'text/csv') {
            return false;
        }

        if (!file_exists($tempFile['tmp_name'])) {
            return false;
        }

        $pathName = $userId . '_' . sha1(time() . VAX_HASH_SALT . $userId) . '.csv';
        if (!move_uploaded_file($tempFile['tmp_name'], VAX_UPLOADED_CSV . '/' . $pathName)) {
            return false;
        }
        return $pathName;
    }

    public function process() {
        $this->reader->setHeaderOffset($this->hasHeader == 1? 0 : null);

        $this->rows = $this->reader->getRecords();
        foreach ($this->rows as $offset => $row) {
            $this->prepareTempFields($row);

            if ($reason = $this->isInvalid($row)) {
                $this->incorrectRows[$offset] = $reason;
                continue;
            }
            if ($repeatedRow = $this->isRepeated($offset)) {
                $this->repeatedRows[$offset] = $repeatedRow;
                continue;
            }
            if ($this->isAlreadyInDb()) {
                $this->repeatedRows[$offset] = 'db';
                continue;
            }

            $this->addChildInDb();
            $this->correctRows[$offset] = true;
        }

        $this->generateNewCsvAndSendBack();
    }

    private function prepareTempFields($row) {
        $this->tempEmail = null;
        $this->tempDate = null;
        $this->tempFirstName = null;
        $this->tempCountry = null;
        $this->tempLang = null;
        $this->tempUniqueID = '';


        $numCols = count($row);

        $row = array_values($row);

        if ($numCols > 0) {
            $this->tempEmail = trim($row[0]);
        }

        if ($numCols > 1) {
            $this->tempDate = trim($row[1]);
        }

        if ($numCols > 2) {
            $this->tempFirstName = trim($row[2]);
        }

        if ($numCols > 3) {
            $supposedCountry = trim($row[3]);
            if (empty($supposedCountry)) {
                $supposedCountry = $this->defaultCountry;
            }
            if ($country = Country::getByIdOrIso($this->db, $supposedCountry)) {
                $this->tempCountry = $country;
            }
        }

        if ($numCols > 4) {
            $supposedLang = trim($row[4]);
            if (empty($supposedLang)) {
                $supposedLang = $this->defaultLang;
            }
            if ($lang = Language::getByIdOrIso($this->db, $supposedLang)) {
                $this->tempLang = $lang;
            }
        }

        if ($numCols > 5) {
            $this->tempUniqueID = trim($row[5]);
        }

    }

    private function isInvalid($row) {
        $numCols = count($row);
        if ($numCols < 3) {
            return 'Missing columns';
        }
        if (!filter_var($this->tempEmail, FILTER_VALIDATE_EMAIL)) {
            return 'Invalid email';
        }

        if (0 === preg_match("/^(19|20)\d{2}\-(0[1-9]|1[0-2])\-(0[1-9]|1\d|2\d|3[01])$/", $this->tempDate)) {
            return 'Invalid date';
        }

        if (empty($this->tempFirstName)) {
            return 'Missing name';
        }

        if (empty($this->tempCountry)) {
            return 'Wrong country code';
        }

        if (empty($this->tempLang)) {
            return 'Wrong language code';
        }

        return false;
    }

    private function isRepeated($offset) {
        $hash = sha1(strtolower($this->tempEmail . '##' . $this->tempDate . '##' . $this->tempFirstName . '##' . $this->tempUniqueID));
        if (array_key_exists($hash, $this->rowHashes)) {
            return $this->rowHashes[$hash];
        }
        $this->rowHashes[$hash] = $offset;
        return false;
    }

    private function isAlreadyInDb() {
        $results = $this->db->fetchAll("SELECT * FROM " . VAX_DB_PREFIX . "children WHERE email = ? AND first_name = ? AND birthday = ?", [$this->tempEmail, $this->tempFirstName, $this->tempDate]);

        return count($results) > 0;
    }

    private function addChildInDb() {
        $child = new Child(
            $this->db, $this->tempEmail, $this->tempFirstName, $this->tempDate, 'm', $this->tempLang->langId, $this->tempCountry->id, null, true, null, $this->userId);
        $child->subscribe();

        $emailProvider = new EmailProvider($this->app);
        $welcomeEmail = Email::factory($this->db, 'welcome', $child, []);
        $emailProvider->sendEmail($welcomeEmail);
    }

    private function generateNewCsvAndSendBack() {
        $arrayRows = iterator_to_array($this->rows);
        foreach ($arrayRows as $offset => &$row) {
            if (isset($this->correctRows[$offset])) {
                $row['correct'] = "Y";
                $row['reason'] = "";
            } else {
                $row['correct'] = "N";
                if (isset($this->incorrectRows[$offset])) {
                    $row['reason'] = $this->incorrectRows[$offset];
                } else if(isset($this->repeatedRows[$offset])) {
                    if ($this->repeatedRows[$offset] == 'db') {
                        $row['reason'] = 'This person is already in the database';
                    } else {
                        $row['reason'] = 'This is a duplicate of row #' . ($this->repeatedRows[$offset] + 1);
                    }
                }
            }
        }

        $writer = Writer::createFromPath($this->outputFilePath, 'w+');
        if ($this->hasHeader) {
            $headers = [];
            foreach($arrayRows[1] as $header => $value) {
                $headers[$header] = $header;
            }

            $headers['correct'] = 'correct';
            $headers['reason'] = 'reason';
            array_unshift($arrayRows, $headers);
        }
        $writer->insertAll($arrayRows);
    }

    public function getOutputFilePath() {
        return $this->outputFilePath;
    }
}