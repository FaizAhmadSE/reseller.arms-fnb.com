<form action="reseller/" onsubmit="return unpair_device($(this).serialize(), true);" method="post">
	<input type="hidden" name="a" value="unpair_device">
	<input type="hidden" name="id" value="{$this->device_id}">
  <table width="100%">
    <tr>
      <td colspan="2">Please type <b>'CONFIRM'</b> to unpair this device #{$this->device_id}:</td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="confirm" value="" style="width:100%;"/></td>
    </tr>
  </table>
</form>