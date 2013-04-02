<?php

OCP\User::checkLoggedIn();
OCP\App::checkAppEnabled('fc_mail_attachments');
OCP\JSON::callCheck();

//OCP\Config::setSystemValue( 'somesetting', $_POST['somesetting'] );

$user = OCP\User::getUser();

$query = \OCP\DB::prepare('SELECT * FROM `*PREFIX*fc_mail_attachments` WHERE user = ?');
$results = $query->execute(array($user))->fetchAll();

$cid = null;
if (sizeof($results)) {
    $cid = $results[0]["id"];
}

if (!isset($_GET['mail_security']) || $_GET['mail_security'] == "") {
    $_GET['mail_security'] = 'none';
}

$cdata = array();
$cfields = array("dir", "mail_host", "mail_port", "mail_security", "mail_user", "mail_password");
$errors = array();
foreach ($cfields as $cfield) {
    if (isset($_GET[$cfield]) && $_GET[$cfield] != "") {
        $cdata[$cfield] = $_GET[$cfield];
    } else {
        $errors[] = "Field '".$cfield."' is not set";
    }
}

$RESPONSE = array("success" => false, "errors" => array());

if (sizeof($errors)) {
    $RESPONSE["errors"] = $errors;
    die(json_encode($RESPONSE));
}

$imap = new \OCA\fc_mail_attachments\lib\imap($cdata['mail_host'], $cdata['mail_port'], $cdata['mail_security'], $cdata['mail_user'], $cdata['mail_password']);
try {
    $imap->open();
} catch(\Exception $e) { array_push($errors, $e->getMessage()); }

if (sizeof($errors)) {
    $RESPONSE["errors"] = $errors;
    die(json_encode($RESPONSE));
}

if ($cid) {
    $query = \OCP\DB::prepare('UPDATE `*PREFIX*fc_mail_attachments` SET dir = ?, mail_host = ?, mail_port = ?, mail_security = ?, mail_user = ?, mail_password = ? WHERE user = ?');
    $results = $query->execute(array($cdata['dir'], $cdata['mail_host'], $cdata['mail_port'], $cdata['mail_security'], $cdata['mail_user'], $cdata['mail_password'], $user));
} else {
    $query = \OCP\DB::prepare('INSERT INTO `*PREFIX*fc_mail_attachments` (user, dir, mail_host, mail_port, mail_security, mail_user, mail_password) VALUES (?, ?, ?, ?, ?, ?, ?)');
    $results = $query->execute(array($user, $cdata['dir'], $cdata['mail_host'], $cdata['mail_port'], $cdata['mail_security'], $cdata['mail_user'], $cdata['mail_password']));
}

$RESPONSE["success"] = true;
die(json_encode($RESPONSE));

?>
