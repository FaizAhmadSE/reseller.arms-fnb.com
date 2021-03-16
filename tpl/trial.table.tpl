<table class="table table-striped table-hover table-subscription-list" width="100%">
	<tr class="ui-state-active">
	{*<th width="7%">&nbsp;</th>*}
	<th align="left">No</th>
	{*<th onclick="sort('active')" align="left">Active{$this->sarrow('active')}</th>*}
	<th onclick="sort('email')" align="left">Email{$this->sarrow('email')}</th>
	<th onclick="sort('restaurant_name')" align="left">Restaurant Name{$this->sarrow('restaurant_name')}</th>
	<th onclick="sort('name')" align="left">Contact Person{$this->sarrow('name')}</th>
	<th onclick="sort('phone')" align="left">Phone{$this->sarrow('phone')}</th>
	<th align="left">Address</th>
	<th onclick="sort('city')">City{$this->sarrow('city')}</th>
	<th onclick="sort('postcode')">Postcode{$this->sarrow('postcode')}</th>
	<th onclick="sort('referral')">Referral{$this->sarrow('referral')}</th>
	<th onclick="sort('_id')">Added{$this->sarrow('_id')}</th>
	</tr>
	<tbody>
	{assign var=i value=$this->startpage*$this->pagesize-$this->pagesize}
	{foreach $this->trial_result as $u}
	{assign var=i value=$i+1}
		<tr data-id="{$u._id}">
		{*<td> 
			{if $u.delete}
			<a class="left undel-agent"><span class="ui-icon ui-icon-refresh" title="Undelete Reseller"></span></a>
			{else}
			<a class="left del-agent"><span class="ui-icon ui-icon-trash" title="Delete Reseller"></span></a>
			{/if}
			<a class="left view-agent"><span class="ui-icon ui-icon-pencil" title="Edit Reseller"></span></a>
		</td>*}
		<td>{$i}</td>
		{*<td><input type=checkbox {if $u.active}checked{/if} onclick="activate(this)"></td>*}
		<td>{$u.email|default:'-'}</td>
		<td>{$u.restaurant_name|default:'-'}</td>
		<td>{$u.name|default:'-'}</td>
		<td>{$u.phone|default:'-'}</td>
		<td>{$u.address1|default:'-'} {$u.address2}</td>
		<td>{$u.city|default:'-'}</td>
		<td>{$u.postcode|default:'-'}</td>
		<td align="center">{$u.referral}</td>
		<td align="center">{$u._id|substr:0:8|hexdec|date_format:'%Y-%m-%d<br>%T'}</td>
		</tr>
	{/foreach}
	</tbody>
</table>

<center>{pagination prepend="<p align=center class=pagination>Page " append="</p>" start=$this->startpage total=$this->totalpage function='goto_page'}</center>
