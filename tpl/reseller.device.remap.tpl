
<br><form name="remap_box" id="remap-box" method="post">
<input type="hidden" name="a" value="remap_vendor_id">
<input type="hidden" name="id" value="{$this->remap_device._id}">
<input type="hidden" name="curr_vendor_id" value="{$this->remap_device.vendor_id}">

<div>
<font color="red">Please remap with caution. Incorrectly done might results in license loss.</font>
</div>
<br />

<table class="table table-bordered table-hover reseller_input">
	<tr>
		<td width="30%">Device ID</td>
		<td>{$this->remap_device._id}</td>
	</tr>
	<tr>
		<td width="30%">Current Vendor ID</td>
		<td>{$this->remap_device.vendor_id}</td>
 	</tr>
	<tr>
		<td width="30%">New Vendor ID</td>
		<td><input style="width:95%" type="text" name="new_vendor_id" value=""></td>
 	</tr>
</table>
</form>
