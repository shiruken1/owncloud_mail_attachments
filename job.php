<?php

namespace OCA\fc_mail_attachments;

class Job {
    
    static public function run() {

        function __run() {
            $fetcher = new \OCA\fc_mail_attachments\lib\Fetcher();
            $fetcher->run();
        }

        __run();
    }
    
}

?>
