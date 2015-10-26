<?php
	
	// Site Address Here:
	// 
		$C->DOMAIN		= 'localhost:90';
		$C->SITE_URL	= 'http://localhost:90/frameworks/sharetronix/1_5_3/';
	// 
	
	// Random identifier for this installation on this server
	// 
		$C->RNDKEY	= '6ccfd';
	// 
	
	// MySQL SETTINGS
	// 
		$C->DB_HOST	= 'localhost';
		$C->DB_USER	= 'root';
		$C->DB_PASS	= '';
		$C->DB_NAME	= 'framework_sharetronix_153';
		$C->DB_MYEXT = 'mysqli'; // 'mysqli' or 'mysql'
	// 
	
	// CACHE SETTINGS
	// 
		$C->CACHE_MECHANISM	= 'filesystem';	// 'apc' or 'memcached' or 'mysqlheap' or 'filesystem'
		$C->CACHE_EXPIRE		= 3600;
		$C->CACHE_KEYS_PREFIX	= '6ccfd';
		
		// If 'memcached':
		$C->CACHE_MEMCACHE_HOST	= '';
		$C->CACHE_MEMCACHE_PORT	= '';
		
		// If 'filesystem':
		$C->CACHE_FILESYSTEM_PATH	= $C->INCPATH.'cache/';
	// 
	
	// IMAGE MANIPULATION SETTINGS
	// 
		$C->IMAGE_MANIPULATION	= 'gd';	// 'imagemagick_cli' or 'gd'
		
		// if 'imagemagick_cli' - /path/to/convert
		$C->IM_CONVERT	= 'convert';
	// 
	
	// DEFAULT LANGUAGE
	// 
		$C->LANGUAGE	= 'fa';
	// 
	
	// USERS ACCOUNTS SETTINGS
	// 
		// if urls are user.site.com or site.com/user
		// this setting is still beta and it is not working properly
		$C->USERS_ARE_SUBDOMAINS	= FALSE;
	// 
	
	// RPC PING SETTINGS
	// 
		$C->RPC_PINGS_ON		= TRUE;
		$C->RPC_PINGS_SERVERS	= array('http://rpc.pingomatic.com');
	// 
	
	// TWITTER & FACEBOOK CONNECT SETTINGS
	//
		// To activate Facebook Connect, check out the README.txt file
		$C->FACEBOOK_API_KEY		= '';
		
		// To activate Twitter OAuth login, check out the README.txt file
		$C->TWITTER_CONSUMER_KEY	= '';
		$C->TWITTER_CONSUMER_SECRET	= '';
		
		// Bit.ly Integration - used for sharing posts to twitter
		$C->BITLY_LOGIN			= 'blogtronixmicro';
		$C->BITLY_API_KEY			= 'R_ffd756f66a4f5082e37989f1bc3301a6';
		
		// For inviting Yahoo contacts. Check out the README.txt file
		$C->YAHOO_CONSUMER_KEY		= '';
		$C->YAHOO_CONSUMER_SECRET	= '';
	//
	
	// FOOTER "Powered by Blogtronix" BACKLINK
	// 
		// The License requires you to keep this backlink. To remove it, visit this page
		// and follow the instructions: http://sharetronix.com/opensource/linkremoval
		// Otherwise you are not allowed to modify this variable or remove the link.
		$C->FOOTER_REMOVE_BACKLINK	= FALSE;
	// 
	
	// IF YOUR SERVER SUPPORTS CRONJOBS, READ THE FILE ./system/cronjobs/readme.txt 
	// 
		$C->CRONJOB_IS_INSTALLED	= FALSE;
	// 
	
	// DO NOT REMOVE THIS
	// 
		$C->INSTALLED	= TRUE;
		$C->VERSION		= '1.5.1';
		$C->DEBUG_USERS		= array();
	// 
	
?>