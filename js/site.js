(function(e){e.session={_id:null,_cookieCache:undefined,_init:function(){if(!window.name){window.name=Math.random()}this._id=window.name;this._initCache();var e=(new RegExp(this._generatePrefix()+"=([^;]+);")).exec(document.cookie);if(e&&document.location.protocol!==e[1]){this._clearSession();for(var t in this._cookieCache){try{window.sessionStorage.setItem(t,this._cookieCache[t])}catch(n){}}}document.cookie=this._generatePrefix()+"="+document.location.protocol+";path=/;expires="+(new Date((new Date).getTime()+12e4)).toUTCString()},_generatePrefix:function(){return"__session:"+this._id+":"},_initCache:function(){var e=document.cookie.split(";");this._cookieCache={};for(var t in e){var n=e[t].split("=");if((new RegExp(this._generatePrefix()+".+")).test(n[0])&&n[1]){this._cookieCache[n[0].split(":",3)[2]]=n[1]}}},_setFallback:function(e,t,n){var r=this._generatePrefix()+e+"="+t+"; path=/";if(n){r+="; expires="+(new Date(Date.now()+12e4)).toUTCString()}document.cookie=r;this._cookieCache[e]=t;return this},_getFallback:function(e){if(!this._cookieCache){this._initCache()}return this._cookieCache[e]},_clearFallback:function(){for(var e in this._cookieCache){document.cookie=this._generatePrefix()+e+"=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;"}this._cookieCache={}},_deleteFallback:function(e){document.cookie=this._generatePrefix()+e+"=; path=/; expires=Thu, 01 Jan 1970 00:00:01 GMT;";delete this._cookieCache[e]},get:function(e){return window.sessionStorage.getItem(e)||this._getFallback(e)},set:function(e,t,n){try{window.sessionStorage.setItem(e,t)}catch(r){}this._setFallback(e,t,n||false);return this},"delete":function(e){return this.remove(e)},remove:function(e){try{window.sessionStorage.removeItem(e)}catch(t){}this._deleteFallback(e);return this},_clearSession:function(){try{window.sessionStorage.clear()}catch(e){for(var t in window.sessionStorage){window.sessionStorage.removeItem(t)}}},clear:function(){this._clearSession();this._clearFallback();return this}};e.session._init()})(jQuery)

$(function() {
	// formmail

	check_con();
	$('#contact_sel').on('change', function() { 
		$.session.set("contact_sel", $(this).val());
		check_con();
		if (this.value == 'Technical Support Request'){
			window.location = 'http://support.arms.com.my/open.php';
		}	
	});
	
	
	$('form.webox2_formmail_2').submit(function() {
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
				else{
					$("#panel").animate({
						opacity: 1,
						left: "-296px"
					}, { duration: 1000, queue:false });
					$("#panel").removeClass('open');
					alert(formmsg);
				}
			}
			else	
				alert(r);
		});	
		return false;
	});
	
	// contect form		
	$("#c-title").click(function () {
		if ($("#panel").hasClass("open")){
		
			$("#panel").animate({
				opacity: 1,
				left: "4px"
			}, { duration: 1000, queue:false });
			$("#panel").removeClass('open');
		}
		else
		{
			$("#panel").animate({
				opacity: 1,
				left: "-296px"
			}, { durstion: 1000, queue:false });
			$("#panel").addClass('open');
		}
	});
	
	$(".btn-close").click(function () {
			$("#panel").animate({
				opacity: 1,
				left: "-296px"
			}, { duration: 1000, queue:false });
			$("#panel").removeClass('open');
	});

	
	if ($("#panel").hasClass("close")){
		$("#c-title").mouseover(function () {
			$(this).animate({
				opacity: 1,
				left: "15px"
				}, { duration:200, queue:false });
		});
		$("#c-title").mouseout(function(){
			$(this).animate({
				opacity: 1,
				left: "-4px"
				}, {
				duration:200, queue:false });
		});
	};
	
	//Top Menu Function
	
	$('.ui-button, #menu a').live('mouseover',function(){ 
        $(this).addClass("ui-state-hover"); 
    }).live('mouseout', function(){ 
        $(this).removeClass("ui-state-hover ui-state-active"); 
    }).live('mousedown', function(){
        $(this).addClass("ui-state-active"); 
    }).live('mouseup',function(){
        $(this).removeClass("ui-state-active");
    });
    
    
    // menu setup
    $('#menu ul, #menu a').addClass("ui-corner-all");
    
    $('#menu ul ul > li:has(ul) > a:first-child').prepend('<span class="ui-icon ui-icon-triangle-1-e right"></span>');
    
    $('#menu ul ul ul').each(function(){
        // auto margin for sub menus
        $(this).css('margin-left',$(this).parent().width());
    });
    
    // stupid IE require this
    $('#menu li ul ul ul ul').hide();
    $('#menu li ul ul ul').hide();
    $('#menu li ul ul').hide();
    $('#menu li ul').hide(); // other browser only need this.
    
    $('#menu li:has(ul)').mouseenter(function(){
        $(this).children('ul').show();
    }).mouseleave(function(){
        $(this).children('ul').hide();
    });
    $('#menu ul ul').mouseenter(function(){
        $(this).prev().addClass('ui-state-default');
    }).mouseleave(function(){
        $(this).prev().removeClass('ui-state-default');
    });
    
//  $('#menu a').not('.noajax').click(function(){
//      return launch_url(this);
//  });
    
    $('a.ajax').live('click',function(){
        return launch_url(this);
    });

    
    jQuery.extend({
        check_required: function(object)
        {
            var ret = true;
            $(object).find(':input.required').removeClass('ui-state-error');
            $(object).find(':input.required').each(function(){
                if ($(this).val()=='' && !this.disabled && !this.readonly) {
                    $(this).addClass('ui-state-error');
                    alert($(this).attr('title')+' is required');
                    $(this).focus();
                    ret = false;
                    return false;
                }
            });
            return ret;
        }
    });
    
    $('button, .button, .toolbar a, input[type=submit], input[type=reset], input[type=button]').button();
    
    
    // check ipad/iphone and disable position absolute
	if (navigator.userAgent.match(/iPad|iPhone/i))
	{
	   $('.sticky-toolbar').css('position','static !important');
	}
        
    // ajax loading notificaiton
    $('#ajax-activity').ajaxStart(function() {
        $(this).html('&nbsp;&nbsp; Loading... &nbsp;&nbsp;');
    }).ajaxComplete(function(){
        $(this).text('');
    });
});



function check_con() {
	if ($.session.get("contact_sel") == 'Technical Support Request'){
		$('#contact_sel option[value="Technical Support Request"]').attr("selected", "selected");
		$('#form_detail').css('display','none');
		$('#form_tosupport').css('display','table-row-group');
	}
	else {
		$('#form_detail').css('display','table-row-group');
		$('#form_tosupport').css('display','none');
	}
}