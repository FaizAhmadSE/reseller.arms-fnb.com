<?
ini_set('display_errors',0);
error_reporting(0);
//error_reporting(E_ALL ^ E_NOTICE);
date_default_timezone_set("Asia/Kuala_Lumpur");

# temp hide, to bypass CF http/2 implementation.
/*if((strstr($_SERVER['HTTP_HOST'],'maximus'))||(strstr($_SERVER['HTTP_HOST'],'dyndns'))||(strstr($_SERVER['HTTP_HOST'],'10.1.1.200')))
{
	//define('IS_MAXIMUS', true);
}
else
{
	if(!$_SERVER["HTTPS"] && !stristr($_SERVER['HTTP_USER_AGENT'],"ARMS") && php_sapi_name() != 'cli') {
		$redir = "Location: https://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		header($redir);
		exit;
	}
}*/

$config['appname'] = 'arms-fnb';
$config['maildomain'] = 'ddns.my';
$config['mailsender'] = 'admin@ddns.my';
$config['mailfooter'] = "\n\n\n----------------------------\nSee you soon!";
$config['copyright'] = ' &copy; arms-fnb '.date('Y').'.';

//$config['db'] = 'arms-fnb';
//$config['db_user'] = 'arms';
//$config['db_password'] = 'Arms4512';
//$config['db_admin_arms'] = 'myhq';
//$config['db_admin_arms_user'] = 'user';
//$config['db_admin_arms_password'] = 'password';

//$config['db_host'] = 'aws-ap-southeast-1-portal.2.dblayer.com:16485,aws-ap-southeast-1-portal.1.dblayer.com:16485';
//$config['db_other'] = '?ssl=true';
$config['db_host'] = "localhost";
$config['db'] = 'arms-fnb';
$config['db_user'] = 'arms';
$config['db_password'] = 'Arms4512';
$config['mongodb_conn'] = 'mongodb://'.$config['db_user'].':'.$config['db_password'].'@'.$config['db_host'].'/'.$config['db'].$config['db_other'];

$config['db_admin_arms'] = 'fnb_cloud';
$config['db_admin_arms_user'] = 'arms';
$config['db_admin_arms_password'] = 'Arms4512';
$config['mongodb_admin_conn'] = 'mongodb://'.$config['db_admin_arms_user'].':'.$config['db_admin_arms_password'].'@'.$config['db_host'].'/'.$config['db_admin_arms'].$config['db_other'];


$config['login_url'] = URL_BASE_HREF.'index.php?a=login';
$config['admin_login_url'] = URL_BASE_HREF.'admin/index.php?a=login';
$config['default_language'] = 'en';
$config['languages'] = array('en'=>'English', 'au'=>'Australia', 'ch'=>'Chinese');
$config['settings']['reseller']['subscription-slot'] = 20;
$config['settings']['reseller']['lifetime-slot'] = 1;

// $config['settings']['paypal']['sandbox'] = 1;
// $config['settings']['paypal']['sandbox-account'] = 'nava_b@wsatp.com';
// $config['settings']['paypal']['sandbox-pdt'] = 'VU85Kzd09wPGj6KElMYgu85wRhwX9QRhEr6V1gioSRiEXIziHZJpNsULOe0';

$config['settings']['payment']['refno_prefix'] = 'R';

$config['settings']['payment']['paypal-sandbox'] = 0;
// $config['settings']['payment']['paypal-sandbox-account'] = 'nava_b@wsatp.com';
//$config['settings']['payment']['paypal-sandbox-api_user'] = 'nava_b_api1.wsatp.com';
//$config['settings']['payment']['paypal-sandbox-api_pwd'] = '1380084829';
$config['settings']['payment']['paypal-sandbox-account'] = 'drkoay-facilitator@wsatp.com';
$config['settings']['payment']['paypal-sandbox-api_user'] = 'drkoay-facilitator_api1.wsatp.com';
$config['settings']['payment']['paypal-sandbox-api_pwd'] = '1392710722';
$config['settings']['payment']['paypal-sandbox-api_signature'] = 'A..QlWWKIPNPqGGd2UXslNmSavf5Adal8Hi6Js2ZewnRw2HcuTeiE31A';
$config['settings']['payment']['paypal-sandbox-return_url'] = 'https://reseller.ddns.my/index.php';
//$config['settings']['payment']['paypal-sandbox-notify_url'] = 'http://maximus.ddns.my:3291/ipn.php';
$config['settings']['payment']['paypal-sandbox-notify_url'] = 'https://cloud.ddns.my/ipn.php';

$config['settings']['payment']['paypal']['old_account'] = 'paypal@wsatp.com';
$config['settings']['payment']['paypal']['account'] = 'paypal@arms.my';
$config['settings']['payment']['paypal']['api_user'] = 'paypal_api1.wsatp.com';
$config['settings']['payment']['paypal']['api_pwd'] = 'YZU8YL8PSMKRC28S';
$config['settings']['payment']['paypal']['api_signature'] = 'AFcWxV21C7fd0v3bYYYRCpSSRl31A3fbch29k6kHvuJTr3.DhtziLiV8';
$config['settings']['payment']['paypal']['return_url'] = 'https://reseller.ddns.my/index.php';
$config['settings']['payment']['paypal']['notify_url'] = 'https://cloud.ddns.my/ipn.php';

//pricing for device reseller 
$config['price_device']['lvl1'] = 7; //1-5000
$config['price_device']['lvl2'] = 6; //5001-10000
$config['price_device']['lvl3'] = 5; // >10000

//pricing for cloud reseller
$config['price_cloud']['lvl1'] = 2; 	//1-5000
$config['price_cloud']['lvl2'] = 1.5; //5001-100
$config['price_cloud']['lvl3'] = 1;   // >10000

if((strstr($_SERVER['HTTP_HOST'],'maximus')) || strstr($_SERVER['HTTP_HOST'],'10.1.1.200')){
  $config['api_path'] = "http://maximus:3291/";
}
else{
  $config['api_path'] = "https://cloud.ddns.my/";
}

$config['token']['key'] = "ARMSFnBKEY4CLNT!ARMSFnBKEY4ARMSC";
?>
