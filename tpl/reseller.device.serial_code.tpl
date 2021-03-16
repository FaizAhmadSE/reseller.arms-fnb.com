<!-- Change Logs -->
<!-- 2018/09/04 Brandon - Enhance License Type Radio Button into the form -->

<form action="reseller/" onsubmit="return {if $type eq serial}generate_serial_code(this){else}generate_slot_code(this){/if};" method="post">
  <table width="100%">
    <tr {if $type eq slot}style="display:none;"{/if}>
      <td colspan="2" height="30">
        <b>License Type</b> &nbsp;&nbsp;
        <input type="radio" name="license_type" value="FULL" required="required" {if $smarty.session.load_licensetype eq 'FULL'}checked{/if}/> Full &nbsp;
        <input type="radio" name="license_type" value="LITE" required="required" {if $smarty.session.load_licensetype eq 'LITE'}checked{/if}/> Lite
      </td>
    </tr>
    <tr>
      <td colspan="2"><b>Customer Name</b></td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="cname" value="{$smarty.session.load_cname}" style="width:100%;" required="required"/></td>
    </tr>
    <tr>
      <td colspan="2"><b>Invoice Number</b></td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="inv" value="{$smarty.session.load_inv}" style="width:100%;" required="required"/></td>
    </tr>
    <tr>
      <td colspan="2"><b>Enter number of Mobile Terminal slots (0-20):</b></td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="number" value="{$smarty.session.load_slots}" style="width:100%;" required="required"/></td>
    </tr>
    <tr>
      <td colspan="2"><b>Remark</b></td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="remark" value="{$smarty.session.load_remark}" style="width:100%;" /></td>
    </tr>
  </table>
</form>
