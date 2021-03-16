{capture assign=smarty_today}{$smarty.now|date_format:'%Y-%m-%d'}{/capture}

<p style="font-size:0.9em;">{$this->totalresult|default:'0'} record(s) found.</p><br />

<table class="table table-bordered table-striped table-hover table-subscription-list" cellpadding="5">
	<thead>
	<tr>
		<th width="6%">&nbsp;</th>
		<th style="text-align:center" width="12%">Device</th>
		<th style="text-align:center" width="20%">Company Name</th>
		<th style="text-align:center;" width="10%">Contact Person</th>
		<th style="text-align:center" width="8%">Amount</th>
		{if $this->login.email ne 'neilfbradley@hotmail.com'}<th style="text-align:center" width="10%">Expiry Date</th>{/if}
		<th style="text-align:center;" width="10%">License Type</th>
		<th style="text-align:center;" width="10%">Zone</th>
		<th style="text-align:center;" width="10%">Device Code</th>
		<th style="text-align:center" width="12%">Paired</th>
		<th>&nbsp;</th>
	</tr>
	</thead>
	<tbody>
	{foreach $this->login_subscribers as $device}
		<tr data-id="{$device._id}">
			<td width="40">
				<a class="edit_this_device" title="Edit this device"><span class="ui-icon ui-icon-pencil edit_this_device-ui left"></span></a>
				&nbsp;<a class="this_device_history" title="Payment History"><span class="ui-icon ui-icon-search left"></span></a>
			</td>
			<td width="80" style="text-align:center;">
				<a class="this_device_history" title="Payment History">{$device._id}</a>
			</td>
			<td style="text-align:center;"><b>{$device.company_name}</b></td>
			<td style="text-align:center;"><b>{$device.contact_person}</b></td>
			
			<!-- Amount -->
			<td style="text-align:center;">
				{if ($device.license_type eq 'FULL' && $device.expiry_date eq 'Lifetime') || $device.license_type eq 'SUBSCRIPTION' && $device.expiry_date}
					{$device.currency} {$device.price|default:'-'}
				{else}
					-
				{/if}</td>
			{if $this->login.email ne 'neilfbradley@hotmail.com'}
			
			<!-- Expire Date -->
			<td style="text-align:center; {if $device.expiry_date!='' && $device.expiry_date!='-' && $device.expiry_date<=$smarty_today}color:red;{/if}">
				{if $device.license_type eq 'FULL' || $device.expiry_date eq 'Lifetime'}
					Lifetime
				{elseif $device.expiry_date eq '' || $device.expiry_date eq '-'}
					-
				{else}
					{$device.expiry_date|date_format:'%d-%b-%Y'|default:'-'}
				{/if}
			</td>
			{/if}
			
			<!-- Licenese Type -->
			<td style="text-align:center;">
				{if ($device.license_type=='FULL' && $device.expiry_date=='Lifetime') || $device.license_type=='SUBSCRIPTION'}
					{$device.license_type|default:'-'}
				{else}
					-
				{/if}
			</td>
			
			<!-- Device Code -->
			<td style="text-align:center;">
				{if $device.device_output.device_code}{$device.device_output.device_code}<br />{/if}

				{***CREATE CLOUD***}
				{if ($device.license_type=='SUBSCRIPTION' && $device.expiry_date!='' && $device.expiry_date!='-' && $device.expiry_date>$smarty_today) ||
					($device.license_type=='FULL' && $device.expiry_date=='Lifetime')}
					{if !$device.device_output.device_code}
						<div><a class="button add_this_device_to_cloud" title="Add Cloud Account for #{$device._id}">Add Cloud</a></div>
					{/if}
				{else}
					-
				{/if}
			</td>
			
			<td style="text-align:center;">
				{$device.zone|default:'-'}
			</td>
			
			<!-- Pair -->
			<td style="text-align:center;">
				{if $device.device_output.device_code}
					{if $device.device_output.paired==1}
						<img src="/ui/icons16/tick.png">

						{if $this->login.cloud=='follow_subscription'}
							<br /><a class="unpair_device button" data-id="{$device._id}">Unpair now</a>
						{/if}
					{else}
						<img src="/ui/icons16/cross.png">
					{/if}
				{else}-{/if}
			</td>
			
			<!-- Renew -->
			<td width="50" style="text-align:center;">
				{if $device.license_type!='FULL' && $device.expiry_date!='Lifetime'}
					<div><a class="button renew_this_device">Renew</a></div>
					<p></p>
				{/if}

				<div><a class="button remap_vendor_id">Remap</a></div>
			</td>
		</tr>
	{foreachelse}
		<tr><td colspan="12">No Device...</td></tr>
	{/foreach}
	</tbody>
</table>
<center>
{pagination prepend="<p align=center class=pagination>Page " append="</p>" start=$this->startpage total=$this->totalpage function='load_subscription_exp'}
</center>

<script>

$(function(){
  	$( ".button, button" ).button();
});

</script>
