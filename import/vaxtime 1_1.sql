ALTER TABLE `country_vaccines`
ADD COLUMN `visible` TINYINT(1) NOT NULL DEFAULT 1 AFTER `comments_tx_code`;

UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7589';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7586';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7577';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7536';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7440';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7439';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7393';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7380';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='8260';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7379';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7336';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7335';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7197';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='7195';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='6777';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='6776';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='6502';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='6335';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='6332';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='6281';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='6272';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='6263';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='5061';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='5058';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='5653';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='5656';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='4826';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='4825';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='4842';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='4493';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='4318';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='4168';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='3979';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='3683';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='3636';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='3401';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='3296';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='3106';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='2955';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='2841';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='2773';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='2216';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='1222';
UPDATE `country_vaccines` SET `visible`='0' WHERE `id`='1221';


CREATE TABLE `users`
(
    `id` INT PRIMARY KEY NOT NULL AUTO_INCREMENT,
    `contact_name` VARCHAR(50) NOT NULL,
    `organisation_name` VARCHAR(250) NOT NULL,
    `email` VARCHAR(250) NOT NULL,
    `pwd` VARCHAR(255) NOT NULL,
    `address_1` VARCHAR(255),
    `address_2` VARCHAR(255),
    `city` VARCHAR(255),
    `country_id` INT,
    `is_admin` TINYINT(1) DEFAULT 0,
    `status` TINYINT(1) DEFAULT 1 COMMENT '1: enabled; 0: disabled;'
);
CREATE UNIQUE INDEX `users_email_uindex` ON `users` (`email`);

ALTER TABLE `children` ADD `manual_id` VARCHAR(72) NULL;
ALTER TABLE `children` ADD `user_id` INT NULL COMMENT 'in case a user manages this child. It can be null';

CREATE TABLE import_files
(
    `id` INT PRIMARY KEY AUTO_INCREMENT,
    `user_id` INT NOT NULL,
    `response_email` VARCHAR(255) NOT NULL,
    `default_lang` INT NOT NULL,
    `default_country` INT NOT NULL,
    `has_permission` TINYINT(1) DEFAULT 0 NOT NULL,
    `file` VARCHAR(255) NOT NULL,
    `has_header` TINYINT DEFAULT 0 NOT NULL,
    `upload_time` DATETIME,
    `completion_time` DATETIME,
    `status` TINYINT DEFAULT 0 COMMENT '0: not started; 1: processing; 2: done; 3; done, no errors'
);
CREATE UNIQUE INDEX import_files_id_uindex ON import_files (id);
CREATE UNIQUE INDEX import_files_file_uindex ON import_files (file);
CREATE INDEX import_files_user_id_index ON import_files (user_id);

CREATE TABLE `sessions`
(
    `session_id` VARCHAR(255) PRIMARY KEY,
    `session_value` TEXT,
    `session_lifetime` INT,
    `session_time` INT
);
CREATE UNIQUE INDEX sessions_session_id_uindex ON sessions (session_id);