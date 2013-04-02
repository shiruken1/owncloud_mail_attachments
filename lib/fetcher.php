<?php

namespace OCA\fc_mail_attachments\lib;

class Fetcher {
    
    private $dir_data;
    private $dir_mail = 'mail_attachments';
    private $IMAP_BATCH_LIMIT = 10;
    
    function __construct() {
        $this->dir_data = \OCP\Config::getSystemValue('datadirectory');
    }
    
    function skipMailbox($data, $mbname) {
        foreach (array("spam", "trash", "[Gmail]/Drafts", "[Gmail]/Starred", "[Gmail]/Important") as $chk) {
            if (stripos($mbname, $chk) !== FALSE) {
                return true;
            }
        }
        return false;
    }
    
    function saveAttachments($imap, $fs, $mboxdir, $uid) {
        $message = $imap->getMessageHeaderFull($uid);
        $attachments = $imap->getAttachments($uid);
        foreach ($attachments as $att) {
            $fdata = $imap->saveAttachment($uid, $att['partNum'], $att['enc']);
            $filename = $message->udate.'-'.$message->subject.'-'.$att['name'];
            if (!$fs->file_exists($mboxdir.$filename)) {
                $fs->file_put_contents($mboxdir.$filename, $fdata);
                $fs->touch($mboxdir.$filename, $message->udate);
            }
        }
    }
    
    function sanitizeFSName($fsname) {
        return str_replace(array(DIRECTORY_SEPARATOR), '-', $fsname);
    }
    
    function run() {
        $query = \OCP\DB::prepare('SELECT * FROM `*PREFIX*fc_mail_attachments`');
        $results = $query->execute()->fetchAll();
        
        foreach ($results as $entry) {
            $fs = new \OC\Files\View('/'. $entry['user']);
            
            $udir = $this->sanitizeFSName($entry["dir"]);
            $path = array('files', $this->dir_mail, $udir);
            for($i = 1; $i < sizeof($path); $i++) {
                $ppath = array();
                for($y = 0; $y <= $i; $y++) {
                    $ppath[] = $path[$y];
                }
                $ppath = implode(DIRECTORY_SEPARATOR, $ppath);
                if (!$fs->is_dir($ppath)) {
                    $fs->mkdir($ppath);
                }
            }
            $maildir = implode(DIRECTORY_SEPARATOR, $path).DIRECTORY_SEPARATOR;
            
            $mstats = array('folders' => array());
            if (isset($entry["stats"]) && $entry["stats"] != "") {
                $mstats = \OCA\fc_mail_attachments\lib\Utils::array_merge_recursive_distinct($mstats, json_decode($entry["stats"], true));
            }
            
            $imap = new \OCA\fc_mail_attachments\lib\imap($entry['mail_host'], $entry['mail_port'], $entry['mail_security'], $entry['mail_user'], $entry['mail_password']);
            try {
                $imap->open();
                
                $mailboxes = $imap->listMailboxes();
                foreach ($mailboxes as $mbname) {
                    if ($this->skipMailbox($entry, $mbname)) { continue; }
                    $imap->open($mbname);
                    
                    $mboxLastMsgNo = $imap->countMessages();
                    if (!$mboxLastMsgNo) { continue; }
                    
                    $mboxdir = $maildir.DIRECTORY_SEPARATOR.$this->sanitizeFSName($mbname).DIRECTORY_SEPARATOR;
                    if (!$fs->is_dir($mboxdir)) {
                        $fs->mkdir($mboxdir);
                    }
                    
                    $mboxLastMsgUid = $imap->getLastMessageUid();
                    
                    $mboxStatus = $imap->status($mbname);
                    $curMboxInfo = array("fcmaVersion" => 0, "lastSeenUid" => -1, "historyUid" => -1, "uidValidity" => -1);
                    if($fs->file_exists($mboxdir.'.info')) {
                        $savedMboxInfo = json_decode($fs->file_get_contents($mboxdir.'.info'), true);
                        $curMboxInfo = \OCA\fc_mail_attachments\lib\Utils::array_merge_recursive_distinct($curMboxInfo, $savedMboxInfo);
                    }
                    if ($mboxStatus->uidvalidity != $curMboxInfo["uidValidity"]) {
                        $curMboxInfo["lastSeenUid"] = $curMboxInfo["historyUid"] = -1;
                        $curMboxInfo["uidValidity"] = $mboxStatus->uidvalidity;
                    }
                    if ($curMboxInfo["lastSeenUid"] == -1) {
                        $curMboxInfo["lastSeenUid"] = $mboxLastMsgUid;                        
                    }
                    if ($curMboxInfo["historyUid"] == -1) {
                        $curMboxInfo["historyUid"] = $mboxLastMsgUid;                        
                    }
                    
                    if ($mboxLastMsgUid != $curMboxInfo["lastSeenUid"]) {
                        $mboxCurMsgNo = $imap->getMessageNumber($curMboxInfo["lastSeenUid"]);
                        for($mboxCurMsgNo, $i = 0; $mboxCurMsgNo <= $mboxLastMsgNo && $i < $this->IMAP_BATCH_LIMIT; $mboxCurMsgNo++, $i++) {
                            $mboxCurMsgUid = $imap->getMessageUid($mboxCurMsgNo);
                            if (!$mboxCurMsgUid) { break; }
                            $this->saveAttachments($imap, $fs, $mboxdir, $mboxCurMsgUid);
                            $curMboxInfo["lastSeenUid"] = $mboxCurMsgUid;   
                        }
                    }
                    
                    $historyMsgNo = $imap->getMessageNumber($curMboxInfo["historyUid"]);
                    if ($historyMsgNo > 1) {
                        for($i = 0; $historyMsgNo > 0 && $i < $this->IMAP_BATCH_LIMIT; $historyMsgNo--, $i++) {
                            $historyMsgUid = $imap->getMessageUid($historyMsgNo);
                            if (!$historyMsgUid) { break; }
                            $this->saveAttachments($imap, $fs, $mboxdir, $historyMsgUid);
                            $curMboxInfo["historyUid"] = $historyMsgUid;   
                        }
                    }

                    $fs->file_put_contents($mboxdir.'.info', json_encode($curMboxInfo));
                }   
                $imap->close();
            } catch(\Exception $e) {
                echo "FCMA ERROR [".$entry["user"]."]: ".$e->getMessage();
            }
        }
    }
}

?>