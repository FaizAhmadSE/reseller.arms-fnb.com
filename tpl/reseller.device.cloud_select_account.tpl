<form action="reseller/" onsubmit="return create_cloud_account($(this).serialize());" method="post">
<input type="hidden" name="a" value="add_device_to_cloud">
<input type="hidden" name="id" value="{$this->subscription._id}">
<table class="table table-bordered table-hover reseller_device_add reseller_input">
	<tr>
		<td width="30%">Device ID</td>
		<td>{$this->subscription._id}</td>
	</tr>
	<tr>
		<td>Company Name</td>
		<td>{$this->subscription.company_name}</td>
	</tr>
	<tr>
		<td>Contact Person</td>
		<td>{$this->subscription.contact_person}</td>
	</tr>
	<tr>
		<td>Email</td>
		<td>{$this->subscription.email}</td>
	</tr>
	<tr>
		<td>Accounts</td>
		<td>
			<select name="db">
				<option value="CREATE_NEW">Create New Account</option>
				{foreach $this->accounts as $v}
					<option value="{$v.db}">{$v.company_name}</option>
				{/foreach}
		</td>
	</tr>
	<tr><td>Cloud Device Subscription Period</td>
		<td>{if $this->login.cloud=='follow_subscription'}
				Follow license
			{else}
				3 Months
			{/if}
		</td>
	</tr>
</table>
</form>