<?php
	
	if( !$this->network->id ) {
		$this->redirect('home');
	}
	if( !$this->user->is_logged ) {
		$this->redirect('signin');
	}
	
		$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/admin.php');
	
	$this->load_template('admin_reminder.php');
	
?>