<?php	
	require_once($C->INCPATH.'helpers/func_api.php');	
	
	$uri = $this->param('more');
	$format = $this->param('format');

	if(isset($_REQUEST['callback']) && valid_fn($_REQUEST['callback'])) $callback = $_REQUEST['callback'];
		else $callback = FALSE;

	if(!is_valid_data_format($format))
	{		
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid data format requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif(!isset($uri[0]))
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
		exit;		
	}elseif($uri[0] == 'test')
	{
		switch($format)
		{
			case 'json': echo '"ok": true';
				break;
			case 'rss':
			case 'atom':
			case 'xml': echo '<ok>true</ok>';
				break;
		}
		exit;		
	}
	
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;	
?>