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

</style>
{/block}

{block name="content"}
<br /><br /><br />
<h1 class="left" style="font-size: 28px;line-height: 30px;">{$this->page_title}</h1><br /><br />

<div class="both">
	{if $this->page_type neq 'trial'}<a class="button right" id="add_reseller" >Add Reseller</a>{/if}
	<form id=form_filter onsubmit="return false">
	Filter by :<select class="ui-autocomplete-input" id="filterby" onchange="filter_list(true)">
	{if $this->page_type neq 'trial'}
	{foreach from=$this->filter_list item=item}
    	<option value="{$item}" {if $this->filter eq $item}selected{/if}>{$item}</option>
	{/foreach}
	{else}
	{foreach from=$this->filter_trial item=item}
    	<option value="{$item}" {if $this->filter eq $item}selected{/if}>{$item}</option>
	{/foreach}
	{/if}
	</select>

 	<input type="text" id=filter onkeyup="filter_list(true)" value="{$this->inputstr}">
	<span id="table_status"></span>
	{if $this->page_type neq 'trial'}<input type="checkbox" id="filter-delete" name="filter_delete" onchange="filter_list(true)" {if $smarty.session.m_delete}checked{/if}> <label for="filter-delete">Deleted</label>{/if}
	</form>
</div>
<br />

{if $this->page_type eq 'trial'}
<div id="sub_reseller_table">
{include file="trial.table.tpl"}
</div>
{else}
<div id="sub_reseller_table">
{include file="sub_reseller.table.tpl"}
</div>
{/if}

<div id="add_reseller_dialog"></div>
<div id="edit"></div>
{/block}

{block name="footer"}
<script>
var php_self = '{$smarty.server.PHP_SELF}';
var page_type = '{$this->page_type}';
var dataStore = window.sessionStorage;
var index="tabindex";
var page_query = '';
	
	if(page_type != ""){
		page_query = "&page_type=trial";
	}
	
$('#table_status').html('{$this->totalrecord} record(s) found.');

$(function() {
	$( ".button, button" ).button();
	
	$(document).on('click','.del-agent', function() {
	if (confirm('Delete this Reseller?'))
	{
		var obj = this;
		$.get(php_self, { a:'set', set:'delete', val:'1', _id:$(this).parents('*[data-id]').attr('data-id') }, function(r){
			if (r=='OK'){
				$(obj).removeClass('del-agent').addClass('undel-agent').html('<span class="ui-icon ui-icon-refresh" title="Undelete Order"></span>');
				$(obj).closest('tr').find('input[type="checkbox"]').attr('disabled',true);
			}
			else
				alert(r);
		});
	}
	});
	
	$(document).on('click','.undel-agent', function() {
	if (confirm('Undelete this Reseller?'))
	{
		var obj = this;
		$.get(php_self, { a:'set', set:'delete', val:'', _id:$(this).parents('*[data-id]').attr('data-id') }, function(r){
			if (r=='OK'){
				$(obj).removeClass('undel-agent').addClass('del-agent').html('<span class="ui-icon ui-icon-trash" title="Delete Order"></span>');
				$(obj).closest('tr').find('input[type="checkbox"]').attr('disabled',false);
			}
			else
				alert(r);
		});
	}
	});
	
	$(document).on('click','.view-agent', function() {
	  $('#edit').dialog('option', 'title', 'Edit Reseller')
	    .dialog('open').load(php_self, { a:'detail', '_id':$(this).parents('*[data-id]').attr('data-id') });
	});
	
	$(document).on('click','.view-region', function() {
	  $('#edit').dialog('option', 'title', 'Edit Region')
	    .dialog('open').load(php_self, { a:'region', '_id':$(this).parents('*[data-id]').attr('data-id') });
	});
	
	$(document).on('click','#add_reseller',function() {
		$('#add_reseller_dialog')
			.dialog('option', 'title', 'Add Reseller')
			.dialog({ width:'800', height:'800', title:'Create New Reseller', autoOpen:false, modal:true ,
				buttons: {
					'Add' : function() { 
						var retr = true;
						$('.required').each(function()
						{
							if($(this).attr('title') == "Email"){
								if(!validateEmail($(this).val())){
									alert('Invalid Email');
									$(this).focus();
									retr = false;
									return false;

								}
							}
							if($.trim($(this).val())=='')
							{
								alert($(this).attr('title') + ' is required');
								$(this).focus();
								retr = false;
								return false;
							}
						});
						if(retr)
						$('#add_reseller_dialog form').submit(); 
					},
					'Reset' : function() { $('#add_reseller_dialog form').get(0).reset(); }
				}
			})
			.dialog('open').load(php_self, { a:'add'});
	});
	
	$('#edit').dialog({ width:'60%', height:'670', title:'Edit Reseller', autoOpen:false, modal:true , buttons: {
		'Update' : function() {
		
			var retr = true;
			$('.required').each(function()
			{
				if($.trim($(this).val())=='')
				{
					alert($(this).attr('title') + ' is required');
					$(this).focus();
					retr = false;
					return false;
				}
			});
			/*
			if($('input[name=cloud]').length>0)
			{	
				if($('input[name=cloud]:checked').length<=0){
				$('input[name=cloud]').focus();
				 alert("Reseller Cloud option is empty");
				 retr = false;
				}
			}
			*/
			if(retr){
			$.post(php_self, $('#edit form').serialize(),function(r){
				if (r=='OK'){
					alert("Updated");
					$('#edit').dialog('close');
				}
				else
					alert('Error: '+r);
			});
			}
			return false;
		 },
	} });
});

function goto_page(pg)
{
	$('#userlist').css('opacity',0.5);
	var url = "";
	if(page_type == "trial"){
		url = php_self+'?a=trial_list&page='+pg+page_query;
	}else{
		url = php_self+'?a=list_resellers&page='+pg;
	}
	
	$('#sub_reseller_table').load(url,null,function(){ $('#userlist').css('opacity',1); });
}

var _t = false;
function filter_list(v)
{
	if (v!=undefined)
	{
		if(_t!=false) clearTimeout(_t);
		_t = setTimeout('filter_list()',500);
	}
	else
	{
	    var selection=$('#filterby').val();
	    var inputbox=$('#filter').val();
	    var deletebox=$('#filter-delete').is(":checked");

	    $('#userlist').css('opacity',0.5);
		var url = "";
		if(page_type == "trial"){
			url = php_self+'?a=trial_list'+page_query;
		}else{
			url = php_self+'?a=list_resellers';
		}
		
		$('#sub_reseller_table').load(url,$('#form_filter').serialize()+'&selection='+selection+'&inputbox='+inputbox+'&deletebox='+deletebox, function(){ $('#userlist').css('opacity',1); });
		
	}
}

function sort(variable)
{	
    $('#sub_reseller_table').load(php_self+'?a=sort&val='+variable+page_query);
}

function activate(obj)
{
	var id = $(obj).parent().parent().attr('data-id');
    $(obj).attr('disabled',true);
    $.get(php_self+'?a=set',{ val:obj.checked?1:0,set:'active',_id:id },function(r)
		{
		    $(obj).attr('disabled',false);
			if (r!='OK')
			{
				obj.checked = !obj.checked;
				alert(r);
			}
		});
}
{literal}
function validateEmail(email) { 
    var re = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return re.test(email);
} 
{/literal}

</script>
{/block}
