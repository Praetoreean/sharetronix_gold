<?php
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('outside/advertise.php');
	
	$D->page_title	= $this->lang('terms_pgtitle', array('#SITE_TITLE#'=>$C->SITE_TITLE));
	$D->terms	= trim(stripslashes($C->TERMSPAGE_CONTENT));
	
	$this->load_template('advertise.php');
	
?>