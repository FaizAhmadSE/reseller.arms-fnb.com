<?php
## Change Logs ##
// 2018/09/04 Brandon - Enhance generate_serial_code() function, add license type variable into the function.
// 2018/09/04 Brandon - Enhance gsc() function, add license type variable into db and add postfix for LITE version.

// cms module
require_once("framework/include.php");

ini_set("display_errors",0);
error_reporting(0);
define("LOG_FILE", "ipn.log");

class Reseller extends Module
{
	var $page_title = 'Reseller';
	var $pagesize=50;

	function init()
	{
		if ($_REQUEST['a']!='check_payment'){
			$this->login = $this->db->resellers->findOne(array('_id'=>$this->login['_id']));

			if ($_REQUEST['a']!='login' && !$this->login)
			{
				$this->login();
				exit;

			}
			
        }
	}

	function _default()
	{
		unset($_SESSION['new_added']);
		$this->list_device();
	}

	// list device and codes
	function _sort_MongoId($a, $b)
	{
		if ($a['_id'] > $b['_id']) return -1;
		if ($a['_id'] < $b['_id']) return 1;
		return strcmp($a['serial_code'], $b['serial_code']);
	}

	function list_device($r="")
	{
		if ($r['renew_new_device'])
			$this->tpl->assign('new_device', $r['renew_new_device']);
        else{
			if ($r['msg'])
				$this->tpl->assign('msg', $r['msg']);
		}
		
		if($this->login['sub_reseller_option'] == "yes" || $this->login['subscription'] == "manual")
		$this->sub_reseller = $this->db->resellers->find(array('$or'=>array(array('parent'=>$this->login['_id']),array('_id'=>$this->login['_id']))));

		$this->display('reseller.page.tpl');
	}


	function login()
	{
		$this->page_title = "Login";
		//$this->login_err = "Invalid Email or Password";

		if (isset($_POST['e']) && isset($_POST['p']))
		{
			// validate
			$ret = $this->db->resellers->findOne(array('email'=>$_POST['e'],'password'=>md5($_POST['p'])));

			if ($ret)
			{

				if ($ret['delete'])
			    {
					print ("Inactive account!");
				}
				elseif (!$ret['active'])
			    {
					print ("Inactive account!");
				}
				else
				{
					$this->db->resellers->update(array('_id'=>$ret['_id']),array('$set'=>array('last_login'=>time(),'ip'=>$_SERVER['REMOTE_ADDR'])));
					$this->set_login($ret);

					if (isset($_REQUEST['redir']))
						print "OK";
					else
						$this->redir(URL_BASE_HREF);
					exit;
				}
			}
			else {
				print ("Invalid Email or Password");
			}
		}
		else{		
			if($this->login){
				if (isset($_REQUEST['redir'])){
					$this->redir(URL_BASE_HREF.$_REQUEST['redir']);
					exit;
				}
			}else{
				$this->display('reseller.login.tpl');
			}
		}
		if ($ajax) return false;
	}

	function logout()
	{
		$this->unset_login();
		$this->redir(URL_BASE_HREF);
	}

	function check_device()
	{
		$this->display('reseller.device.tpl');
	}

	function load_device()
	{
		$this->id = $_REQUEST['id'];
		$device = $this->db->subscribe_user->findOne(array('_id' => $_REQUEST['id'],'license_type' =>null,'reseller_id' =>null));
		$check_device_reseller = $this->check_location($device['country_code'],$device['zone']);
		if ($check_device_reseller){
			//if valid, then check got existing purchase slot record
			$dummycheck = $this->db->purchased_slots->findOne(array('_id'=>$device['mac']));
			if ($dummycheck)
				print 'EXISTING_SLOT';
			else
                print 'OK';
		}
	}

	function add_device()
	{
		global $countries;
		
		$this->countries = $countries;
		$this->subscription = $this->db->subscribe_user->findOne(array('_id' => $_REQUEST['id']));
		$this->add = (isset($_REQUEST['add'])? 1 : 0 );
		$this->display('reseller.device.add.tpl');
	}



	function device_history(){
		$this->device_detail = $this->db->subscribe_user->findOne(array("_id" => $_REQUEST['id']));
		if($this->device_detail['license_status'] == "MANUAL"){
		$this->manual_device_activation_log = $this->db->manual_device_activation_log->find(array('device_id' => $_REQUEST['id']))->sort(array('_id'=>-1));
		$this->manual_cloud_activation_log = $this->db->manual_cloud_activation_log->find(array('device_id' => $_REQUEST['id']))->sort(array('_id'=>-1));
		$this->resellers = $this->db->resellers->find(array('$or'=>array(array('sub_reseller_option' => 'yes'),array('subscription'=>'manual'))));
		}
		$this->device = $this->db->payment_history->find(array('device_id' => $_REQUEST['id']))->sort(array('_id'=>-1));
		
		$this->display('reseller.device.history.tpl');
	}

	function update()
	{	
		$id = $_POST['_id'];
		$res =  $this->db->resellers->findOne(array('_id'=>$this->login['_id']));
		$d = $this->db->subscribe_user->findOne(array('_id'=>$id));
		if  ($d['reseller_id']==''){
			$ret['status'] = 'NEW';
			$ret['msg'] = 'New device #'.$id.' added';
		}
		// can save
		$save = array();
		$save['reseller_id']	= $_POST['reseller_id'];
		$save['company_name']	= $_POST['company_name'];
		$save['contact_person'] = $_POST['contact_person'];
		$save['company_reg_no'] = $_POST['company_reg_no'];
		$save['website']		= $_POST['website'];
		$save['email']			= $_POST['email'];
		$save['phone']			= $_POST['phone'];
		$save['address']		= $_POST['address'];
		$save['city']			= $_POST['city'];
		$save['postcode']		= $_POST['postcode'];
		$save['country']    	= $_POST['country'];
		$save['added']		    = time();
		if($this->login['sub_reseller_option'] == "yes" || $this->login['subscription'] == "manual")
		$save['license_status'] = "MANUAL";
		
		if ($this->login['payware'] == "yes"){ 
			$save['vc_merchant_id']    	= $_POST['vc_merchant_id'];
			$save['vc_username']    	= $_POST['vc_username'];
			$save['vc_password']    	= $_POST['vc_password'];
			$save['vc_merchant_key']    = $_POST['vc_merchant_key'];
		}
		
		if(isset($res['parent'])){
			$save['parent_reseller_id'] = (string)$res['parent'];
		}
		
		$this->db->subscribe_user->update(array('_id'=>$id), array('$set'=>$save));
		//$this->renew_new_device =  $this->db->subscribe_user->findOne(array('_id' =>$id));
		if  ($d['reseller_id']==''){
			
			//Add log for the reseller that is manual when add device id
			if($this->login['sub_reseller_option'] == "yes" || $this->login['subscription'] == "manual")
			{
				$save_device_log['subscribe_user_id'] = $id;
				$save_device_log['reseller_id'] = $_POST['reseller_id'];
				$save_device_log['parent_reseller_id'] = isset($res['parent']) ? (string)$res['parent'] : "";
				$save_device_log['added'] = time();
				$save_device_log['ip'] = $_SERVER['REMOTE_ADDR'];
				$this->db->manual_add_device_log->save($save_device_log,array('fsync'=>1));
				
			}else
				$ret['renew_new_device'] = $this->db->subscribe_user->findOne(array('_id' =>$id));
		}
		
		/*Activate on first time Add Device FOR MANUAL*/
		if(isset($_POST['add']) && ($this->login['sub_reseller_option'] == "yes" || $this->login['subscription'] == "manual") ){
			$action = "activate";
			$this->app_activation($action,$id);
		}
		
		if (!$ret){

			$ret['status'] = 'UPDATE';
			$ret['msg'] = 'Updated for device #'.$id;

		}
		
		
		$this->list_device($ret);

	}

	private function generate_ref_id()
	{
		while(1) // danger -- infinite loop!
		{
			$ref_id = $this->config['settings']['payment']['refno_prefix'].rand(100000,999999);
			$this->db->payment_history->ensureIndex(array('refno'=>1));
			$ff = $this->db->payment_history->findOne(array('refno'=>$ref_id));
			if (!$ff) return $ref_id;
		}
	}

