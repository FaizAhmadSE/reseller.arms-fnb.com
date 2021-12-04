<?php
/*
Copyright (C) 2011 by yinsee@wsatp.com
Published under GPLv3 License

https://github.com/yinsee/WEBOX2/blob/master/framework/include.php
*/
$mtstart=microtime(true);
define('REQUEST_ACTION_PARAM', 'a');	// set ?a=xxx querystring for action

// use DOCUMENT_ROOT for DIR_ROOT, DIR_FRAMEWORK, URL_BASE_HREF if the framework folder is shared
//realpath(dirname(__file__).'/..') != $_SERVER['DOCUMENT_ROOT']
//
if (!isset($_SERVER['HTTPS'])) $_SERVER['HTTPS'] = false;
if(php_sapi_name() == 'cli' || empty($_SERVER['REMOTE_ADDR'])) {
	define('DIR_ROOT', realpath(dirname(__file__).'/..').'/');
	define('URL_BASE_HREF','http://cloud.ddns.my/');
}
elseif (strpos(str_replace("\\","/", realpath(dirname(__file__).'/..')), $_SERVER['DOCUMENT_ROOT'])!==0)
{
	define('DIR_ROOT', $_SERVER['DOCUMENT_ROOT'].'/');
	define('URL_BASE_HREF',($_SERVER['HTTPS']?'https':'http').'://'.$_SERVER['HTTP_HOST'].'/');
}
else
{
	// find where is config.php and that is our doc root
	$p1 = explode("/", $_SERVER['REQUEST_URI']);
	while(!file_exists($_SERVER['DOCUMENT_ROOT'] . implode("/",$p1) . "/config.php"))
	{
		array_pop($p1);
		if(empty($p1)) die("Error: Can't locate DIR_ROOT");
	}
	$path = str_replace('//','/',implode("/",$p1).'/');
	define('DIR_ROOT', $_SERVER['DOCUMENT_ROOT'].$path);	// full path to the root
	define('URL_BASE_HREF',($_SERVER['HTTPS']?'https':'http').'://'.$_SERVER['HTTP_HOST'].$path);
}
define('DIR_FRAMEWORK', DIR_ROOT.'framework/');
define('URL_CURRENT', ($_SERVER['HTTPS']?'https':'http').'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

// detect ajax or mobile mode
define('MOBILE', preg_match('/webkit.*mobile/i',$_SERVER['HTTP_USER_AGENT'])); // are we on mobile?
define('AJAX', isset($_SERVER['HTTP_X_REQUESTED_WITH'])); // tell if we are esnding in as AJAX

// load configuration
require_once(DIR_ROOT."/config.php");

// force HTTPS if not cli-mode
if (!(php_sapi_name()=='cli' && empty($_SERVER['REMOTE_ADDR']))) // skip check if running from CLI
{
	if ($config['force_https'] && !isset($_SERVER['HTTPS'])) {
		header("Location: https://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
		exit;
	}

	ob_start('ob_gzhandler');
	header('Content-Type: text/html; charset='._config('charset','utf-8'));
}

// append WEBOX2 to copyright }:)
$config['copyright'] .= ' Powered by <a style="text-decoration:underline" href="http://www.webox.com.my/" target=_blank>WEBOX2</a>';
if (!defined('DONT_MESS_WITH_COPYRIGHT'))
{
	$config['settings']['website']['copyright'] .= ' Powered by <a style="text-decoration:underline" href="http://www.webox.com.my/" target=_blank>WEBOX2</a>';
}

session_start();
$sid = session_id();

require_once(DIR_FRAMEWORK."smarty/Smarty.class.php");
require_once(DIR_FRAMEWORK."class.phpmailerlite.php");
require_once(DIR_FRAMEWORK."class.smarty_resource_themestore.php");

@include_once("language/"._config('language','en').".php");

function __autoload($class_name)
{
	$fn = DIR_FRAMEWORK . 'class.'.strtolower($class_name). '.php';
	if (file_exists($fn)) require_once($fn);
}

function _config($cfg,$default=false)
{
	global $config;
	return isset($config[$cfg])?$config[$cfg]:$default;
}

function _lang($msg,$section="")
{
	global $LANG;
	if ($section)
		return isset($LANG[$section][$msg])?$LANG[$section][$msg]:$msg;
	else
		return isset($LANG[$msg])?$LANG[$msg]:$msg;
}

function _nice($str)
{
	$str = preg_replace('/[^a-z0-9]+/',' ',strtolower($str));
	return preg_replace('/\s+/','-',trim($str));
}

function _call_at($callat, $cmd)
{
	global $plugins, $class_plugins;
	if (strstr($callat,"::"))
	{
		$cc = explode("::", $callat);
		$class_plugins[$cc[0]][$cc[1]][] = $cmd;
	}
	else
	{
		$plugins[$callat][] = $cmd;
	}
}

// load all PHP in plugins folder
$plugins = array(); $class_plugins = array();
foreach(@glob(DIR_ROOT.'plugins/*.php') as $f) require_once($f);

// module base class
class Module extends WEBOX2
{
	var $action = '_default';
	var $login_session_name = 'reseller_login';
	const REQUIRE_LOGIN = 0x01;
		
	// constructor
	function __construct($init_flags=0,$modulename='')
	{
	    global $config; $this->config = &$config;
		global $sid; $this->sid = $sid;

		if (isset($_REQUEST[REQUEST_ACTION_PARAM]) && $_REQUEST[REQUEST_ACTION_PARAM]!='') $this->action = $_REQUEST[REQUEST_ACTION_PARAM];

		// get user session
		$this->login = $this->get_login($init_flags & Module::REQUIRE_LOGIN);
		
		$this->is_admin = isset($_SESSION['admin_login']['_id']);
		
		$this->tpl = new Template;
		$this->tpl->assignByRef('this', $this);
	    $this->tpl->registerPlugin("block", "webox2_block", array($this,"_webox2_block"));
		$this->tpl->registerPlugin("function", "webox2_element", array($this,"_webox2_element"));

		// copy country
		global $_countries;
		$this->_countries = &$_countries;

		// hook up the Mongo database using the PHP Mongo class
		// to install, run sudo pecl install mongo
		$this->__connect_db();
		
		// call init if ready
		$this->run_plugins('init');
		if (method_exists($this,'init')) {
			$this->init();
		}
		if (!method_exists($this,$this->action))
		{
			$this->page_not_found();
			/*print "<h1>"._lang('Unhandled Request')."</h1>";
			print get_class($this) . "::{$this->action} > ";
			print_r($_REQUEST);*/
			exit;
		}

		// call our own function
		$action = $this->action;
		$this->run_plugins($this->action);
		$this->$action();
 	}
 	
	function __destruct()
	{
		if (defined('BENCHMARK'))
		{
			print "<!-- Mem:".memory_get_usage();
			global $mtstart;
			print " Time:".(microtime(true)-$mtstart).' -->';
		}
	}
	
	function __call($method, $args)
	{
		array_unshift($args, $this); 
		if (function_exists('_plugin_'.$method))
			return call_user_func_array('_plugin_'.$method,  $args);
		else
			return null;
	}
	
	function __connect_db()
	{
		try {
			if (class_exists("MongoClient")) {
				$mongo = new MongoClient(_config('mongodb_conn'));
				$this->db = $mongo->selectDB(_config('db'));
			}
			else {
				$mongo = new Mongo();
				$this->db = $mongo->selectDB(_config('db'));
				if (!$this->db) die("Error: Connect DB failed, check db name.");
				if (_config('db_user') || _config('db_password'))
				{
					$ret = $this->db->authenticate(_config('db_user'), _config('db_password'));
					if ($ret['ok']!=1)
					{
						die("Error: DB Authentication failed, check user and password ($ret[errmsg]).");
					}
				}
			}
		} catch(Exception $e) {
			die("Error: Connect DB failed, check db/user/password (".$e->getMessage().")");
		}

		// test db
		try {
			$this->db->listCollections();
		} catch(Exception $e) {
			die("Error: Connect DB failed, check db/user/password (".$e->getMessage().")");
		}
	}
	
	private function run_plugins($action)
	{
		global $plugins, $class_plugins;
		if (isset($class_plugins[get_class($this)][$action]))
		{
			// call plugins registered with _call_at('Class::action', 'cmd')
			foreach($class_plugins[get_class($this)][$action] as $cmd) eval($cmd);
		}
		if (isset($plugins[$action]))
		{
			// call plugins registered with _call_at('action', 'cmd')
			foreach($plugins[$action] as $cmd) eval($cmd);
		}
	}

	function display($tpl)
	{
		if (strpos($tpl,':')===false) 
		    $this->tpl->display($this->tpl->template_dir[0].'/'.$tpl);
	    else
		    $this->tpl->display($tpl);
	}

	function fetch($tpl)
	{
		if (strpos($tpl,':')===false) 
		    return $this->tpl->fetch($this->tpl->template_dir[0].'/'.$tpl);
	    else
		    return $this->tpl->fetch($tpl);
	}

	// sql helper
	function query($sql, $die_on_error = true)
	{
		$ret = $this->db->query($sql);
		if (!$ret && $die_on_error)
		{
		    print "<h1>SQL Error</h1>";
		    print "<b>Query:</b><br />$sql<br /><br />";
		    print "<b>Error:</b><br />";
			print(array_pop($this->db->errorInfo()));
			exit;
		}
		return $ret;
	}

	// return affected rows
	function exec($sql, $die_on_error = true)
	{
		$ret = $this->db->exec($sql);
		if ($ret===false && $die_on_error) die("Error: ".array_pop($this->db->errorInfo()));
		return $ret;
	}

	function page_not_found()
	{
	    header('HTTP/1.0 404 Not Found');
		$this->tpl->display('ts:page_not_found.tpl');

/*		{
			print "<h1>"._lang("Page Not Found")."</h1>";
	    	print "<p><big>"._lang("You may have entered an invalid URL or the page no longer exists.")."</big></p>";
	    }
		*/
		exit;
	}

	function error($reason)
	{
	    if (file_exists($this->tpl->template_dir[0].'/error.tpl'))
		{
			$this->tpl->assign("reason",$reason);
			$this->tpl->display('ts:error.tpl');
		}
		else
		{
			print "<h1>"._lang("Page Error")."</h1>";
		    print "<p><big>".htmlspecialchars($reason)."</big></p>";
	    }
		exit;
	}

	function redir($url, $use_header=true)
	{
		if ($use_header)
			header("Location: $url");
	    else
	    	print "<p class=large>"._lang('Redirecting you to')."<br /><a href=\"$url\">$url</a></p><meta http-equiv=\"refresh\" content=\"5;URL=$url\">";
		exit;
	}

	function get_login($login_required=false)
	{
		// yes, already login
		if (isset($_SESSION[$this->login_session_name]))
		{
			return $_SESSION[$this->login_session_name];
		}
		elseif ($login_required && $this->action!='login' && $this->action!='logout')
		{
			// redirect to login
			header("Location: "._config('login_url', URL_BASE_HREF.'index.php?a=login').'&redir='.urlencode($_SERVER['REQUEST_URI']));
			exit;
		}
		return false;
	}

	// store the login info to session
	function set_login($user)
	{
		$_SESSION[$this->login_session_name] = $user;
		$this->login = $user;
	}

	// clear login info
	function unset_login()
	{
		unset($_SESSION[$this->login_session_name]);
		unset($this->login);
	}
	
	// auto email form to admin, ajax way?
	function formmail()
	{
		$data = $_POST;
		$body = "<html><table>";
		foreach($data as $f=>$v)
		{
			$body .= "<tr><td>$f</td><td>";
			if (is_array($v)){
				$body .= "√ ".implode(" &nbsp;&nbsp;&nbsp; √ ", $v);
			} else {
				$body .= nl2br($v);
			}
			$body .= "</td></tr>";
		}
		$body .= "</table>";
		$body .= "<p>Send from $_SERVER[HTTP_HOST]<br>TIME: ".date('r')." IP: $_SERVER[REMOTE_ADDR]</p></html>";
		
		$files='';
		if(isset($_FILES['files'])){
			$files=array();
			foreach(array_keys($_FILES['files']['name']) as $key) {
				$source = $_FILES['files']['tmp_name'][$key]; // location of PHP's temporary file for this.
				$filename = $_FILES['files']['name'][$key]; // original filename from the client		 
				$files[$source]=$filename;				
			}			
		}			
		
		$this->sendmail($this->config['settings']['website']['admin-email'], isset($data['subject'])?$data['subject']:"Feedback from $_SERVER[HTTP_HOST]", $body, $data['email'],$files,$data['cc'],$data['bcc']);
		
		if (AJAX)
		{
			print 'OK';
		}
		else
		{
			if($_REQUEST['iframe']){
				echo '<script>window.top.iframe_complete()</script>';
				exit;
			}
			else{
				// redirect to somewhere...
				$this->error("We have received your reply. Thank you!");
			}
		}
	}

	// very basic sendmail, if to is a array then it will send to each
	function sendmail($to,$subject,$message,$from='',$attachments='',$cc='',$bcc='')
 	{
 	    $mail_outsider = false;
//		if (!$mailto) return true; // no mail

		$mail = new PHPMailerLite(); // defaults to using php "Sendmail" (or Qmail, depending on availability)
		if (isset($this->mail_from))
		{
			$mail->SetFrom($this->mail_from, $this->mail_sender);
		}
		else
		{
			/*if ($from=='') $from = _config('mailsender');
			elseif (!strstr($from,"@")) $from = $from."@"._config('maildomain');
			$mail->SetFrom($from);*/
			$mail->SetFrom("noreply@ddns.my");
		}

		// send to
		if (!is_array($to)) $to = preg_split('/[,\n\r]+/',trim($to));
		foreach($to as $e)
		{
		    if ($e=='') continue;
		    // add @wsatp.com to those without domain
		    if (!strstr($e,"@"))
				$e = $e."@"._config('maildomain');
			else
			    $mail_outsider = true; // dont send footer if mail contain outsider!
			$mail->AddAddress($e);
		}

		// send cc
		if ($cc)
		{
			if (!is_array($cc)) $cc = preg_split('/[,\n\r]+/',trim($cc));
			foreach($cc as $e)
			{
			    if ($e=='') continue;
			    // add @wsatp.com to those without domain
			    if (!strstr($e,"@"))
					$e = $e."@"._config('maildomain');
				else
				    $mail_outsider = true; // dont send footer if mail contain outsider!
				$mail->AddCC($e);
			}
		}

		// send bcc
		if ($bcc)
		{
			if (!is_array($bcc)) $bcc = preg_split('/[,\n\r]+/',trim($bcc));
			foreach($bcc as $e)
			{
			    if ($e=='') continue;
			    // add @wsatp.com to those without domain
			    if (!strstr($e,"@"))
					$e = $e."@"._config('maildomain');
				else
				    $mail_outsider = true; // dont send footer if mail contain outsider!
				$mail->AddBCC($e);
			}
		}

		$mail->Subject = $subject;
	    $mail->AltBody = "To view the message, please use an HTML compatible email viewer!"; // optional, comment out and test
		$mail->MsgHTML($message);

		if ($attachments!='')
		{
		    // attach many file
		    foreach($attachments as $f=>$n)
		    {
		        $mail->AddAttachment($f,$n);
			}
		}


		return $mail->Send();
	}
}

class WEBOX2 
{
	// webox2 thingys
	
	function ced_save()
	{
		if (!$this->is_admin) die('Error: You are not admin?!');
		if (!isset($_POST['_id']) || !isset($_POST['collection']) || !isset($_POST['key'])) die("Error: Missing collection or key or _id params");
		
		$collection = $_POST['collection'];
		if ($collection=='') $collection = "cms_internal"; //die("Error: Invalid collection");
		
		
		$key = $_POST['key'];
		if ($key=='') die("Error: Invalid key");
		
		// a little cleanup
		$html = preg_replace('/(<br\/*>)+$/', '', trim($_POST['html']));
		$html = preg_replace('/(<div><br\/*><\/div>)+$/', '', trim($html));
		
		if ($_POST['_id']=='')
		{
			// if no _id, use key as id
			$upd = array('_id' => $key, trim($key) => $html);
			$this->db->$collection->save($upd, array("safe"=>true));
		}
		else
		{
			$set = array(trim($_POST['key']) => trim($html));
			$this->db->$collection->update(array('_id'=>new MongoID($_POST['_id'])), array('$set'=>$set), array('safe'=>true));
		}
		
		/*
		$set = array(trim($_POST['key']) => trim($_POST['html']));
		$collection = $_POST['collection'];
		
		
		
		$upd = array('_id' => $_POST['_id'], trim($_POST['key']) => $_POST['html']);
		$this->db->theme->save($upd, array("safe"=>true));
		
		
		$this->db->$collection->update(array('_id'=>new SafeMongoID($_POST['_id'])), 
		array('$set'=>$set), array('safe'=>true));
		
		$this->db->$collection->update(array('_id'=>$_POST['_id']), 
		array('$set'=>$set), array('safe'=>true));
		*/
		print 'OK';
	}
	
	function ced_dropfile()
	{
		if (preg_match('/\.(png|jpg|gif)$/i', basename($_POST['filename'])))
		{
			$type='image';
		}
		elseif (preg_match('/\.(zip|pdf)$/i', basename($_POST['filename'])))
		{
			$type='link';
		}
		else
		{
			die("Error: Invalid file type!");
		}
		
		$filename = "files/".basename($_POST['filename']);
		$data = $_POST['data'];
		$data = substr($data, strpos($data, "base64,")+7);
		/* file exists, prompt replace? rename?
		if (file_exists(DIR_ROOT.$filename))
		{
		}
		*/
		file_put_contents(DIR_ROOT.$filename, base64_decode($data));
		
		switch($type)
		{
			case 'image':
				print "<img src=\"$filename\" />";
				break;
				
			case 'link':
				print "<a href=\"$filename\">".$_POST['filename']."</a>";
				break;
				
		}
	}

	function ied_dropfile()
	{
		if (!preg_match('/\.(png|jpg|gif)$/i', basename($_POST['filename'])))
		{
			die("Error: Invalid file type!");
		}
		if (!$_POST['_id']) die("Error: Invalid _id");
		
		$filename = "files/".basename($_POST['filename']);
		$data = $_POST['data'];
		$data = substr($data, strpos($data, "base64,")+7);
		/* file exists, prompt replace? rename?
		if (file_exists(DIR_ROOT.$filename))
		{
		}
		*/
		file_put_contents(DIR_ROOT.$filename, base64_decode($data));
		$set = array(trim($_POST['key']) => $filename);
		$collection = $_POST['collection'];
		if ($collection=='') $collection = "cms_internal"; //die("Error: Invalid collection");		
		$this->db->$collection->update(array('_id'=>new MongoID($_POST['_id'])), array('$set'=>$set), array('safe'=>true));
		print $filename;
	}

	function _webox2_element($params, &$smarty)
	{
		
		// if (!isset($params['id'])) die("Error: webox2 missing id attribute");
		if (!isset($params['key'])) die("Error: webox2 missing key attribute");
		// lookup params[collection] => $this->collection => $this->classname
		$collection = isset($params['collection']) ? $params['collection'] : "cms_internal";
		//(isset($this->collection) ? $this->collection : strtolower(get_class($this)) );
		$attr = $params['key'];
		
		if (isset($params['id']))
		{
			$find = array('_id'=>new MongoID($params['id']));
			$result = $this->db->$collection->findOne($find);
		}
		else
		{
			$find = array('_id'=>$params['key']);
			$result = $this->db->$collection->findOne($find);
		}
		// is span better or div ?
		$element = (isset($params['element']) ? $params['element'] : 'div');
		$action =  (isset($params['action']) ? $params['action'] : 'ced_save');
		$script =  (isset($params['action']) ? URL_BASE_HREF.$params['action'] : $_SERVER['PHP_SELF']);
		print "webox2=\"ced\" data-key=\"$params[key]\" data-id=\"$params[id]\" data-collection=\"$params[collection]\" data-action=\"$action\" data-php=\"$script\"";
	}
	
	function _webox2_block($params, $content, &$smarty, &$repeat)
	{
		
		if($repeat) return; //ignore first call
		
		// if (!isset($params['id'])) die("Error: webox2 missing id attribute");
		if (!isset($params['key'])) die("Error: webox2 missing key attribute");
		// lookup params[collection] => $this->collection => $this->classname
		$collection = isset($params['collection']) ? $params['collection'] : "cms_internal";
		//(isset($this->collection) ? $this->collection : strtolower(get_class($this)) );
		$attr = $params['key'];
		
		if (isset($params['id']))
		{
			$find = array('_id'=>new MongoID($params['id']));
			$result = $this->db->$collection->findOne($find);
		}
		else
		{
			$find = array('_id'=>$params['key']);
			$result = $this->db->$collection->findOne($find);
		}
		// is span better or div ?
		$element = (isset($params['element']) ? $params['element'] : 'div');
		$action =  (isset($params['action']) ? $params['action'] : 'ced_save');
		$script =  (isset($params['action']) ? URL_BASE_HREF.$params['action'] : $_SERVER['PHP_SELF']);
		print "<$element webox2=\"ced\" data-key=\"$params[key]\" data-id=\"$params[id]\" data-collection=\"$params[collection]\" data-action=\"$action\" data-php=\"$script\">";
		if ($result)
			print trim($result[$attr]);
		else
			print trim($content);
		print "</$element>";
	}
}

class AdminModule extends Module {

	var $modules = array();
	var $myini;
	var $login_session_name = 'admin_login';

	function load_modules($dir='admin')
	{
		if (!empty($modules)) return;
		if (file_exists("module.meta")) $myini = parse_ini_file("module.meta");

		// load all modules from mods folder
		foreach(glob(DIR_ROOT."$dir/mods/*/module.meta") as $m)
		{
			$mm = parse_ini_file($m);
			$mm['path'] = dirname($m);
			$mm['url'] = str_replace(DIR_ROOT.$dir.'/','',dirname($m).'/');
			$this->modules[$mm['name']] = $mm;
		}
		uasort($this->modules, array($this,'sseq'));
	}

	function sseq($a, $b)
	{
		if ($a['sequence']==$b['sequence']) return ($a['name'] < $b['name']) ? -1 : 1;
		return ($a['sequence'] < $b['sequence']) ? -1 : 1;
	}


	function get_login($login_required=false)
	{
		// yes, already login
		if (isset($_SESSION[$this->login_session_name]))
		{
			return $_SESSION[$this->login_session_name];
		}
		elseif ($login_required && $this->action!='login' && $this->action!='logout')
		{
			// redirect to login
			if (AJAX) {
				print "<script>document.location = \""._config('admin_login_url', URL_BASE_HREF.'admin/index.php?a=login').'&redir='.urlencode($_SERVER['REQUEST_URI'])."\";</script>";
			}
			else
				header("Location: "._config('admin_login_url', URL_BASE_HREF.'admin/index.php?a=login').'&redir='.urlencode($_SERVER['REQUEST_URI']));
			exit;
		}
		return false;
	}
	
	// drag and drop file for xhEditor
	function cms_dropfile()
	{
		if ($this->dropfile_path=='') $this->dropfile_path = 'files/';
		$tempPath = DIR_ROOT . $this->dropfile_path;
		$localName='';
		if (isset($_SERVER['HTTP_CONTENT_DISPOSITION'])&&preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i',$_SERVER['HTTP_CONTENT_DISPOSITION'],$info)){
			// drag and drop
			$localName=$info[2];
			if (!preg_match('/\.(jpg|jpeg|png|gif|swf|pdf|zip|wmv|avi|wma|mp3|mid)$/i', $localName)) {
				$ret['err'] = 'Invalid file type: '.($localName);
				print json_encode($ret);
				exit;
			}
			file_put_contents($tempPath.$localName,file_get_contents("php://input"));
		}
		elseif(isset($_FILES['filedata']))
		{
			// using $_FILES upload
			$upfile=$_FILES['filedata'];
			if(!isset($upfile) || $upfile['error']!=0)
			{
				$ret['err'] = 'Upload error code: '.($upfile['error']);
				print json_encode($ret);
				exit;
			}
			$localName=$upfile['name'];
			if (!preg_match('/\.(jpg|jpeg|png|gif|pdf|zip)$/i', $localName)) {
				$ret['err'] = 'Invalid file type: '.($localName);
				print json_encode($ret);
				exit;
			}
			move_uploaded_file($upfile['tmp_name'],$tempPath.$localName);
		}
		else
		{
			return;
		}
		
		$ret['err'] = '';
		$ret['msg'] = array('url'=>($this->dropfile_path.$localName), 'localname'=>$localName);
		print json_encode($ret);
		exit;
	}
}

// smarty class for Modules' template
class Template extends Smarty
{
	function __construct()
	{
		parent::__construct();

		// if we are not at root directory, then use the template in the same folder as php
		if (getcwd()==realpath(DIR_ROOT)) {
			$this->template_dir = $this->config_dir = DIR_ROOT.'tpl/';
		}
		else
		{
			$this->template_dir = $this->config_dir = getcwd();
		}
		$this->compile_dir = DIR_ROOT.'.c/';

		// create directory if not found
		if (!is_dir($this->compile_dir))
		{
		    mkdir($this->compile_dir,0777);
		}

		if (isset($_SESSION['themepreview']))
		{
			// disable caching
			$this->setCaching(Smarty::CACHING_OFF);
			// create directory if not found
			$this->compile_dir = DIR_ROOT.'.c/preview/';
			if (!is_dir($this->compile_dir))
			{
			    mkdir($this->compile_dir,0777);
			}
		}

		$this->registerResource('ts', new Smarty_Resource_Themestore());
	}
}

class SafeMongoID extends MongoID {
    public function __construct($id=null) {
        try {
            parent::__construct($id);
        } catch (MongoException $ex) {
            parent::__construct(null);
        }
    }
}
?>
