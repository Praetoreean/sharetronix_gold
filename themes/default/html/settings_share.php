<?php

    $this->load_template('header.php');

?>
    <div id="settings">
        <div id="settings_left">
            <?php $this->load_template('settings_leftmenu.php') ?>
        </div>
        <div id="settings_right">
            <div class="ttl"><div class="ttl2"><h3>دکمه های اشتراک</h3></div></div>
            <p>از طریق کد های زیر و با قراردادن در جای مناسب شما خواهید توانست دکمه ای در بلاگ خود تعبیه کنید تا کاربران به راحتی مطالب را در شبکه منتشر نمایند</p>
            <div>
                <p>کاربران بلاگفا</p>
                <textarea><a href="<?= $C->SITE_URL ?>share?title=<-PostTitle->&url=<-BlogUrl->/<-PostLink->">اشتراک در شبکه</a></textarea>

            </div>
        </div>
    </div>
<?php

    $this->load_template('footer.php');

?>