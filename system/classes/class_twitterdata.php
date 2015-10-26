<?php
class TwitterData
{
		private $format;
		private $callback;
		private $uid;
		private $is_data_array;
		private $data_section_name;
		private $data_section_final;
		private $status_id;
		private $user_id;
		
		public function __construct($format, $callback, $uid, $is_data_array = FALSE)
		{
			if($format != 'json' && $format != 'xml' && $format != 'rss' && $format != 'atom') $this->format = 'xml';
			else $this->format = $format;
			
			$this->callback = $callback;
			$this->uid = $uid;
			$this->is_data_array = FALSE;
			$this->data_section_name = '';
			$this->data_section_final = FALSE;
			$this->is_data_array = $is_data_array;
		}

		public function is_feed()
		{
			if($this->format == 'atom' || $this->format == 'rss') return true;
			else return false;
		}
			
		public function data_header()
		{
			global $C;
			$header = '';
			
			if($this->format == 'xml') $header = '<?xml version="1.0" encoding="utf-8"?'.'>';
			elseif($this->format == 'json' && $this->callback) $header = ($this->callback == '?') ? ('('): ($this->callback.'(');		
			elseif($this->format == 'rss') $header = '<?xml version="1.0" encoding="UTF-8"'.'?'.'><rss xmlns:atom="http://www.w3.org/2005/Atom" version="2.0">';
			elseif($this->format == 'atom') $header = '<?xml version="1.0" encoding="UTF-8"?'.'><feed xml:lang="en-US" xmlns:georss="http://www.georss.org/georss" xmlns="http://www.w3.org/2005/Atom">';	
	
			$header .= $this->data_desc();
			
			return $header;
		}
		public function data_desc()
		{
			global $C, $network;
			
			if($this->uid != -1)
			{
				$u = $network->get_user_by_id($this->uid);
				if(!$u) return false;
			}
			
			if($this->format == 'xml' || $this->format == 'json') $desc = '';
			else if($this->format == 'atom')
			{
				$dts	= @gmmktime(0, 0, 1, gmdate('m'), gmdate('d'), gmdate('Y'), $u->lastpost_date);
				
				if($this->uid == -1)
				{
					$desc = '<title>'.$C->SITE_TITLE.'public timeline</title>';
	  				$desc .= '<id>'.$C->SITE_URL.'1/stauses/public_timeline.atom</id>';
	  				$desc .= '<link type="text/html" href="'.$C->SITE_URL.'1/public_timeline" rel="alternate"/>';
	  				$desc .= '<link type="application/atom+xml" href="'.$C->SITE_URL.'1/statuses/public_timeline.atom" rel="self"/>';
	  				$desc .= '<updated>'.date('Y-m-d\TH:i:s\Z',time()).'</updated>';
					$desc .= '<subtitle>'.$C->SITE_TITLE.' updates from everyone!</subtitle>';
				}else
				{
					$desc = '<title>'.$C->SITE_TITLE.$u->username.'</title>';
					$desc .= '<id>'.$C->SITE_URL.'1/stauses/user_timeline/'.$u->id.'.atom</id>';
					$desc .= '<link type="text/html" href="'.$C->SITE_URL.$u->username.'" rel="alternate"/>';
					$desc .= '<link type="application/atom+xml" href="'.$C->SITE_URL.'1/statuses/user_timeline/'.$u->id.'.atom" rel="self"/>';
					$desc .= '<updated>'.date('Y-m-d\TH:i:s\Z',time()).'</updated>';
					$desc .= '<subtitle>'.$C->SITE_TITLE.' updates from '.$u->fullname.' / '.$u->username.'.</subtitle>';
				}
			}else if($this->format == 'rss')
			{
				$desc = '<channel>';
				if($this->uid != -1)
				{
					$desc .= '<title>'.$C->SITE_TITLE.$u->username.'</title>';
					$desc .= '<link>'.$C->SITE_URL.$u->username.'</link>';
					$desc .= '<atom:link type="application/rss+xml" href="'.$C->SITE_URL.'1/statuses/user_timeline/'.$u->id.'.rss" rel="self"/>';
					$desc .= '<description>'.$C->SITE_TITLE.' updates from '.$u->fullname.' / '.$u->username.'.</description>';
					$desc .= '<language>'.$u->language.'</language>';
				}else
				{
					$desc .= '<title>'.$C->SITE_TITLE.' public timeline</title>';
	    				$desc .= '<link>'.$C->SITE_URL.'public_timeline.rss</link>';
	    				$desc .= '<atom:link type="application/rss+xml"';
					$desc .= ' href="'.$C->SITE_URL.'1/statuses/public_timeline.rss" rel="self"/>';
					$desc .= '<description>'.$C->SITE_URL.' public timeline</description>';
	    				$desc .= '<language>en-us</language>';
				}
			}
			
			return $desc;
		}
		public function item_entry($final = FALSE)
		{
			if($this->format == 'rss') $en = ($final)? '</item>':'<item>';
			elseif($this->format == 'atom') $en = ($final)? '</entry>':'<entry>';
			
			return $en;
		} 
		
