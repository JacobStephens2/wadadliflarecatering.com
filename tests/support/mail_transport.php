<?php
namespace PHPMailer\PHPMailer;

// External SMTP boundary: capture the outgoing notification without delivering it.
class PHPMailer {
    public $Subject;
    public $Body;
    public $AltBody;
    public $ErrorInfo = '';
    public function __construct($exceptions = false) {}
    public function __call($name, $arguments) {}
    public function __set($name, $value) {}
    public function send() {
        file_put_contents(getenv('TEST_MAIL_FILE'), json_encode([
            'subject' => $this->Subject, 'body' => $this->Body,
        ]) . "\n", FILE_APPEND);
        return true;
    }
}
