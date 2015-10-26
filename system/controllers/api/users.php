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
			if(!isset($uri[0]))
			{
				$oauth_error = 'Invalid address.';
				exit;
			}
			$oauth_client = new OAuth($auth['oauth_consumer_key'], $auth['oauth_nonce'], $auth['oauth_token'], $auth['oauth_timestamp'], $auth['oauth_signature']);
				
			$oauth_client->set_variable('stage_url', $C->SITE_URL.'1/users/'.$uri[0].'.'.$format);
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
	
	$features = array('show', 'lookup', 'search', 'suggestion', 'groups');
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
	}elseif($uri[0] == 'show')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if(isset($_GET['user_id']) && is_numeric($_GET['user_id'])) $id = intval($_GET['user_id']);
		elseif(isset($_GET['screen_name']))
		{
			$u = $this->network->get_user_by_username(urldecode($_GET['screen_name']));
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid username.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$id = $u->id;
		}elseif(isset($uri[1]) && is_numeric($uri[1])) $id = intval($uri[1]);
		elseif(isset($uri[1]))
		{
			$u = $this->network->get_user_by_username(urldecode($uri[1]));
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid username.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$id = $u->id;
		}elseif($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			
			if(!$id)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
		}elseif($bauth_status) $id = $user->id;
		else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Specify user id.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if(!$bauth_status)
		{
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid user id.', $_SERVER['REQUEST_URI'], $callback);
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
		}
		
		$twitter_data = new TwitterData($format, $callback, $user->id);
		$answer = $twitter_data->data_header();

		$answer .= $twitter_data->data_section('user');
			$answer .=  $twitter_data->print_user($id);		
			$answer .= ($format == 'json')? ',':''; 
			
			$answer .= $twitter_data->data_section('status', TRUE);
				$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($id).'\' ORDER BY id DESC LIMIT 1';				
				$answer .= $twitter_data->print_status(0, FALSE, $q);
			$answer .= $twitter_data->data_section('status', FALSE, TRUE);	
				
		$answer .= $twitter_data->data_section('user', FALSE, TRUE);
		$answer .= $twitter_data->data_bottom();
		
		echo $answer;
		exit;
	}elseif($uri[0] == 'lookup')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(!$oauth_status && !$bauth_status)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem:'.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
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
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
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
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage U1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}		
		}elseif($bauth_status) $id = $user->id;
		
		
		$twitter_data = new TwitterData($format, $callback, $id, TRUE);
		$answer = $twitter_data->data_header();
		$answer .= $twitter_data->data_section('users', FALSE, FALSE, TRUE, ' type="array"');
		
		if(isset($_REQUEST['user_id']))
		{
			$user_ids = explode(',', urldecode($_REQUEST['user_id']));
			$num_rows = count($user_ids);
			
			if(count($user_ids) > 0)
			{
				foreach($user_ids as $user)
				{
					$answer .= $twitter_data->data_section('user');
						$answer .=  $twitter_data->print_user($user);		
							$answer .= ($format == 'json')? ',':''; 
							$answer .= $twitter_data->data_section('status', TRUE);
							$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($user).'\' AND api_id<>2 AND api_id<>6 ORDER BY pid DESC LIMIT 1';
							$answer .= $twitter_data->print_status(0, FALSE, $q);
						$answer .= $twitter_data->data_section('status', FALSE, TRUE);
					$answer .= $twitter_data->data_section('user', FALSE, TRUE);
					
					$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
					$num_rows--;
				}
			}	
		}
		if(isset($_REQUEST['screen_name']))
		{
			$user_names = explode(',', urldecode($_REQUEST['screen_name']));
			$num_rows = count($user_names);
			
			if(count($user_names) > 0)
			{
				foreach($user_names as $user)
				{
					$u = $this->network->get_user_by_username($user);
					if(!$u) continue;
					$answer .= $twitter_data->data_section('user');
						$answer .=  $twitter_data->print_user($u->id);			
							$answer .= ($format == 'json')? ',':''; 
							$answer .= $twitter_data->data_section('status', TRUE);
							$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($u->id).'\' ORDER BY id DESC LIMIT 1';
							$answer .= $twitter_data->print_status(0, FALSE, $q);
						$answer .= $twitter_data->data_section('status', FALSE, TRUE);
					$answer .= $twitter_data->data_section('user', FALSE, TRUE);
					
					$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
					$num_rows--;
				}
			}
		}
		$answer .= $twitter_data->data_section('users', FALSE,  TRUE, TRUE);
		$answer .= $twitter_data->data_bottom();
		echo $answer;
		exit;
	}elseif($uri[0] == 'search')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(!$oauth_status && !$bauth_status)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem:'.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
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

		if(!isset($_REQUEST['q']) || empty($_REQUEST['q']))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Query parameter required.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}else $_REQUEST['q'] = urldecode($_GET['q']);
		
		$q = 'SELECT id FROM users WHERE username LIKE \'%'.$db2->e($_REQUEST['q']).'%\'';
		if(isset($_REQUEST['page']) && is_numeric($_REQUEST['page']))
		{		
			if(isset($_REQUEST['per_page']) && is_numeric($_REQUEST['per_page']) && $_REQUEST['per_page']<=20)
			$q .= ' LIMIT '.intval($_REQUEST['per_page'])*(intval($_REQUEST['page'])-1).', '.(intval($_REQUEST['per_page']))*(intval($_REQUEST['page']));
			else 
			$q .= ' LIMIT '.(20)*(intval($_REQUEST['page'])-1).', '.(20)*(intval($_REQUEST['page']));
		}
		elseif(isset($_GET['per_page']) && is_numeric($_REQUEST['per_page']) && $_REQUEST['per_page']<=20)
			$q .= ' LIMIT '.intval($_REQUEST['per_page']);

		$res = $db2->query($q);
		$num_rows = $db2->num_rows($res);
		
		if($num_rows>0)
		{
			$twitter_data = new TwitterData($format, $callback, $user->id, TRUE);
			$answer = $twitter_data->data_header();
			$answer .= $twitter_data->data_section('users', FALSE, FALSE, TRUE, ' type="array"');
			while($user = $db2->fetch_object($res))
			{
				$answer .= $twitter_data->data_section('user');
					$answer .=  $twitter_data->print_user($user->id);		
					$answer .= ($format == 'json')? ',':''; 
					
					$answer .= $twitter_data->data_section('status', TRUE);
						$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($user->id).'\' ORDER BY id DESC LIMIT 1';			
						$answer .= $twitter_data->print_status(0, FALSE, $q);
					$answer .= $twitter_data->data_section('status', FALSE, TRUE);	
						
				$answer .= $twitter_data->data_section('user', FALSE, TRUE);
				
				$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
				$num_rows--;
			}
			$answer .= $twitter_data->data_section('users', FALSE,  TRUE, TRUE);
			$answer .= $twitter_data->data_bottom();

			echo $answer;
			exit;	
		}else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
				else echo generate_error($format, 'No Results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}		
	}elseif($uri[0] == 'suggestion' && isset($uri[1]) && $uri[1] == 'category')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;	
	}elseif($uri[0] == 'suggestion')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;		
	}elseif(($uri[0] == 'groups'))
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(!$oauth_status && !$bauth_status)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem:'.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
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

		if(isset($uri[1]) && is_numeric($uri[1])) $id = intval($uri[1]);
		elseif(isset($uri[1]))
		{
			$u = $this->network->get_user_by_username(urldecode($uri[1]));
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid username.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$id = $u->id;
		}
		else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Parameter required.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
			 
		$q = 'SELECT groups.groupname AS gn, groups_followed.group_id AS gi FROM groups_followed, groups, users WHERE groups_followed.user_id=users.id AND groups_followed.group_id=groups.id AND groups.is_public AND groups_followed.user_id='.intval($id).' GROUP BY groups.id';
		
		$res = $db2->query($q);
		$num_rows = $db2->num_rows($res);
		
		if($num_rows > 0)
		{	
			$twitter_data = new TwitterData($format, $callback, $id, TRUE);
			$answer = $twitter_data->data_header();
			$answer .= $twitter_data->data_section('groups', FALSE, FALSE, TRUE, ' type="array"');
				while($obj = $db2->fetch_object($res))
				{
					$answer .= $twitter_data->data_section('group', FALSE);
						$answer .= $twitter_data->data_field('id', $obj->id);
						$answer .= $twitter_data->data_field('name', $obj->gn, FALSE);
					$answer .= $twitter_data->data_section('group', FALSE, TRUE);
					$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
					$num_rows--;
				}
					
				$answer .= $twitter_data->data_section('groups', FALSE, TRUE, TRUE);
			$answer .= $twitter_data->data_bottom();

			echo $answer;
			exit;	
		}else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 403 Not Modified');
				else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}	
	}
	
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;		
?>