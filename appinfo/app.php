<?php

//$job = new \OCA\fc_mail_attachments\Job();
//OC::$CLASSPATH['OC_FCMA_Job'] = $job;

\OCP\App::registerPersonal( 'fc_mail_attachments', 'settings' );
\OCP\BackgroundJob::addRegularTask('\OCA\fc_mail_attachments\Job', 'run');

?>
