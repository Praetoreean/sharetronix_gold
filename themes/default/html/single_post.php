		<?php if( $D->p->error ) { ?>
		
		<?php } elseif( $D->p->is_system_post ) { ?>
		
			<div class="mpost" id="post_<?= $D->p->post_tmp_id ?>" postdate="<?= $D->p->post_date ?>" style="display:<?= isset($D->post_show_slow)&&$D->post_show_slow?'none':'block' ?>;">
				<div class="mpost2">
					<?php if( $D->p->if_can_delete() ) { ?>
					<a href="javascript:;" id="postlink_del_<?= $D->p->post_tmp_id ?>" class="mpostclose" title="<?= $this->lang('post_delete_link') ?>" onfocus="this.blur();" onclick="post_delete('<?= $D->p->post_tmp_id ?>');"></a>
					<?php } ?>
					<?= $D->p->parse_text() ?>
					<small><?= post::parse_date($D->p->post_date) ?></small>
				</div>
			</div>
			
		<?php } else { ?>
			<?php
				$show_reshared_design	= FALSE;
				if(isset($D->show_in_updates) && in_array($D->p->post_id, $D->show_in_updates)) $show_reshared_design = TRUE;
				else{
					if( $this->user->is_logged && $D->p->post_resharesnum>0 ) {
						$reshared_by	= array_keys($D->p->post_reshares);
						$i_follow		= array_keys($this->network->get_user_follows($this->user->id)->follow_users);
						$i_follow[]		= intval($this->user->id);
						if( count(array_intersect($reshared_by,$i_follow))>0 ) {
							$show_reshared_design	= TRUE;
						}
					}
				}
			?>
			<?php if( ! $show_reshared_design ) { ?>
				<div class="post" id="post_<?= $D->p->post_tmp_id ?>" postdate="<?= $D->p->post_date ?>" style="overflow:visible; display:<?= isset($D->post_show_slow)&&$D->post_show_slow?'none':'block' ?>;">
			<?php } else { ?>
				<div class="post reposted" id="post_<?= $D->p->post_tmp_id ?>" postdate="<?= $D->p->post_date ?>" style="overflow:visible; display:<?= isset($D->post_show_slow)&&$D->post_show_slow?'none':'block' ?>;">
				<div class="repostedbody"><div class="repostedbody2">
			<?php } ?>
			
				<?php if( $D->p->post_api_id == 2 ) { ?>
					<?php if( $D->p->post_user->id==0 && $D->p->post_group ) { ?>
						<div class="postavatar_rss" style="background-image:url('<?= $C->IMG_URL.'avatars/thumbs1/'.$D->p->post_group->avatar ?>');"><a href="<?= userlink($D->p->post_group->groupname) ?>" title="<?= htmlspecialchars($D->p->post_group->title) ?>"></a></div>
					<?php } else { ?>
						<div class="postavatar_rss" style="background-image:url('<?= $C->IMG_URL.'avatars/thumbs1/'.$D->p->post_user->avatar ?>');"><a href="<?= userlink($D->p->post_user->username) ?>" title="<?= htmlspecialchars($D->p->post_user->fullname) ?>"></a></div>
					<?php } ?>
				<?php } else { ?>
					<?php if( (!$D->p->post_user || $D->p->post_user->id==0) && $D->p->post_group ) { ?>
						<a href="<?= userlink($D->p->post_group->groupname) ?>" class="postavatar" title="<?= htmlspecialchars($D->p->post_group->title) ?>"><img src="<?= $C->IMG_URL ?>avatars/thumbs1/<?= $D->p->post_group->avatar ?>" alt="<?= htmlspecialchars($D->p->post_group->title) ?>" /></a>
					<?php } else { ?>
						<?php if( $this->user->is_logged && $D->p->post_user && $this->user->id!=$D->p->post_user->id ) { ?>
						<a href="<?= userlink($D->p->post_user->username) ?>" id="postuserbox_av1_<?= $D->p->post_tmp_id ?>" onmouseover="postuserbox_open('<?= $D->p->post_tmp_id ?>');" class="postavatar" title="<?= htmlspecialchars($D->p->post_user->fullname) ?>"><img src="<?= $C->IMG_URL ?>avatars/thumbs1/<?= $D->p->post_user->avatar ?>" alt="<?= htmlspecialchars($D->p->post_user->fullname) ?>" /></a>
						<div class="pavatar" onmousemove="postuserbox_keepopen();" id="postuserbox_av2_<?= $D->p->post_tmp_id ?>" onmouseout="postuserbox_close_ev('<?= $D->p->post_tmp_id ?>');" style="display:none;">
							<div class="pavatarbox">
								<div class="pavatarbox2">
									<div class="pavatarbox3">
										<a href="<?= userlink($D->p->post_user->username) ?>" title="<?= htmlspecialchars($D->p->post_user->fullname) ?>" class="pab_avatar"><img src="<?= $C->IMG_URL ?>avatars/thumbs1/<?= $D->p->post_user->avatar ?>" alt="<?= htmlspecialchars($D->p->post_user->fullname) ?>" /></a>
										<a href="<?= userlink($D->p->post_user->username) ?>" class="pab_username"><?= htmlspecialchars($D->p->post_user->username) ?></a>
										<div class="pab_userinfo"><?= htmlspecialchars($D->p->post_user->fullname) ?></div>
										<a href="javascript:;" id="pubx_f_<?= $D->p->post_tmp_id ?>" style="display:<?= $this->user->if_follow_user($D->p->post_user->id)?'none':'block' ?>;" onclick="user_follow('<?= $D->p->post_user->username ?>', this, 'pubx_unf_<?= $D->p->post_tmp_id ?>', false);" onfocus="this.blur();" class="pab_follow"><b><?= $this->lang('postubox_follow') ?></b></a>
										<span id="pubx_unf_<?= $D->p->post_tmp_id ?>" style="display:<?= $this->user->if_follow_user($D->p->post_user->id)?'block':'none' ?>;">
											<div class="pab_following"><?= $this->lang('postubox_following') ?></div>
											<a href="javascript:;" onclick="user_unfollow('<?= $D->p->post_user->username ?>', this.parentNode, 'pubx_f_<?= $D->p->post_tmp_id ?>', '<?= $this->lang('user_unfollow_confirm',array('#USERNAME#'=>$D->p->post_user->username)) ?>', false);" onfocus="this.blur();" class="pab_stopfollow"><b><?= $this->lang('postubox_unfollow') ?></b></a>
										</span>
									</div>
									<?php if( !empty($D->p->post_user->about_me) || !empty($D->p->post_user->location) || ($D->p->post_user->user_details&&!empty($D->p->post_user->user_details->website)) ) { ?>
									<div class="pavatar_contacts">
										<div class="pavatar_contacts2" style="padding:5px;">
											<table cellpadding="0" cellspacing="3">
												<?php if( !empty($D->p->post_user->about_me) ) { ?>
												<tr>
													<td class="contactparam"><img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/cicons_username.gif" alt="" /></td>
													<td class="contactvalue"><?= htmlspecialchars($D->p->post_user->about_me) ?></td>
												</tr>
												<?php } ?>
												<?php if( !empty($D->p->post_user->location) ) { ?>
												<tr>
													<td class="contactparam"><img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/cicons_location.gif" alt="" /></td>
													<td class="contactvalue"><?= htmlspecialchars($D->p->post_user->location) ?></td>
												</tr>
												<?php } ?>
												<?php if( $D->p->post_user->user_details && !empty($D->p->post_user->user_details->website) ) { ?>
												<tr>
													<td class="contactparam"><img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/cicons_site.gif" alt="" /></td>
													<td class="contactvalue"><a href="<?= htmlspecialchars($D->p->post_user->user_details->website) ?>" title="<?= htmlspecialchars($D->p->post_user->user_details->website) ?>" target="_blank"><?= htmlspecialchars(str_cut(preg_replace('/^(http(s)?|ftp)\:\/\/(www\.)?/','',$D->p->post_user->user_details->website),25)) ?></a></td>
												</tr>
												<?php } ?>
											</table>			
										</div>
									</div>
									<?php } ?>
								</div>
								<a href="javascript:;" onclick="postform_open(({username:'<?= $D->p->post_user->username ?>'}));" onfocus="this.blur();" class="pab_btn_pm"><?= $this->lang('postubox_privmsg', array('#USERNAME#'=>$D->p->post_user->username)) ?></a>
								<a href="javascript:;" onclick="postform_mention('<?= $D->p->post_user->username ?>',true);" onfocus="this.blur();" class="pab_btn_at"><?= $this->lang('postubox_mention', array('#USERNAME#'=>$D->p->post_user->username)) ?></a>
							</div>
						</div>
						<?php } else { ?>
						<a href="<?= userlink($D->p->post_user->username) ?>" class="postavatar" title="<?= htmlspecialchars($D->p->post_user->fullname) ?>"><img src="<?= $C->IMG_URL ?>avatars/thumbs1/<?= $D->p->post_user->avatar ?>" alt="<?= htmlspecialchars($D->p->post_user->fullname) ?>" /></a>
						<?php } ?>
					<?php } ?>
				<?php } ?>
				<div class="postcontrols">
					<?php if( ! $this->user->is_logged ) { ?>
						<a href="<?= $C->SITE_URL ?>signin" class="pfave" title="<?= $this->lang('post_fave_link') ?>" onfocus="this.blur();"></a>
					<?php } else { ?>
						<?php if( $D->p->if_can_edit() || $D->p->if_can_delete() || $D->p->if_can_reshare() || $D->p->post_type=='public' || !$D->p->post_group || $D->p->post_group->is_public ) { ?>
							<a href="javascript:;" onclick="dropcontrols_open('<?= $D->p->post_tmp_id ?>');" onfocus="this.blur();" class="dropcontrols" id="dropcontrols_link_<?= $D->p->post_tmp_id ?>"></a>
							<div class="pctrls" style="display:none;" id="dropcontrols_box_<?= $D->p->post_tmp_id ?>">
								<?php if( $D->p->if_can_edit() ) { ?>
									<a href="javascript:;" class="pctrls_edit" title="<?= $this->lang('post_edit_link') ?>" onfocus="this.blur();" onclick="postform_open(({editpost:'<?= $D->p->post_tmp_id ?>'}));"><?= $this->lang('posteditlink') ?></a>
								<?php } ?>
								<?php if( $D->p->if_can_delete() ) { ?>
									<a href="javascript:;" id="postlink_del_<?= $D->p->post_tmp_id ?>" class="pctrls_delete" title="<?= $this->lang('post_delete_link') ?>" onfocus="this.blur();" onclick="post_delete('<?= $D->p->post_tmp_id ?>', '<?= $this->lang('post_delete_confirm') ?>');"><?= $this->lang('postdeletelink') ?></a>
								<?php } ?>
<?php { ?>
<a href="javascript:NewWindow ('police/','Police','400','350','center','front');" class="pctrls_report" title="<?= $this->lang('postreporttip') ?>" onfocus="this.blur();" , '<?= $this->lang('postreport') ?>');"><?= $this->lang('postreport') ?></a>
<?php } ?>
								<?php if( $D->p->if_can_reshare() ) { ?>
									<a href="javascript:;" class="pctrls_repost" onfocus="this.blur();" onclick="reshare_post('<?= $D->p->post_id ?>', '<?= $this->lang('reshare_confirm') ?>', '<?= $this->lang('reshare_done') ?>');"><?= $this->lang('postresharelink') ?></a>
								<?php } ?>
								<?php if( $D->p->post_type=='public' && (!$D->p->post_group || $D->p->post_group->is_public) ) { ?>
								<div class="pctrls_sharediv">
									<a href="javascript:;" onmouseover="dropcontrols_share_open('<?= $D->p->post_tmp_id ?>');" onclick="dropcontrols_open('<?= $D->p->post_tmp_id ?>'); dropcontrols_share_open('<?= $D->p->post_tmp_id ?>');" class="pctrls_share" id="dropcontrols_sharelink_<?= $D->p->post_tmp_id ?>"><b><?= $this->lang('postsharelink') ?></b></a>
									<div class="pctrls_share_services" onmouseout="dropcontrols_share_close_ev('<?= $D->p->post_tmp_id ?>');" style="display:none;" id="dropcontrols_sharebox_<?= $D->p->post_tmp_id ?>">
										<?= $D->p->show_share_link() ?>
									</div>
								</div>
								<?php } ?>
							</div>
						<?php } ?>
						<a href="javascript:;" id="postlink_fave_<?= $D->p->post_tmp_id ?>" class="pfave" title="<?= $this->lang('post_fave_link') ?>" style="<?= $D->p->is_post_faved()?'display:none;':'display:block;' ?>" onfocus="this.blur();" onclick="post_fave('<?= $D->p->post_tmp_id ?>');"></a>
						<a href="javascript:;" id="postlink_unfave_<?= $D->p->post_tmp_id ?>" class="pfave saved" title="<?= $this->lang('post_unfave_link') ?>" style="<?= $D->p->is_post_faved()?'display:block;':'display:none;' ?>" onfocus="this.blur();" onclick="post_unfave('<?= $D->p->post_tmp_id ?>','<?= $this->lang('post_unfave_confirm') ?>',<?= $this->request[0]=='dashboard'&&isset($D->tab)&&$D->tab=='bookmarks' ? 'true' :'false' ?>);"></a>
					<?php } ?>
				</div>
				<div class="postbody">
					<?php if( isset($D->p->post_attached['image']) ) { ?>
					<a href="<?= $D->p->permalink ?>" onclick="flybox_open_att_image(<?= $D->p->post_attached['image']->size_preview[0] ?>, <?= $D->p->post_attached['image']->size_preview[1] ?>, '<?= $this->lang('post_atchimg_title') ?>', '<?= $D->p->post_tmp_id ?>'); return false;" class="postimage"><img src="<?= $C->IMG_URL ?>attachments/<?= $this->network->id ?>/<?= $D->p->post_attached['image']->file_thumbnail ?>" alt="<?= htmlspecialchars($D->p->post_attached['image']->title) ?>" /></a>
					<?php } ?>
					<?php if( isset($D->p->post_attached['videoembed']) ) { ?>
					<div class="postvideo" style="background-image:url('<?= $C->IMG_URL ?>attachments/<?= $this->network->id ?>/<?= $D->p->post_attached['videoembed']->file_thumbnail ?>');"><a href="<?= $D->p->permalink ?>" onclick="flybox_open_att_videoembed(<?= $D->p->post_attached['videoembed']->embed_w ?>, <?= $D->p->post_attached['videoembed']->embed_h ?>, '<?= $this->lang('post_atchvid_title') ?>', '<?= $D->p->post_tmp_id ?>'); return false;" title="<?= htmlspecialchars($D->p->post_attached['videoembed']->orig_url) ?>"></a></div>
					<?php } ?>
					<?php if( (!$D->p->post_user || $D->p->post_user->id==0) && $D->p->post_group ) { ?>
					<a href="<?= userlink($D->p->post_group->groupname) ?>" class="postusername" title="<?= htmlspecialchars($D->p->post_group->title) ?>"><?= htmlspecialchars($D->p->post_group->title) ?></a>
					<?php } elseif( $D->p->post_type == 'private' ) { ?>
					<a href="<?= userlink($D->p->post_user->username) ?>" class="postusername" title="<?= htmlspecialchars($D->p->post_user->fullname) ?>"><?= $D->p->post_user->fullname ?></a>
					<img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/fromto.gif" class="post_fromto" alt="&raquo;" />
					<a href="<?= userlink($D->p->post_to_user->username) ?>" class="postusername" title="<?= htmlspecialchars($D->p->post_to_user->fullname) ?>"><?= $D->p->post_to_user->username ?></a>
					<?php } elseif( $D->p->post_type == 'public' && $D->p->post_user->id != $this->user->id ) { ?>
					<a href="<?= userlink($D->p->post_user->username) ?>" class="postusername" title="<?= htmlspecialchars($D->p->post_user->fullname) ?>" onmouseover="show_post_topbtns('<?= $D->p->post_tmp_id ?>');" onmouseout="hide_post_topbtns('<?= $D->p->post_tmp_id ?>');"><?= $D->p->post_user->fullname ?></a>
					<div class="postusericons" id="post_btns_top_<?= $D->p->post_tmp_id ?>" onmouseover="show_post_topbtns('<?= $D->p->post_tmp_id ?>');" onmouseout="hide_post_topbtns('<?= $D->p->post_tmp_id ?>');" style="display:none;">
						<?php if( $this->user->is_logged ) { ?>
						<a href="javascript:;" onmouseover="this.parentNode.className='postusericons vsbl1';" onmouseout="this.parentNode.className='postusericons';" onclick="postform_mention('<?= $D->p->post_user->username ?>',true);" onfocus="this.blur();" class="pui_atuser"></a>
						<a href="javascript:;" onmouseover="this.parentNode.className='postusericons vsbl2';" onmouseout="this.parentNode.className='postusericons';" onclick="postform_open(({username:'<?= $D->p->post_user->username ?>'}));" onfocus="this.blur();" class="pui_pm"></a>
						<?php if($D->p->post_user->id!=$this->user->id && !$this->user->if_follow_user($D->p->post_user->id)) { ?>
						<a href="javascript:;" onmouseover="this.parentNode.className='postusericons vsbl3';" onmouseout="this.parentNode.className='postusericons';" onclick="user_follow('<?= $D->p->post_user->username ?>',this,false,'<?= addslashes($this->lang('msg_follow_user_on',array('#USERNAME#'=>$D->p->post_user->username))) ?>');" onfocus="this.blur();" class="pui_follow"></a>
						<?php } ?>
						<b class="puicn_mention"><?= $this->lang('post_usricon_mention',array('#USERNAME#'=>$D->p->post_user->username)) ?></b>
						<b class="puicn_private"><?= $this->lang('post_usricon_private',array('#USERNAME#'=>$D->p->post_user->username)) ?></b>
						<b class="puicn_follow"><?= $this->lang('post_usricon_follow',array('#USERNAME#'=>$D->p->post_user->username)) ?></b>
						<?php } ?>
					</div>
					<?php } elseif( $D->p->post_type == 'public' ) { ?>
					<a href="<?= userlink($D->p->post_user->username) ?>" class="postusername" title="<?= htmlspecialchars($D->p->post_user->fullname) ?>"><?= $D->p->post_user->fullname ?></a>
					<?php } ?>
					<div class="posttext">
	<?= $D->p->parse_text() ?><?php if( isset($D->p->post_attached['link']) ) { ?>
	<a href="<?= htmlspecialchars($D->p->post_attached['link']->link) ?>" title="<?= htmlspecialchars($D->p->post_attached['link']->link) ?>" target="_blank" rel="nofollow">[<?= $this->lang('pf_attachtab_link') ?>]</a>
					<?php } ?>
	<?php if( isset($D->p->post_attached['file']) ) { ?>
	<a href="<?= $C->SITE_URL ?>getfile/pid:<?= $D->p->post_tmp_id ?>/<?= htmlspecialchars($D->p->post_attached['file']->title) ?>" title="Name: <?= htmlspecialchars($D->p->post_attached['file']->title) ?> / Size: <?= show_filesize($D->p->post_attached['file']->filesize) ?>">[<?= $this->lang('pf_attachtab_file') ?>]</a>
					<?php } ?></div>
					<div class="postftr">
						<?php if( $D->p->post_commentsnum == 0 ) { ?>
							<?php if( $this->user->is_logged ) { ?>
							<a href="javascript:;" onclick="postcomments_open('<?= $D->p->post_tmp_id ?>');" onfocus="this.blur();"><?= $this->lang('post_opncomments_0') ?></a>
							<?php } else { ?>
							<a href="<?= $C->SITE_URL ?>signin"onfocus="this.blur();"><?= $this->lang('post_opncomments_0') ?></a>
							<?php } ?>
						<?php } else { ?>
						<a href="javascript:;" onclick="postcomments_open('<?= $D->p->post_tmp_id ?>');" class="commentlink" onfocus="this.blur();"><?= $this->lang($D->p->post_commentsnum==1?'post_opncomments_1':'post_opncomments_more', array('#NUM#'=>$D->p->post_commentsnum)) ?></a>
						<?php } ?>
						<span class="newcomments" id="post_newcomments_<?= $D->p->post_tmp_id ?>" style="<?= $D->p->if_new_comments()==0?'display:none;':'' ?>"><b><?= $this->lang($D->p->if_new_comments()==1?'post_newcomments_1':'post_newcomments_more', array('#NUM#'=>$D->p->if_new_comments())) ?></b></span>
						&middot;
						<a title="<?= $this->lang('post_atchftr_permalink') ?>" href="<?= $D->p->permalink ?>"><?= post::parse_date($D->p->post_date) ?></a>
						<?= $D->p->parse_group($D->parsedpost_attlink_maxlen/2) ?>
						<?= post::parse_api($D->p->post_api_id) ?>
						<?php if( $D->p->post_type=='public' && $D->p->post_api_id!=2 && $D->p->post_api_id!=6 && 
							(!$D->p->post_group || $D->p->post_group->is_public) ) { ?>

&middot;<?php if( $this->user->is_logged ) { ?>
			<?php if($D->p->is_post_disliked()){ ?>
                                                    <a href="javascript:;" id="postlink_like_<?= $D->p->post_tmp_id ?>" onfocus="this.blur();" onclick="post_like('<?= $D->p->post_tmp_id ?>');"><img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/add.png" style="border-width: 0" /></a>
						<? }else if($D->p->is_post_liked()){ ?>
                                                    <a href="javascript:;" id="postlink_dislike_<?= $D->p->post_tmp_id ?>" onfocus="this.blur();" onclick="post_dislike('<?= $D->p->post_tmp_id ?>');"><img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/delete.png" style="border-width: 0" /></a>
                                                <?php }else{ ?>
                                                    <a href="javascript:;" id="postlink_like_<?= $D->p->post_tmp_id ?>" onfocus="this.blur();" onclick="post_like('<?= $D->p->post_tmp_id ?>');"><img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/add.png" style="border-width: 0" /></a>
                                                    <a href="javascript:;" id="postlink_dislike_<?= $D->p->post_tmp_id ?>" onfocus="this.blur();" onclick="post_dislike('<?= $D->p->post_tmp_id ?>');"><img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/delete.png" style="border-width: 0" /></a>
                                                    <?php } ?>	<?php } ?>
                                                <?php if($D->p->post_likesnum > 0 || $D->p->post_dislikesnum > 0){ ?>
                                                    <?php echo $D->p->post_likesnum.' '.$this->lang('postlike') ?>
                                                    /
                                                    <?php echo $D->p->post_dislikesnum.' '.$this->lang('postdislike') ?>
                                                <?php } ?>
						<?php } ?>
					</div>
				</div>
				<?php
					$comments_open	= isset($D->p->comments_open) && $D->p->comments_open;
					$comments_closebtn	= TRUE;
				?>
				<div class="postcomments" id="postcomments_<?= $D->p->post_tmp_id ?>" style="display:<?= $comments_open ? 'block' : 'none' ?>;">
					<?php if( $D->p->post_commentsnum == 0 ) { ?>
					<div class="slimpostcommentshdr"><div class="slimpostcommentshdr2"></div></div>
					<div class="postcommentsftr">
						<div class="postcommentsftr2" style="padding-top:2px;">
							<?php if( $this->user->is_logged ) { ?>
	 						<div class="addpc_big">
								<img src="<?= $C->IMG_URL.'avatars/thumbs3/'.$this->user->info->avatar ?>" class="addpc_avatar" alt="" />
								<div class="addpc_right">
									<textarea id="postcomments_<?= $D->p->post_tmp_id ?>_textarea" onkeyup="textarea_autoheight(this);" onkeypress="not_enter_valid($(this));"></textarea>
									<input id="postcomments_<?= $D->p->post_tmp_id ?>_submitbtn" onclick="postcomments_submit('<?= $D->p->post_tmp_id ?>');" type="submit" value="<?= $this->lang('post_comments_submit') ?>" /> <?= $this->lang('post_comments_sbmor') ?>
									<a href="javascript:;" onclick="postcomments_close('<?= $D->p->post_tmp_id ?>');" onfocus="this.blur();"><?= $this->lang('post_comments_sbmcncl') ?></a>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<?php } else { ?>
					<div class="postcommentshdr">
						<div class="postcommentshdr2">
							<b><?= $this->lang( $D->p->post_commentsnum==0?'post_viwcomments_0':($D->p->post_commentsnum==1?'post_viwcomments_1':($D->p->post_commentsnum<=$C->POST_LAST_COMMENTS?'post_viwcomments_all':'post_viwcomments_last')), array('#NUM#'=>min($C->POST_LAST_COMMENTS,$D->p->post_commentsnum) ) ) ?></b>
							<?php if( $D->p->post_commentsnum > $C->POST_LAST_COMMENTS ) { ?>
							&middot;
							<a href="<?= $D->p->permalink ?>#comments"><?= $this->lang('post_viwcomments_link') ?></a>
							<?php } ?>
							<?php if( $comments_closebtn ) { ?>
							<a href="javascript:;" onclick="postcomments_close('<?= $D->p->post_tmp_id ?>');" onfocus="this.blur();" class="closecomments"></a>
							<?php } ?>
						</div>
					</div>
					<div class="postcommentscontent">
						<?php $i=0; foreach($D->p->get_last_comments() as $c) { ?>
						<div class="comment<?= $i==0?' firstcomment':'' ?>" id="postcomment_<?= $c->comment_id ?>">
							<a href="<?= userlink($c->comment_user->username) ?>" class="commentavatar" title="<?= htmlspecialchars($c->comment_user->fullname) ?>"><img src="<?= $C->IMG_URL.'avatars/thumbs3/'.$c->comment_user->avatar ?>" alt="" /></a>
							<div class="comment_right">
								<a href="<?= userlink($c->comment_user->username) ?>" class="commentname" title="<?= htmlspecialchars($c->comment_user->fullname) ?>"><?= $c->comment_user->fullname ?></a>
								<p><?= nl2br($c->parse_text()) ?></p>
								<?= post::parse_date($c->comment_date) ?>
								<?= post::parse_api($c->comment_api_id) ?>
								<?php if( $c->if_can_delete() ) { ?>
								&middot; <a onclick="postcomment_delete('<?= $c->post->post_tmp_id ?>', <?= $c->comment_id ?>, '<?= $this->lang('post_delcomment_cnfrm') ?>');" href="javascript:;" onfocus="this.blur();" class="smalllink"><?= $this->lang('post_delcomment_lnk') ?></a>
								<?php } ?>
							</div>
						</div>
						<?php $i++; } ?>
					</div>
					<div class="postcommentsftr">
						<div class="postcommentsftr2">
							<?php if( $this->user->is_logged ) { ?>
							<div class="addpc_slim" id="postcomments_<?= $D->p->post_tmp_id ?>_slimform" style="">
								<input type="text" value="<?= $this->lang('post_comments_expand') ?>" onfocus="postcomments_expand('<?= $D->p->post_tmp_id ?>')" />
							</div>
		 					<div class="addpc_big" id="postcomments_<?= $D->p->post_tmp_id ?>_bigform" style="display:none;">
								<img src="<?= $C->IMG_URL.'avatars/thumbs3/'.$this->user->info->avatar ?>" class="addpc_avatar" alt="" />
								<div class="addpc_right">
									<textarea id="postcomments_<?= $D->p->post_tmp_id ?>_textarea" onkeyup="textarea_autoheight(this);" onkeypress="not_enter_valid($(this));"></textarea>
									<input id="postcomments_<?= $D->p->post_tmp_id ?>_submitbtn" onclick="postcomments_submit('<?= $D->p->post_tmp_id ?>');" type="submit" value="<?= $this->lang('post_comments_submit') ?>" /> <?= $this->lang('post_comments_sbmor') ?>
									<a href="javascript:;" onclick="postcomments_collapse('<?= $D->p->post_tmp_id ?>');" onfocus="this.blur();"><?= $this->lang('post_comments_sbmcncl') ?></a>
								</div>
							</div>
							<?php } ?>
						</div>
					</div>
					<?php } ?>
				</div>
				
				<?php if( $show_reshared_design ) { ?>
					<div class="repostedftr">
						<div class="repostedftr2">
							<?= $D->p->parse_reshares(); ?>
							<div class="rpftrchovka"></div>
						</div>
					</div>
				</div></div>
				<?php } ?>
				
				<div class="klear"></div>
			</div>
		<?php } ?>