		public function print_status($status_id, $comma = FALSE, $q=FALSE, $post_type = 'public')
		{
			global $db2, $C;
			require_once($C->INCPATH.'classes/class_post.php');
			
			if($q)
			{
				$res = $db2->query($q);
				if(!$db2->num_rows($res)) return '';
				$res = $db2->fetch_object($res);
				$status_id = $res->pid;
			}
			$status = new post($post_type, $status_id); 	
			
			$answer = '';
			
			if(!isset($status->post_id)) return $answer;
			
			if($this->format == 'xml' || $this->format == 'rss' || $this->format == 'atom')
			{ 
				$answer .= '<created_at>'.gmdate('D M d H:i:s \+0000 Y',$status->post_date).'</created_at>';
				$answer .= '<id>'.$status->post_id.'</id>';
				$answer .= '<text>'.htmlspecialchars($status->post_message).'</text>';
				$answer .= '<source>'.$C->SITE_TITLE.'</source>';
				$answer .= '<truncated>false</truncated>';
				$answer .= '<in_reply_to_status_id>false</in_reply_to_status_id>';
				$answer .= '<in_reply_to_user_id>false</in_reply_to_user_id>';
				$answer .= '<favorited>false</favorited>';
				$answer .= '<in_reply_to_screen_name>false</in_reply_to_screen_name>';
				
			}elseif($this->format == 'json')
			{			
				$answer .= '"created_at": "'.gmdate('D M d H:i:s \+0000 Y', $status->post_date).'",';
				$answer .= '"id": '.$status->post_id.',';
				$answer .= '"text": "'.htmlspecialchars($status->post_message).'",';	
				$answer .= '"source": "'.$C->SITE_TITLE.'",';
				$answer .= '"truncated": false,';
				$answer .= '"in_reply_to_status_id": false,';
				$answer .= '"in_reply_to_user_id": false,';
				$answer .= '"favorited": false,';
				$answer .= '"in_reply_to_screen_name": false';
				$answer .= ($comma)? ',':'';
			}		
			return $answer;	
		}
		
