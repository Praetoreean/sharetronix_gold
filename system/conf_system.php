<?php
	
	$C	= new stdClass;
	$C->INCPATH		= dirname(__FILE__).'/';
	
	if( ! file_exists($C->INCPATH.'conf_main.php') ) {
		exit;
	}
	require_once($C->INCPATH.'conf_main.php');
	
	chdir( $C->INCPATH );
	
	$C->DEBUG_MODE		= in_array($_SERVER['REMOTE_ADDR'], $C->DEBUG_USERS);
	if( $C->DEBUG_MODE ) {
		ini_set( 'error_reporting', E_ALL | E_STRICT	);
		ini_set( 'display_errors',			1	);
	}
	
	$C->IMG_URL		= $C->SITE_URL.'i/';
	$C->IMG_DIR		= $C->INCPATH.'../i/';
	$C->TMP_URL		= $C->IMG_URL.'tmp/';
	$C->TMP_DIR		= $C->IMG_DIR.'tmp/';
	
	$C->API_ID		= 0;
	
	$C->DEF_AVATAR_USER	= '_noavatar_user.gif';
	$C->DEF_AVATAR_GROUP	= '_noavatar_group.gif';
	
	$C->AVATAR_SIZE	= 200;
	$C->AVATAR_SIZE1	= 50;
	$C->AVATAR_SIZE2	= 16;
	$C->AVATAR_SIZE3	= 30;
	
	$C->POST_MAX_SYMBOLS	= 160;
	$C->POST_MAX_CHAT_MESSAGE = 1000;
	$C->PAGING_NUM_USERS	= 24;
	$C->PAGING_NUM_POSTS	= 15;
	$C->PAGING_NUM_GROUPS	= 24;
	$C->PAGING_NUM_COMMENTS	= 5;
	$C->POST_LAST_COMMENTS	= 5;
	
	if( substr($_SERVER['HTTP_HOST'], 0, 2) == 'm.' ) {
		$C->API_ID		= 1;
	}
	elseif( $_SERVER['REQUEST_URI']=='/m' || substr($_SERVER['REQUEST_URI'], 0, 3)=='/m/' ) {
		$C->API_ID		= 1;
	}
	if( $C->API_ID == 1 ) {
		$C->PAGING_NUM_USERS	= 10;
		$C->PAGING_NUM_POSTS	= 5;
		$C->PAGING_NUM_GROUPS	= 10;
		$C->PAGING_NUM_COMMENTS	= 5;
	}
	
	$C->ATTACH_VIDEO_THUMBSIZE	= 60;
	$C->ATTACH_IMAGE_THUMBSIZE	= 60;
	$C->ATTACH_IMAGE_MXWIDTH	= 600;
	$C->ATTACH_IMAGE_MXHEIGHT	= 500;
	
	$C->POST_ICONS	= array (
	// Set for Sharetronix Platform by Nipoto
	// www.Sharetronix.ir
		// Yahoo! Smileys Code
		'B-)'	=> 's1.gif',
		'b-)'	=> 's1.gif',
		'#:-s'	=> 's2.gif',
		'#:-S'	=> 's2.gif',
		':-&'	=> 's3.gif',
		':-$'	=> 's4.gif',
		'[-('	=> 's5.gif',
		':D'	=> 's31.gif',
		':-D'	=> 's31.gif',
		':d'	=> 's31.gif',
		':-d'	=> 's31.gif',
		'@-)'	=> 's8.gif',
		':-w'	=> 's9.gif',
		':w'	=> 's9.gif',
		':-W'	=> 's9.gif',
		':W'	=> 's9.gif',
		':-L'	=> 's10.gif',
		':-l'	=> 's10.gif',
		';))'	=> 's14.gif',
		':-@'	=> 's15.gif',
		'x('	=> 's22.gif',
		'X('	=> 's22.gif',
		'x-('	=> 's22.gif',
		'X-('	=> 's22.gif',
		':-B'	=> 's28.gif',
		':-b'	=> 's28.gif',
		':B'	=> 's28.gif',
		':b'	=> 's28.gif',
		':|'	=> 's23.gif',
		':-o'	=> 's20.gif',
		':-O'	=> 's20.gif',
		':o'	=> 's20.gif',
		':O'	=> 's20.gif',
		'^:)^'	=> 's16.gif',
		'=))'	=> 's29.gif',
		'>:p'	=> 's11.gif',
		':-j'	=> 's17.gif',
		'<:-P'	=> 's7.gif',
		'<:-p'	=> 's7.gif',
		'=;'	=> 's24.gif',
		'>:D<'	=> 's19.gif',
		'>:d<'	=> 's19.gif',
		'$-)'	=> 's12.gif',
		'0:-)'	=> 's21.gif',
		';;)'	=> 's27.gif',
		';)'	=> 's34.gif',
		':-??'	=> 's18.gif',
		':)'	=> 's33.gif',
		':-/'	=> 's30.gif',
		':(('	=> 's26.gif',
		':-p'	=> 's32.gif',
		':-P'	=> 's32.gif',
		':p'	=> 's32.gif',
		':-P'	=> 's32.gif',
		':))'	=> 's37.gif',
		'>:/'	=> 's13.gif',
		'@};-'	=> 's43.gif',

		':-?'	=> 's40.gif',
		'=D>'	=> 's42.gif',
		'=d>'	=> 's42.gif',
		':-s'	=> 's41.gif',
		':S'	=> 's41.gif',
		':-S'	=> 's41.gif',
		':s'	=> 's41.gif',
		'/:)'	=> 's38.gif',
		'~x('	=> 's36.gif',
		'~X('	=> 's36.gif',
		':x'	=> 's39.gif',
		':X'	=> 's39.gif',
		':-x'	=> 's39.gif',
		':-X'	=> 's39.gif',
		'<):)'	=> 's35.gif',
		'(:|'	=> 's307.gif',
		':">'	=> 's90.gif',
		'\:D/'	=> 's69.gif',
		'\:d/'	=> 's69.gif',
		'b-('	=> 's66.gif',
		'B-('	=> 's66.gif',
		'[-o<'	=> 's63.gif',
		'8-X'	=> 's59.gif',
		'8-x'	=> 's59.gif',
		':-*'	=> 's101.gif',
		'\m/'	=> 's111.gif',
		'\M/'	=> 's111.gif',
		'#-o'	=> 's400.gif',
		'#-O'	=> 's400.gif',
		':-ss'	=> 's402.gif',
		':-SS'	=> 's402.gif',
		':^o'	=> 's44.gif',
		':^O'	=> 's44.gif',
		':-"'	=> 's605.gif',
		':O)'	=> 's304.gif',
		':o)'	=> 's304.gif',
		':)]'	=> 's100.gif',
		'8->'	=> 's105.gif',
		'%-('	=> 's107.gif',
		'x_x'	=> 's109.gif',
		'X_X'	=> 's109.gif',
		':('	=> 's700.gif',
		// Nipoto Smileys Code
		'{-1-}'	=> 's6.gif',
		'{-2-}'	=> 's1.gif',
		'{-3-}'	=> 's2.gif',
		'{-4-}'	=> 's3.gif',
		'{-5-}'	=> 's4.gif',
		'{-6-}'	=> 's5.gif',
		'{-7-}'	=> 's31.gif',
		'{-8-}'	=> 's8.gif',
		'{-9-}'	=> 's9.gif',
		'{-10-}'	=> 's10.gif',
		'{-11-}'	=> 's14.gif',
		'{-12-}'	=> 's15.gif',
		'{-13-}'	=> 's22.gif',
		'{-14-}'	=> 's28.gif',
		'{-15-}'	=> 's23.gif',
		'{-16-}'	=> 's20.gif',
		'{-17-}'	=> 's16.gif',
		'{-18-}'	=> 's29.gif',
		'{-19-}'	=> 's11.gif',
		'{-20-}'	=> 's17.gif',
		'{-21-}'	=> 's7.gif',
		'{-22-}'	=> 's24.gif',
		'{-23-}'	=> 's19.gif',
		'{-24-}'	=> 's12.gif',
		'{-25-}'	=> 's21.gif',
		'{-26-}'	=> 's27.gif',
		'{-27-}'	=> 's34.gif',
		'{-28-}'	=> 's18.gif',
		'{-29-}'	=> 's33.gif',
		'{-30-}'	=> 's30.gif',
		'{-31-}'	=> 's26.gif',
		'{-32-}'	=> 's32.gif',
		'{-33-}'	=> 's37.gif',
		'{-34-}'	=> 's13.gif',
		'{-35-}'	=> 's43.gif',

		'{-36-}'	=> 's40.gif',
		'{-37-}'	=> 's42.gif',
		'{-38-}'	=> 's41.gif',
		'{-39-}'	=> 's38.gif',
		'{-40-}'	=> 's36.gif',
		'{-41-}'	=> 's39.gif',
		'{-42-}'	=> 's35.gif',
		'{-43-}'	=> 's307.gif',
		'{-44-}'	=> 's90.gif',
		'{-45-}'	=> 's69.gif',
		'{-46-}'	=> 's66.gif',
		'{-47-}'	=> 's63.gif',
		'{-48-}'	=> 's59.gif',
		'{-49-}'	=> 's101.gif',
		'{-50-}'	=> 's111.gif',
		'{-51-}'	=> 's400.gif',
		'{-52-}'	=> 's402.gif',
		'{-53-}'	=> 's44.gif',
		'{-54-}'	=> 's605.gif',
		'{-55-}'	=> 's304.gif',
		'{-56-}'	=> 's100.gif',
		'{-57-}'	=> 's105.gif',
		'{-58-}'	=> 's107.gif',
		'{-59-}'	=> 's109.gif',
		'{-60-}'	=> 's700.gif',
	);
	
	$C->THEME	= 'default';
	$C->DEF_SITE_URL		= $C->SITE_URL;
	$C->OUTSIDE_DOMAIN	= $C->DOMAIN;
	$C->OUTSIDE_SITE_URL	= $C->SITE_URL;
	$C->SITE_TITLE		= '';
	$C->OUTSIDE_SITE_TITLE	= '';
	$C->DEF_LANGUAGE	= $C->LANGUAGE;
	
	ini_set( 'magic_quotes_runtime',		0	);
	ini_set( 'session.name',			my_session_name($C->DOMAIN)	);
	ini_set( 'session.cache_expire',		300	);
	ini_set( 'session.cookie_lifetime',		0	);
	ini_set( 'session.cookie_path',		'/'	);
	ini_set( 'session.cookie_domain',		cookie_domain()	);
	ini_set( 'session.cookie_httponly',		1	);
	ini_set( 'session.use_only_cookies',	1	);
	ini_set( 'session.gc_maxlifetime',		10800	);
	ini_set( 'session.gc_probability',		1	);
	ini_set( 'session.gc_divisor',		1000	);
	ini_set( 'zlib.output_compression_level',	7	);
	ini_set( 'max_execution_time',		20	);
	
	if( ! function_exists('mb_internal_encoding') ) {
		require_once( $C->INCPATH.'helpers/func_mbstring.php' );
	}
	mb_internal_encoding('UTF-8');
	
	if( ! function_exists('json_encode') ) {
		require_once( $C->INCPATH.'helpers/func_json.php' );
	}
	
?>