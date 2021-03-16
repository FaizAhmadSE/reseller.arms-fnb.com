<b>{$this->errmsg}</b>
<form id="signup" method="post" {*if !$this->reseller}onsubmit="return signup()"{/if*}>
<input type="hidden" name="a" value="{if $this->reseller}update{else}signup{/if}">
<table class="table noTopBorder">
<tr><td><h3 style="margin:0">Reseller Profile</h3></td>
	<td></td>
</tr>
<tr><td style="vertical-align:top;">
	<table cellpadding="2" cellspacing="0" border="0" width="95%">
	<tr>
		<th width="250">Email <font color="red">*</font></th>
		<td><input name="email" value="{$this->reseller.email}" type="text" size="50" title="Email" {if $this->reseller}readonly="readonly"{else}class="required"{/if}></td>
	</tr>
	<tr>
		<th>Password <font color="red">*</font></th>
		<td><input name="p" size="50" value="" type="password"  title="Password" {if !$this->reseller}class="required"{/if}></td>
	</tr>
	<tr>
		<th>Company Name <font color="red">*</font></th>
		<td><input name="company_name" value="{$this->reseller.company_name}" type="text" size="50" title="Company Name" class="required"></td>
	</tr>
	<tr>
		<th>Company Registration Number</th>
		<td><input name="company_reg_no" value="{$this->reseller.company_reg_no}" type="text" size="50" title="Company Name"></td>
	</tr>
	<tr>
		<th>Contact Person <font color="red">*</font></th>
		<td><input name="contact_person" type="text" value="{$this->reseller.contact_person}" size="50" title="Contact Person" class="required"></td>
	</tr>
	<tr>
		<th>Phone</th>
		<td><input name="phone" value="{$this->reseller.phone}" type="text" size="50"></td>
	</tr>
	<tr>
		<th>Fax</th>
		<td><input name="fax" value="{$this->reseller.fax}" type="text" size="50"></td>
	</tr>
	<tr>
		<th>Website</th>
		<td><input name="website" value="{$this->reseller.website}" type="text" size="50"></td>
	</tr>
	<tr>
		<th>Address</th>
		<td><input name="address" value="{$this->reseller.address}" type="text" size="50"></td>
	</tr>
	<tr>
		<th>City</th>
		<td><input name="city" value="{$this->reseller.city}" type="text" size="50"></td>
	</tr>
	<tr>
		<th>Postcode</th>
		<td><input name="postcode" value="{$this->reseller.postcode}" type="text" size="50"></td>
	</tr>
	<tr>
		<th>Reseller Image</th>
		<td>
			<input name="reseller_image" value="{$this->reseller.reseller_image}" placeholder="eg: images/reseller/wsatp.png" type="text" size="50">
		</td>
	</tr>
	<input type="hidden" name="subscription" value="manual" />
	<input type="hidden" name="cloud" value="manual" />
	</table>
