<?php
	
	$this->load_langfile('mobile/footer.php');
	
?>
        <nav id="hm-menu">
            <ul>
                <li><a href="<?= $C->SITE_URL ?>panel"><i class="fa fa-home"></i>صفحه اصلی</a></li>
                <li><a href="<?= $C->SITE_URL ?>dashboard"><i class="fa fa-pencil"></i>داشبورد</a>
                </li>
                <li><a href="<?= $C->SITE_URL ?>post"><i class="fa fa-send"></i>ارسال پست</a></li>
                <li><a href="<?= $C->SITE_URL ?>search"><i class="fa fa-search"></i>جستجو</a></li>
                <li><a href="<?= $C->SITE_URL ?>members"><i class="fa fa-user"></i>کاربران</a></li>
                <li><a href="<?= $C->SITE_URL ?>groups"><i class="fa fa-group"></i>گروه ها</a></li>
                <li><a href="<?= $C->SITE_URL ?>avatar"><i class="fa fa-cogs"></i>تنظیمات</a></li>
                <li><a href="<?= $C->SITE_URL ?>signout"><i class="fa fa-sign-out"></i> خروج</a></li>
          </ul>
        </nav>
		<?php if( $this->user->is_logged ) { ?>
		<div id="menu_container" style="display:none;">
			<div id="blackoverlay"></div>
			<div id="menu">
				<div id="menu2">
					<div id="menu3">
						<a href="<?= $C->SITE_URL ?>dashboard" class="menuitem_activity"><div><b><?= $this->lang('iphone_nav_dashboard') ?></b></div></a>
						<a href="<?= $C->SITE_URL ?>post" class="menuitem_newpost"><div><b><?= $this->lang('iphone_nav_newpost') ?></b></div></a>
						<a href="<?= $C->SITE_URL ?>search" class="menuitem_search"><div><b><?= $this->lang('iphone_nav_search') ?></b></div></a>
						<a href="<?= $C->SITE_URL ?>signout" class="menuitem_logout"><div><b><?= $this->lang('iphone_nav_signout') ?></b></div></a>
						<a href="<?= $C->SITE_URL ?>groups" class="menuitem_groups"><div><b><?= $this->lang('iphone_nav_groups') ?></b></div></a>
						<a href="<?= $C->SITE_URL ?>members" class="menuitem_colleagues"><div><b><?= $this->lang('iphone_nav_members') ?></b></div></a>
					</div>
				</div>
			</div>
		</div>
		<?php } ?>
		
		<div id="ftr">
			&copy; <a href="<?= $C->SITE_URL ?>"><?= $C->SITE_TITLE ?></a>
		</div>
		
		<?php if( isset($_COOKIE['mobitouch']) && $_COOKIE['mobitouch']==1 ) { ?>
		<script type="text/javascript"> footer_simpleversion_link("<?= $this->lang('footer_mobi_simple') ?>"); </script>
		<?php } ?>
		
	</body>
</html>