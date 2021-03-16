{extends file="ts:theme.tpl"}
{block name="content"}<br><br><br>
<div class="ui-corner-all ui-widget-content" style="margin: 1em auto;width: 380px;padding: 1em;">
 <h1>Reseller Login</h1>
  <div id="login" class="reseller_input">
  <form onsubmit="login();return false;" method="post">
	{if $this->login_err}
	  <div class="ui-state-error ui-corner-all" style="padding:0.5em">
		<span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span>
		<b>Error:</b> {$this->login_err}
	  </div>
	{/if}
	<input type="hidden" name="a" value="login">
	<input type="hidden" name="redir" value="reseller/">
	<table>
		<tr>
			<td>Email</td>
			<td><input size="30" name="e" type="email" placeholder="Email" class="required" autocomplete="on" value=""></td>
		</tr>
		<tr>
			<td>Password</td>
			<td><input size="30" name="p" type="password" placeholder="Password" class="required" autocomplete="off" value=""></td>
		</tr>
		<tr>
			<td colspan=2>
				<span class="login-status"></span><button class="button right" type=submit><img src="/ui/icons16/user-black.png" align=absmiddle /> Login</button><br>

			</td>
		</tr>
	</table>
	</form>
	</div>
</div>
{/block}

{block name="footer"}
<style>
a.button, button .ui-button-text { font-size:12px; }
a.button:hover, button:hover { color:#fff; }
#login table { margin:0 auto; }
#login table td { padding:10px; }
.login-status { margin-top: 5px;display: block;float: left; }
</style>
<script>
$(function() {
	$( ".button, button" ).button();
});
function login(is_auto)
{
	if (!is_auto && !check_required($('#login form').get(0),'.login-status'))
		return false;

	$('.login-status').html('Logging in...');

	var data = is_auto ? $.Storage.get('autologin') : $('#login form').serialize();

	$.post($('#login form').attr('action'), data, function(r){
		if (r=='OK')
		{
			$('.login-status').html('Login successful');
			document.location.reload();
		}
		else{
			//alert(r);
			$('.login-status').html(r);
		}
	});

	return false;
}

function check_required(f,div)
{
	var ret = true;
	$(f).find('input.required, input[required], select[required]').not(':checkbox')
		.each(function(){
			if ($(this).val()=='')
			{
				if (div!=undefined)
					$(div).html(($(this).attr('title')?$(this).attr('title'):$(this).attr('placeholder'))+' is required');
				else
					alert(($(this).attr('title')?$(this).attr('title'):$(this).attr('placeholder'))+' is required');
				$(this).focus();
				ret = false;
				return false;
			}
		});

	return ret;
}

</script>
{/block}