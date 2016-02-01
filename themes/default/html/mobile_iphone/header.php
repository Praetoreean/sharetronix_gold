<?php
	
	if( $this->user->is_logged ) {
		$this->user->write_pageview();
	}
	
	$this->load_langfile('mobile/header.php');
	
?>
<!DOCTYPE html PUBLIC "-//WAPFORUM//DTD XHTML Mobile 1.0//EN" "http://www.wapforum.org/DTD/xhtml-mobile10.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-US">
	<head>
		<meta charset="utf-8" />
		<meta name="author" content="www.frebsite.nl" />
		<meta name="viewport" content="width=device-width initial-scale=1.0 maximum-scale=1.0 user-scalable=yes" />

		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title><?= htmlspecialchars($D->page_title) ?></title>
		<link href="<?= $C->OUTSIDE_SITE_URL ?>themes/default/css/mobile_iphone.css" media="handheld" rel="stylesheet" type="text/css" />
		<link href="<?= $C->OUTSIDE_SITE_URL ?>themes/default/css/mobile_iphone.css" media="screen" rel="stylesheet" type="text/css" />
		<script type="text/javascript" src="<?= $C->OUTSIDE_SITE_URL ?>themes/default/js/mobile_iphone.js"></script>
		<meta name="apple-mobile-web-app-capable" content="yes" />
		<meta name="viewport" content="width=device-width; initial-scale=1.0; maximum-scale=1.0;" />
		<?php if( isset($D->page_favicon) ) { ?>
		<link href="<?= $D->page_favicon ?>" type="image/x-icon" rel="shortcut icon" />
		<?php } elseif( isset($C->HDR_SHOW_FAVICON) && $C->HDR_SHOW_FAVICON == 1 ) { ?>
		<link href="<?= $C->SITE_URL ?>themes/blue-sky/imgs/favicon.ico" type="image/x-icon" rel="shortcut icon" />
		<?php } elseif( isset($C->HDR_SHOW_FAVICON) && $C->HDR_SHOW_FAVICON == 2 ) { ?>
		<link href="<?= $C->IMG_URL.'attachments/1/'.$C->HDR_CUSTOM_FAVICON ?>" type="image/x-icon" rel="shortcut icon" />
		<?php } ?>
		<script type="text/javascript"> var siteurl = "<?= $C->SITE_URL ?>"; </script>
		<?php if( $this->lang('global_html_direction') == 'rtl' ) { ?>
		<style type="text/css"> body { direction:rtl; } </style>
		<?php } ?>
		<link type="text/css" rel="stylesheet" href="<?= $C->OUTSIDE_SITE_URL.'themes/'.$C->THEME ?>/new_mobile/panel_style.css" />
		<link type="text/css" rel="stylesheet" href="<?= $C->OUTSIDE_SITE_URL.'themes/'.$C->THEME ?>/new_mobile/jquery.mmenu.all.css?" />
		<link type="text/css" rel="stylesheet" href="<?= $C->OUTSIDE_SITE_URL.'themes/'.$C->THEME ?>/new_mobile/font-awesome.css" />
		<style type="text/css">
			.mm-menu li .fa {
				margin    : 0 20px 0 5px;
				font-size : 16px;
				width     : 12px;
			}

			.mm-menu li[class*="mm-tile"] .fa {
				margin      : 0;
				line-height : 0;
			}

			.mm-menu .buttonbar-item:after {
				content : none !important;
				display : none !important;
			}

			.mm-menu {
				background : #440011 !important;
			}

			.mm-navbar {
				text-align    : center;
				position      : relative;
				border-bottom : none;
			}

			.mm-navbar:before {
				content        : "";
				display        : inline-block;
				vertical-align : middle;
				height         : 100%;
				width          : 1px;
			}

			.mm-navbar > * {
				display        : inline-block;
				vertical-align : middle;
			}

			.mm-navbar img {
				border        : 1px solid rgba(255, 255, 255, 0.6);
				border-radius : 60px;
				width         : 60px;
				height        : 60px;
				padding       : 10px;
				margin        : 0 10px;
			}

			.mm-navbar a {
				border        : 1px solid rgba(255, 255, 255, 0.6);
				border-radius : 40px;
				color         : rgba(255, 255, 255, 0.6) !important;
				font-size     : 16px !important;
				line-height   : 40px;
				width         : 40px;
				height        : 40px;
				padding       : 0;
			}

			.mm-navbar a:hover {
				border-color : #fff;
				color        : #fff !important;
			}

			.mm-listview {
				text-transform : uppercase;
				font-size      : 12px;
			}

			.mm-listview li:last-child:after {
				content : none;
																																																																																																																																																																																																																																																																						  display: none;
																																																																																																																																																																																																																																																																					  }
			.mm-listview li:after {
				left: 20px !important;
				right: 20px !important;
			}
			.mm-listview a {
				text-align: center;
				padding: 30px 0 !important;
			}
			.mm-listview a,
			.mm-listview .fa {
				color: rgba(255, 255, 255, 0.6);
			}
			.mm-listview a:hover,
			.mm-listview a:hover .fa {
				color: #fff;
			}
			.mm-listview .fa {
				position: absolute;
				left: 20px;
			}
		</style>
		<script type="text/javascript" src="<?= $C->OUTSIDE_SITE_URL.'themes/'.$C->THEME ?>/new_mobile/jquery-1.8.3.min.js"></script>
		<script type="text/javascript" src="<?= $C->OUTSIDE_SITE_URL.'themes/'.$C->THEME ?>/new_mobile/jquery.mmenu.all.min.js?v=6"></script>
		<script type="text/javascript">
			$(function() {
				$("#hm-menu")
					.mmenu({
						extensions 	: [ "theme-black" ],
						navbar 		: false,
						navbars		: {
							height 	: 4,
							content : [
								'<a href="<?= $C->SITE_URL ?>settings" class="fa fa-cog"></a>',
								'<img src="<?= $C->IMG_URL ?>avatars/thumbs1/<?= $this->user->info->avatar ?>" />',
								'<a href="<?= userlink($this->user->info->username) ?>" class="fa fa-user"></a>'
							]
						}
					}).on( 'click',
					'a[href^="#/"]' );
			});
		</script>
	</head>
	<body>
	<div class="header">
		<a href="#hm-menu"></a>
		<?= $C->SITE_TITLE ?>
	</div>