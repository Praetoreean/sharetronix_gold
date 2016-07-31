<?php
	if( !isset($_POST['tab']) || !isset($_POST['user_id']) || !isset($_POST['last_post_id']) ){
		echo 'ERROR';
		return;
	}
	
	$tab = htmlspecialchars($db2->e($_POST['tab']));
	if( empty($tab) ){
		echo 'ERROR';
		return;
	}
	
	$last_post_id = intval($_POST['last_post_id']);
	if( $last_post_id<1 ){
		echo 'ERROR';
		return;
	}
	
	$user_id = intval($_POST['user_id']);
	if( $user_id<1 ){
		echo 'ERROR';
		return;
	}
	$group_id = intval($_POST['user_id']);
	if( $group_id<1 ){
		echo 'ERROR';
		return;
	}
	
	$need_id_filter = array('dashboard_@me', 'dashboard_everybody', 'user_updates_all', 'user_updates_posts', 'user_updates_tweets', 'user_updates_rss', 'user_updates_reshares');
	
	$not_in_groups	= '';
	$without_users	= '';
	if( in_array($tab, $need_id_filter) && (!$this->user->is_logged || !$this->user->info->is_network_admin) ) {
		$not_in_groups	= array();
	
		$not_in_groups	= count($not_in_groups)>0 ? ('AND p.group_id NOT IN('.implode(', ', $not_in_groups).')') : '';
		
		$without_users = array();
		$without_users = count($without_users)>0 ? (' AND (p.group_id>0 OR p.user_id NOT IN('.implode(', ', $without_users).'))') : '';	
	}
	
	$q = '';
	switch($tab){
		case 'dashboard_all': $q = 'SELECT COUNT(*) AS cnt FROM post_userbox b LEFT JOIN posts p ON p.id=b.post_id WHERE b.user_id="'.$user_id.'" AND b.id>'.$last_post_id;
			break;
		case 'dashboard_feeds': $q = 'SELECT COUNT(*) AS cnt FROM post_userbox_feeds WHERE user_id="'.$this->user->id.'" AND id>'.$last_post_id;
			break;
		case 'dashboard_tweets': $q = 'SELECT COUNT(*) AS cnt FROM post_userbox_tweets WHERE user_id="'.$this->user->id.'" AND id>'.$last_post_id;
			break;
		case 'dashboard_@me': $q = 'SELECT COUNT(p.id) AS cnt FROM posts p INNER JOIN (SELECT post_id FROM posts_mentioned WHERE user_id="'.$this->user->id.'" AND post_id>'.$last_post_id.' UNION SELECT p.post_id FROM posts_comments p, posts_comments_mentioned c WHERE c.comment_id = p.id AND c.user_id ="'.$this->user->id.'" AND p.id>'.$last_post_id.') x ON x.post_id=p.id '.$not_in_groups.$without_users;
			break;
		case 'dashboard_group': $q = 'SELECT COUNT(*) AS cnt FROM posts WHERE group_id="'.$group_id.'" AND p.id>'.$last_post_id;
			break;
		case 'dashboard_everybody': $q = 'SELECT COUNT(*) AS cnt FROM posts p WHERE p.user_id<>0 AND p.api_id<>2 AND p.api_id<>6 '.$not_in_groups.$without_users.' AND p.id>'.$last_post_id;
			break;
		case 'user_updates_posts': $q = 'SELECT COUNT(*) AS cnt FROM posts WHERE user_id="'.$user_id.'" '.$not_in_groups.' AND api_id<>2 AND api_id<>6  AND p.id>'.$last_post_id;
			break;
		case 'user_updates_rss': $q = 'SELECT COUNT(*) AS cnt FROM posts WHERE user_id="'.$user_id.'" '.$not_in_groups.' AND api_id=2 AND p.id>'.$last_post_id;
			break;
		case 'user_updates_tweets': $q = 'SELECT COUNT(*) AS cnt FROM posts WHERE user_id="'.$user_id.'" '.$not_in_groups.' AND api_id=6 AND p.id>'.$last_post_id; 
			break;
		case 'user_updates_all': 
			$reshared	= array();
			$db2->query('SELECT post_id FROM posts_reshares WHERE user_id="'.$user_id.'" ');
			while($tmp = $db2->fetch_object()) {
				$reshared[]	= intval($tmp->post_id);
			}
			$q = 'SELECT COUNT(*) AS cnt FROM posts WHERE ((user_id="'.$user_id.'" '.$not_in_groups.') '.( count($reshared)==0 ? '' : ' OR id IN('.implode(', ',$reshared).')' ).') AND p.id>'.$last_post_id;
			break;
		case 'group_updates': $q = 'SELECT COUNT(*) AS cnt FROM posts p WHERE p.group_id="'.$g->id.'" AND p.id>'.$last_post_id;
			break;
		case 'user_private_all': $q = 'SELECT COUNT(*) AS cnt FROM posts_pr p WHERE (p.user_id="'.$this->user->id.'" OR (p.to_user="'.$this->user->id.'" AND p.is_recp_del=0)) AND p.id>'.$last_post_id;
			break;
		case 'user_private_inbox': $q = 'SELECT COUNT(*) AS cnt FROM posts_pr p WHERE p.to_user="'.$this->user->id.'" AND p.is_recp_del=0 AND p.id>'.$last_post_id;
			break;
	}
	
	if( !empty($q) ){
		$db2->query($q);
		$obj = $db2->fetch_object();
		$num_new_data = ($obj)? $obj->cnt : 0;
	}else{
		$num_new_data = 0;
	}
	
	echo 'OK:'.$num_new_data;
	return;
?>