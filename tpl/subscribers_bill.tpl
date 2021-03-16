<html>
<head>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.1/css/bootstrap.min.css">
</head>
<body>
<div class="container">
<br/>
<img src="images/arms-fnb-logo.png" />
<h2>Invoice</h2>
<p style="text-align:right">
<b>Invoice Date</b> : {$smarty.now|date_format:'%Y-%m-%d'}
</p>
<p>
<!--<b>License of {$this->monthStart|date_format:'%Y-%m-%d'} to {$this->monthLast|date_format:'%Y-%m-%d'}</b>-->
<b>License of </b>
<form id="date_form"> 
<input type="hidden" name="a" value="show">
<select id="bill_date" name="bill_date">
	{foreach $this->bill_date as $k=>$m}
		<option value="{$k}" {if $this->selected eq $k}selected{/if}>{$m.display}</option>
	{/foreach}
</select>
</form>
</p>
<table class="table">
	<!-- APP -->
	<tr>
		<th colspan="4" style="background-color:rgb(239, 239, 239)">App License</th>
	</tr>
	<tr>
	<th>Device</th>
	<th>Company Name</th>
	<th>Reseller</th>
	<th>Last Activated Date</th>
	</tr>
	
	{foreach $this->unique_device as $ud}
	<tr>
		<td>{$ud.device_id}</td>
		<td>{$ud.info.company_name}</td>
		<td>{$ud.info.contact_person}</td>
		<td>{$ud.added|date_format:'%Y-%m-%d'}</td>
	</tr>
	{/foreach}
	
	<tr>
		<td colspan="3" align='right'>Number of License :</td>
		<td>{$this->unique_device_total.qty}</td>
	</tr>
	<tr>
		<td colspan="3" align='right'>Total :</td>
		<td>USD {$this->unique_device_total.total|number_format:2}</td>
	</tr>
	
	<!-- CLOUD -->
	<tr>
		<th colspan="4" style="background-color:rgb(239, 239, 239)">Cloud License</th>
	</tr>
	<tr>
	<th>Device</th>
	<th>Company Name</th>
	<th>Reseller</th>
	<th>Last Activated Date</th>
	</tr>
	
	{foreach $this->unique_cloud as $ud}
	<tr>
		<td>{$ud.device_id}</td>
		<td>{$ud.info.company_name}</td>
		<td>{$ud.info.contact_person}</td>
		<td>{$ud.added|date_format:'%Y-%m-%d'}</td>
	</tr>
	{/foreach}
	
	<tr>
		<td colspan="3" align='right'>Number of License :</td>
		<td>{$this->unique_cloud_total.qty}</td>
	</tr>
	<tr>
		<td colspan="3" align='right'>Total :</td>
		<td>USD {$this->unique_cloud_total.total|number_format:2}</td>
	</tr>
	<!-- GRAND -->
	<tr style="background-color:rgb(239, 239, 239)">
		<td colspan="3" align='right'><b>Grand Total :</b></td>
		<td><b>USD {$this->grand_total|number_format:2}</b></td>
	</tr>
</table>
</div>
</body>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.4/jquery.min.js"></script>

<script>
$(function(){
	$('#bill_date').change(function(){
		$('#date_form').submit();
	});
});
</script>
</html>