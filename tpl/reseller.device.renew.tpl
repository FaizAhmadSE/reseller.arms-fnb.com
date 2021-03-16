{if ($this->login.subscription=='full' && $this->renew_device.price_full<=0) || ($this->login.subscription=='yes' && $this->renew_device.price<=0)}
	<br /><br /><br />
	<p align="center"><b>Sorry, there's something wrong with pricing.</b></p>
	<p align="center">Kindly <a href="/cms/contact" target="_blank">contact us</a> if this problem continue persists.</p>
{else}
<br><form name="renew_box" id="renew-box" target="_ifpay" method="post">
<input type="hidden" name="a" value="pay">
{*<input type="hidden" name="currency" value="{$this->renew_device.currency}">
<input type="hidden" name="price" class="price" value="{$this->renew_device.price|round:2}">
<input type="hidden" name="license_type" id="license_type" value="{$this->renew_device.license_type|default:'SUBSCRIPTION'}">
<input type="hidden" name="renewal_terms" class="renewal_terms">*}
<input type="hidden" id="exp_date" value="{$this->renew_device.expiry_date}">

{if $this->login.subscription=='full'}
	<input type="hidden" id="subscription_type" checked="checked" data-price="{$this->renew_device.price_full|round:2}" data-renewal_year="Lifetime" />
{elseif $this->login.subscription=='yes'}
	<input type="hidden" id="subscription_type" checked="checked" data-price="{$this->renew_device.price|round:2}" data-renewal_year="1 Year"/>
{/if}

<table class="table table-bordered table-hover reseller_input">
	<tr>
		<td>Device ID</td>
		<td><input type="text" name="id" value="{$this->renew_device._id}" readonly="readonly"></td>
	</tr>
	{if $this->login.email ne 'neilfbradley@hotmail.com'}
	<tr>
		<td>Expiry Date</td>
		<td><input type="text" id="expiry_date" value="{$this->renew_device.expiry_date}" readonly="readonly"></td>
	</tr>
	{/if}
	{**}<tr>
		<td>Renewal Terms</td>
		<td>
			<div id="radio">
	    		{if $this->login.subscription=='full'}
				<input type="radio" id="FULL" name="radio" checked="checked" data-price="{$this->renew_device.price_full|round:2}" data-renewal_year="Lifetime" /><label for="FULL">One-time</label>
			{/if}
			{if $this->login.subscription=='yes'}
				<input type="radio" id="SUBSCRIPTION" name="radio" checked="checked" data-price="{$this->renew_device.price|round:2}" data-renewal_year="1 Year"/><label for="SUBSCRIPTION">Annual Subscription</label>
			{/if}
	  		</div>
		</td>
 	</tr>
	<tr>
		<td>Renewal Year</td>
		<td><input class="renewal_year" type="text" value="1 Year" readonly="readonly"></td>
 	</tr>{**}
	<tr>
		<td>Amount</td>
		<td><input type="text" id="price_show" value="{$this->renew_device.currency} {$this->renew_device.price|round:2}" readonly="readonly"></td>
 	</tr>
	<tr>
		<td>Payment Method</td>
		<td><img src="/images/paypal.gif" /></td>
	</tr>
</table>
</form>
<iframe name="_ifpay" style="width:1px;height:1px;visibility:hidden"></iframe>

<script>
var currency = "{$this->renew_device.currency}";
$(function() {
	$( "#radio" ).buttonset();

	/**/if ($('#radio input:checked').length>0){
		upd_frmdata($('#radio input:checked'));
    }

	$('#radio input').live('click', function(){
        upd_frmdata($(this));
	});/**/

	upd_frmdata($('#subscription_type'));

});

function upd_frmdata(obj){
    assign_expiry_date(obj);

	$('.price').val( $(obj).attr('data-price') ); //
	$('.license_type').val( $(obj).attr('id') ); //
	$('#price_show').val( currency+' '+$(obj).attr('data-price') );
	$('.renewal_year, .renewal_terms').val( $(obj).attr('data-renewal_year') ); //
}

function assign_expiry_date(obj){
		if ($(obj).attr('data-renewal_year')!='Lifetime'){
			if ($("#exp_date").val()=='' || $("#exp_date").val()=='-')
	            var exp_date = new Date();
			else
				var exp_date = new Date($("#exp_date").val());

			exp_date.setMonth(exp_date.getMonth()+12);

			var d = new Date(exp_date);
		    var day = d.getDate();
		    var month = d.getMonth() + 1;
		    var year = d.getFullYear();
		    if (day < 10) {
		        day = "0" + day;
		    }
		    if (month < 10) {
		        month = "0" + month;
		    }
		    var date = day + "/" + month + "/" + year;
            $('#expiry_date').val(date);
		}
        else{
			$('#expiry_date').val($(obj).attr('data-renewal_year'));
		}
}
</script>
{/if}
