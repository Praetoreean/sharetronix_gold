<?php
	function prepare_header()
	{
		if( !function_exists('apache_request_headers') ) {
			return false;
		}
		$raw_header = apache_request_headers();

		if(isset($raw_header['Authorization']) && preg_match('/OAuth/iu', $raw_header['Authorization']))
		{
			$raw_header = explode(',', $raw_header['Authorization']);

			foreach($raw_header as $k=>$v)
			{
				$raw_header[$k] = preg_replace('/"/', '', $raw_header[$k]);	
				$raw_header[$k] = explode('=', $raw_header[$k]);
				$ready_header[strtolower(trim($raw_header[$k][0]))] = $raw_header[$k][1]; 
			}
			unset($raw_header);
			
			return $ready_header;
		}else return false;
	}
	function prepare_request()
	{
		if(isset($_REQUEST['oauth_consumer_key'])) return $_REQUEST;
		else return false;
	}
	
	function check_if_basic_auth()
	{
		if(isset($_SERVER['PHP_AUTH_USER']) && isset($_SERVER['PHP_AUTH_PW'])) return array(trim($_SERVER['PHP_AUTH_USER']), trim($_SERVER['PHP_AUTH_PW']));
		
		$raw_header = array();
		
		if(function_exists('apache_request_headers')) $raw_header = apache_request_headers(); 
			elseif(isset($_SERVER['HTTP_AUTHORIZATION']) && !empty($_SERVER['HTTP_AUTHORIZATION']))  
				$raw_header['Authorization'] = $_SERVER['HTTP_AUTHORIZATION']; 
					else return false;

		if( !$raw_header || !isset($raw_header['Authorization']) ) {
			return false;
		}
		if( ! preg_match('/^Basic\s(.*)$/iu', $raw_header['Authorization'], $m) ) {
			return false;
		}
		$tmp	= @base64_decode( trim($m[1]) );
		if( ! $tmp || ! preg_match('/^([^\:]+)\:(.*)$/iu', $tmp, $m) ) {
			return false;
		}
		return array( trim($m[1]), trim($m[2]) );
	}
	function detect_app($check = '')
	{	
		switch(strtolower($check))
		{
			case 'tweetdeck': return 5;
				break;
			case 'spaz': return 'spaz';
				break;
		}
		
		if( !function_exists('apache_request_headers') ) {
			return 4;
		}
		
		if(preg_match('/TweetDeck/iu', implode(' ', apache_request_headers()))) return 5;
		elseif(preg_match('/spaz/iu', implode(' ', apache_request_headers()))) return 'spaz';
		
		return 4;
	}
	function get_app_id($name)
	{
		$res = $db2->query('SELECT * FROM applications WHERE detect="'.$name.'" LIMIT 1');
		if($db2->num_rows($res) > 0) 
		{
			$app = $db2->fetch_object($res);
			return $app->id;
		}
		
		return 4;
	}	
	function is_valid_url($link)
	{
		if(!preg_match('/^(http|https):\/\/((([a-z0-9.-]+\.)+[a-z]{2,4})|([0-9\.]{1,4}){4})(\/([a-zà-ÿ0-9-_\—\:%\.\?\!\=\+\&\/\#\~\;\,\@]+)?)?$/', $link)) 
			return FALSE;
		else return TRUE;
	}
	function is_valid_date($date)
	{
		if(preg_match ("/^([0-9]{4})-([0-9]{2})-([0-9]{2})$/", $date))
		{
			$arr = split("-", $date); 
			if(intval(date("Y", time())) < intval($arr[0]) || intval($arr[0]) < 1900) return false;
		      if (is_numeric($arr[0]) && is_numeric($arr[1]) && is_numeric($arr[2])) return checkdate($arr[1], $arr[2], $arr[0]);
		        else return false;
		        
		}else return false;
	}
	function is_valid_data_format($format, $basic = FALSE)
	{
		if($basic && $format != 'xml' && $format != 'json') return false; 
		if($format != 'xml' && $format != 'json' && $format != 'rss' && $format != 'atom') return false;
		
		return true;
	}
	
	function not_in_groups($user_id)
	{
		global $db2, $network, $user;
		
		$not_in_groups	= array();
		
		$r	= $db2->query('SELECT id FROM groups WHERE is_public=0');
		while($obj = $db2->fetch_object($r)) {
			$g	= $network->get_group_by_id($obj->id);
			if( ! $g ) {
				$not_in_groups[]	= $obj->id;
				continue;
			}
			if( $g->is_public == 1 ) {
				continue;
			}
			if( $user->is_logged ) {
				$m	= $network->get_group_members($g->id);
				if( ! isset($m[$user_id]) ) {
					$not_in_groups[]	= $obj->id;
				}
			}
		}
		return $not_in_groups;
	}
	function generate_error($format, $err, $link, $fancy_function=FALSE)
	{	
		global $C;
		$error = '';
		
		if($format != 'xml' && $format != 'json' && $format != 'atom' && $format != 'rss') $format='xml';

		if($format == 'xml')
		{
			$error .= '<?xml version="1.0" encoding="UTF-8" ?'.'>';
			$error .= '<hash>';
			$error .= '<request>'.$link.'</request>';
			$error .= '<error>'.$err.'</error>';
			$error .= '</hash>';			
		}elseif($format == 'json')
		{
			$error .= ($fancy_function && $fancy_function != '?')? $fancy_function.'(':'';
			$error .= ($fancy_function && $fancy_function == '?')? '(':'';
			$error .= '"hash":{';
			$error .= '"request": "'.$link.'",';
			$error .= '"error": "'.$err.'"';
			$error .= '}';
			if($fancy_function) $error .= ')';	
		}elseif($format == 'rss')
		{
			$error .= '<rss version="2.0">';
   			$error .= '<channel>';
   			
			$error .= '<title> '.$C->SITE_TITLE.' API Error </title> ';
			$error .= '<link>http://'.$C->SITE_URL.'/</link> ';
			$error .= '<description>Error message.</description> ';
			$error .= '<item>';
			
			$error .= '<request>'.$link.'</request>';
			$error .= '<error>'.$err.'</error>';
			
			$error .= '</item>';			
			$error .= '</channel></rss>';	
		}elseif($format == 'atom')
		{
			$error .= '<?xml version="1.0" encoding="utf-8"?'.'>';
 			$error .= '<feed xmlns="http://www.w3.org/2005/Atom">';
			$error .= '<link href="http://'.$C->SITE_URL.'/" />';
			$error .= '<id>urn:'.md5(time()).'</id>';
			$error .= '<author>';
			$error .= '<name>'.$C->SITE_URL.'</name>';
			$error .= '</author>';
			 
			 
			$error .= '<entry>';
   			
			$error .= '<title> '.$C->SITE_TITLE.' API Error </title> ';
			$error .= '<link>http://'.$C->SITE_URL.'/</link> ';
			$error .= '<description>Error message.</description> ';
			$error .= '<item>';
			
			$error .= '<request>'.$link.'</request>';
			$error .= '<error>'.$err.'</error>';
						
			$error .= '</entry></feed>';	
		}
		
		return $error;
	}
	function generate_bottom($format, $fancy_function=FALSE)
	{
		if($format == 'rss')
		{
			$bottom = '</channel></rss>';
		}elseif($format == 'atom')
		{
			$bottom = '</feed>';
		}elseif($format=='json')
		{
			$bottom = ($fancy_function)? ')':'';
		}else $bottom = '';
		
		return $bottom;
	}
	function valid_fn($fn)
	{
		if($fn == '?') return true;
		else if(preg_match('/^([a-z0-9_]+)$/iu', $fn)) return true;
 		else return false;
	}
	
	function check_rate_limits($ip, $rate_num)
	{
		global $db2, $C;

		$res = $db2->query('SELECT 1 FROM ip_rates_limit WHERE ip=\''.ip2long($ip).'\' LIMIT 1');
		
		if($db2->num_rows($res) == 0)
		{
			$q = 'INSERT INTO ip_rates_limit(rate_limits, rate_limits_date, ip)';
			$q .= ' VALUES(0, '.intval(date('G', time())).', '.ip2long($ip).')';
			
			$res = $db2->query($q);
			if(!$db2->affected_rows($res)) return false; 
		}
		$q = 'SELECT rate_limits, rate_limits_date';
		$q .= ' FROM ip_rates_limit';
		$q .= ' WHERE ip = \''.ip2long($ip).'\' LIMIT 1';
		
		$res = $db2->query($q);
		$obj = $db2->fetch_object($res);
	
		if(!$obj) return false;		
	
		if( (($obj->rate_limits + $rate_num) < 150) || ($obj->rate_limits_date != date('G', time())))
		{
			if($obj->rate_limits_date != date('G', time()))
			{
				if(restart_rate_limits($ip)) return true;
					else return false;
			}
			
			if(update_rate_limits($ip, $rate_num)) return true;
				else return false;
				
		}else return false;			
	}
	function restart_rate_limits($ip)
	{
		global $db2;
		
		$q = 'UPDATE ip_rates_limit SET rate_limits=0, rate_limits_date=\''.intval(date('G', time())).'\'';
		$q .= ' WHERE ip = \''.ip2long($ip).'\' LIMIT 1';
		
		$res = $db2->query($q);
		
		if($res) return true;	
			else return false;			
	}
	function update_rate_limits($ip, $rate_num)
	{
		global $db2;
		
		$q = 'UPDATE ip_rates_limit';
		$q .= ' SET rate_limits=(rate_limits+'.$rate_num.'), rate_limits_date=\''.intval(date('G', time())).'\'';
		$q .= ' WHERE ip = \''.ip2long($ip).'\' LIMIT 1';
		
		$res = $db2->query($q);
		
		if($db2->affected_rows($res)) return true;	
			else return false;				
	}
?>