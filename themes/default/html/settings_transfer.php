<?php

    $this->load_template('header.php');

?>
    <div id="settings">
        <div id="settings_left">
            <?php $this->load_template('settings_leftmenu.php') ?>
        </div>
        <div id="settings_right">
            <div class="ttl">
                <div class="ttl2"><h3><?= $this->lang('settings_transfer_pagetitle') ?></h3></div>
            </div>
            <?php if($D->submit && !$D->error){ ?>

                <?= okbox($this->lang('settings_transfer_ok_title'), $this->lang('settings_transfer_ok_text'), FALSE, 'margin-top:5px;') ?>

            <?php } ?>
            <?php if($D->error){ ?>

                <?= errorbox($this->lang('settings_transfer_error_title'), $D->errmsg, TRUE, 'margin-top:5px;') ?>

            <?php } ?>
            <br/>

            <div class="greygrad">
                <div class="greygrad2">
                    <div class="greygrad3">
                        <p><?= $this->lang('settings_transfer_how_much_now') ?><?= $this->user->info->rate ?></p>
                    </div>
                </div>
            </div>
            <div class="greygrad">
                <div class="greygrad2">
                    <div class="greygrad3">
                        <form method="post" action="<?= $C->SITE_URL ?>settings/transfer">
                            <table id="setform" cellspacing="5">
                                <tr>
                                    <td class="setparam">
                                        <?= $this->lang('settings_transfer_how_much') ?>
                                    </td>
                                    <td>
                                        <input type="number" name="rate_much" id="rate_much" class="setinp">
                                    </td>
                                </tr>
                                <tr>
                                    <td class="setparam">
                                        <?= $this->lang('settings_transfer_who') ?>
                                    </td>
                                    <td>
                                        <input rel="autocomplete" autocompleteoffset="0,3" type="text" name="username" id="who_user" class="setinp">
                                    </td>
                                </tr>
                                <tr>
                                    <td></td>
                                    <td>
                                        <input type="submit" name="sbm" value="<?= $this->lang('st_transfer_cng_btn') ?>" style="padding:4px; font-weight:bold;"/>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
<?php

    $this->load_template('footer.php');

?>