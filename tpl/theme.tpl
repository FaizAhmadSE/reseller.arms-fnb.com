<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xmlns:fb="http://ogp.me/ns/fb#">
<head>
<div id="blackout"></div>
<meta http-equiv="content-type" content="text/html; charset=utf-8">
{if $country eq 'ch'}
{config_load file="ch.conf"}
{else}
{config_load file="en.conf"}
{/if}
{if $this->config.settings.google['webmaster-id']}
<meta name="google-site-verification" content="{$this->config.settings.google['webmaster-id']}" />
{/if}
<meta name="robots" content="index,follow" />
<meta name="keywords" content="{$META_KEYWORDS|default:$this->meta_keywords|default:$this->config.settings.google['meta-keywords']}" />
<meta name="description" content="{$META_DESCRIPTION|default:$this->meta_description|default:$this->config.settings.google['meta-description']}" />
{if $this->config.settings.google['bing-id']}
<meta name="msvalidate.01" content="{$this->config.settings.google['bing-id']}" />
{/if}
<base href="{$smarty.const.URL_BASE_HREF}" />
{if !$this->is_admin and $this->config.settings.website['maintenance']}
<title>Maintenance | {$this->config.settings.website['website-name']|default:$this->config.appname|strip_tags}</title>
{else}
<title>{block name="title"}{$PAGE_TITLE|default:$this->page_title|strip_tags}{/block} | {$this->config.settings.website['website-name']|default:$this->config.appname|strip_tags}</title>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.min.js"></script>
<script type="text/javascript" src="framework/webox2.js"></script>
<script type="text/javascript" src="js/site.js"></script>
{if $this->config.settings.website['maintenance'] && !$this->is_admin}
<meta name="google-site-verification" content="{$this->config.settings.google['webmaster-id']}" />
{/if}
{if $this->page.url eq 'cms/payment-success'}<meta http-equiv="refresh" content="1;URL='{$smarty.const.URL_BASE_HREF}reseller/'">{/if}

{if $this->config.settings.google['analytics-web-property-id']}
<script type="text/javascript">var _gaq = _gaq || [];_gaq.push(['_setAccount', '{$this->config.settings.google['analytics-web-property-id']}']);_gaq.push(['_setDomainName', 'arms-fnb.com']);_gaq.push(['_trackPageview']); (function() { var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true; ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js'; var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s); })();</script>
{/if}
{/if}
<link href='//fonts.googleapis.com/css?family=Open+Sans:400,600,700,300' rel='stylesheet' type='text/css'>
<link rel="stylesheet" href="tpl/style.css" type="text/css">
<link href="js/css/screen.css" rel="stylesheet" type="text/css" media="screen" />
{*<link rel="shortcut icon" href="{$smarty.const.URL_BASE_HREF}files/{$this->config.settings.website.favicon}" />
<link rel="apple-touch-icon-precomposed" href="{$smarty.const.URL_BASE_HREF}files/{$this->config.settings.website.favicon}" />*}
<meta name="viewport" content="width=1100,user-scalable=yes" />
<script type="text/javascript" src="js/easySlider1.7.js"></script>
<script type="text/javascript">
	$(document).ready(function(){	
		$("#slider").easySlider({
			auto: true, 
			continuous: true,
			numeric: true
		});
	});
</script>
<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>
<link rel="stylesheet" href="//ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/flick/jquery-ui.css" type="text/css" />
{block name="header"}{/block}
</head>

<body>
{if !$this->is_admin and $this->config.settings.website['maintenance']}
<br><br>
<h1 align=center>{$this->config.settings.website['maintenance-message']|nl2br}</h1>
{else}
<div id="header">
  <div class="header_wrap">
  <a href="/" class="logo" title="ARMS F&B for iPad"></a>
  </div><!--wrap-->
{if $this->login}
	<div id="menu" class="ui-widget-content">
		<ul>
			<li><a href=""><span class="left ui-icon ui-icon-home"></span> Home</a></li>
			{if $this->login.sub_reseller_option eq "yes"}<li><a href="sub_reseller.php"><span class="left ui-icon ui-icon-note"></span> My Resellers</a></li>{/if}
			{if $this->login.sub_reseller_option eq "yes"}<li><a href="sub_reseller.php?page_type=trial"><span class="left ui-icon ui-icon-note"></span> Trial Customers</a></li>{/if}
			<li><a><span class="left ui-icon ui-icon-power"></span> You are <b>{$this->login.email}</b></a>
				<ul>
				<li><a class="noajax" onclick="$.Storage.remove('autologin');" href="index.php?a=logout">Logout</a></li>
				</ul>
			</li>
		</ul>
	</div>
{/if}
</div><!--header-->


<div class="wrap"><br/>{block name="content"}{/block}</div><!--wrap-->
<div class="wrap">
  <div class="both"></div>
  <br/><br/><br/>
  <div id="footer">
    <div class="nav">

  </div>
  <div class="both"></div>
</div><!--wrap-->
{/if}

{if $this->is_admin}
<div id="htmlsource" style="display:none"><textarea style="resize:none;width:99%;height:420px;"></textarea></div>
{/if}

{*<script type="text/javascript">
  var GoSquared = {};
  GoSquared.acct = "GSN-609820-G";
  (function(w){
    function gs(){
      w._gstc_lt = +new Date;
      var d = document, g = d.createElement("script");
      g.type = "text/javascript";
      g.src = "//d1l6p2sc9645hc.cloudfront.net/tracker.js";
      var s = d.getElementsByTagName("script")[0];
      s.parentNode.insertBefore(g, s);
    }
    w.addEventListener ?
      w.addEventListener("load", gs, false) :
      w.attachEvent("onload", gs);
  })(window);
</script>*}
</body>
</html>
{block name="footer"}
{/block}
{*
<div id="fb-root"></div>
<script>(function(d, s, id) {
  var js, fjs = d.getElementsByTagName(s)[0];
  if (d.getElementById(id)) return;
  js = d.createElement(s); js.id = id;
  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=141248879289405";
  fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
</script>
*}