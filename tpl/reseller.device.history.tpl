<fieldset style="border: solid 1px #ddd;">
	<legend style="font-weight:bold;">Device Detail</legend>
	<table width="100%" style="font-size:0.9em;" cellpadding="5">
		<tr><td width="20%">Device ID</td><td>{$this->device_detail['_id']}</td></tr>
		<tr><td>Company Name</td><td>{$this->device_detail['company_name']}</td></tr>
        <tr><td>Added</td><td>{$this->device_detail['added']|date_format:'%d-%b-%Y %H:%M:%S'}</td></tr>
		<tr><td>License Type</td>
			<td>
				{if ($this->device_detail['license_type']=='FULL' && $this->device_detail['expiry_date']=='Lifetime') || $this->device_detail['license_type']=='SUBSCRIPTION' || $this->device_detail['license_type']=='MANUAL'}
					{$this->device_detail['license_type']|default:'-'}
				{else}
					-
				{/if}
			</td>
		</tr>
		<tr style="border:none;">
			<td>Expiry Date</td>
			<td>
				{if $this->device_detail['license_type']=='FULL' || $this->device_detail['expiry_date']=='Lifetime'}
					Lifetime
				{else}
					{$this->device_detail['expiry_date']|date_format:'%d-%b-%Y'|default:'-'}
				{/if}
			</td>
		</tr>
	</table>
</fieldset>

<style>
fieldset table tr{
    border-bottom: solid 1px #ddd;
}
</style>
<br />
{if $this->manual_device_activation_log }
<legend style="font-weight:bold;">Device History</legend>
<table class="table table-bordered table-hover reseller_input">
<tr>
	<th style="text-align:center">Activation Date</th>
	<th style="text-align:center">Status</th>
	<th style="text-align:center">Activate By</th>
</tr>
{foreach $this->manual_device_activation_log as $device}
<tr>
	<td style="text-align:center">{$device._id->getTimestamp()|date_format:'%d-%b-%Y %H:%M:%S'}</td>
	<td style="text-align:center; font-size:0.9em;">
		{if $device.action eq "activate"}
			<font color="green">Activated</font>
		{else}
			<font color="red">Deactivated</font>
		{/if}
	</td>
	<td>
		{foreach $this->resellers as $key=>$d}
			{if $device.by_reseller eq $key}
				{$d.contact_person}
			{/if}
		{/foreach}
	</td>
</tr>
{foreachelse}
	<tr><td colspan="5">No Record.</td></tr>
{/foreach}	
</table>
</br>
<!-- CLOUD TABLE -->
<legend style="font-weight:bold;">Cloud History</legend>
<table class="table table-bordered table-hover reseller_input">
<tr>
	<th style="text-align:center">Activation Date</th>
	<th style="text-align:center">Status</th>
	<th style="text-align:center">Activate By</th>
</tr>
{foreach $this->manual_cloud_activation_log as $device}
<tr>
	<td style="text-align:center">{$device._id->getTimestamp()|date_format:'%d-%b-%Y %H:%M:%S'}</td>
	<td style="text-align:center; font-size:0.9em;">
		{if $device.action eq "activate"}
			<font color="green">Activated</font>
		{else}
			<font color="red">Deactivated</font>
		{/if}
	</td>
	<td>
		{foreach $this->resellers as $key=>$d}
			{if $device.by_reseller eq $key}
				{$d.contact_person}
			{/if}
		{/foreach}
	</td>
</tr>
{foreachelse}
	<tr><td colspan="5">No Record.</td></tr>
{/foreach}	
</table>







{else}
<table class="table table-bordered table-hover reseller_input">
<tr>
	<th style="text-align:center">Refno</th>
	<th style="text-align:center">Created Date</th>
	{*<th>Email</th>*}
	<th style="text-align:center">Amount</th>
	<th style="text-align:center">Status</th>
    <th style="text-align:center">IPN Returned Date</th>
</tr>
{foreach $this->device as $device}
<tr>
	<td style="text-align:center">{$device.refno}</td>
	<td style="text-align:center">{$device._id->getTimestamp()|date_format:'%d-%b-%Y %H:%M:%S'}</td>
	<td style="text-align:center">
		{if $device.pdt_return}
			{$device.pdt_return.mc_currency} {$device.pdt_return.payment_gross|number_format:2}
		{elseif $device.currency && $device.price}
			{$device.currency} {$device.price|number_format:2}
		{else}
			-
		{/if}
	</td>
	<td style="text-align:center; font-size:0.9em;">
		{if $device.pdt_return.0}
			<font color="{if $device.pdt_return.0|upper =='SUCCESSFUL'}green{else}red{/if}">
				{$device.pdt_return.0|upper}
			</font>
		{else}
			{if $device.paid}
				<font color="green">SUCCESSFUL</font>
			{else}
				<font color="red">FAIL</font>
			{/if}
		{/if}
	</td>
	<td style="text-align:center">
        {if $device.ipn_return}
			{$device.ipn_return.date|replace:'0.00000000 ':''|date_format:'%d-%b-%Y %H:%M:%S'}
		{elseif $device.pdt_return}
			{$device.pdt_return.payment_date|date_format:'%d-%b-%Y %H:%M:%S'}
		{else}
			-
		{/if}
	</td>
</tr>
{foreachelse}
	<tr><td colspan="5">No Record.</td></tr>
{/foreach}
{/if}
</table>