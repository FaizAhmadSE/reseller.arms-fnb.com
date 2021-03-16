<?php
	function set_token($module){
	    if(isset($_POST['token'])){
	      $module->token = $_POST['token'];
	      $module->token = str_replace(" ","+",$module->token);
	      $module->token = decrypt_token($module->token);

	      $module->token = parse_json($module, $module->token);
	      $module->return_encrypt = true;
	    }
	    else{
	      $module->return_respond('MISSING_TOKEN');
	    }
	}

	function decrypt_token($module, $str)
	{
	    $str = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $module->config['token']['key'], base64_decode(chunk_split($str)), MCRYPT_MODE_CBC);
	    $pad = ord($str[($len = strlen($str)) - 1]);
	    $len = strlen($str);
	    $pad = ord($str[$len-1]);
	    $decrypted = substr($str, 0, strlen($str) - $pad);
	    return $decrypted;
	}

  	function encrypt_token($module, $str)
  	{
	    $block = 16; //block size for AES is 16
	    $pad = $block - (strlen($str) % $block);
	    $str .= str_repeat(chr($pad), $pad);

	    return base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $module->config['token']['key'], $str, MCRYPT_MODE_CBC));
	}

    function parse_json($module, $data){
		$parsed_data = json_decode($data,true);

        switch (json_last_error()) {
        case JSON_ERROR_NONE:
			return $parsed_data;
        break;
        case JSON_ERROR_DEPTH:
          die('Maximum stack depth exceeded');
        break;
        case JSON_ERROR_STATE_MISMATCH:
          die('Underflow or the modes mismatch');
        break;
        case JSON_ERROR_CTRL_CHAR:
          die('Unexpected control character found');
        break;
        case JSON_ERROR_SYNTAX:
		  die('Syntax error, malformed JSON');
        break;
        case JSON_ERROR_UTF8:
          die('Malformed UTF-8 characters, possibly incorrectly encoded');
        break;
        default:
          die('Unknown error');
        break;
      }
    }
?>