</td></tr>
{*
<tr><td><h3 style="margin:0">Reseller Permission</h3></td>
	<td></td>
</tr>
<tr><td style="vertical-align:top;">
	<table cellpadding=2 cellspacing=0 border=0 width=95%>
	
	<tr><th>Subscription</th>
		<td><div id="subsc">
				<input type="radio" id="subscription_y" name="subscription" value="yes" {if $this->reseller.subscription eq 'yes'}checked="checked"{/if}><label for="subscription_y">Yes</label>
				<input type="radio" id="subscription_n" name="subscription" value="no" {if $this->reseller.subscription eq  'no'}checked="checked"{/if}><label for="subscription_n">No</label>
                <input type="radio" id="subscription_f" name="subscription" value="full" {if $this->reseller.subscription eq  'full'}checked="checked"{/if}><label for="subscription_f">Lifetime</label>
			</div>
		</td>
	</tr>
    <tr><th>Slots</th>
		<td><input type="text" name="slots" value="{$this->reseller.slots}" onchange="check_slot(this);"></td>
	</tr>
	<tr><th>Cloud</th>
		<td><div id="radio">
				<input type="radio" id="self_renew" value="self_renew" name="cloud" {if $this->reseller.cloud eq 'self_renew'}checked="checked"{/if}><label for="self_renew">Self Renew</label>
				<input type="radio" id="top_up" value="top_up" name="cloud" {if $this->reseller.cloud eq 'top_up'}checked="checked"{/if}><label for="top_up">Top-up</label>
				<input type="radio" id="follow_subscription" value="follow_subscription" name="cloud" {if $this->reseller.cloud eq 'follow_subscription'}checked="checked"{/if}><label for="follow_subscription">Follow Subscription</label>
			</div>
			<input name="allow_warranty_register" id="allow_warranty_register" type="hidden" value="1" {if $this->reseller.allow_warranty_register eq 'yes'}checked{/if}>
		</td>
	</tr>
	
	<tr><th>PAYware</th>
		<td>
			<div id="radio">
				<input type="radio" id="payware_yes" value="yes" name="payware" {if $this->reseller.payware eq 'yes'}checked="checked"{/if}>
				<label for="payware_yes">Yes</label>
				<input type="radio" id="payware_no" value="no" name="payware" {if $this->reseller.payware eq 'no' || $this->reseller.payware eq ''}checked="checked"{/if}>
				<label for="payware_no">No</label>
			</div>
		</td>
	</tr>
	
	<tr><th>Generate Code</th>
		<td><div id="generate_code">
				<input type="radio" id="gcode_y" name="allow_generate_code" value="yes" {if $this->reseller.allow_generate_code eq 'yes'}checked="checked"{/if}><label for="gcode_y">Yes</label>
				<input type="radio" id="gcode_n" name="allow_generate_code" value="no" {if $this->reseller.allow_generate_code eq  'no'}checked="checked"{/if}><label for="gcode_n">No</label>
				&nbsp;&nbsp;Reseller can generate Serial Code
			</div>
			<input name="allow_generate_code" type="checkbox" value="yes" {if $this->reseller.allow_generate_code eq 'yes'}checked{/if}> reseller can generate Serial Code
		</td>
	</tr>
	<tr><th>&nbsp;</th>
		<td>Prefix <input id="serial_code_prefix" name="serial_code_prefix" maxlength="1" value="{$this->reseller.serial_code_prefix}" size="2"></td>
	</tr>
	{if $this->reseller.reseller_image}
		<tr>
			<td>&nbsp;</td>
			<td><img align="right" src="/{$this->reseller.reseller_image}" /></td>
		</tr>
	{/if}
	</table>
	</td>
</tr>
*}
</table>
</form>
<div id="zone" style="display:none;">
	<table>
		<tr>
			<td width="65">
				<button class="add-zone" type="button" onclick="addzone();">+</button>
				<button class="remove-zone" type="button" onclick="removezone(this);">-</button>
			</td>
			<td>
			<select name="country[]" onchange="change_country(this)">
				<option value="">Please select country</option>
				{foreach $this->country_list as $c}
					<option value="{$c.code}">{$c.country}</option>
				{/foreach}
			</select>
			<input class="price" name="price[]" type="text" value="0.00">
			<select class="currency" name="currency[]">
			{foreach $this->currency_code as $cc}
				<option value="{$cc.code}" {if $cc.code eq 'USD'}selected="selected"{/if}>{$cc.desc} ({$cc.code})</option>
			{/foreach}
			</select>
			</td>
			</tr>
			<tr class="zone-tr"><td></td><td class="zone"></td></tr>
	</table>
</div>
<script type="text/javascript" src="../../js/chosen/chosen.jquery.min.js"></script>
<link rel="stylesheet" href="../../js/chosen/chosen.css" type="text/css">
<script>

var $aa = '{$this->reseller.subscription}';
$(function() {
    //$( "#radio, #subsc, #generate_code" ).buttonset();
	_checkdis();
	
	$("#subscription_n").click(function(){
		$("#follow_subscription").attr("disabled",true);
		$('#follow_subscription:radio:checked').removeAttr('checked');
		$('#radio').buttonset("refresh");
		
		$("#allow_warranty_register").val('1');
	});
	$("#subscription_y").click(function(){
		$("#follow_subscription").attr("disabled",false);
		$("#allow_warranty_register").val('0');
	});
	
  });
  
function _checkdis()
{
	if($aa == 'no')
	{
		$("#follow_subscription").button("disable");
		$('#follow_subscription:radio:checked').removeAttr('checked');
		$('#radio').buttonset("refresh");
	}
}

function check_slot(obj){
    var reg = new RegExp('^[0-9]+$');

	if ($(obj).val()!='' && reg.test($(obj).val())===false){
		alert("Invalid Slots, only integer allowed.");
		$(obj).focus().val('');
		return false;
	}
}

</script>