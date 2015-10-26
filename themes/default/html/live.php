<?php
		
	$this->load_template('header.php');
	
?>	
		<div id="pagebody" style="margin:0px; border-top:1px solid #fff;"></div>					<div id="home_content" class="publicindex" style="width:670px;">
						<div id="posts_html">
							<?= $D->posts_html ?>
						</div></div>


	<div id="home_right">


						<?php { ?>
						<div class="ttl" style="margin-top:0px; margin-bottom:8px;"><div class="ttl2"><h3><?= $this->lang('block_title') ?></h3></div></div>
						<div class="mobiad" style="margin-right:5px; margin-left:5px; margin-bottom:5px; text-align:justify;"><?= $this->lang('block_text') ?><br><br>
						</div>
						<?php } ?>


						<?php if( $C->MOBI_DISABLED==0 ) { ?>
						<div id="mobiad">
							<strong><?= $this->lang('dbrd_right_mobi_title', array('#SITE_TITLE#' => $C->OUTSIDE_SITE_TITLE) ) ?></strong>
							<?= $this->lang('dbrd_right_mobi_text') ?> <b><?= $C->SITE_URL ?>m</b>
						</div>
						<?php } ?>

<p class="style1" align="center"><a href="invite/parsemail">
<img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/invite.jpg" style="border-width: 0"></a></p>

<p class="style1" align="center"><a href="faq">
<img src="<?= $C->SITE_URL.'themes/'.$C->THEME ?>/imgs/faq.jpg" style="border-width: 0"></a></p>

						<?php if( count($D->last_online) > 0 ) { ?>
						<div class="ttl" style="margin-top:0px; margin-bottom:8px;"><div class="ttl2"><h3><?= $this->lang('dbrd_right_lastonline') ?></h3></div></div>
						<div class="slimusergroup" style="margin-right:1px; margin-left:3px; margin-bottom:5px;">
							<?php foreach($D->last_online as $u) { ?>
							<a href="<?= userlink($u->username) ?>" class="slimuser" title="<?= htmlspecialchars($u->username) ?>"><img src="<?= $C->IMG_URL ?>avatars/thumbs3/<?= $u->avatar ?>" alt="" style="padding:1px;" /></a>
							<?php } ?>
						</div>
						<?php } ?>
</div>


<?php
	
	$this->load_template('footer.php');
	
?>
						