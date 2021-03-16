/*
Copyright (C) 2011 by yinsee@wsatp.com
Published under GPLv3 License

https://github.com/yinsee/WEBOX2/blob/master/webox2.js
*/
var _php_self = /^([^?]+)\?*/.exec(location.href)[1];
var _email_regex = new RegExp(/^(("[\w-\s]+")|([\w-]+(?:\.[\w-]+)*)|("[\w-\s]+")([\w-]+(?:\.[\w-]+)*))(@((?:[\w-]+\.)*\w[\w-]{0,66})\.([a-z]{2,6}(?:\.[a-z]{2})?)$)|(@\[?((25[0-5]\.|2[0-4][0-9]\.|1[0-9]{2}\.|[0-9]{1,2}\.))((25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\.){2}(25[0-5]|2[0-4][0-9]|1[0-9]{2}|[0-9]{1,2})\]?$)/i);
$(function() {
	// formmail

	$('form.webox2_formmail').submit(function() {
        // check required if browser does not support
        var form_error = false;
        $(this).find("*[required]").each(function() {
            if ($(this).val()=='')
            {
                form_error = true;
                $(this).focus();
                alert("Please fill out this field.");
                return false;
            }

            if ($(this).attr('name')=='email' && !_email_regex.test($('input[name="email"]').val())){
             	form_error = true;
                $(this).focus();
                alert("Please enter a valid Email");
                return false;
			}
        });
        if (form_error==true) return false;
        
        // check email if browser does not support
        $(this).find("*[type=email]").each(function() {
            if (!_email_regex.test($(this).val()))
            {
                form_error = true;
                $(this).focus();
                alert("Please enter a valid email address.");
                return false;
            }
        });
        if (form_error==true) return false;
    
        // ready to send mail!
		var formmsg = $(this).attr('data-message');
		if (formmsg=='' || formmsg==undefined) formmsg = 'Form submitted.';
		
		var formhide = $(this).attr('data-hideform');
		var formobj = this;
		$.post(_php_self+'?a=formmail', $(this).serialize(), function(r) {
			if (r=='OK')
			{
				if (formhide!='' && formhide!=undefined) {
					$(formobj).html(formmsg);
				}
				else
					alert(formmsg);
			}
			else	
				alert(r);
		});	
		return false;
	});

});