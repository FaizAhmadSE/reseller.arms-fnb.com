<!-- Change Logs -->
<!-- 2018/09/04 Brandon - Enhance generate_serial_code() function, add license type variable into function and pass to backend process. -->

{extends file="theme.tpl"}
{block name="header"}
<style>
.pagination {
    clear: both;
    padding-top: 2em;
    text-align: center;
}
.pagination a {
    background: none repeat scroll 0 0 #fff;
    border: 1px solid #aaa;
    padding: 0 4px;
}
.pagination a.active{	background-color: #428bca;}

.tabs{	font-size:0.9em;}
.tabs a{ color:#006aad;}
.clearbr{
	clear: both;
	margin-bottom: 15px;
}
a.button, button .ui-button-text { font-size:12px !important; }
a.button:hover, button:hover { color:#fff; }

.inner_tbl{
    width:100%;
	font-size:0.9em;
}
.inner_tbl, .inner_tbl tr, .inner_tbl td{
	border:none;
	background:none;
}
.inner_tbl td{	padding:4px;}

.search_frm .search_select, .search_frm .search_input{
	border: solid 1px #ddd;
	line-height: 25px;
	padding: 0 10px;
	margin-left: 10px;
	background:#f1f1f1;
	color:#555;
}
.search_select{    padding: 4px 0 !important;}

.blink-text td{
	background: yellow !important;
	font-style:italic;
    -webkit-animation-name: blinker;
    -webkit-animation-duration: 1s;
    -webkit-animation-timing-function: linear;
    -webkit-animation-iteration-count: infinite;

    -moz-animation-name: blinker;
    -moz-animation-duration: 1s;
    -moz-animation-timing-function: linear;
    -moz-animation-iteration-count: infinite;

    animation-name: blinker;
    animation-duration: 1s;
    animation-timing-function: linear;
    animation-iteration-count: infinite;
}

@-moz-keyframes blinker {
    0% {  background: #fdff7f;}
    100% { background:white; }
}

@-webkit-keyframes blinker {
    0% {  background: #fdff7f;}
    100% { background:white; }
}

@keyframes blinker {
    0% {  background: #fdff7f;}
    100% { background:white; }
}
}
</style>
{/block}

{block name="content"}
<br /><br /><br />
<div class="both right">
	<a class="button" href="mailto:support@arms.my" target="_blank" >Contact Support</a>
</div>

<h1 class="left" style="font-size: 28px;line-height: 30px;">{$this->login.contact_person}, {$this->login.company_name}</h1><br /><br />

<div id="tabs">
  	<ul>
        {if $this->login.subscription eq 'yes' || $this->login.subscription eq 'full' || $this->login.subscription eq 'manual'}
	    	<li><a href="#tabs-1">Active Subscriptions</a></li>
			<li><a href="#tabs-2">{if $this->login.email eq 'neilfbradley@hotmail.com' || $this->login.subscription eq 'manual' || $this->login.sub_reseller_option eq 'yes'}Inactive{else}Expired{/if} Subscriptions</a></li>
		{*else}
			<li><a href="#tabs-1">Subscriptions</a></li>*}
		{/if}

        {if $this->login.allow_generate_code eq 'yes'}
			<li><a href="#tabs-3">Serial Code</a></li>
	        <li><a href="#tabs-4">Top Up Code (Cloud)</a></li>
		{/if}
	</ul>
    {if $this->login.subscription eq 'yes' || $this->login.subscription eq 'full' || $this->login.subscription eq 'manual'}
		<div id="tabs-1" class="tabs">
	        <button id="check_this_device" class="right">Add Device</button>
			{include file="reseller.search_bar.tpl" func="load_subscription"}
			<div class="clearbr"></div>
            <div id="load_subscription"></div>
		</div>

		<div id="tabs-2" class="tabs">
	        <button id="check_this_device" class="right">Add Device</button>
            {include file="reseller.search_bar.tpl" func="load_subscription_exp"}
			<div class="clearbr"></div>
	        <div id="load_subscription_exp"></div>
		</div>
	{*else}
		<div id="tabs-1" class="tabs">
	        <button id="check_this_device" class="right">Add Device</button>
            {include file="reseller.search_bar.tpl" func="load_subscription"}
			<div class="clearbr"></div>
            <div id="load_subscription"></div>
		</div>*}
    {/if}

    {if $this->login.allow_generate_code eq 'yes'}
		<div id="tabs-3" class="tabs">
			<button class="right" onclick="slot_code()">Generate Slot Code</button>
			<button class="right" onclick="serial_code()">Generate Device Code</button>
            {include file="reseller.search_bar.tpl" func="load_serial_code"}
			<div class="clearbr"></div>
            <div id="load_serial_code"></div>
		</div>

		<div id="tabs-4" class="tabs">
            <button class="right" onclick="topup_code()">Generate Code</button>
            {include file="reseller.search_bar.tpl" func="load_topup_code"}
			<div class="clearbr"></div>
			<div id="load_topup_code"></div>
		</div>
	{/if}
</div>

<div id="topup_code"></div>
<div id="check_device"></div>
<div id="add_device"></div>
<div id="edit_device"></div>
<div id="renew_device"></div>
<div id="remap_device"></div>
<div id="device_history"></div>
<div id="existing_account"></div>
<div id="unpair_device"></div>
<div id="serial_code"></div> <!-- Serial Tab -->
<div id="slot_code"></div> <!-- Serial Tab -->
{/block}

{block name="footer"}
<script>
var php_self = '{$smarty.server.PHP_SELF}';
var dataStore = window.sessionStorage;
var index="tabindex";
{if $new_device}
$(window).load(function(){
	$('#renew_device')
		.dialog('option', 'title', 'Device Payment')
        .dialog({ width:'500', height:'500', title:'Device Payment', autoOpen:false, modal:true , buttons: {
			'Pay' : function() { $('form#renew-box').submit(); },
			'Cancel' : function() { $(this).dialog('close'); }
			}
		})
		.dialog('open').load(php_self, { a:'renew', id:'{$new_device._id}' });
});
{/if}

$(function() {
	try {
        // getter: Fetch previous value
        var oldIndex = parseInt(dataStore.getItem(index));
    } catch(e) {
        // getter: Always default to first tab in error state
        var oldIndex = 0;
    }

	$( "#tabs" ).tabs({
        selected : oldIndex,
        show : function( event, ui ){
			dataStore.setItem( index, ui.index )
        }
    });

	$( ".button, button" ).button();

	//--- Preload tabs ---
    {if $this->login.subscription eq 'yes' || $this->login.subscription eq 'full' || $this->login.subscription eq 'manual'}
		load_subscription(0, '', 0);
	    load_subscription_exp(0, '', 0);
		{if $this->login.payware eq "yes"}
		load_subscription_sub(0, '', 0);
		{/if}
	{else}
        load_subscription(0, '', 0);
	{/if}

    {if $this->login.allow_generate_code eq 'yes'}
	    load_serial_code(0, '', 0);
		load_topup_code(0, '', 0);
    {/if}

	{if $msg}
		alert("{$msg}");
	{/if}

	window.setTimeout(function(){
		$('tr.blink-text').removeClass("blink-text");
	},5000);
	//--- End of preload tabs ---

	$(document).on('click','#check_this_device',function() {
		$('#check_device')
			.dialog('option', 'title', 'Add New Device')
			.dialog({ width:'400', height:'180', title:'Add New Device', autoOpen:false, modal:true ,
				buttons: {
					'Check Device' : function() { check(); },
					'Close' : function() { $(this).dialog('close'); }
				}
			})
			.dialog('open').load(php_self, { a:'check_device'});
	});

	$(document).on('click','.renew_this_device', function() {
		$('#renew_device')
			.dialog('option', 'title', 'Renew Device')
			.dialog({ width:'500', height:'500', title:'Renew Device', autoOpen:false, modal:true , buttons: {
				'Renew' : function() { $('form#renew-box').submit(); },
				'Cancel' : function() { $(this).dialog('close'); }
				}
			})
			.dialog('open').load(php_self, { a:'renew', id:$(this).parents('*[data-id]').attr('data-id') });
	});

	$(document).on('click','.remap_vendor_id', function() {
		$('#remap_device')
			.dialog('option', 'title', 'Remap Vendor ID')
			.dialog({ width:'550', height:'350', title:'Remap Vendor ID', autoOpen:false, modal:true , buttons: {
				'Remap' : function() { $('form#remap-box').submit(); },
				'Cancel' : function() { $(this).dialog('close'); }
				}
			})
			.dialog('open').load(php_self, { a:'remap', id:$(this).parents('*[data-id]').attr('data-id') });
	});

	$(document).on('click','.edit_this_device', function() {
		$('#edit_device')
			.dialog('option', 'title', 'Edit Device')
			.dialog({ width:'50%', height:'700', title:'Add Device', autoOpen:false, modal:true , buttons: {
				'Update' : function() { $('#edit_device form').submit(); },
				'Cancel' : function() { $(this).dialog('close'); }
				}
			})
			.dialog('open').load(php_self, { a:'add_device', id:$(this).parents('*[data-id]').attr('data-id') });
	});

	$(document).on('click','.this_device_history', function() {
		$('#device_history')
			.dialog('option', 'title', 'Payment History')
			.dialog({ width:'50%', height:'700', title:'Device History', autoOpen:false, modal:true , buttons: {
				'Close' : function() { $(this).dialog('close'); }
				}
			})
			.dialog('open').load(php_self, { a:'device_history', id:$(this).parents('*[data-id]').attr('data-id') });
	});

	$(document).on('click','.add_this_device_to_cloud', function(){
		var device_id = $(this).parent().parent().parent().attr('data-id'),
			params = {
				a: "add_device_to_cloud",
			    id: device_id
			};
		$('#blackout').css('display','block');
 		create_cloud_account(params);
	});

	$(document).on('click','.unpair_device', function(){
		var device_id = $(this).attr('data-id'),
			params = {
				a: "unpair_device",
			    id: device_id
			};
 		unpair_device(params, false);
	});
	
	$(document).on('click','.app_activate', function(){
		var device_id = $(this).parent().parent().parent().attr('data-id');
		var condition = "";
		if($(this).is(':checked')){
			var res = confirm("Activate this device?");
			
			if(res)
				condition = "activate";
			else
				$(this).attr('checked', false);
			
		}else{
			var res = confirm("Deactivate this device?");
			
			if(res)
				condition = "deactivate";
			else
				$(this).attr('checked', true);
		}
		
		if(condition != ""){
			var params = {
				a: "app_action",
				id: device_id,
				action: condition
			};
			
			$.post(php_self, params,
			function(data){
				if(data == "OK"){
					window.location.reload();
				}else{
					alert(data);
				}
			});
			
		}
		
	});
	
	$(document).on('click','.cloud_activate', function(){
		var device_id = $(this).parent().parent().parent().attr('data-id');
		var condition = "";
		if($(this).is(':checked')){
			var res = confirm("Activate this cloud?");
			
			if(res)
				condition = "activate";
			else
				$(this).attr('checked', false);
			
		}else{
			var res = confirm("Deactivate this cloud?");
			
			if(res)
				condition = "deactivate";
			else
				$(this).attr('checked', true);
		}
		
		if(condition != ""){
			var params = {
				a: "cloud_activation",
				id: device_id,
				action: condition
			};
			
			$.post(php_self, params,
			function(data){
				if(data == "OK"){
					window.location.reload();
				}else{
					alert(data);
				}	
			});
			
		}
		
	});

	$(document).on('dblclick', '.cus_name', function(e){
		var text = $(this).text();
		$(this).html('<input type="text" value="'+text+'">');
	});
});

function ed(set,obj)
{
	var id = $(obj).parent().attr('data-id');
	var type = $(obj).parent().attr('data-type');
	var pos = $(obj).position();
	var w = $(obj).innerWidth();
	var data = { set:set, _id:id, type:type}
	$('#ipe').css({
	    left: pos.left,
	    top: pos.top,
	    width: (w > 150) ? w : 150
	})
	.val($(obj).text())
	.unbind()
	.blur(function(){ $(this).hide(); })
	.change(function(){
	    // save the value
	    data.val = $(this).val();
	    $(obj).css('color','grey');
	    $.get(php_self+'?a=set_serial_code',data,function(r)
		{
		    $(obj).css('color','');
			if (r=='OK')
			{
			    $(obj).text(data.val);
			}
			else
			{
				obj.checked = !obj.checked;
				alert(r);
			}
		});
	})
	.show()
	.focus();
}

function load_subscription(page, f, reset_f){
	var load_path = php_self+"?a=load_subscription&page="+page;

	if (f!=''){
        if (reset_f)
	        var serializeData = 'filter=&keyword=';
		else
		    var serializeData = $(f).serialize();

		load_path = load_path +'&'+ serializeData;
	}

	$('#load_subscription').load(load_path);
}

function load_subscription_exp(page, f, reset_f){
	var load_path = php_self+"?a=load_subscription_exp&page="+page;

	if (f!=''){
	    if (reset_f)
	        var serializeData = 'filter=&keyword=';
		else
    	    var serializeData = $(f).serialize();

		load_path = load_path +'&'+ serializeData;
	}

	$('#load_subscription_exp').load(load_path);
}

function load_subscription_sub(page, f, reset_f){
	var load_path = php_self+"?a=load_subscription_sub&page="+page;

	if (f!=''){
	    if (reset_f)
	        var serializeData = 'filter=&keyword=';
		else
    	    var serializeData = $(f).serialize();

		load_path = load_path +'&'+ serializeData;
	}

	$('#load_subscription_sub').load(load_path);
}
function load_serial_code(page, f, reset_f){
	var load_path = php_self+"?a=load_serial_code&page="+page;

	if (f!=''){
        if (reset_f)
	        var serializeData = 'filter=&keyword=';
		else
			var serializeData = $(f).serialize();

		load_path = load_path +'&'+ serializeData;
	}

	$('#load_serial_code').load(load_path);
}

function load_topup_code(page, f, reset_f){
	var load_path = php_self+"?a=load_topup_code&page="+page;

	if (f!=''){
        if (reset_f)
	        var serializeData = 'filter=&keyword=';
		else
			var serializeData = $(f).serialize();

		load_path = load_path +'&'+ serializeData;
	}

	$('#load_topup_code').load(load_path);
}

function slot_code(){
	$('#slot_code')
		.dialog({ title:'Generate Device Code', autoOpen:false, modal:true , buttons: {
			'Go' : function() { $('#slot_code form').submit(); },
			'Cancel' : function() { $(this).dialog('close'); }
			}
		})
		.dialog('open').load(php_self, { a:'slot_code' });
}

function generate_slot_code(f){
	var cname = $(f).find('input[name="cname"]').val();
	var inv = $(f).find('input[name="inv"]').val();
	var slots = $(f).find('input[name="number"]').val();
	var remark = $(f).find('input[name="remark"]').val();
	
	$.get(php_self, { a:'generate_slot_code', cname:cname, inv:inv, slots:slots, remark:remark }, function(r){
		if (r!='OK')
			alert(r);
		else{
			load_serial_code(0, 'x', 1);
			$('#slot_code').dialog('close');
            $("#tabs-3 .search_frm .search_select").val("");
            window.setTimeout(function(){
				$('tr.blink-text').removeClass("blink-text");
			},5000);
		}
	});
	
	return false;
}

function serial_code(){
	$('#serial_code')
		.dialog({ title:'Generate Device Code', autoOpen:false, modal:true , buttons: {
			'Go' : function() { $('#serial_code form').submit(); },
			'Cancel' : function() { $(this).dialog('close'); }
			}
		})
		.dialog('open').load(php_self, { a:'serial_code' });
}

function generate_serial_code(f){
	var cname = $(f).find('input[name="cname"]').val();
	var inv = $(f).find('input[name="inv"]').val();
	var slots = $(f).find('input[name="number"]').val();
	var remark = $(f).find('input[name="remark"]').val();
	var license_type = $(f).find('input[name="license_type"]:checked').val();

	$.get(php_self, { a:'generate_serial_code', cname:cname, inv:inv, slots:slots, remark:remark, license_type:license_type }, function(r){
		if (r!='OK')
			alert(r);
		else{
			load_serial_code(0, 'x', 1);
			$('#serial_code').dialog('close');
            $("#tabs-3 .search_frm .search_select").val("");
            window.setTimeout(function(){
				$('tr.blink-text').removeClass("blink-text");
			},5000);
		}
	});
	
	return false;
}

function topup_code(){
	$('#topup_code')
		.dialog({ title:'Generate Top up Code', autoOpen:false, modal:true , buttons: {
			'Go' : function() { $('#topup_code form').submit(); },
			'Cancel' : function() { $(this).dialog('close'); }
			}
		})
		.dialog('open').load(php_self, { a:'topup_code' });
}

function generate_topup_code(f){
	$.get(php_self+"?a=generate_topup_code", $(f).serialize() , function(r){
		if (r!='OK')
			alert(r);
		else{
			load_topup_code(0, 'x', 1);
			$('#topup_code').dialog('close');
            $("#tabs-4 .search_frm .search_select").val("");
            window.setTimeout(function(){
				$('tr.blink-text').removeClass("blink-text");
			},5000);
		}
	});
	return false;
}

//--- Edit device - form submit checking ---
function update(f){
    var retr = false,
		checkfield = check_field();

	if($("#reseller_id").val() != "")
		retr = true;

	if(checkfield && retr)
		return true;
	else
		return false;
}

function check_field(){
	var state = true;
	$('.required').each(function(){
		if (($(this).attr('type')=='text' && $.trim($(this).val())=='') || ($(this).attr('type')!='text' && $(this).find('option:selected').val()=='')){
		    alert($(this).attr('title') + ' is required');
		    $(this).focus();
		    state = false;
			return false;
		}
	});
	return state;
}

//--- Check & Add New Device ID ---
function check(){
	var id = $('#device_id').val();

	if (id == ''){
		alert('Device ID is required.');
		return false;
	}

	$.post(php_self, { a: 'load_device', id: id },
		function(data){
			if (data == 'OK'){
				$('#check_device').dialog('close');
				$('#add_device')
					.dialog('option', 'title', 'Add Device')
					.dialog({ width:'50%', height:'700', title:'Add Device', autoOpen:false, modal:true , buttons: {
						'Add' : function() { $('#add_device form').submit(); },
						'Cancel' : function() {	$('#check_device').dialog('open');$(this).dialog('close'); }
						}
					})
					.dialog('open').load(php_self, { a:'add_device', id:id, add:1 });
			}
            else if(data == 'EXISTING_SLOT'){
				alert('Unable to add this ID, as it already has existing slots. Please contact us for further assists.');
			}
			else
				alert('Invalid ID.');
		}
	);
}

function create_cloud_account(params){
	$.post(php_self, params, function(r){
		if (r.error>0){
			alert(r.msg);
			$('#blackout').css('display','none');
			return false;
		}
        else if (r.success==1){
			alert("Device code generated successful.");
            $('#existing_account').dialog('close');
			$('#blackout').css('display','none');
			window.location.reload();
		}
		else{
			if (r.code=='711' && r.accounts){
				$('#existing_account')
					.dialog('option', 'title', 'Create Cloud for Existing Account')
                    .dialog({ width:'50%', height:'400', title:'Create Cloud for Existing Account', autoOpen:false, modal:true , buttons: {
						'Update' : function() { $('#existing_account form').submit(); },
						'Cancel' : function() { $(this).dialog('close'); }
						}
					})
					.dialog('open').load(php_self, {
							a:'cloud_select_account',
							id: params.id,
							accounts: r.accounts
					});
			}
			$('#blackout').css('display','none');
			return false;
		}
	}, "json");
	return false;
}

function unpair_device(params, confirm){
	if (!confirm){
        $('#unpair_device')
            .dialog({ title:'Unpair Device', autoOpen:false, modal:true , buttons: {
				'Confirm' : function() { $('#unpair_device form').submit(); },
				'Cancel' : function() { $(this).dialog('close'); }
				}
			})
			.dialog('open').load(php_self, params);
	}
	else{
		$.post(php_self, params, function(r){
			if (r!='OK')
				alert(r);
			else{
				alert("Device is unpaired.");
	            window.location.reload();
			}
		});
	}
	return false;
}

</script>
{/block}
