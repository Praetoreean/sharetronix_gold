<?php
	require_once($C->INCPATH.'helpers/func_api.php');	
	
	$uri = $this->param('more');
	$format = $this->param('format');

	$features = array('follow', 'leave');
	if(isset($_REQUEST['callback']) && valid_fn($_REQUEST['callback'])) $callback = $_REQUEST['callback'];
		else $callback = FALSE;

	if(!is_valid_data_format($format))
	{		
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid data format requested.', $_SERVER['REQUEST_URI'], $callback);

		exit;
	}elseif(!isset($uri[0]) || !in_array($uri[0], $features))
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif($uri[0] == 'follow')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;		
	}elseif($uri[0] == 'leave')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
		else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
		exit;	
	}
	
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;	
?>