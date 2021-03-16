<form onsubmit="return update(this);" method="post">
<input type="hidden" name="a" value="update">
{if $this->add}<input type="hidden" name="add" value="1">{/if}
<input type="hidden" name="reseller_id" value="{$this->login._id}">
<table class="table table-bordered table-hover reseller_device_add reseller_input">
	<tr>
		<td width="40%">Device ID</td>
		<td><input name="_id" size="50" value="{$this->subscription._id}" type="text"  title="Reseller ID" readonly="readonly"></td>
	</tr>
	<tr>
		<td>Company Name</td>
		<td><input name="company_name" value="{$this->subscription.company_name}" title="Company Name" class="required"  type="text" size="50"></td>
	</tr>
	<tr>
		<td>Contact Person</td>
		<td><input name="contact_person" value="{$this->subscription.contact_person}" title="Contact Person" class="required"  type="text" size="50"></td>
	</tr>
	<tr>
		<td>Company Registration Number&nbsp;&nbsp;</td>
		<td><input name="company_reg_no" value="{$this->subscription.company_reg_no}" title="Company Registration Number" type="text" size="50"></td>
	</tr>
	<tr>
		<td>Email</td>
		<td><input name="email" value="{$this->subscription.email}" type="text" title="Email" class="required"  size="50"></td>
	</tr>
	<tr>
		<td>Website</td>
		<td><input name="website" value="{$this->subscription.website}" type="text" title="Website" size="50"></td>
	</tr>
	<tr>
		<td>Phone</td>
		<td><input name="phone" value="{$this->subscription.phone}" type="text" title="Phone" class="required" size="50"></td>
	</tr>
	<tr>
		<td>Address</td>
		<td><input name="address" value="{$this->subscription.address}" type="text" title="Address" class="required" size="50"></td>
	</tr>
	<tr>
		<td>City</td>
		<td><input name="city" value="{$this->subscription.city}" type="text" title="City" class="required" size="50"></td>
	</tr>
	<tr>
		<td>Postcode</td>
		<td><input name="postcode" value="{$this->subscription.postcode}" type="text" title="Postcode" class="required" size="50"></td>
	</tr>
	<tr>
		<td>Country</td>
		<td>
        	<select name="country" title="Country" class="required">
            	<option value="">-- Please Select --</option>
				{foreach from=$this->countries item=c}
                	<option {if $this->subscription.country eq $c}selected="selected"{/if} value="{$c}">{$c}</option>
                {/foreach}
            </select>
			{*<input name="country" value="{$this->subscription.country}" type="text" title="Country" class="required" size="50">*}
		</td>
	</tr>
</table>
{if $this->login.payware eq 'yes'}
	<b>PAYware Settings</b>
	<table class="table table-bordered table-hover reseller_device_add reseller_input">
		<tr>
			<td width="40%">Merchant ID</td>
			<td><input name="vc_merchant_id" value="{$this->subscription.vc_merchant_id}" type="text" title="Merchant ID" size="50"></td>
		</tr>
		<tr>
			<td>Username</td>
			<td><input name="vc_username" value="{$this->subscription.vc_username}" type="text" title="Username" size="50"></td>
		</tr>
		<tr>
			<td>Password</td>
			<td><input name="vc_password" value="{$this->subscription.vc_password}" type="text" title="Password" size="50"></td>
		</tr>
		<tr>
			<td>Merchant Key</td>
			<td><input name="vc_merchant_key" value="{$this->subscription.vc_merchant_key}" type="text" title="Merchant Key" size="50"></td>
		</tr>
	</table>
	{/if}
</form>