	private function check_location($country, $zone)
	{

		//$reseller = $this->db->resellers->findOne(
		//	array(
		//		'$or' => array(
		//			array('_id'=>$this->login['_id'], 'country_handled.country' => $country, 'country_handled.regions' => array()),
		//			array('_id'=>$this->login['_id'], 'country_handled.country' => $country, 'country_handled.regions' => $zone),
		//		)
		//	)
		//);

		$reseller = $this->db->resellers->findOne(array('_id'=>$this->login['_id'] ));

		$result = array();
		//$store = array();

		if (count($reseller['no_country'])>0){
			$result['price'] = $reseller['no_country']['price'];
			$result['price_full'] = $reseller['no_country']['price_full'];
			$result['currency'] = $reseller['no_country']['currency'];
		}
		else{
			foreach ($reseller['country_handled'] as $k=>$c){

				if($country == $c['country']){

					foreach($c['regions'] as $ky=>$ite){
						if($ite == $zone){
						//$store = array($c['country'], $c['regions'],$c['price'],$c['currency']);
						//$fields = array('country', 'zone', 'price', 'currency');
						//$result[] = array_combine ($fields , $store);
						$result['price'] = $c['price'];
						$result['price_full'] = $c['price_full'];
						$result['currency'] = $c['currency'];
						}

					}
					if(EMPTY($c['regions'])){
						$result['price'] = $c['price'];
						$result['price_full'] = $c['price_full'];
						$result['currency'] = $c['currency'];
					}
				}
			}
		}
		return ($result);
	}

	function remap()
	{
		$id = $_REQUEST['id'];

		$this->remap_device = $this->db->subscribe_user->findOne(array('_id' => $id));

		$this->display('reseller.device.remap.tpl');

	}

	function remap_vendor_id()
	{
		$id = $_REQUEST['id'];
		$curr_vendor_id = $_REQUEST['curr_vendor_id'];
		$new_vendor_id = $_REQUEST['new_vendor_id'];

		$device = $this->db->subscribe_user->findOne(array('_id' => $id));

		if(!$device) die("Error");


		// Swap vendor ID with the record carrying new vendor ID as its vendor_id if there is any

		$corr_device = $this->db->subscribe_user->findOne(array('vendor_id' => $new_vendor_id));

		if($corr_device)
		{
			$corr_id = $corr_device['_id'];

			$set['old_vendor_id'] = $new_vendor_id;
			$set['vendor_id'] = $curr_vendor_id;

			$this->db->subscribe_user->update(array('_id' => $corr_id),  array('$set'=>$set));
		}

		// Update selected record's vendor ID

		$set['old_vendor_id'] = $curr_vendor_id;
		$set['vendor_id'] = $new_vendor_id;

		$this->db->subscribe_user->update(array('_id'=>$id),  array('$set'=>$set));


		$this->list_device();

		echo '<script language="javascript">';
		echo 'alert("Vendor ID has been successfully remapped.")';
		echo '</script>';

	}

	function renew()
	{
		$id = $_REQUEST['id'];

		$this->renew_device = $this->db->subscribe_user->findOne(array('_id' => $id));

		$priceCurrency = $this->check_location($this->renew_device['country_code'],$this->renew_device['zone']);

		//print_r($priceCurrency);
		//$ret = $this->check_location($this->renew_device['country_code'],$this->renew_device['zone']);
		////var_dump ($ret);
		//foreach ($ret['country_handled'] as $k=>$c){
		//	if ($c['country']==$this->renew_device['country_code'] && in_array($this->renew_device['zone'], $c['regions']))
		//	{
		//		$this->renew_device['currency'] = $ret['country_handled'][$k]['currency'];
		//		$this->renew_device['price'] = $ret['country_handled'][$k]['price'];
		//		//print $ret['country_handled'][$k]['currency'].'<br />Price: '.$ret['country_handled'][$k]['price'];
		//	}
		//}
		//
		$this->renew_device = array_merge($this->renew_device, $priceCurrency);
		//print "<pre>";print_r($this->renew_device);print "</pre>";
		$this->display('reseller.device.renew.tpl');

	}

	function pay()
	{
		/*

		if (strtoupper($_REQUEST['renewal_terms'])!='Lifetime' && $_REQUEST['expiry_date']=='')
			die("Error: expiry date is null.");*/
		$id = $_REQUEST['id'];

		$device = $this->db->subscribe_user->findOne(array('_id' => $id));

		if(!$device) die("Error");

		$priceCurrency = $this->check_location($device['country_code'],$device['zone']);


		if($this->login['subscription']=='full'){
			$price=$priceCurrency['price_full'];
			$renewal_terms="Lifetime";
			$expiry_date="Lifetime";

			$license_type="FULL";
		}
		elseif($this->login['subscription']=='yes'){
			$price=$priceCurrency['price'];
			$renewal_terms="1 Year";

			if(!isset($device['expiry_date']) || $device['expiry_date']==""){
				$expiry_date=date('d/m/Y',strtotime("+1 Year"));
			}
			else{
				$expiry_date=date('d/m/Y',strtotime($device['expiry_date']." +1 Year"));
			}

			$license_type="SUBSCRIPTION";
		}
		else{
			die("Error");
		}

		$save = array();
		$save['refno']			= $this->generate_ref_id();
		$save['currency']		= $priceCurrency['currency'];
		$save['price']			= $price;
		$save['license_type']	= ($device['license_type']!="")?$device['license_type']:$license_type;
		$save['renewal_terms']	= $renewal_terms;
		$save['device_id']		= $id;
		$save['expiry_date'] = $expiry_date;

		$this->db->payment_history->save($save,array('fsync'=>1));

		//$set['currency'] = $save['currency'];
		//$set['price'] = $save['price']	;
		//$set['license_type'] = $save['license_type'];
		//$this->db->subscribe_user->update(array('_id'=>$id),  array('$set'=>$set));

		$this->payment_express($save);
	}

	//RENEW IS THIS
	private function payment_express($order)
	{
		//Real paypal url: https://www.paypal.com/cgi-bin/webscr
		$URL_BASE_HREF = URL_BASE_HREF;

		if($this->config['settings']['payment']['paypal-sandbox']){
			$paypal = "https://www.sandbox.paypal.com/cgi-bin/webscr";
			$account = $this->config['settings']['payment']['paypal-sandbox-account'];
			$return_url = $this->config['settings']['payment']['paypal-sandbox-return_url'];
			$notify_url = $this->config['settings']['payment']['paypal-sandbox-notify_url'];
		}
		else{
			$paypal = "https://www.paypal.com/cgi-bin/webscr";
			$account = $this->config['settings']['payment']['paypal']['account'];
			$return_url = $this->config['settings']['payment']['paypal']['return_url'];
            $notify_url = $this->config['settings']['payment']['paypal']['notify_url'];
		}

		print <<< EOL_PAYPAL
<form target=_top method="post" action="{$paypal}">
<input name="cmd" value="_xclick">
<INPUT name="charset" value="utf-8">
<input name="business" value="{$account}">
<input name="invoice" value="{$order['refno']}">
<input name="cbt" value="You must click here to authorize your payment.">
<input name="currency_code" value="{$order['currency']}">
<input name="return" value='{$return_url}'>
<input name="cancel_return" value='{$URL_BASE_HREF}index.php?a=paypal_cancel_return'>
<input name="upload" value="1">
<input name="no_shipping" value="1">
<input name="item_name" value="ARMS FNB {$order['license_type']} Device ID:{$order['device_id']}">
<input name="amount" value="{$order['price']}">
<input name="quantity_1" value="1">
<input type="hidden" name="notify_url" value="{$notify_url}">
<input type=submit>
</form>
EOL_PAYPAL;

		// must have <form name=checkout_form> and <button name=checkout_button>
		print <<< REDIR_SCRIPT
<script>
document.forms[0].submit();
</script>
REDIR_SCRIPT;
	}

	private function payment_paypal($order)
	{
		$this->payment_data = $order['_id'].".".base64_encode($order['refno']);

		if ($this->config['settings']['paypal']['sandbox']) {
			$url = "https://www.sandbox.paypal.com/cgi-bin/webscr";
// 			$paypal_email = "admin_1357715638_biz@wsatp.com";
// 			$paypal_pdt = "_kEPuaJH1RjZdIQk0NvfFumY88QEDQN3iTr8peXBiUGGTSgfHY2qo6lAPee";

			$paypal_email = $this->config['settings']['paypal']['sandbox-account'];
			$paypal_pdt = $this->config['settings']['paypal']['sandbox-pdt'];
		}
		else {
			$url = "https://www.paypal.com/cgi-bin/webscr";
			$paypal_email = $this->config['settings']['paypal']['paypal-email'];
			$paypal_pdt = $this->config['settings']['paypal']['paypal-pdt'];
		}

$URL_BASE_HREF = URL_BASE_HREF;
print <<< EOL_PAYPAL
<form target="_top" method="post" action="$url">
<input name="cmd" value="_cart">
<INPUT name="charset" value="utf-8">
<input name="business" value="{$paypal_email}">
<input name="invoice" value="{$order['refno']}">
<input name="cbt" value="You must click here to authorize your payment.">
<input name="currency_code" value="{$_REQUEST['currency']}">
<input name="return" value='{$URL_BASE_HREF}reseller.php?a=payment_cb&data={$this->payment_data}'>
<input name="cancel_return" value='{$URL_BASE_HREF}index.php?a=paypal_cancel_return'>
<input name="upload" value="1">
<input name="item_name_1" value="ARMS FNB {$_REQUEST['license_type']} Device ID:{$_REQUEST['id']}">
<input name="amount_1" value="{$_REQUEST['price']}">
<input name="quantity_1" value="1">
<input name="tx" value="3KK900354R868601V">
<input name="at" value="{$paypal_pdt}">
<input value="PDT">
<input type=submit>
</form>
EOL_PAYPAL;

print <<< REDIR_SCRIPT
<script>
document.forms[0].submit();
</script>
REDIR_SCRIPT;
	}

