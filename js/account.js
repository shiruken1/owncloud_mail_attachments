$(document).ready(function() {
    var $form = $("#fc_mail_attachments");
    $('#fcma_submit_btn', $form).click(function() {
	var formvals = $form.fcSerializeObject();
	$.ajax({
	    dataType: "json",
	    url:  OC.filePath('fc_mail_attachments', 'ajax', 'account.php') + '?action=save',
	    data: formvals,
	    success: function(res) {
		var msg = '',
		    $msg = $("span.msg", $form);
		if (res.success) {
		    msg = 'Saved';
		} else {
		    msg = 'Error: ' + res.errors.join(', ');
		}
		$msg.html(msg);
		setTimeout(function() {
		    $msg.html('');
		}, 4000);
	    }
	});
	return false;
    });
});
