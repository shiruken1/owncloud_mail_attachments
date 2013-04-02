<?php

\OCP\User::checkLoggedIn();
\OCP\App::checkAppEnabled('fc_mail_attachments');

$tpl = new OCP\Template("fc_mail_attachments", "main", "user");
$tpl->printPage();

?>