	function payment_cb($_id, $refno, $txn_id, $subscr_id='')
	{
		if ($_REQUEST['manual_update']){
			$find['_id'] = new MongoID($_REQUEST['id']);
			$find['refno'] = $_REQUEST['refno'];
			$find['paid']['$ne'] = 1;
			$txn_id = $_REQUEST['txn_id'];
			$subscr_id = $_REQUEST['subscr_id'];
			//Sample link to update payment: /index.php?a=payment_cb&manual_update=1&id=5480e39a63617503ec000175&refno=R963552&txn_id=xxxxxxxx
			//$order = $this->db->payment_history->findOne($find);
			//var_dump($order);
			//exit;			
		}
		else{		
			$find['_id'] = new MongoID($_id);
			$find['refno'] = $refno;
			$find['paid']['$ne'] = 1;
		}
		$order = $this->db->payment_history->findOne($find);

		if(!$order) die("Invalid payment history");

		//----------------------------------------
		//1. update payment_history
        $ipn['paid'] = 1;
		$ipn['ipn_return']['txn_id'] = $txn_id;
		$ipn['ipn_return']['date'] = new MongoDate(time());
		$this->db->payment_history->update(array('_id'=>new MongoID($_id)), array('$set'=>$ipn), array('safe'=>true));

		$device_id = $order['device_id'];
		$this->device = $this->db->subscribe_user->findOne(array('_id' =>$device_id));
		if(!$this->device) die("Invalid Device ID");

		$expiry_date = $this->device['expiry_date'];
		$today = date("Y-m-d");

		// expire start count from today if nil or long-expired
		if ($expiry_date == '' || $expiry_date <= $today) $expiry_date = $today;

		if ($order['renewal_terms']=='Lifetime')
			$set['expiry_date'] = 'Lifetime';
		else
			$set['expiry_date'] = date("Y-m-d", strtotime($expiry_date . " +1 years"));

		$this->db->subscribe_user->update(array('_id'=>$device_id),  array('$set'=>$set));
		$mac = $this->device['mac'];

		/*--- commented by cheryl on 16/7/2014 - change blocking reseller from adding slot when add device.
        $dummycheck = $this->db->purchased_slots->findOne(array('_id'=>$mac));
		if (!$dummycheck){
			// FREE slot for new subscribers
			if ($this->device['license_type']=='SUBSCRIPTION'){
				$slots = 20;
			}
			else if ($this->device['license_type']=='FULL'){
				$slots = 3;
			}
			// update slots
			$this->db->purchased_slots->update(array('_id'=>$mac), array('$set'=>array('slots'=>intval($slots))), array('upsert'=>true, 'safe'=>true));

			// save audit trail
			$this->db->purchased_slots_history->insert(array('mac'=>$mac, 'slots'=>intval($slots), 'added'=>new MongoDate(), 'ip'=>$_SERVER['REMOTE_ADDR'], 'agent'=>$_SERVER['HTTP_USER_AGENT'], 'pid'=>'NONE',  'remark'=>'RESELLER FREE SLOTS'));
		}
		*/

		//-----------------------------------------------------
		//--- Add Slots without blocking user (in any condition) - 16/7/2014
		// FREE slot for new subscribers (raymond = 20, ireland = 1)
        $dummycheck = $this->db->purchased_slots->findOne(array('_id'=>$mac));
		if (!$dummycheck){
			//Get default slots
			//1. Admin > Settings
			//2. Reseller
			$reseller = $this->db->resellers->findOne(array("_id"=>new MongoID($this->device['reseller_id'])));
			if ($reseller && $reseller['slots']!=''){
				$subscription_slot = $lifetime_slot = intval($reseller['slots']);
	    	}
			else{
				$subscription_slot = $this->config['settings']['reseller']['subscription-slot'];
	        	$lifetime_slot = $this->config['settings']['reseller']['lifetime-slot'];
		    }

			//WARNING! Changing this may affect other existing device ID
			if ($order['license_type']=='SUBSCRIPTION')
				$slots = ($subscription_slot) ? $subscription_slot : 20;

			if ($order['license_type']=='FULL')
                $slots = ($lifetime_slot) ? $lifetime_slot : 1;

	        //Update slots
			$this->db->purchased_slots->update(array('_id'=>$mac), array('$set'=>array('slots'=>intval($slots))), array('upsert'=>true, 'safe'=>true));

			//Save audit trail
			$this->db->purchased_slots_history->insert(array('mac'=>$mac, 'slots'=>intval($slots), 'added'=>new MongoDate(), 'ip'=>$_SERVER['REMOTE_ADDR'], 'agent'=>$_SERVER['HTTP_USER_AGENT'], 'pid'=>'NONE',  'remark'=>'RESELLER FREE SLOTS'));
		}
		//-----------------------------------------------------

		$set=array();
		$set['currency'] = $order['currency'];
		$set['price'] = $order['price']	;
		$set['license_type'] = $order['license_type'];
		$this->db->subscribe_user->update(array('_id'=>$device_id),  array('$set'=>$set));

		//----------------------------------------
		//check if existing device_code, need to update cloud
		if ($this->device['device_output']['device_code']!=''){
			$start_date = ($expiry_date == '' || $expiry_date <= $today) ? strtotime($today) : $this->device['added'];

			$params=array();
			$params['device_code']  = $this->device['device_output']['device_code'];
			$params['subscription']['start_date'] = $start_date;
            $params['subscription']['expiry_date'] = strtotime($set['expiry_date']);

			$send_token = encrypt_token($this, json_encode($params));
			$postdata = http_build_query(
				array(
					'a' => 'update_device',
					'token'=>$send_token
				)
			);
			$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata
				)
			);
			$context  = stream_context_create($opts);
			$result = file_get_contents($this->config['api_path']."api.php", false, $context);

	        $ret = json_decode($result);
			$ret->data = json_decode(decrypt_token($this, $ret->data));

			/*
			//Debug: IPN
			if($dir==null) $dir=DIR_ROOT.'ipn/';

			if(!is_dir($dir)) mkdir($dir,0777,true);
			chmod($dir,0777);

			$filename=$dir.date('Y-m-d H-i-s').".txt";
			file_put_contents($filename,serialize(array('code'=>$ret->code,'data'=>$ret->data)));
			*/

	  		$body = $js_ret;

			if (intval($ret->code)!=220){
				$js_ret = $ret->message;
				$to = array("cheryl"=>"cheryl@arms.my");
				$subject = "arms-fnb.com: Failed to update device code when renew";
		  		$body = $js_ret;
		  		$this->sendmail($to,$subject,$body);
			}
		}

        $this->redir('cms/payment-success');
	}

	private function notify_admin($subject, $tpl){
        //$to = 'yinsee@arms.my';
		$to = 'joochia@arms.my';
		$cc = array('cheryl@arms.my','nava@arms.my');
        $body = $this->tpl->fetch($tpl);
        $this->sendmail($to, $subject, $body, '', '', $cc);
	}

