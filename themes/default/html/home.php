<?php
	
	$this->load_template('header.php');
	
?>
					<div id="home_content" class="publicindex" style="width:670px; margin-bottom:10px;">
						<div id="indexintro">
							<div id="indexintro2">
								<h1><?= $D->intro_ttl ?></h1>
								<p><?= $D->intro_txt ?></p>
								<a href="<?= $C->SITE_URL ?>signup" id="ïntrobtn"><b><?= $this->lang('os_welcome_btn') ?></b></a>
							</div>
						</div>
						<div class="ttl" style="margin-bottom:8px;">
<div class="ttl2"><h3><?= $this->lang('ttlads') ?></h3></div></div>
<!----- Random Advertise by Nipoto ---->
<script language="JavaScript">
function random_imglink(){
  var myimages=new Array()
  myimages[1]="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/ads/hosting-b.jpg"
  myimages[2]="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/ads/twitterish-b.jpg"
  var imagelinks=new Array()
  imagelinks[1]="http://hostronix.ir/"
  imagelinks[2]="http://sharetronix.ir/themes/"

  var ry=Math.floor(Math.random()*myimages.length)

  if (ry==0)
     ry=1
     document.write('<a href='+'"'+imagelinks[ry]+'"'+'><img src="'+myimages[ry]+'" border=0></a>')
}

  random_imglink()
</script>
<!----- Random Advertise by Nipoto ----><br>
						<div class="ttl" style="margin-bottom:8px;">
							<div class="ttl2">
								<h3><?= $this->lang('dbrd_poststitle_everybody', array('#USERNAME#'=>'', '#COMPANY#'=>htmlspecialchars($C->COMPANY))) ?></h3>
								<div id="postfilter">
									<a href="javascript:;" onclick="dropdiv_open('postfilteroptions');" id="postfilterselected" onfocus="this.blur();"><span><?= $this->lang('posts_filter_'.$D->filter) ?></span></a>
									<div id="postfilteroptions" style="display:none;">
										<a href="<?= $C->SITE_URL ?>home/filter:all"><?= $this->lang('posts_filter_all') ?></a>
										<a href="<?= $C->SITE_URL ?>home/filter:links"><?= $this->lang('posts_filter_links') ?></a>
										<a href="<?= $C->SITE_URL ?>home/filter:images"><?= $this->lang('posts_filter_images') ?></a>
										<a href="<?= $C->SITE_URL ?>home/filter:videos"><?= $this->lang('posts_filter_videos') ?></a>
										<a href="<?= $C->SITE_URL ?>home/filter:files" style="border-bottom:0px;"><?= $this->lang('posts_filter_files') ?></a>
									</div>
									<span><?= $this->lang('posts_filter_ttl') ?></span>
								</div>		
							</div>
						</div>
						<div id="posts_html">
							<?= $D->posts_html ?>
						</div>
					</div>
					<div id="home_right" style="width:250px;">
						
						<div id="login">
							<h3><?= $this->lang('os_login_ttl', array('#SITE_TITLE#'=>$C->SITE_TITLE)) ?></h3>
							<div id="loginbox">
								<form method="post" action="<?= $C->SITE_URL ?>signin">
									<small><?= $this->lang('os_login_unm') ?></small>
									<input type="text" name="email" value="" class="loginput" tabindex="1" />
									<small><?= $this->lang('os_login_pwd') ?></small>
									<input type="password" name="password" value="" class="loginput" tabindex="2" />
									<input type="submit" class="loginbtn" value="<?= $this->lang('os_login_btn') ?>" tabindex="4" />
									<label style="clear:none;">
										<input type="checkbox" name="rememberme" value="1" tabindex="3" />
										<span><?= $this->lang('os_login_rem') ?></span>
									</label>
									<div class="klear"></div>
								</form>
								<div id="loginlinks">
									<a href="<?= $C->SITE_URL ?>signup"><?= $this->lang('os_login_reg') ?></a>
									<a href="<?= $C->SITE_URL ?>signin/forgotten"><?= $this->lang('os_login_frg') ?></a>
								</div>
								<?php if( (isset($C->FACEBOOK_API_KEY) && !empty($C->FACEBOOK_API_KEY) || isset($C->TWITTER_CONSUMER_KEY,$C->TWITTER_CONSUMER_SECRET) && !empty($C->TWITTER_CONSUMER_KEY) && !empty($C->TWITTER_CONSUMER_SECRET) ) ) { ?>
								<div id="loginlinks" style="margin-top:5px; margin-bottom:2px; padding-top:5px;">
									<?php if( isset($C->FACEBOOK_API_KEY) && !empty($C->FACEBOOK_API_KEY) ) { ?>
									<div style="float:left; margin-right:5px;" title="Facebook Connect">
										<fb:login-button onlogin="hrd_func_fbconnected();"></fb:login-button>
									</div>
									<?php } ?>
									<?php if( isset($C->TWITTER_CONSUMER_KEY,$C->TWITTER_CONSUMER_SECRET) && !empty($C->TWITTER_CONSUMER_KEY) && !empty($C->TWITTER_CONSUMER_SECRET) ) { ?>
									<a id="twitterconnect" href="<?= $C->SITE_URL ?>twitter-connect?backto=<?= $C->SITE_URL ?>signin/get:twitter" title="Twitter Connect" style="margin-top:3px;"><b>Twitter</b></a>
									<?php } ?>
								</div>
								<?php } ?>
							</div>
							<div id="loginftr"></div>
						</div>
						<?php if( $C->MOBI_DISABLED==0 ) { ?>
						<div id="mobiad">
							<strong><?= $this->lang('dbrd_right_mobi_title', array('#SITE_TITLE#' => $C->OUTSIDE_SITE_TITLE) ) ?></strong>
							<?= $this->lang('dbrd_right_mobi_text') ?> <b><?= $C->SITE_URL ?>m</b>
						</div>
						<?php } ?>
