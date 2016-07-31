<?php
    $this->load_template('header.php');
?>
<div class="greygrad">
    <div class="greygrad2">
        <div class="greygrad3">
            <?php if($D->is_complete == false) { ?>

                <?= errorbox("خطا","اطلاعات به درستی دریافت نگردید، مجددا امتحان کنید") ?>

            <?php }else{ ?>
                <?php if($D->error == true) { ?>
                    <?= errorbox("خطا","مشکلی در ثبت اطلاعات پیش امد مجددا تلاش کنید") ?>
                <?php } ?>


                <h3>اشتراک گذاری مطالب از سراسر نت</h3>
                <form action="<?= $C->SITE_URL ?>share" method="post">
                    <table id="setform" cellspacing="5">
                        <tr>
                            <td>عنوان مطلب</td>
                            <td><input type="text" class="setinp" name="title" value="<?= $D->title ?>"></td>
                        </tr>
                        <tr>
                            <td>لینک مطلب</td>
                            <td><input type="text" class="setinp" name="url" value="<?= $D->url ?>"></td>
                        </tr>
                        <tr>
                            <td>توضیحات</td>
                            <td>
                                <textarea name="detail" id="detail" cols="30" rows="10" class="setinp"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                            </td>
                            <td><input type="submit" name="sbm" value="به اشتراک بزار" style="float:left"></td>
                        </tr>
                    </table>
                </form>
            <?php } ?>
        </div>
    </div>
</div>
<?php
    $this->load_template('footer.php');
?>
