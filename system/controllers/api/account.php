<?php
	require_once( $C->INCPATH.'helpers/func_api.php' );
	require_once( $C->INCPATH.'classes/class_oauth.php' );
	require_once( $C->INCPATH.'classes/class_rssfeed.php' );
	require_once( $C->INCPATH.'helpers/func_images.php' );
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
				
			$oauth_client->set_variable('stage_url', $C->SITE_URL.'1/account/'.$uri[0].'.'.$format);
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
	
	$features = array('create', 'destroy', 'exists', 'show', 'verify_credentials', 'incoming', 'outgoing', 'rate_limit_status', 'delete_feed', 'add_feed', 'update_profile_image', 'end_session', 'update_profile');
				
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
	}elseif($uri[0] == 'update_profile')
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
					else echo generate_error($format, 'Server error (Stage UP1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 403 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;
		
		if(isset($_POST['name']) && strlen($_POST['name']) > 20 )
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Too long name paramater.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(isset($_POST['url']) && !is_valid_url($_POST['url']) )
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid url paramater.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(isset($_POST['description']) && strlen($_POST['description']) > 160 )
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid description paramater.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(isset($_POST['location']) && strlen($_POST['location']) > 30 )
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid location paramater.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(isset($_POST['birthdate']) && !is_valid_date($_POST['birthdate']) )
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid birthdate paramater.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(isset($_POST['gender']) && ($_POST['gender'] != 'm' && $_POST['gender'] != 'f'))
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid gender paramater.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif(isset($_POST['tags']) && (strlen($_POST['tags']) == 0 || strlen($_POST['tags']) > 255))
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid tags paramater.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		if(isset($_POST['name']) || isset($_POST['description']) || isset($_POST['location']) || isset($_POST['birthdate']) || isset($_POST['gender']) || isset($_POST['tags']) )
		{
			$q = 'UPDATE users SET ';
			if(isset($_POST['name'])) $q .= ' fullname=\''.$db2->e(htmlspecialchars(urldecode($_POST['name']))).'\',';
			if(isset($_POST['description'])) $q .= ' about_me=\''.$db2->e(htmlspecialchars(urldecode($_POST['description']))).'\',';
			if(isset($_POST['location'])) $q .= ' location=\''.$db2->e(htmlspecialchars(urldecode($_POST['location']))).'\',';
			if(isset($_POST['birthdate'])) $q .= ' birthdate=\''.$db2->e(htmlspecialchars(urldecode($_POST['birthdate']))).'\',';
			if(isset($_POST['gender'])) $q .= ' gender=\''.$db2->e(htmlspecialchars(urldecode($_POST['gender']))).'\',';
			if(isset($_POST['tags'])) $q .= ' tags=\''.$db2->e(htmlspecialchars(urldecode($_POST['tags']))).'\',';
			$q = substr($q, 0, -1);
			$q .= ' WHERE id=\''.intval($id).'\' LIMIT 1';
			
			$res = $db2->query($q);
			
			if(!$db2->affected_rows($res))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
					else echo generate_error($format, 'Your account was not modified.', $_SERVER['REQUEST_URI'], $callback);
				exit;	
			}
		}
		if(isset($_POST['url']))
		{
			$check = $db2->query('SELECT 1 FROM users_details WHERE user_id=\''.intval($id).'\' LIMIT 1');
			if($db2->num_rows($check) > 0)
			{
				$q = 'UPDATE users_details SET website=\''.$db2->e(urldecode($_POST['url'])).'\' WHERE user_id=\''.intval($id).'\' LIMIT 1';
				
				$res = $db2->query($q);
				
				if(!$db2->affected_rows($res))
				{
					if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
						else echo generate_error($format, 'Your account was not modified.', $_SERVER['REQUEST_URI'], $callback);
					exit;
				}
			}else
			{		
				$res = $db2->query('INSERT INTO users_details(user_id, website) VALUES('.intval($id).', \''.$db2->e(urldecode($_POST['url'])).'\')');		
				if(!$db2->affected_rows($res))
				{
					if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 304 Not Modified');
						else echo generate_error($format, 'Your account was not modified.', $_SERVER['REQUEST_URI'], $callback);
					exit;
				}
			}
		}

		$twitter_data = new TwitterData($format, $callback, $id);
		$answer = $twitter_data->data_header();

		$answer .= $twitter_data->data_section('user');	
			$answer .=  $twitter_data->print_user($id);	
				$answer .= ($format == 'json')? ',' : '';	
				$answer .= $twitter_data->data_section('status', TRUE);
					$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($id).'\' AND api_id<>2 AND api_id<>6 ORDER BY id DESC LIMIT 1';				
					$answer .=  $twitter_data->print_status(0, FALSE, $q);	
				$answer .= $twitter_data->data_section('status', FALSE, TRUE);			
		$answer .= $twitter_data->data_section('user', FALSE, TRUE);
		$answer .= $twitter_data->data_bottom();	
		
		echo $answer;
		exit;
	}
	elseif($uri[0] == 'verify_credentials')
	{					
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
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
					else echo generate_error($format, 'Server error (Stage UP1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			if(!$oauth_client->check_access_type('rw'))
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 403 Forbidden');
					else echo generate_error($format, 'You have no permission for this action.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$u = $this->network->get_user_by_id($id);
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (Stage f1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage f2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;
		
		$twitter_data = new TwitterData($format, $callback, $id);
		$answer = $twitter_data->data_header();

		$answer .= $twitter_data->data_section('user');	
			$answer .=  $twitter_data->print_user($id);	
				$answer .= ($format == 'json')? ',' : '';	
				$answer .= $twitter_data->data_section('status', TRUE);
					$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($id).'\' AND api_id<>2 AND api_id<>6 ORDER BY id DESC LIMIT 1';				
					$answer .=  $twitter_data->print_status(0, FALSE, $q);	
				$answer .= $twitter_data->data_section('status', FALSE, TRUE);			
		$answer .= $twitter_data->data_section('user', FALSE, TRUE);
		$answer .= $twitter_data->data_bottom();	

		echo $answer;
		exit;
		
	}elseif($uri[0] == 'end_session')
	{	
		if($bauth_status) $user->logout();
		switch($format)
		{
			case 'json': echo '"logout": true';
				break;
			case 'rss':
			case 'atom':
			case 'xml': echo '<logout>true</logout>';
				break;
		}
		
		exit;	
	}elseif($uri[0] == 'rate_limit_status')
	{
		if($_SERVER['REQUEST_METHOD'] != 'GET')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		header('X-RateLimit-Remaining: 150');
		header('X-RateLimit-Limit: 150');
		header('X-RateLimit-Reset: 1239227843');
		
		$twitter_data = new TwitterData($format, $callback, -1);
		$answer = $twitter_data->data_header();
	
		$answer .= $twitter_data->data_section('hash');
			$answer .= $twitter_data->data_field('hourly-limit', 150);
			$answer .= $twitter_data->data_field('reset_time_in_seconds', 1281097951);
			$answer .= $twitter_data->data_field('remaining_hits', 150);
			$answer .= $twitter_data->data_field('reset_time', 'Fri Aug 06 12:32:31 +0000 2050', FALSE);		
		$answer .= $twitter_data->data_section('hash', FALSE, TRUE);
		$answer .= $twitter_data->data_bottom();
			
		echo $answer;
		exit;

	}elseif($uri[0] == 'update_profile_image')
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}

		if(!$oauth_status && !$bauth_status)
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 401 Unauthorized');
				else echo generate_error($format, 'OAuth otorization error: '.$oauth_error, $_SERVER['REQUEST_URI'], $callback);
			exit;
		}elseif($oauth_status)
		{
			$id = intval($oauth_client->get_field_in_table('oauth_access_token', 'user_id', 'access_token', urldecode($auth['oauth_token'])));
			if(!$id)
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage UP1).', $_SERVER['REQUEST_URI'], $callback);
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
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (Stage f1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage f2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;
		
		$fl	= $C->TMP_DIR.'tmp_'.md5(time().rand(0,9999));
		file_put_contents($fl, base64_decode($_POST['image']));
		
		list($w, $h, $tp) = @getimagesize($fl);
		if( $w==0 || $h==0 ) {
			$error	= TRUE;
			$errmsg	= 'Invalid image file.';
		}
		elseif( $tp!=IMAGETYPE_GIF && $tp!=IMAGETYPE_JPEG && $tp!=IMAGETYPE_PNG ) {
			$error	= TRUE;
			$errmsg	= 'Invalid image type.';
		}
		elseif( $w<200 || $h<200 ) {
			$error	= TRUE;
			$errmsg	= 'Too small image resolution.';
		}
		else {
			$fn	= time().rand(100000,999999).'.png';
			$res	= copy_avatar($fl, $fn);
			if( ! $res) {
				$error	= TRUE;
				$errmsg	= 'Inappropriate image file.';
			}
		}

		if(!$error) 
		{	
			$old	= $u->avatar;;
			if( $old != $C->DEF_AVATAR_USER ) {
				rm( $C->IMG_DIR.'avatars/'.$old );
				rm( $C->IMG_DIR.'avatars/thumbs1/'.$old );
				rm( $C->IMG_DIR.'avatars/thumbs2/'.$old );
				rm( $C->IMG_DIR.'avatars/thumbs3/'.$old );
			}
			$db2->query('UPDATE users SET avatar="'.$db2->escape($fn).'" WHERE id="'.intval($id).'" LIMIT 1');
			$user->info->avatar	= $fn;
			$this->network->get_user_by_id($u->id, TRUE);
		
			$twitter_data = new TwitterData($format, $callback, $id);
			$answer = $twitter_data->data_header();
	
			$answer .= $twitter_data->data_section('user');	
				$answer .=  $twitter_data->print_user($id);	
					$answer .= ($format == 'json')? ',' : '';	
					$answer .= $twitter_data->data_section('status', TRUE);
						$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($id).'\' AND api_id<>2 AND api_id<>6 ORDER BY id DESC LIMIT 1';				
						$answer .=  $twitter_data->print_status(0, FALSE, $q);	
					$answer .= $twitter_data->data_section('status', FALSE, TRUE);			
			$answer .= $twitter_data->data_section('user', FALSE, TRUE);
			$answer .= $twitter_data->data_bottom();
	
			echo $answer;
			exit;
		}else
		{
			if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, $errmsg, $_SERVER['REQUEST_URI'], $callback);
			exit;	
		}
	}elseif($uri[0] == 'update_delivery_device')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;	
	}elseif($uri[0] == 'update_profile_colors')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;	
	}elseif($uri[0] == 'update_profile_background')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;	
	}elseif($uri[0] == 'add_feed' || $uri[0] == 'delete_feed')
	{
		if($_SERVER['REQUEST_METHOD'] != 'POST')
		{
			if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
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
					else echo generate_error($format, 'Server error (Stage UP1).', $_SERVER['REQUEST_URI'], $callback);
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
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server Error (Stage f1).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			
			$user->logout();
			$user->login($u->username, $u->password); 
			if( ! $user->is_logged ) 
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
					else echo generate_error($format, 'Server error (Stage f2).', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
		}elseif($bauth_status) $id = $user->id;
		
		$error = false;
		$errmsg = '';
		$newfeed_auth_req = false;
		
		if($uri[0] == 'add_feed')
		{
			if(!isset($_POST['url']) || !is_valid_url($_POST['url']))
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Invalid url parameter.', $_SERVER['REQUEST_URI'], $callback);
			
				exit;		
			}
			$newfeed_url	= trim(urldecode($_POST['url']));
			if(isset($_POST['filter']))
			{
				$newfeed_filter	= trim( mb_strtolower(urldecode($_POST['newfeed_filter'])) );
				$newfeed_filter	= preg_replace('/[^\,א-תÀ-ÿ一-龥а-яa-z0-9-\_\.\#\s]/iu', '', $newfeed_filter);
				$newfeed_filter	= preg_replace('/\s+/ius', ' ', $newfeed_filter);
				$newfeed_filter	= preg_replace('/(\s)*(\,)+(\s)*/iu', ',', $newfeed_filter);
				$newfeed_filter	= trim( trim($newfeed_filter, ',') );
				$newfeed_filter	= str_replace(',', ', ', $newfeed_filter);
			}else $newfeed_filter = '';
			
			$newfeed_username	= isset($_POST['username']) ? trim(urldecode($_POST['username'])) : '';
			$newfeed_password	= isset($_POST['password']) ? trim(urldecode($_POST['password'])) : '';
		
			$f	= '';

			$f	= new rssfeed($newfeed_url);
			$auth	= $f->check_if_requires_auth();
			if( $f->error ) 
			{
				$error	= TRUE;
				$errmsg	= 'Feed authentication error.';
			}
			elseif( $auth ) 	$newfeed_auth_req	= TRUE;
			else 
			{
				$f->read();
				if( $f->error ) 
				{
					$error	= TRUE;
					$errmsg	= 'Error reading rss feed';
				}
			}
			
			if( !$error && $newfeed_auth_req && !empty($newfeed_username) && !empty($newfeed_password) ) 
			{
				$f->set_userpwd($newfeed_username.':'.$newfeed_password);
				$auth	= $f->check_if_requires_auth();
				if( $f->error || $auth ) {
					$error	= TRUE;
					$errmsg	= 'Wrong username/password.';
				}
				else 
				{
					$f->read();
					if( $f->error ) 
					{
						$error	= TRUE;
						$errmsg	= 'Inappropriate rss feed';
					}
				}
			}
			if( !$error && $f->is_read ) 
			{
				$f->fetch();
				$lastdate	= $f->get_lastitem_date();
				if( ! $lastdate ) $lastdate	= time();
				
				$title	= $f->title;
				if( empty($title) ) $title	= preg_replace('/^(http|https|ftp)\:\/\//iu', '', $newfeed_url);

				$title	= $db2->e($title);
				$usrpwd	= $newfeed_auth_req ? ($newfeed_username.':'.$newfeed_password) : '';
				$usrpwd	= $db2->e($usrpwd);
				$keywords	= str_replace(', ', ',', $newfeed_filter);
				$keywords	= $db2->e($keywords);
				
				$q = 'SELECT id FROM users_rssfeeds WHERE is_deleted=0 AND user_id="'.intval($id).'" AND feed_url="'.$db2->e($newfeed_url).'" AND feed_userpwd="'.$usrpwd.'" AND filter_keywords="'.$keywords.'" LIMIT 1';
				$db2->query($q);
				
				if( 0 == $db2->num_rows() ) 
				{
					$q = 'INSERT INTO users_rssfeeds SET is_deleted=0, user_id="'.intval($id).'", feed_url="'.$db2->e($newfeed_url).'", feed_title="'.$title.'", feed_userpwd="'.$usrpwd.'", filter_keywords="'.$keywords.'", date_added="'.time().'", date_last_post=0, date_last_crawl="'.time().'", date_last_item="'.$lastdate.'"';		
					$db2->query($q);
				}
				$twitter_data = new TwitterData($format, $callback, $id);
				$answer = $twitter_data->data_header();
		
				$answer .= $twitter_data->data_section('user');	
					$answer .=  $twitter_data->print_user($id);	
						$answer .= ($format == 'json')? ',' : '';	
						$answer .= $twitter_data->data_section('status', TRUE);
							$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($id).'\' AND api_id<>2 AND api_id<>6 ORDER BY id DESC LIMIT 1';				
							$answer .=  $twitter_data->print_status(0, FALSE, $q);	
						$answer .= $twitter_data->data_section('status', FALSE, TRUE);			
				$answer .= $twitter_data->data_section('user', FALSE, TRUE);
				$answer .= $twitter_data->data_bottom();
				
				echo $answer;
				exit;				
			}else
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, $errmsg, $_SERVER['REQUEST_URI'], $callback);
				exit;	
			}
		}else
		{
			if(isset($uri[1]) && is_numeric($uri[1]))
			{
				$q = 'UPDATE users_rssfeeds SET is_deleted=1 WHERE id="'.intval($uri[1]).'" AND user_id="'.intval($id).'" LIMIT 1';

				$res = $db2->query($q); 
				if(!$db2->affected_rows($res))
				{
					if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
						else echo generate_error($format, 'Invalid feed or user id', $_SERVER['REQUEST_URI'], $callback);
					exit;	
				}
				$twitter_data = new TwitterData($format, $callback, $id);
				$answer = $twitter_data->data_header();
		
				$answer .= $twitter_data->data_section('user');	
					$answer .=  $twitter_data->print_user($id);	
						$answer .= ($format == 'json')? ',' : '';	
						$answer .= $twitter_data->data_section('status', TRUE);
							$q = 'SELECT id AS pid FROM posts WHERE user_id=\''.intval($id).'\' AND api_id<>2 AND api_id<>6 ORDER BY id DESC LIMIT 1';				
							$answer .=  $twitter_data->print_status(0, FALSE, $q);	
						$answer .= $twitter_data->data_section('status', FALSE, TRUE);			
				$answer .= $twitter_data->data_section('user', FALSE, TRUE);
				$answer .= $twitter_data->data_bottom();
				
				echo $answer;
				exit;		
			}else
			{
				if(!isset($_POST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid feed id', $_SERVER['REQUEST_URI'], $callback);
				exit;	
			}
		}
	}
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;	
?>