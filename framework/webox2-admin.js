/*
Copyright (C) 2011 by yinsee@wsatp.com
Published under GPLv3 License

https://github.com/yinsee/WEBOX2/blob/master/webox2-admin.js
*/
var orgcontent = {};
var current_edit_obj = false;

// inplace editing

$(function(){

	$('*[webox2=ced]').addClass('ced');
	
	$('.ced').live('dblclick', function(e) {
		if ($(this).attr('contentEditable')!=="true")
		{
			$(this).attr('contentEditable',"true").attr('title','Ctrl+S to save, Escape to cancel changes');
			ced_store(this);
		}
		else
		{
			current_edit_obj = this;
			$('#htmlsource textarea').val($.trim($(this).html())).focus();
			$('#htmlsource').dialog('open');
		}
		e.preventDefault();
		e.stopPropagation();
	}).live('keydown', function(e) {
		if ($(this).attr('contentEditable')!=="true") return;
		if (e.keyCode==27) { //Escape
			ced_restore(this);
		}
        else if (e.ctrlKey && e.keyCode==83) { //Ctrl+S
            ced_save(this);
        }
		else if (e.ctrlKey && e.keyCode==76) { //Ctrl+L
            ced_makelink();
			return false;
        }
		else
			return;
		e.preventDefault();
		e.stopPropagation();
		$(this).attr('contentEditable', "false").attr('title','Double Click to edit').removeClass('changed');
	}).live('keyup', function(e) {
		// detect changes and highlight
		if ($(this).html()!==orgcontent[$(this).attr('data-id')][$(this).attr('data-key')])
			$(this).addClass('changed');
		else
			$(this).removeClass('changed');
    }).live('blur', function(){
		if ($(this).attr('contentEditable')!=="true") return;
	}).live('focus', function(){
		if ($(this).attr('contentEditable')!=="true") return;
	}).attr('title','Double Click to edit'); // add tooltip

	$('#htmlsource').dialog({title:"Edit HTML",width:600,resizable:false,autoOpen:false,buttons:{
		/*'Tidy':  function() { },*/
		'Save': function() { ced_save(current_edit_obj); $(this).dialog('close'); },
		'Compress': function () {
			var $tx = $('#htmlsource textarea');
			$tx.val($tx.val().replace(/[\n\r]+/gi, ""));
			$(current_edit_obj).addClass('changed').html($tx.val());
		},
		'Clean HTML Styles':  function () {
			var $tx = $('#htmlsource textarea');
			$tx.val($tx.val().replace(/style="[^"]+"/gi, ""));
			$(current_edit_obj).addClass('changed').html($tx.val());
		},
		'Remove HTML Tags':  function () {
			var $tx = $('#htmlsource textarea');
			$tx.val(strip_tags($tx.val()));
			$(current_edit_obj).addClass('changed').html($tx.val());
		}
	}});
	$('#htmlsource textarea').keyup(function(){ $(current_edit_obj).addClass('changed').html($(this).val()); });
	//.blur(function(){$('#htmlsource').dialog('close')});

	// cancel ctrl+s on body
	$(document).keydown(function(e) {
		if (e.ctrlKey && e.keyCode==83) {
            return false;
        }
	});
	

	// lets do some html5 image dropping and upload!
	ced_init_drop_file();
});

function ced_makelink()
{
	var link = prompt("Enter URL");
	if (link=='' || link==false || link==undefined) return;
	document.execCommand("createLink", false, link);
}

function ced_dropped(e) {
	if ($(this).attr('contentEditable')!=="true") return;

	if (e.dataTransfer.files.length<=0) return true; // no file, do the default

	if (e.dataTransfer.files.length>1)
	{
		alert('You can only drag and drop 1 file at a time');
		e.preventDefault();
		return false;
	}
	if (!/\.(png|jpg|jpg|zip|pdf)$/i.test(e.dataTransfer.files[0].fileName))
	{
		alert('File type not acceptable');
		return false;
	}

	var divname = 'insert-'+time();
	document.execCommand('insertHTML', false, '<img id="'+divname+'" src="ui/ced_uploading.gif">');

	// do the upload
	var file = e.dataTransfer.files[0];
	var reader = new FileReader();
	reader.onprogress = function(ev) {
		// show progress
	};
	reader.onload = function(ev) {
		// send to server
		var url = location.href;
		url = url.substring(0, url.lastIndexOf('/')+1) + 'index.php';
		$.post(url,{a:'ced_dropfile',filename:file.fileName, data:this.result},function(r){
			$('#'+divname).replaceWith(r);
		});
	};
	reader.readAsDataURL(file);

	e.preventDefault();
	return false;
}

function ced_cancel(e) {
	e.preventDefault();
	return false;
}

function ced_restore(obj)
{
	$(obj).html(orgcontent[$(obj).attr('data-id')][$(obj).attr('data-key')]);
	$(this).removeClass('changed');
}

function ced_store(obj)
{
	if (orgcontent[$(obj).attr('data-id')]==undefined)
		orgcontent[$(obj).attr('data-id')] = {};
	orgcontent[$(obj).attr('data-id')][$(obj).attr('data-key')] = $(obj).html();
}

function ced_get_script(obj,action)
{
	if ($(obj).attr('data-php')!=undefined)
		return $(obj).attr('data-php') + '?a='+$(obj).attr('data-action');

	// get url
	return /^([^?]+)\?*/.exec(location.href)[1] + '?a='+(action==undefined?'ced_save':action);
	//return match[1];
	//var url=location.href;
	//return url.substring(location.href.lastIndexOf('/')+1)
}

function ced_save(obj)
{
    if ($(obj).hasClass('changed'))
    {
        $.post(ced_get_script(obj), {key:$(obj).attr('data-key'), collection:$(obj).attr('data-collection'), _id:$(obj).attr('data-id'), html:$(obj).html()}, function(r){
            if (r=='OK')
            {
				ced_store(obj); // update the html in memory
                // apprise('Saved',{nobutton:false});
            }
            else
				alert(r);
               //apprise(r,{nobutton:false});
            $(obj).removeClass('changed');
        });
    }
}


// image drag and drop
function ced_init_drop_file(obj)
{	

	// lets do some html5 image dropping and upload!
/*
	$('.ced').bind('dragover', ced_cancel);
	//$('.ced').bind('dragenter', ced_cancel);
	$('.ced').bind('dragleave', ced_cancel);
	$('.ced').each(function() { this.ondrop = ced_dropped; });*/


	if (obj==undefined)
	{
		$('.ied').each(function() {
			this.title = 'Drop image to replace';
			this.ondragover = ced_cancel;
			this.ondragleave = ced_cancel;
			this.ondrop = ied_dropped; 
		});

		$('.ced').each(function() {
			this.ondragover = ced_cancel;
			this.ondragleave = ced_cancel;
			this.ondrop = ced_dropped; 
		});
	}
	else
	{
		$(obj).find('.ied').each(function() {
			this.title = 'Drop image to replace';
			this.ondragover = ced_cancel;
			this.ondragleave = ced_cancel;
			this.ondrop = ied_dropped; 
		});

		$(obj).find('.ced').each(function() {
			this.ondragover = ced_cancel;
			this.ondragleave = ced_cancel;
			this.ondrop = ced_dropped; 
		});
	}
}

function ied_dropped(e) {
	if ($(this).attr('data-id')=="") { 
		alert("Cannot upload image, data-id not assigned");
		return;
	}

	if (e.dataTransfer.files.length<=0) return true; // no file, do the default

	if (e.dataTransfer.files.length>1)
	{
		alert('You can only drag and drop 1 file at a time');
		e.preventDefault();
		return false;
	}
	if (!/\.(png|jpg|jpg)$/i.test(e.dataTransfer.files[0].fileName))
	{
		alert('File type not acceptable');
		return false;
	}

	var ied_object = this;
	$(ied_object).addClass("ied_uploading");

	// do the upload
	var file = e.dataTransfer.files[0];
	var reader = new FileReader();
	reader.onprogress = function(ev) {
		// show progress
	};
	reader.onload = function(ev) {
		// send to server
		$.post(ced_get_script(ied_object,'ied_dropfile'),{ _id:$(ied_object).attr('data-id'), key:$(ied_object).attr('data-key'),  collection:$(ied_object).attr('data-collection'), filename:file.fileName, data:this.result},function(r){			
			$(ied_object).removeClass("ied_uploading");
			$(ied_object).css('background-image',"url('"+r+"')");
		});
	};
	reader.readAsDataURL(file);

	e.preventDefault();
	return false;
}


/* other reference js */
function time() {
    // Return current UNIX timestamp
    //
    // version: 1103.1210
    // discuss at: http://phpjs.org/functions/time    // +   original by: GeekFG (http://geekfg.blogspot.com)
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: metjay
    // +   improved by: HKM
    // *     example 1: timeStamp = time();    // *     results 1: timeStamp > 1000000000 && timeStamp < 2000000000
    return Math.floor(new Date().getTime() / 1000);
}

function strip_tags(input, allowed) {
    // Strips HTML and PHP tags from a string
    //
    // version: 1103.1210
    // discuss at: http://phpjs.org/functions/strip_tags    // +   original by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   improved by: Luke Godfrey
    // +      input by: Pul
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Onno Marsman    // +      input by: Alex
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Marc Palau
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +      input by: Brett Zamir (http://brett-zamir.me)    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Eric Nagel
    // +      input by: Bobby Drake
    // +   bugfixed by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Tomasz Wesolowski    // +      input by: Evertjan Garretsen
    // +    revised by: Rafal Kukawski (http://blog.kukawski.pl/)
    // *     example 1: strip_tags('<p>Kevin</p> <b>van</b> <i>Zonneveld</i>', '<i><b>');
    // *     returns 1: 'Kevin <b>van</b> <i>Zonneveld</i>'
    // *     example 2: strip_tags('<p>Kevin <img src="someimage.png" onmouseover="someFunction()">van <i>Zonneveld</i></p>', '<p>');    // *     returns 2: '<p>Kevin van Zonneveld</p>'
    // *     example 3: strip_tags("<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>", "<a>");
    // *     returns 3: '<a href='http://kevin.vanzonneveld.net'>Kevin van Zonneveld</a>'
    // *     example 4: strip_tags('1 < 5 5 > 1');
    // *     returns 4: '1 < 5 5 > 1'    // *     example 5: strip_tags('1 <br/> 1');
    // *     returns 5: '1  1'
    // *     example 6: strip_tags('1 <br/> 1', '<br>');
    // *     returns 6: '1  1'
    // *     example 7: strip_tags('1 <br/> 1', '<br><br/>');    // *     returns 7: '1 <br/> 1'
    allowed = (((allowed || "") + "").toLowerCase().match(/<[a-z][a-z0-9]*>/g) || []).join(''); // making sure the allowed arg is a string containing only tags in lowercase (<a><b><c>)
    var tags = /<\/?([a-z][a-z0-9]*)\b[^>]*>/gi,
        commentsAndPhpTags = /<!--[\s\S]*?-->|<\?(?:php)?[\s\S]*?\?>/gi;
    return input.replace(commentsAndPhpTags, '').replace(tags, function ($0, $1) {        return allowed.indexOf('<' + $1.toLowerCase() + '>') > -1 ? $0 : '';
    });
}