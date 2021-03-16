<form action="reseller/" onsubmit="return generate_topup_code(this);" method="post">
  <table width="100%">
    <tr>
      <td colspan="2"><b>Customer Name</b></td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="cname" value="{$smarty.session.generate_topup_code_cname}" style="width:100%;" required="required"/></td>
    </tr>
    <tr>
      <td colspan="2"><b>Enter number of Code to generate</b></td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="number" value="{$smarty.session.generate_topup_code_number}" style="width:100%;" required="required"/></td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
      <td><b>Type</b></td>
      <td>
        <select name="type">
          <option value="cloud">Cloud</option>
        </select>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
      <td><b>Period</b></td>
      <td>
        <select name="period">
          <option value="1 Year">1 Year</option>
        </select>
      </td>
    </tr>
    <tr><td>&nbsp;</td></tr>
    <tr>
      <td colspan="2"><b>Remark</b></td>
    </tr>
    <tr>
      <td colspan="2"><input type="text" name="remark" value="{$smarty.session.generate_topup_code_remark}" style="width:100%;" /></td>
    </tr>
  </table>
</form>