<p class="style1" align="center"><a href="faq">
<img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/faq.jpg" style="border-width: 0"></a></p>

<p class="style1" align="center"><a href="rss/all:posts">
<img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/rss-feed.png" style="border-width: 0"></a></p>
						<?php if( count($D->last_online) > 0 ) { ?>
						<div class="ttl" style="margin-top:0px; margin-bottom:8px;"><div class="ttl2"><h3><?= $this->lang('dbrd_right_lastonline') ?></h3></div></div>
						<div class="slimusergroup" style="margin-right:1px; margin-left:3px; margin-bottom:5px;">
							<?php foreach($D->last_online as $u) { ?>
							<a href="<?= userlink($u->username) ?>" class="slimuser" title="<?= htmlspecialchars($u->username) ?>"><img src="<?= $C->IMG_URL ?>avatars/thumbs3/<?= $u->avatar ?>" alt="" style="padding:1px;" /></a>
							<?php } ?>
						</div>
						<?php } ?>
<div class="ttl" style="margin-top:0px; margin-bottom:8px;">
	<div class="ttl2"><h3><?= $this->lang('dbrd_topuser_title') ?></h3></div></div>
	<div class="slimusergroup" style="margin-right:1px; margin-left:3px; margin-bottom:5px;">
	<?php foreach($D->topten as $u) { ?>
	<a href="<?= userlink($u->username) ?>" class="slimuser" title="<?= htmlspecialchars($u->username) ?>"><img src="<?= $C->IMG_URL ?>avatars/thumbs3/<?= $u->avatar ?>" alt="" style="padding:1px;" /></a>
	<?php } ?>
	</div>
<?php { ?>
<div class="ttl" style="margin-top:0px; margin-bottom:8px;"><div class="ttl2"><h3><?= $this->lang('active_groups_block') ?></h3></div></div>
<div class="slimusergroup" style="margin-right:1px; margin-left:3px; margin-bottom:5px;"><?php foreach($D->grplar as $u) { ?>
<a href="<?= userlink($u->groupname) ?>" class="slimuser" title="<?= ($u->groupname) ?>">
<img src="<?= $C->IMG_URL ?>avatars/thumbs3/<?= $u->avatar ?>"  style="padding:1px;" /></a>
<?php } ?>
</div>
<?php } ?>
						<?php if( count($D->post_tags) > 0 ) { ?>
						<div class="ttl" style="margin-top:0px; margin-bottom:8px;"><div class="ttl2"><h3><?= $this->lang('dbrd_right_posttags') ?></h3></div></div>
						<div class="taglist" style="margin-bottom:5px;">
							<?php foreach($D->post_tags as $tmp) { ?>
							<a href="<?= $C->SITE_URL ?>search/posttag:%23<?= $tmp ?>" title="#<?= htmlspecialchars($tmp) ?>"><small>#</small><?= htmlspecialchars(str_cut($tmp,25)) ?></a>
							<?php } ?>
						</div>
						<?php } ?>
					</div>
<?php
	
	$this->load_template('footer.php');
	
?>