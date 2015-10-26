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
			if(!isset($uri[0]))
			{
				$oauth_error = 'Invalid address.';
				exit;
			}
			$oauth_client = new OAuth($auth['oauth_consumer_key'], $auth['oauth_nonce'], $auth['oauth_token'], $auth['oauth_timestamp'], $auth['oauth_signature']);
				
			$oauth_client->set_variable('stage_url', $C->SITE_URL.'1/friendships/'.$uri[0].'.'.$format);
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
	
	$features = array('create', 'destroy', 'exists', 'show', 'incoming', 'outgoing');
	if(isset($_REQUEST['callback']) && valid_fn($_REQUEST['callback'])) $callback = $_REQUEST['callback'];
		else $callback = FALSE;

	if(!is_valid_data_format($format, TRUE))
	{		
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error('xml', 'Invalid data format requested.', $_SERVER['REQUEST_URI'], $callback);

		exit;
	}elseif(!isset($uri[0]) || !in_array($uri[0], $features))
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif($uri[0] == 'create' || $uri[0] == 'destroy')
	{	
		if($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if(isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])) $follow_id = intval($_REQUEST['user_id']);
		elseif(isset($_REQUEST['screen_name']) || (isset($uri[1]) && !is_numeric($uri[1])))
		{
			if(isset($_REQUEST['screen_name'])) $u = $this->network->get_user_by_username(urldecode($_REQUEST['screen_name']));
			else $u = $this->network->get_user_by_username(urldecode($uri[1]));
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid username.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$follow_id = $u->id;
		}elseif(isset($uri[1]) && is_numeric($uri[1])) $follow_id = intval($uri[1]);
		else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid user id provided.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem: '.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif($oauth_status)
		{
			$user_id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			if(!$user_id)
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
			$u	= $this->network->get_user_by_id($user_id);
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server Error (Stage CD1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
	
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage CD2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $user_id = $user->id;
		
		if(!isset($user_id) || !isset($follow_id) || ($user_id == $follow_id))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid user ids.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if($uri[0] == 'create')
		{
			$info	= $this->network->get_user_follows($user_id);
			if(!$info) 
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage 1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}	
			$following	= array_keys($info->follow_users);
			
			if(!in_array($follow_id, $following)) $ok = $user->follow($follow_id);
			else
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 403 Forbidden');
					else echo generate_error($format, 'You have already followed this user.', $_SERVER['REQUEST_URI'], $callback);
				exit;	
			}
		}
		elseif($uri[0] == 'destroy')
		{
			$info	= $this->network->get_user_follows($user_id);
			if(!$info) 
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage V1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}	
			$following	= array_keys($info->follow_users);
			
			if(in_array($follow_id, $following)) $ok = $user->follow($follow_id, FALSE);
			else
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
					else echo generate_error($format, 'This user is not your friend.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}	
		}
		
		if( !$ok ) 
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error (Stage CD3).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		else 
		{
			$twitter_data = new TwitterData($format, $callback, $user_id);
			$answer = $twitter_data->data_header();
	
			$answer .= $twitter_data->data_section('user');	
				$answer .=  $twitter_data->print_user($user_id);	
					$answer .= ($format == 'json')? ',' : '';	
					$answer .= $twitter_data->data_section('status', TRUE);
						$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($user_id).'\' AND api_id<>2 AND api_id<>6 ORDER BY id DESC LIMIT 1';				
						$answer .=  $twitter_data->print_status(0, FALSE, $q);	
					$answer .= $twitter_data->data_section('status', FALSE, TRUE);			
			$answer .= $twitter_data->data_section('user', FALSE, TRUE);
			$answer .= $twitter_data->data_bottom();
			
			echo $answer;
			exit;
		}
	}elseif($uri[0] == 'exists')
	{	
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if(isset($_GET['user_a']) && is_numeric($_GET['user_a'])) $user_a = intval($_GET['user_a']);
		if(isset($_GET['user_b']) && is_numeric($_GET['user_b'])) $user_b = intval($_GET['user_b']);
		
		if(!isset($user_a) || !isset($user_b) || ($user_a == $user_b))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid user ids.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}else
		{
			/*if(!check_rate_limits($_SERVER['REMOTE_ADDR'], 1))
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
			$u = $this->network->get_user_by_id($user_a);
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid user.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$user->logout();
			$user->login($u->username, $u->password); 
			if( !$user->is_logged ) 
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage 3).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$ok = $user->if_follow_user($user_b);
			
			$twitter_data = new TwitterData($format, $callback, -1);
			$answer = $twitter_data->data_header();
			
			if( $ok ) $answer .= $twitter_data->data_field('friends', 'true', FALSE);
				else $answer .= $twitter_data->data_field('friends', 'false', FALSE);
				
			$answer .= $twitter_data->data_bottom();
				
			echo $answer;
			exit;	
		}	
	}elseif($uri[0] == 'show')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if(isset($_GET['source_id']) && is_numeric($_GET['source_id'])) $source_id = intval($_GET['source_id']);
		elseif(isset($_GET['source_screen_name']))
		{
			$u = $this->network->get_user_by_username(urldecode($_GET['source_screen_name']));
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid user.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$source_id = $u->id;
		}		
		if(isset($_GET['target_id']) && is_numeric($_GET['target_id'])) $target_id = intval($_GET['target_id']);
		elseif(isset($_GET['target_screen_name']))
		{
			$u = $this->network->get_user_by_username(urldecode($_GET['target_screen_name']));
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid user.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$target_id = $u->id;
		}else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Parameter required.', $_SERVER['REQUEST_URI'], $callback);
				exit;
		}
		
		if(!isset($source_id) || !isset($target_id) || ($source_id == $target_id))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid user ids.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		/*if(!check_rate_limits($_SERVER['REMOTE_ADDR'], 1))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}*/
		$s_u	= $this->network->get_user_by_id($source_id);
		$t_u	= $this->network->get_user_by_id($target_id);
		if(!$s_u || !$t_u)
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid user id.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		$user->logout();
		$user->login($s_u->username, $s_u->password); 
		if( ! $user->is_logged ) 
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error (Stage 4).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$user_result = new stdClass;
		
		$stat = $user->if_follow_user($target_id);
		if($stat) $user_result->source_follow_target = 'true'; 
			else $user_result->source_follow_target = 'false';		
		
		$user->logout();
		$user->login($t_u->username, $t_u->password); 
		if( ! $user->is_logged ) 
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$stat = $user->if_follow_user($source_id);
		if($stat) $user_result->target_follow_source = 'true'; else $stat='false';				

		$twitter_data = new TwitterData($format, $callback, $s_u->id);
			$answer = $twitter_data->data_header();
			$answer .= ($format == 'json')? '{':''; 
			$answer .= $twitter_data->data_section('relationship', TRUE);
				$answer .= $twitter_data->data_section('source', TRUE);
					$answer .= $twitter_data->data_field('id', $source_id);
					$answer .= $twitter_data->data_field('screen_name', $s_u->username);
					$answer .= $twitter_data->data_field('following', $user_result->source_follow_target);
					$answer .= $twitter_data->data_field('followed_by', $user_result->target_follow_source, FALSE);
				$answer .= $twitter_data->data_section('source', FALSE, TRUE);
				
				$answer .= ($format == 'json')? ',':''; 
					
				$answer .= $twitter_data->data_section('target', TRUE);
					$answer .= $twitter_data->data_field('id', $target_id);
					$answer .= $twitter_data->data_field('screen_name', $t_u->username);
					$answer .= $twitter_data->data_field('following', $user_result->target_follow_source);
					$answer .= $twitter_data->data_field('followed_by', $user_result->source_follow_target, FALSE);
				$answer .= $twitter_data->data_section('target', FALSE, TRUE);	
				$answer .= $twitter_data->data_bottom();
			$answer .= $twitter_data->data_section('relationship', FALSE, TRUE);	
			$answer .= ($format == 'json')? '}':''; 
		echo $answer;
		exit;	
	
	}elseif($uri[0] == 'incoming')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;			
	}elseif($uri[0] == 'outgoing')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;	
	}
	
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;	
?>