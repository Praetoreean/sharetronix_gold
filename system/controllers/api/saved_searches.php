<?php
	setlocale(LC_TIME, 'en_US');	

	require_once( $C->INCPATH.'helpers/func_api.php' );
	require_once( $C->INCPATH.'classes/class_oauth.php' );
	require_once( $C->INCPATH.'classes/class_twitterdata.php' );

	global $user;
	$uri = $this->param('more');
	$format = $this->param('format');
	$oauth_status = false;
	$bauth_status = false;

	if( $auth = check_if_basic_auth() ) 
	{
		$user->logout();
		$res = $user->login( $auth[0], md5($auth[1]) );
		if( ! $res ) $oauth_error = 'Invalid Authorization header.';
		if($user->is_logged) $bauth_status = true;
	}
	elseif( ($auth = prepare_header()) || ($auth = prepare_request()) )
	{
		if(isset($auth['oauth_version']) && $auth['oauth_version'] != '1.0') $oauth_error = 'Not supported OAuth version';
		elseif(isset($auth['oauth_consumer_key'], $auth['oauth_nonce'], $auth['oauth_token'],$auth['oauth_signature_method'], $auth['oauth_signature'], $auth['oauth_timestamp']))
		{
			$ares = (isset($uri[1]))? ('/'.$uri[1]):'';
			$resource = (isset($uri[0]))? ('saved_searches/'.$uri[0].$ares):'saved_searches';
			
			$oauth_client = new OAuth($auth['oauth_consumer_key'], $auth['oauth_nonce'], $auth['oauth_token'], $auth['oauth_timestamp'], $auth['oauth_signature']);
				
			$oauth_client->set_variable('stage_url', $C->SITE_URL.'1/'.$resource.'.'.$format);
			if(isset($auth['oauth_version'])) $oauth_client->set_variable('version', '1.0');
			
			if($oauth_client->is_valid_get_resource_request())
			{
				if($auth['oauth_signature_method'] != 'HMAC-SHA1'){ $oauth_error = 'Unsupported signature method'; }
				elseif(!$oauth_client->decrypt_hmac_sha1()){ $oauth_error = 'Invalid signature'; }	
				//success
				else $oauth_status = true;
				//success
			}$oauth_error =  $oauth_client->get_variable('error_msg');		
		}else $oauth_error = 'Missing OAuth parameters';
	}
	
	if(isset($_REQUEST['callback']) && valid_fn($_REQUEST['callback'])) $callback = $_REQUEST['callback'];
		else $callback = FALSE;

	/*if(!$oauth_status && !$bauth_status)
	{
		if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
			else echo generate_error($format, 'OAuth otorization problem.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}else*/if(!is_valid_data_format($format, TRUE))
	{		
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error('xml', 'Invalid data format requested.', $_SERVER['REQUEST_URI'], $callback);

		exit;
	}elseif(isset($uri[0]) && $uri[0] == 'create' ) 
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			if(!$id)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
		
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server error (Stage U11).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( !$user->is_logged ) 
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage U1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}		
		}elseif($bauth_status) $id = $user->id;	

		if(!isset($_POST['query']) || empty($_POST['query']))
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid query parameter.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		$query	= trim(htmlspecialchars(urldecode($_POST['query'])));
				
		$db2->query('SELECT id, search_string, added_date FROM searches WHERE user_id="'.intval($id).'" AND search_string="'.$db2->e($query).'" LIMIT 1');
		if( $obj = $db2->fetch_object() ) 
		{
			$obj->id			= intval($obj->id);
			$obj->search_string	= stripslashes($obj->search_string);
			$obj->added_date		= gmstrftime('%a %b %d %H:%M:%S +0000 %Y', $obj->added_date);
		}else 
		{
			$obj	= new stdClass;
			$obj->search_string	= stripslashes($query);
			$obj->added_date		= gmstrftime('%a %b %d %H:%M:%S +0000 %Y', time());
			$tmp_url	= trim($C->SITE_URL,'/');
			$tmp_url	= str_replace(array('http://','https://'),'',$tmp_url);
			$tmp_url	= '/'.trim($tmp_url,'/').'/search/tab:posts/s:'.urlencode($query);
			$search_key	= md5($query."\n".serialize(array('link','image','video','file'))."\n\n\n".serialize('')."\n".serialize(''));
			
			$q = 'INSERT INTO searches SET user_id="'.intval($id).'", search_key="'.$db2->e($search_key).'", search_string="'.$db2->e($query).'", search_url="'.$db2->e($tmp_url).'", added_date="'.time().'", total_hits=0, last_results=0';
			
			$db2->query( $q );
			$obj->id	= intval($db2->insert_id());
		}
		
		$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();
		
			$answer .= $twitter_data->data_section('saved_search');
				$answer .= $twitter_data->data_field('id', $obj->id);
				$answer .= $twitter_data->data_field('query', $obj->search_string);
				$answer .= $twitter_data->data_field('created_at', $obj->added_date, FALSE);
			$answer .= $twitter_data->data_section('saved_search', FALSE, TRUE);
		$answer .= $twitter_data->data_bottom();
			
		echo $answer;
		exit;
	}
	elseif(isset($uri[0]) &&  $uri[0] == 'show' ) 
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			if(!$id)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
		
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server error (Stage U11).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( !$user->is_logged ) 
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage U1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}		
		}elseif($bauth_status) $id = $user->id;	

		$db2->query('SELECT id, user_id, search_string, added_date FROM searches WHERE id="'.intval($uri[1]).'" AND user_id="'.intval($id).'" LIMIT 1');
		if($obj = $db2->fetch_object()) 
		{
			if($obj->user_id != $id)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid parameter provided.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$obj->id			= intval($obj->id);
			$obj->search_string	= stripslashes($obj->search_string);
			$obj->added_date		= gmstrftime('%a %b %d %H:%M:%S +0000 %Y', $obj->added_date);		
			
			$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();
		
			$answer .= $twitter_data->data_section('saved_search');
				$answer .= $twitter_data->data_field('id', $obj->id);
				$answer .= $twitter_data->data_field('query', $obj->search_string);
				$answer .= $twitter_data->data_field('created_at', $obj->added_date, FALSE);
			$answer .= $twitter_data->data_section('saved_search', FALSE, TRUE);
			$answer .= $twitter_data->data_bottom();

			echo $answer;
			exit;
		}else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
				else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}
	elseif(isset($uri[0]) &&  $uri[0] == 'destroy' ) 
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			if(!$id)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
		
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server error (Stage U11).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( !$user->is_logged ) 
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage U1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}		
		}elseif($bauth_status) $id = $user->id;	
		
		if((isset($uri[1]) && !is_numeric($uri[1])) || !isset($uri[1]))
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid parameter provided.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		$db2->query('SELECT id, search_string, added_date FROM searches WHERE id="'.intval($uri[1]).'" AND user_id="'.intval($id).'" LIMIT 1');
		if($obj = $db2->fetch_object()) {
			$obj->id			= intval($obj->id);
			$obj->search_string	= stripslashes($obj->search_string);
			$obj->added_date		= gmstrftime('%a %b %d %H:%M:%S +0000 %Y', $obj->added_date);
			$db2->query('DELETE FROM searches WHERE id="'.$obj->id.'" LIMIT 1');
			
			$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();
		
			$answer .= $twitter_data->data_section('saved_search');
				$answer .= $twitter_data->data_field('id', $obj->id);
				$answer .= $twitter_data->data_field('query', $obj->search_string);
				$answer .= $twitter_data->data_field('created_at', $obj->added_date, FALSE);
			$answer .= $twitter_data->data_section('saved_search', FALSE, TRUE);
			$answer .= $twitter_data->data_bottom();
			echo $answer;
			exit;
		}else
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 304 Not modified');
				else echo generate_error($format, 'No data deleted.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}
	elseif(isset($this->request[0]) && $this->request[0] == 'saved_searches')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			if(!$id)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
		
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server error (Stage U11).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( !$user->is_logged ) 
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage U1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}		
		}elseif($bauth_status) $id = $user->id;

		$res = $db2->query('SELECT id, search_string, added_date FROM searches WHERE user_id="'.intval($id).'" ORDER BY id DESC');
		if(($db2->num_rows($res) > 0))
		{	
			$twitter_data = new TwitterData($format, $callback, $id, TRUE);
			$answer = $twitter_data->data_header();
			
			if($twitter_data->is_feed())
				while($stat = $db2->fetch_object($res)) 
					$answer .= $twitter_data->print_status_simple($stat->pid);
			else
			{
				$answer .= $twitter_data->data_section('saved_searches', FALSE, FALSE, TRUE, ' type="array"');
				while($obj = $db2->fetch_object($res))
				{	
					$answer .= $twitter_data->data_section('saved_search');
						$answer .= $twitter_data->data_field('id', $obj->id);
						$answer .= $twitter_data->data_field('query', stripslashes($obj->search_string));
						$answer .= $twitter_data->data_field('created_at', gmstrftime('%a %b %d %H:%M:%S +0000 %Y', $obj->added_date), FALSE);
					$answer .= $twitter_data->data_section('saved_search', FALSE, TRUE);
					
					$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
					$num_rows--;
				}
				$answer .= $twitter_data->data_section('saved_searches', FALSE,  TRUE, TRUE);
			}
			$answer .= $twitter_data->data_bottom();
			echo $answer;
			exit;
		}else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 403 Not Modified');
				else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;	
?>