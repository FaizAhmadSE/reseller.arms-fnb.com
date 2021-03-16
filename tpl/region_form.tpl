<style>
ul
{
    list-style-type: none;
}
</style>
<form id="signup" method="post" {if !$this->reseller}onsubmit="return inputcheck()"{/if}>
<input type="hidden" name="a" value="{if $this->reseller}regionupdate{/if}">
<input name="email" value="{$this->reseller.email}" type="hidden"></td>    
<input id="no_country_val" type="hidden" name="no_country" value="{if count($this->reseller.no_country)>0}1{else}0{/if}">
<h3>Country / Region</h3>
<input id="chk_nocountry" type="checkbox" {if $this->reseller.no_country}checked{/if}>Do not block country
<table id="zone-nocountry" {if count($this->reseller.no_country)>0}style="display:block;"{/if}>
	<tr><td>
			<select class="currency" name="no_country_currency">
				{foreach $this->currency_code as $cc}
					<option value="{$cc.code}" {if $cc.code eq $this->reseller.no_country.currency}selected="selected"{/if}>{$cc.desc} ({$cc.code})</option>
				{/foreach}
			</select>
			Subscription Price <input class="price" name="no_country_price" type="text" value="{$this->reseller.no_country.price|default:'0.00'|round:2}">
			Full Price <input class="price_full" name="no_country_price_full" type="text" value="{$this->reseller.no_country.price_full|default:'0.00'|round:2}">
		</td>
	</tr>
</table>

<ul id="zone-container" {if count($this->reseller.no_country)>0}style="display:none;"{/if}>
	{foreach $this->reseller.country_handled as $k=>$v}
		<li>
			<table>
			<tr>
			<td width="65">
				<button class="add-zone" type="button" onclick="addzone();">+</button>
				<button class="remove-zone" type="button" onclick="removezone(this);">-</button>
			</td>
			<td>
				<select class="currency" name="currency[]">
					{foreach $this->currency_code as $cc}
						<option value="{$cc.code}" {if $cc.code eq $v.currency}selected="selected"{/if}>{$cc.desc} ({$cc.code})</option>
					{/foreach}
				</select>
				Subscription Price <input class="price" name="price[]" type="text" value="{$v.price|default:'0.00'|round:2}">
				Full Price <input class="price_full" name="price_full[]" type="text" value="{$v.price_full|default:'0.00'|round:2}">
				&nbsp;
				<select name="country[]" onchange="change_country(this)">
					<option value="">Please select country</option>
					{foreach $this->country_list as $c}
						<option value="{$c.code}" {if $v.country eq $c.code}selected="selected"{/if}>{$c.country}</option>
					{/foreach}
				</select>
				</td>
			</tr>
			<tr class="zone-tr">
				<td></td>
				<td class="zone">
					<select class="multipleselect" name="regions[{$k}][]" multiple style="width:450px;">
						{foreach $this->country_list as $c}
							{if $v.country eq $c.code}
								{foreach $c.zone as $clist}
									<option value="{$clist}" {if in_array($clist, $v.regions)}selected="selected"{/if}>{$clist}</option>
								{/foreach}
							{/if}
						{/foreach}
					</select>
				</td>
			</tr>
			</table>
		</li>
		{/foreach}
		<li>
			<table>
			<tr>
			<td width="65">
				<button class="add-zone" type="button" onclick="addzone();">+</button>
				<button class="remove-zone" type="button" onclick="removezone(this);">-</button>
			</td>
			<td>

				<select class="currency" name="currency[]">
				{foreach $this->currency_code as $cc}
					<option value="{$cc.code}" {if $cc.code eq 'USD'}selected="selected"{/if}>{$cc.desc} ({$cc.code})</option>
				{/foreach}
				</select>
				Subscription Price <input class="price" name="price[]" type="text" value="0.00">
				Full Price <input class="price_full" name="price_full[]" type="text" value="0.00">
				&nbsp;
				<select name="country[]" onchange="change_country(this)">
					<option value="">Please select country</option>
					{foreach $this->country_list as $c}
						<option value="{$c.code}">{$c.country}</option>
					{/foreach}
				</select>
				
			</td>
			</tr>
			{*<tr>*}
			<tr class="zone-tr"><td></td><td class="zone"></td></tr>
			</table>
		</li>
	</ul>
