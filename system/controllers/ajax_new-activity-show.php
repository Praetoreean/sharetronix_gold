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
	$group_id = intval($_POST['group_id']);
	if( $group_id<0 ){
		echo 'ERROR';
		return;
	}
	
	$this->load_langfile('inside/global.php');
	$this->load_langfile('inside/dashboard.php');
	
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
		case 'dashboard_all': $q = 'SELECT b.id AS pid, p.*, "public" AS `type` FROM post_userbox b LEFT JOIN posts p ON p.id=b.post_id WHERE b.user_id="'.$this->user->id.'" AND b.id>'.$last_post_id.' ORDER BY b.id DESC ';
			break;
		case 'dashboard_feeds': $q	= 'SELECT b.id AS pid, p.*, "public" AS `type` FROM post_userbox_feeds b LEFT JOIN posts p ON p.id=b.post_id WHERE b.user_id="'.$this->user->id.'" AND b.id>'.$last_post_id.' ORDER BY b.id DESC ';
			break;
		case 'dashboard_tweets': $q	= 'SELECT b.id AS pid, p.*, "public" AS `type` FROM post_userbox_tweets b LEFT JOIN posts p ON p.id=b.post_id WHERE b.user_id="'.$this->user->id.'" b.id>'.$last_post_id.' ORDER BY b.id DESC ';
			break;
		case 'dashboard_@me': $q = 'SELECT DISTINCT p.*, p.id AS pid, "public" AS `type` FROM posts p INNER JOIN (SELECT pm.post_id, p.date FROM posts_mentioned pm, posts p WHERE pm.user_id="'.$this->user->id.'" AND pm.post_id=p.id UNION SELECT p.post_id, p.date FROM posts_comments p, posts_comments_mentioned c WHERE c.comment_id = p.id AND c.user_id ="'.$this->user->id.'") x ON x.post_id=p.id '.$not_in_groups.$without_users.' AND p.id>'.$last_post_id.' ORDER BY x.date DESC '; 
			break;
		case 'dashboard_group': $q	= 'SELECT *, id AS pid, "public" AS `type` FROM posts WHERE group_id="'.$group_id.'" AND id>'.$last_post_id.' ORDER BY id DESC ';
			break;
		case 'dashboard_everybody': $q = 'SELECT p.*, p.id AS pid, "public" AS `type` FROM posts p WHERE p.user_id<>0 AND p.api_id<>2 AND p.api_id<>6 AND p.id>'.$last_post_id.' '.$not_in_groups.$without_users.' ORDER BY p.id DESC ';
			break;
		case 'user_updates_posts': 
			$q	= 'SELECT *, "public" AS `type`, id AS pid FROM posts WHERE user_id="'.$user_id.'" '.$not_in_groups.' AND api_id<>2 AND api_id<>6 id>'.$last_post_id.' ORDER BY id DESC ';
			break;
		case 'user_updates_rss': $q	= 'SELECT *, id AS pid, "public" AS `type` FROM posts WHERE user_id="'.$user_id.'" '.$not_in_groups.' AND api_id=2 AND id>'.$last_post_id.' ORDER BY id DESC ';
			break;
		case 'user_updates_tweets': $q	= 'SELECT *, id AS pid, "public" AS `type` FROM posts WHERE user_id="'.$user_id.'" '.$not_in_groups.' AND api_id=6 AND id>'.$last_post_id.' ORDER BY id DESC ';
			break;
		case 'user_updates_all': 
			$reshared	= array();
			$db2->query('SELECT post_id FROM posts_reshares WHERE user_id="'.$user_id.'" ');
			while($tmp = $db2->fetch_object()) {
				$reshared[]	= intval($tmp->post_id);
			}
			$q	= 'SELECT *, id AS pid, "public" AS `type` FROM posts WHERE ((user_id="'.$user_id.'" '.$not_in_groups.') '.( count($reshared)==0 ? '' : ' OR id IN('.implode(', ',$reshared).')' ).') id>'.$last_post_id.' ORDER BY id DESC ';
			break;
		case 'group_updates': $q	= 'SELECT *, id AS pid, "public" AS `type` FROM posts WHERE group_id="'.$group_id.'" AND id>'.$last_post_id.' ORDER BY id DESC ';
			break;
		case 'user_private_all': $q	= 'SELECT *, id AS pid, "0" AS `likes`, "private" AS `type` FROM posts_pr p WHERE (p.user_id="'.$this->user->id.'" OR (p.to_user="'.$this->user->id.'" AND p.is_recp_del=0)) AND p.id>'.$last_post_id.' ORDER BY p.date_lastcomment DESC, p.id DESC ';
			break;
		case 'user_private_inbox': $q	= 'SELECT *, p.id AS pid, "0" AS likes, "private" AS `type` FROM posts_pr p WHERE p.to_user="'.$this->user->id.'" AND p.is_recp_del=0 AND p.id>'.$last_post_id.' ORDER BY p.date_lastcomment DESC, p.id DESC ';
			break;
	}

	$db2->query($q);
	if( $db2->num_rows() > 0 ){
		
		$tmpposts	= array();
		$tmpids	= array();
		$postusrs	= array();
		$buff 	= NULL; 	
		$last_post_id = 0;
		$i=1;
		while($obj = $db2->fetch_object()) {
			if( $i == 1 ){
				$last_post_id = $obj->pid;
			}
			$i++;
			
			$buff = new post($obj->type, FALSE, $obj);
			if( $buff->error ) {
				continue;
			}
			$tmpposts[] = $buff;
			$tmpids[]	= $buff->post_tmp_id;
			$postusrs[]	= $buff->post_user->id;
		}
		unset($buff);
		$postusrs = array_unique($postusrs);
		
		ob_start();
		
		$D->if_follow_me = array();
		if( count($postusrs)>0 ){
			$r = $db2->query('SELECT who FROM users_followed WHERE who IN ('.implode(',', $postusrs).') AND whom="'.$this->user->id.'"');
			while($o = $db2->fetch_object($r)){
				if( isset($D->if_follow_me[$o->who]) ){
					continue;
				}
				$D->if_follow_me[$o->who] = 1;
			}
		}
		$D->i_follow	= array_fill_keys(array_keys($this->network->get_user_follows($this->user->id, FALSE, 'hefollows')->follow_users), 1); 
		$D->i_follow[$this->user->id] 	= 1; 
		
		foreach($tmpposts as $tmp) {
			$D->p	= $tmp;
			$D->parsedpost_attlink_maxlen	= 51;
			$D->parsedpost_attfile_maxlen	= 48;
			if( isset($D->p->post_attached['image']) ) {
				$D->parsedpost_attlink_maxlen	-= 10;
				$D->parsedpost_attfile_maxlen	-= 12;
			}
			if( isset($D->p->post_attached['videoembed']) ) {
				$D->parsedpost_attlink_maxlen	-= 10;
				$D->parsedpost_attfile_maxlen	-= 12;
			}
			$D->show_my_email = FALSE;
			if( isset( $D->if_follow_me[$D->p->post_user->id] ) || $D->p->post_user->id == $this->user->id || $this->user->info->is_network_admin ){
				$D->show_my_email = TRUE;
			}

			$D->protected_profile = FALSE;
			$right_post_type = (!$D->p->is_system_post && !$D->p->is_feed_post);
			
			if($right_post_type && !$D->show_my_email && $D->p->post_user->is_profile_protected){
				$D->protected_profile = TRUE;
			}
			
			/*if($D->p->post_likesnum > 0){
				$D->post_likes_html = '';
				$usrs = $D->p->get_post_likes();
				
				if( count($usrs) > 0 ){
					foreach($usrs as $u_s_r){
						$D->post_likes_html .= '<a href=\\\''.userlink($u_s_r['username']).'\\\'><img src=\\\''.$C->SITE_URL.'i/avatars/thumbs3/'.$u_s_r['avatar'].'\\\' title=\\\' '.$u_s_r['username'].' \\\' class=\\\'post-like-img\\\' /></a>';
					}
				}else{
					$D->p->post_likesnum = 0;
				}
			}*/
			
			$D->show_reshared_design = FALSE;
			if($tab == 'dashboard_reshares_byme' || $tab == 'dashboard_reshares_byother'){
				$D->show_reshared_design = TRUE;
			}elseif( count( array_intersect(array_keys($D->p->post_reshares) , $D->i_follow) )>0 ){
				$D->show_reshared_design = TRUE;
			}
			
			$this->load_template('single_post.php');
		}
		$data	= ob_get_contents();

		ob_end_clean();

		echo 'OK:'.$last_post_id.':'.$data;
		return;
	}
	
	echo 'ERROR';
	return;
?>