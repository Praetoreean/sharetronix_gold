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
				
			$oauth_client->set_variable('stage_url', $C->SITE_URL.'1/groups/'.$uri[0].'.'.$format);
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
	$features = array('follow', 'unfollow', 'membership', 'all_groups', 'new', 'destroy');

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
	}elseif(($uri[0] == 'follow' || $uri[0] == 'unfollow') && isset($uri[1]))
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
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
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid user.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
	
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage 1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;

		if(is_numeric($uri[1])) $group_id = intval($uri[1]);
		else
		{
			$res = $db2->query('SELECT id FROM groups WHERE groupname=\''.$db2->e(urldecode($uri[1])).'\' LIMIT 1');
			if(!$db2->num_rows($res))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid group name.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$res = $db2->fetch_object($res);
			$group_id = $res->id;
		}
		if($uri[0] == 'follow') $ok = $user->follow_group($group_id);
		elseif($uri[0] == 'unfollow') $ok = $user->follow_group($group_id, FALSE);

		if($ok)
		{
			$twitter_data = new TwitterData($format, $callback, $id);
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
		}else
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'You can not follow/unfollow this group.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}		
	}elseif(($uri[0] == 'membership'))
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if(isset($uri[1]) && is_numeric($uri[1])) $id = intval($uri[1]);
		elseif(isset($uri[1]))
		{
			$q = 'SELECT id FROM groups WHERE groupname=\''.$db2->e($uri[1]).'\' AND is_public LIMIT 1';
			
			$res = $db2->query($q);
			if($db2->query($res) > 0)
			{
				$res = $db2->fetch_object($res);
				$id = $res->id;
			}else
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
					else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}
		else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Parameter required.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$q = 'SELECT groups_followed.user_id AS user FROM groups_followed, groups, users WHERE groups_followed.user_id=users.id AND groups_followed.group_id=groups.id AND groups_followed.group_id='.intval($id);
		
		$res = $db2->query($q);
		$num_rows = $db2->num_rows($res);
		
		if($num_rows > 0)
		{
			$twitter_data = new TwitterData($format, $callback, $id, TRUE);
			$answer = $twitter_data->data_header();
			
			$answer .= $twitter_data->data_section('users', FALSE, FALSE, TRUE, ' type="array"');
			while($obj = $db2->fetch_object($res))
			{				
				$answer .= $twitter_data->data_section('user');
				$answer .=  $twitter_data->print_user($obj->user);		
				$answer .= ($format == 'json')? ',':''; 
				
				$answer .= $twitter_data->data_section('status', TRUE);
					$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.$obj->user.'\' ORDER BY pid DESC LIMIT 1';		
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
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
				else echo generate_error($format, 'No results found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	
	}elseif(($uri[0] == 'all_groups'))
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		$res = $db2->query('SELECT id, groupname FROM groups WHERE groups.is_public');
		$num_rows = $db2->num_rows($res);
		
		$twitter_data = new TwitterData($format, $callback, -1, TRUE);
			$answer = $twitter_data->data_header();
			$answer .= $twitter_data->data_section('groups', FALSE, FALSE, TRUE, ' type="array"');
			
			while($obj = $db2->fetch_object($res))
			{	
				$answer .= $twitter_data->data_section('group');
					$answer .= $twitter_data->data_field('id', $obj->id);
					$answer .= $twitter_data->data_field('name', $obj->groupname, FALSE);
				$answer .= $twitter_data->data_section('group', FALSE, TRUE);
				
				$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
				$num_rows--;		
			}
			$answer .= $twitter_data->data_section('groups', FALSE,  TRUE, TRUE);		
		$answer .= $twitter_data->data_bottom();
		
		echo $answer;
		exit;
					
	}elseif($uri[0] == 'create')
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
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
			if(!$id)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 403 Forbidden');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}
		
		$error	= FALSE;
		$errmsg	= '';
		
		if(isset($_POST['title'], $_POST['groupname'], $_POST['description'], $_POST['type'])&& ($_POST['type'] == 'private' || $_POST['type'] == 'public'))
		{
			$form_title		= trim(htmlspecialchars(urldecode($_POST['title'])));
			$form_groupname	= trim(htmlspecialchars(urldecode($_POST['groupname'])));
			$form_description	= mb_substr(trim(htmlspecialchars(urldecode($_POST['description']))), 0, $C->POST_MAX_SYMBOLS);
			$form_type		= trim(htmlspecialchars(urldecode($_POST['type'])))=='private' ? 'private' : 'public';
		}else
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid group parameter.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if( mb_strlen($form_title)<3 || mb_strlen($form_title)>30 ) 
		{
			$error	= TRUE;
			$errmsg	= 'Not valid group name.';
		}
		elseif( preg_match('/[^א-תÀ-ÿ一-龥а-яa-z0-9\-\.\s]/iu', $form_title) ) 
		{
			$error	= TRUE;
			$errmsg	= 'Invalid group\'s name characters';
		}
		else 
		{
			$db2->query('SELECT id FROM groups WHERE (groupname="'.$db2->e($form_title).'" OR title="'.$db2->e($form_title).'") LIMIT 1');
			if( $db2->num_rows() > 0 ) 
			{
				$error	= TRUE;
				$errmsg	= 'Provide different group name.';
			}
		}
		
		if( !$error ) 
		{
			if( ! preg_match('/^[a-z0-9\-\_]{3,30}$/iu', $form_groupname) ) {
				$error	= TRUE;
				$errmsg	= 'Invalid group name.';
			}
			else 
			{
				$db2->query('SELECT id FROM groups WHERE (groupname="'.$db2->e($form_groupname).'" OR title="'.$db2->e($form_groupname).'") LIMIT 1');
				if( $db2->num_rows() > 0 ) 
				{
					$error	= TRUE;
					$errmsg	= 'Provide different group name.';
				}
				else 
				{
					$db2->query('SELECT id FROM users WHERE username="'.$db2->e($form_groupname).'" LIMIT 1');
					if( $db2->num_rows() > 0 ) 
					{
						$error	= TRUE;
						$errmsg	= 'Provide different group name.';
					}
					elseif( file_exists($C->INCPATH.'controllers/'.strtolower($form_groupname).'.php') ) 
					{
						$error	= TRUE;
						$errmsg	= 'Provide different group name.';
					}
					elseif( file_exists($C->INCPATH.'controllers/mobile/'.strtolower($form_groupname).'.php') ) 
					{
						$error	= TRUE;
						$errmsg	= 'Provide different group name.';
					}
				}
			}
		}else
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, $errmsg, $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if( ! $error ) 
		{
			$q = 'INSERT INTO groups SET groupname="'.$db2->e($form_groupname).'", title="'.$db2->e($form_title).'", about_me="'.$db2->e($form_description).'", is_public="'.($form_type=='public'?1:0).'"';
			$db2->query($q);
			
			$g = $this->network->get_group_by_id(intval($db2->insert_id()));
			
			$db2->query('INSERT INTO groups_admins SET group_id="'.$g->id.'", user_id="'.$id.'" ');
			if( $g->is_private ) 
			{
				$q = 'INSERT INTO groups_private_members SET group_id="'.$g->id.'", user_id="'.$id.'", invited_by="'.$id.'", invited_date="'.time().'"';
				$db2->query($q);
			}
			
			$u	= $this->network->get_user_by_id($id);
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
					else echo generate_error($format, 'Server error.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$ok = $user->follow_group($g->id);	
			if( $g->is_public ) 
			{
				$n	= intval( $this->network->get_user_notif_rules($id)->ntf_them_if_i_create_grp );
				if( $n == 1 ) 
				{
					$this->load_langfile('inside/notifications.php');
					$this->load_langfile('email/notifications.php');
					$followers	= array_keys($this->network->get_user_follows($id)->followers);
					foreach($followers as $uid) 
					{
						$send_post	= FALSE;
						$send_mail	= FALSE;
						$n	= intval( $this->network->get_user_notif_rules($uid)->ntf_me_if_u_creates_grp );
						if( $n == 2 ) { $send_post = TRUE; } elseif( $n == 3 ) { $send_mail = TRUE; } elseif( $n == 1 ) 
						{ $send_post = TRUE; $send_mail = TRUE; }
						
						if( $send_post ) 
						{
							$lng	= array('#USER#'=>'<a href="'.$C->SITE_URL.$u->username.'" title="'.htmlspecialchars($u->fullname).'"><span 						
							class="mpost_mentioned">@</span>'.$u->username.'</a>', 
							'#GROUP#'=>'<a href="'.$C->SITE_URL.$g->groupname.'" title="'.$g->title.'">'.$g->title.'</a>');
							$this->network->send_notification_post($uid, 0, 'msg_ntf_me_if_u_creates_grp', $lng, 'replace');
						}
						if( $send_mail ) 
						{
							$lng_txt	= array('#SITE_TITLE#'=>$C->SITE_TITLE, 
							'#USER#'=>'@'.$this->user->info->username, 
							'#NAME#'=>$u->fullname, '#GROUP#'=>$g->title, '#A0#'=>$C->SITE_URL.$g->groupname);
							
							$lng_htm	= array('#SITE_TITLE#'=>$C->SITE_TITLE, 
							'#USER#'=>'<a href="'.$C->SITE_URL.$u->username.'" 
							title="'.htmlspecialchars($u->fullname).'" target="_blank">@'.$u->username.'</a>', 
							'#NAME#'=>$u->fullname, 
							'#GROUP#'=>'<a href="'.$C->SITE_URL.$g->groupname.'" title="'.$g->title.'" target="_blank">'.$g->title.'</a>');
							
							$subject		= $this->lang('emlsubj_ntf_me_if_u_creates_grp', $lng_txt);
							$message_txt	= $this->lang('emltxt_ntf_me_if_u_creates_grp', $lng_txt);
							$message_htm	= $this->lang('emlhtml_ntf_me_if_u_creates_grp', $lng_htm);
							$this->network->send_notification_email($uid, 'u_creates_grp', $subject, $message_txt, $message_htm);
						}
					}
				}
			}
			$q = 'SELECT groupname AS gn, group_id AS gi FROM groups, groups_admins WHERE user_id ='.intval($id).' ORDER BY groups_admins.id DESC LIMIT 1';
			$res = $db2->query($q);
			$obj = $db2->fetch_object($res);
			
			$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();
			
				$answer .= $twitter_data->data_section('group');	
					$answer .= $twitter_data->data_field('id', $obj->gi);	
					$answer .= $twitter_data->data_field('name', $obj->gn);					
					
					$answer .= $twitter_data->data_section('user', TRUE);						
						$answer .=  $twitter_data->print_user($id);		
					$answer .= $twitter_data->data_section('user', FALSE, TRUE);					
				$answer .= $twitter_data->data_section('group', FALSE, TRUE);	
					
			$answer .= $twitter_data->data_bottom();
			
			echo $answer;
			exit;
		}else
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, $errmsg, $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
	}elseif(($uri[0] == 'destroy'))
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization problem.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		if($oauth_status)
		{
		
			$oauth_token = (isset($header['oauth_token']))? $header['oauth_token']:$_POST['oauth_token'];
				
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($oauth_token)));
			if(!$id)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage C1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			/*if(!$oauth_client->check_rate_limits($id, 1))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 403 Forbidden');
					else echo generate_error($format, 'You have no available rate limits, try again later.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}*/
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;
		
		if(!isset($uri[1]) || !is_numeric($uri[1]))
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Provide valid group id.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$q = 'SELECT 1 FROM groups_admins WHERE group_id='.intval($uri[1]).' AND user_id ='.intval($id).' LIMIT 1';
		$res = $db2->query($q);
		if($db2->num_rows($res) != 1)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'You are not admin or only admin. You can not delete this group.', $_SERVER['REQUEST_URI'], $callback);
			
			exit;
		}

		$u	= $this->network->get_user_by_id($id);
		if(!$u)
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error (Stage 1).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		$user->logout();
		$user->login($u->username, $u->password); 
		if( ! $user->is_logged ) 
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server Error (Stage D1).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		ini_set('max_execution_time', 10*60*60);

		$r	= $db2->query('SELECT * FROM posts WHERE group_id="'.intval($uri[1]).'" ORDER BY id ASC');
		while($obj = $db2->fetch_object($r)) 
		{
			$p	= new post('public', FALSE, $obj);
			if( $p->error ) { continue; }
			$p->delete_this_post();
		}
		$r	= $db2->query('SELECT id FROM groups_rssfeeds WHERE group_id="'.intval($uri[1]).'" ');
		while($obj = $db2->fetch_object($r)) 
		{
			$db2->query('DELETE FROM groups_rssfeeds_posts WHERE rssfeed_id="'.$obj->id.'" ');
		}
		$db2->query('DELETE FROM groups_rssfeeds WHERE group_id="'.intval($uri[1]).'" ');

		$r	= $db2->query('SELECT * FROM posts WHERE user_id="0" AND group_id="'.intval($uri[1]).'" ORDER BY id ASC');
		while($obj = $db2->fetch_object($r)) 
		{
			$p	= new post('public', FALSE, $obj);
			if( $p->error ) { continue; }
			$p->delete_this_post();
		}
		$f	= array_keys($this->network->get_group_members(intval($uri[1])));
		$db2->query('DELETE FROM groups_followed WHERE group_id="'.intval($uri[1]).'" ');
		$db2->query('DELETE FROM groups_private_members WHERE group_id="'.intval($uri[1]).'" ');
		$db2->query('DELETE FROM groups_admins WHERE group_id="'.intval($uri[1]).'" ');
		$db2->query('UPDATE groups_rssfeeds SET is_deleted=1 WHERE group_id="'.intval($uri[1]).'" ');
		foreach($f as $uid) 
		{
			$this->network->get_user_follows($uid, TRUE);
		}
		$db2->query('INSERT INTO groups_deleted (id, groupname, title, is_public) SELECT id, groupname, title, is_public FROM groups WHERE id="'.intval($uri[1]).'" LIMIT 1');
		$db2->query('DELETE FROM groups WHERE id="'.intval($uri[1]).'" LIMIT 1');

		$res = $db2->query('SELECT id, groupname, title FROM groups_deleted WHERE id='.intval($uri[1]).' LIMIT 1');
		$gr = $db2->fetch_object($res);	
		
		$twitter_data = new TwitterData($format, $callback, $id);
		$answer = $twitter_data->data_header();

		$answer .= $twitter_data->data_section('group');
			
			$answer .=  $twitter_data->data_field('id', $gr->pid);		
			$answer .=  $twitter_data->data_field('name', $gr->groupname);
			$answer .=  $twitter_data->data_field('title', $gr->title, FALSE);	
				
		$answer .= $twitter_data->data_section('group', FALSE, TRUE);
		$answer .= $twitter_data->data_bottom();
		
		echo $answer;
		exit;	
	}	
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request.', $_SERVER['REQUEST_URI'], $callback);
	exit;
?>