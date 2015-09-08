<form id="fc_mail_attachments" action="#" method="post">
  <fieldset class="personalblock">  
    <strong>Mail Attachments</strong><br />
    <input type="text" name="mail_host" id="mail_host" value="<?php p($_['mail_host']); ?>" placeholder="<?php p($l->t('Mail host'));?>" />
    <input type="text" name="mail_port" id="mail_port" value="<?php p($_['mail_port']); ?>" placeholder="<?php p($l->t('Mail port'));?>" />
    <label for="mail_security"><?php p($l->t('Mail security'));?></label>
    <select name="mail_security" id="mail_security" value="<?php p($_['mail_security']); ?>">
      <option value="" <?php if($_['mail_security'] == "") { echo "selected"; } ?>><?php p($l->t('Choose mail security'));?></option>
      <option value="none" <?php if($_['mail_security'] == "none") { echo "selected"; } ?>><?php p($l->t('None'));?></option>
      <option value="ssl" <?php if($_['mail_security'] == "ssl") { echo "selected"; } ?>><?php p($l->t('SSL (Secure Socket Layer)'));?></option>
      <option value="tls" <?php if($_['mail_security'] == "tls") { echo "selected"; } ?>><?php p($l->t('TLS'));?></option>
    </select>
    <br />
    <input type="text" name="dir" id="dir" value="<?php p($_['dir']); ?>" placeholder="<?php p($l->t('Account name / directory name'));?>" />
    <input type="text" name="mail_user" id="mail_user" value="<?php p($_['mail_user']); ?>" placeholder="<?php p($l->t('Mail user'));?>" />
    <input type="password" name="mail_password" id="mail_password" value="<?php p($_['mail_password']); ?>" placeholder="<?php p($l->t('Mail password'));?>" />
    <br />
    <button id="fcma_submit_btn">Set</button>
    <span class="msg"></span>
  </fieldset>
</form>
