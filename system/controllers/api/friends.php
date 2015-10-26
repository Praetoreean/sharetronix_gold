<?php
	require_once( $C->INCPATH.'helpers/func_api.php' );
	require_once( $C->INCPATH.'classes/class_twitterdata.php' );
		
	$uri = $this->param('more');
	$format = $this->param('format');
	
	$features = array('ids');
	if(isset($_GET['callback']) && valid_fn($_GET['callback'])) $callback = $_GET['callback'];
		else $callback = FALSE;

	if($_SERVER['REQUEST_METHOD'] != 'GET')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid request method.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif(!is_valid_data_format($format, TRUE))
	{		
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error('xml', 'Invalid data format requested.', $_SERVER['REQUEST_URI'], $callback);

		exit;
	}elseif(!isset($uri[0]) || !in_array($uri[0], $features))
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif($uri[0] == 'ids')
	{	
		if(isset($_GET['user_id']) && is_numeric($_GET['user_id'])) $id = intval($_GET['user_id']);
		elseif(isset($_GET['screen_name'])  || (isset($uri[1]) && !is_numeric($uri[1])))
		{
			if(isset($_GET['screen_name'])) $u = $this->network->get_user_by_username(urldecode($_GET['screen_name']));
				else $u = $this->network->get_user_by_username(urldecode($uri[1]));
			if(!$u)
			{
				if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
					else echo generate_error($format, 'Invalid username/id.', $_SERVER['REQUEST_URI'], $callback);
				exit;
			}
			$id = $u->id;
		}elseif(isset($uri[1]) && is_numeric($uri[1])) $id = intval($uri[1]);
		else
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
				else echo generate_error($format, 'Parameter required.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$info	= $this->network->get_user_follows($id);
		if(!$info) 
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 500 Internal Server Error');
				else echo generate_error($format, 'Server error (Stage 1).', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$following	= array_keys($info->follow_users);
		if(!count($following))
		{
			if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 403 Not Modified');
				else echo generate_error($format, 'No friends found.', $_SERVER['REQUEST_URI'], $callback);
			exit;
		}
		
		$num_rows = count($following);
		
		$twitter_data = new TwitterData($format, $callback, $id, TRUE);
		$answer = $twitter_data->data_header();

		$answer .= $twitter_data->data_section('id_list', FALSE, FALSE, TRUE, ' type="array"');
			$answer .= $twitter_data->data_section('ids');			
			foreach($following as $user_id)
			{ 
				$check = ($num_rows-1>0)? true:false;
				$answer .= $twitter_data->data_field('id', $user_id, $check);
				$num_rows--;	
			}		
			$answer .= $twitter_data->data_section('ids', FALSE, TRUE);
		$answer .= $twitter_data->data_section('id_list', FALSE,  TRUE, TRUE);
		$answer .= $twitter_data->data_bottom();
		
		echo $answer;
		exit;
	}
	
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;	
?>