<?php
	require_once( $C->INCPATH.'helpers/func_api.php' );
	require_once( $C->INCPATH.'classes/class_twitterdata.php' );
	
	global $user;
	$uri = $this->param('more');
	$format = $this->param('format');
	$oauth_status = false;
	$bauth_status = false;
	$not_in_groups	= '';

	if(isset($_REQUEST['callback']) && valid_fn($_REQUEST['callback'])) $callback = $_REQUEST['callback'];
		else $callback = FALSE;
		
	if($_SERVER['REQUEST_METHOD'] != 'GET')
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error('xml', 'Invalid request method or data format.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif(!is_valid_data_format($format, TRUE))
	{		
		if(!isset($_GET['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error('xml', 'Invalid data format requested.', $_SERVER['REQUEST_URI'], $callback);

		exit;
	}
	$features = array('top10', 'current', 'daily', 'weekly', 'location', 'available');
	if(!isset($uri[0]) || !in_array($uri[0], $features))
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}
	
	setlocale(LC_TIME, 'en_US');
	
	if( $uri[0] == 'top10' ) 
	{
		$dts	= @gmmktime(0, 0, 1, gmdate('m'), gmdate('d'), gmdate('Y'), time()-24*60*60);
		$msg	= array();
		$db2->query('SELECT message FROM posts WHERE user_id<>0 AND posttags>0 AND date>'.$dts);
		
		while($obj = $db2->fetch_object()) $msg[]	= stripslashes($obj->message);
		
		$data	= array();
		foreach($msg as $m) {
			if( ! preg_match_all('/\#([א-תÀ-ÿ一-龥а-яa-z0-9\-_]{1,50})/iu', $m, $matches, PREG_PATTERN_ORDER) ) {
				continue;
			}
			foreach($matches[1] as $tg) {
				$tg	= trim($tg);
				if( ! isset($data[$tg]) ) {
					$data[$tg]	= 0;
				}
				$data[$tg]	++;
			}
		}
		
		$result	= new stdClass;
		$result->trends	= array();
		$result->as_of	= gmstrftime('%a, %d %b %Y %H:%M:%S +0000', $dts);
		foreach($data as $k=>$v) {
			$result->trends[]	= (object) array(
				'name'	=> '#'.$tg,
				'url'		=> $C->SITE_URL.'search/tab:posts/s:%23'.urlencode($tg)
			);
		}
		
		$num_rows = count($result->trends);
	
		$twitter_data = new TwitterData($format, $callback, -1, TRUE);
		$answer = $twitter_data->data_header();
	
		$answer .= $twitter_data->data_section('trends', FALSE, FALSE, TRUE, ' type="array"');
	
			foreach($result->trends as $tr)
			{
				$answer .=  $twitter_data->data_section('trend', FALSE);	
					$answer .=  $twitter_data->data_field('name', $tr->name);		
					$answer .=  $twitter_data->data_field('url', $tr->url, FALSE);
				$answer .=  $twitter_data->data_section('trend', FALSE, TRUE);
				
				$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
				$num_rows--;
			}
		$answer .= $twitter_data->data_section('trends', FALSE,  TRUE, TRUE);	
		$answer .= $twitter_data->data_bottom();
		
		echo $answer;
		exit;
	}
	elseif( $uri[0] == 'current' ) 
	{
		$dts	= @gmmktime(0, 0, 1, gmdate('m'), gmdate('d'), gmdate('Y'), time()-24*60*60);
		$msg	= array();
		$db2->query('SELECT date, message FROM posts WHERE user_id<>0 AND posttags>0 AND date>'.$dts);
		while($obj = $db2->fetch_object()) 
		{
			$msg[]	= (object) array(
				'date'	=> gmdate('Y-m-d H:i:s',$obj->date),
				'text'	=> stripslashes($obj->message));
		}

		$data	= array();
		foreach($msg as $m) {
			if( ! preg_match_all('/\#([א-תÀ-ÿ一-龥а-яa-z0-9\-_]{1,50})/iu', $m->text, $matches, PREG_PATTERN_ORDER) ) {
				continue;
			}
			foreach($matches[1] as $tg) {
				$tg	= trim($tg);
				if( ! isset($data[$m->date]) ) {
					$data[$m->date]	= array();
				}
				$data[$m->date][]	= $tg;
			}
		}
		$tmp	= array();
		foreach($data as $tgs) {
			foreach($tgs as $tg) {
				if( ! isset($tmp[$tg]) ) {
					$tmp[$tg]	= 0;
				}
				$tmp[$tg]	++;
			}
		}
		if( count($tmp) > 10 ) {
			arsort($tmp);
			$tmp	= array_slice(array_keys($tmp), 9);
			foreach($tmp as $deltg) {
				foreach($data as $dt=>$tgs) {
					foreach($tgs as $i=>$tg) {
						if( $tg == $deltg ) {
							unset($data[$dt][$i]);
						}
					}
					if( count($data[$dt]) == 0 ) {
						unset($data[$dt]);
					}
				}
			}
		}
		$result	= new stdClass;
		$result->trends	= new stdClass;
		$result->as_of	= $dts; 
		foreach($data as $dt=>$tgs) {
			if( !isset($result->trends->dt) ) {
				$result->trends->dt	= array();
			}
			foreach($tgs as $tg) {

				$result->trends->dt	= (object) array(
					'query'	=> '#'.$tg,
					'name'	=> '#'.$tg,
					'url'		=> $C->SITE_URL.'search/tab:posts/s:%23'.urlencode($tg)
				);
			}
		}
		if( !isset($result->trends->dt) ) {
				$result->trends->dt	= array();
			}
			
		$num_rows = count($result->trends->dt); 

		$twitter_data = new TwitterData($format, $callback, -1, TRUE);
		$answer = $twitter_data->data_header();
	
		$answer .= $twitter_data->data_section('trends', FALSE, FALSE, TRUE, ' type="array"');
	
			foreach($result->trends->dt as $tr)
			{
				$answer .=  $twitter_data->data_section('trend', FALSE);	
					$answer .=  $twitter_data->data_field('name', $tr->name);	
					$answer .=  $twitter_data->data_field('query', $tr->query);	
					$answer .=  $twitter_data->data_field('url', $tr->url, FALSE);
				$answer .=  $twitter_data->data_section('trend', FALSE, TRUE);
				
				$answer .= ($format == 'json' && $num_rows-1>0)? ',':''; 
				$num_rows--;
			}
		$answer .= $twitter_data->data_section('trends', FALSE,  TRUE, TRUE);	
		$answer .= $twitter_data->data_bottom();

		echo $answer;
		exit;
	}
	elseif( $uri[0] == 'daily' ) 
	{
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}
	elseif( $uri[0] == 'weekly' ) {
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif( $uri[0] == 'location' ) {
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}elseif( $uri[0] == 'available' ) {
		if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 400 Bad Request');
			else echo generate_error($format, 'Invalid feature requested.', $_SERVER['REQUEST_URI'], $callback);
		exit;
	}
	if(!isset($_REQUEST['suppress_response_codes'])) header('HTTP/1.1 404 Not Found');
		else echo generate_error($format, 'Invalid resource request', $_SERVER['REQUEST_URI'], $callback);
	exit;	
	
?>