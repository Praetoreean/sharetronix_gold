<?php
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
			$resource = (isset($uri[0]))? ('favorites/'.$uri[0].$ares):'favorites';

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
	if(!is_valid_data_format($format, TRUE))
	{		
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error('xml', 'Invalid data format requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}
	
	if(isset($_REQUEST['callback']) && valid_fn($_REQUEST['callback'])) $callback = $_REQUEST['callback'];
		else $callback = FALSE;

	if(!$oauth_status && !$bauth_status)
	{
		if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
			else echo generate_error($format, 'OAuth otorization problem: '.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif(isset($uri[0]) && ($uri[0] == 'create' || $uri[0] == 'destroy'))
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'DELETE' && $_SERVER['REQUEST_METHOD'] != 'GET')
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
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
	
			$u	= $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage CD1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$user->logout();
			$user->login( $u->username, $u->password);
			if( ! $user->is_logged ) {
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage CD22).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;
		
		if(!isset($uri[1]) || !is_numeric($uri[1]))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid favorite id parameter.', $_SERVER['REQUEST_URI'], $callback);
			exit;	
		}	
		
		$post	= new post('public', intval($uri[1]));
		
		if($uri[0] == 'create') $res = $post->fave_post();
			else $res = $post->fave_post(FALSE);

		if($res)
		{
			$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();

			$answer .= $twitter_data->data_section('status');
				$answer .= $twitter_data->print_status(intval($uri[1]), TRUE);	
					
					$answer .= $twitter_data->data_section('user', TRUE);				
						$answer .=  $twitter_data->print_user($post->post_user->id);	
					$answer .= $twitter_data->data_section('user', FALSE, TRUE);	
					
			$answer .= $twitter_data->data_section('status', FALSE, TRUE);
			$answer .= $twitter_data->data_bottom();
	
			echo $answer;
			exit;
		}else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid favorites id.', $_SERVER['REQUEST_URI'], $callback);
			exit;	
		}
		
	}elseif(isset($this->request[0]) && $this->request[0] == 'favorites' && !isset($uri[0]))
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			if(!$id)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($user->is_logged) $id = $user->id;
		
		$q = 'SELECT post_id AS pid FROM post_favs WHERE user_id=\''.intval($id).'\' AND post_type="public" ORDER BY date DESC';
		if(isset($_GET['page']) && is_numeric($_GET['page'])) $q .= ' LIMIT '.(20)*(intval($_GET['page'])-1).', '.(20)*(intval($_GET['page']));
		else $q .= ' LIMIT 20';

		$res = $db2->query($q);
		$num_rows = $db2->num_rows($res);

		if($num_rows > 0)
		{
			if(!$user->is_logged)
			{
				$user->id	= $id;
				$user->is_logged	= TRUE;
			}

			$twitter_data = new TwitterData($format, $callback, $id, TRUE);
			$answer = $twitter_data->data_header();
			
			if($twitter_data->is_feed())
				while($stat = $db2->fetch_object($res)) 
					$answer .= $twitter_data->print_status_simple($stat->pid, 'public');
			else
			{
				$answer .= $twitter_data->data_section('statuses', FALSE, FALSE, TRUE, ' type="array"');
					while($stat = $db2->fetch_object($res))
					{	
						$answer .= $twitter_data->data_section('status');
							$answer .= $twitter_data->print_status($stat->pid, TRUE);	
								
								$answer .= $twitter_data->data_section('user', TRUE);	
									$usr = $db2->query('SELECT user_id FROM posts WHERE id="'.$stat->pid.'" ORDER BY id DESC LIMIT 1');
									$usr = $db2->fetch_object($usr);		
									
									$answer .=  $twitter_data->print_user($usr->user_id);	
								$answer .= $twitter_data->data_section('user', FALSE, TRUE);	
								
						$answer .= $twitter_data->data_section('status', FALSE, TRUE);
						
						$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
						$num_rows--;
					}
				$answer .= $twitter_data->data_section('statuses', FALSE,  TRUE, TRUE);
			}
			$answer .= $twitter_data->data_bottom();
			
			echo $answer;
			exit;
		}else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
				else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}
	
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;	
?>