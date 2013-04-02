<?php

\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('fc_mail_attachments');

\OCP\Util::addscript('fc_mail_attachments', 'utils');
\OCP\Util::addscript('fc_mail_attachments', 'account');
//OCP\Util::addstyle('fc_mail_attachments', 'style');

$user = OCP\User::getUser();

$tmpl = new OCP\Template('fc_mail_attachments', 'settings');

$query = \OCP\DB::prepare('SELECT * FROM `*PREFIX*fc_mail_attachments` WHERE user = ?');
$results = $query->execute(array($user))->fetchAll();

if (sizeof($results)) {
    $conf = $results[0];
    $tmpl->assign('dir', $conf['dir']);
    $tmpl->assign('mail_host', $conf['mail_host']);
    $tmpl->assign('mail_user', $conf['mail_user']);
    $tmpl->assign('mail_password', $conf['mail_password']);
}

return $tmpl->fetchPage();

?>
