<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailProvider {
    private $app;
    public static $mailer = null;

    public function __construct($app) {
        $this->app = $app;

        if (self::$mailer == null) {
            self::$mailer = new PHPMailer(true);

            self::$mailer->CharSet = "UTF-8";
            self::$mailer->IsSMTP();
            self::$mailer->Port = VAX_EMAIL_PORT;

            self::$mailer->SMTPDebug = false;

            self::$mailer->Host = VAX_EMAIL_HOST;
            self::$mailer->Hostname = VAX_EMAIL_HOST;
            if (VAX_EMAIL_SMTP_AUTH) {
                self::$mailer->Username = VAX_EMAIL_USER;
                self::$mailer->Password = VAX_EMAIL_PASSWORD;
                self::$mailer->SMTPAuth = VAX_EMAIL_SMTP_AUTH;
                self::$mailer->SMTPSecure = VAX_EMAIL_SMTP_SECURE;
            }
            self::$mailer->isHTML(true);
        }

    }

    public function sendEmail($email) {
        $email->prepare();

        self::$mailer->Subject = $email->subject();
        self::$mailer->Body = $this->app['twig']->render($email->templateFile(), $email->templateVars);
        self::$mailer->setFrom($email->sender(), VAX_NAME);
        self::$mailer->addReplyTo($email->sender());
        self::$mailer->addAddress($email->child->email, $email->child->firstname);

        $status = self::$mailer->send();

        self::$mailer->ClearAddresses();
        self::$mailer->ClearCCs();
        self::$mailer->ClearBCCs();
        self::$mailer->clearReplyTos();

        return $status;
    }
}