<div>
	<form class="search_frm {$func}_frm" style=" display: inline; " method="post" onsubmit="{$func}(0, this); return false;">
		Filter{if $func=='load_serial_code'} code{/if} by
        {if $func=='load_subscription' || $func=='load_subscription_exp'}
			<select class="search_select" name="filter">
				<option value="_id" {if $this->filter eq '_id'}selected="selected"{/if}>Device ID</option>
				<option value="company_name" {if $this->filter eq 'company_name'}selected="selected"{/if}>Company Name</option>
				<option value="email" {if $this->filter eq 'email'}selected="selected"{/if}>Email</option>
			</select>
		{/if}
		
        {if $func=='load_serial_code'}
			<select class="search_select" name="filter">
				<option value="" {if $this->filter=='' || $smarty.session['load_serial_code_selection']==''}selected="selected"{/if}>Show All Types</option>
				<option value="slots" {if $this->filter=='slots' || $smarty.session['load_serial_code_selection']=='slots'}selected="selected"{/if}>Slots</option>
				<option value="device" {if $this->filter=='device' || $smarty.session['load_serial_code_selection']=='device'}selected="selected"{/if}>Device</option>
			</select>
		{/if}

        {if $func=='load_topup_code'}
			<select class="search_select" name="filter">
				<option value="code" {if $this->filter eq 'code' || $smarty.session['load_topup_code_selection']==''}selected="selected"{/if}>Code</option>
				<option value="cname" {if $this->filter eq 'cname' || $smarty.session['load_topup_code_selection']=='cname'}selected="selected"{/if}>Customer</option>
			</select>
		{/if}
		<input class="search_input" type="text" name="keyword" value="{strip}
			{if $func=='load_subscription'}
				{$smarty.session['load_subscription_inputbox']}
			{elseif $func=='load_subscription_exp'}
				{$smarty.session['load_subscriptionex_inputbox']}
			{elseif $func=='load_serial_code'}
				{$smarty.session['load_serial_code_inputbox']}
			{elseif $func=='load_topup_code'}
				{$smarty.session['load_topup_code_inputbox']}
			{else}
				{$this->inputstr}
			{/if}
			{/strip}">
		
		{if $this->login.sub_reseller_option eq "yes"}
		Resellers: 
		<select name="reseller_filter" class="search_select">
			<option value="all">All</option>
			{foreach $this->sub_reseller as $key => $d}
				<option value="{$key}">{$d.email}</option>
			{/foreach}
		</select>
		{/if}
		<button class="button">Search</button>
	</form>
</div>