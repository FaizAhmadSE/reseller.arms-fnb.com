<table class="table table-striped table-hover table-subscription-list" width="100%">
	<tr class="ui-state-active">
	<th width="7%">&nbsp;</th>
	<th align="left">No</th>
	<th onclick="sort('active')" align="left">Active{$this->sarrow('active')}</th>
	<th>Clients</th>
	<th onclick="sort('email')" align="left">Email{$this->sarrow('email')}</th>
	<th onclick="sort('company_name')" align="left">Company Name{$this->sarrow('company_name')}</th>
	<th onclick="sort('company_reg_no')" align="left">Company Reg No{$this->sarrow('company_reg_no')}</th>
	<th onclick="sort('contact_person')" align="left">Contact Person{$this->sarrow('contact_person')}</th>
	<th onclick="sort('phone')" align="left">Phone{$this->sarrow('phone')}</th>
	<th onclick="sort('fax')" align="left">Fax{$this->sarrow('fax')}</th>
	<th onclick="sort('website')" align="left">Website{$this->sarrow('website')}</th>
	<th onclick="sort('city')">City{$this->sarrow('city')}</th>
	<th onclick="sort('last_login')">Last Login{$this->sarrow('last_login')}</th>
	<th onclick="sort('_id')">Added{$this->sarrow('_id')}</th>
	</tr>
	<tbody>
	{assign var=i value=$this->startpage*$this->pagesize-$this->pagesize}
	{foreach $this->resellerslist as $u}
	{assign var=i value=$i+1}
		<tr data-id="{$u._id}">
		<td> 
			{if $u.delete}
			<a class="left undel-agent"><span class="ui-icon ui-icon-refresh" title="Undelete Reseller"></span></a>
			{else}
			<a class="left del-agent"><span class="ui-icon ui-icon-trash" title="Delete Reseller"></span></a>
			{/if}
			<a class="left view-agent"><span class="ui-icon ui-icon-pencil" title="Edit Reseller"></span></a>
			{*<a class="left view-region"><span class="ui-icon ui-icon-suitcase" title="Edit Region"></span></a>*}
		</td>
		<td>{$i}</td>
		<td><input type=checkbox {if $u.active}checked{/if} onclick="activate(this)"></td>
		<td align="center">{if $u.no_of_clients>0}{$u.no_of_clients}{else}-{/if}</td>
		<td>{$u.email|default:'-'}</td>
		<td>{$u.company_name|default:'-'}</td>
		<td>{$u.company_reg_no|default:'-'}</td>
		<td>{$u.contact_person|default:'-'}</td>
		<td>{$u.phone|default:'-'}</td>
		<td>{$u.fax|default:'-'}</td>
		<td>{$u.website|default:'-'}</td>
		<td>{$u.city|default:'-'}</td>
		<td align="center">{$u.last_login|date_format:'%Y-%m-%d<br>%T'|default:'-'}</td>
		<td align="center">{$u._id|substr:0:8|hexdec|date_format:'%Y-%m-%d<br>%T'}</td>
		</tr>
	{/foreach}
	</tbody>
</table>

<center>{pagination prepend="<p align=center class=pagination>Page " append="</p>" start=$this->startpage total=$this->totalpage function='goto_page'}</center>
