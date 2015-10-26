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
	}
	
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
	else echo generate_error($format, 'Not implemented feature. Contact our support team for more information.', $_SERVER['REQUEST_URI'], $callback);
		
	exit;		
?>