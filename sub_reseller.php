<?php
// cms module
require_once("framework/include.php");

ini_set("display_errors",1);
error_reporting(1);

class Sub_Reseller extends Module
{	
	var $filter_list = array('email','country_handled.country','country_handled.regions');
	var $filter_trial = array('name','email','restaurant_name','city');
	var $page_title = 'My Resellers';	
	var $pagesize = 50;
	
	function init(){
		if ($_SESSION['m_sort_col']=='') $_SESSION['m_sort_col'] = 'added';
		if ($_SESSION['m_sort_ord']=='') $_SESSION['m_sort_ord'] = -1;
	}
	
	function _default(){
		
		if($_REQUEST['page_type']=='trial'){
			$this->trial_list('sub_reseller.page.tpl');
		}else{
			$this->list_resellers('sub_reseller.page.tpl');
		}
	}

	function list_resellers($template='sub_reseller.table.tpl')
	{
        if(isset($_REQUEST['inputbox']))
		{
		    $selection = $_REQUEST['selection'];
		    $inputbox=trim($_REQUEST['inputbox']);

			if ($inputbox=='')
		    {
				$_SESSION['m_inputbox'] = '';
			}
			else
			{
				$_SESSION['m_inputbox'] = $inputbox;
			}

		    $_SESSION['m_selection'] = $selection;
			$_REQUEST['page'] = 1;

			
		}
		$_SESSION['m_delete'] = (isset($_REQUEST['filter_delete']));
		    
	    if(isset($_REQUEST['page'])) 
		{
			$this->startpage = $_REQUEST['page'];
			$_SESSION['m_sort_page'] = $this->startpage;
		}
	    else 
			$this->startpage = intval($_SESSION['m_sort_page']);
	    
		if ($this->startpage<=1) $this->startpage = 1;
		$s = ($this->startpage - 1) * $this->pagesize;
		
		$sort = array($_SESSION['m_sort_col'] => $_SESSION['m_sort_ord']);
		if ($inputbox){
 			if($selection=='country_handled.country'){
				$country = $this->db->country_list->findOne(array('country'=>new MongoRegex("/$inputbox/i")));
				$find = array('country_handled.country' => $country['code']);
			 }
			 else
			 	$find = array($selection => new MongoRegex("/$inputbox/i"));
		}
		else
			$find = array();

			if ($_REQUEST['filter_delete'])
				$find['delete'] = '1';
			else
				$find['delete'] = array('$ne'=>'1');
		
		$find['parent'] = $this->login['_id'];
		$this->inputstr = $_SESSION['m_inputbox'];
		$this->filter = $_SESSION['m_selection'];

		$this->resellerslist = array();
		foreach($this->db->resellers->find($find)->sort($sort)->skip($s)->limit($this->pagesize) as $r)
		{
			$r['no_of_clients'] = $this->db->subscribe_user->find(array('reseller_id'=>strval($r['_id'])))->count();
			$this->resellerslist[] = $r;
		}
		$this->totalrecord = $this->db->resellers->find($find)->count();
		$this->totalpage = ceil($this->totalrecord / $this->pagesize);

		$this->display($template);
	}
	
	function add(){
		$this->get_country();
		$this->display("reseller_form.tpl");
	}
	
