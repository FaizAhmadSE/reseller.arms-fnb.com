{capture assign=smarty_today}{$smarty.now|date_format:'%Y-%m-%d'}{/capture}

<p style="font-size:0.9em;">{$this->totalresult|default:'0'} record(s) found.</p><br />

<table class="table table-bordered table-striped table-hover table-subscription-list" cellpadding="5">
	<thead>
	<tr>
		<th width="6%">&nbsp;</th>
		<th style="text-align:center" width="12%">Device</th>
		<th style="text-align:center" width="30%">Company Name</th>
		<th style="text-align:center" width="8%">Reseller</th>
		<th style="text-align:center" width="10%">Zone</th>
		<th style="text-align:center;" width="10%">Start Date</th>
		<th style="text-align:center" width="12%">App</th>
		<th style="text-align:center" width="12%">Cloud</th>
	</tr>
	</thead>
	<tbody>
	{foreach $this->login_subscribers as $device}
		<tr data-id="{$device._id}">
			<td width="40">
				<a class="edit_this_device" title="Edit this device"><span class="ui-icon ui-icon-pencil edit_this_device-ui left"></span></a>
				&nbsp;<a class="this_device_history" title="Payment History"><span class="ui-icon ui-icon-search left"></span></a>
			</td>
			<!-- Device Code -->
			<td width="80" style="text-align:center;">
				<a class="this_device_history" title="Payment History">{$device._id}</a>
			</td>
			<!-- Company Name -->
			<td style="text-align:center;"><b>{$device.company_name}</b></td>
			
			<!-- Reseller -->
			<td style="text-align:center;">
			{foreach $this->sub_reseller as $key=>$sr}
				{if $key eq $device.reseller_id}
					{$sr.email}
				{/if}
			{/foreach}
			</td>
			
			<!-- ZONE -->
			<td style="text-align:center;">
				{$device.zone}
			</td>
			<!-- START DATE -->
			<td style="text-align:center; {if $device.expiry_date!='' && $device.expiry_date!='-' && $device.expiry_date<=$smarty_today}color:red;{/if}">
				{if $device.start_date}
					{$device.start_date|date_format:'%d-%b-%Y'|default:'-'}
				{else}
					-
				{/if}
			</td>
			
			<!-- APP -->
			<td width="50" style="text-align:center;">
				<div><input class="app_activate" type="checkbox" name="app_activate" value="1" {if $device.license_type eq "FULL" || $device.license_type eq "MANUAL"} checked {/if}/></div>
				<!-- FUTURE NEED CHANGE TO MANUAL WHEN CT UPLOAD NEW VER OF APP -->
			</td>
			
			<!-- CLOUD -->
			<td style="text-align:center;">
				{if $device.device_output.device_code}
				<div><input class="cloud_activate" type="checkbox" name="cloud_activate" value="1" {if $device.device_output.active eq 1} checked {/if}/> {$device.device_output.device_code} </div>{/if}

				{***CREATE CLOUD***}
				{if ($device.license_type=='MANUAL' OR $device.license_type=='FULL') && $device.expiry_date=='Lifetime'}
					{if !$device.device_output.device_code}
						<div><a class="button add_this_device_to_cloud" title="Add Cloud Account for #{$device._id}">Add Cloud</a></div>
					{/if}
				{else}
					-
				{/if}
			</td>
		</tr>
		
	{foreachelse}
		<tr><td colspan="12">No Device...</td></tr>
	{/foreach}
	</tbody>
</table>
<center>
{pagination prepend="<p align=center class=pagination>Page " append="</p>" start=$this->startpage total=$this->totalpage function='load_subscription'}
</center>

<script>

$(function(){
  	$( ".button, button" ).button();
});

</script>
