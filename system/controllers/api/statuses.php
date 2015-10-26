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
	$not_in_groups	= '';

	if( ($auth = prepare_request()) || ($auth = prepare_header()))
	{
		if(isset($auth['oauth_version']) && $auth['oauth_version'] != '1.0') $oauth_error = 'Not supported OAuth version';
		elseif(isset($auth['oauth_consumer_key'], $auth['oauth_nonce'], $auth['oauth_token'],$auth['oauth_signature_method'], $auth['oauth_signature'], $auth['oauth_timestamp']))
		{
			if(!isset($uri[0]))
			{
				$oauth_error = 'Invalid address.';
				exit;
			}
			$ares = (isset($uri[1]))? ('/'.$uri[1]):'';
			
			$oauth_client = new OAuth($auth['oauth_consumer_key'], $auth['oauth_nonce'], $auth['oauth_token'], $auth['oauth_timestamp'], $auth['oauth_signature']);
				
			$oauth_client->set_variable('stage_url', $C->SITE_URL.'1/statuses/'.$uri[0].$ares.'.'.$format);
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
	}elseif( $auth = check_if_basic_auth() ) 
	{
   		$user->logout();
		$res = $user->login( $auth[0], md5($auth[1]) );
		if( !$res ) $oauth_error = 'Invalid Authorization header.';
		if($user->is_logged) $bauth_status = true;
	}

	$features = array('public_timeline', 'user_timeline', 'mentions', 'update', 'destroy', 'friends', 'followers', 'friends_timeline', 'home_timeline', 'show', 'group_update', 'commented', 'private_mentions', 'private_destroy', 'private_comments', 'comments', 'replies');
	
	if(isset($_REQUEST['callback']) && valid_fn($_REQUEST['callback'])) $callback = $_REQUEST['callback'];
		else $callback = FALSE;

	if(!is_valid_data_format($format))
	{		
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error('xml', 'Invalid data format requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif(!isset($uri[0]) || !in_array($uri[0], $features))
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif($uri[0] == 'public_timeline')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		$res = $db2->query('SELECT posts.id AS pid, posts.user_id AS uid FROM posts, users WHERE users.id=posts.user_id AND posts.user_id <>0 AND users.avatar <> \'\'  AND posts.api_id <> 2 AND posts.api_id <> 6 AND posts.group_id=0 ORDER BY pid DESC LIMIT 20');		
		$num_rows = $db2->num_rows($res);
		if($num_rows > 0)
		{	
			$twitter_data = new TwitterData($format, $callback, -1, TRUE);
			$answer = $twitter_data->data_header();

			if($twitter_data->is_feed())
				while($stat = $db2->fetch_object($res)) 
					$answer .= $twitter_data->print_status_simple($stat->pid);
			else
			{
				$answer .= $twitter_data->data_section('statuses', FALSE, FALSE, TRUE, ' type="array"');
					while($stat = $db2->fetch_object($res))
					{	
						$answer .= $twitter_data->data_section('status');
							$answer .= $twitter_data->print_status($stat->pid, TRUE);	
								
								$answer .= $twitter_data->data_section('user', TRUE);				
									$answer .=  $twitter_data->print_user($stat->uid);	
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
	}elseif($uri[0] == 'user_timeline')
	{	
		if($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}	
		if(isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])) $id = $_REQUEST['user_id'];
		elseif(isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) $id = $_REQUEST['id'];
		elseif(isset($uri[1]) && is_numeric($uri[1])) $id = $uri[1]; 
		elseif(isset($_REQUEST['screen_name']) || isset($uri[1]))
		{
			$screen_name = (isset($_REQUEST['screen_name']))? $_REQUEST['screen_name'] : $uri[1];
			$u = $this->network->get_user_by_username(urldecode($screen_name));
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid user id.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$id = $u->id;
		}elseif($bauth_status) $id = $user->id;
		elseif($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server Error PT1.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}

			$user->logout();
			$user->login($u->username, $u->password); 
			if(!$user->is_logged)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (UT1X).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Parameter required.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if( !$user->is_logged || !$user->info->is_network_admin ) 
		{
			$not_in_groups	= array();
			$not_in_groups 	= not_in_groups($id);
			$not_in_groups	= count($not_in_groups)>0 ? ('AND group_id NOT IN('.implode(', ', $not_in_groups).')') : '';
		}
		
		$q = 'SELECT id AS pid FROM posts WHERE user_id<>0 '.$not_in_groups.' AND user_id=\''.intval($id).'\' AND api_id<>2 AND api_id<>6 ';

		if(isset($_REQUEST['since_id']) && is_numeric($_REQUEST['since_id'])) $q .= ' AND id>'.intval($_REQUEST['since_id']);
		if(isset($_REQUEST['max_id']) && is_numeric($_REQUEST['max_id'])) $q .= ' AND id<'.intval($_REQUEST['max_id']);
		
		$q .= ' ORDER BY id DESC ';
		
		if(isset($_REQUEST['count']) && !isset($_REQUEST['page'])) 
			{if(is_numeric($_REQUEST['count']) && $_REQUEST['count']<200) $q .= ' LIMIT '.intval($_REQUEST['count']);}
		elseif(isset($_REQUEST['page']) && !isset($_GET['count']))
			{if(is_numeric($_REQUEST['page'])) $q .= ' LIMIT '.(20)*(intval($_REQUEST['page'])-1).', '.(20)*(intval($_REQUEST['page']));}
		elseif(isset($_REQUEST['page']) && isset($_REQUEST['count']))
			{if(is_numeric($_REQUEST['page']) && is_numeric($_REQUEST['count'])) 
				$q .= ' LIMIT '.(intval($_REQUEST['count']))*(intval($_REQUEST['page'])-1).', '.(intval($_REQUEST['count']))*(intval($_REQUEST['page']));}
		else $q .= ' LIMIT 20';

		$res = $db2->query($q);
		$num_rows = $db2->num_rows($res);

		if($num_rows > 0)
		{
			$twitter_data = new TwitterData($format, $callback, $id, TRUE);
			$answer = $twitter_data->data_header();
			
			if($twitter_data->is_feed())
				while($stat = $db2->fetch_object($res)) 
					$answer .= $twitter_data->print_status_simple($stat->pid);
			else
			{
				$answer .= $twitter_data->data_section('statuses', FALSE, FALSE, TRUE, ' type="array"');
				while($stat = $db2->fetch_object($res))
				{	
					$answer .= $twitter_data->data_section('status');
						$answer .= $twitter_data->print_status($stat->pid, TRUE);	
							
							$answer .= $twitter_data->data_section('user', TRUE);				
								$answer .=  $twitter_data->print_user($id);	
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
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
				else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}elseif($uri[0] == 'mentions' || $uri[0] == 'private_mentions' || $uri[0] == 'replies')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(!$oauth_status && !$bauth_status)
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem.', $_SERVER['REQUEST_URI'], $callback);
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
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/

		}else $id = $user->id;

		$table = ($uri[0] == 'mentions' || $uri[0] == 'replies')? 'posts_mentioned':'posts_pr_mentioned';

		$q = 'SELECT post_id FROM '.$table.' WHERE user_id=\''.intval($id).'\' ';
		
		if(isset($_GET['since_id']) && is_numeric($_GET['since_id'])) $q .= ' AND post_id>'.intval($_GET['since_id']);
		if(isset($_GET['max_id']) && is_numeric($_GET['max_id'])) $q .= ' AND post_id<'.intval($_GET['max_id']);
			
		$q .= ' ORDER BY post_id DESC ';
		
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
			
			$type = ($uri[0] == 'mentions' || $uri[0] == 'replies')? 'public':'private';	
			
			if($twitter_data->is_feed())
				while($mention = $db2->fetch_object($res)) 
					$answer .= $twitter_data->print_status_simple($mention->post_id, $type);
			else
			{
				$answer .= $twitter_data->data_section('statuses', FALSE, FALSE, TRUE, ' type="array"');
					while($mention = $db2->fetch_object($res))
					{	
						$answer .= $twitter_data->data_section('status');
							$answer .= $twitter_data->print_status($mention->post_id, TRUE, FALSE, $type);
								
								$answer .= $twitter_data->data_section('user', TRUE);	
									
									$usr = $db2->query('SELECT user_id FROM posts WHERE id="'.$mention->post_id.'" LIMIT 1');
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
	}elseif($uri[0] == 'update' || $uri[0] == 'group_update')
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST' || (!is_valid_data_format($format, TRUE)))
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
			$app_id = $oauth_client->get_value_in_consumer_key('app_id');
			
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
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
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage U1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}		
		}elseif($bauth_status)
		{
			$id = $user->id;	
			if(isset($_POST['source'])) $app_id = detect_app($_POST['source']);
			else $app_id = detect_app(); 
			
			if(!is_numeric($app_id)) $app_id = get_app_id($app_id);
		}

		if(!isset($_POST['status']) || empty($_POST['status']))
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid status parameter.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(isset($_POST['status']) && strlen($_POST['status']) > $C->POST_MAX_SYMBOLS)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Status could no be longer than '.$C->POST_MAX_SYMBOLS.' symbols.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}		
		$_POST['status'] = trim(stripslashes(htmlspecialchars(urldecode($_POST['status']))));
		
		if(!isset($_POST['in_reply_to_status_id'])) 
		{
			$res = $db2->query('SELECT message FROM posts WHERE user_id =\''.intval($id).'\' ORDER BY id DESC LIMIT 1');
			if($db2->num_rows($res) > 0) $text = $db2->fetch_object($res);
			
			if((isset($text) && $text->message != $_POST['status']) || !isset($text))
			{
				$newpost	= new newpost();
				$newpost->set_api_id( $app_id );
				$newpost->set_message($_POST['status']);
				if($uri[0] == 'group_update')
				{
					if(isset($uri[1]) && is_numeric($uri[1])) $group_id = $uri[1];
					elseif(isset($uri[1]))
					{
						$g = $network->get_group_by_name(urldecode($uri[1]));
						if(!$g)
						{
							if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
								else echo generate_error($format, 'Invalid group parameter.', $_SERVER['REQUEST_URI'], $callback);
							exit;
						}
						$group_id = $g->id;
					}else
					{
						if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
							else echo generate_error($format, 'Group paramater required.', $_SERVER['REQUEST_URI'], $callback);
						exit;	
					}
					
					if($user->if_follow_group($group_id))
					{
						$newpost->set_group_id($group_id);
					}else
					{
						if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
							else echo generate_error($format, 'You are not a group member.', $_SERVER['REQUEST_URI'], $callback);
						exit;
					}		
				}
				if(isset($_POST['link']) && is_valid_url($_POST['link']))
				{
					if(!$newpost->attach_link(urldecode($_POST['link'])))
					{
						if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
							else echo generate_error($format, 'Invalid link.', $_SERVER['REQUEST_URI'], $callback);
						exit;
					}
				}
				if(isset($_POST['video']) && !empty($_POST['video']))
				{
					if(!$newpost->attach_videoembed(urldecode($_REQUEST['video'])))
					{
						if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
							else echo generate_error($format, 'Invalid video link.', $_SERVER['REQUEST_URI'], $callback);
						exit;
					}
				}
				if(isset($_POST['file']) && !empty($_POST['file']) && isset($_POST['file_type']) && !empty($_POST['file_type']))
				{
					$tmp	= $C->TMP_DIR.'tmp_'.md5(time().rand(0,9999));
					$fl = file_put_contents($tmp, base64_decode($_POST['file']));

					if(!$newpost->attach_file($tmp, $fl.'.'.$_POST['file_type']))
					{
						if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
							else echo generate_error($format, 'Invalid file.', $_SERVER['REQUEST_URI'], $callback);
						exit;
					}
				}
				if(isset($_POST['image']) && !empty($_POST['image']))
				{
					$fl	= $C->TMP_DIR.'tmp_'.md5(time().rand(0,9999));
					file_put_contents($fl, base64_decode($_POST['image']));
					
					if(!$newpost->attach_image($fl))
					{
						if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
							else echo generate_error($format, 'Invalid image file.', $_SERVER['REQUEST_URI'], $callback);
						exit;
					}
				}
					
				$ok	= $newpost->save();
				if( ! $ok ) {
					if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
						else echo generate_error($format, 'Server error (Stage 1).', $_SERVER['REQUEST_URI'], $callback);
					exit;
				}
				else 
				{
					$new_post = explode("_", $ok);
					
					$twitter_data = new TwitterData($format, $callback, $id);
					$answer = $twitter_data->data_header();
		
					$answer .= $twitter_data->data_section('status');
						$answer .= $twitter_data->print_status(intval($new_post[0]), TRUE);	
							
							$answer .= $twitter_data->data_section('user', TRUE);				
								$answer .=  $twitter_data->print_user($id);	
							$answer .= $twitter_data->data_section('user', FALSE, TRUE);	
							
					$answer .= $twitter_data->data_section('status', FALSE, TRUE);
					$answer .= $twitter_data->data_bottom();

					echo $answer;
					exit;
				}
			}else
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad request');
					else echo generate_error($format, 'Provide diffrent status.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif(isset($_POST['in_reply_to_status_id']) && is_numeric($_POST['in_reply_to_status_id']))
		{
			$post	= new post('public', intval($_POST['in_reply_to_status_id']));
			
			if(!$post || !isset($post->post_user->id))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad request');
					else echo generate_error($format, 'Invalid post id.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$author = $this->network->get_user_by_id($post->post_user->id);
			if(!$author)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage 2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$mentioned = false;
			if($post->post_user->id == $id) $mentioned = true;
			elseif(preg_match('/@'.$author->username.'/iu', $_POST['status'])) $mentioned = true;
			elseif(preg_match('/@'.$user->info->username.'/iu', $post->post_message)) $mentioned = true;

			if($mentioned)
			{	
				$q = 'SELECT message FROM posts_comments WHERE post_id='.intval($post->post_id).' AND user_id='.intval($id).' ORDER BY id DESC LIMIT 1';
				$check = $db2->query($q);

				if($db2->num_rows($check) > 0)
				{
					$check = $db2->fetch_object($check);
					
					if($check->message == $_POST['status'])
					{
						if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad request');
							else echo generate_error($format, 'Provide different comment.', $_SERVER['REQUEST_URI'], $callback);
						exit;
					}
				}
				
				$check_post = new post('public', intval($_POST['in_reply_to_status_id']));
				if(!$check_post)
				{
					if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
						else echo generate_error($format, 'Server error (Stage U11).', $_SERVER['REQUEST_URI'], $callback);
					exit;
				}
				
				if($check_post->post_group && !$user->if_follow_group($check_post->post_group->id))
				{
					if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
						else echo generate_error($format, 'You are not a group member.', $_SERVER['REQUEST_URI'], $callback);
					exit;
				}
					
				$np = new newpostcomment($post);
				$np->set_api_id( $app_id );
				$np->set_message($_POST['status']);
				$result = $np->save();

				if( $result ) 
				{ 
					$twitter_data = new TwitterData($format, $callback, $id);
					$answer = $twitter_data->data_header();
		
					$answer .= $twitter_data->data_section('status');
						$answer .= $twitter_data->print_status(intval($_POST['in_reply_to_status_id']), TRUE);	
							
							$answer .= $twitter_data->data_section('user', TRUE);				
								$answer .=  $twitter_data->print_user($id);	
							$answer .= $twitter_data->data_section('user', FALSE, TRUE);	
							
					$answer .= $twitter_data->data_section('status', FALSE, TRUE);
					$answer .= $twitter_data->data_bottom();
					
					echo $answer;
					exit;
				}else 
				{
					if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
						else echo generate_error($format, 'Server error (Stage 4).', $_SERVER['REQUEST_URI'], $callback);
					exit;
				}
			}else
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Not mentioned in author\'s post.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}		
	}elseif($uri[0] == 'destroy' || $uri[0] == 'private_destroy')
	{
		if(($_SERVER['REQUEST_METHOD'] != 'POST' && $_SERVER['REQUEST_METHOD'] != 'DELETE') || (!is_valid_data_format($format, TRUE)))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if(!isset($uri[1]) || !is_numeric($uri[1]))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Incorrect status id.', $_SERVER['REQUEST_URI'], $callback);
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
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 401 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server error D1.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if(!$user->is_logged)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1X).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;

		$post_type = ($uri[0] == 'destroy')? 'public':'private';
		$post	= new post($post_type, intval($uri[1]));
		
		if(!$post || !isset($post->post_user->id))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Bad Request');
				else echo generate_error($format, 'No such post.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if( $post->post_user->id == $id)
		{	
			$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();

			$answer .= $twitter_data->data_section('status');
				$answer .= $twitter_data->print_status(intval($post->post_id), TRUE);	
					
					$answer .= $twitter_data->data_section('user', TRUE);				
						$answer .=  $twitter_data->print_user($post->post_user->id);	
					$answer .= $twitter_data->data_section('user', FALSE, TRUE);	
					
			$answer .= $twitter_data->data_section('status', FALSE, TRUE);
			$answer .= $twitter_data->data_bottom();

			if($post->delete_this_post())
			{
				echo $answer;
				exit;
			}else
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (Stage 5).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'You are not the author of the post.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}elseif($uri[0] == 'friends' || $uri[0] == 'followers')
	{
		if(($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST') || (!is_valid_data_format($format, TRUE)))
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if(isset($_REQUEST['user_id']) && is_numeric($_REQUEST['user_id'])) $id = intval($_REQUEST['user_id']);
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
			$id = $u->id;
		}elseif(isset($uri[1]) && is_numeric($uri[1])) $id = intval($uri[1]);
		elseif($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			if(!$id)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server Error PT1.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}

			$user->logout();
			$user->login($u->username, $u->password); 
			if(!$user->is_logged)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Server Error PT1.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}		
		}elseif($bauth_status) $id = $user->id;	
		else
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'User paramater required.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$info	= $this->network->get_user_follows($id);
		if(!$info) 
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error (Stage 6).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		else 
		{
			$followers	= array_keys($info->followers);
			$following	= array_keys($info->follow_users);
		}
		if($uri[0] == 'friends') $users = &$following;
		else 	$users = &$followers;

		if( !$user->is_logged || !$user->info->is_network_admin ) 
		{
			$not_in_groups	= array();
			$not_in_groups 	= not_in_groups($id);
			$not_in_groups	= count($not_in_groups)>0 ? ('AND group_id NOT IN('.implode(', ', $not_in_groups).')') : '';
		}
		$num_rows = count($users);
		
		$twitter_data = new TwitterData($format, $callback, $id, TRUE);
		$answer = $twitter_data->data_header();
		$answer .= $twitter_data->data_section('users_list', FALSE, FALSE, TRUE, ' type="array"');
			$answer .= $twitter_data->data_section('users', FALSE, FALSE);
			foreach($users as $id)
			{	
				$answer .= $twitter_data->data_section('user', TRUE);
					$answer .=  $twitter_data->print_user($id);		
						$answer .= ($format == 'json')? ',':''; 
						
						$answer .= $twitter_data->data_section('status', TRUE);
							$q = 'SELECT id AS pid FROM posts WHERE api_id<>2 AND api_id<>6 AND user_id=\''.$id.'\' '.$not_in_groups.' ORDER BY id DESC LIMIT 1';					
							$answer .= $twitter_data->print_status(0, FALSE, $q);
						$answer .= $twitter_data->data_section('status', FALSE, TRUE);	
						
				$answer .= $twitter_data->data_section('user', FALSE, TRUE);
				
				$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
				$num_rows--;
			}
			$answer .= $twitter_data->data_section('users', FALSE, TRUE);
		$answer .= $twitter_data->data_section('users_list', FALSE,  TRUE, TRUE);
		$answer .= $twitter_data->data_bottom();
		
		echo $answer;
		exit;
			
	}elseif($uri[0] == 'friends_timeline' || $uri[0] == 'home_timeline')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET' && $_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));

			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
			
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (Stage f1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage f2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id=$user->id;
			
		$info	= $this->network->get_user_follows($id);
		if(!$info) 
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error (Stage 7).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		else $following	= array_keys($info->follow_users);

		if(count($following) > 0)
		{	
			if( !$user->is_logged || !$user->info->is_network_admin ) 
			{
				$not_in_groups	= array();
				$not_in_groups 	= not_in_groups($id);
				$not_in_groups	= count($not_in_groups)>0 ? ('AND group_id NOT IN('.implode(', ', $not_in_groups).')') : '';
			}

			$q = 'SELECT id AS pid, user_id AS uid FROM posts WHERE api_id<>2 AND api_id<>6 '.$not_in_groups.' AND (user_id="'.intval($id).'" OR user_id IN ('.implode(',', $following).')) ';
			
			if(isset($_REQUEST['since_id']) && is_numeric($_REQUEST['since_id'])) $q .= ' AND id>'.intval($_REQUEST['since_id']);
			if(isset($_REQUEST['max_id']) && is_numeric($_REQUEST['max_id'])) $q .= ' AND id<'.intval($_REQUEST['max_id']);
			
			$q .= ' ORDER BY id DESC ';
			
			if(isset($_REQUEST['count']) && !isset($_REQUEST['page']))
				{if(is_numeric($_REQUEST['count']) && $_GET['count']<200) $q .= ' LIMIT '.intval($_REQUEST['count']);}
			elseif(isset($_REQUEST['page']) && !isset($_REQUEST['count']))
				{if(is_numeric($_REQUEST['page'])) $q .= ' LIMIT '.(20)*(intval($_REQUEST['page'])-1).', '.(20)*(intval($_REQUEST['page']));}
			elseif(isset($_REQUEST['page']) && isset($_REQUEST['count']))
				{if(is_numeric($_REQUEST['page']) && is_numeric($_REQUEST['count'])) 
					$q .= ' LIMIT '.(intval($_REQUEST['count']))*(intval($_REQUEST['page'])-1).', '.(intval($_REQUEST['count']))*(intval($_REQUEST['page']));}
			else $q .= ' LIMIT 20';

			$res = $db2->query($q);
			$num_rows = $db2->num_rows($res);
			
			if($num_rows > 0)
			{
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
										$answer .=  $twitter_data->print_user($stat->uid);	
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
				if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
					else echo generate_error($format, 'No posts found.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}else 
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 403 Not Modified');
				else echo generate_error($format, 'No friends found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	
	}elseif($uri[0] == 'home_timeline_unfinished')
	{
		//to do: user_timeline + retweets
	}elseif($uri[0] == 'show')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET' || (!is_valid_data_format($format, TRUE)))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or requested data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if(isset($uri[1]) && is_numeric($uri[1])) $post_id = $uri[1];
		else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Incorrect post paramater.', $_SERVER['REQUEST_URI'], $callback);
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
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (Stage S1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
				
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage S2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}	
		}
		
		/*if((!$oauth_status && !check_rate_limits($_SERVER['REMOTE_ADDR'], 1)) || ($oauth_status && !$oauth_client->check_rate_limits($id, 1)))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}*/
		
		if( !$user->is_logged || !$user->info->is_network_admin ) 
		{
			$not_in_groups	= array();
			$not_in_groups 	= not_in_groups($id);
			$not_in_groups	= count($not_in_groups)>0 ? ('AND group_id NOT IN('.implode(', ', $not_in_groups).')') : '';
		}
		
		$q	= 'SELECT id AS pid, user_id AS uid FROM posts WHERE id="'.intval($post_id).'" AND api_id<>2 AND api_id<>6 AND user_id<>0 '.$not_in_groups.' LIMIT 1';
		
		$res = $db2->query($q);	
		
		if($db2->num_rows($res) > 0)
		{	
			$res = $db2->fetch_object($res);
			$id = isset($id)? $id : -1;
			
			$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();

			$answer .= $twitter_data->data_section('status');
				$answer .= $twitter_data->print_status(intval($res->pid), TRUE);	
					
					$answer .= $twitter_data->data_section('user', TRUE);				
						$answer .=  $twitter_data->print_user($res->uid);	
					$answer .= $twitter_data->data_section('user', FALSE, TRUE);	
					
			$answer .= $twitter_data->data_section('status', FALSE, TRUE);
			$answer .= $twitter_data->data_bottom();
			
			echo $answer;
			exit;
		}else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 403 Forbidden');
				else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}	
	}elseif($uri[0] == 'commented')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET' || (!is_valid_data_format($format, TRUE)))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or requested data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem.', $_SERVER['REQUEST_URI'], $callback);
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
		}elseif($bauth_status) $id = $user->id;
		
		/*if(!$oauth_client->check_rate_limits($id, 1))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}*/
		
		if(!isset($_GET['type']) || ($_GET['type']!='private' && $_GET['type']!='public'))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Post type paramater required.', $_SERVER['REQUEST_URI'], $callback);
			exit;	
		}	
		
		if($_GET['type']=='public')
		{
			$q = 'SELECT posts_comments.post_id, posts.id, posts.user_id FROM posts_comments, posts WHERE posts_comments.post_id = posts.id AND posts.user_id='.intval($id).' GROUP BY post_id ORDER BY posts.id DESC LIMIT 20';
		}else
		{
			$q = 'SELECT posts_pr_comments.post_id, posts.id, posts.user_id, posts.date AS pdate FROM posts_pr_comments, posts WHERE posts_pr_comments.post_id = posts.id AND posts.user_id='.intval($id).' GROUP BY post_id ORDER BY posts.id DESC LIMIT 20';
		}
		
		$res = $db2->query($q);
		$num_rows = $db2->num_rows($res);
		
		if($num_rows > 0)
		{
			$twitter_data = new TwitterData($format, $callback, $id, TRUE);
			$answer = $twitter_data->data_header();
			
			$answer .= $twitter_data->data_section('statuses', FALSE, FALSE, TRUE, ' type="array"');
				while($stat = $db2->fetch_object($res))
				{	
					$answer .= $twitter_data->data_section('post', FALSE);		
						$answer .= $twitter_data->data_field('id', $stat->post_id, FALSE);				
					$answer .= $twitter_data->data_section('post', FALSE, TRUE);
					
					$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
					$num_rows--;
				}
			$answer .= $twitter_data->data_section('statuses', FALSE,  TRUE, TRUE);
			$answer .= $twitter_data->data_bottom();

			echo $answer;
			exit;	
		}else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 403 Not Modified');
				else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}elseif($uri[0] == 'comments' || $uri[0] == 'private_comments')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET' || (!is_valid_data_format($format, TRUE)))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error('xml', 'Invalid request method or requested data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem.', $_SERVER['REQUEST_URI'], $callback);
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
		}elseif($bauth_status) $id = $user->id;
		
		if(!isset($_GET['post_id']) || !is_numeric($_GET['post_id']))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Post parameter required.', $_SERVER['REQUEST_URI'], $callback);
			exit;	
		}	
		
		if($uri[0] == 'comments')
		{
			$q = 'SELECT posts_comments.id AS cid, posts_comments.message AS mtext FROM posts_comments, posts WHERE posts.user_id='.intval($id).' AND posts_comments.post_id='.intval($_GET['post_id']).' GROUP BY posts_comments.id ORDER BY posts_comments.id DESC LIMIT 20';
		}else
		{
			$q = 'SELECT posts_pr_comments.id AS cid, posts_pr_comments.message AS mtext FROM posts_pr_comments, posts_pr WHERE posts_pr.user_id='.intval($id).' AND posts_pr.id='.intval($_GET['post_id']).' GROUP BY posts_pr_comments.id ORDER BY posts_pr_comments.id DESC LIMIT 20';
		}

		$res = $db2->query($q); 
		$num_rows = $db2->num_rows($res);
		
		if($num_rows > 0)
		{
			$twitter_data = new TwitterData($format, $callback, $id, TRUE);
			$answer = $twitter_data->data_header();
			
			$answer .= $twitter_data->data_section('comments', FALSE, FALSE, TRUE, ' type="array"');
				while($stat = $db2->fetch_object($res))
				{	
					$answer .= $twitter_data->data_section('post');		
						$answer .= $twitter_data->data_field('id', $stat->cid);	
						$answer .= $twitter_data->data_field('text', $stat->mtext, FALSE);	
					$answer .= $twitter_data->data_section('post', FALSE, TRUE);
					
					$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
					$num_rows--;
				}
			$answer .= $twitter_data->data_section('comments', FALSE,  TRUE, TRUE);
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