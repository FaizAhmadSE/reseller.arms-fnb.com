<p style="font-size:0.9em;">{$this->totalresult|default:'0'} record(s) found.</p><br />

<table class="table table-bordered table-striped table-hover table-subscription-list" cellpadding="5">
<thead>
    <tr>
        <th width="10%" style="text-align:center;">Added</th>
        <th width="10%" style="text-align:center;">Type</th>
        <th width="10%" style="text-align:center;">Code</th>
		<th >Customer</th>
        <th width="20%">Remark</th>
        <th width="10%" style="text-align:center;">Period</th>
        <th width="8%" style="text-align:center;">Used</th>
    </tr>
</thead>
<tbody >
    {foreach $this->topup_code as $code}
    <tr {if in_array($code.code, $smarty.session['new_added']['topup_code'])}class="blink-text"{/if}>
        <td style="text-align:center;">{$code.added_date|date_format:'%d-%b-%Y %H:%M:%S'}</td>
        <td style="text-align:center;">{$code.type|@ucfirst|default:'-'}</td>
        <td style="text-align:center;">{$code.code}</td>
		<td>{$code.cname}</td>
        <td>{$code.remark}</td>
        <td style="text-align:center;">{$code.period}</td>
        <td style="text-align:center;"><img src="/ui/icons16/{if $code.used}tick{else}cross{/if}.png"></td>
    </tr>
	{foreachelse}
		<tr><td colspan="7">No Code...</td></tr>
	{/foreach}
</tbody>
</table>
<center>
{pagination prepend="<p align=center class=pagination>Page " append="</p>" start=$this->startpage total=$this->totalpage function='load_topup_code'}
</center>
