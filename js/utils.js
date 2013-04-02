$.fn.fcSerializeObject = function()
{
    var disabled = this.find(':input:disabled').removeAttr('disabled');
    var o = {};
    var a = this.serializeArray();
    $.each(a, function() {
	if (o[this.name] !== undefined) {
	    if (!o[this.name].push) {
		o[this.name] = [o[this.name]];
	    }
	    o[this.name].push(this.value || '');
	} else {
	    o[this.name] = this.value || '';
	}
    });
    disabled.attr('disabled','disabled');
    return o;
};