	function get_country()
	{
		$country_list = $this->db->country_list->find();
		foreach($country_list as $k=>$v){
			if (trim($v['country']!='')){
				$this->country_list[$k]['code'] = $v['code'];
				$this->country_list[$k]['country'] = $v['country'];
				$this->country_list[$k]['zone'] = $v['zone'];
			}
		}
 		$this->currency_code = $this->db->currency_code->find();
	}
	function signup()
	{
		$email = trim($_POST['email']);
		$p = trim($_POST['p']);
		if ($p=='') $this->errmsg = "Invalid password!";

		if (!$this->errmsg)
		{
			$su = $this->db->resellers->findOne(array('email'=>$email));

			if ($su)
			{
				$this->errmsg = "Email Used";
			}
			else
			{
				// sign up done
				$save = array();
				$save['email'] = $email;
				$save['password'] = md5($p);
				$save['company_name'] = $_POST['company_name'];
				$save['company_reg_no'] = $_POST['company_reg_no'];
				$save['contact_person'] = $_POST['contact_person'];
				$save['phone'] = $_POST['phone'];
				$save['fax'] = $_POST['fax'];
				$save['website'] = $_POST['website'];
				$save['address'] = $_POST['address'];
				$save['city'] = $_POST['city'];
				$save['postcode'] = $_POST['postcode'];
				$save['reseller_image'] = $_POST['reseller_image'];
				$save['allow_generate_code'] = $_POST['allow_generate_code'];
				$save['payware'] = $_POST['payware'];
				$save['subscription'] = $_POST['subscription'];
                $save['slots'] = $_POST['slots'];
				//allow_warranty_register rely on subscription type
				$save['allow_warranty_register'] = ($_POST['subscription']=='yes' || $_POST['subscription']=='manual') ? 0 : 1;
				$save['cloud'] = $_POST['cloud'];
				$save['serial_code_prefix'] = $_POST['serial_code_prefix'];
				$save['parent'] = $this->login['_id'];
				if (is_array($_REQUEST['country']) && count($_REQUEST['country'])>0){
					$n=0;

					foreach($_REQUEST['country'] as $k=>$v){
						if (trim($v)!=''){
							$save['country_handled'][$n]['country'] = $v;
							$save['country_handled'][$n]['currency'] = $_REQUEST['currency'][$k];
							$save['country_handled'][$n]['price'] = floatval($_REQUEST['price'][$k]);
							$save['country_handled'][$n]['price_full'] = floatval($_REQUEST['price_full'][$k]);

							if (is_array($_REQUEST['regions'][$k]) && count($_REQUEST['regions'][$k])>0)
								$save['country_handled'][$n]['regions'] = $_REQUEST['regions'][$k];
							else
								$save['country_handled'][$n]['regions'] = array();
						}
						$n++;
					}
				}
				else
					$save['country_handled'] = NULL;
					
				//Sub Reseller will follow Parent Region
				$parent = $this->db->resellers->findOne(array('email'=>$this->login['email']));
				$save['no_country'] = $parent['no_country'];	

				$save['active'] = '1';
				$save['joined'] = time();
				$save['last_login'] = time();
				$save['ip'] = $_SERVER['REMOTE_ADDR'];

                	try {
						$this->db->resellers->insert($save, array("safe" => true));
					}
					catch (MongoCursorException $e) {
						$this->errmsg="error message: ".$e->getMessage()."\n";
						$this->errmsg.="error code: ".$e->getCode()."\n";
					}
			}
		}

		print "<pre>";
		print_r($this->errmsg);
		print "</pre>";

		$this->list_resellers('sub_reseller.page.tpl');
	}
	
	function update()
	{
			// can save
			$save = array();
			$save['email'] = $_POST['email'];
			$save['company_name'] = $_POST['company_name'];
			$save['company_reg_no'] = $_POST['company_reg_no'];
			$save['contact_person'] = $_POST['contact_person'];
			$save['phone'] = $_POST['phone'];
			$save['fax'] = $_POST['fax'];
			$save['website'] = $_POST['website'];
			$save['address'] = $_POST['address'];
			$save['city'] = $_POST['city'];
			$save['postcode'] = $_POST['postcode'];
			$save['reseller_image'] = $_POST['reseller_image'];
			$save['allow_generate_code'] = $_POST['allow_generate_code'];
			$save['payware'] = $_POST['payware'];
			$save['subscription'] = $_POST['subscription'];
            $save['slots'] = $_POST['slots'];
            $save['allow_warranty_register'] = ($_POST['subscription']=='yes' || $_POST['subscription'] == "manual" ) ? 0 : 1;
			$save['cloud'] = $_POST['cloud'];
			$save['serial_code_prefix'] = $_POST['serial_code_prefix'];
			
			$save['modified'] = time();
			if ($_POST['p'] != ''){
				$save['password'] = md5($_POST['p']);
			}

// 			print "<pre>";
// 			print_r($save);
// 			print "</pre>";
// 			exit;

			$this->db->resellers->update(array('email'=>$save['email']), array('$set'=>$save));
			print 'OK';
	}
	
