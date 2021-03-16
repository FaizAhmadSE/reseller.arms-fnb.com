<style>
.table td { vertical-align:middle; }
</style>
<p style="font-size:0.9em;">{$this->totalresult|default:'0'} record(s) found.</p><br />

<table class="table table-bordered table-striped table-hover table-subscription-list" cellpadding="5">
	<thead>
		<tr>
			<th width="10%" style="text-align:center;">Added</th>
			<th width="10%" style="text-align:center;">Type</th>
			<th width="10%" style="text-align:center;">Code</th>
			<th width="10%" style="text-align:center;">Slots</th>
			<th>Customer Name</th>
			<th width="20%">Remark</th>
			<th width="10%" style="text-align:center;">Invoice No.</th>
			<th width="8%" style="text-align:center;">Used</th> 
		</tr>
	</thead>
	<tbody>
		{foreach $this->codes as $code}
			<tr {if in_array($code.serial_code, $smarty.session['new_added']['gsc'])}class="blink-text"{/if} data-id="{$code._id}" data-type="{$code.type|@lower}">
				<td style="text-align:center;">{$code._id->getTimestamp()|date_format:'%d-%b-%Y %H:%M:%S'}</td>
				<td style="text-align:center;">{$code.type|@ucfirst}</td>
				<td style="text-align:center;">{$code.serial_code}</td>
				<td style="text-align:center;">
					{if $code.type eq 'Device'}
						{if strlen($code.serial_code) lt 8}0{else}{substr($code.serial_code,-2)|intval}{/if}
					{else}
						{substr($code.serial_code,-3)|intval}
					{/if}
				</td>
				<td ondblclick="ed('customer_name',this)">{$code.customer_name}</td>
				<td ondblclick="ed('remark',this)">{$code.remark}</td>
				<td ondblclick="ed('invoice_no',this)" style="text-align:center;">{$code.invoice_no}</td>
				<td style="text-align:center;"><img src="/ui/icons16/{if $code.mac}tick{else}cross{/if}.png"></td>
			</tr>
		{foreachelse}
			<tr><td colspan="8">No Code...</td></tr>
		{/foreach}
	</tbody>
</table>
<center>
{pagination prepend="<p align=center class=pagination>Page " append="</p>" start=$this->startpage total=$this->totalpage function='load_serial_code'}
</center>

<textarea style="width: 150px; height: 50px; position: absolute; left: 819px; top: 223px; display: none;" id="ipe"></textarea>