<?php
// cms module
require_once("framework/include.php");

ini_set("display_errors",0);
error_reporting(0);

class Cron_Renew extends Module
{
	var $page_title = 'Log';

	function init()
	{
		
	}

	function _default()
	{
		$this->renew();
	}
	
	function renew(){
		$reseller = $this->db->resellers->find(array('subscription'=>'manual'));
		
		$reseller_id = array();
		foreach($reseller as $r){
			$reseller_id[] = $r['_id'];
		}
		
		$this->monthStart = strtotime(date("Y-m-01")." -1 month");
		$this->monthLast = strtotime(date("Y-m-t")." -1 month");		
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
			//Check current device status is full mean activate for renew
			if($info['license_type'] === "FULL"){
				$this->unique_device[$x['device_id']] = $x;
			}
		}

		foreach($cloud_log as $k=>$x){
			$f['_id'] = $x['device_id'];
			$info = $this->db->subscribe_user->findOne($f);
			//Check current device cloud status active for renew
			if($info['device_output']['active'] == 1){
				$this->unique_cloud[$x['device_id']] = $x;
			}
		}

		//Add Log to renew the device for next month billing
		print "Renew Devices: \n";
		foreach ($this->unique_device as $device_id=>$info){
			$set = array();
			$set['license_type'] = "FULL"; //FUTURE NEED CHANGE TO MANUAL WHEN CT UPLOAD NEW VER OF APP
			$set['expiry_date'] = "Lifetime";
			$set['remove_license'] = false;
			$set['device_id'] = (string) $device_id;
			$set['action'] = 'activate';
			$set['added'] = time();
			$set['by_reseller'] = $info['by_reseller'];
			$set['ip'] = $_SERVER['REMOTE_ADDR'];
			$set['cron_renew'] = 1; //Add this tag to mark as renew from cron
			$this->db->manual_device_activation_log->insert($set);
			
			print $device_id."\n";
		}

		//Add Log to renew the cloud for next month billing
		print "\n Renew Cloud: \n";
		foreach ($this->unique_cloud as $device_id=>$info){
			$set = array();
			$set['device_id'] = (string) $device_id;
			$set['action'] = 'activate';
			$set['added'] = time();
			$set['by_reseller'] = $info['by_reseller'];
			$set['ip'] = $_SERVER['REMOTE_ADDR'];
			$set['return_msg'] = $info['return_msg'];
			$set['cron_renew'] = 1; //Add this tag to mark as renew from cron
			$this->db->manual_cloud_activation_log->insert($set);
			print $device_id."\n";
		}
		
	}
	
	function prn($x){
		print "<pre>";
		print_r($x);
		print "</pre>";
	}
}

new Cron_Renew;
?>