/*	function payment_cb()
	{
 		$payment = explode(".", $_REQUEST['data'], 2);

 		$_id = $payment[0];
 		//$refno = (int)base64_decode($payment[1]);
 		$refno = base64_decode($payment[1]);

		$order = $this->db->payment_history->findOne(array('_id'=>new MongoID($_id), 'refno'=>$refno));

		if(!$order)
		{
			$this->error("Your Order info is invalid. Please contact us immediately.");
		}
		else {
			$this->pdt_return($order);
		}

		$this->redir('cms/payment-success');
	}*/

	private function pdt_return(&$order)
	{

	    $id = new MongoID($order['_id']);
	    if (isset($_REQUEST['tx']))
	    {
	        // copy paypal PDT info
	        $pdt = $this->get_paypal_pdt();
			$this->db->payment_history->update(array('_id'=>$id), array('$set'=>array('pdt_return'=>$pdt)), array('safe'=>true));

			if ($pdt[0]!='SUCCESS')
	        {
	                print "
	                <p align=center><br /><br />
	                Unable to verify your Paypal payment (Paypal says: $pdt[0], $pdt[1]).<br /><br />
	                Please <a href=/contact-us.html>Contact Us</a>
	                with your <b>Reference #$_GET[oid]</b> for manual process of your order.</p>";
	                exit;
	        }
			else
			{
				$device = $this->db->payment_history->findOne(array('_id' =>$id));
				$device_id = $device['device_id'];
				$this->device = $this->db->subscribe_user->findOne(array('_id' =>$device_id));

				$expiry_date = $this->device['expiry_date'];
				$today = date("Y-m-d");

				// expire start count from today if nil or long-expired
				if ($expiry_date == '' || $expiry_date <= $today) $expiry_date = $today;
				$set['expiry_date'] = date("Y-m-d", strtotime($expiry_date . " +1 years"));

				// re-update? no need. $set['license_type'] = $this->device['license_type'];

				$this->db->subscribe_user->update(array('_id'=>$device_id),  array('$set'=>$set));

				$mac = $this->device['mac'];
				$dummycheck = $this->db->purchased_slots->findOne(array('_id'=>$mac));
				if (!$dummycheck)
				{
					// FREE slot for new subscribers
					if ($this->device['license_type']=='SUBSCRIPTION')
					{
						$slots = 20;
					}
					else if ($this->device['license_type']=='FULL')
					{
						$slots = 3;
					}
					// update slots
					$this->db->purchased_slots->update(array('_id'=>$mac), array('$set'=>array('slots'=>intval($slots))), array('upsert'=>true, 'safe'=>true));

					// save audit trail
					$this->db->purchased_slots_history->insert(array('mac'=>$mac, 'slots'=>intval($slots), 'added'=>new MongoDate(), 'ip'=>$_SERVER['REMOTE_ADDR'], 'agent'=>$_SERVER['HTTP_USER_AGENT'], 'pid'=>'NONE',  'remark'=>'RESELLER FREE SLOTS'));
				}
				//$this->db->payment_history->update(array('_id'=>$id), array('$set'=>array('pdt_return'=>$pdt)), array('safe'=>true));
			}
	    }
	    //header("Location: /thank-you.html/oid=$oid");
	    //exit;
	}

	private function get_paypal_pdt()
	{
         	// read the post from PayPal system and add 'cmd'
	        $req = 'cmd=_notify-synch';
	      	$tx_token = $_REQUEST['tx'];
	        $auth_token = $this->config['settings']['paypal']['paypal-pdt'];
	        $req .= "&tx=$tx_token&at=$auth_token";

			if ($this->config['settings']['paypal']['sandbox']) {
			$pp_hostname = "www.sandbox.paypal.com";
			}
			else { $pp_hostname = "www.paypal.com"; }

	        // post back to PayPal system to validate
	        // read the post from PayPal system and add 'cmd'
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, "https://$pp_hostname/cgi-bin/webscr");
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $req);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 1);
			//set cacert.pem verisign certificate path in curl using 'CURLOPT_CAINFO' field here,
			//if your server does not bundled with default verisign certificates.
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Host: $pp_hostname"));
			$res = curl_exec($ch);
			curl_close($ch);

			// parse the data
			$lines = explode("\n", $res);
			$keyarray = array();
			$keyarray[0] = $lines[0];
			if (strcmp ($lines[0], "SUCCESS") == 0) {
				for ($i=1; $i<count($lines);$i++){
					list($key,$val) = explode("=", $lines[$i]);
					if (urldecode($key) != '')  {
						$keyarray[urldecode($key)] = urldecode($val);
					}
				}
			}
	        return $keyarray;
	}

	function paypal_cancel_return()
	{
		$this->display('payment-fail.tpl');
	}

	/* temp*/
	/*private function generate_ref_id()
	{
		while(1) // danger -- infinite loop!
		{
			//$ref_id = rand(100000,999999);
			$ref_id = base64_encode(md5(uniqid(),true));
			//$this->db->orders->ensureIndex(array('refno'=>1));
			//$ff = $this->db->whatever->findOne(array('refno'=>$ref_id));
			//$countryCode =  $this->geo['geoplugin_countryCode'];
			//$ret = $countryCode."-".$ref_id;
			$ret = $ref_id;
			if (!$ff) return $ret;
		}
	}*/
	
	function app_action(){
		$action = $_POST['action'];
		$device_id = $_POST['id'];
		$this->app_activation($action,$device_id);
				
		print "OK";
	}
	
	//MANUAL Reseller that Able to Activate and Deactivate App
	function app_activation($action,$device_id){
		$subscribe = null;		
		//Check device status
		$this->device = $this->db->subscribe_user->findOne(array('_id' =>$device_id));
		if(!$this->device) die("Invalid Device ID");
		
		if($action == "activate"){
			$set['license_type'] = "FULL"; //FUTURE NEED CHANGE TO MANUAL WHEN CT UPLOAD NEW VER OF APP
			$set['expiry_date'] = "Lifetime";
			$set['remove_license'] = false;
			if(!isset($this->device['start_date']))
			$set['start_date'] = time();
			
			/* NOT USING
			$ms = $this->db->manual_subscribe_log->find(array('device_id'=>$device_id))->sort(array("subscribe_date"=> -1))->limit(1);

			foreach ($ms as $v){
				foreach($v as $k=>$y)
				{
					$output[$k] = $y;
				}
			}	
			
			if($output){
				$m = date('M',$output['subscribe_date']);
				$y = date('Y',$output['subscribe_date']);
				
				$tm = date('Y',time());
				$ty = date('Y',time());
				
				if ($y != $ty || $m != $tm)
					$subscribe['subscribe_date'] = time();
				
			}else{
				$subscribe['subscribe_date'] = time();
			}	
			*/
		
		}
		elseif($action == "deactivate"){
			$set['license_type'] = "";
			$set['remove_license'] = true;
		}
		$this->db->subscribe_user->update(array('_id'=>$device_id),  array('$set'=>$set));
		
		//Add Log whenever the device is activate or deactivate	
		$set['device_id'] = $device_id;
		$set['action'] = $action;
		$set['added'] = time();
		$set['by_reseller'] = $this->login['_id'];
		$set['ip'] = $_SERVER['REMOTE_ADDR'];
		$this->db->manual_device_activation_log->insert($set);
		
		/*
		if($subscribe){
			$set['subscribe_date'] = $subscribe['subscribe_date'];
			$set['type'] = "app";
			$this->db->manual_subscribe_log->insert($set);
		}
		*/
		
		//Add Slots for first time app activate
		$mac = $this->device['mac'];
		$dummycheck = $this->db->purchased_slots->findOne(array('_id'=>$mac));
		if (!$dummycheck){
			//SET DEFAULT 20 for this
			$reseller = $this->db->resellers->findOne(array("_id"=>new MongoID($this->device['reseller_id'])));
			
			$slots = $this->config['settings']['reseller']['subscription-slot'];

	        //Update slots
			$this->db->purchased_slots->update(array('_id'=>$mac), array('$set'=>array('slots'=>intval($slots))), array('upsert'=>true, 'safe'=>true));

			//Save audit trail
			$this->db->purchased_slots_history->insert(array('mac'=>$mac, 'slots'=>intval($slots), 'added'=>new MongoDate(), 'ip'=>$_SERVER['REMOTE_ADDR'], 'agent'=>$_SERVER['HTTP_USER_AGENT'], 'pid'=>'NONE',  'remark'=>'LICENSE TYPE MANUAL'));
			
		}
		//-----------------------------------------------------
	}
	
	function cloud_activation(){
		$action = $_POST['action'];
		$device_id = $_POST['id'];
		
		//Check device status
		$this->device = $this->db->subscribe_user->findOne(array('_id' =>$device_id));
		if(!$this->device) die("Invalid Device ID");
		
		$params=array();
		$params['device_code'] = $this->device['device_output']['device_code'];
		$params['status'] = $action;
		
		$send_token = encrypt_token($this, json_encode($params));
		$postdata = http_build_query(
			array(
				'a' => 'device_activation',
				'token'=>$send_token
			)
		);
		$opts = array('http' =>
			array(
				'method'  => 'POST',
				'header'  => 'Content-type: application/x-www-form-urlencoded',
				'content' => $postdata
			)
		);
		$context  = stream_context_create($opts);
		$result = file_get_contents($this->config['api_path']."api.php", false, $context);

	    $ret = json_decode($result);
		$ret->data = json_decode(decrypt_token($this, $ret->data));
		if($action == "activate"){
		
		}
		//Add Log whenever the device is activate or deactivate	
		$set['device_id'] = $device_id;
		$set['action'] = $action;
		$set['added'] = time();
		$set['by_reseller'] = $this->login['_id'];
		$set['ip'] = $_SERVER['REMOTE_ADDR'];
		$set['return_msg'] = $ret;
		$this->db->manual_cloud_activation_log->insert($set);
		
		if (intval($ret->code)!=220)
			print "FAIL";
		else
			print "OK";
	}
	function load_subscription(){
		//--- Pagination ---
		if(isset($_REQUEST['page'])) $this->startpage = $_REQUEST['page'];

		if ($this->startpage<=1) $this->startpage = 1;
		$s = ($this->startpage - 1) * $this->pagesize;

		//--- Search filter ---
        if(isset($_REQUEST['keyword'])){
			$selection = $_REQUEST['filter'];
			$inputbox = trim($_REQUEST['keyword']);

			$_SESSION['load_subscription_inputbox'] = ($inputbox) ? $inputbox : '';
		    $_SESSION['load_subscription_selection'] = $selection;
		}
		
		if($_REQUEST['reseller_filter'] != "all"){
			$_SESSION['load_subscription_reseller_filter'] = $_REQUEST['reseller_filter'];
		}else{
			$_SESSION['load_subscription_reseller_filter'] = "";
		}
		
		if ($_SESSION['load_subscription_selection'] && trim($_SESSION['load_subscription_inputbox'])!='')
            $find_subs = array($_SESSION['load_subscription_selection'] => new MongoRegex("/".$_SESSION['load_subscription_inputbox']."/i"));
	
		if($_SESSION['load_subscription_reseller_filter'] && trim($_SESSION['load_subscription_reseller_filter'])!='')
			$find_subs['reseller_id'] = $_SESSION['load_subscription_reseller_filter'];
		
		//--- Listing query ---
		$today = date("Y-m-d");
		$sort['added'] = -1;

		$id = $this->login['_id'] . "";
		
		if($this->login['sub_reseller_option'] == "yes" || $this->login['subscription'] == "manual"){
			
			if($this->login['sub_reseller_option'] == "yes"){
				$find_subs['license_status'] = "MANUAL";
				$find_subs['license_type'] = array('$ne' => "");
				$or1['$or'] = array(
					array('reseller_id'=> $id),
					array('parent_reseller_id' => $id),
				);
				$or2['$or'] = array(	
					array('license_type'=>'FULL'),
					array('license_type'=>'MANUAL')
				);
				$find_subs['$and'] = array($or1,$or2);
			}else{
				$find_subs['reseller_id'] = $id;
				$find_subs['license_status'] = "MANUAL";
				$find_subs['$or'] = array(
					array('license_type'=>'FULL'),
					array('license_type'=>'MANUAL'),
				);
			}
  		}else{
	        $find_subs['reseller_id'] = $id;
			$find_subs['$or'] = array(
				array('license_type'=>'FULL',
						'expiry_date'=>'Lifetime'),
				array('expiry_date'=>array('$exists'=>true, '$gte'=>$today))
			);
		}

		$this->inputstr = $_SESSION['load_subscription_inputbox'];
		$this->filter = $_SESSION['load_subscription_selection'];
		$this->login_subscribers = $this->db->subscribe_user->find($find_subs)->sort($sort);
		$this->totalresult = $this->login_subscribers->count();
		$this->totalpage = ceil($this->login_subscribers->count() / $this->pagesize);
        $this->login_subscribers = $this->login_subscribers->skip($s)->limit($this->pagesize);
		
		if($this->login['sub_reseller_option'] == "yes" || $this->login['subscription'] == "manual"){
			$this->sub_reseller = $this->db->resellers->find(array('$or'=>array(array('parent'=>$this->login['_id']),array('_id'=>$this->login['_id']))));
			$this->display('reseller.page.table.us.tpl');
		}
		else
			$this->display('reseller.page.table.tpl');
	}

	function load_subscription_exp(){
		//--- Pagination ---
		if(isset($_REQUEST['page'])) $this->startpage = $_REQUEST['page'];

		if ($this->startpage<=1) $this->startpage = 1;
		$s = ($this->startpage - 1) * $this->pagesize;

		//--- Search filter ---
        if(isset($_REQUEST['keyword'])){
			$selection = $_REQUEST['filter'];
			$inputbox = trim($_REQUEST['keyword']);

			$_SESSION['load_subscriptionex_inputbox'] = ($inputbox) ? $inputbox : '';
		    $_SESSION['load_subscriptionex_selection'] = $selection;
		}

		if ($_SESSION['load_subscriptionex_selection'] && trim($_SESSION['load_subscriptionex_inputbox'])!='')
            $find_ex = array($_SESSION['load_subscriptionex_selection'] => new MongoRegex("/".$_SESSION['load_subscriptionex_inputbox']."/i"));

		//--- Listing query ---
		$id = $this->login['_id'] . "";
		$today = date("Y-m-d");
		$sort['added'] = -1;

		
		
		if($this->login['sub_reseller_option'] == "yes" || $this->login['subscription'] == "manual"){
			
			if($this->login['sub_reseller_option'] == "yes"){
				$find_ex['license_status'] = "MANUAL";
				$or1['$or'] = array(
					array('reseller_id'=> $id),
					array('parent_reseller_id' => $id),
				);
				$or2['$or'] = array(	
					array('license_type'=>array('$exists'=>false)),
					array('license_type'=>'')
				);
				$find_ex['$and'] = array($or1,$or2);
			}else{
				$find_ex['reseller_id'] = $id;
				$find_ex['license_status'] = "MANUAL";
				$find_ex['$or'] = array(
					array('license_type'=>array('$exists'=>false)),
					array('license_type'=>'')
				);
			}
  		}else{
			$find_ex['reseller_id'] = $id;
			$find_ex['$or'] = array(
				array('license_type'=>array('$exists'=>false)),
				array('license_type'=>'SUBSCRIPTION',
						'expiry_date'=>array('$exists'=>true, '$lt'=>$today)
				),
				array('license_type'=>'FULL',
						'expiry_date'=>array('$ne'=>'Lifetime')
				)
			);
		}

		$this->inputstr = $_SESSION['load_subscriptionex_inputbox'];
		$this->filter = $_SESSION['load_subscriptionex_selection'];
        $this->login_subscribers = $this->db->subscribe_user->find($find_ex)->sort($sort);
        $this->totalresult = $this->login_subscribers->count();
		$this->totalpage = ceil($this->login_subscribers->count() / $this->pagesize);
        $this->login_subscribers = $this->login_subscribers->skip($s)->limit($this->pagesize);
		$this->subscription_exp = 1;
		
		if($this->login['sub_reseller_option'] == "yes" || $this->login['subscription'] == "manual"){
			$this->sub_reseller = $this->db->resellers->find(array('$or'=>array(array('parent'=>$this->login['_id']),array('_id'=>$this->login['_id']))));
			$this->display('reseller.page.table.us.tpl');
		}
		else
			$this->display('reseller.subscription_exp.row.tpl');
	}
	
	function load_subscription_sub(){
		//--- Pagination ---
		if(isset($_REQUEST['page'])) $this->startpage = $_REQUEST['page'];

		if ($this->startpage<=1) $this->startpage = 1;
		$s = ($this->startpage - 1) * $this->pagesize;

		//--- Search filter ---
        if(isset($_REQUEST['keyword'])){
			$selection = $_REQUEST['filter'];
			$inputbox = trim($_REQUEST['keyword']);

			$_SESSION['load_subscription_inputbox'] = ($inputbox) ? $inputbox : '';
		    $_SESSION['load_subscription_selection'] = $selection;
		}

		if ($_SESSION['load_subscription_selection'] && trim($_SESSION['load_subscription_inputbox'])!='')
            $find_subs = array($_SESSION['load_subscription_selection'] => new MongoRegex("/".$_SESSION['load_subscription_inputbox']."/i"));

		//--- Listing query ---
		$today = date("Y-m-d");
		$sort['added'] = -1;

		$id = $this->login['_id'] . "";
		$find_subs['parent_reseller_id'] = (string)$this->login['_id'];
        
		$this->inputstr = $_SESSION['load_subscription_inputbox'];
		$this->filter = $_SESSION['load_subscription_selection'];
		$this->login_subscribers = $this->db->subscribe_user->find($find_subs)->sort($sort);
		$this->sub_reseller = $this->db->resellers->find(array('parent'=>$this->login['_id']));
		$this->totalresult = $this->login_subscribers->count();
		$this->totalpage = ceil($this->login_subscribers->count() / $this->pagesize);
        $this->login_subscribers = $this->login_subscribers->skip($s)->limit($this->pagesize);
		$this->page_mode = "sub";
		
		
		if($this->login['sub_reseller_option'] == "yes" || $this->login['parent'] != "")
			$this->display('reseller.page.table.us.tpl');
		else
			$this->display('reseller.page.table.tpl');	
	}
	// generate codes
	function slot_code(){
		$this->tpl->assign('type','slot');
		$this->display('reseller.device.serial_code.tpl');
	}
	function generate_slot_code()
	{
		$cname = $_GET['cname'];
		$inv = $_GET['inv'];
		$slots = intval($_GET['slots']);
		$remark = $_GET['remark'];
		
		$_SESSION['load_cname'] = $cname;
		$_SESSION['load_inv'] = $inv;
		$_SESSION['load_slots'] = $slots;
		$_SESSION['load_remark'] = $remark;

		if(empty($cname)) die("Please enter the Customer Name");
		if(empty($inv)) die("Please enter the Invoice Number");
		if($slots<=0 || $slots > 20) die("Please enter the number of slots");
		$code = $this->gsc($this->login['serial_code_prefix'], $cname, $inv, $slots, true, $remark);
		
		print $code;
	}
	
	function serial_code(){
		$this->tpl->assign('type','serial');
		$this->display('reseller.device.serial_code.tpl');
	}
	function generate_serial_code()
	{
		$cname = $_GET['cname'];
		$inv = $_GET['inv'];
		$slots = intval($_GET['slots']);
		$remark = $_GET['remark'];
		$license_type = $_GET['license_type'];
		
		$_SESSION['load_cname'] = $cname;
		$_SESSION['load_inv'] = $inv;
		$_SESSION['load_slots'] = $slots;
		$_SESSION['load_remark'] = $remark;
		$_SESSION['load_licensetype'] = $license_type;

		if(empty($cname)) die("Please enter the Customer Name");
		if(empty($inv)) die("Please enter the Invoice Number");
		//if($slots<=0 || $slots > 20) die("Please enter the number of slots");
		if($slots < 0 || $slots > 20) die("Please enter the number of slots");
		if(empty($license_type)) die("Please select the License Type");
		$code = $this->gsc($this->login['serial_code_prefix'], $cname, $inv, $slots, false, $remark, $license_type);
		
		print $code;
	}

	function topup_code(){
		$this->display('reseller.device.topup_code.tpl');
	}

	function generate_topup_code(){
		$this->db->topup_code->ensureIndex(array('code'=>1));
		//$slots = intval($_GET['slots']);

		$cname=$_REQUEST['cname'];
		$number=intval($_REQUEST['number']);
		$period=$_REQUEST['period'];
		$remark = $_REQUEST['remark'];
		
		$_SESSION['generate_topup_code_cname'] = $cname;
		$_SESSION['generate_topup_code_number'] = $number;
		$_SESSION['generate_topup_code_remark'] = $remark;

		if(empty($cname)) die("Please enter the Customer Name");
		if($number<=0) die("Please enter the number");

		$keys = range('A', 'Z');
		$keys = array_merge(range(0, 9), $keys);

		$group=date('YmdHis');
		$date=date('Y-m-d H:i:s');
		$type=$_REQUEST['type'];
		for($i=0; $i<$number; $i++){
			while(1) // danger -- infinite loop!
			{
				$code="";
				for ($j = 0; $j < 10; $j++) {
					$code .= $keys[array_rand($keys)];
				}
				$ff = $this->db->topup_code->findOne(array('code'=>$code));
				if (!$ff) break;
			}

			$save = array(
							'group'=>$group,
							'cname'=>$cname,
							'code'=>$code,
							'type'=>$type,
							'period'=>$period,
							'remark'=>$remark,
							'used'=>0,
							'added_date'=>$date,
							'reseller_id'=>$this->login['_id'] . ""
						);
			$this->db->topup_code->save($save, array('fsync'=>1));
            $_SESSION['new_added']['topup_code'][] = $code;
		}

		die("OK");
	}

	function load_serial_code(){
		//--- Pagination ---
		if(isset($_REQUEST['page'])) $this->startpage = $_REQUEST['page'];

		if ($this->startpage<=1) $this->startpage = 1;
		$s = ($this->startpage - 1) * $this->pagesize;

		//--- Search filter ---
        if(isset($_REQUEST['keyword'])){
			$selection = $_REQUEST['filter'];
			$inputbox = trim($_REQUEST['keyword']);

			$_SESSION['load_serial_code_inputbox'] = ($inputbox) ? $inputbox : '';
		    $_SESSION['load_serial_code_selection'] = $selection;
		}

		//--- Listing query ---
		$id = $this->login['_id'] . "";
        $this->codes = array();
        if ($_SESSION['load_serial_code_selection']=='slots' || $_SESSION['load_serial_code_selection']==''){
	        if (trim($_SESSION['load_serial_code_inputbox'])!=''){
	            	$find_scm = array('$or' => array(array('serial_code'=> new MongoRegex("/".$_SESSION['load_serial_code_inputbox']."/i")),
												array('customer_name'=> new MongoRegex("/".$_SESSION['load_serial_code_inputbox']."/i")),
												array('invoice_no'=> new MongoRegex("/".$_SESSION['load_serial_code_inputbox']."/i"))
												)
								);
			}
			$find_scm['reseller_id'] = $id;
			$find_scm["generate_from"] = array('$exists'=>false);
			foreach($this->db->slot_code_mapping->find($find_scm)->sort(array('_id'=>-1)) as $k){
				$k['type'] = 'Slots';
				$k['added'] = $k['_id']->getTimestamp();
				$this->codes[$k['_id']->getTimestamp()] = $k;
			}
		}

        if ($_SESSION['load_serial_code_selection']=='device' || $_SESSION['load_serial_code_selection']==''){
			if (trim($_SESSION['load_serial_code_inputbox'])!=''){
            	$find_ucm = array('$or' => array(array('serial_code'=> new MongoRegex("/".$_SESSION['load_serial_code_inputbox']."/i")),
												array('customer_name'=> new MongoRegex("/".$_SESSION['load_serial_code_inputbox']."/i")),
												array('invoice_no'=> new MongoRegex("/".$_SESSION['load_serial_code_inputbox']."/i"))
												)
								);
			}
			$find_ucm['reseller_id'] = $id;
			$find_ucm["generate_from"] = array('$exists'=>false);

			foreach($this->db->upgrade_code_mapping->find($find_ucm)->sort(array('_id'=>-1)) as $k){
				$k['type'] = 'Device';
	            $k['added'] = $k['_id']->getTimestamp();
				$this->codes[$k['_id']->getTimestamp()] = $k;
		 	}
		}

		if ($this->login['serial_code_prefix']){

            if ($_SESSION['load_serial_code_selection']=='slots' || $_SESSION['load_serial_code_selection']==''){
	        	if (trim($_SESSION['load_serial_code_inputbox'])!='')
	            	$find_scm_old = array('serial_code'=> new MongoRegex("/".$_SESSION['load_serial_code_inputbox']."/i"));
				else
                    $find_scm_old["serial_code"] = new MongoRegex("/^".($this->login['serial_code_prefix'])."/");

				$find_scm_old["reseller_id"] = array('$ne'=>$id);
				$find_scm_old["generate_from"] = array('$exists'=>false);


				foreach($this->db->slot_code_mapping->find($find_scm_old)->sort(array('_id'=>-1)) as $k){
	            	$k['type'] = 'Slots';
	                $k['added'] = $k['_id']->getTimestamp();
	                $this->codes[$k['_id']->getTimestamp()] = $k;
	            }
			}

            if ($_SESSION['load_serial_code_selection']=='device' || $_SESSION['load_serial_code_selection']==''){
	        	if (trim($_SESSION['load_serial_code_inputbox'])!='')
	            	$find_ucm_old = array('serial_code'=> new MongoRegex("/".$_SESSION['load_serial_code_inputbox']."/i"));
				else
                    $find_ucm_old["serial_code"] = new MongoRegex("/^".($this->login['serial_code_prefix'])."/");

				$find_ucm_old["reseller_id"] = array('$ne'=>$id);
				$find_scm_old["generate_from"] = array('$exists'=>false);

				//array("reseller_id"=>array('$ne'=>$id), "serial_code"=>new MongoRegex("/^".($this->login['serial_code_prefix'])."/"))
				foreach($this->db->upgrade_code_mapping->find($find_ucm_old)->sort(array('_id'=>-1)) as $k){
	                $k['type'] = 'Device';
	                $k['added'] = $k['_id']->getTimestamp();
	                $this->codes[$k['_id']->getTimestamp()] = $k;
	            }
			}
		}
        krsort($this->codes);

		$this->inputstr = $_SESSION['load_serial_code_inputbox'];
		$this->filter = $_SESSION['load_serial_code_selection'];
		$this->totalresult = count($this->codes);
		$this->totalpage = ceil(count($this->codes) / $this->pagesize);
        $this->codes = array_slice($this->codes, $s, $this->pagesize);
		$this->display('reseller.serial.row.tpl');
	}


	function set_serial_code(){
		$id = $_REQUEST['_id'];
		$val = $_REQUEST['val'];
		$key = $_REQUEST['set'];

		$save[$key] = $val;

		if($_REQUEST['type']=="device"){
			$this->db->upgrade_code_mapping->update(array('_id'=>new MongoID($id)), array('$set'=>$save));
		}elseif($_REQUEST['type']=="slots"){
			$this->db->slot_code_mapping->update(array('_id'=>new MongoID($id)), array('$set'=>$save));
		}

		print "OK";
	}

	function load_topup_code(){
		//--- Pagination ---
		if(isset($_REQUEST['page'])) $this->startpage = $_REQUEST['page'];

		if ($this->startpage<=1) $this->startpage = 1;
		$s = ($this->startpage - 1) * $this->pagesize;

		//--- Search filter ---
        if(isset($_REQUEST['keyword'])){
			$selection = $_REQUEST['filter'];
			$inputbox = trim($_REQUEST['keyword']);

			$_SESSION['load_topup_code_inputbox'] = ($inputbox) ? $inputbox : '';
		    $_SESSION['load_topup_code_selection'] = $selection;
		}

		if ($_SESSION['load_topup_code_selection'] && trim($_SESSION['load_topup_code_inputbox'])!='')
            $find_topup = array($_SESSION['load_topup_code_selection'] => new MongoRegex("/".$_SESSION['load_topup_code_inputbox']."/i"));

		//--- Listing query ---
		$id = $this->login['_id'] . "";
        $find_topup['reseller_id'] = $id;
		$this->topup_code = $this->db->topup_code->find($find_topup)->sort(array('group'=>-1,'_id'=>1));
		$this->totalresult = $this->topup_code->count();
		$this->totalpage = ceil($this->topup_code->count() / $this->pagesize);
		$this->topup_code=$this->topup_code->skip($s)->limit($this->pagesize);

		$this->inputstr = $_SESSION['load_topup_code_inputbox'];
		$this->filter = $_SESSION['load_topup_code_selection'];
		$this->display('reseller.top_up.row.tpl');
	}


	private function gsc($user, $cname, $inv, $slots, $slot_code, $remark, $license_type="FULL")
	{
		$collection = ($slot_code ? $this->db->slot_code_mapping : $this->db->upgrade_code_mapping);
		$collection->ensureIndex(array('serial_code'=>1));

		while(1) // danger -- infinite loop!
		{
			// $postfix = $slot_code == 1 ? 'T' : '';
			if ($slot_code == 1) 								{ $postfix = 'T'; } 
			elseif ($slot_code == 0 && $license_type == "LITE") { $postfix = 'L'; } 
			else 												{ $postfix = ''; }
			$serial_code = rand(100000,999999);
			$code = $user."".$serial_code."".sprintf('%02d',$slots)."".$postfix;
			$ff = $collection->findOne(array('serial_code'=>$code));
			if (!$ff) break;
		}

		$sc = $collection->findOne(array('serial_code'=>$code));
		if (!$sc)
		{
			$save = array('serial_code'=>$code, 'reseller_id'=>strval($this->login['_id']), 'customer_name' =>$cname, 'invoice_no'=>$inv, 'remark'=>$remark, 'type'=>strtoupper($license_type), 'added'=>new MongoDate());
			$collection->save($save, array('fsync'=>1));
			$_SESSION['new_added']['gsc'][] = $code;
			return 'OK';
		}
		else
		{
			return 'Something just happened! Please regenerate.';
		}
	}

	function unpair_device(){
		if (!$_REQUEST['confirm']){
			$this->device_id = $_REQUEST['id'];
            $this->display("reseller.device.unpair.tpl");
		}
		else{
			if (strtoupper($_REQUEST['confirm'])!='CONFIRM'){
				die("Incorrect for typing word 'CONFIRM'. Please try again.");
			}

			$find['_id'] = $_REQUEST['id'] . "";
			$find['reseller_id'] = $this->login['_id'] . "";
			$device = $this->db->subscribe_user->findOne($find);

			if(!$device) die("Device not found.");

			$params['device_code']=$device['device_output']['device_code'];

			$send_token = encrypt_token($this,json_encode($params));
			$postdata = http_build_query(
				array(
					'a' => 'unpair',
					'token'=>$send_token
				)
			);

			$opts = array('http' =>
				array(
					'method'  => 'POST',
					'header'  => 'Content-type: application/x-www-form-urlencoded',
					'content' => $postdata
				)
			);

			$context  = stream_context_create($opts);
			$result = file_get_contents($this->config['api_path']."api.php", false, $context);

			$ret = json_decode($result);

			$ret->data = json_decode(decrypt_token($this,$ret->data));

			if (intval($ret->code)==231){
				//update device : set new device_code + paired=0
				//$this->update_device_code($_REQUEST['id'], $ret->data);

				die("OK");
			}
			else
				die("Unpair device fail.");
        }
	}

	function add_device_to_cloud(){
		//if (!$this->login['cloud_access'])  die("ERROR: NULL for Cloud's Access");
		if ($_REQUEST['id']==''){
			$js_ret['error'] = 1;
			$js_ret['msg'] = "Device ID is NULL";
		}
		else{
			//Check if Device ID is valid
			$today = date("Y-m-d");
			$find['_id'] = $_REQUEST['id'];
			$find['reseller_id'] = $this->login['_id'] . "";
			$device = $this->db->subscribe_user->findOne($find);

			if (!$device){
				$js_ret['error'] = 1;
				$js_ret['msg'] = "Device ID not found."; //$find;
			}
			else{
				//$str = array();
				$params=array();
		        $params['email']			= 	$device['email'];
				$params['name']				= 	$device['contact_person'];
				$params['cname']			=	$device['company_name'];
				$params['city']         	=   $device['city'];
				$params['address']			= 	$device['address'];
				$params['postcode']   		= 	$device['postcode'];
				$params['country']      	=   $device['country'];
				$params['mobile'] 			= 	$device['phone'];
				$params['company_website'] 	= 	$device['website'];
				$params['reseller_id']		=	$this->login['_id'] . "";
				$params['reseller_name']	=	$this->login['contact_person'];
				$params['reseller_email']	=	$this->login['email'];
				$params['reseller_device_id'] = $device['_id'] . "";

				//get from reseller detail
				/*if($this->login['subscription']=='yes'){
					if($this->login['cloud']=='follow_subscription')
						$params['reseller_acc_type']='SUBSCRIPTION';
					elseif($this->login['cloud']=='top_up')
						$params['reseller_acc_type']='TOPUP';
					elseif($this->login['cloud']=='self_renew')
						$params['reseller_acc_type']='STANDARD';
				}
				else{
					$params['reseller_acc_type']='STANDARD';
				}*/

				if($this->login['cloud']=='follow_subscription')
					$params['reseller_acc_type']='SUBSCRIPTION';
				elseif($this->login['cloud']=='top_up')
					$params['reseller_acc_type']='TOPUP';
				/*elseif($this->login['cloud']=='self_renew')
					$params['reseller_acc_type']='STANDARD';*/
				else
					$params['reseller_acc_type']='STANDARD';

				//-----------------------------------------
				//RAYMOND
				if($this->login['subscription']=='yes'){
					$expiry_date = date_parse($device['expiry_date']);

                    if ($expiry_date["error_count"] == 0 && checkdate($expiry_date["month"], $expiry_date["day"], $expiry_date["year"])){
					    if (time()<strtotime($device['expiry_date'])){
							$params['subscription']['period'] = 'MANUAL';
							$params['subscription']['start_date'] = strtotime(date('Y-m-d 23:59:59',$device['added']));
							$params['subscription']['expiry_date'] = strtotime(date('Y-m-d 23:59:59', strtotime($device['expiry_date'])));
						}
						else{
                            $js_ret['error'] = 1;
							$js_ret['msg'] = "Device ID is expired.";
		                }
					}
                    else{
                        $js_ret['error'] = 1;
						$js_ret['msg'] = "Device ID expiry date not valid.";
					}
				}
				else{
					//KAI YEH / IRELAND
					$params['subscription']['period'] = '3M';
				}

				if ($_REQUEST['db']!=''){
					if ($_REQUEST['db']=='CREATE_NEW')
						$params['account_decision'] = "CREATE_NEW";
					else{
						$params['account_decision'] = "USE_EXISTING";
						$params['db'] = $_REQUEST['db'];
					}
				}

				$send_token = encrypt_token($this, json_encode($params));
				$postdata = http_build_query(
					array(
						'a' => 'third_party_registration',
						'token'=>$send_token
					)
				);
				$opts = array('http' =>
					array(
						'method'  => 'POST',
						'header'  => 'Content-type: application/x-www-form-urlencoded',
						'content' => $postdata
					)
				);
				$context  = stream_context_create($opts);
				$result = file_get_contents($this->config['api_path']."api.php", false, $context);

		        $ret = json_decode($result);
				$ret->data = json_decode(decrypt_token($this, $ret->data));

				if ($ret->code!=0){
		        	switch ($ret->code){
						case "201":
						case "229":
						case "710":
						case "712":
						case "713":
						case "714":
						case "715":
						case "717":
							$js_ret['error'] = 1;
							$js_ret['msg'] = $ret->message;
							break;

						//EXISTING ACCOUNTS
						case "711":
							$total_db = count((array)$ret->data->accounts);

							if ($total_db>0){
								$js_ret['error'] = 0;
								$js_ret['code'] = $ret->code;
								$js_ret['accounts'] = (array)$ret->data->accounts;
							}
							else{
								$js_ret['error'] = 1;
								$js_ret['msg'] = "ERROR: Existing account with null account.";
							}
							break;

						//SUCCESS CREATE NEW DEVICE_CODE
						case "716":
							$js_ret['success'] = 1;
							$js_ret['device_output'] = $ret->data->device_ouput;
							//$this->update_device_code($_REQUEST['id'], $ret->data->device_output);
							break;

						default:
							$js_ret['error'] = 1;
							$js_ret['msg'] = "UNKNOWN ERROR";
							break;
					}
				}
	        }
        }
	    echo json_encode($js_ret);
		exit;
	}

	function cloud_select_account(){
		$this->subscription = $this->db->subscribe_user->findOne(array('_id' => $_REQUEST['id']));
		$this->accounts = $_REQUEST['accounts'];
		$this->display('reseller.device.cloud_select_account.tpl');
	}

	/*private function update_device_code($device_id, $ret){
		$today = date("Y-m-d");
		$find['_id'] = $device_id;
		$find['reseller_id'] = $this->login['_id'] . "";
		//$find['expiry_date'] = array('$gt'=>$today);
		$device = $this->db->subscribe_user->findOne($find);

		if (!$device){
			//Send Error Report
			$to = "nava@arms.my";
			$subject = "ARMS F&B: Unable add Device Code to Device ID #".$device_id;
			$body = "<h1>Unable add Device Code to Device ID #".$device_id."</h1><u>Device Code info:</u>";
      		$api_ret = json_decode(json_encode($ret), true);

			foreach($api_ret as $k=>$v){
				$body .= "<li>$k => ";

				if (is_array($v)){
		          $body .="<br />";
		          foreach($v as $k2=>$v2){
		            $body .= "$k2 = $v2<br />";
		          }
		        }
		        else
		          $body .= $v;

		        $body .= "</li>";
		    }

	      	$body .="<h2>Unable search device ID @ arms-fnb:</h2>";

			foreach($find as $fk=>$fv)
	    		$body .= "<li>$fk => $fv</li>";

	    	$this->sendmail($to, $subject, $body);
			exit;
		}

		//Update Device Code into Device ID
		$save = array();
		$save['device_output'] = $ret;
		//RAYMOND - no subscription period, follow license
		//$save['device_subscription_period'] = $subscription_period;
		$this->db->subscribe_user->update(array('_id'=>$device_id), array('$set'=>$save), array('upsert'=>true));
	}*/

	function check_payment(){
		$invoice = $_POST['invoice'];

		/*
		$find=array();
		$find['invoice']=$invoice;
 		$o = $this->db->ipn->find($find)->sort(array('_id'=>-1))->limit(1);

		if(!$o) die('Error');

		$o_arr = iterator_to_array($o);

		foreach($o_arr as $k=>$v){
			$order = $v;
		}
		*/

		// --- wait DR finish update cloud part, then open below
   		$find = array();
		$find['_id'] = new MongoID($_POST['id']);
		$order = $this->db->ipn->findOne($find);

		if(!$order) $this->redir("/index.php");

		if(intval($order['read'])) $this->redir("/index.php");

		$this->db->ipn->update(array('_id'=>new MongoID($order['_id'])),array('$set'=>array('read'=>1)),array('fsync'=>true,'safe'=>true));

		if ($order['res']!='VERIFIED'){
			$pending_opt = array("Created", "Pending", "Processed");

			if (in_array($order['payment_status'], $pending_opt))
				$this->redir("/cms/payment-under-process");
			else
				$this->redir("/cms/payment-fail");

			exit;
		}

		$this->update_payment($order);
	}

	private function update_payment($order=null){
		//Below are $_POST data:
		$txn_type = $order['txn_type'];
		$txn_id = $order['txn_id'];
		$amount = $order['mc_gross'];
		$status = $order['payment_status'];
		$paypal_email = $order['receiver_email'];
		$subscr_id = $order['subscr_id'];

        if ($this->config['settings']['payment']['paypal-sandbox'])
			$paypal_business_account = $this->config['settings']['payment']['paypal-sandbox-account'];
		else{
			$paypal_old_business_account = $this->config['settings']['payment']['paypal']['old_account'];
			$paypal_business_account = $this->config['settings']['payment']['paypal']['account'];
		}

        $this->response = 'VERIFIED';

		$this->refno = $order['invoice'];
		$payment_record = $this->db->payment_history->findOne(array('refno'=>$this->refno));

		if (!$payment_record)	die("Invalid refno: ".$refno);

		switch ($txn_type){
			// --------------------------
			// Express checkout / cart
            case 'cart':
			case 'express_checkout':
            case 'web_accept':
			case 'virtual_terminal':

			// --------------------------
	        //subscription payment recieved
	        case 'subscr_payment':
	           /*************************
	           --------Paypal Response in case of subscr_payment-----
				transaction_subject=Songs&payment_date=15:01:01 Jun 20, 2013 PDT&txn_type=subscr_payment&subscr_id=*********&last_name=****&residence_country=US&item_name=Songs&payment_gross=7.99&mc_currency=USD&business=*********&payment_type=instant&protection_eligibility=Ineligible&verify_sign=************* * * *&payer_status=verified&test_ipn=1&payer_email=********&txn_id=*********&receiver_email=**********&first_name=*****&payer_id=********&receiver_id=*********&payment_status=Completed&payment_fee=0.53&mc_fee=0.53&mc_gross=7.99&custom=uid:****&charset=windows-1252&notify_version=3.7&ipn_track_id=************
	            **************************/
				// check returned payment status
				if ($status != 'Completed')
					$this->err[] = "Incomplete status: ".$status;
				else{
					if ($payment_record['price'] != $amount)
						$this->err[] = "Invalid price: ".$amount."(response), ".$payment_record['price']."(payment_log)";

					// check price, existing txn_id, paypal_business_account
    				//if ($payment_record['ipn_return']['txn_id'] == $txn_id)
					//	$this->err[] = "Existing txn_id: ".$txn_id;

					if ($this->config['settings']['payment']['paypal-sandbox']){
						if ($paypal_email !== $paypal_business_account){
							$this->err[] = "Invalid receiver_email: ".$paypal_email."(response), ".$paypal_business_account."(config)";							
						}
					}
					else{
						if ($paypal_email !== $paypal_business_account && $paypal_email !== $paypal_old_business_account)
							$this->err[] = "Invalid receiver_email: ".$paypal_email."(response), ".$paypal_business_account."(new acc - config) / ".$paypal_old_business_account."(old acc - config)";
					}
				}

	            if (count($this->err)<1){
					$this->payment_cb($payment_record['_id'], $this->refno, $txn_id, $subscr_id);
	            }
 				else{
 	                $subject = "arms-fnb.com: Payment Error Refno #".$this->refno;
 					$this->notify_admin($subject, 'reseller.payment.error.mail.tpl');
				}
	            break;


            case 'subscr_signup':
            	 //subscription bought payment pending
	           /*************************
               --------Paypal Response in case of subscr_signup-----
txn_type=subscr_signup&subscr_id=*******&last_name=***&residence_country=US&mc_currency=USD&item_name=*******&business=***********&amount3=7.99&recurring=1&verify_sign=****************&payer_status=verified&test_ipn=1&payer_email=**********&first_name=******&receiver_email=*********&payer_id=********&reattempt=1&subscr_date=10:44:12 Jun 20, 2013 PDT&custom=uid:****-pid:**********&charset=windows-1252&notify_version=3.7&period3=1 M&mc_amount3=7.99&ipn_track_id=*********
            *************************/
				//paypal create subscription profile + thus not update payment_log
            break;

            case 'subscr_eot':
            //subscription end of term
            break;

            case 'subscr_cancel':
	           //subscription canceled Section
	            /*************************
	            --------Paypal Response in case of subscr_cancel-----
	txn_type=subscr_cancel&subscr_id=*******&last_name=***&residence_country=US&mc_currency=USD&item_name=Songs&business=***********&amount3=7.99&recurring=1&verify_sign=**********&payer_status=verified&test_ipn=1&payer_email=********&first_name=*****&receiver_email=**********&payer_id=**********&reattempt=1&subscr_date=14:56:59 Jun 20, 2013 PDT&custom=uid:****&charset=windows-1252&notify_version=3.7&period3=1 M&mc_amount3=7.99&ipn_track_id=***********
	            *************/

	           break;

           case 'subscr_modify':
           //This IPN is for a subscription modification.

           break;

           case 'subscr_failed':
           //This IPN is for a subscription payment failure.

           break;
        }
	}
}

new Reseller;
?>