		public function print_user($id)
		{
			global $network, $db2, $C;
			$img_path = $C->SITE_URL.'i/avatars/thumbs1/';
			
			$u = $network->get_user_by_id($id);		
			
			$info	= $network->get_user_follows($id);
			if(!$info) $friends = 0;
			
			$following	= array_keys($info->follow_users);
			$friends = count($following);
			
			$favorites = $db2->query('SELECT COUNT(user_id) AS num FROM post_favs WHERE user_id ='.$id);
			$favorites = $db2->fetch_object($favorites);
			$favorites = $favorites->num;
			
			$answer = '';
			$img_check = ($u->avatar != '')? $u->avatar:'_noavatar_user.gif';
			$active = ($u->active==1) ? 'true':'false';
			$u->about_me = substr($u->about_me, 0, 10);
			
			if($this->format == 'xml' || $this->format == 'rss' || $this->format == 'atom')
			{ 
				$answer .= '<id>'.$u->id.'</id>';
				$answer .= '<name>'.$u->fullname.'</name>';
				$answer .= '<screen_name>'.$u->username.'</screen_name>';
				$answer .= '<location>'.$u->location.'</location>';
				$answer .= '<description>'.$u->about_me.'</description>';
				$answer .= '<profile_image_url>'.$img_path.$img_check.'</profile_image_url>';
				$answer .= '<url>false</url>';
				$answer .= '<protected>false</protected>';
				$answer .= '<followers_count>'.$u->num_followers.'</followers_count>';
				$answer .= '<profile_background_color>false</profile_background_color>';
				$answer .= '<profile_text_color>false</profile_text_color>';
				$answer .= '<profile_link_color>false</profile_link_color>';
				$answer .= '<profile_sidebar_fill_color>false</profile_sidebar_fill_color>';
				$answer .= '<profile_sidebar_border_color>false</profile_sidebar_border_color>';
				$answer .= '<friends_count>'.$friends.'</friends_count>';
				$answer .= '<created_at>'.gmdate('D M d H:i:s \+0000 Y',$u->reg_date).'</created_at>';
				$answer .= '<favourites_count>'.$favorites.'</favourites_count>';
				$answer .= '<utc_offset>false</utc_offset>';
				$answer .= '<timezone>'.$u->timezone.'</timezone>';		
				$answer .= '<profile_background_image_url>false</profile_background_image_url>';
				$answer .= '<profile_background_tile>false</profile_background_tile>';
				$answer .= '<statuses_count>'.$u->num_posts.'</statuses_count>';	
				$answer .= '<notifications>false</notifications>';
				$answer .= '<following>false</following>';
				$answer .= '<verified>'.$active.'</verified>';
			}
			elseif($this->format == 'json')
			{			
				$answer .= '"id": '.$u->id.',';
				$answer .= '"name": "'.$u->fullname.'",';
				$answer .= '"screen_name": "'.$u->username.'",';
				$answer .= '"location": "'.$u->location.'",';
				$answer .= '"description": "'.$u->about_me.'",';
				$answer .= '"profile_image_url": "'.$img_path.$img_check.'",';
				$answer .= '"url": false,';
				$answer .= '"protected": false,';
				$answer .= '"followers_count": '.$u->num_followers.',';
				$answer .= '"profile_background_color": false,';
				$answer .= '"profile_text_color": false,';
				$answer .= '"profile_link_color": false,';
				$answer .= '"profile_sidebar_fill_color": false,';
				$answer .= '"profile_sidebar_border_color": false,';
				$answer .= '"friends_count": '.$friends.',';
				$answer .= '"created_at": "'.gmdate('D M d H:i:s \+0000 Y',$u->reg_date).'",';
				$answer .= '"favourites_count": "'.$favorites.'",';
				$answer .= '"utc_offset": false,';
				$answer .= '"timezone": "'.$u->timezone.'",';
				$answer .= '"profile_background_image_url": false,';
				$answer .= '"profile_background_tile": false,';	
				$answer .= '"statuses_count": '.$u->num_posts.',';
				$answer .= '"notifications": false,';
				$answer .= '"following": false,';
				$answer .= '"verified": '.$active;
			}
			
			return $answer;
		}
		public function print_status_simple($post_id, $post_type='public')
		{
			global $db2, $C;
			$answer = '';
			
			require_once($C->INCPATH.'classes/class_post.php');
	
			$status = new post($post_type, $post_id); 	
			if(!isset($status->post_id)) return $answer;
				
			$answer .= $this->item_entry();
				if($this->format == 'rss')
				{
					$answer .= '<title>'.htmlspecialchars($status->post_message).'</title>';
					$answer .= '<description>'.htmlspecialchars($status->post_message).'</description>';
					$answer .= '<pubDate>'.gmdate('D, d M Y H:i:s \+0000',$status->post_date).'</pubDate>';
					$answer .= '<guid>'.$C->SITE_URL.'view/post:'.$status->post_id.'</guid>';
					$answer .= '<link>'.$C->SITE_URL.'view/post:'.$status->post_id.'</link>';
				}elseif($this->format == 'atom')
				{
					$answer .= '<title>'.htmlspecialchars($status->post_message).'</title>';
					$answer .= '<id>'.$C->SITE_URL.'1/statuses/show/'.$post_id.'</id>';
					$answer .= '<updated>'.date('Y-m-d\TH:i:s\Z',$status->post_date).'</updated>';
					$answer .= '<link type="text/html" href="'.$C->SITE_URL.'view/post:'.$post_id.'" rel="alternate"/>';
					$answer .= '<author><name>'.$status->post_user->username.'</name></author>';
				}
			$answer .= $this->item_entry(true);
			return $answer;
		}
	
		public public function data_field($field_name, $field_value, $comma = TRUE, $is_string = TRUE)
		{
			$check = ($comma)?',':'';
			
			if($this->format == 'xml' || $this->format == 'rss' || $this->format == 'atom') $field = '<'.$field_name.'>'.$field_value.'</'.$field_name.'>';
			elseif($this->format == 'json' && $is_string) $field = '"'.$field_name.'": "'.$field_value.'"'.$check;
			elseif($this->format == 'json' && !$is_string) $field = '"'.$field_name.'": '.$field_value.$check;
	
			return $field;
		}
		
		public function data_section($name, $print_name = FALSE, $final = FALSE, $is_main = FALSE, $additional_params = '')
		{
			if($this->format == 'xml' || $this->format == 'rss' || $this->format == 'atom')
			{
				$data = (!$final)? '<'.$name.$additional_params.'>':'</'.$name.'>';
			}
			elseif($this->format == 'json')
			{
				if($this->is_data_array && $is_main) $data = (!$final)? '[':']';
				elseif($print_name) $data = (!$final)? '"'.$name.'"'.':{':'}';
				elseif(!$print_name) $data = (!$final)? '{':'}';
			}
				
			return $data;
		}
		
		public function data_bottom()
		{
			if($this->format == 'rss') $bottom = '</channel></rss>';
				elseif($this->format == 'atom') $bottom = '</feed>';
					elseif($this->format == 'json') $bottom = ($this->callback)? ')':'';
						else $bottom = '';	
			return $bottom;
		}
}
?>