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
			$resource = (isset($uri[0]))? ('direct_messages/'.$uri[0].$ares):'direct_messages';
			
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

	if(isset($uri[0]) && $uri[0] == 'new')
	{	
		if($_SERVER['REQUEST_METHOD'] != 'POST'  || (!is_valid_data_format($format, TRUE)))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem: '.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			$app_id = $oauth_client->get_value_in_consumer_key('app_id');
			if(!$id)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 403 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid user id.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage SX2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status)
		{
			$id = $user->id;	
			if(isset($_POST['source'])) $app_id = detect_app($_POST['source']); 
			else $app_id = detect_app(); 
			
			if(!is_numeric($app_id)) $app_id = get_app_id($app_id);
		}
		$sender_name = $user->info->username;;
		
		if(isset($_POST['user_id']) && is_numeric($_POST['user_id'])) $to_id = intval($_POST['user_id']);
		elseif(isset($_POST['screen_name']))
		{
			$u = $this->network->get_user_by_username(urldecode($_POST['screen_name']));
			if(!$u)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid username.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$to_id = $u->id;
			$recipient_name = $u->info->username;
		}else
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Parameter required.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if(!isset($recipient_name) && isset($to_id))
		{
			$u = $this->network->get_user_by_id(intval($to_id));
			if(!$u)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid username.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$recipient_name = $u->info->username;	
		}else $recipient_name = 'none';
		
		if(!isset($_POST['text']) || empty($_POST['text']))
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid text parameter.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		$_POST['text'] = htmlspecialchars(stripslashes(urldecode($_POST['text'])));
		
		if($to_id == $id)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid user id.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$r = $db2->query('SELECT message FROM posts_pr WHERE user_id='.intval($id).' ORDER BY id DESC LIMIT 1');
		if($db2->num_rows($r) > 0) 
		{
			$obj = $db2->fetch_object($r); 
			if($obj->message == $_POST['text'])
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Provide different message.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}
	
		$newpost	= new newpost();
		$newpost->set_api_id( $app_id );
		$newpost->set_to_user( $to_id ); 
		$newpost->set_message( $_POST['text'] );
		$ok	= $newpost->save();
		if( !$ok ) 
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error (Stage N2).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		else 
		{
			$p_id = explode("_", $ok);
			$res = $db2->query('SELECT id AS pid, user_id, to_user, message, date FROM posts_pr WHERE id=\''.intval($p_id[0]).'\' LIMIT 1');
			if($db2->num_rows($res) == 0) 
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage 22).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$message = $db2->fetch_object($res);

			$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();
		
			$answer .= $twitter_data->data_section('direct_message');
				$answer .= $twitter_data->data_field('id', $message->pid, TRUE, FALSE);
				$answer .= $twitter_data->data_field('sender_id', $message->user_id, TRUE, FALSE);
				$answer .= $twitter_data->data_field('text', $message->message);
				$answer .= $twitter_data->data_field('recipient_id', $message->to_user, TRUE, FALSE);
				$answer .= $twitter_data->data_field('created_at', gmdate('D M d H:i:s \+0000 Y', $message->date));
				$answer .= $twitter_data->data_field('sender_screen_name', $sender_name);
				$answer .= $twitter_data->data_field('recipient_screen_name', $recipient_name);
				
				$answer .= $twitter_data->data_section('sender', TRUE);
					$answer .= $twitter_data->print_user($message->user_id);
				$answer .= $twitter_data->data_section('sender', FALSE, TRUE);
				$answer .= ($format == 'json')? ',' : '';	
				
				$answer .= $twitter_data->data_section('recipient', TRUE);
					$answer .= $twitter_data->print_user($message->to_user);
				$answer .= $twitter_data->data_section('recipient', FALSE, TRUE);	
					
			$answer .= $twitter_data->data_section('direct_message', FALSE, TRUE);
			$answer .= $twitter_data->data_bottom();

			echo $answer;
			exit;
		}	
	}elseif(isset($uri[0]) && $uri[0] == 'destroy')
	{
		if(($_SERVER['REQUEST_METHOD'] != 'POST'  && $_SERVER['REQUEST_METHOD'] != 'DELETE') || (!is_valid_data_format($format, TRUE)))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem: '.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif($oauth_status)
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
					else echo generate_error($format, 'You have no permission forthis action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (Stage PD1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage SG2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;

		if(isset($uri[1])) $m_id = intval($uri[1]);
		else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid id parameter.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		$private_post = new post('private', $m_id);	

		if(!$private_post)
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Bad Request');
				else echo generate_error($format, 'Post author authenticating problem.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		$twitter_data = new TwitterData($format, $callback, $id);
		$answer = $twitter_data->data_header();

		$answer .= $twitter_data->data_section('direct_message');
			$answer .= $twitter_data->data_field('id', $m_id);
			$answer .= $twitter_data->data_field('sender_id', $private_post->post_user->id);
			$answer .= $twitter_data->data_field('text', htmlspecialchars($private_post->post_message));
			$answer .= $twitter_data->data_field('recipient_id', $private_post->post_to_user->id);
			$answer .= $twitter_data->data_field('created_at', gmdate('D M d H:i:s \+0000 Y', $private_post->post_date));
			$answer .= $twitter_data->data_field('sender_screen_name', 'none');
			$answer .= $twitter_data->data_field('recipient_screen_name', 'none');
			
			$answer .= $twitter_data->data_section('sender', TRUE);
				$answer .= $twitter_data->print_user($private_post->post_user->id);
			$answer .= $twitter_data->data_section('sender', FALSE, TRUE);
			$answer .= ($format == 'json')? ',' : '';	
			
			$answer .= $twitter_data->data_section('recipient', TRUE);
				$answer .= $twitter_data->print_user($private_post->post_to_user->id);
			$answer .= $twitter_data->data_section('recipient', FALSE, TRUE);	
				
		$answer .= $twitter_data->data_section('direct_message', FALSE, TRUE);
		$answer .= $twitter_data->data_bottom();

		if($private_post->delete_this_post())
		{
			echo $answer;
			exit;
		}else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error (Stage 4).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}else
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
				else echo generate_error($format, 'OAuth otorization problem: '.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
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
			
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (Stage PD1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage SG2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;

		$field = (isset($uri[0]) && $uri[0] == 'sent') ? 'user_id' : 'to_user';	
		$q = 'SELECT id, user_id, to_user, message, date  FROM posts_pr WHERE '.$field.' =\''.intval($id).'\'';	
		
		if(isset($_GET['since_id']) && is_numeric($_GET['since_id'])) $q .= ' AND id>'.intval($_GET['since_id']);
		if(isset($_GET['max_id']) && is_numeric($_GET['max_id'])) $q .= ' AND id<'.intval($_GET['max_id']);
		
		$q .= ' ORDER BY id DESC ';
		
		if(isset($_GET['count']) && !isset($_GET['page']))
			{if(is_numeric($_GET['count']) && $_GET['count']<200) $q .= ' LIMIT '.intval($_GET['count']);}
		elseif(isset($_GET['page']) && !isset($_GET['count']))
			{if(is_numeric($_GET['page'])) $q .= ' LIMIT '.(20)*(intval($_GET['page'])-1).', '.(20)*(intval($_GET['page']));}
		elseif(isset($_GET['page']) && isset($_GET['count']))
			{if(is_numeric($_GET['page']) && is_numeric($_GET['count'])) 
				$q .= ' LIMIT '.(intval($_GET['count']))*(intval($_GET['page'])-1).', '.(intval($_GET['count']))*(intval($_GET['page']));}
		else $q .= ' LIMIT 20';

		$res = $db2->query($q); 
		$num_rows = $db2->num_rows($res);
		
		if($num_rows > 0)
		{	
			$twitter_data = new TwitterData($format, $callback, $id, TRUE);
			$answer = $twitter_data->data_header();
			
			if($twitter_data->is_feed())
				while($stat = $db2->fetch_object($res)) 
					$answer .= $twitter_data->print_status_simple($stat->pid, 'private');
			else
			{
				$answer .= $twitter_data->data_section('direct-messages', FALSE, FALSE, TRUE, ' type="array"');
					while($message = $db2->fetch_object($res))
					{	
						$answer .= $twitter_data->data_section('direct_message');
							$answer .= $twitter_data->data_field('id', $message->id);
							$answer .= $twitter_data->data_field('sender_id', $message->user_id);
							$answer .= $twitter_data->data_field('text', htmlspecialchars($message->message));
							$answer .= $twitter_data->data_field('recipient_id', $message->to_user);
							$answer .= $twitter_data->data_field('created_at', gmdate('D M d H:i:s \+0000 Y', $message->date));
							$answer .= $twitter_data->data_field('sender_screen_name', 'none');
							$answer .= $twitter_data->data_field('recipient_screen_name', 'none');
							
							$answer .= $twitter_data->data_section('sender', TRUE);
								$answer .= $twitter_data->print_user($message->user_id);
							$answer .= $twitter_data->data_section('sender', FALSE, TRUE);
							$answer .= ($format == 'json')? ',' : '';	
							
							$answer .= $twitter_data->data_section('recipient', TRUE);
								$answer .= $twitter_data->print_user($message->to_user);
							$answer .= $twitter_data->data_section('recipient', FALSE, TRUE);	
								
						$answer .= $twitter_data->data_section('direct_message', FALSE, TRUE);
						
						$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
						$num_rows--;
					}
				$answer .= $twitter_data->data_section('direct-messages', FALSE,  TRUE, TRUE);	
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