</form>
<div id="zone" style="display:none;">
<table>
			<tr>
			<td width="65">
				<button class="add-zone" type="button" onclick="addzone();">+</button>
				<button class="remove-zone" type="button" onclick="removezone(this);">-</button>
			</td>
			<td>

				<select class="currency" name="currency[]">
				{foreach $this->currency_code as $cc}
					<option value="{$cc.code}" {if $cc.code eq 'USD'}selected="selected"{/if}>{$cc.desc} ({$cc.code})</option>
				{/foreach}
				</select>
				Subscription Price <input class="price" name="price[]" type="text" value="0.00">
				Full Price <input class="price_full" name="price_full[]" type="text" value="0.00">
				<select name="country[]" onchange="change_country(this)">
					<option value="">Please select country</option>
					{foreach $this->country_list as $c}
						<option value="{$c.code}">{$c.country}</option>
					{/foreach}
				</select>
				
			</td>
			</tr>
			{*<tr>*}
			<tr class="zone-tr"><td></td><td class="zone"></td></tr>
			</table>
</div>
<script type="text/javascript" src="../../js/chosen/chosen.jquery.min.js"></script>
<link rel="stylesheet" href="../../js/chosen/chosen.css" type="text/css">
<style>
#zone-nocountry{
	display:none;
}
</style>
<script>
$(function(){
	$('.multipleselect')
		.chosen()
		.trigger('liszt:updated');

	$('#chk_nocountry').change(function(){
		var nocountry = $(this).prop("checked");

		if (nocountry){
           	$('#zone-nocountry').fadeIn();
			$('#zone-container').fadeOut();
			$('#no_country_val').val("1");
		}
		else{
           	$('#zone-nocountry').fadeOut();
            $('#zone-container').fadeIn();
            $('#no_country_val').val("0");
		}
	});

// 	$('#signup').submit(function(){
// 		inputcheck();
// 	});
});

function inputcheck(){
	var retr = true;
	//no_country = parseInt($('#no_country_val').val());

	$('.required').each(function()
	{
		if($.trim($(this).val())=='')
		{
		    alert($(this).attr('title') + ' is required');
		    $(this).focus();
		    retr = false;
		}
	});
	if(!retr) return false;
}

function addzone(){
	var data = '<li>'+$('#zone').html()+'</li>';
	$('#zone-container').append(data);
};
function removezone(obj){
	$(obj).parent().parent().parent().parent().remove();
};

function change_country(obj){
	var code = $(obj).val(),
		timestamp_key = new Date().getTime();

	if ($.trim(code)=='')
		$(obj).parent().siblings('.zone').html('');
	else{
		$.post(php_self, { a:'change_country', code:code}, function(r){
			var str = '<select name="regions['+timestamp_key+'][]" multiple style="width:450px;">';
			for(var i=0; i<r.length; i++){
				str += '<option value="'+r[i]+'">'+r[i]+'</option>';
			}
			str += '</select>';

			$(obj).parent().parent().siblings('.zone-tr').find('.zone').html(str);
			$(obj).attr('name','country['+timestamp_key+']');
			$(obj).parent().find('.price').attr('name','price['+timestamp_key+']');
            $(obj).parent().find('.price_full').attr('name','price_full['+timestamp_key+']');
			$(obj).parent().find('.currency').attr('name','currency['+timestamp_key+']');
			$(obj).parent().parent().siblings('.zone-tr').find('.zone select').chosen();
		}, 'json');
	}
}
</script>