	function detail()
	{
		$this->reseller = $this->db->resellers->findOne(array('_id'=>new MongoID($_REQUEST['_id'])));
		$this->get_country();
		$this->display("reseller_form.tpl");
	}
	function region()
	{
		$this->reseller = $this->db->resellers->findOne(array('_id'=>new MongoID($_REQUEST['_id'])));
		$this->get_country();
		$this->display("region_form.tpl");
	}
	
	function change_country(){
		$ret = $this->db->country_list->findOne(array('code'=>$_REQUEST['code']), array('zone'=>1));
		print json_encode($ret['zone']);
 	}
	
	function sort()
	{
		$val = trim($_REQUEST['val']);
		if ($_SESSION['m_sort_col']==$val)
		{
			$_SESSION['m_sort_ord'] = ($_SESSION['m_sort_ord']==1) ? -1 : 1;
		}
		else
		{
			$_SESSION['m_sort_col'] = $val;
			$_SESSION['m_sort_ord'] = 1;
		}
		if($_REQUEST['page_type']=='trial'){
			$this->trial_list();
		}else{
			$this->list_resellers();
		}
	}
	
	function sarrow($str)
	{
		if ($str==$_SESSION['m_sort_col'])
		{
			if ($_SESSION['m_sort_ord']==1)
				print "&darr;";
			else
				print "&uarr;";
		}
	}
	
	//Delete or UnDelete
	function set()
	{
	    $id = $_REQUEST['_id'];
		$set = array($_REQUEST['set'] => trim($_REQUEST['val']));
		$this->db->resellers->update(array('_id'=>new MongoID($id)), array('$set'=>$set));
		print "OK";
	}
	
	function trial_list($template='trial.table.tpl'){
		$this->page_title = "15 Days Trial Customers";
		$this->page_type = 'trial';
		//Filter Type
		if(isset($_REQUEST['inputbox']))
		{
		    $selection = $_REQUEST['selection'];
		    $inputbox=trim($_REQUEST['inputbox']);

			if ($inputbox=='')
		    {
				$_SESSION['t_inputbox'] = '';
			}
			else
			{
				$_SESSION['t_inputbox'] = $inputbox;
			}

		    $_SESSION['t_selection'] = $selection;
			$_REQUEST['page'] = 1;

			
		}
		    
	    if(isset($_REQUEST['page'])) 
		{
			$this->startpage = $_REQUEST['page'];
			$_SESSION['t_sort_page'] = $this->startpage;
		}
	    else 
			$this->startpage = intval($_SESSION['t_sort_page']);
	    
		if ($this->startpage<=1) $this->startpage = 1;
		$s = ($this->startpage - 1) * $this->pagesize;
		
		$sort = array($_SESSION['m_sort_col'] => $_SESSION['m_sort_ord']);
		if ($inputbox){
 			$find = array($selection => new MongoRegex("/$inputbox/i"));
		}

		if($this->login['email'] == "jeff@comrc.io"){
			$find['country'] = 'United States';
		}elseif($this->login['email'] == "neilfbradley@hotmail.com"){
			$find['country'] = 'United Kingdom';
		}elseif($this->login['email'] == "raymond@synergypos.com.au"){
			$find['country'] = 'Australia';
		}
		else{
			$find['country'] = 'Malaysia';
		}

		$this->inputstr = $_SESSION['t_inputbox'];
		$this->filter = $_SESSION['t_selection'];
		
		$this->trial_result = $this->db->full_trial_list->find($find)->sort($sort)->skip($s)->limit($this->pagesize);	
		$this->totalrecord = $this->db->full_trial_list->find($find)->count();
		$this->totalpage = ceil($this->totalrecord / $this->pagesize);
		$this->display($template);
	}
}

new Sub_Reseller(MODULE::REQUIRE_LOGIN);

?>