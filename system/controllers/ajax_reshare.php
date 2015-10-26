<?php
	
	if( !$this->network->id ) {
		echo 'ERROR';
		return;
	}
	if( !$this->user->is_logged ) {
		echo 'ERROR';
		return;
	}
	if( !isset($_POST['postid']) ) {
		echo 'ERROR';
		return;
	}
	
	$p	= new post('public', $_POST['postid']);
	if( $p->error ) {
		echo 'ERROR';
		return;
	}
	
	if( $p->reshare_post() ) {
		echo 'OK';
		return;
	}
	
	echo 'ERROR';
	return;
	
?>