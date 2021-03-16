<?php
// cms module
require_once("framework/include.php");

ini_set("display_errors",0);
error_reporting(0);
define("LOG_FILE", "ipn.log");

class Subscriber_Log extends Module
{
	var $page_title = 'Log';

	function init()
	{
		
	}

	function _default()
	{
		die("Bye");
	}
	
	function show(){

		if(isset($_REQUEST['bill_date'])){
			$this->selected = $_REQUEST['bill_date'];
		}else{
			$this->selected = 0;
		}

		$initial_time = strtotime("2014-11-01");
		$start_date = $initial_time;

		$current_date = strtotime(date('Y-m-d'));

		$year1 = date('Y', $initial_time);
		$year2 = date('Y', $current_date);

		$month1 = date('m', $initial_time);
		$month2 = date('m', $current_date);

		$number_of_months = (($year2 - $year1) * 12) + ($month2 - $month1) + 1;


		//for ($i=0; $i<date('n')-1; $i++){
		for ($i=0; $i<$number_of_months; $i++){
			$increaseMonth = strtotime("+1 month", $start_date);
			$x['start'] = $start_date;
			$x['end'] = strtotime("-1 second", $increaseMonth);
			$x['display'] = date('Y-m-d',$start_date).' to '.date('Y-m-d',$x['end']);
			$this->bill_date[] = $x;
			$start_date = $increaseMonth;
		}

		rsort($this->bill_date);
		$reseller = $this->db->resellers->find(array('subscription'=>'manual'));
		
		$reseller_id = array();
		foreach($reseller as $r){
			$reseller_id[] = $r['_id'];
		}
		
		$this->monthStart = $this->bill_date[$this->selected]['start'];
		$this->monthLast = $this->bill_date[$this->selected]['end'];
		$find['added'] = array('$gte' => $this->monthStart, '$lt' => $this->monthLast);
		$find['action'] = 'activate';
		$find['by_reseller'] = array('$in'=>$reseller_id);
		
		$device_log = $this->db->manual_device_activation_log->find($find)->sort(array('added'=>1));
		$cloud_log = $this->db->manual_cloud_activation_log->find($find)->sort(array('added'=>1));
		
		$this->unique_device = array();	
		$this->unique_cloud = array();		
		foreach($device_log as $k=>$x){
			$f['_id'] = $x['device_id'];
			$info = $this->db->subscribe_user->findOne($f);
			$x['info'] = $info;
			$this->unique_device[$x['device_id']] = $x;
		}
		
		$this->unique_device_total['qty'] = sizeOf($this->unique_device);
		//Price depend on the quantity
		if($this->unique_device_total['qty'] <= 5000){
			$device_price = $this->config['price_device']['lvl1'];
		}elseif ($this->unique_device_total['qty'] <= 10000){
			$device_price = $this->config['price_device']['lvl2'];
		}else{
			$device_price = $this->config['price_device']['lvl3'];
		}
		$this->unique_device_total['total'] = $this->unique_device_total['qty'] * $device_price;
		
		foreach($cloud_log as $k=>$x){
			$f['_id'] = $x['device_id'];
			$info = $this->db->subscribe_user->findOne($f);
			$x['info'] = $info;
			$this->unique_cloud[$x['device_id']] = $x;
		}
	
		$this->unique_cloud_total['qty'] = sizeOf($this->unique_cloud);
		//Price depend on the quantity
		if($this->unique_cloud_total['qty'] <= 5000){
			$cloud_price = $this->config['price_cloud']['lvl1'];
		}elseif ($this->unique_cloud_total['qty'] <= 10000){
			$cloud_price = $this->config['price_cloud']['lvl2'];
		}else{
			$cloud_price = $this->config['price_cloud']['lvl3'];
		}
		$this->unique_cloud_total['total'] = $this->unique_cloud_total['qty'] * $cloud_price;
		
		$this->grand_total = $this->unique_cloud_total['total'] + $this->unique_device_total['total'];
		$this->display('subscribers_bill.tpl');
	}
}

new Subscriber_Log;
?>
