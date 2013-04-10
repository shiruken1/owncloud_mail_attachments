<?php

namespace OCA\fc_mail_attachments\lib;

class Imap 
{ 
	private $host; 
    private $port;
    private $mode = 'imap';
    private $security;
	private $username; 
	private $password;
	private $stream;

    function __construct($host, $port = 25, $security = 'none', $username, $password) { 
        $this->host = $host;
        $this->port = $port;
        $this->security = $security;
        $this->username = $username;
        $this->password = $password;
    }
    
    function open($mailbox = '') { 
        if ($this->stream) {
            $this->close();
        }
        $this->target = "{".$this->host.":".$this->port."/".$this->mode.($this->security != "none" ? "/".$this->security."/novalidate-cert" : "")."/readonly}"; 
        $this->stream = imap_open($this->target.$mailbox, $this->username,$this->password); 
        if (!$this->stream) {
            throw new \Exception(implode(", ", imap_errors()));
        }
    }

    function close() {
        if ($this->stream) {
            imap_close($this->stream);
        }
    }

	function listMailboxes() {
		$mailboxes = imap_list($this->stream, $this->target, "*");
		foreach ($mailboxes as &$folder) {
            $folder = str_replace($this->target, "", imap_utf7_decode($folder));
        }
        return $mailboxes;
    }

    function countMessages() {
        return imap_num_msg($this->stream);
    }

    function getMessageUid($msgno) {
        return imap_uid($this->stream, $msgno);
    }

    function getMessageNumber($uid) {
        return imap_msgno($this->stream, $uid);
    }
    
    function getLastMessageUid() {
        return $this->getMessageUid($this->countMessages());
    }

    function getMessageHeader($uid) {
        return imap_header($this->stream, imap_msgno($this->stream, $uid));
    }

    function getMessageHeaderFull($uid) {
        return imap_headerinfo($this->stream, imap_msgno($this->stream, $uid));
    }

    function listMessages() {
        return imap_headers($this->stream);
    }

    function status($mailbox) {
        return imap_status($this->stream, $this->target.$mailbox, SA_ALL);
    }
    
	function getBody($uid) {
		$body = $this->get_part($this->stream, $uid, "TEXT/HTML");
		// if HTML body is empty, try getting text body
		if ($body == "") {
			$body = $this->get_part($uid, "TEXT/PLAIN");
		}
		return $body;
	}

	function get_part($uid, $mimetype, $structure = false, $partNumber = false) {
		if (!$structure) {
			$structure = imap_fetchstructure($this->stream, $uid, FT_UID);
		}
		if (is_object($structure)) {
			if ($mimetype == $this->get_mime_type($structure)) {
				if (!$partNumber) {
					$partNumber = 1;
				}
				$text = imap_fetchbody($this->stream, $uid, $partNumber, FT_UID);
				switch ($structure->encoding) {
					case 3: return imap_base64($text);
					case 4: return imap_qprint($text);
					default: return $text;
				}
			}

			// multipart 
			if ($structure->type == 1) {
				foreach ($structure->parts as $index => $subStruct) {
					$prefix = "";
					if ($partNumber) {
						$prefix = $partNumber . ".";
					}
					$data = $this->get_part($uid, $mimetype, $subStruct, $prefix . ($index + 1));
					if ($data) {
						return $data;
					}
				}
			}
		}
		return false;
	}

	function get_mime_type($structure) {
		$primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

		if ($structure->subtype) {
			return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
		}
		return "TEXT/PLAIN";
	}

	function getAttachments($uid) {
		$mailStruct = imap_fetchstructure($this->stream, $uid, FT_UID);
		$attachments = $this->_getAttachments($uid, $mailStruct, "");
		return $attachments;
	}

	function _getAttachments($uid, $part, $partNum) {
		$attachments = array();

		if (isset($part->parts)) {
			foreach ($part->parts as $key => $subpart) {
				if($partNum != "") {
					$newPartNum = $partNum . "." . ($key + 1);
				}
				else {
					$newPartNum = ($key+1);
				}
				$result = $this->_getAttachments($uid, $subpart, $newPartNum);
				if (count($result) != 0) {
					array_push($attachments, $result);
				}
			}
		} else if (isset($part->disposition)) {
            if (strtoupper($part->disposition) == "ATTACHMENT") {
				$partStruct = imap_bodystruct($this->stream, imap_msgno($this->stream, $uid), $partNum);
				$attachmentDetails = array(
						"name"    => $part->dparameters[0]->value,
						"partNum" => $partNum,
						"enc"     => $partStruct->encoding
						);
				return $attachmentDetails;
			}
		}

		return $attachments;
	}

	function saveAttachment($uid, $partNum, $encoding) {
		$partStruct = imap_bodystruct($this->stream, imap_msgno($this->stream, $uid), $partNum);

		$message = imap_fetchbody($this->stream, $uid, $partNum, FT_UID);

		switch ($encoding) {
			case 0:
			case 1:
				$message = imap_8bit($message);
				break;
			case 2:
				$message = imap_binary($message);
				break;
			case 3:
				$message = imap_base64($message);
				break;
			case 4:
				$message = quoted_printable_decode($message);
				break;
		}

		return $message;
	}
}

?>
